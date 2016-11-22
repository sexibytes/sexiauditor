<?php
require("session.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != '1')
{
  
  header('Location: logout.php');

} # END if (!isset($_SESSION['role']) || $_SESSION['role'] != '1')

# class loading
require_once('class/PHPTail.class.php');

# Initilize a new instance of PHPTail
$tail = new PHPTail(array(,
  "vCron Errors" => "/var/log/sexiauditor/vcronScheduler.err",
  "vCron Scheduler" => "/var/log/sexiauditor/vcronScheduler.log"
));

# We're getting an AJAX call
if (isset($_GET['ajax']))
{
  
    echo $tail->getNewLines($_GET['file'], $_GET['lastsize'], $_GET['grep'], $_GET['invert']);
    die();
    
} # END if (isset($_GET['ajax']))

# Regular GET/POST call, print out the GUI
$tail->generateGUI();
?>
