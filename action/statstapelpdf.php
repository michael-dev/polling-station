<?php

if(ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');

require "../lib/auth.php";
require "../lib/latex.php";

global $wahlId, $tempdir;
$wahlId = (int) $_REQUEST["wahl"];
$stapelId = (int) $_REQUEST["stapel"];

execStm(newStm("BEGIN;"));

/** processing header */

$sth = newStm("SELECT name AS name FROM wahl WHERE id = :wid");
execStm($sth, Array(":wid" => $wahlId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);
$titel = $r[0]["name"]. " Stapel $wahlId/$stapelId";

$sth = newStm("SELECT COUNT(*) AS anzahl FROM kandidat WHERE wahl = :wid");
execStm($sth, Array(":wid" => $wahlId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);
$numKand = $r[0]["anzahl"];
$rotate = ($numKand > 26);
$orientation = (!$rotate) ? "portrait" : "landscape";

$base = dirname(dirname(__FILE__))."/latex";
$tmpfname = tempnam($tempdir, "STATWAHL");
$handle = fopen($tmpfname, "w");
$header = file_get_contents($base."/prefix.tex");
$header = str_replace("%%TITEL%%", sanitizeLatex($titel), $header);
$header = str_replace("%%ORIENTATION%%", $orientation, $header);
fwrite($handle, $header);

/** processing election results */

$sth = newStm("SELECT COUNT(*) AS anzahl FROM stimmzettel s WHERE (invalid = 0 OR EXISTS (SELECT * FROM zettelstimme z WHERE s.wahl = z.wahl AND s.id = z.stimmzettel)) AND s.wahl = :wid AND s.stapel = :sid");
execStm($sth, Array(":wid" => $wahlId, ":sid" => $stapelId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);
$numValidBallots = $r[0]["anzahl"];

$sth = newStm("SELECT COUNT(*) AS anzahl FROM zettelstimme z INNER JOIN stimmzettel s ON s.wahl = z.wahl AND s.id = z.stimmzettel WHERE z.wahl = :wid AND s.stapel = :sid");
execStm($sth, Array(":wid" => $wahlId, ":sid" => $stapelId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);
$numTotalVotes = $r[0]["anzahl"];

$sth = newStm("SELECT COUNT(*) AS anzahl FROM stimmzettel s WHERE NOT EXISTS (SELECT * FROM zettelstimme z WHERE z.wahl = s.wahl AND z.stimmzettel = s.id) AND s.wahl = :wid AND s.stapel = :sid AND invalid = 1");
execStm($sth, Array(":wid" => $wahlId, ":sid" => $stapelId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);
$numInvalidVotes = $r[0]["anzahl"];

$sth = newStm("SELECT COUNT(*) AS anzahl FROM stimmzettel s WHERE s.wahl = :wid AND s.stapel = :sid");
execStm($sth, Array(":wid" => $wahlId, ":sid" => $stapelId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);
$numTotalBallots = $r[0]["anzahl"];

$sth = newStm("SELECT COUNT(*) AS anzahl FROM kandidat k WHERE k.wahl = :wid");
execStm($sth, Array(":wid" => $wahlId));
$r = $sth->fetchAll(PDO::FETCH_ASSOC);
$numKand = $r[0]["anzahl"];

$sth = newStm("SELECT k.id AS id, k.name AS kandidatname, (SELECT COUNT(*) FROM zettelstimme z INNER JOIN stimmzettel s ON s.wahl = z.wahl AND z.stimmzettel = s.id WHERE s.wahl = k.wahl AND z.kandidat = k.id AND s.stapel = :sid) AS numVotes from kandidat k WHERE k.wahl = :wid ORDER BY k.id ASC");
execStm($sth, Array(":wid" => $wahlId, ":sid" => $stapelId));
$kandidatenRows = $sth->fetchAll(PDO::FETCH_ASSOC);

$wahlergebnis = "";

$h = Array();
$h[0] = "\\textbf{Stammdaten}";
$h[1] = "\\textbf{Wahlergebnis}";
$h[2] = "\\textbf{Wahlbeteiligung}";

if (!$rotate) {
 foreach ($h as $i => $j)
  $h[$i] = "\\begin{sideways}$j\\end{sideways}";
}

$wahlergebnis .= "\\newsavebox{\\stammbox}\n";
$wahlergebnis .= "\\savebox{\\stammbox}{".$h[0]."}\n";
$wahlergebnis .= "\\newlength{\\stammb}\n";
$wahlergebnis .= "\\newlength{\\stammh}\n";
$wahlergebnis .= "\\settoheight{\\stammh}{\\usebox{\\stammbox}}\n";
$wahlergebnis .= "\\settowidth{\\stammb}{\\usebox{\\stammbox}}\n";
$wahlergebnis .= "\\setlength{\\stammh}{0.33\\stammh}\n";
$wahlergebnis .= "\\addtolength{\\stammh}{1ex}\n";

$wahlergebnis .= "\\newsavebox{\\stimmbox}\n";
$wahlergebnis .= "\\savebox{\\stimmbox}{".$h[1]."}\n";
$wahlergebnis .= "\\newlength{\\stimmb}\n";
$wahlergebnis .= "\\newlength{\\stimmh}\n";
$wahlergebnis .= "\\settoheight{\\stimmh}{\\usebox{\\stimmbox}}\n";
$wahlergebnis .= "\\settowidth{\\stimmb}{\\usebox{\\stimmbox}}\n";
$scale = 1.00 / ((double) $numKand + 1.0);
$wahlergebnis .= "\\setlength{\\stimmh}{".$scale."\\stimmh}\n";
$wahlergebnis .= "\\addtolength{\\stimmh}{1ex}\n";

$wahlergebnis .= "\\newsavebox{\\beteilbox}\n";
$wahlergebnis .= "\\savebox{\\beteilbox}{".$h[2]."}\n";
$wahlergebnis .= "\\newlength{\\beteilb}\n";
$wahlergebnis .= "\\newlength{\\beteilh}\n";
$wahlergebnis .= "\\settoheight{\\beteilh}{\\usebox{\\beteilbox}}\n";
$wahlergebnis .= "\\settowidth{\\beteilb}{\\usebox{\\beteilbox}}\n";
$wahlergebnis .= "\\setlength{\\beteilh}{0.25\\beteilh}\n";
$wahlergebnis .= "\\addtolength{\\beteilh}{1ex}\n";

if (!$rotate) {
 $w = "2cm";
 $extraheader = "|p{".$w."}|p{".$w."}";
 $extrarow = "& &";
} else {
 $w = "3cm";
 $extraheader = "|p{".$w."}|p{".$w."}|p{".$w."}";
 $extrarow = "& & &";
}
$wahlergebnis .= "\\begin{tabular}{l l l | >{\centering}m{".$w."}$extraheader}\n";
$wahlergebnis .= " & & & \\multicolumn{3}{c}{\\textbf{Ergebnisse der Zählungen}} \\\\\\hline\n";
$wahlergebnis .= "\\multirow{4}{*}{".$h[2]."} & & Stimmzettel & ".$numTotalBallots." $extrarow \\parbox[c][\\beteilh]{0cm}{}\\\\\n";
$wahlergebnis .= " & & ... davon gültige Stimmzettel & ".$numValidBallots." $extrarow \\parbox[c][\\beteilh]{0cm}{}\\\\\n";
$wahlergebnis .= " & & ... davon ungültige Stimmzettel & ".$numInvalidVotes." $extrarow \\parbox[c][\\beteilh]{0cm}{}\\\\\n";
$wahlergebnis .= " & & gültige Stimmen & ".$numTotalVotes." $extrarow \\parbox[c][\\beteilh]{0cm}{}\\\\\\hline\n";
$wahlergebnis .= "\\multirow{".($numKand+1)."}{*}{".$h[1]."} & & Ungültig & ".$numInvalidVotes." $extrarow \\parbox[c][\\stimmh]{0cm}{}\\\\\n";

foreach ($kandidatenRows as $row) {
 $id = sanitizeLatex($wahlId."/".$row["id"]);
 $name = sanitizeLatex($row["kandidatname"]);
 $votes = $row["numVotes"];
 $wahlergebnis .= " & $id & $name & $votes $extrarow \\parbox[c][\\stimmh]{0cm}{}\\\\\n";
}

$wahlergebnis .= "\\hline\n";

$wahlergebnis .= "\\multirow{3}{*}{".$h[0]."} & & Wahlhelfer & $extrarow \\parbox[c][\\stammh]{0cm}{}\\\\\n";
$wahlergebnis .= " & & Uhrzeit & $extrarow \\parbox[c][\\stammh]{0cm}{}\\\\\n";
$wahlergebnis .= " & & Unterschrift & $extrarow \\parbox[c][\\stammh]{0cm}{}\n";
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

/** footer */

fwrite($handle, file_get_contents($base."/suffix.tex"));
fclose($handle);

chdir(dirname($tmpfname));
//echo system("pdflatex $tmpfname");
$ret = -1;
$out = Array();
exec("/usr/bin/pdflatex -interaction batchmode \"$tmpfname\"", $out, $ret);
exec("/usr/bin/pdflatex -interaction batchmode \"$tmpfname\"", $out, $ret);
exec("/usr/bin/pdflatex -interaction batchmode \"$tmpfname\"", $out, $ret);

if ($ret != 0) {
 $out = implode("\n", $out);
 header("HTTP/1.0 500 Internal Server Error");
 echo "<pre>$out\n$ret</pre>";
 exit;
}

unlink($tmpfname);
unlink($tmpfname.".aux");
unlink($tmpfname.".log");
unlink($tmpfname.".out");

execStm(newStm("COMMIT;"));

$downloadname = "stapel-$wahlId-$stapelId-".date("Ymd-His").".pdf";

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
