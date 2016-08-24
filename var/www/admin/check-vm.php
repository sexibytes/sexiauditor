<?php require("session.php"); ?>
<?php
$title = "VM Checks";
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

# Main class loading
try {
  $check = new SexiCheck();
  # Header generation
  $check->displayHeader($_SERVER['SCRIPT_NAME']);
  // $check->setSSPCategory('vm');
} catch (Exception $e) {
  # Any exception will be ending the script, we want exception-free run
  exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> ' . $e->getMessage() . '</div>');
}

if($check->getModuleSchedule('vmSnapshotsage') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.name as vm, main.quiesced, main.state, main.name, main.description, DATEDIFF('" . $check->getSelectedDate() . "', main.createTime) as age, v.vcname as vcenter FROM snapshots main INNER JOIN vms ON main.vm = main.id INNER JOIN hosts h ON vms.host = h.id INNER JOIN vcenters v ON h.vcenter = v.id WHERE DATEDIFF('" . $check->getSelectedDate() . "', main.createTime) > " . $check->getConfig('vmSnapshotAge'),
                          "id" => "VMSNAPSHOTSAGE",
                          'thead' => array('VM Name', 'Quiesced/State', 'Snapshot', 'Description', 'Age(day)', 'vCenter'),
                          'tbody' => array('"<td>".$entry["vm"]."</td>"', '"<td>".(($entry["quiesced"] == 0) ? \'<i class="glyphicon glyphicon-remove-sign alarm-red"></i>\' : \'<i class="glyphicon glyphicon-ok-sign alarm-green"></i>\') . (($entry["state"] == "poweredOff") ? \'<i class="glyphicon glyphicon-stop"></i>\' : \'<i class="glyphicon glyphicon-play"></i>\')."</td>"', '"<td>".$entry["name"]."</td>"', '"<td>".$entry["description"]."</td>"', '"<td>".$entry["age"]."</td>"', '"<td>".$entry["vcenter"]."</td>"'),
                          'columnDefs' => '{ "orderable": false, className: "dt-body-center", "targets": [ 1 ] }']);
}

if($check->getModuleSchedule('vmphantomsnapshot') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM vms AS main INNER JOIN hosts h ON main.host = h.id INNER JOIN vcenters v ON h.vcenter = v.id WHERE main.phantomSnapshot > 0",
                          "id" => "VMPHANTOMSNAPSHOT",
                          "typeCheck" => 'ssp',
                          'thead' => array('VM Name', 'vCenter')]);
                          // 'tbody' => array('"<td>".$entry["name"]."</td>"', '"<td>".$entry["vcenter"]."</td>"')]);
}

if($check->getModuleSchedule('vmconsolidationneeded') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM vms AS main INNER JOIN hosts h ON main.host = h.id INNER JOIN vcenters v ON h.vcenter = v.id WHERE main.consolidationNeeded = 1",
                          "id" => "VMCONSOLIDATIONNEEDED",
                          "typeCheck" => 'ssp',
                          'thead' => array('VM Name', 'vCenter')]);
                          // 'tbody' => array('"<td>".$entry["name"]."</td>"', '"<td>".$entry["vcenter"]."</td>"')]);
}

if($check->getModuleSchedule('vmcpuramhddreservation') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM vms AS main INNER JOIN hosts h ON main.host = h.id INNER JOIN vcenters v ON h.vcenter = v.id WHERE (main.cpuReservation > 0 OR main.memReservation > 0)",
                          "id" => "VMCPURAMHDDRESERVATION",
                          "typeCheck" => 'ssp',
                          'thead' => array('VM Name', 'CPU Reservation', 'MEM Reservation', 'vCenter')]);
                          // 'tbody' => array('"<td>".$entry["name"]."</td>"', '"<td>".$entry["cpuReservation"]." MB</td>"', '"<td>".$entry["memReservation"]." MB</td>"', '"<td>".$entry["vcenter"]."</td>"'),
                          // 'columnDefs' => '{ type: "file-size", targets: [ 2, 3 ] }']);
}

if($check->getModuleSchedule('vmcpuramhddlimits') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM vms AS main INNER JOIN hosts h ON main.host = h.id INNER JOIN vcenters v ON h.vcenter = v.id WHERE (main.cpuLimit > 0 OR main.memLimit > 0)",
                          "id" => "VMCPURAMHDDLIMITS",
                          "typeCheck" => 'ssp',
                          'thead' => array('VM Name', 'CPU Limit', 'MEM Limit', 'vCenter')]);
                          // 'tbody' => array('"<td>".$entry["name"]."</td>"', '"<td>".$entry["cpuLimit"]."</td>"', '"<td>".$entry["memLimit"]."</td>"', '"<td>".$entry["vcenter"]."</td>"'),
                          // 'columnDefs' => '{ type: "file-size", targets: [ 2, 3 ] }']);
}

if($check->getModuleSchedule('vmcpuramhotadd') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM vms AS main INNER JOIN hosts h ON main.host = h.id INNER JOIN vcenters v ON h.vcenter = v.id WHERE (main.cpuHotAddEnabled = 1 OR main.memHotAddEnabled = 1)",
                          "id" => "VMCPURAMHOTADD",
                          "typeCheck" => 'ssp',
                          'thead' => array('VM Name', 'CPU HotAdd', 'MEM HotAdd', 'vCenter'),
                          // 'tbody' => array('"<td>".$entry["name"]."</td>"', '"<td><i class=\"glyphicon glyphicon-".(($entry["cpuHotAddEnabled"] == 1) ? "ok-sign alarm-green" : "remove-sign alarm-red")."\"></i></td>"', '"<td><i class=\"glyphicon glyphicon-".(($entry["memHotAddEnabled"] == 1) ? "ok-sign alarm-green" : "remove-sign alarm-red")."\"></i></td>"', '"<td>".$entry["vcenter"]."</td>"'),
                          'columnDefs' => '{ "orderable": false, className: "dt-body-center", "targets": [ 1, 2 ] }']);
}

if($check->getModuleSchedule('vmToolsPivot') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.vmtools as dataKey, COUNT(*) as dataValue FROM vms AS main WHERE main.vmtools > 0",
                          'sqlQueryGroupBy' => "main.vmtools",
                          "id" => "VMTOOLSPIVOT",
                          'typeCheck' => 'pivotTableGraphed',
                          'thead' => array('vmtools Version', 'Count'),
                          'order' => '[ 1, "desc" ]']);
}

if($check->getModuleSchedule('vmvHardwarePivot') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.hwversion as dataKey, COUNT(*) as dataValue FROM vms AS main WHERE true",
                          'sqlQueryGroupBy' => "main.hwversion",
                          "id" => "VMVHARDWAREPIVOT",
                          'typeCheck' => 'pivotTableGraphed',
                          'thead' => array('vmtools Hardware', 'Count'),
                          'order' => '[ 1, "desc" ]']);
}

if($check->getModuleSchedule('vmballoonzipswap') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM vms AS main INNER JOIN vmMetrics AS vmm ON (main.id = vmm.vm_id) WHERE (vmm.swappedMemory > 0 OR vmm.balloonedMemory > 0 OR vmm.compressedMemory > 0)",
                          "id" => "VMBALLOONZIPSWAP",
                          "typeCheck" => 'ssp',
                          'thead' => array('VM Name', 'Ballooned', 'Compressed', 'Swapped', 'vCenter')]);
                          // 'tbody' => array('"<td>".$entry["name"]."</td>"', '"<td>".human_filesize($entry["balloonedMemory"])."</td>"', '"<td>".human_filesize($entry["swappedMemory"])."</td>"', '"<td>".human_filesize($entry["compressedMemory"])."</td>"', '"<td>".$entry["vcenter"]."</td>"'),
                          // 'columnDefs' => '{ type: "file-size", targets: [ 1, 2, 3 ] }']);
}

if($check->getModuleSchedule('vmmultiwritermode') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM vms AS main INNER JOIN hosts h ON main.host = h.id INNER JOIN vcenters v ON h.vcenter = v.id WHERE main.multiwriter = 1",
                          "id" => "VMMULTIWRITERMODE",
                          "typeCheck" => 'ssp',
                          'thead' => array('VM Name', 'vCenter')]);
                          // 'tbody' => array('"<td>".$entry["name"]."</td>"', '"<td>".$entry["vcenter"]."</td>"')]);
}

// // if($check->getModuleSchedule('vmNonpersistentmode') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
// //   $check->displayCheck([  'sqlQuery' => "SELECT main.name as name, v.name as vcenter FROM vms AS main INNER JOIN hosts h ON main.host = h.id INNER JOIN vcenters v ON h.vcenter = v.id WHERE main.active = 1 AND main.nonPersistentDisk = 1",
// //                           "id" => "VMNONPERSISTENTMODE",
// //                           'thead' => array('VM Name', 'vCenter'),
// //                           'tbody' => array('"<td>".$entry["name"]."</td>"', '"<td>".$entry["vcenter"]."</td>"')]);
// // }

if($check->getModuleSchedule('vmscsibussharing') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM vms AS main INNER JOIN hosts h ON main.host = h.id INNER JOIN vcenters v ON h.vcenter = v.id WHERE main.sharedBus = 1",
                          "id" => "VMSCSIBUSSHARING",
                          "typeCheck" => 'ssp',
                          'thead' => array('VM Name', 'Power State', 'vCenter')]);
                          // 'tbody' => array('"<td>".$entry["name"]."</td>"', '"<td>".$entry["powerState"]."</td>"', '"<td>".$entry["vcenter"]."</td>"')]);
}

if($check->getModuleSchedule('vmInvalidOrInaccessible') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM vms AS main INNER JOIN hosts h ON main.host = h.id INNER JOIN vcenters v ON h.vcenter = v.id WHERE main.connectionState NOT LIKE 'connected'",
                          "id" => "VMINVALIDORINACCESSIBLE",
                          "typeCheck" => 'ssp',
                          'thead' => array('VM Name', 'Connection State', 'vCenter')]);
                          // 'tbody' => array('"<td>".$entry["name"]."</td>"', '"<td>".$entry->connectionState."</td>"', '"<td>".$entry["vcenter"]."</td>"')]);
}

if($check->getModuleSchedule('vmInconsistent') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM vms AS main INNER JOIN hosts h ON main.host = h.id INNER JOIN vcenters v ON h.vcenter = v.id WHERE main.vmxpath NOT LIKE CONCAT('%', main.name, '/', main.name, '.vmx')",
                          "id" => "VMINCONSISTENT",
                          "typeCheck" => 'ssp',
                          'thead' => array('VM Name', 'vmx Path', 'vCenter')]);
                          // 'tbody' => array('"<td>".$entry["name"]."</td>"', '"<td>".$entry["vm"]xpath."</td>"', '"<td>".$entry["vcenter"]."</td>"')]);
}

if($check->getModuleSchedule('vmRemovableConnected') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM vms AS main INNER JOIN hosts h ON main.host = h.id INNER JOIN vcenters v ON h.vcenter = v.id WHERE main.removable = 1",
                          "id" => "VMREMOVABLECONNECTED",
                          "typeCheck" => 'ssp',
                          'thead' => array('', 'VM Name', 'vCenter'),
                          // 'tbody' => array('"<td><i class=\"glyphicon glyphicon-floppy-disk alarm-red\"></i> - <i class=\"glyphicon glyphicon-cd alarm-red\"></i></td>"', '"<td>".$entry["name"]."</td>"', '"<td>".$entry["vcenter"]."</td>"'),
                          'order' => '[ 1, "asc" ]',
                          'columnDefs' => '{ "orderable": false, className: "dt-body-right", "targets": [ 0 ] }']);
}

if($check->getModuleSchedule('alarms') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM alarms main INNER JOIN vcenters v ON main.vcenter = v.id INNER JOIN vms ON main.entityMoRef = vms.moref WHERE main.entityMoRef LIKE 'VirtualMachine%'",
                          "id" => "ALARMSVM",
                          "typeCheck" => 'ssp',
                          'thead' => array('Status', 'Alarm', 'Date', 'Name', 'vCenter'),
                          // 'tbody' => array('"<td>" . $this->alarmStatus[(string) $entry->status] . "</td>"', '"<td>" . $entry["name"] . "</td>"', '"<td>" . $entry->time . "</td>"', '"<td>" . $entry->entity . "</td>"', '"<td>" . $entry["vcenter"] . "</td>"'),
                          'order' => '[ 1, "asc" ]',
                          'columnDefs' => '{ "orderable": false, className: "dt-body-right", "targets": [ 0 ] }']);
}

if($check->getModuleSchedule('vmGuestIdMismatch') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM vms AS main INNER JOIN hosts h ON main.host = h.id INNER JOIN vcenters v ON h.vcenter = v.id WHERE main.guestId <> 'Not Available' AND main.guestId <> main.configGuestId",
                          "id" => "VMGUESTIDMISMATCH",
                          "typeCheck" => 'ssp',
                          'thead' => array('VM Name', 'GuestId', 'Config GuestId', 'vCenter')]);
                          // 'tbody' => array('"<td>".$entry["name"]."</td>"', '"<td>".$entry->guestId."</td>"', '"<td>".$entry->configGuestId."</td>"', '"<td>".$entry["vcenter"]."</td>"')]);
}

if($check->getModuleSchedule('vmPoweredOff') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM vms AS main INNER JOIN hosts h ON main.host = h.id INNER JOIN vcenters v ON h.vcenter = v.id WHERE main.powerState = 'poweredOff'",
                          "id" => "VMPOWEREDOFF",
                          "typeCheck" => 'ssp',
                          'thead' => array('VM Name', 'vCenter')]);
                          // 'tbody' => array('"<td>".$entry["name"]."</td>"', '"<td>".$entry["vcenter"]."</td>"')]);
}

if($check->getModuleSchedule('vmMisnamed') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM vms AS main INNER JOIN hosts h ON main.host = h.id INNER JOIN vcenters v ON h.vcenter = v.id WHERE main.fqdn <> 'Not Available' AND main.fqdn NOT LIKE CONCAT(main.name, '%')",
                          "id" => "VMMISNAMED",
                          "typeCheck" => 'ssp',
                          'thead' => array('VM Name', 'FQDN', 'vCenter')]);
                          // 'tbody' => array('"<td>".$entry["name"]."</td>"', '"<td>".$entry->fqdn."</td>"', '"<td>".$entry["vcenter"]."</td>"')]);
}

if($check->getModuleSchedule('vmGuestPivot') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'sqlQuery' => "SELECT main.guestOS as dataKey, COUNT(*) as dataValue FROM vms AS main WHERE main.guestOS <> 'Not Available'",
                          "sqlQueryGroupBy" => "main.guestOS",
                          "id" => "VMGUESTPIVOT",
                          'typeCheck' => 'pivotTableGraphed',
                          'thead' => array('GuestOS', 'Count'),
                          'order' => '[ 1, "desc" ]']);
}
?>

	</div>
<?php require("footer.php"); ?>
