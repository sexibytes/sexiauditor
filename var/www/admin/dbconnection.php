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
  header("Location: exception.php?e=dbcon");
  exit;
}
?>
