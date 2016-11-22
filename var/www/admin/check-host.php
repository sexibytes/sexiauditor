<?php
require("session.php");
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
                            'js/bootstrap-datetimepicker.js',
                            'js/echarts-all-english-v2.js');
require("header.php");
require("helper.php");

try
{
  
  # Main class loading
  $check = new SexiCheck();
  # Header generation
  $check->displayHeader($_SERVER['SCRIPT_NAME']);
  
}
catch (Exception $e)
{
  
  # Any exception will be ending the script, we want exception-free run
  # CSS hack for navbar margin removal
  echo '  <style>#wrapper { margin-bottom: 0px !important; }</style>'."\n";
  require("exception.php");
  exit;
  
} # END try

if($check->getModuleSchedule('hostLUNPathDead') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.host_name, main.deadlunpathcount, main.lunpathcount, c.cluster_name as cluster, v.vcname as vcenter FROM hosts main INNER JOIN vcenters v ON main.vcenter = v.id INNER JOIN clusters c ON main.cluster = c.id WHERE main.deadlunpathcount > 0",
                          'sqlQueryGroupBy' => "main.moref, main.vcenter",
                          "id" => "HOSTLUNPATHDEAD",
                          'thead' => array('Name', 'Dead LUN path', 'LUN Path', 'Cluster', 'vCenter'),
                          'tbody' => array('"<td>".$entry["host_name"]."</td>"', '"<td>".$entry["deadlunpathcount"]."</td>"', '"<td>".$entry["lunpathcount"]."</td>"', '"<td>".$entry["cluster"]."</td>"', '"<td>".$entry["vcenter"]."</td>"')]);
}
?>
    <h2 class="not-available"><i class="glyphicon glyphicon-remove-sign"></i> Module 'Host Profile Compliance' not yet available <small>(Soon)</small></h2>
    <h2 class="not-available"><i class="glyphicon glyphicon-remove-sign"></i> Module 'Host LocalSwapDatastore Compliance' not yet available <small>(Soon)</small></h2>
<?php

if ($check->getModuleSchedule('hostSshShell') != 'off' && $check->getModuleSchedule('inventory') != 'off')
{
  
  $currentSshPolicy = $check->getConfig('hostSSHPolicy');
  $currentShellPolicy = $check->getConfig('hostShellPolicy');
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM hosts main INNER JOIN vcenters v ON main.vcenter = v.id INNER JOIN clusters c ON main.cluster = c.id WHERE main.ssh_policy <> '$currentSshPolicy' OR main.shell_policy <> '$currentShellPolicy' AND main.connectionState LIKE 'connected' AND main.id IN (SELECT MAX(id) FROM hosts GROUP BY moref,vcenter)",
                          'sqlQueryGroupBy' => "main.moref, main.vcenter",
                          "id" => "HOSTSSHSHELL",
                          "typeCheck" => 'ssp',
                          'thead' => array('Name', 'SSH Policy', 'Desired SSH Policy', 'Shell Policy', 'Desired Shell Policy', 'vCenter')]);
                          
} # END if ($check->getModuleSchedule('hostSshShell') != 'off' && $check->getModuleSchedule('inventory') != 'off')

if($check->getModuleSchedule('hostNTPCheck') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT c.id as clusterId, c.cluster_name as cluster, main.host_name, main.ntpservers, v.vcname as vcenter FROM hosts main INNER JOIN clusters c ON main.cluster = c.id INNER JOIN vcenters v ON main.vcenter = v.id WHERE c.id <> 1 AND main.connectionState LIKE 'connected' AND main.id IN (SELECT MAX(id) FROM hosts GROUP BY moref,vcenter)",
                          'sqlQueryGroupBy' => "main.moref, main.vcenter",
                          "id" => "HOSTNTPCHECK",
                          'typeCheck' => 'majorityPerCluster',
                          'majorityProperty' => 'ntpservers',
                          'thead' => array('Cluster Name', 'Majority NTP', 'Host Name', 'NTP Servers', 'vCenter'),
                          'tbody' => array('"<td>" . $entry["cluster"] . "</td>"', '"<td>" . str_replace(";", "<br />", $hMajority[$entry["clusterId"]]) . "</td>"', '"<td>" . $entry["host_name"] . "</td>"', '"<td>" . str_replace(";", "<br />", $entry["ntpservers"]) . "</td>"', '"<td>" . $entry["vcenter"] . "</td>"')]);
}

if($check->getModuleSchedule('hostDNSCheck') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT c.id as clusterId, c.cluster_name as cluster, main.host_name, main.dnsservers, v.vcname as vcenter FROM hosts main INNER JOIN clusters c ON main.cluster = c.id INNER JOIN vcenters v ON main.vcenter = v.id WHERE c.id <> 1 AND main.connectionState LIKE 'connected' AND main.id IN (SELECT MAX(id) FROM hosts GROUP BY moref,vcenter)",
                          'sqlQueryGroupBy' => "main.moref, main.vcenter",
                          "id" => "HOSTDNSCHECK",
                          'typeCheck' => 'majorityPerCluster',
                          'majorityProperty' => 'dnsservers',
                          'thead' => array('Cluster Name', 'Majority DNS', 'Host Name', 'DNS Servers', 'vCenter'),
                          'tbody' => array('"<td>" . $entry["cluster"] . "</td>"', '"<td>" . str_replace(";", "<br />", $hMajority[$entry["clusterId"]]) . "</td>"', '"<td>" . $entry["host_name"] . "</td>"', '"<td>" . str_replace(";", "<br />", $entry["dnsservers"]) . "</td>"', '"<td>" . $entry["vcenter"] . "</td>"')]);
}

if($check->getModuleSchedule('hostSyslogCheck') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT c.id as clusterId, c.cluster_name as cluster, main.host_name, main.syslog_target, v.vcname as vcenter FROM hosts main INNER JOIN clusters c ON main.cluster = c.id INNER JOIN vcenters v ON main.vcenter = v.id WHERE c.id <> 1 AND main.connectionState LIKE 'connected' AND main.id IN (SELECT MAX(id) FROM hosts GROUP BY moref,vcenter)",
                          'sqlQueryGroupBy' => "main.moref, main.vcenter",
                          "id" => "HOSTSYSLOGCHECK",
                          'typeCheck' => 'majorityPerCluster',
                          'majorityProperty' => 'syslog_target',
                          'thead' => array('Cluster Name', 'Majority Syslog', 'Host Name', 'Syslog Target', 'vCenter'),
                          'tbody' => array('"<td>" . $entry["cluster"] . "</td>"', '"<td>" . $hMajority[$entry["clusterId"]] . "</td>"', '"<td>" . $entry["host_name"] . "</td>"', '"<td>" . $entry["syslog_target"] . "</td>"', '"<td>" . $entry["vcenter"] . "</td>"')]);
}

if($check->getModuleSchedule('hostConfigurationIssues') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.configissue, h.host_name, cl.cluster_name as cluster, v.vcname as vcenter FROM configurationissues main INNER JOIN hosts h ON main.host = h.id INNER JOIN clusters cl ON h.cluster = cl.id INNER JOIN vcenters v ON h.vcenter = v.id WHERE h.connectionState LIKE 'connected' AND h.id IN (SELECT MAX(id) FROM hosts GROUP BY moref,vcenter)",
                          'sqlQueryGroupBy' => "main.host, main.configissue",
                          "id" => "HOSTCONFIGURATIONISSUES",
                          'thead' => array('Issue', 'Name', 'Cluster', 'vCenter'),
                          'tbody' => array('"<td>" . $entry["configissue"] . "</td>"', '"<td>" . $entry["host_name"] . "</td>"', '"<td>" . $entry["cluster"] . "</td>"', '"<td>" . $entry["vcenter"] . "</td>"')]);
}

if($check->getModuleSchedule('alarms') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.alarm_name, main.status, main.time, main.entityMoRef, v.vcname as vcenter, h.host_name as entity FROM alarms main INNER JOIN vcenters v ON main.vcenter = v.id INNER JOIN hosts h ON main.entityMoRef = h.moref WHERE main.entityMoRef LIKE 'HostSystem%'",
                          'sqlQueryGroupBy' => "main.entityMoRef",
                          "id" => "ALARMSHOST",
                          'thead' => array('Status', 'Alarm', 'Date', 'Name', 'vCenter'),
                          'tbody' => array('"<td>" . $this->alarmStatus[(string) $entry["status"]] . "</td>"', '"<td>" . $entry["alarm_name"] . "</td>"', '"<td>" . $entry["time"] . "</td>"', '"<td>" . $entry["entity"] . "</td>"', '"<td>" . $entry["vcenter"] . "</td>"'),
                          'order' => '[ 1, "asc" ]',
                          'columnDefs' => '{ "orderable": false, className: "dt-body-right", "targets": [ 0 ] }']);
}

if($check->getModuleSchedule('hostHardwareStatus') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.issuename, main.issuestate, main.issuetype, h.host_name, v.vcname as vcenter FROM hardwarestatus main INNER JOIN hosts h ON main.host = h.id INNER JOIN vcenters v ON h.vcenter = v.id WHERE h.connectionState LIKE 'connected'",
                          'sqlQueryGroupBy' => "main.issuename, main.issuestate, h.host_name, v.vcname",
                          "id" => "HOSTHARDWARESTATUS",
                          'thead' => array('State', 'Issue', 'Type', 'Name', 'vCenter'),
                          'tbody' => array('"<td>" . $this->alarmStatus[(string) $entry["issuestate"]] . "</td>"', '"<td>" . $entry["issuename"] . "</td>"', '"<td>" . $entry["issuetype"] . "</td>"', '"<td>" . $entry["host_name"] . "</td>"', '"<td>" . $entry["vcenter"] . "</td>"'),
                          'order' => '[ 3, "asc" ]',
                          'columnDefs' => '{ "orderable": false, className: "dt-body-right", "targets": [ 0 ] }']);
}

if($check->getModuleSchedule('hostRebootrequired') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.host_name, c.cluster_name as cluster, v.vcname as vcenter FROM hosts main INNER JOIN vcenters v ON main.vcenter = v.id INNER JOIN clusters c ON main.cluster = c.id WHERE main.rebootrequired = 1",
                          "id" => "HOSTREBOOTREQUIRED",
                          'thead' => array('Name', 'Cluster', 'vCenter'),
                          'tbody' => array('"<td>" . $entry["host_name"] . "</td>"', '"<td>" . $entry["cluster"] . "</td>"', '"<td>" . $entry["vcenter"] . "</td>"')]);
}

if($check->getModuleSchedule('hostFQDNHostnameMismatch') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.host_name, main.hostname, c.cluster_name as cluster, v.vcname as vcenter FROM hosts main INNER JOIN vcenters v ON main.vcenter = v.id INNER JOIN clusters c ON main.cluster = c.id WHERE main.host_name NOT LIKE CONCAT(main.hostname, '%')",
                          'sqlQueryGroupBy' => "main.host_name",
                          "id" => "HOSTFQDNHOSTNAMEMISMATCH",
                          'thead' => array('FQDN', 'Hostname', 'Cluster', 'vCenter'),
                          'tbody' => array('"<td>".$entry["host_name"]."</td>"', '"<td>".$entry["hostname"]."</td>"', '"<td>".$entry["cluster"]."</td>"', '"<td>".$entry["vcenter"]."</td>"')]);
}

if($check->getModuleSchedule('hostMaintenanceMode') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.host_name, c.cluster_name as cluster, v.vcname as vcenter FROM hosts main INNER JOIN vcenters v ON main.vcenter = v.id INNER JOIN clusters c ON main.cluster = c.id WHERE main.inmaintenancemode = 1 AND main.connectionState LIKE 'connected'",
                          'sqlQueryGroupBy' => "main.vcenter, main.moref",
                          "id" => "HOSTMAINTENANCEMODE",
                          'thead' => array('Name', 'Cluster', 'vCenter'),
                          'tbody' => array('"<td><img src=\"images/vc-hostInMaintenance.gif\"> ".$entry["host_name"]."</td>"', '"<td>".$entry["cluster"]."</td>"', '"<td>".$entry["vcenter"]."</td>"')]);
}

if ($check->getModuleSchedule('hostPowerManagementPolicy') != 'off' && $check->getModuleSchedule('inventory') != 'off')
{
  
  $currentPolicy = $check->getConfig('powerSystemInfo');
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM hosts main INNER JOIN vcenters v ON main.vcenter = v.id INNER JOIN clusters c ON main.cluster = c.id WHERE main.powerpolicy <> '" . $currentPolicy . "' AND main.connectionState LIKE 'connected'",
                          'sqlQueryGroupBy' => "main.vcenter, main.moref",
                          "id" => "HOSTPOWERMANAGEMENTPOLICY",
                          "typeCheck" => 'ssp',
                          'thead' => array('Name', 'Power Policy', 'Desired Power Policy', 'vCenter')]);

} # END if ($check->getModuleSchedule('hostPowerManagementPolicy') != 'off' && $check->getModuleSchedule('inventory') != 'off')

if ($check->getModuleSchedule('hostBuildPivot') != 'off' && $check->getModuleSchedule('inventory') != 'off')
{
  
  $check->displayCheck([  'sqlQuery' => "SELECT main.esxbuild as dataKey, COUNT(*) as dataValue FROM hosts AS main WHERE true",
                          'sqlQueryGroupBy' => "main.esxbuild",
                          'sqlQueryOrderBy' => "dataValue DESC",
                          "id" => "HOSTBUILDPIVOT",
                          'typeCheck' => 'pivotTableGraphed',
                          'thead' => array('Host Build', 'Count'),
                          'order' => '[ 1, "desc" ]']);

} # END if ($check->getModuleSchedule('hostBuildPivot') != 'off' && $check->getModuleSchedule('inventory') != 'off')

  ?>
    <h2 class="not-available"><i class="glyphicon glyphicon-remove-sign"></i> Module 'Host ballooning/zip/swap' not yet available <small>(Soon)</small></h2>
  </div>
<?php require("footer.php"); ?>
