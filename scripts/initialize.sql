-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 18, 2020 at 10:36 PM
-- Server version: 8.0.20
-- PHP Version: 7.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `bitcoin`
--
CREATE DATABASE IF NOT EXISTS `bitcoin` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bitcoin`;

-- --------------------------------------------------------

--
-- Table structure for table `BTCUSD`
--

CREATE TABLE IF NOT EXISTS `BTCUSD` (
  `timestamp` bigint NOT NULL,
  `close` decimal(13,3) DEFAULT NULL,
  PRIMARY KEY (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;
