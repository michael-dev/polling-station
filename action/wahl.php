<?php

require "../lib/auth.php";

$sth = newStm("Select id as id, name as name from wahl");
$r = execStm($sth,Array());

try {
 $r = $sth->fetchAll(PDO::FETCH_ASSOC);
 header("Content-Type: application/json");
 echo json_encode($r);
} catch (PDOException $e) {
 header("HTTP/1.0 500 Internal Server Error");
 var_dump($e);
}

?>
