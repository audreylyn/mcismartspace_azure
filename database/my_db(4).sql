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
