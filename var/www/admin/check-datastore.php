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
    <div style="padding-top: 10px; padding-bottom: 10px;" class="container">
	<div class="col-lg-12">
		<div class="col-lg-10 alert alert-info" style="margin-top: 20px; text-align: center;">
			<h1 style="margin-top: 10px;">Datastore Checks on <?php echo DateTime::createFromFormat('Y/m/d', $selectedDate)->format('l jS F Y'); ?></h1>
		</div>
	
		<div class="alert col-lg-2">
			<form action="check-datastore.php" style="margin-top: 5px;" method="post">
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
    $xmlDatastoreFile = "$xmlStartPath$xmlSelectedPath/datastores-global.xml";
    if (is_readable($xmlDatastoreFile)) {
        $xmlDatastore = simplexml_load_file($xmlDatastoreFile);
    } else {
        exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Error:</span> File ' . $xmlDatastoreFile . ' is not existant or not readable</div>');
    }
?>
<?php if($h_settings['datastoreSpacereport'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php
    if (!isset($xpathFullDatastore)) { $xpathFullDatastore = $xmlDatastore->xpath("/datastores/datastore"); }
	$impactedDatastore = array();
	foreach ($xpathFullDatastore as $datastore) { if ((($datastore->freespace / $datastore->size) * 100) < $h_modulesettings['datastoreFreeSpaceThreshold']) { $impactedDatastore[] = $datastore; } }
    if (count($impactedDatastore) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> Datastore Space report</h2>
        <div class="alert alert-warning" role="alert"><i>Datastores which run out of space will cause impact on the virtual machines held on these datastores.</i></div>
        <div class="col-lg-12">
        <table id="datastoreSpacereport" class="table table-hover">
            <thead><tr>
                <th>Datastore Name</th>
                <th>Capacity</th>
                <th>FreeSpace</th>
                <th>% Free</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedDatastore as $datastore) {
        echo '            <tr><td>' . $datastore->name . '</td><td>' . human_filesize($datastore->size) . '</td><td>' . human_filesize($datastore->freespace) . '</td><td>' . round((($datastore->freespace / $datastore->size) * 100),0) . '%</td><td>' . $datastore->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#datastoreSpacereport').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
				columnDefs: [{ type: 'file-size', targets: [ 1, 2 ] }]
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> Datastore Space report <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> Orphaned VM Files report <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php if($h_settings['datastoreOverallocation'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php
    if (!isset($xpathFullDatastore)) { $xpathFullDatastore = $xmlDatastore->xpath("/datastores/datastore"); }
	$impactedDatastore = array();
	foreach ($xpathFullDatastore as $datastore) { if (((($datastore->size - $datastore->freespace + $datastore->uncommitted) * 100) / $datastore->size) > $h_modulesettings['datastoreOverallocation']) { $impactedDatastore[] = $datastore; } }
    if (count($impactedDatastore) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> Overallocation</h2>
        <div class="alert alert-warning" role="alert"><i>The following datastores may be overcommitted (overallocation > <?php echo $h_modulesettings['datastoreOverallocation']; ?>%), it is strongly suggested you check these.</i></div>
        <div class="col-lg-12">
        <table id="datastoreOverallocation" class="table table-hover">
            <thead><tr>
                <th>Datastore Name</th>
                <th>Capacity</th>
                <th>FreeSpace</th>
                <th>Uncommitted</th>
                <th>Overallocation</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedDatastore as $datastore) {
        echo '            <tr><td>' . $datastore->name . '</td><td>' . human_filesize($datastore->size) . '</td><td>' . human_filesize($datastore->freespace) . '</td><td>' . human_filesize($datastore->uncommitted) . '</td><td>' . round(((($datastore->size - $datastore->freespace + $datastore->uncommitted) * 100) / $datastore->size),0) . '%</td><td>' . $datastore->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#datastoreOverallocation').DataTable( {
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
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> Overallocation <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['datastoreSIOCdisabled'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php 
    $impactedDatastore = $xmlDatastore->xpath("/datastores/datastore[iormConfiguration=0]");
    if (count($impactedDatastore) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> Datastore with SIOC disabled</h2>
        <div class="alert alert-warning" role="alert"><i>Datastores with Storage I/O Control Disabled can impact the performance of your virtual machines.</i></div>
        <div class="col-lg-12">
        <table id="datastoreIORMDisabled" class="table table-hover">
            <thead><tr>
                <th>Datastore Name</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedDatastore as $datastore) {
        echo '            <tr><td>' . $datastore->name . '</td><td>' . $datastore->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#datastoreIORMDisabled').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> Datastore with SIOC disabled <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['datastoremaintenancemode'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php 
    $impactedDatastore = $xmlDatastore->xpath("/datastores/datastore[maintenanceMode!='normal']");
    if (count($impactedDatastore) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> Datastore in Maintenance Mode</h2>
        <div class="alert alert-warning" role="alert"><i>Datastore held in Maintenance mode will not be hosting any virtual machine, check the below Datastore are in an expected state.</i></div>
        <div class="col-lg-12">
        <table id="datastoreInMaintenance" class="table table-hover">
            <thead><tr>
                <th>Datastore Name</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedDatastore as $datastore) {
        echo '            <tr><td>' . $datastore->name . '</td><td>' . $datastore->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#datastoreInMaintenance').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> Datastore in Maintenance Mode <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['datastoreAccessible'] != 'off' && $h_settings['inventory'] != 'off'): ?>
<?php 
    $impactedDatastore = $xmlDatastore->xpath("/datastores/datastore[accessible!=1]");
    if (count($impactedDatastore) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> Datastore not Accessible</h2>
        <div class="alert alert-warning" role="alert"><i>The following datastores are not in 'Accessible' state, which mean there is a connectivity issue and should be investiguated.</i></div>
        <div class="col-lg-12">
        <table id="datastoreAccessible" class="table table-hover">
            <thead><tr>
                <th>Datastore Name</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedDatastore as $datastore) {
        echo '            <tr><td>' . $datastore->name . '</td><td>' . $datastore->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#datastoreAccessible').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success">Datastore not Accessible <i class="glyphicon glyphicon-ok"></i> <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
	</div>
<?php require("footer.php"); ?>
