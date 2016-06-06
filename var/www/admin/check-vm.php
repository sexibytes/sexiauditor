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
} catch (Exception $e) {
  # Any exception will be ending the script, we want exception-free run
  exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> ' . $e->getMessage() . '</div>');
}

if($check->getModuleSchedule('vmSnapshotsage') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "snapshots-global.xml",
                          'xpathQuery' => "/snapshots/snapshot",
                          "id" => "VMSNAPSHOTSAGE",
                          'title' => 'VM Snapshots Age',
                          'description' => 'This module will display snapshots that are older than ' . $check->getConfig('vmSnapshotAge') . ' day(s). Keeping snapshot can result in performance degradation under certain circumstances.',
                          'thead' => array('VM Name', 'Quiesced/State', 'Snapshot', 'Description', 'Age(day)', 'vCenter'),
                          'tbody' => array('"<td>".$entry->vm."</td>"', '"<td>".(($entry->quiesced == 0) ? \'<i class="glyphicon glyphicon-remove-sign alarm-red"></i>\' : \'<i class="glyphicon glyphicon-ok-sign alarm-green"></i>\') . (($entry->state == "poweredOff") ? \'<i class="glyphicon glyphicon-stop"></i>\' : \'<i class="glyphicon glyphicon-play"></i>\')."</td>"', '"<td>".$entry->name."</td>"', '"<td>".$entry->description."</td>"', '"<td>".DateTime::createFromFormat("Y-m-d", substr($entry->createTime, 0, 10))->diff(new DateTime("now"))->format("%a")."</td>"', '"<td>".$entry->vcenter."</td>"'),
                          'columnDefs' => '{ "orderable": false, className: "dt-body-center", "targets": [ 1 ] }']);
}

if($check->getModuleSchedule('vmphantomsnapshot') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "vms-global.xml",
                          'xpathQuery' => "/vms/vm[phantomSnapshot='1']",
                          "id" => "VMPHANTOMSNAPSHOT",
                          'title' => 'VM phantom snapshot',
                          'description' => 'The following VM\s have Phantom Snapshots.',
                          'thead' => array('VM Name', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}

if($check->getModuleSchedule('vmconsolidationneeded') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "vms-global.xml",
                          'xpathQuery' => "/vms/vm[consolidationNeeded='1']",
                          "id" => "VMCONSOLIDATIONNEEDED",
                          'title' => 'VM consolidation needed',
                          'description' => 'The following VMs have snapshots that failed to consolidate. See <a href=\'http://blogs.vmware.com/vsphere/2011/08/consolidate-snapshots.html\' target=\'_blank\'>this article</a> for more details.',
                          'thead' => array('VM Name', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}

if($check->getModuleSchedule('vmcpuramhddreservation') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "vms-global.xml",
                          'xpathQuery' => "/vms/vm[cpuReservation>0 or memReservation>0]",
                          "id" => "VMCPURAMHDDRESERVATION",
                          'title' => 'VM CPU-MEM reservation',
                          'description' => 'The following VMs have a CPU or Memory Reservation configured which may impact the performance of the VM.',
                          'thead' => array('VM Name', 'CPU Reservation', 'MEM Reservation', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->cpuReservation."</td>"', '"<td>".$entry->memReservation."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}

if($check->getModuleSchedule('vmcpuramhddlimits') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "vms-global.xml",
                          'xpathQuery' => "/vms/vm[cpuLimit>0 or memLimit>0]",
                          "id" => "VMCPURAMHDDLIMITS",
                          'title' => 'VM CPU-MEM limit',
                          'description' => 'The following VMs have a CPU or memory limit configured which may impact the performance of the VM. Note: -1 indicates no limit.',
                          'thead' => array('VM Name', 'CPU Limit', 'MEM Limit', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->cpuLimit."</td>"', '"<td>".$entry->memLimit."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}

if($check->getModuleSchedule('vmcpuramhotadd') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "vms-global.xml",
                          'xpathQuery' => "/vms/vm[cpuHotAddEnabled='true' or memHotAddEnabled='true']",
                          "id" => "VMCPURAMHOTADD",
                          'title' => 'VM CPU-MEM hot-add',
                          'description' => 'The following lists all VMs and they Hot Add / Hot Plug feature configuration.',
                          'thead' => array('VM Name', 'CPU HotAdd', 'MEM HotAdd', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td><i class=\"glyphicon glyphicon-".(($entry->cpuHotAddEnabled == "true") ? "ok-sign alarm-green" : "remove-sign alarm-red")."\"></i></td>"', '"<td><i class=\"glyphicon glyphicon-".(($entry->memHotAddEnabled == "true") ? "ok-sign alarm-green" : "remove-sign alarm-red")."\"></i></td>"', '"<td>".$entry->vcenter."</td>"'),
                          'columnDefs' => '{ "orderable": false, className: "dt-body-center", "targets": [ 1, 2 ] }']);
}

if($check->getModuleSchedule('vmToolsPivot') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "vms-global.xml",
                          'xpathQuery' => "/vms/vm[vmtools!='0' and vmtools!='Not Available']/vmtools",
                          "id" => "VMTOOLSPIVOT",
                          'title' => 'VM vmtools pivot table',
                          'description' => 'xxx',
                          'typeCheck' => 'pivotTableGraphed',
                          'thead' => array('vmtools Version', 'Count'),
                          'order' => '[ 1, "desc" ]']);
}

if($check->getModuleSchedule('vmvHardwarePivot') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "vms-global.xml",
                          'xpathQuery' => "/vms/vm/hwversion",
                          "id" => "VMVHARDWAREPIVOT",
                          'title' => 'VM vHardware pivot table',
                          'description' => 'xxx',
                          'typeCheck' => 'pivotTableGraphed',
                          'thead' => array('vmtools Hardware', 'Count'),
                          'order' => '[ 1, "desc" ]']);
}

if($check->getModuleSchedule('vmballoonzipswap') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "vms-global.xml",
                          'xpathQuery' => "/vms/vm[swappedMemory!=0 or balloonedMemory!=0 or compressedMemory!=0]",
                          "id" => "VMBALLOONZIPSWAP",
                          'title' => 'Balloon-Swap-Compression on memory',
                          'description' => 'Ballooning and swapping may indicate a lack of memory or a limit on a VM, this may be an indication of not enough memory in a host or a limit held on a VM, <a href=\'http://www.virtualinsanity.com/index.php/2010/02/19/performance-troubleshooting-vmware-vsphere-memory/\' target=\'_blank\'>further information is available here</a>.',
                          'thead' => array('VM Name', 'Ballooned', 'Compressed', 'Swapped', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".human_filesize($entry->balloonedMemory)."</td>"', '"<td>".human_filesize($entry->swappedMemory)."</td>"', '"<td>".human_filesize($entry->compressedMemory)."</td>"', '"<td>".$entry->vcenter."</td>"'),
                          'columnDefs' => '{ type: "file-size", targets: [ 1, 2, 3 ] }']);
}

if($check->getModuleSchedule('vmmultiwritermode') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "vms-global.xml",
                          'xpathQuery' => "/vms/vm[mutlwriter=1]",
                          "id" => "VMMULTIWRITERMODE",
                          'title' => 'VM with vmdk in multiwriter mode',
                          'description' => 'The following VMs have multi-writer parameter. A problem will occur in case of svMotion without reconfiguration of the applications which are using these virtual disks and also change of the VM configuration concerned. More information <a href=\'http://kb.vmware.com/selfservice/microsites/search.do?language=en_US&cmd=displayKC&externalId=1034165\'>here</a>.',
                          'thead' => array('VM Name', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}

if($check->getModuleSchedule('vmNonpersistentmode') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "vms-global.xml",
                          'xpathQuery' => "/vms/vm[nonPersistentDisk=1]",
                          "id" => "VMNONPERSISTENTMODE",
                          'title' => 'VM with vmdk in Non persistent mode',
                          'description' => 'The following server VMs have disks in NonPersistent mode (excludes all desktop VMs). A problem will occur in case of svMotion without reconfiguration of these virtual disks.',
                          'thead' => array('VM Name', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}

if($check->getModuleSchedule('vmscsibussharing') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "vms-global.xml",
                          'xpathQuery' => "/vms/vm[sharedBus=1]",
                          "id" => "VMSCSIBUSSHARING",
                          'title' => 'VM with scsi bus sharing',
                          'description' => 'The following VMs have physical and/or virtual bus sharing. A problem will occur in case of svMotion without reconfiguration of the applications which are using these virtual disks and also change of the VM configuration concerned.',
                          'thead' => array('VM Name', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}

if($check->getModuleSchedule('vmInvalidOrInaccessible') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "vms-global.xml",
                          'xpathQuery' => "/vms/vm[connectionState='invalid' or connectionState='inaccessible']",
                          "id" => "VMINVALIDORINACCESSIBLE",
                          'title' => 'VM invalid or innaccessible',
                          'description' => 'The following VMs are marked as inaccessible or invalid.',
                          'thead' => array('VM Name', 'Connection State', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->connectionState."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}

if($check->getModuleSchedule('vmInconsistent') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "vms-global.xml",
                          'xpathQuery' => "/vms/vm[not(contains(translate(vmxpath, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), concat(translate(name, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'),'/',translate(name, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'),'.vmx')))]",
                          "id" => "VMINCONSISTENT",
                          'title' => 'VM in inconsistent folder',
                          'description' => 'The following VMs are not stored in folders consistent to their names, this may cause issues when trying to locate them from the datastore manually.',
                          'thead' => array('VM Name', 'vmx Path', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->vmxpath."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}

if($check->getModuleSchedule('vmRemovableConnected') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "vms-global.xml",
                          'xpathQuery' => "/vms/vm[removable='1']",
                          "id" => "VMREMOVABLECONNECTED",
                          'title' => 'VM with removable devices',
                          'description' => 'This module will display VM that have removable devices (floppy, CD-Rom, ...) connected.',
                          'thead' => array('', 'VM Name', 'vCenter'),
                          'tbody' => array('"<td><i class=\"glyphicon glyphicon-floppy-disk alarm-red\"></i> - <i class=\"glyphicon glyphicon-cd alarm-red\"></i></td>"', '"<td>".$entry->name."</td>"', '"<td>".$entry->vcenter."</td>"'),
                          'order' => '[ 1, "asc" ]',
                          'columnDefs' => '{ "orderable": false, className: "dt-body-right", "targets": [ 0 ] }']);
}

if($check->getModuleSchedule('alarms') != 'off') {
  $check->displayCheck([  'xmlFile' => "alarms-global.xml",
                          'xpathQuery' => "/alarms/alarm[entity_type='VirtualMachine']",
                          "id" => "ALARMSVM",
                          'title' => 'Host Alarms',
                          'description' => 'This module will display triggered alarms on VirtualMachine objects level with status and time of creation.',
                          'thead' => array('Status', 'Alarm', 'Date', 'Name', 'vCenter'),
                          'tbody' => array('"<td>" . $this->alarmStatus[(string) $entry->status] . "</td>"', '"<td>" . $entry->name . "</td>"', '"<td>" . $entry->time . "</td>"', '"<td>" . $entry->entity . "</td>"', '"<td>" . $entry->vcenter . "</td>"'),
                          'order' => '[ 1, "asc" ]',
                          'columnDefs' => '{ "orderable": false, className: "dt-body-right", "targets": [ 0 ] }']);
}

if($check->getModuleSchedule('vmGuestIdMismatch') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "vms-global.xml",
                          'xpathQuery' => "/vms/vm[guestId!='Not Available' and guestId!=configGuestId]",
                          "id" => "VMGUESTIDMISMATCH",
                          'title' => 'VM GuestId Mismatch',
                          'description' => 'This module will display VM that have GuestOS setting different from GuestOS retrived through vmtools.',
                          'thead' => array('VM Name', 'GuestId', 'Config GuestId', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->guestId."</td>"', '"<td>".$entry->configGuestId."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}

if($check->getModuleSchedule('vmPoweredOff') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "vms-global.xml",
                          'xpathQuery' => "/vms/vm[powerState='poweredOff']",
                          "id" => "VMPOWEREDOFF",
                          'title' => 'VM Powered Off',
                          'description' => 'This module will display VM that are Powered Off. This can be useful to check if this state is expected.',
                          'thead' => array('VM Name', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}

if($check->getModuleSchedule('vmMisnamed') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "vms-global.xml",
                          'xpathQuery' => "/vms/vm[fqdn!='Not Available' and not(starts-with(translate(fqdn, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), translate(name, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')))]",
                          "id" => "VMMISNAMED",
                          'title' => 'VM misnamed',
                          'description' => 'This module will display VM that have FQDN (based on vmtools) mismatched with the VM object name.',
                          'thead' => array('VM Name', 'FQDN', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->fqdn."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}

if($check->getModuleSchedule('vmGuestPivot') != 'off' && $check->getModuleSchedule('inventory') != 'off') {
  $check->displayCheck([  'xmlFile' => "vms-global.xml",
                          'xpathQuery' => "/vms/vm[guestOS!='Not Available']/guestOS",
                          "id" => "VMGUESTPIVOT",
                          'title' => 'VM GuestId pivot table',
                          'description' => 'This module will display GuestOS pivot table and family repartition',
                          'typeCheck' => 'pivotTableGraphed',
                          'thead' => array('GuestOS', 'Count'),
                          'order' => '[ 1, "desc" ]']);
}
?>

	</div>
<?php require("footer.php"); ?>
