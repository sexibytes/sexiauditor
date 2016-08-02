<?php require("session.php"); ?>
<?php
$title = "Datastore Checks";
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

try {
  # Main class loading
  $check = new SexiCheck();
  # Header generation
  $check->displayHeader($_SERVER['SCRIPT_NAME']);
} catch (Exception $e) {
  # Any exception will be ending the script, we want exception-free run
  exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> ' . $e->getMessage() . '</div>');
}

if($check->getModuleSchedule('datastoreSpacereport') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.datastore_name, main.size, main.freespace, ROUND(100*(main.freespace/main.size)) as pct_free, v.vcname as vcenter FROM datastores main INNER JOIN vcenters v ON main.vcenter = v.id WHERE ROUND(100*(main.freespace/main.size)) < ". $check->getConfig('datastoreFreeSpaceThreshold'),
                          "id" => "DATASTORESPACEREPORT",
                          'thead' => array('Datastore Name', 'Capacity', 'FreeSpace', '% Free', 'vCenter'),
                          'tbody' => array('"<td>".$entry["datastore_name"]."</td>"', '"<td>".human_filesize($entry["size"])."</td>"', '"<td>".human_filesize($entry["freespace"])."</td>"', '"<td>".$entry["pct_free"]." %</td>"', '"<td>".$entry["vcenter"]."</td>"'),
                          'columnDefs' => '{ type: "file-size", targets: [ 1, 2 ] }']);
}
?>
    <h2>Orphaned VM Files report</h2>
<?php
if($check->getModuleSchedule('datastoreOverallocation') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.datastore_name, main.size, main.freespace, main.uncommitted, ROUND(100*((main.size-main.freespace+main.uncommitted)/main.size)) as pct_overallocation, v.vcname as vcenter FROM datastores main INNER JOIN vcenters v ON main.vcenter = v.id WHERE ROUND(100*((main.size-main.freespace+main.uncommitted)/main.size)) > ". $check->getConfig('datastoreOverallocation'),
                          "id" => "DATASTOREOVERALLOCATION",
                          'thead' => array('Datastore Name', 'Capacity', 'FreeSpace', 'Uncommitted', 'Overallocation', 'vCenter'),
                          'tbody' => array('"<td>".$entry["datastore_name"]."</td>"', '"<td>".human_filesize($entry["size"])."</td>"', '"<td>".human_filesize($entry["freespace"])."</td>"', '"<td>".human_filesize($entry["uncommitted"])."</td>"', '"<td>".$entry["pct_overallocation"]." %</td>"', '"<td>".$entry["vcenter"]."</td>"'),
                          'columnDefs' => '{ type: "file-size", targets: [ 1, 2, 3 ] }']);
}

if($check->getModuleSchedule('datastoreSIOCdisabled') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.datastore_name, v.vcname as vcenter FROM datastores main INNER JOIN vcenters v ON main.vcenter = v.id WHERE main.iormConfiguration = 0",
                          "id" => "DATASTORESIOCDISABLED",
                          'thead' => array('Datastore Name', 'vCenter'),
                          'tbody' => array('"<td>".$entry["datastore_name"]."</td>"', '"<td>".$entry["vcenter"]."</td>"')]);
}

if($check->getModuleSchedule('datastoremaintenancemode') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.datastore_name, v.vcname as vcenter FROM datastores main INNER JOIN vcenters v ON main.vcenter = v.id WHERE main.maintenanceMode <> 'normal'",
                          "id" => "DATASTOREMAINTENANCEMODE",
                          'thead' => array('Datastore Name', 'vCenter'),
                          'tbody' => array('"<td>".$entry["datastore_name"]."</td>"', '"<td>".$entry["vcenter"]."</td>"')]);
}

if($check->getModuleSchedule('datastoreAccessible') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.datastore_name, v.vcname as vcenter FROM datastores main INNER JOIN vcenters v ON main.vcenter = v.id WHERE main.isAccessible = 0",
                          "id" => "DATASTOREACCESSIBLE",
                          'thead' => array('Datastore Name', 'vCenter'),
                          'tbody' => array('"<td>".$entry["datastore_name"]."</td>"', '"<td>".$entry["vcenter"]."</td>"')]);
}
?>
  </div>
<?php require("footer.php"); ?>
