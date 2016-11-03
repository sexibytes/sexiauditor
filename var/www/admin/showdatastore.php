<?php
// require("session.php");
require("helper.php");

if (empty($_GET['dsid']))
{
  
  exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> Missing mandatory values</div>');

} # END if (empty($_GET['hostid']))


try
{
  
  # Main class loading
  $sexihelper = new SexiHelper();
  
}
catch (Exception $e)
{
  
  # Any exception will be ending the script, we want exception-free run
  # CSS hack for navbar margin removal
  echo '  <style>#wrapper { margin-bottom: 0px !important; }</style>'."\n";
  require("exception.php");
  exit;

} # END try

if (!is_array($datastore = $sexihelper->getDatastoreInfos($_GET['dsid'])))
{
  
  exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> ' . $datastore . '</div>');
  
} # END if (!is_array($datastore = $sexihelper->getHostInfos($_GET['hostid'])))

?>


<div class="row">
    <div class="col-sm-4 col-xs-6">
        <div class="tile-stats tile-red">
            <div class="icon">
                <i class="glyphicon icon-database"></i>
            </div>
            <div class="num">
                <?php echo human_filesize($datastore["size"]); ?>
            </div>
            <h3>Size</h3>
        </div>
    </div>
    <div class="col-sm-4 col-xs-6">
        <div class="tile-stats tile-green">
            <div class="icon">
                <i class="glyphicon icon-database"></i>
            </div>
            <div class="num">
                <?php echo human_filesize($datastore["freespace"]); ?>
            </div>
            <h3>Free space</h3>
        </div>
    </div>
    <div class="clear visible-xs"></div>
    <div class="col-sm-4 col-xs-6">
        <div class="tile-stats tile-aqua">
            <div class="icon">
                <i class="glyphicon icon-database"></i>
            </div>
            <div class="num">
              <?php echo human_filesize($datastore["size"]-$datastore["freespace"]+$datastore["uncommitted"]); ?>
            </div>
            <h3>Provisioned</h3>
        </div>
    </div>
</div>
<div style="min-height:20px;">&nbsp;</div>
<div class="row" style="margin-bottom:0px; font-size: 14px">
  <div class="col-sm-6">
    <div class="panel panel-default">
      <div class="panel-heading text-center"><strong>Hardware Configuration</strong></div>
      <div class="panel-body" style="padding: 0px;">
        <table>
          <tr><td class="table-title text-right">Maintenance Mode</td><td style="padding-left: 10px;"><?php echo $datastore["maintenanceMode"]; ?></td></tr>
          <tr><td class="table-title text-right">Storage I/O Control</td><td style="padding-left: 10px;"><?php echo $enableStatus[$datastore["iormConfiguration"]]; ?></td></tr>
          <tr><td class="table-title text-right">Accessible</td><td style="padding-left: 10px;"><?php echo $enableStatus[$datastore["isAccessible"]]; ?></td></tr>
          <tr><td class="table-title text-right">Shared</td><td style="padding-left: 10px;"><?php echo $enableStatus[$datastore["shared"]]; ?></td></tr>
        </table>
      </div>
    </div>
  </div>
  <div class="col-sm-6">
    <div class="panel panel-default">
      <div class="panel-heading text-center"><strong>Identity</strong></div>
      <div class="panel-body" style="padding: 0px;">
        <table>
          <tr><td class="table-title text-right">Name</td><td style="padding-left: 10px;"><?php echo (string)$datastore["datastore_name"]; ?></td></tr>
          <tr><td class="table-title text-right">Type</td><td style="padding-left: 10px;"><?php echo (string)$datastore["type"]; ?></td></tr>
          <tr><td class="table-title text-right">MoRef</td><td style="padding-left: 10px;"><?php echo (string)$datastore["moref"]; ?></td></tr>
          <tr><td class="table-title text-right">vCenter</td><td style="padding-left: 10px;"><?php echo (string)$datastore["vcenter"]; ?></td></tr>
        </table>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  var linkElement = document.createElement("link");
  linkElement.rel = "stylesheet";
  linkElement.href = "css/whhg.css";
  document.head.appendChild(linkElement);
<?php if (!empty($_GET['vmidsource'])) : ?>
  $("#modal-previous").attr('href', 'showvm.php?vmid=<?php echo $_GET['vmidsource']; ?>');
  $("#modal-previous").css('display', 'inline');
<?php endif; ?>
</script>

</body>
</html>
