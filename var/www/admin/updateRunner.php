<?php
require("session.php");
$title = "SexiAuditor Package Update Runner";
$xmlPath = "/tmp/sexiauditor-update/sexiauditor-master/updateRunner.xml";
require("header.php");
require("helper.php");
$dir = '/var/www/admin/files/';
$SexiAuditorVersion = (file_exists('/etc/sexiauditor_version') ? file_get_contents('/etc/sexiauditor_version', FILE_USE_INCLUDE_PATH) : "Unknown");
?>
    <div class="container"><br/>
      <h2><span class="glyphicon glyphicon-hdd" aria-hidden="true"></span> SexiAuditor Package Update Runner</h2>
<?php
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST["submit"] != "upgrade-confirmed") {
      $tempVersionOutput = shell_exec("/usr/bin/unzip -p \"".$dir.$_POST['input-file']."\" sexiauditor-master/updateRunner.xml");
      preg_match('/<version>(.*)<\/version>/', $tempVersionOutput, $matches);
      $newVersion = (is_array($matches) && count($matches) > 0) ? $matches[0] : 'Unknown';
      echo '            <div class="alert alert-success" role="alert" style="padding: 5px 15px;"><p><span class="glyphicon glyphicon-hdd" aria-hidden="true"></span> <span style="font-weight: normal;">Current installed version: </span><strong>' . $SexiAuditorVersion . '</strong></p><p><span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span> <span style="font-weight: normal;">New package version: </span><strong>' . $newVersion . '</strong><p></div>
      <div class="alert alert-warning" role="warning"><h4><span class="glyphicon glyphicon-alert" aria-hidden="true"></span><span class="sr-only">Warning:</span> Upgrade process check!</h4>The upgrade process will be launched after you click on the \'Upgrade Me\' button.<br / >After the upgrade process is succeeded, SexiAuditor services will be restarted.<p>The following file will be used for upgrade process: '.$_POST['input-file'].'</p><form class="form" action="updateRunner.php" method="post"><input type="hidden" name="input-file" value="' . $_POST["input-file"] . '"><p><button name="submit" class="btn btn-info" value="upgrade-confirmed"><i class="glyphicon glyphicon-cog"></i> Upgrade Me</button>&nbsp;<a class="btn btn-danger" href="updater.php"><i class="glyphicon glyphicon-remove-sign"></i> Cancel</a></p></div>';
    } else {
      echo "<pre>";
      unlinkRecursive("/tmp/sexiauditor-update/");
      $messageOutput = "Starting update process on " . (new DateTime())->format('Y-m-d H:i:s') . "\n";
      $tempMessageOutput = shell_exec("/usr/bin/unzip \"".$dir.$_POST['input-file']."\" -d /tmp/sexiauditor-update/ 2>&1");
      if (file_exists($xmlPath)) {
        $domXML = new DomDocument();
        $domXML->load($xmlPath);
        $listeCommands = $domXML->getElementsByTagName('command');
        $SexiAuditorNewVersion = $domXML->getElementsByTagName("version")->item(0)->nodeValue;
        $messageOutput .= "Updating from version " . trim($SexiAuditorVersion) . " to version $SexiAuditorNewVersion\n";
        $messageOutput .= "Unpacking SexiAuditor Update Package in /tmp/sexiauditor-update/\n";
        $messageOutput .= $tempMessageOutput;
        $errorInCommand = false;
        foreach($listeCommands as $command2Run){
          $outputCommand = [];
          $returnError = "";
          $command2sudo = $command2Run->firstChild->nodeValue;
          exec("sudo $command2sudo", $outputCommand, $returnError);
          if ($returnError) {
            $messageOutput .= "[ERROR] Command run with errors: $command2sudo\n";
            $errorInCommand = true;
          } else {
            $messageOutput .= "[INFO] Command run successfully: $command2sudo\n";
          }
          $messageOutput .= implode("\n", $outputCommand) . "\n";
        }
      } else {
        $messageOutput .= "!!! Missing mandatory file. Please check package integrity.\n";
      }
      $messageOutput .= "Purging temporary folder /tmp/sexiauditor-update/";
      unlinkRecursive("/tmp/sexiauditor-update/");
      echo $messageOutput;
      echo "</pre>";
      $updateLog = fopen("update.log", "w");
      fwrite($updateLog, $messageOutput);
      fclose($updateLog);
      if ($errorInCommand) {
        echo ' <div class="alert alert-danger" role="danger"><h4><span class="glyphicon glyphicon-alert" aria-hidden="true"></span><span class="sr-only">Error:</span>There was some errors during the update process!</h4>Some errors occured during update of your SexiAuditor appliance. This shouldn\'t happen, but don\'t worry, we are here to help you!<br />If you want, you can take a look above to the report that can point you to the right direction, or you can send it to us at check &lt;at&gt; sexiauditor.fr<p>You can use the following button to send us an email (and you can attach the log available <a href="update.log" target="_blank">here</a> for debug purpose), we\'ll look into it and get back to you.</p><form class="form" action="" method="post"><p><a class="btn btn-danger" href="mailto:check@sexiauditor.fr?subject=Error during upgrade&body=Please Find attached update error log."><i class="glyphicon glyphicon-remove-sign"></i> Send Support Mail</a></p></div>';
      } else {
        echo ' <div class="alert alert-success" role="success"><h4><span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span><span class="sr-only">Success:</span>Update completed successfully!</h4><p>The update of your SexiAuditor appliance completed successfully, you are now using version ' . $SexiAuditorNewVersion . '!</p><p><a class="btn btn-success" href="index.php"><i class="glyphicon glyphicon-home"></i> Go Home</a></p></div>';
      }
    }
  }
?>
  </div>
</body>
</html>
