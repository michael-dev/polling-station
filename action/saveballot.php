<?php

require "../lib/auth.php";

# $_REQUEST["invalid"] : 0 == false, 1 == true

execStm(newStm("BEGIN;"),Array());
$txt = "";
foreach ($_REQUEST as $k => $v) {
 if (is_array($v)) $v = join(",",$v);
 $txt .= " $k => $v";
}
dblog("saveBallot $txt");

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

$sth = newStm("INSERT INTO stimmzettel (id, wahl, stapel, numOk, invalid) VALUES (:id, :wid, :sid, 0, :inv);");
execStm($sth, Array(":id" => $_REQUEST["id"], ":wid" => $_REQUEST["wahl"], ":sid" => $_REQUEST["stapel"], ":inv" => $_REQUEST["invalid"]));

$sth = newStm("INSERT INTO zettelstimme (wahl, stimmzettel, kandidat) VALUES (:wid, :id, :vote);");
foreach ($_REQUEST["votes"] as $vote) {
  execStm($sth, Array(":id" => $_REQUEST["id"], ":wid" => $_REQUEST["wahl"], ":vote" => $vote));
}

execStm(newStm("COMMIT;"),Array());

header("Content-Type: application/json");
echo json_encode(Array("status"=>"OK"));

?>
