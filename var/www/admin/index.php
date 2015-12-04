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
?>
<link href='css/application.css' rel='stylesheet' />

  <div class='container'>
  <div class='navbar navbar-static-top'>
    <div class='navbar-inner'>
      <div class=''>
        <ul class='nav navbar-top-links navbar-left' id='filters'>
          <li><i class="glyphicon glyphicon-th"></i> <b>Please select your view scope:</b></li>
          <li>
            <a data-filter='*' href='#'>All</a>
          </li>
          <li>
            <a data-filter='.stat-infrastructure' href='#'>Infrastructure</a>
          </li>
          <li>
            <a data-filter='.stat-vcenter' href='#'>vCenter</a>
          </li>
          <li>
            <a data-filter='.stat-cluster' href='#'>Cluster</a>
          </li>
          <li>
            <a data-filter='.stat-host' href='#'>Host</a>
          </li>
          <li>
            <a data-filter='.stat-lun' href='#'>LUN</a>
          </li>
          <li>
            <a data-filter='.stat-datastore' href='#'>Datastore</a>
          </li>
          <li>
            <a data-filter='.stat-vm' href='#'>VM</a>
          </li>
          <li>
            <a data-filter='.stat-vmdk' href='#'>VMDK</a>
          </li>
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
            <div class='stat wide2 stat-infrastructure'>
              <div class='widget widget-infrastructure'>
                <div class='title'>Infrastructures</div>
                <div class='value'>340</div>
                <div class='more-info2'>Total</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-infrastructure stat-cluster'>
              <div class='widget widget-infrastructure'>
                <div class='title'>Clusters</div>
                <div class='value'>4</div>
                <div class='more-info'>Average Per Infrastructure</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-infrastructure stat-datastore'>
              <div class='widget widget-infrastructure'>
                <div class='title'>Datastores</div>
                <div class='value'>92</div>
                <div class='more-info'>Average Per Infrastructure</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-vcenter'>
              <div class='widget widget-vcenter'>
                <div class='title'>vCenters</div>
                <div class='value'>395</div>
                <div class='more-info'>Total</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-host stat-vcenter'>
              <div class='widget widget-vcenter'>
                <div class='title'>Hosts</div>
                <div class='value'>37</div>
                <div class='more-info'>Average Per vCenter</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 height2 stat-infrastructure'>
              <div class='widget widget-infrastructure'>
                <div class='title'>Top 10 Countries</div>
                <table width='100%'>
                  <tr>
                    <td class='tdlabel'>1. United States</td>
                    <td class='tdvalue'>32.0%</td>
                  </tr>
                  <tr>
                    <td class='tdlabel'>2. United Kingdom</td>
                    <td class='tdvalue'>10.7%</td>
                  </tr>
                  <tr>
                    <td class='tdlabel'>3. France</td>
                    <td class='tdvalue'>10.4%</td>
                  </tr>
                  <tr>
                    <td class='tdlabel'>4. Germany</td>
                    <td class='tdvalue'>6.8%</td>
                  </tr>
                  <tr>
                    <td class='tdlabel'>5. Netherlands</td>
                    <td class='tdvalue'>6.5%</td>
                  </tr>
                  <tr>
                    <td class='tdlabel'>6. Belgium</td>
                    <td class='tdvalue'>5.9%</td>
                  </tr>
                  <tr>
                    <td class='tdlabel'>7. Switzerland</td>
                    <td class='tdvalue'>4.1%</td>
                  </tr>
                  <tr>
                    <td class='tdlabel'>8. Canada</td>
                    <td class='tdvalue'>2.7%</td>
                  </tr>
                  <tr>
                    <td class='tdlabel'>9. Sweden</td>
                    <td class='tdvalue'>2.4%</td>
                  </tr>
                  <tr>
                    <td class='tdlabel'>10. Denmark</td>
                    <td class='tdvalue'>2.1%</td>
                  </tr>
                </table>
                <div class='more-info2'>Percent Of Total Infrastructures</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-infrastructure stat-vmdk'>
              <div class='widget widget-infrastructure'>
                <div class='title'>VMDKs</div>
                <div class='value'>754</div>
                <div class='more-info'>Average Per Infrastructure</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-infrastructure stat-host'>
              <div class='widget widget-infrastructure'>
                <div class='title'>Hosts</div>
                <div class='value'>40</div>
                <div class='more-info'>Average Per Infrastructure</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-cluster stat-vcenter'>
              <div class='widget widget-vcenter'>
                <div class='title'>Clusters</div>
                <div class='value'>4</div>
                <div class='more-info'>Average Per vCenter</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-vm stat-vcenter'>
              <div class='widget widget-vcenter'>
                <div class='title'>VMs</div>
                <div class='value'>426</div>
                <div class='more-info'>Average Per vCenter</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-infrastructure'>
              <div class='widget widget-infrastructure'>
                <div class='title'>Infrastructure Types</div>
                <div class='top5table'>
                  <table class='top5table' width='100%'>
                    <tr>
                      <td class='tdlabel'>1. Server</td>
                      <td class='tdvalue'>54.1%</td>
                    </tr>
                    <tr>
                      <td class='tdlabel'>2. Lab</td>
                      <td class='tdvalue'>17.4%</td>
                    </tr>
                    <tr>
                      <td class='tdlabel'>3. Combination</td>
                      <td class='tdvalue'>12.4%</td>
                    </tr>
                    <tr>
                      <td class='tdlabel'>4. Cloud</td>
                      <td class='tdvalue'>8.5%</td>
                    </tr>
                    <tr>
                      <td class='tdlabel'>5. Desktop/VDI</td>
                      <td class='tdvalue'>7.6%</td>
                    </tr>
                  </table>
                </div>
                <div class='more-info2'>Percent Of Total Infrastructures</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-vmdk stat-vcenter'>
              <div class='widget widget-vcenter'>
                <div class='title'>VMDKs</div>
                <div class='value'>684</div>
                <div class='more-info'>Average Per vCenter</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-lun stat-vcenter'>
              <div class='widget widget-vcenter'>
                <div class='title'>LUNs</div>
                <div class='value'>91</div>
                <div class='more-info'>Average Per vCenter</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-cluster'>
              <div class='widget widget-cluster'>
                <div class='title'>Clusters</div>
                <div class='value'>1.6K</div>
                <div class='more-info'>Total</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-cluster stat-host'>
              <div class='widget widget-cluster'>
                <div class='title'>Hosts</div>
                <div class='value'>5.4</div>
                <div class='more-info'>Average Per Cluster</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-cluster stat-vm'>
              <div class='widget widget-cluster'>
                <div class='title'>VMs</div>
                <div class='value'>89.4</div>
                <div class='more-info'>Average Per Cluster</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-cluster stat-datastore'>
              <div class='widget widget-cluster'>
                <div class='title'>Datastores</div>
                <div class='value'>20.5</div>
                <div class='more-info'>Average Per Cluster</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-host'>
              <div class='widget widget-host'>
                <div class='title'>Hosts</div>
                <div class='value'>13.9K</div>
                <div class='more-info2'>Total</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-host stat-vm'>
              <div class='widget widget-host'>
                <div class='title'>VMs</div>
                <div class='value'>12.2</div>
                <div class='more-info'>Average Per Host</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-host stat-datastore'>
              <div class='widget widget-host'>
                <div class='title'>Datastores</div>
                <div class='value'>16.0</div>
                <div class='more-info'>Average Per Host</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-host'>
              <div class='widget widget-host'>
                <div class='title'>Memory</div>
                <div class='value'>118.91 GB</div>
                <div class='more-info'>Average Per Host</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide3 height2 stat-vm'>
              <div class='widget widget-vm'>
                <div class='title'>Top 10 Operating Systems</div>
                <table width='100%'>
                  <tr>
                    <td class='tdlabel'>1. Microsoft Windows Server 2008 R2 (64-bit)</td>
                    <td class='tdvalue'>20.9%</td>
                  </tr>
                  <tr>
                    <td class='tdlabel'>2. Microsoft Windows Server 2003 Standard (64-bit)</td>
                    <td class='tdvalue'>12.4%</td>
                  </tr>
                  <tr>
                    <td class='tdlabel'>3. Red Hat Enterprise Linux 5 (64-bit)</td>
                    <td class='tdvalue'>8.6%</td>
                  </tr>
                  <tr>
                    <td class='tdlabel'>4. Microsoft Windows Server 2003 Standard (32-bit)</td>
                    <td class='tdvalue'>6.7%</td>
                  </tr>
                  <tr>
                    <td class='tdlabel'>5. Microsoft Windows XP Professional (32-bit)</td>
                    <td class='tdvalue'>6.2%</td>
                  </tr>
                  <tr>
                    <td class='tdlabel'>6. Ubuntu Linux (64-bit)</td>
                    <td class='tdvalue'>5.8%</td>
                  </tr>
                  <tr>
                    <td class='tdlabel'>7. Microsoft Windows 7 (64-bit)</td>
                    <td class='tdvalue'>5.1%</td>
                  </tr>
                  <tr>
                    <td class='tdlabel'>8. Red Hat Enterprise Linux 6 (64-bit)</td>
                    <td class='tdvalue'>3.9%</td>
                  </tr>
                  <tr>
                    <td class='tdlabel'>9. Microsoft Windows Server 2008 (64-bit)</td>
                    <td class='tdvalue'>3.4%</td>
                  </tr>
                  <tr>
                    <td class='tdlabel'>10. Microsoft Windows 7 (32-bit)</td>
                    <td class='tdvalue'>2.5%</td>
                  </tr>
                </table>
                <div class='more-info2'>Percent of Total VMs</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-host'>
              <div class='widget widget-host'>
                <div class='title'>CPUs</div>
                <div class='value'>2.1</div>
                <div class='more-info'>Average Per Host</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-host'>
              <div class='widget widget-host'>
                <div class='title'>Top 5 Host Vendors</div>
                <div class='top5table'>
                  <table class='top5table' width='100%'>
                    <tr>
                      <td class='tdlabel'>1. HP</td>
                      <td class='tdvalue'>48.5%</td>
                    </tr>
                    <tr>
                      <td class='tdlabel'>2. Dell</td>
                      <td class='tdvalue'>16.8%</td>
                    </tr>
                    <tr>
                      <td class='tdlabel'>3. Dell Inc.</td>
                      <td class='tdvalue'>15.8%</td>
                    </tr>
                    <tr>
                      <td class='tdlabel'>4. IBM</td>
                      <td class='tdvalue'>11.1%</td>
                    </tr>
                    <tr>
                      <td class='tdlabel'>5. Cisco Systems Inc</td>
                      <td class='tdvalue'>5.6%</td>
                    </tr>
                  </table>
                </div>
                <div class='more-info2'>Percent of Total Hosts</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat stat-lun'>
              <div class='widget widget-lun'>
                <div class='title'>LUNs</div>
                <div class='value'>34.4K</div>
                <div class='more-info'>Total</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-lun'>
              <div class='widget widget-lun'>
                <div class='title'>Top 5 Storage Vendors</div>
                <div class='top5table'>
                  <table width='100%'>
                    <tr>
                      <td class='tdlabel'>1. HP</td>
                      <td class='tdvalue'>21.2%</td>
                    </tr>
                    <tr>
                      <td class='tdlabel'>2. DGC</td>
                      <td class='tdvalue'>14.6%</td>
                    </tr>
                    <tr>
                      <td class='tdlabel'>3. EMC</td>
                      <td class='tdvalue'>14.3%</td>
                    </tr>
                    <tr>
                      <td class='tdlabel'>4. IBM</td>
                      <td class='tdvalue'>9.3%</td>
                    </tr>
                    <tr>
                      <td class='tdlabel'>5. HITACHI</td>
                      <td class='tdvalue'>9.0%</td>
                    </tr>
                  </table>
                </div>
                <div class='more-info2'>Percent of Total LUNs</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-vm'>
              <div class='widget widget-vm'>
                <div class='title'>VMs</div>
                <div class='value'>168.5K</div>
                <div class='more-info2'>Total</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-datastore'>
              <div class='widget widget-datastore'>
                <div class='title'>Datastores</div>
                <div class='value'>31.4K</div>
                <div class='more-info2'>Total</div>
                <div class='updated-at'></div>
              </div>
            </div>
            <div class='stat wide2 stat-vmdk'>
              <div class='widget widget-vmdk'>
                <div class='title'>VMDK</div>
                <div class='value'>75.72 GB</div>
                <div class='more-info2'>Average Size</div>
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
