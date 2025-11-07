-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2025 at 05:39 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `football_league`
--

-- --------------------------------------------------------

--
-- Table structure for table `leagues`
--

CREATE TABLE `leagues` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leagues`
--

INSERT INTO `leagues` (`id`, `name`) VALUES
(1, 'SEASON 2'),
(3, 'SEASON 3'),
(4, 'SEASON 4');

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `id` int(11) NOT NULL,
  `league_name` varchar(255) NOT NULL,
  `round_number` int(11) DEFAULT 1,
  `home_team_id` int(11) NOT NULL,
  `away_team_id` int(11) NOT NULL,
  `home_goals` int(11) DEFAULT 0,
  `away_goals` int(11) DEFAULT 0,
  `match_played` tinyint(1) DEFAULT 0,
  `is_playoff` tinyint(1) DEFAULT 0,
  `winner_team_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`id`, `league_name`, `round_number`, `home_team_id`, `away_team_id`, `home_goals`, `away_goals`, `match_played`, `is_playoff`, `winner_team_id`) VALUES
(1, '', 1, 11, 8, 0, 0, 0, 0, NULL),
(2, '', 1, 10, 9, 0, 0, 0, 0, NULL),
(3, '', 1, 7, 12, 0, 0, 0, 0, NULL),
(4, '', 2, 11, 7, 0, 0, 0, 0, NULL),
(5, '', 2, 8, 9, 0, 0, 0, 0, NULL),
(6, '', 2, 10, 12, 0, 0, 0, 0, NULL),
(7, '', 3, 10, 7, 0, 0, 0, 0, NULL),
(8, '', 3, 11, 9, 0, 0, 0, 0, NULL),
(9, '', 3, 12, 8, 0, 0, 0, 0, NULL),
(10, '', 4, 7, 9, 0, 0, 0, 0, NULL),
(11, '', 4, 11, 12, 0, 0, 0, 0, NULL),
(12, '', 4, 8, 10, 0, 0, 0, 0, NULL),
(13, '', 5, 10, 11, 0, 0, 0, 0, NULL),
(14, '', 5, 7, 8, 0, 0, 0, 0, NULL),
(15, '', 5, 12, 9, 0, 0, 0, 0, NULL),
(16, '', 6, 8, 11, 0, 0, 0, 0, NULL),
(17, '', 6, 9, 10, 0, 0, 0, 0, NULL),
(18, '', 6, 12, 7, 0, 0, 0, 0, NULL),
(19, '', 7, 7, 11, 0, 0, 0, 0, NULL),
(20, '', 7, 9, 8, 0, 0, 0, 0, NULL),
(21, '', 7, 12, 10, 0, 0, 0, 0, NULL),
(22, '', 8, 7, 10, 0, 0, 0, 0, NULL),
(23, '', 8, 9, 11, 0, 0, 0, 0, NULL),
(24, '', 8, 8, 12, 0, 0, 0, 0, NULL),
(25, '', 9, 9, 7, 0, 0, 0, 0, NULL),
(26, '', 9, 12, 11, 0, 0, 0, 0, NULL),
(27, '', 9, 10, 8, 0, 0, 0, 0, NULL),
(28, '', 10, 11, 10, 0, 0, 0, 0, NULL),
(29, '', 10, 8, 7, 0, 0, 0, 0, NULL),
(30, '', 10, 9, 12, 0, 0, 0, 0, NULL),
(31, 'SEASON 2', 1, 12, 7, 0, 0, 0, 0, NULL),
(32, 'SEASON 2', 1, 8, 11, 0, 0, 0, 0, NULL),
(33, 'SEASON 2', 1, 10, 9, 0, 0, 0, 0, NULL),
(34, 'SEASON 2', 2, 11, 7, 0, 0, 0, 0, NULL),
(35, 'SEASON 2', 2, 10, 12, 0, 0, 0, 0, NULL),
(36, 'SEASON 2', 2, 8, 9, 0, 0, 0, 0, NULL),
(37, 'SEASON 2', 3, 10, 7, 0, 0, 0, 0, NULL),
(38, 'SEASON 2', 3, 9, 11, 0, 0, 0, 0, NULL),
(39, 'SEASON 2', 3, 12, 8, 0, 0, 0, 0, NULL),
(40, 'SEASON 2', 4, 9, 7, 0, 0, 0, 0, NULL),
(41, 'SEASON 2', 4, 10, 8, 0, 0, 0, 0, NULL),
(42, 'SEASON 2', 4, 12, 11, 0, 0, 0, 0, NULL),
(43, 'SEASON 2', 5, 7, 8, 0, 0, 0, 0, NULL),
(44, 'SEASON 2', 5, 12, 9, 0, 0, 0, 0, NULL),
(45, 'SEASON 2', 5, 11, 10, 0, 0, 0, 0, NULL),
(46, 'SEASON 2', 6, 7, 12, 0, 0, 0, 0, NULL),
(47, 'SEASON 2', 6, 11, 8, 0, 0, 0, 0, NULL),
(48, 'SEASON 2', 6, 9, 10, 0, 0, 0, 0, NULL),
(49, 'SEASON 2', 7, 7, 11, 0, 0, 0, 0, NULL),
(50, 'SEASON 2', 7, 12, 10, 0, 0, 0, 0, NULL),
(51, 'SEASON 2', 7, 9, 8, 0, 0, 0, 0, NULL),
(52, 'SEASON 2', 8, 7, 10, 0, 0, 0, 0, NULL),
(53, 'SEASON 2', 8, 11, 9, 0, 0, 0, 0, NULL),
(54, 'SEASON 2', 8, 8, 12, 0, 0, 0, 0, NULL),
(55, 'SEASON 2', 9, 7, 9, 0, 0, 0, 0, NULL),
(56, 'SEASON 2', 9, 8, 10, 0, 0, 0, 0, NULL),
(57, 'SEASON 2', 9, 11, 12, 0, 0, 0, 0, NULL),
(58, 'SEASON 2', 10, 8, 7, 0, 0, 0, 0, NULL),
(59, 'SEASON 2', 10, 9, 12, 0, 0, 0, 0, NULL),
(60, 'SEASON 2', 10, 10, 11, 0, 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `league_name` varchar(255) NOT NULL,
  `played` int(11) DEFAULT 0,
  `won` int(11) DEFAULT 0,
  `drawn` int(11) DEFAULT 0,
  `lost` int(11) DEFAULT 0,
  `goals_for` int(11) DEFAULT 0,
  `goals_against` int(11) DEFAULT 0,
  `points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `name`, `league_name`, `played`, `won`, `drawn`, `lost`, `goals_for`, `goals_against`, `points`) VALUES
(7, 'ARGENTINA', 'SEASON 2', 0, 0, 0, 0, 0, 0, 0),
(8, 'NETHERLANDS', 'SEASON 2', 0, 0, 0, 0, 0, 0, 0),
(9, 'SPAIN', 'SEASON 2', 0, 0, 0, 0, 0, 0, 0),
(10, 'PORTUGAL', 'SEASON 2', 0, 0, 0, 0, 0, 0, 0),
(11, 'FRANCE', 'SEASON 2', 0, 0, 0, 0, 0, 0, 0),
(12, 'ENGLAND', 'SEASON 2', 0, 0, 0, 0, 0, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `leagues`
--
ALTER TABLE `leagues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `home_team_id` (`home_team_id`),
  ADD KEY `away_team_id` (`away_team_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `leagues`
--
ALTER TABLE `leagues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`home_team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`away_team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
