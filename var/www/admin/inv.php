<?php
require("session.php");
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

try {
  # Main class loading
  $check = new SexiCheck();
  # Header generation
  $check->displayHeader($_SERVER['SCRIPT_NAME'], $visible = false);
} catch (Exception $e) {
  # Any exception will be ending the script, we want exception-free run
  # CSS hack for navbar margin removal
  echo '  <style>#wrapper { margin-bottom: 0px !important; }</style>'."\n";
  require("exception.php");
  exit;
}

?>
    <div style="width:98%; padding:10px;">
      <div>Show/Hide column:
        <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="1" data-column="1">VM</button>
        <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="2" data-column="2">vCenter</button>
        <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="3" data-column="3">Cluster</button>
        <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="4" data-column="4">Host</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="5" data-column="5">vmx Path</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="6" data-column="6">Portgroup</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="7" data-column="7">IP</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="8" data-column="8">NumCPU</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="9" data-column="9">MemoryMB</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="10" data-column="10">CommitedGB</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="11" data-column="11">ProvisionnedGB</button>
        <button type="button" class="btn btn-success btn-xs toggle-vis" style="outline: 5px auto;" name="12" data-column="12">Datastore</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="13" data-column="13">VMPath</button>
        <button type="button" class="btn btn-danger btn-xs toggle-vis" style="outline: 5px auto;" name="14" data-column="14">MAC</button>
      </div>
      <hr />
      <table id="inventory" class="display table" cellspacing="0" width="100%">
        <thead><tr>
          <th>id</th>
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
          <th>VM Path</th>
          <th>MAC</th>
        </tr></thead>
        <tbody>
        </tbody>
      </table>
    </div>
    <div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="plan-info" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <!-- <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
              <span class="glyphicon glyphicon-remove-circle"></span>
            </button>
            <h4 class="modal-title"><strong>VM Details<strong></h4>
          </div> -->
          <div class="modal-body"><!-- /# content will goes here after ajax calls --></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <script type="text/javascript">
      $(document).ready( function () {
        var table = $('#inventory').DataTable( {
          "language": { "infoFiltered": "" },
          "processing": true,
          "serverSide": true,
          "ajax": "server_processing.php?c=VMINVENTORY&t=<?php echo strtotime($check->getSelectedDate()); ?>",
          "deferRender": true,
          "search": {
            "smart": false,
            "regex": true
          },
          "columnDefs": [ { "targets": [ 0, 5, 6, 7, 8, 9, 10, 11, 13, 14 ], "visible": false } ]
        } );

        $('#inventory').on('click', 'a[rel=modal]', function(evt) {
          evt.preventDefault();
          var modal = $('#modal').modal();
          modal
            .find('.modal-body')
            .load($(this).attr('href'), function (responseText, textStatus) {
              if ( textStatus === 'success' || textStatus === 'notmodified') {
                modal.show();
              }
          });
        });

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
