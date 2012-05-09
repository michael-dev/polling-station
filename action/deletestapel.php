<?php

require "../lib/auth.php";

execStm(newStm("BEGIN;"));
dblog("delstapel ".$_REQUEST["wahl"]."/".$_REQUEST["id"]);
$sth = newStm("DELETE FROM stapel WHERE wahl = :wid and id = :id");
execStm($sth, Array(":id" => $_REQUEST["id"], ":wid" => $_REQUEST["wahl"]));
execStm(newStm("COMMIT;"));

?>
