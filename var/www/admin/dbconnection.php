<?php
require_once ('MysqliDb.php');
$dbServer = 'localhost';
$dbUername = 'sexiauditor';
$dbPassword = 'Sex!@ud1t0r';
$dbDabatase = 'sexiauditor';
$db = new MysqliDb ($dbServer, $dbUername, $dbPassword, $dbDabatase);
try {
  $db->connect();
} catch (Exception $e) {
  # Any exception will be ending the script, we want exception-free run
  # CSS hack for navbar margin removal
  echo '  <style>#wrapper { margin-bottom: 0px !important; }</style>'."\n";
  require("exception.php");
  exit;
}
?>
