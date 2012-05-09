<?php

require_once "../lib/config.php";
require_once "../lib/database.php";

global $realm;

if (!login()) {
   header('WWW-Authenticate: Basic realm="'.$realm.'"');
   header('HTTP/1.0 400 Unauthorized');
   echo 'Sie haben sich nicht Authentifiziert.';
   exit;
}

header("Content-Type: application/json");
echo json_encode(loginname());

?>
