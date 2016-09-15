<?php
require("session.php");
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

if ($check->getModuleSchedule('datastoreSpacereport') != 'off' && $check->getModuleSchedule('inventory') != 'off')
{
  
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM datastores AS main INNER JOIN (SELECT MAX(id), datastore_id, size, freespace, firstseen, lastseen FROM datastoreMetrics GROUP BY datastore_id) dm ON (main.id = dm.datastore_id) WHERE ROUND(100*(dm.freespace/dm.size)) < ". $check->getConfig('datastoreFreeSpaceThreshold'),
                          "id" => "DATASTORESPACEREPORT",
                          "typeCheck" => 'ssp',
                          'thead' => array('Datastore Name', 'Capacity', 'FreeSpace', '% Free', 'vCenter'),
                          'columnDefs' => '{type: "file-size", targets: [ 1, 2 ]}']);

} # END if ($check->getModuleSchedule('datastoreSpacereport') != 'off' && $check->getModuleSchedule('inventory') != 'off')

if ($check->getModuleSchedule('datastoreOrphanedVMFilesreport') != 'off' && $check->getModuleSchedule('inventory') != 'off')
{
  
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM orphanFiles AS main WHERE true",
                          "id" => "DATASTOREORPHANEDVMFILESREPORT",
                          "typeCheck" => 'ssp',
                          'thead' => array('vCenter Name', 'File Path', 'File Size', 'File Modification'),
                          'columnDefs' => '{ type: "file-size", targets: [ 2 ] }']);
                          
} # END if ($check->getModuleSchedule('datastoreOrphanedVMFilesreport') != 'off' && $check->getModuleSchedule('inventory') != 'off')

if ($check->getModuleSchedule('datastoreOverallocation') != 'off' && $check->getModuleSchedule('inventory') != 'off')
{
  
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM datastores AS main INNER JOIN (SELECT MAX(id), datastore_id, size, freespace, uncommitted, firstseen, lastseen FROM datastoreMetrics GROUP BY datastore_id) dm ON (main.id = dm.datastore_id) WHERE dm.firstseen < '" . $check->getSelectedDate() . " 23:59:59' AND dm.lastseen > '" . $check->getSelectedDate() . " 00:00:01' AND ROUND(100*((dm.size-dm.freespace+dm.uncommitted)/dm.size)) > ". $check->getConfig('datastoreOverallocation'),
                          "id" => "DATASTOREOVERALLOCATION",
                          "typeCheck" => 'ssp',
                          'thead' => array('Datastore Name', 'Capacity', 'FreeSpace', 'Uncommitted', 'Allocation', 'vCenter'),
                          'columnDefs' => '{ type: "file-size", targets: [ 1, 2, 3 ] }']);

} # END if ($check->getModuleSchedule('datastoreOverallocation') != 'off' && $check->getModuleSchedule('inventory') != 'off')

if ($check->getModuleSchedule('datastoreSIOCdisabled') != 'off' && $check->getModuleSchedule('inventory') != 'off')
{
  
  $check->displayCheck([  'sqlQuery' => "SELECT main.datastore_name, v.vcname as vcenter FROM datastores main INNER JOIN vcenters v ON main.vcenter = v.id WHERE main.iormConfiguration = 0",
                          "id" => "DATASTORESIOCDISABLED",
                          'thead' => array('Datastore Name', 'vCenter'),
                          'tbody' => array('"<td>".$entry["datastore_name"]."</td>"', '"<td>".$entry["vcenter"]."</td>"')]);

} # END if ($check->getModuleSchedule('datastoreSIOCdisabled') != 'off' && $check->getModuleSchedule('inventory') != 'off')

if ($check->getModuleSchedule('datastoremaintenancemode') != 'off' && $check->getModuleSchedule('inventory') != 'off')
{
  
  $check->displayCheck([  'sqlQuery' => "SELECT main.datastore_name, v.vcname as vcenter FROM datastores main INNER JOIN vcenters v ON main.vcenter = v.id WHERE main.maintenanceMode <> 'normal'",
                          "id" => "DATASTOREMAINTENANCEMODE",
                          'thead' => array('Datastore Name', 'vCenter'),
                          'tbody' => array('"<td>".$entry["datastore_name"]."</td>"', '"<td>".$entry["vcenter"]."</td>"')]);

} # END if ($check->getModuleSchedule('datastoremaintenancemode') != 'off' && $check->getModuleSchedule('inventory') != 'off')

if ($check->getModuleSchedule('datastoreAccessible') != 'off' && $check->getModuleSchedule('inventory') != 'off')
{
  
  $check->displayCheck([  'sqlQuery' => "SELECT main.datastore_name, v.vcname as vcenter FROM datastores main INNER JOIN vcenters v ON main.vcenter = v.id WHERE main.isAccessible = 0",
                          'sqlQueryGroupBy' => "main.datastore_name, main.vcenter",
                          "id" => "DATASTOREACCESSIBLE",
                          'thead' => array('Datastore Name', 'vCenter'),
                          'tbody' => array('"<td>".$entry["datastore_name"]."</td>"', '"<td>".$entry["vcenter"]."</td>"')]);

} # END if ($check->getModuleSchedule('datastoreAccessible') != 'off' && $check->getModuleSchedule('inventory') != 'off')
?>
  </div>
<?php require("footer.php"); ?>
