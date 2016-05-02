<?php
#########################
# VARIABLE EDITION ZONE #
#########################
$achievementFile = "/var/www/admin/achievements.txt";
$credstoreFile = "/var/www/.vmware/credstore/vicredentials.xml";
$xmlSettingsFile = "/var/www/admin/conf/settings.xml";
$xmlModulesFile = "/var/www/admin/conf/modules.xml";
$xmlModuleSchedulesFile = "/var/www/admin/conf/moduleschedules.xml";
$xmlConfigsFile = "/var/www/admin/conf/configs.xml";
$xmlStartPath = "/opt/vcron/data/";
$powerChoice = array("static" => "High performance", "dynamic" => "Balanced", "low" => "Low power", "custom" => "Custom", "off" => "Not supported (BIOS config)");
$servicePolicyChoice = array("off" => "Start and stop manually", "on" => "Start and stop with host", "automatic" => "Start and stop automatically");
$alarmStatus = array("unknown" => '<i class="glyphicon glyphicon-question-sign"></i>', "green" => '<i class="glyphicon glyphicon-ok-sign alarm-green"></i>', "yellow" => '<i class="glyphicon glyphicon-exclamation-sign alarm-yellow"></i>', "red" => '<i class="glyphicon glyphicon-remove-sign alarm-red"></i>');
$userAgent = array("Perl" => '<img src="images/logo-perl.png" title="VI Perl" />', 'Client' => '<img src="images/logo-viclient.png" title="VMware VI Client" />', 'Mozilla' => '<img src="images/logo-chrome.png" title="Browser" />', 'java' => '<img src="images/logo-java.png" title="VMware vim-java" />', "PowerCLI" => '<img src="images/logo-powercli.png" title="PowerCLI" />');
#############################
# VARIABLE EDITION END ZONE #
#############################

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
  private $h_configs = array();
  private $h_modules = array();
  private $h_moduleschedules = array();
  private $powerChoice;
  private $servicePolicyChoice;
  private $alarmStatus;
  private $header;
  private $body;
  private $footer;
  private $achievementFile;
  private $xmlConfigsFile;
  private $xmlModulesFile;
  private $xmlModuleSchedulesFile;
  private $selectedDate;
  private $xmlSelectedPath;
  private $scannedDirectories;
  private $xmlStartPath;

  public function __construct() {
    global $achievementFile;
    global $xmlConfigsFile;
    global $xmlModulesFile;
    global $xmlModuleSchedulesFile;
    $this->achievementFile = $achievementFile;
    $this->xmlConfigsFile = $xmlConfigsFile;
    $this->xmlModulesFile = $xmlModulesFile;
    $this->xmlModuleSchedulesFile = $xmlModuleSchedulesFile;

    if (is_readable($this->xmlConfigsFile)) {
      $xmlConfigs = simplexml_load_file($this->xmlConfigsFile);
      # hash table initialization with settings XML file
      foreach ($xmlConfigs->xpath('/configs/config') as $config) {
        $this->h_configs[(string) $config->id] = (string) $config->value;
      }
    } else {
      throw new Exception('File ' . $this->xmlConfigsFile . ' is not existant or not readable');
    }

    if (is_readable($this->xmlModulesFile)) {
      $xmlModules = simplexml_load_file($this->xmlModulesFile);
      # hash table initialization with settings XML file
      foreach ($xmlModules->xpath('/modules/module') as $module) {
        $this->h_modules[(string) $module->id] = (string) $module->value;
      }
    } else {
      throw new Exception('File ' . $this->xmlModulesFile . ' is not existant or not readable');
    }

    if (is_readable($this->xmlModuleSchedulesFile)) {
      $xmlModuleSchedules = simplexml_load_file($this->xmlModuleSchedulesFile);
      # hash table initialization with settings XML file
      foreach ($xmlModuleSchedules->xpath('/modules/module') as $module) {
        $this->h_moduleschedules[(string) $module->id] = (string) $module->schedule;
      }
    } else {
      throw new Exception('File ' . $this->xmlModuleSchedulesFile . ' is not existant or not readable');
    }

    global $powerChoice;
    global $alarmStatus;
    global $servicePolicyChoice;
    $this->powerChoice = $powerChoice;
    $this->alarmStatus = $alarmStatus;
    $this->servicePolicyChoice = $servicePolicyChoice;

    global $xmlStartPath;
    $this->xmlStartPath = $xmlStartPath;
    $this->scannedDirectories = array_values(array_diff(scandir($xmlStartPath, SCANDIR_SORT_DESCENDING), array('..', '.', 'latest')));
    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
      $this->selectedDate = $_POST["selectedDate"];
      foreach ($this->scannedDirectories as $key => $value) {
        if (strpos($value, str_replace("/","",$this->selectedDate)) === 0) {
          $this->xmlSelectedPath = $value;
          break;
        }
      }
    } else {
      $this->selectedDate = DateTime::createFromFormat('Ymd', $this->scannedDirectories[0])->format('Y/m/d');
      $this->xmlSelectedPath = "latest";
    }
    # Header generation
    $this->displayHeader($_SERVER['SCRIPT_NAME']);
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
      'mismatchProperty' => null,
    ];
    extract($args);
    $this->xmlFile = $this->xmlStartPath.$this->xmlSelectedPath.'/'.$xmlFile;
    $this->xpathQuery = $xpathQuery;
    $this->title = $title;
    $this->description = $description;
    $this->thead = $thead;
    $this->tbody = $tbody;
    $this->order = $order;
    $this->columnDefs = $columnDefs;
    $this->typeCheck = $typeCheck;
    $this->majorityProperty = $majorityProperty;
    $this->mismatchProperty = $mismatchProperty;
    $this->header = "";
    $this->body = "";
    $this->footer = "";

    if (is_readable($this->xmlFile)) {
      $xmlContent = simplexml_load_file($this->xmlFile);
    	$xpathFull = $xmlContent->xpath($this->xpathQuery);
    	if (count($xpathFull) > 0) {
        $this->header .= '    <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> ' . $this->title . '</h2>'."\n";
        $this->header .= '    <div class="alert alert-warning" role="alert"><i>' . $this->description . '</i></div>'."\n";
        $this->header .= '    <div class="col-lg-12">'."\n";
        $this->header .= '      <table id="' . preg_replace('/\s+/', '', strtolower($this->title)) . '" class="table table-hover">'."\n";
        $this->header .= '        <thead><tr>'."\n";
        foreach ($this->thead as $thead) {
          $this->header .= '          <th>' . $thead . '</th>'."\n";
        }
        $this->header .= '        </tr></thead>'."\n";
        $this->header .= '        <tbody>'."\n";
        $entries = 0;
        switch ($this->typeCheck) {
    			case 'majorityPerCluster':
          	foreach (array_diff(array_count_values(array_map("strval", $xmlContent->xpath("/hosts/host/vcenter"))), array("1")) as  $key_vcenter => $value_vcenter) {
          		foreach (array_diff(array_count_values(array_map("strval", $xmlContent->xpath("/hosts/host[vcenter='".$key_vcenter."']/cluster"))), array("1")) as  $key_cluster => $value_cluster) {
          			if ($key_cluster == 'Standalone') { continue; }
          			$majorityGroup = array_diff(array_count_values(array_map("strval", $xmlContent->xpath("/hosts/host[vcenter='".$key_vcenter."' and cluster='".$key_cluster."']/".$this->majorityProperty))), array("0"));
          			if (count($majorityGroup) < 1) {
                  $majorityGroup = '';
                } else {
                  arsort($majorityGroup);
                  $majorityGroup = array_keys($majorityGroup)[0];
                }
          			foreach ($xmlContent->xpath("/hosts/host[vcenter='".$key_vcenter."' and cluster='".$key_cluster."' and ".$this->majorityProperty."!='".$majorityGroup."']") as $entry) {
                  $this->body .= '          <tr>';
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
              $this->body = '    <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> ' . $this->title . ' <small>' . str_replace(array("\n", "\t", "\r"), '', (rand_line($this->achievementFile))) . '</small></h2>'."\n";
            }
            break;
          case 'mismatchPerCluster':
          	foreach (array_diff(array_count_values(array_map("strval", $xmlContent->xpath("/hosts/host/vcenter"))), array("1")) as  $key_vcenter => $value_vcenter) {
          		foreach (array_diff(array_count_values(array_map("strval", $xmlContent->xpath("/hosts/host[vcenter='".$key_vcenter."']/cluster"))), array("1")) as  $key_cluster => $value_cluster) {
          			if ($key_cluster == 'Standalone') { continue; }
          			$mismatchMatches = array_diff(array_count_values(array_map("strval", $xmlContent->xpath("/hosts/host[vcenter='".$key_vcenter."' and cluster='".$key_cluster."']/".$this->mismatchProperty))), array("1"));
                if (count($mismatchMatches) > 1) {
          				$compliance = '<i class="glyphicon glyphicon-remove-sign text-danger"></i>';
            			$mismatchEntry = "*Mismatch* ";
          			} else {
          				$compliance = '<i class="glyphicon glyphicon-ok-sign text-success"></i>';
            			$mismatchEntry = "";
          			}
          			foreach (array_diff(array_count_values(array_map("strval", $xmlContent->xpath("/hosts/host[vcenter='".$key_vcenter."' and cluster='".$key_cluster."']/".$this->mismatchProperty))), array("1")) as  $key_entry => $value_entry) {
          				$mismatchEntry .= " $key_entry";
          			}
                $this->body .= '          <tr>';
                foreach ($this->tbody as $column) {
                  $entries++;
                  $this->body .= eval("return $column;");
                }
                $this->body .= '</tr>'."\n";
          		}
        		}
            break;
          default:
            foreach ($xpathFull as $entry) {
              $entries++;
              $this->body .= '          <tr>';
              foreach ($this->tbody as $column) {
                $this->body .= eval("return $column;");
              }
              $this->body .= '</tr>'."\n";
            }
        }
        if ($entries > 0) {
          $this->footer .= '        </tbody>
      </table>
    </div>
    <script type="text/javascript">
    $(document).ready( function () {
      $("#' . preg_replace('/\s+/', '', strtolower($this->title)) . '").DataTable( {
        "search": {
          "smart": false,
          "regex": true
        },'."\n";
          if (!is_null($this->order)) { $this->footer .= '        "order": [' . $this->order . '],'."\n"; }
          if (!is_null($this->columnDefs)) { $this->footer .= '        "columnDefs": [' . $this->columnDefs . '],'."\n"; }
          $this->footer .= '      } );
    } );
    </script>
    <hr class="divider-dashed" />'."\n";
        }
      } elseif ($this->h_configs['showEmpty'] == 'enable') {
        $this->body = '    <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> ' . $this->title . ' <small>' . str_replace(array("\n", "\t", "\r"), '', (rand_line($this->achievementFile))) . '</small></h2>'."\n";
      }
    } else {
      $this->body .= '    <div class="alert alert-danger" role="alert">
      <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
      <span class="sr-only">Error:</span>
      File ' . $this->xmlFile . ' is not existant or not readable. Please check for <a href="moduleselector.php">module selection</a> and/or wait for scheduler
    </div>'."\n";
    }
    echo $this->header;
    echo $this->body;
    echo $this->footer;
  }

  public function displayHeader($formPage) {
    global $title;
    $this->header = "";
    $this->header .= '      <div style="padding-top: 10px; padding-bottom: 10px;" class="container">
        <div class="row">
          <div class="col-lg-10 alert alert-info" style="margin-top: 20px; text-align: center;">
            <h1 style="margin-top: 10px;">'.$title.' on ' . DateTime::createFromFormat('Y/m/d', $this->selectedDate)->format('l jS F Y') . '</h1>
          </div>
          <div class="alert col-lg-2">
            <form action="' . $formPage . '" style="margin-top: 5px;" method="post">
              <div class="form-group" style="margin-bottom: 5px;">
                <div class="input-group date" id="datetimepicker11">
                  <input type="text" class="form-control" name="selectedDate" readonly />
                  <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                </div>
              </div>
              <button type="submit" class="btn btn-default" style="width: 100%">Select this date</button>
              <script type="text/javascript">'."\n";
    $this->header .= "              $(function () {
                $('#datetimepicker11').datetimepicker({
                  ignoreReadonly: true,
                  format: 'YYYY/MM/DD',
                  showTodayButton: true,
                  defaultDate: \"" . $this->selectedDate . "\",
                  enabledDates: [\n";
    foreach ($this->scannedDirectories as $xmlDirectory) {
      $this->header .= '                "' . DateTime::createFromFormat('Ymd', $xmlDirectory)->format('Y/m/d H:i') . '",' . "\n";
    }
    $this->header .= '                  ]
                });
              });
              </script>
            </form>
          </div>
        </div>'."\n";
    echo $this->header;
  }

  public function getModuleSchedule($module) {
    return $this->h_moduleschedules[$module];
  }

  public function getConfig($config) {
    if (array_key_exists($config, $this->h_configs)) {
      return $this->h_configs[$config];
    } else {
      return "undefined";
    }
  }

  public function getUserAgent($useragentPattern) {
    global $userAgent;
    if ($useragentPattern == 'VI Perl') {
      return $userAgent['Perl'];
    } elseif (preg_match("/^VMware VI Client/", $useragentPattern)) {
      return $userAgent['Client'];
    } elseif (preg_match("/^Mozilla/", $useragentPattern)) {
      return $userAgent['Mozilla'];
    } elseif (preg_match("/^VMware vim-java/", $useragentPattern)) {
      return $userAgent['java'];
    } elseif (preg_match("/^PowerCLI/", $useragentPattern)) {
      return $userAgent['PowerCLI'];
    } else {
      return "undefined";
    }
  }

  public function getSumValue() {
    # code...
  }
}

?>
