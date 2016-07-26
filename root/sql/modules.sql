-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 26, 2016 at 10:23 AM
-- Server version: 10.0.25-MariaDB-0+deb8u1
-- PHP Version: 5.6.23-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `sexiauditor`
--

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE IF NOT EXISTS `modules` (
`id` int(11) NOT NULL,
  `module﻿_id` varchar(50) CHARACTER SET latin1 NOT NULL,
  `type` varchar(10) CHARACTER SET latin1 NOT NULL,
  `displayName` varchar(255) CHARACTER SET latin1 NOT NULL,
  `version` decimal(10,0) NOT NULL,
  `description` varchar(255) CHARACTER SET latin1 NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `module﻿_id`, `type`, `displayName`, `version`, `description`, `category_id`) VALUES
(1, 'VSANHealthCheck', 'report', 'VSAN Health Check', '0', 'Display VSAN information about Health Check.', 1),
(2, 'vcSessionAge', 'report', 'Session Age', '1', 'Display vCenter session that are older than (x)days.', 2),
(3, 'vcLicenceReport', 'report', 'License Report', '1', 'Display licence consumption based on vCenter licence defined.', 2),
(4, 'vcPermissionReport', 'report', 'Permission report', '0', 'Display permission listing with user/role combination.', 2),
(5, 'vcTerminateSession', 'action', 'Terminate session', '0', '[Action] Kill vCenter session older than (x)days.', 2),
(6, 'vcCertificatesReport', 'report', 'Certificate Report', '0', 'Display soon-to-be-expired certificates for all vCenter components (SSO, WebClient, ...).', 2),
(7, 'clusterConfigurationIssues', 'report', 'Configuration Issues', '1', 'Configuration Issues report.', 3),
(8, 'clusterAdmissionControl', 'report', 'Admission Control', '0', 'Admission Control report.', 3),
(9, 'clusterHAStatus', 'report', 'Cluster Without HA', '1', 'Display cluster without HA.', 3),
(10, 'clusterDatastoreConsistency', 'report', 'Datastore Consistency', '0', 'Datastore Consistency report.', 3),
(11, 'clusterMembersVersion', 'report', 'Members Version', '1', 'Members Version Report.', 3),
(12, 'clusterMembersOvercommit', 'report', 'Members Overcommit', '0', 'Members Overcommit Report.', 3),
(13, 'clusterMembersLUNPathCountMismatch', 'report', 'Members LUN Path count mismatch', '0', 'Members LUN Path count mismatch Report.', 3),
(14, 'clusterCPURatio', 'report', 'vCPU pCPU table', '1', 'vCPU pCPU table Report.', 3),
(15, 'clusterTPSSavings', 'report', 'TPS savings', '0', 'TPS savings Report.', 3),
(16, 'clusterAutoSlotSize', 'report', 'AutoSlotSize', '0', 'AutoSlotSize Report.', 3),
(17, 'clusterProfile', 'report', 'Profile', '0', 'Profile Report.', 3),
(18, 'hostLUNPathDead', 'report', 'LUN Path Dead', '0', 'LUN Path Dead report.', 4),
(19, 'hostProfileCompliance', 'report', 'Profile Compliance', '0', 'Profile Compliance report.', 4),
(20, 'hostLocalSwapDatastoreCompliance', 'report', 'LocalSwapDatastore Compliance', '0', 'LocalSwapDatastore Compliance report.', 4),
(21, 'hostSshShell', 'report', 'SSH/shell check', '0', 'SSH/shell check report.', 4),
(22, 'hostNTPCheck', 'report', 'NTP Check', '1', 'NTP Check report.', 4),
(23, 'hostDNSCheck', 'report', 'DNS Check', '1', 'DNS Check report.', 4),
(24, 'hostSyslogCheck', 'report', 'Syslog Check', '1', 'Syslog Check report.', 4),
(25, 'hostConfigurationIssues', 'report', 'configuration issues', '1', 'configuration issues report.', 4),
(26, 'hostHardwareStatus', 'report', 'Hardware Status', '1', 'Hardware Status report.', 4),
(27, 'hostRebootrequired', 'report', 'Reboot required', '1', 'Reboot required report.', 4),
(28, 'hostFQDNHostnameMismatch', 'report', 'FQDN/hostname mismatch', '1', 'FQDN/hostname mismatch report.', 4),
(29, 'hostMaintenanceMode', 'report', 'Maintenance Mode', '1', 'maintenance mode report.', 4),
(30, 'hostballooningzipswap', 'report', 'ballooning/zip/swap', '0', 'ballooning/zip/swap report.', 4),
(31, 'hostPowerManagementPolicy', 'report', 'PowerManagement Policy', '0', 'PowerManagement Policy report.', 4),
(32, 'hostBundlebackup', 'action', 'Bundle backup', '0', 'Bundle backup report.', 4),
(33, 'datastoreSpacereport', 'report', 'Space report', '1', 'Space report.', 5),
(34, 'datastoreOrphanedVMFilesreport', 'report', 'Orphaned VM Files report', '0', 'Orphaned VM Files report report.', 5),
(35, 'datastoreOverallocation', 'report', 'Overallocation', '1', 'Overallocation report.', 5),
(36, 'datastoreSIOCdisabled', 'report', 'SIOC disabled', '1', 'SIOC disabled report.', 5),
(37, 'datastoremaintenancemode', 'report', 'maintenance mode', '1', 'maintenance mode report.', 5),
(38, 'datastoreAccessible', 'report', 'Accessible', '1', 'Accessible report.', 5),
(39, 'networkDVSportsfree', 'report', 'DVS ports free', '1', 'DVS ports free report.', 6),
(40, 'networkDVPGAutoExpand', 'action', 'DVPG AutoExpand', '0', 'DVPG AutoExpand action.', 6),
(41, 'networkDVSprofile', 'report', 'DVS profile', '0', 'DVS profile report.', 6),
(42, 'vmSnapshotsage', 'report', 'Snapshots age', '1', 'Snapshots age report.', 7),
(43, 'vmphantomsnapshot', 'report', 'phantom snapshot', '1', 'phantom snapshot report.', 7),
(44, 'vmconsolidationneeded', 'report', 'consolidation needed', '1', 'consolidation needed report.', 7),
(45, 'vmcpuramhddreservation', 'report', 'cpu/ram/hdd reservation', '1', 'cpu/ram/hdd reservation report.', 7),
(46, 'vmcpuramhddlimits', 'report', 'cpu/ram/hdd limits', '1', 'cpu/ram/hdd limits report.', 7),
(47, 'vmcpuramhotadd', 'report', 'cpu/ram hot-add', '1', 'cpu/ram hot-add report.', 7),
(48, 'vmToolsPivot', 'report', 'VM Tools Pivot Table', '1', 'Will display a list of all vmtools version group by count.', 7),
(49, 'vmvHardwarePivot', 'report', 'vHardware Pivot Table', '1', 'Will display a list of all guest hardware version (VHW) group by count.', 7),
(50, 'vmballoonzipswap', 'report', 'balloon/zip/swap', '1', 'balloon/zip/swap report.', 7),
(51, 'vmmultiwritermode', 'report', 'multiwriter mode', '1', 'multiwriter mode report.', 7),
(52, 'vmNonpersistentmode', 'report', 'Non persistent mode', '1', 'Non persistent mode report.', 7),
(53, 'vmscsibussharing', 'report', 'scsi bus sharing', '1', 'scsi bus sharing report.', 7),
(54, 'vmInvalidOrInaccessible', 'report', 'VM Invalid Or Inaccessible', '1', 'This module will display VMs that are marked as inaccessible or invalid.', 7),
(55, 'vmInconsistent', 'report', 'Inconsistent Folder', '1', 'The following VMs are not stored in folders consistent to their names, this may cause issues when trying to locate them from the datastore manually.', 7),
(56, 'vmRemovableConnected', 'report', 'Removable Connected', '1', 'This module will display VM that have removable devices (floppy, CD-Rom, ...) connected.', 7),
(57, 'vmGuestIdMismatch', 'report', 'GuestId mismatch', '1', 'GuestId mismatch report.', 7),
(58, 'vmPoweredOff', 'report', 'Powered Off', '1', 'This module will display VM that are Powered Off. This can be useful to check if this state is expected.', 7),
(59, 'vmGuestPivot', 'report', 'GuestID Pivot Table', '1', 'Will display a list of all guest OS group by count.', 7),
(60, 'vmMisnamed', 'report', 'Misnamed based on FQDN', '1', 'Will display VM that have FQDN mismatched with the VM object name.', 7),
(61, 'inventory', 'action', 'Inventory', '1', 'Virtual Machine inventory with a lot of properties. Hosts and Datastores info will be retrieved as well (used in main page stats page).', 8),
(62, 'alarms', 'report', 'Alarms', '1', 'Will display triggered alarms on objects level with status and time of creation.', 8);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
 ADD PRIMARY KEY (`id`), ADD KEY `module﻿_id` (`module﻿_id`), ADD KEY `module﻿_id_2` (`module﻿_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=63;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
