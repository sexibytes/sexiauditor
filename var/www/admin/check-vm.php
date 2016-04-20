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
							'https://code.highcharts.com/highcharts.js',
							'https://code.highcharts.com/modules/exporting.js');
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
    <div style="margin: 10px 20px 10px 20px;" class="container-fluid">
	<div class="row">
	<div class="col-lg-10 alert alert-info" style="margin-top: 20px; text-align: center;">
			<h1 style="margin-top: 10px;">VM Checks on <?php echo DateTime::createFromFormat('Y/m/d', $selectedDate)->format('l jS F Y'); ?></h1>
		</div>
	
	<div class="alert col-lg-2">
        <form action="check-vm.php" style="margin-top: 5px;" method="post">
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

// Load the fonts
Highcharts.createElement('link', {
   href: '//fonts.googleapis.com/css?family=Dosis:400,600',
   rel: 'stylesheet',
   type: 'text/css'
}, null, document.getElementsByTagName('head')[0]);

Highcharts.theme = {
   colors: ["#7cb5ec", "#f7a35c", "#90ee7e", "#7798BF", "#aaeeee", "#ff0066", "#eeaaee",
      "#55BF3B", "#DF5353", "#7798BF", "#aaeeee"],
   chart: {
      backgroundColor: null,
      style: {
         fontFamily: "Dosis, sans-serif"
      }
   },
   title: {
      style: {
         fontSize: '16px',
         fontWeight: 'bold',
         textTransform: 'uppercase'
      }
   },
   tooltip: {
      borderWidth: 0,
      backgroundColor: 'rgba(219,219,216,0.8)',
      shadow: false
   },
   legend: {
      itemStyle: {
         fontWeight: 'bold',
         fontSize: '13px'
      }
   },
   xAxis: {
      gridLineWidth: 1,
      labels: {
         style: {
            fontSize: '12px'
         }
      }
   },
   yAxis: {
      minorTickInterval: 'auto',
      title: {
         style: {
            textTransform: 'uppercase'
         }
      },
      labels: {
         style: {
            fontSize: '12px'
         }
      }
   },
   plotOptions: {
      candlestick: {
         lineColor: '#404048'
      }
   },


   // General
   background2: '#F0F0EA'
   
};

// Apply the theme
Highcharts.setOptions(Highcharts.theme);


        });
        </script>
        </form>        </div>
</div>
<?php
    # TODO
    # initialise objects if at least one module is active
    # Display bootstrap Success Panel if no result per module instead of empty dataTable
    $xmlVMFile = "$xmlStartPath$xmlSelectedPath/vms-global.xml";
    if (is_readable($xmlVMFile)) {
        $xmlVM = simplexml_load_file($xmlVMFile);
    } else {
        exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Error:</span> File ' . $xmlVMFile . ' is not existant or not readable</div>');
    }
?>
        
<?php if($h_settings['vmSnapshotsage'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php
    $xmlSnapshotFile = "$xmlStartPath$xmlSelectedPath/snapshots-global.xml";
    if (is_readable($xmlSnapshotFile)) {
        $xmlSnapshot = simplexml_load_file($xmlSnapshotFile);
    } else {
        exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Error:</span> File ' . $xmlSnapshotFile . ' is not existant or not readable</div>');
    }
    $maxSnapshotAge = (int) $h_modulesettings['vmSnapshotAge'];
    $impactedSnapshot = $xmlSnapshot->xpath("/snapshots/snapshot");
    if (count($impactedSnapshot) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> VM Snapshots Age <small><a href="generatepdf.php?module=vmSnapshotsage"><i class="glyphicon glyphicon-export"></i> (Export to PDF)</a></small></h2>
        <div class="alert alert-warning" role="alert"><i>This module will display snapshots that are older than <?php echo $maxSnapshotAge; ?> day(s). Keeping snapshot can result in performance degradation under certain circumstances.</i></div>
        <div class="col-lg-6"></div>
        <div class="col-lg-12">
        <table id="snapshotAge" class="table table-hover">
            <thead><tr>
                <th>VM Name</th>
                <th>Quiesced/State</th>
                <th>Snapshot</th>
                <th>Description</th>
                <th>Age(day)</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedSnapshot as $snapshot) {
        $snapshotAge = DateTime::createFromFormat('Y-m-d', substr($snapshot->createTime, 0, 10))->diff(new DateTime("now"))->format('%a');
        if ($snapshotAge > $maxSnapshotAge) {
            echo '            <tr><td>' . $snapshot->vm . '</td><td>' . (($snapshot->quiesced == 0) ? '<i class="glyphicon glyphicon-remove-sign alarm-red"></i>' : '<i class="glyphicon glyphicon-ok-sign alarm-green"></i>') . ' / ' . (($snapshot->state == 'poweredOff') ? '<i class="glyphicon glyphicon-stop"></i>' : '<i class="glyphicon glyphicon-play"></i>') . '</td><td>' . $snapshot->name . '</td><td>' . $snapshot->description . '</td><td>' . $snapshotAge . '</td><td>' . $snapshot->vcenter . '</td></tr>';
        }
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#snapshotAge').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
                "columnDefs": [
                    { "orderable": false, className: "dt-body-center", "targets": [ 1 ] }
                ]
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> VM Snapshots Age <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['vmphantomsnapshot'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php 
    $impactedVM = $xmlVM->xpath("/vms/vm[phantomSnapshot='1']");
    if (count($impactedVM) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> VM phantom snapshot</h2>
        <div class="alert alert-warning" role="alert"><i>The following VM's have Phantom Snapshots.</i></div>
        <div class="col-lg-12">
        <table id="vmphantomsnapshot" class="table table-hover">
            <thead><tr>
                <th>VM Name</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedVM as $vm) {
        echo '            <tr><td>' . $vm->name . '</td><td>' . $vm->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#vmphantomsnapshot').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> VM phantom snapshot <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['vmconsolidationneeded'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php 
    $impactedVM = $xmlVM->xpath("/vms/vm[consolidationNeeded='1']");
    if (count($impactedVM) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> VM consolidation needed</h2>
        <div class="alert alert-warning" role="alert"><i>The following VMs have snapshots that failed to consolidate. See <a href='http://blogs.vmware.com/vsphere/2011/08/consolidate-snapshots.html' target='_blank'>this article</a> for more details.</i></div>
        <div class="col-lg-12">
        <table id="consolidationNeeded" class="table table-hover">
            <thead><tr>
                <th>VM Name</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedVM as $vm) {
        echo '            <tr><td>' . $vm->name . '</td><td>' . $vm->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#consolidationNeeded').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> VM consolidation needed <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['vmcpuramhddreservation'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php 
    $impactedVM = $xmlVM->xpath("/vms/vm[cpuReservation>0 or memReservation>0]");
    if (count($impactedVM) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> VM CPU / MEM reservation</h2>
        <div class="alert alert-warning" role="alert"><i>The following VMs have a CPU or Memory Reservation configured which may impact the performance of the VM.</i></div>
        <div class="col-lg-12">
        <table id="vmReservation" class="table table-hover">
            <thead><tr>
                <th>VM Name</th>
                <th>CPU Reservation</th>
                <th>MEM Reservation</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedVM as $vm) {
        echo '            <tr><td>' . $vm->name . '</td><td>' . $vm->cpuReservation . '</td><td>' . $vm->memReservation . '</td><td>' . $vm->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#vmReservation').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> VM CPU / MEM reservation <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['vmcpuramhddlimits'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php
    $impactedVM = $xmlVM->xpath("/vms/vm[cpuLimit>0 or memLimit>0]");
    if (count($impactedVM) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> VM CPU / MEM limit</h2>
        <div class="alert alert-warning" role="alert"><i>The following VMs have a CPU or memory limit configured which may impact the performance of the VM. Note: -1 indicates no limit.</i></div>
        <div class="col-lg-12">
        <table id="vmLimit" class="table table-hover">
            <thead><tr>
                <th>VM Name</th>
                <th>CPU Limit</th>
                <th>MEM Limit</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedVM as $vm) {
        echo '            <tr><td>' . $vm->name . '</td><td>' . $vm->cpuLimit . '</td><td>' . $vm->memLimit . '</td><td>' . $vm->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#vmLimit').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> VM CPU / MEM limit <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['vmcpuramhotadd'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php 
    $impactedVM = $xmlVM->xpath("/vms/vm[cpuHotAddEnabled='true' or memHotAddEnabled='true']");
    if (count($impactedVM) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> VM cpu/ram hot-add</h2>
        <div class="alert alert-warning" role="alert"><i>The following lists all VMs and they Hot Add / Hot Plug feature configuration.</i></div>
        <div class="col-lg-12">
        <table id="vmHotAdd" class="table table-hover">
            <thead><tr>
                <th>VM Name</th>
                <th>CPU HotAdd</th>
                <th>MEM HotAdd</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedVM as $vm) {
        $colorCPU = (($vm->cpuHotAddEnabled == 'true') ? 'ok-sign alarm-green' : 'remove-sign alarm-red');
        $colorMEM = (($vm->memHotAddEnabled == 'true') ? 'ok-sign alarm-green' : 'remove-sign alarm-red');
        echo '            <tr><td>' . $vm->name . '</td><td><i class="glyphicon glyphicon-' . $colorCPU . '"></i></td><td><i class="glyphicon glyphicon-' . $colorMEM . '"></i></td><td>' . $vm->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#vmHotAdd').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
                "columnDefs": [
                    { "orderable": false, className: "dt-body-center", "targets": [ 1, 2 ] }
                ]
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> VM cpu/ram hot-add <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['vmToolsPivot'] != 'off' && $h_settings['inventory'] != 'off'): ?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> VM vmtools pivot table</h2>
        <div class="col-lg-12">
        <table class="table table-hover">
            <thead><tr>
                <th>vmtools Version</th>
                <th>Count</th>
            </thead>
            <tbody>
<?php
    $dataVMTools = array_diff(array_count_values(array_map("strval", $xmlVM->xpath("/vms/vm/vmtools"))), array("1"));
    arsort($dataVMTools);
    foreach ($dataVMTools as $key => $value) {
        echo '            <tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <hr class="divider-dashed" />
<?php endif; ?>
<?php if($h_settings['vmvHardwarePivot'] != 'off' && $h_settings['inventory'] != 'off'): ?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> VM vHardware pivot table</h2>
        <div class="col-lg-12">
        <table class="table table-hover">
            <thead><tr>
                <th>VM Hardware</th>
                <th>Count</th>
            </thead>
            <tbody>
<?php
    $dataVMHw = array_diff(array_count_values(array_map("strval", $xmlVM->xpath("/vms/vm/hwversion"))), array("1"));
    arsort($dataVMHw);
    foreach ($dataVMHw as $key => $value) {
        echo '            <tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <hr class="divider-dashed" />
<?php endif; ?>
<?php if($h_settings['vmballoonzipswap'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php 
    $impactedVM = $xmlVM->xpath("/vms/vm[swappedMemory!=0 or balloonedMemory!=0 or compressedMemory!=0]");
    if (count($impactedVM) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> Balloon|Swap|Compression on memory</h2>
        <div class="alert alert-warning col-lg-<?php echo $coloumnWidth; ?>" role="alert"><i>Ballooning and swapping may indicate a lack of memory or a limit on a VM, this may be an indication of not enough memory in a host or a limit held on a VM, <a href='http://www.virtualinsanity.com/index.php/2010/02/19/performance-troubleshooting-vmware-vsphere-memory/' target='_blank'>further information is available here</a>.</i></div>
        <div class="col-lg-6"></div>
        <div class="col-lg-12">
        <table id="balloon" class="table table-hover">
            <thead><tr>
                <th>VM Name</th>
                <th>Ballooned</th>
                <th>Compressed</th>
                <th>Swapped</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedVM as $vm) {
        echo '            <tr><td>' . $vm->name . '</td><td>' . human_filesize($vm->balloonedMemory) . '</td><td>' . human_filesize($vm->swappedMemory) . '</td><td>' . human_filesize($vm->compressedMemory) . '</td><td>' . $vm->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#balloon').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
                columnDefs: [{ type: 'file-size', targets: [ 1, 2, 3 ] }]
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> VM balloon/zip/swap <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['vmmultiwritermode'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php 
    $impactedVM = $xmlVM->xpath("/vms/vm[mutlwriter=1]");
    if (count($impactedVM) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> VM with vmdk in multiwriter mode</h2>
        <div class="alert alert-warning col-lg-<?php echo $coloumnWidth; ?>" role="alert"><i>The following VMs have multi-writer parameter. A problem will occur in case of svMotion without reconfiguration of the applications which are using these virtual disks and also change of the VM configuration concerned. More information <a href='http://kb.vmware.com/selfservice/microsites/search.do?language=en_US&cmd=displayKC&externalId=1034165'>here</a>.</i></div>
        <div class="col-lg-6"></div>
        <div class="col-lg-12">
        <table id="multiwriter" class="table table-hover">
            <thead><tr>
                <th>VM Name</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedVM as $vm) {
        echo '            <tr><td>' . $vm->name . '</td><td>' . $vm->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#multiwriter').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> VM with vmdk in multiwriter mode <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['vmNonpersistentmode'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php 
    $impactedVM = $xmlVM->xpath("/vms/vm[nonPersistentDisk=1]");
    if (count($impactedVM) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> VM with vmdk in Non persistent mode</h2>
        <div class="alert alert-warning col-lg-<?php echo $coloumnWidth; ?>" role="alert"><i>The following server VMs have disks in NonPersistent mode (excludes all desktop VMs). A problem will occur in case of svMotion without reconfiguration of these virtual disks.</i></div>
        <div class="col-lg-6"></div>
        <div class="col-lg-12">
        <table id="nonPersistentDisk" class="table table-hover">
            <thead><tr>
                <th>VM Name</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedVM as $vm) {
        echo '            <tr><td>' . $vm->name . '</td><td>' . $vm->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#nonPersistentDisk').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> VM with vmdk in Non persistent mode <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['vmscsibussharing'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php 
    $impactedVM = $xmlVM->xpath("/vms/vm[sharedBus=1]");
    if (count($impactedVM) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> VM with scsi bus sharing</h2>
        <div class="alert alert-warning" role="alert"><i>The following VMs have physical and/or virtual bus sharing. A problem will occur in case of svMotion without reconfiguration of the applications which are using these virtual disks and also change of the VM configuration concerned.</i></div>
        <div class="col-lg-12">
        <table id="busSharing" class="table table-hover">
            <thead><tr>
                <th>VM Name</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedVM as $vm) {
        echo '            <tr><td>' . $vm->name . '</td><td>' . $vm->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#busSharing').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> VM with scsi bus sharing <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['vmInvalidOrInaccessible'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php 
    $impactedVM = $xmlVM->xpath("/vms/vm[connectionState='invalid' or connectionState='inaccessible']");
    if (count($impactedVM) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> VM invalid or innaccessible</h2>
        <div class="alert alert-warning" role="alert"><i>The following VMs are marked as inaccessible or invalid.</i></div>
        <div class="col-lg-12">
        <table id="invalidVM" class="table table-hover">
            <thead><tr>
                <th>VM Name</th>
                <th>Connection State</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedVM as $vm) {
        echo '            <tr><td>' . $vm->name . '</td><td>' . $vm->connectionState . '</td><td>' . $vm->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#invalidVM').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> VM invalid or innaccessible <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['vmInconsistent'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php 
    if (!isset($xpathFullVM)) { $xpathFullVM = $xmlVM->xpath("/vms/vm"); }
	$impactedVM = array();
	foreach ($xpathFullVM as $vm) { if (!preg_match("/\[.*\] " . $vm->name . "\/" . $vm->name . "\.vmx/i", $vm->vmxpath, $null)) { $impactedVM[] = $vm; } }
    if (count($impactedVM) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> VM in inconsistent folder</h2>
        <div class="alert alert-warning" role="alert"><i>The following VMs are not stored in folders consistent to their names, this may cause issues when trying to locate them from the datastore manually.</i></div>
        <div class="col-lg-12">
        <table id="inconsistentVM" class="table table-hover">
            <thead><tr>
                <th>VM Name</th>
                <th>vmx Path</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedVM as $vm) {
        echo '            <tr><td>' . $vm->name . '</td><td>' . $vm->vmxpath . '</td><td>' . $vm->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#inconsistentVM').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> VM in inconsistent folder <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['vmRemovableConnected'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php 
    $impactedVM = $xmlVM->xpath("/vms/vm[removable='1']");
    if (count($impactedVM) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> VM with removable devices</h2>
        <div class="alert alert-warning" role="alert"><i>This module will display VM that have removable devices (floppy, CD-Rom, ...) connected.</i></div>
        <div class="col-lg-12">
        <table id="removableConnected" class="table table-hover">
            <thead><tr>
                <th></th>
                <th>VM Name</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedVM as $vm) {
        echo '            <tr><td><i class="glyphicon glyphicon-floppy-disk alarm-red"></i> / <i class="glyphicon glyphicon-cd alarm-red"></i></td><td>' . $vm->name . '</td><td>' . $vm->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#removableConnected').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
                "order": [[ 1, "asc" ]],
                "columnDefs": [
                    { "orderable": false, className: "dt-body-right", "targets": [ 0 ] }
                ]
            } );
         } );
    </script>
 
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> VM with removable devices <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['alarms'] != 'off'): ?>
<?php
    $xmlAlarmFile = "$xmlStartPath$xmlSelectedPath/alarms-global.xml";
    $xmlAlarm = simplexml_load_file($xmlAlarmFile);
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> VM Alarms</h2>
        <div class="alert alert-warning" role="alert"><i>This module will display triggered alarms on VM objects level with status and time of creation.</i></div>
        <div class="col-lg-12">
        <table id="vmAlarms" class="table table-hover">
            <thead><tr>
                <th>Status</th>
                <th>Alarm</th>
                <th>Date</th>
                <th>Name</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($xmlAlarm->xpath("/alarms/alarm/entity_type[text()='VirtualMachine']/..") as $alarm) {
        switch ($alarm->status) {
            case "red":
                $alarmStatus = '<i class="glyphicon glyphicon-remove-sign alarm-red"></i>';
                break;
            case "yellow":
                $alarmStatus = '<i class="glyphicon glyphicon-question-sign alarm-yellow"></i>';
                break;
            default:
                $alarmStatus = '<i class="glyphicon glyphicon-info-sign"></i>';
        }
        echo '            <tr><td>' . $alarmStatus . '</td><td>' . $alarm->name . '</td><td>' . $alarm->time . '</td><td>' . $alarm->entity . '</td><td>' . $alarm->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#vmAlarms').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
                "order": [[ 1, "asc" ]],
                "columnDefs": [
                    { "orderable": false, className: "dt-body-right", "targets": [ 0 ] } 
                ]
            } );
         } );
    </script>
        <hr class="divider-dashed" />
<?php endif; ?>
<?php if($h_settings['vmGuestIdMismatch'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php 
    $impactedVM = $xmlVM->xpath("/vms/vm[guestId!='Not Available' and guestId!=configGuestId]");
    if (count($impactedVM) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> VM GuestId Mismatch</h2>
        <div class="alert alert-warning" role="alert"><i>This module will display VM that have GuestOS setting different from GuestOS retrived through vmtools.</i></div>
        <div class="col-lg-12">
        <table id="guestMismatch" class="table table-hover">
            <thead><tr>
                <th>VM Name</th>
                <th>GuestId</th>
                <th>Config GuestId</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedVM as $vm) {
        echo '            <tr><td>' . $vm->name . '</td><td>' . $vm->guestId . '</td><td>' . $vm->configGuestId . '</td><td>' . $vm->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#guestMismatch').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> VM GuestId Mismatch <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['vmPoweredOff'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php 
    $impactedVM = $xmlVM->xpath("/vms/vm[powerState='poweredOff']");
    if (count($impactedVM) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> VM Powered Off</h2>
        <div class="alert alert-warning" role="alert"><i>This module will display VM that are Powered Off. This can be useful to check if this state is expected.</i></div>
        <div class="col-lg-12">
        <table id="poweredOffVM" class="table table-hover">
            <thead><tr>
                <th>VM Name</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedVM as $vm) {
        echo '            <tr><td>' . $vm->name . '</td><td>' . $vm->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#poweredOffVM').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
            } );
         } );
		</script>
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> VM Powered Off <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['vmGuestPivot'] != 'off' && $h_settings['inventory'] != 'off'): ?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> VM GuestId pivot table</h2>
        <div class="alert alert-warning" role="alert"><i>This module will display GuestOS pivot table and family repartition.</i></div>
<?php
    $dataWindows = array();
    $dataLinux = array();
    foreach (array_diff(array_count_values(array_map("strval", $xmlVM->xpath("/vms/vm/guestFamily"))), array("1")) as $key => $value) {
        $dataTemp = null;
        $dataTemp[] = $key;
        $dataTemp[] = $value;
        $data[] = $dataTemp;
    }
    foreach (array_diff(array_count_values(array_map("strval", $xmlVM->xpath("/vms/vm/guestFamily[text()='windowsGuest']/../guestOS"))), array("1")) as $key => $value) {
        $key = str_replace("Microsoft ", "", trim(preg_split("/\(/", str_replace("\xC2\xA0", " ", $key))[0]));
        if (array_key_exists($key, $dataWindows)) {
            $dataWindows[$key] += $value;
        } else {
            $dataWindows[$key] = $value;
        }
    }
    foreach ($dataWindows as $key => $value) { $dataWindowsHash[] = (object) array('data' => array($value), 'name' => $key); }
    foreach (array_diff(array_count_values(array_map("strval", $xmlVM->xpath("/vms/vm/guestFamily[text()='linuxGuest']/../guestOS"))), array("1")) as $key => $value) {
        # remove non breaking space
        $key = trim(preg_split("/\(/", str_replace("\xC2\xA0", " ", $key))[0]);
        if (array_key_exists($key, $dataLinux)) {
            $dataLinux[$key] += $value;
        } else {
            $dataLinux[$key] = $value;
        }
    }
    foreach ($dataLinux as $key => $value) { $dataLinuxHash[] = (object) array('y' => $value, 'name' => $key); }
?>
        <div class="col-lg-6">
        <table class="table table-hover">
            <thead><tr>
                <th>GuestOS</th>
                <th>Count</th>
            </thead>
            <tbody>
<?php
    $dataGuest = array_merge($dataWindows, $dataLinux);
    arsort($dataGuest);
    foreach ($dataGuest as $key => $value) {
        echo '            <tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
    }
?>
            </tbody> 
        </table>
        </div>
        <div class="col-lg-6">
            <div id="containerVMGuestPivot" style="height: 300px"></div>
            <div id="containerVMGuestWindows"></div>
            <div id="containerVMGuestLinux"></div>
        </div>
        <script type="text/javascript">

$(function () {
    $('#containerVMGuestPivot').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                //type: 'pie'
            },
            title: {
                text: 'Guest Family',
                align: 'center',
                verticalAlign: 'middle',
                y: 40
            },
            credits: false,
            exporting: false,
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    dataLabels: {
                        enabled: true,
                        distance: -30,
                        style: {
                            fontWeight: 'bold',
                            color: 'white',
                            textShadow: '0px 1px 2px black'
                        }
                    },
                    startAngle: -90,
                    endAngle: 90,
                    center: ['50%', '75%']
                }
            },
            series: [{
                type: 'pie',
                innerSize: '50%',
                name: 'GuestFamily',
                data: <?php echo json_encode($data, JSON_NUMERIC_CHECK); ?>
            }]
    });

    $('#containerVMGuestWindows').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: 'Monthly Average Rainfall'
        },
        subtitle: {
            text: 'Source: WorldClimate.com'
        },
        xAxis: {
            categories: [
                ''
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: ''
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                '<td style="padding:0"><b>{point.y}</b></td></tr>',
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: <?php echo json_encode($dataWindowsHash, JSON_NUMERIC_CHECK); ?>
    });

    $('#containerVMGuestLinux').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: { text: null },
            credits: false,
            exporting: false,
            tooltip: { pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>' },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                        style: { color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black' }
                    },
                    showInLegend: false
                }
            },
            series: [{
                name: 'Brands',
                colorByPoint: true,
                data: <?php echo json_encode($dataLinuxHash, JSON_NUMERIC_CHECK); ?>
            }]
    });
});

</script>

        <hr class="divider-dashed" />

<?php endif; ?>
<?php if($h_settings['vmMisnamed'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php
    $impactedVMtmp = $xmlVM->xpath("/vms/vm[fqdn!='Not Available']");
	$impactedVM = array();
	foreach ($impactedVMtmp as $vm) { if (!preg_match('/^' . $vm->name . '/i', $vm->fqdn)) { $impactedVM[] = $vm; } }
    if (count($impactedVM) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> VM misnamed</h2>
        <div class="alert alert-warning" role="alert"><i>This module will display VM that have FQDN (based on vmtools) mismatched with the VM object name.</i></div>
        <div class="col-lg-12">
        <table id="mismatchedVM" class="table table-hover">
            <thead><tr>
                <th>VM Name</th>
                <th>FQDN</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedVM as $vm) {
        echo '            <tr><td>' . $vm->name . '</td><td>'. $vm->fqdn . '</td><td>' . $vm->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#mismatchedVM').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
            } );
         } );
    </script>
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> VM misnamed <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
	</div>
<?php require("footer.php"); ?>
