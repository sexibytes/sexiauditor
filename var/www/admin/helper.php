<?php

$achievementFile = "/var/www/admin/achievements.txt";
$credstoreFile = "/var/www/.vmware/credstore/vicredentials.xml";
$xmlStartPath = "/opt/vcron/data/";
$powerChoice = array("static" => "High performance", "dynamic" => "Balanced", "low" => "Low power", "custom" => "Custom", "off" => "Not supported (BIOS config)");
$servicePolicyChoice = array("off" => "Start and stop manually", "on" => "Start and stop with host", "automatic" => "Start and stop automatically");
$alarmStatus = array("unknown" => '<i class="glyphicon glyphicon-question-sign"></i>', "green" => '<i class="glyphicon glyphicon-ok-sign alarm-green"></i>', "yellow" => '<i class="glyphicon glyphicon-exclamation-sign alarm-yellow"></i>', "red" => '<i class="glyphicon glyphicon-remove-sign alarm-red"></i>');

function humanFileSize($size,$unit="") {
        if( (!$unit && $size >= 1<<30) || $unit == "GB")
                return number_format($size/(1<<30),2)."GB";
        if( (!$unit && $size >= 1<<20) || $unit == "MB")
                return number_format($size/(1<<20),2)."MB";
        if( (!$unit && $size >= 1<<10) || $unit == "KB")
                return number_format($size/(1<<10),2)."KB";
        return number_format($size)." bytes";
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

function php_file_tree_dir($directory, $first_call = true) {
	$file = scandir($directory);
	natcasesort($file);
	$files = $dirs = array();
	foreach($file as $this_file) {
		if( is_dir("$directory/$this_file" ) ) $dirs[] = $this_file; else $files[] = $this_file;
	}
	$file = array_merge($dirs, $files);

	if( count($file) > 2 ) {
		$php_file_tree = "<ul";
		if( $first_call ) { $php_file_tree .= " class=\"php-file-tree\""; $first_call = false; }
		$php_file_tree .= ">";
		foreach( $file as $this_file ) {
			if( $this_file != "." && $this_file != ".." ) {
				if( is_dir("$directory/$this_file") ) {
					$php_file_tree .= "<li class=\"pft-directory\"><input type=\"checkbox\" name=\"pathChecked[]\" value=\"$directory/$this_file\"> <a href=\"#\"><strong>" . htmlspecialchars($this_file) . "</strong></a>";
					$php_file_tree .= php_file_tree_dir("$directory/$this_file" , false);
					$php_file_tree .= "</li>";
				} else {
					$php_file_tree .= "<li class=\"pft-file\"><input type=\"checkbox\" name=\"pathChecked[]\" value=\"$directory/$this_file\"> <strong>" . htmlspecialchars($this_file) . "</strong> (". humanFileSize(filesize("$directory/$this_file")) . ")</li>";
				}
			}
		}
		$php_file_tree .= "</ul>";
	}
	return $php_file_tree;
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

function sendMailNewUser($username, $displayname, $role, $acess) {
  $bodyTextContent    = 'A new user have been created with your email to access audit platform on ' . gethostname() . '<br/>Please find information below:<br />';
  $bodyTextContent   .= 'Username: ' . $username . '<br />';
  $bodyTextContent   .= 'Display Name: ' . $displayname . '<br />';
  $bodyTextContent   .= 'Role: ' . $role . '<br />';
  $bodyTextContent   .= 'Access: '. $acess . '<br />';
  $bodyContent = preg_replace('/\%SEXIAUDITOR_TEXT\%/s', $bodyTextContent, file_get_contents('mail-template/newuser.html'));

  return $bodyContent;
}

class SexiCheck {
  private $checkType = "";
  private $xmlFile;
  private $xpathQuery;
  private $title;
  private $description;
  private $thead = array();
  private $tbody = array();
  private $order;
  private $columnDefs;
  private $h_modulesettings = array();
  private $powerChoice;
  private $servicePolicyChoice;
  private $alarmStatus;
  private $header;
  private $body;
  private $footer;
  // private $h_settings = array();
  private $achievementFile = "/var/www/admin/achievements.txt";
  private $xmlSettingsFile = "/var/www/admin/conf/modulesettings.xml";

  public function __construct() {
    if (is_readable($this->xmlSettingsFile)) {
      $xmlSettings = simplexml_load_file($this->xmlSettingsFile);
      # hash table initialization with settings XML file
      foreach ($xmlSettings->xpath('/settings/setting') as $setting) {
        $this->h_modulesettings[(string) $setting->id] = (string) $setting->value;
      }

      # hash table initialization with settings XML file
      // foreach ($xmlSettings->xpath('/modules/module') as $module) {
      //   $this->h_settings[(string) $module->id] = (string) $module->schedule;
      // }
      global $powerChoice;
      global $alarmStatus;
      global $servicePolicyChoice;
      $this->powerChoice = $powerChoice;
      $this->alarmStatus = $alarmStatus;
      $this->servicePolicyChoice = $servicePolicyChoice;
    }
  }

  public function displayCheck($args) {
    $args += [
      'xmlFile' => null,
      'xpathQuery' => null,
      'title' => null,
      'description' => null,
      'thead' => array(),
      'tbody' => array(),
      'order' => null,
      'columnDefs' => null,
      'typeCheck' => null,
      'majorityProperty' => null,
    ];
    extract($args);
    $this->xmlFile = $xmlFile;
    $this->xpathQuery = $xpathQuery;
    $this->title = $title;
    $this->description = $description;
    $this->thead = $thead;
    $this->tbody = $tbody;
    $this->order = $order;
    $this->columnDefs = $columnDefs;
    $this->typeCheck = $typeCheck;
    $this->majorityProperty = $majorityProperty;
    $this->header = "";
    $this->body = "";
    $this->footer = "";

    if (is_readable($this->xmlFile)) {
      $xmlContent = simplexml_load_file($this->xmlFile);
    	$xpathFull = $xmlContent->xpath($this->xpathQuery);
    	if (count($xpathFull) > 0) {
        $this->header .= '              <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> ' . $this->title . '</h2>'."\n";
        $this->header .= '              <div class="alert alert-warning" role="alert"><i>' . $this->description . '</i></div>'."\n";
        $this->header .= '              <div class="col-lg-12">'."\n";
        $this->header .= '                <table id="' . preg_replace('/\s+/', '', strtolower($this->title)) . '" class="table table-hover">'."\n";
        $this->header .= '                <thead><tr>'."\n";
        foreach ($this->thead as $thead) {
          $this->header .= '                    <th>' . $thead . '</th>'."\n";
        }
        $this->header .= '                  </thead>'."\n";
        $this->header .= '                <tbody>'."\n";
        $entries = 0;
        switch ($this->typeCheck) {
    			case 'majorityPerCluster':
          	foreach (array_diff(array_count_values(array_map("strval", $xmlContent->xpath("/hosts/host/vcenter"))), array("1")) as  $key_vcenter => $value_vcenter) {
          		foreach (array_diff(array_count_values(array_map("strval", $xmlContent->xpath("/hosts/host[vcenter='".$key_vcenter."']/cluster"))), array("1")) as  $key_cluster => $value_cluster) {
          			if ($key_cluster == 'Standalone') { continue; }
          			$majorityGroup = array_diff(array_count_values(array_map("strval", $xmlContent->xpath("/hosts/host[vcenter='".$key_vcenter."' and cluster='".$key_cluster."']/".$this->majorityProperty))), array("1"));
          			if (count($majorityGroup) < 1) {
                  $majorityGroup = '';
                } else {
                  arsort($majorityGroup);
                  $majorityGroup = array_keys($majorityGroup)[0];
                }
          			foreach ($xmlContent->xpath("/hosts/host[vcenter='".$key_vcenter."' and cluster='".$key_cluster."' and ".$this->majorityProperty."!='".$majorityGroup."']") as $entry) {
                  $this->body .= '                    <tr>';
                  foreach ($this->tbody as $column) {
                    $entries++;
                    $this->body .= eval("return $column;");
                  }
                  $this->body .= '</tr>'."\n";
          			}
              }
        		}
            if ($entries == 0) {
              $this->header = '';
              $this->body = '              <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> ' . $this->title . ' <small>' . rand_line($this->achievementFile) . '</small></h2>';
            }
            break;
          default:
            foreach ($xpathFull as $entry) {
              $entries++;
              $this->body .= '                    <tr>';
              foreach ($this->tbody as $column) {
                $this->body .= eval("return $column;");
              }
              $this->body .= '</tr>'."\n";
            }
        }
        if ($entries > 0) {
          $this->footer .= '                  </tbody>
            </table>
            </div>
            <script type="text/javascript">
            $(document).ready( function () {
                $("#' . preg_replace('/\s+/', '', strtolower($this->title)) . '").DataTable( {
                    "search": {
                        "smart": false,
                        "regex": true
                    },';
          if (!is_null($this->order)) { $this->footer .= '          "order": [' . $this->order . '],'; }
          if (!is_null($this->columnDefs)) { $this->footer .= '          "columnDefs": [' . $this->columnDefs . '],'; }
          $this->footer .= '
                } );
             } );
            </script>
            <hr class="divider-dashed" />'."\n";
        }
      } elseif ($this->h_modulesettings['showEmpty'] == 'enable') {
        $this->body .= '              <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> ' . $this->title . ' <small>' . rand_line($this->achievementFile) . '</small></h2>';
      }
    } else {
      $this->body .= '          <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Error:</span> File <?php echo $xmlLicenseFile; ?> is not existant or not readable. Please check for <a href="/admin/sandbox.php">module selection</a> and/or wait for scheduler</div>';
    }
    echo $this->header;
    echo $this->body;
    echo $this->footer;
  }
}


?>
