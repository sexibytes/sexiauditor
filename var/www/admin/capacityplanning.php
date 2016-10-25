<?php
# TO ADD TO GLOBAL OPTIONS
$vmLeftThreshold = 50;
$daysLeftThreshold = 180;
# END TO ADD TO GLOBAL OPTIONS

require("session.php");
$title = "Capacity Planning";
$additionalStylesheet = array('css/capacityplanning.css',
                              'css/whhg.css');
$additionalScript = array(  'js/vopendata.js',
                            'js/isotope.pkgd.min.js');
require("dbconnection.php");
require("header.php");
require("helper.php");

try
{
  
  # Main class loading
  $sexihelper = new SexiHelper();
  $sexigrafNode = $sexihelper->getConfig('sexigrafNode');

  try
  {
    
    # Testing connectivity to SexiGraf node
    if (!isHttpAvailable("http://$sexigrafNode:8080"))
    {
      
      throw new Exception('Connection to SexiGraf node "'. $sexigrafNode . '" seems impossible. Please check URL on settings page or check for network/firewall issue.');
      
    } # END if (!isHttpAvailable($sexigrafNode))
    
  }
  catch (Exception $e)
  {
    
    # Any exception will be ending the script, we want exception-free run
    # CSS hack for navbar margin removal
    echo '  <style>#wrapper { margin-bottom: 0px !important; }</style>'."\n";
    require("exception.php");
    exit;
    
  } # END try

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
  <div style="padding-top: 10px; padding-bottom: 10px;" class="container" id="wrapper-container">
    <div class="panel panel-default">
      <div class="panel-heading"><h3 class="panel-title">Capacity Planning Notes</h3></div>
      <div class="panel-body"><ul>
        <li>This capacity planning was computed based on the statistics of the last <?php echo $sexihelper->getConfig('capacityPlanningDays'); ?> days, administrators can change it in the settings of the appliance.</li>
        <li>The VM statistics represent average resources consumption based on the last <?php echo $sexihelper->getConfig('capacityPlanningDays'); ?> days</li>
        <li>Please refer to the <a href="http://www.sexiauditor.fr/">project website</a> and documentation for more information.</li>
      </ul></div>
    </div>
    <div class='navbar navbar-static-top' style="z-index: 0;">
      <div class='navbar-inner'>
        <div class=''>
          <ul class='nav navbar-top-links navbar-left' id='filters'>
            <li><i class="glyphicon glyphicon-th"></i> <b>Please select your view filter:</b></li>
            <li><a data-filter='*' href='#'>All</a></li>
            <li><a data-filter='.stat-vmleft' href='#'>Alarm on VM (&lt; <?php echo $vmLeftThreshold; ?> left)</a></li>
            <li><a data-filter='.stat-daysleft' href='#'>Alarm on days (&lt; <?php echo $daysLeftThreshold; ?> left)</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class='row'>
      <div class='span12'>
        <div class='well'>
          <div id='statsgrid'>
<?php
# We want to take a little safety percentage before dropping huge numbers :)
$safetyPct = 10;
$capacityPlanningDays = (int)$sexihelper->getConfig('capacityPlanningDays');
$showInfinite = $sexihelper->getConfig('showInfinite');
$capacityPlanningGroups = $db->get("capacityPlanningGroups", NULL, "group_name, members, percentageThreshold");

foreach ($capacityPlanningGroups as $capacityPlanningGroup)
{
  
  // if ($capacityPlanningGroup["group_name"] != "PROD-TIGERY-PLATINIUM") {continue;}
  $CPquery = $sexihelper->buildSqlQueryCPGroup($capacityPlanningGroup["members"]);
  $dateYesterday = date('Y-m-d', time() - 60 * 60 * 24);
  $dateBeginning = date('Y-m-d', time() - ($capacityPlanningDays * 24 * 60 * 60));
  # Retrieve current number of VM powered on
  $currentVmOn = $db->rawQueryValue("SELECT COUNT(v.id) AS NUMVMON FROM vms AS v INNER JOIN hosts AS h ON (h.id = v.host) INNER JOIN clusters AS c ON (c.id = h.cluster) WHERE $CPquery AND v.firstseen < '" . $dateYesterday . "' AND v.lastseen > '" . $dateYesterday . "' LIMIT 1");
  # Retrieve current statistices for compute (cpu and memory)
  $currentStatsCompute = $db->rawQueryOne("SELECT ROUND(SUM(h.memory)/1024/1024,0) AS MEMCAPA, SUM(h.cpumhz * h.numcpucore) AS CPUCAPA, SUM(hm.cpuUsage) AS CPUUSAGE, SUM(hm.memoryUsage) AS MEMUSAGE FROM hosts AS h INNER JOIN clusters AS c ON (h.cluster = c.id) INNER JOIN hostMetrics AS hm ON (hm.host_id = h.id) WHERE $CPquery AND h.firstseen < '" . $dateYesterday . "' AND h.lastseen > '" . $dateYesterday . "' AND hm.id IN (SELECT MAX(id) FROM hostMetrics WHERE lastseen < '" . $dateYesterday . " 23:59:59' GROUP BY host_id)");
  # Variable retrieving
  $currentMemCapacity = $currentStatsCompute['MEMCAPA'];
  $currentCpuCapacity = $currentStatsCompute['CPUCAPA'];
  $currentMemUsage = $currentStatsCompute['MEMUSAGE'];
  $currentCpuUsage = $currentStatsCompute['CPUUSAGE'];
  $currentMemUsagePct = round(100 * ($currentMemUsage / $currentMemCapacity));
  $currentCpuUsagePct = round(100 * ($currentCpuUsage / $currentCpuCapacity));
  # Retrieve current statistices for storage
  $currentStatsStorage = $db->rawQueryOne("SELECT SUM(size) AS STORAGECAPA, SUM(freespace) AS STORAGEFREE FROM (SELECT DISTINCT c.cluster_name, d.datastore_name, dm.size, dm.freespace FROM clusters AS c INNER JOIN hosts AS h ON c.id = h.cluster INNER JOIN datastoreMappings AS dma ON h.id = dma.host_id INNER JOIN datastores AS d ON dma.datastore_id = d.id INNER JOIN datastoreMetrics AS dm ON dm.datastore_id = d.id WHERE $CPquery AND d.firstseen < '" . $dateYesterday . "' AND d.lastseen > '" . $dateYesterday . "' AND dm.id IN (SELECT MAX(id) FROM datastoreMetrics WHERE lastseen < '" . $dateYesterday . "' GROUP BY datastore_id) ) AS T1");
  $currentStorageCapacity = $currentStatsStorage['STORAGECAPA'];
  $currentStorageUsage = $currentStorageCapacity - $currentStatsStorage['STORAGEFREE'];
  $currentStorageUsagePct = ceil(100 * ($currentStorageUsage / $currentStorageCapacity));
  $currentMaxUsagePct = max($currentMemUsagePct, $currentCpuUsagePct);
  $currentVmLeft = (int)round(min(((($capacityPlanningGroup["percentageThreshold"] - $safetyPct) * $currentVmOn / $currentMaxUsagePct) - $currentVmOn),((90 * $currentVmOn / $currentStorageUsagePct) - $currentVmOn)));
  $currentVmMemUsage = round($currentMemUsage / $currentVmOn);
  $currentVmCpuUsage = round($currentCpuUsage / $currentVmOn);
  $currentVmStorageUsage = round($currentStorageUsage / $currentVmOn);
  # Retrieve previous statistices based on $capacityPlanningDays for compute (cpu and memory)
  $previousVmOn = $db->rawQueryValue("SELECT COUNT(v.id) AS NUMVMON FROM vms AS v INNER JOIN hosts AS h ON (h.id = v.host) INNER JOIN clusters AS c ON (c.id = h.cluster) WHERE $CPquery AND v.firstseen < '" . $dateBeginning . "' AND v.lastseen > '" . $dateBeginning . "' LIMIT 1");
  # Retrieve previous statistices based on $capacityPlanningDays for compute (cpu and memory)
  $previousStatsCompute = $db->rawQueryOne("SELECT ROUND(SUM(h.memory)/1024/1024,0) AS MEMCAPA, SUM(h.cpumhz * h.numcpucore) AS CPUCAPA, SUM(hm.cpuUsage) AS CPUUSAGE, SUM(hm.memoryUsage) AS MEMUSAGE FROM hosts AS h INNER JOIN clusters AS c ON (h.cluster = c.id) INNER JOIN hostMetrics AS hm ON (hm.host_id = h.id) WHERE $CPquery AND h.firstseen < '" . $dateBeginning . "' AND h.lastseen > '" . $dateBeginning . "' AND hm.id IN (SELECT MAX(id) FROM hostMetrics WHERE lastseen < '" . $dateBeginning . " 23:59:59' GROUP BY host_id)");
  $previousMemCapacity = $previousStatsCompute['MEMCAPA'];
  $previousCpuCapacity = $previousStatsCompute['CPUCAPA'];
  $previousMemUsage = $previousStatsCompute['MEMUSAGE'];
  $previousCpuUsage = $previousStatsCompute['CPUUSAGE'];
  $previousMemUsagePct = round(100 * ($previousMemUsage / $previousMemCapacity));
  $previousCpuUsagePct = round(100 * ($previousCpuUsage / $previousCpuCapacity));
  # Retrieve previous statistices for storage
  $previousStatsStorage = $db->rawQueryOne("SELECT SUM(size) AS STORAGECAPA, SUM(freespace) AS STORAGEFREE FROM (SELECT DISTINCT c.cluster_name, d.datastore_name, dm.size, dm.freespace FROM clusters AS c INNER JOIN hosts AS h ON c.id = h.cluster INNER JOIN datastoreMappings AS dma ON h.id = dma.host_id INNER JOIN datastores AS d ON dma.datastore_id = d.id INNER JOIN datastoreMetrics AS dm ON dm.datastore_id = d.id WHERE $CPquery AND d.firstseen < '" . $dateBeginning . "' AND d.lastseen > '" . $dateBeginning . "' AND dm.id IN (SELECT MAX(id) FROM datastoreMetrics WHERE lastseen < '" . $dateBeginning . "' GROUP BY datastore_id) ) AS T1");
  $previousStorageCapacity = $previousStatsStorage['STORAGECAPA'];
  $previousStorageUsage = $previousStorageCapacity - $previousStatsStorage['STORAGEFREE'];
  $previousStorageUsagePct = ceil(100 * ($previousStorageUsage / $previousStorageCapacity));
  $previousMaxUsagePct = max($previousMemUsagePct, $previousCpuUsagePct);
  $previousVmLeft = round(min(((($capacityPlanningGroup["percentageThreshold"] - $safetyPct) * $previousVmOn / $previousMaxUsagePct) - $previousVmOn),((90 * $previousVmOn / $previousStorageUsagePct) - $previousVmOn)));
  $coefficientCapaPlan = ($currentVmLeft-$previousVmLeft)/$capacityPlanningDays;
  // var_dump("previousMemUsage: $previousMemUsage");
  // var_dump("previousMemCapacity: $previousMemCapacity");
  # if VM left count trend is negative, there will an exhaustion, we will compute the days based on this trend, if not we will display 'infinite' icon
  if ($coefficientCapaPlan < 0)
  {
    
    $daysLeft = (int)round(abs($currentVmLeft/$coefficientCapaPlan));
    
  }
  else
  {
    
    $daysLeft = "<i class=\"icon-infinityalt\"></i>";
    
  } # END if ($coefficientCapaPlan < 0)
  
  if ($coefficientCapaPlan < 0 || $showInfinite == 'enable')
  {
    
    $categoryStats = "stat-ok";
    $widget = "ok";

    if (is_int($currentVmLeft) && $currentVmLeft < $vmLeftThreshold)
    {
      
      $categoryStats .= " stat-vmleft";
      $widget = "vmleft";
      
    } # END if (is_int($vmLeftT1) && $vmLeftT1 < $vmLeftThreshold)
    
    if (is_int($daysLeft) && $daysLeft < $daysLeftThreshold)
    {
      
      $categoryStats .= " stat-daysleft";
      $widget = "daysleft";
      
    } # END if (is_int($daysLeft) && $daysLeft < $daysLeftThreshold)
    
    echo "         <div class='stat ".$categoryStats." wide2'>\n";
    echo "          <div class='widget widget-".$widget."'>\n";
    echo "            <div class='title'>".$capacityPlanningGroup["group_name"]."</div>\n";
    echo "            <div class='value'>" . $daysLeft . " <small>days left</small><br>" . $currentVmLeft . " <small>VM left</small></div>\n";
    echo "            <div class='more-info'>Based on maximum consumption threshold: " . $capacityPlanningGroup["percentageThreshold"] . "%<br/>Avg. consumption: <i class='glyphicon icon-cpu-processor'></i> " .  human_filesize($currentVmCpuUsage*1000*1000,0,'Hz') . " <i class='glyphicon icon-ram'></i> " .  human_filesize($currentVmMemUsage*1024*1024,0) . " <i class='glyphicon icon-database'></i> " .  human_filesize($currentVmStorageUsage,0) . "</div>\n";
    echo "          </div>\n";
    echo "        </div>\n";
        
  } # END if ($coefficientCapaPlan < 0 || $showInfinite == 'enable')
  
} # END foreach ($capacityPlanningGroups as $capacityPlanningGroup)

?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require("footer.php"); ?>
