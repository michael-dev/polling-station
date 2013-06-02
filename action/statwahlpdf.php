<?php

if(ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');

require "../lib/auth.php";
require "../lib/latex.php";

global $wahlId, $tempdir;
$wahlId = (int) $_REQUEST["wahl"];

execStm(newStm("BEGIN;"));

/** processing header */

$sth = newStm("SELECT name AS name FROM wahl WHERE id = :wid");
execStm($sth, Array(":wid" => $wahlId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);
$titel = $r[0]["name"];

$sth = newStm("SELECT COUNT(*) AS anzahl FROM kandidat WHERE wahl = :wid");
execStm($sth, Array(":wid" => $wahlId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);
$numKand = $r[0]["anzahl"];
if ($numKand <= 15) {
  $orientation = "portrait";
  $itemPerPage = 40;
} else {
  $orientation = "landscape";
  $itemPerPage = 20;
}

$base = dirname(dirname(__FILE__))."/latex";
$tmpfname = tempnam($tempdir, "STATWAHL");
$handle = fopen($tmpfname, "w");
$header = file_get_contents($base."/prefix.tex");
$header = str_replace("%%TITEL%%", sanitizeLatex($titel), $header);
$header = str_replace("%%ORIENTATION%%", $orientation, $header);
fwrite($handle, $header);

/** processing election results */

fwrite($handle,"\\section{Wahlergebnis}\n");

$wahlergebnis = "\\begin{tabular}{l l |c| r r r}\n";
$wahlergebnis .= "\\multicolumn{2}{c|}{\\textbf{Stimmverteilung}} &  & \\multicolumn{3}{c}{Anteil an} \\\\\n";
$wahlergebnis .= "\\multicolumn{2}{c|}{Kandidat} & Stimmen & gültigen Stimmen & gültigen Stimmzetteln & allen Stimmzetteln \\\\\n";
$wahlergebnis .= "\\hline\n";

$sth = newStm("SELECT COUNT(*) AS anzahl FROM stimmzettel s WHERE (invalid = 0 OR EXISTS (SELECT * FROM zettelstimme z WHERE s.wahl = z.wahl AND s.id = z.stimmzettel)) AND s.wahl = :wid");
execStm($sth, Array(":wid" => $wahlId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);
$numValidBallots = $r[0]["anzahl"];

$sth = newStm("SELECT COUNT(*) AS anzahl FROM zettelstimme z WHERE z.wahl = :wid");
execStm($sth, Array(":wid" => $wahlId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);
$numTotalVotes = $r[0]["anzahl"];

$sth = newStm("SELECT COUNT(*) AS anzahl FROM stimmzettel s WHERE NOT EXISTS (SELECT * FROM zettelstimme z WHERE z.wahl = s.wahl AND z.stimmzettel = s.id) AND s.wahl = :wid AND invalid = 1");
execStm($sth, Array(":wid" => $wahlId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);
$numInvalidVotes = $r[0]["anzahl"];

$sth = newStm("SELECT COUNT(*) AS anzahl FROM stimmzettel s WHERE s.wahl = :wid");
execStm($sth, Array(":wid" => $wahlId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);
$numTotalBallots = $r[0]["anzahl"];

$sth = newStm("SELECT k.id AS id, k.name AS kandidatname, (SELECT COUNT(*) FROM zettelstimme z INNER JOIN stimmzettel s ON s.wahl = z.wahl AND z.stimmzettel = s.id WHERE s.wahl = k.wahl AND z.kandidat = k.id) AS numVotes from kandidat k WHERE k.wahl = :wid ORDER BY numVotes DESC, k.id ASC");
execStm($sth, Array(":wid" => $wahlId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);

foreach ($r as $row) {
 $id = sanitizeLatex($wahlId."/".$row["id"]);
 $name = sanitizeLatex($row["kandidatname"]);
 $votes = $row["numVotes"];
 if ($numTotalVotes > 0) {
  $percentage1 = round(100 * (double) $votes / $numTotalVotes)."\\%";
 } else {
  $percentage1 = "n/a";
 }
 if ($numValidBallots > 0) {
  $percentage2 = round(100 * (double) $votes / $numValidBallots)."\\%";
 } else {
  $percentage2 = "n/a";
 }
 if ($numTotalBallots > 0) {
  $percentage3 = round(100 * (double) $votes / $numTotalBallots)."\\%";
 } else {
  $percentage3 = "n/a";
 }
 $wahlergebnis .= "$id & $name & $votes & $percentage1 & $percentage2 & $percentage3 \\\\\n";
}

if ($numTotalBallots > 0) {
 $percentage3 = round(100 * (double) $numInvalidVotes / $numTotalBallots)."\\%";
} else {
 $percentage3 = "n/a";
}
$wahlergebnis .= "- & Ungültig & $numInvalidVotes & n/a & n/a & $percentage3 \\\\\n";

$wahlergebnis .= "\\end{tabular}\n";
fwrite($handle, $wahlergebnis);

$wahlergebnis .= "\\\n";

$wahlergebnis = "\\begin{tabular}{l|r}\n";
$wahlergebnis .= "\\textbf{Wahlbeteiligung} & Anzahl \\\\\n";
$wahlergebnis .= "\\hline\n";
$wahlergebnis .= "Stimmzettel & ".$numTotalBallots." \\\\\n";
$wahlergebnis .= "... davon gültige Stimmzettel & ".$numValidBallots." \\\\\n";
$wahlergebnis .= "... davon ungültige Stimmzettel & ".$numInvalidVotes." \\\\\n";
$wahlergebnis .= "gültige Stimmen & ".$numTotalVotes." \\\\\n";
$wahlergebnis .= "\\end{tabular}\n";
fwrite($handle, $wahlergebnis);

/** printing each ballot */
$sth = newStm("SELECT k.id AS id, k.name AS kandidatname FROM kandidat k WHERE k.wahl = :wid ORDER BY k.id ASC");
execStm($sth, Array(":wid" => $wahlId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);

$candidate = Array();
foreach ($r as $row) {
 $candidate[$row["id"]] = $row["kandidatname"];
}

fwrite($handle, "\\newpage\n");
fwrite($handle,"\\section{Einzelnachweis}\n");

foreach ($candidate as $i => $name) {
 $votesCounter[$i] = 0;
}
$lfdNr = 0;

function ballotTableHeader($candidate) {
 global $wahlId;
 $wahlergebnis = "\\begin{tabular}{l l l";
 for ($i=0; $i < count($candidate); $i++)
  $wahlergebnis .= " c";
 $wahlergebnis .= "}\n";

 $wahlergebnis .= "\\begin{sideways}lfd. Nr.\\end{sideways} & ID & Stapel";
 foreach ($candidate as $i => $name) {
  $wahlergebnis .= " & \\begin{sideways}".sanitizeLatex($wahlId."/$i").": ".sanitizeLatex($name)."\\end{sideways}";
 }
 $wahlergebnis .= " \\\\\n";
 $wahlergebnis .= "\\hline\n";
 return $wahlergebnis;
}

function ballotTableFooter() {
 $wahlergebnis = "\\end{tabular}\n";
 return $wahlergebnis;
}

function pageBreakingRow($votesCounter, $text) {
  $txt = "\\multicolumn{3}{c}{".$text."}";
  foreach ($votesCounter as $nr)
   $txt .= " & ".((int)$nr);
  $txt .= " \\\\\n";
  return $txt;
}

fwrite($handle, ballotTableHeader($candidate));
fwrite($handle, pageBreakingRow($votesCounter,"Ausgangswert"));
fwrite($handle, "\\hline\n");

/* foreach ballot */
$sth = newStm("SELECT id as id, stapel as stapel, invalid as invalid FROM stimmzettel WHERE wahl = :wid ORDER BY id ASC");
execStm($sth, Array(":wid" => $wahlId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);

foreach ($r as $row) {
 /* fetch all votes on ballot */
 $sth2 = newStm("SELECT kandidat as kandidat FROM zettelstimme WHERE stimmzettel = :id AND wahl = :wid ORDER BY kandidat ASC");
 execStm($sth2, Array(":wid" => $wahlId, ":id" => $row["id"]));
 $r2 = $sth2->fetchAll(PDO::FETCH_ASSOC);
 $votes = Array();
 foreach ($r2 as $row2) {
  $votes[$row2["kandidat"]] = true;
 }

 /* print ballot & update counters */
 $wahlergebnis = "$lfdNr & " . $wahlId."/".$row["id"] . " & " . $wahlId."/".$row["stapel"] ;
 if (count($votes) == 0 && $row["invalid"] == 1) { // invalid or empty ballot
   $wahlergebnis .= " & \\multicolumn{".count($candidate)."}{l}{ung\\\"ultiger Stimmzettel}";
 } else {
  foreach ($candidate as $i => $name) {
   $currentItem = "-";
   if ($votes[$i])
    $currentItem = ++$votesCounter[$i];
   $wahlergebnis .= " & ". $currentItem;
  }
 }
 $wahlergebnis .= "\\\\\n";
 fwrite($handle, $wahlergebnis);

 $lfdNr++;

 /* print page break */
 if ($lfdNr % $itemPerPage == 0) {
  fwrite($handle, "\\hline\n");
  fwrite($handle, pageBreakingRow($votesCounter,"Zwischensumme"));
  fwrite($handle, ballotTableFooter($candidate));
  fwrite($handle, "\\newpage\n");
  fwrite($handle, ballotTableHeader($candidate));
  fwrite($handle, pageBreakingRow($votesCounter,"Übertrag"));
  fwrite($handle, "\\hline\n");
 }

}

fwrite($handle, "\\hline\n");
fwrite($handle, pageBreakingRow($votesCounter,"Endergebnis"));
fwrite($handle, ballotTableFooter($candidate));

/** footer */

fwrite($handle, file_get_contents($base."/suffix.tex"));
fclose($handle);

chdir(dirname($tmpfname));
//echo system("pdflatex $tmpfname");
$ret = -1;
$out = Array();
exec("/usr/bin/pdflatex -interaction batchmode \"$tmpfname\" 2>&1", $out, $ret);
exec("/usr/bin/pdflatex -interaction batchmode \"$tmpfname\" 2>&1", $out, $ret);
exec("/usr/bin/pdflatex -interaction batchmode \"$tmpfname\" 2>&1", $out, $ret);

if ($ret != 0) {
 $ret = join("\n",$ret);
 header("HTTP/1.0 500 Internal Server Error");
 echo "<pre>$out\n$ret</pre>";
 exit;
}

unlink($tmpfname);
unlink($tmpfname.".aux");
unlink($tmpfname.".log");
unlink($tmpfname.".out");

execStm(newStm("COMMIT;"));

$downloadname = "wahl-$wahlId-".date("Ymd-His").".pdf";

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false); // required for certain browsers 
header("Content-Transfer-Encoding: binary"); 
header("Content-Length: ".filesize($tmpfname.".pdf"));
header("Content-type: application/pdf");
header("Content-disposition: attachment; filename=\"$downloadname\";");
readfile($tmpfname.".pdf");
@unlink($tmpfname.".pdf");

?>
