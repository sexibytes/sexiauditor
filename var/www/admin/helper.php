<?php
#########################
# VARIABLE EDITION ZONE #
#########################
$achievementFile = "/var/www/admin/achievements.txt";
$credstoreFile = "/var/www/.vmware/credstore/vicredentials.xml";
// $xmlSettingsFile = "/var/www/admin/conf/settings.xml";
// $xmlModulesFile = "/var/www/admin/conf/modules.xml";
// $xmlModuleSchedulesFile = "/var/www/admin/conf/moduleschedules.xml";
// $xmlConfigsFile = "/var/www/admin/conf/configs.xml";
// $xmlTTBFile = "/opt/vcron/scheduler-ttb.xml";
// $xmlStartPath = "/opt/vcron/data/";
$powerChoice = array("static" => "High performance", "dynamic" => "Balanced", "low" => "Low power", "custom" => "Custom", "off" => "Not supported (BIOS config)");
$servicePolicyChoice = array("off" => "Start and stop manually", "on" => "Start and stop with host", "automatic" => "Start and stop automatically");
$langChoice = array("en" => "English");
$alarmStatus = array("unknown" => '<i class="glyphicon glyphicon-question-sign"></i>', "green" => '<i class="glyphicon glyphicon-ok-sign alarm-green"></i>', "yellow" => '<i class="glyphicon glyphicon-exclamation-sign alarm-yellow"></i>', "red" => '<i class="glyphicon glyphicon-remove-sign alarm-red"></i>');
$userAgent = array("Perl" => '<img src="images/logo-perl.png" title="VI Perl" />', 'Client' => '<img src="images/logo-viclient.png" title="VMware Client" />', 'Mozilla' => '<img src="images/logo-chrome.png" title="Browser" />', 'java' => '<img src="images/logo-java.png" title="VMware vim-java" />', "PowerCLI" => '<img src="images/logo-powercli.png" title="PowerCLI" />');
#############################
# VARIABLE EDITION END ZONE #
#############################

// function secondsToTime($inputSeconds) {
//   // var_dump($inputSeconds);
//   if ($inputSeconds != "Unreachable") {
//     // $then = new DateTime(date('Y-m-d H:i:s', time() + $inputSeconds));
//     // $now = new DateTime(date('Y-m-d H:i:s', time()));
//     // $diff = $then->diff($now);
//     // return ((($diff->y > 0) ? $diff->y . 'y ' : '') . (($diff->m > 0) ? $diff->m . 'm ' : '') . $diff->d . 'd');
//     return round($inputSeconds/86400);
//   } else {
//     return 0;
//   }
// }

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

// class SexiCheckXML {
//   private $checkType = "";
//   private $xmlFile;
//   private $xpathQuery;
//   private $title;
//   private $description;
//   private $thead = array();
//   private $tbody = array();
//   private $order;
//   private $columnDefs;
//   private $h_configs = array();
//   private $h_modules = array();
//   private $h_moduleschedules = array();
//   private $powerChoice;
//   private $servicePolicyChoice;
//   private $alarmStatus;
//   private $header;
//   private $body;
//   private $footer;
//   private $graph;
//   private $achievementFile;
//   private $xmlConfigsFile;
//   private $xmlModulesFile;
//   private $xmlModuleSchedulesFile;
//   private $selectedDate;
//   private $xmlSelectedPath;
//   private $scannedDirectories;
//   private $xmlStartPath;
//   private $lang;
//   private $langDef;
//
//   public function __construct() {
//     global $achievementFile;
//     global $xmlConfigsFile;
//     global $xmlModulesFile;
//     global $xmlModuleSchedulesFile;
//     $this->achievementFile = $achievementFile;
//     $this->xmlConfigsFile = $xmlConfigsFile;
//     $this->xmlModulesFile = $xmlModulesFile;
//     $this->xmlModuleSchedulesFile = $xmlModuleSchedulesFile;
//
//     if (is_readable($this->xmlConfigsFile)) {
//       $xmlConfigs = simplexml_load_file($this->xmlConfigsFile);
//       # hash table initialization with settings XML file
//       foreach ($xmlConfigs->xpath('/configs/config') as $config) {
//         $this->h_configs[(string) $config->id] = (string) $config->value;
//       }
//     } else {
//       throw new Exception('File ' . $this->xmlConfigsFile . ' is not existant or not readable');
//     }
//
//     if (is_readable($this->xmlModulesFile)) {
//       $xmlModules = simplexml_load_file($this->xmlModulesFile);
//       # hash table initialization with settings XML file
//       foreach ($xmlModules->xpath('/modules/module') as $module) {
//         $this->h_modules[(string) $module->id] = (string) $module->value;
//       }
//     } else {
//       throw new Exception('File ' . $this->xmlModulesFile . ' is not existant or not readable');
//     }
//
//     if (is_readable($this->xmlModuleSchedulesFile)) {
//       $xmlModuleSchedules = simplexml_load_file($this->xmlModuleSchedulesFile);
//       # hash table initialization with settings XML file
//       foreach ($xmlModuleSchedules->xpath('/modules/module') as $module) {
//         $this->h_moduleschedules[(string) $module->id] = (string) $module->schedule;
//       }
//     } else {
//       throw new Exception('File ' . $this->xmlModuleSchedulesFile . ' is not existant or not readable');
//     }
//
//     $this->lang = (defined($this->h_configs['lang'])) ? $this->h_configs['lang'] : 'en';
//     switch ($this->lang) {
//       case 'en':
//         $lang_file = 'lang.en.php';
//         break;
//       case 'fr':
//         $lang_file = 'lang.fr.php';
//         break;
//       default:
//       $lang_file = 'lang.en.php';
//     }
//
//     require("dbconnection.php");
//     include_once 'locales/'.$lang_file;
//     $this->langDef = $lang;
//
//     global $powerChoice;
//     global $alarmStatus;
//     global $servicePolicyChoice;
//     $this->powerChoice = $powerChoice;
//     $this->alarmStatus = $alarmStatus;
//     $this->servicePolicyChoice = $servicePolicyChoice;
//   }
//
//   public function displayCheck($args) {
//     $args += [
//       'xmlFile' => null,
//       'xpathQuery' => null,
//       // 'title' => null,
//       // 'description' => null,
//       'thead' => array(),
//       'tbody' => array(),
//       'order' => null,
//       'columnDefs' => null,
//       'typeCheck' => null,
//       'majorityProperty' => null,
//       'mismatchProperty' => null,
//       'pivotProperty' => null,
//       'id' => null,
//     ];
//     extract($args);
//     $this->xmlFile = $this->xmlStartPath.$this->xmlSelectedPath.'/'.$xmlFile;
//     $this->xpathQuery = $xpathQuery;
//     // $this->title = $title;
//     // $this->description = $description;
//     $this->thead = $thead;
//     $this->tbody = $tbody;
//     $this->order = $order;
//     $this->columnDefs = $columnDefs;
//     $this->typeCheck = $typeCheck;
//     $this->majorityProperty = $majorityProperty;
//     $this->mismatchProperty = $mismatchProperty;
//     $this->pivotProperty = $pivotProperty;
//     $this->id = $id;
//     $this->header = "";
//     $this->body = "";
//     $this->footer = "";
//     $this->graph = "";
//
//     if (is_readable($this->xmlFile)) {
//       // global $lang;
//       $xmlContent = simplexml_load_file($this->xmlFile);
//       $xpathFull = $xmlContent->xpath($this->xpathQuery);
//       if (count($xpathFull) > 0) {
//         $this->header .= '    <h2 class="text-danger anchor" id="' . $this->id . '"><i class="glyphicon glyphicon-exclamation-sign"></i> ' . $this->langDef[$this->id]["title"] . '</h2>'."\n";
//         $this->header .= '    <div class="alert alert-warning" role="alert"><i>' . $this->langDef[$this->id]["description"] . '</i></div>'."\n";
//         if ($this->typeCheck == "pivotTableGraphed") {
//           $this->header .= '    <div class="row">'."\n";
//           $this->header .= '    <div class="col-lg-4">'."\n";
//         } else {
//           $this->header .= '    <div class="col-lg-12">'."\n";
//         }
//         $this->header .= '      <table id="tab_' . $this->id . '" class="table table-hover">'."\n";
//         $this->header .= '        <thead><tr>'."\n";
//         foreach ($this->thead as $thead) {
//           $this->header .= '          <th>' . $thead . '</th>'."\n";
//         }
//         $this->header .= '        </tr></thead>'."\n";
//         $this->header .= '        <tbody>'."\n";
//         $entries = 0;
//         # $this->body generation will depend on check type
//         switch ($this->typeCheck) {
//           case 'majorityPerCluster':
//             foreach (array_diff(array_count_values(array_map("strval", $xmlContent->xpath("/hosts/host/vcenter"))), array("1")) as  $key_vcenter => $value_vcenter) {
//               foreach (array_diff(array_count_values(array_map("strval", $xmlContent->xpath("/hosts/host[vcenter='".$key_vcenter."']/cluster"))), array("1")) as  $key_cluster => $value_cluster) {
//                 if ($key_cluster == 'Standalone') { continue; }
//                 $majorityGroup = array_diff(array_count_values(array_map("strval", $xmlContent->xpath("/hosts/host[vcenter='".$key_vcenter."' and cluster='".$key_cluster."']/".$this->majorityProperty))), array("0"));
//                 if (count($majorityGroup) < 1) {
//                   $majorityGroup = '';
//                 } else {
//                   arsort($majorityGroup);
//                   $majorityGroup = array_keys($majorityGroup)[0];
//                 }
//                 foreach ($xmlContent->xpath("/hosts/host[vcenter='".$key_vcenter."' and cluster='".$key_cluster."' and ".$this->majorityProperty."!='".$majorityGroup."']") as $entry) {
//                   $this->body .= '          <tr>';
//                   foreach ($this->tbody as $column) {
//                     $entries++;
//                     $this->body .= eval("return $column;");
//                   }
//                   $this->body .= '</tr>'."\n";
//                 }
//               }
//             }
//             if ($entries == 0) {
//               $this->header = '';
//               $this->body = '    <h2 class="text-success anchor" id="' . $this->id . '"><i class="glyphicon glyphicon-ok-sign"></i> ' . $this->langDef[$this->id]["title"] . ' <small>' . str_replace(array("\n", "\t", "\r"), '', (rand_line($this->achievementFile))) . '</small></h2>'."\n";
//             }
//             break;
//           case 'mismatchPerCluster':
//             foreach (array_diff(array_count_values(array_map("strval", $xmlContent->xpath("/hosts/host/vcenter"))), array("1")) as  $key_vcenter => $value_vcenter) {
//               foreach (array_diff(array_count_values(array_map("strval", $xmlContent->xpath("/hosts/host[vcenter='".$key_vcenter."']/cluster"))), array("1")) as  $key_cluster => $value_cluster) {
//                 if ($key_cluster == 'Standalone') { continue; }
//                 $mismatchMatches = array_diff(array_count_values(array_map("strval", $xmlContent->xpath("/hosts/host[vcenter='".$key_vcenter."' and cluster='".$key_cluster."']/".$this->mismatchProperty))), array("1"));
//                 if (count($mismatchMatches) > 1) {
//                   $compliance = '<i class="glyphicon glyphicon-remove-sign text-danger"></i>';
//                   $mismatchEntry = "*Mismatch* ";
//                 } else {
//                   $compliance = '<i class="glyphicon glyphicon-ok-sign text-success"></i>';
//                   $mismatchEntry = "";
//                 }
//                 foreach (array_diff(array_count_values(array_map("strval", $xmlContent->xpath("/hosts/host[vcenter='".$key_vcenter."' and cluster='".$key_cluster."']/".$this->mismatchProperty))), array("1")) as  $key_entry => $value_entry) {
//                   $mismatchEntry .= " $key_entry";
//                 }
//                 $this->body .= '          <tr>';
//                 foreach ($this->tbody as $column) {
//                   $entries++;
//                   $this->body .= eval("return $column;");
//                 }
//                 $this->body .= '</tr>'."\n";
//               }
//             }
//             break;
//           case 'pivotTable':
//             $dataPivot = array_diff(array_count_values(array_map("strval", $xmlContent->xpath($this->xpathQuery))), array("1"));
//             arsort($dataPivot);
//             foreach ($dataPivot as $key => $value) {
//               $entries++;
//               $this->body .= '            <tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
//             }
//             break;
//           case 'pivotTableGraphed':
//             $dataPivot = array_diff(array_count_values(array_map("strval", $xmlContent->xpath($this->xpathQuery))), array("1"));
//             arsort($dataPivot);
//             $this->graph .= '<div id="graph_' . $this->id . '" class="col-lg-8" style="min-height: 550px;">sdf</div>'."\n";
//             $this->graph .= '            <script>
//             var option = {
//               tooltip : {
//                   trigger: "item",
//                   formatter: "{b}<br/>{c} ({d}%)"
//               },
//               toolbox: {
//                 show : true,
//                 feature : {
//                   mark : {show: false},
//                   dataView : {show: false},
//                   magicType: { show : true, title : { line : "Display with line", bar : "Display with bar" }, type : ["line", "bar"] },
//                   restore : {show: true},
//                   saveAsImage : {show: true}
//                 }
//               },
//               calculable : true,
//               series : [{
//                       name:' . $this->id . ',
//                       type:"pie",
//
//             ';
//
//             foreach ($dataPivot as $key => $value) {
//               $data[] = (object) array('value' => $value, 'name' => $key);
//               $entries++;
//               $this->body .= '            <tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
//             }
//
//             $this->graph .= '
//                         data: ' . json_encode($data, JSON_NUMERIC_CHECK) . '
//                     }]
//                   };
//                   var ttbChart = echarts.init(document.getElementById("graph_' . $this->id . '"));
//                   ttbChart.setTheme("macarons");
//                   ttbChart.setOption(option);
//             </script>';
//             $this->graph .= '    </div><hr class="divider-dashed" />'."\n";
//             break;
//           default:
//             foreach ($xpathFull as $entry) {
//               $entries++;
//               $this->body .= '          <tr>';
//               foreach ($this->tbody as $column) {
//                 $this->body .= eval("return $column;");
//               }
//               $this->body .= '</tr>'."\n";
//             }
//         }
//         if ($entries > 0) {
//           $this->footer .= '        </tbody>
//       </table>
//     </div>
//     <script type="text/javascript">
//     $(document).ready( function () {
//       $("#tab_' . $this->id . '").DataTable( {'."\n";
//         if ($this->typeCheck != "pivotTableGraphed") { $this->footer .= '        "search": { "smart": false, "regex": true },'."\n"; } else { $this->footer .= '        "searching": false, "lengthChange": false, "info": false,'; }
//         if (!is_null($this->order)) { $this->footer .= '        "order": [' . $this->order . '],'."\n"; }
//         if (!is_null($this->columnDefs)) { $this->footer .= '        "columnDefs": [' . $this->columnDefs . '],'."\n"; }
//         $this->footer .= '      } );
//     } );
//     </script>'."\n";
//         if ($this->typeCheck != "pivotTableGraphed") { $this->footer .= '    <hr class="divider-dashed" />'."\n"; }
//         }
//       } elseif ($this->h_configs['showEmpty'] == 'enable') {
//         $this->body = '    <h2 class="text-success anchor" id="' . $this->id . '"><i class="glyphicon glyphicon-ok-sign"></i> ' . $this->langDef[$this->id]["title"] . ' <small>' . str_replace(array("\n", "\t", "\r"), '', (rand_line($this->achievementFile))) . '</small></h2>'."\n";
//       }
//     } else {
//       $this->body .= '    <div class="alert alert-danger" role="alert">
//       <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
//       <span class="sr-only">Error:</span>
//       File ' . $this->xmlFile . ' is not existant or not readable. Please check for <a href="moduleselector.php">module selection</a> and/or wait for scheduler
//     </div>'."\n";
//     }
//     echo $this->header;
//     echo $this->body;
//     echo $this->footer;
//     if ($this->typeCheck == "pivotTableGraphed") { echo $this->graph; }
//   }
//
//   public function displayHeader($formPage, $visible = true) {
//     global $xmlStartPath;
//     $this->xmlStartPath = $xmlStartPath;
//     $this->scannedDirectories = array_values(array_diff(scandir($xmlStartPath, SCANDIR_SORT_DESCENDING), array('..', '.', 'latest')));
//     if (count($this->scannedDirectories) < 1) {
//       throw new Exception('There is no data generated yet. Please add some vCenter to the <a href="credstore.php">Credential Store</a> and come back as soon as data will be retrieved (should take just a couple of minutes).');
//     }
//     if ($_SERVER['REQUEST_METHOD'] == 'POST'){
//       $this->selectedDate = $_POST["selectedDate"];
//       foreach ($this->scannedDirectories as $key => $value) {
//         if (strpos($value, str_replace("/","",$this->selectedDate)) === 0) {
//           $this->xmlSelectedPath = $value;
//           break;
//         }
//       }
//     } else {
//       $this->selectedDate = DateTime::createFromFormat('Ymd', $this->scannedDirectories[0])->format('Y/m/d');
//       $this->xmlSelectedPath = "latest";
//     }
//     global $title;
//     $this->header = ($visible) ? '  <div style="padding-top: 10px; padding-bottom: 10px;" class="container">'."\n" : '  <div id="purgeLoading" style="display:flex;"><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>&nbsp; Loading inventory, please wait for awesomeness ...</div>' . "\n" . '  <div style="display:none; padding-top: 10px; padding-bottom: 10px;" class="container" id="wrapper-container">'."\n";
//     $this->header .= '    <div class="row">
//       <div class="col-lg-10 alert alert-info" style="margin-top: 20px; text-align: center;">
//         <h1 style="margin-top: 10px;">'.$title.' on ' . DateTime::createFromFormat('Y/m/d', $this->selectedDate)->format('l jS F Y') . '</h1>
//       </div>
//       <div class="alert col-lg-2">
//         <form action="' . $formPage . '" style="margin-top: 5px;" method="post">
//           <div class="form-group" style="margin-bottom: 5px;">
//             <div class="input-group date" id="datetimepicker11">
//               <input type="text" class="form-control" name="selectedDate" readonly />
//               <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
//             </div>
//           </div>
//           <button type="submit" class="btn btn-default" style="width: 100%">Select this date</button>
//           <script type="text/javascript">'."\n";
//     $this->header .= "             $(function () {
//               $('#datetimepicker11').datetimepicker({
//                 ignoreReadonly: true,
//                 format: 'YYYY/MM/DD',
//                 showTodayButton: true,
//                 defaultDate: moment(\"" . $this->selectedDate . "\", \"YYYY/MM/DD\"),
//                 enabledDates: [\n";
//     foreach ($this->scannedDirectories as $xmlDirectory) {
//       $this->header .= '                  moment("' . DateTime::createFromFormat('Ymd', $xmlDirectory)->format('Y/m/d H:i') . '", "YYYY/MM/DD HH:ii"),' . "\n";
//     }
//     $this->header .= '                ]
//               });
//             });
//           </script>
//         </form>
//       </div>
//     </div>'."\n";
//     if ($visible) { $this->header .= '    <div class="row" id="toc"><ul><li><strong>Tags</strong> <i class="glyphicon glyphicon-chevron-right"></i><i class="glyphicon glyphicon-chevron-right"></i></li></ul></div><hr class="divider-dashed">'."\n"; }
//     echo $this->header;
//   }
//
//   public function getModuleSchedule($module) {
//     return $this->h_moduleschedules[$module];
//   }
//
//   public function getConfig($config) {
//     if (array_key_exists($config, $this->h_configs)) {
//       return $this->h_configs[$config];
//     } else {
//       return "undefined";
//     }
//   }
//
//   public function getUserAgent($useragentPattern) {
//     global $userAgent;
//     if ($useragentPattern == 'VI Perl') {
//       return $userAgent['Perl'];
//     } elseif (preg_match("/^VMware VI Client/", $useragentPattern)) {
//       return $userAgent['Client'];
//     } elseif (preg_match("/^Mozilla/", $useragentPattern)) {
//       return $userAgent['Mozilla'];
//     } elseif (preg_match("/^VMware vim-java/", $useragentPattern)) {
//       return $userAgent['java'];
//     } elseif (preg_match("/^PowerCLI/", $useragentPattern)) {
//       return $userAgent['PowerCLI'];
//     } else {
//       return "undefined";
//     }
//   }
//
//   public function getSumValue() {
//     # code...
//   }
//
//   public function getSelectedPath() {
//     return $this->xmlStartPath.$this->xmlSelectedPath;
//   }
//
//   public function getVMInfos($vmMoRef, $vcenter) {
//     $xmlFile = $this->xmlStartPath.$this->xmlSelectedPath.'/vms-global.xml';
//     if (is_readable($xmlFile)) {
//       $xmlContent = simplexml_load_file($xmlFile);
//       $xpathFull = $xmlContent->xpath("/vms/vm[moref='" . $vmMoRef . "' and vcenter='" . $vcenter . "']");
//       return ((count($xpathFull) == 1) ? $xpathFull[0] : 'inexistant');
//     } else {
//       return 'error';
//     }
//   }
//
//   public function getDatastoreInfos($datastoreName, $vcenter) {
//     $xmlFile = $this->xmlStartPath.$this->xmlSelectedPath.'/datastores-global.xml';
//     if (is_readable($xmlFile)) {
//       $xmlContent = simplexml_load_file($xmlFile);
//       $xpathFull = $xmlContent->xpath("/datastores/datastore[name='" . $datastoreName . "' and vcenter='" . $vcenter . "']");
//       return ((count($xpathFull) == 1) ? $xpathFull[0] : 'inexistant');
//     } else {
//       return 'error';
//     }
//   }
// }

class SexiCheck {
  private $checkType = "";
  // private $xmlFile;
  // private $sqlQuery;
  private $title;
  private $description;
  private $thead = array();
  private $tbody = array();
  private $order;
  private $columnDefs;
  // private $h_configs = array();
  // private $h_modules = array();
  // private $h_moduleschedules = array();
  private $powerChoice;
  private $servicePolicyChoice;
  private $alarmStatus;
  private $header;
  private $body;
  private $footer;
  private $graph;
  private $achievementFile;
  // private $xmlConfigsFile;
  // private $xmlModulesFile;
  // private $xmlModuleSchedulesFile;
  private $selectedDate;
  // private $xmlSelectedPath;
  // private $scannedDirectories;
  // private $xmlStartPath;
  private $lang;
  private $langDef;
  private $db;
  // private $SSPCategory;

  public function __construct() {
    global $achievementFile;
    // global $xmlConfigsFile;
    // global $xmlModulesFile;
    // global $xmlModuleSchedulesFile;
    $this->achievementFile = $achievementFile;
    // $this->xmlConfigsFile = $xmlConfigsFile;
    // $this->xmlModulesFile = $xmlModulesFile;
    // $this->xmlModuleSchedulesFile = $xmlModuleSchedulesFile;

    // if (is_readable($this->xmlConfigsFile)) {
    //   $xmlConfigs = simplexml_load_file($this->xmlConfigsFile);
    //   # hash table initialization with settings XML file
    //   foreach ($xmlConfigs->xpath('/configs/config') as $config) {
    //     $this->h_configs[(string) $config->id] = (string) $config->value;
    //   }
    // } else {
    //   throw new Exception('File ' . $this->xmlConfigsFile . ' is not existant or not readable');
    // }

    // if (is_readable($this->xmlModulesFile)) {
    //   $xmlModules = simplexml_load_file($this->xmlModulesFile);
    //   # hash table initialization with settings XML file
    //   foreach ($xmlModules->xpath('/modules/module') as $module) {
    //     $this->h_modules[(string) $module->id] = (string) $module->value;
    //   }
    // } else {
    //   throw new Exception('File ' . $this->xmlModulesFile . ' is not existant or not readable');
    // }

    // if (is_readable($this->xmlModuleSchedulesFile)) {
    //   $xmlModuleSchedules = simplexml_load_file($this->xmlModuleSchedulesFile);
    //   # hash table initialization with settings XML file
    //   foreach ($xmlModuleSchedules->xpath('/modules/module') as $module) {
    //     $this->h_moduleschedules[(string) $module->id] = (string) $module->schedule;
    //   }
    // } else {
    //   throw new Exception('File ' . $this->xmlModuleSchedulesFile . ' is not existant or not readable');
    // }

    # database instanciation so we can use $db object in this class methods
    require("dbconnection.php");
    $this->db = $db;

    $this->lang = (defined($this->getConfig('lang'))) ? $this->getConfig('lang') : 'en';
    switch ($this->lang) {
      case 'en':
        $lang_file = 'lang.en.php';
        break;
      case 'fr':
        $lang_file = 'lang.fr.php';
        break;
      default:
      $lang_file = 'lang.en.php';
    }

    include_once 'locales/'.$lang_file;
    $this->langDef = $lang;

    global $powerChoice;
    global $alarmStatus;
    global $servicePolicyChoice;
    $this->powerChoice = $powerChoice;
    $this->alarmStatus = $alarmStatus;
    $this->servicePolicyChoice = $servicePolicyChoice;
  }

  private function dbGetDate() {
    $this->db->orderBy("date","desc");
    $this->db->groupBy("DATE(executiontime.date)");
    $resultDate = $this->db->get('executiontime', NULL, 'date');
    return $resultDate;
  }

  public function displayCheck($args) {
    $args += [
      'xmlFile' => null,
      'sqlQuery' => null,
      'thead' => array(),
      'tbody' => array(),
      'order' => null,
      'columnDefs' => null,
      'typeCheck' => null,
      'majorityProperty' => null,
      'mismatchProperty' => null,
      'pivotProperty' => null,
      'id' => null,
      'sqlQueryHaving' => null,
      'sqlQueryGroupBy' => null,
    ];
    extract($args);
    $this->thead = $thead;
    $this->tbody = $tbody;
    $this->order = $order;
    $this->columnDefs = $columnDefs;
    $this->typeCheck = $typeCheck;
    $this->majorityProperty = $majorityProperty;
    $this->mismatchProperty = $mismatchProperty;
    $this->pivotProperty = $pivotProperty;
    $this->id = $id;
    $this->header = "";
    $this->body = "";
    $this->footer = "";
    $this->graph = "";

    if ($this->selectedDate != date("Y/m/d")) {
      # if not the same day, we build our dates objects that will be used in SQL query (after firstseen + before lastseen)
      $sqlQuery .= " AND main.firstseen < '" . $this->selectedDate . " 23:59:59' AND main.lastseen > '" . $this->selectedDate . " 00:00:01'";
    } else {
      $sqlQuery .= " AND main.active = 1";
    }

    if (!empty($sqlQueryHaving)) {
      $sqlQuery .= " HAVING $sqlQueryHaving";
    }
    if (!empty($sqlQueryGroupBy)) {
      $sqlQuery .= " GROUP BY $sqlQueryGroupBy";
    } elseif ($this->id != ("HOSTCONFIGURATIONISSUES" || "HOSTHARDWARESTATUS" || "VCCERTIFICATESREPORT" || "VCLICENCEREPORT")) {
      $sqlQuery .= " GROUP BY main.moref, v.id";
    }
    $sqlData = $this->db->rawQuery($sqlQuery);
    if ($this->db->count > 0) {
      $this->header .= '    <h2 class="text-danger anchor" id="' . $this->id . '"><i class="glyphicon glyphicon-exclamation-sign"></i> ' . $this->langDef[$this->id]["title"] . '</h2>'."\n";
      $this->header .= '    <div class="alert alert-warning" role="alert"><i>' . $this->langDef[$this->id]["description"] . '</i></div>'."\n";
      if ($this->typeCheck == "pivotTableGraphed") {
        $this->header .= '    <div class="row">'."\n";
        $this->header .= '    <div class="col-lg-4">'."\n";
      } else {
        $this->header .= '    <div class="col-lg-12">'."\n";
      }
      $this->header .= '      <table id="tab_' . $this->id . '" class="table table-hover">'."\n";
      $this->header .= '        <thead><tr>'."\n";
      foreach ($this->thead as $thead) {
        $this->header .= '          <th>' . $thead . '</th>'."\n";
      }
      $this->header .= '        </tr></thead>'."\n";
      $this->header .= '        <tbody>'."\n";
      $entries = 0;
      # $this->body generation will depend on check type
      switch ($this->typeCheck) {
        case 'majorityPerCluster':
          $hMajority = array();
          $sqlDataMaj = $this->db->rawQuery("SELECT cluster as clus, (SELECT " . $this->majorityProperty . " FROM hosts WHERE cluster = clus GROUP BY " . $this->majorityProperty . " ORDER BY COUNT(*) DESC LIMIT 0,1) AS topProp FROM `hosts` WHERE active = 1 GROUP BY clus");
          foreach ($sqlDataMaj as $entry) {
            $hMajority[$entry["clus"]] = $entry["topProp"];
          }
          foreach ($sqlData as $entry) {
            if ($hMajority[$entry["clusterId"]] != $entry[$this->majorityProperty]) {
              $entries++;
              $this->body .= '          <tr>';
              foreach ($this->tbody as $column) {
                $this->body .= eval("return $column;");
              }
              $this->body .= '</tr>'."\n";
            }
          }
          if ($entries == 0) {
            $this->header = '';
            $this->body = '    <h2 class="text-success anchor" id="' . $this->id . '"><i class="glyphicon glyphicon-ok-sign"></i> ' . $this->langDef[$this->id]["title"] . ' <small>' . str_replace(array("\n", "\t", "\r"), '', (rand_line($this->achievementFile))) . '</small></h2>'."\n";
          }
          break;
        case 'pivotTable':
          $dataPivot = array_diff(array_count_values(array_map("strval", $xmlContent->xpath($this->xpathQuery))), array("1"));
          arsort($dataPivot);
          foreach ($dataPivot as $key => $value) {
            $entries++;
            $this->body .= '            <tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
          }
          break;
        case 'pivotTableGraphed':
          $this->graph .= '    <div id="graph_' . $this->id . '" class="col-lg-8" style="min-height: 550px;"></div>'."\n";
          $this->graph .= '            <script>
          var option = {
            tooltip : {
                trigger: "item",
                formatter: "{b}<br/>{c} ({d}%)"
            },
            toolbox: {
              show : true,
              feature : {
                mark : {show: false},
                dataView : {show: false},
                magicType: { show : true, title : { line : "Display with line", bar : "Display with bar" }, type : ["line", "bar"] },
                restore : {show: true},
                saveAsImage : {show: true}
              }
            },
            calculable : true,
            series : [{
                    name:' . $this->id . ',
                    type:"pie",';
          foreach ($sqlData as $entry) {
            $data[] = (object) array('value' => $entry["dataValue"], 'name' => $entry["dataKey"]);
            $entries++;
            $this->body .= '            <tr><td>' . $entry["dataKey"] . '</td><td>' . $entry["dataValue"] . '</td></tr>'."\n";
          }
          $this->graph .= '
                      data: ' . json_encode($data, JSON_NUMERIC_CHECK) . '
                  }]
                };
                var ttbChart = echarts.init(document.getElementById("graph_' . $this->id . '"));
                ttbChart.setTheme("macarons");
                ttbChart.setOption(option);
          </script>'."\n";
          $this->graph .= '    </div><hr class="divider-dashed" />'."\n";
          break;
        case 'ssp':
          # hack for later loop
          $entries = $this->db->count;
          // foreach ($sqlData as $entry) {
          //   $entries++;
          //   $this->body .= '          <tr>';
          //   foreach ($this->tbody as $column) {
          //     $this->body .= eval("return $column;");
          //   }
          //   $this->body .= '</tr>'."\n";
          // }
          break;
        default:
          foreach ($sqlData as $entry) {
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
  </div>'."\n";
        if ($entries < 11 && $this->typeCheck != "pivotTableGraphed") { $this->footer .= '  <div style="clear: both; height: 10px;">&nbsp;</div>'."\n"; }
        $this->footer .= '  <script type="text/javascript">
  $(document).ready( function () {
    $("#tab_' . $this->id . '").DataTable( {
      "language": { "infoFiltered": "" },'."\n";
        if ($entries < 11) {
          $this->footer .= '      "paging":   false,
      "info":   false,'."\n";
        }
        if ($this->typeCheck != "pivotTableGraphed") { $this->footer .= '      "search": { "smart": false, "regex": true },'."\n"; } else { $this->footer .= '      "searching": false, "lengthChange": false, "info": false,'."\n"; }
        if (!is_null($this->order)) { $this->footer .= '      "order": [' . $this->order . '],'."\n"; }
        if (!is_null($this->columnDefs)) { $this->footer .= '      "columnDefs": [' . $this->columnDefs . '],'."\n"; }

        if ($this->typeCheck == 'ssp') {
          $this->footer .= '      "processing": true,
      "serverSide": true,
      "deferRender": true,
      "ajax": "server_processing.php?c=' . $this->id . '&t=' . strtotime($this->selectedDate) . '"'."\n";
        }
        $this->footer .= '      } );
    } );
    </script>'."\n";
        if ($this->typeCheck != "pivotTableGraphed") { $this->footer .= '    <hr class="divider-dashed" />'."\n"; }
      }
    } elseif ($this->getConfig('showEmpty') == 'enable') {
      $this->body = '    <h2 class="text-success anchor" id="' . $this->id . '"><i class="glyphicon glyphicon-ok-sign"></i> ' . $this->langDef[$this->id]["title"] . ' <small>' . str_replace(array("\n", "\t", "\r"), '', (rand_line($this->achievementFile))) . '</small></h2>'."\n";
    }
    // var_dump($this->db->getLastQuery());
    echo $this->header;
    echo $this->body;
    echo $this->footer;
    if ($this->typeCheck == "pivotTableGraphed") { echo $this->graph; }
  }

  public function displayHeader($formPage, $visible = true) {
    $dateAvailable = $this->dbGetDate();
    if (count($dateAvailable) < 1) {
      throw new Exception('There is no data generated yet. Please add some vCenter to the <a href="credstore.php">Credential Store</a> and come back as soon as data will be retrieved (should take just a couple of minutes).');
    }
    if ($this->dbGetCheckQuantity($formPage) < 1) {
      throw new Exception('There is no check enabled for this section (which is by default). You can enable some checks on the <a href="moduleselector.php">Module Selector</a> and come back as soon as data will be retrieved (by default it should be daily, but you can force the execution on the <a href="onetime.php">One Time Report</a>).');
    }
    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
      $this->selectedDate = $_POST["selectedDate"];
    } else {
      $this->selectedDate = DateTime::createFromFormat('Y-m-d H:i:s', $dateAvailable[0]['date'])->format('Y/m/d');
    }
    global $title;
    $this->header = ($visible) ? '  <div style="padding-top: 10px; padding-bottom: 10px;" class="container">'."\n" : '  <div id="purgeLoading" style="display:flex;"><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>&nbsp; Loading inventory, please wait for awesomeness ...</div>' . "\n" . '  <div style="display:none; padding-top: 10px; padding-bottom: 10px;" class="container" id="wrapper-container">'."\n";
    $this->header .= '    <div class="row">
      <div class="col-lg-10 alert alert-info" style="padding: 6px; margin-top: 20px; text-align: center;">
        <h1 style="margin-top: 10px;">'.$title.' <small>on ' . DateTime::createFromFormat('Y/m/d', $this->selectedDate)->format('l jS F Y') . '</small></h1>
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
    $this->header .= "             $(function () {
              $('#datetimepicker11').datetimepicker({
                ignoreReadonly: true,
                format: 'YYYY/MM/DD',
                showTodayButton: true,
                defaultDate: moment(\"" . $this->selectedDate . "\", \"YYYY/MM/DD\"),
                enabledDates: [\n";
    foreach ($dateAvailable as $dateDirectory) {
      $this->header .= '                  moment("' . DateTime::createFromFormat('Y-m-d H:i:s', $dateDirectory['date'])->format('Y/m/d H:i') . '", "YYYY/MM/DD HH:ii"),' . "\n";
    }
    $this->header .= '                ]
              });
            });
          </script>
        </form>
      </div>
    </div>'."\n";
    if ($visible) { $this->header .= '    <div class="row" id="toc"><ul><li><strong>Tags</strong> <i class="glyphicon glyphicon-chevron-right"></i><i class="glyphicon glyphicon-chevron-right"></i></li></ul></div><hr class="divider-dashed">'."\n"; }
    echo $this->header;
  }

  public function getModuleSchedule($module) {
    $this->db->where('id', $module);
    $resultSchedule = $this->db->getOne("modules", "schedule");
    return $resultSchedule['schedule'];
  }

  public function getConfig($config) {
    $this->db->where('configid', $config);
    $resultConfig = $this->db->getOne("config", "value");
    if ($this->db->count > 0) {
      return $resultConfig['value'];
    } else {
      return "undefined";
    }
  }

  public function getUserAgent($useragentPattern) {
    global $userAgent;
    if ($useragentPattern == 'VI Perl') {
      return $userAgent['Perl'];
    } elseif (preg_match("/^VMware \w* Client/", $useragentPattern)) {
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

  public function getSelectedPath() {
    return $this->xmlStartPath.$this->xmlSelectedPath;
  }

  public function getSelectedDate() {
    return $this->selectedDate;
  }

  // public function setSSPCategory($category) {
  //   $this->SSPCategory = $category;
  // }

  public function getVMInfos($vmID) {
    $this->db->join("hosts h", "vms.host = h.id", "INNER");
    $this->db->join("clusters c", "h.cluster = c.id", "INNER");
    $this->db->join("vcenters v", "h.vcenter = v.id", "INNER");
    $this->db->where('vms.id', $vmID);
    $resultVM = $this->db->getOne("vms", "vms.*, c.cluster_name as cluster, h.host_name as host, v.vcname as vcenter, v.id as vcenterID");
    if ($this->db->count > 0) {
      return $resultVM;
    } else {
      return "undefined";
    }
  }

  public function getDatastoreInfos($datastoreID) {
    $this->db->where('id', $datastoreID);
    $resultVM = $this->db->getOne("datastores", "datastore_name, size, ROUND(100*(freespace/size)) as pct_free");
    if ($this->db->count > 0) {
      return $resultVM;
    } else {
      return "undefined";
    }
  }

  public function getHostInfos($hostID) {
    $this->db->join("clusters c", "hosts.cluster = c.id", "INNER");
    $this->db->join("vcenters v", "hosts.vcenter = v.id", "INNER");
    $this->db->where('hosts.id', $hostID);
    $resultHost = $this->db->getOne("hosts", "hosts.*, c.cluster_name as cluster, v.vcname as vcenter");
    if ($this->db->count > 0) {
      return $resultHost;
    } else {
      return "undefined";
    }
  }

  private function dbGetCheckQuantity($formPage) {
    # hack for handling inventory page
    if ($formPage == ('/inv.php' OR '/capacityplanning.php')) { return 1; }
    # this will return eabled checks for the requested formpage
    $this->db->join("moduleCategory", "modules.category_id = moduleCategory.id", "INNER");
    $this->db->where('modules.schedule', 'off', '<>');
    switch($formPage) {
      case '/check-cluster.php':
        $this->db->where('moduleCategory.category', 'Cluster');
      break;
      case '/check-datastore.php':
        $this->db->where('moduleCategory.category', 'Datastore');
      break;
      case '/check-host.php':
        $this->db->where('moduleCategory.category', 'Host');
      break;
      case '/check-network.php':
        $this->db->where('moduleCategory.category', 'Network');
      break;
      case '/check-vsan.php':
        $this->db->where('moduleCategory.category', 'VSAN');
      break;
      case '/check-vcenter.php':
        $this->db->where('moduleCategory.category', 'vCenter');
      break;
      case '/check-vm.php':
        $this->db->where('moduleCategory.category', 'Virtual Machine');
      break;
      default:
        $this->db->where('moduleCategory.category', null);
    }
    return $this->db->getValue("modules", "COUNT(modules.id)");
    // var_dump($this->db->getLastQuery());
    // return $total;
  }
}

?>
