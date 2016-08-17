-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 17, 2016 at 01:49 PM
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
  `active` tinyint(1) NOT NULL,
  `isAdmissionEnable` tinyint(1) NOT NULL,
  `admissionModel` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `admissionThreshold` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `admissionValue` varchar(50) COLLATE utf8_unicode_ci NOT NULL
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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
(17, 'smtpAddress', 2, 'SMTP server address (IP, or FQDN) to forward email to', 'smtp.sexibyte.es'),
(18, 'senderMail', 3, 'Sender email to be used for report export feature', 'sender@sexibyte.es'),
(19, 'recipientMail', 3, 'Recipient email to be used for report export feature', 'frederic@sexibyte.es'),
(20, 'pdfAuthor', 2, 'Username that will be used as the ''Author'' of generated PDFs', 'Gordon Freeman'),
(21, 'showEmpty', 1, 'Show checks that return empty values (ie when there is nothing to report)', 'enable'),
(22, 'showAuthors', 1, 'Show authors page of generated PDF''s, be aware that disabling it may kill some kitten...', 'enable'),
(23, 'sexigrafNode', 2, 'SexiGraf node IP or FQDN (used for Capacity Planning)', 'sexigraf.sexibyte.es'),
(24, 'capacityPlanningDays', 4, 'Number of days Capacity Planning will used for computationcalculation of ''Days Left'' value', '7'),
(25, 'showInfinite', 1, 'Does Capacity Planning display clusters with ''Infinite'' days left ?', 'enable');

-- --------------------------------------------------------

--
-- Table structure for table `configtype`
--

DROP TABLE IF EXISTS `configtype`;
CREATE TABLE IF NOT EXISTS `configtype` (
`id` int(11) NOT NULL,
  `type` varchar(50) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=418 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `executiontime`
--

INSERT INTO `executiontime` (`id`, `date`, `seconds`) VALUES
(1, '2016-08-02 08:19:53', 154),
(2, '2016-08-02 09:00:02', 164),
(3, '2016-08-02 11:00:02', 191),
(4, '2016-08-02 11:05:16', 169),
(5, '2016-08-02 11:13:49', 172),
(6, '2016-08-02 11:18:22', 169),
(7, '2016-08-02 11:21:42', 172),
(8, '2016-08-02 11:31:57', 170),
(9, '2016-08-02 11:37:52', 172),
(10, '2016-08-02 11:42:59', 173),
(11, '2016-08-02 11:49:18', 284),
(12, '2016-08-02 11:54:54', 5),
(13, '2016-08-02 11:56:29', 5),
(14, '2016-08-02 11:58:54', 5),
(15, '2016-08-02 11:59:36', 5),
(16, '2016-08-02 11:59:53', 5),
(17, '2016-08-02 12:00:02', 1),
(18, '2016-08-02 12:02:02', 4),
(19, '2016-08-02 12:02:49', 5),
(20, '2016-08-02 12:03:37', 5),
(21, '2016-08-02 12:05:29', 5),
(22, '2016-08-02 12:06:21', 5),
(23, '2016-08-02 12:07:41', 5),
(24, '2016-08-02 12:07:56', 5),
(25, '2016-08-02 12:08:37', 5),
(26, '2016-08-02 12:08:58', 97),
(27, '2016-08-02 12:11:01', 98),
(28, '2016-08-02 12:14:53', 100),
(29, '2016-08-02 12:18:12', 101),
(30, '2016-08-02 12:20:10', 5),
(31, '2016-08-02 12:20:23', 186),
(32, '2016-08-02 12:24:49', 111),
(33, '2016-08-02 12:37:30', 8),
(34, '2016-08-02 12:37:54', 20),
(35, '2016-08-02 13:26:01', 291),
(36, '2016-08-02 14:00:02', 294),
(37, '2016-08-02 14:17:57', 300),
(38, '2016-08-02 14:27:50', 303),
(39, '2016-08-02 14:44:39', 2),
(40, '2016-08-02 14:44:50', 2),
(41, '2016-08-02 15:00:02', 312),
(42, '2016-08-02 16:00:02', 324),
(43, '2016-08-02 17:00:02', 327),
(44, '2016-08-02 18:00:02', 337),
(45, '2016-08-02 19:00:02', 344),
(46, '2016-08-02 20:00:02', 355),
(47, '2016-08-02 21:00:02', 366),
(48, '2016-08-02 22:00:02', 377),
(49, '2016-08-02 23:00:02', 380),
(50, '2016-08-03 00:00:03', 388),
(51, '2016-08-03 01:00:02', 397),
(52, '2016-08-03 02:00:02', 405),
(53, '2016-08-03 03:00:02', 414),
(54, '2016-08-03 04:00:02', 424),
(55, '2016-08-03 05:00:02', 433),
(56, '2016-08-03 06:00:02', 444),
(57, '2016-08-03 07:00:02', 451),
(58, '2016-08-03 08:16:16', 250),
(59, '2016-08-03 08:21:22', 249),
(60, '2016-08-03 08:43:46', 248),
(61, '2016-08-03 09:56:13', 111),
(62, '2016-08-03 10:00:03', 117),
(63, '2016-08-03 10:00:21', 120),
(64, '2016-08-03 10:08:01', 118),
(65, '2016-08-03 10:20:31', 177),
(66, '2016-08-03 13:00:02', 183),
(67, '2016-08-03 13:43:51', 197),
(68, '2016-08-03 14:00:02', 182),
(69, '2016-08-03 15:13:52', 196),
(70, '2016-08-03 16:00:02', 187),
(71, '2016-08-03 17:00:03', 185),
(72, '2016-08-03 18:00:02', 187),
(73, '2016-08-03 19:00:02', 185),
(74, '2016-08-03 20:00:03', 186),
(75, '2016-08-03 21:00:03', 185),
(76, '2016-08-03 22:00:03', 189),
(77, '2016-08-03 23:00:03', 187),
(78, '2016-08-04 00:00:02', 187),
(79, '2016-08-04 01:00:02', 186),
(80, '2016-08-04 02:00:02', 186),
(81, '2016-08-04 03:00:03', 185),
(82, '2016-08-04 04:00:02', 187),
(83, '2016-08-04 05:00:03', 187),
(84, '2016-08-04 06:00:02', 186),
(85, '2016-08-04 07:00:02', 186),
(86, '2016-08-04 07:43:35', 211),
(87, '2016-08-04 08:00:03', 211),
(88, '2016-08-04 08:39:19', 212),
(89, '2016-08-04 09:00:02', 187),
(90, '2016-08-04 10:00:02', 187),
(91, '2016-08-04 11:00:02', 189),
(92, '2016-08-04 11:10:57', 203),
(93, '2016-08-04 12:00:02', 190),
(94, '2016-08-04 12:43:19', 193),
(95, '2016-08-04 12:49:16', 200),
(96, '2016-08-04 13:04:31', 48),
(97, '2016-08-04 14:11:38', 195),
(98, '2016-08-04 14:19:58', 201),
(99, '2016-08-04 15:00:02', 186),
(100, '2016-08-04 16:00:02', 185),
(101, '2016-08-04 17:00:03', 184),
(102, '2016-08-04 18:00:02', 185),
(103, '2016-08-04 19:00:03', 187),
(104, '2016-08-04 20:00:02', 185),
(105, '2016-08-04 21:00:02', 186),
(106, '2016-08-04 22:00:03', 193),
(107, '2016-08-04 23:00:02', 187),
(108, '2016-08-05 00:00:03', 185),
(109, '2016-08-05 01:00:02', 186),
(110, '2016-08-05 02:00:02', 186),
(111, '2016-08-05 03:00:02', 188),
(112, '2016-08-05 04:00:03', 188),
(113, '2016-08-05 05:00:03', 185),
(114, '2016-08-05 06:00:03', 186),
(115, '2016-08-05 07:00:03', 187),
(116, '2016-08-05 08:00:03', 208),
(117, '2016-08-05 09:00:03', 186),
(118, '2016-08-05 10:00:02', 187),
(119, '2016-08-05 11:00:02', 187),
(120, '2016-08-05 12:00:03', 187),
(121, '2016-08-05 13:00:02', 186),
(122, '2016-08-05 14:00:02', 185),
(123, '2016-08-05 15:00:03', 185),
(124, '2016-08-05 16:00:02', 187),
(125, '2016-08-05 17:00:03', 189),
(126, '2016-08-05 18:00:03', 187),
(127, '2016-08-05 19:00:02', 191),
(128, '2016-08-05 20:00:02', 187),
(129, '2016-08-05 21:00:02', 189),
(130, '2016-08-05 22:00:02', 198),
(131, '2016-08-05 23:00:02', 190),
(132, '2016-08-06 00:00:03', 189),
(133, '2016-08-06 01:00:02', 190),
(134, '2016-08-06 02:00:02', 187),
(135, '2016-08-06 03:00:02', 190),
(136, '2016-08-06 04:00:02', 192),
(137, '2016-08-06 05:00:02', 188),
(138, '2016-08-06 06:00:02', 189),
(139, '2016-08-06 07:00:03', 187),
(140, '2016-08-06 08:00:03', 211),
(141, '2016-08-06 09:00:02', 190),
(142, '2016-08-06 10:00:02', 188),
(143, '2016-08-06 11:00:03', 189),
(144, '2016-08-06 12:00:02', 189),
(145, '2016-08-06 13:00:03', 186),
(146, '2016-08-06 14:00:02', 189),
(147, '2016-08-06 15:00:03', 188),
(148, '2016-08-06 16:00:03', 188),
(149, '2016-08-06 17:00:03', 120),
(150, '2016-08-06 18:00:02', 121),
(151, '2016-08-06 19:00:03', 121),
(152, '2016-08-06 20:00:03', 123),
(153, '2016-08-06 21:00:02', 122),
(154, '2016-08-06 22:00:03', 197),
(155, '2016-08-06 23:00:03', 190),
(156, '2016-08-07 00:00:03', 190),
(157, '2016-08-07 01:00:02', 191),
(158, '2016-08-07 02:00:02', 191),
(159, '2016-08-07 03:00:03', 187),
(160, '2016-08-07 04:00:02', 191),
(161, '2016-08-07 05:00:03', 194),
(162, '2016-08-07 06:00:02', 191),
(163, '2016-08-07 07:00:02', 190),
(164, '2016-08-07 08:00:02', 210),
(165, '2016-08-07 09:00:02', 196),
(166, '2016-08-07 10:00:02', 190),
(167, '2016-08-07 11:00:02', 190),
(168, '2016-08-07 12:00:03', 192),
(169, '2016-08-07 13:00:02', 196),
(170, '2016-08-07 14:00:03', 191),
(171, '2016-08-07 15:00:02', 191),
(172, '2016-08-07 16:00:03', 190),
(173, '2016-08-07 17:00:02', 190),
(174, '2016-08-07 18:00:03', 189),
(175, '2016-08-07 19:00:02', 190),
(176, '2016-08-07 20:00:02', 191),
(177, '2016-08-07 21:00:02', 190),
(178, '2016-08-07 22:00:02', 195),
(179, '2016-08-07 23:00:02', 123),
(180, '2016-08-08 00:00:03', 123),
(181, '2016-08-08 01:00:02', 125),
(182, '2016-08-08 02:00:03', 122),
(183, '2016-08-08 03:00:02', 124),
(184, '2016-08-08 04:00:02', 124),
(185, '2016-08-08 05:00:02', 124),
(186, '2016-08-08 06:00:03', 122),
(187, '2016-08-08 07:00:02', 192),
(188, '2016-08-08 08:00:03', 207),
(189, '2016-08-08 09:00:02', 191),
(190, '2016-08-08 10:00:02', 192),
(191, '2016-08-08 11:00:03', 192),
(192, '2016-08-08 12:00:02', 192),
(193, '2016-08-08 13:00:03', 192),
(194, '2016-08-08 14:00:03', 191),
(195, '2016-08-08 15:00:03', 192),
(196, '2016-08-08 16:00:03', 192),
(197, '2016-08-08 17:00:02', 194),
(198, '2016-08-08 18:00:03', 191),
(199, '2016-08-08 19:00:02', 195),
(200, '2016-08-08 20:00:03', 191),
(201, '2016-08-08 21:00:02', 192),
(202, '2016-08-08 22:00:02', 204),
(203, '2016-08-08 23:00:02', 192),
(204, '2016-08-09 00:00:03', 192),
(205, '2016-08-09 01:00:02', 193),
(206, '2016-08-09 02:00:02', 191),
(207, '2016-08-09 03:00:02', 192),
(208, '2016-08-09 04:00:03', 193),
(209, '2016-08-09 05:00:02', 193),
(210, '2016-08-09 06:00:02', 192),
(211, '2016-08-09 07:00:02', 198),
(212, '2016-08-09 08:00:02', 208),
(213, '2016-08-09 09:00:02', 193),
(214, '2016-08-09 10:00:03', 192),
(215, '2016-08-09 11:00:03', 192),
(216, '2016-08-09 13:19:19', 196),
(217, '2016-08-09 13:25:16', 196),
(218, '2016-08-09 13:51:14', 7),
(219, '2016-08-09 13:54:48', 6),
(220, '2016-08-09 14:33:57', 211),
(221, '2016-08-09 15:01:55', 194),
(222, '2016-08-09 15:08:40', 6),
(223, '2016-08-09 15:09:43', 6),
(224, '2016-08-09 15:11:53', 6),
(225, '2016-08-09 15:13:22', 6),
(226, '2016-08-09 16:00:03', 4),
(227, '2016-08-09 17:00:02', 5),
(228, '2016-08-09 18:00:02', 4),
(229, '2016-08-09 19:00:03', 4),
(230, '2016-08-09 20:00:03', 4),
(231, '2016-08-09 21:00:03', 4),
(232, '2016-08-09 22:00:02', 5),
(233, '2016-08-09 23:00:02', 4),
(234, '2016-08-10 00:00:02', 4),
(235, '2016-08-10 01:00:03', 3),
(236, '2016-08-10 02:00:02', 4),
(237, '2016-08-10 03:00:03', 3),
(238, '2016-08-10 04:00:02', 4),
(239, '2016-08-10 05:00:03', 3),
(240, '2016-08-10 06:00:03', 3),
(241, '2016-08-10 07:00:02', 4),
(242, '2016-08-10 08:00:02', 6),
(243, '2016-08-10 09:00:02', 4),
(244, '2016-08-10 09:34:57', 7),
(245, '2016-08-10 09:36:18', 195),
(246, '2016-08-10 10:00:02', 185),
(247, '2016-08-10 11:00:02', 189),
(248, '2016-08-10 12:00:03', 187),
(249, '2016-08-10 13:00:03', 190),
(250, '2016-08-10 14:00:03', 188),
(251, '2016-08-10 15:00:03', 188),
(252, '2016-08-10 16:00:03', 189),
(253, '2016-08-10 17:00:03', 188),
(254, '2016-08-10 18:00:02', 187),
(255, '2016-08-10 19:00:03', 190),
(256, '2016-08-10 20:00:02', 191),
(257, '2016-08-10 21:00:02', 189),
(258, '2016-08-10 22:00:02', 195),
(259, '2016-08-10 23:00:03', 189),
(260, '2016-08-11 00:00:03', 187),
(261, '2016-08-11 01:00:02', 190),
(262, '2016-08-11 02:00:03', 189),
(263, '2016-08-11 03:00:03', 192),
(264, '2016-08-11 04:00:02', 189),
(265, '2016-08-11 05:00:03', 189),
(266, '2016-08-11 06:00:02', 191),
(267, '2016-08-11 07:00:02', 190),
(268, '2016-08-11 08:00:03', 206),
(269, '2016-08-11 09:00:03', 190),
(270, '2016-08-11 10:00:02', 191),
(271, '2016-08-11 11:00:02', 190),
(272, '2016-08-11 12:00:03', 190),
(273, '2016-08-11 13:00:02', 190),
(274, '2016-08-11 14:00:03', 189),
(275, '2016-08-11 15:00:02', 189),
(276, '2016-08-11 16:00:02', 191),
(277, '2016-08-11 17:00:03', 189),
(278, '2016-08-11 18:00:03', 189),
(279, '2016-08-11 19:00:02', 194),
(280, '2016-08-11 20:00:02', 192),
(281, '2016-08-11 21:00:02', 190),
(282, '2016-08-11 22:00:03', 197),
(283, '2016-08-11 23:00:03', 190),
(284, '2016-08-12 00:00:02', 192),
(285, '2016-08-12 01:00:02', 190),
(286, '2016-08-12 02:00:03', 189),
(287, '2016-08-12 03:00:03', 191),
(288, '2016-08-12 04:00:03', 196),
(289, '2016-08-12 05:00:03', 189),
(290, '2016-08-12 06:00:02', 192),
(291, '2016-08-12 07:00:02', 190),
(292, '2016-08-12 08:00:03', 206),
(293, '2016-08-12 09:00:02', 191),
(294, '2016-08-12 10:00:02', 256),
(295, '2016-08-12 11:00:03', 255),
(296, '2016-08-12 12:00:02', 258),
(297, '2016-08-12 13:00:03', 255),
(298, '2016-08-12 14:00:02', 255),
(299, '2016-08-12 15:00:03', 256),
(300, '2016-08-12 16:00:02', 254),
(301, '2016-08-12 17:00:02', 254),
(302, '2016-08-12 18:00:03', 253),
(303, '2016-08-12 19:00:02', 259),
(304, '2016-08-12 20:00:03', 254),
(305, '2016-08-12 21:00:02', 257),
(306, '2016-08-12 22:00:03', 262),
(307, '2016-08-12 23:00:02', 256),
(308, '2016-08-13 00:00:03', 256),
(309, '2016-08-13 01:00:03', 256),
(310, '2016-08-13 02:00:02', 253),
(311, '2016-08-13 03:00:02', 257),
(312, '2016-08-13 04:00:02', 260),
(313, '2016-08-13 05:00:02', 258),
(314, '2016-08-13 06:00:03', 253),
(315, '2016-08-13 07:00:03', 258),
(316, '2016-08-13 08:00:02', 278),
(317, '2016-08-13 09:00:03', 256),
(318, '2016-08-13 10:00:02', 256),
(319, '2016-08-13 11:00:03', 257),
(320, '2016-08-13 12:00:03', 257),
(321, '2016-08-13 13:00:03', 254),
(322, '2016-08-13 14:00:02', 256),
(323, '2016-08-13 15:00:02', 256),
(324, '2016-08-13 16:00:02', 259),
(325, '2016-08-13 17:00:03', 256),
(326, '2016-08-13 18:00:02', 260),
(327, '2016-08-13 19:00:03', 257),
(328, '2016-08-13 20:00:02', 258),
(329, '2016-08-13 21:00:03', 265),
(330, '2016-08-13 22:00:02', 268),
(331, '2016-08-13 23:00:03', 259),
(332, '2016-08-14 00:00:02', 262),
(333, '2016-08-14 01:00:02', 256),
(334, '2016-08-14 02:00:02', 256),
(335, '2016-08-14 03:00:02', 258),
(336, '2016-08-14 04:00:03', 256),
(337, '2016-08-14 05:00:02', 264),
(338, '2016-08-14 06:00:03', 258),
(339, '2016-08-14 07:00:02', 257),
(340, '2016-08-14 08:00:03', 277),
(341, '2016-08-14 09:00:02', 256),
(342, '2016-08-14 10:00:03', 257),
(343, '2016-08-14 11:00:03', 256),
(344, '2016-08-14 12:00:02', 257),
(345, '2016-08-14 13:00:02', 259),
(346, '2016-08-14 14:00:03', 255),
(347, '2016-08-14 15:00:03', 257),
(348, '2016-08-14 16:00:03', 254),
(349, '2016-08-14 17:00:02', 255),
(350, '2016-08-14 18:00:03', 255),
(351, '2016-08-14 19:00:03', 256),
(352, '2016-08-14 20:00:02', 259),
(353, '2016-08-14 21:00:03', 258),
(354, '2016-08-14 22:00:03', 262),
(355, '2016-08-14 23:00:03', 259),
(356, '2016-08-15 00:00:02', 258),
(357, '2016-08-15 01:00:03', 256),
(358, '2016-08-15 02:00:03', 257),
(359, '2016-08-15 03:00:02', 256),
(360, '2016-08-15 04:00:03', 255),
(361, '2016-08-15 05:00:02', 258),
(362, '2016-08-15 06:00:03', 258),
(363, '2016-08-15 07:00:02', 260),
(364, '2016-08-15 08:00:02', 279),
(365, '2016-08-15 09:00:02', 260),
(366, '2016-08-15 10:00:03', 256),
(367, '2016-08-15 11:00:02', 258),
(368, '2016-08-15 12:00:03', 256),
(369, '2016-08-15 13:00:03', 256),
(370, '2016-08-15 14:00:02', 257),
(371, '2016-08-15 15:00:02', 260),
(372, '2016-08-15 16:00:03', 256),
(373, '2016-08-15 17:00:02', 257),
(374, '2016-08-15 18:00:03', 257),
(375, '2016-08-15 19:00:03', 257),
(376, '2016-08-15 20:00:02', 257),
(377, '2016-08-15 21:00:02', 261),
(378, '2016-08-15 22:00:03', 262),
(379, '2016-08-15 23:00:02', 257),
(380, '2016-08-16 00:00:02', 257),
(381, '2016-08-16 01:00:03', 254),
(382, '2016-08-16 02:00:02', 259),
(383, '2016-08-16 03:00:03', 258),
(384, '2016-08-16 04:00:03', 261),
(385, '2016-08-16 05:00:03', 257),
(386, '2016-08-16 06:00:02', 260),
(387, '2016-08-16 07:00:02', 257),
(388, '2016-08-16 08:00:02', 279),
(389, '2016-08-16 09:00:03', 258),
(390, '2016-08-16 10:00:03', 256),
(391, '2016-08-16 11:00:03', 261),
(392, '2016-08-16 12:00:02', 258),
(393, '2016-08-16 13:00:03', 255),
(394, '2016-08-16 14:00:02', 257),
(395, '2016-08-16 15:00:02', 257),
(396, '2016-08-16 16:00:02', 256),
(397, '2016-08-16 17:00:02', 257),
(398, '2016-08-16 18:00:02', 258),
(399, '2016-08-16 19:00:03', 258),
(400, '2016-08-16 20:00:02', 263),
(401, '2016-08-16 21:00:03', 256),
(402, '2016-08-16 22:00:02', 264),
(403, '2016-08-16 23:00:02', 259),
(404, '2016-08-17 00:00:03', 256),
(405, '2016-08-17 01:00:03', 258),
(406, '2016-08-17 02:00:03', 258),
(407, '2016-08-17 03:00:03', 259),
(408, '2016-08-17 04:00:02', 256),
(409, '2016-08-17 05:00:02', 259),
(410, '2016-08-17 06:00:02', 258),
(411, '2016-08-17 07:00:03', 257),
(412, '2016-08-17 08:00:03', 282),
(413, '2016-08-17 09:00:03', 259),
(414, '2016-08-17 10:00:03', 261),
(415, '2016-08-17 11:00:02', 260),
(416, '2016-08-17 12:00:02', 257),
(417, '2016-08-17 13:00:02', 257);

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
  `datastorecount` int(11) NOT NULL,
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
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=26;
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
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=418;
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
