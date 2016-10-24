<?php
require("session.php");
$title = "Platform stats";
$additionalStylesheet = array('css/vopendata.css');
$additionalScript = array(  'js/vopendata.js',
                            'js/isotope.pkgd.min.js');
require("dbconnection.php");
require("header.php");
require("helper.php");
$dateLastSearch = date("Y-m-d") . " 00:00:01";
$dateFirstSearch = date("Y-m-d") . " 23:59:59";
# get vcenters info
$totalVCs = $db->getValue("vcenters", "COUNT(vcenters.id)");
# get clusters info
$db->where('clusters.lastseen', $dateLastSearch, ">=");
# We must exclude the 'Standalone' cluster from count
$db->where('clusters.id', 1, "<>");
$totalClusters = $db->getValue("clusters", "COUNT(DISTINCT clusters.id)");
# get hosts info
$db->where('hosts.lastseen', $dateLastSearch, ">=");
$totalHosts = $db->getValue("hosts", "COUNT(DISTINCT hosts.vcenter, hosts.moref)");
# get vms info
$db->where('vms.lastseen', $dateLastSearch, ">=");
$totalVMs = $db->getValue("vms", "COUNT(DISTINCT vms.vcenter, vms.moref)");

if ($totalVCs == 0 || $totalClusters == 0 || $totalHosts == 0 || $totalVMs == 0)
{

  ##########################################
  # vOpenData default sample               #
  # It's used only at the beginning before #
  # any infrastructure have been added     #
  ##########################################
  $introductionLabel = 'This is a selection of statistics from the <a href="http://www.vopendata.org">vOpenData project</a>. Want to see you infrastructure metrics instead? Add your infrastructure in the Admin View > Credential Store!';
  $totalVMs = "168K";
  $totalVCs = 395;
  $totalClusters = "1.6K";
  $totalHosts = "13.9K";
  $totalVMFS = "34.4K";
  $totalNFS = 0;
  $totalDatastore = "31.4K";
  $averageVMPervCenter = 426;
  $totalHostsCPU = 0;
  $totalHostsCPUMhz = 0;
  $totalHostsMemory = 0;
  $totalDatastoreSize = 0;
  $totalvMotion = 0;
  $totalBandwidth = 0;
  $totalTPSSavings = 0;
  $averageVMPerCluster = 89.4;
  $averageVMPerHost = 12.2;
  $averageVMDKCommitedSize = 75.72;
  $averageVMDKUncommitedSize = 0;
  $averageVMDKProvisionedSize = 75.72;
  $sortedTabGuestOS = array(  array("guestOS" => "Microsoft Windows Server 2008 R2 (64-bit)", "total" => "20.9%"),
                              array("guestOS" => "Microsoft Windows Server 2003 Standard (64-bit)", "total" => "12.4%"),
                              array("guestOS" => "Red Hat Enterprise Linux 5 (64-bit)", "total" => "8.6%"),
                              array("guestOS" => "Microsoft Windows Server 2003 Standard (32-bit)", "total" => "6.7%"),
                              array("guestOS" => "Microsoft Windows XP Professional (32-bit)", "total" => "6.2%"),
                              array("guestOS" => "Ubuntu Linux (64-bit)", "total" => "5.8%"),
                              array("guestOS" => "Microsoft Windows 7 (64-bit)", "total" => "5.1%"),
                              array("guestOS" => "Red Hat Enterprise Linux 6 (64-bit)", "total" => "3.9%"),
                              array("guestOS" => "Microsoft Windows Server 2008 (64-bit)", "total" => "3.4%"),
                              array("guestOS" => "Microsoft Windows 7 (32-bit)", "total" => "2.5%") );
  $sortedHostModel = array(   array("model" => "HP", "total" => "48.5%"),
                              array("model" => "Dell", "total" => "16.8%"),
                              array("model" => "Dell Inc.", "total" => "15.8%"),
                              array("model" => "IBM", "total" => "11.1%"),
                              array("model" => "Cisco Systems Inc", "total" => "5.6%") );
  $sortedHostCPUType = array( array("cputype" => "Intel Xeon E5-2970", "total" => "48.5%"),
                              array("cputype" => "Cyrix", "total" => "16.8%"),
                              array("cputype" => "AMD G2", "total" => "15.8%"),
                              array("cputype" => "Pentium M", "total" => "11.1%"),
                              array("cputype" => "A9X", "total" => "5.6%") );
  $sortedESXBuild = array(    array("esxbuild" => "ESX 5.5 build 23123", "total" => "48.5%"),
                              array("esxbuild" => "sdfsdfs", "total" => "16.8%"),
                              array("esxbuild" => "sdfsdfsdf", "total" => "15.8%"),
                              array("esxbuild" => "sdfsdfsdfaa", "total" => "11.1%"),
                              array("esxbuild" => "eqdnspdfn", "total" => "5.6%") );
  $averageHostPervCenter = 37;
  $averageClusterPervCenter = 4;
  $averageHostPerCluster = "5.4";
  $averageDatastorePerCluster = "20.5";
  $averageMemoryPerHost = "118.91 GB";
  $averageCPUPerHost = "2.1";
  $averageDatastoreSize = 0;
  $averageVMMemory = 0;
  $averageVMCPU = 0;
  
}
else
{
  
  $introductionLabel = 'This is a selection of statistics from your platform, based on the <a href="http://www.vopendata.org">vOpenData project</a>. These will be updated every time scheduler is running !';
  $vmMetricsSubQ = $db->subQuery();
  $vmMetricsSubQ->where('firstseen', $dateFirstSearch, '<=');
  $vmMetricsSubQ->where('lastseen', $dateLastSearch, '>=');
  $vmMetricsSubQ->groupBy("vm_id");
  $vmMetricsSubQ->get("vmMetrics", null, "MAX(id)");
  $db->join("vmMetrics", "vms.id = vmMetrics.vm_id", "INNER");
  $db->where('vmMetrics.id', $vmMetricsSubQ, "IN");
  $totalCommited = $db->getValue("vms", "SUM(vmMetrics.commited)");
  $vmMetricsSubQ = $db->subQuery();
  $vmMetricsSubQ->where('firstseen', $dateFirstSearch, '<=');
  $vmMetricsSubQ->where('lastseen', $dateLastSearch, '>=');
  $vmMetricsSubQ->groupBy("vm_id");
  $vmMetricsSubQ->get("vmMetrics", null, "MAX(id)");
  $db->join("vmMetrics", "vms.id = vmMetrics.vm_id", "INNER");
  $db->where('vmMetrics.id', $vmMetricsSubQ, "IN");
  $totalUncommited = $db->getValue("vms", "SUM(vmMetrics.uncommited)");
  (int)$totalProvisioned = $db->rawQueryValue("SELECT SUM(provisionned) FROM (SELECT DISTINCT vcenter, moref, provisionned FROM vms WHERE lastseen >= '".$dateLastSearch."') AS T1 LIMIT 1");
  (int)$totalVMCPU = $db->rawQueryValue("SELECT SUM(numcpu) FROM (SELECT DISTINCT vcenter, moref, numcpu FROM vms WHERE lastseen >= '".$dateLastSearch."') AS T1 LIMIT 1");
  (int)$totalVMMemory = $db->rawQueryValue("SELECT SUM(memory) FROM (SELECT DISTINCT vcenter, moref, memory FROM vms WHERE lastseen >= '".$dateLastSearch."') AS T1 LIMIT 1");
  (int)$totalVMFS = $db->rawQueryValue("SELECT COUNT(*) FROM (SELECT DISTINCT vcenter, moref FROM datastores WHERE lastseen >= '".$dateLastSearch."' AND type = 'VMFS') AS T1 LIMIT 1");
  (int)$totalNFS = $db->rawQueryValue("SELECT COUNT(*) FROM (SELECT DISTINCT vcenter, moref FROM datastores WHERE lastseen >= '".$dateLastSearch."' AND shared = 1 AND type = 'NFS') AS T1 LIMIT 1");
  $totalDatastore = $totalNFS + $totalVMFS;
  (int)$totalHostsCPU = $db->rawQueryValue("SELECT SUM(numcpu) FROM (SELECT DISTINCT vcenter, moref, numcpu FROM hosts WHERE lastseen >= '".$dateLastSearch."') AS T1 LIMIT 1");
  (int)$totalHostsCPUMhz = $db->rawQueryValue("SELECT SUM(cpumhz) FROM (SELECT DISTINCT vcenter, moref, cpumhz FROM hosts WHERE lastseen >= '".$dateLastSearch."') AS T1 LIMIT 1");
  (int)$totalHostsMemory = $db->rawQueryValue("SELECT SUM(memory) FROM (SELECT DISTINCT vcenter, moref, memory FROM hosts WHERE lastseen >= '".$dateLastSearch."') AS T1 LIMIT 1");
  (int)$totalDatastoreSize = $db->rawQueryValue("SELECT SUM(size) FROM (SELECT DISTINCT datastore_id, size FROM datastoreMetrics WHERE lastseen >= '".$dateLastSearch."' GROUP BY datastore_id) AS T1 LIMIT 1");
  (int)$totalvMotion = $db->rawQueryValue("SELECT SUM(vmotion) FROM (SELECT DISTINCT cluster_id, vmotion FROM clusterMetrics WHERE lastseen >= '".$dateLastSearch."' GROUP BY cluster_id) AS T1 LIMIT 1");
  (int)$totalTPSSavings = $db->rawQueryValue("SELECT SUM(sharedmemory) FROM (SELECT DISTINCT host_id, sharedmemory FROM hostMetrics WHERE lastseen >= '".$dateLastSearch."' GROUP BY host_id) AS T1 LIMIT 1");
  $totalBandwidth = 0;
  $averageVMPervCenter = round($totalVMs / $totalVCs);
  $averageVMPerCluster = round($totalVMs / $totalClusters);
  $averageVMPerHost = round($totalVMs / $totalHosts);
  $averageVMDKCommitedSize = round($totalCommited / $totalVMs, 0);
  $averageVMDKProvisionedSize = round($totalProvisioned / $totalVMs, 0);
  $averageVMDKUncommitedSize = round($totalUncommited / $totalVMs, 0);
  $db->where('lastseen', $dateLastSearch, ">=");
  $db->where('guestOS', '', '<>');
  $db->groupBy("guestOS");
  $db->orderBy("total","desc");
  $sortedTabGuestOS = $db->get("vms", 11, "guestOS, COUNT(*) as total");
  $db->where('hosts.lastseen', $dateLastSearch, ">=");
  $db->groupBy("hosts.model");
  $db->orderBy("total","desc");
  $sortedHostModel = $db->get("hosts", 5, "hosts.model, COUNT(*) as total");
  $db->where('hosts.lastseen', $dateLastSearch, ">=");
  $db->groupBy("hosts.cputype");
  $db->orderBy("total","desc");
  $sortedHostCPUType = $db->get("hosts", 5, "hosts.cputype, COUNT(*) as total");
  $db->where('hosts.lastseen', $dateLastSearch, ">=");
  $db->groupBy("hosts.esxbuild");
  $db->orderBy("total","desc");
  $sortedESXBuild = $db->get("hosts", 5, "hosts.esxbuild, COUNT(*) as total");
  $averageHostPervCenter = round($totalHosts / $totalVCs, 0);
  $averageClusterPervCenter = round($totalClusters / $totalVCs, 0);
  $averageHostPerCluster = round($totalHosts / $totalClusters,0);
  $averageDatastorePerCluster = round($totalDatastore / $totalClusters,0);
  $averageMemoryPerHost = human_filesize($totalHostsMemory / $totalHosts,0);
  $averageCPUPerHost = round($totalHostsCPU / $totalHosts,0);
  $averageDatastoreSize = round($totalDatastoreSize / $totalDatastore,0);
  $averageVMMemory = human_filesize(1024 * 1024 * $totalVMMemory / $totalVMs,0);
  $averageVMCPU = round($totalVMCPU / $totalVMs,0);

} # END if ($totalVCs == 0 || $totalClusters == 0 || $totalHosts == 0 || $totalVMs == 0)

?>

  <div class='container'>
  <div class='navbar navbar-static-top' style="z-index: 0;">
    <div class='navbar-inner'>
      <div class=''>
        <ul class='nav navbar-top-links navbar-left' id='filters'>
          <li><i class="glyphicon glyphicon-th"></i> <b>Please select your view scope:</b></li>
          <li><a data-filter='*' href='#'>All</a></li>
          <li><a data-filter='.stat-infra' href='#'>Infrastructure</a></li>
          <li><a data-filter='.stat-vcenter' href='#'>vCenter</a></li>
          <li><a data-filter='.stat-cluster' href='#'>Cluster</a></li>
          <li><a data-filter='.stat-host' href='#'>Host</a></li>
          <li><a data-filter='.stat-storage' href='#'>Storage</a></li>
          <li><a data-filter='.stat-vm' href='#'>VM</a></li>
        </ul>
      </div>
    </div>
  </div>
    <div class='row'>
      <div class='span12'>
        <div class='well'>
          <div id='statsgrid'>
            <div class='stat wide2'>
              <div class='widget widget-moreinfo'>
                <div class='title'>What Is This?</div>
                <h4 class='text'><?php echo $introductionLabel; ?></h4>
                <div class='more-info'></div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-vcenter stat-infra'>
              <div class='widget widget-infrastructure'>
                <div class='title'>vCenters</div>
                <div class='value'><?php echo $totalVCs; ?></div>
                <div class='more-info'>Total</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-cluster stat-infra'>
              <div class='widget widget-infrastructure'>
                <div class='title'>Clusters</div>
                <div class='value'><?php echo $totalClusters; ?></div>
                <div class='more-info'>Total</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-host stat-infra'>
              <div class='widget widget-infrastructure'>
                <div class='title'>Hosts</div>
                <div class='value'><?php echo $totalHosts; ?></div>
                <div class='more-info'>Total</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-vm stat-infra'>
              <div class='widget widget-infrastructure'>
                <div class='title'>VMs</div>
                <div class='value'><?php echo $totalVMs; ?></div>
                <div class='more-info'>Total</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-storage stat-infra'>
              <div class='widget widget-infrastructure'>
                <div class='title'>Datastores</div>
                <div class='value'><?php echo $totalDatastore; ?></div>
                <div class='more-info'>Total</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-host stat-vcenter'>
              <div class='widget widget-vcenter'>
                <div class='title'>Hosts</div>
                <div class='value'><?php echo $averageHostPervCenter; ?></div>
                <div class='more-info'>Average Per vCenter</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-cluster stat-vcenter'>
              <div class='widget widget-vcenter'>
                <div class='title'>Clusters</div>
                <div class='value'><?php echo $averageClusterPervCenter; ?></div>
                <div class='more-info'>Average Per vCenter</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-vm stat-vcenter'>
              <div class='widget widget-vcenter'>
                <div class='title'>VMs</div>
                <div class='value'><?php echo $averageVMPervCenter; ?></div>
                <div class='more-info'>Average Per vCenter</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-cluster stat-vm'>
              <div class='widget widget-cluster'>
                <div class='title'>VMs</div>
                <div class='value'><?php echo $averageVMPerCluster; ?></div>
                <div class='more-info'>Average Per Cluster</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide3 height2 stat-vm'>
              <div class='widget widget-vm'>
                <div class='title'>Top 10 Operating Systems</div>
                <table width='100%'>
<?php
  $topGuestOS = 1;
  
  foreach ($sortedTabGuestOS as $guestOS)
  {
    
    if ($guestOS["guestOS"] == 'Not Available') { continue; }
    echo '                  <tr>';
    echo '                <td class=\'tdlabel\'>' . $topGuestOS++ . '. '. $guestOS["guestOS"] . '</td>';
    echo '                <td class=\'tdvalue\'>' . $guestOS["total"] . '</td>';
    echo '              </tr>';
              
  } # END foreach ($sortedTabGuestOS as $guestOS)
  
?>
                </table>
                <div class='more-info2'>Percent of Total VMs</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-cluster stat-host'>
              <div class='widget widget-cluster'>
                <div class='title'>Hosts</div>
                <div class='value'><?php echo $averageHostPerCluster; ?></div>
                <div class='more-info'>Average Per Cluster</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-cluster stat-storage'>
              <div class='widget widget-cluster'>
                <div class='title'>Datastores</div>
                <div class='value'><?php echo $averageDatastorePerCluster; ?></div>
                <div class='more-info'>Average Per Cluster</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-host stat-vm'>
              <div class='widget widget-host'>
                <div class='title'>VMs</div>
                <div class='value'><?php echo $averageVMPerHost; ?></div>
                <div class='more-info'>Average Per Host</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-host'>
              <div class='widget widget-host'>
                <div class='title'>Memory</div>
                <div class='value'><?php echo $averageMemoryPerHost; ?></div>
                <div class='more-info'>Average Per Host</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-host'>
              <div class='widget widget-host'>
                <div class='title'>Sockets</div>
                <div class='value'><?php echo $averageCPUPerHost; ?></div>
                <div class='more-info'>Average Per Host</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-host'>
              <div class='widget widget-host'>
                <div class='title'>Host</div>
                <div class='value'><?php echo human_filesize($totalTPSSavings*1024,0); ?></div>
                <div class='more-info'>Total TPS Savings</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-host'>
              <div class='widget widget-host'>
                <div class='title'>Host</div>
                <div class='value'><?php echo $totalBandwidth; ?></div>
                <div class='more-info2'>Total Bandwidth</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide3 stat-host'>
              <div class='widget widget-host'>
                <div class='title'>Top 5 Host CPU Type</div>
                <div class='top5table'>
                  <table class='top5table' width='100%'>
<?php
  $topHostCPUType = 1;
  
  foreach ($sortedHostCPUType as $cpuType)
  {
    
    if ($cpuType["cputype"] == 'Not Available') { continue; }
    echo '                  <tr>';
    echo '                <td class=\'tdlabel\'>' . $topHostCPUType++ . '. '. $cpuType["cputype"] . '</td>';
    echo '                <td class=\'tdvalue\'>' . $cpuType["total"] . ' (' . floor(100 * $cpuType["total"] / $totalHosts) . '%)</td>';
    echo '              </tr>';
              
  } # END foreach ($sortedHostCPUType as $cpuType)
  
?>
                  </table>
                </div>
                <div class='more-info2'>Percent of Total Hosts</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-storage'>
              <div class='widget widget-storage'>
                <div class='title'>NFS</div>
                <div class='value'><?php echo $totalNFS; ?></div>
                <div class='more-info'>Total</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-storage'>
              <div class='widget widget-storage'>
                <div class='title'>VMFS</div>
                <div class='value'><?php echo $totalVMFS; ?></div>
                <div class='more-info'>Total</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide3 stat-host'>
              <div class='widget widget-host'>
                <div class='title'>Top 5 ESX Build</div>
                <div class='top5table'>
                  <table class='top5table' width='100%'>
<?php
  $topESXBuild = 1;
  
  foreach ($sortedESXBuild as $esxBuild)
  {
    
    if ($esxBuild["esxbuild"] == 'Not Available') { continue; }
    echo '                  <tr>';
    echo '                <td class=\'tdlabel\'>' . $topESXBuild++ . '. '. $esxBuild["esxbuild"] . '</td>';
    echo '                <td class=\'tdvalue\'>' . $esxBuild["total"] . ' (' . floor(100 * $esxBuild["total"] / $totalHosts) . '%)</td>';
    echo '              </tr>';
              
  } # END foreach ($sortedESXBuild as $esxBuild)
  
?>
                  </table>
                </div>
                <div class='more-info2'>Percent of Total Hosts</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide3 stat-host'>
              <div class='widget widget-host'>
                <div class='title'>Top 5 Host Models</div>
                <div class='top5table'>
                  <table class='top5table' width='100%'>
<?php
  $topHostModel = 1;
  
  foreach ($sortedHostModel as $hostModel)
  {
    
    if ($hostModel["model"] == 'Not Available') { continue; }
    echo '                  <tr>';
    echo '                <td class=\'tdlabel\'>' . $topHostModel++ . '. '. $hostModel["model"] . '</td>';
    echo '                <td class=\'tdvalue\'>' . $hostModel["total"] . ' (' . floor(100 * $hostModel["total"] / $totalHosts) . '%)</td>';
    echo '              </tr>';
              
  } # END foreach ($sortedHostModel as $hostModel)
  
?>
                  </table>
                </div>
                <div class='more-info2'>Percent of Total Hosts</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-storage'>
              <div class='widget widget-storage'>
                <div class='title'>Datastores</div>
                <div class='value'><?php echo human_filesize($averageDatastoreSize, 0); ?></div>
                <div class='more-info'>Average Size</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-storage'>
              <div class='widget widget-storage'>
                <div class='title'>Datastore</div>
                <div class='value'><?php echo human_filesize($totalDatastoreSize, 0); ?></div>
                <div class='more-info'>Total Storage Size</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-storage stat-vm'>
              <div class='widget widget-vm'>
                <div class='title'>VMDK</div>
                <div class='value'><?php echo $averageVMDKUncommitedSize; ?> GB</div>
                <div class='more-info'>Average Uncommited Size</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-storage stat-vm'>
              <div class='widget widget-vm'>
                <div class='title'>VMDK</div>
                <div class='value'><?php echo $averageVMDKCommitedSize; ?> GB</div>
                <div class='more-info'>Average Commited Size</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-storage stat-vm'>
              <div class='widget widget-vm'>
                <div class='title'>VMDK</div>
                <div class='value'><?php echo $averageVMDKProvisionedSize; ?> GB</div>
                <div class='more-info'>Average Provisioned Size</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-host'>
              <div class='widget widget-host'>
                <div class='title'>Host</div>
                <div class='value'><?php echo human_filesize($totalHostsCPUMhz * 1024 * 1024, 0, "Hz"); ?></div>
                <div class='more-info'>Total CPU</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-host'>
              <div class='widget widget-host'>
                <div class='title'>Host</div>
                <div class='value'><?php echo human_filesize($totalHostsMemory,0); ?></div>
                <div class='more-info'>Total Memory</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-vm'>
              <div class='widget widget-vm'>
                <div class='title'>VM</div>
                <div class='value'><?php echo $averageVMMemory; ?></div>
                <div class='more-info'>Average VM Memory</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-vm'>
              <div class='widget widget-vm'>
                <div class='title'>VM</div>
                <div class='value'><?php echo $averageVMCPU; ?></div>
                <div class='more-info'>Average VM CPU</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-cluster'>
              <div class='widget widget-cluster'>
                <div class='title'>Cluster</div>
                <div class='value'><?php echo floor($totalvMotion / 1000); ?>K</div>
                <div class='more-info'>Total vMotion</div>
                <div class='updated-at'></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php require("footer.php"); ?>
