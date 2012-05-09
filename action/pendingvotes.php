<?php

/* return fields: id, name, zeitpunkt, fakname, studiengang */

require "../lib/auth.php";

$sth = newStm("SELECT s.id as id, concat(b.vorname, ' ', b.namenszusatz, ' ', b.nachname) as name, (SELECT GROUP_CONCAT(a.zettel) FROM ausgabezettel a WHERE a.id = s.id ORDER BY zettel) as zettel, s.timestamp as zeitpunkt, (unix_timestamp(current_timestamp) - unix_timestamp(timestamp) < 60 * 60 * 6) as recent FROM stimmen s INNER JOIN stimmberechtigt b ON s.id = b.id WHERE NOT EXISTS (SELECT * FROM eingesammelt e WHERE e.id = s.id) ORDER BY (unix_timestamp(current_timestamp) - unix_timestamp(timestamp)) div (60 * 30) ASC, name ASC");
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
