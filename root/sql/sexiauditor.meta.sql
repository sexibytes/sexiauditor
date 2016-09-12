-- --------------------------------------------------------
-- Host:                         sexiauditor.sexibyt.es
-- Server version:               10.0.26-MariaDB-0+deb8u1 - (Debian)
-- Server OS:                    debian-linux-gnu
-- HeidiSQL Version:             9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for sexiauditor
CREATE DATABASE IF NOT EXISTS `sexiauditor` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `sexiauditor`;


-- Dumping structure for table sexiauditor.alarms
DROP TABLE IF EXISTS `alarms`;
CREATE TABLE IF NOT EXISTS `alarms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vcenter` int(11) NOT NULL,
  `moref` varchar(100) CHARACTER SET utf8 NOT NULL,
  `entityMoRef` varchar(100) CHARACTER SET utf8 NOT NULL,
  `alarm_name` varchar(100) CHARACTER SET utf8 NOT NULL,
  `time` datetime NOT NULL,
  `status` varchar(50) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vcenter` (`vcenter`),
  KEY `moref` (`moref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.certificates
DROP TABLE IF EXISTS `certificates`;
CREATE TABLE IF NOT EXISTS `certificates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vcenter` int(11) NOT NULL,
  `url` varchar(255) CHARACTER SET utf8 NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `type` varchar(100) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vcenter` (`vcenter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.clusterMetrics
DROP TABLE IF EXISTS `clusterMetrics`;
CREATE TABLE IF NOT EXISTS `clusterMetrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cluster_id` int(11) NOT NULL,
  `vmotion` int(11) NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cluster_id` (`cluster_id`),
  KEY `firstseen_lastseen` (`firstseen`,`lastseen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.clusters
DROP TABLE IF EXISTS `clusters`;
CREATE TABLE IF NOT EXISTS `clusters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vcenter` int(11) NOT NULL,
  `moref` varchar(100) CHARACTER SET utf8 NOT NULL,
  `cluster_name` varchar(100) CHARACTER SET utf8 NOT NULL,
  `dasenabled` tinyint(1) NOT NULL,
  `lastconfigissuetime` datetime NOT NULL,
  `lastconfigissue` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `isAdmissionEnable` tinyint(1) NOT NULL,
  `admissionModel` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `admissionThreshold` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `admissionValue` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vcenter` (`vcenter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.clustersVSAN
DROP TABLE IF EXISTS `clustersVSAN`;
CREATE TABLE IF NOT EXISTS `clustersVSAN` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cluster_id` int(11) NOT NULL,
  `autohclupdate` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `hcldbuptodate` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `controlleronhcl` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `controllerreleasesupport` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `controllerdriver` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `clusterpartition` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `vmknicconfigured` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `matchingsubnets` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `matchingmulticast` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `physdiskoverall` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `physdiskmetadata` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `physdisksoftware` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `physdiskcongestion` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `healthversion` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `advcfgsync` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `clomdliveness` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `diskbalance` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `upgradesoftware` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `upgradelowerhosts` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cluster_id` (`cluster_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.config
DROP TABLE IF EXISTS `config`;
CREATE TABLE IF NOT EXISTS `config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `configid` varchar(50) CHARACTER SET utf8 NOT NULL,
  `type` int(11) NOT NULL,
  `label` varchar(255) CHARACTER SET utf8 NOT NULL,
  `value` varchar(50) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `configid` (`configid`),
  CONSTRAINT `config_ibfk_1` FOREIGN KEY (`type`) REFERENCES `configtype` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.configtype
DROP TABLE IF EXISTS `configtype`;
CREATE TABLE IF NOT EXISTS `configtype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.configurationissues
DROP TABLE IF EXISTS `configurationissues`;
CREATE TABLE IF NOT EXISTS `configurationissues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` int(11) NOT NULL,
  `configissue` varchar(255) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `host` (`host`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.datastoreMetrics
DROP TABLE IF EXISTS `datastoreMetrics`;
CREATE TABLE IF NOT EXISTS `datastoreMetrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datastore_id` int(11) NOT NULL,
  `size` bigint(20) NOT NULL,
  `freespace` bigint(20) NOT NULL,
  `uncommitted` bigint(20) NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `datastore_id` (`datastore_id`),
  KEY `firstseen_lastseen` (`firstseen`,`lastseen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.datastores
DROP TABLE IF EXISTS `datastores`;
CREATE TABLE IF NOT EXISTS `datastores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vcenter` int(11) NOT NULL,
  `moref` varchar(100) CHARACTER SET utf8 NOT NULL,
  `datastore_name` varchar(100) CHARACTER SET utf8 NOT NULL,
  `type` varchar(50) CHARACTER SET utf8 NOT NULL,
  `iormConfiguration` tinyint(1) NOT NULL,
  `maintenanceMode` varchar(10) CHARACTER SET utf8 NOT NULL,
  `isAccessible` tinyint(1) NOT NULL,
  `shared` tinyint(1) NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vcenter` (`vcenter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.distributedvirtualportgroups
DROP TABLE IF EXISTS `distributedvirtualportgroups`;
CREATE TABLE IF NOT EXISTS `distributedvirtualportgroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vcenter` int(11) NOT NULL,
  `moref` varchar(100) CHARACTER SET utf8 NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 NOT NULL,
  `numports` int(11) NOT NULL,
  `openports` int(11) NOT NULL,
  `autoexpand` tinyint(1) NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vcenter` (`vcenter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.executiontime
DROP TABLE IF EXISTS `executiontime`;
CREATE TABLE IF NOT EXISTS `executiontime` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `seconds` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.hardwarestatus
DROP TABLE IF EXISTS `hardwarestatus`;
CREATE TABLE IF NOT EXISTS `hardwarestatus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` int(11) NOT NULL,
  `issuename` varchar(255) CHARACTER SET utf8 NOT NULL,
  `issuestate` varchar(255) CHARACTER SET utf8 NOT NULL,
  `issuetype` varchar(255) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `host` (`host`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.hostMetrics
DROP TABLE IF EXISTS `hostMetrics`;
CREATE TABLE IF NOT EXISTS `hostMetrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host_id` int(11) NOT NULL,
  `sharedmemory` bigint(20) NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `host_id` (`host_id`),
  KEY `firstseen_lastseen` (`firstseen`,`lastseen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.hosts
DROP TABLE IF EXISTS `hosts`;
CREATE TABLE IF NOT EXISTS `hosts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vcenter` int(11) NOT NULL,
  `cluster` int(11) DEFAULT NULL,
  `moref` varchar(100) CHARACTER SET utf8 NOT NULL,
  `hostname` varchar(100) CHARACTER SET utf8 NOT NULL,
  `host_name` varchar(100) CHARACTER SET utf8 NOT NULL,
  `ntpservers` varchar(255) CHARACTER SET utf8 NOT NULL,
  `deadlunpathcount` int(11) NOT NULL,
  `numcpucore` int(11) NOT NULL,
  `syslog_target` varchar(255) CHARACTER SET utf8 NOT NULL,
  `rebootrequired` tinyint(1) NOT NULL,
  `powerpolicy` varchar(100) CHARACTER SET utf8 NOT NULL,
  `bandwidthcapacity` int(11) NOT NULL,
  `memory` bigint(20) NOT NULL,
  `dnsservers` varchar(255) CHARACTER SET utf8 NOT NULL,
  `cputype` varchar(100) CHARACTER SET utf8 NOT NULL,
  `numcpu` int(11) NOT NULL,
  `inmaintenancemode` tinyint(1) NOT NULL,
  `lunpathcount` int(11) NOT NULL,
  `datastorecount` int(11) NOT NULL,
  `model` varchar(100) CHARACTER SET utf8 NOT NULL,
  `cpumhz` int(11) NOT NULL,
  `esxbuild` varchar(100) CHARACTER SET utf8 NOT NULL,
  `ssh_policy` varchar(100) CHARACTER SET utf8 NOT NULL,
  `shell_policy` varchar(100) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vcenter` (`vcenter`),
  KEY `cluster` (`cluster`),
  KEY `moref` (`moref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.licenses
DROP TABLE IF EXISTS `licenses`;
CREATE TABLE IF NOT EXISTS `licenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `licenseKey` varchar(50) CHARACTER SET utf8 NOT NULL,
  `vcenter` int(11) NOT NULL,
  `costUnit` varchar(100) CHARACTER SET utf8 NOT NULL,
  `editionKey` varchar(100) CHARACTER SET utf8 NOT NULL,
  `used` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vcenter` (`vcenter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.moduleCategory
DROP TABLE IF EXISTS `moduleCategory`;
CREATE TABLE IF NOT EXISTS `moduleCategory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(50) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.modules
DROP TABLE IF EXISTS `modules`;
CREATE TABLE IF NOT EXISTS `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(50) CHARACTER SET utf8 NOT NULL,
  `type` varchar(10) CHARACTER SET utf8 NOT NULL,
  `displayName` varchar(100) CHARACTER SET utf8 NOT NULL,
  `version` decimal(10,0) NOT NULL,
  `description` varchar(255) CHARACTER SET utf8 NOT NULL,
  `category_id` int(11) NOT NULL,
  `schedule` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.orphanFiles
DROP TABLE IF EXISTS `orphanFiles`;
CREATE TABLE IF NOT EXISTS `orphanFiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vcenter` int(11) NOT NULL,
  `filePath` varchar(255) CHARACTER SET utf8 NOT NULL,
  `fileSize` bigint(20) NOT NULL,
  `fileModification` datetime NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vcenter` (`vcenter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.permissions
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vcenter` int(11) NOT NULL,
  `principal` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `role_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `isGroup` tinyint(1) NOT NULL,
  `inventory_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vcenter` (`vcenter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.roles
DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(10) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.sessions
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionKey` varchar(100) CHARACTER SET utf8 NOT NULL,
  `vcenter` int(11) NOT NULL,
  `lastActiveTime` datetime NOT NULL,
  `userName` varchar(100) CHARACTER SET utf8 NOT NULL,
  `loginTime` datetime NOT NULL,
  `ipAddress` varchar(50) CHARACTER SET utf8 NOT NULL,
  `userAgent` varchar(50) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vcenter` (`vcenter`),
  KEY `sessionKey` (`sessionKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.snapshots
DROP TABLE IF EXISTS `snapshots`;
CREATE TABLE IF NOT EXISTS `snapshots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vm` int(11) NOT NULL,
  `moref` varchar(100) CHARACTER SET utf8 NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 NOT NULL,
  `createTime` datetime NOT NULL,
  `snapid` int(11) NOT NULL,
  `description` varchar(255) CHARACTER SET latin1 NOT NULL,
  `quiesced` tinyint(1) NOT NULL,
  `state` varchar(50) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vm` (`vm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) CHARACTER SET utf8 NOT NULL,
  `displayname` varchar(255) CHARACTER SET utf8 NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 NOT NULL,
  `role` int(11) NOT NULL,
  `password` char(128) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.vcenters
DROP TABLE IF EXISTS `vcenters`;
CREATE TABLE IF NOT EXISTS `vcenters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vcname` varchar(255) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.vmMetrics
DROP TABLE IF EXISTS `vmMetrics`;
CREATE TABLE IF NOT EXISTS `vmMetrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vm_id` int(11) NOT NULL,
  `swappedMemory` int(11) NOT NULL,
  `compressedMemory` int(11) NOT NULL,
  `commited` int(11) NOT NULL,
  `balloonedMemory` int(11) NOT NULL,
  `uncommited` int(11) NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vm_id` (`vm_id`),
  KEY `firstseen_lastseen` (`firstseen`,`lastseen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table sexiauditor.vms
DROP TABLE IF EXISTS `vms`;
CREATE TABLE IF NOT EXISTS `vms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vcenter` int(11) NOT NULL,
  `host` int(11) NOT NULL,
  `memReservation` int(11) NOT NULL,
  `guestFamily` varchar(100) CHARACTER SET utf8 NOT NULL,
  `ip` varchar(255) CHARACTER SET utf8 NOT NULL,
  `cpuLimit` int(11) NOT NULL,
  `datastore` int(11) NOT NULL,
  `moref` varchar(100) CHARACTER SET utf8 NOT NULL,
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
  `guestOS` varchar(100) CHARACTER SET utf8 NOT NULL,
  `removable` tinyint(1) NOT NULL,
  `vmpath` varchar(255) CHARACTER SET utf8 NOT NULL,
  `vmtools` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `configGuestId` varchar(100) CHARACTER SET utf8 NOT NULL,
  `memLimit` int(11) NOT NULL,
  `vmxpath` varchar(255) CHARACTER SET utf8 NOT NULL,
  `connectionState` varchar(20) CHARACTER SET utf8 NOT NULL,
  `cpuHotAddEnabled` tinyint(1) NOT NULL,
  `powerState` varchar(20) CHARACTER SET utf8 NOT NULL,
  `guestId` varchar(100) CHARACTER SET utf8 NOT NULL,
  `firstseen` datetime NOT NULL,
  `lastseen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vcenter` (`vcenter`),
  KEY `firstseen_lastseen` (`firstseen`,`lastseen`),
  KEY `host` (`host`,`moref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
