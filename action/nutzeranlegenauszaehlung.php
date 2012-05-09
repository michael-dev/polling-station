<?php
require "../lib/auth.php";
global $db_host;

if ("" == trim($_REQUEST["name"])) die("Kein Name angegeben.");
if ("" == trim($_REQUEST["pass"])) die("Kein Passwort angegeben.");

$sqls = array();

$sqls[] = "create user :name@:host identified by :password";
$sqls[] = "GRANT SELECT ON wahl TO :name@:host";
$sqls[] = "GRANT SELECT ON kandidat TO :name@:host";
$sqls[] = "GRANT SELECT ON fakultaet TO :name@:host";
$sqls[] = "GRANT SELECT ON stapel TO :name@:host";
$sqls[] = "GRANT SELECT ON stimmzettel TO :name@:host";
$sqls[] = "GRANT SELECT ON zettelstimme TO :name@:host";
$sqls[] = "GRANT INSERT ON `stapel` TO :name@:host";
$sqls[] = "GRANT INSERT( `id`, `wahl`, `stapel`, `numOk`, `invalid` ) ON `stimmzettel` TO :name@:host";
$sqls[] = "GRANT INSERT( `message` ) ON `auszaehllog` TO :name@:host";
$sqls[] = "GRANT INSERT ON `zettelstimme` TO :name@:host";
$sqls[] = "GRANT UPDATE( `numOk`, `invalid` ) ON `stimmzettel` TO :name@:host";
$sqls[] = "GRANT UPDATE( `session`, `expiresAt` ) ON `stapel` TO :name@:host";
$sqls[] = "GRANT DELETE ON `zettelstimme` TO :name@:host";

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
