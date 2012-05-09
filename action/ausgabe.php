<?php

require "../lib/auth.php";

execStm(newStm("BEGIN;"));
dblog("vote ".$_REQUEST["id"]);
$sth = newStm("insert into stimmen (id) values (:id)");
execStm($sth, Array(":id" => $_REQUEST["id"]));
$sth = newStm("insert into ausgabezettel (id, zettel) values (:id, :zettel)");
foreach ($_REQUEST["zettel"] as $zettel) {
 execStm($sth, Array(":id" => $_REQUEST["id"], ":zettel" => $zettel));
}
execStm(newStm("COMMIT;"));

?>
