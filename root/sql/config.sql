-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 26, 2016 at 10:22 AM
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
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
`id` int(11) NOT NULL,
  `configid` varchar(50) CHARACTER SET latin1 NOT NULL,
  `type` int(11) NOT NULL,
  `label` varchar(255) CHARACTER SET latin1 NOT NULL,
  `value` varchar(50) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`id`, `configid`, `type`, `label`, `value`) VALUES
(1, 'dailySchedule', 8, 'Choose the hour used for daily schedule (appliance use UTC format)', '8'),
(2, 'weeklySchedule', 9, 'Choose the day used for weekly schedule', '6'),
(3, 'monthlySchedule', 10, 'Choose the day used for monthly schedule (beware of short/long months)', '6'),
(4, 'powerSystemInfo', 7, 'Choose the desired Power Management Policy for ESX', 'off'),
(5, 'thresholdHistory', 4, 'Number of days before data is purged (0 to disabled)', '7'),
(6, 'thresholdCPURatio', 4, 'Threshold for vCPU/pCPU ratio check', '2'),
(7, 'lang', 6, 'Language of appliance', 'en'),
(8, 'showPlainLicense', 1, 'Display plain license in License Report instead of ''####''', 'disable'),
(9, 'vcSessionAge', 4, 'Number of days before a session is defined as ''old''', '2'),
(10, 'hostSSHPolicy', 5, 'Choose the desired policy for SSH service', 'on'),
(11, 'hostShellPolicy', 5, 'Choose the desired policy for Shell service', 'on'),
(12, 'datastoreFreeSpaceThreshold', 4, 'Datastore free space threshold %', '30'),
(13, 'datastoreOverallocation', 4, 'Datastore OverAllocation %', '30'),
(14, 'networkDVSVSSportsfree', 4, 'Threshold of free port for DVS needed', '123'),
(15, 'vmSnapshotAge', 4, 'Number of days before a snapshot is defined as ''old''', '98'),
(16, 'timeToBuildCount', 4, 'Number of entries ''Time To Build'' page will display (0 for all)', '100'),
(17, 'smtpAddress', 2, 'SMTP server address (IP, or FQDN) to forward email to', 'smtp'),
(18, 'senderMail', 3, 'Sender email to be used for report export feature', 'BCO_VCenter-2@xxx.com'),
(19, 'recipientMail', 3, 'Recipient email to be used for report export feature', 'frederic.c.martin-ext@xxx.com'),
(20, 'pdfAuthor', 2, 'Username that will be used as the ''Author'' of generated PDFs', 'Gordon Freeman'),
(21, 'showEmpty', 1, 'Show checks that return empty values (ie when there is nothing to report)', 'enable'),
(22, 'showAuthors', 1, 'Show authors page of generated PDF''s, be aware that disabling it may kill some kitten...', 'enable');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `config`
--
ALTER TABLE `config`
 ADD PRIMARY KEY (`id`), ADD KEY `type` (`type`), ADD KEY `configid` (`configid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `config`
--
ALTER TABLE `config`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=24;
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
