<?php
# session management file, if not logged, go back to login page
session_name('SexiAuditor');
session_start();
header('Cache-control: private'); // IE 6 FIX

if (!isset($_SESSION) || !isset($_SESSION['isLogged']) || !$_SESSION['isLogged'])
{
  
  # redirecting to login page with custom error message
  header('Location: login.php?e=timeout');
  exit;
  
} # END if (!isset($_SESSION) || !isset($_SESSION['isLogged']) || !$_SESSION['isLogged'])
?>
