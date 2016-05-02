#!/usr/bin/perl -w

use strict;
use warnings;
use VMware::VIRuntime;
use VMware::VICredStore;
use JSON;
use Data::Dumper;
use Date::Format qw(time2str);
use URI::URL;
use Log::Log4perl qw(:easy);
use Number::Bytes::Human qw(format_bytes);
use POSIX qw(strftime);
use XML::LibXML;
use File::Path qw( make_path );
use Switch;
use Getopt::Long;


use Socket;


# TODO
# check for multiple run, prevent simultaneous execution


my $start = time;

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
my $xmlModuleFile = '/var/www/admin/conf/modules.xml';
my $xmlModuleScheduleFile = '/var/www/admin/conf/moduleschedules.xml';
my $xmlConfigsFile = '/var/www/admin/conf/configs.xml';
my $schedulerTTBFile = '/opt/vcron/scheduler-ttb.xml';

# Using --force switch will bypass scheduler and run every subroutine
my $force;
GetOptions("force"  => \$force);
if ($force) { $logger->info ("[DEBUG] Force Mode enable, all checks will be run whatever their schedule!"); }

# global variables to store view objects
my ($view_Datacenter, $view_ClusterComputeResource, $view_VirtualMachine, $view_HostSystem, $view_Datastore, $view_DistributedVirtualPortgroup);

my ($alarm_key,$alarm_state,$alarm_name,$alarm_entity) = ("Alarm Key","Alarm State", "Alarm Name", "Alarm Entity");

# hastables
my %h_cluster = ("domain-c000" => "N/A");
my %h_host = ();
my %h_hostcluster = ();

# requiring both file to be readable
(-r $xmlModuleFile) or $logger->logdie ("[ERROR] File $xmlModuleFile not available and/or readable, abort");
(-r $xmlModuleScheduleFile) or $logger->logdie ("[ERROR] File $xmlModuleScheduleFile not available and/or readable, abort");
(-r $xmlConfigsFile) or $logger->logdie ("[ERROR] File $xmlConfigsFile not available and/or readable, abort");
(-w $schedulerTTBFile) or $logger->logdie ("[ERROR] File $schedulerTTBFile not available and/or writeable, abort");

# modules and settings xml file initialize
#my $parser = XML::LibXML->new();
my $docModule = XML::LibXML->new->parse_file($xmlModuleFile);
my $docModuleSchedules = XML::LibXML->new->parse_file($xmlModuleScheduleFile);
my $docConfigs = XML::LibXML->new->parse_file($xmlConfigsFile);

# Data purge threshold
my $purgeThreshold = 0;
$purgeThreshold = $docConfigs->findvalue("//config[id='thresholdHistory']/value");

# date schedule
my $dailySchedule = ($docConfigs->exists("/configs/config[id='dailySchedule']/value") ? $docConfigs->findvalue("/configs/config[id='dailySchedule']/value") : 0);
my $weeklySchedule = ($docConfigs->exists("/configs/config[id='weeklySchedule']/value") ? $docConfigs->findvalue("/configs/config[id='weeklySchedule']/value") : 0);
my $monthlySchedule = ($docConfigs->exists("/configs/config[id='monthlySchedule']/value") ? $docConfigs->findvalue("/configs/config[id='monthlySchedule']/value") : 1);

# browsing modules and fetching schedule
$logger->info("[INFO] Start processing modules list");
foreach my $node ($docModule->findnodes('/modules/category/module')) {
	my $moduleName = $node->findvalue('./id');
	my $scheduleModule = $docModuleSchedules->findvalue("/modules/module/id[text()='".$moduleName."']/../schedule");
	if ($scheduleModule ne 'off') {
		$href->{ $moduleName } = $scheduleModule;
		$logger->info("[INFO] Found module $moduleName with schedule $scheduleModule");
	} else {
		$logger->info("[INFO] Found module $moduleName with schedule $scheduleModule, skipping...");
	}
}

# fetching active modules
my $nbActiveModule = scalar keys(%$href);
$logger->info("[INFO] End processing modules list, found $nbActiveModule active modules");

# exiting if no active module
($nbActiveModule gt 0) or $logger->logdie ("[ERROR] No active module found, abort");

# datetime used for folder history management
my $execDateTTB = time2str("%Y%m%d%H%M", time);
my $execDate = time2str("%Y%m%d", time);


######################
###                ###
###  EDITING ZONE  ###
###                ###
######################

############################################
# XML files definition (1 module = 1 file) #
############################################

# TODO
# only create folder if files to dump are not empty, in order to avoir empty folders
my $xmlPath = "/opt/vcron/data/$execDate";
if ( !-d $xmlPath ) { make_path $xmlPath or $logger->logdie("[ERROR] Failed to create path: $xmlPath"); }
### VirtualMachine
my $xmlVMs = "$xmlPath/vms-global.xml";
my $docVMs = XML::LibXML::Document->new('1.0', 'utf-8');
my $rootVMs = $docVMs->createElement("vms");
### HostSystem
my $xmlHosts = "$xmlPath/hosts-global.xml";
my $docHosts = XML::LibXML::Document->new('1.0', 'utf-8');
my $rootHosts = $docHosts->createElement("hosts");
### ClusterComputeResource
my $xmlClusters = "$xmlPath/clusters-global.xml";
my $docClusters = XML::LibXML::Document->new('1.0', 'utf-8');
my $rootClusters = $docClusters->createElement("clusters");
### Datastore
my $xmlDatastores = "$xmlPath/datastores-global.xml";
my $docDatastores = XML::LibXML::Document->new('1.0', 'utf-8');
my $rootDatastores = $docDatastores->createElement("datastores");
### Alarms
my $xmlAlarms = "$xmlPath/alarms-global.xml";
my $docAlarms = XML::LibXML::Document->new('1.0', 'utf-8');
my $rootAlarms = $docAlarms->createElement("alarms");
### Snapshots
my $xmlSnapshots = "$xmlPath/snapshots-global.xml";
my $docSnapshots = XML::LibXML::Document->new('1.0', 'utf-8');
my $rootSnapshots = $docSnapshots->createElement("snapshots");
### DistributedVirtualPortgroups
my $xmlDistributedVirtualPortgroups = "$xmlPath/distributedvirtualportgroups-global.xml";
my $docDistributedVirtualPortgroups = XML::LibXML::Document->new('1.0', 'utf-8');
my $rootDistributedVirtualPortgroups = $docDistributedVirtualPortgroups->createElement("distributedvirtualportgroups");
### vCenter Sessions
my $xmlSessions = "$xmlPath/sessions-global.xml";
my $docSessions = XML::LibXML::Document->new('1.0', 'utf-8');
my $rootSessions = $docSessions->createElement("sessions");
### vCenter Licenses
my $xmlLicenses = "$xmlPath/licenses-global.xml";
my $docLicenses = XML::LibXML::Document->new('1.0', 'utf-8');
my $rootLicenses = $docLicenses->createElement("licenses");
### vCenter Certificates
my $xmlCertificates = "$xmlPath/certificates-global.xml";
my $docCertificates = XML::LibXML::Document->new('1.0', 'utf-8');
my $rootCertificates = $docCertificates->createElement("certificates");
### Hardware Status error
my $xmlHardwareStatus = "$xmlPath/hardwarestatus-global.xml";
my $docHardwareStatus = XML::LibXML::Document->new('1.0', 'utf-8');
my $rootHardwareStatus = $docHardwareStatus->createElement("hardwarestatus");
### Hardware Status error
my $xmlConfigurationIssues = "$xmlPath/configurationissues-global.xml";
my $docConfigurationIssues = XML::LibXML::Document->new('1.0', 'utf-8');
my $rootConfigurationIssues = $docConfigurationIssues->createElement("configurationissues");


###########################################################
# dispatch table for subroutine (1 module = 1 subroutine) #
###########################################################
my %actions = ( inventory => \&inventory,
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
    my $command = `find /opt/vcron/data/ -type d -ctime +$purgeThreshold -exec rm -rf {} \\;`;
    $logger->info("[INFO][PURGE] Purge return: $command");
    $logger->info("[INFO][PURGE] End purge process");
}

# TODO
# plan to kill some previous execution if it's hang

VMware::VICredStore::init (filename => $filename) or $logger->logdie ("[ERROR] Unable to initialize Credential Store.");
@server_list = VMware::VICredStore::get_hosts ();
foreach $s_item (@server_list) {
	$logger->info("[INFO][VCENTER] Start processing vCenter $s_item");
	my $normalizedServerName = $s_item;
	@user_list = VMware::VICredStore::get_usernames (server => $s_item);
	if (scalar @user_list == 0) {
		$logger->error ("[ERROR] No credential store user detected for $s_item");
        next;
	} elsif (scalar @user_list > 1) {
		$logger->error ("[ERROR] Multiple credential store user detected for $s_item");
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

    # TODO: check version
    # TODO: watchdog

	# vCenter connection should be OK at this point
	# generating meta objects
	$logger->info("[INFO][OBJECTS] Start retrieving ClusterComputeResource objects");
	$view_ClusterComputeResource = Vim::find_entity_views(view_type => 'ClusterComputeResource', properties => ['name', 'host', 'summary', 'configIssue']);
	$logger->info("[INFO][OBJECTS] End retrieving ClusterComputeResource objects");
	#$logger->info("[INFO] Start retrieving ComputeResource objects");
	#my $view_ComputeResource = Vim::find_entity_views(view_type => 'ComputeResource');
	#$logger->info("[INFO] End retrieving ComputeResource objects");
	$logger->info("[INFO][OBJECTS] Start retrieving HostSystem objects");
	$view_HostSystem = Vim::find_entity_views(view_type => 'HostSystem', properties => ['name', 'config.dateTimeInfo.ntpConfig.server', 'config.network.dnsConfig', 'config.powerSystemInfo.currentPolicy.shortName', 'configIssue', 'configManager.advancedOption', 'configManager.healthStatusSystem', 'configManager.storageSystem', 'configManager.serviceSystem', 'runtime.inMaintenanceMode', 'summary.config.product.fullName', 'summary.hardware.cpuMhz', 'summary.hardware.cpuModel', 'summary.hardware.memorySize', 'summary.hardware.model', 'summary.hardware.numCpuCores', 'summary.hardware.numCpuPkgs', 'summary.rebootRequired']);
	$logger->info("[INFO][OBJECTS] End retrieving HostSystem objects");
	$logger->info("[INFO][OBJECTS] Start retrieving DistributedVirtualPortgroup objects");
	$view_DistributedVirtualPortgroup = Vim::find_entity_views(view_type => 'DistributedVirtualPortgroup', properties => ['name', 'vm', 'config.numPorts', 'config.autoExpand', 'tag']);
	$logger->info("[INFO][OBJECTS] End retrieving DistributedVirtualPortgroup objects");
	$logger->info("[INFO][OBJECTS] Start retrieving Datastore objects");
	$view_Datastore = Vim::find_entity_views(view_type => 'Datastore', properties => ['name', 'summary', 'iormConfiguration']);
	$logger->info("[INFO][OBJECTS] End retrieving Datastore objects");
    #$logger->info("[INFO] Start retrieving StoragePod objects");
	#my $view_StoragePod = Vim::find_entity_views(view_type => 'StoragePod');
	#$logger->info("[INFO] End retrieving StoragePod objects");
	$logger->info("[INFO][OBJECTS] Start retrieving Datacenter objects");
	$view_Datacenter = Vim::find_entity_views(view_type => 'Datacenter', properties => ['name','triggeredAlarmState']);
	$logger->info("[INFO][OBJECTS] End retrieving Datacenter objects");
	$logger->info("[INFO][OBJECTS] Start retrieving VirtualMachine objects");
	$view_VirtualMachine = Vim::find_entity_views(view_type => 'VirtualMachine', properties => ['name','guest','summary.config.vmPathName','config.guestId','runtime','network','summary.config.numCpu','summary.config.memorySizeMB','summary.storage','triggeredAlarmState','config.hardware.device','config.version','resourceConfig','config.cpuHotAddEnabled','config.memoryHotAddEnabled','config.extraConfig','summary.quickStats','snapshot']);
	$logger->info("[INFO][OBJECTS] End retrieving VirtualMachine objects");
	# hastables creation to speed later queries
	foreach my $cluster_view (@$view_ClusterComputeResource) {
		my $cluster_name = lc ($cluster_view->name);
		$h_cluster{%$cluster_view{'mo_ref'}->value} = $cluster_name;
		my $cluster_hosts_views = Vim::find_entity_views(view_type => 'HostSystem', begin_entity => $cluster_view , properties => [ 'name' ]);
		foreach my $cluster_host_view (@$cluster_hosts_views) {
    	my $host_name = lc ($cluster_host_view->{'name'});
	    $h_host{%$cluster_host_view{'mo_ref'}->value} = $host_name;
			$h_hostcluster{%$cluster_host_view{'mo_ref'}->value} = %$cluster_view{'mo_ref'}->value;
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
	#
	# certificatesReport();
	# exit;

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
sub xmlDump {
	my ($docXML, $obj, $xmlObject, $xmlFile) = @_;
	if ($docXML->findvalue("count(//".$obj.")") > 0) {
		if ($docXML->toFile($xmlObject, 2)) {
			$logger->info("[INFO][XMLDUMP] Saving file $xmlObject");
		} else {
			$logger->error("[ERROR][XMLDUMP] Unable to save file $xmlObject");
		}
		unlink($xmlFile);
		symlink($xmlObject, $xmlFile);
		chmod 0644, $xmlObject;
	} else {
		$logger->info("[DEBUG][XMLDUMP] No objects for type $obj");
	}
}

xmlDump($docVMs, "vm", $xmlVMs, "/opt/vcron/data/latest/vms-global.xml");
xmlDump($docHosts, "host", $xmlHosts, "/opt/vcron/data/latest/hosts-global.xml");
xmlDump($docClusters, "cluster", $xmlClusters, "/opt/vcron/data/latest/clusters-global.xml");
xmlDump($docDatastores, "datastore", $xmlDatastores, "/opt/vcron/data/latest/datastores-global.xml");
xmlDump($docAlarms, "alarm", $xmlAlarms, "/opt/vcron/data/latest/alarms-global.xml");
xmlDump($docSnapshots, "snapshot", $xmlSnapshots, "/opt/vcron/data/latest/snapshots-global.xml");
xmlDump($docDistributedVirtualPortgroups, "distributedvirtualportgroup", $xmlDistributedVirtualPortgroups, "/opt/vcron/data/latest/distributedvirtualportgroups-global.xml");
xmlDump($docSessions, "session", $xmlSessions, "/opt/vcron/data/latest/sessions-global.xml");
xmlDump($docLicenses, "license", $xmlLicenses, "/opt/vcron/data/latest/licenses-global.xml");
xmlDump($docCertificates, "certificate", $xmlCertificates, "/opt/vcron/data/latest/certificates-global.xml");
xmlDump($docHardwareStatus, "hardwarestate", $xmlHardwareStatus, "/opt/vcron/data/latest/hardwarestatus-global.xml");
xmlDump($docConfigurationIssues, "configurationissue", $xmlConfigurationIssues, "/opt/vcron/data/latest/configurationissues-global.xml");

my $ttbParser = XML::LibXML->new();
$ttbParser->keep_blanks(0);
my $docTTB = $ttbParser->load_xml(location => $schedulerTTBFile);
my $executionEntry = $docTTB->ownerDocument->createElement('executiontime');
$executionEntry->setAttribute("date", $execDateTTB);
$executionEntry->setAttribute("seconds", time - $start);
$docTTB->documentElement()->appendChild($executionEntry);
$docTTB->toFile($schedulerTTBFile,2);


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
        my %h_session = (
            loginTime => substr($session->loginTime, 0, 19),
            vcenter => $vcentersdk->host,
            userAgent => (defined($session->userAgent) ? $session->userAgent : 'N/A'),
            ipAddress => (defined($session->ipAddress) ? $session->ipAddress : 'N/A'),
            lastActiveTime => substr($session->lastActiveTime, 0, 19),
            userName => $session->userName
        );
        my $sessionNode = $docSessions->createElement("session");
        for my $sessionProperty (keys %h_session) {
            my $sessionNodeProperty = $docSessions->createElement($sessionProperty);
            my $value = $h_session{$sessionProperty};
            $sessionNodeProperty->appendTextNode($value);
            $sessionNode->appendChild($sessionNodeProperty);
        }
        $rootSessions->appendChild($sessionNode);
    }
    $docSessions->setDocumentElement($rootSessions);
}

sub licenseReport {
    my $licMgr = Vim::get_view(mo_ref => Vim::get_service_content()->licenseManager);
    my $installedLicenses = $licMgr->licenses;
    my $vcentersdk = new URI::URL $licMgr->{'vim'}->{'service_url'};

    foreach my $license (@$installedLicenses) {
        # we don't want evaluation license to be stored
        if ($license->editionKey ne 'eval') {
            my %h_license = (
                total => $license->total,
                vcenter => $vcentersdk->host,
                name => $license->name,
                licenseKey => $license->licenseKey,
                editionKey => $license->editionKey,
                used => $license->used,
                costUnit => $license->costUnit
            );
            my $licenseNode = $docLicenses->createElement("license");
            for my $licenseProperty (keys %h_license) {
                my $licenseNodeProperty = $docLicenses->createElement($licenseProperty);
                my $value = $h_license{$licenseProperty};
                $licenseNodeProperty->appendTextNode($value);
                $licenseNode->appendChild($licenseNodeProperty);
            }
           $rootLicenses->appendChild($licenseNode);
        }
    }
    $docLicenses->setDocumentElement($rootLicenses);
}

sub certificatesReport {
    # my $sessionMgr = Vim::get_view(mo_ref => Vim::get_service_content()->sessionManager);
    # my $sessionList = eval {$sessionMgr->sessionList || []};
    # my $currentSessionkey = $sessionMgr->currentSession->key;
    # my $vcentersdk = new URI::URL $sessionMgr->{'vim'}->{'service_url'};
		#
    # foreach my $session (@$sessionList) {
    #     my %h_session = (
    #         loginTime => substr($session->loginTime, 0, 19),
    #         vcenter => $vcentersdk->host,
    #         userAgent => (defined($session->userAgent) ? $session->userAgent : 'N/A'),
    #         ipAddress => (defined($session->ipAddress) ? $session->ipAddress : 'N/A'),
    #         lastActiveTime => substr($session->lastActiveTime, 0, 19),
    #         userName => $session->userName
    #     );
    #     my $sessionNode = $docSessions->createElement("session");
    #     for my $sessionProperty (keys %h_session) {
    #         my $sessionNodeProperty = $docSessions->createElement($sessionProperty);
    #         my $value = $h_session{$sessionProperty};
    #         $sessionNodeProperty->appendTextNode($value);
    #         $sessionNode->appendChild($sessionNodeProperty);
    #     }
    #     $rootSessions->appendChild($sessionNode);
    # }
    # $docSessions->setDocumentElement($rootSessions);
}

sub inventory {
	# cluster inventory must be done after vm and host ones, as it uses some of their data
  vminventory( );
  hostinventory( );
  clusterinventory( );
  datastoreinventory( );
  dvpginventory( );
}

sub vminventory {
	foreach my $vm_view (@$view_VirtualMachine) {
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
    my $vm_toolsVersion = "Not Available";
    if(defined($vm_view->guest) && defined($vm_view->guest->toolsVersion)) { $vm_toolsVersion = $vm_view->guest->toolsVersion; }
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
		my %h_vm = (
			name => $vm_view->name,
			vcenter => $vcentersdk->host,
			cluster => $h_cluster{($h_hostcluster{$vm_view->runtime->host->value} ? $h_hostcluster{$vm_view->runtime->host->value} : "domain-c000")},
			host => $h_host{$vm_view->runtime->host->value},
			vmxpath => $vm_view->{'summary.config.vmPathName'},
			portgroup => join(',', @vm_pg_string),
			ip => join(',', @vm_ip_string),
			numcpu => ($vm_view->{'summary.config.numCpu'} ? $vm_view->{'summary.config.numCpu'} : "N/A"),
			memory => ($vm_view->{'summary.config.memorySizeMB'} ? $vm_view->{'summary.config.memorySizeMB'} : "N/A"),
			commited => int($vm_view->{'summary.storage'}->committed / 1073741824),
			uncommited => int($vm_view->{'summary.storage'}->uncommitted / 1073741824),
			provisionned => int(($vm_view->{'summary.storage'}->committed + $vm_view->{'summary.storage'}->uncommitted) / 1073741824),
			datastore => (split /\[/, (split /\]/, $vm_view->{'summary.config.vmPathName'})[0])[1],
			mac => join(',', @vm_mac),
			guestOS => $vm_guestfullname,
			guestId => $vm_guestId,
			configGuestId => $vm_configGuestId,
      guestFamily =>  $vm_guestFamily,
      moref => $vm_view->{'mo_ref'}->{'type'}."-".$vm_view->{'mo_ref'}->{'value'},
      powerState => $vm_view->runtime->powerState->val,
      fqdn => $vm_guestHostName,
      removable => $removableExist,
      hwversion => $vm_view->{'config.version'},
      vmtools => $vm_toolsVersion,
      consolidationNeeded => (defined($vm_view->runtime->consolidationNeeded) ? $vm_view->runtime->consolidationNeeded : 0),
      cpuReservation => $vm_view->resourceConfig->cpuAllocation->reservation,
      cpuLimit => $vm_view->resourceConfig->cpuAllocation->limit,
      memReservation => $vm_view->resourceConfig->memoryAllocation->reservation,
      memLimit => $vm_view->resourceConfig->memoryAllocation->limit,
      cpuHotAddEnabled => (defined($vm_view->{'config.cpuHotAddEnabled'}) ? $vm_view->{'config.cpuHotAddEnabled'} : 0),
      memHotAddEnabled => (defined($vm_view->{'config.memoryHotAddEnabled'}) ? $vm_view->{'config.memoryHotAddEnabled'} : 0),
      sharedBus => $sharedBus,
      multiwriter => $multiwriter,
      swappedMemory => 1048576*$vm_view->{'summary.quickStats'}->swappedMemory,
      balloonedMemory => 1048576*$vm_view->{'summary.quickStats'}->balloonedMemory,
      compressedMemory => 1024*$vm_view->{'summary.quickStats'}->compressedMemory,
      connectionState => $vm_view->runtime->connectionState->val,
      phantomSnapshot => $phantomSnapshot
		);
		my $vmNode = $docVMs->createElement("vm");
		for my $vmProperty (keys %h_vm) {
			my $vmNodeProperty = $docVMs->createElement($vmProperty);
			my $value = $h_vm{$vmProperty};
			$vmNodeProperty->appendTextNode($value);
			$vmNode->appendChild($vmNodeProperty);
		}
		$rootVMs->appendChild($vmNode);
        if ($vm_view->snapshot) {
            foreach (@{$vm_view->snapshot->rootSnapshotList}) {
                getSnapshots($_, $vcentersdk->host, $vm_view->name);
            }
        }
	}
	$docVMs->setDocumentElement($rootVMs);
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
		my $ntpservers = $host_view->{'config.dateTimeInfo.ntpConfig.server'};
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
    my %h_host = (
      name => $host_view->name,
      vcenter => $vcentersdk->host,
      numcpu => $host_view->{'summary.hardware.numCpuPkgs'},
      numcpucore => $host_view->{'summary.hardware.numCpuCores'},
      cpumhz => $host_view->{'summary.hardware.cpuMhz'},
      cputype => $host_view->{'summary.hardware.cpuModel'},
      memory => $host_view->{'summary.hardware.memorySize'},
      esxbuild => $host_view->{'summary.config.product.fullName'},
      model => $host_view->{'summary.hardware.model'},
      lunpathcount => $lunpathcount,
      deadlunpathcount => $lundeadpathcount,
      sharedmemory => 0,
      bandwidthcapacity => 0,
			ssh_policy => $service_ssh,
			shell_policy => $service_shell,
			syslog_target => $syslog_target,
			ntpservers => join(';', sort @$ntpservers),
			dnsservers => join(';', @sorted_dnsservers),
			inmaintenancemode => $host_view->{'runtime.inMaintenanceMode'},
			hostname => $host_view->{'config.network.dnsConfig'}->hostName,
			rebootrequired => $host_view->{'summary.rebootRequired'},
			powerpolicy => (defined($host_view->{'config.powerSystemInfo.currentPolicy.shortName'}) ? $host_view->{'config.powerSystemInfo.currentPolicy.shortName'} : 'off'),
      cluster => (defined($h_hostcluster{$host_view->{'mo_ref'}->{'value'}}) ? $h_cluster{$h_hostcluster{$host_view->{'mo_ref'}->{'value'}}} : 'Standalone'),
      moref => $host_view->{'mo_ref'}->{'type'}."-".$host_view->{'mo_ref'}->{'value'}
    );
    my $hostNode = $docHosts->createElement("host");
    for my $hostProperty (keys %h_host) {
      my $hostNodeProperty = $docHosts->createElement($hostProperty);
      my $value = $h_host{$hostProperty};
      $hostNodeProperty->appendTextNode($value);
      $hostNode->appendChild($hostNodeProperty);
    }
    $rootHosts->appendChild($hostNode);
  }
  $docHosts->setDocumentElement($rootHosts);
}

sub clusterinventory {
  foreach my $cluster_view (@$view_ClusterComputeResource) {
    my $lastconfigissue = 0;
    my $lastconfigissuetime = 0;
    if (defined($cluster_view->configIssue)){
      foreach my $issue ( sort {$b->key cmp $a->key} @{$cluster_view->configIssue}) {
        $lastconfigissue = $issue->fullFormattedMessage;
        $lastconfigissuetime = substr($issue->createdTime, 0, 19);
        last;
      }
    }
		my $vcpu = $docVMs->findvalue("sum(/vms/vm[cluster=\'".lc($cluster_view->name)."\']/numcpu)");
		my $pcpu = $docHosts->findvalue("sum(/hosts/host[cluster=\'".lc($cluster_view->name)."\']/numcpucore)");
    my $vcentersdk = new URI::URL $cluster_view->{'vim'}->{'service_url'};
    my %h_cluster = (
      name => $cluster_view->name,
      vcenter => $vcentersdk->host,
      vmotion => $cluster_view->summary->numVmotions,
      dasenabled => (defined($cluster_view->summary->dasData) ? 1 : 0),
      lastconfigissue => $lastconfigissue,
      lastconfigissuetime => $lastconfigissuetime,
			vcpu => $vcpu,
			pcpu => $pcpu,
			vp_cpuratio => (($vcpu gt 0 && $pcpu gt 0) ? ($vcpu / $pcpu) : 0),
      moref => $cluster_view->{'mo_ref'}->{'type'}."-".$cluster_view->{'mo_ref'}->{'value'}
    );
    my $clusterNode = $docClusters->createElement("cluster");
    for my $clusterProperty (keys %h_cluster) {
      my $clusterNodeProperty = $docClusters->createElement($clusterProperty);
      my $value = $h_cluster{$clusterProperty};
      $clusterNodeProperty->appendTextNode($value);
      $clusterNode->appendChild($clusterNodeProperty);
    }
    $rootClusters->appendChild($clusterNode);
  }
  $docClusters->setDocumentElement($rootClusters);
}

sub datastoreinventory {
    foreach my $datastore_view (@$view_Datastore) {
        my $vcentersdk = new URI::URL $datastore_view->{'vim'}->{'service_url'};
        my %h_datastore = (
            name => $datastore_view->name,
            vcenter => $vcentersdk->host,
            size => $datastore_view->summary->capacity,
            freespace => $datastore_view->summary->freeSpace,
            uncommitted => (defined($datastore_view->summary->uncommitted) ? $datastore_view->summary->uncommitted : 0),
            type => $datastore_view->summary->type,
            accessible => $datastore_view->summary->accessible,
            iormConfiguration => $datastore_view->iormConfiguration->enabled,
            shared => $datastore_view->summary->multipleHostAccess,
            maintenanceMode => (defined($datastore_view->summary->maintenanceMode) ? $datastore_view->summary->maintenanceMode : 'normal'),
            moref => $datastore_view->{'mo_ref'}->{'type'}."-".$datastore_view->{'mo_ref'}->{'value'}
        );
        my $datastoreNode = $docDatastores->createElement("datastore");
        for my $datastoreProperty (keys %h_datastore) {
            my $datastoreNodeProperty = $docDatastores->createElement($datastoreProperty);
            my $value = $h_datastore{$datastoreProperty};
            $datastoreNodeProperty->appendTextNode($value);
            $datastoreNode->appendChild($datastoreNodeProperty);
        }
        $rootDatastores->appendChild($datastoreNode);
    }
    $docDatastores->setDocumentElement($rootDatastores);
}

sub getHardwareStatus {
	foreach my $host_view (@$view_HostSystem) {
	    my $healthStatusSystem = Vim::get_view(mo_ref => $host_view->{'configManager.healthStatusSystem'});
	    my $vcentersdk = new URI::URL $host_view->{'vim'}->{'service_url'};
			my @h_hwissues = ();
			my %h_hwissue;
			if ($healthStatusSystem->runtime) {
				if ($healthStatusSystem->runtime->hardwareStatusInfo) {
					my $cpuStatus = $healthStatusSystem->runtime->hardwareStatusInfo->cpuStatusInfo;
					foreach(@$cpuStatus) {
						if(lc($_->status->key) ne 'green' && lc($_->status->key) ne 'unknown') {
							%h_hwissue = (
	                type => "cpu",
	                name => $_->name,
	                state => lc($_->status->key)
	            );
							push( @h_hwissues, \%h_hwissue );
						}
					}
					my $memStatus = $healthStatusSystem->runtime->hardwareStatusInfo->memoryStatusInfo;
					foreach(@$memStatus) {
						if(lc($_->status->key) ne 'green' && lc($_->status->key) ne 'unknown') {
							%h_hwissue = (
	                type => "memory",
	                name => $_->name,
	                state => lc($_->status->key)
	            );
							push( @h_hwissues, \%h_hwissue );
						}
					}
					my $storageStatus = $healthStatusSystem->runtime->hardwareStatusInfo->storageStatusInfo;
					foreach(@$storageStatus) {
						if(lc($_->status->key) ne 'green' && lc($_->status->key) ne 'unknown') {
							%h_hwissue = (
	                type => "storage",
	                name => $_->name,
	                state => lc($_->status->key)
	            );
							push( @h_hwissues, \%h_hwissue );
						}
					}
				}
				if($healthStatusSystem->runtime->systemHealthInfo) {
					my $sensorInfo = $healthStatusSystem->runtime->systemHealthInfo->numericSensorInfo;
					foreach(@$sensorInfo) {
						if($_->healthState && lc($_->healthState->key) ne 'green' && lc($_->healthState->key) ne 'unknown') {
							%h_hwissue = (
									type => $_->sensorType,
									name => $_->name,
									state => lc($_->healthState->key)
							);
							push( @h_hwissues, \%h_hwissue );
						}
					}
				}
			}
			foreach my $hwissue (@h_hwissues) {
		    my %h_hardwarestatus = (
          name => $host_view->name,
					issuetype => $hwissue->{'type'},
					issuename => $hwissue->{'name'},
					issuestate => $hwissue->{'state'},
          cluster => (defined($h_hostcluster{$host_view->{'mo_ref'}->{'value'}}) ? $h_cluster{$h_hostcluster{$host_view->{'mo_ref'}->{'value'}}} : 'Standalone'),
          vcenter => $vcentersdk->host,
		      moref => $host_view->{'mo_ref'}->{'type'}."-".$host_view->{'mo_ref'}->{'value'}
		    );
		    my $hardwareStatusNode = $docHardwareStatus->createElement("hardwarestate");
		    for my $hardwareStatusProperty (keys %h_hardwarestatus) {
		        my $hardwareStatusNodeProperty = $docHardwareStatus->createElement($hardwareStatusProperty);
		        my $value = $h_hardwarestatus{$hardwareStatusProperty};
		        $hardwareStatusNodeProperty->appendTextNode($value);
		        $hardwareStatusNode->appendChild($hardwareStatusNodeProperty);
		    }
		    $rootHardwareStatus->appendChild($hardwareStatusNode);
			}
	}
	$docHardwareStatus->setDocumentElement($rootHardwareStatus);
}

sub getAlarms {
    foreach my $datacenter_view (@$view_Datacenter) {
        next if(!defined($datacenter_view->triggeredAlarmState));
        foreach my $triggeredAlarm (@{$datacenter_view->triggeredAlarmState}) {
            my $entity = Vim::get_view(mo_ref => $triggeredAlarm->entity, properties => [ 'name' ]);
            my $alarm = Vim::get_view(mo_ref => $triggeredAlarm->alarm, properties => [ 'info.name' ]);
            my $vcentersdk = new URI::URL $datacenter_view->{'vim'}->{'service_url'};
            my %h_alarm = (
                name => $alarm->{'info.name'},
                vcenter => $vcentersdk->host,
                entity_type => $triggeredAlarm->entity->type,
                entity => $entity->name,
                status => $triggeredAlarm->overallStatus->val,
                moref => $alarm->{'mo_ref'}->{'type'}."-".$alarm->{'mo_ref'}->{'value'},
                time => substr($triggeredAlarm->time, 0, 19)
            );
            my $alarmNode = $docAlarms->createElement("alarm");
            for my $alarmProperty (keys %h_alarm) {
                my $alarmNodeProperty = $docAlarms->createElement($alarmProperty);
                my $value = $h_alarm{$alarmProperty};
                $alarmNodeProperty->appendTextNode($value);
                $alarmNode->appendChild($alarmNodeProperty);
            }
            $rootAlarms->appendChild($alarmNode);
        }
    }
    $docAlarms->setDocumentElement($rootAlarms);
}

sub getSnapshots {
    my ($snapshotTree,$vcenterURL,$vmname) = @_;
    my $description = 'Not Available';
    my %h_snapshot = (
        quiesced => $snapshotTree->quiesced,
        vcenter => $vcenterURL,
        createTime => substr($snapshotTree->createTime, 0, 19),
        state => $snapshotTree->state->val,
        name => $snapshotTree->name,
        description => (defined($snapshotTree->description) ? $snapshotTree->description : $description),
        id => $snapshotTree->id,
        moref => $snapshotTree->{'snapshot'}->{'type'}."-".$snapshotTree->{'snapshot'}->{'value'},
        vm => $vmname
    );
    my $snapshotNode = $docSnapshots->createElement("snapshot");
    for my $snapshotProperty (keys %h_snapshot) {
        my $snapshotNodeProperty = $docSnapshots->createElement($snapshotProperty);
        my $value = $h_snapshot{$snapshotProperty};
        $snapshotNodeProperty->appendTextNode($value);
        $snapshotNode->appendChild($snapshotNodeProperty);
    }
    $rootSnapshots->appendChild($snapshotNode);
    $docSnapshots->setDocumentElement($rootSnapshots);

    # recurse through the tree of snaps
    if ($snapshotTree->childSnapshotList) {
        # loop through any children that may exist
        foreach (@{$snapshotTree->childSnapshotList}) {
            getSnapshots($_,$vcenterURL,$vmname);
        }
    }
}

sub getConfigurationIssue {
	foreach my $host_view (@$view_HostSystem) {
		my $vcentersdk = new URI::URL $host_view->{'vim'}->{'service_url'};
		foreach ($host_view->configIssue) {
			if (defined(@$_[0])) {
				my %h_configurationissue = (
					name => $host_view->name,
					configissue => @$_[0]->fullFormattedMessage,
					cluster => (defined($h_hostcluster{$host_view->{'mo_ref'}->{'value'}}) ? $h_cluster{$h_hostcluster{$host_view->{'mo_ref'}->{'value'}}} : 'Standalone'),
					vcenter => $vcentersdk->host,
					moref => $host_view->{'mo_ref'}->{'type'}."-".$host_view->{'mo_ref'}->{'value'}
				);
				my $configurationIssueNode = $docConfigurationIssues->createElement("configurationissue");
				for my $configurationIssueProperty (keys %h_configurationissue) {
						my $configurationIssueNodeProperty = $docConfigurationIssues->createElement($configurationIssueProperty);
						my $value = $h_configurationissue{$configurationIssueProperty};
						$configurationIssueNodeProperty->appendTextNode($value);
						$configurationIssueNode->appendChild($configurationIssueNodeProperty);
				}
				$rootConfigurationIssues->appendChild($configurationIssueNode);
			}
		}
		$docConfigurationIssues->setDocumentElement($rootConfigurationIssues);
	}
}

sub dvpginventory {
    foreach my $distributedVirtualPortgroup_view (@$view_DistributedVirtualPortgroup) {
        # Exclude DV uplinks portgroup
        if (!defined($distributedVirtualPortgroup_view->tag) || @{$distributedVirtualPortgroup_view->tag}[0]->key ne 'SYSTEM/DVS.UPLINKPG') {
            my $vcentersdk = new URI::URL $distributedVirtualPortgroup_view->{'vim'}->{'service_url'};
            my %h_distributedVirtualPortgroup = (
                name => $distributedVirtualPortgroup_view->name,
                vcenter => $vcentersdk->host,
                numports => $distributedVirtualPortgroup_view->{'config.numPorts'},
                openports => $distributedVirtualPortgroup_view->{'config.numPorts'} - (defined($distributedVirtualPortgroup_view->vm) ? 0+@{$distributedVirtualPortgroup_view->vm} : 0),
                autoexpand => $distributedVirtualPortgroup_view->{'config.autoExpand'},
                moref => $distributedVirtualPortgroup_view->{'mo_ref'}->{'type'}."-".$distributedVirtualPortgroup_view->{'mo_ref'}->{'value'}
            );
            my $distributedVirtualPortgroupNode = $docDistributedVirtualPortgroups->createElement("distributedvirtualportgroup");
            for my $distributedVirtualPortgroupProperty (keys %h_distributedVirtualPortgroup) {
                my $distributedVirtualPortgroupNodeProperty = $docDistributedVirtualPortgroups->createElement($distributedVirtualPortgroupProperty);
                my $value = $h_distributedVirtualPortgroup{$distributedVirtualPortgroupProperty};
                $distributedVirtualPortgroupNodeProperty->appendTextNode($value);
                $distributedVirtualPortgroupNode->appendChild($distributedVirtualPortgroupNodeProperty);
            }
            $rootDistributedVirtualPortgroups->appendChild($distributedVirtualPortgroupNode);
        }
        $docDistributedVirtualPortgroups->setDocumentElement($rootDistributedVirtualPortgroups);
    }
}
