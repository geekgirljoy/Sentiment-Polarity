-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 07, 2019 at 01:14 AM
-- Server version: 10.1.37-MariaDB
-- PHP Version: 7.3.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `sentimentanalysis`
--

-- --------------------------------------------------------

--
-- Table structure for table `dictionary`
--

CREATE TABLE `dictionary` (
  `Hash` varchar(32) COLLATE latin1_general_ci NOT NULL,
  `Word` varchar(254) COLLATE latin1_general_ci NOT NULL,
  `Count` int(11) NOT NULL,
  `AmazonPositive` int(11) NOT NULL,
  `AmazonNegative` int(11) NOT NULL,
  `IMDBPositive` int(11) NOT NULL,
  `IMDBNegative` int(11) NOT NULL,
  `YelpPositive` int(11) NOT NULL,
  `YelpNegative` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
COMMIT;
