<?php

require "../lib/auth.php";

execStm(newStm("BEGIN;"),Array());
if (!isset($_FILES["file"])) {
  dblog("saveWahl in ".$_REQUEST["wahl"]." with name ".$_REQUEST["name"]." and kandidates ".join(",",$kandidatenName)." and kids ".join(",",$kandidatenKid));
  $kandidatenName = $kandidatenName;
  $kandidatenKid = $kandidatenKid;
} elseif($_FILES["file"]["error"] != 0) {
  header("HTTP/1.0 500 Internal Server Error");
  echo "Error: file upload failed";
  exit;
} else {
  dblog("saveWahl in ".$_REQUEST["wahl"]." with name ".$_REQUEST["name"]." and file upload");
  $data = gzfile($_FILES["file"]["tmp_name"]);
  if ($data === false) {
    header("HTTP/1.0 500 Internal Server Error");
    echo "Error: uploaded file wasn't gzip encoded";
    exit;
  }
  $data = explode("\n", implode("", $data));
  for($i=0; $i < count($data); $i++) $data[$i] = str_getcsv($data[$i]);
  $colName = array_search("name",$data[0]);
  $colKid = array_search("kid",$data[0]);
  if ($colName === false || $colKid === false) {
    header("HTTP/1.0 500 Internal Server Error");
    echo "Error: uploaded file has no name,kid columns";
    exit;
  }
  $kandidatenName = Array();
  $kandidatenKid = Array();
  for($i=1; $i < count($data); $i++) {
    $kandidatenName[] = $data[$i][$colName];
    $kandidatenKid[] = $data[$i][$colKid];
  }
}

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
 if (!isset($kandidatenName[$kid])) {
  execStm($sth, Array(":wid" => $id, ":kid" => $kid));
 }
}

$sthIns = newStm("INSERT INTO kandidat (id, name, wahl, kid) VALUES (:id, :name, :wid, :kid)");
$sthUpd = newStm("UPDATE kandidat SET name = :name, kid = :kid WHERE id = :id AND wahl = :wid");
foreach ($kandidatenName as $i => $name) {
 $kid = $kandidatenKid[$i];
 if (in_array($i, $listOfKandidateIds)) {
  execStm($sthUpd, Array(":id" => $i, ":name" => $name, ":wid" => $id, ":kid" => $kid));
 } else {
  execStm($sthIns, Array(":id" => $i, ":name" => $name, ":wid" => $id, ":kid" => $kid));
 }
}

execStm(newStm("COMMIT;"),Array());

?>
