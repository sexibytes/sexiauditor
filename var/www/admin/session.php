<?php
# session management file, if not logged, go back to login page
session_name('SexiAuditor');
session_start();
header('Cache-control: private'); // IE 6 FIX
if (!isset($_SESSION) || !isset($_SESSION['isLogged']) || !$_SESSION['isLogged']) { header('Location: login.php'); }
?>
