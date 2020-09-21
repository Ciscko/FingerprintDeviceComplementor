-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 31, 2020 at 09:32 AM
-- Server version: 5.7.26
-- PHP Version: 7.0.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sms`
--

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
CREATE TABLE IF NOT EXISTS `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fingerprintID` varchar(12) NOT NULL,
  `regno` varchar(12) NOT NULL,
  `department` varchar(100) NOT NULL,
  `year` varchar(10) NOT NULL,
  `firstName` varchar(20) NOT NULL,
  `lastName` varchar(20) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `address` varchar(100) NOT NULL,
  `dob` date NOT NULL,
  `exam` varchar(10) NOT NULL,
  `photo` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `regno` (`regno`),
  UNIQUE KEY `fingerprintID` (`fingerprintID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `fingerprintID`, `regno`, `department`, `year`, `firstName`, `lastName`, `gender`, `address`, `dob`, `exam`, `photo`) VALUES
(2, '3', 'F17/1685/201', 'Arts and Design', '4th Year', 'Francis', 'Kiiru', 'male', 'Nakuru, Lare-Naishi Centre, Lare-Naishi Centre\r\nLare-Naishi Centre', '2019-12-03', 'Valid', '1575402069815.JPG'),
(3, '2', 'F18/1205/201', 'Mechatronic Engineering', '3rd Year', 'Joseph', 'Kamau', 'male', 'Nairobi Ngara Plaza\r\nMaclavin House 3', '2019-12-05', 'Invalid', '1575404816177.JPG'),
(4, '1', 'F1/1635/2015', 'Electrical and Information Engineering', '5th Year', 'Juma', 'Zioka', 'male', 'Nairobi Ngara Plaza\r\nMaclavin House 3', '2019-12-03', 'Valid', '1575402111164.JPG');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(45) NOT NULL,
  `email` varchar(45) NOT NULL,
  `gender` varchar(45) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `auth` varchar(20) NOT NULL,
  `dob` date DEFAULT NULL,
  `firstName` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `department` varchar(45) NOT NULL,
  `photo` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `phone`, `email`, `gender`, `username`, `password`, `auth`, `dob`, `firstName`, `lastName`, `department`, `photo`) VALUES
(4, '0703993629', 'kiragufrancis97@gmail.com', 'male', 'Cisco', '12345678', 'super', '1995-10-12', 'Francis', 'Kiiru', 'Eng', '1577352821265.jpg'),
(2, '0726385936', 'joseph@gmail.com', 'male', 'Hose', '12345678', 'admin', '2019-12-19', 'Joseph', 'Kamau', 'Procurement', '1577350548827.jpg');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
