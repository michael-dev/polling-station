<?php

require "../lib/auth.php";

execStm(newStm("BEGIN;"));
dblog("vote ".$_REQUEST["id"]);
$sth = newStm("insert into eingesammelt (id) values (:id)");
execStm($sth, Array(":id" => $_REQUEST["id"]));
execStm(newStm("COMMIT;"));

?>
