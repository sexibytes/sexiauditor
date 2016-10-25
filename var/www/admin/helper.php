<?php
#########################
# VARIABLE EDITION ZONE #
#########################
$achievementFile = "/var/www/admin/achievements.txt";
$credstoreFile = "/var/www/.vmware/credstore/vicredentials.xml";
$powerChoice = array("static" => "High performance", "dynamic" => "Balanced", "low" => "Low power", "custom" => "Custom", "off" => "Not supported (BIOS config)");
$servicePolicyChoice = array("off" => "Start and stop manually", "on" => "Start and stop with host", "automatic" => "Start and stop automatically");
$langChoice = array("en" => "English");
$alarmStatus = array("unknown" => '<i class="glyphicon glyphicon-question-sign"></i>', "green" => '<i class="glyphicon glyphicon-ok-sign alarm-green"></i>', "yellow" => '<i class="glyphicon glyphicon-exclamation-sign alarm-yellow"></i>', "red" => "<i class='glyphicon glyphicon-remove-sign alarm-red'></i>");
$enableStatus = array("0" => '<i class="glyphicon glyphicon-remove-sign alarm-red"></i>', "1" => '<i class="glyphicon glyphicon-ok-sign alarm-green"></i>', "yellow" => '<i class="glyphicon glyphicon-exclamation-sign alarm-yellow"></i>', "red" => "<i class='glyphicon glyphicon-remove-sign alarm-red'></i>");
$userAgent = array("Perl" => '<img src="images/logo-perl.png" title="VI Perl" />', 'Client' => '<img src="images/logo-viclient.png" title="VMware Client" />', 'Mozilla' => '<img src="images/logo-chrome.png" title="Browser" />', 'java' => '<img src="images/logo-java.png" title="VMware vim-java" />', "PowerCLI" => '<img src="images/logo-powercli.png" title="PowerCLI" />');
#############################
# VARIABLE EDITION END ZONE #
#############################

function isHttpAvailable($domain) {
  //initialize curl
  $curlInit = curl_init($domain);
  curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,5);
  curl_setopt($curlInit,CURLOPT_HEADER,true);
  curl_setopt($curlInit,CURLOPT_NOBODY,true);
  curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);

  //get answer
  $response = curl_exec($curlInit);
  curl_close($curlInit);
  if ($response) return true;
  return false;
}

function human_filesize($size, $precision = 2, $unity = 'B') {
  for($i = 0; ($size / 1024) > 0.9; $i++, $size /= 1024) {}
  return round($size, $precision).' '.[$unity,"k$unity","M$unity","G$unity","T$unity","P$unity","E$unity","Z$unity","Y$unity"][$i];
}

function unlinkRecursive($dir) {
  if(!$dh = @opendir($dir))
    return;
  while (false !== ($obj = readdir($dh))) {
    if($obj == '.' || $obj == '..')
      continue;
    if (!@unlink($dir . '/' . $obj))
      unlinkRecursive($dir.'/'.$obj);
  }
  closedir($dh);
  @rmdir($dir);
  return;
}

function rcopy($src, $dest){
  if(!is_dir($src)) return false;
  if(!is_dir($dest))
    if(!mkdir($dest))
      return false;
  $i = new DirectoryIterator($src);
  foreach($i as $f) {
     if($f->isFile())
      copy($f->getRealPath(), "$dest/" . $f->getFilename());
    else if(!$f->isDot() && $f->isDir())
       rcopy($f->getRealPath(), "$dest/$f");
  }
}

function rand_line($fileName, $maxLineLength = 4096) {
  $handle = @fopen($fileName, "r");
  if ($handle) {
    $random_line = null;
    $line = null;
    $count = 0;
    while (($line = fgets($handle, $maxLineLength)) !== false) {
      $count++;
      // P(1/$count) probability of picking current line as random line
      if(rand() % $count == 0) {
        $random_line = $line;
      }
    }
    if (!feof($handle)) {
      echo "Error: unexpected fgets() fail\n";
      fclose($handle);
      return null;
    } else {
      fclose($handle);
    }
    return $random_line;
  }
}

function addOrdinalNumberSuffix($num) {
  if (!in_array(($num % 100),array(11,12,13))){
  switch ($num % 10) {
    // Handle 1st, 2nd, 3rd
    case 1:  return $num.'st';
    case 2:  return $num.'nd';
    case 3:  return $num.'rd';
  }
  }
  return $num.'th';
}

function secureInput($data) { return htmlspecialchars(stripslashes(trim($data))); }

function sendMailNewUser($username, $displayname, $password, $role, $access) {
  $bodyTextContent    = 'A new user have been created with your email to access audit platform on ' . gethostname() . '<br/>Please find information below:<br />';
  $bodyTextContent   .= 'Username: ' . $username . '<br />';
  $bodyTextContent   .= 'Password: ' . $password . '<br />';
  $bodyTextContent   .= 'Display Name: ' . $displayname . '<br />';
  $bodyTextContent   .= 'Role: ' . $role . '<br />';
  $bodyTextContent   .= 'Access: '. $access . '<br />';
  $bodyTextContent   .= '<br /><strong>Please, for the sake of Unicorn, don\'t forget to change your password once connected<strong><br />';
  $bodyContent = preg_replace('/\%SEXIAUDITOR_TEXT\%/s', $bodyTextContent, file_get_contents('mail-template/newuser.html'));

  return $bodyContent;
}

# class loading
require_once('class/SexiHelper.class.php');
require_once('class/SexiCheck.class.php');
require_once('class/SexiLang.class.php');

?>
