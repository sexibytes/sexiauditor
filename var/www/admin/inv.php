<?php require("session.php"); ?>
<?php
$title = "VM Inventory";
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

?>
	<div id="purgeLoading" style="display:flex;"><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>&nbsp; Loading inventory, please wait for awesomeness ...</div> 
    <div style="display:none; padding-top: 10px; padding-bottom: 10px;" class="container" id="wrapper-container">
	<div class="row">
	<div class="col-lg-10 alert alert-info" style="margin-top: 20px; text-align: center;">
		<h1 style="margin-top: 10px;">VM Inventory on <?php echo DateTime::createFromFormat('Y/m/d', $selectedDate)->format('l jS F Y'); ?></h1>
	</div>
	
	<div class="alert col-lg-2">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" style="margin-top: 5px;" method="post">
        <div class="form-group" style="margin-bottom: 5px;">
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
    <div style="width:98%; padding:10px;">
        <div>Show/Hide column: 
            <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="0" data-column="0">VM</button> 
            <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="1" data-column="1">vCenter</button> 
            <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="2" data-column="2">Cluster</button> 
            <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="3" data-column="3">Host</button> 
            <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="4" data-column="4">vmx Path</button> 
            <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="5" data-column="5">Portgroup</button> 
            <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="6" data-column="6">IP</button> 
            <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="7" data-column="7">NumCPU</button> 
            <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="8" data-column="8">MemoryMB</button>
            <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="9" data-column="9">CommitedGB</button>
            <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="10" data-column="10">ProvisionnedGB</button>
            <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="11" data-column="11">Datastore</button>
            <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="12" data-column="12">MAC</button>
        </div>
        <hr />
        <table id="inventory" class="display table" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>VM</th>
                    <th>vCenter</th>
                    <th>Cluster</th>
                    <th>Host</th>
                    <th>vmx Path</th>
                    <th>Portgroup</th>
                    <th>IP</th>
                    <th>NumCPU</th>
                    <th>MemoryMB</th>
                    <th>CommitedGB</th>
                    <th>ProvisionnedGB</th>
                    <th>Datastore</th>
                    <th>MAC</th>
                </tr>
            </thead>

            <tbody>
<?php
$xmlFile = "$xmlStartPath$xmlSelectedPath/vms-global.xml";
$xml = simplexml_load_file($xmlFile);

foreach ($xml->vm as $vm) {
echo "<tr>";
echo "<td>" . $vm->name . "</td>";
echo "<td>" . $vm->vcenter . "</td>";
echo "<td>" . $vm->cluster . "</td>";
echo "<td>" . $vm->host . "</td>";
echo "<td>" . $vm->vmxpath . "</td>";
echo "<td>" . str_ireplace(',','<br/>',$vm->portgroup) . "</td>";
echo "<td>" . str_ireplace(',','<br/>',$vm->ip) . "</td>";
echo "<td>" . $vm->numcpu . "</td>";
echo "<td>" . $vm->memory . "</td>";
echo "<td>" . $vm->commited . "</td>";
echo "<td>" . $vm->provisionned . "</td>";
echo "<td>" . str_ireplace(',','<br/>',$vm->datastore). "</td>";
echo "<td>" . str_ireplace(',','<br/>',$vm->mac) . "</td>";
echo "</tr>";

}


?>
            </tbody>
        </table>
    </div>
    <script type="text/javascript">
        $(document).ready( function () {
            var table = $('#inventory').DataTable( {
                                "search": {
                                        "smart": false,
                                        "regex": true
                                },
                    "columnDefs": [ { "targets": [ 4, 5, 6, 7, 8, 9, 10, 12 ], "visible": false } ]
                }
            );
            $('button.toggle-vis').on( 'click', function (e) {
                    e.preventDefault();
                    var column = table.column( $(this).attr('data-column') );
                    column.visible( ! column.visible() );
                var nodeList = document.getElementsByName( $(this).attr('data-column') );
                var regexMatch = new RegExp("btn-success","g");
                if (nodeList[0].className.match(regexMatch)) {
                    nodeList[0].className = "btn btn-danger btn-xs toggle-vis btn-no-outline";
                } else {
                    nodeList[0].className = "btn btn-success btn-xs toggle-vis btn-no-outline";
                }
            } );
         } );
                 document.getElementById("wrapper-container").style.display = "block";
                 document.getElementById("purgeLoading").style.display = "none";
    </script>

<?php require("footer.php"); ?>
