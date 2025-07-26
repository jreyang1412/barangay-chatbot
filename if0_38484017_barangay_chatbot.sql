-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql308.byetcluster.com
-- Generation Time: Jul 26, 2025 at 03:56 AM
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
-- Table structure for table `bot_options`
--

CREATE TABLE `bot_options` (
  `id` varchar(50) NOT NULL,
  `parent_id` varchar(50) DEFAULT NULL,
  `option_text` varchar(255) NOT NULL,
  `response_text` mediumtext NOT NULL,
  `has_children` tinyint(1) DEFAULT 0,
  `option_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bot_options`
--

INSERT INTO `bot_options` (`id`, `parent_id`, `option_text`, `response_text`, `has_children`, `option_order`, `is_active`) VALUES
('barangay_problems', 'main_menu', '🏢 Barangay Related Problems', 'What kind of barangay issue would you like to report?', 1, 1, 1),
('livelihood_problems', 'main_menu', '💼 Livelihood Problems', 'I can help you with livelihood concerns. What specific issue are you facing?', 1, 2, 1),
('emergency', 'main_menu', '🚨 Emergency', 'EMERGENCY ALERT ACTIVATED! Your location is being sent to barangay officials. Help is on the way!', 0, 3, 1),
('road_issues', 'barangay_problems', '🛣️ Road/Infrastructure Issues', 'I understand you have road or infrastructure concerns. Please describe the specific problem (potholes, broken streetlights, damaged sidewalks, etc.) and I will forward this to our Public Works team for immediate attention.', 0, 1, 1),
('garbage_issues', 'barangay_problems', '🗑️ Garbage/Sanitation Issues', 'Thank you for reporting a sanitation issue. Please provide details about the garbage collection problem, clogged drainage, or other sanitation concerns. Our Environmental Services team will address this within 24-48 hours.', 0, 2, 1),
('noise_complaints', 'barangay_problems', '🔊 Noise/Community Disturbance', 'I will help you with noise complaints or community disturbances. Please provide the location and nature of the disturbance. Our Community Relations team will investigate and take appropriate action according to barangay ordinances.', 0, 3, 1),
('job_assistance', 'livelihood_problems', '👷 Job/Employment Assistance', 'Our Livelihood Office can help connect you with job opportunities! We have partnerships with local businesses and skills training programs. Please tell me about your skills, experience, or what type of work you are looking for.', 0, 1, 1),
('business_permits', 'livelihood_problems', '📋 Business Permits & Registration', 'I can guide you through the business permit process. For new businesses, you will need: Barangay Business Clearance, DTI Registration, BIR TIN, and Mayor\'s Permit. The total processing time is usually 7-14 days. Would you like specific requirements for your business type?', 0, 2, 1),
('financial_assistance', 'livelihood_problems', '💰 Financial Assistance Programs', 'Our barangay offers several financial assistance programs: Micro-lending for small businesses, Educational assistance, Medical assistance, and Emergency financial aid. Each program has specific requirements. Which type of assistance do you need?', 0, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `barangay_official_id` int(11) DEFAULT NULL,
  `conversation_type` enum('bot','human','emergency') DEFAULT 'bot',
  `status` enum('active','resolved','closed') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `user_id`, `barangay_official_id`, `conversation_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 9, NULL, 'bot', 'active', '2025-07-25 18:08:25', '2025-07-25 18:08:25');

-- --------------------------------------------------------

--
-- Table structure for table `emergency_reports`
--

CREATE TABLE `emergency_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `emergency_type` varchar(100) DEFAULT 'General Emergency',
  `location_description` text DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `status` enum('pending','responding','resolved') DEFAULT 'pending',
  `priority` enum('low','medium','high','critical') DEFAULT 'high',
  `assigned_official_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `message_type` enum('text','bot_option','location','emergency') DEFAULT 'text',
  `bot_option_id` varchar(50) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `user_type` enum('resident','barangay_official') NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `last_seen` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `user_type`, `phone_number`, `address`, `latitude`, `longitude`, `is_online`, `last_seen`, `created_at`) VALUES
(7, 'danilo', '$2y$10$i9TVTzS8wshWn8GYYvw4a.GhtjfzWEX7fQcOj2HnH2/q5IU2ozYN6', 'danilo m. padlan', 'resident', '09564886008', 'kankaloo', NULL, NULL, 1, '2025-07-25 17:58:42', '2025-07-25 17:58:42'),
(8, 'boss jherwin', '$2y$10$APrs2gJkruoK.qGAOAY.xuN8ataqEWjzY5Ud/CEBUYDfbpp7nGfAG', 'boss jherwin ceo', 'barangay_official', '09564886008', 'kyusi', NULL, NULL, 0, '2025-07-25 18:08:59', '2025-07-25 17:59:07'),
(9, 'danilo2', '$2y$10$qKU6i8wnEu4tTp1t2jm/t.JGagCZhJEM4dQlljtn8w5c27HUWlFz2', 'danilo m. padlan', 'resident', '09564886009', 'kyusi2', NULL, NULL, 0, '2025-07-25 18:09:09', '2025-07-25 18:06:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bot_options`
--
ALTER TABLE `bot_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `barangay_official_id` (`barangay_official_id`);

--
-- Indexes for table `emergency_reports`
--
ALTER TABLE `emergency_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `assigned_official_id` (`assigned_official_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `emergency_reports`
--
ALTER TABLE `emergency_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
