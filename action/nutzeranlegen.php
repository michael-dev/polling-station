<?php
require "../lib/auth.php";
global $db_host;

if ("" == trim($_REQUEST["name"])) die("Kein Name angegeben.");
if ("" == trim($_REQUEST["pass"])) die("Kein Passwort angegeben.");

$sqls = array();

$sqls[] = "create user :name@:host identified by :password";
$sqls[] = "GRANT SELECT ON stimmen TO :name@:host";
$sqls[] = "GRANT SELECT ON stimmberechtigt TO :name@:host";
$sqls[] = "GRANT SELECT ON fakultaet TO :name@:host";
$sqls[] = "GRANT SELECT ON ausgabezettel TO :name@:host";
$sqls[] = "GRANT SELECT ON eingesammelt TO :name@:host";
$sqls[] = "GRANT INSERT( `mtknr` , `namenszusatz` , `vorname` , `nachname` , `geburtsdatum` , `fakultaet` , `studiengang` ) ON `stimmberechtigt` TO :name@:host";
$sqls[] = "GRANT INSERT( `id` ) ON `stimmen` TO :name@:host";
$sqls[] = "GRANT INSERT( `id`, `zettel` ) ON `ausgabezettel` TO :name@:host";
$sqls[] = "GRANT INSERT( `id` ) ON `eingesammelt` TO :name@:host";
$sqls[] = "GRANT INSERT( `message` ) ON `auszaehllog` TO :name@:host";

execStm(newStm("BEGIN;"), Array());
dblog("adduser ".$_REQUEST["name"]);

foreach ($sqls as $i => $sql) {
 $sth = newStm($sql);
 $data = Array();
 $data[":name"] = $_REQUEST["name"];
 if ($i == 0)
  $data[":password"] = $_REQUEST["pass"];
 $data[":host"] = $db_host;
 execStm($sth, $data);
}

execStm(newStm("COMMIT;"), Array());

?>
