<?php

require "../lib/auth.php";

execStm(newStm("BEGIN;"),Array());

$sth = newStm("UPDATE stapel SET session = :session, expiresAt = CURRENT_TIMESTAMP + 10 WHERE id = :id AND wahl = :wid AND (session = :session OR expiresAt < CURRENT_TIMESTAMP);");
execStm($sth, Array(":wid" => $_REQUEST["wahl"], ":id" => $_REQUEST["id"], ":session" => $_REQUEST["session"]));

execStm(newStm("COMMIT;"),Array());

?>
