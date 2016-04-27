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
    $xmlSelectedPath = "latest";
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

$check = new SexiCheck();
?>
    <div style="padding-top: 10px; padding-bottom: 10px;" class="container">
	<div class="row">
		<div class="col-lg-10 alert alert-info" style="margin-top: 20px; text-align: center;">
			<h1 style="margin-top: 10px;">Host Checks on <?php echo DateTime::createFromFormat('Y/m/d', $selectedDate)->format('l jS F Y'); ?></h1>
		</div>

		<div class="alert col-lg-2">
			<form action="check-host.php" style="margin-top: 5px;" method="post">
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
	<h2>Host LUN Path Dead</h2>
	<h2>Host Profile Compliance</h2>
	<h2>Host LocalSwapDatastore Compliance</h2>

<?php
  if($h_settings['hostSshShellLockdown'] != 'off' && $h_settings['inventory'] != 'off') {
    $currentSshPolicy = $h_modulesettings['hostSSHPolicy'];
    $currentShellPolicy = $h_modulesettings['hostShellPolicy'];
    $check->displayCheck([  'xmlFile' => "$xmlStartPath$xmlSelectedPath/hosts-global.xml",
                            'xpathQuery' => "/hosts/host[(ssh_policy!='$currentSshPolicy' and ssh_policy!='') or (shell_policy!='$currentShellPolicy' and shell_policy!='')]",
                            'title' => 'Host SSH-Shell-lockdown check',
                            'description' => 'The following displays host that not match the selected ssh/shell/lockdown policy.',
                            'thead' => array('Name', 'Cluster', 'SSH Policy', 'Desired SSH Policy', 'Shell Policy', 'Desired Shell Policy', 'Lockdown', 'vCenter'),
                            'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->cluster."</td>"', '"<td>".$this->servicePolicyChoice[(string) $entry->ssh_policy]."</td>"', '"<td>'.$servicePolicyChoice[$currentSshPolicy].'</td>"', '"<td>".$this->servicePolicyChoice[(string) $entry->shell_policy]."</td>"', '"<td>'.$servicePolicyChoice[$currentShellPolicy].'</td>"', '"<td></td>"', '"<td>".$entry->vcenter."</td>"')]);
  }

  if($h_settings['hostNTPCheck'] != 'off' && $h_settings['inventory'] != 'off') {
    $check->displayCheck([  'xmlFile' => "$xmlStartPath$xmlSelectedPath/hosts-global.xml",
                            'xpathQuery' => "/hosts/host",
                            'title' => 'Host NTP Check',
                            'description' => 'The following hosts have mismatch NTP configuration.',
                            'typeCheck' => 'majorityPerCluster',
                            'majorityProperty' => 'ntpservers',
                            'thead' => array('Cluster Name', 'Majority NTP', 'Host Name', 'NTP Servers', 'vCenter'),
                            'tbody' => array('"<td>" . $entry->cluster . "</td>"', '"<td>" . $majorityGroup . "</td>"', '"<td>" . $entry->name . "</td>"', '"<td>" . str_replace(";", "<br />", $entry->ntpservers) . "</td>"', '"<td>" . $entry->vcenter . "</td>"')]);
  }

  if($h_settings['hostDNSCheck'] != 'off' && $h_settings['inventory'] != 'off') {
    $check->displayCheck([  'xmlFile' => "$xmlStartPath$xmlSelectedPath/hosts-global.xml",
                            'xpathQuery' => "/hosts/host",
                            'title' => 'Host DNS Check',
                            'description' => 'The following hosts have mismatch DNS configuration.',
                            'typeCheck' => 'majorityPerCluster',
                            'majorityProperty' => 'dnsservers',
                            'thead' => array('Cluster Name', 'Majority DNS', 'Host Name', 'DNS Servers', 'vCenter'),
                            'tbody' => array('"<td>" . $entry->cluster . "</td>"', '"<td>" . $majorityGroup . "</td>"', '"<td>" . $entry->name . "</td>"', '"<td>" . str_replace(";", "<br />", $entry->dnsservers) . "</td>"', '"<td>" . $entry->vcenter . "</td>"')]);
  }

  if($h_settings['hostSyslogCheck'] != 'off' && $h_settings['inventory'] != 'off') {
    $check->displayCheck([  'xmlFile' => "$xmlStartPath$xmlSelectedPath/hosts-global.xml",
                            'xpathQuery' => "/hosts/host",
                            'title' => 'Host Syslog Check',
                            'description' => 'The following hosts do not have the correct Syslog settings which may cause issues if ESXi hosts experience issues and logs need to be investigated.',
                            'typeCheck' => 'majorityPerCluster',
                            'majorityProperty' => 'syslog_target',
                            'thead' => array('Cluster Name', 'Majority Syslog', 'Host Name', 'Syslog Target', 'vCenter'),
                            'tbody' => array('"<td>" . $entry->cluster . "</td>"', '"<td>" . $majorityGroup . "</td>"', '"<td>" . $entry->name . "</td>"', '"<td>" . $entry->syslog_target . "</td>"', '"<td>" . $entry->vcenter . "</td>"')]);
  }

  if($h_settings['hostConfigurationIssues'] != 'off') {
    $check->displayCheck([  'xmlFile' => "$xmlStartPath$xmlSelectedPath/configurationissues-global.xml",
                            'xpathQuery' => "/configurationissues/configurationissue",
                            'title' => 'Host configuration issues',
                            'description' => 'The following configuration issues have been registered against Hosts in vCenter.',
                            'thead' => array('Issue', 'Name', 'Cluster', 'vCenter'),
                            'tbody' => array('"<td>" . $entry->configissue . "</td>"', '"<td>" . $entry->name . "</td>"', '"<td>" . $entry->cluster . "</td>"', '"<td>" . $entry->vcenter . "</td>"')]);
  }

  if($h_settings['alarms'] != 'off') {
    $check->displayCheck([  'xmlFile' => "$xmlStartPath$xmlSelectedPath/alarms-global.xml",
                            'xpathQuery' => "/alarms/alarm[entity_type='HostSystem']",
                            'title' => 'Host Alarms',
                            'description' => 'This module will display triggered alarms on Host objects level with status and time of creation.',
                            'thead' => array('Status', 'Alarm', 'Date', 'Name', 'vCenter'),
                            'tbody' => array('"<td>" . $this->alarmStatus[(string) $entry->status] . "</td>"', '"<td>" . $entry->name . "</td>"', '"<td>" . $entry->time . "</td>"', '"<td>" . $entry->entity . "</td>"', '"<td>" . $entry->vcenter . "</td>"'),
                            'order' => '[ 1, "asc" ]',
                            'columnDefs' => '{ "orderable": false, className: "dt-body-right", "targets": [ 0 ] }']);
  }

  if($h_settings['hostHardwareStatus'] != 'off') {
    $check->displayCheck([  'xmlFile' => "$xmlStartPath$xmlSelectedPath/hardwarestatus-global.xml",
                            'xpathQuery' => "/hardwarestatus/hardwarestate",
                            'title' => 'Host Hardware Status',
                            'description' => 'Details can be found in the Hardware Status tab.',
                            'thead' => array('State', 'Issue', 'Type', 'Name', 'vCenter'),
                            'tbody' => array('"<td>" . $this->alarmStatus[(string) $entry->issuestate] . "</td>"', '"<td>" . $entry->issuename . "</td>"', '"<td>" . $entry->issuetype . "</td>"', '"<td>" . $entry->name . "</td>"', '"<td>" . $entry->vcenter . "</td>"'),
                            'order' => '[ 3, "asc" ]',
                            'columnDefs' => '{ "orderable": false, className: "dt-body-right", "targets": [ 0 ] }']);
  }

  if($h_settings['hostRebootrequired'] != 'off' && $h_settings['inventory'] != 'off') {
    $check->displayCheck([  'xmlFile' => "$xmlStartPath$xmlSelectedPath/hosts-global.xml",
                            'xpathQuery' => "/hosts/host[rebootrequired='true']",
                            'title' => 'Host Reboot required',
                            'description' => 'The following displays host that required reboot (after some configuration update for instance).',
                            'thead' => array('Name', 'vCenter'),
                            'tbody' => array('"<td>" . $entry->name . "</td>"', '"<td>" . $entry->vcenter . "</td>"')]);
  }

  if($h_settings['hostFQDNHostnameMismatch'] != 'off' && $h_settings['inventory'] != 'off') {
    $check->displayCheck([  'xmlFile' => "$xmlStartPath$xmlSelectedPath/hosts-global.xml",
                            'xpathQuery' => "/hosts/host[not(starts-with(translate(name, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), translate(hostname, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')))]",
                            'title' => 'Host FQDN and hostname mismatch',
                            'description' => 'The following displays host that have FQDN and hostname mismatch.',
                            'thead' => array('FQDN', 'Hostname', 'Cluster', 'vCenter'),
                            'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->hostname."</td>"', '"<td>".$entry->cluster."</td>"', '"<td>".$entry->vcenter."</td>"')]);
  }

  if($h_settings['hostMaintenanceMode'] != 'off' && $h_settings['inventory'] != 'off') {
    $check->displayCheck([  'xmlFile' => "$xmlStartPath$xmlSelectedPath/hosts-global.xml",
                            'xpathQuery' => "/hosts/host[inmaintenancemode='true']",
                            'title' => 'Host in maintenance mode',
                            'description' => 'The following displays host that are in maintenance mode.',
                            'thead' => array('Name', 'Cluster', 'vCenter'),
                            'tbody' => array('"<td><img src=\"images/vc-hostInMaintenance.gif\"> ".$entry->name."</td>"', '"<td>".$entry->cluster."</td>"', '"<td>".$entry->vcenter."</td>"')]);
  }

  if($h_settings['hostPowerManagementPolicy'] != 'off' && $h_settings['inventory'] != 'off') {
    $currentPolicy = $h_modulesettings['powerSystemInfo'];
    $check->displayCheck([  'xmlFile' => "$xmlStartPath$xmlSelectedPath/hosts-global.xml",
                            'xpathQuery' => "/hosts/host[powerpolicy!='$currentPolicy' and powerpolicy!='']",
                            'title' => 'Host PowerManagement Policy',
                            'description' => 'The following displays host that not match the selected power management policy.',
                            'thead' => array('Name', 'Cluster', 'Power Policy', 'Desired Power Policy', 'vCenter'),
                            'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->cluster."</td>"', '"<td>".$this->powerChoice[(string) $entry->powerpolicy]."</td>"', '"<td>'.$powerChoice[$currentPolicy].'</td>"', '"<td>".$entry->vcenter."</td>"')]);
  }
  ?>

	<h2>Host ballooning/zip/swap ==> perfManager?</h2>

  	</div>
<?php require("footer.php"); ?>
