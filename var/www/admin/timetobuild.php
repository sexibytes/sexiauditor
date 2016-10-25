<?php
require("session.php");
require("dbconnection.php");
$isAdminPage = true;
$title = "Time To Build";
$additionalScript = array( 'js/echarts-all-english-v2.js' );
require("header.php");
require("helper.php");
$data = array();
$db->where('configid', "timeToBuildCount");
$timeToBuildCount = $db->getOne('config', "value");

if ($timeToBuildCount['value'] > 0)
{
  
  $limit = $timeToBuildCount['value'];
  
}
else
{
  
  $limit = NULL;
  
} # END if ($timeToBuildCount['value'] > 0)

$db->orderBy("date","Desc");
$resultExecutionTime = $db->get('executiontime', $limit);

# For display purpose, we want only to show graph if there is at least 2 entries
if ($db->count > 1) :
  
  foreach ($resultExecutionTime as $exectime)
  {
    
    $dataTemp = null;
    $dataTemp[] = 1000 * DateTime::createFromFormat('Y-m-d H:i:s', $exectime['date'])->getTimestamp();
    $dataTemp[] = (int) $exectime['seconds'];
    $data[] = $dataTemp;
    
  } # END foreach ($resultExecutionTime as $exectime)
  
?>
  <div class="container">
    <h2>Execution Time <small>(last <?php echo $timeToBuildCount['value']; ?> builds)</small></h2>
    <div id="main" style="height:400px"></div>
  </div>

  <script type="text/javascript">
  var option = {
    tooltip : {
      trigger: 'item',
      formatter : function (params) {
        var date = new Date(params.value[0]);
        return date.getFullYear() + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2) + ' ' + ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + '<br/>Duration: ' + params.value[1] + 's';
      }
    },
    toolbox: {
      show : true,
      feature : {
        mark : {show: false},
        dataView : {show: false},
        magicType: { show : true, title : { line : 'Display with line', bar : 'Display with bar' }, type : ['line', 'bar'] },
        restore : {show: true},
        saveAsImage : {show: true}
      }
    },
<?php if ($db->count > 10) : ?>
    dataZoom: { show: true, start : 70 },
<?php endif; ?>
    grid: { y2: 80 },
    xAxis : [ { type : 'time' } ],
    yAxis : [ { min : 0, axisLabel : { formatter: '{value}s'} } ],
    series : [ {
      smooth:true,
      name: 'executiontime',
      type: 'line',
      showAllSymbol: true,
      symbolSize: 2,
      data: <?php echo json_encode($data, JSON_NUMERIC_CHECK) . "\n"; ?>
    } ]
  };
  var ttbChart = echarts.init(document.getElementById('main'));
  ttbChart.setTheme('macarons');
  ttbChart.setOption(option);
  </script>
<?php else : ?>
    <div class="alert alert-warning" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Warning:</span> The scheduler have not been executed yet, add some server and module first.</div>
  </div>
<?php endif; ?>
<?php require("footer.php"); ?>
