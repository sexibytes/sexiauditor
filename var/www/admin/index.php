<?php require("session.php"); ?>
<?php
$title = "Platform stats";
$additionalStylesheet = array('css/vopendata.css');
$additionalScript = array(  'js/vopendata.js',
                            'js/isotope.min.js');
require("header.php");
require("helper.php");

// $xmlPath = "/opt/vcron/data/";
// if (!file_exists($xmlPath)) :
//     echo '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> Folder ' . $xmlPath . ' don\'t exist, aborting... </div>';
// else:

require("dbconnection.php");
# get vcenters info
$totalVCs = $db->getValue("vcenters", "COUNT(vcenters.id)");
# get clusters info
$db->join("vcenters", "clusters.vcenter = vcenters.id", "INNER");
$db->where('clusters.active', 1);
$totalClusters = $db->getValue("clusters", "COUNT(clusters.id)");
# get hosts info
$db->join("clusters", "hosts.cluster = clusters.id", "INNER");
$db->join("vcenters", "clusters.vcenter = vcenters.id", "INNER");
$db->where('hosts.active', 1);
$totalHosts = $db->getValue("hosts", "COUNT(hosts.id)");
# get vms info
$db->join("hosts", "vms.host = hosts.id", "INNER");
$db->join("clusters", "hosts.cluster = clusters.id", "INNER");
$db->join("vcenters", "hosts.vcenter = vcenters.id", "INNER");
$db->where('vms.active', 1);
$totalVMs = $db->getValue("vms", "COUNT(vms.id)");

if ($totalVCs == 0 || $totalClusters == 0 || $totalHosts == 0 || $totalVMs == 0) {
// if (!file_exists("$xmlPath/latest/vms-global.xml") or !file_exists("$xmlPath/latest/hosts-global.xml") or !file_exists("$xmlPath/latest/clusters-global.xml") or !file_exists("$xmlPath/latest/datastores-global.xml")) {
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
  $sortedTabGuestOS = array(  "Microsoft Windows Server 2008 R2 (64-bit)" => "20.9%",
                              "Microsoft Windows Server 2003 Standard (64-bit)" => "12.4%",
                              "Red Hat Enterprise Linux 5 (64-bit)" => "8.6%",
                              "Microsoft Windows Server 2003 Standard (32-bit)" => "6.7%",
                              "Microsoft Windows XP Professional (32-bit)" => "6.2%",
                              "Ubuntu Linux (64-bit)" => "5.8%",
                              "Microsoft Windows 7 (64-bit)" => "5.1%",
                              "Red Hat Enterprise Linux 6 (64-bit)" => "3.9%",
                              "Microsoft Windows Server 2008 (64-bit)" => "3.4%",
                              "Microsoft Windows 7 (32-bit)" => "2.5%" );
  $sortedHostModel = array(  "HP" => "48.5%",
                              "Dell" => "16.8%",
                              "Dell Inc." => "15.8%",
                              "IBM" => "11.1%",
                              "Cisco Systems Inc" => "5.6%" );
  $sortedHostCPUType = array( "Intel Xeon E5-2970" => "48.5%",
                              "Cyrix" => "16.8%",
                              "AMD G2" => "15.8%",
                              "Pentium M" => "11.1%",
                              "A9X" => "5.6%" );
  $sortedESXBuild = array(    "ESX 5.5 build 23123" => "48.5%",
                              "sdfsdfs" => "16.8%",
                              "sdfsdfsdf" => "15.8%",
                              "sdfsdfsdfaa" => "11.1%",
                              "eqdnspdfn" => "5.6%" );
  $averageHostPervCenter = 37;
  $averageClusterPervCenter = 4;
  $averageHostPerCluster = "5.4";
  $averageDatastorePerCluster = "20.5";
  $averageMemoryPerHost = "118.91 GB";
  $averageCPUPerHost = "2.1";
  $averageDatastoreSize = 0;
  $averageVMMemory = 0;
  $averageVMCPU = 0;
} else {
  $introductionLabel = 'This is a selection of statistics from your platform, based on the <a href="http://www.vopendata.org">vOpenData project</a>. These will be updated every time scheduler is running !';
  // $xmlVMFile = "$xmlPath/latest/vms-global.xml";
  // $xmlHostFile = "$xmlPath/latest/hosts-global.xml";
  // $xmlClusterFile = "$xmlPath/latest/clusters-global.xml";
  // $xmlDatastoreFile = "$xmlPath/latest/datastores-global.xml";
  // $xmlVM = simplexml_load_file($xmlVMFile);
  // $xmlHost = simplexml_load_file($xmlHostFile);
  // $xmlVM2 = new DOMDocument;
  // $xmlVM2->load($xmlVMFile);
  // $xpathVM = new DOMXPath($xmlVM2);
  // $xmlHost2 = new DOMDocument;
  // $xmlHost2->load($xmlHostFile);
  // $xpathHost = new DOMXPath($xmlHost2);
  // $xmlCluster = new DOMDocument;
  // $xmlCluster->load($xmlClusterFile);
  // $xpathCluster = new DOMXPath($xmlCluster);
  // $xmlDatastore = simplexml_load_file($xmlDatastoreFile);
  // $xmlDatastoreDOM = new DOMDocument;
  // $xmlDatastoreDOM->load($xmlDatastoreFile);
  // $xpathDatastore = new DOMXPath($xmlDatastoreDOM);
  $db->where('vms.active', 1);
  $totalCommited = $db->getValue("vms", "SUM(vms.commited)");
  // $totalCommited = (int) $xpathVM->evaluate('sum(/vms/vm/commited)');
  $db->where('vms.active', 1);
  $totalUncommited = $db->getValue("vms", "SUM(vms.uncommited)");
  // $totalUncommited = (int) $xpathVM->evaluate('sum(/vms/vm/uncommited)');
  $db->where('vms.active', 1);
  $totalProvisioned = $db->getValue("vms", "SUM(vms.provisionned)");
  // $totalProvisioned = (int) $xpathVM->evaluate('sum(/vms/vm/provisionned)');
  // $totalVMs = $xmlVM->count();
  // $totalVCs = count(array_diff(array_count_values(array_map("strval", $xmlVM->xpath("/vms/vm/vcenter"))), array("1")));
  // $totalClusters = count(array_diff(array_count_values(array_map("strval", $xmlVM->xpath("/vms/vm/cluster"))), array("1")));
  // $totalHosts = count($xmlHost);
  $db->where('vms.active', 1);
  $totalVMCPU = $db->getValue("vms", "SUM(vms.numcpu)");
  // $totalVMCPU = (int) $xpathVM->evaluate('sum(/vms/vm/numcpu)');
  $db->where('vms.active', 1);
  $totalVMMemory = $db->getValue("vms", "SUM(vms.memory)");
  // $totalVMMemory = (int) $xpathVM->evaluate('sum(/vms/vm/memory)');
  $db->where('datastores.active', 1);
  $db->where('datastores.shared', 1);
  $totalVMFS = $db->getValue("datastores", "COUNT(datastores.id)");
  // $totalVMFS = (int) $xpathDatastore->evaluate('count(/datastores/datastore/type[text()=\'VMFS\']/../shared[text()=\'true\'])');
  $db->where('datastores.active', 1);
  $db->where('datastores.type', 'NFS');
  $totalNFS = $db->getValue("datastores", "COUNT(datastores.id)");
  // $totalNFS = (int) $xpathDatastore->evaluate('count(/datastores/datastore/type[text()=\'NFS\'])');
  $totalDatastore = $totalNFS + $totalVMFS;
  $db->where('hosts.active', 1);
  $totalHostsCPU = $db->getValue("hosts", "SUM(hosts.numcpu)");
  // $totalHostsCPU = (int) $xpathHost->evaluate('sum(/hosts/host/numcpu)');
  $db->where('hosts.active', 1);
  $totalHostsCPUMhz = $db->getValue("hosts", "SUM(hosts.cpumhz)");
  // $totalHostsCPUMhz = (int) $xpathHost->evaluate('sum(/hosts/host/cpumhz)');
  $db->where('hosts.active', 1);
  $totalHostsMemory = $db->getValue("hosts", "SUM(hosts.memory)");
  // $totalHostsMemory = (int) $xpathHost->evaluate('sum(/hosts/host/memory)');
  $db->where('datastores.active', 1);
  $totalDatastoreSize = $db->getValue("datastores", "SUM(datastores.size)");
  // $totalDatastoreSize = (int) $xpathDatastore->evaluate('sum(/datastores/datastore/size)');
  $db->where('clusters.active', 1);
  $totalvMotion = $db->getValue("clusters", "SUM(clusters.vmotion)");
  // $totalvMotion = (int) $xpathCluster->evaluate('sum(/clusters/cluster/vmotion)');
  $totalBandwidth = 0;
  $totalTPSSavings = 0;
  $averageVMPervCenter = round($totalVMs / $totalVCs);
  $averageVMPerCluster = round($totalVMs / $totalClusters);
  $averageVMPerHost = round($totalVMs / $totalHosts);
  $averageVMDKCommitedSize = round($totalCommited / $totalVMs, 2);
  $averageVMDKProvisionedSize = round($totalProvisioned / $totalVMs, 2);
  $averageVMDKUncommitedSize = round($totalUncommited / $totalVMs, 2);
  $db->where('active', 1);
  $db->groupBy("guestOS");
  $db->orderBy("total","desc");
  $sortedTabGuestOS = $db->get("vms", 11, "guestOS, COUNT(*) as total");
  // $sortedTabGuestOS = array_diff(array_count_values(array_map("strval", $xmlVM->xpath("/vms/vm/guestOS"))), array("1"));
  // arsort($sortedTabGuestOS);
  $db->where('hosts.active', 1);
  $db->groupBy("hosts.model");
  $db->orderBy("total","desc");
  $sortedHostModel = $db->get("hosts", 5, "hosts.model, COUNT(*) as total");
  // $sortedHostModel = array_diff(array_count_values(array_map("strval", $xmlHost->xpath("/hosts/host/model"))), array("1"));
  // arsort($sortedHostModel);
  $db->where('hosts.active', 1);
  $db->groupBy("hosts.cputype");
  $db->orderBy("total","desc");
  $sortedHostCPUType = $db->get("hosts", 5, "hosts.cputype, COUNT(*) as total");
  // $sortedHostCPUType = array_diff(array_count_values(array_map("strval", $xmlHost->xpath("/hosts/host/cputype"))), array("1"));
  // arsort($sortedHostCPUType);
  $db->where('hosts.active', 1);
  $db->groupBy("hosts.esxbuild");
  $db->orderBy("total","desc");
  $sortedESXBuild = $db->get("hosts", 5, "hosts.esxbuild, COUNT(*) as total");
  // $sortedESXBuild = array_diff(array_count_values(array_map("strval", $xmlHost->xpath("/hosts/host/esxbuild"))), array("1"));
  // arsort($sortedESXBuild);
  $averageHostPervCenter = round($totalHosts / $totalVCs, 2);
  $averageClusterPervCenter = round($totalClusters / $totalVCs, 2);
  $averageHostPerCluster = round($totalHosts / $totalClusters,2);
  $averageDatastorePerCluster = round($totalDatastore / $totalClusters,2);
  $averageMemoryPerHost = human_filesize($totalHostsMemory / $totalHosts,2);
  $averageCPUPerHost = round($totalHostsCPU / $totalHosts,2);
  $averageDatastoreSize = round($totalDatastoreSize / $totalDatastore,2);
  $averageVMMemory = human_filesize(1024 * 1024 * $totalVMMemory / $totalVMs,2);
  $averageVMCPU = round($totalVMCPU / $totalVMs,2);
}
?>

  <div class='container'>
  <div class='navbar navbar-static-top' style="z-index: 0;">
    <div class='navbar-inner'>
      <div class=''>
        <ul class='nav navbar-top-links navbar-left' id='filters'>
          <li><i class="glyphicon glyphicon-th"></i> <b>Please select your view scope:</b></li>
          <li><a data-filter='*' href='#'>All</a></li>
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
            <div class='stat stat-vcenter'>
              <div class='widget widget-vcenter'>
                <div class='title'>vCenters</div>
                <div class='value'><?php echo $totalVCs; ?></div>
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
            <div class='stat stat-cluster'>
              <div class='widget widget-cluster'>
                <div class='title'>Clusters</div>
                <div class='value'><?php echo $totalClusters; ?></div>
                <div class='more-info'>Total</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide3 height2 stat-vm'>
              <div class='widget widget-vm'>
                <div class='title'>Top 10 Operating Systems</div>
                <table width='100%'>
<?php
  $topGuestOS = 1;
  foreach ($sortedTabGuestOS as $guestOS) {
    if ($guestOS["guestOS"] == 'Not Available') { continue; }
    echo '                  <tr>
                <td class=\'tdlabel\'>' . $topGuestOS++ . '. '. $guestOS["guestOS"] . '</td>
                <td class=\'tdvalue\'>' . $guestOS["total"] . '</td>
              </tr>';
  }
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
            <div class='stat stat-cluster stat-vm'>
              <div class='widget widget-cluster'>
                <div class='title'>VMs</div>
                <div class='value'><?php echo $averageVMPerCluster; ?></div>
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
            <div class='stat wide2 stat-host'>
              <div class='widget widget-host'>
                <div class='title'>Hosts</div>
                <div class='value'><?php echo $totalHosts; ?></div>
                <div class='more-info2'>Total</div>
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
            <div class='stat wide3 stat-host'>
              <div class='widget widget-host'>
                <div class='title'>Top 5 Host CPU Type</div>
                <div class='top5table'>
                  <table class='top5table' width='100%'>
<?php
  $topHostCPUType = 1;
  foreach ($sortedHostCPUType as $cpuType) {
    if ($cpuType["cputype"] == 'Not Available') { continue; }
    echo '                  <tr>
                <td class=\'tdlabel\'>' . $topHostCPUType++ . '. '. $cpuType["cputype"] . '</td>
                <td class=\'tdvalue\'>' . $cpuType["total"] . ' (' . floor(100 * $cpuType["total"] / $totalHosts) . '%)</td>
              </tr>';
  }
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
  foreach ($sortedESXBuild as $esxBuild) {
    if ($esxBuild["esxbuild"] == 'Not Available') { continue; }
    echo '                  <tr>
                <td class=\'tdlabel\'>' . $topESXBuild++ . '. '. $esxBuild["esxbuild"] . '</td>
                <td class=\'tdvalue\'>' . $esxBuild["total"] . ' (' . floor(100 * $esxBuild["total"] / $totalHosts) . '%)</td>
              </tr>';
  }
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
  foreach ($sortedHostModel as $hostModel) {
    if ($hostModel["model"] == 'Not Available') { continue; }
    echo '                  <tr>
                <td class=\'tdlabel\'>' . $topHostModel++ . '. '. $hostModel["model"] . '</td>
                <td class=\'tdvalue\'>' . $hostModel["total"] . ' (' . floor(100 * $hostModel["total"] / $totalHosts) . '%)</td>
              </tr>';
  }
?>
                  </table>
                </div>
                <div class='more-info2'>Percent of Total Hosts</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-vm'>
              <div class='widget widget-vm'>
                <div class='title'>VMs</div>
                <div class='value'><?php echo $totalVMs; ?></div>
                <div class='more-info2'>Total</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-storage'>
              <div class='widget widget-datastore'>
                <div class='title'>Datastores</div>
                <div class='value'><?php echo human_filesize($averageDatastoreSize, 2); ?></div>
                <div class='more-info2'>Average Size</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-storage'>
              <div class='widget widget-datastore'>
                <div class='title'>Datastores</div>
                <div class='value'><?php echo $totalDatastore; ?></div>
                <div class='more-info2'>Total</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-storage'>
              <div class='widget widget-datastore'>
                <div class='title'>Datastore</div>
                <div class='value'><?php echo human_filesize($totalDatastoreSize, 2); ?></div>
                <div class='more-info'>Total Storage Size</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-storage stat-vm'>
              <div class='widget widget-vmdk'>
                <div class='title'>VMDK</div>
                <div class='value'><?php echo $averageVMDKUncommitedSize; ?> GB</div>
                <div class='more-info2'>Average Uncommited Size</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-storage stat-vm'>
              <div class='widget widget-vmdk'>
                <div class='title'>VMDK</div>
                <div class='value'><?php echo $averageVMDKCommitedSize; ?> GB</div>
                <div class='more-info2'>Average Commited Size</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-storage stat-vm'>
              <div class='widget widget-vmdk'>
                <div class='title'>VMDK</div>
                <div class='value'><?php echo $averageVMDKProvisionedSize; ?> GB</div>
                <div class='more-info2'>Average Provisioned Size</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-host'>
              <div class='widget widget-host'>
                <div class='title'>Host</div>
                <div class='value'><?php echo human_filesize($totalHostsCPUMhz * 1024 * 1024, 2, "Hz"); ?></div>
                <div class='more-info'>Total CPU</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-host'>
              <div class='widget widget-host'>
                <div class='title'>Host</div>
                <div class='value'><?php echo human_filesize($totalHostsMemory,2); ?></div>
                <div class='more-info'>Total Memory</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-vm'>
              <div class='widget widget-vm'>
                <div class='title'>VM</div>
                <div class='value'><?php echo $averageVMMemory; ?></div>
                <div class='more-info2'>Average VM Memory</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-host'>
              <div class='widget widget-host'>
                <div class='title'>Host</div>
                <div class='value'><?php echo $totalTPSSavings; ?></div>
                <div class='more-info2'>Total TPS Savings</div>
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
            <div class='stat stat-cluster'>
              <div class='widget widget-cluster'>
                <div class='title'>Cluster</div>
                <div class='value'><?php echo floor($totalvMotion / 1000); ?>K</div>
                <div class='more-info2'>Total vMotion</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-vm'>
              <div class='widget widget-vm'>
                <div class='title'>VM</div>
                <div class='value'><?php echo $averageVMCPU; ?></div>
                <div class='more-info2'>Average VM CPU</div>
                <div class='updated-at'></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php //endif; ?>
<?php require("footer.php"); ?>
