-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 03, 2016 at 02:14 PM
-- Server version: 10.0.26-MariaDB-0+deb8u1
-- PHP Version: 5.6.24-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `sexiauditor`
--
CREATE DATABASE IF NOT EXISTS `sexiauditor` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `sexiauditor`;

-- --------------------------------------------------------

--
-- Table structure for table `alarms`
--

DROP TABLE IF EXISTS `alarms`;
CREATE TABLE IF NOT EXISTS `alarms` (
`id` int(11) NOT NULL,
  `vcenter` int(11) NOT NULL,
  `moref` varchar(255) CHARACTER SET utf8 NOT NULL,
  `entityMoRef` varchar(255) CHARACTER SET utf8 NOT NULL,
  `alarm_name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `time` datetime NOT NULL,
  `status` varchar(50) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

DROP TABLE IF EXISTS `certificates`;
CREATE TABLE IF NOT EXISTS `certificates` (
`id` int(11) NOT NULL,
  `vcenter` int(11) NOT NULL,
  `url` varchar(255) CHARACTER SET utf8 NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `type` varchar(255) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clusters`
--

DROP TABLE IF EXISTS `clusters`;
CREATE TABLE IF NOT EXISTS `clusters` (
`id` int(11) NOT NULL,
  `vcenter` int(11) NOT NULL,
  `moref` varchar(255) CHARACTER SET utf8 NOT NULL,
  `cluster_name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `vmotion` int(11) NOT NULL,
  `dasenabled` tinyint(1) NOT NULL,
  `lastconfigissuetime` datetime NOT NULL,
  `lastconfigissue` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
CREATE TABLE IF NOT EXISTS `config` (
`id` int(11) NOT NULL,
  `configid` varchar(50) CHARACTER SET utf8 NOT NULL,
  `type` int(11) NOT NULL,
  `label` varchar(255) CHARACTER SET utf8 NOT NULL,
  `value` varchar(50) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`id`, `configid`, `type`, `label`, `value`) VALUES
(1, 'dailySchedule', 8, 'Choose the hour used for daily schedule (appliance use UTC format)', '8'),
(2, 'weeklySchedule', 9, 'Choose the day used for weekly schedule', '4'),
(3, 'monthlySchedule', 10, 'Choose the day used for monthly schedule (beware of short/long months)', '6'),
(4, 'powerSystemInfo', 7, 'Choose the desired Power Management Policy for ESX', 'off'),
(5, 'thresholdHistory', 4, 'Number of days before data is purged (0 to disabled)', '180'),
(6, 'thresholdCPURatio', 4, 'Threshold for vCPU/pCPU ratio check', '2'),
(7, 'lang', 6, 'Language of appliance', 'en'),
(8, 'showPlainLicense', 1, 'Display plain license in License Report instead of ''####''', 'disable'),
(9, 'vcSessionAge', 4, 'Number of days before a session is defined as ''old''', '7'),
(10, 'hostSSHPolicy', 5, 'Choose the desired policy for SSH service', 'on'),
(11, 'hostShellPolicy', 5, 'Choose the desired policy for Shell service', 'on'),
(12, 'datastoreFreeSpaceThreshold', 4, 'Datastore free space threshold %', '30'),
(13, 'datastoreOverallocation', 4, 'Datastore OverAllocation %', '30'),
(14, 'networkDVSVSSportsfree', 4, 'Threshold of free port for DVS needed', '123'),
(15, 'vmSnapshotAge', 4, 'Number of days before a snapshot is defined as ''old''', '98'),
(16, 'timeToBuildCount', 4, 'Number of entries ''Time To Build'' page will display (0 for all)', '100'),
(17, 'smtpAddress', 2, 'SMTP server address (IP, or FQDN) to forward email to', 'smtp'),
(18, 'senderMail', 3, 'Sender email to be used for report export feature', 'sender@sexibyt.es'),
(19, 'recipientMail', 3, 'Recipient email to be used for report export feature', 'frederic@sexibyt.es'),
(20, 'pdfAuthor', 2, 'Username that will be used as the ''Author'' of generated PDFs', 'Gordon Freeman'),
(21, 'showEmpty', 1, 'Show checks that return empty values (ie when there is nothing to report)', 'enable'),
(22, 'showAuthors', 1, 'Show authors page of generated PDF''s, be aware that disabling it may kill some kitten...', 'enable'),
(23, 'sexigrafNode', 2, 'SexiGraf node IP or FQDN (used for Capacity Planning)', '127.0.0.1'),
(24, 'capacityPlanningDays', 4, 'Number of days Capacity Planning will used for computationcalculation of ''Days Left'' value', '7'),
(25, 'showInfinite', 1, 'Does Capacity Planning display clusters with ''Infinite'' days left ?)', 'enable');

-- --------------------------------------------------------

--
-- Table structure for table `configtype`
--

DROP TABLE IF EXISTS `configtype`;
CREATE TABLE IF NOT EXISTS `configtype` (
`id` int(11) NOT NULL,
  `type` varchar(50) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `configtype`
--

INSERT INTO `configtype` (`id`, `type`) VALUES
(1, 'boolean'),
(2, 'text'),
(3, 'email'),
(4, 'number'),
(5, 'servicePolicy'),
(6, 'language'),
(7, 'powerList'),
(8, 'off'),
(9, 'weekly'),
(10, 'monthly');

-- --------------------------------------------------------

--
-- Table structure for table `configurationissues`
--

DROP TABLE IF EXISTS `configurationissues`;
CREATE TABLE IF NOT EXISTS `configurationissues` (
`id` int(11) NOT NULL,
  `host` int(11) NOT NULL,
  `configissue` varchar(255) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `datastores`
--

DROP TABLE IF EXISTS `datastores`;
CREATE TABLE IF NOT EXISTS `datastores` (
`id` int(11) NOT NULL,
  `vcenter` int(11) NOT NULL,
  `moref` varchar(255) CHARACTER SET utf8 NOT NULL,
  `datastore_name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `type` varchar(50) CHARACTER SET utf8 NOT NULL,
  `size` bigint(20) NOT NULL,
  `uncommitted` bigint(20) NOT NULL,
  `iormConfiguration` tinyint(1) NOT NULL,
  `freespace` bigint(20) NOT NULL,
  `maintenanceMode` varchar(50) CHARACTER SET utf8 NOT NULL,
  `isAccessible` tinyint(1) NOT NULL,
  `shared` tinyint(1) NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `distributedvirtualportgroups`
--

DROP TABLE IF EXISTS `distributedvirtualportgroups`;
CREATE TABLE IF NOT EXISTS `distributedvirtualportgroups` (
`id` int(11) NOT NULL,
  `vcenter` int(11) NOT NULL,
  `moref` varchar(255) CHARACTER SET utf8 NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `numports` int(11) NOT NULL,
  `openports` int(11) NOT NULL,
  `autoexpand` tinyint(1) NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `executiontime`
--

DROP TABLE IF EXISTS `executiontime`;
CREATE TABLE IF NOT EXISTS `executiontime` (
`id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `seconds` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hardwarestatus`
--

DROP TABLE IF EXISTS `hardwarestatus`;
CREATE TABLE IF NOT EXISTS `hardwarestatus` (
`id` int(11) NOT NULL,
  `host` int(11) NOT NULL,
  `issuename` varchar(255) CHARACTER SET utf8 NOT NULL,
  `issuestate` varchar(255) CHARACTER SET utf8 NOT NULL,
  `issuetype` varchar(255) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hosts`
--

DROP TABLE IF EXISTS `hosts`;
CREATE TABLE IF NOT EXISTS `hosts` (
`id` int(11) NOT NULL,
  `vcenter` int(11) NOT NULL,
  `cluster` int(11) DEFAULT NULL,
  `moref` varchar(255) CHARACTER SET utf8 NOT NULL,
  `hostname` varchar(255) CHARACTER SET utf8 NOT NULL,
  `host_name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `ntpservers` varchar(255) CHARACTER SET utf8 NOT NULL,
  `deadlunpathcount` int(11) NOT NULL,
  `numcpucore` int(11) NOT NULL,
  `syslog_target` varchar(255) CHARACTER SET utf8 NOT NULL,
  `rebootrequired` tinyint(1) NOT NULL,
  `powerpolicy` varchar(255) CHARACTER SET utf8 NOT NULL,
  `bandwidthcapacity` int(11) NOT NULL,
  `memory` bigint(20) NOT NULL,
  `dnsservers` varchar(255) CHARACTER SET utf8 NOT NULL,
  `cputype` varchar(255) CHARACTER SET utf8 NOT NULL,
  `numcpu` int(11) NOT NULL,
  `inmaintenancemode` tinyint(1) NOT NULL,
  `lunpathcount` int(11) NOT NULL,
  `model` varchar(255) CHARACTER SET utf8 NOT NULL,
  `sharedmemory` int(11) NOT NULL,
  `cpumhz` int(11) NOT NULL,
  `esxbuild` varchar(255) CHARACTER SET utf8 NOT NULL,
  `ssh_policy` varchar(255) CHARACTER SET utf8 NOT NULL,
  `shell_policy` varchar(255) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `licenses`
--

DROP TABLE IF EXISTS `licenses`;
CREATE TABLE IF NOT EXISTS `licenses` (
`id` int(11) NOT NULL,
  `licenseKey` varchar(255) CHARACTER SET utf8 NOT NULL,
  `vcenter` int(11) NOT NULL,
  `costUnit` varchar(255) CHARACTER SET utf8 NOT NULL,
  `editionKey` varchar(255) CHARACTER SET utf8 NOT NULL,
  `used` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `moduleCategory`
--

DROP TABLE IF EXISTS `moduleCategory`;
CREATE TABLE IF NOT EXISTS `moduleCategory` (
`id` int(11) NOT NULL,
  `category` varchar(50) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `moduleCategory`
--

INSERT INTO `moduleCategory` (`id`, `category`) VALUES
(1, 'VSAN'),
(2, 'vCenter'),
(3, 'Cluster'),
(4, 'Host'),
(5, 'Datastore'),
(6, 'Network'),
(7, 'Virtual Machine'),
(8, 'Global');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
CREATE TABLE IF NOT EXISTS `modules` (
`id` int(11) NOT NULL,
  `module` varchar(50) CHARACTER SET utf8 NOT NULL,
  `type` varchar(10) CHARACTER SET utf8 NOT NULL,
  `displayName` varchar(255) CHARACTER SET utf8 NOT NULL,
  `version` decimal(10,0) NOT NULL,
  `description` varchar(255) CHARACTER SET utf8 NOT NULL,
  `category_id` int(11) NOT NULL,
  `schedule` varchar(10) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `module`, `type`, `displayName`, `version`, `description`, `category_id`, `schedule`) VALUES
(1, 'inventory', 'action', 'Inventory', '1', 'Virtual Machine inventory with a lot of properties. Hosts and Datastores info will be retrieved as well (used in main page stats page).', 8, 'hourly'),
(2, 'vcSessionAge', 'report', 'Session Age', '1', 'Display vCenter session that are older than (x)days.', 2, 'daily'),
(3, 'vcLicenceReport', 'report', 'License Report', '1', 'Display licence consumption based on vCenter licence defined.', 2, 'daily'),
(4, 'vcPermissionReport', 'report', 'Permission report', '1', 'Display permission listing with user/role combination.', 2, 'daily'),
(5, 'vcTerminateSession', 'action', 'Terminate session', '1', '[Action] Kill vCenter session older than (x)days.', 2, 'daily'),
(6, 'vcCertificatesReport', 'report', 'Certificate Report', '1', 'Display soon-to-be-expired certificates for all vCenter components (SSO, WebClient, ...).', 2, 'daily'),
(7, 'clusterConfigurationIssues', 'report', 'Configuration Issues', '1', 'Configuration Issues report.', 3, 'daily'),
(8, 'clusterAdmissionControl', 'report', 'Admission Control', '0', 'Admission Control report.', 3, 'daily'),
(9, 'clusterHAStatus', 'report', 'Cluster Without HA', '1', 'Display cluster without HA.', 3, 'daily'),
(10, 'clusterDatastoreConsistency', 'report', 'Datastore Consistency', '1', 'Datastore Consistency report.', 3, 'daily'),
(11, 'clusterMembersVersion', 'report', 'Members Version', '1', 'Members Version Report.', 3, 'daily'),
(12, 'clusterMembersOvercommit', 'report', 'Members Overcommit', '0', 'Members Overcommit Report.', 3, 'daily'),
(13, 'clusterMembersLUNPathCountMismatch', 'report', 'Members LUN Path count mismatch', '1', 'Members LUN Path count mismatch Report.', 3, 'daily'),
(14, 'clusterCPURatio', 'report', 'vCPU pCPU table', '1', 'vCPU pCPU table Report.', 3, 'daily'),
(15, 'clusterTPSSavings', 'report', 'TPS savings', '0', 'TPS savings Report.', 3, 'daily'),
(16, 'clusterAutoSlotSize', 'report', 'AutoSlotSize', '0', 'AutoSlotSize Report.', 3, 'off'),
(17, 'clusterProfile', 'report', 'Profile', '0', 'Profile Report.', 3, 'off'),
(18, 'hostLUNPathDead', 'report', 'LUN Path Dead', '1', 'LUN Path Dead report.', 4, 'daily'),
(19, 'hostProfileCompliance', 'report', 'Profile Compliance', '0', 'Profile Compliance report.', 4, 'off'),
(20, 'hostLocalSwapDatastoreCompliance', 'report', 'LocalSwapDatastore Compliance', '0', 'LocalSwapDatastore Compliance report.', 4, 'off'),
(21, 'hostSshShell', 'report', 'SSH/shell check', '1', 'SSH/shell check report.', 4, 'daily'),
(22, 'hostNTPCheck', 'report', 'NTP Check', '1', 'NTP Check report.', 4, 'daily'),
(23, 'hostDNSCheck', 'report', 'DNS Check', '1', 'DNS Check report.', 4, 'daily'),
(24, 'hostSyslogCheck', 'report', 'Syslog Check', '1', 'Syslog Check report.', 4, 'daily'),
(25, 'hostConfigurationIssues', 'report', 'configuration issues', '1', 'configuration issues report.', 4, 'daily'),
(26, 'hostHardwareStatus', 'report', 'Hardware Status', '1', 'Hardware Status report.', 4, 'daily'),
(27, 'hostRebootrequired', 'report', 'Reboot required', '1', 'Reboot required report.', 4, 'daily'),
(28, 'hostFQDNHostnameMismatch', 'report', 'FQDN/hostname mismatch', '1', 'FQDN/hostname mismatch report.', 4, 'daily'),
(29, 'hostMaintenanceMode', 'report', 'Maintenance Mode', '1', 'maintenance mode report.', 4, 'daily'),
(30, 'hostballooningzipswap', 'report', 'ballooning/zip/swap', '0', 'ballooning/zip/swap report.', 4, 'off'),
(31, 'hostPowerManagementPolicy', 'report', 'PowerManagement Policy', '1', 'PowerManagement Policy report.', 4, 'daily'),
(32, 'hostBundlebackup', 'action', 'Bundle backup', '0', 'Bundle backup report.', 4, 'off'),
(33, 'datastoreSpacereport', 'report', 'Space report', '1', 'Space report.', 5, 'daily'),
(34, 'datastoreOrphanedVMFilesreport', 'report', 'Orphaned VM Files report', '0', 'Orphaned VM Files report report.', 5, 'off'),
(35, 'datastoreOverallocation', 'report', 'Overallocation', '1', 'Overallocation report.', 5, 'daily'),
(36, 'datastoreSIOCdisabled', 'report', 'SIOC disabled', '1', 'SIOC disabled report.', 5, 'daily'),
(37, 'datastoremaintenancemode', 'report', 'maintenance mode', '1', 'maintenance mode report.', 5, 'daily'),
(38, 'datastoreAccessible', 'report', 'Accessible', '1', 'Accessible report.', 5, 'daily'),
(39, 'networkDVSportsfree', 'report', 'DVS ports free', '1', 'DVS ports free report.', 6, 'daily'),
(40, 'networkDVPGAutoExpand', 'action', 'DVPG AutoExpand', '0', 'DVPG AutoExpand action.', 6, 'off'),
(41, 'networkDVSprofile', 'report', 'DVS profile', '0', 'DVS profile report.', 6, 'off'),
(42, 'vmSnapshotsage', 'report', 'Snapshots age', '1', 'Snapshots age report.', 7, 'daily'),
(43, 'vmphantomsnapshot', 'report', 'phantom snapshot', '1', 'phantom snapshot report.', 7, 'daily'),
(44, 'vmconsolidationneeded', 'report', 'consolidation needed', '1', 'consolidation needed report.', 7, 'daily'),
(45, 'vmcpuramhddreservation', 'report', 'cpu/ram/hdd reservation', '1', 'cpu/ram/hdd reservation report.', 7, 'daily'),
(46, 'vmcpuramhddlimits', 'report', 'cpu/ram/hdd limits', '1', 'cpu/ram/hdd limits report.', 7, 'daily'),
(47, 'vmcpuramhotadd', 'report', 'cpu/ram hot-add', '1', 'cpu/ram hot-add report.', 7, 'daily'),
(48, 'vmToolsPivot', 'report', 'VM Tools Pivot Table', '1', 'Will display a list of all vmtools version group by count.', 7, 'daily'),
(49, 'vmvHardwarePivot', 'report', 'vHardware Pivot Table', '1', 'Will display a list of all guest hardware version (VHW) group by count.', 7, 'daily'),
(50, 'vmballoonzipswap', 'report', 'balloon/zip/swap', '1', 'balloon/zip/swap report.', 7, 'daily'),
(51, 'vmmultiwritermode', 'report', 'multiwriter mode', '1', 'multiwriter mode report.', 7, 'daily'),
(52, 'vmNonpersistentmode', 'report', 'Non persistent mode', '1', 'Non persistent mode report.', 7, 'daily'),
(53, 'vmscsibussharing', 'report', 'scsi bus sharing', '1', 'scsi bus sharing report.', 7, 'daily'),
(54, 'vmInvalidOrInaccessible', 'report', 'VM Invalid Or Inaccessible', '1', 'This module will display VMs that are marked as inaccessible or invalid.', 7, 'daily'),
(55, 'vmInconsistent', 'report', 'Inconsistent Folder', '1', 'The following VMs are not stored in folders consistent to their names, this may cause issues when trying to locate them from the datastore manually.', 7, 'daily'),
(56, 'vmRemovableConnected', 'report', 'Removable Connected', '1', 'This module will display VM that have removable devices (floppy, CD-Rom, ...) connected.', 7, 'daily'),
(57, 'vmGuestIdMismatch', 'report', 'GuestId mismatch', '1', 'GuestId mismatch report.', 7, 'daily'),
(58, 'vmPoweredOff', 'report', 'Powered Off', '1', 'This module will display VM that are Powered Off. This can be useful to check if this state is expected.', 7, 'daily'),
(59, 'vmGuestPivot', 'report', 'GuestID Pivot Table', '1', 'Will display a list of all guest OS group by count.', 7, 'daily'),
(60, 'vmMisnamed', 'report', 'Misnamed based on FQDN', '1', 'Will display VM that have FQDN mismatched with the VM object name.', 7, 'daily'),
(61, 'VSANHealthCheck', 'report', 'VSAN Health Check', '0', 'Display VSAN information about Health Check.', 1, 'off'),
(62, 'alarms', 'report', 'Alarms', '1', 'Will display triggered alarms on objects level with status and time of creation.', 8, 'daily');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
`id` int(11) NOT NULL,
  `vcenter` int(11) NOT NULL,
  `principal` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `role_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `isGroup` tinyint(1) NOT NULL,
  `inventory_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
`id` int(11) NOT NULL,
  `role` varchar(10) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role`) VALUES
(1, 'admin'),
(2, 'reader');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
`id` int(11) NOT NULL,
  `sessionKey` varchar(255) CHARACTER SET utf8 NOT NULL,
  `vcenter` int(11) NOT NULL,
  `lastActiveTime` datetime NOT NULL,
  `userName` varchar(255) CHARACTER SET utf8 NOT NULL,
  `loginTime` datetime NOT NULL,
  `ipAddress` varchar(255) CHARACTER SET utf8 NOT NULL,
  `userAgent` varchar(255) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `snapshots`
--

DROP TABLE IF EXISTS `snapshots`;
CREATE TABLE IF NOT EXISTS `snapshots` (
`id` int(11) NOT NULL,
  `vm` int(11) NOT NULL,
  `moref` varchar(255) CHARACTER SET utf8 NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `createTime` datetime NOT NULL,
  `snapid` int(11) NOT NULL,
  `description` varchar(255) CHARACTER SET utf8 NOT NULL,
  `quiesced` tinyint(1) NOT NULL,
  `state` varchar(255) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
`id` int(11) NOT NULL,
  `username` varchar(255) CHARACTER SET utf8 NOT NULL,
  `displayname` varchar(255) CHARACTER SET utf8 NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 NOT NULL,
  `role` int(11) NOT NULL,
  `password` char(128) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `displayname`, `email`, `role`, `password`) VALUES
(1, 'admin', 'Administrator', 'admin@dev.null', 1, '1f40fc92da241694750979ee6cf582f2d5d7d28e18335de05abc54d0560e0f5302860c652bf08d560252aa5e74210546f369fbbbce8c12cfc7957b2652fe9a75'),
(2, 'reader', 'Kindle', 'dev@dev.null', 2, '2e96772232487fb3a058d58f2c310023e07e4017c94d56cc5fae4b54b44605f42a75b0b1f358991f8c6cbe9b68b64e5b2a09d0ad23fcac07ee9a9198a745e1d5');

-- --------------------------------------------------------

--
-- Table structure for table `vcenters`
--

DROP TABLE IF EXISTS `vcenters`;
CREATE TABLE IF NOT EXISTS `vcenters` (
`id` int(11) NOT NULL,
  `vcname` varchar(255) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vms`
--

DROP TABLE IF EXISTS `vms`;
CREATE TABLE IF NOT EXISTS `vms` (
`id` int(11) NOT NULL,
  `vcenter` int(11) NOT NULL,
  `host` int(11) NOT NULL,
  `memReservation` int(11) NOT NULL,
  `guestFamily` varchar(255) CHARACTER SET utf8 NOT NULL,
  `ip` varchar(255) CHARACTER SET utf8 NOT NULL,
  `swappedMemory` int(11) NOT NULL,
  `cpuLimit` int(11) NOT NULL,
  `datastore` varchar(255) CHARACTER SET utf8 NOT NULL,
  `moref` varchar(255) CHARACTER SET utf8 NOT NULL,
  `consolidationNeeded` tinyint(1) NOT NULL,
  `fqdn` varchar(255) CHARACTER SET utf8 NOT NULL,
  `numcpu` int(11) NOT NULL,
  `cpuReservation` int(11) NOT NULL,
  `sharedBus` tinyint(1) NOT NULL,
  `portgroup` varchar(255) CHARACTER SET utf8 NOT NULL,
  `memory` int(11) NOT NULL,
  `phantomSnapshot` int(11) NOT NULL,
  `hwversion` varchar(20) CHARACTER SET utf8 NOT NULL,
  `provisionned` int(11) NOT NULL,
  `mac` varchar(255) CHARACTER SET utf8 NOT NULL,
  `multiwriter` tinyint(1) NOT NULL,
  `memHotAddEnabled` tinyint(1) NOT NULL,
  `guestOS` varchar(255) CHARACTER SET utf8 NOT NULL,
  `compressedMemory` int(11) NOT NULL,
  `removable` tinyint(1) NOT NULL,
  `commited` int(11) NOT NULL,
  `vmpath` varchar(255) CHARACTER SET utf8 NOT NULL,
  `balloonedMemory` int(11) NOT NULL,
  `vmtools` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `configGuestId` varchar(255) CHARACTER SET utf8 NOT NULL,
  `memLimit` int(11) NOT NULL,
  `vmxpath` varchar(255) CHARACTER SET utf8 NOT NULL,
  `connectionState` varchar(255) CHARACTER SET utf8 NOT NULL,
  `cpuHotAddEnabled` tinyint(1) NOT NULL,
  `uncommited` int(11) NOT NULL,
  `powerState` varchar(255) CHARACTER SET utf8 NOT NULL,
  `guestId` varchar(255) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alarms`
--
ALTER TABLE `alarms`
 ADD PRIMARY KEY (`id`), ADD KEY `vcenter` (`vcenter`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
 ADD PRIMARY KEY (`id`), ADD KEY `vcenter` (`vcenter`);

--
-- Indexes for table `clusters`
--
ALTER TABLE `clusters`
 ADD PRIMARY KEY (`id`), ADD KEY `vcenter` (`vcenter`), ADD KEY `id` (`id`);

--
-- Indexes for table `config`
--
ALTER TABLE `config`
 ADD PRIMARY KEY (`id`), ADD KEY `type` (`type`), ADD KEY `configid` (`configid`);

--
-- Indexes for table `configtype`
--
ALTER TABLE `configtype`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `configurationissues`
--
ALTER TABLE `configurationissues`
 ADD PRIMARY KEY (`id`), ADD KEY `host` (`host`);

--
-- Indexes for table `datastores`
--
ALTER TABLE `datastores`
 ADD PRIMARY KEY (`id`), ADD KEY `vcenter` (`vcenter`);

--
-- Indexes for table `distributedvirtualportgroups`
--
ALTER TABLE `distributedvirtualportgroups`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `executiontime`
--
ALTER TABLE `executiontime`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hardwarestatus`
--
ALTER TABLE `hardwarestatus`
 ADD PRIMARY KEY (`id`), ADD KEY `host` (`host`);

--
-- Indexes for table `hosts`
--
ALTER TABLE `hosts`
 ADD PRIMARY KEY (`id`), ADD KEY `id` (`id`), ADD KEY `vcenter` (`vcenter`), ADD KEY `cluster` (`cluster`), ADD KEY `moref` (`moref`);

--
-- Indexes for table `licenses`
--
ALTER TABLE `licenses`
 ADD PRIMARY KEY (`id`), ADD KEY `vcenter` (`vcenter`);

--
-- Indexes for table `moduleCategory`
--
ALTER TABLE `moduleCategory`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
 ADD PRIMARY KEY (`id`), ADD KEY `module` (`module`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
 ADD PRIMARY KEY (`id`), ADD KEY `vcenter` (`vcenter`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
 ADD PRIMARY KEY (`id`), ADD KEY `role` (`role`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
 ADD PRIMARY KEY (`id`), ADD KEY `vcenter` (`vcenter`);

--
-- Indexes for table `snapshots`
--
ALTER TABLE `snapshots`
 ADD PRIMARY KEY (`id`), ADD KEY `vm` (`vm`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`), ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `vcenters`
--
ALTER TABLE `vcenters`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vms`
--
ALTER TABLE `vms`
 ADD PRIMARY KEY (`id`), ADD KEY `host` (`host`), ADD KEY `vcenter` (`vcenter`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alarms`
--
ALTER TABLE `alarms`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `clusters`
--
ALTER TABLE `clusters`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `config`
--
ALTER TABLE `config`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=23;
--
-- AUTO_INCREMENT for table `configtype`
--
ALTER TABLE `configtype`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT for table `configurationissues`
--
ALTER TABLE `configurationissues`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `datastores`
--
ALTER TABLE `datastores`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `distributedvirtualportgroups`
--
ALTER TABLE `distributedvirtualportgroups`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `executiontime`
--
ALTER TABLE `executiontime`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `hardwarestatus`
--
ALTER TABLE `hardwarestatus`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `hosts`
--
ALTER TABLE `hosts`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `licenses`
--
ALTER TABLE `licenses`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `moduleCategory`
--
ALTER TABLE `moduleCategory`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=63;
--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `snapshots`
--
ALTER TABLE `snapshots`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `vcenters`
--
ALTER TABLE `vcenters`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `vms`
--
ALTER TABLE `vms`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `config`
--
ALTER TABLE `config`
ADD CONSTRAINT `config_ibfk_1` FOREIGN KEY (`type`) REFERENCES `configtype` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
