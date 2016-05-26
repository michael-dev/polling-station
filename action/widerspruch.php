<?php

require "../lib/auth.php";

execStm(newStm("BEGIN;"),Array());
$txt = "";
foreach ($_REQUEST as $k => $v)
 $txt .= " $k => $v";
dblog("widerspruch $txt");

$sth = newStm("INSERT INTO stimmberechtigt (mtknr, namenszusatz, vorname, nachname, geburtsdatum, fakultaet, studiengang, freigegeben) VALUES (:mtknr, :namenszusatz, :vorname, :nachname, :geburtsdatum, :fakultaet, :studiengang, :freigegeben)");
execStm($sth,Array(":mtknr" => $_REQUEST["wMatrikel"],
                    ":vorname" => $_REQUEST["wVorname"],
                    ":namenszusatz" => $_REQUEST["wNamenszusatz"],
                    ":nachname" => $_REQUEST["wNachname"],
                    ":geburtsdatum" => $_REQUEST["wGeburtsdatum"],
                    ":fakultaet" => $_REQUEST["wFak"],
                    ":studiengang" => $_REQUEST["wStudiengang"],
                    ":freigegeben" => 0));
execStm(newStm("COMMIT;"),Array());
?>
