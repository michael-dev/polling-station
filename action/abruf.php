<?php

require "../lib/auth.php";

$sth = newStm("Select id as id, s.fakultaet as fakId, concat(vorname, ' ', namenszusatz, ' ', nachname) as name, geburtsdatum as geburtsdatum, (SELECT f.name FROM fakultaet f WHERE f.id = s.fakultaet) as fak, studiengang as stud, freigegeben as registriert, (SELECT COUNT(*) FROM stimmen t WHERE t.id = s.id) as abgestimmt from stimmberechtigt s where id = :term limit 1");

$r = execStm($sth,Array(":term" => $_REQUEST["id"]));

try {
 $r = $sth->fetchAll(PDO::FETCH_ASSOC);
 header("Content-Type: application/json");
 echo json_encode($r[0]);
} catch (PDOException $e) {
 header("HTTP/1.0 500 Internal Server Error");
 var_dump($e);
}

?>
