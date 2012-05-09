<?php

require_once "../lib/config.php";
require_once "../lib/database.php";

function relogin($msg = "Sie haben sich nicht Authentifiziert.") {
 global $realm;
 header('WWW-Authenticate: Basic realm="'.$realm.'"');
 header('HTTP/1.0 401 Unauthorized');
 echo $msg;
 die();
}

if (!login()) {
 relogin();
}

?>
