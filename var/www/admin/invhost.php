<?php require("session.php"); ?>
<?php
$title = "VMHost Inventory";
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
$xmlSelectedPath = $scannedDirectories[0];
$selectedDate = DateTime::createFromFormat('Ymd', $scannedDirectories[0])->format('Y/m/d');

?>
	<div id="purgeLoading" style="display:flex;"><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>&nbsp; Loading inventory, please wait for awesomeness ...</div>
    <div style="display:none; padding-top: 10px; padding-bottom: 10px;" class="container" id="wrapper-container">
	<div class="row">
	<div class="col-lg-12 alert alert-info" style="margin-top: 20px; text-align: center;">
		<h1 style="margin-top: 10px;">VMHost Inventory on <?php echo DateTime::createFromFormat('Y/m/d', $selectedDate)->format('l jS F Y'); ?></h1>
	</div>
	</div>
    <div style="width:98%; padding:10px;">
        <table id="inventory" class="display table" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>vCenter</th>
                    <th>Cluster</th>
                    <th>ESX</th>
                    <th>NumCPU</th>
                    <th>NumCore</th>
                </tr>
            </thead>

            <tbody>
<?php
$xmlFile = "$xmlStartPath$xmlSelectedPath/hosts-global.xml";
$xml = simplexml_load_file($xmlFile);

foreach ($xml->host as $vmhost) {
echo "<tr>";
echo "<td>" . $vmhost->vcenter . "</td>";
echo "<td>" . $vmhost->cluster . "</td>";
echo "<td>" . $vmhost->name . "</td>";
echo "<td>" . $vmhost->numcpu . "</td>";
echo "<td>" . $vmhost->numcpucore . "</td>";
echo "</tr>";
}


?>
            </tbody>
        </table>
    </div>
    <script type="text/javascript">
        $(document).ready( function () {
            $('#inventory').DataTable( {
        dom: 'Bfrtip',
        buttons: [
            'csv', 'excel', 'pdf'
        ],
                    "search": {
                            "smart": false,
                            "regex": true
                    }
                }
            );
         } );
                 document.getElementById("wrapper-container").style.display = "block";
                 document.getElementById("purgeLoading").style.display = "none";
    </script>

<?php require("footer.php"); ?>
