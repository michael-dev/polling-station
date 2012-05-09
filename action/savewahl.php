<?php

require "../lib/auth.php";

execStm(newStm("BEGIN;"),Array());
dblog("saveWahl in ".$_REQUEST["wahl"]." with name ".$_REQUEST["name"]." and kandidates ".join(",",$_REQUEST["kandidaten"]));

$id = $_REQUEST["wahl"];
if ($id == -1) {
 $sth = newStm("INSERT INTO wahl (name) VALUES (:name);");
 $id = execStm($sth, Array(":name" => $_REQUEST["name"]));
} else {
 $sth = newStm("UPDATE wahl SET name = :name WHERE id = :id LIMIT 1;");
 execStm($sth, Array(":name" => $_REQUEST["name"], ":id" => $id));
}

$sth = newStm("SELECT id AS id FROM kandidat WHERE wahl = :wid");
execStm($sth, Array(":wid" => $id));
$listOfKandidateIds = $sth->fetchAll(PDO::FETCH_COLUMN,0);

$sth = newStm("DELETE FROM kandidat WHERE wahl = :wid AND id = :kid");
foreach ($listOfKandidateIds as $kid) {
 if (!isset($_REQUEST["kandidaten"][$kid])) {
  execStm($sth, Array(":wid" => $id, ":kid" => $kid));
 }
}

$sthIns = newStm("INSERT INTO kandidat (id, name, wahl) VALUES (:kid, :name, :wid)");
$sthUpd = newStm("UPDATE kandidat SET name = :name WHERE id = :kid AND wahl = :wid");
foreach ($_REQUEST["kandidaten"] as $i => $name) {
 if (in_array($i, $listOfKandidateIds)) {
  execStm($sthUpd, Array(":kid" => $i, ":name" => $name, ":wid" => $id));
 } else {
  execStm($sthIns, Array(":kid" => $i, ":name" => $name, ":wid" => $id));
 }
}

execStm(newStm("COMMIT;"),Array());

?>
