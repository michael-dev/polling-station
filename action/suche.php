<?php

require "../lib/auth.php";

$sth = newStm("Select id as id, concat(vorname, ' ', namenszusatz, ' ', nachname) as name, geburtsdatum as geburtsdatum from stimmberechtigt where concat(lower(vorname), ' ', lower(nachname), ' - ', geburtsdatum) like :term or concat(lower(vorname), ' ', lower(namenszusatz), ' ', lower(nachname), ' - ', geburtsdatum) like :term or mtknr like :term or concat('id:', id) like :xterm");
$r = execStm($sth,Array(":term" => "%".strtolower($_REQUEST["term"])."%", ":xterm" => strtolower($_REQUEST["term"])));

try {
 $r = $sth->fetchAll(PDO::FETCH_ASSOC);
 if (strlen($_REQUEST["term"])<3) $r=array();
 header("Content-Type: application/json");
 echo json_encode($r);
} catch (PDOException $e) {
 header("HTTP/1.0 500 Internal Server Error");
 var_dump($e);
}

?>
