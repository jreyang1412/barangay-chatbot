-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql308.infinityfree.com
-- Generation Time: Aug 14, 2025 at 11:43 PM
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
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_type` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_type`, `user_id`, `action`, `details`, `created_at`) VALUES
(1, 'user', 2, 'BARANGAY_REQUEST_SUBMITTED', 'barangay_clearance', '2025-08-14 14:33:23'),
(2, 'user', 2, 'BARANGAY_REQUEST_SUBMITTED', 'certificate_of_indigency', '2025-08-14 14:33:43');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `city` varchar(100) NOT NULL,
  `barangay_number` varchar(50) NOT NULL,
  `username` varchar(100) NOT NULL,
  `profile_picture` varchar(500) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `city`, `barangay_number`, `username`, `profile_picture`, `password`, `last_login`, `created_at`) VALUES
(1, 'caloocan', '028', 'admin_caloocan_brgy028', NULL, '$2y$10$53L7Nj9/ZJ/YI3YQhvHvVOuI5CAImJnhkl1FgbsTP0fW3xiMe62cy', '2025-08-09 15:54:06', '2025-07-26 20:50:47'),
(2, 'manila', '187', 'admin1', NULL, '$2y$10$CJdRAS5SINxaP1i6TTmZFuYraACGo94rJpcy9bKll8du3OMLC3DLy', NULL, '2025-07-26 21:05:38'),
(3, 'qc', '122', 'admin2', NULL, '$2y$10$I8fylOcrvsmrXS1gzWGpkODYZpAYABJKZbVvAIO5Hv5m5xo960WqK', NULL, '2025-07-26 21:08:02'),
(4, 'caloocan', '123', 'sepulture', NULL, '$2y$10$rgi8/2B8EGEfnBbrwXwvPOLbWIy96WMPboyftgWWWKyA3IUhcFoq2', NULL, '2025-07-26 23:04:14'),
(5, 'San Juan', '240', 'danilo', 'uploads/admin_profile_pictures/admin_5_1755182735.jpg', '$2y$10$io.IRWh2LJtvGl9ma9Rj4e/g8/YNzT8lXvB8vLOX4qPAIBzITmJm.', '2025-08-14 20:09:31', '2025-07-27 15:14:52'),
(6, 'Quezon City', '1', 'AdminMIlky', NULL, '$2y$10$ISW64naK.L1ZtHWRvZMskOlWTp8WEOpQHivwPIDQIpvAc7oSKQSIy', '2025-08-14 09:05:27', '2025-08-05 11:30:43'),
(7, 'Quezon City', '7', 'denzrevz26@gmail.com', NULL, '$2y$10$NVBGXz9C4o4W15VjiZxJweVwlvrhPsqYXMF0vPFxmlLGgffw62kn6', '2025-08-09 16:14:25', '2025-08-09 16:16:12'),
(8, 'Quezon City', '28', 'TUPsub', NULL, '$2y$10$.2ZFKObP6eYmEdctGtlwuurQgnyRSumM/2B93DLaWRLr/qdzN21vi', '2025-08-09 16:12:19', '2025-08-09 22:55:41'),
(9, 'Quezon City', 'Pasong Tamo', 'dmpelaez06', NULL, '$2y$10$vS2tX2ugkUooH4B8NQxNsOQ7ZjJH6DEVeNXL3hqNp8GJkBCJbcpo2', '2025-08-09 21:14:03', '2025-08-10 04:05:59'),
(10, 'San Juan', '21', 'danilo2', NULL, '$2y$10$bWRQjn1Ilg3YjxTG.0Lvje5KhfRafROu4MLJ6kgfiVLz0biNMkWrm', '2025-08-14 04:09:43', '2025-08-12 01:55:59'),
(11, 'Quezon City', 'Pasong Tamo', 'adminako', NULL, '$2y$10$t6AjCYE3.R/CmjYiUv8.deTW3RMIyLg2Mc1tPVb7g2FZns2qVeN0i', '2025-08-12 00:10:00', '2025-08-12 07:06:45'),
(12, 'Quezon City', 'Pasong Tamo', 'ptamoadmin', NULL, '$2y$10$fMafFYKN/QYBzR0FtvDJT.VMmQrxORTidujC6w3HGyz7COo01O8E.', '2025-08-14 08:09:43', '2025-08-12 11:43:49');

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
  `user_type` enum('user','admin') NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(13, 2, 'calamity_certificate', 'Ang', 'John Rey', 'fernandez', '2007-08-08', 'dawdwad', '09564886008', 'National Museum', 'wdadada', 'pending', NULL, NULL, '2025-08-09 15:25:59', '2025-08-09 16:17:20', NULL, NULL, '0000-00-00', '{\"incident_proof\":\"calamity_certificate_incident_proof_1754753159_689768877ed40.pdf\",\"valid_id\":\"calamity_certificate_valid_id_1754753159_689768877efe8.pdf\"}'),
(14, 9, 'barangay_clearance', 'Pelaez', 'Daniel', 'Mendoza', '2001-12-20', '8 Cresentwoods, Pingkian 2, Brgy. Pasong Tamo, Quezon City', '09484838184', 'Employment', '', 'pending', NULL, NULL, '2025-08-12 11:59:06', '2025-08-12 12:04:58', NULL, NULL, '0000-00-00', '[]'),
(0, 2, 'barangay_clearance', 'Ang', 'John Rey', 'fernandez', '2007-08-13', 'awdad', '09564886008', 'wdawd', 'adwwad', 'pending', NULL, NULL, '2025-08-14 14:33:23', '2025-08-14 14:33:23', NULL, NULL, '0000-00-00', '[]'),
(0, 2, 'certificate_of_indigency', 'Ang', 'John Rey', 'fernandez', '2007-08-13', 'dawdawd', '09564886008', 'wdadad', 'awdada', 'pending', NULL, NULL, '2025-08-14 14:33:43', '2025-08-14 14:33:43', NULL, NULL, '0000-00-00', '{\"pao_application\":\"certificate_of_indigency_pao_application_1755182023_689df3c756864.pdf\"}');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` varchar(50) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `city` varchar(100) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `help_type` enum('emergency','technical','general') DEFAULT 'general',
  `status` enum('waiting','active','resolved','closed') DEFAULT 'waiting',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `assigned_admin_id` int(11) DEFAULT NULL,
  `priority` enum('low','normal','high') DEFAULT 'normal',
  `user_name` varchar(255) DEFAULT NULL,
  `location_lat` decimal(10,8) DEFAULT NULL,
  `location_lng` decimal(11,8) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `user_id`, `city`, `barangay`, `help_type`, `status`, `created_at`, `last_activity`, `assigned_admin_id`, `priority`, `user_name`, `location_lat`, `location_lng`) VALUES
('john_rey_ang_2', 'user_2', 'Quezon City', '28', 'emergency', 'active', '2025-08-14 07:48:04', '2025-08-14 15:10:05', 12, 'high', 'John rey ang', NULL, NULL),
('psalm_ang_1755157521569', 'user_1755157521569', 'Caloocan', 'Bagumbong North', 'general', 'waiting', '2025-08-14 07:46:52', '2025-08-14 07:46:52', NULL, 'normal', 'Psalm Ang', NULL, NULL),
('user_2_1755157334155', 'user_2', 'Unknown', 'Unknown', 'technical', 'waiting', '2025-08-14 07:43:48', '2025-08-14 07:44:01', NULL, 'normal', 'null', NULL, NULL),
('test_user_1', 'user_1', 'Test City', 'Test Barangay', 'technical', 'active', '2025-08-14 07:21:12', '2025-08-14 07:21:28', 5, 'normal', 'Test User', NULL, NULL),
('john_rey_ang_123', 'user_123', 'Unknown', '28', 'technical', 'active', '2025-08-14 07:02:39', '2025-08-14 07:06:18', 5, 'normal', 'john rey ang', '14.64092694', '120.96675971'),
('_2', 'user_2', 'Quezon City', '28', '', 'active', '2025-08-14 02:52:19', '2025-08-14 07:01:34', 5, 'normal', 'John rey ang', NULL, NULL),
('psalm_ang_1755169711039', 'user_1755169711039', 'Caloocan', 'Bagumbong North', 'general', 'waiting', '2025-08-14 11:10:03', '2025-08-14 11:10:03', NULL, 'normal', 'Psalm Ang', NULL, NULL),
('psalm_ang_1755169740078', 'user_1755169740078', 'Caloocan', 'Bagumbong North', 'general', 'active', '2025-08-14 11:10:31', '2025-08-14 11:12:10', 10, 'normal', 'Psalm Ang', NULL, NULL),
('psalm_ang_1755169854332', 'user_1755169854332', 'Caloocan', 'Bagumbong North', 'general', 'waiting', '2025-08-14 11:12:33', '2025-08-14 11:12:33', NULL, 'normal', 'Psalm Ang', NULL, NULL),
('daniel_pelaez_9', 'user_9', 'Quezon City', 'Pasong Tamo', 'emergency', 'waiting', '2025-08-14 14:52:12', '2025-08-14 14:52:56', NULL, 'high', 'Daniel Pelaez', '14.68023040', '121.05710560'),
('daniel_pelaez_11', 'user_11', 'Quezon City', 'Pasong Tamo', 'emergency', 'active', '2025-08-14 15:12:55', '2025-08-14 15:13:36', 12, 'high', 'Daniel Pelaez', '14.68026460', '121.05713460'),
('jauin_b_8', 'user_8', 'Quezon City', 'Fariview', 'emergency', 'active', '2025-08-14 16:03:52', '2025-08-14 16:05:49', 6, 'high', 'Jauin B', '14.69747980', '120.96492530');

-- --------------------------------------------------------

--
-- Table structure for table `file_attachments`
--

CREATE TABLE `file_attachments` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `stored_filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_hash` varchar(64) NOT NULL,
  `thumbnail_path` varchar(500) DEFAULT NULL,
  `upload_ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `file_attachments`
--

INSERT INTO `file_attachments` (`id`, `message_id`, `original_filename`, `stored_filename`, `file_path`, `file_size`, `mime_type`, `file_hash`, `thumbnail_path`, `upload_ip`, `created_at`) VALUES
(1, 1, 'pic1.jpg', '1755089391_689c89efe3e20.jpg', 'uploads/images/1755089391_689c89efe3e20.jpg', 97130, 'image/jpeg', '1e1f94510e5b7d0557f572cba5e99e1b7aca81d8cb505394f9cf252030be1ac5', NULL, NULL, '2025-08-13 12:49:51'),
(2, 3, 'camera_photo_1755090679397.jpg', '1755090681_689c8ef9e5382.jpg', 'uploads/images/1755090681_689c8ef9e5382.jpg', 107231, 'image/jpeg', '7174ec6187980f832775186b12f35dc410bb73810a459a06e310e2e98ab0bea2', NULL, NULL, '2025-08-13 13:11:21'),
(3, 6, 'pic1.jpg', '1755093890_689c9b8250bac.jpg', 'uploads/images/1755093890_689c9b8250bac.jpg', 97130, 'image/jpeg', '1e1f94510e5b7d0557f572cba5e99e1b7aca81d8cb505394f9cf252030be1ac5', NULL, NULL, '2025-08-13 14:04:50'),
(4, 8, 'IMG_0256.jpeg', '1755094017_689c9c0119809.jpeg', 'uploads/images/1755094017_689c9c0119809.jpeg', 23972, 'image/jpeg', 'b283118a0d96e60f7dc6f6645cf944130c82b2bfe00193575f57f1a34d906ff5', NULL, NULL, '2025-08-13 14:06:57'),
(5, 17, '1000015118.jpg', '1755094661_689c9e85de7f6.jpg', 'uploads/images/1755094661_689c9e85de7f6.jpg', 4220288, 'image/jpeg', '2e8a2f4aa61e7f1f37e4081edc338bb94c9bab2133fcc0d484caf5019c910537', NULL, NULL, '2025-08-13 14:17:41'),
(6, 18, 'pic1.jpg', '1755136287_689d411f278c5.jpg', 'uploads/images/1755136287_689d411f278c5.jpg', 97130, 'image/jpeg', '1e1f94510e5b7d0557f572cba5e99e1b7aca81d8cb505394f9cf252030be1ac5', NULL, NULL, '2025-08-14 01:51:27'),
(7, 21, 'pic1.jpg', '1755138089_689d482968fae.jpg', 'uploads/images/1755138089_689d482968fae.jpg', 97130, 'image/jpeg', '1e1f94510e5b7d0557f572cba5e99e1b7aca81d8cb505394f9cf252030be1ac5', NULL, NULL, '2025-08-14 02:21:29');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` varchar(50) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `sender_type` enum('user','admin','system') NOT NULL,
  `message_type` enum('text','image','location','file') DEFAULT 'text',
  `message` text DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `thumbnail_path` varchar(500) DEFAULT NULL,
  `location_lat` decimal(10,8) DEFAULT NULL,
  `location_lng` decimal(11,8) DEFAULT NULL,
  `help_type` enum('emergency','technical','general') DEFAULT NULL,
  `is_read_by_user` tinyint(1) DEFAULT 0,
  `is_read_by_admin` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `admin_id` int(11) DEFAULT NULL,
  `admin_name` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `user_id`, `sender_type`, `message_type`, `message`, `file_path`, `file_name`, `file_size`, `file_type`, `thumbnail_path`, `location_lat`, `location_lng`, `help_type`, `is_read_by_user`, `is_read_by_admin`, `is_active`, `created_at`, `admin_id`, `admin_name`) VALUES
(48, 'psalm_ang_1755169740078', 'user_1755169740078', 'user', 'text', 'help', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'general', 0, 1, 1, '2025-08-14 11:10:31', NULL, NULL),
(49, 'psalm_ang_1755169740078', 'user_1755169740078', 'user', 'image', 'Sent an image', 'uploads/images/1755169843_689dc4338e36d.jpg', 'pic1.jpg', 97130, 'image/jpeg', NULL, NULL, NULL, 'general', 0, 1, 1, '2025-08-14 11:10:43', NULL, NULL),
(46, 'john_rey_ang_2', 'user_2', 'user', 'text', 'Help', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'emergency', 0, 1, 1, '2025-08-14 07:48:04', NULL, NULL),
(47, 'psalm_ang_1755169711039', 'user_1755169711039', 'user', 'text', 'adadsad', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'general', 0, 1, 1, '2025-08-14 11:10:03', NULL, NULL),
(45, 'psalm_ang_1755157521569', 'user_1755157521569', 'user', 'text', 'dwadada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'general', 0, 1, 1, '2025-08-14 07:46:52', NULL, NULL),
(44, 'user_2_1755157334155', 'user_2', 'user', 'text', 'adwdawdad', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'general', 0, 1, 1, '2025-08-14 07:44:01', NULL, NULL),
(43, 'user_2_1755157334155', 'user_2', 'user', 'text', 'asdsadasdasd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'technical', 0, 1, 1, '2025-08-14 07:43:48', NULL, NULL),
(42, 'test_user_1', 'user_1', 'user', 'image', 'Sent an image', 'uploads/images/1755156088_689d8e7842429.jpg', 'pic1.jpg', 97130, 'image/jpeg', NULL, NULL, NULL, 'technical', 0, 1, 1, '2025-08-14 07:21:28', NULL, NULL),
(41, 'test_user_1', 'user_1', 'admin', 'text', 'hello', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 1, '2025-08-14 07:21:19', 5, 'danilo'),
(40, 'test_user_1', 'user_1', 'user', 'text', 'hi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'technical', 0, 1, 1, '2025-08-14 07:21:12', NULL, NULL),
(39, 'john_rey_ang_123', 'user_123', 'user', 'location', 'ðŸ“ Emergency location shared', NULL, NULL, NULL, NULL, NULL, '14.64092694', '120.96675971', 'emergency', 0, 1, 1, '2025-08-14 07:06:18', NULL, NULL),
(38, 'john_rey_ang_123', 'user_123', 'admin', 'text', 'fuck off', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 1, '2025-08-14 07:03:22', 5, 'danilo'),
(37, 'john_rey_ang_123', 'user_123', 'user', 'image', 'Sent an image', 'uploads/images/1755154982_689d8a2619729.jpg', 'pic1.jpg', 97130, 'image/jpeg', NULL, NULL, NULL, 'general', 0, 1, 1, '2025-08-14 07:03:02', NULL, NULL),
(36, 'john_rey_ang_123', 'user_123', 'user', 'text', 'hello', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'technical', 0, 1, 1, '2025-08-14 07:02:39', NULL, NULL),
(35, '_2', 'user_2', 'user', 'text', 'hoh?', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 1, 1, '2025-08-14 07:01:34', NULL, NULL),
(34, '_2', 'user_2', 'user', 'text', 'awww', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 1, 1, '2025-08-14 06:47:41', NULL, NULL),
(32, '_2', 'user_2', 'admin', 'text', 'weh', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 1, '2025-08-14 06:16:08', 5, 'danilo'),
(33, '_2', 'user_2', 'admin', 'text', 'loveu', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 1, '2025-08-14 06:16:09', 5, 'danilo'),
(30, '_2', 'user_2', 'user', 'text', 'omg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 1, 1, '2025-08-14 06:16:02', NULL, NULL),
(31, '_2', 'user_2', 'user', 'text', 'omg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 1, 1, '2025-08-14 06:16:03', NULL, NULL),
(29, '_2', 'user_2', 'user', 'text', 'omg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 1, 1, '2025-08-14 06:16:01', NULL, NULL),
(28, '_2', 'user_2', 'admin', 'text', 'helu', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 1, '2025-08-14 06:15:35', 5, 'danilo'),
(26, '_2', 'user_2', 'user', 'text', 'hihi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 1, 1, '2025-08-14 03:07:48', NULL, NULL),
(27, '_2', 'user_2', 'user', 'text', 'wewew', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 1, 1, '2025-08-14 03:07:53', NULL, NULL),
(25, '_2', 'user_2', 'user', 'text', 'hi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 1, 1, '2025-08-14 02:54:30', NULL, NULL),
(24, '_2', 'user_2', 'user', 'image', 'Sent an image', 'uploads/images/1755139939_689d4f6312654.jpg', 'pic1.jpg', 97130, 'image/jpeg', NULL, NULL, NULL, '', 0, 1, 1, '2025-08-14 02:52:19', NULL, NULL),
(50, 'john_rey_ang_2', 'user_2', 'user', 'text', 'nihao', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'technical', 0, 1, 1, '2025-08-14 11:11:34', NULL, NULL),
(51, 'psalm_ang_1755169740078', 'user_1755169740078', 'user', 'text', 'so?', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'general', 0, 1, 1, '2025-08-14 11:11:56', NULL, NULL),
(52, 'john_rey_ang_2', 'user_2', 'admin', 'text', 'oki', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 1, '2025-08-14 11:12:06', 10, 'danilo2'),
(53, 'psalm_ang_1755169740078', 'user_1755169740078', 'admin', 'text', 'omki', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 1, '2025-08-14 11:12:10', 10, 'danilo2'),
(54, 'psalm_ang_1755169854332', 'user_1755169854332', 'user', 'text', 'oksi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'general', 0, 1, 1, '2025-08-14 11:12:33', NULL, NULL),
(55, 'john_rey_ang_2', 'user_2', 'user', 'text', 'what?', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'general', 0, 1, 1, '2025-08-14 14:03:02', NULL, NULL),
(56, 'john_rey_ang_2', 'user_2', 'user', 'text', 'hihi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'general', 0, 1, 1, '2025-08-14 14:03:02', NULL, NULL),
(57, 'john_rey_ang_2', 'user_2', 'user', 'text', 'dadada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'general', 0, 1, 1, '2025-08-14 14:03:06', NULL, NULL),
(58, 'daniel_pelaez_9', 'user_9', 'user', 'location', 'ðŸ“ Emergency location shared', NULL, NULL, NULL, NULL, NULL, '14.68023040', '121.05710560', 'emergency', 0, 1, 1, '2025-08-14 14:52:12', NULL, NULL),
(59, 'daniel_pelaez_9', 'user_9', 'user', 'text', 'yow', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'emergency', 0, 1, 1, '2025-08-14 14:52:16', NULL, NULL),
(60, 'daniel_pelaez_9', 'user_9', 'user', 'location', 'ðŸ“ Emergency location shared', NULL, NULL, NULL, NULL, NULL, '14.68023040', '121.05710560', 'emergency', 0, 1, 1, '2025-08-14 14:52:56', NULL, NULL),
(61, 'john_rey_ang_2', 'user_2', 'user', 'text', 'hi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'general', 0, 1, 1, '2025-08-14 14:54:47', NULL, NULL),
(62, 'john_rey_ang_2', 'user_2', 'user', 'image', 'Sent an image', 'uploads/images/1755183356_689df8fcd3688.jpg', '1.jpg', 64381, 'image/jpeg', NULL, NULL, NULL, 'general', 0, 1, 1, '2025-08-14 14:55:56', NULL, NULL),
(63, 'john_rey_ang_2', 'user_2', 'user', 'text', 'asd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'technical', 0, 1, 1, '2025-08-14 14:56:03', NULL, NULL),
(64, 'john_rey_ang_2', 'user_2', 'admin', 'text', 'aray ko', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 1, '2025-08-14 15:10:05', 12, 'ptamoadmin'),
(65, 'daniel_pelaez_11', 'user_11', 'user', 'text', 'nugga', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'emergency', 0, 1, 1, '2025-08-14 15:12:55', NULL, NULL),
(66, 'daniel_pelaez_11', 'user_11', 'user', 'location', 'ðŸ“ Emergency location shared', NULL, NULL, NULL, NULL, NULL, '14.68026460', '121.05713460', 'emergency', 0, 1, 1, '2025-08-14 15:13:03', NULL, NULL),
(67, 'daniel_pelaez_11', 'user_11', 'admin', 'text', 'what', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 1, '2025-08-14 15:13:03', 12, 'ptamoadmin'),
(68, 'daniel_pelaez_11', 'user_11', 'user', 'text', 'nigga kaba', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'general', 0, 1, 1, '2025-08-14 15:13:29', NULL, NULL),
(69, 'daniel_pelaez_11', 'user_11', 'user', 'location', 'ðŸ“ Emergency location shared', NULL, NULL, NULL, NULL, NULL, '14.68026460', '121.05713460', 'general', 0, 1, 1, '2025-08-14 15:13:32', NULL, NULL),
(70, 'daniel_pelaez_11', 'user_11', 'admin', 'text', 'hindi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 1, '2025-08-14 15:13:36', 12, 'ptamoadmin'),
(71, 'jauin_b_8', 'user_8', 'user', 'location', 'ðŸ“ Emergency location shared', NULL, NULL, NULL, NULL, NULL, '14.69747980', '120.96492530', 'emergency', 0, 1, 1, '2025-08-14 16:03:52', NULL, NULL),
(72, 'jauin_b_8', 'user_8', 'user', 'image', 'Sent an image', 'uploads/images/1755187465_689e0909dd502.jpg', 'camera_photo_1755187462612.jpg', 9124, 'image/jpeg', NULL, NULL, NULL, 'emergency', 0, 1, 1, '2025-08-14 16:04:24', NULL, NULL),
(73, 'jauin_b_8', 'user_8', 'admin', 'text', 'What happened?', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 1, '2025-08-14 16:05:49', 6, 'AdminMIlky');

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
  `profile_picture` varchar(500) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `status` enum('basic','verified') DEFAULT 'basic',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `middle_name`, `last_name`, `mobile_number`, `city`, `barangay`, `email`, `profile_picture`, `password`, `is_active`, `status`, `created_at`, `updated_at`) VALUES
(1, 'John rey', 'fernandez', 'ang', '09564886008', 'Malabon', '28', 'johnrey.ang@tup.edu.ph', NULL, '$2y$10$rUBbUmP7WXFzWFaeOBhbfuUFKpZ2Niv5Cen3RFbOkVox78lsLfpW.', 1, 'basic', '2025-07-26 18:17:53', '2025-07-26 18:17:53'),
(2, 'John rey', 'fernandez', 'ang', '09564886008', 'Quezon City', '28', 'kagariakko1@gmail.com', 'uploads/profile_pictures/user_2_1755180223.jpg', '$2y$10$.QKKGav3gmw9jpbTc3bkSemqVwiCCQT4smm43jELUd7mxI24ZhKYm', 1, 'verified', '2025-07-26 18:18:14', '2025-08-14 17:03:43'),
(3, 'danilo', 'fernandez', 'padlam', '09564886008', 'Valenzuela', '1', 'test@email.com', NULL, '$2y$10$R7CGMwlmUjaEoLfRatAD0e7iroI8m/XnnCVod.ePjGKJPrfAMAo1.', 1, 'basic', '2025-07-26 18:24:57', '2025-07-26 18:24:57'),
(4, 'John rey', 'sad', 'saga', '09564886009', 'Quezon City', '18', 'jreyang27@gmail.com', NULL, '$2y$10$6iiw8hHD9AYdGoxLMSsxVuXwXGNEOnvbnn37wTezh2U2kKJeNd2Ji', 1, 'basic', '2025-07-26 18:42:32', '2025-07-26 18:42:32'),
(5, 'Daniel', NULL, 'Pelaez', '09077208778', 'Quezon City', 'Pasong Tamo', 'denzrevz26@gmail.com', NULL, '$2y$10$7p7htCsk26M0zXNtYCSES.YRQq9S3VR9f74s2DFXPFDdV/YIGfHDm', 1, 'verified', '2025-07-26 22:32:10', '2025-08-14 15:12:08'),
(6, 'Aice', 'Gomez', 'Pisot', '09448522354', 'Quezon City', 'Fairview', 'enbcomshop11@gmail.com', NULL, '$2y$10$WM18CirJrzUs3y/TN/XJq./jU3ZlGJa8wR0KTJ4XB65dTgVl0Exzm', 1, 'basic', '2025-07-27 03:06:52', '2025-08-08 02:31:26'),
(7, 'Nigga', NULL, 'Nigga', '09123456689', 'Quezon City', 'Pasong Tamo', 'dmpelaez06@gmail.com', NULL, '$2y$10$MAwYrmwFov9zuas7ozq7T.X6tZkhhbHXUUBrytbzP8sRSGns1ZUBC', 1, 'verified', '2025-07-27 13:23:40', '2025-08-14 15:12:17'),
(8, 'Jauin', NULL, 'B', '09060769248', 'Quezon City', 'Fariview', 'jauinbautista15@gmail.com', NULL, '$2y$10$FXWwr6rpuBHKNbY1Pvdtuue96HHi2xP7RFqJDMLhPph8EoFP0Qnte', 1, 'verified', '2025-08-06 15:15:49', '2025-08-09 11:35:09'),
(9, 'Daniel', 'Mendoza', 'Pelaez', '09484838184', 'Quezon City', 'Pasong Tamo', 'dmpelaez@gmail.com', NULL, '$2y$10$aJRZxViyR4MY9MK2jLhCyeCCTlaruWOmJEoiKT.CAYVtGN8ZyYrpW', 1, 'verified', '2025-08-10 03:02:10', '2025-08-12 11:58:00'),
(10, 'John rey', 'Mandarambong', 'Fernandez', '09711168999', 'Manila', 'Santa Mesa', 'keimakun27@gmail.com', NULL, '$2y$10$xbc3//OYgHMZjbaCBBrPQ.rpyWNoNGqhV/9GoVGZGMf86H7fiMfLm', 1, 'basic', '2025-08-10 12:11:51', '2025-08-10 12:11:51'),
(11, 'Daniel', NULL, 'Pelaez', '09484838184', 'Quezon City', 'Pasong Tamo', 'denzrevz@gmail.com', NULL, '$2y$10$W8KdbhodNxhWtKKuE2xpXOEbfQgiUC53iKhIZiA4guX4KTwq.PZxy', 1, 'verified', '2025-08-11 15:16:37', '2025-08-14 15:12:28'),
(0, 'Psalm', 'Fernandez', 'Ang', '09564886008', 'Caloocan', 'Bagumbong North', 'haremdicker249@gmail.com', NULL, '$2y$10$7L0mpC6gVv.5B83r0V2b8eQGMc84c4A8TNnBlDWok328TD0EeHHr2', 1, 'basic', '2025-08-14 07:05:36', '2025-08-14 07:05:36');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `user_type` enum('user','guest') DEFAULT 'guest',
  `session_token` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `barangay` varchar(50) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(12, 2, 'danilo m. padlan', '2007-08-08', 'uploads/1754731286_pic1.jpg', 'uploads/1754731286_pic1.jpg', 'uploads/1754731286_pic1.jpg', '123124141ASDSAD', 'adadadwdaw', 'adwdawdadasd', 'pending', NULL, '2025-08-09 09:21:26', '2025-08-09 09:21:26', NULL, NULL),
(13, 7, 'Daniel Mendoza Pelaez', '2003-12-31', 'uploads/1754998328_BirthCert.pdf', NULL, NULL, '23-1231211-2323', NULL, NULL, 'pending', NULL, '2025-08-12 11:32:08', '2025-08-12 11:32:08', NULL, NULL),
(14, 9, 'Daniel Pelaez', '2004-02-03', 'uploads/1754998739_Philhealth.jpg', NULL, NULL, '23-1231211-2323', NULL, NULL, 'pending', NULL, '2025-08-12 11:38:59', '2025-08-12 11:38:59', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_admins_location` (`city`,`barangay_number`);

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
  ADD KEY `idx_user_action` (`user_type`,`user_id`,`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_status_city_barangay` (`status`,`city`,`barangay`),
  ADD KEY `idx_last_activity` (`last_activity`),
  ADD KEY `idx_conversations_help_type` (`help_type`,`status`);

--
-- Indexes for table `file_attachments`
--
ALTER TABLE `file_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`),
  ADD KEY `idx_file_hash` (`file_hash`),
  ADD KEY `idx_mime_type` (`mime_type`),
  ADD KEY `idx_file_attachments_size` (`file_size`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_conversation_created` (`conversation_id`,`created_at`),
  ADD KEY `idx_sender_type` (`sender_type`),
  ADD KEY `idx_message_type` (`message_type`),
  ADD KEY `idx_messages_file_type` (`message_type`,`created_at`);

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
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_user_location` (`city`,`barangay`),
  ADD KEY `idx_users_location` (`city`,`barangay`),
  ADD KEY `idx_users_active` (`is_active`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `idx_user_token` (`session_token`),
  ADD KEY `idx_user_activity` (`user_id`,`last_activity`);

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
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `file_attachments`
--
ALTER TABLE `file_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
