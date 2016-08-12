<?php
require("session.php");
$title = "Capacity Planning";
$additionalStylesheet = array(  'css/jquery.dataTables.min.css',
                                'css/whhg.css',
                                'css/bootstrap-datetimepicker.css');
$additionalScript = array(  'js/jquery.dataTables.min.js',
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
  # Header generation
  $check->displayHeader($_SERVER['SCRIPT_NAME'], false);
} catch (Exception $e) {
  # Any exception will be ending the script, we want exception-free run
  exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> ' . $e->getMessage() . '</div>');
}
?>
    <table id="capacityplanning" class="display table" cellspacing="0" width="100%">
      <thead>
        <th>Cluster</th>
        <th>vCenter</th>
        <th>VM Left</th>
        <th>Days Left</th>
        <th>Trend</th>
      </thead>
      <tbody>
<?php
$sexigrafNode = $check->getConfig('sexigrafNode');
$capacityPlanningDays = (int)$check->getConfig('capacityPlanningDays');
$showInfinite = $check->getConfig('showInfinite');
$jsonVC = json_decode(file_get_contents("http://$sexigrafNode:8080/metrics/find?query=vmw.*"), TRUE);
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
      $urlCapaPlan = "http://$sexigrafNode:8080/render?target=scale(diffSeries(divideSeries(scale(sumSeries($clusterID.runtime.vm.on),100),maxSeries(asPercent(sumSeries($clusterID.quickstats.cpu.usage),sumSeries($clusterID.quickstats.cpu.effective)),asPercent(sumSeries($clusterID.quickstats.mem.usage),sumSeries($clusterID.quickstats.mem.effective)))),sumSeries($clusterID.runtime.vm.on)),1)";
      $optionsJson = "&from=-".$capacityPlanningDays."days&format=json";
      $dataCapaPlan = json_decode(file_get_contents($urlCapaPlan.$optionsJson), TRUE)[0]["datapoints"];
      $vmLeftT0 = round($dataCapaPlan[0][0],0);
      $vmLeftT1 = round(array_slice($dataCapaPlan,-2)[0][0],0);
      $coefficientCapaPlan = ($vmLeftT1-$vmLeftT0)/$capacityPlanningDays;
      if ($coefficientCapaPlan < 0) {
        $daysLeft = round(abs($vmLeftT1/$coefficientCapaPlan),0);
        $colorLine = "red";
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
      $optionsImage = $optionsTime."&hideLegend=true&width=500&height=50&hideAxes=true&bgcolor=FFFFFF00&colorList=$colorLine";
      if ($coefficientCapaPlan < 0 || $showInfinite == 'enable') {
        echo "      <tr>\n";
        echo "        <th>$cluster</th>\n";
        echo "        <td>$vcenter</td>\n";
        echo "        <td>$vmLeftT0</td>\n";
        echo "        <td>$daysLeft</td>\n";
        echo '        <td><img src="data:image/png;base64,' . base64_encode(file_get_contents($urlCapaPlan.$optionsImage)) . '"></td>'."\n";
        echo "      </tr>\n";
      }
    }
  }
}
?>
      <tbody>
    </table>

    <script type="text/javascript">
      $(document).ready( function () {
        var table = $('#capacityplanning').DataTable( {
          "language": { "infoFiltered": "" },
          "search": {
            "smart": false,
            "regex": true
          },
          "columnDefs": [
            { "orderable": false, "targets": 4 },
            { "className": "dt-body-center", "targets": [ 2,3 ] }
          ]
        } );
      } );
      document.getElementById("wrapper-container").style.display = "block";
      document.getElementById("purgeLoading").style.display = "none";
    </script>

<?php require("footer.php"); ?>
