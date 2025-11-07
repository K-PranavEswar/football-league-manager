-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2025 at 06:48 PM
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
(151, 'SEASON 2', 1, 22, 27, 0, 0, 0, 0, NULL),
(152, 'SEASON 2', 1, 23, 26, 0, 0, 0, 0, NULL),
(153, 'SEASON 2', 1, 25, 24, 0, 0, 0, 0, NULL),
(154, 'SEASON 2', 2, 22, 26, 0, 0, 0, 0, NULL),
(155, 'SEASON 2', 2, 27, 25, 0, 0, 0, 0, NULL),
(156, 'SEASON 2', 2, 23, 24, 0, 0, 0, 0, NULL),
(157, 'SEASON 2', 3, 22, 25, 0, 0, 0, 0, NULL),
(158, 'SEASON 2', 3, 26, 24, 0, 0, 0, 0, NULL),
(159, 'SEASON 2', 3, 27, 23, 0, 0, 0, 0, NULL),
(160, 'SEASON 2', 4, 24, 22, 0, 0, 0, 0, NULL),
(161, 'SEASON 2', 4, 23, 25, 0, 0, 0, 0, NULL),
(162, 'SEASON 2', 4, 27, 26, 0, 0, 0, 0, NULL),
(163, 'SEASON 2', 5, 23, 22, 0, 0, 0, 0, NULL),
(164, 'SEASON 2', 5, 27, 24, 0, 0, 0, 0, NULL),
(165, 'SEASON 2', 5, 25, 26, 0, 0, 0, 0, NULL),
(166, 'SEASON 2', 6, 27, 22, 0, 0, 0, 0, NULL),
(167, 'SEASON 2', 6, 26, 23, 0, 0, 0, 0, NULL),
(168, 'SEASON 2', 6, 24, 25, 0, 0, 0, 0, NULL),
(169, 'SEASON 2', 7, 26, 22, 0, 0, 0, 0, NULL),
(170, 'SEASON 2', 7, 25, 27, 0, 0, 0, 0, NULL),
(171, 'SEASON 2', 7, 24, 23, 0, 0, 0, 0, NULL),
(172, 'SEASON 2', 8, 25, 22, 0, 0, 0, 0, NULL),
(173, 'SEASON 2', 8, 24, 26, 0, 0, 0, 0, NULL),
(174, 'SEASON 2', 8, 23, 27, 0, 0, 0, 0, NULL),
(175, 'SEASON 2', 9, 22, 24, 0, 0, 0, 0, NULL),
(176, 'SEASON 2', 9, 25, 23, 0, 0, 0, 0, NULL),
(177, 'SEASON 2', 9, 26, 27, 0, 0, 0, 0, NULL),
(178, 'SEASON 2', 10, 22, 23, 0, 0, 0, 0, NULL),
(179, 'SEASON 2', 10, 24, 27, 0, 0, 0, 0, NULL),
(180, 'SEASON 2', 10, 26, 25, 0, 0, 0, 0, NULL);

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
(22, 'SPAIN', 'SEASON 2', 0, 0, 0, 0, 0, 0, 0),
(23, 'ARGENTINA', 'SEASON 2', 0, 0, 0, 0, 0, 0, 0),
(24, 'NETHERLANDS', 'SEASON 2', 0, 0, 0, 0, 0, 0, 0),
(25, 'FRANCE', 'SEASON 2', 0, 0, 0, 0, 0, 0, 0),
(26, 'ENGLAND', 'SEASON 2', 0, 0, 0, 0, 0, 0, 0),
(27, 'PORTUGAL', 'SEASON 2', 0, 0, 0, 0, 0, 0, 0);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

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
