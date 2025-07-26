-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql308.infinityfree.com
-- Generation Time: Jul 26, 2025 at 06:21 AM
-- Server version: 11.4.7-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_38484017_barangay_chatbot`
--

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `last_activity` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('waiting','active','closed') DEFAULT 'waiting'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `user_id`, `last_activity`, `status`) VALUES
(1, 'user_1753524808485', '2025-07-26 10:16:15', 'active'),
(2, 'user_1753524961703', '2025-07-26 10:16:06', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `sender_type` enum('user','admin') NOT NULL,
  `message` text NOT NULL,
  `help_type` varchar(50) DEFAULT NULL,
  `location_lat` decimal(10,8) DEFAULT NULL,
  `location_lng` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `sender_type`, `message`, `help_type`, `location_lat`, `location_lng`, `created_at`, `is_active`) VALUES
(1, 'user_1753524808485', 'user', 'ðŸš¨ EMERGENCY HELP REQUESTED - Please respond immediately!', 'emergency', NULL, NULL, '2025-07-26 10:14:21', 1),
(2, 'user_1753524808485', 'user', 'ðŸ“ Emergency location shared', 'emergency', '14.68661760', '121.00239360', '2025-07-26 10:14:23', 1),
(3, 'user_1753524808485', 'admin', 'nihao', NULL, NULL, NULL, '2025-07-26 10:15:41', 1),
(4, 'user_1753524808485', 'user', 'ðŸš¨ EMERGENCY HELP REQUESTED - Please respond immediately!', 'emergency', NULL, NULL, '2025-07-26 10:15:44', 1),
(5, 'user_1753524808485', 'user', 'ðŸ“ Emergency location shared', 'emergency', '14.68661760', '121.00239360', '2025-07-26 10:15:47', 1),
(6, 'user_1753524808485', 'user', 'gago', NULL, NULL, NULL, '2025-07-26 10:15:52', 1),
(7, 'user_1753524961703', 'user', 'ðŸ”§ Technical support needed - User is waiting for assistance.', 'technical', NULL, NULL, '2025-07-26 10:16:04', 1),
(8, 'user_1753524961703', 'user', 'dfdf', NULL, NULL, NULL, '2025-07-26 10:16:06', 1),
(9, 'user_1753524808485', 'user', 'oki', NULL, NULL, NULL, '2025-07-26 10:16:15', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_last_activity` (`last_activity`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
