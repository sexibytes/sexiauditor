<?php
require("session.php");
require("helper.php");

if (empty($_GET['hostid']))
{
  
  exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> Missing mandatory values</div>');

} # END if (empty($_GET['hostid']))

try
{
  
  # Main class loading
  $check = new SexiCheck();
  
}
catch (Exception $e)
{
  
  # Any exception will be ending the script, we want exception-free run
  exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> ' . $e->getMessage() . '</div>');
  
} # END try

if (!is_array($esxhost = $check->getHostInfos($_GET['hostid'])))
{
  
  exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> ' . $esxhost . '</div>');
  
} # END if (!is_array($esxhost = $check->getHostInfos($_GET['hostid'])))

?>

<div class="row">
    <div class="col-sm-6 col-xs-6">
        <div class="tile-stats tile-red">
            <div class="icon">
                <i class="glyphicon icon-cpu-processor"></i>
            </div>
            <div class="num">
                <?php echo $esxhost["numcpucore"]; ?> cores
            </div>
            <h3>Total of cores</h3>
        </div>
    </div>
    <div class="col-sm-6 col-xs-6">
        <div class="tile-stats tile-green">
            <div class="icon">
                <i class="glyphicon icon-ram"></i>
            </div>
            <div class="num">
                <?php echo round($esxhost["memory"]/1024/1024/1024); ?> GB
            </div>
            <h3>Total of memory</h3>
        </div>
    </div>
</div>
<div style="min-height:20px;">&nbsp;</div>
<div class="row" style="margin-bottom:0px; font-size: 14px;">
  <div class="col-sm-6">
    <div class="panel panel-default">
      <div class="panel-heading text-center"><strong>vHardware Configuration</strong></div>
      <div class="panel-body" style="padding: 0px;">
        <table>
          <tr><td class="table-title text-right">Model</td><td style="padding-left: 10px;"><?php echo (string)$esxhost["model"]; ?></td></tr>
          <tr><td class="table-title text-right">CPU Type</td><td style="padding-left: 10px;"><?php echo (string)$esxhost["cputype"]; ?></td></tr>
          <tr><td class="table-title text-right">CPU MHz</td><td style="padding-left: 10px;"><?php echo (int)$esxhost["cpumhz"]; ?> MHz</td></tr>
          <tr><td class="table-title text-right">DNS Servers</td><td style="padding-left: 10px;"><?php echo (string)$esxhost["dnsservers"]; ?></td></tr>
          <tr><td class="table-title text-right">SSH Policy</td><td style="padding-left: 10px;"><?php echo (string)$esxhost["ssh_policy"]; ?></td></tr>
          <tr><td class="table-title text-right">Shell Policy</td><td style="padding-left: 10px;"><?php echo (string)$esxhost["shell_policy"]; ?></td></tr>
        </table>
      </div>
    </div>
  </div>
  <div class="col-sm-6">
    <div class="panel panel-default">
      <div class="panel-heading text-center"><strong>Identity</strong></div>
      <div class="panel-body" style="padding: 0px;">
        <table>
          <tr><td class="table-title text-right">Name</td><td style="padding-left: 10px;"><?php echo (string)$esxhost["host_name"]; ?></td></tr>
          <tr><td class="table-title text-right">DNS Name</td><td style="padding-left: 10px;"><?php echo (string)$esxhost["hostname"]; ?></td></tr>
          <tr><td class="table-title text-right">ESX Build</td><td style="padding-left: 10px;"><?php echo (string)$esxhost["esxbuild"]; ?></td></tr>
          <tr><td class="table-title text-right">MoRef</td><td style="padding-left: 10px;"><?php echo (string)$esxhost["moref"]; ?></td></tr>
          <tr><td class="table-title text-right">Cluster</td><td style="padding-left: 10px;"><?php echo (string)$esxhost["cluster"]; ?></td></tr>
          <tr><td class="table-title text-right">vCenter</td><td style="padding-left: 10px;"><?php echo (string)$esxhost["vcenter"]; ?></td></tr>
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
