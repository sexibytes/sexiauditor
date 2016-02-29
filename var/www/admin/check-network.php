<?php require("session.php"); ?>
<?php
$title = "Network Checks";
$additionalStylesheet = array(  'css/jquery.dataTables.min.css',
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

$scannedDirectories = array_values(array_diff(scandir($xmlStartPath, SCANDIR_SORT_DESCENDING), array('..', '.', 'latest')));
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $selectedDate = $_POST["selectedDate"];
    foreach ($scannedDirectories as $key => $value) {
        if (strpos($value, str_replace("/","",$selectedDate)) === 0) {
            $xmlSelectedPath = $value;
            break;
        }
    }
} else {
    $xmlSelectedPath = $scannedDirectories[0];
    $selectedDate = DateTime::createFromFormat('Ymd', $scannedDirectories[0])->format('Y/m/d');
}

$xmlSettingsFile = "/var/www/admin/conf/settings.xml";
if (is_readable($xmlSettingsFile)) {
    $xmlSettings = simplexml_load_file($xmlSettingsFile);
} else {
    exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Error:</span> File ' . $xmlSettingsFile . ' is not existant or not readable</div>');
}

# hash table initialization with settings XML file
$h_settings = array();
foreach ($xmlSettings->xpath('/modules/module') as $module) { $h_settings[(string) $module->id] = (string) $module->schedule; }

$xmlSettingsFile = "/var/www/admin/conf/modulesettings.xml";
if (is_readable($xmlSettingsFile)) {
    $xmlSettings = simplexml_load_file($xmlSettingsFile);
} else {
    exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Error:</span> File ' . $xmlSettingsFile . ' is not existant or not readable</div>');
}

# hash table initialization with settings XML file
$h_modulesettings = array();
foreach ($xmlSettings->xpath('/settings/setting') as $setting) { $h_modulesettings[(string) $setting->id] = (string) $setting->value; }

?>
    <div style="padding-top: 10px; padding-bottom: 10px;" class="container">
	<div class="col-lg-12">
		<div class="col-lg-10 alert alert-info" style="margin-top: 20px; text-align: center;">
			<h1 style="margin-top: 10px;">Network Checks on <?php echo DateTime::createFromFormat('Y/m/d', $selectedDate)->format('l jS F Y'); ?></h1>
		</div>

		<div class="alert col-lg-2">
			<form action="check-network.php" style="margin-top: 5px;" method="post">
			<div class="form-group" style="margin-bottom: 5px;">
				<!-- <label for="datetimepicker11">Select your date:</label> -->
				<div class='input-group date' id='datetimepicker11'>
					<input type='text' class="form-control" name="selectedDate" readonly />
					<span class="input-group-addon">
						<span class="glyphicon glyphicon-calendar">
						</span>
					</span>
				</div>
			</div>
			<button type="submit" class="btn btn-default" style="width: 100%">Select this date</button>
			<script type="text/javascript">
			$(function () {
				$('#datetimepicker11').datetimepicker({
					ignoreReadonly: true,
					format: 'YYYY/MM/DD',
					showTodayButton: true,
					defaultDate: <?php echo "\"$selectedDate\""; ?>,
					enabledDates: [
<?php
    foreach ($scannedDirectories as $xmlDirectory) {
        echo '                  "' . DateTime::createFromFormat('Ymd', $xmlDirectory)->format('Y/m/d H:i') . '",' . "\n";
    }
?>
                ]
            });
        });
			</script>
			</form>
		</div>
	</div>
<?php
    # TODO
    # initialise objects if at least one module is active
    # Display bootstrap Success Panel if no result per module instead of empty dataTable
    $xmlDVPortgroupFile = "$xmlStartPath$xmlSelectedPath/distributedvirtualportgroups-global.xml";
    if (is_readable($xmlDVPortgroupFile)) {
        $xmlDVPortgroup = simplexml_load_file($xmlDVPortgroupFile);
    } else {
        exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Error:</span> File ' . $xmlDVPortgroupFile . ' is not existant or not readable</div>');
    }
	$openPortThreshold = (int) $h_modulesettings['networkDVSVSSportsfree'];
?>
<?php if($h_settings['networkDVSportsfree'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php
    $impactedDVPG = $xmlDVPortgroup->xpath("/distributedvirtualportgroups/distributedvirtualportgroup[openports<" . $openPortThreshold . "]");
    if (count($impactedDVPG) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> DVS ports free</h2>
        <div class="alert alert-warning" role="alert"><i>The following Distributed vSwitch Port Groups have less than <?php echo $openPortThreshold; ?> open port(s) left.</i></div>
        <div class="col-lg-12">
        <table id="portgroupOpenPort" class="table table-hover">
            <thead><tr>
                <th>Portgroup Name</th>
                <th>Auto Expand</th>
                <th>NumPorts</th>
                <th>OpenPorts</th>
                <th>PercentFree</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedDVPG as $dvportgroup) {
        echo '            <tr><td>' . $dvportgroup->name . '</td><td>' . ($dvportgroup->autoexpand ? '<i class="glyphicon glyphicon-ok alarm-green"></i>' : '<i class="glyphicon glyphicon-remove alarm-red"></i>') . '</td><td>' . $dvportgroup->numports . '</td><td>' . $dvportgroup->openports . '</td><td>' . (($dvportgroup->numports > 0) ? round(100 * ($dvportgroup->openports / $dvportgroup->numports)) : 0) . ' %</td><td>' . $dvportgroup->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#portgroupOpenPort').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
                "columnDefs": [
                    { "orderable": false, className: "dt-body-center", "targets": [ 1 ] },
                    { className: "dt-body-center", "targets": [ 2, 3, 4 ] }
                ]

            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> DVS ports free <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> DVS profile</h2>
	</div>
<?php require("footer.php"); ?>
