<?php

require "../lib/auth.php";

$id = $_REQUEST["wahl"];
if ($id == -1 || $id === "") {
 header("HTTP/1.0 500 Internal Server Error");
 echo "Error: invalid wahl value";
}

execStm(newStm("BEGIN;"));
dblog("delwahl ".$_REQUEST["wahl"]);
$sth = newStm("DELETE FROM kandidat WHERE wahl = :id");
execStm($sth, Array(":id" => $id));
$sth = newStm("DELETE FROM wahl WHERE id = :id LIMIT 1");
execStm($sth, Array(":id" => $id));
execStm(newStm("COMMIT;"));

?>
