<?php

require "../lib/auth.php";

switch ($_REQUEST["action"]):
 case "accept":
   $sql = "UPDATE stimmberechtigt SET freigegeben = 1 WHERE id = :id";
   break;
 case "deny":
   $sql = "DELETE FROM stimmberechtigt WHERE id = :id LIMIT 1";
   break;
 default:
   exit;
   break;
endswitch;

execStm(newStm("BEGIN;"));
dblog("decide ".$_REQUEST["id"]." => ".$_REQUEST["action"]);
$sth = newStm($sql);
execStm($sth, Array(":id" => $_REQUEST["id"]));
execStm(newStm("COMMIT;"));

?>
