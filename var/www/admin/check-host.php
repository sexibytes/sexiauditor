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

# Main class loading
try {
  $check = new SexiCheck();
} catch (Exception $e) {
  # Any exception will be ending the script, we want exception-free run
  exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> ' . $e->getMessage() . '</div>');
}

if($check->getModuleSchedule('hostLUNPathDead') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "hosts-global.xml",
                          'xpathQuery' => "/hosts/host[lundeadpathcount!=0]",
                          "id" => "HOSTLUNPATHDEAD",
                          'thead' => array('Name', 'Dead LUN path', 'LUN Path', 'Cluster', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->deadlunpathcount."</td>"', '"<td>".$entry->lunpathcount."</td>"', '"<td>".$entry->cluster."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}
?>
    <h2>Host Profile Compliance</h2>
    <h2>Host LocalSwapDatastore Compliance</h2>
<?php
if($check->getModuleSchedule('hostSshShell') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $currentSshPolicy = $check->getConfig('hostSSHPolicy');
  $currentShellPolicy = $check->getConfig('hostShellPolicy');
  $check->displayCheck([  'xmlFile' => "hosts-global.xml",
                          'xpathQuery' => "/hosts/host[(ssh_policy!='$currentSshPolicy' and ssh_policy!='') or (shell_policy!='$currentShellPolicy' and shell_policy!='')]",
                          "id" => "HOSTSSHSHELL",
                          'thead' => array('Name', 'Cluster', 'SSH Policy', 'Desired SSH Policy', 'Shell Policy', 'Desired Shell Policy', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->cluster."</td>"', '"<td>".$this->servicePolicyChoice[(string) $entry->ssh_policy]."</td>"', '"<td>'.$servicePolicyChoice[$currentSshPolicy].'</td>"', '"<td>".$this->servicePolicyChoice[(string) $entry->shell_policy]."</td>"', '"<td>'.$servicePolicyChoice[$currentShellPolicy].'</td>"', '"<td>".$entry->vcenter."</td>"')]);
}

if($check->getModuleSchedule('hostNTPCheck') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "hosts-global.xml",
                          'xpathQuery' => "/hosts/host",
                          "id" => "HOSTNTPCHECK",
                          'typeCheck' => 'majorityPerCluster',
                          'majorityProperty' => 'ntpservers',
                          'thead' => array('Cluster Name', 'Majority NTP', 'Host Name', 'NTP Servers', 'vCenter'),
                          'tbody' => array('"<td>" . $entry->cluster . "</td>"', '"<td>" . str_replace(";", "<br />", $majorityGroup) . "</td>"', '"<td>" . $entry->name . "</td>"', '"<td>" . str_replace(";", "<br />", $entry->ntpservers) . "</td>"', '"<td>" . $entry->vcenter . "</td>"')]);
}

if($check->getModuleSchedule('hostDNSCheck') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "hosts-global.xml",
                          'xpathQuery' => "/hosts/host",
                          "id" => "HOSTDNSCHECK",
                          'typeCheck' => 'majorityPerCluster',
                          'majorityProperty' => 'dnsservers',
                          'thead' => array('Cluster Name', 'Majority DNS', 'Host Name', 'DNS Servers', 'vCenter'),
                          'tbody' => array('"<td>" . $entry->cluster . "</td>"', '"<td>" . str_replace(";", "<br />", $majorityGroup) . "</td>"', '"<td>" . $entry->name . "</td>"', '"<td>" . str_replace(";", "<br />", $entry->dnsservers) . "</td>"', '"<td>" . $entry->vcenter . "</td>"')]);
}

if($check->getModuleSchedule('hostSyslogCheck') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "hosts-global.xml",
                          'xpathQuery' => "/hosts/host",
                          "id" => "HOSTSYSLOGCHECK",
                          'typeCheck' => 'majorityPerCluster',
                          'majorityProperty' => 'syslog_target',
                          'thead' => array('Cluster Name', 'Majority Syslog', 'Host Name', 'Syslog Target', 'vCenter'),
                          'tbody' => array('"<td>" . $entry->cluster . "</td>"', '"<td>" . $majorityGroup . "</td>"', '"<td>" . $entry->name . "</td>"', '"<td>" . $entry->syslog_target . "</td>"', '"<td>" . $entry->vcenter . "</td>"')]);
}

if($check->getModuleSchedule('hostConfigurationIssues') != 'off') {
  $check->displayCheck([  'xmlFile' => "configurationissues-global.xml",
                          'xpathQuery' => "/configurationissues/configurationissue",
                          "id" => "HOSTCONFIGURATIONISSUES",
                          'thead' => array('Issue', 'Name', 'Cluster', 'vCenter'),
                          'tbody' => array('"<td>" . $entry->configissue . "</td>"', '"<td>" . $entry->name . "</td>"', '"<td>" . $entry->cluster . "</td>"', '"<td>" . $entry->vcenter . "</td>"')]);
}

if($check->getModuleSchedule('alarms') != 'off') {
  $check->displayCheck([  'xmlFile' => "alarms-global.xml",
                          'xpathQuery' => "/alarms/alarm[entity_type='HostSystem']",
                          "id" => "ALARMSHOST",
                          'thead' => array('Status', 'Alarm', 'Date', 'Name', 'vCenter'),
                          'tbody' => array('"<td>" . $this->alarmStatus[(string) $entry->status] . "</td>"', '"<td>" . $entry->name . "</td>"', '"<td>" . $entry->time . "</td>"', '"<td>" . $entry->entity . "</td>"', '"<td>" . $entry->vcenter . "</td>"'),
                          'order' => '[ 1, "asc" ]',
                          'columnDefs' => '{ "orderable": false, className: "dt-body-right", "targets": [ 0 ] }']);
}

if($check->getModuleSchedule('hostHardwareStatus') != 'off') {
  $check->displayCheck([  'xmlFile' => "hardwarestatus-global.xml",
                          'xpathQuery' => "/hardwarestatus/hardwarestate",
                          "id" => "HOSTHARDWARESTATUS",
                          'thead' => array('State', 'Issue', 'Type', 'Name', 'vCenter'),
                          'tbody' => array('"<td>" . $this->alarmStatus[(string) $entry->issuestate] . "</td>"', '"<td>" . $entry->issuename . "</td>"', '"<td>" . $entry->issuetype . "</td>"', '"<td>" . $entry->name . "</td>"', '"<td>" . $entry->vcenter . "</td>"'),
                          'order' => '[ 3, "asc" ]',
                          'columnDefs' => '{ "orderable": false, className: "dt-body-right", "targets": [ 0 ] }']);
}

if($check->getModuleSchedule('hostRebootrequired') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "hosts-global.xml",
                          'xpathQuery' => "/hosts/host[rebootrequired='true']",
                          "id" => "HOSTREBOOTREQUIRED",
                          'thead' => array('Name', 'vCenter'),
                          'tbody' => array('"<td>" . $entry->name . "</td>"', '"<td>" . $entry->vcenter . "</td>"')]);
}

if($check->getModuleSchedule('hostFQDNHostnameMismatch') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "hosts-global.xml",
                          'xpathQuery' => "/hosts/host[not(starts-with(translate(name, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), translate(hostname, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')))]",
                          "id" => "HOSTFQDNHOSTNAMEMISMATCH",
                          'thead' => array('FQDN', 'Hostname', 'Cluster', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->hostname."</td>"', '"<td>".$entry->cluster."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}

if($check->getModuleSchedule('hostMaintenanceMode') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "hosts-global.xml",
                          'xpathQuery' => "/hosts/host[inmaintenancemode='true']",
                          "id" => "HOSTMAINTENANCEMODE",
                          'thead' => array('Name', 'Cluster', 'vCenter'),
                          'tbody' => array('"<td><img src=\"images/vc-hostInMaintenance.gif\"> ".$entry->name."</td>"', '"<td>".$entry->cluster."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}

if($check->getModuleSchedule('hostPowerManagementPolicy') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $currentPolicy = $check->getConfig('powerSystemInfo');
  $check->displayCheck([  'xmlFile' => "hosts-global.xml",
                          'xpathQuery' => "/hosts/host[powerpolicy!='$currentPolicy' and powerpolicy!='']",
                          "id" => "HOSTPOWERMANAGEMENTPOLICY",
                          'thead' => array('Name', 'Cluster', 'Power Policy', 'Desired Power Policy', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->cluster."</td>"', '"<td>".$this->powerChoice[(string) $entry->powerpolicy]."</td>"', '"<td>'.$powerChoice[$currentPolicy].'</td>"', '"<td>".$entry->vcenter."</td>"')]);
}
  ?>
    <h2>Host ballooning/zip/swap ==> perfManager?</h2>
  </div>
<?php require("footer.php"); ?>
