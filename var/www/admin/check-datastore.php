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

# Main class loading
try {
  $check = new SexiCheck();
} catch (Exception $e) {
  # Any exception will be ending the script, we want exception-free run
  exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> ' . $e->getMessage() . '</div>');
}

if($check->getModuleSchedule('datastoreSpacereport') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "datastores-global.xml",
                          'xpathQuery' => "/datastores/datastore[pct_free<" . $check->getConfig('datastoreFreeSpaceThreshold') . "]",
                          "id" => "DATASTORESPACEREPORT",
                          'thead' => array('Datastore Name', 'Capacity', 'FreeSpace', '% Free', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".human_filesize($entry->size)."</td>"', '"<td>".human_filesize($entry->freespace)."</td>"', '"<td>".round((($entry->freespace / $entry->size) * 100),0)."</td>"', '"<td>".$entry->vcenter."</td>"'),
                          'columnDefs' => '{ type: "file-size", targets: [ 1, 2 ] }']);
}
?>
    <h2>Orphaned VM Files report</h2>
<?php
if($check->getModuleSchedule('datastoreOverallocation') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "datastores-global.xml",
                          'xpathQuery' => "/datastores/datastore[pct_overallocation>" . $check->getConfig('datastoreOverallocation') . "]",
                          "id" => "DATASTOREOVERALLOCATION",
                          'thead' => array('Datastore Name', 'Capacity', 'FreeSpace', 'Uncommitted', 'Overallocation', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".human_filesize($entry->size)."</td>"', '"<td>".human_filesize($entry->freespace)."</td>"', '"<td>".human_filesize($entry->uncommitted)."</td>"', '"<td>".round(((($entry->size - $entry->freespace + $entry->uncommitted) * 100) / $entry->size),0)." %</td>"', '"<td>".$entry->vcenter."</td>"'),
                          'columnDefs' => '{ type: "file-size", targets: [ 1, 2, 3 ] }']);
}

if($check->getModuleSchedule('datastoreSIOCdisabled') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "datastores-global.xml",
                          'xpathQuery' => "/datastores/datastore[iormConfiguration=0]",
                          "id" => "DATASTORESIOCDISABLED",
                          'thead' => array('Datastore Name', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}

if($check->getModuleSchedule('datastoremaintenancemode') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "datastores-global.xml",
                          'xpathQuery' => "/datastores/datastore[maintenanceMode!='normal']",
                          "id" => "DATASTOREMAINTENANCEMODE",
                          'thead' => array('Datastore Name', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}

if($check->getModuleSchedule('datastoreAccessible') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "datastores-global.xml",
                          'xpathQuery' => "/datastores/datastore[accessible!=1]",
                          "id" => "DATASTOREACCESSIBLE",
                          'thead' => array('Datastore Name', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}
?>
  </div>
<?php require("footer.php"); ?>
