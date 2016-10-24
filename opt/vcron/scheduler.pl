#!/usr/bin/perl -w

use strict;
use warnings;
use DBI;
use DBD::mysql;
use Data::Dumper;
use Date::Format qw(time2str);
use Date::Parse;
use Encode;
use File::Fetch;
use File::Find;
use File::Path qw( make_path remove_tree );
use Getopt::Long;
use JSON;
use List::Util qw( min max );
use Log::Log4perl qw(:easy);
use LWP::UserAgent;
use MIME::Lite::TT::HTML;
use Number::Bytes::Human qw(format_bytes);
use POSIX qw(strftime ceil);
use Socket;
use Switch;
use Time::Piece;
use URI::URL;
use utf8;
use VMware::VIRuntime;
use VMware::VICredStore;

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
my $activeVC;
my %boolHash = (true => "1", false => "0");
my $perfMgr;
my %perfCntr;
my $capacityPlanningExecuted = 0;

# Using --force switch will bypass scheduler and run every subroutine
my $force;
GetOptions("force" => \$force);
$logger->info("[BYPASS-MODE] Force Mode enable, all checks will be run whatever their schedule!") if $force;

# Connect to the database.
my $dbh = DBI->connect("DBI:mysql:database=sexiauditor;host=localhost", "sexiauditor", 'Sex!@ud1t0r', {'RaiseError' => 1});

# global variables to store view objects
my ($view_Datacenter, $view_ClusterComputeResource, $view_VirtualMachine, $view_HostSystem, $view_Datastore, $view_DistributedVirtualPortgroup);
my ($alarm_key,$alarm_state,$alarm_name,$alarm_entity) = ("Alarm Key","Alarm State", "Alarm Name", "Alarm Entity");

# hastables
my %h_cluster = ("domain-c000" => "N/A");
my %h_host = ();
my %h_hostcluster = ();

# debug Mode
my $showDebug = (dbGetConfig("showDebug", 'disable') eq 'disable') ? 0 : 1;

# Data purge threshold
my $purgeThreshold = dbGetConfig("thresholdHistory", 0);

# Date schedule
my $dailySchedule = dbGetConfig("dailySchedule", 0);
my $weeklySchedule = dbGetConfig("weeklySchedule", 0);
my $monthlySchedule = dbGetConfig("monthlySchedule", 1);

# browsing modules and fetching schedule
$logger->info("[INFO] Start processing modules list");
my $query = "SELECT module, schedule FROM modules ORDER BY id";
my $sth = $dbh->prepare($query);
$sth->execute();
my $nbActiveModule = 0;

while (my $ref = $sth->fetchrow_hashref())
{
  
  if ($ref->{'schedule'} ne 'off')
  {
    
    $nbActiveModule++;
    $logger->info("[INFO] Found module " . $ref->{'module'} . " with schedule " . $ref->{'schedule'});
    
  }
  else
  {
    
    $logger->info("[INFO] Found module " . $ref->{'module'} . " with schedule off, skipping...");
    
  } # END if ($ref->{'schedule'} ne 'off')

} # END while (my $ref = $sth->fetchrow_hashref())

$sth->finish();
$logger->info("[INFO] End processing modules list, found $nbActiveModule active modules");

# exiting if no active module
($nbActiveModule gt 0) or $logger->logdie ("[ERROR] No active module found, abort");

###########################################################
# dispatch table for subroutine (1 module = 1 subroutine) #
###########################################################
my %actions = ( inventory => \&inventory,
                VSANHealthCheck => \&VSANHealthCheck,
                vcSessionAge => \&sessionage,
                vcLicenceReport => \&licenseReport,
                vcPermissionReport => \&getPermissions,
                vcTerminateSession => \&terminateSession,
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
                hostBuildPivot => \&dummy,
                hostBundlebackup => \&bundleBackup,
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
                datastoreOrphanedVMFilesreport => \&datastoreOrphanedVMFilesreport,
                datastoreOverallocation => \&dummy,
                datastoreSIOCdisabled => \&dummy,
                datastoremaintenancemode => \&dummy,
                datastoreAccessible => \&dummy,
                capacityPlanningReport => \&capacityPlanningReport,
                mailAlert => \&mailAlert
              );

# Data purge
# no purge done if 0
if ($purgeThreshold ne 0)
{
  
  $logger->info("[INFO][PURGE] Start purge process");
  dbPurgeOldData($purgeThreshold);
  $logger->info("[INFO][PURGE] End purge process");
  
} # END if ($purgeThreshold ne 0)

# TODO = plan to kill some previous execution if it's hang
VMware::VICredStore::init (filename => $filename) or $logger->logdie ("[ERROR] Unable to initialize Credential Store.");
@server_list = VMware::VICredStore::get_hosts ();

foreach $s_item (@server_list)
{
  
  # next if ($s_item ne "vmlon03vce2.lon.uk.world.socgen");
  $activeVC = $s_item;
  $logger->info("[INFO][VCENTER] Start processing vCenter $s_item");
  my $normalizedServerName = $s_item;
  @user_list = VMware::VICredStore::get_usernames (server => $s_item);
  
  if (scalar @user_list == 0)
  {
    
    $logger->error("[ERROR] No credential store user detected for $s_item");
    next;
    
  }
  elsif (scalar @user_list > 1)
  {
    
    $logger->error("[ERROR] Multiple credential store user detected for $s_item");
    next;
    
  }
  else
  {
    
    $u_item = "@user_list";
    $password = VMware::VICredStore::get_password (server => $s_item, username => $u_item);
    $url = "https://" . $s_item . "/sdk";
    $normalizedServerName =~ s/[ .]/_/g;
    $normalizedServerName = lc ($normalizedServerName);
    my $sessionfile = "/tmp/vpx_${normalizedServerName}.dat";
    
    if (-e $sessionfile)
    {
      
      eval
      {
        
        Vim::load_session(service_url => $url, session_file => $sessionfile);
      
      }; # END eval
      
      if ($@)
      {
        
        # session is no longer valid, we must destroy it to let it be recreated
        $logger->warn("[WARNING][TOKEN] Session file $sessionfile is no longer valid, it has been destroyed");
        unlink($sessionfile);
        
        eval
        {
          
          Vim::login(service_url => $url, user_name => $u_item, password => $password);
        
        }; # END eval
        
        if ($@)
        {
          
          $logger->error("[ERROR] Cannot connect to vCenter $normalizedServerName and login $u_item, moving on to next vCenter entry");
          next;
          
        }
        else
        {
          
          $logger->info("[INFO][TOKEN] Saving session token in file $sessionfile");
          Vim::save_session(session_file => $sessionfile);
          
        } # END if ($@)
        
      } # END if ($@)
      
    }
    else
    {
      
      eval
      {
        
        Vim::login(service_url => $url, user_name => $u_item, password => $password);
      
      }; # END eval
      
      if ($@)
      {
        
        $logger->error("[ERROR] Cannot connect to vCenter $normalizedServerName and login $u_item, moving on to next vCenter entry");
        next;
        
      }
      else
      {
        
        $logger->info("[INFO][TOKEN] Saving session token in file $sessionfile");
        Vim::save_session(session_file => $sessionfile);
        
      } # END if ($@)
      
    } # END if (-e $sessionfile)
    
  } # END if (scalar @user_list == 0)

  # TODO
  # check version
  # watchdog
  $perfMgr = (Vim::get_view(mo_ref => Vim::get_service_content()->perfManager));
  %perfCntr = map { $_->groupInfo->key . "." . $_->nameInfo->key . "." . $_->rollupType->val => $_ } @{$perfMgr->perfCounter};
  # vCenter connection should be OK at this point, generating meta objects
  $logger->info("[INFO][OBJECTS] Start retrieving ClusterComputeResource objects");
  $view_ClusterComputeResource = Vim::find_entity_views(view_type => 'ClusterComputeResource', properties => ['name', 'host', 'summary', 'configIssue', 'configuration.dasConfig.admissionControlPolicy', 'configuration.dasConfig.admissionControlEnabled', 'configurationEx']);
  $logger->info("[INFO][OBJECTS] End retrieving ClusterComputeResource objects");
  $logger->info("[INFO][OBJECTS] Start retrieving HostSystem objects");
  $view_HostSystem = Vim::find_entity_views(view_type => 'HostSystem', properties => ['name', 'config.dateTimeInfo.ntpConfig.server', 'config.network.dnsConfig', 'config.powerSystemInfo.currentPolicy.shortName', 'configIssue', 'configManager.advancedOption', 'configManager.firmwareSystem', 'configManager.healthStatusSystem', 'configManager.storageSystem', 'configManager.serviceSystem', 'datastore', 'runtime.inMaintenanceMode', 'runtime.connectionState', 'summary.config.product.fullName', 'summary.hardware.cpuMhz', 'summary.hardware.cpuModel', 'summary.hardware.memorySize', 'summary.hardware.model', 'summary.hardware.numCpuCores', 'summary.hardware.numCpuPkgs', 'summary.rebootRequired', 'summary.runtime.connectionState', 'summary.quickStats']);
  $logger->info("[INFO][OBJECTS] End retrieving HostSystem objects");
  $logger->info("[INFO][OBJECTS] Start retrieving DistributedVirtualPortgroup objects");
  $view_DistributedVirtualPortgroup = Vim::find_entity_views(view_type => 'DistributedVirtualPortgroup', properties => ['name', 'vm', 'config.numPorts', 'config.autoExpand', 'tag']);
  $logger->info("[INFO][OBJECTS] End retrieving DistributedVirtualPortgroup objects");
  $logger->info("[INFO][OBJECTS] Start retrieving Datastore objects");
  $view_Datastore = Vim::find_entity_views(view_type => 'Datastore', properties => ['name', 'summary', 'iormConfiguration', 'browser']);
  $logger->info("[INFO][OBJECTS] End retrieving Datastore objects");
  $logger->info("[INFO][OBJECTS] Start retrieving Datacenter objects");
  $view_Datacenter = Vim::find_entity_views(view_type => 'Datacenter', properties => ['name','triggeredAlarmState']);
  $logger->info("[INFO][OBJECTS] End retrieving Datacenter objects");
  $logger->info("[INFO][OBJECTS] Start retrieving VirtualMachine objects");
  $view_VirtualMachine = Vim::find_entity_views(view_type => 'VirtualMachine', properties => ['name','guest','summary.config.vmPathName','layoutEx.file','layout.swapFile','config.guestId','runtime','network','summary.config.numCpu','summary.config.memorySizeMB','summary.storage','triggeredAlarmState','config.hardware.device','config.version','resourceConfig','config.cpuHotAddEnabled','config.memoryHotAddEnabled','config.extraConfig','summary.quickStats','snapshot']);
  $logger->info("[INFO][OBJECTS] End retrieving VirtualMachine objects");
  
  # hastables creation to speed later queries
  foreach my $cluster_view (@$view_ClusterComputeResource)
  {
    
    my $cluster_hosts_views = Vim::find_entity_views(view_type => 'HostSystem', begin_entity => $cluster_view , properties => [ 'name' ]);
    
    foreach my $cluster_host_view (@$cluster_hosts_views)
    {
      
      $h_hostcluster{%$cluster_host_view{'mo_ref'}->type . "-" . %$cluster_host_view{'mo_ref'}->value} = %$cluster_view{'mo_ref'}->type . "-" . %$cluster_view{'mo_ref'}->value;
      
    } # END foreach my $cluster_host_view (@$cluster_hosts_views)
    
  } # END foreach my $cluster_view (@$view_ClusterComputeResource)
  
  my $StandaloneComputeResources = Vim::find_entity_views(view_type => 'ComputeResource', filter => {'summary.numHosts' => "1"}, properties => [ 'host' ]);
  
  foreach my $StandaloneComputeResource (@$StandaloneComputeResources)
  {
        
    if ($StandaloneComputeResource->{'mo_ref'}->type eq "ComputeResource" )
    {
      
      my @StandaloneResourceVMHost = Vim::get_views(mo_ref_array => $StandaloneComputeResource->host, properties => ['name']);
      my $StandaloneResourceVMHostName = $StandaloneResourceVMHost[0][0]->{'name'};
      $h_host{$StandaloneResourceVMHost[0][0]->{'mo_ref'}->value} = $StandaloneResourceVMHostName;
      
    } # END if ($StandaloneComputeResource->{'mo_ref'}->type eq "ComputeResource" )
    
  } # END foreach my $StandaloneComputeResource (@$StandaloneComputeResources)

  $sth = $dbh->prepare($query);
  $sth->execute();
  
  while (my $ref = $sth->fetchrow_hashref())
  {
    
    my $key = $ref->{'module'};
    my $value = $ref->{'schedule'};
    my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
    
    if ($force && $value ne "off")
    {
      
      # --force switch have been triggered, unleashed the subroutine
      $logger->info("[INFO][SUBROUTINE-FORCE] Start process for $key (normal schedule is $value)");
      $actions{ $key }->();
      $logger->info("[INFO][SUBROUTINE-FORCE] End process for $key (normal schedule is $value)");
      
    }
    else
    {
      
      switch ($value)
      {
        
        case "hourly"
        {
          
          $logger->info("[INFO][SUBROUTINE] Start hourly process for $key");
          $actions{ $key }->();
          $logger->info("[INFO][SUBROUTINE] End hourly process for $key");
          
        } # END case "hourly"
        
        case "daily"
        {
          
          if ($hour == $dailySchedule)
          {
            
            $logger->info("[INFO][SUBROUTINE] Start daily process for $key");
            $actions{ $key }->();
            $logger->info("[INFO][SUBROUTINE] End daily process for $key");
            
          }
          else
          {
            
            $logger->info("[DEBUG][SUBROUTINE] Skipping daily process for $key as it's not yet daily schedule $dailySchedule");
            
          } # END if ($hour == $dailySchedule)
          
        } # END case "daily"
        
        case "weekly"
        {
          
          if ($wday == $weeklySchedule) 
          {
            
            $logger->info("[INFO][SUBROUTINE] Start weekly process for $key");
            $actions{ $key }->();
            $logger->info("[INFO][SUBROUTINE] End weekly process for $key");
            
          }
          else
          {
            
            $logger->info("[DEBUG][SUBROUTINE] Skipping weekly process for $key as it's not yet weekly schedule $weeklySchedule");
            
          } # END if ($wday == $weeklySchedule) 
          
        } # END case "weekly"
        
        case "monthly"
        {
          
          if ($wday == $weeklySchedule)
          {
            
            $logger->info("[INFO][SUBROUTINE] Start monthly process for $key");
            $actions{ $key }->();
            $logger->info("[INFO][SUBROUTINE] End monthly process for $key");
            
          }
          else
          {
            
            $logger->info("[DEBUG][SUBROUTINE] Skipping monthly process for $key as it's not yet monthly schedule $monthlySchedule") if $showDebug;
            
          } # END if ($wday == $weeklySchedule)
          
        } # END case "monthly"
         
        case "off"
        {
          
          $logger->info("[INFO][SUBROUTINE] Ignoring process for $key as it's off");
          
        } # END case "off"
        
        else
        {
          
          $logger->warning("[WARNING][SUBROUTINE] Unknow schedule $value for $key");
        
        } # END else
        
      } # END switch ($value)
      
    } # END if ($force && $value ne "off")
    
  } # END while (my $ref = $sth->fetchrow_hashref())
  
  $sth->finish();
  $logger->info("[INFO][VCENTER] End processing vCenter $s_item");
  
} # END foreach $s_item (@server_list)

my $sqlInsert = $dbh->prepare("INSERT INTO executiontime (date, seconds) VALUES (FROM_UNIXTIME (?), ?)");
$sqlInsert->execute($start, time - $start);
$sqlInsert->finish();

# Disconnect from the database.
$dbh->disconnect();

#########################
# subroutine definition #
#########################

sub dummy { }

sub sessionage
{
  
  my $sessionMgr = Vim::get_view(mo_ref => Vim::get_service_content()->sessionManager);
  my $sessionList = eval {$sessionMgr->sessionList || []};
  my $currentSessionkey = $sessionMgr->currentSession->key;
  my $vcentersdk = new URI::URL $sessionMgr->{'vim'}->{'service_url'};
  
  foreach my $session (@$sessionList)
  {
    
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
    my $query = "SELECT * FROM sessions WHERE vcenter = '" . $vcenterID . "' AND sessionKey = '" . $sessionKey . "' ORDER BY lastseen DESC LIMIT 1";
    my $sth = $dbh->prepare($query);
    $sth->execute();
    my $rows = $sth->rows;
    # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
    my $ref = $sth->fetchrow_hashref();
    
    if (($rows gt 0)
      && ($ref->{'sessionKey'} eq $sessionKey)
      && ($ref->{'loginTime'} eq $loginTime)
      && ($ref->{'userAgent'} eq $userAgent)
      && ($ref->{'ipAddress'} eq $ipAddress)
      && ($ref->{'userName'} eq $session->userName))
    {
      
      # Sessions already exists, have not changed, updated lastseen property
      my $sqlUpdate = $dbh->prepare("UPDATE sessions set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
      $sqlUpdate->execute($start);
      $sqlUpdate->finish();
      
    }
    else
    {
      
      my $sqlInsert = $dbh->prepare("INSERT INTO sessions (vcenter, sessionKey, loginTime, userAgent, ipAddress, lastActiveTime, userName, firstseen, lastseen) VALUES (?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
      $sqlInsert->execute(
        $vcenterID,
        $sessionKey,
        $loginTime,
        $userAgent,
        $ipAddress,
        $lastActiveTime,
        $session->userName,
        $start,
        $start
      );
      $sqlInsert->finish();
      
    } # END if (($rows gt 0) + checks
    
  } # END foreach my $session (@$sessionList)
  
} # END sub sessionage

sub licenseReport
{
  
  my $licMgr = Vim::get_view(mo_ref => Vim::get_service_content()->licenseManager);
  my $installedLicenses = $licMgr->licenses;
  my $vcentersdk = new URI::URL $licMgr->{'vim'}->{'service_url'};

  foreach my $license (@$installedLicenses)
  {
    
    # we don't want evaluation license to be stored
    if ($license->editionKey ne 'eval')
    {
      
      # get vcenter id from database
      my $vcenterID = dbGetVC($vcentersdk->host);
      my $query = "SELECT * FROM licenses WHERE vcenter = '" . $vcenterID . "' AND licenseKey = '" . $license->licenseKey . "' ORDER BY lastseen DESC LIMIT 1";
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
        && ($ref->{'costUnit'} eq $license->costUnit))
      {
        
        # License already exists, have not changed, updated lastseen property
        my $sqlUpdate = $dbh->prepare("UPDATE licenses set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
        $sqlUpdate->execute($start);
        $sqlUpdate->finish();
        
      }
      else
      {
        
        my $sqlInsert = $dbh->prepare("INSERT INTO licenses (vcenter, licenseKey, total, used, name, editionKey, costUnit, firstseen, lastseen) VALUES (?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
        $sqlInsert->execute(
          $vcenterID,
          $license->licenseKey,
          $license->total,
          $license->used,
          $license->name,
          $license->editionKey,
          $license->costUnit,
          $start,
          $start
        );
        $sqlInsert->finish();
        
      } # END if (($rows gt 0) + checks
      
    } # END if ($license->editionKey ne 'eval')
    
  } # END foreach my $license (@$installedLicenses)
  
} # END sub licenseReport

sub certificatesReport
{
  
  my $vpxSetting = Vim::get_view(mo_ref => Vim::get_service_content()->setting);
  my $vpxSettings = $vpxSetting->setting;
  my $vcentersdk = new URI::URL $vpxSetting->{'vim'}->{'service_url'};
  
  foreach my $vpxSetting (@$vpxSettings)
  {
    
    # Query SDK, WS, SSO uri
    if ($vpxSetting->key eq "VirtualCenter.VimApiUrl" or $vpxSetting->key eq "config.vpxd.sso.admin.uri")
    {
      
      my $urlToCheck = new URI::URL $vpxSetting->value;
      my $startDate = '0000-00-00 00:00:00';
      my $endDate = '0000-00-00 00:00:00';
      
      if (gethostbyname($urlToCheck->host) && $urlToCheck->host ne 'localhost')
      {
        
        $urlToCheck = $urlToCheck->host . ":" . $urlToCheck->port;
        my $command = `echo "QUIT" | timeout 3 openssl s_client -connect $urlToCheck 2>/dev/null | openssl x509 -noout -dates`;
        
        if (defined($command))
        {
          
          $command =~ /notBefore=(.*)/;
          $startDate = `date --date="$1" --iso-8601`;
          my $normalizedStartDate = $1;
          $normalizedStartDate =~ s/ +/ /;
          my $startTime = (split(/ /, $normalizedStartDate))[2];
          $startDate =~ s/\r|\n//g;
          $startDate = $startDate . " " . $startTime;
          $command =~ /notAfter=(.*)/;
          $endDate = `date --date="$1" --iso-8601`;
          my $normalizedEndDate = $1;
          $normalizedEndDate =~ s/ +/ /;
          my $endTime = (split(/ /, $normalizedEndDate))[2];
          $endDate =~ s/\r|\n//g;
          $endDate = $endDate . " " . $endTime;
          
        } # END if (defined($command))
        
      } # END if (gethostbyname($urlToCheck->host) && $urlToCheck->host ne 'localhost')
      
      # get vcenter id from database
      my $vcenterID = dbGetVC($vcentersdk->host);
      my $certificateUrl = $vpxSetting->value;
      my $query = "SELECT * FROM certificates WHERE vcenter = '" . $vcenterID . "' AND url = '" . $certificateUrl . "' ORDER BY lastseen DESC LIMIT 1";
      my $sth = $dbh->prepare($query);
      $sth->execute();
      my $rows = $sth->rows;
      # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
      my $ref = $sth->fetchrow_hashref();
      
      if (($rows gt 0)
        && ($ref->{'vcenter'} eq $vcenterID)
        && ($ref->{'url'} eq $vpxSetting->value)
        && ($ref->{'type'} eq $vpxSetting->key)
        && ($ref->{'start'} eq $startDate)
        && ($ref->{'end'} eq $endDate))
      {
          
        # certificate already exists, have not changed, updated lastseen property
        my $sqlUpdate = $dbh->prepare("UPDATE certificates set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
        $sqlUpdate->execute($start);
        $sqlUpdate->finish();
        
      }
      else
      {

        my $sqlInsert = $dbh->prepare("INSERT INTO certificates (vcenter, url, type, start, end, firstseen, lastseen) VALUES (?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
        $sqlInsert->execute(
          $vcenterID,
          $vpxSetting->value,
          $vpxSetting->key,
          $startDate,
          $endDate,
          $start,
          $start
        );
        $sqlInsert->finish();
        
      } # END if (($rows gt 0) + checks
      
    } # END if ($vpxSetting->key eq "VirtualCenter.VimApiUrl" or $vpxSetting->key eq "config.vpxd.sso.admin.uri")
    
  } # END foreach my $vpxSetting (@$vpxSettings)
  
} # END sub certificatesReport

sub inventory
{
  
  # in order to avoid adding empty entries, inventory should be done from top objects to bottom ones (cluster>host>vm)
  clusterinventory( );
  hostinventory( );
  datastoreinventory( );
  dvpginventory( );
  vminventory( );
  
} # END sub inventory

sub vminventory
{
  
  foreach my $vm_view (@$view_VirtualMachine)
  {
    
    my $vmPath = Util::get_inventory_path($vm_view, Vim::get_vim());
    $vmPath = (split(/\/([^\/]+)$/, $vmPath))[0] || "Unknown";
    if ($vmPath ne "Unknown") { $vmPath = '/'.$vmPath; }
    my $vnics = $vm_view->guest->net;
    my @vm_pg_string = ();
    my @vm_ip_string = ();
    my @vm_mac = ();
    
    foreach (@$vnics)
    {
      
      ($_->macAddress) ? push(@vm_mac, $_->macAddress) : push(@vm_mac, "N/A");
      ($_->network) ? push(@vm_pg_string, $_->network) : push(@vm_pg_string, "N/A");
      
      if ($_->ipConfig)
      {
        
        my $ips = $_->ipConfig->ipAddress;
        
        foreach (@$ips)
        {
          
          if ($_->ipAddress and $_->prefixLength <= 32)
          {
            
            push(@vm_ip_string, $_->ipAddress);
            
          } # END if ($_->ipAddress and $_->prefixLength <= 32)
          
        } # END foreach (@$ips)
        
      }
      else
      {
        
        push(@vm_ip_string, "N/A");
        
      } # END if ($_->ipConfig)
      
    } # END foreach (@$vnics)
    
    my $vm_guestfullname = (defined($vm_view->guest) && defined($vm_view->guest->guestFullName)) ? $vm_view->guest->guestFullName : "Not Available";
    my $vm_guestFamily = (defined($vm_view->guest) && defined($vm_view->guest->guestFamily)) ? $vm_view->guest->guestFamily : "Not Available";
    my $vm_guestHostName = (defined($vm_view->guest) && defined($vm_view->guest->hostName)) ? $vm_view->guest->hostName : "Not Available";
    my $vm_guestId = (defined($vm_view->guest) && defined($vm_view->guest->guestId)) ? $vm_view->guest->guestId : "Not Available";
    my $vm_configGuestId = (defined($vm_view->{'config.guestId'})) ? $vm_view->{'config.guestId'} : "Not Available";
    my $vm_toolsVersion = (defined($vm_view->guest) && defined($vm_view->guest->toolsVersion)) ? 0+$vm_view->guest->toolsVersion : 0;
    my $devices = $vm_view->{'config.hardware.device'};
    my $extraConfigs = $vm_view->{'config.extraConfig'};
    my $removableExist = 0;
    
    foreach my $device (@$devices)
    {
      
      if (($device->isa('VirtualFloppy') or $device->isa('VirtualCdrom')) and $device->connectable->connected)
      {
        
        $removableExist = 1;
        last;
        
      } # END if (($device->isa('VirtualFloppy') or $device->isa('VirtualCdrom')) and $device->connectable->connected)
      
    } # END foreach my $device (@$devices)
    
    my $sharedBus = 0;
    
    foreach my $device (@$devices)
    {
      
      if (($device->isa('VirtualSCSIController')) and $device->sharedBus->val ne 'noSharing')
      {
        
        $sharedBus = 1;
        last;
        
      } # END if (($device->isa('VirtualSCSIController')) and $device->sharedBus->val ne 'noSharing')
      
    } # END foreach my $device (@$devices)
    
    my $multiwriter = 0;
    
    foreach my $extraConfig (@$extraConfigs)
    {
      
      if ($extraConfig->key =~ /scsi.*sharing/ && $extraConfig->value eq 'multi-writer')
      {
        
        $multiwriter = 1;
        last;
        
     } # END if ($extraConfig->key =~ /scsi.*sharing/ && $extraConfig->value eq 'multi-writer')
     
   } # END foreach my $extraConfig (@$extraConfigs)
    
    my $phantomSnapshot = 0;
    
    if (!$vm_view->snapshot)
    {
      
      foreach my $device (@$devices)
      {
        
        if ($device->isa('VirtualDisk') && $device->backing->fileName =~ /-\d{6}\.vmdk/i)
        {
          
          $phantomSnapshot = 1;
          last;
          
        } # END if ($device->isa('VirtualDisk') && $device->backing->fileName =~ /-\d{6}\.vmdk/i)
        
      } # END foreach my $device (@$devices)
      
    } # END if (!$vm_view->snapshot)
    
    my $vcentersdk = new URI::URL $vm_view->{'vim'}->{'service_url'};
    my $vcenterID = dbGetVC($vcentersdk->host);
    my $host = dbGetHost($vm_view->runtime->host->type."-".$vm_view->runtime->host->value, $vcenterID);
    my $moRef = $vm_view->{'mo_ref'}->{'type'}."-".$vm_view->{'mo_ref'}->{'value'};
    my $numcpu = ($vm_view->{'summary.config.numCpu'} ? $vm_view->{'summary.config.numCpu'} : "0");
    my $memory = ($vm_view->{'summary.config.memorySizeMB'} ? $vm_view->{'summary.config.memorySizeMB'} : "0");
    my $provisionned = int(($vm_view->{'summary.storage'}->committed + $vm_view->{'summary.storage'}->uncommitted) / 1073741824);
    my $uncommitted = int($vm_view->{'summary.storage'}->uncommitted / 1073741824);
    my $balloonedMemory = 1048576*$vm_view->{'summary.quickStats'}->balloonedMemory;
    my $swappedMemory = 1048576*$vm_view->{'summary.quickStats'}->swappedMemory;
    my $compressedMemory = 1024*$vm_view->{'summary.quickStats'}->compressedMemory;
    my $committed = int($vm_view->{'summary.storage'}->committed / 1073741824);
    my $datastore = (split /\[/, (split /\]/, $vm_view->{'summary.config.vmPathName'})[0])[1];
    $datastore = dbGetDatastore($datastore, $vcenterID);
    my $consolidationNeeded = (defined($vm_view->runtime->consolidationNeeded) ? $vm_view->runtime->consolidationNeeded : 0);
    my $cpuHotAddEnabled = (defined($vm_view->{'config.cpuHotAddEnabled'}) ? $boolHash{$vm_view->{'config.cpuHotAddEnabled'}} : 0);
    my $memHotAddEnabled = (defined($vm_view->{'config.memoryHotAddEnabled'}) ? $boolHash{$vm_view->{'config.memoryHotAddEnabled'}} : 0);
    # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
    my $refVM = dbGetVM($moRef,$vcenterID);
    my $insertVM = 0;
    
    if ( ($refVM != 0)
      && ($refVM->{'host'} eq $host->{'id'})
      && ($refVM->{'memReservation'} eq $vm_view->resourceConfig->memoryAllocation->reservation)
      && ($refVM->{'guestFamily'} eq $vm_guestFamily)
      && ($refVM->{'ip'} eq join(',', @vm_ip_string))
      && ($refVM->{'cpuLimit'} eq $vm_view->resourceConfig->cpuAllocation->limit)
      && ($refVM->{'consolidationNeeded'} eq $consolidationNeeded)
      && ($refVM->{'fqdn'} eq $vm_guestHostName)
      && ($refVM->{'numcpu'} eq $numcpu)
      && ($refVM->{'cpuReservation'} eq $vm_view->resourceConfig->cpuAllocation->reservation)
      && ($refVM->{'sharedBus'} eq $sharedBus)
      && ($refVM->{'portgroup'} eq join(',', @vm_pg_string))
      && ($refVM->{'memory'} eq $memory)
      && ($refVM->{'phantomSnapshot'} eq $phantomSnapshot)
      && ($refVM->{'hwversion'} eq $vm_view->{'config.version'})
      && ($refVM->{'provisionned'} eq $provisionned)
      && ($refVM->{'mac'} eq join(',', @vm_mac))
      && ($refVM->{'multiwriter'} eq $multiwriter)
      && ($refVM->{'memHotAddEnabled'} eq $memHotAddEnabled)
      && ($refVM->{'guestOS'} eq $vm_guestfullname)
      && ($refVM->{'removable'} eq $removableExist)
      && ($refVM->{'datastore'} eq $datastore->{'id'})
      && ($refVM->{'vmtools'} eq $vm_toolsVersion)
      && ($refVM->{'name'} eq $vm_view->name)
      && ($refVM->{'memLimit'} eq $vm_view->resourceConfig->memoryAllocation->limit)
      && ($refVM->{'vmxpath'} eq $vm_view->{'summary.config.vmPathName'})
      && ($refVM->{'connectionState'} eq $vm_view->runtime->connectionState->val)
      && ($refVM->{'cpuHotAddEnabled'} eq $cpuHotAddEnabled)
      && ($refVM->{'powerState'} eq $vm_view->runtime->powerState->val)
      && ($refVM->{'guestId'} eq $vm_guestId)
      && ($refVM->{'configGuestId'} eq $vm_configGuestId)
      && ($refVM->{'vmpath'} eq $vmPath))
    {

      # VM already exists, have not changed, updated lastseen property
      $logger->info("[DEBUG][VM-INVENTORY] VM $moRef on host " . $host->{'id'} . " already exists and have not changed since last check, updating lastseen property") if $showDebug;
      my $sqlUpdate = $dbh->prepare("UPDATE vms set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $refVM->{'id'} . "'");
      $sqlUpdate->execute($start);
      $sqlUpdate->finish();
      
    }
    else
    {
      
      if ($refVM != 0)
      {

        # VM have changed, we must decom old one before create a new one
        compareAndLog($refVM->{'host'}, $host->{'id'});
        compareAndLog($refVM->{'memReservation'}, $vm_view->resourceConfig->memoryAllocation->reservation);
        compareAndLog($refVM->{'guestFamily'}, $vm_guestFamily);
        compareAndLog($refVM->{'ip'}, join(',', @vm_ip_string));
        compareAndLog($refVM->{'cpuLimit'}, $vm_view->resourceConfig->cpuAllocation->limit);
        compareAndLog($refVM->{'consolidationNeeded'}, $consolidationNeeded);
        compareAndLog($refVM->{'fqdn'}, $vm_guestHostName);
        compareAndLog($refVM->{'numcpu'}, $numcpu);
        compareAndLog($refVM->{'cpuReservation'}, $vm_view->resourceConfig->cpuAllocation->reservation);
        compareAndLog($refVM->{'sharedBus'}, $sharedBus);
        compareAndLog($refVM->{'portgroup'}, join(',', @vm_pg_string));
        compareAndLog($refVM->{'memory'}, $memory);
        compareAndLog($refVM->{'phantomSnapshot'}, $phantomSnapshot);
        compareAndLog($refVM->{'hwversion'}, $vm_view->{'config.version'});
        compareAndLog($refVM->{'provisionned'}, $provisionned);
        compareAndLog($refVM->{'mac'}, join(',', @vm_mac));
        compareAndLog($refVM->{'multiwriter'}, $multiwriter);
        compareAndLog($refVM->{'memHotAddEnabled'}, $memHotAddEnabled);
        compareAndLog($refVM->{'guestOS'}, $vm_guestfullname);
        compareAndLog($refVM->{'removable'}, $removableExist);
        compareAndLog($refVM->{'datastore'}, $datastore->{'id'});
        compareAndLog($refVM->{'vmtools'}, $vm_toolsVersion);
        compareAndLog($refVM->{'name'}, $vm_view->name);
        compareAndLog($refVM->{'memLimit'}, $vm_view->resourceConfig->memoryAllocation->limit);
        compareAndLog($refVM->{'vmxpath'}, $vm_view->{'summary.config.vmPathName'});
        compareAndLog($refVM->{'connectionState'}, $vm_view->runtime->connectionState->val);
        compareAndLog($refVM->{'cpuHotAddEnabled'}, $cpuHotAddEnabled);
        compareAndLog($refVM->{'powerState'}, $vm_view->runtime->powerState->val);
        compareAndLog($refVM->{'guestId'}, $vm_guestId);
        compareAndLog($refVM->{'configGuestId'}, $vm_configGuestId);
        compareAndLog($refVM->{'vmpath'}, $vmPath);
        $logger->info("[DEBUG][VM-INVENTORY] VM $moRef on host " . $host->{'id'} . " have changed since last check, sending old entry " . $refVM->{'id'} . " it into oblivion") if $showDebug;
        
      } # END if ($refVM != 0)
      
      $logger->info("[DEBUG][VM-INVENTORY] Adding data for VM $moRef on host $host->{'id'}") if $showDebug;
      my $sqlInsert = $dbh->prepare("INSERT INTO vms (vcenter, host, moref, memReservation, guestFamily, ip, cpuLimit, consolidationNeeded, fqdn, numcpu, cpuReservation, sharedBus, portgroup, memory, phantomSnapshot, hwversion, provisionned, mac, multiwriter, memHotAddEnabled, guestOS, removable, datastore, vmtools, name, memLimit, vmxpath, connectionState, cpuHotAddEnabled, powerState, guestId, configGuestId, vmpath, firstseen, lastseen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
      my @h_tmp = (
        $vcenterID,
        $host->{'id'},
        $moRef,
        $vm_view->resourceConfig->memoryAllocation->reservation,
        $vm_guestFamily,
        join(',', @vm_ip_string),
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
        $memHotAddEnabled,
        $vm_guestfullname,
        $removableExist,
        $datastore->{'id'},
        $vm_toolsVersion,
        $vm_view->name,
        $vm_view->resourceConfig->memoryAllocation->limit,
        $vm_view->{'summary.config.vmPathName'},
        $vm_view->runtime->connectionState->val,
        $cpuHotAddEnabled,
        $vm_view->runtime->powerState->val,
        $vm_guestId,
        $vm_configGuestId,
        $vmPath,
        $start,
        $start
      );
      $sqlInsert->execute(@h_tmp);
      $sqlInsert->finish();
      $insertVM = 1;

    } # END if ($refVM != 0) + check
    
    # One vm metadata have been handled, we must check metrics
    if ($insertVM) { $refVM = dbGetVM($moRef,$vcenterID); }
    my $vmMetrics = dbGetVMMetrics($refVM->{'id'});
    
    # Check for metrics existence and similarity
    if ( ($vmMetrics eq "0")
      || ($swappedMemory != $vmMetrics->{'swappedMemory'})
      || ($compressedMemory != $vmMetrics->{'compressedMemory'})
      || ($committed != $vmMetrics->{'commited'})
      || ($balloonedMemory != $vmMetrics->{'balloonedMemory'})
      || ($uncommitted != $vmMetrics->{'uncommited'}) )
    {
      
      my $sqlInsert = $dbh->prepare("INSERT INTO vmMetrics (vm_id, swappedMemory, compressedMemory, commited, balloonedMemory, uncommited, firstseen, lastseen) VALUES (?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
      $sqlInsert->execute(
        $refVM->{'id'},
        $swappedMemory,
        $compressedMemory,
        $committed,
        $balloonedMemory,
        $uncommitted,
        $start,
        $start
      );
      $sqlInsert->finish();
      
    }
    else
    {
      
      # VM metrics already exists, have not changed, updated lastseen property
      my $sqlUpdate = $dbh->prepare("UPDATE vmMetrics set lastseen = FROM_UNIXTIME (?) WHERE vm_id = '" . $refVM->{'id'} . "' ORDER BY id DESC LIMIT 1");
      $sqlUpdate->execute($start);
      $sqlUpdate->finish();
      
    } # END Check for metrics existence and similarity
    
    if ($vm_view->snapshot)
    {
      
        foreach (@{$vm_view->snapshot->rootSnapshotList})
        {
          
            snapshotInventory($_, $refVM->{'id'});
            
        } # END foreach (@{$vm_view->snapshot->rootSnapshotList})
        
    } # END if ($vm_view->snapshot)
    
  } # END foreach my $vm_view (@$view_VirtualMachine)
  
} # END sub vminventory

sub hostinventory
{
  
  foreach my $host_view (@$view_HostSystem)
  {

    my $service_ssh = 'n/a';
    my $service_shell = 'n/a';
    my $dnsservers = [];
    my $services = [];
    my $esxHostName = 'n/a';
    my $lunpathcount = 0;
    my $lundeadpathcount = 0;
    my $syslog_target = '';

    # We should call get_view subroutine only if host is connected to avoid error
    if ($host_view->{'runtime.connectionState'}->val eq "connected")
    {

      if (defined($host_view->{'configManager.serviceSystem'}))
      {
        
        my $serviceSys = Vim::get_view(mo_ref => $host_view->{'configManager.serviceSystem'}, properties => ['serviceInfo']);
        $services = $serviceSys->serviceInfo->service;
        
      } # END if (defined($host_view->{'configManager.serviceSystem'}))

      foreach(@$services)
      {
        
        if ($_->key eq 'TSM-SSH')
        {
          
          $service_ssh = $_->policy;
          
        }
        elsif($_->key eq 'TSM')
        {
          
          $service_shell = $_->policy;
          
        } # END if ($_->key eq 'TSM-SSH')
        
      } # END foreach(@$services)
    
      $dnsservers = $host_view->{'config.network.dnsConfig'}->address;
      $esxHostName = $host_view->{'config.network.dnsConfig'}->hostName;
      my $storageSys = Vim::get_view(mo_ref => $host_view->{'configManager.storageSystem'}, properties => ['storageDeviceInfo.multipathInfo']);
      my $luns = eval{$storageSys->{'storageDeviceInfo.multipathInfo'}->lun || []};
      
      foreach my $lun (@$luns)
      {
        
        $lunpathcount += (0+@{$lun->path});
        
        foreach my $path (@{$lun->path})
        {
          
          if ($path->{pathState} eq "dead") { $lundeadpathcount++; }
          
        } # END foreach my $path (@{$lun->path})
        
      } # END foreach my $lun (@$luns)
      
      my $advOpt = Vim::get_view(mo_ref => $host_view->{'configManager.advancedOption'}, properties => ['setting']);
      
      eval
      {
        
        $syslog_target = $advOpt->QueryOptions(name => 'Syslog.global.logHost');
        $syslog_target = @$syslog_target[0]->value;
        
      }; # END eval
      
    } # END if ($host_view->{'runtime.connectionState'}->val eq "connected")

    my @sorted_dnsservers = map { $_->[1] } sort { $a->[0] <=> $b->[0] } map {[ unpack('N',inet_aton($_)), $_ ]} @$dnsservers;
    my $ntpservers = $host_view->{'config.dateTimeInfo.ntpConfig.server'} || [];
    my $datastores = $host_view->{'datastore'} || [];
    my $datastorecount = 0+@{$host_view->{'datastore'}};
    my $memoryShared = QuickQueryPerf($host_view, 'mem', 'shared', 'average', '*');
    $memoryShared = (defined($memoryShared)) ? 0+$memoryShared : 0;
    my $cpuUsage = (defined $host_view->{'summary.quickStats'}->overallCpuUsage) ? $host_view->{'summary.quickStats'}->overallCpuUsage : 0;
    my $memoryUsage = (defined $host_view->{'summary.quickStats'}->overallMemoryUsage) ? $host_view->{'summary.quickStats'}->overallMemoryUsage : 0;
    my $vcentersdk = new URI::URL $host_view->{'vim'}->{'service_url'};
    my $moRef = $host_view->{'mo_ref'}->{'type'}."-".$host_view->{'mo_ref'}->{'value'};
    my $vcenterID = dbGetVC($vcentersdk->host);
    $logger->info("[DEBUG][HOST-INVENTORY] Retrieved vCenterID = $vcenterID for host $moRef") if $showDebug;
    my $cluster = (defined($h_hostcluster{$host_view->{'mo_ref'}->{'type'}."-".$host_view->{'mo_ref'}->{'value'}}) ? dbGetCluster($h_hostcluster{$host_view->{'mo_ref'}->{'type'}."-".$host_view->{'mo_ref'}->{'value'}}, $vcenterID) : 0);
    my $clusterID = ($cluster != 0) ? $cluster->{'id'} : 1;
    $logger->info("[DEBUG][HOST-INVENTORY] Retrieved clusterID = $clusterID for host $moRef") if $showDebug;
    # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
    my $refHost = dbGetHost($moRef,$vcenterID);
    my $insertHost = 0;
    
    if ( ($refHost != 0)
      && ($refHost->{'cluster'} eq $clusterID)
      && ($refHost->{'hostname'} eq $esxHostName)
      && ($refHost->{'host_name'} eq $host_view->name)
      && ($refHost->{'ntpservers'} eq join(';', sort @$ntpservers))
      && ($refHost->{'deadlunpathcount'} eq $lundeadpathcount)
      && ($refHost->{'numcpucore'} eq $host_view->{'summary.hardware.numCpuCores'})
      && ($refHost->{'syslog_target'} eq $syslog_target)
      && ($refHost->{'rebootrequired'} eq $boolHash{$host_view->{'summary.rebootRequired'}})
      && ($refHost->{'powerpolicy'} eq (defined($host_view->{'config.powerSystemInfo.currentPolicy.shortName'}) ? $host_view->{'config.powerSystemInfo.currentPolicy.shortName'} : 'off'))
      && ($refHost->{'bandwidthcapacity'} eq 0)
      && ($refHost->{'memory'} eq $host_view->{'summary.hardware.memorySize'})
      && ($refHost->{'dnsservers'} eq join(';', @sorted_dnsservers))
      && ($refHost->{'cputype'} eq $host_view->{'summary.hardware.cpuModel'})
      && ($refHost->{'numcpu'} eq $host_view->{'summary.hardware.numCpuPkgs'})
      && ($refHost->{'inmaintenancemode'} eq $boolHash{$host_view->{'runtime.inMaintenanceMode'}})
      && ($refHost->{'lunpathcount'} eq $lunpathcount)
      && ($refHost->{'datastorecount'} eq $datastorecount)
      && ($refHost->{'model'} eq $host_view->{'summary.hardware.model'})
      && ($refHost->{'cpumhz'} eq $host_view->{'summary.hardware.cpuMhz'})
      && ($refHost->{'esxbuild'} eq $host_view->{'summary.config.product.fullName'})
      && ($refHost->{'ssh_policy'} eq $service_ssh)
      && ($refHost->{'shell_policy'} eq $service_shell))
    {

      # Host already exists, have not changed, updated lastseen property
      $logger->info("[DEBUG][HOST-INVENTORY] Host $moRef already exists and have not changed since last check, updating lastseen property") if $showDebug;
      my $sqlUpdate = $dbh->prepare("UPDATE hosts set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $refHost->{'id'} . "'");
      $sqlUpdate->execute($start);
      $sqlUpdate->finish();
      
    }
    else
    {
      
      if ($refHost != 0)
      {
        
        # Host have changed, we must decom old one before create a new one
        compareAndLog($refHost->{'cluster'}, $clusterID);
        compareAndLog($refHost->{'hostname'}, $esxHostName);
        compareAndLog($refHost->{'host_name'}, $host_view->name);
        compareAndLog($refHost->{'ntpservers'}, join(';', sort @$ntpservers));
        compareAndLog($refHost->{'deadlunpathcount'}, $lundeadpathcount);
        compareAndLog($refHost->{'numcpucore'}, $host_view->{'summary.hardware.numCpuCores'});
        compareAndLog($refHost->{'syslog_target'}, $syslog_target);
        compareAndLog($refHost->{'rebootrequired'}, $boolHash{$host_view->{'summary.rebootRequired'}});
        compareAndLog($refHost->{'powerpolicy'}, (defined($host_view->{'config.powerSystemInfo.currentPolicy.shortName'}) ? $host_view->{'config.powerSystemInfo.currentPolicy.shortName'} : 'off'));
        compareAndLog($refHost->{'bandwidthcapacity'}, 0);
        compareAndLog($refHost->{'memory'}, $host_view->{'summary.hardware.memorySize'});
        compareAndLog($refHost->{'dnsservers'}, join(';', @sorted_dnsservers));
        compareAndLog($refHost->{'cputype'}, $host_view->{'summary.hardware.cpuModel'});
        compareAndLog($refHost->{'numcpu'}, $host_view->{'summary.hardware.numCpuPkgs'});
        compareAndLog($refHost->{'inmaintenancemode'}, $boolHash{$host_view->{'runtime.inMaintenanceMode'}});
        compareAndLog($refHost->{'lunpathcount'}, $lunpathcount);
        compareAndLog($refHost->{'datastorecount'}, $datastorecount);
        compareAndLog($refHost->{'model'}, $host_view->{'summary.hardware.model'});
        compareAndLog($refHost->{'cpumhz'}, $host_view->{'summary.hardware.cpuMhz'});
        compareAndLog($refHost->{'esxbuild'}, $host_view->{'summary.config.product.fullName'});
        compareAndLog($refHost->{'ssh_policy'}, $service_ssh);
        compareAndLog($refHost->{'shell_policy'}, $service_shell);
        $logger->info("[DEBUG][HOST-INVENTORY] Host $moRef have changed since last check, sending old entry it into oblivion") if $showDebug;
        
      } # END if ($refHost != 0)
      
      $logger->info("[DEBUG][HOST-INVENTORY] Adding data for host $moRef") if $showDebug;
      my $sqlInsert = $dbh->prepare("INSERT INTO hosts (vcenter, cluster, moref, hostname, host_name, ntpservers, deadlunpathcount, numcpucore, syslog_target, rebootrequired, powerpolicy, bandwidthcapacity, memory, dnsservers, cputype, numcpu, inmaintenancemode, lunpathcount, datastorecount, model, cpumhz, esxbuild, ssh_policy, shell_policy, firstseen, lastseen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
      $sqlInsert->execute(
        $vcenterID,
        $clusterID,
        $moRef,
        $esxHostName,
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
        $datastorecount,
        $host_view->{'summary.hardware.model'},
        $host_view->{'summary.hardware.cpuMhz'},
        $host_view->{'summary.config.product.fullName'},
        $service_ssh,
        $service_shell,
        $start,
        $start
      );
      $sqlInsert->finish();
      $insertHost = 1;
      
      if ($refHost != 0)
      {

        # We must update vms if needed
        my $newHost = dbGetHost($moRef,$vcenterID);
        my $sqlUpdate = $dbh->prepare("UPDATE vms SET host =  '" . $newHost->{'id'} . "' WHERE host = '" . $refHost->{'id'} . "'");
        $sqlUpdate->execute();
        $sqlUpdate->finish();
        
      } # END if ($refHost != 0)
      
    } # END if ($refHost != 0) + check
    
    # One host metadata have been handled, we must check metrics
    if ($insertHost) { $refHost = dbGetHost($moRef,$vcenterID); }
    my $hostMetrics = dbGetHostMetrics($refHost->{'id'});

    # Check for metrics existence and similarity
    if ( ($hostMetrics eq "0")
      || ($memoryShared != $hostMetrics->{'sharedmemory'})
      || ($cpuUsage != $hostMetrics->{'cpuUsage'})
      || ($memoryUsage != $hostMetrics->{'memoryUsage'}) )
    {
      
      my $sqlInsert = $dbh->prepare("INSERT INTO hostMetrics (host_id, sharedmemory, cpuUsage, memoryUsage, firstseen, lastseen) VALUES (?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
      $sqlInsert->execute(
        $refHost->{'id'},
        $memoryShared,
        $cpuUsage,
        $memoryUsage,
        $start,
        $start
      );
      $sqlInsert->finish();
      
    }
    else
    {
      
      # Host metrics already exists, have not changed, updated lastseen property
      my $sqlUpdate = $dbh->prepare("UPDATE hostMetrics set lastseen = FROM_UNIXTIME (?) WHERE host_id = '" . $refHost->{'id'} . "' ORDER BY id DESC LIMIT 1");
      $sqlUpdate->execute($start);
      $sqlUpdate->finish();
      
    } # END Check for metrics existence and similarity

    
    foreach my $datastore (@$datastores)
    {
      
      my $datastoreMoRef = $datastore->{'type'}."-".$datastore->{'value'};
      my $datastoreID = dbGetDatastoreID($datastoreMoRef, $vcenterID);
      my $datastoreMapping = dbGetDatastoreMapping($datastoreID,$refHost->{'id'});
      
      if ($datastoreMapping eq "0")
      {
        
        my $sqlInsert = $dbh->prepare("INSERT INTO datastoreMappings (datastore_id, host_id, firstseen, lastseen) VALUES (?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
        $sqlInsert->execute(
          $datastoreID,
          $refHost->{'id'},
          $start,
          $start
        );
        $sqlInsert->finish();
        
      }
      else
      {
        
        # Host metrics already exists, have not changed, updated lastseen property
        my $sqlUpdate = $dbh->prepare("UPDATE datastoreMappings set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $datastoreMapping->{'id'} . "'");
        $sqlUpdate->execute($start);
        $sqlUpdate->finish();
        
      } # END if ($datastoreMapping eq "0")
      
    } # END foreach my $datastore @($host_view->{'datastore'})
    
  } # END foreach my $host_view (@$view_HostSystem)
  
} # END sub hostinventory

sub clusterinventory
{
  
  foreach my $cluster_view (@$view_ClusterComputeResource)
  {
    
    my $lastconfigissue = 0;
    my $lastconfigissuetime = "0000-00-00 00:00:00";
    if (defined($cluster_view->configIssue))
    {
      
      foreach my $issue ( sort {$b->key cmp $a->key} @{$cluster_view->configIssue} )
      {
        
        $lastconfigissue = $issue->fullFormattedMessage;
        $lastconfigissuetime = substr($issue->createdTime, 0, 19);
        $lastconfigissuetime =~ s/T/ /g;
        last;
        
      } # END foreach my $issue ( sort {$b->key cmp $a->key} @{$cluster_view->configIssue})
      
    } # END if (defined($cluster_view->configIssue))
    
    my $isAdmissionEnable = 0;
    my $admissionModel = 0;
    my $admissionThreshold = 0;
    my $admissionValue = 0;
    
    if ($cluster_view->{'configuration.dasConfig.admissionControlEnabled'} eq 'true')
    {
      
      $isAdmissionEnable = 1;
      my $admissionControlPolicy = $cluster_view->{'configuration.dasConfig.admissionControlPolicy'};
      
      if ($admissionControlPolicy->isa('ClusterFailoverHostAdmissionControlPolicy'))
      {
      
        $admissionModel = 'ClusterFailoverHostAdmissionControlPolicy';
        $admissionThreshold = scalar @{$admissionControlPolicy->failoverHosts};
        $admissionValue = $cluster_view->summary->currentFailoverLevel;
        
      }
      elsif ($admissionControlPolicy->isa('ClusterFailoverLevelAdmissionControlPolicy'))
      {
        
        $admissionModel = 'ClusterFailoverLevelAdmissionControlPolicy';
        $admissionThreshold = $admissionControlPolicy->failoverLevel;
        $admissionValue = $cluster_view->summary->currentFailoverLevel;
        
      }
      elsif ($admissionControlPolicy->isa('ClusterFailoverResourcesAdmissionControlPolicy'))
      {
        
        $admissionModel = 'ClusterFailoverResourcesAdmissionControlPolicy';
        $admissionThreshold = "CPU:".$admissionControlPolicy->cpuFailoverResourcesPercent."% | MEM:".$admissionControlPolicy->memoryFailoverResourcesPercent."%";;
        $admissionValue = "CPU:".$cluster_view->summary->admissionControlInfo->currentCpuFailoverResourcesPercent."% | MEM:".$cluster_view->summary->admissionControlInfo->currentMemoryFailoverResourcesPercent."%";
        
      } # END if ($admissionControlPolicy->isa('ClusterFailoverHostAdmissionControlPolicy'))
      
    } # END if ($cluster_view->{'configuration.dasConfig.admissionControlEnabled'} eq 'true')
    
    my $dasenabled = (defined($cluster_view->summary->dasData) ? 1 : 0);
    my $vcentersdk = new URI::URL $cluster_view->{'vim'}->{'service_url'};
    my $vcenterID = dbGetVC($vcentersdk->host);
    my $moRef = $cluster_view->{'mo_ref'}->{'type'}."-".$cluster_view->{'mo_ref'}->{'value'};
    # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
    my $refCluster = dbGetCluster($moRef,$vcenterID);
    my $insertCluster = 0;
    
    if ( ($refCluster != 0)
      && ($refCluster->{'cluster_name'} eq $cluster_view->name)
      && ($refCluster->{'dasenabled'} eq $dasenabled)
      && ($refCluster->{'lastconfigissuetime'} eq $lastconfigissuetime)
      && ($refCluster->{'isAdmissionEnable'} eq $isAdmissionEnable)
      && ($refCluster->{'admissionModel'} eq $admissionModel)
      && ($refCluster->{'admissionThreshold'} eq $admissionThreshold)
      && ($refCluster->{'admissionValue'} eq $admissionValue)
      && ($refCluster->{'lastconfigissue'} eq $lastconfigissue))
    {
      
      # Cluster already exists, have not changed, updated lastseen property
      $logger->info("[DEBUG][CLUSTER-INVENTORY] Cluster $moRef already exists and have not changed since last check, updating lastseen property") if $showDebug;
      my $sqlUpdate = $dbh->prepare("UPDATE clusters set lastseen = FROM_UNIXTIME ($start) WHERE id = '" . $refCluster->{'id'} . "'");
      $sqlUpdate->execute();
      $sqlUpdate->finish();
      
    }
    else
    {
      
      if ($refCluster != 0)
      {

        # Cluster have changed, we must decom old one before create a new one
        compareAndLog($refCluster->{'cluster_name'}, $cluster_view->name);
        compareAndLog($refCluster->{'dasenabled'}, $dasenabled);
        compareAndLog($refCluster->{'lastconfigissuetime'}, $lastconfigissuetime);
        compareAndLog($refCluster->{'isAdmissionEnable'}, $isAdmissionEnable);
        compareAndLog($refCluster->{'admissionModel'}, $admissionModel);
        compareAndLog($refCluster->{'admissionThreshold'}, $admissionThreshold);
        compareAndLog($refCluster->{'admissionValue'}, $admissionValue);
        compareAndLog($refCluster->{'lastconfigissue'}, $lastconfigissue);
        $logger->info("[DEBUG][CLUSTER-INVENTORY] Cluster $moRef have changed since last check, sending old entry it into oblivion") if $showDebug;
        
      } # END if ($refCluster != 0)
      
      $logger->info("[DEBUG][CLUSTER-INVENTORY] Adding data for cluster $moRef") if $showDebug;
      my $sqlInsert = $dbh->prepare("INSERT INTO clusters (vcenter, moref, cluster_name, dasenabled, isAdmissionEnable, admissionModel, admissionThreshold, admissionValue, lastconfigissuetime, lastconfigissue, firstseen, lastseen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
      $sqlInsert->execute(
        $vcenterID,
        $moRef,
        $cluster_view->name,
        $dasenabled,
        $isAdmissionEnable,
        $admissionModel,
        $admissionThreshold,
        $admissionValue,
        $lastconfigissuetime,
        $lastconfigissue,
        $start,
        $start
      );
      $sqlInsert->finish();
      $insertCluster = 1;
      
      if ($refCluster != 0)
      {

        # We must update host if needed
        my $newCluster = dbGetCluster($moRef,$vcenterID);
        my $sqlUpdate = $dbh->prepare("UPDATE hosts SET cluster =  '" . $newCluster->{'id'} . "' WHERE cluster = '" . $refCluster->{'id'} . "'");
        $sqlUpdate->execute();
        $sqlUpdate->finish();
        
      } # END if ($refCluster != 0)
      
    } # END if ($refCluster != 0) + check
    
    # One cluster metadata have been handled, we must check metrics
    if ($insertCluster) { $refCluster = dbGetCluster($moRef,$vcenterID); }
    my $clusterMetrics = dbGetClusterMetrics($refCluster->{'id'});
    
    # Check for metrics existence and similarity
    if ( ($clusterMetrics eq "0")
      || ($cluster_view->summary->numVmotions != $clusterMetrics->{'vmotion'}) )
    {
      
      my $sqlInsert = $dbh->prepare("INSERT INTO clusterMetrics (cluster_id, vmotion, firstseen, lastseen) VALUES (?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
      $sqlInsert->execute(
        $refCluster->{'id'},
        $cluster_view->summary->numVmotions,
        $start,
        $start
      );
      $sqlInsert->finish();
      
    }
    else
    {
      
      # Cluster metrics already exists, have not changed, updated lastseen property
      my $sqlUpdate = $dbh->prepare("UPDATE clusterMetrics set lastseen = FROM_UNIXTIME (?) WHERE cluster_id = '" . $refCluster->{'id'} . "' ORDER BY id DESC LIMIT 1");
      $sqlUpdate->execute($start);
      $sqlUpdate->finish();
      
    } # END Check for metrics existence and similarity
    
  } # END foreach my $cluster_view (@$view_ClusterComputeResource)
  
} # END sub clusterinventory

sub datastoreinventory
{
  
  foreach my $datastore_view (@$view_Datastore)
  {
    
    my $vcentersdk = new URI::URL $datastore_view->{'vim'}->{'service_url'};
    my $moRef = $datastore_view->{'mo_ref'}->{'type'}."-".$datastore_view->{'mo_ref'}->{'value'};
    my $size = int($datastore_view->summary->capacity + 0.5);
    my $freespace = int($datastore_view->summary->freeSpace + 0.5);
    my $uncommitted = (defined($datastore_view->summary->uncommitted) ? int($datastore_view->summary->uncommitted + 0.5) : 0);
    my $maintenanceMode = (defined($datastore_view->summary->maintenanceMode) ? $datastore_view->summary->maintenanceMode : 'normal');
    my $vcenterID = dbGetVC($vcentersdk->host);
    my $refDatastore = dbGetDatastore($datastore_view->name,$vcenterID);
    my $insertDatastore = 0;

    if ( ($refDatastore != 0)
      && ($refDatastore->{'datastore_name'} eq $datastore_view->name)
      && ($refDatastore->{'type'} eq $datastore_view->summary->type)
      && ($refDatastore->{'maintenanceMode'} eq $maintenanceMode)
      && ($refDatastore->{'isAccessible'} eq $datastore_view->summary->accessible)
      && ($refDatastore->{'shared'} eq $datastore_view->summary->multipleHostAccess)
      && ($refDatastore->{'iormConfiguration'} eq $datastore_view->iormConfiguration->enabled) )
    {
      
      # Datastore already exists, have not changed, updated lastseen property
      $logger->info("[DEBUG][DATASTORE-INVENTORY] Datastore " . $refDatastore->{'id'} . " already exists and have not changed since last check, updating lastseen property") if $showDebug;
      my $sqlUpdate = $dbh->prepare("UPDATE datastores SET lastseen = FROM_UNIXTIME (?) WHERE id = '" . $refDatastore->{'id'} . "'");
      $sqlUpdate->execute($start);
      $sqlUpdate->finish();
      
    }
    else
    {
      
      if ($refDatastore != 0)
      {
        
        # Datastore have changed, we must decom old one before create a new one
        compareAndLog($refDatastore->{'datastore_name'}, $datastore_view->name);
        compareAndLog($refDatastore->{'type'}, $datastore_view->summary->type);
        compareAndLog($refDatastore->{'maintenanceMode'}, $maintenanceMode);
        compareAndLog($refDatastore->{'isAccessible'}, $datastore_view->summary->accessible);
        compareAndLog($refDatastore->{'shared'}, $datastore_view->summary->multipleHostAccess);
        compareAndLog($refDatastore->{'iormConfiguration'}, $datastore_view->iormConfiguration->enabled);
        $logger->info("[DEBUG][DATASTORE-INVENTORY] Datastore " . $refDatastore->{'id'} . " have changed since last check, sending old entry it into oblivion") if $showDebug;
        # my $sqlUpdate = $dbh->prepare("UPDATE datastores set active = 0 WHERE id = '" . $refDatastore->{'id'} . "'");
        # $sqlUpdate->execute();
        # $sqlUpdate->finish();
        
      } # END if ($refDatastore != 0)
      
      $logger->info("[DEBUG][DATASTORE-INVENTORY] Adding data for datastore $moRef") if $showDebug;
      my $sqlInsert = $dbh->prepare("INSERT INTO datastores (vcenter, moref, datastore_name, type, isAccessible, maintenanceMode, shared, iormConfiguration, firstseen, lastseen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
      $sqlInsert->execute(
        $vcenterID,
        $datastore_view->{'mo_ref'}->{'type'}."-".$datastore_view->{'mo_ref'}->{'value'},
        $datastore_view->name,
        $datastore_view->summary->type,
        $datastore_view->summary->accessible,
        $maintenanceMode,
        $datastore_view->summary->multipleHostAccess,
        $datastore_view->iormConfiguration->enabled,
        $start,
        $start
      );
      $sqlInsert->finish();
      $insertDatastore = 1;
      
      if ($refDatastore != 0)
      {
        
        # We must update vms if needed
        my $newDatastore = dbGetDatastore($datastore_view->name,$vcenterID);
        my $sqlUpdate = $dbh->prepare("UPDATE vms SET datastore =  '" . $newDatastore->{'id'} . "' WHERE datastore = '" . $refDatastore->{'id'} . "'");
        $sqlUpdate->execute();
        $sqlUpdate->finish();
        
      } # END if ($refDatastore != 0)
      
    } # END if ($refDatastore != 0) + check
    
    # One datastore metadata have been handled, we must check metrics
    if ($insertDatastore) { $refDatastore = dbGetDatastore($datastore_view->name,$vcenterID); }
    my $datastoreMetrics = dbGetDatastoreMetrics($refDatastore->{'id'});
    
    # Check for metrics existence and similarity
    if ( ($datastoreMetrics eq "0")
      || ($size != $datastoreMetrics->{'size'})
      || ($freespace != $datastoreMetrics->{'freespace'})
      || ($uncommitted != $datastoreMetrics->{'uncommitted'}) )
    {
      
      my $sqlInsert = $dbh->prepare("INSERT INTO datastoreMetrics (datastore_id, size, freeSpace, uncommitted, firstseen, lastseen) VALUES (?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
      $sqlInsert->execute(
        $refDatastore->{'id'},
        $size,
        $freespace,
        $uncommitted,
        $start,
        $start
      );
      $sqlInsert->finish();
      
    }
    else
    {
      
      # Datastore metrics already exists, have not changed, updated lastseen property
      my $sqlUpdate = $dbh->prepare("UPDATE datastoreMetrics set lastseen = FROM_UNIXTIME (?) WHERE datastore_id = '" . $refDatastore->{'id'} . "' ORDER BY id DESC LIMIT 1");
      $sqlUpdate->execute($start);
      $sqlUpdate->finish();
      
    } # END Check for metrics existence and similarity
    
  } # END foreach my $datastore_view (@$view_Datastore)
  
} # END sub datastoreinventory

sub VSANHealthCheck
{
  
  my $service_content = Vim::get_service_content();
  my $apiType = $service_content->about->apiType;
  my $fullApiVersion = $service_content->about->apiVersion;
  my $majorApiVersion = (split /\./, $fullApiVersion)[0];
  # TODO: check if min6.2
  my %vc_mos = get_vsan_vc_mos();
  my $VsanVcClusterHealthSystem = $vc_mos{"vsan-cluster-health-system"};
  foreach my $cluster_view (@$view_ClusterComputeResource)
  {
    
		if (defined($cluster_view->configurationEx->vsanConfigInfo) && $cluster_view->configurationEx->vsanConfigInfo->enabled)
    {
      
      my $moRef = $cluster_view->{'mo_ref'}->{'type'}."-".$cluster_view->{'mo_ref'}->{'value'};
      my $vcentersdk = new URI::URL $cluster_view->{'vim'}->{'service_url'};
      my $vcenterID = dbGetVC($vcentersdk->host);
      my $refCluster = dbGetCluster($moRef,$vcenterID);
      my $refClusterVSAN = dbGetClusterVSAN($refCluster->{'id'});
      my $VsanQueryVcClusterHealthSummary = eval { $VsanVcClusterHealthSystem->VsanQueryVcClusterHealthSummary(cluster => $cluster_view) } || 0;
      next if ($VsanQueryVcClusterHealthSummary == 0);
      my $VSANgroups = $VsanQueryVcClusterHealthSummary->groups;
      
      my $hcldbuptodate = "unknown";
      my $autohclupdate = "unknown";
      my $controlleronhcl = "unknown";
      my $controllerreleasesupport = "unknown";
      my $controllerdriver = "unknown";
      my $clusterpartition = "unknown";
      my $vmknicconfigured = "unknown";
      my $matchingsubnets = "unknown";
      my $matchingmulticast = "unknown";
      my $physdiskoverall = "unknown";
      my $physdiskmetadata = "unknown";
      my $physdisksoftware = "unknown";
      my $physdiskcongestion = "unknown";
      my $healthversion = "unknown";
      my $advcfgsync = "unknown";
      my $clomdliveness = "unknown";
      my $diskbalance = "unknown";
      my $upgradesoftware = "unknown";
      my $upgradelowerhosts = "unknown";

      foreach my $VSANgroup (@$VSANgroups)
      {
        
        my $VSANgroupTests = $VSANgroup->{'groupTests'};
        
        switch ($VSANgroup->{'groupId'})
        {
          
          case "com.vmware.vsan.health.test.hcl"
          {
        
            foreach my $VSANgroupTest (@$VSANgroupTests)
            {
              
              switch ($VSANgroupTest->{'testId'})
              {
                
                case "com.vmware.vsan.health.test.hcldbuptodate"
                {
                  
                  $hcldbuptodate = $VSANgroupTest->{'testHealth'};
                  
                } # END case "com.vmware.vsan.health.test.hcldbuptodate"
                
                case "com.vmware.vsan.health.test.autohclupdate"
                {
                  
                  $autohclupdate = $VSANgroupTest->{'testHealth'};
                  
                } # END case "com.vmware.vsan.health.test.autohclupdate"
                
                case "com.vmware.vsan.health.test.controlleronhcl"
                {
                  
                  $controlleronhcl = $VSANgroupTest->{'testHealth'};
                  
                } # END case "com.vmware.vsan.health.test.controlleronhcl"
                
                case "com.vmware.vsan.health.test.controllerreleasesupport"
                {
                  
                  $controllerreleasesupport = $VSANgroupTest->{'testHealth'};
                  
                } # END case "com.vmware.vsan.health.test.controllerreleasesupport"
                
                case "com.vmware.vsan.health.test.controllerdriver"
                {
                  
                  $controllerdriver = $VSANgroupTest->{'testHealth'};
                  
                } # END case "com.vmware.vsan.health.test.controllerdriver"
                
              } # END switch ($VSANgroupTest->{'testId'})
              
            } # END foreach my $VSANgroupTest (@$VSANgroupTests)
            
          } # END case "com.vmware.vsan.health.test.hcl"
                    
          case "com.vmware.vsan.health.test.network"
          {
        
            foreach my $VSANgroupTest (@$VSANgroupTests)
            {
              
              switch ($VSANgroupTest->{'testId'})
              {
                
                case "com.vmware.vsan.health.test.clusterpartition"
                {
                  
                  $clusterpartition = $VSANgroupTest->{'testHealth'};
                  
                } # END case "com.vmware.vsan.health.test.clusterpartition"
                  
                case "com.vmware.vsan.health.test.vsanvmknic"
                {
                  
                  $vmknicconfigured = $VSANgroupTest->{'testHealth'};
                  
                } # END case "com.vmware.vsan.health.test.vsanvmknic"
                  
                case "com.vmware.vsan.health.test.matchingsubnet"
                {
                  
                  $matchingsubnets = $VSANgroupTest->{'testHealth'};
                  
                } # END case "com.vmware.vsan.health.test.clusterpartition"
                  
                case "com.vmware.vsan.health.test.multicastsettings"
                {
                  
                  $matchingmulticast = $VSANgroupTest->{'testHealth'};
                  
                } # END case "com.vmware.vsan.health.test.clusterpartition"
                
              } # END switch ($VSANgroupTest->{'testId'})
              
            } # END foreach my $VSANgroupTest (@$VSANgroupTests)
            
          } # END case "com.vmware.vsan.health.test.network"
                    
          case "com.vmware.vsan.health.test.physicaldisks"
          {
        
            foreach my $VSANgroupTest (@$VSANgroupTests)
            {
              
              switch ($VSANgroupTest->{'testId'})
              {
                
                case "com.vmware.vsan.health.test.physdiskoverall"
                {
                  
                  $physdiskoverall = $VSANgroupTest->{'testHealth'};
                  
                } # END case "com.vmware.vsan.health.test.physdiskoverall"
                  
                case "com.vmware.vsan.health.test.physdiskmetadata"
                {
                  
                  $physdiskmetadata = $VSANgroupTest->{'testHealth'};
                  
                } # END case "com.vmware.vsan.health.test.physdiskmetadata"
                  
                case "com.vmware.vsan.health.test.physdisksoftware"
                {
                  
                  $physdisksoftware = $VSANgroupTest->{'testHealth'};
                  
                } # END case "com.vmware.vsan.health.test.physdisksoftware"
                  
                case "com.vmware.vsan.health.test.physdiskcongestion"
                {
                  
                  $physdiskcongestion = $VSANgroupTest->{'testHealth'};
                  
                } # END case "com.vmware.vsan.health.test.physdiskcongestion"
                
              } # END switch ($VSANgroupTest->{'testId'})
              
            } # END foreach my $VSANgroupTest (@$VSANgroupTests)
            
          } # END case "com.vmware.vsan.health.test.physicaldisks"
                    
          case "com.vmware.vsan.health.test.cluster"
          {
        
            foreach my $VSANgroupTest (@$VSANgroupTests)
            {
              
              switch ($VSANgroupTest->{'testId'})
              {
                
                case "com.vmware.vsan.health.test.healthversion"
                {
                  
                  $healthversion = $VSANgroupTest->{'testHealth'};
                  
                } # END case "com.vmware.vsan.health.test.healthversion"
                  
                case "com.vmware.vsan.health.test.advcfgsync"
                {
                  
                  $advcfgsync = $VSANgroupTest->{'testHealth'};
                  
                } # END case "com.vmware.vsan.health.test.advcfgsync"
                  
                case "com.vmware.vsan.health.test.clomdliveness"
                {
                  
                  $clomdliveness = $VSANgroupTest->{'testHealth'};
                  
                } # END case "com.vmware.vsan.health.test.clomdliveness"
                  
                case "com.vmware.vsan.health.test.diskbalance"
                {
                  
                  $diskbalance = $VSANgroupTest->{'testHealth'};
                  
                } # END case "com.vmware.vsan.health.test.diskbalance"
                  
                case "com.vmware.vsan.health.test.upgradesoftware"
                {
                  
                  $upgradesoftware = $VSANgroupTest->{'testHealth'};
                  
                } # END case "com.vmware.vsan.health.test.upgradesoftware"
                  
                case "com.vmware.vsan.health.test.upgradelowerhosts"
                {
                  
                  $upgradelowerhosts = $VSANgroupTest->{'testHealth'};
                  
                } # END case "com.vmware.vsan.health.test.upgradelowerhosts"
                
              } # END switch ($VSANgroupTest->{'testId'})
              
            } # END foreach my $VSANgroupTest (@$VSANgroupTests)
            
          } # END case "com.vmware.vsan.health.test.cluster"
          
        } # END switch ($VSANgroup->{'groupId'})
        
      } # END foreach my $VSANgroup (@$VSANgroups) 
 
      if ( ($refClusterVSAN != 0)
        && ($refClusterVSAN->{'hcldbuptodate'} eq $hcldbuptodate)
        && ($refClusterVSAN->{'autohclupdate'} eq $autohclupdate)
        && ($refClusterVSAN->{'controlleronhcl'} eq $controlleronhcl)
        && ($refClusterVSAN->{'controllerreleasesupport'} eq $controllerreleasesupport)
        && ($refClusterVSAN->{'controllerdriver'} eq $controllerdriver)
        && ($refClusterVSAN->{'clusterpartition'} eq $clusterpartition)
        && ($refClusterVSAN->{'vmknicconfigured'} eq $vmknicconfigured)
        && ($refClusterVSAN->{'matchingsubnets'} eq $matchingsubnets)
        && ($refClusterVSAN->{'matchingmulticast'} eq $matchingmulticast)
        && ($refClusterVSAN->{'physdiskoverall'} eq $physdiskoverall)
        && ($refClusterVSAN->{'physdiskmetadata'} eq $physdiskmetadata)
        && ($refClusterVSAN->{'physdisksoftware'} eq $physdisksoftware)
        && ($refClusterVSAN->{'physdiskcongestion'} eq $physdiskcongestion)
        && ($refClusterVSAN->{'healthversion'} eq $healthversion)
        && ($refClusterVSAN->{'advcfgsync'} eq $advcfgsync)
        && ($refClusterVSAN->{'clomdliveness'} eq $clomdliveness)
        && ($refClusterVSAN->{'diskbalance'} eq $diskbalance)
        && ($refClusterVSAN->{'upgradesoftware'} eq $upgradesoftware)
        && ($refClusterVSAN->{'upgradelowerhosts'} eq $upgradelowerhosts))
      {
        
        # Cluster VSAN already exists, have not changed, updated lastseen property
        $logger->info("[DEBUG][CLUSTERVSAN-INVENTORY] Cluster VSAN $moRef already exists and have not changed since last check, updating lastseen property") if $showDebug;
        my $sqlUpdate = $dbh->prepare("UPDATE clustersVSAN set lastseen = FROM_UNIXTIME ($start) WHERE id = '" . $refClusterVSAN->{'id'} . "'");
        $sqlUpdate->execute();
        $sqlUpdate->finish();
        
      }
      else
      {
        
        if ($refClusterVSAN != 0)
        {

          # Cluster have changed, we must decom old one before create a new one
          compareAndLog($refClusterVSAN->{'hcldbuptodate'}, $hcldbuptodate);
          compareAndLog($refClusterVSAN->{'autohclupdate'}, $autohclupdate);
          compareAndLog($refClusterVSAN->{'controlleronhcl'}, $controlleronhcl);
          compareAndLog($refClusterVSAN->{'controllerreleasesupport'}, $controllerreleasesupport);
          compareAndLog($refClusterVSAN->{'controllerdriver'}, $controllerdriver);
          compareAndLog($refClusterVSAN->{'clusterpartition'}, $clusterpartition);
          compareAndLog($refClusterVSAN->{'vmknicconfigured'}, $vmknicconfigured);
          compareAndLog($refClusterVSAN->{'matchingsubnets'}, $matchingsubnets);
          compareAndLog($refClusterVSAN->{'matchingmulticast'}, $matchingmulticast);
          compareAndLog($refClusterVSAN->{'physdiskoverall'}, $physdiskoverall);
          compareAndLog($refClusterVSAN->{'physdiskmetadata'}, $physdiskmetadata);
          compareAndLog($refClusterVSAN->{'physdisksoftware'}, $physdisksoftware);
          compareAndLog($refClusterVSAN->{'physdiskcongestion'}, $physdiskcongestion);
          compareAndLog($refClusterVSAN->{'healthversion'}, $healthversion);
          compareAndLog($refClusterVSAN->{'advcfgsync'}, $advcfgsync);
          compareAndLog($refClusterVSAN->{'clomdliveness'}, $clomdliveness);
          compareAndLog($refClusterVSAN->{'diskbalance'}, $diskbalance);
          compareAndLog($refClusterVSAN->{'upgradesoftware'}, $upgradesoftware);
          compareAndLog($refClusterVSAN->{'upgradelowerhosts'}, $upgradelowerhosts);
          
        } # END if ($refClusterVSAN != 0)
        
        $logger->info("[DEBUG][CLUSTERVSAN-INVENTORY] Adding data for cluster VSAN $moRef") if $showDebug;
        my $sqlInsert = $dbh->prepare("INSERT INTO clustersVSAN (cluster_id, autohclupdate, hcldbuptodate, controlleronhcl, controllerreleasesupport, controllerdriver, clusterpartition, vmknicconfigured, matchingsubnets, matchingmulticast, physdiskoverall, physdiskmetadata, physdisksoftware, physdiskcongestion, healthversion, advcfgsync, clomdliveness, diskbalance, upgradesoftware, upgradelowerhosts, firstseen, lastseen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
        $sqlInsert->execute(
          $refCluster->{'id'},
          $autohclupdate,
          $hcldbuptodate,
          $controlleronhcl,
          $controllerreleasesupport,
          $controllerdriver,
          $clusterpartition,
          $vmknicconfigured,
          $matchingsubnets,
          $matchingmulticast,
          $physdiskoverall,
          $physdiskmetadata,
          $physdisksoftware,
          $physdiskcongestion,
          $healthversion,
          $advcfgsync,
          $clomdliveness,
          $diskbalance,
          $upgradesoftware,
          $upgradelowerhosts,
          $start,
          $start
        );
        $sqlInsert->finish();
        
      } # END if ($refCluster != 0) + check
      
    } # END if (defined($cluster_view->configurationEx->vsanConfigInfo))
    
  } # END foreach my $cluster_view (@$view_ClusterComputeResource)
    
} # END sub VSANHealthCheck

sub getHardwareStatus
{
  
  foreach my $host_view (@$view_HostSystem)
  {

    next if (!defined($host_view->{'configManager.healthStatusSystem'}));
    my $healthStatusSystem = Vim::get_view(mo_ref => $host_view->{'configManager.healthStatusSystem'});
    my $vcentersdk = new URI::URL $host_view->{'vim'}->{'service_url'};
    my @h_hwissues = ();
    my %h_hwissue;
    
    if ($healthStatusSystem->runtime)
    {
      
      my $vcenterID = dbGetVC($vcentersdk->host);
      my $hostID = dbGetHost($host_view->{"mo_ref"}->{"type"}."-".$host_view->{"mo_ref"}->{"value"}, $vcenterID);
      
      if ($healthStatusSystem->runtime->hardwareStatusInfo)
      {
        
        my $cpuStatus = $healthStatusSystem->runtime->hardwareStatusInfo->cpuStatusInfo;
        
        foreach (@$cpuStatus)
        {
          
          if (lc($_->status->key) ne 'green' && lc($_->status->key) ne 'unknown')
          {
            
            my $query = "SELECT * FROM hardwarestatus WHERE host = '" . $hostID . "' AND issuename = '" . $_->name . "' ORDER BY lastseen DESC LIMIT 1";
            my $sth = $dbh->prepare($query);
            $sth->execute();
            my $rows = $sth->rows;
            # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
            my $ref = $sth->fetchrow_hashref();
            
            if (($rows gt 0) && ($ref->{'issuestate'} eq lc($_->status->key)))
            {
              
              # HWStatus already exists, have not changed, updated lastseen property
              my $sqlUpdate = $dbh->prepare("UPDATE hardwarestatus set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
              $sqlUpdate->execute($start);
              $sqlUpdate->finish();
              
            }
            else
            {
              
              my $sqlInsert = $dbh->prepare("INSERT INTO hardwarestatus (host, issuename, issuestate, issuetype, firstseen, lastseen) VALUES (?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
              $sqlInsert->execute(
                $hostID,
                $_->name,
                lc($_->status->key),
                "cpu",
                $start,
                $start
              );
              $sqlInsert->finish();
              
            } # END if (($rows gt 0) && ($ref->{'issuestate'} eq lc($_->status->key)))
            
          } # END if (lc($_->status->key) ne 'green' && lc($_->status->key) ne 'unknown')
          
        } # END foreach (@$cpuStatus)
        
        my $memStatus = $healthStatusSystem->runtime->hardwareStatusInfo->memoryStatusInfo;
        
        foreach (@$memStatus)
        {
          
          if (lc($_->status->key) ne 'green' && lc($_->status->key) ne 'unknown')
          {
            
            my $query = "SELECT * FROM hardwarestatus WHERE host = '" . $hostID . "' AND issuename = '" . $_->name . "' ORDER BY lastseen DESC LIMIT 1";
            my $sth = $dbh->prepare($query);
            $sth->execute();
            my $rows = $sth->rows;
            # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
            my $ref = $sth->fetchrow_hashref();
            
            if (($rows gt 0) && ($ref->{'issuestate'} eq lc($_->status->key)))
            {
              
              # HWStatus already exists, have not changed, updated lastseen property
              my $sqlUpdate = $dbh->prepare("UPDATE hardwarestatus set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
              $sqlUpdate->execute($start);
              $sqlUpdate->finish();
              
            }
            else
            {
              
              my $sqlInsert = $dbh->prepare("INSERT INTO hardwarestatus (host, issuename, issuestate, issuetype, firstseen, lastseen) VALUES (?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
              $sqlInsert->execute(
                $hostID,
                $_->name,
                lc($_->status->key),
                "memory",
                $start,
                $start
              );
              $sqlInsert->finish();
              
            } # END if (($rows gt 0) && ($ref->{'issuestate'} eq lc($_->status->key)))
            
          } # END if (lc($_->status->key) ne 'green' && lc($_->status->key) ne 'unknown')
          
        } # END foreach (@$memStatus)
        
        my $storageStatus = $healthStatusSystem->runtime->hardwareStatusInfo->storageStatusInfo;
        
        foreach (@$storageStatus)
        {
          
          if (lc($_->status->key) ne 'green' && lc($_->status->key) ne 'unknown')
          {
            
            my $query = "SELECT * FROM hardwarestatus WHERE host = '" . $hostID . "' AND issuename = '" . $_->name . "' ORDER BY lastseen DESC LIMIT 1";
            my $sth = $dbh->prepare($query);
            $sth->execute();
            my $rows = $sth->rows;
            # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
            my $ref = $sth->fetchrow_hashref();
            
            if (($rows gt 0) && ($ref->{'issuestate'} eq lc($_->status->key)))
            {
              
              # HWStatus already exists, have not changed, updated lastseen property
              my $sqlUpdate = $dbh->prepare("UPDATE hardwarestatus set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
              $sqlUpdate->execute($start);
              $sqlUpdate->finish();
              
            }
            else
            {
              
              my $sqlInsert = $dbh->prepare("INSERT INTO hardwarestatus (host, issuename, issuestate, issuetype, firstseen, lastseen) VALUES (?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
              $sqlInsert->execute(
                $hostID,
                $_->name,
                lc($_->status->key),
                "storage",
                $start,
                $start
              );
              $sqlInsert->finish();
              
            } # END if (($rows gt 0) && ($ref->{'issuestate'} eq lc($_->status->key)))
            
          } # END if (lc($_->status->key) ne 'green' && lc($_->status->key) ne 'unknown')
          
        } # END foreach (@$storageStatus)
        
      } # END if ($healthStatusSystem->runtime->hardwareStatusInfo)
      
      if ($healthStatusSystem->runtime->systemHealthInfo)
      {
        
        my $sensorInfo = $healthStatusSystem->runtime->systemHealthInfo->numericSensorInfo;
        
        foreach (@$sensorInfo)
        {
          
          if ($_->healthState && lc($_->healthState->key) ne 'green' && lc($_->healthState->key) ne 'unknown')
          {
            
            my $query = "SELECT * FROM hardwarestatus WHERE host = '" . $hostID . "' AND issuename = '" . $_->name . "' ORDER BY lastseen DESC LIMIT 1";
            my $sth = $dbh->prepare($query);
            $sth->execute();
            my $rows = $sth->rows;
            # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
            my $ref = $sth->fetchrow_hashref();
            
            if (($rows gt 0) && ($ref->{'issuestate'} eq lc($_->healthState->key)))
            {
              
              # HWStatus already exists, have not changed, updated lastseen property
              my $sqlUpdate = $dbh->prepare("UPDATE hardwarestatus set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
              $sqlUpdate->execute($start);
              $sqlUpdate->finish();
              
            }
            else
            {
              
              my $sqlInsert = $dbh->prepare("INSERT INTO hardwarestatus (host, issuename, issuestate, issuetype, firstseen, lastseen) VALUES (?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
              $sqlInsert->execute(
                $hostID,
                $_->name,
                lc($_->healthState->key),
                $_->sensorType,
                $start,
                $start
              );
              $sqlInsert->finish();
              
            } # END if (($rows gt 0) && ($ref->{'issuestate'} eq lc($_->healthState->key)))
            
          } # END if ($_->healthState && lc($_->healthState->key) ne 'green' && lc($_->healthState->key) ne 'unknown')
          
        } # END foreach (@$sensorInfo)
        
      } # END if ($healthStatusSystem->runtime->systemHealthInfo)
      
    } # END if ($healthStatusSystem->runtime)
    
  } # END foreach my $host_view (@$view_HostSystem)
  
} # END sub getHardwareStatus

sub getAlarms
{
  
  foreach my $datacenter_view (@$view_Datacenter)
  {
    
    next if(!defined($datacenter_view->triggeredAlarmState));
    
    foreach my $triggeredAlarm (@{$datacenter_view->triggeredAlarmState})
    {

      my $entity = Vim::get_view(mo_ref => $triggeredAlarm->entity, properties => [ 'name' ]);
      my $alarm = Vim::get_view(mo_ref => $triggeredAlarm->alarm, properties => [ 'info.name' ]);
      my $vcentersdk = new URI::URL $datacenter_view->{'vim'}->{'service_url'};
      my $vcenterID = dbGetVC($vcentersdk->host);
      my $createTime = "0000-00-00 00:00:00";
      $createTime = substr($triggeredAlarm->time, 0, 19);
      $createTime =~ s/T/ /g;
      my $moRef = $alarm->{'mo_ref'}->{'type'}."-".$alarm->{'mo_ref'}->{'value'};
      my $entityMoRef = $entity->{'mo_ref'}->{'type'}."-".$entity->{'mo_ref'}->{'value'};
      my $query = "SELECT * FROM alarms WHERE vcenter = '" . $vcenterID . "' AND moref = '" . $moRef . "' AND entityMoRef = '" . $entityMoRef . "' ORDER BY lastseen DESC LIMIT 1";
      my $sth = $dbh->prepare($query);
      $sth->execute();
      my $rows = $sth->rows;
      # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
      my $ref = $sth->fetchrow_hashref();
      
      if (($rows gt 0)
        && ($ref->{'entityMoRef'} eq $entityMoRef)
        && ($ref->{'alarm_name'} eq $alarm->{'info.name'})
        && ($ref->{'time'} eq $createTime)
        && ($ref->{'status'} eq $triggeredAlarm->overallStatus->val))
      {
        
        # Alarm already exists, have not changed, updated lastseen property
        my $sqlUpdate = $dbh->prepare("UPDATE alarms set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
        $sqlUpdate->execute($start);
        $sqlUpdate->finish();
        
      }
      else
      {
        
        my $sqlInsert = $dbh->prepare("INSERT INTO alarms (vcenter, moref, entityMoRef, alarm_name, time, status, firstseen, lastseen) VALUES (?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
        $sqlInsert->execute(
          $vcenterID,
          $moRef,
          $entityMoRef,
          $alarm->{'info.name'},
          $createTime,
          $triggeredAlarm->overallStatus->val,
          $start,
          $start
        );
        $sqlInsert->finish();
        
      } # END if (($rows gt 0)
      
      $sth->finish();
      
    } # END foreach my $triggeredAlarm (@{$datacenter_view->triggeredAlarmState})
    
  } # END foreach my $datacenter_view (@$view_Datacenter)
  
} # END sub getAlarms

sub snapshotInventory
{
  
  my ($snapshotTree,$vmID) = @_;
  my $description = (defined($snapshotTree->description) ? $snapshotTree->description : 'Not Available');
  my $moRef = $snapshotTree->{'snapshot'}->{'type'}."-".$snapshotTree->{'snapshot'}->{'value'};
  my $createTime = "0000-00-00 00:00:00";
  $createTime = substr($snapshotTree->createTime, 0, 19);
  $createTime =~ s/T/ /g;
  # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
  my $refSnapshot = dbGetSnapshot($moRef,$vmID);
  
  if ( ($refSnapshot != 0)
    && (decode_utf8($refSnapshot->{'name'}) eq $snapshotTree->name)
    && ($refSnapshot->{'createTime'} eq $createTime)
    && ($refSnapshot->{'snapid'} eq $snapshotTree->id)
    && (decode_utf8($refSnapshot->{'description'}) eq $description)
    && ($refSnapshot->{'quiesced'} eq $snapshotTree->quiesced)
    && ($refSnapshot->{'state'} eq $snapshotTree->state->val))
  {
    
    # Snapshot already exists, have not changed, updated lastseen property
    $logger->info("[DEBUG][SNAPSHOT-INVENTORY] Snapshot $moRef already exists and have not changed since last check, updating lastseen property") if $showDebug;
    my $sqlUpdate = $dbh->prepare("UPDATE snapshots set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $refSnapshot->{'id'} . "'");
    $sqlUpdate->execute($start);
    $sqlUpdate->finish();
    
  }
  else
  {
    
    if ($refSnapshot != 0)
    {
      
      # Snapshot have changed, we must decom old one before create a new one
      compareAndLog($refSnapshot->{'name'}, $snapshotTree->name);
      compareAndLog($refSnapshot->{'createTime'}, $createTime);
      compareAndLog($refSnapshot->{'snapid'}, $snapshotTree->id);
      compareAndLog($refSnapshot->{'description'}, $description);
      compareAndLog($refSnapshot->{'quiesced'}, $snapshotTree->quiesced);
      compareAndLog($refSnapshot->{'state'}, $snapshotTree->state->val);
      $logger->info("[DEBUG][SNAPSHOT-INVENTORY] Snapshot $moRef have changed since last check, sending old entry it into oblivion") if $showDebug;
      
    } # END if ($refSnapshot != 0)
    
    $logger->info("[DEBUG][SNAPSHOT-INVENTORY] Adding data for snapshot $moRef") if $showDebug;
    my $sqlInsert = $dbh->prepare("INSERT INTO snapshots (vm, moref, name, createTime, snapid, description, quiesced, state, firstseen, lastseen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
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
      $start
    );
    $sqlInsert->finish();
    
  } # END if ($refSnapshot != 0) + check
  
  # recurse through the tree of snaps
  if ($snapshotTree->childSnapshotList)
  {
    
    # loop through any children that may exist
    foreach (@{$snapshotTree->childSnapshotList})
    {
      
      snapshotInventory($_,$vmID);
      
    } # END foreach (@{$snapshotTree->childSnapshotList})
    
  } # END if ($snapshotTree->childSnapshotList)
  
} # END sub snapshotInventory

sub getConfigurationIssue
{
  
  foreach my $host_view (@$view_HostSystem)
  {
    
    my $vcentersdk = new URI::URL $host_view->{'vim'}->{'service_url'};
    # get vcenter id from database
    my $vcenterID = dbGetVC($vcentersdk->host);
    my $hostID = dbGetHost($host_view->{'mo_ref'}->{'type'}."-".$host_view->{'mo_ref'}->{'value'}, $vcenterID);
    
    foreach ($host_view->configIssue)
    {
      
      if (defined(@$_[0]))
      {

        my $fullFormattedMessage = $dbh->quote(@$_[0]->fullFormattedMessage);
        my $query = "SELECT * FROM configurationissues WHERE host = '" . $hostID->{'id'} . "' AND configissue = " . $fullFormattedMessage . " ORDER BY lastseen DESC LIMIT 1";
        my $sth = $dbh->prepare($query);
        $sth->execute();
        my $rows = $sth->rows;
        # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
        my $ref = $sth->fetchrow_hashref();
        
        if ($rows gt 0)
        {
          
          # ConfigIssue already exists, have not changed, updated lastseen property
          $logger->info("[DEBUG][CONFIGISSUE-INVENTORY] ConfigIssue for host $hostID already exists and have not changed since last check, updating lastseen property") if $showDebug;
          my $sqlUpdate = $dbh->prepare("UPDATE configurationissues set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
          $sqlUpdate->execute($start);
          $sqlUpdate->finish();
          
        }
        else
        {
          
          $logger->info("[DEBUG][CONFIGISSUE-INVENTORY] Adding ConfigIssue data for host $hostID") if $showDebug;
          my $sqlInsert = $dbh->prepare("INSERT INTO configurationissues (host, configissue, firstseen, lastseen) VALUES (?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
          $sqlInsert->execute(
            $hostID,
            @$_[0]->fullFormattedMessage,
            $start,
            $start
          );
          $sqlInsert->finish();
          
        } # END if ($rows gt 0)
        
        $sth->finish();
        
      } # END if (defined(@$_[0]))
      
    } # END foreach ($host_view->configIssue)
    
  } # END foreach my $host_view (@$view_HostSystem)
  
} # END sub getConfigurationIssue

sub getPermissions
{

  my $authorizationMgr = Vim::get_view(mo_ref => Vim::get_service_content()->authorizationManager);
  my $roleList = $authorizationMgr->roleList;
  my %h_role = ();
  foreach(@$roleList) { $h_role{$_->roleId} = $_->name; }
  my $perms = $authorizationMgr->RetrieveAllPermissions;
  # get vcenter id from database
  my $vcenterID = dbGetVC($activeVC);
  
  foreach my $perm (@$perms)
  {
    
    my $principal = $perm->principal;
    $principal =~ s/\\/\\\\/g;
    my $query = "SELECT * FROM permissions WHERE principal LIKE '" . $principal . "' ESCAPE '|' AND vcenter = '" . $vcenterID . "' AND role_name = '" . $h_role{$perm->roleId} . "' ORDER BY lastseen DESC LIMIT 1";
    my $sth = $dbh->prepare($query);
    $sth->execute();
    my $rows = $sth->rows;
    # TODO > generate error and skip if multiple + manage deletion (execute query on lastseen != $start)
    my $ref = $sth->fetchrow_hashref();
    
    if ($rows gt 0)
    {
      
      # Permission already exists, have not changed, updated lastseen property
      $logger->info("[DEBUG][PERMISSION-INVENTORY] Permission for role '" . $h_role{$perm->roleId} . " on user '" . $principal . "' already exists and have not changed since last check, updating lastseen property") if $showDebug;
      my $sqlUpdate = $dbh->prepare("UPDATE permissions set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
      $sqlUpdate->execute($start);
      $sqlUpdate->finish();
      
    }
    else
    {
      
      $logger->info("[DEBUG][PERMISSION-INVENTORY] Adding Permission data for role '" . $h_role{$perm->roleId} . " on user '" . $principal . "'") if $showDebug;
      my $sqlInsert = $dbh->prepare("INSERT INTO permissions (vcenter, principal, role_name, isGroup, inventory_path, firstseen, lastseen) VALUES (?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
      my $inventory_path = '/' . Util::get_inventory_path(Vim::get_view(mo_ref => $perm->entity, properties => ['name']), Vim::get_vim());
      $sqlInsert->execute(
        $vcenterID,
        $perm->principal,
        $h_role{$perm->roleId},
        $perm->group,
        $inventory_path,
        $start,
        $start
      );
      $sqlInsert->finish();
      
    } # END if ($rows gt 0)
    
    $sth->finish();
    
  } # END foreach my $perm (@$perms)
  
} # END sub getPermissions

sub dvpginventory
{
  
  foreach my $distributedVirtualPortgroup_view (@$view_DistributedVirtualPortgroup)
  {
    
    # Exclude DV uplinks portgroup
    if (!defined($distributedVirtualPortgroup_view->tag) || @{$distributedVirtualPortgroup_view->tag}[0]->key ne 'SYSTEM/DVS.UPLINKPG')
    {
      
      my $vcentersdk = new URI::URL $distributedVirtualPortgroup_view->{'vim'}->{'service_url'};
      my $openPorts = $distributedVirtualPortgroup_view->{'config.numPorts'} - (defined($distributedVirtualPortgroup_view->vm) ? 0+@{$distributedVirtualPortgroup_view->vm} : 0);
      # get vcenter id from database
      my $vcenterID = dbGetVC($vcentersdk->host);
      my $moRef = $distributedVirtualPortgroup_view->{'mo_ref'}->{'type'}."-".$distributedVirtualPortgroup_view->{'mo_ref'}->{'value'};
      my $query = "SELECT * FROM distributedvirtualportgroups WHERE vcenter = '" . $vcenterID . "' AND moref = '" . $moRef . "' ORDER BY lastseen DESC LIMIT 1";
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
        && ($ref->{'autoexpand'} eq $boolHash{$distributedVirtualPortgroup_view->{'config.autoExpand'}}))
      {
        
        # DVPortgroup already exists, have not changed, updated lastseen property
        my $sqlUpdate = $dbh->prepare("UPDATE distributedvirtualportgroups set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $ref->{'id'} . "'");
        $sqlUpdate->execute($start);
        $sqlUpdate->finish();
        
      }
      else
      {
        
        my $sqlInsert = $dbh->prepare("INSERT INTO distributedvirtualportgroups (vcenter, moref, name, numports, openports, autoexpand, firstseen, lastseen) VALUES (?, ?, ?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
        $sqlInsert->execute(
          $vcenterID,
          $moRef,
          $distributedVirtualPortgroup_view->name,
          $distributedVirtualPortgroup_view->{'config.numPorts'},
          $openPorts,
          $boolHash{$distributedVirtualPortgroup_view->{'config.autoExpand'}},
          $start,
          $start
        );
        $sqlInsert->finish();
        
      } # END if (($rows gt 0) + check
      
      $sth->finish();
      
    } # END if (!defined($distributedVirtualPortgroup_view->tag) || @{$distributedVirtualPortgroup_view->tag}[0]->key ne 'SYSTEM/DVS.UPLINKPG')
    
  } # END foreach my $distributedVirtualPortgroup_view (@$view_DistributedVirtualPortgroup)

} # END sub dvpginventory

sub datastoreOrphanedVMFilesreport
{
  
  my %h_layoutFiles = ();
  foreach my $vm_view (@$view_VirtualMachine)
  {
    
    if ($vm_view->runtime->connectionState ne "invalid" || $vm_view->runtime->connectionState ne "orphaned")
    {
      
      my $layoutFiles = eval {$vm_view->{'layoutEx.file'}} || [];
      my $swapFile = eval {$vm_view->{'layout.swapFile'}} || [];
      
      foreach my $layoutFile (@$layoutFiles)
      {
        
        if ($layoutFile->type ne "log")
        {
        
          $h_layoutFiles{ $layoutFile->name } = '1';
          
        } # END if ($layoutFile->type ne "log")
        
      } # END foreach my $layoutFile (@$layoutFiles)
      
      if ($swapFile)
      {
        
        $h_layoutFiles{ $swapFile } = '1';
        
      } # END if ($swapFile)
      
    } # END if ($vm_view->runtime->connectionState ne "invalid" || $vm_view->runtime->connectionState ne "orphaned")
    
  } # END foreach my $vm_view (@$view_VirtualMachine)
  
  foreach my $datastore_view (@$view_Datastore)
  {
    
    if ($datastore_view->summary->accessible)
    {
      
      my $vcentersdk = new URI::URL $datastore_view->{'vim'}->{'service_url'};
      my $vcenterID = dbGetVC($vcentersdk->host);
      my $dsbrowser = Vim::get_view(mo_ref => $datastore_view->browser);
      my $ds_path = "[" . $datastore_view->name . "]";
      my $file_query = FileQueryFlags->new(fileOwner => 0, fileSize => 1, fileType => 0, modification => 1);
      my $search_res;
      eval
      {
        
        my $searchSpec = HostDatastoreBrowserSearchSpec->new(details => $file_query, matchPattern => ["*zdump*", "*.xml", "*.vmsn", "*.vmsd", "*.vswp*",  "*.vmx", "*.vmdk", "*.vmss", "*.nvram", "*.vmxf"]);
        $search_res = $dsbrowser->SearchDatastoreSubFolders(datastorePath => $ds_path, searchSpec => $searchSpec);
        
      };
      
      if ($@)
      {
        
        $logger->info("[DEBUG][ORPHANFILE-INVENTORY] Error during searching on $ds_path it usually is due to directory too large to search") if $showDebug;
        next;
        
      }
      
      if ($search_res)
      {
        
        foreach my $result (@$search_res)
        {
          
          my $files = $result->file;
          
          if ($files && !($result->folderPath =~ /.zfs/ || $result->folderPath =~ /.snapshot/ || $result->folderPath =~ /var\/tmp\/cache/ || $result->folderPath =~ /.lck/ || $result->folderPath =~ /\/hostCache\//))
          {
            
            foreach my $file (@$files)
            {
              
              my $fullFilePath = $result->folderPath . $file->path;
              if (!($file->path =~ /-ctk.vmdk$/ || $file->path =~ /esxconsole-flat.vmdk$/ || $file->path =~ /esxconsole.vmdk$/ || $h_layoutFiles{ $fullFilePath }))
              {
                
                my $refOrphanFile = dbGetOrphanFile($fullFilePath,$vcenterID);
                
                if ($refOrphanFile != 0)
                {
                  
                  # Orphan file already exists, have not changed, updated lastseen property
                  $logger->info("[DEBUG][ORPHANFILE-INVENTORY] Orphan file $fullFilePath already exists and have not changed since last check, updating lastseen property") if $showDebug;
                  my $sqlUpdate = $dbh->prepare("UPDATE orphanFiles set lastseen = FROM_UNIXTIME (?) WHERE id = '" . $refOrphanFile->{'id'} . "'");
                  $sqlUpdate->execute($start);
                  $sqlUpdate->finish();
                  
                }
                else
                {
                  
                  $logger->info("[DEBUG][ORPHANFILE-INVENTORY] Adding data for orphan file $fullFilePath") if $showDebug;
                  my $sqlInsert = $dbh->prepare("INSERT INTO orphanFiles (vcenter, filePath, fileSize, fileModification, firstseen, lastseen) VALUES (?, ?, ?, ?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
                  $sqlInsert->execute(
                    $vcenterID,
                    $fullFilePath,
                    $file->fileSize,
                    $file->modification,
                    $start,
                    $start
                  );
                  $sqlInsert->finish();
                  
                } # END if ($refOrphanFile != 0)
                
              } # END if (!($file->path =~ /-ctk.vmdk$/ || $file->path =~ /esxconsole-flat.vmdk$/ || $file->path =~ /esxconsole.vmdk$/ || $h_layoutFiles{ $fullFilePath }))
              
            } # END foreach my $file (@$files)
            
          } # END if($files)
          
        } # END foreach my $result (@$search_res)
        
      } # END if ($search_res)

    } # END if ($datastore_view->summary->accessible)
    
  } # END foreach my $datastore_view (@$view_Datastore)
  
} # END sub datastoreOrphanedVMFilesreport

sub dbGetVC
{

  # This subroutine will return vcenter ID if it exists
  # or create a new vcenter ID if not
  my ($vcenterName) = @_;
  my $query = "SELECT id FROM vcenters WHERE vcname = '" . $vcenterName . "'";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  my $vcenterID = 0;

  if ($rows eq 0)
  {
    
    # vcenter ID does not exist so we create it and return it
    my $sqlInsert = $dbh->prepare("INSERT INTO vcenters (vcname, firstseen, lastseen) VALUES (?, FROM_UNIXTIME (?), FROM_UNIXTIME (?))");
    $sqlInsert->execute($vcenterName, $start, $start);
    $sqlInsert->finish();
    # re-execute query after inserting new vcenter
    $sth = $dbh->prepare($query);
    $sth->execute();
    my $ref = $sth->fetchrow_hashref();
    $vcenterID = $ref->{'id'};
    
  }
  else
  {
    
    # vcenter is still alive, updating lastseen property
    my $ref = $sth->fetchrow_hashref();
    $vcenterID = $ref->{'id'};
    $dbh->do("UPDATE vcenters set lastseen = FROM_UNIXTIME ($start) WHERE id = '" . $vcenterID . "'");
  
  } # END if ($rows eq 0)
  
  $sth->finish();
  return $vcenterID;

} # END sub dbGetVC

sub dbGetSnapshot
{
  
  # This subroutine will return snapshot object if it exists or 0 if not
  my ($snapshotMoref,$vmID) = @_;
  my $query = "SELECT * FROM snapshots WHERE moref = '" . $snapshotMoref . "' AND vm = '" . $vmID . "' ORDER BY lastseen DESC LIMIT 1";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  my $ref = 0;
  
  if ($rows eq 0)
  {
    
    $logger->info("[DEBUG][GETSNAPSHOT] Snapshot $snapshotMoref on VM $vmID doesn't exist") if $showDebug;
    
  }
  else
  {
    
    $ref = $sth->fetchrow_hashref();
    $logger->info("[DEBUG][GETSNAPSHOT] SnapshotID for snapshot $snapshotMoref on VM $vmID is ".$ref->{'id'}) if $showDebug;
    
  } # END if ($rows eq 0)
  
  $sth->finish();
  return $ref;
  
} # END sub dbGetSnapshot

sub dbGetOrphanFile
{
  
  # This subroutine will return orphan file object if it exists or 0 if not
  my ($orphanFilePath,$vcenterID) = @_;
  my $query = "SELECT * FROM orphanFiles WHERE filePath = '" . $orphanFilePath . "' AND vcenter = '" . $vcenterID . "' ORDER BY lastseen DESC LIMIT 1";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  my $ref = 0;
  
  if ($rows eq 0)
  {
    
    $logger->info("[DEBUG][GETORPHANFILE] Orphan file $orphanFilePath on vCenter $vcenterID doesn't exist") if $showDebug;
    
  }
  else
  {
    
    $ref = $sth->fetchrow_hashref();
    $logger->info("[DEBUG][GETORPHANFILE] Orphan file ID for orphan file $orphanFilePath on vCenter $vcenterID is ".$ref->{'id'}) if $showDebug;
    
  } # END if ($rows eq 0)
  
  $sth->finish();
  return $ref;
  
} # END sub dbGetOrphanFile

sub dbGetCluster
{
  
  # This subroutine will return cluster object if it exists or 0 if not
  my ($clusterMoref,$vcenterID) = @_;
  my $query = "SELECT * FROM clusters WHERE moref = '" . $clusterMoref . "' AND vcenter = '" . $vcenterID . "' ORDER BY lastseen DESC LIMIT 1";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  my $ref = 0;
  
  if ($rows eq 0)
  {
    
    $logger->info("[DEBUG][GETCLUSTER] Cluster $clusterMoref on vCenter $vcenterID doesn't exist") if $showDebug;
    
  }
  else
  {
    
    $ref = $sth->fetchrow_hashref();
    $logger->info("[DEBUG][GETCLUSTER] ClusterID for cluster $clusterMoref on vCenter $vcenterID is ".$ref->{'id'}) if $showDebug;
    
  } # END if ($rows eq 0)
  
  $sth->finish();
  return $ref;
  
} # END sub dbGetCluster

sub dbGetClusterVSAN
{
  
  # This subroutine will return cluster VSAN object if it exists or 0 if not
  my ($clusterID) = @_;
  my $query = "SELECT * FROM clustersVSAN WHERE cluster_id = '" . $clusterID . "' ORDER BY lastseen DESC LIMIT 1";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  my $ref = 0;
  
  if ($rows eq 0)
  {
    
    $logger->info("[DEBUG][GETCLUSTERVSAN] Cluster VSAN $clusterID doesn't exist") if $showDebug;
    
  }
  else
  {
    
    $ref = $sth->fetchrow_hashref();
    $logger->info("[DEBUG][GETCLUSTERVSAN] ClusterID for cluster VSAN $clusterID is ".$ref->{'id'}) if $showDebug;
    
  } # END if ($rows eq 0)
  
  $sth->finish();
  return $ref;
  
} # END sub dbGetClusterVSAN

sub dbGetClusterMetrics
{
  
  # This subroutine will return cluster metrics
  my ($clusterID) = @_;
  my $query = "SELECT vmotion FROM clusterMetrics WHERE cluster_id = '" . $clusterID . "' ORDER BY id DESC LIMIT 1";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  my $ref = 0;
  
  if ($rows eq 0)
  {
    
    $logger->info("[DEBUG][GETCLUSTER] Cluster $clusterID Metrics doesn't exist") if $showDebug;
    
  }
  else
  {
    
    $ref = $sth->fetchrow_hashref();
    $logger->info("[DEBUG][GETCLUSTER] Cluster vmotion for cluster $clusterID is " . encode_json $ref) if $showDebug;
    
  } # END if ($rows eq 0)
  
  $sth->finish();
  return $ref;
  
} # END sub dbGetClusterMetrics

sub dbGetHost
{

  # This subroutine will return host object if it exists or 0 if not
  my ($hostMoref,$vcenterID) = @_;
  my $query = "SELECT * FROM hosts WHERE moref = '" . $hostMoref . "' AND vcenter = '" . $vcenterID . "' ORDER BY lastseen DESC LIMIT 1";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  my $ref = 0;
  
  if ($rows eq 0)
  {
    
    $logger->info("[DEBUG][GETHOST] Host $hostMoref on vCenter $vcenterID doesn't exist") if $showDebug;
    
  }
  else
  {
    
    $ref = $sth->fetchrow_hashref();
    $logger->info("[DEBUG][GETHOST] HostID for host $hostMoref on vCenter $vcenterID is ".$ref->{'id'}) if $showDebug;
    
  } # END if ($rows eq 0)
  
  $sth->finish();
  return $ref;
  
} # END sub dbGetHost

sub dbGetHostMetrics
{
  
  # This subroutine will return host metrics
  my ($hostID) = @_;
  my $query = "SELECT sharedmemory, cpuUsage, memoryUsage FROM hostMetrics WHERE host_id = '" . $hostID . "' ORDER BY id DESC LIMIT 1";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  my $ref = 0;
  
  if ($rows eq 0)
  {
    
    $logger->info("[DEBUG][GETHOST] Host $hostID Metrics doesn't exist") if $showDebug;
    
  }
  else
  {
    
    $ref = $sth->fetchrow_hashref();
    $logger->info("[DEBUG][GETHOST] Host sharedmemory, cpuUsage, memoryUsage for host $hostID is " . encode_json $ref) if $showDebug;
    
  } # END if ($rows eq 0)
  
  $sth->finish();
  return $ref;
  
} # END sub dbGetHostMetrics

sub dbGetDatastore
{
  
  # This subroutine will return datastore object if it exists or 0 if not
  my ($datastoreName,$vcenterID) = @_;
  my $query = "SELECT * FROM datastores WHERE datastore_name = '" . $datastoreName . "' AND vcenter = '" . $vcenterID . "' ORDER BY lastseen DESC LIMIT 1";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  my $ref = 0;
  
  if ($rows eq 0)
  {
    
    $logger->info("[DEBUG][GETDATASTORE] Datastore $datastoreName on vCenter $vcenterID doesn't exist") if $showDebug;
    
  }
  else
  {
    
    $ref = $sth->fetchrow_hashref();
    $logger->info("[DEBUG][GETDATASTORE] DatastoreID for datastore $datastoreName on vCenter $vcenterID is ".$ref->{'id'}) if $showDebug;
    
  } # END if ($rows eq 0)
  
  $sth->finish();
  return $ref;
  
} # END sub dbGetDatastore

sub dbGetDatastoreMapping
{
  
  # This subroutine will return datastore mapping object if it exists or 0 if not
  my ($datastoreID,$hostID) = @_;
  my $query = "SELECT * FROM datastoreMappings WHERE datastore_id = '" . $datastoreID . "' AND host_id = '" . $hostID . "' ORDER BY lastseen DESC LIMIT 1";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  my $ref = 0;
  
  if ($rows eq 0)
  {
    
    $logger->info("[DEBUG][GETDATASTOREMAPPING] Datastore mapping between datastore $datastoreID and host $hostID doesn't exist") if $showDebug;
    
  }
  else
  {
    
    $ref = $sth->fetchrow_hashref();
    $logger->info("[DEBUG][GETDATASTOREMAPPING] Datastore mapping ID for datastore $datastoreID and host $hostID is ".$ref->{'id'}) if $showDebug;
    
  } # END if ($rows eq 0)
  
  $sth->finish();
  return $ref;
  
} # END sub dbGetDatastoreMapping

sub dbGetDatastoreID
{
  
  # This subroutine will return datastore ID if it exists or 0 if not
  my ($datastoreMoRef,$vcenterID) = @_;
  my $query = "SELECT id FROM datastores WHERE moref = '" . $datastoreMoRef . "' AND vcenter = '" . $vcenterID . "' ORDER BY lastseen DESC LIMIT 1";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  my $datastoreID = 0;
  
  if ($rows eq 0)
  {
    
    $logger->info("[DEBUG][GETDATASTOREID] Datastore $datastoreMoRef on vCenter $vcenterID doesn't exist") if $showDebug;
    
  }
  else
  {
    
    $datastoreID = $sth->fetchrow_hashref();
    $logger->info("[DEBUG][GETDATASTOREID] DatastoreID for datastore $datastoreMoRef on vCenter $vcenterID is ".$datastoreID->{'id'}) if $showDebug;
    $datastoreID = $datastoreID->{'id'};
    
  } # END if ($rows eq 0)
  
  $sth->finish();
  return $datastoreID;
  
} # END sub dbGetDatastoreID

sub dbGetDatastoreMetrics
{
  
  # This subroutine will return datastore metrics
  my ($datastoreID) = @_;
  my $query = "SELECT size, freespace, uncommitted FROM datastoreMetrics WHERE datastore_id = '" . $datastoreID . "' ORDER BY id DESC LIMIT 1";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  my $ref = 0;
  
  if ($rows eq 0)
  {
    
    $logger->info("[DEBUG][GETDATASTORE] Datastore $datastoreID Metrics doesn't exist") if $showDebug;
    
  }
  else
  {
    
    $ref = $sth->fetchrow_hashref();
    $logger->info("[DEBUG][GETDATASTORE] Datastore size, freespace, uncommitted for datastore $datastoreID is " . encode_json $ref) if $showDebug;
    
  } # END if ($rows eq 0)
  
  $sth->finish();
  return $ref;
  
} # END sub dbGetDatastoreMetrics

sub dbGetVM
{
  
  # This subroutine will return vm object if it exists or 0 if not
  my ($vmMoref,$vcenterID) = @_;
  my $query = "SELECT * FROM vms WHERE moref = '" . $vmMoref . "' AND vcenter = '" . $vcenterID . "' ORDER BY lastseen DESC LIMIT 1";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  my $ref = 0;
  
  if ($rows eq 0)
  {
    
    $logger->info("[DEBUG][GETVM] VM $vmMoref on vCenter $vcenterID doesn't exist") if $showDebug;
    
  }
  else
  {
    
    $ref = $sth->fetchrow_hashref();
    $logger->info("[DEBUG][GETVM] VMID for VM $vmMoref on vCenter $vcenterID is ".$ref->{'id'}) if $showDebug;
    
  } # END if ($rows eq 0)
  
  $sth->finish();
  return $ref;
  
} # END sub dbGetVM

sub dbGetVMMetrics
{
  
  # This subroutine will return vm metrics
  my ($vmID) = @_;
  my $query = "SELECT swappedMemory, compressedMemory, commited, balloonedMemory, uncommited FROM vmMetrics WHERE vm_id = '" . $vmID . "' ORDER BY id DESC LIMIT 1";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  my $ref = 0;
  
  if ($rows eq 0)
  {
    
    $logger->info("[DEBUG][GETVM] VM $vmID Metrics doesn't exist") if $showDebug;
    
  }
  else
  {
    
    $ref = $sth->fetchrow_hashref();
    $logger->info("[DEBUG][GETVM] VM swappedMemory, compressedMemory, commited, balloonedMemory, uncommited for VM $vmID is " . encode_json $ref) if $showDebug;
    
  } # END if ($rows eq 0)
  
  $sth->finish();
  return $ref;
  
} # END sub dbGetVMMetrics

sub dbGetConfig
{
  
  # This subroutine will return config value
  my ($configID,$defaultValue) = @_;
  my $query = "SELECT value FROM config WHERE configid = '" . $configID . "'";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  my $configValue = 0;
  
  if ($rows eq 0)
  {
    
    $configValue = $defaultValue;
    
  }
  else
  {
    
    my $ref = $sth->fetchrow_hashref();
    $configValue = $ref->{'value'};
    
  } # END if ($rows eq 0)
  
  $logger->info("[DEBUG][GETCONFIG] ConfigID $configID have value $configValue") if $showDebug;
  return $configValue;
  
} # END sub dbGetConfig

sub dbGetSchedule
{
  
  # This subroutine will return config value
  my ($moduleID) = @_;
  my $query = "SELECT schedule FROM moduleSchedule WHERE id = '" . $moduleID . "'";
  my $sth = $dbh->prepare($query);
  $sth->execute();
  my $rows = $sth->rows;
  my $scheduleValue = "off";
  
  if ($rows gt 0)
  {
    
    my $ref = $sth->fetchrow_hashref();
    $scheduleValue = $ref->{'schedule'};
  
  } # END if ($rows gt 0)
  
  $logger->info("[DEBUG][GETSCHEDULE] ModuleID $moduleID have schedule $scheduleValue") if $showDebug;
  return $scheduleValue;
  
} # END sub dbGetSchedule

sub dbPurgeOldData
{
  
  # This subroutine will scavenge old data based on date threshold
  my ($purgeThreshold) = @_;
  $dbh->do("DELETE FROM alarms WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY)");
  $dbh->do("DELETE FROM certificates WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY)");
  $dbh->do("DELETE FROM clusters WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY) AND id <> '1'");
  $dbh->do("DELETE FROM clustersVSAN WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY)");
  $dbh->do("DELETE FROM clusterMetrics WHERE lastseen < DATE_SUB(NOW(), INTERVAL ".($purgeThreshold+1)." DAY)");
  $dbh->do("DELETE FROM configurationissues WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY)");
  $dbh->do("DELETE FROM datastores WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY)");
  $dbh->do("DELETE FROM datastoreMetrics WHERE lastseen < DATE_SUB(NOW(), INTERVAL ".($purgeThreshold+1)." DAY)");
  $dbh->do("DELETE FROM distributedvirtualportgroups WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY)");
  $dbh->do("DELETE FROM hardwarestatus WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY)");
  $dbh->do("DELETE FROM hosts WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY)");
  $dbh->do("DELETE FROM hostMetrics WHERE lastseen < DATE_SUB(NOW(), INTERVAL ".($purgeThreshold+1)." DAY)");
  $dbh->do("DELETE FROM licenses WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY)");
  $dbh->do("DELETE FROM sessions WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY)");
  $dbh->do("DELETE FROM snapshots WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY)");
  $dbh->do("DELETE FROM vcenters WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY)");
  $dbh->do("DELETE FROM vms WHERE lastseen < DATE_SUB(NOW(), INTERVAL $purgeThreshold DAY)");
  $dbh->do("DELETE FROM vmMetrics WHERE lastseen < DATE_SUB(NOW(), INTERVAL ".($purgeThreshold+1)." DAY)");
  # After purging data, we run some optimisation task on db
  # TODO = switch to InnoDb RECREATE + ANALYZE tasks as OPTIMIZE is supported only on MyISAM
  $dbh->do("OPTIMIZE TABLE alarms, certificates, clusters, clustersVSAN, clusterMetrics, configurationissues, datastores, datastoreMetrics, distributedvirtualportgroups, hardwarestatus, hosts, hostMetrics, licenses, sessions, snapshots, vcenters, vms, vmMetrics");

} # END sub dbPurgeOldData

sub compareAndLog
{
  
  my ($source, $destination) = @_;
  
  if ($source ne $destination)
  {
    
    $logger->info("[DEBUG][COMPAREANDLOG] old=$source | new=$destination") if $showDebug;
    
  } # END if ($source ne $destination)
  
} # END sub compareAndLog

sub terminateSession
{

  my $sessionMgr = Vim::get_view(mo_ref => Vim::get_service_content()->sessionManager);
  my $sessionList = eval {$sessionMgr->sessionList || []};
  my $thresholdSession = dbGetConfig('vcSessionAge');
  my $senderMail = dbGetConfig('senderMail');
  my $recipientMail = dbGetConfig('recipientMail');
  my $smtpAddress = dbGetConfig('smtpAddress');
  my $killedSession = 0;
  my %options;
  $options{INCLUDE_PATH} = '/var/www/admin/mail-template';
  chomp(my $HOSTNAME = `hostname -s`);
  my $sessionBody = "";
  
  foreach my $session (@$sessionList)
  {
    
    # We decide to exclude idle session from VSPHERE.LOCAL\vpxd-extension-### as they should be system related, we only want to deal with end-user ones
    if (((abs(str2time($session->lastActiveTime) - $start) / 86400) > $thresholdSession) && ($session->userName !~ /vpxd-extension/))
    {
      
      $sessionBody = $sessionBody . "<tr style='font-size:16px;'><td style='border:1px solid #CCC'>" . $session->userName . "</td><td style='border:1px solid #CCC'>" . $session->lastActiveTime . "</td></tr>";
      $logger->info("[TERMINATESESSION] Killing session " . $session->key . " of user " . $session->userName . " since it's idle since " . $session->lastActiveTime);
       $sessionMgr->TerminateSession(sessionId => [$session->key]);
      $killedSession++;
      
    } # END if exclude session
    
  } # END foreach my $session (@$sessionList)

  if ($killedSession > 0)
  {
    
    my %params;
    $params{VCSESSIONAGE} = $thresholdSession;
    $params{TERMINATESESSIONBODY} = $sessionBody;
    my $msg = MIME::Lite::TT::HTML->new(
      From        =>  $senderMail,
      To          =>  $recipientMail,
      Subject     =>  '['.uc($HOSTNAME).'] Terminate vCenter '.$activeVC.' Sessions Report',
      Template    =>  { html => 'terminatesession.html' },
      TmplOptions =>  \%options,
      TmplParams  =>  \%params,
    );
    $msg->send('smtp', $smtpAddress, Timeout => 60 );
    
  } # END if ($killedSession > 0)
  
} # END sub terminateSession

sub bundleBackup
{
  
  my $bundleThreshold = dbGetConfig("thresholdBundle", 0);
  $logger->info("[INFO][PURGE] Start ESX bundle purge process");
  my $command = `find /var/www/admin/esxbundle/ -type d -ctime +$bundleThreshold -exec rm -rf {} \\;`;
  $logger->info("[INFO][PURGE] ESX bundle purge return: $command");
  $logger->info("[INFO][PURGE] End ESX bundle purge process");
  my $esxBundlePath = '/var/www/admin/esxbundle/' . time2str("%Y%m%d%H%M", $start);
  $File::Fetch::TIMEOUT = 3;
  $File::Fetch::WARN = 0;
  
  if (!-d $esxBundlePath)
  {
    
    make_path $esxBundlePath;
    
  } # END if (!-d $esxBundlePath)
  
  foreach my $host_view (@$view_HostSystem)
  {

    # We want to backup only connected ESX to avoid error
    if ($host_view->{'runtime.connectionState'}->val ne 'connected' || !defined($host_view->{'configManager.firmwareSystem'})) { next; }
    my $firmwareSys = Vim::get_view(mo_ref => $host_view->{'configManager.firmwareSystem'});
    my $downloadUrl;

    eval
    {
      
      $downloadUrl = $firmwareSys->BackupFirmwareConfiguration();
    
    }; # END eval
    
    if ($@)
    {
      
      $logger->info("[DEBUG][BUNDLEBACKUP] Generating bundle failed for host " . $host_view->name . " " . $@) if $showDebug;
      next;
      
    } # END if ($@)
    
    if ($downloadUrl =~ m@http.*//\*//?(.*)@)
    {

      my $esxName = $host_view->name;
      $downloadUrl =~ s/\/\*\//\/$esxName\//g;
      my $ua = LWP::UserAgent->new;
      $ua->timeout(3);
      my $response = $ua->get($downloadUrl); 
      
      # check if URL is available with small timeout to avoid time consumption
      if (!$response->is_success())
      {
        
        $logger->info("[DEBUG][BUNDLEBACKUP] Downloading bundle failed for host " . $host_view->name . " " . $response->status_line) if $showDebug;
        next;
        
      } # END if ($response->is_success())
      
      my $ff = File::Fetch->new(uri => $downloadUrl);
      
      eval
      {
        
        $logger->info("[DEBUG][BUNDLEBACKUP] Downloading bundle $downloadUrl for host " . $host_view->name) if $showDebug;
        my $where = $ff->fetch( to => $esxBundlePath );
        $command = `chown -R www-data:www-data /var/www/admin/esxbundle/`;
        
      }; # END eval

      if ($@)
      {
      
        $logger->info("[DEBUG][BUNDLEBACKUP] Downloading bundle failed for host " . $host_view->name . " " . $@) if $showDebug;
        next;
        
      } # END if ($@)
    
    }
    else
    {
    
      $logger->info("[DEBUG][BUNDLEBACKUP] Unexpected download URL format: $downloadUrl") if $showDebug;
      next;
      
    } # END if ($downloadUrl =~ m@http.*//\*//?(.*)@)
    
  } # END foreach my $host_view (@$view_HostSystem)
  
} # END sub bundleBackup

sub QuickQueryPerf
{
  
  my ($query_entity_view, $query_group, $query_counter, $query_rollup, $query_instance) = @_;
  my $perfKey = $perfCntr{"$query_group.$query_counter.$query_rollup"}->key;
  my @metricIDs = ();
  my $metricId = PerfMetricId->new(counterId => $perfKey, instance => $query_instance);
  push @metricIDs,$metricId;
  my $perfQuerySpec = PerfQuerySpec->new(entity => $query_entity_view, maxSample => 15, intervalId => 20, metricId => \@metricIDs);
  my $metrics = $perfMgr->QueryPerf(querySpec => [$perfQuerySpec]);
  
  foreach(@$metrics)
  {
    
    my $perfValues = $_->value;
    
    foreach(@$perfValues)
    {
      
      my $values = $_->value;
      my @s_values = sort { $a <=> $b } @$values;
      my $sum = 0;
      my $count = 0;
      
      foreach (@s_values)
      {
        
        if ($count < 13)
        {
          
          $sum += $_;
          $count += 1;
          
        } # END if ($count < 13)
        
      } # END foreach (@s_values)
      
      my $perfavg = $sum/$count;
      $perfavg =~ s/\.\d+$//;
      return $perfavg;
      
    } # END foreach(@$perfValues)
    
  } # END foreach(@$metrics)

} # END sub QuickQueryPerf

sub buildSqlQueryCPGroup
{
  
  my ($CPGroupMembers) = @_;
  my $sqlQuery = " (";
  my $firstMember = '1';

  if ($CPGroupMembers eq 0)
  {
    
    return "$sqlQuery TRUE )";
    
  } # END if ($CPGroupMembers eq 0)
  
  foreach my $CPGroupMember (split(/;/, $CPGroupMembers))
  {
    
    if ($firstMember eq '1')
    {
      
      $firstMember = '0';
      $sqlQuery = $sqlQuery . "c.cluster_name LIKE '" . $CPGroupMember . "'";
      
    }
    else
    {
      
      $sqlQuery = $sqlQuery . " OR c.cluster_name LIKE '" . $CPGroupMember . "'";
      
    } # END if ($firstMember eq '1')
    
  } # END foreach my $CPGroupMember (split(/;/, $CPGroupMembers))
  
  return $sqlQuery . ")";
  
} # END sub buildSqlQueryCPGroup

sub capacityPlanningReport
{
  
  # As this check is cross vcenter (to be able to define cross-vcenter capacity planning groups),
  # we must only execute it once, thus we have to trigger the already-ran flag
  if ($capacityPlanningExecuted == 0)
  {
    
    # TO ADD TO GLOBAL OPTIONS
    my $vmLeftThreshold = 50;
    my $daysLeftThreshold = 180;
    # END TO ADD TO GLOBAL OPTIONS

    # We switch the already-ran flag so that it will not be executed anymore during this execution
    $capacityPlanningExecuted = 1;
    my $senderMail = dbGetConfig('senderMail');
    my $recipientMail = dbGetConfig('recipientMail');
    my $smtpAddress = dbGetConfig('smtpAddress');
    my $capacityPlanningDays = dbGetConfig('capacityPlanningDays');
    my @groups;
    my %options;
    my $numGroup = 0;
    chomp(my $HOSTNAME = `hostname -s`);
    # We want to take a little safety percentage before dropping huge numbers :)
    my $safetyPct = 10;
    my $sthCPG = $dbh->prepare("SELECT group_name, members, percentageThreshold FROM capacityPlanningGroups");
    $sthCPG->execute();
    
    while (my $CPGroup = $sthCPG->fetchrow_hashref)
    {

      # Retrieve current number of VM powered on
      my $CPquery = buildSqlQueryCPGroup($CPGroup->{'members'});
      my $query = "SELECT COUNT(v.id) AS NUMVMON FROM vms AS v INNER JOIN hosts AS h ON (h.id = v.host) INNER JOIN clusters AS c ON (c.id = h.cluster) WHERE $CPquery AND v.firstseen < '" . time2str("%Y-%m-%d", $start - (24 * 60 * 60)) . "' AND v.lastseen > '" . time2str("%Y-%m-%d", $start - (24 * 60 * 60)) . "'";
      # print(Dumper($query));
      my $sth = $dbh->prepare($query);
      $sth->execute();
      my $ref = $sth->fetchrow_hashref;
      my $currentVmOn = int($ref->{'NUMVMON'});
      # print(Dumper("currentVmOn: $currentVmOn"));
      # Retrieve current statistices for compute (cpu and memory)
      $query = "SELECT ROUND(SUM(h.memory)/1024/1024,0) AS MEMCAPA, SUM(h.cpumhz * h.numcpucore) AS CPUCAPA, SUM(hm.cpuUsage) AS CPUUSAGE, SUM(hm.memoryUsage) AS MEMUSAGE FROM hosts AS h INNER JOIN clusters AS c ON (h.cluster = c.id) INNER JOIN hostMetrics AS hm ON (hm.host_id = h.id) WHERE $CPquery AND h.firstseen < '" . time2str("%Y-%m-%d", $start - (24 * 60 * 60)) . "' AND h.lastseen > '" . time2str("%Y-%m-%d", $start - (24 * 60 * 60)) . "' AND hm.id IN (SELECT MAX(id) FROM hostMetrics WHERE lastseen < '" . time2str("%Y-%m-%d", $start - (24 * 60 * 60)) . " 23:59:59' GROUP BY host_id)";
      $sth = $dbh->prepare($query);
      $sth->execute();
      $ref = $sth->fetchrow_hashref;
      my $currentMemCapacity = $ref->{'MEMCAPA'};
      my $currentCpuCapacity = $ref->{'CPUCAPA'};
      my $currentMemUsage = $ref->{'MEMUSAGE'};
      my $currentCpuUsage = $ref->{'CPUUSAGE'};
      next if (!defined($currentMemCapacity) || !defined($currentCpuCapacity) || !defined($currentMemUsage) || !defined($currentCpuUsage));
      my $currentMemUsagePct = int(100 * ($currentMemUsage / $currentMemCapacity) + 0.5);
      my $currentCpuUsagePct = int(100 * ($currentCpuUsage / $currentCpuCapacity) + 0.5);
      # print(Dumper("currentMemCapacity: $currentMemCapacity"));
      # print(Dumper("currentCpuCapacity: $currentCpuCapacity"));
      # print(Dumper("currentMemUsage: $currentMemUsage"));
      # print(Dumper("currentCpuUsage: $currentCpuUsage"));
      # print(Dumper("currentMemUsagePct: $currentMemUsagePct"));
      # print(Dumper("currentCpuUsagePct: $currentCpuUsagePct"));
      # Retrieve current statistices for storage
      $query = "SELECT SUM(size) AS STORAGECAPA, SUM(freespace) AS STORAGEFREE FROM (SELECT DISTINCT c.cluster_name, d.datastore_name, dm.size, dm.freespace FROM clusters AS c INNER JOIN hosts AS h ON c.id = h.cluster INNER JOIN datastoreMappings AS dma ON h.id = dma.host_id INNER JOIN datastores AS d ON dma.datastore_id = d.id INNER JOIN datastoreMetrics AS dm ON dm.datastore_id = d.id WHERE $CPquery AND d.firstseen < '" . time2str("%Y-%m-%d", $start - (24 * 60 * 60)) . "' AND d.lastseen > '" . time2str("%Y-%m-%d", $start - (24 * 60 * 60)) . "' AND dm.id IN (SELECT MAX(id) FROM datastoreMetrics WHERE lastseen < '" . time2str("%Y-%m-%d", $start - (24 * 60 * 60)) . "' GROUP BY datastore_id) ) AS T1";
      $sth = $dbh->prepare($query);
      $sth->execute();
      $ref = $sth->fetchrow_hashref;
      my $currentStorageCapacity = $ref->{'STORAGECAPA'};
      my $currentStorageUsage = $currentStorageCapacity - $ref->{'STORAGEFREE'};
      my $currentStorageUsagePct = int(100 * ($currentStorageUsage / $currentStorageCapacity) + 0.5);
      my $currentMaxUsagePct = max ($currentMemUsagePct, $currentCpuUsagePct);
      my $currentVmLeft = int(min(((($CPGroup->{'percentageThreshold'} - $safetyPct) * $currentVmOn / $currentMaxUsagePct) - $currentVmOn),((90 * $currentVmOn / $currentStorageUsagePct) - $currentVmOn))+ 0.5);
      my $currentVmMemUsage = int($currentMemUsage / $currentVmOn + 0.5);
      my $currentVmCpuUsage = int($currentCpuUsage / $currentVmOn + 0.5);
      my $currentVmStorageUsage = int($currentStorageUsage / $currentVmOn + 0.5);
      # print(Dumper("vmleftcompute:".((($CPGroup->{'percentageThreshold'} - $safetyPct) * $currentVmOn / $currentMaxUsagePct) - $currentVmOn)));
      # print(Dumper("vmleftstorage:".((90 * $currentVmOn / $currentStorageUsagePct) - $currentVmOn)));
      # print(Dumper("currentStorageCapacity: $currentStorageCapacity"));
      # print(Dumper("currentStorageUsage: $currentStorageUsage"));
      # print(Dumper("currentStorageUsagePct: $currentStorageUsagePct"));
      # print(Dumper("currentMaxUsagePct: $currentMaxUsagePct"));
      # print(Dumper("currentVmLeft: $currentVmLeft"));
      # print(Dumper("currentVmMemUsage: $currentVmMemUsage"));
      # print(Dumper("currentVmCpuUsage: $currentVmCpuUsage"));
      # print(Dumper("currentVmStorageUsage: $currentVmStorageUsage"));
      # Retrieve previous statistices based on $capacityPlanningDays for compute (cpu and memory)
      $query = "SELECT COUNT(v.id) AS NUMVMON FROM vms AS v INNER JOIN hosts AS h ON (h.id = v.host) INNER JOIN clusters AS c ON (c.id = h.cluster) WHERE $CPquery AND v.firstseen < '" . time2str("%Y-%m-%d", $start - ($capacityPlanningDays * 24 * 60 * 60)) . "' AND v.lastseen > '" . time2str("%Y-%m-%d", $start - ($capacityPlanningDays * 24 * 60 * 60)) . "'";
      $sth = $dbh->prepare($query);
      $sth->execute();
      $ref = $sth->fetchrow_hashref;
      my $previousVmOn = int($ref->{'NUMVMON'});
      # print(Dumper("previousVmOn: $previousVmOn"));
      $query = "SELECT ROUND(SUM(h.memory)/1024/1024,0) AS MEMCAPA, SUM(h.cpumhz * h.numcpucore) AS CPUCAPA, SUM(hm.cpuUsage) AS CPUUSAGE, SUM(hm.memoryUsage) AS MEMUSAGE FROM hosts AS h INNER JOIN clusters AS c ON (h.cluster = c.id) INNER JOIN hostMetrics AS hm ON (hm.host_id = h.id) WHERE $CPquery AND h.firstseen < '" . time2str("%Y-%m-%d", $start - ($capacityPlanningDays * 24 * 60 * 60)) . "' AND h.lastseen > '" . time2str("%Y-%m-%d", $start - ($capacityPlanningDays * 24 * 60 * 60)) . "' AND hm.id IN (SELECT MAX(id) FROM hostMetrics WHERE lastseen < '" . time2str("%Y-%m-%d", $start - ($capacityPlanningDays * 24 * 60 * 60)) . " 23:59:59' GROUP BY host_id)";
      $sth = $dbh->prepare($query);
      $sth->execute();
      $ref = $sth->fetchrow_hashref;
      my $previousMemCapacity = $ref->{'MEMCAPA'};
      my $previousCpuCapacity = $ref->{'CPUCAPA'};
      my $previousMemUsage = $ref->{'MEMUSAGE'};
      my $previousCpuUsage = $ref->{'CPUUSAGE'};
      next if (!defined($previousMemCapacity) || !defined($previousCpuCapacity) || !defined($previousMemUsage) || !defined($previousCpuUsage));
      my $previousMemUsagePct = int(100 * ($previousMemUsage / $previousMemCapacity) + 0.5);
      my $previousCpuUsagePct = int(100 * ($previousCpuUsage / $previousCpuCapacity) + 0.5);
      # print(Dumper("previousMemCapacity: $previousMemCapacity"));
      # print(Dumper("previousCpuCapacity: $previousCpuCapacity"));
      # print(Dumper("previousMemUsage: $previousMemUsage"));
      # print(Dumper("previousCpuUsage: $previousCpuUsage"));
      # print(Dumper("previousMemUsagePct: $previousMemUsagePct"));
      # print(Dumper("previousCpuUsagePct: $previousCpuUsagePct"));
      # Retrieve previous statistices for storage
      $query = "SELECT SUM(size) AS STORAGECAPA, SUM(freespace) AS STORAGEFREE FROM (SELECT DISTINCT c.cluster_name, d.datastore_name, dm.size, dm.freespace FROM clusters AS c INNER JOIN hosts AS h ON c.id = h.cluster INNER JOIN datastoreMappings AS dma ON h.id = dma.host_id INNER JOIN datastores AS d ON dma.datastore_id = d.id INNER JOIN datastoreMetrics AS dm ON dm.datastore_id = d.id WHERE $CPquery AND d.firstseen < '" . time2str("%Y-%m-%d", $start - ($capacityPlanningDays * 24 * 60 * 60)) . "' AND d.lastseen > '" . time2str("%Y-%m-%d", $start - ($capacityPlanningDays * 24 * 60 * 60)) . "' AND dm.id IN (SELECT MAX(id) FROM datastoreMetrics WHERE lastseen < '" . time2str("%Y-%m-%d", $start - ($capacityPlanningDays * 24 * 60 * 60)) . "' GROUP BY datastore_id) ) AS T1";
      $sth = $dbh->prepare($query);
      $sth->execute();
      $ref = $sth->fetchrow_hashref;
      my $previousStorageCapacity = $ref->{'STORAGECAPA'};
      my $previousStorageUsage = $previousStorageCapacity - $ref->{'STORAGEFREE'};
      my $previousStorageUsagePct = int(100 * ($previousStorageUsage / $previousStorageCapacity) + 0.5);
      if ($previousStorageUsagePct == 0) { $previousStorageUsagePct = 1; }
      my $previousMaxUsagePct = max ($previousMemUsagePct, $previousCpuUsagePct);
      if ($previousMaxUsagePct == 0) { $previousMaxUsagePct = 1; }
      # print(Dumper("previousStorageCapacity: $previousStorageCapacity"));
      # print(Dumper("previousStorageUsage: $previousStorageUsage"));
      # print(Dumper("previousStorageUsagePct: $previousStorageUsagePct"));
      # print(Dumper("previousMaxUsagePct: $previousMaxUsagePct"));
      my $previousVmLeft = int(min(((($CPGroup->{'percentageThreshold'} - $safetyPct) * $previousVmOn / $previousMaxUsagePct) - $previousVmOn),((90 * $previousVmOn / $previousStorageUsagePct) - $previousVmOn)) + 0.5);
      my $coefficientCapaPlan = ($currentVmLeft-$previousVmLeft)/$capacityPlanningDays;
      # print(Dumper("previousMemUsage: $previousMemUsage"));
      # print(Dumper("previousMemCapacity: $previousMemCapacity"));
      my $daysLeft = "Infinite";
      
      # if VM left count trend is negative, there will an exhaustion, we will compute the days based on this trend, if not we will display 'infinite' icon
      if ($coefficientCapaPlan < 0)
      {
        
        $daysLeft = int(abs($currentVmLeft/$coefficientCapaPlan) + 0.5);
        
      } # END if ($coefficientCapaPlan < 0)
      
      # print(Dumper("daysLeft: $daysLeft"));
      
      my $colorCP = "#5cb85c";
          
      if ($currentVmLeft < $vmLeftThreshold)
      {
        
        $colorCP = "#ffbb33";
        
      } # END if ($currentVmLeft < $vmLeftThreshold)
      
      if ($daysLeft ne "Infinite" && $daysLeft < $daysLeftThreshold)
      {
        
        $colorCP = "#d9534f";
        
      } # END if ($daysLeft < $daysLeftThreshold)

      push @groups, { title => $CPGroup->{'group_name'}, daysLeft => $daysLeft, vmLeft => $currentVmLeft, cpu => format_bytes($currentVmCpuUsage*1000*1000)."Hz", mem => format_bytes($currentVmMemUsage*1024*1024)."B", hdd => format_bytes($currentVmStorageUsage)."B", maxpct =>  $CPGroup->{'percentageThreshold'}, color => $colorCP };
      $numGroup++;
      
    } # END while (my $CPGroup = $sthCPG->fetchrow_hashref)
    
    if ($numGroup > 0)
    {
      
      # Once we retrieved data, we send the report by mail using responsive template
      my $params = { 'groups' => \@groups };
      $options{INCLUDE_PATH} = '/var/www/admin/mail-template';
      my $msg = MIME::Lite::TT::HTML->new(
        From        =>  $senderMail,
        To          =>  $recipientMail,
        Subject     =>  '['.uc($HOSTNAME).'] Capacity Planning Report',
        Template    =>  { html => 'capacityplanning.html' },
        TmplOptions =>  \%options,
        TmplParams  =>  $params,
      );
      $msg->send('smtp', $smtpAddress, Timeout => 60 );
      
    } # END if ($numGroup > 0)

  } # END if ($capacityPlanningExecuted == 0)
  
} # END sub capacityPlanningReport

sub mailAlert
{
  
  # code
  
} # END sub mailAlert