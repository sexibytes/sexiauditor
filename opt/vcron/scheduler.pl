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

$Util::script_version = "0.1";
$ENV{'PERL_LWP_SSL_VERIFY_HOSTNAME'} = 0;
Log::Log4perl::init('/etc/log4perl.conf');

my $logger = Log::Log4perl->get_logger('sexiaudit.vcronScheduler');
my $filename = "/var/www/.vmware/credstore/vicredentials.xml";
my $s_item;
my @server_list;
my $u_item;
my @user_list;
my $password;
my $url;
my $href = ();
my $xmlModuleFile = '/var/www/admin/conf/modules.xml';
my $xmlSettingsFile = '/var/www/admin/conf/settings.xml';

# global variables to store view objects
my ($view_Datacenter, $view_ClusterComputeResource, $view_VirtualMachine);

# hastables
my %h_cluster = ("domain-c000" => "N/A");
my %h_host = ();
my %h_hostcluster = ();

# requiring both file to be readable
(-r $xmlModuleFile) or $logger->logdie ("[ERROR] File $xmlModuleFile not available and/or readable, abort");
(-r $xmlSettingsFile) or $logger->logdie ("[ERROR] File $xmlSettingsFile not available and/or readable, abort");

# modules and settings xml file initialize
my $parser = XML::LibXML->new();
my $docModule = $parser->parse_file($xmlModuleFile);
my $docSettings = $parser->parse_file($xmlSettingsFile);

# browsing modules and fetching schedule
$logger->info("[INFO] Start processing modules list");
foreach my $node ($docModule->findnodes('/modules/category/module')) {
	my $moduleName = $node->findnodes('./id')->to_literal;
	my $scheduleModule = $docSettings->findnodes("/modules/module/id[text()='".$moduleName."']/../schedule")->to_literal;
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
my $execDate = time2str("%Y%m%d%H%M", time);


######################
###                ###
###  EDITING ZONE  ###
###                ###
######################

############################################
# XML files definition (1 module = 1 file) #
############################################
my $xmlPath = "/opt/vcron/data/$execDate";
if ( !-d $xmlPath ) { make_path $xmlPath or $logger->logdie("[ERROR] Failed to create path: $xmlPath"); }
### VirtualMachine
my $xmlVMs = "$xmlPath/vms-global.xml";
my $docVMs = XML::LibXML::Document->new('1.0', 'utf-8');
my $rootVMs = $docVMs->createElement("vms");

###########################################################
# dispatch table for subroutine (1 module = 1 subroutine) #
###########################################################
my %actions = ( vminventory => \&vminventory,
              );

##########################
###                    ###
###  END EDITING ZONE  ###
###                    ###
##########################


VMware::VICredStore::init (filename => $filename) or $logger->logdie ("[ERROR] Unable to initialize Credential Store.");
@server_list = VMware::VICredStore::get_hosts ();
foreach $s_item (@server_list) {
	$logger->info("[INFO] Start processing vCenter $s_item");
	my $normalizedServerName = $s_item;
	@user_list = VMware::VICredStore::get_usernames (server => $s_item);
	if (scalar @user_list == 0) {
		$logger->logdie ("[ERROR] No credential store user detected for $s_item");
	} elsif (scalar @user_list > 1) {
		$logger->logdie ("[ERROR] Multiple credential store user detected for $s_item");
	} else {
		$u_item = "@user_list";
		$password = VMware::VICredStore::get_password (server => $s_item, username => $u_item);
		$url = "https://" . $s_item . "/sdk";
		$normalizedServerName =~ s/[ .]/_/g;
		$normalizedServerName = lc ($normalizedServerName);
		my $sessionfile = "/tmp/vpx_${normalizedServerName}.dat";
		if (defined($sessionfile) and -e $sessionfile) {
		        eval { Vim::load_session(service_url => $url, session_file => $sessionfile); };
		        if ($@) {
				# session is no longer valid, we must destroy it to let it be recreated
				$logger->warn("[WARNING] Session file $sessionfile is no longer valid, it has been destroyed");
				unlink($sessionfile);
		                Vim::login(service_url => $url, user_name => $u_item, password => $password) or $logger->logdie ("[ERROR] Unable to connect to $url with username $u_item");
		        }
		} else {
		        eval { Vim::login(service_url => $url, user_name => $u_item, password => $password); };
			if ($@) {
				$logger->logdie("[ERROR] Wrong credential for vCenter $normalizedServerName and login $u_item");
			}
		}
		if (defined($sessionfile)) {
			$logger->info("[INFO] Saving session token in file $sessionfile");
		        Vim::save_session(session_file => $sessionfile);
		}
	}

    # TODO: check version

	# vCenter connection should be OK at this point
	# generating meta objects
	$logger->info("[INFO] Start retrieving ClusterComputeResource objects");
	$view_ClusterComputeResource = Vim::find_entity_views(view_type => 'ClusterComputeResource', properties => ['name', 'host']);
	$logger->info("[INFO] End retrieving ClusterComputeResource objects");
	#$logger->info("[INFO] Start retrieving ComputeResource objects");
	#my $view_ComputeResource = Vim::find_entity_views(view_type => 'ComputeResource');
	#$logger->info("[INFO] End retrieving ComputeResource objects");
	#$logger->info("[INFO] Start retrieving HostSystem objects");
	#my $view_HostSystem = Vim::find_entity_views(view_type => 'HostSystem');
	#$logger->info("[INFO] End retrieving HostSystem objects");
	#$logger->info("[INFO] Start retrieving DistributedVirtualSwitch objects");
	#my $view_DistributedVirtualSwitch = Vim::find_entity_views(view_type => 'DistributedVirtualSwitch');
	#$logger->info("[INFO] End retrieving DistributedVirtualSwitch objects");
	#$logger->info("[INFO] Start retrieving StoragePod objects");
	#my $view_StoragePod = Vim::find_entity_views(view_type => 'StoragePod');
	#$logger->info("[INFO] End retrieving StoragePod objects");
	#$logger->info("[INFO] Start retrieving Datacenter objects");
	#$view_Datacenter = Vim::find_entity_views(view_type => 'Datacenter');
	#$logger->info("[INFO] End retrieving Datacenter objects");
	$logger->info("[INFO] Start retrieving VirtualMachine objects");
	$view_VirtualMachine = Vim::find_entity_views(view_type => 'VirtualMachine', properties => ['name','guest','summary.config.vmPathName','runtime.host','network','summary.config.numCpu','summary.config.memorySizeMB','summary.storage']);
	$logger->info("[INFO] End retrieving VirtualMachine objects");
	#do 'modules/module01.pl';

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

	for my $key ( keys(%$href) ) {
		my $value = $href->{$key};
		# using dispatch table to call dynamically named subroutine
		$actions{ $key }->();
	}

	$logger->info("[INFO] End processing vCenter $s_item");
}

########################
# File dump generation #
########################
$docVMs->toFile($xmlVMs, 2) or $logger->error("[ERROR] Unable to save file $xmlVMs");
chmod 0644, $xmlVMs;



#########################
# subroutine definition #
#########################

sub vminventory {
	my $vmNodeProperty;
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
		my $vcentersdk = new URI::URL $vm_view->{'vim'}->{'service_url'};
		my %h_vm = (
			name => $vm_view->name,
			VCENTER => $vcentersdk->host,
			CLUSTER => $h_cluster{($h_hostcluster{$vm_view->{'runtime.host'}->value} ? $h_hostcluster{$vm_view->{'runtime.host'}->value} : "domain-c000")},
			HOST => $h_host{$vm_view->{'runtime.host'}->value},
			VMXPATH => $vm_view->{'summary.config.vmPathName'},
			PORTGROUP => join(',', @vm_pg_string),
			IP => join(',', @vm_ip_string),
			NUMCPU => ($vm_view->{'summary.config.numCpu'} ? $vm_view->{'summary.config.numCpu'} : "N/A"),
			MEMORY => ($vm_view->{'summary.config.memorySizeMB'} ? $vm_view->{'summary.config.memorySizeMB'} : "N/A"),
			COMMITED => int($vm_view->{'summary.storage'}->committed / 1073741824),
			PROVISIONNED => int(($vm_view->{'summary.storage'}->committed + $vm_view->{'summary.storage'}->uncommitted) / 1073741824),
			DATASTORE => (split /\[/, (split /\]/, $vm_view->{'summary.config.vmPathName'})[0])[1],
			MAC => join(',', @vm_mac)
		);
		my $vmNode = $docVMs->createElement("vm");
		for my $vmProperty (keys %h_vm) {
			my $vmNodeProperty = $docVMs->createElement($vmProperty);
			my $value = $h_vm{$vmProperty};
			$vmNodeProperty->appendTextNode($value);
			$vmNode->appendChild($vmNodeProperty);
		}                                
		$rootVMs->appendChild($vmNode);
	}
	$docVMs->setDocumentElement($rootVMs);
}
