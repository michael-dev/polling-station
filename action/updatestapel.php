<?php

require "../lib/auth.php";

execStm(newStm("BEGIN;"));
dblog("updStapel ".$_REQUEST["wahl"]."/".$_REQUEST["id"]." with numVotes ".$_REQUEST["numVotes"]);
$sth = newStm("UPDATE stapel SET numVotes = :numVotes WHERE wahl = :wid and id = :id LIMIT 1");
execStm($sth, Array(":id" => $_REQUEST["id"], ":wid" => $_REQUEST["wahl"], ":numVotes" => $_REQUEST["numVotes"]));
execStm(newStm("COMMIT;"));

?>
