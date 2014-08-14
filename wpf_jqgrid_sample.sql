-- phpMyAdmin SQL Dump
-- version 4.0.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 27, 2014 at 07:32 PM
-- Server version: 5.1.71
-- PHP Version: 5.3.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `faina09`
--

-- --------------------------------------------------------

--
-- Table structure for table `wpf_jqgrid_sample`
--

CREATE TABLE IF NOT EXISTS `wpf_jqgrid_sample` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `City` varchar(100) DEFAULT NULL,
  `Temp_C` decimal(10,2) DEFAULT NULL,
  `DateTime` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `wpf_jqgrid_sample`
--

INSERT INTO `wpf_jqgrid_sample` (`ID`, `City`, `Temp_C`, `DateTime`) VALUES
(1, 'Udine', '4.10', '2014-01-26 19:00:00'),
(2, 'Cividale', '2.70', '2014-01-26 19:23:00'),
(3, 'Udine', '4.00', '2014-01-26 21:00:00'),
(4, 'Cividale', '2.30', '2014-01-26 21:30:00'),
(5, 'Udine', '3.60', '2014-01-26 22:00:00'),
(6, 'Cividale', '-1.00', '2014-01-26 22:37:00');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
