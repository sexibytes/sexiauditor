<?php require("session.php"); ?>
<?php
$title = "Host Checks";
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
?>
    <div style="padding-top: 10px; padding-bottom: 10px;" class="container">
	<div class="row">
		<div class="col-lg-10 alert alert-info" style="margin-top: 20px; text-align: center;">
			<h1 style="margin-top: 10px;">Cluster Checks on <?php echo DateTime::createFromFormat('Y/m/d', $selectedDate)->format('l jS F Y'); ?></h1>
		</div>

		<div class="alert col-lg-2">
			<form action="check-cluster.php" style="margin-top: 5px;" method="post">
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
	<h2>Host LUN Path Dead</h2>
	<h2>Host Profile Compliance</h2>
	<h2>Host LocalSwapDatastore Compliance</h2>
	<h2>Host SSH/shell/lockdown check</h2>
	<h2>Host NTP Check</h2>
	<h2>Host DNS Check</h2>
	<h2>Host Syslog Check</h2>
	<h2>Host configuration issues</h2>
	<h2>Host Alarms</h2>
	<h2>Host Hardware Status</h2>
	<h2>Host Reboot required</h2>
	<h2>Host FQDN/hostname mismatch</h2>
	<h2>Host in maintenance mode</h2>
	<h2>Host ballooning/zip/swap</h2>
	<h2>Host PowerManagement Policy</h2>
	<h2>+ Host Bundle backup</h2>
<?php require("footer.php"); ?>