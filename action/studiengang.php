<?php

require "../lib/auth.php";

$sth = newStm("Select distinct studiengang  as value, studiengang as label from stimmberechtigt where lower(studiengang) like :term");
$r = execStm($sth,Array(":term" => "%".strtolower($_REQUEST["term"])."%"));

try {
 $r = $sth->fetchAll(PDO::FETCH_ASSOC);
 header("Content-Type: application/json");
 echo json_encode($r);
} catch (PDOException $e) {
 header("HTTP/1.0 500 Internal Server Error");
 var_dump($e);
}

?>
