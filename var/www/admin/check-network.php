<?php
require("session.php");
$title = "Network Checks";
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
try
{
  
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

if ($check->getModuleSchedule('networkDVSportsfree') != 'off')
{
  
  $check->displayCheck([  'sqlQuery' => "SELECT main.name, main.autoexpand, main.numports, main.openports, v.vcname as vcenter FROM distributedvirtualportgroups main INNER JOIN vcenters v ON main.vcenter = v.id WHERE main.openports < " . $check->getConfig('networkDVSVSSportsfree'),
                          "id" => "NETWORKDVSPORTSFREE",
                          'thead' => array('Portgroup Name', 'Auto Expand', 'NumPorts', 'OpenPorts', 'PercentFree', 'vCenter'),
                          'tbody' => array('"<td>".$entry["name"]."</td>"', '"<td>".($entry["autoexpand"] ? \'<i class="glyphicon glyphicon-ok alarm-green"></i>\' : \'<i class="glyphicon glyphicon-remove alarm-red"></i>\')."</td>"', '"<td>".$entry["numports"]."</td>"', '"<td>".$entry["openports"]."</td>"', '"<td>".(($entry["numports"] > 0) ? round(100 * ($entry["openports"] / $entry["numports"])) : 0)."</td>"', '"<td>".$entry["vcenter"]."</td>"'),
                          'order' => '[ 0, "desc" ]',
                          'columnDefs' => '{ "orderable": false, className: "dt-body-center", "targets": [ 1 ] }, { className: "dt-body-center", "targets": [ 2, 3, 4 ] }']);

} # END if ($check->getModuleSchedule('networkDVSportsfree') != 'off')

?>
    <h2 class="not-available"><i class="glyphicon glyphicon-remove-sign"></i> DVS profile <small>(Soon)</small></h2>
	</div>
<?php require("footer.php"); ?>
