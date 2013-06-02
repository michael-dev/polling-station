<?php

require "../lib/auth.php";

// parameters
// always: {wahl:wahlId, stapel:stapelId, minNext = currentBallotId + 1, session = sessionId, maxNumOk = maxVoteCheckCount};
// a)
//  modify := currentBallotId;
//  newVotes = [votes]
//  invalid = 1 if invalid, 0 else
// b)
//  confirm = currentBallotId;
// return
//  status => OK | message
//  votes => Array of voteIds
//  id => new ballot id
//  found => true of ballot found
execStm(newStm("BEGIN;"),Array());
$txt = "";
foreach ($_REQUEST as $k => $v) {
 if (is_array($v)) $v = join(",",$v);
 $txt .= " $k => $v";
}
dblog("verfyBallot $txt");

$sth = newStm("SELECT COUNT(*) as i FROM stapel WHERE id = :stapelid AND wahl = :wahlid AND (expiresAt < CURRENT_TIMESTAMP OR session = :session)");
execStm($sth, Array(":stapelid" => $_REQUEST["stapel"],
                    ":wahlid" => $_REQUEST["wahl"],
                    ":session" => $_REQUEST["session"]));
$r = $sth->fetchAll();
if ($r[0]["i"] != 1) {
  header("Content-Type: application/json");
  echo json_encode(Array("status"=>"Der Stapel ist durch einen anderen Nutzer gesperrt."));
  exit;
}

if (isset($_REQUEST["confirm"])) {
 $sth = newStm("UPDATE stimmzettel SET numOk = numOk + 1 WHERE wahl = :wid AND id = :id;");
 execStm($sth, Array(":id" => $_REQUEST["confirm"], ":wid" => $_REQUEST["wahl"]));
}

if (isset($_REQUEST["modify"])) {
 $sth = newStm("DELETE FROM zettelstimme WHERE wahl = :wid AND stimmzettel = :id;");
 execStm($sth, Array(":id" => $_REQUEST["modify"], ":wid" => $_REQUEST["wahl"]));
 $sth = newStm("UPDATE stimmzettel SET numOk = 0, invalid = :invalid WHERE wahl = :wid AND id = :id;");
 execStm($sth, Array(":id" => $_REQUEST["modify"], ":wid" => $_REQUEST["wahl"], ":invalid" => $_REQUEST["invalid"]));
 $sth = newStm("INSERT INTO zettelstimme (wahl, stimmzettel, kandidat) VALUES (:wid, :id, :vote);");
 foreach ($_REQUEST["newVotes"] as $vote) {
  execStm($sth, Array(":id" => $_REQUEST["modify"], ":wid" => $_REQUEST["wahl"], ":vote" => $vote));
 }
}
$sth = newStm("SELECT id AS id, invalid as invalid FROM stimmzettel WHERE id >= :minNextId AND wahl = :wid AND stapel = :sid AND numOk < :maxNumOk ORDER BY id ASC LIMIT 1");
execStm($sth, Array(":minNextId" => $_REQUEST["minNext"], ":wid" => $_REQUEST["wahl"], ":sid" => $_REQUEST["stapel"], ":maxNumOk" => $_REQUEST["maxNumOk"]));

$stimmzettel = $sth->fetchAll(PDO::FETCH_ASSOC);
$result = Array("status" => "OK");
if (count($stimmzettel) > 0) {
 $stimmzettel = $stimmzettel[0];
 $result["id"] = $stimmzettel["id"];
 $result["invalid"] = $stimmzettel["invalid"];
 $result["found"] = true;

 $sth = newStm("SELECT kandidat AS kandidat FROM zettelstimme WHERE stimmzettel = :id AND wahl = :wid");
 execStm($sth, Array(":id" => $stimmzettel["id"], ":wid" => $_REQUEST["wahl"]));
 $votes = $sth->fetchAll(PDO::FETCH_ASSOC);
 $result["votes"] = Array();
 foreach ($votes as $row) {
  $result["votes"][] = $row["kandidat"];
 }
} else {
 $result["found"] = false;
}

execStm(newStm("COMMIT;"),Array());

try {
 header("Content-Type: application/json");
 echo json_encode($result);
} catch (PDOException $e) {
 header("HTTP/1.0 500 Internal Server Error");
 var_dump($e);
}

?>
