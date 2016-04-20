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
	<div class="row">
		<div class="col-lg-10 alert alert-info" style="margin-top: 20px; text-align: center;">
			<h1 style="margin-top: 10px;">Cluster Checks on <?php echo DateTime::createFromFormat('Y/m/d', $selectedDate)->format('l jS F Y'); ?></h1>
		</div>

		<div class="alert col-lg-2">
			<form action="check-cluster.php" style="margin-top: 5px;" method="post">
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
	$xmlClusterFile = "$xmlStartPath$xmlSelectedPath/clusters-global.xml";
    if (is_readable($xmlClusterFile)) {
		$xmlCluster = simplexml_load_file($xmlClusterFile);
		$xpathFullCluster = $xmlCluster->xpath("/clusters/cluster");
		$xmlClusterDOM = new DOMDocument;
		$xmlClusterDOM->load($xmlClusterFile);
		$xmlClusterDOM = new DOMXPath($xmlClusterDOM);
    } else {
        echo '  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Error:</span> File ' . $xmlClusterFile . ' is not existant or not readable. Please check for <a href="/admin/sandbox.php">module selection</a> and/or wait for scheduler</div>';
    }
	
	$xmlHostFile = "$xmlStartPath$xmlSelectedPath/hosts-global.xml";
    if (is_readable($xmlHostFile)) {
		$xmlHost = simplexml_load_file($xmlHostFile);
		$xpathFullHost = $xmlCluster->xpath("/clusters/cluster");
		$xmlHostDOM = new DOMDocument;
		$xmlHostDOM->load($xmlHostFile);
		$xmlHostDOM = new DOMXPath($xmlHostDOM);
    } else {
        echo '  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Error:</span> File ' . $xmlHostFile . ' is not existant or not readable. Please check for <a href="/admin/sandbox.php">module selection</a> and/or wait for scheduler</div>';
    }
	
	$xmlVMFile = "$xmlStartPath$xmlSelectedPath/vms-global.xml";
    if (is_readable($xmlVMFile)) {
        // $xmlVM = simplexml_load_file($xmlVMFile);
		// $xpathFullVM = $xmlVM->xpath("/vms/vm");
		$xmlVMDOM = new DOMDocument;
		$xmlVMDOM->load($xmlVMFile);
		$xmlVMDOM = new DOMXPath($xmlVMDOM);
    } else {
        echo '  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Error:</span> File ' . $xmlVMFile . ' is not existant or not readable. Please check for <a href="/admin/sandbox.php">module selection</a> and/or wait for scheduler</div>';
    }
?>

<?php if($h_settings['clusterConfigurationIssues'] != 'off' && $h_settings['inventory'] != 'off' && isset($xmlCluster)): ?>
<?php 
    $impactedCluster = $xmlCluster->xpath("/clusters/cluster[lastconfigissue!='0']");
    if (count($impactedCluster) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> Cluster with Configuration Issues</h2>
        <div class="alert alert-warning" role="alert"><i>The following clusters have HA configuration issues. This will impact your disaster recovery.</i></div>
        <div class="col-lg-12">
        <table id="clusterConfigurationIssues" class="table table-hover">
            <thead><tr>
                <th>Cluster Name</th>
                <th>HA Status</th>
                <th>Last Config Issue</th>
                <th>Time</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedCluster as $cluster) {
        echo '            <tr><td>' . $cluster->name . '</td><td>'. (($cluster->dasenabled == '1') ? '<i class="glyphicon glyphicon-remove-sign text-danger"></i>' : '<i class="glyphicon glyphicon-ok-sign text-success"></i>') . '</td><td>'. $cluster->lastconfigissue . '</td><td>'. $cluster->lastconfigissuetime . '</td><td>' . $cluster->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#clusterConfigurationIssues').DataTable( {
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
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> Cluster with Configuration Issues <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
<?php if($h_settings['clusterHAStatus'] != 'off' && $h_settings['inventory'] != 'off' && isset($xmlCluster)): ?>
<?php 
    $impactedCluster = $xmlCluster->xpath("/clusters/cluster[dasenabled!='1']");
    if (count($impactedCluster) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> Cluster Without HA</h2>
        <div class="alert alert-warning" role="alert"><i>The following cluster does not have HA enabled. You should check if that's expected as this is a must have feature!</i></div>
        <div class="col-lg-12">
        <table id="clusterHAStatus" class="table table-hover">
            <thead><tr>
                <th>Cluster Name</th>
                <th>HA Status</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
    foreach ($impactedCluster as $cluster) {
        echo '            <tr><td>' . $cluster->name . '</td><td class="text-danger"><i class="glyphicon glyphicon-remove-sign"></i> no HA</td><td>' . $cluster->vcenter . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#clusterHAStatus').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> Cluster Without HA <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
		<h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> clusterAdmissionControl <small><?php echo rand_line($achievementFile); ?></small></h2>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> clusterDatastoreConsistency <small><?php echo rand_line($achievementFile); ?></small></h2>

<?php if($h_settings['clusterMembersVersion'] != 'off' && $h_settings['inventory'] != 'off' && isset($xmlHost)): ?>
<?php
    if (count($xpathFullCluster) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> Hosts Build Number Mismatch</h2>
        <div class="alert alert-warning" role="alert"><i>Display ESX build number by cluster, in order to spot potential intracluster build mismatch</i></div>
        <div class="col-lg-12">
        <table id="clusterMembersVersion" class="table table-hover">
            <thead><tr>
                <th>Cluster Name</th>
                <th>Compliance</th>
                <th>Build Number</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
	foreach (array_diff(array_count_values(array_map("strval", $xmlHost->xpath("/hosts/host/vcenter"))), array("1")) as  $key_vcenter => $value_vcenter) {
		foreach (array_diff(array_count_values(array_map("strval", $xmlHost->xpath("/hosts/host[vcenter='".$key_vcenter."']/cluster"))), array("1")) as  $key_cluster => $value_cluster) {
			if ($key_cluster == 'Standalone') { continue; }
			$mismatchMatches = array_diff(array_count_values(array_map("strval", $xmlHost->xpath("/hosts/host[vcenter='".$key_vcenter."' and cluster='".$key_cluster."']/esxbuild"))), array("1"));
			if (count($mismatchMatches) > 1) {
				$compliance = '<i class="glyphicon glyphicon-remove-sign text-danger"></i>';
			} else {
				$compliance = '<i class="glyphicon glyphicon-ok-sign text-success"></i>';
			}
			$builds = "";
			foreach (array_diff(array_count_values(array_map("strval", $xmlHost->xpath("/hosts/host[vcenter='".$key_vcenter."' and cluster='".$key_cluster."']/esxbuild"))), array("1"	)) as  $key_build => $value_build) {
				$builds .= " $key_build";
			}
			echo '            <tr><td>' . $key_cluster . '</td><td>' . $compliance . '</td><td>' . $builds . '</td><td>' . $key_vcenter . '</td></tr>';
		}
	}
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#clusterMembersVersion').DataTable( {
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
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> Hosts Build Number <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> clusterMembersOvercommit <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php if($h_settings['clusterMembersLUNPathCountMismatch'] != 'off' && $h_settings['inventory'] != 'off' && isset($xmlCluster) && isset($xmlHost)): ?>
<?php
    if (count($xpathFullCluster) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> Cluster With Members LUN Path Count Mismatch</h2>
        <div class="alert alert-warning" role="alert"><i>The following cluster members does not have the same number of LUN, please check for mapping or masking misconfiguration</i></div>
        <div class="col-lg-12">
        <table id="clusterMembersLUNPathCountMismatch" class="table table-hover">
            <thead><tr>
                <th>Cluster Name</th>
                <th>Majority Path Count</th>
                <th>Host Name</th>
                <th>LUN Path Count</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
	foreach (array_diff(array_count_values(array_map("strval", $xmlHost->xpath("/hosts/host/vcenter"))), array("1")) as  $key_vcenter => $value_vcenter) {
		foreach (array_diff(array_count_values(array_map("strval", $xmlHost->xpath("/hosts/host[vcenter='".$key_vcenter."']/cluster"))), array("1")) as  $key_cluster => $value_cluster) {
			if ($key_cluster == 'Standalone') { continue; }
			$majorityLunCount = array_diff(array_count_values(array_map("strval", $xmlHost->xpath("/hosts/host[vcenter='".$key_vcenter."' and cluster='".$key_cluster."']/lunpathcount"))), array("1"));
			if (count($majorityLunCount) > 1) {
				arsort($majorityLunCount);
			}
			$majorityLunCount = array_keys($majorityLunCount)[0];
			foreach ($xmlHost->xpath("/hosts/host[vcenter='".$key_vcenter."' and cluster='".$key_cluster."' and lunpathcount!='".$majorityLunCount."']") as $esxhosts) {
				echo '            <tr><td>' . $key_cluster . '</td><td>' . $majorityLunCount . '</td><td>' . $esxhosts->name . '</td><td>' . $esxhosts->lunpathcount . '</td><td>' . $key_vcenter . '</td></tr>';
			}
		}
	}
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#clusterMembersLUNPathCountMismatch').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
            } );
         } );
        </script>
        <hr class="divider-dashed" />
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> clusterMembersLUNPathCountMismatch <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>

		
		

		
		
<?php if($h_settings['clusterCPURatio'] != 'off' && $h_settings['inventory'] != 'off' && isset($xmlHostDOM) && isset($xmlVMDOM)): ?>
<?php
	$impactedCluster = array();
	foreach ($xpathFullCluster as $cluster) {
		if ($cluster->name == 'Standalone') { continue; }
		$pCPU = (int) $xmlHostDOM->evaluate('sum(/hosts/host[cluster=\''.strtolower($cluster->name).'\']/numcpucore)');
		$vCPU = (int) $xmlVMDOM->evaluate('sum(/vms/vm[cluster=\''.strtolower($cluster->name).'\']/numcpu)');
		if ($vCPU > 0 and $pCPU > 0 and ($vCPU / $pCPU) > $h_modulesettings['thresholdCPURatio']) {$impactedCluster[] = $cluster; }
	}
    if (count($impactedCluster) > 0):
?>
        <h2 class="text-danger"><i class="glyphicon glyphicon-exclamation-sign"></i> Ratio Virtual/Physical CPU</h2>
        <div class="alert alert-warning" role="alert"><i>Display ratio of virtual CPU per physical CPU that goes over threshold of <?php echo $h_modulesettings['thresholdCPURatio']; ?></i></div>
        <div class="col-lg-12">
        <table id="clusterCPURatio" class="table table-hover">
            <thead><tr>
                <th>Cluster Name</th>
                <th>pCPU</th>
                <th>vCPU</th>
                <th>CPU ratio</th>
                <th>vCenter</th>
            </thead>
            <tbody>
<?php
	foreach ($impactedCluster as  $cluster) {
		$pCPU = (int) $xmlHostDOM->evaluate('sum(/hosts/host[cluster=\''.strtolower($cluster->name).'\']/numcpucore)');
		$vCPU = (int) $xmlVMDOM->evaluate('sum(/vms/vm[cluster=\''.strtolower($cluster->name).'\']/numcpu)');
		echo '            <tr><td>' . $cluster->name . '</td><td>' . $pCPU . '</td><td>' . $vCPU . '</td><td>' . (($vCPU > 0 and $pCPU > 0) ? round($vCPU / $pCPU, 1) . ' : 1' : 'N/A'). '</td><td>' . $cluster->vcenter . '</td></tr>';
	}
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#clusterCPURatio').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
            } );
         } );
		</script>
<?php elseif ($h_modulesettings['showEmpty'] == 'enable'): /* else count */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> clusterCPURatio <small><?php echo rand_line($achievementFile); ?></small></h2>
<?php endif; /* endif count */ ?>
<?php endif; /* endif module */ ?>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> clusterTPSSavings <small><?php echo rand_line($achievementFile); ?></small></h2>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> clusterAutoSlotSize <small><?php echo rand_line($achievementFile); ?></small></h2>
        <h2 class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> clusterProfile <small><?php echo rand_line($achievementFile); ?></small></h2>
	</div>
<?php require("footer.php"); ?>