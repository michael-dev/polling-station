<?php

require "../lib/auth.php";

execStm(newStm("BEGIN;"),Array());
dblog("saveStapel in ".$_REQUEST["wahl"]." with ".$_REQUEST["numVotes"]);

$sth = newStm("INSERT INTO stapel (wahl, numVotes) VALUES (:wid, :numVotes);");
$id = execStm($sth, Array(":wid" => $_REQUEST["wahl"], ":numVotes" => $_REQUEST["numVotes"]));

execStm(newStm("COMMIT;"),Array());

header("Content-Type: application/json");
echo json_encode(Array("id"=>$id));

?>
