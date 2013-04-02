<?php

if(ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');

require "../lib/auth.php";
require "../lib/csv.php";

global $wahlId, $tempdir;
$wahlId = (int) $_REQUEST["wahl"];

execStm(newStm("BEGIN;"));

/** processing header */

$sth = newStm("SELECT name AS name FROM wahl WHERE id = :wid");
execStm($sth, Array(":wid" => $wahlId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);
$name = $r[0]["name"];

/** processing election results */

$sth = newStm("SELECT COUNT(*) AS anzahl FROM stimmzettel s WHERE (invalid = 0 OR EXISTS (SELECT * FROM zettelstimme z WHERE s.wahl = z.wahl AND s.id = z.stimmzettel)) AND s.wahl = :wid");
execStm($sth, Array(":wid" => $wahlId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);
$numValidBallots = $r[0]["anzahl"];

$sth = newStm("SELECT COUNT(*) AS anzahl FROM stimmzettel s WHERE s.wahl = :wid");
execStm($sth, Array(":wid" => $wahlId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);
$numTotalBallots = $r[0]["anzahl"];

$voteResult = Array();
$voteResult[] = str_putcsv(Array("kid","votes"));
$voteResult[] = str_putcsv(Array("zettel_gesamt", $numTotalBallots));
$voteResult[] = str_putcsv(Array("zettel_gueltig", $numValidBallots));

$sth = newStm("SELECT k.kid as kandidatkid, (SELECT COUNT(*) FROM zettelstimme z INNER JOIN stimmzettel s ON s.wahl = z.wahl AND z.stimmzettel = s.id WHERE s.wahl = k.wahl AND z.kandidat = k.id) AS numVotes from kandidat k WHERE k.wahl = :wid ORDER BY numVotes DESC, k.id ASC");
execStm($sth, Array(":wid" => $wahlId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);

foreach ($r as $row) {
 $kid = $row["kandidatkid"];
 $votes = $row["numVotes"];
 $voteResult[] = str_putcsv(Array($kid, $votes));
}

execStm(newStm("COMMIT;"));

$downloadname = "wahl-$name-result.csv.gz";

header("Content-type: application/data");
header("Content-disposition: attachment; filename=\"$downloadname\";");
echo gzencode(implode("\n", $voteResult));

?>
