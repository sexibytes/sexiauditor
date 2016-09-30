<?php
require("session.php");
require("helper.php");

if (empty($_GET['vmid']))
{
  
  exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> Missing mandatory values</div>');

} # END if (empty($_GET['vmid']))

try
{
  
  # Main class loading
  $sexiclass = new SexiClass();
  
}
catch (Exception $e)
{
  
  # Any exception will be ending the script, we want exception-free run
  exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> ' . $e->getMessage() . '</div>');
  
} # END try

if (!is_array($vm = $sexiclass->getVMInfos($_GET['vmid'])))
{
  
  exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> ' . $vm . '</div>');
  
} # END if (!is_array($vm = $sexiclass->getVMInfos($_GET['vmid'])))

?>

<div class="row">
    <div class="col-sm-4 col-xs-6">
        <div class="tile-stats tile-red">
            <div class="icon">
                <i class="glyphicon icon-cpu-processor"></i>
            </div>
            <div class="num">
                <?php echo $vm["numcpu"]; ?> vCPU
            </div>
            <h3>Number of vCPU</h3>
        </div>
    </div>
    <div class="col-sm-4 col-xs-6">
        <div class="tile-stats tile-green">
            <div class="icon">
                <i class="glyphicon icon-ram"></i>
            </div>
            <div class="num">
                <?php echo $vm["memory"]; ?> MB
            </div>
            <h3>Total of memory</h3>
        </div>
    </div>
    <div class="clear visible-xs"></div>
    <div class="col-sm-4 col-xs-6">
        <div class="tile-stats tile-aqua">
            <div class="icon">
                <i class="glyphicon icon-database"></i>
            </div>
            <div class="num">
              <?php echo (string)$vm["provisionned"]; ?> GB
            </div>
            <h3>Total of storage</h3>
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
          <tr><td class="table-title text-right">Last Seen</td><td style="padding-left: 10px;"><?php echo (string)$vm["lastseen"]; ?></td></tr>
          <tr><td class="table-title text-right">Connection State</td><td style="padding-left: 10px;"><?php echo (string)$vm["connectionState"]; ?></td></tr>
          <tr><td class="table-title text-right">Power State</td><td style="padding-left: 10px;"><?php echo (string)$vm["powerState"]; ?></td></tr>
          <tr><td class="table-title text-right">IP Address</td><td style="padding-left: 10px;"><?php echo (string)$vm["ip"]; ?></td></tr>
          <tr><td class="table-title text-right">MAC Address</td><td style="padding-left: 10px;"><?php echo (string)$vm["mac"]; ?></td></tr>
          <tr><td class="table-title text-right">Portgroup</td><td style="padding-left: 10px;"><?php echo (string)$vm["portgroup"]; ?></td></tr>
          <tr><td class="table-title text-right">Commited</td><td style="padding-left: 10px;"><?php echo (int)$vm["commited"]; ?> GB</td></tr>
          <tr><td class="table-title text-right">Uncommited</td><td style="padding-left: 10px;"><?php echo (int)$vm["uncommited"]; ?> GB</td></tr>
          <tr><td class="table-title text-right">Provisionned</td><td style="padding-left: 10px;"><?php echo (int)$vm["provisionned"]; ?> GB</td></tr>
          <tr><td class="table-title text-right">VM Tools</td><td style="padding-left: 10px;"><?php echo (int)$vm["vmtools"]; ?></td></tr>
          <tr><td class="table-title text-right">Hardware Version</td><td style="padding-left: 10px;"><?php echo (string)$vm["hwversion"]; ?></td></tr>
        </table>
      </div>
    </div>
  </div>
  <div class="col-sm-6">
    <div class="panel panel-default">
      <div class="panel-heading text-center"><strong>Identity</strong></div>
      <div class="panel-body" style="padding: 0px;">
        <table>
          <tr><td class="table-title text-right">Name</td><td style="padding-left: 10px;"><?php echo (string)$vm["name"]; ?></td></tr>
          <tr><td class="table-title text-right">FQDN</td><td style="padding-left: 10px;"><?php echo (string)$vm["fqdn"]; ?></td></tr>
          <tr><td class="table-title text-right">MoRef</td><td style="padding-left: 10px;"><?php echo (string)$vm["moref"]; ?></td></tr>
          <tr><td class="table-title text-right">ESX Host</td><td style="padding-left: 10px;"><a href="showhost.php?hostid=<?php echo $vm["hostid"]; ?>&amp;vmidsource=<?php echo $vm["id"]; ?>" rel="modal"><?php echo $vm["host"]; ?> <i class="glyphicon glyphicon-share"></i></a></td></tr>
          <tr><td class="table-title text-right">Cluster</td><td style="padding-left: 10px;"><?php echo (string)$vm["cluster"]; ?></td></tr>
          <tr><td class="table-title text-right">vCenter</td><td style="padding-left: 10px;"><?php echo (string)$vm["vcenter"]; ?></td></tr>
          <tr><td class="table-title text-right">Guest OS</td><td style="padding-left: 10px;"><?php echo (string)$vm["guestOS"]; ?></td></tr>
<?php

if (is_array($datastore = $sexiclass->getDatastoreInfos($vm["datastore"], $vm["vcenterID"])))
{
  
  $datastoreName = $datastore["datastore_name"];
  
  if ($datastore["pct_free"] < 10)
  {
    
    $labelFreeColor = "danger";
    
  }
  elseif ($datastore["pct_free"] < 20)
  {
    
    $labelFreeColor = "warning";
    
  }
  else
  {
    
    $labelFreeColor = "success";
    
  } # END if ($datastore["pct_free"] < 10)
  
  $additionalDatastoreInfos = " <span class=\"label label-primary\">" . human_filesize($datastore["size"]) . " total size</span> <span class=\"label label-" . $labelFreeColor . "\">" . $datastore["pct_free"] . "% free</span>";

}
else
{
  
  $datastoreName = "Undefined";
  $additionalDatastoreInfos = " <span class=\"label label-default\">Unknow infos</span>";
  
} # END if (is_array($datastore = $sexiclass->getDatastoreInfos($vm["datastore"], $vm["vcenterID"])))

?>
          <tr><td class="table-title text-right">Datastore</td><td style="padding-left: 10px;"><a href="showdatastore.php?dsid=<?php echo $vm["datastore"]; ?>&amp;vmidsource=<?php echo $vm["id"]; ?>" rel="modal"><?php echo $datastoreName; ?> <i class="glyphicon glyphicon-share"></i> <?php echo $additionalDatastoreInfos; ?></a></td></tr>
          <tr><td class="table-title text-right">VMX Path</td><td style="padding-left: 10px;"><?php echo (string)$vm["vmxpath"]; ?></td></tr>
          <tr><td class="table-title text-right">VM Path</td><td style="padding-left: 10px;"><?php echo (string)$vm["vmpath"]; ?></td></tr>
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
  $("#modal-previous").css('display', 'none');
</script>