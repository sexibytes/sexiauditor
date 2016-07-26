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
-- Table structure for table `moduleSchedule`
--

CREATE TABLE IF NOT EXISTS `moduleSchedule` (
  `id` varchar(50) CHARACTER SET latin1 NOT NULL,
  `schedule` varchar(50) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `moduleSchedule`
--

INSERT INTO `moduleSchedule` (`id`, `schedule`) VALUES
('alarms', 'daily'),
('clusterAdmissionControl', 'daily'),
('clusterAutoSlotSize', 'daily'),
('clusterConfigurationIssues', 'daily'),
('clusterCPURatio', 'daily'),
('clusterDatastoreConsistency', 'daily'),
('clusterHAStatus', 'daily'),
('clusterMembersLUNPathCountMismatch', 'daily'),
('clusterMembersOvercommit', 'daily'),
('clusterMembersVersion', 'daily'),
('clusterProfile', 'daily'),
('clusterTPSSavings', 'daily'),
('datastoreAccessible', 'daily'),
('datastoremaintenancemode', 'daily'),
('datastoreOrphanedVMFilesreport', 'daily'),
('datastoreOverallocation', 'daily'),
('datastoreSIOCdisabled', 'daily'),
('datastoreSpacereport', 'daily'),
('hostballooningzipswap', 'daily'),
('hostConfigurationIssues', 'daily'),
('hostDNSCheck', 'daily'),
('hostFQDNHostnameMismatch', 'daily'),
('hostHardwareStatus', 'daily'),
('hostLocalSwapDatastoreCompliance', 'daily'),
('hostLUNPathDead', 'daily'),
('hostMaintenanceMode', 'daily'),
('hostNTPCheck', 'daily'),
('hostPowerManagementPolicy', 'daily'),
('hostProfileCompliance', 'daily'),
('hostRebootrequired', 'daily'),
('hostSshShell', 'daily'),
('hostSyslogCheck', 'daily'),
('networkDVSportsfree', 'daily'),
('networkDVSprofile', 'daily'),
('vcCertificatesReport', 'daily'),
('vcLicenceReport', 'daily'),
('vcSessionAge', 'daily'),
('vmballoonzipswap', 'daily'),
('vmconsolidationneeded', 'daily'),
('vmcpuramhddlimits', 'daily'),
('vmcpuramhddreservation', 'daily'),
('vmcpuramhotadd', 'daily'),
('vmGuestIdMismatch', 'daily'),
('vmGuestPivot', 'daily'),
('vmInconsistent', 'daily'),
('vmInvalidOrInaccessible', 'daily'),
('vmMisnamed', 'daily'),
('vmmultiwritermode', 'daily'),
('vmNonpersistentmode', 'daily'),
('vmphantomsnapshot', 'daily'),
('vmPoweredOff', 'daily'),
('vmRemovableConnected', 'daily'),
('vmscsibussharing', 'daily'),
('vmSnapshotsage', 'daily'),
('vmToolsPivot', 'daily'),
('vmvHardwarePivot', 'daily'),
('VSANHealthCheck', 'daily'),
('inventory', 'hourly'),
('hostBundlebackup', 'off'),
('networkDVPGAutoExpand', 'off'),
('vcPermissionReport', 'off'),
('vcTerminateSession', 'off');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `moduleSchedule`
--
ALTER TABLE `moduleSchedule`
 ADD PRIMARY KEY (`id`), ADD KEY `schedule` (`schedule`), ADD KEY `schedule_2` (`schedule`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
