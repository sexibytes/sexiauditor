<?php require("session.php"); ?>
<?php
$isAdminPage = true;
$title = "Time To Build";
$additionalStylesheet = array(  );
$additionalScript = array( 'js/echarts-all-english-v2.js' );
require("header.php");
?>
<?php
$xmlTTBFile = "/opt/vcron/scheduler-ttb.xml";
if (is_readable($xmlTTBFile)) :
	$xml = simplexml_load_file($xmlTTBFile);

	foreach ($xml->children() as $exectime) {
		$dataTemp = null;
		$dataTemp[] = 1000 * DateTime::createFromFormat('YmdHi', $exectime->attributes()['date'])->getTimestamp();
		$dataTemp[] = (int) $exectime->attributes()['seconds'];
		$data[] = $dataTemp;
	}
?>
    <div class="container">
        <h2>Execution Time</h2>
		<div id="main" style="height:600px"></div>
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
		dataZoom: { show: true, start : 70 },
		grid: { y2: 80 },
		xAxis : [ { type : 'time' } ],
		yAxis : [ { min : 0, axisLabel : { formatter: '{value}s'} } ],
		series : [ {
            name: 'executiontime',
			type: 'line',
            showAllSymbol: true,
			symbolSize: 2,
			data: <?php echo json_encode($data, JSON_NUMERIC_CHECK); ?>
		} ]
	};
	var ttbChart = echarts.init(document.getElementById('main'));
	ttbChart.setTheme('macarons');
	ttbChart.setOption(option); 
</script>

<?php
else :
    echo '  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> File ' . $xmlTTBFile . ' is not existant or not readable</div>';
endif;
?>
<?php require("footer.php"); ?>
