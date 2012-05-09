<?php

require "../lib/auth.php";

$sth = newStm("Select id as id, mtknr as mtknr, concat(vorname, ' ', namenszusatz, ' ', nachname) as name, geburtsdatum as geburtsdatum, (select f.name from fakultaet f where f.id = s.fakultaet) as fak, studiengang as studg from stimmberechtigt s where freigegeben=0");
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
