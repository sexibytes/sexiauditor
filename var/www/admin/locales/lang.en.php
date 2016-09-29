<?php
/*
------------------
Language: English
Credits: Frederic MARTIN and Raphael SCHITZ
------------------
*/

$lang = array();

# Login
$lang['LOGINSEXIAUDITOR'] = "Login to SexiAuditor";
$lang['USERNAME'] = "Username";
$lang['PASSWORD'] = "Password";
$lang['SIGNIN'] = "Sign in";
$lang['TIMEOUT'] = "Your session have timeout, please reconnect...";
$lang['UNKNOWNUSERNAME'] = "Unknown username";
$lang['BADPASSWORD'] = "Bad password for username";

# Header
$lang['WELCOME'] = "Welcome";
$lang['USERDASHBOARD'] = "User Dashboard";
$lang['VMINVENTORY'] = "VM Inventory";
$lang['HOSTINVENTORY'] = "Host Inventory";
$lang['CAPACITYPLANNING'] = "Capacity Planning";
$lang['VSANCHECKS'] = "VSAN Checks";
$lang['VCENTERCHECKS'] = "vCenter Checks";
$lang['CLUSTERCHECKS'] = "Cluster Checks";
$lang['HOSTCHECKS'] = "Host Checks";
$lang['DATASTORECHECKS'] = "Datastore Checks";
$lang['NETWORKCHECKS'] = "Network Checks";
$lang['VMCHECKS'] = "VM Checks";
$lang['ADMINDASHBOARD'] = "Admin Dashboard";
$lang['CREDENTIALSTORE'] = "Credential Store";
$lang['PACKAGEUPDATER'] = "Package Updater";
$lang['MODULESELECTOR'] = "Module Selector";
$lang['LOGVIEWER'] = "Log Viewer";
$lang['TIMETOBUILD'] = "Time To Build";
$lang['ESXBUNDLE'] = "ESX Bundle";
$lang['MODULESETTINGS'] = "Module Settings";
$lang['USERSMANAGEMENT'] = "Users Management";
$lang['IMPORTEXPORT'] = "Import/Export";
$lang['ONETIMEREPORT'] = "One Time Report";
$lang['PDFREPORTS'] = "PDF Reports";
$lang['SCHEDULERSTATUS'] = "Scheduler Status";
$lang['UPDATEPASSWORD'] = "Update Password";
$lang['ABOUTSEXIAUDITOR'] = "About SexiAuditor";
$lang['LOGOUT'] = "Logout";
$lang['GOBACK'] = "Go back";
$lang['CLOSE'] = "Close";

# Footer
$lang['ADMINRIGHTS'] = "Beware of these awesome admin rights, with power comes great responsibility-ish !!!";

# VSAN items
$lang['VSANHARDWARECOMPATIBILITY'] = array("title" => "Hardware Compatibility", "description" => 'This module will display hardware compatibility checks for VSAN. Further information can be found <a href=\"https://kb.vmware.com/kb/2114803\">here</a>');
$lang['VSANNETWORK'] = array("title" => "Network", "description" => 'This module will display network checks for VSAN. Further information can be found <a href=\"https://kb.vmware.com/kb/2114803\">here</a>');
$lang['VSANPHYSICALDISK'] = array("title" => "Physical disk", "description" => 'This module will display physical disk checks for VSAN. Further information can be found <a href=\"https://kb.vmware.com/kb/2114803\">here</a>');
$lang['VSANCLUSTER'] = array("title" => "Cluster", "description" => 'This module will display cluster checks for VSAN. Further information can be found <a href=\"https://kb.vmware.com/kb/2114803\">here</a>');

# vcenter items
$lang['VCSESSIONAGE'] = array("title" => "Session Age", "description" => 'The following displays vCenter sessions that exceed the maximum session age days).');
$lang['VCLICENCEREPORT'] = array("title" => "License Report", "description" => 'The following displays vCenter licenses.');
$lang['VCPERMISSIONREPORT'] = array("title" => "Permission Report", "description" => 'The following displays vCenter permissions.');
$lang['VCCERTIFICATESREPORT'] = array("title" => "Certificate Report", "description" => 'List of all certificates with their expiry date used in your vSphere platform.');

# cluster items
$lang['CLUSTERCONFIGURATIONISSUES'] = array('title' => 'Cluster with Configuration Issues','description' => 'The following clusters have HA configuration issues. This will impact your disaster recovery.');
$lang['CLUSTERHASTATUS'] = array('title' => 'Cluster Without HA','description' => 'The following cluster does not have HA enabled. You should check if that\'s expected as this is a must have feature!');
$lang['ALARMSCLUSTER'] = array('title' => 'Cluster Alarms','description' => 'This module will display triggered alarms on Cluster objects level (including VSAN ones) with status and time of creation.');
$lang['CLUSTERMEMBERSVERSION'] = array('title' => 'Hosts Build Number Mismatch','description' => 'Display ESX build number by cluster, in order to spot potential intracluster build mismatch.');
$lang['CLUSTERDATASTORECONSISTENCY'] = array('title' => 'Cluster With Datastore Count Mismatch','description' => 'The following cluster members does not have the same number of datastore.');
$lang['CLUSTERMEMBERSLUNPATHCOUNTMISMATCH'] = array('title' => 'Cluster With Members LUN Path Count Mismatch','description' => 'The following cluster members does not have the same number of LUN, please check for mapping or masking misconfiguration.');
$lang['CLUSTERTPSSAVINGS'] = array('title' => 'TPS Savings','description' => 'Display TPS savings on all clusters, can be null if disabled or with enforced salting');
$lang['CLUSTERADMISSIONCONTROL'] = array('title' => 'Admission Control Overtake','description' => 'These cluster have admission control constraint and should be overlook.');
$lang['CLUSTERCPURATIO'] = array('title' => 'Ratio Virtual/Physical CPU','description' => 'Display ratio of virtual CPU per physical CPU that goes over threshold of ' . $this->getConfig('thresholdCPURatio'));

# host items
$lang['HOSTLUNPATHDEAD'] = array('title' => 'Host LUN Path Dead', 'description' => 'Dead LUN Paths may cause issues with storage performance or be an indication of loss of redundancy.');
$lang['HOSTSSHSHELL'] = array('title' => 'Host SSH-Shell check', 'description' => 'The following displays host that not match the selected ssh/shell policy.');
$lang['HOSTNTPCHECK'] = array('title' => 'Host NTP Check', 'description' => 'The following hosts have mismatch NTP configuration.');
$lang['HOSTDNSCHECK'] = array('title' => 'Host DNS Check', 'description' => 'The following hosts have mismatch DNS configuration.');
$lang['HOSTSYSLOGCHECK'] = array('title' => 'Host Syslog Check', 'description' => 'The following hosts do not have the correct Syslog settings which may cause issues if ESXi hosts experience issues and logs need to be investigated.');
$lang['HOSTCONFIGURATIONISSUES'] = array('title' => 'Host configuration issues', 'description' => 'The following configuration issues have been registered against Hosts in vCenter.');
$lang['ALARMSHOST'] = array('title' => 'Host Alarms', 'description' => 'This module will display triggered alarms on Host objects level with status and time of creation.');
$lang['HOSTHARDWARESTATUS'] = array('title' => 'Host Hardware Status', 'description' => 'Details can be found in the Hardware Status tab.');
$lang['HOSTREBOOTREQUIRED'] = array('title' => 'Host Reboot required', 'description' => 'The following displays host that required reboot (after some configuration update for instance).');
$lang['HOSTFQDNHOSTNAMEMISMATCH'] = array('title' => 'Host FQDN and hostname mismatch', 'description' => 'The following displays host that have FQDN and hostname mismatch.');
$lang['HOSTMAINTENANCEMODE'] = array('title' => 'Host in maintenance mode', 'description' => 'The following displays host that are in maintenance mode.');
$lang['HOSTPOWERMANAGEMENTPOLICY'] = array('title' => 'Host PowerManagement Policy', 'description' => 'The following displays host that not match the selected power management policy.');
$lang['HOSTBUILDPIVOT'] = array('title' => 'Host ESXBuild Pivot Table', 'description' => 'Will display a list of all ESX build group by count.');

# datastore items
$lang['DATASTORESPACEREPORT'] = array('title' => 'Datastore Space report', 'description' => 'Datastores which run out of space will cause impact on the virtual machines held on these datastores.');
$lang['DATASTOREOVERALLOCATION'] = array('title' => 'Datastore Overallocation', 'description' => 'The following datastores may be overcommitted (allocation > ' . $this->getConfig('datastoreOverallocation') . '%), it is strongly suggested you check these.');
$lang['DATASTOREORPHANEDVMFILESREPORT'] = array('title' => 'Orphaned VM Files', 'description' => 'The following files does not belong to your vSphere platform anymore, you should check and delete them if necessary.');
$lang['DATASTORESIOCDISABLED'] = array('title' => 'Datastore with SIOC disabled', 'description' => 'Datastores with Storage I/O Control Disabled can impact the performance of your virtual machines.');
$lang['DATASTOREMAINTENANCEMODE'] = array('title' => 'Datastore in Maintenance Mode', 'description' => 'Datastore held in Maintenance mode will not be hosting any virtual machine, check the below Datastore are in an expected state.');
$lang['DATASTOREACCESSIBLE'] = array('title' => 'Datastore not Accessible', 'description' => 'The following datastores are not in "Accessible" state, which mean there is a connectivity issue and should be investiguated.');

# network items
$lang['NETWORKDVSPORTSFREE'] = array('title' => 'DVS ports free', 'description' => 'The following Distributed vSwitch Port Groups have less than ' . $this->getConfig('networkDVSVSSportsfree') . ' open port(s) left.');

#vm items
$lang['VMSNAPSHOTSAGE'] = array('title' => 'VM Snapshots Age', 'description' => 'This module will display snapshots that are older than ' . $this->getConfig('vmSnapshotAge') . ' day(s). Keeping snapshot can result in performance degradation under certain circumstances.');
$lang['VMPHANTOMSNAPSHOT'] = array('title' => 'VM phantom snapshot', 'description' => 'The following VM\s have Phantom Snapshots.');
$lang['VMCONSOLIDATIONNEEDED'] = array('title' => 'VM consolidation needed', 'description' => 'The following VMs have snapshots that failed to consolidate. See <a href=\'http://blogs.vmware.com/vsphere/2011/08/consolidate-snapshots.html\' target=\'_blank\'>this article</a> for more details.');
$lang['VMCPURAMHDDRESERVATION'] = array('title' => 'VM CPU-MEM reservation', 'description' => 'The following VMs have a CPU or Memory Reservation configured which may impact the performance of the VM.');
$lang['VMCPURAMHDDLIMITS'] = array('title' => 'VM CPU-MEM limit', 'description' => 'The following VMs have a CPU or memory limit configured which may impact the performance of the VM. Note: -1 indicates no limit.');
$lang['VMCPURAMHOTADD'] = array('title' => 'VM CPU-MEM hot-add', 'description' => 'The following lists all VMs and they Hot Add / Hot Plug feature configuration.');
$lang['VMTOOLSPIVOT'] = array('title' => 'VM vmtools pivot table', 'description' => 'xxx');
$lang['VMVHARDWAREPIVOT'] = array('title' => 'VM vHardware pivot table', 'description' => 'xxx');
$lang['VMBALLOONZIPSWAP'] = array('title' => 'Balloon-Swap-Compression on memory', 'description' => 'Ballooning and swapping may indicate a lack of memory or a limit on a VM, this may be an indication of not enough memory in a host or a limit held on a VM, <a href=\'http://www.virtualinsanity.com/index.php/2010/02/19/performance-troubleshooting-vmware-vsphere-memory/\' target=\'_blank\'>further information is available here</a>.');
$lang['VMMULTIWRITERMODE'] = array('title' => 'VM with vmdk in multiwriter mode', 'description' => 'The following VMs have multi-writer parameter. A problem will occur in case of svMotion without reconfiguration of the applications which are using these virtual disks and also change of the VM configuration concerned. More information <a href=\'http://kb.vmware.com/selfservice/microsites/search.do?language=en_US&cmd=displayKC&externalId=1034165\'>here</a>.');
$lang['VMNONPERSISTENTMODE'] = array('title' => 'VM with vmdk in Non persistent mode', 'description' => 'The following server VMs have disks in NonPersistent mode (excludes all desktop VMs). A problem will occur in case of svMotion without reconfiguration of these virtual disks.');
$lang['VMSCSIBUSSHARING'] = array('title' => 'VM with scsi bus sharing', 'description' => 'The following VMs have physical and/or virtual bus sharing. A problem will occur in case of svMotion without reconfiguration of the applications which are using these virtual disks and also change of the VM configuration concerned.');
$lang['VMINVALIDORINACCESSIBLE'] = array('title' => 'VM invalid or innaccessible', 'description' => 'The following VMs are marked as inaccessible or invalid.');
$lang['VMINCONSISTENT'] = array('title' => 'VM in inconsistent folder', 'description' => 'The following VMs are not stored in folders consistent to their names, this may cause issues when trying to locate them from the datastore manually.');
$lang['VMREMOVABLECONNECTED'] = array('title' => 'VM with removable devices', 'description' => 'This module will display VM that have removable devices (floppy, CD-Rom, ...) connected.');
$lang['ALARMSVM'] = array('title' => 'VM Alarms', 'description' => 'This module will display triggered alarms on VirtualMachine objects level with status and time of creation.');
$lang['VMGUESTIDMISMATCH'] = array('title' => 'VM GuestId Mismatch', 'description' => 'This module will display VM that have GuestOS setting different from GuestOS retrived through vmtools.');
$lang['VMPOWEREDOFF'] = array('title' => 'VM Powered Off', 'description' => 'This module will display VM that are Powered Off. This can be useful to check if this state is expected.');
$lang['VMMISNAMED'] = array('title' => 'VM misnamed', 'description' => 'This module will display VM that have FQDN (based on vmtools) mismatched with the VM object name.');
$lang['VMGUESTPIVOT'] = array('title' => 'VM GuestId pivot table', 'description' => 'This module will display GuestOS pivot table and family repartition');
?>
