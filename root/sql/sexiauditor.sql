-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 26, 2016 at 10:24 AM
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
  `moref` varchar(255) CHARACTER SET latin1 NOT NULL,
  `entityMoRef` varchar(255) CHARACTER SET latin1 NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `time` datetime NOT NULL,
  `status` varchar(50) CHARACTER SET latin1 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

DROP TABLE IF EXISTS `certificates`;
CREATE TABLE IF NOT EXISTS `certificates` (
`id` int(11) NOT NULL,
  `vcenter` int(11) NOT NULL,
  `url` varchar(255) CHARACTER SET latin1 NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `type` varchar(255) CHARACTER SET latin1 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clusters`
--

DROP TABLE IF EXISTS `clusters`;
CREATE TABLE IF NOT EXISTS `clusters` (
`id` int(11) NOT NULL,
  `vcenter` int(11) NOT NULL,
  `moref` varchar(255) CHARACTER SET latin1 NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `vmotion` int(11) NOT NULL,
  `dasenabled` tinyint(1) NOT NULL,
  `lastconfigissuetime` datetime NOT NULL,
  `lastconfigissue` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `configurationissues`
--

DROP TABLE IF EXISTS `configurationissues`;
CREATE TABLE IF NOT EXISTS `configurationissues` (
`id` int(11) NOT NULL,
  `host` int(11) NOT NULL,
  `configissue` varchar(255) CHARACTER SET latin1 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `datastores`
--

DROP TABLE IF EXISTS `datastores`;
CREATE TABLE IF NOT EXISTS `datastores` (
`id` int(11) NOT NULL,
  `vcenter` int(11) NOT NULL,
  `moref` varchar(255) CHARACTER SET latin1 NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `type` varchar(50) CHARACTER SET latin1 NOT NULL,
  `size` bigint(20) NOT NULL,
  `uncommitted` bigint(20) NOT NULL,
  `iormConfiguration` tinyint(1) NOT NULL,
  `freespace` bigint(20) NOT NULL,
  `maintenanceMode` varchar(50) CHARACTER SET latin1 NOT NULL,
  `isAccessible` tinyint(1) NOT NULL,
  `shared` tinyint(1) NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=318 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `distributedvirtualportgroups`
--

DROP TABLE IF EXISTS `distributedvirtualportgroups`;
CREATE TABLE IF NOT EXISTS `distributedvirtualportgroups` (
`id` int(11) NOT NULL,
  `vcenter` int(11) NOT NULL,
  `moref` varchar(255) CHARACTER SET latin1 NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `numports` int(11) NOT NULL,
  `openports` int(11) NOT NULL,
  `autoexpand` tinyint(1) NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `executiontime`
--

DROP TABLE IF EXISTS `executiontime`;
CREATE TABLE IF NOT EXISTS `executiontime` (
`id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `seconds` smallint(6) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hardwarestatus`
--

DROP TABLE IF EXISTS `hardwarestatus`;
CREATE TABLE IF NOT EXISTS `hardwarestatus` (
`id` int(11) NOT NULL,
  `host` int(11) NOT NULL,
  `issuename` varchar(255) CHARACTER SET latin1 NOT NULL,
  `issuestate` varchar(255) CHARACTER SET latin1 NOT NULL,
  `issuetype` varchar(255) CHARACTER SET latin1 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hosts`
--

DROP TABLE IF EXISTS `hosts`;
CREATE TABLE IF NOT EXISTS `hosts` (
`id` int(11) NOT NULL,
  `vcenter` int(11) NOT NULL,
  `cluster` int(11) DEFAULT NULL,
  `moref` varchar(255) CHARACTER SET latin1 NOT NULL,
  `hostname` varchar(255) CHARACTER SET latin1 NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `ntpservers` varchar(255) CHARACTER SET latin1 NOT NULL,
  `deadlunpathcount` int(11) NOT NULL,
  `numcpucore` int(11) NOT NULL,
  `syslog_target` varchar(255) CHARACTER SET latin1 NOT NULL,
  `rebootrequired` tinyint(1) NOT NULL,
  `powerpolicy` varchar(255) CHARACTER SET latin1 NOT NULL,
  `bandwidthcapacity` int(11) NOT NULL,
  `memory` bigint(20) NOT NULL,
  `dnsservers` varchar(255) CHARACTER SET latin1 NOT NULL,
  `cputype` varchar(255) CHARACTER SET latin1 NOT NULL,
  `numcpu` int(11) NOT NULL,
  `inmaintenancemode` tinyint(1) NOT NULL,
  `lunpathcount` int(11) NOT NULL,
  `model` varchar(255) CHARACTER SET latin1 NOT NULL,
  `sharedmemory` int(11) NOT NULL,
  `cpumhz` int(11) NOT NULL,
  `esxbuild` varchar(255) CHARACTER SET latin1 NOT NULL,
  `ssh_policy` varchar(255) CHARACTER SET latin1 NOT NULL,
  `shell_policy` varchar(255) CHARACTER SET latin1 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=397 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `licenses`
--

DROP TABLE IF EXISTS `licenses`;
CREATE TABLE IF NOT EXISTS `licenses` (
`id` int(11) NOT NULL,
  `licenseKey` varchar(255) CHARACTER SET latin1 NOT NULL,
  `vcenter` int(11) NOT NULL,
  `costUnit` varchar(255) CHARACTER SET latin1 NOT NULL,
  `editionKey` varchar(255) CHARACTER SET latin1 NOT NULL,
  `used` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
`id` int(11) NOT NULL,
  `sessionKey` varchar(255) CHARACTER SET latin1 NOT NULL,
  `vcenter` int(11) NOT NULL,
  `lastActiveTime` datetime NOT NULL,
  `userName` varchar(255) CHARACTER SET latin1 NOT NULL,
  `loginTime` datetime NOT NULL,
  `ipAddress` varchar(255) CHARACTER SET latin1 NOT NULL,
  `userAgent` varchar(255) CHARACTER SET latin1 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `snapshots`
--

DROP TABLE IF EXISTS `snapshots`;
CREATE TABLE IF NOT EXISTS `snapshots` (
`id` int(11) NOT NULL,
  `vm` int(11) NOT NULL,
  `moref` varchar(255) CHARACTER SET latin1 NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `createTime` datetime NOT NULL,
  `snapid` int(11) NOT NULL,
  `description` varchar(255) CHARACTER SET latin1 NOT NULL,
  `quiesced` tinyint(1) NOT NULL,
  `state` varchar(255) CHARACTER SET latin1 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vcenters`
--

DROP TABLE IF EXISTS `vcenters`;
CREATE TABLE IF NOT EXISTS `vcenters` (
`id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vms`
--

DROP TABLE IF EXISTS `vms`;
CREATE TABLE IF NOT EXISTS `vms` (
`id` int(11) NOT NULL,
  `host` int(11) NOT NULL,
  `memReservation` int(11) NOT NULL,
  `guestFamily` varchar(255) CHARACTER SET latin1 NOT NULL,
  `ip` varchar(255) CHARACTER SET latin1 NOT NULL,
  `swappedMemory` int(11) NOT NULL,
  `cpuLimit` int(11) NOT NULL,
  `datastore` varchar(255) CHARACTER SET latin1 NOT NULL,
  `moref` varchar(255) CHARACTER SET latin1 NOT NULL,
  `consolidationNeeded` tinyint(1) NOT NULL,
  `fqdn` varchar(255) CHARACTER SET latin1 NOT NULL,
  `numcpu` int(11) NOT NULL,
  `cpuReservation` int(11) NOT NULL,
  `sharedBus` tinyint(1) NOT NULL,
  `portgroup` varchar(255) CHARACTER SET latin1 NOT NULL,
  `memory` int(11) NOT NULL,
  `phantomSnapshot` int(11) NOT NULL,
  `hwversion` varchar(20) CHARACTER SET latin1 NOT NULL,
  `provisionned` int(11) NOT NULL,
  `mac` varchar(255) CHARACTER SET latin1 NOT NULL,
  `multiwriter` tinyint(1) NOT NULL,
  `memHotAddEnabled` tinyint(1) NOT NULL,
  `guestOS` varchar(255) CHARACTER SET latin1 NOT NULL,
  `compressedMemory` int(11) NOT NULL,
  `removable` tinyint(1) NOT NULL,
  `commited` int(11) NOT NULL,
  `vmpath` varchar(255) CHARACTER SET latin1 NOT NULL,
  `balloonedMemory` int(11) NOT NULL,
  `vmtools` varchar(255) CHARACTER SET latin1 NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `configGuestId` varchar(255) CHARACTER SET latin1 NOT NULL,
  `memLimit` int(11) NOT NULL,
  `vmxpath` varchar(255) CHARACTER SET latin1 NOT NULL,
  `connectionState` varchar(255) CHARACTER SET latin1 NOT NULL,
  `cpuHotAddEnabled` tinyint(1) NOT NULL,
  `uncommited` int(11) NOT NULL,
  `powerState` varchar(255) CHARACTER SET latin1 NOT NULL,
  `guestId` varchar(255) CHARACTER SET latin1 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1295 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
 ADD PRIMARY KEY (`id`), ADD KEY `id` (`id`), ADD KEY `vcenter` (`vcenter`), ADD KEY `vcenter_2` (`vcenter`), ADD KEY `cluster` (`cluster`), ADD KEY `moref` (`moref`), ADD KEY `name` (`name`), ADD KEY `hostname` (`hostname`);

--
-- Indexes for table `licenses`
--
ALTER TABLE `licenses`
 ADD PRIMARY KEY (`id`), ADD KEY `vcenter` (`vcenter`), ADD KEY `vcenter_2` (`vcenter`);

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
-- Indexes for table `vcenters`
--
ALTER TABLE `vcenters`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vms`
--
ALTER TABLE `vms`
 ADD PRIMARY KEY (`id`), ADD KEY `host` (`host`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alarms`
--
ALTER TABLE `alarms`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=34;
--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `clusters`
--
ALTER TABLE `clusters`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT for table `configurationissues`
--
ALTER TABLE `configurationissues`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `datastores`
--
ALTER TABLE `datastores`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=318;
--
-- AUTO_INCREMENT for table `distributedvirtualportgroups`
--
ALTER TABLE `distributedvirtualportgroups`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `executiontime`
--
ALTER TABLE `executiontime`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=33;
--
-- AUTO_INCREMENT for table `hardwarestatus`
--
ALTER TABLE `hardwarestatus`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `hosts`
--
ALTER TABLE `hosts`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=397;
--
-- AUTO_INCREMENT for table `licenses`
--
ALTER TABLE `licenses`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=47;
--
-- AUTO_INCREMENT for table `snapshots`
--
ALTER TABLE `snapshots`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `vcenters`
--
ALTER TABLE `vcenters`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=57;
--
-- AUTO_INCREMENT for table `vms`
--
ALTER TABLE `vms`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1295;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `alarms`
--
ALTER TABLE `alarms`
ADD CONSTRAINT `alarms_ibfk_1` FOREIGN KEY (`vcenter`) REFERENCES `vcenters` (`id`);

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`vcenter`) REFERENCES `vcenters` (`id`);

--
-- Constraints for table `clusters`
--
ALTER TABLE `clusters`
ADD CONSTRAINT `clusters_ibfk_1` FOREIGN KEY (`vcenter`) REFERENCES `vcenters` (`id`);

--
-- Constraints for table `configurationissues`
--
ALTER TABLE `configurationissues`
ADD CONSTRAINT `configurationissues_ibfk_1` FOREIGN KEY (`host`) REFERENCES `hosts` (`id`);

--
-- Constraints for table `datastores`
--
ALTER TABLE `datastores`
ADD CONSTRAINT `datastores_ibfk_1` FOREIGN KEY (`vcenter`) REFERENCES `vcenters` (`id`);

--
-- Constraints for table `hardwarestatus`
--
ALTER TABLE `hardwarestatus`
ADD CONSTRAINT `hardwarestatus_ibfk_1` FOREIGN KEY (`host`) REFERENCES `hosts` (`id`);

--
-- Constraints for table `hosts`
--
ALTER TABLE `hosts`
ADD CONSTRAINT `hosts_ibfk_1` FOREIGN KEY (`vcenter`) REFERENCES `vcenters` (`id`);

--
-- Constraints for table `licenses`
--
ALTER TABLE `licenses`
ADD CONSTRAINT `licenses_ibfk_1` FOREIGN KEY (`vcenter`) REFERENCES `vcenters` (`id`);

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`vcenter`) REFERENCES `vcenters` (`id`);

--
-- Constraints for table `snapshots`
--
ALTER TABLE `snapshots`
ADD CONSTRAINT `snapshots_ibfk_1` FOREIGN KEY (`vm`) REFERENCES `vms` (`id`);

--
-- Constraints for table `vms`
--
ALTER TABLE `vms`
ADD CONSTRAINT `vms_ibfk_1` FOREIGN KEY (`host`) REFERENCES `hosts` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
