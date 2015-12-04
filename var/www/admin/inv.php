<?php
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;
?>
<!doctype html>
<html>
    <head>
        <title>Offline Inventory</title>
        
        <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css"/>
        <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.min.css"/>
        <link rel="stylesheet" type="text/css" href="css/sexigraf.css"/>
        <link rel="stylesheet" type="text/css" href="css/bootstrap-datetimepicker.css" rel="stylesheet"> 

                <script type="text/javascript" src="js/jquery.min.js"></script>
                <script type="text/javascript" src="js/bootstrap.min.js"></script>
                <script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
                <script type="text/javascript" src="js/jszip.min.js"></script>
                <script type="text/javascript" src="js/dataTables.autoFill.min.js"></script>
                <script type="text/javascript" src="js/autoFill.bootstrap.min.js"></script>
                <script type="text/javascript" src="js/dataTables.buttons.min.js"></script>
                <script type="text/javascript" src="js/buttons.bootstrap.min.js"></script>
                <script type="text/javascript" src="js/buttons.colVis.min.js"></script>
                <script type="text/javascript" src="js/buttons.html5.min.js"></script>
                <script type="text/javascript" src="http://momentjs.com/downloads/moment.js"></script>
                <script src="//cdn.rawgit.com/Eonasdan/bootstrap-datetimepicker/e8bddc60e73c1ec2475f827be36e1957af72e2ea/src/js/bootstrap-datetimepicker.js"></script>
    </head>
    <body>

<?php
$xmlStartPath = "/opt/vcron/data/";
$scannedDirectories = array_values(array_diff(scandir($xmlStartPath, SCANDIR_SORT_DESCENDING), array('..', '.')));
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $selectedDate = $_POST["selectedDate"];
    foreach ($scannedDirectories as $key => $value) {
        if (strpos($value, str_replace("/","",$selectedDate)) === 0) {
         $xmlSelectedPath = $value;
         break;
        }
    }
} else {
    $selectedDate = DateTime::createFromFormat('YmdHi', $scannedDirectories[0])->format('Y/m/d');
}
?>
<div class="container">
<form class="form-inline" action="inv.php" method="post">
<!-- <div class="container">
    <div class="col-sm-2" style="height:130px;"> -->
        <div class="form-group">
            <label for="datetimepicker11">Select your date:</label>
            <div class='input-group date' id='datetimepicker11'>
                <input type='text' class="form-control" name="selectedDate" readonly />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar">
                    </span>
                </span>
            </div>
        </div>
        <button type="submit" class="btn btn-default">Select this date</button>
<!--    </div> -->
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
        echo '                  "' . DateTime::createFromFormat('YmdHi', $xmlDirectory)->format('Y/m/d H:i') . '",' . "\n";
    }
?>
                ]
            });
        });
    </script>
<!-- </div> -->
</form>
</div>
<?php 
if ($_SERVER['REQUEST_METHOD'] == 'POST'):

?>
   <div id="purgeLoading" style="display:flex;">
            <span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>&nbsp; Loading inventory, please wait for awesomeness ...
    </div>
    <div id="wrapper" style="width:98%; padding:10px; display:none;">
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
        </div><br />
       <div name="export-button">Export in: </div>
        <hr />
        <table id="inventory" class="display" cellspacing="0" width="100%">
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
$xmlFile = "/opt/vcron/data/$xmlSelectedPath/vms-global.xml";
$xml = simplexml_load_file($xmlFile);

foreach ($xml->vm as $vm) {
echo "<tr>";
echo "<td>" . $vm->name . "</td>";
echo "<td>" . $vm->VCENTER . "</td>";
echo "<td>" . $vm->CLUSTER . "</td>";
echo "<td>" . $vm->HOST . "</td>";
echo "<td>" . $vm->VMXPATH . "</td>";
echo "<td>" . $vm->PORTGROUP . "</td>";
echo "<td>" . $vm->IP . "</td>";
echo "<td>" . $vm->NUMCPU . "</td>";
echo "<td>" . $vm->MEMORY . "</td>";
echo "<td>" . $vm->COMMITED . "</td>";
echo "<td>" . $vm->PROVISIONNED . "</td>";
echo "<td>" . $vm->DATASTORE . "</td>";
echo "<td>" . $vm->MAC . "</td>";
echo "</tr>";

}


?>
            </tbody>
        </table>
        <div class="generated">Page generated @ 2015-11-26 06:03 UTC</div>
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
            new $.fn.dataTable.Buttons( table, { buttons: [ 'csv', 'excel' ] } );
            table.buttons().container().appendTo( $(document.getElementsByName('export-button')) );
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
                 document.getElementById("wrapper").style.display = "block";
                 document.getElementById("purgeLoading").style.display = "none";
    </script>
<?php endif; ?>
<?php
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);
echo 'Page generated in '.$total_time.' seconds.';
?>
</body>
</html>
