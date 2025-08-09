-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql308.infinityfree.com
-- Generation Time: Aug 09, 2025 at 07:18 PM
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
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `city` varchar(100) NOT NULL,
  `barangay_number` varchar(50) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `city`, `barangay_number`, `username`, `password`, `last_login`, `created_at`) VALUES
(1, 'caloocan', '028', 'admin_caloocan_brgy028', '$2y$10$53L7Nj9/ZJ/YI3YQhvHvVOuI5CAImJnhkl1FgbsTP0fW3xiMe62cy', '2025-08-09 15:54:06', '2025-07-26 20:50:47'),
(2, 'manila', '187', 'admin1', '$2y$10$CJdRAS5SINxaP1i6TTmZFuYraACGo94rJpcy9bKll8du3OMLC3DLy', NULL, '2025-07-26 21:05:38'),
(3, 'qc', '122', 'admin2', '$2y$10$I8fylOcrvsmrXS1gzWGpkODYZpAYABJKZbVvAIO5Hv5m5xo960WqK', NULL, '2025-07-26 21:08:02'),
(4, 'caloocan', '123', 'sepulture', '$2y$10$rgi8/2B8EGEfnBbrwXwvPOLbWIy96WMPboyftgWWWKyA3IUhcFoq2', NULL, '2025-07-26 23:04:14'),
(5, 'San Juan', '240', 'danilo', '$2y$10$il/94zYbWhwvA86SYqfBfe7nFVIbH7BrFJvTZx1RRMK0Xt9ZQHVCm', '2025-08-09 16:00:51', '2025-07-27 15:14:52'),
(6, 'Quezon City', '1', 'AdminMIlky', '$2y$10$ISW64naK.L1ZtHWRvZMskOlWTp8WEOpQHivwPIDQIpvAc7oSKQSIy', NULL, '2025-08-05 11:30:43'),
(7, 'Quezon City', '7', 'denzrevz26@gmail.com', '$2y$10$NVBGXz9C4o4W15VjiZxJweVwlvrhPsqYXMF0vPFxmlLGgffw62kn6', '2025-08-09 16:14:25', '2025-08-09 16:16:12'),
(8, 'Quezon City', '28', 'TUPsub', '$2y$10$.2ZFKObP6eYmEdctGtlwuurQgnyRSumM/2B93DLaWRLr/qdzN21vi', '2025-08-09 16:12:19', '2025-08-09 22:55:41');

-- --------------------------------------------------------

--
-- Table structure for table `admin_sessions`
--

CREATE TABLE `admin_sessions` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `session_token` varchar(64) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_type` enum('user','admin','system') NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_type`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 'user', 2, 'BARANGAY_REQUEST_SUBMITTED', 'barangay_clearance', '136.158.40.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 06:00:12'),
(2, 'user', 2, 'BARANGAY_REQUEST_SUBMITTED', 'barangay_clearance', '136.158.40.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 06:05:19'),
(3, 'user', 8, 'BARANGAY_REQUEST_SUBMITTED', 'event_permit', '112.202.242.151', 'Mozilla/5.0 (Linux; Android 15; SM-S928B Build/AP3A.240905.015.A2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/138.0.7204.179 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/518.0.0.53.109;]', '2025-08-06 15:28:40'),
(4, 'user', 8, 'BARANGAY_REQUEST_SUBMITTED', 'barangay_clearance', '112.202.239.194', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-06 15:43:02'),
(5, 'user', 2, 'BARANGAY_REQUEST_SUBMITTED', 'calamity_certificate', '136.158.40.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-06 15:58:50'),
(6, 'user', 2, 'BARANGAY_REQUEST_SUBMITTED', 'barangay_clearance', '136.158.40.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 15:23:07'),
(7, 'user', 2, 'BARANGAY_REQUEST_SUBMITTED', 'certificate_of_residency', '136.158.40.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 15:23:34'),
(8, 'user', 2, 'BARANGAY_REQUEST_SUBMITTED', 'certificate_of_indigency', '136.158.40.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 15:23:54'),
(9, 'user', 2, 'BARANGAY_REQUEST_SUBMITTED', 'business_clearance', '136.158.40.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 15:24:23'),
(10, 'user', 2, 'BARANGAY_REQUEST_SUBMITTED', 'solo_parent_certificate', '136.158.40.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 15:24:46'),
(11, 'user', 2, 'BARANGAY_REQUEST_SUBMITTED', 'barangay_id', '136.158.40.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 15:25:06'),
(12, 'user', 2, 'BARANGAY_REQUEST_SUBMITTED', 'event_permit', '136.158.40.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 15:25:27'),
(13, 'user', 2, 'BARANGAY_REQUEST_SUBMITTED', 'calamity_certificate', '136.158.40.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 15:25:59');

-- --------------------------------------------------------

--
-- Table structure for table `barangay_requests`
--

CREATE TABLE `barangay_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_type` enum('barangay_clearance','certificate_of_residency','certificate_of_indigency','business_clearance','solo_parent_certificate','barangay_id','event_permit','calamity_certificate') NOT NULL,
  `surname` varchar(100) NOT NULL,
  `given_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `birthdate` date NOT NULL,
  `address` text NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `additional_info` text DEFAULT NULL,
  `status` enum('pending','processing','ready','completed','rejected') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `assigned_admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `uploaded_files` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ;

--
-- Dumping data for table `barangay_requests`
--

INSERT INTO `barangay_requests` (`id`, `user_id`, `service_type`, `surname`, `given_name`, `middle_name`, `birthdate`, `address`, `contact_number`, `purpose`, `additional_info`, `status`, `admin_notes`, `assigned_admin_id`, `created_at`, `updated_at`, `processed_at`, `completed_at`, `event_date`, `uploaded_files`) VALUES
(6, 2, 'barangay_clearance', 'Ang', 'John Rey', 'fernandez', '2007-08-08', 'adadad', '09564886008', 'sadadwa', 'dwadasd', 'ready', NULL, NULL, '2025-08-09 15:23:07', '2025-08-09 16:17:36', NULL, NULL, '0000-00-00', '[]'),
(7, 2, 'certificate_of_residency', 'Ang', 'John Rey', 'fernandez', '2007-08-08', 'awdadwad', '09564886008', 'awdawd', 'awdawdawd', 'pending', NULL, NULL, '2025-08-09 15:23:34', '2025-08-09 15:23:34', NULL, NULL, '0000-00-00', '{\"billing_statement\":\"certificate_of_residency_billing_statement_1754753014_689767f641897.pdf\"}'),
(8, 2, 'certificate_of_indigency', 'Ang', 'John Rey', 'fernandez', '2007-08-08', 'adwdada', '09564886008', 'awdwad', 'awdad', 'pending', NULL, NULL, '2025-08-09 15:23:54', '2025-08-09 15:23:54', NULL, NULL, '0000-00-00', '{\"pao_application\":\"certificate_of_indigency_pao_application_1754753034_6897680a77ecb.pdf\"}'),
(9, 2, 'business_clearance', 'Ang', 'John Rey', 'fernandez', '2007-08-08', 'adadaw', '09564886008', 'National Museum', 'awdadwad', 'pending', NULL, NULL, '2025-08-09 15:24:23', '2025-08-09 15:24:23', NULL, NULL, '0000-00-00', '{\"barangay_id_or_residency\":\"business_clearance_barangay_id_or_residency_1754753063_689768278fe48.pdf\",\"lease_contract\":\"business_clearance_lease_contract_1754753063_68976827900af.pdf\",\"dti_registration\":\"business_clearance_dti_registration_1754753063_689768279031f.pdf\"}'),
(10, 2, 'solo_parent_certificate', 'Ang', 'John Rey', 'fernandez', '2007-08-08', 'wdadwad', '09564886008', 'dwadawd', 'wdadaa', 'pending', NULL, NULL, '2025-08-09 15:24:46', '2025-08-09 15:24:46', NULL, NULL, '0000-00-00', '{\"supporting_document\":\"solo_parent_certificate_supporting_document_1754753086_6897683e703ae.pdf\"}'),
(11, 2, 'barangay_id', 'dawdad', 'awdawda', 'adwawd', '2007-08-08', 'dawdwadawd', '09564886008', 'awdada', 'adwadwa', 'ready', NULL, NULL, '2025-08-09 15:25:06', '2025-08-09 15:48:38', NULL, NULL, '0000-00-00', '{\"id_picture\":\"barangay_id_id_picture_1754753106_689768521f39d.jpg\"}'),
(12, 2, 'event_permit', 'Ang', 'John Rey', 'fernandez', '2007-08-08', 'wadadad', '09564886008', 'awdadad', 'dwadawdad', 'pending', NULL, NULL, '2025-08-09 15:25:27', '2025-08-09 15:25:27', NULL, NULL, '2025-08-09', '[]'),
(13, 2, 'calamity_certificate', 'Ang', 'John Rey', 'fernandez', '2007-08-08', 'dawdwad', '09564886008', 'National Museum', 'wdadada', 'pending', NULL, NULL, '2025-08-09 15:25:59', '2025-08-09 16:17:20', NULL, NULL, '0000-00-00', '{\"incident_proof\":\"calamity_certificate_incident_proof_1754753159_689768877ed40.pdf\",\"valid_id\":\"calamity_certificate_valid_id_1754753159_689768877efe8.pdf\"}');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `status` enum('waiting','active','closed') DEFAULT 'waiting',
  `last_activity` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `closed_at` timestamp NULL DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `user_id`, `status`, `last_activity`, `created_at`, `closed_at`, `admin_id`) VALUES
(1, 'user_1753565942725', 'closed', '2025-07-27 15:19:40', '2025-07-26 21:39:07', NULL, NULL),
(2, 'user_1753524808485', 'closed', '2025-07-27 15:19:40', '2025-07-26 23:12:34', NULL, NULL),
(3, 'user_1753531528322', 'closed', '2025-07-27 15:19:40', '2025-07-26 23:30:49', NULL, NULL),
(4, 'user_1753532507560', 'closed', '2025-07-27 15:19:40', '2025-07-27 03:10:57', NULL, NULL),
(5, 'user_1753585279093', 'closed', '2025-07-27 15:19:40', '2025-07-27 03:11:43', NULL, NULL),
(6, 'user_1753524961703', 'closed', '2025-07-27 15:19:40', '2025-07-27 13:24:22', NULL, NULL),
(7, 'user_1753629916301', 'closed', '2025-07-27 16:33:23', '2025-07-27 15:26:08', NULL, NULL),
(8, 'user_1753630192845', 'closed', '2025-07-27 16:33:23', '2025-07-27 15:30:46', NULL, NULL),
(9, 'user_1753630386206', 'closed', '2025-07-27 16:33:23', '2025-07-27 15:33:57', NULL, NULL),
(10, 'user_1753630571461', 'closed', '2025-07-27 16:33:23', '2025-07-27 15:37:03', NULL, NULL),
(11, 'user_1753631072168', 'closed', '2025-07-27 16:33:23', '2025-07-27 15:44:39', NULL, NULL),
(12, 'user_1753633914154', 'closed', '2025-07-27 16:43:00', '2025-07-27 16:32:07', NULL, NULL),
(13, 'user_1753634029429', 'closed', '2025-07-27 16:43:56', '2025-07-27 16:33:54', NULL, NULL),
(14, 'user_1753634187597', 'closed', '2025-07-28 00:17:45', '2025-07-27 16:37:24', NULL, NULL),
(15, 'user_1753634296896', 'closed', '2025-07-27 16:48:23', '2025-07-27 16:38:22', NULL, NULL),
(16, 'user_1753634641063', 'closed', '2025-07-28 00:17:45', '2025-07-27 16:44:10', NULL, NULL),
(17, 'user_1753634709566', 'closed', '2025-07-28 00:17:45', '2025-07-27 16:46:06', NULL, NULL),
(18, 'user_1753634782314', 'closed', '2025-07-28 00:17:45', '2025-07-27 16:47:15', NULL, NULL),
(19, 'user_1753661711727', 'closed', '2025-07-28 08:40:25', '2025-07-28 00:16:15', NULL, NULL),
(20, 'user_1753661778498', 'closed', '2025-07-28 08:40:25', '2025-07-28 00:17:37', NULL, NULL),
(21, 'user_1753661895526', 'closed', '2025-07-28 08:40:25', '2025-07-28 00:18:23', NULL, NULL),
(22, 'user_1753661981407', 'closed', '2025-07-28 08:40:25', '2025-07-28 00:20:37', NULL, NULL),
(23, 'user_1753864032152', 'closed', '2025-08-06 15:23:52', '2025-07-30 08:27:19', NULL, NULL),
(24, 'user_1754287823568', 'closed', '2025-08-06 15:23:52', '2025-08-04 06:11:07', NULL, NULL),
(25, 'user_1754393610100', 'closed', '2025-08-06 15:23:52', '2025-08-05 11:33:42', NULL, NULL),
(26, 'user_1754495267491', 'active', '2025-08-06 15:48:22', '2025-08-06 15:48:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `sender_type` enum('user','admin') NOT NULL,
  `message` text NOT NULL,
  `help_type` enum('emergency','technical','general') DEFAULT NULL,
  `location_lat` decimal(10,8) DEFAULT NULL,
  `location_lng` decimal(11,8) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `admin_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `sender_type`, `message`, `help_type`, `location_lat`, `location_lng`, `is_active`, `created_at`, `admin_id`) VALUES
(1, 'user_1753565942725', 'user', 'ðŸ’¬ General inquiry started - User has a question.', 'general', NULL, NULL, 1, '2025-07-26 21:39:07', NULL),
(2, 'user_1753565942725', 'user', 'ðŸš¨ EMERGENCY HELP REQUESTED - Please respond immediately!', 'emergency', NULL, NULL, 1, '2025-07-26 21:39:15', NULL),
(3, 'user_1753565942725', 'user', 'ðŸ“ Emergency location shared', 'emergency', '14.64094270', '120.96671220', 1, '2025-07-26 21:39:17', NULL),
(4, 'user_1753524808485', 'user', 'hello i need help', NULL, NULL, NULL, 1, '2025-07-26 23:12:34', NULL),
(5, 'user_1753531528322', 'user', 'ðŸ”§ Technical support needed - User is waiting for assistance.', 'technical', NULL, NULL, 1, '2025-07-26 23:30:49', NULL),
(6, 'user_1753531528322', 'user', 'ðŸš¨ EMERGENCY HELP REQUESTED - Please respond immediately!', 'emergency', NULL, NULL, 1, '2025-07-26 23:31:17', NULL),
(7, 'user_1753531528322', 'user', 'ðŸ“ Emergency location shared', 'emergency', '14.68022480', '121.05709760', 1, '2025-07-26 23:31:18', NULL),
(8, 'user_1753532507560', 'user', 'ðŸš¨ EMERGENCY HELP REQUESTED - Please respond immediately!', 'emergency', NULL, NULL, 1, '2025-07-27 03:10:57', NULL),
(9, 'user_1753532507560', 'user', 'ðŸš¨ EMERGENCY HELP REQUESTED - Please respond immediately!', 'emergency', NULL, NULL, 1, '2025-07-27 03:11:33', NULL),
(10, 'user_1753585279093', 'user', 'ðŸš¨ EMERGENCY HELP REQUESTED - Please respond immediately!', 'emergency', NULL, NULL, 1, '2025-07-27 03:11:43', NULL),
(11, 'user_1753585279093', 'user', 'ðŸ“ Emergency location shared', 'emergency', '14.69117030', '121.06717940', 1, '2025-07-27 03:11:47', NULL),
(12, 'user_1753531528322', 'user', 'ðŸ’¬ General inquiry started - User has a question.', 'general', NULL, NULL, 1, '2025-07-27 11:00:56', NULL),
(13, 'user_1753524961703', 'user', 'ðŸ’¬ General inquiry started - User has a question.', 'general', NULL, NULL, 1, '2025-07-27 13:24:22', NULL),
(14, 'user_1753629916301', 'user', 'ðŸš¨ EMERGENCY HELP REQUESTED - Please respond immediately!', 'emergency', NULL, NULL, 1, '2025-07-27 15:26:08', NULL),
(15, 'user_1753629916301', 'user', 'ðŸ“ Emergency location shared', 'emergency', '14.68989440', '120.99256320', 1, '2025-07-27 15:26:08', NULL),
(16, 'user_1753629916301', 'admin', 'hi', NULL, NULL, NULL, 0, '2025-07-27 15:26:21', NULL),
(17, 'user_1753629916301', 'user', 'ðŸ”§ Technical support needed - User is waiting for assistance.', 'technical', NULL, NULL, 1, '2025-07-27 15:30:12', NULL),
(18, 'user_1753630192845', 'user', 'ðŸ’¬ General inquiry started - User has a question.', 'general', NULL, NULL, 1, '2025-07-27 15:30:46', NULL),
(19, 'user_1753630192845', 'user', 'hello po', NULL, NULL, NULL, 1, '2025-07-27 15:30:51', NULL),
(20, 'user_1753630192845', 'user', 'hi', NULL, NULL, NULL, 1, '2025-07-27 15:31:10', NULL),
(21, 'user_1753630386206', 'user', 'hello', NULL, NULL, NULL, 1, '2025-07-27 15:33:57', NULL),
(22, 'user_1753630386206', 'user', 'hi', NULL, NULL, NULL, 1, '2025-07-27 15:34:02', NULL),
(23, 'user_1753630386206', 'user', 'asda', NULL, NULL, NULL, 1, '2025-07-27 15:34:04', NULL),
(24, 'user_1753630386206', 'user', 'asda', NULL, NULL, NULL, 1, '2025-07-27 15:34:05', NULL),
(25, 'user_1753630571461', 'user', 'ðŸš¨ EMERGENCY HELP REQUESTED - Please respond immediately!', 'emergency', NULL, NULL, 1, '2025-07-27 15:37:03', NULL),
(26, 'user_1753630571461', 'user', 'ðŸ“ Emergency location shared', 'emergency', '14.64095140', '120.96671740', 1, '2025-07-27 15:37:03', NULL),
(27, 'user_1753630571461', 'user', 'help', NULL, NULL, NULL, 1, '2025-07-27 15:37:08', NULL),
(28, 'user_1753630571461', 'admin', 'ano nanaman yon?', NULL, NULL, NULL, 0, '2025-07-27 15:37:31', NULL),
(29, 'user_1753630571461', 'user', 'tanginamo', NULL, NULL, NULL, 1, '2025-07-27 15:37:37', NULL),
(30, 'user_1753631072168', 'user', 'dfd', NULL, NULL, NULL, 1, '2025-07-27 15:44:39', NULL),
(31, 'user_1753631072168', 'user', 'ðŸ’¬ General inquiry started - User has a question.', 'general', NULL, NULL, 1, '2025-07-27 15:44:41', NULL),
(32, 'user_1753631072168', 'user', 'ðŸš¨ EMERGENCY HELP REQUESTED - Please respond immediately!', 'emergency', NULL, NULL, 1, '2025-07-27 15:44:45', NULL),
(33, 'user_1753631072168', 'user', 'ðŸš¨ EMERGENCY HELP REQUESTED - Please respond immediately!', 'emergency', NULL, NULL, 1, '2025-07-27 15:44:48', NULL),
(34, 'user_1753631072168', 'user', 'ðŸ“ Emergency location shared', 'emergency', '14.68019360', '121.05683630', 1, '2025-07-27 15:44:49', NULL),
(35, 'user_1753631072168', 'user', 'ðŸ“ Emergency location shared', 'emergency', '14.68019360', '121.05683630', 1, '2025-07-27 15:44:49', NULL),
(36, 'user_1753633914154', 'user', 'Help', NULL, NULL, NULL, 1, '2025-07-27 16:32:07', NULL),
(37, 'user_1753633914154', 'user', 'ðŸš¨ EMERGENCY HELP REQUESTED - Please respond immediately!', 'emergency', NULL, NULL, 1, '2025-07-27 16:32:11', NULL),
(38, 'user_1753633914154', 'user', 'ðŸ“ Emergency location shared', 'emergency', '14.64094160', '120.96672390', 1, '2025-07-27 16:32:13', NULL),
(39, 'user_1753633914154', 'user', 'ðŸ’¬ General inquiry started - User has a question.', 'general', NULL, NULL, 1, '2025-07-27 16:32:22', NULL),
(40, 'user_1753634029429', 'user', 'Hello', NULL, NULL, NULL, 1, '2025-07-27 16:33:54', NULL),
(41, 'user_1753634187597', 'user', 'ðŸš¨ EMERGENCY HELP REQUESTED - Please respond immediately!', 'emergency', NULL, NULL, 1, '2025-07-27 16:37:24', NULL),
(42, 'user_1753634187597', 'user', 'ðŸ“ Emergency location shared', 'emergency', '14.64094910', '120.96671860', 1, '2025-07-27 16:37:26', NULL),
(43, 'user_1753634296896', 'user', 'Help', NULL, NULL, NULL, 1, '2025-07-27 16:38:22', NULL),
(44, 'user_1753634187597', 'user', 'ðŸš¨ EMERGENCY HELP REQUESTED - Please respond immediately!', 'emergency', NULL, NULL, 1, '2025-07-27 16:43:11', NULL),
(45, 'user_1753634187597', 'user', 'ðŸ“ Emergency location shared', 'emergency', '14.64095370', '120.96671910', 1, '2025-07-27 16:43:11', NULL),
(46, 'user_1753634641063', 'user', 'Help', NULL, NULL, NULL, 1, '2025-07-27 16:44:10', NULL),
(47, 'user_1753634187597', 'admin', 'Nigga', NULL, NULL, NULL, 0, '2025-07-27 16:45:05', NULL),
(48, 'user_1753634709566', 'user', 'oki', NULL, NULL, NULL, 1, '2025-07-27 16:46:06', NULL),
(49, 'user_1753634782314', 'user', 'hello i need help', NULL, NULL, NULL, 1, '2025-07-27 16:47:15', NULL),
(50, 'user_1753634782314', 'user', 'ðŸš¨ EMERGENCY HELP REQUESTED - Please respond immediately!', 'emergency', NULL, NULL, 1, '2025-07-27 16:48:00', NULL),
(51, 'user_1753634782314', 'user', 'ðŸ“ Emergency location shared', 'emergency', '14.68989440', '120.99256320', 1, '2025-07-27 16:48:00', NULL),
(52, 'user_1753661711727', 'user', 'help', NULL, NULL, NULL, 1, '2025-07-28 00:16:15', NULL),
(53, 'user_1753661778498', 'user', 'hello i need help', NULL, NULL, NULL, 1, '2025-07-28 00:17:37', NULL),
(54, 'user_1753661778498', 'user', 'hello?', NULL, NULL, NULL, 1, '2025-07-28 00:17:52', NULL),
(55, 'user_1753661895526', 'user', 'Hello', NULL, NULL, NULL, 1, '2025-07-28 00:18:23', NULL),
(56, 'user_1753661981407', 'user', 'ðŸš¨ EMERGENCY HELP REQUESTED - Please respond immediately!', 'emergency', NULL, NULL, 1, '2025-07-28 00:20:37', NULL),
(57, 'user_1753661981407', 'user', 'ðŸ“ Emergency location shared', 'emergency', '14.64095620', '120.96671260', 1, '2025-07-28 00:20:37', NULL),
(58, 'user_1753661981407', 'user', 'sige', NULL, NULL, NULL, 1, '2025-07-28 00:22:11', NULL),
(59, 'user_1753864032152', 'user', 'Help', NULL, NULL, NULL, 1, '2025-07-30 08:27:19', NULL),
(60, 'user_1753864032152', 'user', 'ðŸš¨ EMERGENCY HELP REQUESTED - Please respond immediately!', 'emergency', NULL, NULL, 1, '2025-07-30 08:27:34', NULL),
(61, 'user_1753864032152', 'user', 'ðŸ“ Emergency location shared', 'emergency', '14.55536800', '121.07178670', 1, '2025-07-30 08:27:36', NULL),
(62, 'user_1753864032152', 'user', 'ðŸ’¬ General inquiry started - User has a question.', 'general', NULL, NULL, 1, '2025-07-30 08:28:09', NULL),
(63, 'user_1754287823568', 'user', 'ia', NULL, NULL, NULL, 1, '2025-08-04 06:11:07', NULL),
(64, 'user_1754393610100', 'user', 'Help!', NULL, NULL, NULL, 1, '2025-08-05 11:33:42', NULL),
(65, 'user_1754495267491', 'user', 'ðŸš¨ EMERGENCY HELP REQUESTED - Please respond immediately!', 'emergency', NULL, NULL, 1, '2025-08-06 15:48:05', NULL),
(66, 'user_1754495267491', 'user', 'ðŸ“ Emergency location shared', 'emergency', '14.69106430', '121.06789580', 1, '2025-08-06 15:48:10', NULL),
(67, 'user_1754495267491', 'user', 'ðŸ”§ Technical support needed - User is waiting for assistance.', 'technical', NULL, NULL, 1, '2025-08-06 15:48:18', NULL),
(68, 'user_1754495267491', 'user', 'ðŸ’¬ General inquiry started - User has a question.', 'general', NULL, NULL, 1, '2025-08-06 15:48:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','integer','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `is_public`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Barangay Help Desk', 'string', 'Name of the help desk system', 1, '2025-07-26 18:16:29', '2025-07-26 18:16:29'),
(2, 'maintenance_mode', 'false', 'boolean', 'Enable/disable maintenance mode', 0, '2025-07-26 18:16:29', '2025-07-26 18:16:29'),
(3, 'max_file_size', '5242880', 'integer', 'Maximum file upload size in bytes (5MB)', 0, '2025-07-26 18:16:29', '2025-07-26 18:16:29'),
(4, 'allowed_file_types', '[\"jpg\",\"jpeg\",\"png\",\"gif\",\"pdf\",\"doc\",\"docx\"]', 'json', 'Allowed file types for uploads', 0, '2025-07-26 18:16:29', '2025-07-26 18:16:29'),
(5, 'emergency_notification_emails', '[]', 'json', 'Email addresses to notify for emergency requests', 0, '2025-07-26 18:16:29', '2025-07-26 18:16:29'),
(6, 'chat_timeout_minutes', '10', 'integer', 'Minutes before chat session times out', 0, '2025-07-26 18:16:29', '2025-07-26 18:16:29'),
(7, 'ticket_auto_close_days', '30', 'integer', 'Days after which resolved tickets are auto-closed', 0, '2025-07-26 18:16:29', '2025-07-26 18:16:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `city` varchar(100) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `status` enum('basic','verified') DEFAULT 'basic',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `middle_name`, `last_name`, `mobile_number`, `city`, `barangay`, `email`, `password`, `is_active`, `status`, `created_at`, `updated_at`) VALUES
(1, 'John rey', 'fernandez', 'ang', '09564886008', 'Malabon', '28', 'johnrey.ang@tup.edu.ph', '$2y$10$rUBbUmP7WXFzWFaeOBhbfuUFKpZ2Niv5Cen3RFbOkVox78lsLfpW.', 1, 'basic', '2025-07-26 18:17:53', '2025-07-26 18:17:53'),
(2, 'John rey', 'fernandez', 'ang', '09564886008', 'Quezon City', '28', 'kagariakko1@gmail.com', '$2y$10$.QKKGav3gmw9jpbTc3bkSemqVwiCCQT4smm43jELUd7mxI24ZhKYm', 1, 'verified', '2025-07-26 18:18:14', '2025-08-09 09:46:18'),
(3, 'danilo', 'fernandez', 'padlam', '09564886008', 'Valenzuela', '1', 'test@email.com', '$2y$10$R7CGMwlmUjaEoLfRatAD0e7iroI8m/XnnCVod.ePjGKJPrfAMAo1.', 1, 'basic', '2025-07-26 18:24:57', '2025-07-26 18:24:57'),
(4, 'John rey', 'sad', 'saga', '09564886009', 'Quezon City', '18', 'jreyang27@gmail.com', '$2y$10$6iiw8hHD9AYdGoxLMSsxVuXwXGNEOnvbnn37wTezh2U2kKJeNd2Ji', 1, 'basic', '2025-07-26 18:42:32', '2025-07-26 18:42:32'),
(5, 'Daniel', NULL, 'Pelaez', '09077208778', 'Quezon City', 'Pasong Tamo', 'denzrevz26@gmail.com', '$2y$10$7p7htCsk26M0zXNtYCSES.YRQq9S3VR9f74s2DFXPFDdV/YIGfHDm', 1, 'basic', '2025-07-26 22:32:10', '2025-07-26 22:32:10'),
(6, 'Aice', 'Gomez', 'Pisot', '09448522354', 'Quezon City', 'Fairview', 'enbcomshop11@gmail.com', '$2y$10$WM18CirJrzUs3y/TN/XJq./jU3ZlGJa8wR0KTJ4XB65dTgVl0Exzm', 1, 'basic', '2025-07-27 03:06:52', '2025-08-08 02:31:26'),
(7, 'Nigga', NULL, 'Nigga', '09123456689', 'Quezon City', 'Pasong Tamo', 'dmpelaez06@gmail.com', '$2y$10$MAwYrmwFov9zuas7ozq7T.X6tZkhhbHXUUBrytbzP8sRSGns1ZUBC', 1, 'basic', '2025-07-27 13:23:40', '2025-08-08 02:31:25'),
(8, 'Jauin', NULL, 'B', '09060769248', 'Quezon City', 'Fariview', 'jauinbautista15@gmail.com', '$2y$10$FXWwr6rpuBHKNbY1Pvdtuue96HHi2xP7RFqJDMLhPph8EoFP0Qnte', 1, 'verified', '2025-08-06 15:15:49', '2025-08-09 11:35:09');

-- --------------------------------------------------------

--
-- Table structure for table `verification_requests`
--

CREATE TABLE `verification_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `attachment_1` varchar(255) DEFAULT NULL,
  `attachment_2` varchar(255) DEFAULT NULL,
  `attachment_3` varchar(255) DEFAULT NULL,
  `id_number_1` varchar(50) DEFAULT NULL COMMENT 'ID number for primary document',
  `id_number_2` varchar(50) DEFAULT NULL COMMENT 'ID number for secondary document',
  `id_number_3` varchar(50) DEFAULT NULL COMMENT 'Reference number for additional document',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `verification_requests`
--

INSERT INTO `verification_requests` (`id`, `user_id`, `full_name`, `birthdate`, `attachment_1`, `attachment_2`, `attachment_3`, `id_number_1`, `id_number_2`, `id_number_3`, `status`, `rejection_reason`, `created_at`, `updated_at`, `processed_by`, `processed_at`) VALUES
(12, 2, 'danilo m. padlan', '2007-08-08', 'uploads/1754731286_pic1.jpg', 'uploads/1754731286_pic1.jpg', 'uploads/1754731286_pic1.jpg', '123124141ASDSAD', 'adadadwdaw', 'adwdawdadasd', 'pending', NULL, '2025-08-09 09:21:26', '2025-08-09 09:21:26', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `idx_token` (`session_token`),
  ADD KEY `idx_admin_active` (`admin_id`,`is_active`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_type_id` (`user_type`,`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_ip` (`ip_address`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_conversation` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_last_activity` (`last_activity`),
  ADD KEY `idx_admin` (`admin_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `idx_user_active` (`user_id`,`is_active`),
  ADD KEY `idx_sender_type` (`sender_type`),
  ADD KEY `idx_help_type` (`help_type`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_location` (`location_lat`,`location_lng`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_key` (`setting_key`),
  ADD KEY `idx_public` (`is_public`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_city_barangay` (`city`,`barangay`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `verification_requests`
--
ALTER TABLE `verification_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_id_number_1` (`id_number_1`),
  ADD KEY `idx_id_number_2` (`id_number_2`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `barangay_requests`
--
ALTER TABLE `barangay_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `verification_requests`
--
ALTER TABLE `verification_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
