#!/usr/bin/perl -w

use strict;
use warnings;
use Data::Dumper;
use Date::Format qw(time2str);
use Date::Parse;
use File::Path qw( make_path );
use Getopt::Long;
use JSON;
use Log::Log4perl qw(:easy);
use Number::Bytes::Human qw(format_bytes);
use POSIX qw(strftime);
use Socket;
use Switch;
use Time::Piece;
use URI::URL;
use VMware::VIRuntime;
use VMware::VICredStore;
# use XML::LibXML;
use DBI;
use DBD::mysql;
# loading VSAN module for perl
use FindBin;
use lib "$FindBin::Bin/VSAN/";
use VsanapiUtils;
load_vsanmgmt_binding_files("$FindBin::Bin/VSAN/bindings/VIM25VsanmgmtStub.pm",
                            "$FindBin::Bin/VSAN/bindings/VIM25VsanmgmtRuntime.pm");

# initialize starting point for duration calculation
my $start = time;

# TODO
# check for multiple run, prevent simultaneous execution
# add option --debug to show verbose log in console
# enable debug log only with debug flag

$Util::script_version = "0.1";
$ENV{'PERL_LWP_SSL_VERIFY_HOSTNAME'} = 0;
Log::Log4perl::init('/etc/log4perl.conf');

my $logger = Log::Log4perl->get_logger('sexiauditor.vcronScheduler');
my $filename = "/var/www/.vmware/credstore/vicredentials.xml";
my $s_item;
my @server_list;
my $u_item;
my @user_list;
my $password;
my $url;
my $href = ();
# my $xmlModuleFile = '/var/www/admin-db/conf/modules.xml';
# my $xmlModuleScheduleFile = '/var/www/admin-db/conf/moduleschedules.xml';
# my $xmlConfigsFile = '/var/www/admin-db/conf/configs.xml';
# my $schedulerTTBFile = '/opt/vcron/scheduler-ttb.xml';
my %boolHash = (true => "1", false => "0");

# Using --force switch will bypass scheduler and run every subroutine
my $force;
GetOptions("force" => \$force);
if ($force) { $logger->info ("[DEBUG] Force Mode enable, all checks will be run whatever their schedule!"); }

# Connect to the database.
my $dbh = DBI->connect("DBI:mysql:database=sexiauditor;host=localhost", "sexiauditor", 'Sex!@ud1t0r', {'RaiseError' => 1});

# global variables to store view objects
my ($view_Datacenter, $view_ClusterComputeResource, $view_VirtualMachine, $view_HostSystem, $view_Datastore, $view_DistributedVirtualPortgroup);
my ($alarm_key,$alarm_state,$alarm_name,$alarm_entity) = ("Alarm Key","Alarm State", "Alarm Name", "Alarm Entity");

# hastables
my %h_cluster = ("domain-c000" => "N/A");
my %h_host = ();
my %h_hostcluster = ();

# requiring both file to be readable
# (-r $xmlModuleFile) or $logger->logdie ("[ERROR] File $xmlModuleFile not available and/or readable, abort");
# (-r $xmlModuleScheduleFile) or $logger->logdie ("[ERROR] File $xmlModuleScheduleFile not available and/or readable, abort");
# (-r $xmlConfigsFile) or $logger->logdie ("[ERROR] File $xmlConfigsFile not available and/or readable, abort");
# (-w $schedulerTTBFile) or $logger->logdie ("[ERROR] File $schedulerTTBFile not available and/or writeable, abort");

# modules and settings xml file initialize
# my $docModule = XML::LibXML->new->parse_file($xmlModuleFile);
# my $docModuleSchedules = XML::LibXML->new->parse_file($xmlModuleScheduleFile);
# my $docConfigs = XML::LibXML->new->parse_file($xmlConfigsFile);

# Data purge threshold
# my $purgeThreshold = $docConfigs->findvalue("//config[id='thresholdHistory']/value") || 0;
my $purgeThreshold = dbGetConfig("thresholdHistory", 0);

# date schedule
# my $dailySchedule = ($docConfigs->exists("/configs/config[id='dailySchedule']/value") ? $docConfigs->findvalue("/configs/config[id='dailySchedule']/value") : 0);
my $dailySchedule = dbGetConfig("dailySchedule", 0);
# my $weeklySchedule = ($docConfigs->exists("/configs/config[id='weeklySchedule']/value") ? $docConfigs->findvalue("/configs/config[id='weeklySchedule']/value") : 0);
my $weeklySchedule = dbGetConfig("weeklySchedule", 0);
# my $monthlySchedule = ($docConfigs->exists("/configs/config[id='monthlySchedule']/value") ? $docConfigs->findvalue("/configs/config[id='monthlySchedule']/value") : 1);
my $monthlySchedule = dbGetConfig("monthlySchedule", 1);

# browsing modules and fetching schedule
$logger->info("[INFO] Start processing modules list");

my $query = "SELECT * FROM moduleSchedule";
my $sth = $dbh->prepare($query);
$sth->execute();
while (my $ref = $sth->fetchrow_hashref()) {
  if ($ref->{'schedule'} ne 'off') {
  	$href->{ $ref->{'id'} } = $ref->{'schedule'};
    $logger->info("[INFO] Found module " . $ref->{'id'} . " with schedule " . $ref->{'schedule'});
  } else {
    $logger->info("[INFO] Found module " . $ref->{'id'} . " with schedule off, skipping...");
  }
}
$sth->finish();

# fetching active modules
my $nbActiveModule = scalar keys(%$href);
$logger->info("[INFO] End processing modules list, found $nbActiveModule active modules");

# exiting if no active module
($nbActiveModule gt 0) or $logger->logdie ("[ERROR] No active module found, abort");

# datetime used for folder history management
# my $execDateTTB = time2str("%Y%m%d%H%M", time);
# my $execDate = time2str("%Y%m%d", time);


######################
###                ###
###  EDITING ZONE  ###
###                ###
######################

############################################
# XML files definition (1 module = 1 file) #
############################################

# my $xmlPath = "/opt/vcron/data/$execDate";
# if ( !-d $xmlPath ) { make_path $xmlPath or $logger->logdie("[ERROR] Failed to create path: $xmlPath"); }
### VirtualMachine
# my $xmlVMs = "$xmlPath/vms-global.xml";
# my $docVMs = XML::LibXML::Document->new('1.0', 'utf-8');
# my $rootVMs = $docVMs->createElement("vms");
### HostSystem
# my $xmlHosts = "$xmlPath/hosts-global.xml";
# my $docHosts = XML::LibXML::Document->new('1.0', 'utf-8');
# my $rootHosts = $docHosts->createElement("hosts");
### ClusterComputeResource
# my $xmlClusters = "$xmlPath/clusters-global.xml";
# my $docClusters = XML::LibXML::Document->new('1.0', 'utf-8');
# my $rootClusters = $docClusters->createElement("clusters");
### Datastore
# my $xmlDatastores = "$xmlPath/datastores-global.xml";
# my $docDatastores = XML::LibXML::Document->new('1.0', 'utf-8');
# my $rootDatastores = $docDatastores->createElement("datastores");
### Alarms
# my $xmlAlarms = "$xmlPath/alarms-global.xml";
# my $docAlarms = XML::LibXML::Document->new('1.0', 'utf-8');
# my $rootAlarms = $docAlarms->createElement("alarms");
### Snapshots
# my $xmlSnapshots = "$xmlPath/snapshots-global.xml";
# my $docSnapshots = XML::LibXML::Document->new('1.0', 'utf-8');
# my $rootSnapshots = $docSnapshots->createElement("snapshots");
### DistributedVirtualPortgroups
# my $xmlDistributedVirtualPortgroups = "$xmlPath/distributedvirtualportgroups-global.xml";
# my $docDistributedVirtualPortgroups = XML::LibXML::Document->new('1.0', 'utf-8');
# my $rootDistributedVirtualPortgroups = $docDistributedVirtualPortgroups->createElement("distributedvirtualportgroups");
### vCenter Sessions
# my $xmlSessions = "$xmlPath/sessions-global.xml";
# my $docSessions = XML::LibXML::Document->new('1.0', 'utf-8');
# my $rootSessions = $docSessions->createElement("sessions");
### vCenter Licenses
# my $xmlLicenses = "$xmlPath/licenses-global.xml";
# my $docLicenses = XML::LibXML::Document->new('1.0', 'utf-8');
# my $rootLicenses = $docLicenses->createElement("licenses");
### vCenter Certificates
# my $xmlCertificates = "$xmlPath/certificates-global.xml";
# my $docCertificates = XML::LibXML::Document->new('1.0', 'utf-8');
# my $rootCertificates = $docCertificates->createElement("certificates");
### Hardware Status error
# my $xmlHardwareStatus = "$xmlPath/hardwarestatus-global.xml";
# my $docHardwareStatus = XML::LibXML::Document->new('1.0', 'utf-8');
# my $rootHardwareStatus = $docHardwareStatus->createElement("hardwarestatus");
### Hardware Status error
# my $xmlConfigurationIssues = "$xmlPath/configurationissues-global.xml";
# my $docConfigurationIssues = XML::LibXML::Document->new('1.0', 'utf-8');
# my $rootConfigurationIssues = $docConfigurationIssues->createElement("configurationissues");


###########################################################
# dispatch table for subroutine (1 module = 1 subroutine) #
###########################################################
my %actions = ( inventory => \&inventory,
                VSANHealthCheck => \&sessionage,
                vcSessionAge => \&sessionage,
                vcLicenceReport => \&licenseReport,
                vcPermissionReport => \&dummy,
                vcTerminateSession => \&dummy,
                vcCertificatesReport => \&certificatesReport,
                clusterConfigurationIssues => \&dummy,
                clusterAdmissionControl => \&dummy,
                clusterHAStatus => \&dummy,
                clusterDatastoreConsistency => \&dummy,
                clusterMembersVersion => \&dummy,
                clusterMembersOvercommit => \&dummy,
                clusterMembersLUNPathCountMismatch => \&dummy,
                clusterCPURatio => \&dummy,
                clusterTPSSavings => \&dummy,
                clusterAutoSlotSize => \&dummy,
                clusterProfile => \&dummy,
                hostMaintenanceMode => \&dummy,
                hostballooningzipswap => \&dummy,
                hostLocalSwapDatastoreCompliance => \&dummy,
                hostProfileCompliance => \&dummy,
                hostRebootrequired => \&dummy,
                hostFQDNHostnameMismatch => \&dummy,
                hostPowerManagementPolicy => \&dummy,
                hostHardwareStatus => \&getHardwareStatus,
                hostConfigurationIssues => \&getConfigurationIssue,
                hostSyslogCheck => \&dummy,
                hostDNSCheck => \&dummy,
                hostNTPCheck => \&dummy,
                hostSshShell => \&dummy,
                hostLUNPathDead => \&dummy,
                vmSnapshotsage => \&dummy,
                vmphantomsnapshot => \&dummy,
                vmballoonzipswap => \&dummy,
                vmmultiwritermode => \&dummy,
                vmNonpersistentmode => \&dummy,
                vmscsibussharing => \&dummy,
                vmconsolidationneeded => \&dummy,
                vmcpuramhddreservation => \&dummy,
                vmcpuramhddlimits => \&dummy,
                vmcpuramhotadd => \&dummy,
                vmvHardwarePivot => \&dummy,
                vmToolsPivot => \&dummy,
                alarms => \&getAlarms,
                vmInconsistent => \&dummy,
                vmRemovableConnected => \&dummy,
                vmGuestIdMismatch => \&dummy,
                vmPoweredOff => \&dummy,
                vmGuestPivot => \&dummy,
                vmMisnamed => \&dummy,
                vmInvalidOrInaccessible => \&dummy,
                networkDVSportsfree => \&dummy,
                networkDVPGAutoExpand => \&dummy,
                networkDVSprofile => \&dummy,
                datastoreSpacereport => \&dummy,
                datastoreOrphanedVMFilesreport => \&dummy,
                datastoreOverallocation => \&dummy,
                datastoreSIOCdisabled => \&dummy,
                datastoremaintenancemode => \&dummy,
                datastoreAccessible => \&dummy
              );

##########################
###                    ###
###  END EDITING ZONE  ###
###                    ###
##########################


# Data purge
# no purge done if 0
if ($purgeThreshold ne 0) {
  $logger->info("[INFO][PURGE] Start purge process");
  # my $command = `find /opt/vcron/data/ -type d -ctime +$purgeThreshold -exec rm -rf {} \\;`;
  # purge sql if date>threshold + active=0
  dbPurgeOldData($purgeThreshold);
  # $logger->info("[INFO][PURGE] Purge return: $command");
  $logger->info("[INFO][PURGE] End purge process");
}

# TODO = plan to kill some previous execution if it's hang
VMware::VICredStore::init (filename => $filename) or $logger->logdie ("[ERROR] Unable to initialize Credential Store.");
@server_list = VMware::VICredStore::get_hosts ();
foreach $s_item (@server_list) {
  $logger->info("[INFO][VCENTER] Start processing vCenter $s_item");
  my $normalizedServerName = $s_item;
  @user_list = VMware::VICredStore::get_usernames (server => $s_item);
  if (scalar @user_list == 0) {
    $logger->error("[ERROR] No credential store user detected for $s_item");
    next;
  } elsif (scalar @user_list > 1) {
    $logger->error("[ERROR] Multiple credential store user detected for $s_item");
    next;
  } else {
    $u_item = "@user_list";
    $password = VMware::VICredStore::get_password (server => $s_item, username => $u_item);
    $url = "https://" . $s_item . "/sdk";
    $normalizedServerName =~ s/[ .]/_/g;
    $normalizedServerName = lc ($normalizedServerName);
    my $sessionfile = "/tmp/vpx_${normalizedServerName}.dat";
    if (-e $sessionfile) {
      eval { Vim::load_session(service_url => $url, session_file => $sessionfile); };
      if ($@) {
        # session is no longer valid, we must destroy it to let it be recreated
        $logger->warn("[WARNING][TOKEN] Session file $sessionfile is no longer valid, it has been destroyed");
        unlink($sessionfile);
        eval { Vim::login(service_url => $url, user_name => $u_item, password => $password); };
        if ($@) {
          $logger->error("[ERROR] Cannot connect to vCenter $normalizedServerName and login $u_item, moving on to next vCenter entry");
          next;
        } else {
          $logger->info("[INFO][TOKEN] Saving session token in file $sessionfile");
          Vim::save_session(session_file => $sessionfile);
        }
      }
    } else {
      eval { Vim::login(service_url => $url, user_name => $u_item, password => $password); };
      if ($@) {
        $logger->error("[ERROR] Cannot connect to vCenter $normalizedServerName and login $u_item, moving on to next vCenter entry");
        next;
      } else {
        $logger->info("[INFO][TOKEN] Saving session token in file $sessionfile");
        Vim::save_session(session_file => $sessionfile);
      }
    }
  }

  # TODO
  # check version
  # watchdog

  # vCenter connection should be OK at this point
  # generating meta objects
  $logger->info("[INFO][OBJECTS] Start retrieving ClusterComputeResource objects");
  $view_ClusterComputeResource = Vim::find_entity_views(view_type => 'ClusterComputeResource', properties => ['name', 'host', 'summary', 'configIssue']);
  $logger->info("[INFO][OBJECTS] End retrieving ClusterComputeResource objects");
  $logger->info("[INFO][OBJECTS] Start retrieving HostSystem objects");
  $view_HostSystem = Vim::find_entity_views(view_type => 'HostSystem', properties => ['name', 'config.dateTimeInfo.ntpConfig.server', 'config.network.dnsConfig', 'config.powerSystemInfo.currentPolicy.shortName', 'configIssue', 'configManager.advancedOption', 'configManager.healthStatusSystem', 'configManager.storageSystem', 'configManager.serviceSystem', 'runtime.inMaintenanceMode', 'summary.config.product.fullName', 'summary.hardware.cpuMhz', 'summary.hardware.cpuModel', 'summary.hardware.memorySize', 'summary.hardware.model', 'summary.hardware.numCpuCores', 'summary.hardware.numCpuPkgs', 'summary.rebootRequired']);
  $logger->info("[INFO][OBJECTS] End retrieving HostSystem objects");
  $logger->info("[INFO][OBJECTS] Start retrieving DistributedVirtualPortgroup objects");
  $view_DistributedVirtualPortgroup = Vim::find_entity_views(view_type => 'DistributedVirtualPortgroup', properties => ['name', 'vm', 'config.numPorts', 'config.autoExpand', 'tag']);
  $logger->info("[INFO][OBJECTS] End retrieving DistributedVirtualPortgroup objects");
  $logger->info("[INFO][OBJECTS] Start retrieving Datastore objects");
  $view_Datastore = Vim::find_entity_views(view_type => 'Datastore', properties => ['name', 'summary', 'iormConfiguration']);
  $logger->info("[INFO][OBJECTS] End retrieving Datastore objects");
  $logger->info("[INFO][OBJECTS] Start retrieving Datacenter objects");
  $view_Datacenter = Vim::find_entity_views(view_type => 'Datacenter', properties => ['name','triggeredAlarmState']);
  $logger->info("[INFO][OBJECTS] End retrieving Datacenter objects");
  $logger->info("[INFO][OBJECTS] Start retrieving VirtualMachine objects");
  $view_VirtualMachine = Vim::find_entity_views(view_type => 'VirtualMachine', properties => ['name','guest','summary.config.vmPathName','config.guestId','runtime','network','summary.config.numCpu','summary.config.memorySizeMB','summary.storage','triggeredAlarmState','config.hardware.device','config.version','resourceConfig','config.cpuHotAddEnabled','config.memoryHotAddEnabled','config.extraConfig','summary.quickStats','snapshot']);
  $logger->info("[INFO][OBJECTS] End retrieving VirtualMachine objects");
  # hastables creation to speed later queries
  foreach my $cluster_view (@$view_ClusterComputeResource) {
    # my $cluster_name = lc ($cluster_view->name);
    # $h_cluster{%$cluster_view{'mo_ref'}->value} = $cluster_name;
    my $cluster_hosts_views = Vim::find_entity_views(view_type => 'HostSystem', begin_entity => $cluster_view , properties => [ 'name' ]);
    foreach my $cluster_host_view (@$cluster_hosts_views) {
      # my $host_name = lc ($cluster_host_view->{'name'});
      # $h_host{%$cluster_host_view{'mo_ref'}->value} = $host_name;
      $h_hostcluster{%$cluster_host_view{'mo_ref'}->type . "-" . %$cluster_host_view{'mo_ref'}->value} = %$cluster_view{'mo_ref'}->type . "-" . %$cluster_view{'mo_ref'}->value;
    }
  }
  my $StandaloneComputeResources = Vim::find_entity_views(view_type => 'ComputeResource', filter => {'summary.numHosts' => "1"}, properties => [ 'host' ]);
  foreach my $StandaloneComputeResource (@$StandaloneComputeResources) {
    if  ($StandaloneComputeResource->{'mo_ref'}->type eq "ComputeResource" ) {
      my @StandaloneResourceVMHost = Vim::get_views(mo_ref_array => $StandaloneComputeResource->host, properties => ['name']);
      my $StandaloneResourceVMHostName = $StandaloneResourceVMHost[0][0]->{'name'};
      $h_host{$StandaloneResourceVMHost[0][0]->{'mo_ref'}->value} = $StandaloneResourceVMHostName;
    }
  }

  for my $key ( keys(%$href) ) {
    # using dispatch table to call dynamically named subroutine
    my $value = $href->{$key};
    my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
    if ($force) {
      # --force switch have been triggered, unleashed the subroutine
      $logger->info("[INFO][SUBROUTINE-FORCE] Start process for $key (normal schedule is $value)");
      $actions{ $key }->();
      $logger->info("[INFO][SUBROUTINE-FORCE] End process for $key (normal schedule is $value)");
    } else {
      switch ($value) {
        case "hourly" {
          $logger->info("[INFO][SUBROUTINE] Start hourly process for $key");
          $actions{ $key }->();
          $logger->info("[INFO][SUBROUTINE] End hourly process for $key");
        }
        case "daily" {
          if ($hour == $dailySchedule) {
            $logger->info("[INFO][SUBROUTINE] Start daily process for $key");
            $actions{ $key }->();
            $logger->info("[INFO][SUBROUTINE] End daily process for $key");
          } else {
            $logger->info("[DEBUG][SUBROUTINE] Skipping daily process for $key as it's not yet daily schedule $dailySchedule");
          }
         }
        case "weekly" {
          if ($wday == $weeklySchedule) {
            $logger->info("[INFO][SUBROUTINE] Start weekly process for $key");
            $actions{ $key }->();
            $logger->info("[INFO][SUBROUTINE] End weekly process for $key");
          } else {
            $logger->info("[DEBUG][SUBROUTINE] Skipping weekly process for $key as it's not yet weekly schedule $weeklySchedule");
          }
        }
        case "monthly" {
          if ($wday == $weeklySchedule) {
            $logger->info("[INFO][SUBROUTINE] Start monthly process for $key");
            $actions{ $key }->();
            $logger->info("[INFO][SUBROUTINE] End monthly process for $key");
          } else {
            $logger->info("[DEBUG][SUBROUTINE] Skipping monthly process for $key as it's not yet monthly schedule $monthlySchedule");
          }
        }
        case "off" { $logger->info("[INFO][SUBROUTINE] Ignoring process for $key as it's off"); }
        else { $logger->warning("[WARNING][SUBROUTINE] Unknow schedule $value for $key"); }
      }
    }
  }
  $logger->info("[INFO][VCENTER] End processing vCenter $s_item");
}

###################################
# File dump generation            #
# Updating 'latest' symbolic link #
# >inline update is not possible  #
# >thus we do it in 2 way         #
###################################
# sub xmlDump {
#   my ($docXML, $obj, $xmlObject, $xmlFile) = @_;
#   if ($docXML->findvalue("count(//".$obj.")") > 0) {
#     if ($docXML->toFile($xmlObject, 2)) {
#       $logger->info("[INFO][XMLDUMP] Saving file $xmlObject");
#     } else {
#       $logger->error("[ERROR][XMLDUMP] Unable to save file $xmlObject");
#     }
#     unlink($xmlFile);
#     symlink($xmlObject, $xmlFile);
#     chmod 0644, $xmlObject;
#   } else {
#     $logger->info("[DEBUG][XMLDUMP] No objects for type $obj");
#   }
# }

# xmlDump($docVMs, "vm", $xmlVMs, "/opt/vcron/data/latest/vms-global.xml");
# xmlDump($docHosts, "host", $xmlHosts, "/opt/vcron/data/latest/hosts-global.xml");
# xmlDump($docClusters, "cluster", $xmlClusters, "/opt/vcron/data/latest/clusters-global.xml");
# xmlDump($docDatastores, "datastore", $xmlDatastores, "/opt/vcron/data/latest/datastores-global.xml");
# xmlDump($docAlarms, "alarm", $xmlAlarms, "/opt/vcron/data/latest/alarms-global.xml");
# xmlDump($docSnapshots, "snapshot", $xmlSnapshots, "/opt/vcron/data/latest/snapshots-global.xml");
# xmlDump($docDistributedVirtualPortgroups, "distributedvirtualportgroup", $xmlDistributedVirtualPortgroups, "/opt/vcron/data/latest/distributedvirtualportgroups-global.xml");
# xmlDump($docSessions, "session", $xmlSessions, "/opt/vcron/data/latest/sessions-global.xml");
# xmlDump($docLicenses, "license", $xmlLicenses, "/opt/vcron/data/latest/licenses-global.xml");
# xmlDump($docCertificates, "certificate", $xmlCertificates, "/opt/vcron/data/latest/certificates-global.xml");
# xmlDump($docHardwareStatus, "hardwarestate", $xmlHardwareStatus, "/opt/vcron/data/latest/hardwarestatus-global.xml");
# xmlDump($docConfigurationIssues, "configurationissue", $xmlConfigurationIssues, "/opt/vcron/data/latest/configurationissues-global.xml");

# my $ttbParser = XML::LibXML->new();
# $ttbParser->keep_blanks(0);
# my $docTTB = $ttbParser->load_xml(location => $schedulerTTBFile);
# my $executionEntry = $docTTB->ownerDocument->createElement('executiontime');
# $executionEntry->setAttribute("date", $execDateTTB);
# $executionEntry->setAttribute("seconds", time - $start);
# $docTTB->documentElement()->appendChild($executionEntry);
# $docTTB->toFile($schedulerTTBFile,2);

my $sqlInsert = $dbh->prepare("INSERT INTO executiontime (date, seconds) VALUES (FROM_UNIXTIME (?), ?)");
$sqlInsert->execute($start, time - $start);
$sqlInsert->finish();

# Disconnect from the database.
$dbh->disconnect();

#########################
# subroutine definition #
#########################

sub dummy { }

sub sessionage {
  my $sessionMgr = Vim::get_view(mo_ref => Vim::get_service_content()->sessionManager);
  my $sessionList = eval {$sessionMgr->sessionList || []};
  my $currentSessionkey = $sessionMgr->currentSession->key;
  my $vcentersdk = new URI::URL $sessionMgr->{'vim'}->{'service_url'};
  foreach my $session (@$sessionList) {
    my $loginTime = "0000-00-00 00:00:00";
    $loginTime = substr($session->loginTime, 0, 19);
    $loginTime =~ s/T/ /g;
    my $lastActiveTime = "0000-00-00 00:00:00";
    $lastActiveTime = substr($session->lastActiveTime, 0, 19);
    $lastActiveTime =~ s/T/ /g;
    # get vcenter id from database
    my $vcenterID = dbGetVC($vcentersdk->host);
    my $sessionKey = $session->key;
    my $userAgent = (defined($session->userAgent) ? $session->userAgent : 'N/A');
    my $ipAddress = (defined($session->ipAddress) ? $session->ipAddress : 'N/A');
    my $query = "SELECT * FROM sessions WHERE vcenter = '" . $vcenterID . "' AND sessionKey = '" . $sessionKey . "' AND active = 1";
    my $sth = $dbh->prepare($query);
    $sth->execute();
    my $rows = $sth->rows;
    # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
    my $ref = $sth->fetchrow_hashref();
    if (($rows gt 0)
      && ($ref->{'vcenter'} eq $vcenterID)
      && ($ref->{'sessionKey'} eq $sessionKey)
      && ($ref->{'loginTime'} eq $loginTime)
      && ($ref->{'userAgent'} eq $userAgent)
      && ($ref->{'ipAddress'} eq $ipAddress)
      && ($ref->{'lastActiveTime'} eq $lastActiveTime)
      && ($ref->{'userName'} eq $session->userName)) {
      # Sessions already exists, have not changed, updated lastseen property
      my $sqlUpdate = $dbh->prepare("UPDATE sessions set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
      $sqlUpdate->execute($start);
      $sqlUpdate->finish();
    } else {
      if ($rows gt 0) {
        # Sessions have changed, we must decom old one before create a new one
        my $sqlUpdate = $dbh->prepare("UPDATE sessions set active = 0 WHERE id = '" . $ref->{'id'} . "'");
        $sqlUpdate->execute();
        $sqlUpdate->finish();
      }

      my $sqlInsert = $dbh->prepare("INSERT INTO sessions (vcenter, sessionKey, loginTime, userAgent, ipAddress, lastActiveTime, userName, firstseen, lastseen, active) VALUES (?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?), ?)");
      $sqlInsert->execute(
        $vcenterID,
        $sessionKey,
        $loginTime,
        $userAgent,
        $ipAddress,
        $lastActiveTime,
        $session->userName,
        $start,
        $start,
        1
      );
      $sqlInsert->finish();
    }
  }
}

sub licenseReport {
  my $licMgr = Vim::get_view(mo_ref => Vim::get_service_content()->licenseManager);
  my $installedLicenses = $licMgr->licenses;
  my $vcentersdk = new URI::URL $licMgr->{'vim'}->{'service_url'};

  foreach my $license (@$installedLicenses) {
    # we don't want evaluation license to be stored
    if ($license->editionKey ne 'eval') {
      # get vcenter id from database
      my $vcenterID = dbGetVC($vcentersdk->host);
      my $query = "SELECT * FROM licenses WHERE vcenter = '" . $vcenterID . "' AND licenseKey = '" . $license->licenseKey . "' AND active = 1";
      my $sth = $dbh->prepare($query);
      $sth->execute();
      my $rows = $sth->rows;
      # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
      my $ref = $sth->fetchrow_hashref();
      if (($rows gt 0)
        && ($ref->{'vcenter'} eq $vcenterID)
        && ($ref->{'licenseKey'} eq $license->licenseKey)
        && ($ref->{'total'} eq $license->total)
        && ($ref->{'used'} eq $license->used)
        && ($ref->{'name'} eq $license->name)
        && ($ref->{'editionKey'} eq $license->editionKey)
        && ($ref->{'costUnit'} eq $license->costUnit)) {
        # License already exists, have not changed, updated lastseen property
        my $sqlUpdate = $dbh->prepare("UPDATE licenses set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
        $sqlUpdate->execute($start);
        $sqlUpdate->finish();
      } else {
        if ($rows gt 0) {
          # License have changed, we must decom old one before create a new one
          my $sqlUpdate = $dbh->prepare("UPDATE licenses set active = 0 WHERE id = '" . $ref->{'id'} . "'");
          $sqlUpdate->execute();
          $sqlUpdate->finish();
        }

        my $sqlInsert = $dbh->prepare("INSERT INTO licenses (vcenter, licenseKey, total, used, name, editionKey, costUnit, firstseen, lastseen, active) VALUES (?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?), ?)");
        $sqlInsert->execute(
          $vcenterID,
          $license->licenseKey,
          $license->total,
          $license->used,
          $license->name,
          $license->editionKey,
          $license->costUnit,
          $start,
          $start,
          1
        );
        $sqlInsert->finish();
      }
    }
  }
}

sub certificatesReport {
  my $vpxSetting = Vim::get_view(mo_ref => Vim::get_service_content()->setting);
  my $vpxSettings = $vpxSetting->setting;
  my $vcentersdk = new URI::URL $vpxSetting->{'vim'}->{'service_url'};
  foreach(@$vpxSettings) {
    # Query SDK, WS, SSO uri
    if($_->key eq "VirtualCenter.VimApiUrl" or $_->key eq "config.vpxd.sso.admin.uri") {
      my $urlToCheck = new URI::URL $_->value;
      my $startDate = '0000-00-00 00:00:00';
      my $endDate = '0000-00-00 00:00:00';
      if (gethostbyname($urlToCheck->host) && $urlToCheck->host ne 'localhost') {
        $urlToCheck = $urlToCheck->host . ":" . $urlToCheck->port;
        my $command = `echo "QUIT" | timeout 3 openssl s_client -connect $urlToCheck 2>/dev/null | openssl x509 -noout -dates`;
        $command =~ /^notBefore=(.*)$/m;
        $startDate = `date --date="$1" --iso-8601`;
        my $startTime = (split(/ /, $1))[2];
        $startDate =~ s/\r|\n//g;
        $startDate = $startDate . " " . $startTime;
        $command =~ /^notAfter=(.*)$/m;
        $endDate = `date --date="$1" --iso-8601`;
        my $endTime = (split(/ /, $1))[2];
        $endDate =~ s/\r|\n//g;
        $endDate = $endDate . " " . $endTime;
      }
      # get vcenter id from database
      my $vcenterID = dbGetVC($vcentersdk->host);
      my $certificateUrl = $_->value;
      my $query = "SELECT * FROM certificates WHERE vcenter = '" . $vcenterID . "' AND url = '" . $certificateUrl . "' AND active = 1";
      my $sth = $dbh->prepare($query);
      $sth->execute();
      my $rows = $sth->rows;
      # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
      my $ref = $sth->fetchrow_hashref();
      if (($rows gt 0)
        && ($ref->{'vcenter'} eq $vcenterID)
        && ($ref->{'url'} eq $_->value)
        && ($ref->{'type'} eq $_->key)
        && ($ref->{'start'} eq $startDate)
        && ($ref->{'end'} eq $endDate)) {
        # certificate already exists, have not changed, updated lastseen property
        my $sqlUpdate = $dbh->prepare("UPDATE certificates set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
        $sqlUpdate->execute($start);
        $sqlUpdate->finish();
      } else {
        if ($rows gt 0) {
          # certificate have changed, we must decom old one before create a new one
          my $sqlUpdate = $dbh->prepare("UPDATE certificates set active = 0 WHERE id = '" . $ref->{'id'} . "'");
          $sqlUpdate->execute();
          $sqlUpdate->finish();
        }

        my $sqlInsert = $dbh->prepare("INSERT INTO certificates (vcenter, url, type, start, end, firstseen, lastseen, active) VALUES (?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?), ?)");
        $sqlInsert->execute(
          $vcenterID,
          $_->value,
          $_->key,
          $startDate,
          $endDate,
          $start,
          $start,
          1
        );
        $sqlInsert->finish();
      }
    }
  }
}

sub inventory {
  # in order to avoid adding empty entries, inventory should be done from top objects to bottom ones (cluster>host>vm)
  clusterinventory( );
  hostinventory( );
  datastoreinventory( );
  dvpginventory( );
  vminventory( );
}

sub vminventory {
  foreach my $vm_view (@$view_VirtualMachine) {
    my $vmPath = Util::get_inventory_path($vm_view, Vim::get_vim());
    $vmPath = (split(/\/([^\/]+)$/, $vmPath))[0] || "Unknown";
    my $vnics = $vm_view->guest->net;
    my @vm_pg_string = ();
    my @vm_ip_string = ();
    my @vm_mac = ();
    foreach (@$vnics) {
      ($_->macAddress) ? push(@vm_mac, $_->macAddress) : push(@vm_mac, "N/A");
      ($_->network) ? push(@vm_pg_string, $_->network) : push(@vm_pg_string, "N/A");
      if ($_->ipConfig) {
        my $ips = $_->ipConfig->ipAddress;
        foreach (@$ips) {
          if ($_->ipAddress and $_->prefixLength <= 32) {
            push(@vm_ip_string, $_->ipAddress);
          }
        }
      } else {
        push(@vm_ip_string, "N/A");
      }
    }
    my $vm_guestfullname = "Not Available";
    if(defined($vm_view->guest) && defined($vm_view->guest->guestFullName)) { $vm_guestfullname = $vm_view->guest->guestFullName; }
    my $vm_guestFamily = "Not Available";
    if(defined($vm_view->guest) && defined($vm_view->guest->guestFamily)) { $vm_guestFamily = $vm_view->guest->guestFamily; }
    my $vm_guestHostName = "Not Available";
    if(defined($vm_view->guest) && defined($vm_view->guest->hostName)) { $vm_guestHostName = $vm_view->guest->hostName; }
    my $vm_guestId = "Not Available";
    if(defined($vm_view->guest) && defined($vm_view->guest->guestId)) { $vm_guestId = $vm_view->guest->guestId; }
    my $vm_configGuestId = "Not Available";
    if(defined($vm_view->{'config.guestId'})) { $vm_configGuestId = $vm_view->{'config.guestId'}; }
    my $vm_toolsVersion = 0;
    if(defined($vm_view->guest) && defined($vm_view->guest->toolsVersion)) { $vm_toolsVersion = 0+$vm_view->guest->toolsVersion; }
    my $devices = $vm_view->{'config.hardware.device'};
    my $removableExist = 0;
    foreach my $device (@$devices) {
      if(($device->isa('VirtualFloppy') or $device->isa('VirtualCdrom')) and $device->connectable->connected) {
        $removableExist = 1;
        last;
      }
    }
    my $sharedBus = 0;
    foreach my $device (@$devices) {
      if(($device->isa('VirtualSCSIController')) and $device->sharedBus->val ne 'noSharing') {
        $sharedBus = 1;
        last;
      }
    }
    my $multiwriter = 0;
    foreach(@{$vm_view->{'config.extraConfig'}}) {
      if ($_->key =~ /scsi.*sharing/ && $_->value eq 'multi-writer') {
        $multiwriter = 1;
        last;
     }
    }
    my $phantomSnapshot = 0;
    if (!$vm_view->snapshot) {
      foreach my $device (@$devices) {
        if ($device->isa('VirtualDisk') && $device->backing->fileName =~ /-\d{6}\.vmdk/i) {
          $phantomSnapshot = 1;
          last;
        }
      }
    }
    my $vcentersdk = new URI::URL $vm_view->{'vim'}->{'service_url'};
    # get vcenter id from database
    my $vcenterID = dbGetVC($vcentersdk->host);
    my $hostID = dbGetHost($vm_view->runtime->host->type."-".$vm_view->runtime->host->value, $vcenterID);
    my $moRef = $vm_view->{'mo_ref'}->{'type'}."-".$vm_view->{'mo_ref'}->{'value'};
    my $query = "SELECT vms.* FROM vms INNER JOIN hosts h ON vms.host = h.id WHERE h.vcenter = '" . $vcenterID . "' AND vms.moref = '" . $moRef . "' AND vms.active = 1";
    my $sth = $dbh->prepare($query);
    $sth->execute();
    my $rows = $sth->rows;
    my $numcpu = ($vm_view->{'summary.config.numCpu'} ? $vm_view->{'summary.config.numCpu'} : "0");
    my $memory = ($vm_view->{'summary.config.memorySizeMB'} ? $vm_view->{'summary.config.memorySizeMB'} : "0");
    my $provisionned = int(($vm_view->{'summary.storage'}->committed + $vm_view->{'summary.storage'}->uncommitted) / 1073741824);
    my $datastore = (split /\[/, (split /\]/, $vm_view->{'summary.config.vmPathName'})[0])[1];
    my $consolidationNeeded = (defined($vm_view->runtime->consolidationNeeded) ? $vm_view->runtime->consolidationNeeded : 0);
    my $cpuHotAddEnabled = (defined($vm_view->{'config.cpuHotAddEnabled'}) ? $vm_view->{'config.cpuHotAddEnabled'} : 0);
    my $memHotAddEnabled = (defined($vm_view->{'config.memoryHotAddEnabled'}) ? $vm_view->{'config.memoryHotAddEnabled'} : 0);

    # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
    my $ref = $sth->fetchrow_hashref();
    if (($rows gt 0)
      && ($ref->{'host'} eq $hostID)
      && ($ref->{'memReservation'} eq $vm_view->resourceConfig->memoryAllocation->reservation)
      && ($ref->{'guestFamily'} eq $vm_guestFamily)
      && ($ref->{'ip'} eq join(',', @vm_ip_string))
      && ($ref->{'swappedMemory'} eq 1048576*$vm_view->{'summary.quickStats'}->swappedMemory)
      && ($ref->{'cpuLimit'} eq $vm_view->resourceConfig->cpuAllocation->limit)
      && ($ref->{'consolidationNeeded'} eq $consolidationNeeded)
      && ($ref->{'fqdn'} eq $vm_guestHostName)
      && ($ref->{'numcpu'} eq $numcpu)
      && ($ref->{'cpuReservation'} eq $vm_view->resourceConfig->cpuAllocation->reservation)
      && ($ref->{'sharedBus'} eq $sharedBus)
      && ($ref->{'portgroup'} eq join(',', @vm_pg_string))
      && ($ref->{'memory'} eq $memory)
      && ($ref->{'phantomSnapshot'} eq $phantomSnapshot)
      && ($ref->{'hwversion'} eq $vm_view->{'config.version'})
      && ($ref->{'provisionned'} eq $provisionned)
      && ($ref->{'mac'} eq join(',', @vm_mac))
      && ($ref->{'multiwriter'} eq $multiwriter)
      && ($ref->{'memHotAddEnabled'} eq $boolHash{$memHotAddEnabled})
      && ($ref->{'guestOS'} eq $vm_guestfullname)
      && ($ref->{'compressedMemory'} eq 1024*$vm_view->{'summary.quickStats'}->compressedMemory)
      && ($ref->{'removable'} eq $removableExist)
      && ($ref->{'commited'} eq int($vm_view->{'summary.storage'}->committed / 1073741824))
      && ($ref->{'datastore'} eq $datastore)
      && ($ref->{'balloonedMemory'} eq 1048576*$vm_view->{'summary.quickStats'}->balloonedMemory)
      && ($ref->{'vmtools'} eq $vm_toolsVersion)
      && ($ref->{'name'} eq $vm_view->name)
      && ($ref->{'memLimit'} eq $vm_view->resourceConfig->memoryAllocation->limit)
      && ($ref->{'vmxpath'} eq $vm_view->{'summary.config.vmPathName'})
      && ($ref->{'connectionState'} eq $vm_view->runtime->connectionState->val)
      && ($ref->{'cpuHotAddEnabled'} eq $boolHash{$cpuHotAddEnabled})
      && ($ref->{'uncommited'} eq int($vm_view->{'summary.storage'}->uncommitted / 1073741824))
      && ($ref->{'powerState'} eq $vm_view->runtime->powerState->val)
      && ($ref->{'guestId'} eq $vm_guestId)
      && ($ref->{'configGuestId'} eq $vm_configGuestId)
      && ($ref->{'vmpath'} eq $vmPath)) {

      # VM already exists, have not changed, updated lastseen property
      $logger->info("[DEBUG][VM-INVENTORY] VM $moRef on host $hostID already exists and have not changed since last check, updating lastseen property");
      my $sqlUpdate = $dbh->prepare("UPDATE vms set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
      $sqlUpdate->execute($start);
      $sqlUpdate->finish();
    } else {
      if ($rows gt 0) {
        # VM have changed, we must decom old one before create a new one
        $logger->info("[DEBUG][VM-INVENTORY] VM $moRef on host $hostID have changed since last check, sending old entry " . $ref->{'id'} . " it into oblivion");
        my $sqlUpdate = $dbh->prepare("UPDATE vms set active = 0 WHERE id = '" . $ref->{'id'} . "'");
        $sqlUpdate->execute();
        $sqlUpdate->finish();
      }
      $logger->info("[DEBUG][VM-INVENTORY] Adding data for VM $moRef on host $hostID");
      my $sqlInsert = $dbh->prepare("INSERT INTO vms (host, moref, memReservation, guestFamily, ip, swappedMemory, cpuLimit, consolidationNeeded, fqdn, numcpu, cpuReservation, sharedBus, portgroup, memory, phantomSnapshot, hwversion, provisionned, mac, multiwriter, memHotAddEnabled, guestOS, compressedMemory, removable, commited, datastore, balloonedMemory, vmtools, name, memLimit, vmxpath, connectionState, cpuHotAddEnabled, uncommited, powerState, guestId, configGuestId, vmpath, firstseen, lastseen, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?), ?)");
      $sqlInsert->execute(
        $hostID,
        $moRef,
        $vm_view->resourceConfig->memoryAllocation->reservation,
        $vm_guestFamily,
        join(',', @vm_ip_string),
        1048576*$vm_view->{'summary.quickStats'}->swappedMemory,
        $vm_view->resourceConfig->cpuAllocation->limit,
        $consolidationNeeded,
        $vm_guestHostName,
        $numcpu,
        $vm_view->resourceConfig->cpuAllocation->reservation,
        $sharedBus,
        join(',', @vm_pg_string),
        $memory,
        $phantomSnapshot,
        $vm_view->{'config.version'},
        $provisionned,
        join(',', @vm_mac),
        $multiwriter,
        $boolHash{$memHotAddEnabled},
        $vm_guestfullname,
        1024*$vm_view->{'summary.quickStats'}->compressedMemory,
        $removableExist,
        int($vm_view->{'summary.storage'}->committed / 1073741824),
        $datastore,
        1048576*$vm_view->{'summary.quickStats'}->balloonedMemory,
        $vm_toolsVersion,
        $vm_view->name,
        $vm_view->resourceConfig->memoryAllocation->limit,
        $vm_view->{'summary.config.vmPathName'},
        $vm_view->runtime->connectionState->val,
        $boolHash{$cpuHotAddEnabled},
        int($vm_view->{'summary.storage'}->uncommitted / 1073741824),
        $vm_view->runtime->powerState->val,
        $vm_guestId,
        $vm_configGuestId,
        $vmPath,
        $start,
        $start,
        1
      );
      $sqlInsert->finish();
    }
    $sth->finish();
    my $vmID = dbGetVM($moRef,$hostID);
    if ($vm_view->snapshot) {
        foreach (@{$vm_view->snapshot->rootSnapshotList}) {
            getSnapshots($_, $vmID);
        }
    }
  }
}

sub hostinventory {
  foreach my $host_view (@$view_HostSystem) {
    my $serviceSys = Vim::get_view(mo_ref => $host_view->{'configManager.serviceSystem'}, properties => ['serviceInfo']);
    my $services = $serviceSys->serviceInfo->service;
    my $service_ssh = 'off';
    my $service_shell = 'off';
    foreach(@$services) {
      if($_->key eq 'TSM-SSH') {
        $service_ssh = $_->policy;
      } elsif($_->key eq 'TSM') {
        $service_shell = $_->policy;
      }
    }
    my $dnsservers = $host_view->{'config.network.dnsConfig'}->address;
    my @sorted_dnsservers = map { $_->[1] } sort { $a->[0] <=> $b->[0] } map {[ unpack('N',inet_aton($_)), $_ ]} @$dnsservers;
    my $ntpservers = $host_view->{'config.dateTimeInfo.ntpConfig.server'} || [];
    my $storageSys = Vim::get_view(mo_ref => $host_view->{'configManager.storageSystem'}, properties => ['storageDeviceInfo']);
    my $lunpathcount = 0;
    my $lundeadpathcount = 0;
    my $luns = eval{$storageSys->storageDeviceInfo->multipathInfo->lun || []};
    foreach my $lun (@$luns) {
      $lunpathcount += (0+@{$lun->path});
      foreach my $path (@{$lun->path}) {
        if ($path->{pathState} eq "dead") { $lundeadpathcount++; }
      }
    }
    my $advOpt = Vim::get_view(mo_ref => $host_view->{'configManager.advancedOption'});
    my $syslog_target = '';
    eval {
      $syslog_target = $advOpt->QueryOptions(name => 'Syslog.global.logHost');
      $syslog_target = @$syslog_target[0]->value;
    };
    my $vcentersdk = new URI::URL $host_view->{'vim'}->{'service_url'};
    my $moRef = $host_view->{'mo_ref'}->{'type'}."-".$host_view->{'mo_ref'}->{'value'};
    # get vcenter id from database
    my $vcenterID = dbGetVC($vcentersdk->host);
    $logger->info("[DEBUG][HOST-INVENTORY] Retrieved vCenterID = $vcenterID for host $moRef");
    my $clusterID = dbGetCluster((defined($h_hostcluster{$host_view->{'mo_ref'}->{'type'}."-".$host_view->{'mo_ref'}->{'value'}}) ? $h_hostcluster{$host_view->{'mo_ref'}->{'type'}."-".$host_view->{'mo_ref'}->{'value'}} : 0), $vcenterID);
    $logger->info("[DEBUG][HOST-INVENTORY] Retrieved clusterID = $clusterID for host $moRef");
    my $query = "SELECT * FROM hosts WHERE vcenter = '" . $vcenterID . "' AND moref = '" . $moRef . "' AND active = 1";
    my $sth = $dbh->prepare($query);
    $sth->execute();
    my $rows = $sth->rows;
    # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
    my $ref = $sth->fetchrow_hashref();
    if (($rows gt 0)
      && ($ref->{'vcenter'} eq $vcenterID)
      && ($ref->{'cluster'} eq $clusterID)
      && ($ref->{'hostname'} eq $host_view->{'config.network.dnsConfig'}->hostName)
      && ($ref->{'host_name'} eq $host_view->name)
      && ($ref->{'ntpservers'} eq join(';', sort @$ntpservers))
      && ($ref->{'deadlunpathcount'} eq $lundeadpathcount)
      && ($ref->{'numcpucore'} eq $host_view->{'summary.hardware.numCpuCores'})
      && ($ref->{'syslog_target'} eq $syslog_target)
      && ($ref->{'rebootrequired'} eq $boolHash{$host_view->{'summary.rebootRequired'}})
      && ($ref->{'powerpolicy'} eq (defined($host_view->{'config.powerSystemInfo.currentPolicy.shortName'}) ? $host_view->{'config.powerSystemInfo.currentPolicy.shortName'} : 'off'))
      && ($ref->{'bandwidthcapacity'} eq 0)
      && ($ref->{'memory'} eq $host_view->{'summary.hardware.memorySize'})
      && ($ref->{'dnsservers'} eq join(';', @sorted_dnsservers))
      && ($ref->{'cputype'} eq $host_view->{'summary.hardware.cpuModel'})
      && ($ref->{'numcpu'} eq $host_view->{'summary.hardware.numCpuPkgs'})
      && ($ref->{'inmaintenancemode'} eq $boolHash{$host_view->{'runtime.inMaintenanceMode'}})
      && ($ref->{'lunpathcount'} eq $lunpathcount)
      && ($ref->{'model'} eq $host_view->{'summary.hardware.model'})
      && ($ref->{'sharedmemory'} eq 0)
      && ($ref->{'cpumhz'} eq $host_view->{'summary.hardware.cpuMhz'})
      && ($ref->{'esxbuild'} eq $host_view->{'summary.config.product.fullName'})
      && ($ref->{'ssh_policy'} eq $service_ssh)
      && ($ref->{'shell_policy'} eq $service_shell)) {

      # Host already exists, have not changed, updated lastseen property
      $logger->info("[DEBUG][HOST-INVENTORY] Host $moRef already exists and have not changed since last check, updating lastseen property");
      my $sqlUpdate = $dbh->prepare("UPDATE hosts set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
      $sqlUpdate->execute($start);
      $sqlUpdate->finish();
    } else {
      if ($rows gt 0) {
        # Host have changed, we must decom old one before create a new one
        $logger->info("[DEBUG][HOST-INVENTORY] Host $moRef have changed since last check, sending old entry it into oblivion");
        my $sqlUpdate = $dbh->prepare("UPDATE hosts set active = 0 WHERE id = '" . $ref->{'id'} . "'");
        $sqlUpdate->execute();
        $sqlUpdate->finish();
      }
      $logger->info("[DEBUG][HOST-INVENTORY] Adding data for host $moRef");
      my $sqlInsert = $dbh->prepare("INSERT INTO hosts (vcenter, cluster, moref, hostname, host_name, ntpservers, deadlunpathcount, numcpucore, syslog_target, rebootrequired, powerpolicy, bandwidthcapacity, memory, dnsservers, cputype, numcpu, inmaintenancemode, lunpathcount, model, sharedmemory, cpumhz, esxbuild, ssh_policy, shell_policy, firstseen, lastseen, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?), ?)");
      $sqlInsert->execute(
        $vcenterID,
        $clusterID,
        $moRef,
        $host_view->{'config.network.dnsConfig'}->hostName,
        $host_view->name,
        join(';', sort @$ntpservers),
        $lundeadpathcount,
        $host_view->{'summary.hardware.numCpuCores'},
        $syslog_target,
        $boolHash{$host_view->{'summary.rebootRequired'}},
        (defined($host_view->{'config.powerSystemInfo.currentPolicy.shortName'}) ? $host_view->{'config.powerSystemInfo.currentPolicy.shortName'} : 'off'),
        0,
        $host_view->{'summary.hardware.memorySize'},
        join(';', @sorted_dnsservers),
        $host_view->{'summary.hardware.cpuModel'},
        $host_view->{'summary.hardware.numCpuPkgs'},
        $boolHash{$host_view->{'runtime.inMaintenanceMode'}},
        $lunpathcount,
        $host_view->{'summary.hardware.model'},
        0,
        $host_view->{'summary.hardware.cpuMhz'},
        $host_view->{'summary.config.product.fullName'},
        $service_ssh,
        $service_shell,
        $start,
        $start,
        1
      );
      $sqlInsert->finish();
    }
    $sth->finish();
  }
}

sub clusterinventory {
  foreach my $cluster_view (@$view_ClusterComputeResource) {
    my $lastconfigissue = 0;
    my $lastconfigissuetime = "0000-00-00 00:00:00";
    if (defined($cluster_view->configIssue)){
      foreach my $issue ( sort {$b->key cmp $a->key} @{$cluster_view->configIssue}) {
        $lastconfigissue = $issue->fullFormattedMessage;
        $lastconfigissuetime = substr($issue->createdTime, 0, 19);
        $lastconfigissuetime =~ s/T/ /g;
        last;
      }
    }
    my $dasenabled = (defined($cluster_view->summary->dasData) ? 1 : 0);
    my $vcentersdk = new URI::URL $cluster_view->{'vim'}->{'service_url'};
    # get vcenter id from database
    my $vcenterID = dbGetVC($vcentersdk->host);
    my $moRef = $cluster_view->{'mo_ref'}->{'type'}."-".$cluster_view->{'mo_ref'}->{'value'};
    my $query = "SELECT * FROM clusters WHERE vcenter = '" . $vcenterID . "' AND moref = '" . $moRef . "' AND active = 1";
    my $sth = $dbh->prepare($query);
    $sth->execute();
    my $rows = $sth->rows;
    # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
    my $ref = $sth->fetchrow_hashref();
    if (($rows gt 0)
      && ($ref->{'cluster_name'} eq $cluster_view->name)
      && ($ref->{'dasenabled'} eq $dasenabled)
      && ($ref->{'lastconfigissuetime'} eq $lastconfigissuetime)
      && ($ref->{'lastconfigissue'} eq $lastconfigissue)) {
      # Cluster already exists, have not changed, updated lastseen property
      $logger->info("[DEBUG][CLUSTER-INVENTORY] Cluster $moRef already exists and have not changed since last check, updating lastseen property");
      my $sqlUpdate = $dbh->prepare("UPDATE clusters set lastseen = FROM_UNIXTIME ($start), vmotion = " . $cluster_view->summary->numVmotions . " WHERE id = '" . $ref->{'id'} . "'");
      $sqlUpdate->execute();
      $sqlUpdate->finish();
    } else {
      if ($rows gt 0) {
        # Cluster have changed, we must decom old one before create a new one
        $logger->info("[DEBUG][CLUSTER-INVENTORY] Cluster $moRef have changed since last check, sending old entry it into oblivion");
        my $sqlUpdate = $dbh->prepare("UPDATE clusters set active = 0 WHERE id = '" . $ref->{'id'} . "'");
        $sqlUpdate->execute();
        $sqlUpdate->finish();
      }
      $logger->info("[DEBUG][CLUSTER-INVENTORY] Adding data for cluster $moRef");
      my $sqlInsert = $dbh->prepare("INSERT INTO clusters (vcenter, moref, cluster_name, vmotion, dasenabled, lastconfigissuetime, lastconfigissue, firstseen, lastseen, active) VALUES (?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?), ?)");
      $sqlInsert->execute(
        $vcenterID,
        $moRef,
        $cluster_view->name,
        $cluster_view->summary->numVmotions,
        $dasenabled,
        $lastconfigissuetime,
        $lastconfigissue,
        $start,
        $start,
        1
      );
      $sqlInsert->finish();
      if ($rows gt 0) {
        # We must update host if needed
        my $clusterID = dbGetCluster($moRef,$vcenterID);
        my $sqlUpdate = $dbh->prepare("UPDATE hosts SET cluster =  '" . $clusterID . "' WHERE cluster = '" . $ref->{'id'} . "' and active = 1");
        $sqlUpdate->execute();
        $sqlUpdate->finish();
      }
    }
    $sth->finish();
  }
}

sub datastoreinventory {
  foreach my $datastore_view (@$view_Datastore) {
    my $vcentersdk = new URI::URL $datastore_view->{'vim'}->{'service_url'};
    my $uncommitted = (defined($datastore_view->summary->uncommitted) ? $datastore_view->summary->uncommitted : 0);
    my $maintenanceMode = (defined($datastore_view->summary->maintenanceMode) ? $datastore_view->summary->maintenanceMode : 'normal');
    # get vcenter id from database
    my $vcenterID = dbGetVC($vcentersdk->host);
    my $moRef = $datastore_view->{'mo_ref'}->{'type'}."-".$datastore_view->{'mo_ref'}->{'value'};
    my $query = "SELECT * FROM datastores WHERE vcenter = '" . $vcenterID . "' AND moref = '" . $moRef . "' AND active = 1";
    my $sth = $dbh->prepare($query);
    $sth->execute();
    my $rows = $sth->rows;
    # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
    my $ref = $sth->fetchrow_hashref();
    if (($rows gt 0)
      && ($ref->{'vcenter'} eq $vcenterID)
      && ($ref->{'name'} eq $datastore_view->name)
      && ($ref->{'type'} eq $datastore_view->summary->type)
      && ($ref->{'size'} eq $datastore_view->summary->capacity)
      && ($ref->{'uncommitted'} eq $uncommitted)
      && ($ref->{'freespace'} eq $datastore_view->summary->freeSpace)
      && ($ref->{'maintenanceMode'} eq $maintenanceMode)
      && ($ref->{'isAccessible'} eq $datastore_view->summary->accessible)
      && ($ref->{'shared'} eq $datastore_view->summary->multipleHostAccess)
      && ($ref->{'iormConfiguration'} eq $datastore_view->iormConfiguration->enabled)) {
      # Cluster already exists, have not changed, updated lastseen property
      my $sqlUpdate = $dbh->prepare("UPDATE datastores set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
      $sqlUpdate->execute($start);
      $sqlUpdate->finish();
    } else {
      if ($rows gt 0) {
        # Cluster have changed, we must decom old one before create a new one
        my $sqlUpdate = $dbh->prepare("UPDATE datastores set active = 0 WHERE id = '" . $ref->{'id'} . "'");
        $sqlUpdate->execute();
        $sqlUpdate->finish();
      }

      my $sqlInsert = $dbh->prepare("INSERT INTO datastores (vcenter, moref, name, type, size, uncommitted, freespace, isAccessible, maintenanceMode, shared, iormConfiguration, firstseen, lastseen, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?), ?)");
      $sqlInsert->execute(
        $vcenterID,
        $moRef,
        $datastore_view->name,
        $datastore_view->summary->type,
        $datastore_view->summary->capacity,
        $uncommitted,
        $datastore_view->summary->freeSpace,
        $datastore_view->summary->accessible,
        $maintenanceMode,
        $datastore_view->summary->multipleHostAccess,
        $datastore_view->iormConfiguration->enabled,
        $start,
        $start,
        1
      );
      $sqlInsert->finish();
    }
  }
}

sub getHardwareStatus {
  foreach my $host_view (@$view_HostSystem) {
    my $healthStatusSystem = Vim::get_view(mo_ref => $host_view->{'configManager.healthStatusSystem'});
    my $vcentersdk = new URI::URL $host_view->{'vim'}->{'service_url'};
    my @h_hwissues = ();
    my %h_hwissue;
    if ($healthStatusSystem->runtime) {
      my $vcenterID = dbGetVC($vcentersdk->host);
      my $hostID = dbGetHost($host_view->{"mo_ref"}->{"type"}."-".$host_view->{"mo_ref"}->{"value"}, $vcenterID);
      if ($healthStatusSystem->runtime->hardwareStatusInfo) {
        my $cpuStatus = $healthStatusSystem->runtime->hardwareStatusInfo->cpuStatusInfo;
        foreach(@$cpuStatus) {
          if(lc($_->status->key) ne 'green' && lc($_->status->key) ne 'unknown') {
            my $query = "SELECT * FROM hardwarestatus WHERE host = '" . $hostID . "' AND issuename = '" . $_->name . "' AND active = 1";
            my $sth = $dbh->prepare($query);
            $sth->execute();
            my $rows = $sth->rows;
            # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
            my $ref = $sth->fetchrow_hashref();
            if (($rows gt 0) && ($ref->{'issuestate'} eq lc($_->status->key))) {
              # HWStatus already exists, have not changed, updated lastseen property
              my $sqlUpdate = $dbh->prepare("UPDATE hardwarestatus set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
              $sqlUpdate->execute($start);
              $sqlUpdate->finish();
            } else {
              if ($rows gt 0) {
                # HWStatus have changed, we must decom old one before create a new one
                my $sqlUpdate = $dbh->prepare("UPDATE hardwarestatus set active = 0 WHERE id = '" . $ref->{'id'} . "'");
                $sqlUpdate->execute();
                $sqlUpdate->finish();
              }
              my $sqlInsert = $dbh->prepare("INSERT INTO hardwarestatus (host, issuename, issuestate, issuetype, firstseen, lastseen, active) VALUES (?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?), ?)");
              $sqlInsert->execute(
                $hostID,
                $_->name,
                lc($_->status->key),
                "cpu",
                $start,
                $start,
                1
              );
              $sqlInsert->finish();
            }
          }
        }
        my $memStatus = $healthStatusSystem->runtime->hardwareStatusInfo->memoryStatusInfo;
        foreach(@$memStatus) {
          if(lc($_->status->key) ne 'green' && lc($_->status->key) ne 'unknown') {
            my $query = "SELECT * FROM hardwarestatus WHERE host = '" . $hostID . "' AND issuename = '" . $_->name . "' AND active = 1";
            my $sth = $dbh->prepare($query);
            $sth->execute();
            my $rows = $sth->rows;
            # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
            my $ref = $sth->fetchrow_hashref();
            if (($rows gt 0) && ($ref->{'issuestate'} eq lc($_->status->key))) {
              # HWStatus already exists, have not changed, updated lastseen property
              my $sqlUpdate = $dbh->prepare("UPDATE hardwarestatus set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
              $sqlUpdate->execute($start);
              $sqlUpdate->finish();
            } else {
              if ($rows gt 0) {
                # HWStatus have changed, we must decom old one before create a new one
                my $sqlUpdate = $dbh->prepare("UPDATE hardwarestatus set active = 0 WHERE id = '" . $ref->{'id'} . "'");
                $sqlUpdate->execute();
                $sqlUpdate->finish();
              }
              my $sqlInsert = $dbh->prepare("INSERT INTO hardwarestatus (host, issuename, issuestate, issuetype, firstseen, lastseen, active) VALUES (?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?), ?)");
              $sqlInsert->execute(
                $hostID,
                $_->name,
                lc($_->status->key),
                "memory",
                $start,
                $start,
                1
              );
              $sqlInsert->finish();
            }
          }
        }
        my $storageStatus = $healthStatusSystem->runtime->hardwareStatusInfo->storageStatusInfo;
        foreach(@$storageStatus) {
          if(lc($_->status->key) ne 'green' && lc($_->status->key) ne 'unknown') {
            my $query = "SELECT * FROM hardwarestatus WHERE host = '" . $hostID . "' AND issuename = '" . $_->name . "' AND active = 1";
            my $sth = $dbh->prepare($query);
            $sth->execute();
            my $rows = $sth->rows;
            # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
            my $ref = $sth->fetchrow_hashref();
            if (($rows gt 0) && ($ref->{'issuestate'} eq lc($_->status->key))) {
              # HWStatus already exists, have not changed, updated lastseen property
              my $sqlUpdate = $dbh->prepare("UPDATE hardwarestatus set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
              $sqlUpdate->execute($start);
              $sqlUpdate->finish();
            } else {
              if ($rows gt 0) {
                # HWStatus have changed, we must decom old one before create a new one
                my $sqlUpdate = $dbh->prepare("UPDATE hardwarestatus set active = 0 WHERE id = '" . $ref->{'id'} . "'");
                $sqlUpdate->execute();
                $sqlUpdate->finish();
              }
              my $sqlInsert = $dbh->prepare("INSERT INTO hardwarestatus (host, issuename, issuestate, issuetype, firstseen, lastseen, active) VALUES (?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?), ?)");
              $sqlInsert->execute(
                $hostID,
                $_->name,
                lc($_->status->key),
                "storage",
                $start,
                $start,
                1
              );
              $sqlInsert->finish();
            }
          }
        }
      }
      if($healthStatusSystem->runtime->systemHealthInfo) {
        my $sensorInfo = $healthStatusSystem->runtime->systemHealthInfo->numericSensorInfo;
        foreach(@$sensorInfo) {
          if($_->healthState && lc($_->healthState->key) ne 'green' && lc($_->healthState->key) ne 'unknown') {
            my $query = "SELECT * FROM hardwarestatus WHERE host = '" . $hostID . "' AND issuename = '" . $_->name . "' AND active = 1";
            my $sth = $dbh->prepare($query);
            $sth->execute();
            my $rows = $sth->rows;
            # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
            my $ref = $sth->fetchrow_hashref();
            if (($rows gt 0) && ($ref->{'issuestate'} eq lc($_->healthState->key))) {
              # HWStatus already exists, have not changed, updated lastseen property
              my $sqlUpdate = $dbh->prepare("UPDATE hardwarestatus set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
              $sqlUpdate->execute($start);
              $sqlUpdate->finish();
            } else {
              if ($rows gt 0) {
                # HWStatus have changed, we must decom old one before create a new one
                my $sqlUpdate = $dbh->prepare("UPDATE hardwarestatus set active = 0 WHERE id = '" . $ref->{'id'} . "'");
                $sqlUpdate->execute();
                $sqlUpdate->finish();
              }
              my $sqlInsert = $dbh->prepare("INSERT INTO hardwarestatus (host, issuename, issuestate, issuetype, firstseen, lastseen, active) VALUES (?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?), ?)");
              $sqlInsert->execute(
                $hostID,
                $_->name,
                lc($_->healthState->key),
                $_->sensorType,
                $start,
                $start,
                1
              );
              $sqlInsert->finish();
            }
          }
        }
      }
    }
  }
}

sub getAlarms {
  foreach my $datacenter_view (@$view_Datacenter) {
    next if(!defined($datacenter_view->triggeredAlarmState));
    foreach my $triggeredAlarm (@{$datacenter_view->triggeredAlarmState}) {
      my $entity = Vim::get_view(mo_ref => $triggeredAlarm->entity, properties => [ 'name' ]);
      my $alarm = Vim::get_view(mo_ref => $triggeredAlarm->alarm, properties => [ 'info.name' ]);
      my $vcentersdk = new URI::URL $datacenter_view->{'vim'}->{'service_url'};
      my $vcenterID = dbGetVC($vcentersdk->host);
      my $createTime = "0000-00-00 00:00:00";
      $createTime = substr($triggeredAlarm->time, 0, 19);
      $createTime =~ s/T/ /g;
      my $moRef = $alarm->{'mo_ref'}->{'type'}."-".$alarm->{'mo_ref'}->{'value'};
      my $entityMoRef = $entity->{'mo_ref'}->{'type'}."-".$entity->{'mo_ref'}->{'value'};
      my $query = "SELECT * FROM alarms WHERE vcenter = '" . $vcenterID . "' AND moref = '" . $moRef . "' AND entityMoRef = '" . $entityMoRef . "' AND active = 1";
      my $sth = $dbh->prepare($query);
      $sth->execute();
      my $rows = $sth->rows;
      # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
      my $ref = $sth->fetchrow_hashref();
      if (($rows gt 0)
        && ($ref->{'entityMoRef'} eq $entityMoRef)
        && ($ref->{'alarm_name'} eq $alarm->{'info.name'})
        && ($ref->{'time'} eq $createTime)
        && ($ref->{'status'} eq $triggeredAlarm->overallStatus->val)) {
        # Alarm already exists, have not changed, updated lastseen property
        my $sqlUpdate = $dbh->prepare("UPDATE alarms set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
        $sqlUpdate->execute($start);
        $sqlUpdate->finish();
      } else {
        if ($rows gt 0) {
          # Alarm have changed, we must decom old one before create a new one
          my $sqlUpdate = $dbh->prepare("UPDATE alarms set active = 0 WHERE id = '" . $ref->{'id'} . "'");
          $sqlUpdate->execute();
          $sqlUpdate->finish();
        }
        my $sqlInsert = $dbh->prepare("INSERT INTO alarms (vcenter, moref, entityMoRef, alarm_name, time, status, firstseen, lastseen, active) VALUES (?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?), ?)");
        $sqlInsert->execute(
          $vcenterID,
          $moRef,
          $entityMoRef,
          $alarm->{'info.name'},
          $createTime,
          $triggeredAlarm->overallStatus->val,
          $start,
          $start,
          1
        );
        $sqlInsert->finish();
      }
      $sth->finish();
    }
  }
}

sub getSnapshots {
  my ($snapshotTree,$vmID) = @_;
  my $description = (defined($snapshotTree->description) ? $snapshotTree->description : 'Not Available');
  my $moRef = $snapshotTree->{'snapshot'}->{'type'}."-".$snapshotTree->{'snapshot'}->{'value'};
  my $createTime = "0000-00-00 00:00:00";
  $createTime = substr($snapshotTree->createTime, 0, 19);
  $createTime =~ s/T/ /g;
  my $query = "SELECT * FROM snapshots WHERE vm = '" . $vmID . "' AND moref = '" . $moRef . "' AND active = 1";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
  my $ref = $sth->fetchrow_hashref();
  if (($rows gt 0)
    && ($ref->{'name'} eq $snapshotTree->name)
    && ($ref->{'createTime'} eq $createTime)
    && ($ref->{'snapid'} eq $snapshotTree->id)
    && ($ref->{'description'} eq $description)
    && ($ref->{'quiesced'} eq $snapshotTree->quiesced)
    && ($ref->{'state'} eq $snapshotTree->state->val)) {
    # Snapshot already exists, have not changed, updated lastseen property
    my $sqlUpdate = $dbh->prepare("UPDATE snapshots set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
    $sqlUpdate->execute($start);
    $sqlUpdate->finish();
  } else {
    if ($rows gt 0) {
      # Snapshot have changed, we must decom old one before create a new one
      my $sqlUpdate = $dbh->prepare("UPDATE snapshots set active = 0 WHERE id = '" . $ref->{'id'} . "'");
      $sqlUpdate->execute();
      $sqlUpdate->finish();
    }
    my $sqlInsert = $dbh->prepare("INSERT INTO snapshots (vm, moref, name, createTime, snapid, description, quiesced, state, firstseen, lastseen, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?), ?)");
    $sqlInsert->execute(
      $vmID,
      $moRef,
      $snapshotTree->name,
      $createTime,
      $snapshotTree->id,
      $description,
      $snapshotTree->quiesced,
      $snapshotTree->state->val,
      $start,
      $start,
      1
    );
    $sqlInsert->finish();
  }
  $sth->finish();

  # recurse through the tree of snaps
  if ($snapshotTree->childSnapshotList) {
    # loop through any children that may exist
    foreach (@{$snapshotTree->childSnapshotList}) {
      getSnapshots($_,$vmID);
    }
  }
}

sub getConfigurationIssue {
  foreach my $host_view (@$view_HostSystem) {
    my $vcentersdk = new URI::URL $host_view->{'vim'}->{'service_url'};
    # get vcenter id from database
    my $vcenterID = dbGetVC($vcentersdk->host);
    my $hostID = dbGetHost($host_view->{'mo_ref'}->{'type'}."-".$host_view->{'mo_ref'}->{'value'}, $vcenterID);
    foreach ($host_view->configIssue) {
      if (defined(@$_[0])) {
        my $query = "SELECT * FROM configurationissues WHERE host = '" . $hostID . "' AND configissue = '" . @$_[0]->fullFormattedMessage . "' AND active = 1";
        my $sth = $dbh->prepare($query);
        $sth->execute();
        my $rows = $sth->rows;
        # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
        my $ref = $sth->fetchrow_hashref();
        if ($rows gt 0) {
          # ConfigIssue already exists, have not changed, updated lastseen property
          $logger->info("[DEBUG][CONFIGISSUE-INVENTORY] ConfigIssue for host $hostID already exists and have not changed since last check, updating lastseen property");
          my $sqlUpdate = $dbh->prepare("UPDATE configurationissues set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
          $sqlUpdate->execute($start);
          $sqlUpdate->finish();
        } else {
          $logger->info("[DEBUG][CONFIGISSUE-INVENTORY] Adding ConfigIssue data for host $hostID");
          my $sqlInsert = $dbh->prepare("INSERT INTO configurationissues (host, configissue, firstseen, lastseen, active) VALUES (?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?), ?)");
          $sqlInsert->execute(
            $hostID,
            @$_[0]->fullFormattedMessage,
            $start,
            $start,
            1
          );
          $sqlInsert->finish();
        }
        $sth->finish();
      }
    }
  }
}

sub dvpginventory {
  foreach my $distributedVirtualPortgroup_view (@$view_DistributedVirtualPortgroup) {
    # Exclude DV uplinks portgroup
    if (!defined($distributedVirtualPortgroup_view->tag) || @{$distributedVirtualPortgroup_view->tag}[0]->key ne 'SYSTEM/DVS.UPLINKPG') {
      my $vcentersdk = new URI::URL $distributedVirtualPortgroup_view->{'vim'}->{'service_url'};
      my $openPorts = $distributedVirtualPortgroup_view->{'config.numPorts'} - (defined($distributedVirtualPortgroup_view->vm) ? 0+@{$distributedVirtualPortgroup_view->vm} : 0);
      # get vcenter id from database
      my $vcenterID = dbGetVC($vcentersdk->host);
      my $moRef = $distributedVirtualPortgroup_view->{'mo_ref'}->{'type'}."-".$distributedVirtualPortgroup_view->{'mo_ref'}->{'value'};
      my $query = "SELECT * FROM distributedvirtualportgroups WHERE vcenter = '" . $vcenterID . "' AND moref = '" . $moRef . "' AND active = 1";
      my $sth = $dbh->prepare($query);
      $sth->execute();
      my $rows = $sth->rows;
      # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
      my $ref = $sth->fetchrow_hashref();
      if (($rows gt 0)
        && ($ref->{'vcenter'} eq $vcenterID)
                && ($ref->{'name'} eq $distributedVirtualPortgroup_view->name)
        && ($ref->{'numports'} eq $distributedVirtualPortgroup_view->{'config.numPorts'})
        && ($ref->{'openports'} eq $openPorts)
        && ($ref->{'autoexpand'} eq $boolHash{$distributedVirtualPortgroup_view->{'config.autoExpand'}})) {
        # DVPortgroup already exists, have not changed, updated lastseen property
        my $sqlUpdate = $dbh->prepare("UPDATE distributedvirtualportgroups set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
        $sqlUpdate->execute($start);
        $sqlUpdate->finish();
      } else {
        if ($rows gt 0) {
          # DVPortgroup have changed, we must decom old one before create a new one
          my $sqlUpdate = $dbh->prepare("UPDATE distributedvirtualportgroups set active = 0 WHERE id = '" . $ref->{'id'} . "'");
          $sqlUpdate->execute();
          $sqlUpdate->finish();
        }
        my $sqlInsert = $dbh->prepare("INSERT INTO distributedvirtualportgroups (vcenter, moref, name, numports, openports, autoexpand, firstseen, lastseen, active) VALUES (?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?), ?)");
        $sqlInsert->execute(
          $vcenterID,
          $moRef,
          $distributedVirtualPortgroup_view->name,
          $distributedVirtualPortgroup_view->{'config.numPorts'},
          $openPorts,
          $boolHash{$distributedVirtualPortgroup_view->{'config.autoExpand'}},
          $start,
          $start,
          1
        );
        $sqlInsert->finish();
      }
      $sth->finish();
    }
  }
}

sub dbGetVC {
  # This subroutine will return vcenter ID if it exists
  # or create a new vcenter ID if not
  my ($vcenterName) = @_;
  my $query = "SELECT id FROM vcenters WHERE vcname = '" . $vcenterName . "'";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  my $vcenterID = 0;
  if ($rows eq 0) {
    # vcenter ID does not exist so we create it and return it
    my $sqlInsert = $dbh->prepare("INSERT INTO vcenters (vcname, firstseen, lastseen) VALUES (?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
    $sqlInsert->execute($vcenterName, $start, $start);
    $sqlInsert->finish();
    # re-execute query after inserting new vcenter
    $sth = $dbh->prepare($query);
    $sth->execute();
    my $ref = $sth->fetchrow_hashref();
    $vcenterID = $ref->{'id'};
  } else {
    # vcenter is still alive, updating lastseen property
    my $ref = $sth->fetchrow_hashref();
    $vcenterID = $ref->{'id'};
    $dbh->do("UPDATE vcenters set lastseen = FROM_UNIXTIME ($start) WHERE id = '" . $vcenterID . "'");
  }
  $sth->finish();
  return $vcenterID;
}

sub dbGetCluster {
  # This subroutine will return cluster ID if it exists
  # or create a new cluster ID if not
  my ($clusterMoref,$vcenterID) = @_;
  my $query = "SELECT id FROM clusters WHERE moref = '" . $clusterMoref . "' AND vcenter = '" . $vcenterID . "' AND active = 1";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  if ($rows eq 0) {
    # vcenter ID does not exist so we create it and return it
    my $sqlInsert = $dbh->prepare("INSERT INTO clusters (vcenter, moref) VALUES (?, ?)");
    $sqlInsert->execute($vcenterID, $clusterMoref);
    $sqlInsert->finish();
    # re-execute query after inserting new vcenter
    $sth = $dbh->prepare($query);
    $sth->execute();
  }
  my $clusterID = 0;
  while (my $ref = $sth->fetchrow_hashref()) {
    $clusterID = $ref->{'id'};
    last;
  }
  $sth->finish();
  $logger->info("[DEBUG][GETCLUSTER] ClusterID for cluster $clusterMoref on vCenter $vcenterID is $clusterID");
  return $clusterID;
}

sub dbGetHost {
  # This subroutine will return host ID if it exists
  # or create a new host ID if not
  my ($hostMoref,$vcenterID) = @_;
  my $query = "SELECT id FROM hosts WHERE moref = '" . $hostMoref . "' AND vcenter = '" . $vcenterID . "' AND active = 1";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  if ($rows eq 0) {
    # host ID does not exist so we create it and return it
    my $sqlInsert = $dbh->prepare("INSERT INTO hosts (vcenter, moref) VALUES (?, ?)");
    $sqlInsert->execute($vcenterID, $hostMoref);
    $sqlInsert->finish();
    # re-execute query after inserting new vcenter
    $sth = $dbh->prepare($query);
    $sth->execute();
  }
  my $hostID = 0;
  while (my $ref = $sth->fetchrow_hashref()) {
    $hostID = $ref->{'id'};
    last;
  }
  $sth->finish();
  $logger->info("[DEBUG][GETHOST] HostID for host $hostMoref on vCenter $vcenterID is $hostID");
  return $hostID;
}

sub dbGetVM {
  # This subroutine will return vm ID
  my ($vmMoref,$hostID) = @_;
  my $query = "SELECT id FROM vms WHERE moref = '" . $vmMoref . "' AND host = '" . $hostID . "' AND active = 1";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $vmID = 0;
  while (my $ref = $sth->fetchrow_hashref()) {
    $vmID = $ref->{'id'};
    last;
  }
  $logger->info("[DEBUG][GETVM] VMID for vm $vmMoref on host $hostID is $vmID");
  return $vmID;
}

sub dbGetConfig {
  # This subroutine will return config value
  my ($configID,$defaultValue) = @_;
  my $query = "SELECT value FROM config WHERE configid = '" . $configID . "'";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  my $configValue = 0;
  if ($rows eq 0) {
    $configValue = $defaultValue;
  } else {
    my $ref = $sth->fetchrow_hashref();
    $configValue = $ref->{'value'};
  }
  $logger->info("[DEBUG][GETCONFIG] ConfigID $configID have value $configValue");
  return $configValue;
}

sub dbGetSchedule {
  # This subroutine will return config value
  my ($moduleID) = @_;
  my $query = "SELECT schedule FROM moduleSchedule WHERE id = '" . $moduleID . "'";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  my $scheduleValue = "off";
  if ($rows gt 0) {
    my $ref = $sth->fetchrow_hashref();
    $scheduleValue = $ref->{'schedule'};
  }
  $logger->info("[DEBUG][GETSCHEDULE] ModuleID $moduleID have schedule $scheduleValue");
  return $scheduleValue;
}

sub dbPurgeOldData {
  # This subroutine will scavenge old data based on date threshold
  my ($purgeThreshold) = @_;
  $dbh->do("DELETE FROM alarms WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY) AND active = 0");
  $dbh->do("DELETE FROM certificates WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY) AND active = 0");
  $dbh->do("DELETE FROM clusters WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY) AND active = 0");
  $dbh->do("DELETE FROM configurationissues WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY) AND active = 0");
  $dbh->do("DELETE FROM datastores WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY) AND active = 0");
  $dbh->do("DELETE FROM distributedvirtualportgroups WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY) AND active = 0");
  $dbh->do("DELETE FROM hardwarestatus WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY) AND active = 0");
  $dbh->do("DELETE FROM hosts WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY) AND active = 0");
  $dbh->do("DELETE FROM licenses WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY) AND active = 0");
  $dbh->do("DELETE FROM sessions WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY) AND active = 0");
  $dbh->do("DELETE FROM snapshots WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY) AND active = 0");
  $dbh->do("DELETE FROM vcenters WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY)");
  $dbh->do("DELETE FROM vms WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY) AND active = 0");
  # After purging data, we run some optimisation task on db
  # TODO = switch to InnoDb RECREATE + ANALYZE tasks as OPTIMIZE is supported only on MyISAM
  $dbh->do("OPTIMIZE TABLE alarms, certificates, clusters, configurationissues, datastores, distributedvirtualportgroups, hardwarestatus, hosts, licenses, sessions, snapshots, vcenters, vms");
}
