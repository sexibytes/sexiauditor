<?php 
session_name('Private');
if (session_status() != PHP_SESSION_ACTIVE ) { session_start(); }
#$private_id = session_id();
#if (!isset($_SESSION['viewState'])) { $_SESSION['viewState'] = 'user'; }
#$validViewState = array('user', 'admin');
# define default view if not or wrong specified
#if ((!isset($_GET['viewState']) || !in_array($_GET['viewState'], $validViewState)) && isset($isAdminPage) && !$isAdminPage) { $_GET['viewState'] = 'user'; }
#session_write_close();

#session_start();
$title = "SexiGraf summary";
require("header.php");
require("helper.php");

$xmlPath = "/opt/vcron/data/";
if (!file_exists($xmlPath)) { 
    exit("[ERROR] Folder $xmlPath don't exist, aborting...");
}

$xmlSubDirectories = scandir($xmlPath, SCANDIR_SORT_DESCENDING);
if (count($xmlSubDirectories) < 3) {
##########################################
# vOpenData default sample               #
# It's used only at the beginning before #
# any infrastructure have been added     #
##########################################
    $totalVMs = "168K";
    $totalvCenter = 395; 
    $totalCluster = "1.6K";
    $totalHost = "13.9K";
    $totalLUN = "34.4K";
    $totalNFS = 0;
    $totalDatastore = "31.4K";
    $averageVMPervCenter = 426;
    $totalHostCPU = 0;
    $totalHostMemory = 0;
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
    $averageVMDKPervCenter = 684;
    $averageLUNPervCenter = 91;
    $averageHostPerCluster = "5.4";
    $averageDatastorePerCluster = "20.5";
    $averageDatastorePerHost = 16;
    $averageMemoryPerHost = "118.91 GB";
    $averageCPUPerHost = "2.1";
    $averageDatastoreSize = 0;
    $averageVMMemory = 0;
    $averageVMCPU = 0;
} else {
    $scannedDirectories = array_values(array_diff(scandir($xmlPath, SCANDIR_SORT_DESCENDING), array('..', '.')))[0];
    $xmlVMFile = "$xmlPath$scannedDirectories/vms-global.xml";
    $xmlHostFile = "$xmlPath$scannedDirectories/hosts-global.xml";
    $xmlVM = simplexml_load_file($xmlVMFile);
    $xmlHost = simplexml_load_file($xmlHostFile);
    $xmlVM2 = new DOMDocument;
    $xmlVM2->load($xmlVMFile);
    $xpathVM = new DOMXPath($xmlVM2);
    $xmlHost2 = new DOMDocument;
    $xmlHost2->load($xmlHostFile);
    $xpathHost = new DOMXPath($xmlHost2);
    $totalCommited = (int) $xpathVM->evaluate('sum(/vms/vm/COMMITED)');
    $totalUncommited = (int) $xpathVM->evaluate('sum(/vms/vm/uncommited)');
    $totalProvisioned = (int) $xpathVM->evaluate('sum(/vms/vm/PROVISIONNED)');
    $totalVMs = $xmlVM->count();
    $totalvCenter = count(array_diff(array_count_values(array_map("strval", $xmlVM->xpath("/vms/vm/VCENTER"))), array("1")));
    $totalCluster = count(array_diff(array_count_values(array_map("strval", $xmlVM->xpath("/vms/vm/CLUSTER"))), array("1")));
    $totalHost = count($xmlHost);
    $totalVMCPU = (int) $xpathVM->evaluate('sum(/vms/vm/NUMCPU)');
    $totalVMMemory = (int) $xpathVM->evaluate('sum(/vms/vm/MEMORY)');
    $totalLUN = 0;
    $totalNFS = 0;
    $totalDatastore = 0;
    $totalHostCPU = (int) $xpathHost->evaluate('sum(/hosts/host/numcpu)');
    $totalHostCPUMhz = (int) $xpathHost->evaluate('sum(/hosts/host/cpumhz)');
    $totalHostMemory = (int) $xpathHost->evaluate('sum(/hosts/host/memory)');
    $totalDatastoreSize = 0;
    $totalvMotion = 0;
    $totalBandwidth = 0;
    $totalTPSSavings = 0;
    $averageVMPervCenter = round($totalVMs / $totalvCenter);
    $averageVMPerCluster = round($totalVMs / $totalCluster);
    $averageVMPerHost = round($totalVMs / $totalHost);
    $averageVMDKCommitedSize = round($totalCommited / $totalVMs, 2);
    $averageVMDKProvisionedSize = round($totalProvisioned / $totalVMs, 2);
    $averageVMDKUncommitedSize = round($totalUncommited / $totalVMs, 2);
    $sortedTabGuestOS = array_diff(array_count_values(array_map("strval", $xmlVM->xpath("/vms/vm/guestOS"))), array("1"));
    arsort($sortedTabGuestOS);
    $sortedHostModel = array_diff(array_count_values(array_map("strval", $xmlHost->xpath("/hosts/host/model"))), array("1"));
    arsort($sortedHostModel);
    $sortedHostCPUType = array_diff(array_count_values(array_map("strval", $xmlHost->xpath("/hosts/host/cputype"))), array("1"));
    arsort($sortedHostCPUType);
    $sortedESXBuild = array_diff(array_count_values(array_map("strval", $xmlHost->xpath("/hosts/host/esxbuild"))), array("1"));
    arsort($sortedESXBuild);
    $averageHostPervCenter = round($totalHost / $totalvCenter, 2);
    $averageClusterPervCenter = round($totalCluster / $totalvCenter, 2);
    $averageVMDKPervCenter = 0;
    $averageLUNPervCenter = 0;
    $averageHostPerCluster = round($totalHost / $totalCluster,2);
    $averageDatastorePerCluster = 0;
    $averageDatastorePerHost = 0;
    $averageMemoryPerHost = human_filesize($totalHostMemory / $totalHost,2);
    $averageCPUPerHost = round($totalHostCPU / $totalHost,2);
    $averageDatastoreSize = 0;
    $averageVMMemory = human_filesize(1024 * 1024 * $totalVMMemory / $totalVMs,2);
    $averageVMCPU = round($totalVMCPU / $totalVMs,2);
}
?>
  <link href='css/application.css' rel='stylesheet' />

  <div class='container'>
  <div class='navbar navbar-static-top'>
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
          <li><a data-filter='.stat-vmdk' href='#'>VMDK</a></li>
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
                <h4 class='text'>This is a selection of statistics from the <a href="http://www.vopendata.org">vOpenData project</a>. Want to see you infrastructure metrics instead? Add your infrastructure in the Admin View > Credential Store!</h4>
                <div class='more-info'></div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-vcenter'>
              <div class='widget widget-vcenter'>
                <div class='title'>vCenters</div>
                <div class='value'><?php echo $totalvCenter; ?></div>
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
            <div class='stat stat-vmdk stat-vcenter'>
              <div class='widget widget-vcenter'>
                <div class='title'>VMDKs</div>
                <div class='value'><?php echo $averageVMDKPervCenter; ?></div>
                <div class='more-info'>Average Per vCenter</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-storage stat-vcenter'>
              <div class='widget widget-vcenter'>
                <div class='title'>LUNs</div>
                <div class='value'><?php echo $averageLUNPervCenter; ?></div>
                <div class='more-info'>Average Per vCenter</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-cluster'>
              <div class='widget widget-cluster'>
                <div class='title'>Clusters</div>
                <div class='value'><?php echo $totalCluster; ?></div>
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
    foreach ($sortedTabGuestOS as  $key => $value) {
        if ($key == 'Not Available') { continue; }
        echo '                  <tr>
                    <td class=\'tdlabel\'>' . $topGuestOS++ . '. '. $key . '</td>
                    <td class=\'tdvalue\'>' . $value . '</td>
                  </tr>';
        if ($topGuestOS >= 11) { break; }
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
                <div class='value'><?php echo $totalHost; ?></div>
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
            <div class='stat stat-host stat-storage'>
              <div class='widget widget-host'>
                <div class='title'>Datastores</div>
                <div class='value'><?php echo $averageDatastorePerHost; ?></div>
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
    foreach ($sortedHostCPUType as  $key => $value) {
        if ($key == 'Not Available') { continue; }
        echo '                  <tr>
                    <td class=\'tdlabel\'>' . $topHostCPUType++ . '. '. $key . '</td>
                    <td class=\'tdvalue\'>' . $value . ' (' . floor(100 * $value / $totalHost) . '%)</td>
                  </tr>';
        if ($topHostCPUType >= 6) { break; }
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
                <div class='title'>LUNs</div>
                <div class='value'><?php echo $totalLUN; ?></div>
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
    foreach ($sortedESXBuild as  $key => $value) {
        if ($key == 'Not Available') { continue; }
        echo '                  <tr>
                    <td class=\'tdlabel\'>' . $topESXBuild++ . '. '. $key . '</td>
                    <td class=\'tdvalue\'>' . $value . ' (' . floor(100 * $value / $totalHost) . '%)</td>
                  </tr>';
        if ($topESXBuild >= 6) { break; }
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
    foreach ($sortedHostModel as  $key => $value) {
        if ($key == 'Not Available') { continue; }
        echo '                  <tr>
                    <td class=\'tdlabel\'>' . $topHostModel++ . '. '. $key . '</td>
                    <td class=\'tdvalue\'>' . $value . ' (' . floor(100 * $value / $totalHost) . '%)</td>
                  </tr>';
        if ($topHostModel >= 6) { break; }
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
            <div class='stat stat-storage'>
              <div class='widget widget-datastore'>
                <div class='title'>Datastores</div>
                <div class='value'><?php echo $averageDatastoreSize; ?></div>
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
            <div class='stat wide2 stat-vmdk'>
              <div class='widget widget-vmdk'>
                <div class='title'>VMDK</div>
                <div class='value'><?php echo $averageVMDKUncommitedSize; ?> GB</div>
                <div class='more-info2'>Average Uncommited Size</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-vmdk'>
              <div class='widget widget-vmdk'>
                <div class='title'>VMDK</div>
                <div class='value'><?php echo $averageVMDKCommitedSize; ?> GB</div>
                <div class='more-info2'>Average Commited Size</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-vmdk'>
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
                <div class='value'><?php echo human_filesize($totalHostCPUMhz * 1024 * 1024, 2, "Hz"); ?></div>
                <div class='more-info'>Total CPU</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-host'>
              <div class='widget widget-host'>
                <div class='title'>Host</div>
                <div class='value'><?php echo human_filesize($totalHostMemory,2); ?></div>
                <div class='more-info'>Total Memory</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-datastore'>
              <div class='widget widget-datastore'>
                <div class='title'>Datastore</div>
                <div class='value'><?php echo $totalDatastoreSize; ?></div>
                <div class='more-info'>Total Storage Size</div>
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
                <div class='value'><?php echo $totalvMotion; ?></div>
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

<script src='http://dash.vopendata.org/js/application.js'></script>
<script src='http://dash.vopendata.org/js/isotope.min.js'></script>

<?php require("footer.php"); ?>
