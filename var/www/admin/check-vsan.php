<?php
require("session.php");
$title = "VSAN Checks";
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

if ($check->getModuleSchedule('VSANHealthCheck') != 'off' && $check->getModuleSchedule('inventory') != 'off')
{
  
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM clustersVSAN AS main INNER JOIN (SELECT cluster_id, MAX(lastseen) AS ts FROM clustersVSAN GROUP BY cluster_id) maxt ON (maxt.cluster_id = main.cluster_id AND maxt.ts = main.lastseen) INNER JOIN clusters AS c ON (main.cluster_id = c.id) WHERE (main.autohclupdate <> 'green' OR main.hcldbuptodate <> 'green' OR main.controlleronhcl <> 'green' OR main.controllerreleasesupport <> 'green' OR main.controllerdriver <> 'green')",
                          "id" => "VSANHARDWARECOMPATIBILITY",
                          "typeCheck" => 'ssp',
                          'thead' => array('Cluster Name', '<a href="https://kb.vmware.com/kb/2109870">HCL DB up-to-date</a>', '<a href="https://kb.vmware.com/kb/2146132">HCL DB Auto Update</a>', '<a href="https://kb.vmware.com/kb/2109871">SCSI Controller on Virtual SAN HCL</a>', '<a href="https://kb.vmware.com/kb/2109262">Controller Release Support</a>', '<a href="https://kb.vmware.com/kb/2109263">Controller Driver</a>', 'vCenter'),
                          'columnDefs' => '{ "orderable": false, className: "dt-body-center", "targets": [ 1,2,3,4,5 ] }']);


  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM clustersVSAN AS main INNER JOIN (SELECT cluster_id, MAX(lastseen) AS ts FROM clustersVSAN GROUP BY cluster_id) maxt ON (maxt.cluster_id = main.cluster_id AND maxt.ts = main.lastseen) INNER JOIN clusters AS c ON (main.cluster_id = c.id) WHERE (main.clusterpartition <> 'green' OR main.vmknicconfigured <> 'green' OR main.matchingsubnets <> 'green' OR main.matchingmulticast <> 'green')",
                          "id" => "VSANNETWORK",
                          "typeCheck" => 'ssp',
                          'thead' => array('Cluster Name', '<a href="https://kb.vmware.com/kb/2108011">Virtual SAN cluster partition</a>', '<a href="https://kb.vmware.com/kb/2108062">All hosts have a Virtual SAN vmknic configured</a>', '<a href="https://kb.vmware.com/kb/2108066">All hosts have matching subnets</a>', '<a href="https://kb.vmware.com/kb/2108092">All hosts have matching multicast settings</a>', 'vCenter'),
                          'columnDefs' => '{ "orderable": false, className: "dt-body-center", "targets": [ 1,2,3,4 ] }']);


  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM clustersVSAN AS main INNER JOIN (SELECT cluster_id, MAX(lastseen) AS ts FROM clustersVSAN GROUP BY cluster_id) maxt ON (maxt.cluster_id = main.cluster_id AND maxt.ts = main.lastseen) INNER JOIN clusters AS c ON (main.cluster_id = c.id) WHERE (main.physdiskoverall <> 'green' OR main.physdiskmetadata <> 'green' OR main.physdisksoftware <> 'green' OR main.physdiskcongestion <> 'green')",
                          "id" => "VSANPHYSICALDISK",
                          "typeCheck" => 'ssp',
                          'thead' => array('Cluster Name', '<a href="https://kb.vmware.com/kb/2108691">Overall disks health</a>', '<a href="https://kb.vmware.com/kb/2108690">Metadata health</a>', '<a href="https://kb.vmware.com/kb/2108910">Software state health</a>', '<a href="https://kb.vmware.com/kb/2109255">Congestion</a>', 'vCenter'),
                          'columnDefs' => '{ "orderable": false, className: "dt-body-center", "targets": [ 1,2,3,4 ] }']);

                          
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM clustersVSAN AS main INNER JOIN (SELECT cluster_id, MAX(lastseen) AS ts FROM clustersVSAN GROUP BY cluster_id) maxt ON (maxt.cluster_id = main.cluster_id AND maxt.ts = main.lastseen) INNER JOIN clusters AS c ON (main.cluster_id = c.id) WHERE (main.healthversion <> 'green' OR main.advcfgsync <> 'green' OR main.clomdliveness <> 'green' OR main.diskbalance <> 'green' OR main.upgradesoftware <> 'green' OR main.upgradelowerhosts <> 'green')",
                          "id" => "VSANCLUSTER",
                          "typeCheck" => 'ssp',
                          'thead' => array('Cluster Name', '<a href="https://kb.vmware.com/kb/2107705">Virtual SAN Health Service up-to-date</a>', '<a href="https://kb.vmware.com/kb/2107713">Advanced Virtual SAN configuration in sync</a>', '<a href="https://kb.vmware.com/kb/2109873">Virtual SAN CLOMD liveness</a>', '<a href="https://kb.vmware.com/kb/2144278">Virtual SAN Disk Balance</a>', '<a href="https://kb.vmware.com/kb/2146134">Software version compatibility</a>', '<a href="https://kb.vmware.com/kb/2146135">Disk format version</a>', 'vCenter'),
                          'columnDefs' => '{ "orderable": false, className: "dt-body-center", "targets": [ 1,2,3,4 ] }']);

} # END if ($check->getModuleSchedule('VSANHealthCheck') != 'off' && $check->getModuleSchedule('inventory') != 'off')

?>

    <!-- <h1>Performance service</h1>
    <h2>Stats DB object</h2> -->
    
    <!-- <h2>Unexpected Virtual SAN cluster members</h2>
    <h2>Hosts with Virtual SAN disabled</h2>
    <h2>Basic (unicast) connectivity check (normal ping)</h2>
    <h2>MTU check (ping with large packet size)</h2>
    <h2>Multicast assessment based on other checks</h2> -->
    
    <!-- <h1>Physical disk</h1>
    <h2>Overall disks health</h2>
    <h2>Metadata health</h2>
    <h2>Software state health</h2>
    <h2>Congestion</h2> -->
    
    <!-- <h1>Data</h1>
    <h2>Virtual SAN object health</h2> -->
    
    <!-- <h1>Cluster</h1>
    <h2>Virtual SAN Health Service up-to-date</h2>
    <h2>Advanced Virtual SAN configuration in sync</h2>
    <h2>Virtual SAN CLOMD liveness</h2>
    <h2>Virtual SAN Disk Balance</h2>
    <h2>Software version compatibility</h2>
    <h2>Disk format version</h2> -->
    
    <!-- <h1>Limits</h1>
    <h2>Current cluster situation</h2>
    <h2>After 1 additional host failure</h2>
    <h2>Host component limit</h2> -->
  </div>
<?php require("footer.php"); ?>
