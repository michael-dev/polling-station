<?php

require_once "../lib/config.php";
require_once "../lib/database.php";

function relogin($msg = "Sie haben sich nicht Authentifiziert.") {
 global $realm;
 header('WWW-Authenticate: Basic realm="'.$realm.'"');
 header('HTTP/1.0 401 Unauthorized');
 echo $msg;
 echo '<script>self.history.back();</script>';
 die();
}

$self = $_SERVER["REQUEST_URI"];
$target = dirname(dirname($self));
header('Location: ' . $target);

if (!login() || $_SERVER['PHP_AUTH_USER'] == $_REQUEST["nouser"]) {
 relogin();
}

?>
