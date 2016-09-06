<?php
class SexiCheck {
  private $checkType = "";
  private $title;
  private $description;
  private $thead = array();
  private $tbody = array();
  private $order;
  private $columnDefs;
  private $powerChoice;
  private $servicePolicyChoice;
  private $alarmStatus;
  private $header;
  private $body;
  private $footer;
  private $graph;
  private $achievementFile;
  private $selectedDate;
  private $lang;
  private $langDef;
  private $db;

  public function __construct() {
    global $achievementFile;
    $this->achievementFile = $achievementFile;
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
    error_log($sqlQuery);
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
          $this->graph .= '            <script>'."\n";
          $this->graph .= '          var option = {'."\n";
          $this->graph .= '            tooltip : {'."\n";
          $this->graph .= '                trigger: "item",'."\n";
          $this->graph .= '                formatter: "{b}<br/>{c} ({d}%)"'."\n";
          $this->graph .= '            },'."\n";
          $this->graph .= '            toolbox: {'."\n";
          $this->graph .= '              show : true,'."\n";
          $this->graph .= '              feature : {'."\n";
          $this->graph .= '                mark : {show: false},'."\n";
          $this->graph .= '                dataView : {show: false},'."\n";
          $this->graph .= '                magicType: { show : true, title : { line : "Display with line", bar : "Display with bar" }, type : ["line", "bar"] },'."\n";
          $this->graph .= '                restore : {show: true},'."\n";
          $this->graph .= '                saveAsImage : {show: true}'."\n";
          $this->graph .= '              }'."\n";
          $this->graph .= '            },'."\n";
          $this->graph .= '            calculable : true,'."\n";
          $this->graph .= '            series : [{'."\n";
          $this->graph .= '                    name:' . $this->id . ','."\n";
          $this->graph .= '                    type:"pie",'."\n";
          foreach ($sqlData as $entry) {
            $data[] = (object) array('value' => $entry["dataValue"], 'name' => $entry["dataKey"]);
            $entries++;
            $this->body .= '            <tr><td>' . $entry["dataKey"] . '</td><td>' . $entry["dataValue"] . '</td></tr>'."\n";
          }
          $this->graph .= '                      data: ' . json_encode($data, JSON_NUMERIC_CHECK) . ''."\n";
          $this->graph .= '                  }]'."\n";
          $this->graph .= '                };'."\n";
          $this->graph .= '                var ttbChart = echarts.init(document.getElementById("graph_' . $this->id . '"));'."\n";
          $this->graph .= '                ttbChart.setTheme("macarons");'."\n";
          $this->graph .= '                ttbChart.setOption(option);'."\n";
          $this->graph .= '          </script>'."\n";
          $this->graph .= '    </div><hr class="divider-dashed" />'."\n";
          break;
        case 'ssp':
          # hack for later loop
          $entries = $this->db->count;
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
        $this->footer .= '        </tbody>'."\n";
        $this->footer .= '    </table>'."\n";
        $this->footer .= '  </div>'."\n";
        if ($entries < 11 && $this->typeCheck != "pivotTableGraphed") { $this->footer .= '  <div style="clear: both; height: 10px;">&nbsp;</div>'."\n"; }
        $this->footer .= '  <script type="text/javascript">'."\n";
        $this->footer .= '  $(document).ready( function () {'."\n";
        $this->footer .= '    $("#tab_' . $this->id . '").DataTable( {'."\n";
        $this->footer .= '      "language": { "infoFiltered": "" },'."\n";
        if ($entries < 11) {
          $this->footer .= '      "paging":   false,'."\n";
          $this->footer .= '      "info":   false,'."\n";
        }
        if ($this->typeCheck != "pivotTableGraphed") { $this->footer .= '      "search": { "smart": false, "regex": true },'."\n"; } else { $this->footer .= '      "searching": false, "lengthChange": false, "info": false,'."\n"; }
        if (!is_null($this->order)) { $this->footer .= '      "order": [' . $this->order . '],'."\n"; }
        if (!is_null($this->columnDefs)) { $this->footer .= '      "columnDefs": [' . $this->columnDefs . '],'."\n"; }

        if ($this->typeCheck == 'ssp') {
          $this->footer .= '      "processing": true,'."\n";
          $this->footer .= '      "serverSide": true,'."\n";
          $this->footer .= '      "deferRender": true,'."\n";
          $this->footer .= '      "ajax": "server_processing.php?c=' . $this->id . '&t=' . strtotime($this->selectedDate) . '"'."\n";
        }
        $this->footer .= '      } );'."\n";
        $this->footer .= '    } );'."\n";
        $this->footer .= '    </script>'."\n";
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
    $this->db->where('module', $module);
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
    $this->db->join("vcenters v", "vms.vcenter = v.id", "INNER");
    $this->db->join("vmMetrics vmm", "vms.id = vmm.vm_id", "INNER");
    $this->db->where('vms.id', $vmID);
    $resultVM = $this->db->getOne("vms", "vms.*, vmm.*, c.cluster_name as cluster, h.host_name as host, v.vcname as vcenter, v.id as vcenterID");
    if ($this->db->count > 0) {
      return $resultVM;
    } else {
      return "undefined";
    }
  }

  public function getDatastoreInfos($datastoreID) {
    $this->db->join("datastoreMetrics dm", "datastores.id = dm.datastore_id", "INNER");
    $this->db->where('datastores.id', $datastoreID);
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
    if ($formPage == '/inv.php' OR $formPage == '/bundle.php' OR $formPage == '/capacityplanning.php') { return 1; }
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
  }
}
?>
