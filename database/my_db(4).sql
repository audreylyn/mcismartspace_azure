-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 25, 2025 at 10:42 AM
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
-- Database: `my_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `buildings`
--

CREATE TABLE `buildings` (
  `id` int(11) NOT NULL,
  `building_name` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `number_of_floors` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buildings`
--

INSERT INTO `buildings` (`id`, `building_name`, `department`, `number_of_floors`, `created_at`) VALUES
(1, 'Accountancy Building', 'Accountancy', 4, '2025-05-22 12:05:20'),
(2, 'Business Administration Complex', 'Business Administration', 5, '2025-05-22 12:05:20'),
(3, 'Hospitality Management Building', 'Hospitality Management', 3, '2025-05-22 12:05:20'),
(4, 'Education and Arts Center', 'Education, Arts, and Sciences', 4, '2025-05-22 12:05:20'),
(5, 'Criminal Justice Building', 'Criminal Justice Education', 3, '2025-05-22 12:05:20'),
(6, 'Sports Complex', 'Athletics', 1, '2025-08-18 21:17:52'),
(11, 'Bill Building', 'Accountancy', 7, '2025-08-25 02:59:46'),
(12, '4', 'Accountancy', 7, '2025-08-25 04:38:14');

-- --------------------------------------------------------

--
-- Table structure for table `dept_admin`
--

CREATE TABLE `dept_admin` (
  `AdminID` int(11) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Department` varchar(50) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `RoleID` int(11) NOT NULL DEFAULT 2
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dept_admin`
--

INSERT INTO `dept_admin` (`AdminID`, `FirstName`, `LastName`, `Department`, `Email`, `Password`, `RoleID`) VALUES
(46, 'Jane', 'Smith', 'Accountancy', 'jane.smith@gmail.com', '$2y$10$25LjpttRKXO0m5mXhLHOPexq.Tft3WQMNrFizPT3wzBJ9Fnw3ji3u', 2),
(54, 'Rianne', 'Gon', 'Education and Arts', 'rianne@gmail.com', '$2y$10$j2ZcP2CfetkK/6QmP1aTa.Ur35UU0k0p8DUzV.ljdmNs0Sd0CCNiq', 2),
(56, 'John', 'Doe', 'Business Administration', 'john.doe@gmail.com', '$2y$10$QbvCkE8RCU4h14sdTJL0JOvtymMTin/uuUE1l00EWK4SH909Qal/C', 2),
(61, 'Bob', 'Brown', 'Education and Arts', 'bob.brown@gmail.com', '$2y$10$oS2d9aeb2ZV0Jgr78dlLkegC2PfvIXfOoFHIYSTLRBVoj/C9PeY9q', 2),
(63, 'rian', 'Saquez', 'Hospitality Management', 'rian@gmail.com', '$2y$10$HvFbYN7s3jn6r.KVUks1ieDvEga2NiXP0TeyA0gnwtp6L1TzOF/Ya', 2),
(64, 'charlie', 'cha', 'Criminal Justice', 'char@gmail.com', '$2y$10$Lv7A3h27bQgDN1YNK9esyOKySMo39STA26eUyN/sk6su9yog2B1d.', 2);

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`id`, `name`, `description`, `category`, `created_at`) VALUES
(1, 'Smart TV', 'A smart television with internet capabilities', 'Electronics', '2025-05-22 12:05:20'),
(2, 'TV Remote', 'Remote control compatible with smart TVs', 'Accessories', '2025-05-22 12:05:20'),
(3, 'Projector', 'Digital projector for presentations', 'Electronics', '2025-05-22 12:05:20'),
(4, 'Electric Fan', 'Oscillating electric fan for ventilation', 'Appliances', '2025-05-22 12:05:20'),
(5, 'Aircon', 'Air conditioning unit for room cooling', 'Appliances', '2025-05-22 12:05:20'),
(6, 'Speaker', 'Audio speaker system for sound output', 'Audio Equipment', '2025-05-22 12:05:20'),
(7, 'Microphone', 'Handheld microphone for voice amplification', 'Audio Equipment', '2025-05-22 12:05:20'),
(8, 'Lapel', 'Clip-on lapel microphone for presentations', 'Audio Equipment', '2025-05-22 12:05:20'),
(9, 'HDMI Cable', 'High-Definition Multimedia Interface cable for audio/video connection', 'Accessories', '2025-05-22 12:05:20'),
(28, 'Lapel', 'lapel lapel', 'Teaching Materials', '2025-08-25 03:07:09');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_audit`
--

CREATE TABLE `equipment_audit` (
  `id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `action_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment_audit`
--

INSERT INTO `equipment_audit` (`id`, `equipment_id`, `action`, `action_date`, `notes`) VALUES
(5, 2, 'Assigned', '2025-08-23 12:26:36', 'Assigned to room ID: 11'),
(6, 1, 'Assigned', '2025-08-23 12:35:24', 'Assigned to room ID: 11'),
(7, 3, 'Assigned', '2025-08-23 12:35:30', 'Assigned to room ID: 11'),
(8, 4, 'Assigned', '2025-08-23 12:35:36', 'Assigned to room ID: 11'),
(9, 5, 'Assigned', '2025-08-23 12:35:41', 'Assigned to room ID: 11'),
(10, 6, 'Assigned', '2025-08-23 12:35:52', 'Assigned to room ID: 11'),
(11, 7, 'Assigned', '2025-08-23 12:36:03', 'Assigned to room ID: 11'),
(12, 8, 'Assigned', '2025-08-23 12:36:11', 'Assigned to room ID: 11'),
(13, 9, 'Assigned', '2025-08-23 12:36:24', 'Assigned to room ID: 11'),
(14, 2, 'Issue Reported', '2025-08-23 12:44:04', 'Issue reported by student ID: 20 - Type: Peripheral Not Working'),
(15, 5, 'Issue Reported', '2025-08-23 12:48:22', 'Issue reported by student ID: 20 - Type: Hardware Failure'),
(16, 7, 'Issue Reported', '2025-08-23 12:58:19', 'Issue reported by student ID: 20 - Type: Audio Problem'),
(17, 5, 'Issue Reported', '2025-08-23 13:23:01', 'Issue reported by student ID: 20 - Type: Connectivity Issue'),
(18, 2, 'Status Updated: pending Condition: missing', '2025-08-23 14:49:54', 'Admin response: missing you'),
(19, 2, 'Status Updated: resolved Condition: missing', '2025-08-23 14:50:09', 'Admin response: missing you'),
(20, 2, 'Status Updated: resolved Condition: working', '2025-08-24 06:43:44', 'Admin response: ok na'),
(21, 2, 'Issue Reported', '2025-08-24 06:44:38', 'Issue reported by student ID: 20 - Type: Other'),
(22, 9, 'Issue Reported', '2025-08-24 06:59:03', 'Issue reported by student ID: 20 - Type: Hardware Failure'),
(23, 9, 'Status Updated: in_progress Condition: needs_repai', '2025-08-24 07:30:05', 'Admin response: thankss'),
(24, 2, 'Status Updated: in_progress Condition: maintenance', '2025-08-24 07:35:47', 'Admin response: '),
(25, 2, 'Status Updated: in_progress Condition: maintenance', '2025-08-24 07:35:56', 'Admin response: '),
(26, 2, 'Status Updated: in_progress Condition: maintenance', '2025-08-24 07:36:02', 'Admin response: '),
(27, 2, 'Status Updated: in_progress Condition: maintenance', '2025-08-24 07:41:49', 'Admin response: cvfdsfgfdsfdsf'),
(28, 2, 'Status Updated: resolved Condition: working', '2025-08-25 03:02:37', 'Admin response: cvfdsfgfdsfdsf'),
(29, 2, 'Issue Reported', '2025-08-25 03:03:27', 'Issue reported by student ID: 20 - Type: Power Problem'),
(30, 4, 'Assigned', '2025-08-25 03:07:44', 'Assigned to room ID: 17'),
(31, 4, 'Issue Reported', '2025-08-25 03:19:19', 'Issue reported by teacher ID: 17 - Type: Hardware Failure'),
(32, 1, 'Assigned', '2025-08-25 03:54:29', 'Assigned to room ID: 6'),
(33, 2, 'Assigned', '2025-08-25 03:54:32', 'Assigned to room ID: 7'),
(34, 3, 'Assigned', '2025-08-25 03:54:36', 'Assigned to room ID: 8'),
(35, 6, 'Assigned', '2025-08-25 03:54:41', 'Assigned to room ID: 9'),
(36, 1, 'Assigned', '2025-08-25 03:56:38', 'Assigned to room ID: 16'),
(37, 2, 'Assigned', '2025-08-25 03:56:42', 'Assigned to room ID: 17'),
(38, 6, 'Assigned', '2025-08-25 03:56:47', 'Assigned to room ID: 18'),
(39, 8, 'Assigned', '2025-08-25 03:56:50', 'Assigned to room ID: 19'),
(40, 5, 'Assigned', '2025-08-25 04:00:27', 'Assigned to room ID: 18'),
(41, 8, 'Assigned', '2025-08-25 04:00:35', 'Assigned to room ID: 20'),
(42, 28, 'Assigned', '2025-08-25 04:00:44', 'Assigned to room ID: 18'),
(43, 4, 'Assigned', '2025-08-25 04:00:55', 'Assigned to room ID: 21'),
(44, 6, 'Assigned', '2025-08-25 04:01:01', 'Assigned to room ID: 22'),
(45, 3, 'Assigned', '2025-08-25 04:01:07', 'Assigned to room ID: 24'),
(46, 2, 'Assigned', '2025-08-25 04:02:36', 'Assigned to room ID: 5'),
(47, 6, 'Assigned', '2025-08-25 04:02:39', 'Assigned to room ID: 5'),
(48, 4, 'Assigned', '2025-08-25 04:02:44', 'Assigned to room ID: 5'),
(49, 4, 'Assigned', '2025-08-25 05:05:53', 'Assigned to room ID: 8'),
(50, 4, 'Issue Reported', '2025-08-25 07:10:32', 'Issue reported by student ID: 26 - Type: Connectivity Issue'),
(51, 4, 'Status Updated: in_progress Condition: needs_repai', '2025-08-25 07:13:58', 'Admin response: '),
(52, 4, 'Status Updated: resolved Condition: working', '2025-08-25 08:02:12', 'Admin response: '),
(53, 4, 'Status Updated: resolved Condition: working', '2025-08-25 08:02:27', 'Admin response: ');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_issues`
--

CREATE TABLE `equipment_issues` (
  `id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `issue_type` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','in_progress','resolved','rejected') DEFAULT 'pending',
  `admin_response` text DEFAULT NULL,
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `reference_number` varchar(15) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment_issues`
--

INSERT INTO `equipment_issues` (`id`, `equipment_id`, `student_id`, `teacher_id`, `issue_type`, `description`, `status`, `admin_response`, `reported_at`, `resolved_at`, `image_path`, `reference_number`, `rejection_reason`) VALUES
(9, 4, 26, NULL, 'Connectivity Issue', 'DSDSDSDSDSDSD', 'resolved', '', '2025-08-25 07:10:32', '2025-08-25 08:02:27', '../uploads/equipment_issues/issue_26_1756105832.jpg', 'EQ134785', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `email` varchar(100) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `ip_address`, `email`, `attempt_time`, `success`) VALUES
(329, '::1', 'anita@gmail.com', '2025-08-24 08:16:05', 1),
(330, '127.0.0.1', 'registrar@gmail.com', '2025-08-24 08:19:17', 1),
(331, '127.0.0.1', 'anita@gmail.com', '2025-08-24 08:19:52', 1),
(332, '127.0.0.1', 'john.doe@gmail.com', '2025-08-24 08:20:02', 1),
(333, '192.168.8.105', 'anita@gmail.com', '2025-08-24 10:13:32', 1),
(334, '127.0.0.1', 'john.doe@gmail.com', '2025-08-24 10:14:58', 1),
(335, '192.168.8.105', 'anita@gmail.com', '2025-08-24 10:56:56', 1),
(336, '192.168.8.105', 'anita@gmail.com', '2025-08-24 10:57:54', 1),
(337, '192.168.8.105', 'anita@gmail.com', '2025-08-24 11:09:51', 1),
(338, '127.0.0.1', 'john.doe@gmail.com', '2025-08-24 11:13:44', 1),
(340, '::1', 'john.doe@gmail.com', '2025-08-24 11:21:59', 1),
(341, '192.168.8.105', 'anita@gmail.com', '2025-08-24 11:22:48', 1),
(342, '192.168.8.105', 'anita@gmail.com', '2025-08-24 11:24:09', 1),
(343, '::1', 'anita@gmail.com', '2025-08-24 11:25:23', 1),
(344, '192.168.8.105', 'anita@gmail.com', '2025-08-24 11:39:02', 1),
(345, '192.168.8.105', 'anita@gmail.com', '2025-08-24 11:42:34', 1),
(346, '192.168.8.105', 'anita@gmail.com', '2025-08-24 11:45:16', 1),
(347, '::1', 'john.doe@gmail.com', '2025-08-24 11:52:08', 1),
(348, '192.168.8.105', 'anita@gmail.com', '2025-08-24 11:52:56', 1),
(349, '::1', 'anita@gmail.com', '2025-08-24 12:07:11', 1),
(350, '::1', 'john.doe@gmail.com', '2025-08-24 12:12:43', 1),
(351, '::1', 'john.doe@gmail.com', '2025-08-24 12:35:59', 1),
(352, '192.168.8.105', 'anita@gmail.com', '2025-08-24 12:36:48', 1),
(353, '::1', 'john.doe@gmail.com', '2025-08-25 02:50:26', 1),
(354, '192.168.8.107', 'registrar@gmail.com', '2025-08-25 02:53:40', 1),
(355, '::1', 'anita@gmail.com', '2025-08-25 02:57:54', 1),
(356, '::1', 'john.doe@gmail.com', '2025-08-25 03:01:56', 1),
(357, '::1', 'anita@gmail.com', '2025-08-25 03:02:55', 1),
(358, '::1', 'john.doe@gmail.com', '2025-08-25 03:04:00', 1),
(359, '192.168.8.107', 'rianne@gmail.com', '2025-08-25 03:08:03', 1),
(361, '192.168.8.110', 'jerry@gmail.com', '2025-08-25 03:17:36', 1),
(362, '192.168.8.105', 'anita@gmail.com', '2025-08-25 03:19:18', 1),
(363, '192.168.8.105', 'jerry@gmail.com', '2025-08-25 03:20:45', 1),
(365, '192.168.8.104', 'john.doe@gmail.com', '2025-08-25 03:23:04', 1),
(366, '192.168.8.107', 'jerry@gmail.com', '2025-08-25 03:27:23', 1),
(367, '192.168.8.107', 'registrar@gmail.com', '2025-08-25 03:38:11', 1),
(368, '192.168.8.104', 'john.doe@gmail.com', '2025-08-25 03:39:42', 1),
(369, '192.168.8.104', 'john.doe@gmail.com', '2025-08-25 03:41:10', 1),
(370, '192.168.8.104', 'john.doe@gmail.com', '2025-08-25 03:42:36', 1),
(371, '192.168.8.104', 'john.doe@gmail.com', '2025-08-25 03:45:53', 1),
(373, '192.168.8.104', 'bob.brown@gmail.com', '2025-08-25 03:47:31', 1),
(375, '192.168.8.104', 'alice.j@gmail.com', '2025-08-25 03:48:20', 1),
(380, '192.168.8.104', 'john.doe@gmail.com', '2025-08-25 03:52:55', 1),
(381, '192.168.8.104', 'bob.brown@gmail.com', '2025-08-25 03:53:08', 1),
(385, '192.168.8.104', 'registrar@gmail.com', '2025-08-25 03:54:14', 1),
(386, '192.168.8.104', 'john.doe@gmail.com', '2025-08-25 03:54:50', 1),
(387, '192.168.8.107', 'jerry@gmail.com', '2025-08-25 03:55:29', 1),
(388, '192.168.8.107', 'rianne@gmail.com', '2025-08-25 03:55:44', 1),
(389, '192.168.8.104', 'registrar@gmail.com', '2025-08-25 03:56:26', 1),
(390, '192.168.8.107', 'rianne@gmail.com', '2025-08-25 03:57:08', 1),
(391, '192.168.8.107', 'rianne@gmail.com', '2025-08-25 03:57:49', 1),
(392, '192.168.8.107', 'jerry@gmail.com', '2025-08-25 03:59:06', 1),
(393, '192.168.8.104', 'rian@gmail.com', '2025-08-25 03:59:24', 1),
(394, '192.168.8.107', 'rian@gmail.com', '2025-08-25 03:59:36', 1),
(395, '192.168.8.104', 'registrar@gmail.com', '2025-08-25 03:59:41', 1),
(396, '192.168.8.107', 'registrar@gmail.com', '2025-08-25 04:00:05', 1),
(397, '192.168.8.104', 'charlie.d@gmail.com', '2025-08-25 04:00:56', 1),
(398, '192.168.8.107', 'rianne@gmail.com', '2025-08-25 04:01:17', 1),
(399, '192.168.8.104', 'jane.smith@gmail.com', '2025-08-25 04:01:48', 1),
(400, '192.168.8.107', 'registrar@gmail.com', '2025-08-25 04:01:50', 1),
(401, '192.168.8.104', 'john.doe@gmail.com', '2025-08-25 04:02:00', 1),
(402, '192.168.8.104', 'registrar@gmail.com', '2025-08-25 04:02:22', 1),
(404, '192.168.8.107', 'char@gmail.com', '2025-08-25 04:02:58', 1),
(405, '192.168.8.104', 'jane.smith@gmail.com', '2025-08-25 04:03:01', 1),
(407, '192.168.8.104', 'jane.smith@gmail.com', '2025-08-25 04:04:19', 1),
(408, '192.168.8.104', 'rian@gmail.com', '2025-08-25 04:06:07', 1),
(409, '192.168.8.107', 'char@gmail.com', '2025-08-25 04:06:18', 1),
(410, '192.168.8.104', 'bob.brown@gmail.com', '2025-08-25 04:06:21', 1),
(411, '192.168.8.107', 'rianne@gmail.com', '2025-08-25 04:06:43', 1),
(412, '192.168.8.104', 'registrar@gmail.com', '2025-08-25 04:08:06', 1),
(413, '192.168.8.104', 'bob.brown@gmail.com', '2025-08-25 04:10:13', 1),
(415, '192.168.8.104', 'jane.smith@gmail.com', '2025-08-25 04:10:34', 1),
(416, '192.168.8.104', 'john.doe@gmail.com', '2025-08-25 04:10:41', 1),
(417, '192.168.8.105', 'anita@gmail.com', '2025-08-25 04:11:06', 0),
(418, '192.168.8.105', 'anita@gmail.com', '2025-08-25 04:11:16', 0),
(419, '192.168.8.107', 'jerry@gmail.com', '2025-08-25 04:12:33', 1),
(420, '192.168.8.104', 'registrar@gmail.com', '2025-08-25 04:13:14', 1),
(421, '192.168.8.110', 'jerry@gmail.com', '2025-08-25 04:13:34', 1),
(422, '192.168.8.107', 'rianne@gmail.com', '2025-08-25 04:14:00', 1),
(423, '192.168.8.107', 'registrar@gmail.com', '2025-08-25 04:30:47', 1),
(424, '192.168.8.104', 'john.doe@gmail.com', '2025-08-25 04:42:25', 1),
(425, '192.168.8.104', 'jerry@gmail.com', '2025-08-25 04:43:05', 1),
(426, '192.168.8.104', 'rianne@gmail.com', '2025-08-25 04:43:55', 1),
(427, '192.168.8.104', 'registrar@gmail.com', '2025-08-25 04:51:01', 1),
(428, '192.168.8.107', 'jerry@gmail.com', '2025-08-25 04:56:06', 1),
(429, '192.168.8.107', 'rianne@gmail.com', '2025-08-25 04:57:30', 1),
(430, '192.168.8.104', 'registrar@gmail.com', '2025-08-25 04:59:03', 1),
(431, '192.168.8.104', 'john.doe@gmail.com', '2025-08-25 05:06:15', 1),
(432, '192.168.8.104', 'registrar@gmail.com', '2025-08-25 05:09:14', 1),
(433, '192.168.8.104', 'registrar@gmail.com', '2025-08-25 05:18:41', 1),
(434, '127.0.0.1', 'john.doe@gmail.com', '2025-08-25 05:20:00', 1),
(435, '::1', 'registrar@gmail.com', '2025-08-25 05:24:22', 1),
(436, '::1', 'john.doe@gmail.com', '2025-08-25 05:24:56', 1),
(437, '127.0.0.1', 'john.doe@gmail.com', '2025-08-25 05:27:00', 1),
(438, '192.168.8.104', 'john.doe@gmail.com', '2025-08-25 05:27:26', 1),
(439, '127.0.0.1', 'john.doe@gmail.com', '2025-08-25 05:30:19', 1),
(440, '192.168.8.107', 'rianne@gmail.com', '2025-08-25 05:30:46', 1),
(441, '::1', 'john.doe@gmail.com', '2025-08-25 05:30:48', 1),
(442, '127.0.0.1', 'john.doe@gmail.com', '2025-08-25 05:33:43', 1),
(443, '::1', 'john.doe@gmail.com', '2025-08-25 05:37:41', 1),
(444, '127.0.0.1', 'john.doe@gmail.com', '2025-08-25 05:40:35', 1),
(445, '::1', 'john.doe@gmail.com', '2025-08-25 05:41:39', 1),
(446, '192.168.8.107', 'rianne@gmail.com', '2025-08-25 05:41:53', 1),
(447, '127.0.0.1', 'john.doe@gmail.com', '2025-08-25 05:44:06', 1),
(449, '192.168.8.107', 'registrar@gmail.com', '2025-08-25 05:45:38', 1),
(450, '::1', 'john.doe@gmail.com', '2025-08-25 05:45:44', 1),
(452, '::1', 'john.doe@gmail.com', '2025-08-25 05:50:25', 1),
(453, '127.0.0.1', 'john.doe@gmail.com', '2025-08-25 05:52:45', 1),
(454, '192.168.8.107', 'john.doe@gmail.com', '2025-08-25 05:56:32', 1),
(455, '127.0.0.1', 'john.doe@gmail.com', '2025-08-25 05:57:24', 1),
(456, '::1', 'john.doe@gmail.com', '2025-08-25 06:01:35', 1),
(457, '127.0.0.1', 'anita@gmail.com', '2025-08-25 06:12:03', 1),
(459, '192.168.8.107', 'anita@gmail.com', '2025-08-25 06:38:50', 1),
(460, '192.168.8.107', 'rianne@gmail.com', '2025-08-25 07:12:48', 1),
(461, '192.168.8.107', 'john.doe@gmail.com', '2025-08-25 07:13:40', 1),
(462, '192.168.8.104', 'john.doe@gmail.com', '2025-08-25 07:18:19', 1),
(463, '::1', 'john.doe@gmail.com', '2025-08-25 07:34:57', 1),
(464, '192.168.8.107', 'john.doe@gmail.com', '2025-08-25 07:44:10', 1),
(465, '192.168.8.107', 'john.doe@gmail.com', '2025-08-25 07:45:59', 1),
(466, '192.168.8.107', 'registrar@gmail.com', '2025-08-25 07:46:09', 1),
(467, '192.168.8.107', 'registrar@gmail.com', '2025-08-25 07:46:24', 1),
(468, '192.168.8.107', 'registrar@gmail.com', '2025-08-25 07:46:37', 1),
(469, '192.168.8.107', 'registrar@gmail.com', '2025-08-25 07:46:52', 1),
(470, '192.168.8.107', 'registrar@gmail.com', '2025-08-25 07:47:03', 1),
(472, '192.168.8.107', 'anita@gmail.com', '2025-08-25 07:47:30', 1),
(473, '192.168.8.107', 'registrar@gmail.com', '2025-08-25 07:48:22', 1),
(474, '192.168.8.107', 'john.doe@gmail.com', '2025-08-25 07:49:26', 1),
(475, '192.168.8.110', 'anita@gmail.com', '2025-08-25 08:00:39', 1),
(476, '192.168.8.107', 'anita@gmail.com', '2025-08-25 08:05:02', 1),
(477, '192.168.8.107', 'rianne@gmail.com', '2025-08-25 08:09:10', 1),
(478, '127.0.0.1', 'john.doe@gmail.com', '2025-08-25 08:10:58', 1);

-- --------------------------------------------------------

--
-- Table structure for table `penalty`
--

CREATE TABLE `penalty` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `type` enum('warning','ban') NOT NULL,
  `reason` text NOT NULL,
  `description` text NOT NULL,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `issued_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penalty`
--

INSERT INTO `penalty` (`id`, `student_id`, `type`, `reason`, `description`, `issued_at`, `expires_at`, `issued_by`) VALUES
(1, 26, 'warning', 'fdsfdsgfsg', '', '2025-08-25 07:36:16', '2025-08-25 07:36:00', 56),
(2, 26, 'warning', 'Repeated Cancellations', 'hghgfhgfh', '2025-08-25 08:28:02', '2025-09-24 00:28:02', NULL),
(3, 26, 'warning', 'Repeated Cancellations', 'hghgfhgfh', '2025-08-25 08:28:04', '2025-09-24 00:28:04', NULL),
(4, 27, 'warning', 'Equipment Damage', 'hghgfhgfhgfdgdfg', '2025-08-25 08:28:10', '2025-09-24 00:28:10', NULL),
(5, 27, 'warning', 'Equipment Damage', 'hghgfhgfhgfdgdfg', '2025-08-25 08:28:11', '2025-09-24 00:28:11', NULL),
(6, 25, 'warning', 'Repeated Cancellations', 'cdcdcdcdcd', '2025-08-25 08:28:14', '2025-09-24 00:28:14', NULL),
(7, 25, 'warning', 'Repeated Cancellations', 'cdcdcdcdcd', '2025-08-25 08:28:27', '2025-09-24 00:28:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `registrar`
--

CREATE TABLE `registrar` (
  `regid` int(11) NOT NULL,
  `Reg_Email` varchar(50) NOT NULL,
  `Reg_Password` varchar(255) NOT NULL,
  `RoleID` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrar`
--

INSERT INTO `registrar` (`regid`, `Reg_Email`, `Reg_Password`, `RoleID`) VALUES
(1, 'registrar@gmail.com', '1234', 1);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `RoleID` int(11) NOT NULL,
  `RoleName` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`RoleID`, `RoleName`) VALUES
(1, 'Registrar'),
(2, 'Department Admin'),
(3, 'Teacher'),
(4, 'Student');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_name` varchar(255) NOT NULL,
  `room_type` varchar(255) NOT NULL,
  `capacity` int(11) NOT NULL,
  `RoomStatus` enum('available','occupied','maintenance') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `building_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_name`, `room_type`, `capacity`, `RoomStatus`, `created_at`, `building_id`) VALUES
(7, 'BA-103', 'Classroom', 45, 'available', '2025-05-22 12:05:20', 2),
(8, 'BA-201', 'Classroom', 60, 'available', '2025-05-22 12:05:20', 2),
(9, 'BA-202', 'Lecture Hall', 40, 'available', '2025-05-22 12:05:20', 2),
(11, 'HM-101', 'Classroom', 35, 'available', '2025-05-22 12:05:20', 3),
(16, 'EA-101', 'Classroom', 45, 'available', '2025-05-22 12:05:20', 4),
(17, 'EA-201', 'Classroom', 40, 'available', '2025-05-22 12:05:20', 4),
(18, 'EA-202', 'Classroom', 50, 'available', '2025-05-22 12:05:20', 4),
(19, 'EA-301', 'Classroom', 35, 'available', '2025-05-22 12:05:20', 4),
(21, 'CJ-1011', 'Lecture Hall', 400, 'available', '2025-05-22 12:05:20', 5),
(24, 'CJ-301', 'Auditorium', 250, 'available', '2025-05-22 12:05:20', 5),
(25, 'CJ-302', 'Classroom', 30, 'available', '2025-05-22 12:05:20', 5),
(73, 'TestRoom 4', 'Classroom', 30, 'available', '2025-08-25 04:49:52', 4),
(74, 'TestRoom 5', 'Classroom', 30, 'available', '2025-08-25 04:49:52', 5),
(78, '33', 'Gymnasium', 500, 'available', '2025-08-25 05:03:20', 1);

-- --------------------------------------------------------

--
-- Table structure for table `room_equipment`
--

CREATE TABLE `room_equipment` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('working','needs_repair','maintenance','missing') DEFAULT 'working'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_equipment`
--

INSERT INTO `room_equipment` (`id`, `room_id`, `equipment_id`, `quantity`, `notes`, `last_updated`, `status`) VALUES
(1, 8, 4, 1, NULL, '2025-08-25 08:02:27', 'working');

-- --------------------------------------------------------

--
-- Table structure for table `room_requests`
--

CREATE TABLE `room_requests` (
  `RequestID` int(11) NOT NULL,
  `StudentID` int(11) DEFAULT NULL,
  `TeacherID` int(11) DEFAULT NULL,
  `RoomID` int(11) NOT NULL,
  `ActivityName` varchar(255) NOT NULL,
  `Purpose` text NOT NULL,
  `RequestDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `ReservationDate` datetime NOT NULL,
  `StartTime` datetime NOT NULL,
  `EndTime` datetime NOT NULL,
  `NumberOfParticipants` int(11) NOT NULL,
  `Status` enum('pending','approved','rejected') DEFAULT 'pending',
  `RejectionReason` text DEFAULT NULL,
  `ApprovedBy` int(11) DEFAULT NULL,
  `ApproverFirstName` varchar(128) DEFAULT NULL,
  `ApproverLastName` varchar(128) DEFAULT NULL,
  `RejectedBy` int(11) DEFAULT NULL,
  `RejecterFirstName` varchar(128) DEFAULT NULL,
  `RejecterLastName` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_requests`
--

INSERT INTO `room_requests` (`RequestID`, `StudentID`, `TeacherID`, `RoomID`, `ActivityName`, `Purpose`, `RequestDate`, `ReservationDate`, `StartTime`, `EndTime`, `NumberOfParticipants`, `Status`, `RejectionReason`, `ApprovedBy`, `ApproverFirstName`, `ApproverLastName`, `RejectedBy`, `RejecterFirstName`, `RejecterLastName`) VALUES
(27, 26, NULL, 7, 'Dance Practice', 'cscscscscscscs', '2025-08-25 07:01:34', '0000-00-00 00:00:00', '2025-08-26 08:00:00', '2025-08-26 13:30:00', 16, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 26, NULL, 21, 'Research Presentation', 'sasasasasasasasa', '2025-08-25 07:04:52', '0000-00-00 00:00:00', '2025-08-26 17:00:00', '2025-08-26 22:30:00', 8, 'rejected', 'Room unavailable due to maintenance', NULL, NULL, NULL, 56, 'John', 'Doe'),
(29, 26, NULL, 16, 'Club Orientation', 'fdfdfdfdfdfdfdfd', '2025-08-25 07:07:25', '0000-00-00 00:00:00', '2025-08-26 15:00:00', '2025-08-26 19:30:00', 44, 'approved', NULL, 56, 'John', 'Doe', NULL, NULL, NULL),
(30, 26, NULL, 21, 'Oath Taking', 'DFDFDFDFDF', '2025-08-25 07:09:10', '0000-00-00 00:00:00', '2025-08-26 14:00:00', '2025-08-26 18:30:00', 400, 'approved', NULL, 56, 'John', 'Doe', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `StudentID` int(11) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Department` varchar(50) NOT NULL,
  `Program` varchar(50) NOT NULL,
  `YearSection` varchar(50) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `AdminID` int(11) NOT NULL,
  `RoleID` int(11) NOT NULL DEFAULT 4,
  `PenaltyExpiresAt` timestamp NULL DEFAULT NULL,
  `PenaltyStatus` enum('none','warning','banned') DEFAULT 'none'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`StudentID`, `FirstName`, `LastName`, `Department`, `Program`, `YearSection`, `Email`, `Password`, `AdminID`, `RoleID`, `PenaltyExpiresAt`, `PenaltyStatus`) VALUES
(24, 'Jean', 'Abay', 'Education and Arts', 'BS CRIM', 'CRIM A1', 'jean@gmail.com', '$2y$10$oFqHs8thw.Z4N1r9416gE.naOTiUAbh9m5580JhXeZi3RaHtXNfWS', 54, 4, NULL, 'none'),
(25, 'Aya', 'Ayaa', 'Education and Arts', 'BSE-MATH', 'BSE-MATH 4-7', 'aya@gmail.com', '$2y$10$K/.PFXIIoGxesbk9DoSo3u7Zx8O.Q9bTq4lQY4QD1ddPh1q/inDXa', 54, 4, NULL, 'warning'),
(26, 'Anita', 'Oira', 'Business Administration', 'BSBA-FM', 'BSBA-FM 1-9', 'anita@gmail.com', '$2y$10$5cKBZ7iOxmtMJi5xpJNzaecV804ew4e9scnjHnMZhKwq4LeouZMfm', 56, 4, '2025-08-25 07:36:00', 'warning'),
(27, 'Daron', 'Mangaoang', 'Business Administration', 'BSBA-FM', 'BSBA-FM 4-5', 'daron@gmail.c', '$2y$10$SnbsU0lcU7fC2MLrAy5Nfe29IJN1Ie0bMYb36kKT1raztpz7b5cyK', 56, 4, NULL, 'warning');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('room_status_last_check', '2025-08-25 16:38:34', '2025-08-25 08:38:34');

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `TeacherID` int(11) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Department` varchar(50) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `AdminID` int(11) NOT NULL,
  `RoleID` int(11) NOT NULL DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`TeacherID`, `FirstName`, `LastName`, `Department`, `Email`, `Password`, `AdminID`, `RoleID`) VALUES
(19, 'Jerry', 'Castrudes', 'Business Administration', 'jerry@gmail.com', '$2y$10$vSjiA1s43hLNZhu3xEbpwOPGknJ0Xxc6kt78u5jiFr2u2gx35F82i', 56, 3),
(20, 'Ashley', 'Perez', 'Business Administration', 'ash@gmail.co', '$2y$10$uTa2p5pSG6ONMQeddDvsF.ZgWeq53HlbOL7V1DdNN2VzmmbrUeyCu', 56, 3),
(21, 'Daron', 'Daron', 'Business Administration', 'dar@g.com', '$2y$10$JMNHnoKaPzz0EDGW1EspKOthOkNKV9ilTU9g01l61cmuh2kryk/Hi', 56, 3),
(22, 'juls', 'nava', 'Business Administration', 'juls@gail.c', '$2y$10$P7Yagfj638JDhJk5OGQU8OxlK7YLv53JhHQY.80vLgRWrpiiVJTM6', 56, 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `buildings`
--
ALTER TABLE `buildings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dept_admin`
--
ALTER TABLE `dept_admin`
  ADD PRIMARY KEY (`AdminID`),
  ADD KEY `idx_deptadmin_email` (`Email`),
  ADD KEY `idx_deptadmin_department` (`Department`),
  ADD KEY `idx_deptadmin_role` (`RoleID`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `equipment_audit`
--
ALTER TABLE `equipment_audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_id` (`equipment_id`);

--
-- Indexes for table `equipment_issues`
--
ALTER TABLE `equipment_issues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `equipment_id` (`equipment_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip_time` (`ip_address`,`attempt_time`),
  ADD KEY `idx_cleanup` (`attempt_time`);

--
-- Indexes for table `penalty`
--
ALTER TABLE `penalty`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `issued_by` (`issued_by`);

--
-- Indexes for table `registrar`
--
ALTER TABLE `registrar`
  ADD PRIMARY KEY (`regid`),
  ADD KEY `idx_registrar_email` (`Reg_Email`),
  ADD KEY `idx_registrar_role` (`RoleID`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`RoleID`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `building_id` (`building_id`);

--
-- Indexes for table `room_equipment`
--
ALTER TABLE `room_equipment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `equipment_id` (`equipment_id`);

--
-- Indexes for table `room_requests`
--
ALTER TABLE `room_requests`
  ADD PRIMARY KEY (`RequestID`),
  ADD KEY `StudentID` (`StudentID`),
  ADD KEY `TeacherID` (`TeacherID`),
  ADD KEY `RoomID` (`RoomID`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`StudentID`),
  ADD KEY `AdminID` (`AdminID`),
  ADD KEY `RoleID` (`RoleID`),
  ADD KEY `idx_student_department` (`Department`),
  ADD KEY `idx_student_email` (`Email`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`TeacherID`),
  ADD KEY `AdminID` (`AdminID`),
  ADD KEY `RoleID` (`RoleID`),
  ADD KEY `idx_teacher_department` (`Department`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `buildings`
--
ALTER TABLE `buildings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `dept_admin`
--
ALTER TABLE `dept_admin`
  MODIFY `AdminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `equipment_audit`
--
ALTER TABLE `equipment_audit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `equipment_issues`
--
ALTER TABLE `equipment_issues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=479;

--
-- AUTO_INCREMENT for table `penalty`
--
ALTER TABLE `penalty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `registrar`
--
ALTER TABLE `registrar`
  MODIFY `regid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `RoleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `room_equipment`
--
ALTER TABLE `room_equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `room_requests`
--
ALTER TABLE `room_requests`
  MODIFY `RequestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `StudentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `TeacherID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dept_admin`
--
ALTER TABLE `dept_admin`
  ADD CONSTRAINT `dept_admin_ibfk_1` FOREIGN KEY (`RoleID`) REFERENCES `roles` (`RoleID`);

--
-- Constraints for table `equipment_audit`
--
ALTER TABLE `equipment_audit`
  ADD CONSTRAINT `equipment_audit_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`);

--
-- Constraints for table `equipment_issues`
--
ALTER TABLE `equipment_issues`
  ADD CONSTRAINT `equipment_issues_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`StudentID`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_issues_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`TeacherID`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_issues_ibfk_3` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`);

--
-- Constraints for table `penalty`
--
ALTER TABLE `penalty`
  ADD CONSTRAINT `penalty_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`StudentID`) ON DELETE CASCADE,
  ADD CONSTRAINT `penalty_ibfk_2` FOREIGN KEY (`issued_by`) REFERENCES `dept_admin` (`AdminID`) ON DELETE SET NULL;

--
-- Constraints for table `registrar`
--
ALTER TABLE `registrar`
  ADD CONSTRAINT `registrar_ibfk_1` FOREIGN KEY (`RoleID`) REFERENCES `roles` (`RoleID`);

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`);

--
-- Constraints for table `room_equipment`
--
ALTER TABLE `room_equipment`
  ADD CONSTRAINT `room_equipment_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_equipment_ibfk_2` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`);

--
-- Constraints for table `room_requests`
--
ALTER TABLE `room_requests`
  ADD CONSTRAINT `room_requests_ibfk_1` FOREIGN KEY (`StudentID`) REFERENCES `student` (`StudentID`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_requests_ibfk_2` FOREIGN KEY (`TeacherID`) REFERENCES `teacher` (`TeacherID`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_requests_ibfk_3` FOREIGN KEY (`RoomID`) REFERENCES `rooms` (`id`);

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`AdminID`) REFERENCES `dept_admin` (`AdminID`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_ibfk_2` FOREIGN KEY (`RoleID`) REFERENCES `roles` (`RoleID`);

--
-- Constraints for table `teacher`
--
ALTER TABLE `teacher`
  ADD CONSTRAINT `teacher_ibfk_1` FOREIGN KEY (`AdminID`) REFERENCES `dept_admin` (`AdminID`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_ibfk_2` FOREIGN KEY (`RoleID`) REFERENCES `roles` (`RoleID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
