-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 25, 2016 at 07:45 AM
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

USE `sexiauditor`;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role`) VALUES
(1, 'admin'),
(2, 'reader');

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `displayname`, `email`, `role`, `password`) VALUES
(1, 'admin', 'Administrator', 'admin@dev.null', 1, '1f40fc92da241694750979ee6cf582f2d5d7d28e18335de05abc54d0560e0f5302860c652bf08d560252aa5e74210546f369fbbbce8c12cfc7957b2652fe9a75'),
(2, 'reader', 'Kindle', 'dev@dev.null', 2, '2e96772232487fb3a058d58f2c310023e07e4017c94d56cc5fae4b54b44605f42a75b0b1f358991f8c6cbe9b68b64e5b2a09d0ad23fcac07ee9a9198a745e1d5');

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
(13, 'datastoreOverallocation', 4, 'Datastore OverAllocation %', '130'),
(14, 'networkDVSVSSportsfree', 4, 'Threshold of free port for DVS needed', '123'),
(15, 'vmSnapshotAge', 4, 'Number of days before a snapshot is defined as ''old''', '98'),
(16, 'timeToBuildCount', 4, 'Number of entries ''Time To Build'' page will display (0 for all)', '100'),
(17, 'smtpAddress', 2, 'SMTP server address (IP, or FQDN) to forward email to', 'smtp.sexibyt.es'),
(18, 'senderMail', 3, 'Sender email to be used for report export feature', 'sender@sexibyt.es'),
(19, 'recipientMail', 3, 'Recipient email to be used for report export feature', 'frederic@sexibyt.es'),
(20, 'pdfAuthor', 2, 'Username that will be used as the ''Author'' of generated PDFs', 'Gordon Freeman'),
(21, 'showEmpty', 1, 'Show checks that return empty values (ie when there is nothing to report)', 'enable'),
(22, 'showAuthors', 1, 'Show authors page of generated PDF''s, be aware that disabling it may kill some kitten...', 'enable'),
(23, 'sexigrafNode', 2, 'SexiGraf node IP or FQDN (used for Capacity Planning)', 'sexigraf.sexibyt.es'),
(24, 'capacityPlanningDays', 4, 'Number of days Capacity Planning will used for computationcalculation of ''Days Left'' value', '7'),
(25, 'showInfinite', 1, 'Does Capacity Planning display clusters with ''Infinite'' days left ?', 'enable'),
(26, 'showDebug', 1, 'Display debug log in log files (careful, it is really verbose)', 'disable');

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

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `module`, `type`, `displayName`, `version`, `description`, `category_id`, `schedule`) VALUES
(1, 'inventory', 'action', 'Inventory', '1', 'Virtual Machine inventory with a lot of properties. Hosts and Datastores info will be retrieved as well (used in main page stats page).', 8, 'hourly'),
(2, 'vcSessionAge', 'report', 'Session Age', '1', 'Display vCenter session that are older than (x)days.', 2, 'off'),
(3, 'vcLicenceReport', 'report', 'License Report', '1', 'Display licence consumption based on vCenter licence defined.', 2, 'off'),
(4, 'vcPermissionReport', 'report', 'Permission report', '1', 'Display permission listing with user/role combination.', 2, 'off'),
(5, 'vcTerminateSession', 'action', 'Terminate session', '1', '[Action] Kill vCenter session older than (x)days.', 2, 'off'),
(6, 'vcCertificatesReport', 'report', 'Certificate Report', '1', 'Display soon-to-be-expired certificates for all vCenter components (SSO, WebClient, ...).', 2, 'off'),
(7, 'clusterConfigurationIssues', 'report', 'Configuration Issues', '1', 'Configuration Issues report.', 3, 'off'),
(8, 'clusterAdmissionControl', 'report', 'Admission Control', '0', 'Admission Control report.', 3, 'off'),
(9, 'clusterHAStatus', 'report', 'Cluster Without HA', '1', 'Display cluster without HA.', 3, 'off'),
(10, 'clusterDatastoreConsistency', 'report', 'Datastore Consistency', '1', 'Datastore Consistency report.', 3, 'off'),
(11, 'clusterMembersVersion', 'report', 'Members Version', '1', 'Members Version Report.', 3, 'off'),
(12, 'clusterMembersOvercommit', 'report', 'Members Overcommit', '0', 'Members Overcommit Report.', 3, 'off'),
(13, 'clusterMembersLUNPathCountMismatch', 'report', 'Members LUN Path count mismatch', '1', 'Members LUN Path count mismatch Report.', 3, 'off'),
(14, 'clusterCPURatio', 'report', 'vCPU pCPU table', '1', 'vCPU pCPU table Report.', 3, 'off'),
(15, 'clusterTPSSavings', 'report', 'TPS savings', '0', 'TPS savings Report.', 3, 'off'),
(16, 'clusterAutoSlotSize', 'report', 'AutoSlotSize', '0', 'AutoSlotSize Report.', 3, 'off'),
(17, 'clusterProfile', 'report', 'Profile', '0', 'Profile Report.', 3, 'off'),
(18, 'hostLUNPathDead', 'report', 'LUN Path Dead', '1', 'LUN Path Dead report.', 4, 'off'),
(19, 'hostProfileCompliance', 'report', 'Profile Compliance', '0', 'Profile Compliance report.', 4, 'off'),
(20, 'hostLocalSwapDatastoreCompliance', 'report', 'LocalSwapDatastore Compliance', '0', 'LocalSwapDatastore Compliance report.', 4, 'off'),
(21, 'hostSshShell', 'report', 'SSH/shell check', '1', 'SSH/shell check report.', 4, 'off'),
(22, 'hostNTPCheck', 'report', 'NTP Check', '1', 'NTP Check report.', 4, 'off'),
(23, 'hostDNSCheck', 'report', 'DNS Check', '1', 'DNS Check report.', 4, 'off'),
(24, 'hostSyslogCheck', 'report', 'Syslog Check', '1', 'Syslog Check report.', 4, 'off'),
(25, 'hostConfigurationIssues', 'report', 'configuration issues', '1', 'configuration issues report.', 4, 'off'),
(26, 'hostHardwareStatus', 'report', 'Hardware Status', '1', 'Hardware Status report.', 4, 'off'),
(27, 'hostRebootrequired', 'report', 'Reboot required', '1', 'Reboot required report.', 4, 'off'),
(28, 'hostFQDNHostnameMismatch', 'report', 'FQDN/hostname mismatch', '1', 'FQDN/hostname mismatch report.', 4, 'off'),
(29, 'hostMaintenanceMode', 'report', 'Maintenance Mode', '1', 'maintenance mode report.', 4, 'off'),
(30, 'hostballooningzipswap', 'report', 'ballooning/zip/swap', '0', 'ballooning/zip/swap report.', 4, 'off'),
(31, 'hostPowerManagementPolicy', 'report', 'PowerManagement Policy', '1', 'PowerManagement Policy report.', 4, 'off'),
(32, 'hostBundlebackup', 'action', 'Bundle backup', '0', 'Bundle backup report.', 4, 'off'),
(33, 'datastoreSpacereport', 'report', 'Space report', '1', 'Space report.', 5, 'off'),
(34, 'datastoreOrphanedVMFilesreport', 'report', 'Orphaned VM Files report', '0', 'Orphaned VM Files report report.', 5, 'off'),
(35, 'datastoreOverallocation', 'report', 'Overallocation', '1', 'Overallocation report.', 5, 'off'),
(36, 'datastoreSIOCdisabled', 'report', 'SIOC disabled', '1', 'SIOC disabled report.', 5, 'off'),
(37, 'datastoremaintenancemode', 'report', 'maintenance mode', '1', 'maintenance mode report.', 5, 'off'),
(38, 'datastoreAccessible', 'report', 'Accessible', '1', 'Accessible report.', 5, 'off'),
(39, 'networkDVSportsfree', 'report', 'DVS ports free', '1', 'DVS ports free report.', 6, 'off'),
(40, 'networkDVPGAutoExpand', 'action', 'DVPG AutoExpand', '0', 'DVPG AutoExpand action.', 6, 'off'),
(41, 'networkDVSprofile', 'report', 'DVS profile', '0', 'DVS profile report.', 6, 'off'),
(42, 'vmSnapshotsage', 'report', 'Snapshots age', '1', 'Snapshots age report.', 7, 'off'),
(43, 'vmphantomsnapshot', 'report', 'phantom snapshot', '1', 'phantom snapshot report.', 7, 'off'),
(44, 'vmconsolidationneeded', 'report', 'consolidation needed', '1', 'consolidation needed report.', 7, 'off'),
(45, 'vmcpuramhddreservation', 'report', 'cpu/ram/hdd reservation', '1', 'cpu/ram/hdd reservation report.', 7, 'off'),
(46, 'vmcpuramhddlimits', 'report', 'cpu/ram/hdd limits', '1', 'cpu/ram/hdd limits report.', 7, 'off'),
(47, 'vmcpuramhotadd', 'report', 'cpu/ram hot-add', '1', 'cpu/ram hot-add report.', 7, 'off'),
(48, 'vmToolsPivot', 'report', 'VM Tools Pivot Table', '1', 'Will display a list of all vmtools version group by count.', 7, 'off'),
(49, 'vmvHardwarePivot', 'report', 'vHardware Pivot Table', '1', 'Will display a list of all guest hardware version (VHW) group by count.', 7, 'off'),
(50, 'vmballoonzipswap', 'report', 'balloon/zip/swap', '1', 'balloon/zip/swap report.', 7, 'off'),
(51, 'vmmultiwritermode', 'report', 'multiwriter mode', '1', 'multiwriter mode report.', 7, 'off'),
(52, 'vmNonpersistentmode', 'report', 'Non persistent mode', '1', 'Non persistent mode report.', 7, 'off'),
(53, 'vmscsibussharing', 'report', 'scsi bus sharing', '1', 'scsi bus sharing report.', 7, 'off'),
(54, 'vmInvalidOrInaccessible', 'report', 'VM Invalid Or Inaccessible', '1', 'This module will display VMs that are marked as inaccessible or invalid.', 7, 'off'),
(55, 'vmInconsistent', 'report', 'Inconsistent Folder', '1', 'The following VMs are not stored in folders consistent to their names, this may cause issues when trying to locate them from the datastore manually.', 7, 'off'),
(56, 'vmRemovableConnected', 'report', 'Removable Connected', '1', 'This module will display VM that have removable devices (floppy, CD-Rom, ...) connected.', 7, 'off'),
(57, 'vmGuestIdMismatch', 'report', 'GuestId mismatch', '1', 'GuestId mismatch report.', 7, 'off'),
(58, 'vmPoweredOff', 'report', 'Powered Off', '1', 'This module will display VM that are Powered Off. This can be useful to check if this state is expected.', 7, 'off'),
(59, 'vmGuestPivot', 'report', 'GuestID Pivot Table', '1', 'Will display a list of all guest OS group by count.', 7, 'off'),
(60, 'vmMisnamed', 'report', 'Misnamed based on FQDN', '1', 'Will display VM that have FQDN mismatched with the VM object name.', 7, 'off'),
(61, 'VSANHealthCheck', 'report', 'VSAN Health Check', '0', 'Display VSAN information about Health Check.', 1, 'off'),
(62, 'alarms', 'report', 'Alarms', '1', 'Will display triggered alarms on objects level with status and time of creation.', 8, 'off');


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
