-- phpMyAdmin SQL Dump
-- version 3.3.7deb6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 18, 2011 at 11:10 PM
-- Server version: 5.1.49
-- PHP Version: 5.3.3-7+squeeze3

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT=0;
START TRANSACTION;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `wahlen2011`
--

-- --------------------------------------------------------

--
-- Table structure for table `ausgabezettel`
--
-- Creation: Jun 05, 2011 at 03:51 PM
-- Last update: Sep 18, 2011 at 09:30 AM
--

DROP TABLE IF EXISTS `ausgabezettel`;
CREATE TABLE IF NOT EXISTS `ausgabezettel` (
  `id` int(11) NOT NULL,
  `zettel` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user` varchar(255) NOT NULL,
  PRIMARY KEY (`id`,`zettel`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `ausgabezettel`
--
DROP TRIGGER IF EXISTS `ausgabetrigger`;
DELIMITER //
CREATE TRIGGER `ausgabetrigger` BEFORE INSERT ON `ausgabezettel`
 FOR EACH ROW SET NEW.user = USER()
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `auszaehllog`
--
-- Creation: May 21, 2011 at 10:09 PM
--

DROP TABLE IF EXISTS `auszaehllog`;
CREATE TABLE IF NOT EXISTS `auszaehllog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=298 ;

--
-- Triggers `auszaehllog`
--
DROP TRIGGER IF EXISTS `auszaehllog`;
DELIMITER //
CREATE TRIGGER `auszaehllog` BEFORE INSERT ON `auszaehllog`
 FOR EACH ROW SET NEW.user = USER()
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `eingesammelt`
--
-- Creation: Jun 05, 2011 at 01:49 PM
--

DROP TABLE IF EXISTS `eingesammelt`;
CREATE TABLE IF NOT EXISTS `eingesammelt` (
  `id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'CURRENT_USER',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- RELATIONS FOR TABLE `eingesammelt`:
--   `id`
--       `stimmen` -> `id`
--

--
-- Triggers `eingesammelt`
--
DROP TRIGGER IF EXISTS `eingesammelttrigger`;
DELIMITER //
CREATE TRIGGER `eingesammelttrigger` BEFORE INSERT ON `eingesammelt`
 FOR EACH ROW SET NEW.user = USER()
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `fakultaet`
--
-- Creation: May 13, 2011 at 06:31 PM
--

DROP TABLE IF EXISTS `fakultaet`;
CREATE TABLE IF NOT EXISTS `fakultaet` (
  `id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kandidat`
--
-- Creation: May 21, 2011 at 04:49 PM
--

DROP TABLE IF EXISTS `kandidat`;
CREATE TABLE IF NOT EXISTS `kandidat` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `wahl` int(11) NOT NULL,
  `kid` int(11) NULL,
  PRIMARY KEY (`id`,`wahl`),
  KEY `wahl` (`wahl`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- RELATIONS FOR TABLE `kandidat`:
--   `wahl`
--       `wahl` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `stapel`
--
-- Creation: May 21, 2011 at 04:49 PM
--

DROP TABLE IF EXISTS `stapel`;
CREATE TABLE IF NOT EXISTS `stapel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wahl` int(11) NOT NULL,
  `numVotes` int(11) NOT NULL,
  `session` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `expiresAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`,`wahl`),
  KEY `wahl` (`wahl`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=37 ;

--
-- RELATIONS FOR TABLE `stapel`:
--   `wahl`
--       `wahl` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `stimmberechtigt`
--
-- Creation: May 13, 2011 at 11:21 PM
--

DROP TABLE IF EXISTS `stimmberechtigt`;
CREATE TABLE IF NOT EXISTS `stimmberechtigt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mtknr` int(11) NOT NULL,
  `namenszusatz` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `vorname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `nachname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `geburtsdatum` date NOT NULL,
  `fakultaet` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `studiengang` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `freigegeben` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mtknr` (`mtknr`),
  KEY `geburtsdatum` (`geburtsdatum`),
  KEY `fakultaet` (`fakultaet`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=16 ;

--
-- RELATIONS FOR TABLE `stimmberechtigt`:
--   `fakultaet`
--       `fakultaet` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `stimmen`
--
-- Creation: Jun 05, 2011 at 02:44 PM
--

DROP TABLE IF EXISTS `stimmen`;
CREATE TABLE IF NOT EXISTS `stimmen` (
  `id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'CURRENT_USER',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- RELATIONS FOR TABLE `stimmen`:
--   `id`
--       `stimmberechtigt` -> `id`
--

--
-- Triggers `stimmen`
--
DROP TRIGGER IF EXISTS `instrigger`;
DELIMITER //
CREATE TRIGGER `instrigger` BEFORE INSERT ON `stimmen`
 FOR EACH ROW SET NEW.user = USER()
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `stimmzettel`
--
-- Creation: Sep 18, 2011 at 11:10 AM
--

DROP TABLE IF EXISTS `stimmzettel`;
CREATE TABLE IF NOT EXISTS `stimmzettel` (
  `id` int(11) NOT NULL,
  `stapel` int(11) NOT NULL,
  `wahl` int(11) NOT NULL,
  `numOk` int(11) NOT NULL,
  `invalid` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`,`wahl`),
  KEY `wahl` (`wahl`),
  KEY `stapel` (`stapel`,`wahl`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wahl`
--
-- Creation: May 21, 2011 at 04:48 PM
--

DROP TABLE IF EXISTS `wahl`;
CREATE TABLE IF NOT EXISTS `wahl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `zettelstimme`
--
-- Creation: Sep 18, 2011 at 11:55 AM
--

DROP TABLE IF EXISTS `zettelstimme`;
CREATE TABLE IF NOT EXISTS `zettelstimme` (
  `wahl` int(11) NOT NULL,
  `stimmzettel` int(11) NOT NULL,
  `kandidat` int(11) NOT NULL,
  PRIMARY KEY (`wahl`,`stimmzettel`,`kandidat`),
  KEY `wahl` (`wahl`,`kandidat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `eingesammelt`
--
ALTER TABLE `eingesammelt`
  ADD CONSTRAINT `eingesammelt_ibfk_1` FOREIGN KEY (`id`) REFERENCES `stimmen` (`id`);

--
-- Constraints for table `kandidat`
--
ALTER TABLE `kandidat`
  ADD CONSTRAINT `kandidat_ibfk_1` FOREIGN KEY (`wahl`) REFERENCES `wahl` (`id`);

--
-- Constraints for table `stapel`
--
ALTER TABLE `stapel`
  ADD CONSTRAINT `stapel_ibfk_1` FOREIGN KEY (`wahl`) REFERENCES `wahl` (`id`);

--
-- Constraints for table `stimmberechtigt`
--
ALTER TABLE `stimmberechtigt`
  ADD CONSTRAINT `stimmberechtigt_ibfk_1` FOREIGN KEY (`fakultaet`) REFERENCES `fakultaet` (`id`),
  ADD CONSTRAINT `stimmberechtigt_ibfk_2` FOREIGN KEY (`fakultaet`) REFERENCES `fakultaet` (`id`),
  ADD CONSTRAINT `stimmberechtigt_ibfk_3` FOREIGN KEY (`fakultaet`) REFERENCES `fakultaet` (`id`);

--
-- Constraints for table `stimmen`
--
ALTER TABLE `stimmen`
  ADD CONSTRAINT `stimmen_ibfk_1` FOREIGN KEY (`id`) REFERENCES `stimmberechtigt` (`id`);

--
-- Constraints for table `stimmzettel`
--
ALTER TABLE `stimmzettel`
  ADD CONSTRAINT `stimmzettel_ibfk_1` FOREIGN KEY (`stapel`, `wahl`) REFERENCES `stapel` (`id`, `wahl`);

--
-- Constraints for table `zettelstimme`
--
ALTER TABLE `zettelstimme`
  ADD CONSTRAINT `zettelstimme_ibfk_1` FOREIGN KEY (`wahl`, `stimmzettel`) REFERENCES `stimmzettel` (`wahl`, `id`),
  ADD CONSTRAINT `zettelstimme_ibfk_2` FOREIGN KEY (`wahl`, `kandidat`) REFERENCES `kandidat` (`wahl`, `id`);
SET FOREIGN_KEY_CHECKS=1;
COMMIT;
