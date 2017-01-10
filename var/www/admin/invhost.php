<?php
require("session.php");
$title = "Host Inventory";
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
  $check->displayHeader($_SERVER['SCRIPT_NAME'], $visible = false);
  
}
catch (Exception $e)
{
  
  # Any exception will be ending the script, we want exception-free run
  # CSS hack for navbar margin removal
  echo '  <style>#wrapper { margin-bottom: 0px !important; }</style>'."\n";
  require("exception.php");
  exit;

} # END try

?>
    <div style="width:98%; padding:10px;">
      <div>Show/Hide column:
        <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="1" data-column="1">Host</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="2" data-column="2">vCenter</button>
        <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="3" data-column="3">Cluster</button>
        <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="4" data-column="4">Socket</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="5" data-column="5">Core</button>
        <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="6" data-column="6">Memory</button>
        <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="7" data-column="7">Model</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="8" data-column="8">CPU Type</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="9" data-column="9">CPU Freq</button>
        <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="10" data-column="10">Build ESX</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="11" data-column="11">Group</button>
      </div>
  		<hr />
      <table id="inventory" class="table display" cellspacing="0" width="100%">
        <thead><tr>
          <th>id</th>
          <th>Host</th>
          <th>vCenter</th>
          <th>Cluster</th>
          <th>Socket</th>
          <th>Core</th>
          <th>Memory</th>
          <th>Model</th>
          <th>CPU Type</th>
          <th>CPU Freq</th>
          <th>Build ESX</th>
          <th>Group</th>
        </tr></thead>
        <tbody>
        </tbody>
      </table>
    </div>

    <script type="text/javascript">
      $(document).ready( function () {
        var table = $('#inventory').DataTable( {
          "language": { "infoFiltered": "" },
          "processing": true,
          "serverSide": true,
          "ajax": "server_processing.php?c=HOSTINVENTORY&t=<?php echo strtotime($check->getSelectedDate()); ?>",
          "deferRender": true,
          "search": {
            "smart": false,
            "regex": true
          },
          "columnDefs": [ { "targets": [ 0, 2, 5, 8, 9, 11 ], "visible": false } ],
          "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ]
        } );
  			new $.fn.dataTable.Buttons( table, { buttons: [ 'csv', 'excel' ] } );
        table.buttons().container().appendTo( '#inventory_wrapper .col-sm-6:eq(0)' );
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
