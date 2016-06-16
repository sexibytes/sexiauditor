<?php require("session.php"); ?>
<?php
$title = "Cluster Checks";
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
  # Header generation
  $check->displayHeader($_SERVER['SCRIPT_NAME']);
} catch (Exception $e) {
  # Any exception will be ending the script, we want exception-free run
  exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> ' . $e->getMessage() . '</div>');
}

if($check->getModuleSchedule('clusterConfigurationIssues') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "clusters-global.xml",
                          'xpathQuery' => "/clusters/cluster[lastconfigissue!='0']",
                          "id" => "CLUSTERCONFIGURATIONISSUES",
                          'thead' => array('Cluster Name', 'HA Status', 'Last Config Issue', 'Time', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".(($entry->dasenabled == "1") ? "<i class=\"glyphicon glyphicon-remove-sign text-danger\"></i>" : "<i class=\"glyphicon glyphicon-ok-sign text-success\"></i>")."</td>"', '"<td>".$entry->lastconfigissue."</td>"', '"<td>".$entry->lastconfigissuetime."</td>"', '"<td>".$entry->vcenter."</td>"'),
                          'columnDefs' => '{ "orderable": false, className: "dt-body-center", "targets": [ 4 ] }']);
}

if($check->getModuleSchedule('alarms') != 'off') {
  $check->displayCheck([  'xmlFile' => "alarms-global.xml",
                          'xpathQuery' => "/alarms/alarm[entity_type='ClusterComputeResource']",
                          "id" => "ALARMSCLUSTER",
                          'thead' => array('Status', 'Alarm', 'Date', 'Name', 'vCenter'),
                          'tbody' => array('"<td>" . $this->alarmStatus[(string) $entry->status] . "</td>"', '"<td>" . $entry->name . "</td>"', '"<td>" . $entry->time . "</td>"', '"<td>" . $entry->entity . "</td>"', '"<td>" . $entry->vcenter . "</td>"'),
                          'order' => '[ 1, "asc" ]',
                          'columnDefs' => '{ "orderable": false, className: "dt-body-right", "targets": [ 0 ] }']);
}

if($check->getModuleSchedule('clusterHAStatus') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "clusters-global.xml",
                          'xpathQuery' => "/clusters/cluster[dasenabled!='1']",
                          "id" => "CLUSTERHASTATUS",
                          'thead' => array('Cluster Name', 'HA Status', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td class=\"text-danger\"><i class=\"glyphicon glyphicon-remove-sign\"></i> no HA</td>"', '"<td>".$entry->vcenter."</td>"')]);
}
?>
    <h2>clusterAdmissionControl</h2>
    <h2>clusterDatastoreConsistency</h2>
    <h2>clusterMembersOvercommit</h2>

<?php
if($check->getModuleSchedule('clusterMembersVersion') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "hosts-global.xml",
                          'xpathQuery' => "/hosts/host",
                          "id" => "CLUSTERMEMBERSVERSION",
                          'typeCheck' => 'mismatchPerCluster',
                          'mismatchProperty' => 'esxbuild',
                          'thead' => array('Cluster Name', 'Compliance', 'Build Number', 'vCenter'),
                          'tbody' => array('"<td>" . $key_cluster . "</td>"', '"<td>" . $compliance . "</td>"', '"<td>" . $mismatchEntry . "</td>"', '"<td>" . $key_vcenter . "</td>"'),
                          'columnDefs' => '{ "orderable": false, className: "dt-body-center", "targets": [ 1 ] }']);
}

if($check->getModuleSchedule('clusterMembersLUNPathCountMismatch') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "hosts-global.xml",
                          'xpathQuery' => "/hosts/host",
                          "id" => "CLUSTERMEMBERSLUNPATHCOUNTMISMATCH",
                          'typeCheck' => 'majorityPerCluster',
                          'majorityProperty' => 'lunpathcount',
                          'thead' => array('Cluster Name', 'Majority Path Count', 'Host Name', 'LUN Path Count', 'vCenter'),
                          'tbody' => array('"<td>" . $entry->cluster . "</td>"', '"<td>" . $majorityGroup . "</td>"', '"<td>" . $entry->name . "</td>"', '"<td>" . $entry->lunpathcount . "</td>"', '"<td>" . $entry->vcenter . "</td>"')]);
}

if($check->getModuleSchedule('clusterCPURatio') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "clusters-global.xml",
                          'xpathQuery' => "/clusters/cluster[vp_cpuratio>". $check->getConfig('thresholdCPURatio') ."]",
                          "id" => "CLUSTERCPURATIO",
                          'thead' => array('Cluster Name', 'pCPU', 'vCPU', 'CPU ratio', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->pcpu."</td>"', '"<td>".$entry->vcpu."</td>"', '"<td>".$entry->vp_cpuratio."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}

?>
    <h2>clusterTPSSavings</h2>
    <h2>clusterAutoSlotSize</h2>
    <h2>clusterProfile</h2>
  </div>
<?php require("footer.php"); ?>
