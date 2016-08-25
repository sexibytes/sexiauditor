-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 25, 2016 at 12:13 PM
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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clusterMetrics`
--

DROP TABLE IF EXISTS `clusterMetrics`;
CREATE TABLE IF NOT EXISTS `clusterMetrics` (
`id` int(11) NOT NULL,
  `cluster_id` int(11) NOT NULL,
  `vmotion` int(11) NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `dasenabled` tinyint(1) NOT NULL,
  `lastconfigissuetime` datetime NOT NULL,
  `lastconfigissue` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `isAdmissionEnable` tinyint(1) NOT NULL,
  `admissionModel` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `admissionThreshold` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `admissionValue` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `configtype`
--

DROP TABLE IF EXISTS `configtype`;
CREATE TABLE IF NOT EXISTS `configtype` (
`id` int(11) NOT NULL,
  `type` varchar(50) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `datastoreMetrics`
--

DROP TABLE IF EXISTS `datastoreMetrics`;
CREATE TABLE IF NOT EXISTS `datastoreMetrics` (
`id` int(11) NOT NULL,
  `datastore_id` int(11) NOT NULL,
  `size` bigint(20) NOT NULL,
  `freespace` bigint(20) NOT NULL,
  `uncommitted` bigint(20) NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `iormConfiguration` tinyint(1) NOT NULL,
  `maintenanceMode` varchar(50) CHARACTER SET utf8 NOT NULL,
  `isAccessible` tinyint(1) NOT NULL,
  `shared` tinyint(1) NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `executiontime`
--

DROP TABLE IF EXISTS `executiontime`;
CREATE TABLE IF NOT EXISTS `executiontime` (
`id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `seconds` smallint(6) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hostMetrics`
--

DROP TABLE IF EXISTS `hostMetrics`;
CREATE TABLE IF NOT EXISTS `hostMetrics` (
`id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `sharedmemory` bigint(20) NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `datastorecount` int(11) NOT NULL,
  `model` varchar(255) CHARACTER SET utf8 NOT NULL,
  `cpumhz` int(11) NOT NULL,
  `esxbuild` varchar(255) CHARACTER SET utf8 NOT NULL,
  `ssh_policy` varchar(255) CHARACTER SET utf8 NOT NULL,
  `shell_policy` varchar(255) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `moduleCategory`
--

DROP TABLE IF EXISTS `moduleCategory`;
CREATE TABLE IF NOT EXISTS `moduleCategory` (
`id` int(11) NOT NULL,
  `category` varchar(50) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
`id` int(11) NOT NULL,
  `role` varchar(10) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vmMetrics`
--

DROP TABLE IF EXISTS `vmMetrics`;
CREATE TABLE IF NOT EXISTS `vmMetrics` (
`id` int(11) NOT NULL,
  `vm_id` int(11) NOT NULL,
  `swappedMemory` int(11) NOT NULL,
  `compressedMemory` int(11) NOT NULL,
  `commited` int(11) NOT NULL,
  `balloonedMemory` int(11) NOT NULL,
  `uncommited` int(11) NOT NULL,
  `firstseen` int(11) NOT NULL,
  `lastseen` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `removable` tinyint(1) NOT NULL,
  `vmpath` varchar(255) CHARACTER SET utf8 NOT NULL,
  `vmtools` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `configGuestId` varchar(255) CHARACTER SET utf8 NOT NULL,
  `memLimit` int(11) NOT NULL,
  `vmxpath` varchar(255) CHARACTER SET utf8 NOT NULL,
  `connectionState` varchar(255) CHARACTER SET utf8 NOT NULL,
  `cpuHotAddEnabled` tinyint(1) NOT NULL,
  `powerState` varchar(255) CHARACTER SET utf8 NOT NULL,
  `guestId` varchar(255) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
-- Indexes for table `clusterMetrics`
--
ALTER TABLE `clusterMetrics`
 ADD PRIMARY KEY (`id`);

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
-- Indexes for table `datastoreMetrics`
--
ALTER TABLE `datastoreMetrics`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`), ADD KEY `datastore_id` (`datastore_id`);

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
-- Indexes for table `hostMetrics`
--
ALTER TABLE `hostMetrics`
 ADD PRIMARY KEY (`id`);

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
 ADD PRIMARY KEY (`id`);

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
-- Indexes for table `vmMetrics`
--
ALTER TABLE `vmMetrics`
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
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `clusterMetrics`
--
ALTER TABLE `clusterMetrics`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `clusters`
--
ALTER TABLE `clusters`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `config`
--
ALTER TABLE `config`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=27;
--
-- AUTO_INCREMENT for table `configtype`
--
ALTER TABLE `configtype`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT for table `configurationissues`
--
ALTER TABLE `configurationissues`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `datastoreMetrics`
--
ALTER TABLE `datastoreMetrics`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `datastores`
--
ALTER TABLE `datastores`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `distributedvirtualportgroups`
--
ALTER TABLE `distributedvirtualportgroups`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `executiontime`
--
ALTER TABLE `executiontime`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `hardwarestatus`
--
ALTER TABLE `hardwarestatus`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `hostMetrics`
--
ALTER TABLE `hostMetrics`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `hosts`
--
ALTER TABLE `hosts`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `licenses`
--
ALTER TABLE `licenses`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
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
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `snapshots`
--
ALTER TABLE `snapshots`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `vcenters`
--
ALTER TABLE `vcenters`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `vmMetrics`
--
ALTER TABLE `vmMetrics`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `vms`
--
ALTER TABLE `vms`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
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
