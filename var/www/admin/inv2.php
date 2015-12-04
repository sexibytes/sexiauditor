<?php
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;
?>
<!doctype html>
<html>
	<head>
		<title>Offline Inventory</title>
		
<!--		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css"/>
		<link rel="stylesheet" type="text/css" href="css/jquery.dataTables.min.css"/>
		<link rel="stylesheet" type="text/css" href="css/sexigraf.css"/>

                <script type="text/javascript" src="js/jquery.min.js"></script>
                <script type="text/javascript" src="js/bootstrap.min.js"></script>
                <script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
                <script type="text/javascript" src="js/dataTables.bootstrap.min.js"></script>
                <script type="text/javascript" src="js/jszip.min.js"></script>
                <script type="text/javascript" src="js/dataTables.autoFill.min.js"></script>
                <script type="text/javascript" src="js/autoFill.bootstrap.min.js"></script>
                <script type="text/javascript" src="js/dataTables.buttons.min.js"></script>
                <script type="text/javascript" src="js/buttons.bootstrap.min.js"></script>
                <script type="text/javascript" src="js/buttons.colVis.min.js"></script>
                <script type="text/javascript" src="js/buttons.html5.min.js"></script> -->
		<script type="text/javascript" src="//code.jquery.com/jquery-1.9.1.js"></script>
		<script src="https://code.highcharts.com/highcharts.js"></script>
		<script src="https://code.highcharts.com/modules/exporting.js"></script>
	</head>
	<body>
<pre>
<?php
$xmlFile = "/opt/vcron/data/vms.xml";
$xml = simplexml_load_file($xmlFile);

#$cluster = $xml->xpath("/vms/vm/CLUSTER");
#$cluster = array_diff(array_count_values(array_map("strval", $xml->xpath("/vms/vm/CLUSTER"))), array("1"));
foreach (array_diff(array_count_values(array_map("strval", $xml->xpath("/vms/vm/VCENTER"))), array("1")) as  $key => $value) {
	$categories[] = $key;
	#$arrayvCenterToJson[] = (object) array('name' => $key, 'y' => $value);
	$categories2 = "";
	$data2 = "";
	foreach (array_diff(array_count_values(array_map("strval", $xml->xpath("/vms/vm/VCENTER[text()='".$key."']/../CLUSTER"))), array("1")) as  $key2 => $value2) {
		$categories2[] = $key2;
		$data2[] = $value2;
	}
	$drilldown = (object) array('name' => $key, 'categories' => $categories2, 'data' => $data2);
	$data[] = (object) array('y' => $value, 'drilldown' => $drilldown);

}
#foreach (array_diff(array_count_values(array_map("strval", $xml->xpath("/vms/vm/CLUSTER"))), array("1")) as  $key => $value) { $arrayClusterToJson[] = (object) array('name' => $key, 'y' => $value); }
#echo json_encode ($cluster);

#foreach ($xml->vm as $vm) {
#}


?>
</pre>
<div id="containerVC" style="min-width: 600px; height: 1000px; max-width: 1200px; margin: 0 auto"></div>
<div id="containerCluster" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>
<script type="text/javascript">

$(function () {

    var colors = Highcharts.getOptions().colors,
	categories = <?php echo json_encode($categories); ?>,
	data = <?php echo json_encode($data, JSON_NUMERIC_CHECK); ?>,
	browserData = [],
        versionsData = [],
        i,
        j,
        dataLen = data.length,
        drillDataLen,
        brightness;
	// Build the data arrays
    for (i = 0; i < dataLen; i += 1) {

        // add browser data
        browserData.push({
            name: categories[i],
            y: data[i].y,
            color: colors[i%10]
        });

        // add version data
        drillDataLen = data[i].drilldown.data.length;
        for (j = 0; j < drillDataLen; j += 1) {
            brightness = 0.2 - (j / drillDataLen) / 5;
            versionsData.push({
                name: data[i].drilldown.categories[j],
                y: data[i].drilldown.data[j],
                color: Highcharts.Color(colors[i%10]).brighten(brightness).get()
            });
        }
    }

    $('#containerVC').highcharts({
        chart: {
            type: 'pie'
        },
	credits : { enabled : false },
        title: { text: 'vCenter segmentation' },
        tooltip: { pointFormat: '{series.name}: <b>{point.y}</b>' },
        plotOptions: {
            pie: {
                shadow: false,
                center: ['50%', '50%']
            }
        },
        series: [{
            name: 'Cluster',
            data: browserData,
            size: '60%',
            dataLabels: {
                formatter: function () {
                    return this.y > 5 ? this.point.name : null;
                },
                color: '#ffffff',
                distance: -30
            }
        }, {
            name: 'Versions',
            data: versionsData,
            size: '80%',
            innerSize: '60%',
            dataLabels: {
                formatter: function () {
                    // display only if larger than 1
                    return this.y > 1 ? '<b>' + this.point.name + ':</b> ' + this.y : null;
                }
            }
        }]
    });
});

</script>
<?php
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);
echo 'Page generated in '.$total_time.' seconds.';
?>
</body>
</html>

