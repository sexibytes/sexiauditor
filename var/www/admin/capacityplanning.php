<?php
require("session.php");
$title = "Capacity Planning";
$additionalStylesheet = array(  'css/jquery.dataTables.min.css',
                                'css/whhg.css',
                                'css/bootstrap-datetimepicker.css');
$additionalScript = array(  'js/highcharts.js',
                            'js/jquery.dataTables.min.js',
                            'js/jszip.min.js',
                            'js/dataTables.autoFill.min.js',
                            'js/dataTables.bootstrap.min.js',
                            'js/dataTables.buttons.min.js',
                            'js/autoFill.bootstrap.min.js',
                            'js/buttons.bootstrap.min.js',
                            'js/buttons.colVis.min.js',
                            'js/buttons.html5.min.js',
                            'js/file-size.js',
                            'js/moment.js',
                            'js/bootstrap-datetimepicker.js');
require("header.php");
require("helper.php");

try {
  # Main class loading
  $check = new SexiCheck();
  $sexigrafNode = $check->getConfig('sexigrafNode');

  try
  {
    
    # Testing connectivity to SexiGraf node
    if (!isHttpAvailable("http://$sexigrafNode:8080"))
    {
      
      throw new Exception('Connection to SexiGraf node "'. $sexigrafNode . '" seems impossible. Please check URL on settings page or check for network/firewall issue.');
      
    } # END if (!isHttpAvailable($sexigrafNode))
    
  }
  catch (Exception $e)
  {
    
    # Any exception will be ending the script, we want exception-free run
    # CSS hack for navbar margin removal
    echo '  <style>#wrapper { margin-bottom: 0px !important; }</style>'."\n";
    require("exception.php");
    exit;
    
  } # END try
  
  # Header generation
  $check->displayHeader($_SERVER['SCRIPT_NAME'], false);
} catch (Exception $e) {
  # Any exception will be ending the script, we want exception-free run
  # CSS hack for navbar margin removal
  echo '  <style>#wrapper { margin-bottom: 0px !important; }</style>'."\n";
  require("exception.php");
  exit;
}
?>

    <style>
    .highcharts-tooltip>span {
      background: white;
      border: 1px solid silver;
      border-radius: 3px;
      box-shadow: 1px 1px 2px #888;
      padding: 8px;
    }
    </style>

    <div class="col-lg-12 alert alert-warning"><i><small>This capacity planning was computed based on the statistics of the last <?php echo $check->getConfig('capacityPlanningDays'); ?> days, you can change it in the <a href="/config.php">settings</a> of the appliance.</small></i></div>

    <table id="table-sparkline" class="display table" cellspacing="0" width="100%">
      <thead>
        <th>Cluster</th>
        <th>vCenter</th>
        <th>VM Left</th>
        <th>Days Left</th>
        <th>Trend</th>
      </thead>
      <tbody id="tbody-sparkline">
<?php
$capacityPlanningDays = (int)$check->getConfig('capacityPlanningDays');
$showInfinite = $check->getConfig('showInfinite');
$jsonVC = json_decode(file_get_contents("http://$sexigrafNode:8080/metrics/find?query=vmw.*"), TRUE);
$javascriptCode = "";
foreach ($jsonVC as $entryVC) {
  $vcenter = $entryVC['text'];
  $vcenterID = $entryVC['id'];
  $jsonDC = json_decode(file_get_contents("http://$sexigrafNode:8080/metrics/find?query=$vcenterID.*"), TRUE);
  foreach ($jsonDC as $entryDC) {
    $datacenter = $entryDC['text'];
    $datacenterID = $entryDC['id'];
    $jsonCluster = json_decode(file_get_contents("http://$sexigrafNode:8080/metrics/find?query=$datacenterID.*"), TRUE);
    foreach ($jsonCluster as $entryCluster) {
      $cluster = $entryCluster['text'];
      $clusterID = $entryCluster['id'];
      $urlCapaPlan = "http://$sexigrafNode:8080/render?target=minSeries(scale(diffSeries(divideSeries(scale(sumSeries($clusterID.runtime.vm.on),100),asPercent(diffSeries(sumSeries($clusterID.datastore.*.summary.capacity),sumSeries($clusterID.datastore.*.summary.freeSpace)),sumSeries($clusterID.datastore.*.summary.capacity))),sumSeries($clusterID.runtime.vm.on)),1),scale(diffSeries(divideSeries(scale(sumSeries($clusterID.runtime.vm.on),100),maxSeries(asPercent(sumSeries($clusterID.quickstats.cpu.usage),sumSeries($clusterID.quickstats.cpu.effective)),asPercent(sumSeries($clusterID.quickstats.mem.usage),sumSeries($clusterID.quickstats.mem.effective)))),sumSeries($clusterID.runtime.vm.on)),1))";

      $optionsJson = "&from=-".$capacityPlanningDays."days&format=json";
      $dataCapaPlan = json_decode(file_get_contents($urlCapaPlan.$optionsJson), TRUE)[0]["datapoints"];
      
      $dataCapaPlanInversed = array();
      foreach ($dataCapaPlan as $tmpDataCapaPlan) {
        $dataCapaPlanInversed[] = (int)round($tmpDataCapaPlan[0]);
      }
      $maxDataPoint = 50;
      $newDataCapaPlanInversed = array();
      $stepDataPoint = 1;
      if (count($dataCapaPlanInversed) > $maxDataPoint) {
        $stepDataPoint = floor(count($dataCapaPlanInversed)/$maxDataPoint);
      } else {
        $maxDataPoint = count($dataCapaPlanInversed);
      }
      for ($i = 1; $i < $maxDataPoint; $i++) {
        $newDataCapaPlanInversed[] = $dataCapaPlanInversed[$stepDataPoint * $i];
      }
      $newDataCapaPlanInversed[] = $dataCapaPlanInversed[count($dataCapaPlanInversed)-2];
      $vmLeftT0 = $dataCapaPlanInversed[0];
      $vmLeftT1 = $dataCapaPlanInversed[count($dataCapaPlanInversed)-2];
      $coefficientCapaPlan = ($vmLeftT1-$vmLeftT0)/$capacityPlanningDays;
      if ($coefficientCapaPlan < 0) {
        $daysLeft = round(abs($vmLeftT1/$coefficientCapaPlan),0);
        $colorLine = "#910000";
      } else {
        $daysLeft = "<i class=\"icon-infinityalt\"></i>";
        $colorLine = "green";
      }
      if ($check->getSelectedDate() != date("Y/m/d")) {
        $from = str_replace("/", "", $check->getSelectedDate());
        $until = (string)DateTime::createFromFormat('Y/m/d', $check->getSelectedDate())->sub(new DateInterval('P'.$capacityPlanningDays.'D'))->format('Ymd');
        $optionsTime = "&from=$from&until=$until";
      } else {
        $optionsTime = "&from=-".$capacityPlanningDays."days";
      }
      if ($coefficientCapaPlan < 0 || $showInfinite == 'enable') {
        echo "      <tr>\n";
        echo "        <th>$cluster</th>\n";
        echo "        <td>$vcenter</td>\n";
        echo "        <td>$vmLeftT1</td>\n";
        echo "        <td>$daysLeft</td>\n";
        echo '        <td data-sparkline="' . implode(", ", $newDataCapaPlanInversed) . '" data-sparkline-color="'. $colorLine . '"/>'."\n";
        echo "      </tr>\n";
      }
    }
  }
}
?>
      <tbody>
    </table>
    
    <script type="text/javascript">
      $(function () {
        /**
         * Create a constructor for sparklines that takes some sensible defaults and merges in the individual
         * chart options. This function is also available from the jQuery plugin as $(element).highcharts('SparkLine').
         */
        Highcharts.SparkLine = function (a, b, c) {
          var hasRenderToArg = typeof a === 'string' || a.nodeName,
          options = arguments[hasRenderToArg ? 1 : 0],
          defaultOptions = {
            chart: {
              renderTo: (options.chart && options.chart.renderTo) || this,
              backgroundColor: null,
              borderWidth: 0,
              type: 'area',
              margin: [2, 0, 2, 0],
              width: 500,
              height: 50,
              style: { overflow: 'visible', fontFamily: 'Helvetica' },
              skipClone: true
            },
            title: { text: '' },
            credits: { enabled: false },
            xAxis: {
              labels: { enabled: false },
              title: { text: null },
              startOnTick: false,
              endOnTick: false,
              tickPositions: []
            },
            yAxis: {
              endOnTick: false,
              startOnTick: false,
              labels: { enabled: false },
              title: { text: null },
              tickPositions: [0]
            },
            legend: { enabled: false },
            tooltip: {
              backgroundColor: null,
              borderWidth: 0,
              shadow: false,
              useHTML: true,
              hideDelay: 0,
              shared: true,
              padding: 0,
              positioner: function (w, h, point) { return { x: point.plotX - w / 2, y: point.plotY - h }; }
            },
            plotOptions: {
              series: {
                animation: false,
                lineWidth: 1,
                shadow: false,
                states: { hover: { lineWidth: 1 } },
                marker: {
                  radius: 1,
                  states: { hover: { radius: 2 } }
                },
                fillOpacity: 0.25
              }
            }
          };
          options = Highcharts.merge(defaultOptions, options);
          return hasRenderToArg ? new Highcharts.Chart(a, options, c) : new Highcharts.Chart(options, b);
        };

        var start = +new Date(),
        $tds = $('td[data-sparkline]'),
        fullLen = $tds.length,
        n = 0;

        // Creating 153 sparkline charts is quite fast in modern browsers, but IE8 and mobile
        // can take some seconds, so we split the input into chunks and apply them in timeouts
        // in order avoid locking up the browser process and allow interaction.
        function doChunk() {
          var time = +new Date(),
          i,
          len = $tds.length,
          $td,
          stringdata,
          arr,
          data,
          chart;

          for (i = 0; i < len; i += 1) {
            $td = $($tds[i]);
            stringdata = $td.data('sparkline');
            arr = stringdata.split('; ');
            data = $.map(arr[0].split(', '), parseFloat);
            chart = {};
            if (arr[1]) { chart.type = arr[1]; }
            $td.highcharts('SparkLine', {
              series: [{
                data: data,
                pointStart: 1
              }],
              plotOptions: {
                series: {
                  color: $td.data('sparkline-color')
                }
              },
              tooltip: {
                headerFormat: '<span>' + $td.parent().find('th').html() + '</span><br/>',
                pointFormat: '<b>{point.y}</b> VM left'
              },
              chart: chart
            });
            n += 1;
            // If the process takes too much time, run a timeout to allow interaction with the browser
            if (new Date() - time > 500) {
              $tds.splice(0, i + 1);
              setTimeout(doChunk, 0);
              break;
            }
          }
        }
        doChunk();
      });
      
      $(document).ready( function () {
        var table = $('#table-sparkline').DataTable( {
          "language": { "infoFiltered": "" },
          "search": {
            "smart": false,
            "regex": true
          },
          "columnDefs": [
            { "orderable": false, "targets": 4 },
            { "className": "dt-body-center", "targets": [ 2,3,4 ] }
          ]
        } );
      } );
      document.getElementById("wrapper-container").style.display = "block";
      document.getElementById("purgeLoading").style.display = "none";
    </script>

<?php require("footer.php"); ?>
