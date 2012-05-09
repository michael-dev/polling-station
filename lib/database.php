<?php

require_once "config.php";

function dblog($msg) {
 execStm(newStm("INSERT INTO auszaehllog (message) VALUES (:msg)"), Array(":msg" => $_SERVER['REMOTE_ADDR'].":".$msg));
}

function login() {
  global $pdo, $db_uri;
  try {
   $pdo = new PDO(
    $db_uri,
    $_SERVER['PHP_AUTH_USER'],
    $_SERVER['PHP_AUTH_PW'],
    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
   );
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    return true;
  } catch (PDOException $e) {
    return false;
  }
};

function loginname() {
  return $_SERVER['PHP_AUTH_USER'];
}

function newStm($sql) {
 global $pdo;
 $sth = $pdo->prepare($sql);
 if ($sth === false) {
  header("HTTP/1.0 500 Internal Server Error");
  echo "Error prepare:$sql.";
  die();
 }
 return $sth;
}

function execStm($sth, $data=null, $msg="") {
 global $pdo;
 if ($data === null) {
  $data = Array();
 }
 $r = $sth->execute($data);
 if ($r === false) {
  $err = $sth->errorInfo();
  if ($err[0] == 42000) {
   relogin($err[2]);
  } else {
   header("HTTP/1.0 500 Internal Server Error");
   echo "Error: exec ins/upd wahl\n$msg\n";
   print_r($err);
  }
  die();
 }
 return $pdo->lastInsertId();
}

?>
