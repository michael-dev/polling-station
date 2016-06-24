<?php
# wahlId: $('#stapelWahlSelect').val(), filter:$('#filterMaxVerify').val()
# -1 == any

require "../lib/auth.php";
$wahlId = $_REQUEST["wahlId"];
$filter = $_REQUEST["filter"];
if ($wahlId == "null" || $wahlId === null || $wahlId === "" || $wahlId == -1) $wahlId = false;
if ($filter == "null" || $filter === null || $filter === "" || $filter == -1) $filter = false;

$sql =    "Select w.name as wahlname, w.id as wahl, s.id as id, s.numVotes as numVotes, (SELECT COUNT(*) FROM stimmzettel z WHERE z.stapel = s.id) as numBallot, (SELECT MIN(numOk) FROM stimmzettel z WHERE z.stapel = s.id ) as minVerify, (SELECT COUNT(*) FROM stimmzettel z WHERE z.stapel = s.id ".($filter !== false ? "AND numOk <= ". ((int)$filter) : "").") as todoBallot, (SELECT COUNT(*) FROM stimmzettel z WHERE z.stapel = s.id ".($filter !== false ? "AND numOk > ". ((int)$filter) : "").") as doneBallot from stapel s inner join wahl w on s.wahl = w.id having 1 ". ($wahlId !== false ? " AND wahl = ". ((int)$wahlId) : "") . ($filter !== false ? " AND (not exists(select * from stimmzettel zz where zz.stapel = s.id) OR minVerify <= " . ((int)$filter). ")" : ""). " order by wahl DESC, id ASC";
$sth = newStm($sql);

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
