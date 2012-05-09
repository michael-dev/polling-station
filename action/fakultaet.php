<?php

require "../lib/auth.php";

$sth = newStm("Select id as value, name as label from fakultaet where lower(name) like :termx or id LIKE :term");
$r = execStm($sth,Array(":termx" => "%".strtolower($_REQUEST["term"])."%", ":term" => strtolower($_REQUEST["term"])));

try {
 $r = $sth->fetchAll(PDO::FETCH_ASSOC);
 header("Content-Type: application/json");
 echo json_encode($r);
} catch (PDOException $e) {
 header("HTTP/1.0 500 Internal Server Error");
 var_dump($e);
}

?>
