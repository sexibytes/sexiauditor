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
  
  $check->displayCheck([  'sqlQuery' => "SELECT main.id FROM clustersVSAN AS main INNER JOIN clusters AS c ON (main.cluster_id = c._id) WHERE true",
                          "id" => "VSANHEALTHCHECK",
                          "typeCheck" => 'ssp',
                          'thead' => array('Cluster Name', 'hcldbuptodate', 'autohclupdate', 'controlleronhcl', 'controllerreleasesupport', 'controllerdriver')]);

} # END if ($check->getModuleSchedule('VSANHealthCheck') != 'off' && $check->getModuleSchedule('inventory') != 'off')


?>
    <h1>Hardware compatibility</h1>
    <h2>Virtual SAN HCL DB up-to-date</h2>
    <h2>Virtual SAN HCL DB Auto Update</h2>
    <h2>SCSI Controller on Virtual SAN HCL</h2>
    <h2>Controller Release Support</h2>
    <h2>Controller Driver</h2>
    <h1>Performance service</h1>
    <h2>Stats DB object</h2>
    <h1>Network</h1>
    <h2>Hosts disconnected from VC</h2>
    <h2>Hosts with connectivity issues</h2>
    <h2>Virtual SAN cluster partition</h2>
    <h2>Unexpected Virtual SAN cluster members</h2>
    <h2>Hosts with Virtual SAN disabled</h2>
    <h2>All hosts have a Virtual SAN vmknic configured</h2>
    <h2>All hosts have matching subnets</h2>
    <h2>All hosts have matching multicast settings</h2>
    <h2>Basic (unicast) connectivity check (normal ping)</h2>
    <h2>MTU check (ping with large packet size)</h2>
    <h2>Multicast assessment based on other checks</h2>
    <h1>Physical disk</h1>
    <h2>Overall disks health</h2>
    <h2>Metadata health</h2>
    <h2>Disk capacity</h2>
    <h2>Software state health</h2>
    <h2>Congestion</h2>
    <h2>Component limit health</h2>
    <h2>Component metadata health</h2>
    <h2>Memory pools (heaps)</h2>
    <h2>Memory pools (slabs)</h2>
    <h1>Data</h1>
    <h2>Virtual SAN object health</h2>
    <h1>Cluster</h1>
    <h2>ESX Virtual SAN Health service installation</h2>
    <h2>Virtual SAN Health Service up-to-date</h2>
    <h2>Advanced Virtual SAN configuration in sync</h2>
    <h2>Virtual SAN CLOMD liveness</h2>
    <h2>Virtual SAN Disk Balance</h2>
    <h2>Deduplication and compression configuration consistency</h2>
    <h2>Disk group with incorrect deduplication and compression configuration</h2>
    <h2>Software version compatibility</h2>
    <h2>Disk format version</h2>
    <h1>Limits</h1>
    <h2>Current cluster situation</h2>
    <h2>After 1 additional host failure</h2>
    <h2>Host component limit</h2>
  </div>
<?php require("footer.php"); ?>
