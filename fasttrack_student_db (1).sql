-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 11, 2026 at 06:57 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fasttrack_student_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_years`
--

CREATE TABLE `academic_years` (
  `id` int(11) NOT NULL,
  `year_name` varchar(20) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`id`, `year_name`, `status`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(5, '2025-2026', 'Active', '0000-00-00', '0000-00-00', '2025-12-24 19:00:08', '2025-12-25 13:29:12'),
(6, '2024-2025', 'Active', '0000-00-00', '0000-00-00', '2025-12-29 05:50:57', '2025-12-31 06:08:15');

-- --------------------------------------------------------

--
-- Table structure for table `assessments`
--

CREATE TABLE `assessments` (
  `id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessments`
--

INSERT INTO `assessments` (`id`, `type`, `weight`, `status`, `created_at`, `updated_at`) VALUES
(7, 'Individual Class Assessment', 15.00, 'Active', '2025-12-22 15:39:38', '2025-12-24 18:41:58'),
(8, 'Mid-Sem', 15.00, 'Active', '2025-12-22 15:40:21', '2025-12-24 18:41:55'),
(9, 'Practical or Portfolio', 10.00, 'Active', '2025-12-22 15:41:01', '2025-12-24 18:41:53'),
(10, 'Group Project', 20.00, 'Active', '2025-12-22 15:41:57', '2025-12-24 18:41:50'),
(11, 'Supervised Examination', 40.00, 'Active', '2025-12-22 15:42:21', '2025-12-24 18:41:45');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_results`
--

CREATE TABLE `assessment_results` (
  `id` int(10) NOT NULL,
  `student_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `assessment_id` int(10) NOT NULL,
  `semester` varchar(20) NOT NULL,
  `year_group` varchar(10) NOT NULL,
  `academic_year` varchar(10) NOT NULL,
  `overall_score` int(10) NOT NULL,
  `score` decimal(10,0) NOT NULL,
  `weighted_score` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `class_name` varchar(50) NOT NULL,
  `learning_area` varchar(25) NOT NULL,
  `year_group` int(5) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `class_name`, `learning_area`, `year_group`, `created_at`) VALUES
(13, 'Sci 1-25', 'Science', 2025, '2025-12-20 23:38:44'),
(15, 'ARTS 2-24', 'General Arts', 2024, '2025-12-30 17:30:55'),
(16, 'ARTS 4-25', 'General Arts', 2025, '2026-01-08 16:25:44');

-- --------------------------------------------------------

--
-- Table structure for table `class_subjects`
--

CREATE TABLE `class_subjects` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_subjects`
--

INSERT INTO `class_subjects` (`id`, `class_id`, `subject_id`) VALUES
(50, 15, 7),
(51, 15, 10),
(52, 16, 7),
(53, 16, 10),
(54, 16, 11),
(55, 16, 9),
(56, 16, 8);

-- --------------------------------------------------------

--
-- Table structure for table `fee_categories`
--

CREATE TABLE `fee_categories` (
  `id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `category_type` enum('Goods','Service') NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_frequency` varchar(20) DEFAULT 'NA',
  `year_group` varchar(50) DEFAULT 'All',
  `learning_area_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fee_categories`
--

INSERT INTO `fee_categories` (`id`, `academic_year_id`, `category_name`, `category_type`, `status`, `created_at`, `updated_at`, `payment_frequency`, `year_group`, `learning_area_id`, `total_amount`) VALUES
(18, 5, 'Feeding', 'Service', 'Active', '2026-01-01 05:58:05', '2026-01-01 06:59:34', 'Per Sem', '2024', 11, 1200.00),
(19, 5, 'Feeding', 'Service', 'Active', '2026-01-01 06:07:51', '2026-01-01 06:07:51', 'Per Sem', '2025', 11, 1700.00),
(20, 5, 'School Uniform', 'Goods', 'Active', '2026-01-01 07:26:04', '2026-01-01 07:26:04', 'NA', '2025', 11, 200.00),
(21, 5, 'School Uniform', 'Goods', 'Active', '2026-01-01 08:18:27', '2026-01-01 08:18:27', 'NA', '2024', 11, 200.00),
(22, 5, 'School Fees', 'Service', 'Active', '2026-01-02 05:38:17', '2026-01-02 05:38:17', 'Per Sem', '2025', 1, 3000.00),
(23, 6, 'FEES', 'Service', 'Active', '2026-01-08 15:57:17', '2026-01-08 16:15:23', 'Per Sem', '2024', 7, 7563.00);

-- --------------------------------------------------------

--
-- Table structure for table `fee_payments`
--

CREATE TABLE `fee_payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `fee_category_id` int(11) NOT NULL,
  `fee_item_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `receipt_no` varchar(100) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `semester` varchar(50) NOT NULL,
  `payment_date` date NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `slip_number` varchar(100) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `outstanding_balance` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fee_payments`
--

INSERT INTO `fee_payments` (`id`, `student_id`, `class_id`, `fee_category_id`, `fee_item_id`, `quantity`, `amount_paid`, `receipt_no`, `academic_year_id`, `semester`, `payment_date`, `bank_name`, `slip_number`, `remarks`, `outstanding_balance`, `created_at`) VALUES
(30, 51, 13, 16, 12, 1, 1500.00, '899647', 5, 'Semester 1', '2025-12-30', 'GCB', '899647', '', 0.00, '2025-12-31 06:23:53'),
(31, 46, 13, 16, 12, 1, 500.00, '456789654', 5, 'Semester 1', '2025-12-24', 'GCB', '456789654', '', 1000.00, '2025-12-31 06:30:13'),
(33, 51, 13, 19, 16, 1, 450.00, '4567899', 5, 'Semester 1', '2026-01-14', 'GCB', '4567899', '', 1250.00, '2026-01-01 06:09:18'),
(43, 59, 15, 18, 15, 1, 800.00, '456789', 5, 'Semester 1', '2026-01-14', 'Fidelity', '456789', '', 400.00, '2026-01-03 06:53:54'),
(44, 74, 15, 18, 15, 1, 300.00, '456789', 5, 'Semester 1', '2026-01-21', 'First Atlantic', '456789', '', 900.00, '2026-01-03 08:16:11'),
(45, 74, 15, 18, 15, 1, 300.00, '456789', 5, 'Semester 1', '2026-01-21', 'First Atlantic', '456789', '', 600.00, '2026-01-03 08:18:38'),
(46, 59, 15, 18, 15, 1, 100.00, '788333', 5, 'Semester 1', '2026-01-21', 'Fidelity', '788333', '', 300.00, '2026-01-03 08:19:15'),
(47, 59, 15, 18, 15, 1, 100.00, '4567892134', 5, 'Semester 1', '2026-01-13', 'Fidelity', '4567892134', '', 200.00, '2026-01-03 08:23:22'),
(53, 52, 15, 21, 18, 1, 200.00, '456789', 5, 'Semester 1', '2026-01-21', 'Ecobank', '456789', '', 0.00, '2026-01-07 07:02:33'),
(54, 75, 15, 18, 15, 1, 1000.00, '456789', 5, 'Semester 1', '2026-01-20', 'OmniBSIC', '456789', '', 200.00, '2026-01-07 07:07:38'),
(55, 52, 15, 18, 15, 1, 800.00, '456789', 5, 'Semester 1', '2026-01-27', 'First Atlantic', '456789', '', 400.00, '2026-01-07 07:14:34'),
(56, 52, 15, 18, 15, 1, 400.00, '456789', 5, 'Semester 1', '2026-01-13', 'GTBank', '456789', '', 0.00, '2026-01-07 07:17:57'),
(57, 51, 13, 19, 16, 1, 1250.00, 'lkkk', 5, 'Semester 1', '2026-02-06', 'FNB', 'lkkk', '', 0.00, '2026-01-07 19:53:31'),
(58, 51, 13, 22, 20, 1, 500.00, 'lkkk', 5, 'Semester 1', '2026-02-06', 'FNB', 'lkkk', '', 0.00, '2026-01-07 19:53:31'),
(59, 74, 15, 18, 15, 1, 600.00, 'YYY', 5, 'Semester 1', '2026-01-12', 'Okomfo Anokye Rural Bank', 'YYY', '', 0.00, '2026-01-08 15:40:28'),
(60, 74, 15, 21, 18, 1, 200.00, 'YYY', 5, 'Semester 1', '2026-01-12', 'Okomfo Anokye Rural Bank', 'YYY', '', 0.00, '2026-01-08 15:40:28'),
(61, 52, 15, 23, 22, 1, 200.00, 'lkkk', 6, 'Semester 1', '2026-01-08', 'CBG', 'lkkk', '', 0.00, '2026-01-08 16:16:41'),
(62, 52, 15, 23, 23, 1, 500.00, 'lkkk', 6, 'Semester 1', '2026-01-08', 'CBG', 'lkkk', '', 0.00, '2026-01-08 16:16:41');

-- --------------------------------------------------------

--
-- Table structure for table `guardians`
--

CREATE TABLE `guardians` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `contact` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guardians`
--

INSERT INTO `guardians` (`id`, `name`, `occupation`, `relationship`, `contact`, `created_at`) VALUES
(14, 'AMANKWAA SAMUEL', 'Farming', 'Father', '0244345467', '2025-12-10 05:53:53'),
(15, 'AMANKWAA SAMUEL', 'Farming', 'Father', '0244345467', '2025-12-10 05:56:31'),
(16, 'AMANKWAA SAMUEL', 'Farming', 'Father', '0244345467', '2025-12-10 06:00:35'),
(17, 'AMANKWAA SAMUEL', 'Farming', 'Father', '0244345467', '2025-12-10 06:04:19'),
(18, 'AMANKWAA SAMUEL', 'Farming', 'Father', '0244345467', '2025-12-10 06:07:11'),
(19, 'AMANG GLADYS', 'Farming', 'Father', '023343567', '2025-12-10 06:09:04'),
(20, '', '', '', '', '2025-12-10 06:23:12'),
(21, '', '', '', '', '2025-12-10 06:23:34'),
(22, 'COMFORT OWUSU', '', '', '0244345467', '2025-12-10 06:28:49'),
(23, 'ANTWIWAA LAWRENCIA', 'Farming', 'mother', '0244345467', '2025-12-10 06:34:45'),
(24, 'AKUA MANU ARTHUR', 'Farming', 'mother', '0543232345', '2025-12-10 15:23:11'),
(25, 'AMANKWAA SAMUEL', 'Farming', 'Father', '0244345467', '2025-12-10 18:13:56'),
(26, 'ADAMU MUSAH', 'Farming', 'Father', '0543232345', '2025-12-10 19:36:12'),
(27, 'AMANKWAA SAMUEL', 'Farming', 'Father', '0244345467', '2025-12-13 05:42:51'),
(28, 'AMANKWAA SAMUEL', '', '', '0543232345', '2025-12-13 06:51:56'),
(29, 'AKUA MANU ARTHUR', '', 'Father', '0543232345', '2025-12-13 06:55:09'),
(30, 'COMFORT OWUSU', '', 'Father', '0543232345', '2025-12-13 22:14:11'),
(31, 'ANTWIWAA LAWRENCIA', '', '', '0244345467', '2025-12-13 22:25:52'),
(32, 'COMFORT OWUSU', '', '', '0543232345', '2025-12-15 20:30:50'),
(33, 'ANTWIWAA LAWRENCIA', '', '', '0244345467', '2025-12-20 23:40:26'),
(34, 'COMFORT OWUSU', '', '', '0543232345', '2025-12-23 23:32:45'),
(35, 'Jane Doe', NULL, NULL, '244987654', '2025-12-24 20:50:19'),
(36, 'Jane Doe', NULL, NULL, '244987654', '2025-12-24 21:07:42'),
(37, 'Jane Doe', NULL, NULL, '244987654', '2025-12-24 21:12:32'),
(38, 'Jane Doe', NULL, NULL, '244987654', '2025-12-24 21:13:06'),
(39, 'Jane Doe', '', '', '244987654', '2025-12-24 21:17:23'),
(40, 'ANTWIWAA LAWRENCIA', '', '', '0244345467', '2025-12-30 17:38:45'),
(41, 'Jane Doe', NULL, NULL, '0541574526', '2026-01-03 06:44:35'),
(42, 'Kuma Portia', NULL, NULL, '0541574527', '2026-01-03 06:44:35'),
(43, 'Jane Doe', NULL, NULL, '0541574528', '2026-01-03 06:44:35'),
(44, 'Kuma Portia', NULL, NULL, '0541574529', '2026-01-03 06:44:35'),
(45, 'Jane Doe', NULL, NULL, '0541574530', '2026-01-03 06:44:35'),
(46, 'Kuma Portia', NULL, NULL, '0541574531', '2026-01-03 06:44:35'),
(47, 'Alfred Sarpong', NULL, NULL, '0541574532', '2026-01-03 06:44:35'),
(48, 'Kuma Portia', NULL, NULL, '0541574533', '2026-01-03 06:44:35'),
(49, 'Jane Doe', NULL, NULL, '0541574534', '2026-01-03 06:44:35'),
(50, 'Kuma Princess', NULL, NULL, '0541574535', '2026-01-03 06:44:35'),
(51, 'John Doe', NULL, NULL, '0541574536', '2026-01-03 06:44:35'),
(52, 'Kuma Portia', NULL, NULL, '0541574537', '2026-01-03 06:44:35'),
(53, 'Jane Doe', NULL, NULL, '0541574538', '2026-01-03 06:44:35'),
(54, 'Kuma Portia', NULL, NULL, '0541574539', '2026-01-03 06:44:35'),
(55, 'Jane Doe', NULL, NULL, '0541574540', '2026-01-03 06:44:35'),
(56, 'Kuma Portia', NULL, NULL, '0541574541', '2026-01-03 06:44:35'),
(57, 'Jane Doe', NULL, NULL, '0541574542', '2026-01-03 06:44:35'),
(58, 'Kuma Portia', NULL, NULL, '0541574543', '2026-01-03 06:44:36'),
(59, 'Jane Doe', NULL, NULL, '0541574544', '2026-01-03 06:44:36'),
(60, 'Kuma Portia', NULL, NULL, '0541574545', '2026-01-03 06:44:36'),
(61, 'Jane Doe', NULL, NULL, '0541574546', '2026-01-03 06:44:36'),
(62, 'Kuma Portia', NULL, NULL, '0541574547', '2026-01-03 06:44:36'),
(63, 'Jane Doe', NULL, NULL, '0541574548', '2026-01-03 06:44:36');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `admission_number` varchar(50) NOT NULL,
  `year_of_admission` int(5) DEFAULT NULL,
  `level` varchar(10) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `surname` varchar(50) NOT NULL,
  `hometown` varchar(100) DEFAULT NULL,
  `student_contact` varchar(20) DEFAULT NULL,
  `dob` date NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `nationality` varchar(50) NOT NULL,
  `languages_spoken` varchar(100) DEFAULT NULL,
  `religion` varchar(50) NOT NULL,
  `last_school` varchar(100) DEFAULT NULL,
  `last_school_position` varchar(50) DEFAULT NULL,
  `bece_scores` int(11) DEFAULT NULL,
  `residential_status` enum('Boarding','Day') NOT NULL,
  `hall_of_residence` varchar(100) DEFAULT NULL,
  `nhis_no` varchar(50) DEFAULT NULL,
  `interests` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `guardian_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `admission_number`, `year_of_admission`, `level`, `first_name`, `middle_name`, `surname`, `hometown`, `student_contact`, `dob`, `gender`, `nationality`, `languages_spoken`, `religion`, `last_school`, `last_school_position`, `bece_scores`, `residential_status`, `hall_of_residence`, `nhis_no`, `interests`, `photo`, `class_id`, `guardian_id`, `created_at`) VALUES
(46, 'FTC/2025/0002', 2025, 'SHS-1', 'Kofi', '', 'Awudu', '', '', '1991-09-04', 'Male', 'GHANAIAN', '', 'Islam', '', '', 78, '', '', '', '', NULL, 13, 34, '2025-12-23 23:32:45'),
(51, 'FTC/2025/0005', 2025, 'SHS-1', 'Freeman', '', 'Ansah', '', '244123456', '2025-12-08', 'Male', 'Ghanaian', '', 'Christianity', '', '', 0, 'Day', '', NULL, NULL, '1766611153_IMG_0554.png', 13, 39, '2025-12-24 21:17:23'),
(52, 'FTC/2024/0001', 2025, 'SHS-2', 'Yaw', '', 'Dabo', '', '', '2025-12-14', 'Male', 'Ghanaian', '', 'Islam', '', '', 0, 'Boarding', '', '', '', NULL, 15, 40, '2025-12-30 17:38:45'),
(53, 'FTC/2024/0002', 2026, 'SHS-1', 'John', NULL, 'Doe', NULL, '054157456', '0000-00-00', 'Male', 'Ghanaian', NULL, 'Christianity', NULL, NULL, NULL, 'Day', NULL, NULL, NULL, NULL, 15, 41, '2026-01-03 06:44:35'),
(54, 'FTC/2024/0003', 2026, 'SHS-1', 'PATRICIA', NULL, 'ABANKWAH', NULL, '054157456', '2008-08-08', 'Female', 'Ghanaian', NULL, 'Christianity', NULL, NULL, NULL, 'Day', NULL, NULL, NULL, NULL, 15, 42, '2026-01-03 06:44:35'),
(58, 'FTC/2024/0007', 2026, 'SHS-1', 'KINGSFORD', NULL, 'ADDAE', NULL, '054157456', '2010-06-04', 'Female', 'Ghanaian', NULL, 'Christianity', NULL, NULL, NULL, 'Day', NULL, NULL, NULL, NULL, 15, 46, '2026-01-03 06:44:35'),
(59, 'FTC/2024/0008', 2026, 'SHS-1', 'AKWASI', NULL, 'ADDAI', NULL, '054157456', '2010-01-02', 'Male', 'Ghanaian', NULL, 'Christianity', NULL, NULL, NULL, 'Day', NULL, NULL, NULL, NULL, 15, 47, '2026-01-03 06:44:35'),
(60, 'FTC/2024/0009', 2026, 'SHS-1', 'ELIZABETH', NULL, 'ADDAI', NULL, '054157456', '2007-09-06', 'Male', 'Ghanaian', NULL, 'Christianity', NULL, NULL, NULL, 'Day', NULL, NULL, NULL, NULL, 15, 48, '2026-01-03 06:44:35'),
(61, 'FTC/2024/0010', 2026, 'SHS-1', 'CHRISTABEL', NULL, 'ADDO', NULL, '054157456', '2009-04-03', 'Male', 'Ghanaian', NULL, 'Christianity', NULL, NULL, NULL, 'Day', NULL, NULL, NULL, NULL, 15, 49, '2026-01-03 06:44:35'),
(63, 'FTC/2024/0012', 2026, 'SHS-1', 'MONALISA', NULL, 'ADUSEI', NULL, '054157456', '2010-01-03', 'Male', 'Ghanaian', NULL, 'Christianity', NULL, NULL, NULL, 'Day', NULL, NULL, NULL, NULL, 15, 51, '2026-01-03 06:44:35'),
(65, 'FTC/2024/0014', 2026, 'SHS-1', 'GEORGINA', NULL, 'AGYEMANG', NULL, '054157456', '2009-11-12', 'Male', 'Ghanaian', NULL, 'Christianity', NULL, NULL, NULL, 'Day', NULL, NULL, NULL, NULL, 15, 53, '2026-01-03 06:44:35'),
(66, 'FTC/2024/0015', 2026, 'SHS-1', 'PAMELA', NULL, 'AKYEMPEM', NULL, '054157456', '2010-05-05', 'Female', 'Ghanaian', NULL, 'Christianity', NULL, NULL, NULL, 'Day', NULL, NULL, NULL, NULL, 15, 54, '2026-01-03 06:44:35'),
(67, 'FTC/2024/0016', 2026, 'SHS-1', 'EVELYN', NULL, 'HALIDU', NULL, '054157456', '2010-06-26', 'Male', 'Ghanaian', NULL, 'Christianity', NULL, NULL, NULL, 'Day', NULL, NULL, NULL, NULL, 15, 55, '2026-01-03 06:44:35'),
(68, 'FTC/2024/0017', 2026, 'SHS-1', 'WILLIAMS', NULL, 'AMAKYE', NULL, '054157456', '2009-02-23', 'Male', 'Ghanaian', NULL, 'Christianity', NULL, NULL, NULL, 'Boarding', NULL, NULL, NULL, NULL, 15, 56, '2026-01-03 06:44:35'),
(69, 'FTC/2024/0018', 2026, 'SHS-1', 'AUGUSTINE', NULL, 'AMANKWAH', NULL, '054157456', '2009-03-20', 'Female', 'Ghanaian', NULL, 'Christianity', NULL, NULL, NULL, 'Boarding', NULL, NULL, NULL, NULL, 15, 57, '2026-01-03 06:44:36'),
(70, 'FTC/2024/0019', 2026, 'SHS-1', 'PRISCILLA', NULL, 'ANIM', NULL, '054157456', '2007-08-19', 'Female', 'Ghanaian', NULL, 'Christianity', NULL, NULL, NULL, 'Boarding', NULL, NULL, NULL, NULL, 15, 58, '2026-01-03 06:44:36'),
(71, 'FTC/2024/0020', 2026, 'SHS-1', 'BRIGHT', NULL, 'ANTWI', NULL, '054157456', '1900-01-01', 'Male', 'Ghanaian', NULL, 'Christianity', NULL, NULL, NULL, 'Boarding', NULL, NULL, NULL, NULL, 15, 59, '2026-01-03 06:44:36'),
(73, 'FTC/2024/0022', 2026, 'SHS-1', 'BRIGHT', NULL, 'APPIAH', NULL, '054157456', '2008-08-22', 'Female', 'Ghanaian', NULL, 'Christianity', NULL, NULL, NULL, 'Boarding', NULL, NULL, NULL, NULL, 15, 61, '2026-01-03 06:44:36'),
(74, 'FTC/2024/0023', 2026, 'SHS-1', 'ALBERTA', NULL, 'ARMAH', NULL, '054157456', '2010-03-16', 'Male', 'Ghanaian', NULL, 'Christianity', NULL, NULL, NULL, 'Boarding', NULL, NULL, NULL, NULL, 15, 62, '2026-01-03 06:44:36'),
(75, 'FTC/2024/0024', 2026, 'SHS-1', 'ISAAC', NULL, 'ASARE', NULL, '054157456', '2011-10-22', 'Female', 'Ghanaian', NULL, 'Christianity', NULL, NULL, NULL, 'Boarding', NULL, NULL, NULL, NULL, 15, 63, '2026-01-03 06:44:36');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(10) NOT NULL,
  `subject_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_name`) VALUES
(7, 'Biology'),
(8, 'Physics'),
(9, 'ICT(Elective)'),
(10, 'Chemistry'),
(11, 'Computing');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `staff_id` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `other_names` varchar(150) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `staff_type` enum('Teaching','Non-Teaching') NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `qualification` varchar(150) DEFAULT NULL,
  `employment_date` date DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `status` enum('Active','Inactive','Suspended') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `staff_id`, `first_name`, `surname`, `other_names`, `dob`, `gender`, `staff_type`, `email`, `phone`, `nationality`, `religion`, `address`, `qualification`, `employment_date`, `photo`, `password`, `status`, `created_at`, `updated_at`) VALUES
(6, 'FTC/STF/6763', 'BRIDGET', 'ANANE', '', '2026-01-12', 'Female', 'Teaching', 'anane2020@GMAIL.COM', '0541574526', '', '', 'OKOMFO ANOKYE SHS, AGONA-ASHANTI, GHANA.', '', '0000-00-00', NULL, '$2y$10$gqz73QoW7DHeUGvfB67/3.7x3PVaSZYC.4Ke4EjF1T0ZHD9lSX5iG', 'Active', '2026-01-07 00:42:01', '2026-01-07 00:42:01');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_subjects`
--

CREATE TABLE `teacher_subjects` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `assigned_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `other_names` varchar(150) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `role` enum('Administrator','Accountant','Super_Admin','Store') NOT NULL,
  `password` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `surname`, `other_names`, `email`, `phone`, `role`, `password`, `photo`, `status`, `created_at`, `updated_at`) VALUES
(17, 'ERIC', 'ABABIO', '', 'anane2020@gmail.com', '0541574528', 'Super_Admin', '$2y$10$Ciz0W.tqvTjnbFdo0d0GgufkOgV2slZKWmmiGh2DPbGDav.mCz/OW', 'user_1767638406.png', 'active', '2026-01-05 18:40:06', '2026-01-05 18:40:06'),
(18, 'Sam', 'Okyere', '', 'qu35t1991@gmail.com', '0541574567', 'Accountant', '$2y$10$J34xVDs99ZxGJX9Cc7n9Yel6XLGBekuPMl82v.8SDMMHcbdIdlo.a', NULL, 'active', '2026-01-05 20:43:20', '2026-01-05 20:44:35'),
(19, 'ANGELA', 'AMOAH', '', 'rimsalaid@gmail.com', '0541574526', 'Administrator', '$2y$10$fM.jiba5rWR4eWKYh//ce.B8qFyrDDXSzbgN1NjfrS7siaxJg49SC', NULL, 'active', '2026-01-05 20:49:33', '2026-01-05 20:50:25'),
(21, 'MUSAH', 'ABDULAI', '', 'Musaabdulai89@gmail.com', '0246420258', 'Accountant', '$2y$10$bYU30skemC9HfjgMufDFu.RQI2x8s4hdNPgNkPIzKr2UGvEEZstSi', NULL, 'active', '2026-01-08 15:26:55', '2026-01-08 15:26:55'),
(22, 'KODUA', 'AGYEMANG', '', 'KODUAAGYEMANG@GMAIL.COM', '0243743458', 'Administrator', '$2y$10$SnrxwjQbRBEqpS2l/ABDi.LGXq./pBOzczLaP10Uwvkd22rUxYItG', NULL, 'active', '2026-01-08 15:28:45', '2026-01-08 15:28:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_years`
--
ALTER TABLE `academic_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `year_name` (`year_name`);

--
-- Indexes for table `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assessment_results`
--
ALTER TABLE `assessment_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stud_idFK` (`student_id`),
  ADD KEY `classFK` (`class_id`),
  ADD KEY `subject_idFK` (`subject_id`),
  ADD KEY `teacher_idFK` (`teacher_id`),
  ADD KEY `ass_fk` (`assessment_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `fee_categories`
--
ALTER TABLE `fee_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_learning_area` (`learning_area_id`),
  ADD KEY `academic_year_fk` (`academic_year_id`);

--
-- Indexes for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `academic_year_id` (`academic_year_id`),
  ADD KEY `fee_payments_ibfk_3` (`fee_category_id`),
  ADD KEY `idx_fee_item_id` (`fee_item_id`);

--
-- Indexes for table `guardians`
--
ALTER TABLE `guardians`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `guardian_id` (`guardian_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_id` (`staff_id`),
  ADD KEY `idx_staff_type` (`staff_type`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `teacher_subjects`
--
ALTER TABLE `teacher_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_assignment` (`teacher_id`,`subject_id`,`class_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD UNIQUE KEY `unique_phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_years`
--
ALTER TABLE `academic_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `assessments`
--
ALTER TABLE `assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `assessment_results`
--
ALTER TABLE `assessment_results`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `class_subjects`
--
ALTER TABLE `class_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `fee_categories`
--
ALTER TABLE `fee_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `fee_payments`
--
ALTER TABLE `fee_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `guardians`
--
ALTER TABLE `guardians`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `teacher_subjects`
--
ALTER TABLE `teacher_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assessment_results`
--
ALTER TABLE `assessment_results`
  ADD CONSTRAINT `ass_fk` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `classFK` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `subject_idFK` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `teacher_idFK` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD CONSTRAINT `class_subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_categories`
--
ALTER TABLE `fee_categories`
  ADD CONSTRAINT `academic_year_fk` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_learning_area` FOREIGN KEY (`learning_area_id`) REFERENCES `learning_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD CONSTRAINT `fee_payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_payments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_payments_ibfk_4` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`guardian_id`) REFERENCES `guardians` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `teacher_subjects`
--
ALTER TABLE `teacher_subjects`
  ADD CONSTRAINT `teach_idFK` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
