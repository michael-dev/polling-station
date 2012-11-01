<?php

require "../lib/auth.php";

$sth1 = newStm("SELECT f.id as id, name as name, (SELECT COUNT(*) FROM stimmberechtigt b WHERE b.fakultaet = f.id AND freigegeben = 1) AS gesamt, (SELECT COUNT(*) FROM stimmberechtigt b INNER JOIN eingesammelt s ON s.id = b.id WHERE b.fakultaet = f.id) AS abgegeben FROM fakultaet f");
$sth2 = newStm("SELECT a.zettel as zettel, COUNT(*) AS ausgegeben FROM stimmberechtigt b INNER JOIN ausgabezettel a ON a.id = b.id INNER JOIN eingesammelt s ON s.id = b.id GROUP BY a.zettel ORDER BY a.zettel");
execStm($sth1,Array());
execStm($sth2,Array());

try {
 $r1 = $sth1->fetchAll(PDO::FETCH_ASSOC);
 $r2 = $sth2->fetchAll(PDO::FETCH_ASSOC);
 header("Content-Type: application/json");
 $data = Array("overview" => $r1, "detail" => $r2);
 echo json_encode($data);
} catch (PDOException $e) {
 header("HTTP/1.0 500 Internal Server Error");
 var_dump($e);
}

?>
