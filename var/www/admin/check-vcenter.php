<?php require("session.php"); ?>
<?php
$title = "vCenter Checks";
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
	<div class="row">
		<div class="col-lg-10 alert alert-info" style="margin-top: 20px; text-align: center;">
			<h1 style="margin-top: 10px;">vCenter Checks on <?php echo DateTime::createFromFormat('Y/m/d', $selectedDate)->format('l jS F Y'); ?></h1>
		</div>
	
		<div class="alert col-lg-2">
			<form action="check-vcenter.php" style="margin-top: 5px;" method="post">
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


?>
<?php if($h_settings['vcSessionAge'] != 'off'): ?>
<?php
    $xmlSessionFile = "$xmlStartPath$xmlSelectedPath/sessions-global.xml";
    if (is_readable($xmlSessionFile)):
        $xmlSession = simplexml_load_file($xmlSessionFile);
		$xpathFullSession = $xmlSession->xpath("/sessions/session");
		if (count($xpathFullSession) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> Session Age</h2>
        <div class="alert alert-warning" role="alert"><i>The following displays vCenter sessions that exceed the maximum session age (<?php echo $h_modulesettings['vcSessionAge']; ?> days).</i></div>
        <div class="col-lg-12">
        <table id="vcSessionAge" class="table table-hover">
            <thead><tr>
                <th>SessionAge</th>
                <th>lastActiveTime</th>
                <th>userName</th>
                <th>ipAddress</th>
                <th>userAgent</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($xpathFullSession as $session) {
		$sessionAge = DateTime::createFromFormat('Y-m-d', substr($session->lastActiveTime, 0, 10))->diff(new DateTime("now"))->format('%a');
		if ($sessionAge > $h_modulesettings['vcSessionAge']) {
			if ($session->userAgent == 'VI Perl') {
				$sessionUserAgent = '<img src="images/logo-perl.png" title="VI Perl" />';
			} elseif (preg_match("/^VMware VI Client/", $session->userAgent)) {
				$sessionUserAgent = '<img src="images/logo-viclient.png" title="VMware VI Client" />';
			} elseif (preg_match("/^Mozilla/", $session->userAgent)) {
				$sessionUserAgent = '<img src="images/logo-chrome.png" title="Browser" />';
			} elseif (preg_match("/^VMware vim-java/", $session->userAgent)) {
				$sessionUserAgent = '<img src="images/logo-java.png" title="VMware vim-java" />';
			} elseif (preg_match("/^PowerCLI/", $session->userAgent)) {
				$sessionUserAgent = '<img src="images/logo-powercli.png" title="PowerCLI" />';
			}
			echo '            <tr><td>' . $sessionAge . '</td><td>' . $session->lastActiveTime . '</td><td>' . $session->userName . '</td><td>' . $session->ipAddress . '</td><td>' . $sessionUserAgent . '</td><td>' . $session->vcenter . '</td></tr>';
		}
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#vcSessionAge').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
				"order": [[ 0, "desc" ]],
                "columnDefs": [
                    { "orderable": false, className: "dt-body-center", "targets": [ 4 ] }
                ]
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> Session Age <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php else: ?>
    <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Error:</span> File <?php echo $xmlSessionFile; ?> is not existant or not readable. Please check for <a href="/admin/sandbox.php">module selection</a> and/or wait for scheduler</div>
<?php endif; /* endif check file */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['vcLicenceReport'] != 'off'): ?>
<?php
	$xmlLicenseFile = "$xmlStartPath$xmlSelectedPath/licenses-global.xml";
    if (is_readable($xmlLicenseFile)) :
        $xmlLicense = simplexml_load_file($xmlLicenseFile);
		$xpathFullLicense = $xmlLicense->xpath("/licenses/license");
		if (count($xpathFullLicense) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> License Report</h2>
        <div class="alert alert-warning" role="alert"><i>The following displays vCenter licenses.</i></div>
        <div class="col-lg-12">
        <table id="vcLicenceReport" class="table table-hover">
            <thead><tr>
                <th>name</th>
                <th>costUnit</th>
                <th>total</th>
                <th>used</th>
                <th>licenseKey</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($xpathFullLicense as $license) {
		if ($h_modulesettings['showPlainLicense'] == 'disable') {
			$licenseKey = substr($license->licenseKey, 0, 5) . "-#####-#####-#####-" . substr($license->licenseKey, -5);
		} else {
			$licenseKey = $license->licenseKey;
		}
		echo '            <tr><td>' . $license->name . '</td><td>' . $license->costUnit . '</td><td>' . $license->total . '</td><td>' . $license->used . '</td><td>' . $licenseKey . '</td><td>' . $license->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#vcLicenceReport').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> License Report <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php else: ?>
    <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Error:</span> File <?php echo $xmlLicenseFile; ?> is not existant or not readable. Please check for <a href="/admin/sandbox.php">module selection</a> and/or wait for scheduler</div>
<?php endif; /* endif check file */ ?>
<?php endif; /* endif module */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> Permission report <small><?php echo rand_line($achievementFile); ?></small></h2>
	</div>
<?php require("footer.php"); ?>
