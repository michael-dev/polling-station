<?php

require "../lib/auth.php";

$sth = newStm("SELECT k.id AS id, k.name AS kandidatname, (SELECT COUNT(*) FROM zettelstimme z INNER JOIN stimmzettel s ON s.wahl = z.wahl AND z.stimmzettel = s.id WHERE s.wahl = k.wahl AND z.kandidat = k.id AND s.stapel = :sid) AS numVotes from kandidat k WHERE k.wahl = :wid ORDER BY k.id ASC");
execStm($sth, Array(":sid" => $_REQUEST["stapel"], ":wid" => $_REQUEST["wahl"]));

$sthInv = newStm("SELECT COUNT(*) AS anzahl FROM stimmzettel s WHERE NOT EXISTS (SELECT * FROM zettelstimme z WHERE z.wahl = s.wahl AND z.stimmzettel = s.id) AND s.stapel = :sid AND s.wahl = :wid AND invalid = 1");
execStm($sthInv, Array(":sid" => $_REQUEST["stapel"], ":wid" => $_REQUEST["wahl"]));

$sthVal = newStm("SELECT COUNT(*) AS anzahl FROM stimmzettel s WHERE (invalid = 0 OR EXISTS (SELECT * FROM zettelstimme z WHERE z.wahl = s.wahl AND z.stimmzettel = s.id)) AND s.stapel = :sid AND s.wahl = :wid");
execStm($sthVal, Array(":sid" => $_REQUEST["stapel"], ":wid" => $_REQUEST["wahl"]));

try {
 $r = $sth->fetchAll(PDO::FETCH_ASSOC);
 $rInv = $sthInv->fetchAll(PDO::FETCH_ASSOC);
 $rVal = $sthVal->fetchAll(PDO::FETCH_ASSOC);
 header("Content-Type: application/json");
 $data = Array("votes" => $r, "invalid" => $rInv[0]["anzahl"], "valid" => $rVal[0]["anzahl"]);
 echo json_encode($data);
} catch (PDOException $e) {
 header("HTTP/1.0 500 Internal Server Error");
 var_dump($e);
}

?>
