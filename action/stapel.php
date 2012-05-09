<?php

require "../lib/auth.php";

$sth = newStm("Select id as id, numVotes as numVotes from stapel where wahl = :wahl");
$r = execStm($sth,Array(":wahl" => $_REQUEST["wahl"]));

try {
 $r = $sth->fetchAll(PDO::FETCH_ASSOC);
 header("Content-Type: application/json");
 echo json_encode($r);
} catch (PDOException $e) {
 header("HTTP/1.0 500 Internal Server Error");
 var_dump($e);
}

?>
