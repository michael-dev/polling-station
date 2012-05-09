<?php

require "../lib/auth.php";

$sth = newStm("SELECT k.id AS id, k.name AS kandidatname, (SELECT COUNT(*) FROM zettelstimme z INNER JOIN stimmzettel s ON s.wahl = z.wahl AND z.stimmzettel = s.id WHERE s.wahl = k.wahl AND z.kandidat = k.id) AS numVotes from kandidat k WHERE k.wahl = :wid ORDER BY k.id ASC");
$r = execStm($sth,Array(":wid" => $_REQUEST["wahl"]));

try {
 $r = $sth->fetchAll(PDO::FETCH_ASSOC);
 header("Content-Type: application/json");
 echo json_encode($r);
} catch (PDOException $e) {
 header("HTTP/1.0 500 Internal Server Error");
 var_dump($e);
}

?>
