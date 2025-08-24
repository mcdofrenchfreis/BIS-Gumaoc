-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 24, 2025 at 02:54 PM
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
-- Database: `gumaoc_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` varchar(100) DEFAULT 'system',
  `action_type` varchar(50) NOT NULL,
  `target_type` varchar(50) NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `admin_id`, `action_type`, `target_type`, `target_id`, `description`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 'admin', 'form_view', 'resident_registration', 2, 'Viewed resident registration form ID #2', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-19 13:07:55'),
(2, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-19 13:08:03'),
(3, 'admin', 'print_action', 'certificate_request', 5, 'Printed residency_certificate for certificate_request ID #5', '{\"print_type\":\"residency_certificate\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-19 13:08:04'),
(4, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed dashboard page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-19 13:16:36'),
(5, 'admin', 'status_update', 'certificate_request', 123, 'Updated certificate request status', '{\"old_status\":\"pending\",\"new_status\":\"processing\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-19 13:16:36'),
(6, 'admin', 'print_action', 'certificate_request', 456, 'Printed residency certificate', '{\"certificate_type\":\"RESIDENCY\",\"applicant_name\":\"Test User\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-19 13:16:36'),
(7, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful', '{\"username\":\"admin\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-19 13:16:36'),
(8, 'admin', 'form_view', 'resident_registration', 789, 'Viewed registration form', '{\"view_mode\":\"readonly\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-19 13:16:36'),
(9, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 06:03:24'),
(10, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 06:03:28'),
(11, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 06:22:20'),
(12, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 06:22:21'),
(13, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 06:53:41'),
(14, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:05:47'),
(15, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:19:32'),
(16, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:24:23'),
(17, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:24:23'),
(18, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:24:23'),
(19, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:24:24'),
(20, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:24:24'),
(21, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:32:44'),
(22, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:33:23'),
(23, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:36:04'),
(24, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:40:22'),
(25, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:40:51'),
(26, 'admin', 'status_update', 'certificate_request', 6, 'Updated certificate_request ID #6 status from \'pending\' to \'processing\'', '{\"old_status\":\"pending\",\"new_status\":\"processing\",\"timestamp\":\"2025-08-21 09:41:04\",\"certificate_type\":\"TRICYCLE PERMIT\",\"applicant_name\":\"Juan Dela Cruz\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:41:04'),
(27, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:41:04'),
(28, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:45:16'),
(29, 'admin', 'print_action', 'certificate_request', 6, 'Printed tricycle_permit for certificate_request ID #6', '{\"print_type\":\"tricycle_permit\",\"print_timestamp\":\"2025-08-21 09:45:17\",\"certificate_type\":\"TRICYCLE PERMIT\",\"applicant_name\":\"Juan Dela Cruz\",\"certificate_number\":\"TP-00006-2025\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:45:17'),
(30, 'admin', 'print_action', 'certificate_request', 6, 'Printed tricycle_permit for certificate_request ID #6', '{\"print_type\":\"tricycle_permit\",\"print_timestamp\":\"2025-08-21 09:46:25\",\"certificate_type\":\"TRICYCLE PERMIT\",\"applicant_name\":\"Juan Dela Cruz\",\"certificate_number\":\"TP-00006-2025\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:46:25'),
(31, 'admin', 'status_update', 'certificate_request', 12, 'Updated certificate_request ID #12 status from \'pending\' to \'processing\'', '{\"old_status\":\"pending\",\"new_status\":\"processing\",\"timestamp\":\"2025-08-21 09:46:38\",\"certificate_type\":\"BRGY. INDIGENCY\",\"applicant_name\":\"TEST TEST TEST\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:46:38'),
(32, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:46:38'),
(33, 'admin', 'print_action', 'certificate_request', 12, 'Printed indigency_certificate for certificate_request ID #12', '{\"print_type\":\"indigency_certificate\",\"print_timestamp\":\"2025-08-21 09:46:40\",\"certificate_type\":\"BRGY. INDIGENCY\",\"applicant_name\":\"TEST TEST TEST\",\"certificate_number\":\"IND-00012-2025\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 07:46:40'),
(34, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 08:39:00'),
(35, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 09:15:10'),
(36, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 09:25:03'),
(37, 'admin', 'form_view', 'resident_registration', 2, 'Viewed resident registration form ID #2', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 09:25:11'),
(38, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-21 09:25:21'),
(39, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-24 08:00:12'),
(40, 'admin', 'form_view', 'resident_registration', 2, 'Viewed resident registration form ID #2', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-24 08:00:22'),
(41, 'admin', 'form_view', 'resident_registration', 3, 'Viewed resident registration form ID #3', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-24 08:20:12'),
(42, 'admin', 'form_view', 'resident_registration', 3, 'Viewed resident registration form ID #3', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-24 08:22:14'),
(43, 'admin', 'form_view', 'resident_registration', 8, 'Viewed resident registration form ID #8', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-24 08:41:09'),
(44, 'admin', 'form_view', 'resident_registration', 28, 'Viewed resident registration form ID #28', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-24 08:47:41'),
(45, 'admin', 'form_view', 'resident_registration', 48, 'Viewed resident registration form ID #48', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-24 09:04:15'),
(46, 'admin', 'form_view', 'resident_registration', 48, 'Viewed resident registration form ID #48', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-24 09:46:19'),
(47, 'admin', 'form_view', 'resident_registration', 57, 'Viewed resident registration form ID #57', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-24 10:30:13'),
(48, 'admin', 'form_view', 'resident_registration', 200, 'Viewed resident registration form ID #200', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-24 12:24:45'),
(49, 'admin', 'form_view', 'resident_registration', 58, 'Viewed resident registration form ID #58', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-24 12:24:58'),
(50, 'admin', 'form_view', 'resident_registration', 67, 'Viewed resident registration form ID #67', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-24 12:25:12'),
(51, 'admin', 'form_view', 'resident_registration', 201, 'Viewed resident registration form ID #201', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-24 12:26:02'),
(52, 'admin', 'form_view', 'resident_registration', 210, 'Viewed resident registration form ID #210', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-24 12:26:14'),
(53, 'admin', 'form_view', 'resident_registration', 202, 'Viewed resident registration form ID #202', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-24 12:26:27'),
(54, 'admin', 'form_view', 'resident_registration', 211, 'Viewed resident registration form ID #211', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-24 12:41:55');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('super_admin','admin') NOT NULL DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `full_name`, `email`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@gumaoc.local', 'super_admin', '2025-08-01 07:44:29', '2025-08-01 07:44:29');

-- --------------------------------------------------------

--
-- Table structure for table `business_applications`
--

CREATE TABLE `business_applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reference_no` varchar(50) DEFAULT NULL,
  `application_date` date DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `business_location` text DEFAULT NULL,
  `or_number` varchar(100) DEFAULT NULL,
  `ctc_number` varchar(100) DEFAULT NULL,
  `business_name` varchar(255) NOT NULL,
  `business_type` varchar(100) NOT NULL,
  `business_address` varchar(500) NOT NULL,
  `owner_name` varchar(255) NOT NULL,
  `owner_address` text DEFAULT NULL,
  `contact_number` varchar(20) NOT NULL,
  `years_operation` int(11) NOT NULL,
  `investment_capital` decimal(15,2) NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','reviewing','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `business_applications`
--

INSERT INTO `business_applications` (`id`, `user_id`, `reference_no`, `application_date`, `first_name`, `middle_name`, `last_name`, `business_location`, `or_number`, `ctc_number`, `business_name`, `business_type`, `business_address`, `owner_name`, `owner_address`, `contact_number`, `years_operation`, `investment_capital`, `submitted_at`, `status`) VALUES
(1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Test', 'General Business', 'Test Test', 'Test Test Test', 'Test', '09000000000', 1, 0.00, '2025-08-01 10:21:59', 'pending'),
(2, 1, 'BA-2025-6770', '2025-08-19', 'Mar Yvan', 'Sagun', 'Dela Cruz', '\r\nError submitting application. Please try again.', '32213231231', '3212132231', ' Error submitting application. Please try again.', 'General Business', '\r\nError submitting application. Please try again.', 'Mar Yvan Sagun Dela Cruz', '\r\nError submitting application. Please try again.', '09000000000', 1, 0.00, '2025-08-19 11:42:40', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `certificate_requests`
--

CREATE TABLE `certificate_requests` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `address` varchar(500) NOT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `civil_status` varchar(50) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `birth_date` date NOT NULL,
  `birth_place` varchar(255) NOT NULL,
  `citizenship` varchar(100) DEFAULT NULL,
  `years_of_residence` int(11) DEFAULT NULL,
  `certificate_type` varchar(100) NOT NULL,
  `purpose` text NOT NULL,
  `vehicle_make_type` varchar(255) DEFAULT NULL,
  `motor_no` varchar(100) DEFAULT NULL,
  `chassis_no` varchar(100) DEFAULT NULL,
  `plate_no` varchar(50) DEFAULT NULL,
  `vehicle_color` varchar(50) DEFAULT NULL,
  `year_model` int(4) DEFAULT NULL,
  `body_no` varchar(100) DEFAULT NULL,
  `operator_license` varchar(100) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','processing','ready','released') DEFAULT 'pending',
  `queue_ticket_id` int(11) DEFAULT NULL,
  `queue_ticket_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificate_requests`
--

INSERT INTO `certificate_requests` (`id`, `full_name`, `address`, `mobile_number`, `civil_status`, `gender`, `birth_date`, `birth_place`, `citizenship`, `years_of_residence`, `certificate_type`, `purpose`, `vehicle_make_type`, `motor_no`, `chassis_no`, `plate_no`, `vehicle_color`, `year_model`, `body_no`, `operator_license`, `submitted_at`, `status`, `queue_ticket_id`, `queue_ticket_number`) VALUES
(6, 'Juan Dela Cruz', 'Purok 1, Gumaoc East', '09123456789', 'Married', 'Male', '1985-05-15', 'Baguio City', 'Filipino', 10, 'TRICYCLE PERMIT', 'Business Operation', 'Honda TMX-155', 'TMX155-2020-001234', 'CH-TMX155-5678', 'ABC-1234', 'Blue', 2020, 'BD-2020-001', 'N01-85-012345', '2025-08-19 13:28:00', 'processing', NULL, NULL),
(11, 'TEST TEST TEST', 'Purok 1, Gumaoc East', '+639162291763', 'Single', 'Male', '2004-07-08', 'Caloocan City', 'Filipino', 3, 'BRGY. CLEARANCE', 'TEST', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-21 07:35:59', 'pending', NULL, NULL),
(12, 'TEST TEST TEST', 'Purok 1, Gumaoc East', '+639162291763', 'Single', 'Male', '2004-08-07', 'TEST', 'Filipino', 3, 'BRGY. INDIGENCY', 'TEST', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-21 07:40:07', 'processing', NULL, NULL),
(13, 'TEST TEST TEST', 'Purok 1, Gumaoc East', '+639162291763', 'Single', 'Male', '2004-08-07', 'Caloocan City', 'Filipino', 13, 'BRGY. CLEARANCE', 'TEST', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-21 08:23:55', 'pending', 1, 'BC-20250821-001');

-- --------------------------------------------------------

--
-- Table structure for table `family_disabilities`
--

CREATE TABLE `family_disabilities` (
  `id` int(11) NOT NULL,
  `registration_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `disability_type` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_disabilities`
--

INSERT INTO `family_disabilities` (`id`, `registration_id`, `name`, `disability_type`, `created_at`) VALUES
(67, 201, 'Elena Santos Rodriguez', 'Diabetes Type 2', '2025-08-24 12:25:56'),
(68, 202, 'Roberto Dela Rosa Jr.', 'Hypertension', '2025-08-24 12:25:56'),
(69, 203, 'Isabella Grace Fernandez', 'Mild Asthma', '2025-08-24 12:25:56'),
(70, 204, 'Luis Cabrera Santos', 'Learning Disability - ADHD', '2025-08-24 12:25:56'),
(71, 205, 'Sebastian Rodriguez', 'Speech Therapy', '2025-08-24 12:25:56'),
(72, 207, 'Mayumi Magbanua', 'Partial Hearing Loss', '2025-08-24 12:25:56'),
(73, 208, 'Olivia Tan Wong', 'Developmental Delay', '2025-08-24 12:25:56'),
(74, 209, 'Corazon Esperanza Villanueva', 'Mobility Impairment - Uses Walker', '2025-08-24 12:25:56'),
(75, 209, 'Teodoro Villanueva', 'Vision Impairment - Cataracts', '2025-08-24 12:25:56'),
(76, 210, 'Lucas Moreno Silva', 'Mild Cerebral Palsy', '2025-08-24 12:25:56'),
(77, 211, 'TEST', 'TEST', '2025-08-24 12:37:03');

-- --------------------------------------------------------

--
-- Table structure for table `family_members`
--

CREATE TABLE `family_members` (
  `id` int(11) NOT NULL,
  `registration_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `relationship` varchar(100) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('Lalaki','Babae') DEFAULT NULL,
  `civil_status` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `education` varchar(100) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `skills` varchar(255) DEFAULT NULL,
  `monthly_income` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_members`
--

INSERT INTO `family_members` (`id`, `registration_id`, `full_name`, `relationship`, `birth_date`, `age`, `gender`, `civil_status`, `email`, `education`, `occupation`, `skills`, `monthly_income`, `created_at`) VALUES
(204, 201, 'Marcus Santos Dela Cruz', 'Spouse', '1992-12-13', 33, 'Lalaki', 'Married', 'marcus.delacruz817@gmail.com', NULL, 'DevOps Engineer', NULL, NULL, '2025-08-24 12:25:56'),
(205, 201, 'Sophia Santos Dela Cruz', 'Daughter', '2017-01-12', 8, 'Babae', 'Single', '', NULL, 'Student', NULL, NULL, '2025-08-24 12:25:56'),
(206, 201, 'Ethan Santos Dela Cruz', 'Son', '2020-08-09', 5, 'Lalaki', 'Single', '', NULL, 'Pre-school', NULL, NULL, '2025-08-24 12:25:56'),
(207, 201, 'Elena Santos Rodriguez', 'Mother', '1960-04-07', 65, 'Babae', 'Widow', 'elena.rodriguez497@yahoo.com', NULL, 'Retired Nurse', NULL, NULL, '2025-08-24 12:25:56'),
(208, 201, 'Victor Santos', 'Father-in-law', '1958-04-15', 67, 'Lalaki', 'Married', 'victor.santos807@email.com', NULL, 'Retired Engineer', NULL, NULL, '2025-08-24 12:25:56'),
(209, 202, 'Patricia Dela Rosa Morales', 'Daughter', '1980-01-24', 45, 'Babae', 'Married', 'patricia.morales543@outlook.com', NULL, 'Bank Manager', NULL, NULL, '2025-08-24 12:25:56'),
(210, 202, 'Roberto Dela Rosa Jr.', 'Son', '1983-07-19', 42, 'Lalaki', 'Married', 'roberto.delarosa597@email.com', NULL, 'Police Officer', NULL, NULL, '2025-08-24 12:25:56'),
(211, 202, 'Sofia Dela Rosa', 'Granddaughter', '2007-12-08', 18, 'Babae', 'Single', 'sofia.delarosa457@hotmail.com', NULL, 'College Student', NULL, NULL, '2025-08-24 12:25:56'),
(212, 203, 'Mia Fernandez', 'Daughter', '2021-11-18', 4, 'Babae', 'Single', '', NULL, 'Pre-school', NULL, NULL, '2025-08-24 12:25:56'),
(213, 203, 'Rosa Fernandez Martinez', 'Mother', '1977-04-22', 48, 'Babae', 'Separated', 'rosa.martinez629@hotmail.com', NULL, 'Factory Worker', NULL, NULL, '2025-08-24 12:25:56'),
(214, 204, 'Luz Cabrera Santos', 'Spouse', '1983-08-10', 42, 'Babae', 'Married', 'luz.santos491@email.com', NULL, 'Housewife/Farmer', NULL, NULL, '2025-08-24 12:25:56'),
(215, 204, 'Juan Cabrera Santos', 'Son', '2006-01-12', 19, 'Lalaki', 'Single', 'juan.cabrera745@outlook.com', NULL, 'Agricultural Student', NULL, NULL, '2025-08-24 12:25:56'),
(216, 204, 'Maria Cabrera Santos', 'Daughter', '2009-05-20', 16, 'Babae', 'Single', '', NULL, 'High School Student', NULL, NULL, '2025-08-24 12:25:56'),
(217, 204, 'Luis Cabrera Santos', 'Son', '2013-08-04', 12, 'Lalaki', 'Single', '', NULL, 'Elementary Student', NULL, NULL, '2025-08-24 12:25:56'),
(218, 205, 'Dr. Paulo Rodriguez', 'Spouse', '1985-09-27', 40, 'Lalaki', 'Married', 'paulo.rodriguez657@mail.com', NULL, 'Pediatrician', NULL, NULL, '2025-08-24 12:25:56'),
(219, 205, 'Camila Rodriguez', 'Daughter', '2013-06-12', 12, 'Babae', 'Single', '', NULL, 'Student', NULL, NULL, '2025-08-24 12:25:56'),
(220, 205, 'Sebastian Rodriguez', 'Son', '2016-06-06', 9, 'Lalaki', 'Single', '', NULL, 'Student', NULL, NULL, '2025-08-24 12:25:56'),
(221, 206, 'Jennifer Cruz Reyes', 'Spouse', '1993-12-17', 32, 'Babae', 'Married', 'jennifer.reyes797@mail.com', NULL, 'Teacher', NULL, NULL, '2025-08-24 12:25:56'),
(222, 206, 'Matthew Cruz Reyes', 'Son', '2015-05-15', 10, 'Lalaki', 'Single', '', NULL, 'Student', NULL, NULL, '2025-08-24 12:25:56'),
(223, 206, 'Samantha Cruz Reyes', 'Daughter', '2018-04-07', 7, 'Babae', 'Single', '', NULL, 'Student', NULL, NULL, '2025-08-24 12:25:56'),
(224, 206, 'Antonio Cruz', 'Father', '1957-07-28', 68, 'Lalaki', 'Married', 'antonio.cruz142@mail.com', NULL, 'Retired Government Employee', NULL, NULL, '2025-08-24 12:25:56'),
(225, 207, 'Mayumi Magbanua', 'Spouse', '1977-07-17', 48, 'Babae', 'Married', '', NULL, 'Traditional Weaver', NULL, NULL, '2025-08-24 12:25:56'),
(226, 207, 'Kalaw Magbanua', 'Son', '2003-03-24', 22, 'Lalaki', 'Single', 'kalaw.magbanua145@email.com', NULL, 'Cultural Preservation Officer', NULL, NULL, '2025-08-24 12:25:56'),
(227, 207, 'Tala Magbanua', 'Daughter', '2006-02-16', 19, 'Babae', 'Single', '', NULL, 'Traditional Arts Student', NULL, NULL, '2025-08-24 12:25:56'),
(228, 208, 'David Tan Wong', 'Spouse', '1994-03-13', 31, 'Lalaki', 'Married', 'david.wong881@outlook.com', NULL, 'Financial Analyst', NULL, NULL, '2025-08-24 12:25:56'),
(229, 208, 'Olivia Tan Wong', 'Daughter', '2022-08-11', 3, 'Babae', 'Single', '', NULL, 'Toddler', NULL, NULL, '2025-08-24 12:25:56'),
(230, 209, 'Teodoro Villanueva', 'Spouse', '1944-02-02', 81, 'Lalaki', 'Married', 'teodoro.villanueva581@outlook.com', NULL, 'Retired Principal', NULL, NULL, '2025-08-24 12:25:56'),
(231, 209, 'Grace Villanueva Santos', 'Daughter', '1973-04-12', 52, 'Babae', 'Married', 'grace.santos317@email.com', NULL, 'Social Worker', NULL, NULL, '2025-08-24 12:25:56'),
(232, 209, 'Paul Villanueva', 'Son', '1977-07-12', 48, 'Lalaki', 'Married', 'paul.villanueva232@mail.com', NULL, 'Government Employee', NULL, NULL, '2025-08-24 12:25:56'),
(233, 210, 'Bianca Moreno Silva', 'Spouse', '2000-03-20', 25, 'Babae', 'Married', 'bianca.silva922@mail.com', NULL, 'Marketing Coordinator', NULL, NULL, '2025-08-24 12:25:56'),
(234, 210, 'Lucas Moreno Silva', 'Son', '2023-05-25', 2, 'Lalaki', 'Single', '', NULL, 'Toddler', NULL, NULL, '2025-08-24 12:25:56'),
(235, 211, 'TESTTEST', 'TEST', NULL, 10, '', 'Single', 'TEST@gmail.com', NULL, 'TEST', NULL, NULL, '2025-08-24 12:37:03'),
(236, 211, 'TESTTEST', 'TEST', NULL, 10, '', 'Married', 'TEST@gmail.com', NULL, 'TEST', NULL, NULL, '2025-08-24 12:37:03');

-- --------------------------------------------------------

--
-- Table structure for table `family_organizations`
--

CREATE TABLE `family_organizations` (
  `id` int(11) NOT NULL,
  `registration_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `organization_type` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_organizations`
--

INSERT INTO `family_organizations` (`id`, `registration_id`, `name`, `organization_type`, `created_at`) VALUES
(187, 201, 'Angela Marie Santos', 'Women in Technology Philippines', '2025-08-24 12:25:56'),
(188, 201, 'Marcus Santos Dela Cruz', 'Philippine Software Industry Association', '2025-08-24 12:25:56'),
(189, 201, 'Elena Santos Rodriguez', 'Retired Nurses Association', '2025-08-24 12:25:56'),
(190, 202, 'Ricardo Pablo Dela Rosa', 'Senior Citizens Federation', '2025-08-24 12:25:56'),
(191, 202, 'Ricardo Pablo Dela Rosa', 'Retired Educators Association', '2025-08-24 12:25:56'),
(192, 202, 'Patricia Dela Rosa Morales', 'Bankers Association of the Philippines', '2025-08-24 12:25:56'),
(193, 202, 'Roberto Dela Rosa Jr.', 'Police Officers Association', '2025-08-24 12:25:56'),
(194, 203, 'Isabella Grace Fernandez', 'Single Mothers Support Group', '2025-08-24 12:25:56'),
(195, 203, 'Rosa Fernandez Martinez', 'Workers Union Local Chapter', '2025-08-24 12:25:57'),
(196, 204, 'Jose Miguel Cabrera', 'Farmers Cooperative', '2025-08-24 12:25:57'),
(197, 204, 'Luz Cabrera Santos', 'Rural Women Association', '2025-08-24 12:25:57'),
(198, 204, 'Juan Cabrera Santos', 'Young Farmers Organization', '2025-08-24 12:25:57'),
(199, 205, 'Dr. Carmen Luna Rodriguez', 'Philippine Medical Association', '2025-08-24 12:25:57'),
(200, 205, 'Dr. Paulo Rodriguez', 'Philippine Pediatric Society', '2025-08-24 12:25:57'),
(201, 205, 'Jennifer Cruz Reyes', 'Teachers Association', '2025-08-24 12:25:57'),
(202, 206, 'Michael Jose Cruz', 'OFW Mutual Aid Society', '2025-08-24 12:25:57'),
(203, 206, 'Jennifer Cruz Reyes', 'Public School Teachers Association', '2025-08-24 12:25:57'),
(204, 206, 'Antonio Cruz', 'Government Retirees Association', '2025-08-24 12:25:57'),
(205, 207, 'Lakandula Bayani Magbanua', 'Indigenous Peoples Council', '2025-08-24 12:25:57'),
(206, 207, 'Mayumi Magbanua', 'Traditional Arts Preservation Society', '2025-08-24 12:25:57'),
(207, 207, 'Kalaw Magbanua', 'Cultural Heritage Foundation', '2025-08-24 12:25:57'),
(208, 208, 'Alexandra Sophia Tan', 'Marketing Association Philippines', '2025-08-24 12:25:57'),
(209, 208, 'David Tan Wong', 'Financial Analysts Society', '2025-08-24 12:25:57'),
(210, 209, 'Corazon Esperanza Villanueva', 'Senior Citizens Club', '2025-08-24 12:25:57'),
(211, 209, 'Teodoro Villanueva', 'Retired Principals Association', '2025-08-24 12:25:57'),
(212, 209, 'Grace Villanueva Santos', 'Social Workers Association', '2025-08-24 12:25:57'),
(213, 209, 'Paul Villanueva', 'Government Employees Organization', '2025-08-24 12:25:57'),
(214, 210, 'Gabriel Andrew Moreno', 'Young Entrepreneurs Organization', '2025-08-24 12:25:57'),
(215, 210, 'Bianca Moreno Silva', 'Digital Marketing Professionals', '2025-08-24 12:25:57'),
(216, 211, 'TEST', 'TEST', '2025-08-24 12:37:03');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error','queue','certificate') DEFAULT 'info',
  `action_url` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `action_url`, `is_read`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Welcome to GUMAOC East E-Services', 'Your account has been successfully activated. You can now access all available services.', 'success', NULL, 0, NULL, '2025-08-21 10:56:35', '2025-08-21 10:56:35'),
(2, 1, 'Certificate Request Update', 'Your Barangay Certificate request is now being processed. Expected completion in 2-3 business days.', 'info', 'certificate-request.php', 0, NULL, '2025-08-21 10:56:35', '2025-08-21 10:56:35'),
(3, 1, 'Queue Ticket Ready', 'Your queue ticket #QT-001 is now being served. Please proceed to window 2.', 'queue', 'queue-status.php', 0, NULL, '2025-08-21 10:56:35', '2025-08-21 10:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `queue_counters`
--

CREATE TABLE `queue_counters` (
  `id` int(11) NOT NULL,
  `counter_number` varchar(10) NOT NULL,
  `counter_name` varchar(50) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `operator_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `current_ticket_id` int(11) DEFAULT NULL,
  `last_called_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queue_counters`
--

INSERT INTO `queue_counters` (`id`, `counter_number`, `counter_name`, `service_id`, `operator_name`, `is_active`, `current_ticket_id`, `last_called_at`, `created_at`, `updated_at`) VALUES
(1, 'C1', 'Counter 1 - Certificates', 1, NULL, 1, NULL, NULL, '2025-08-21 08:17:36', '2025-08-21 08:17:36'),
(2, 'C2', 'Counter 2 - Permits', 3, NULL, 1, NULL, NULL, '2025-08-21 08:17:36', '2025-08-21 08:17:36'),
(3, 'C3', 'Counter 3 - General', 5, NULL, 1, NULL, NULL, '2025-08-21 08:17:36', '2025-08-21 08:17:36');

-- --------------------------------------------------------

--
-- Table structure for table `queue_services`
--

CREATE TABLE `queue_services` (
  `id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `service_code` varchar(10) NOT NULL,
  `description` text DEFAULT NULL,
  `estimated_time` int(11) DEFAULT 15 COMMENT 'Estimated time in minutes',
  `max_daily_capacity` int(11) DEFAULT 50,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queue_services`
--

INSERT INTO `queue_services` (`id`, `service_name`, `service_code`, `description`, `estimated_time`, `max_daily_capacity`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Barangay Clearance', 'BC', 'Processing of Barangay Clearance certificates', 15, 50, 1, '2025-08-21 08:17:36', '2025-08-21 08:17:36'),
(2, 'Barangay Indigency', 'BI', 'Processing of Barangay Indigency certificates', 15, 30, 1, '2025-08-21 08:17:36', '2025-08-21 08:17:36'),
(3, 'Tricycle Permit', 'TP', 'Processing of Tricycle Operator Permits', 25, 20, 1, '2025-08-21 08:17:36', '2025-08-21 08:17:36'),
(4, 'Proof of Residency', 'PR', 'Processing of Proof of Residency certificates', 10, 40, 1, '2025-08-21 08:17:36', '2025-08-21 08:17:36'),
(5, 'General Services', 'GS', 'Other barangay services and inquiries', 20, 30, 1, '2025-08-21 08:17:36', '2025-08-21 08:17:36'),
(6, 'Business Permit', 'BP', 'Business permit applications and renewals', 30, 15, 1, '2025-08-21 08:17:36', '2025-08-21 08:17:36');

-- --------------------------------------------------------

--
-- Table structure for table `queue_tickets`
--

CREATE TABLE `queue_tickets` (
  `id` int(11) NOT NULL,
  `ticket_number` varchar(20) NOT NULL,
  `service_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `priority_level` enum('normal','priority','urgent') DEFAULT 'normal',
  `status` enum('waiting','serving','completed','cancelled','no_show') DEFAULT 'waiting',
  `queue_position` int(11) DEFAULT NULL,
  `estimated_time` datetime DEFAULT NULL,
  `called_at` timestamp NULL DEFAULT NULL,
  `served_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `served_by` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queue_tickets`
--

INSERT INTO `queue_tickets` (`id`, `ticket_number`, `service_id`, `customer_name`, `mobile_number`, `user_id`, `purpose`, `priority_level`, `status`, `queue_position`, `estimated_time`, `called_at`, `served_at`, `completed_at`, `served_by`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'BC-20250821-001', 1, 'TEST TEST TEST', '+639162291763', NULL, 'Certificate Request: BRGY. CLEARANCE', 'normal', 'waiting', 1, '2025-08-21 10:23:55', NULL, NULL, NULL, NULL, NULL, '2025-08-21 08:23:55', '2025-08-21 08:23:55');

-- --------------------------------------------------------

--
-- Table structure for table `queue_windows`
--

CREATE TABLE `queue_windows` (
  `id` int(11) NOT NULL,
  `window_number` varchar(10) NOT NULL,
  `window_name` varchar(50) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `operator_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `current_ticket_id` int(11) DEFAULT NULL,
  `last_called_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queue_windows`
--

INSERT INTO `queue_windows` (`id`, `window_number`, `window_name`, `service_id`, `operator_name`, `is_active`, `current_ticket_id`, `last_called_at`, `created_at`, `updated_at`) VALUES
(1, 'W1', 'Window 1 - Certificates', 1, NULL, 1, NULL, NULL, '2025-08-21 09:00:41', '2025-08-21 09:00:41'),
(2, 'W2', 'Window 2 - Permits', 3, NULL, 1, NULL, NULL, '2025-08-21 09:00:41', '2025-08-21 09:00:41'),
(3, 'W3', 'Window 3 - General Services', 5, NULL, 1, NULL, NULL, '2025-08-21 09:00:41', '2025-08-21 09:00:41');

-- --------------------------------------------------------

--
-- Table structure for table `residents`
--

CREATE TABLE `residents` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `address` text NOT NULL,
  `birthdate` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `civil_status` enum('Single','Married','Widowed','Separated','Divorced') NOT NULL,
  `rfid_code` varchar(50) DEFAULT NULL,
  `rfid` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'active',
  `reset_otp` varchar(6) DEFAULT NULL,
  `otp_expiry` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `residents`
--

INSERT INTO `residents` (`id`, `first_name`, `middle_name`, `last_name`, `email`, `phone`, `password`, `address`, `birthdate`, `gender`, `civil_status`, `rfid_code`, `rfid`, `status`, `reset_otp`, `otp_expiry`, `created_at`, `updated_at`) VALUES
(1, 'Mar Yvan', 'Sagun', 'Dela Cruz', 'biofrostyv@gmail.com', '09162291763', '$2y$10$T2sgNqcXTphnpenkr7Uy3uVzQVYaj4mlh825BV.CMsMZW1wkOao8W', 'test test test', '2004-07-08', 'Male', 'Single', '', '', 'active', NULL, NULL, '2025-08-18 12:26:08', '2025-08-18 12:26:08');

-- --------------------------------------------------------

--
-- Table structure for table `resident_registrations`
--

CREATE TABLE `resident_registrations` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `birth_date` date NOT NULL,
  `age` int(11) NOT NULL,
  `civil_status` varchar(50) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `house_number` varchar(20) DEFAULT NULL,
  `pangkabuhayan` varchar(100) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `land_ownership` varchar(100) DEFAULT NULL,
  `land_ownership_other` varchar(255) DEFAULT NULL,
  `house_ownership` varchar(100) DEFAULT NULL,
  `house_ownership_other` varchar(255) DEFAULT NULL,
  `farmland` varchar(100) DEFAULT NULL,
  `cooking_energy` varchar(100) DEFAULT NULL,
  `cooking_energy_other` varchar(255) DEFAULT NULL,
  `toilet_type` varchar(100) DEFAULT NULL,
  `toilet_type_other` varchar(255) DEFAULT NULL,
  `electricity_source` varchar(100) DEFAULT NULL,
  `electricity_source_other` varchar(255) DEFAULT NULL,
  `water_source` varchar(100) DEFAULT NULL,
  `water_source_other` varchar(255) DEFAULT NULL,
  `waste_disposal` varchar(100) DEFAULT NULL,
  `waste_disposal_other` varchar(255) DEFAULT NULL,
  `appliances` text DEFAULT NULL,
  `transportation` text DEFAULT NULL,
  `transportation_other` varchar(255) DEFAULT NULL,
  `business` text DEFAULT NULL,
  `business_other` varchar(255) DEFAULT NULL,
  `contraceptive` text DEFAULT NULL,
  `interviewer` varchar(255) DEFAULT NULL,
  `interviewer_title` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resident_registrations`
--

INSERT INTO `resident_registrations` (`id`, `first_name`, `middle_name`, `last_name`, `birth_date`, `age`, `civil_status`, `gender`, `contact_number`, `email`, `house_number`, `pangkabuhayan`, `submitted_at`, `status`, `land_ownership`, `land_ownership_other`, `house_ownership`, `house_ownership_other`, `farmland`, `cooking_energy`, `cooking_energy_other`, `toilet_type`, `toilet_type_other`, `electricity_source`, `electricity_source_other`, `water_source`, `water_source_other`, `waste_disposal`, `waste_disposal_other`, `appliances`, `transportation`, `transportation_other`, `business`, `business_other`, `contraceptive`, `interviewer`, `interviewer_title`) VALUES
(201, 'Angela', 'Marie', 'Santos', '1994-06-22', 31, 'Married', 'Female', '09123456789', 'angela.santos@email.com', '101', 'Software Developer', '2025-08-24 06:25:56', 'pending', 'Pag-aari', '', 'Pag-aari', '', 'Wala', 'LPG', '', 'Flush', '', 'Kuryente', '', 'Water District', '', 'Kinokolekta', '', 'Radyo/Stereo,Telebisyon,Refrigerator,Muwebles,Computer,Air Conditioner,Washing Machine', 'Kotse,Motorsiklo', '', 'Online Business,Freelancing,Software Development', '', 'Pills', 'Jenny Mendoza', 'Barangay Health Worker'),
(202, 'Ricardo', 'Pablo', 'Dela Rosa', '1953-02-26', 72, 'Widower', 'Male', '09234567890', 'ricardo.delarosa@email.com', '202', 'Retired Principal', '2025-08-21 06:25:56', 'approved', 'Pag-aari', '', 'Pag-aari', '', 'Wala', 'LPG', '', 'Flush', '', 'Kuryente', '', 'Water District', '', 'Kinokolekta', '', 'Radyo/Stereo,Telebisyon,Refrigerator', 'Tricycle', '', 'Pension,Government Benefits', '', 'Wala', 'Carlos Villanueva', 'Census Enumerator'),
(203, 'Isabella', 'Grace', 'Fernandez', '2001-06-18', 24, 'Single', 'Female', '09345678901', 'isabella.fernandez@email.com', '303', 'Cashier', '2025-08-24 00:25:56', 'pending', 'Inuupahan', '', 'Umuupa', '', 'Wala', 'Gaas', '', 'De-buhos', '', 'Gaas', '', 'Poso Artesiyano', '', 'Sinusunog', '', 'Radyo/Stereo,Telebisyon', 'Jeep,Tricycle', '', 'Sari-Sari Store', '', 'Condom', 'Maria Gonzales', 'Community Volunteer'),
(204, 'Jose', 'Miguel', 'Cabrera', '1980-03-23', 45, 'Married', 'Male', '09456789012', 'jose.cabrera@email.com', '404', 'Farmer', '2025-08-17 06:25:56', 'rejected', 'Pag-aari', '', 'Pag-aari', '', 'Pag-aari', 'Kahoy', '', 'Hinuhukay/Balon', '', 'Generator', 'Solar Panel', 'Deep Well', 'Spring Water', 'Compost Pit', 'Organic Composting', 'Radyo/Stereo', 'Carabao Cart,Motorcle', 'Farm Tractor', 'Rice Farming,Vegetable Garden,Livestock', 'Organic Farming', 'NFP', 'Pedro Reyes', 'Agricultural Extension Worker'),
(205, 'Dr. Carmen', 'Luna', 'Rodriguez', '1987-01-11', 38, 'Married', 'Female', '09567890123', 'carmen.rodriguez@email.com', '505', 'Medical Doctor', '2025-08-24 04:25:56', 'pending', 'Pag-aari', '', 'Pag-aari', '', 'Wala', 'LPG', '', 'Flush', '', 'Kuryente', '', 'Water District', '', 'Kinokolekta', '', 'Radyo/Stereo,Telebisyon,Refrigerator,Muwebles,Air Conditioner,Computer,Washing Machine', 'Kotse,Motorsiklo,Van', '', 'Medical Clinic,Health Services', '', 'IUD', 'Rosa Martinez', 'Public Health Nurse'),
(206, 'Michael', 'Jose', 'Cruz', '1990-06-16', 35, 'Married', 'Male', '09678901234', 'michael.cruz@email.com', '606', 'Overseas Worker (Dubai)', '2025-08-19 06:25:56', 'approved', 'Pag-aari', '', 'Pag-aari', '', 'Wala', 'LPG', '', 'Flush', '', 'Kuryente', '', 'Water District', '', 'Kinokolekta', '', 'Radyo/Stereo,Telebisyon,Refrigerator,Muwebles,Air Conditioner,Computer', 'Kotse,Van,Motorsiklo', '', 'Remittances,Real Estate,Investment', 'International Money Transfer', 'Pills', 'Ana Flores', 'Barangay Secretary'),
(207, 'Lakandula', 'Bayani', 'Magbanua', '1973-10-06', 52, 'Married', 'Male', '09789012345', 'lakandula.magbanua@email.com', '707', 'Traditional Healer', '2025-08-24 02:25:56', 'pending', 'Iba pa', 'Ancestral Domain', 'Iba pa', 'Traditional House', 'Pag-aari', 'Kahoy', '', 'Hinuhukay/Balon', '', 'Iba pa', 'Solar Panel', 'Iba pa', 'Spring Water', 'Iba pa', 'Natural Composting', 'Radyo/Stereo', 'Walking,Tricycle', 'Carabao', 'Herbal Medicine,Handicrafts,Traditional Crafts', 'Cultural Arts', 'NFP', 'Esperanza Santos', 'Indigenous Peoples Affairs Officer'),
(208, 'Alexandra', 'Sophia', 'Tan', '1996-11-08', 29, 'Married', 'Female', '09890123456', 'alexandra.tan@email.com', '808', 'Marketing Manager', '2025-08-23 06:25:56', 'pending', 'Pag-aari', '', 'Pag-aari', '', 'Wala', 'LPG', '', 'Flush', '', 'Kuryente', '', 'Water District', '', 'Kinokolekta', '', 'Radyo/Stereo,Telebisyon,Refrigerator,Muwebles,Air Conditioner,Computer,Washing Machine', 'Kotse,Motorcycle', '', 'Digital Marketing Agency,Online Business', '', 'Pills', 'Roberto Silva', 'IT Support Specialist'),
(209, 'Corazon', 'Esperanza', 'Villanueva', '1947-11-01', 78, 'Married', 'Female', '09901234567', 'corazon.villanueva@email.com', '909', 'Retired Teacher', '2025-08-10 06:25:56', 'approved', 'Pag-aari', '', 'Pag-aari', '', 'Wala', 'LPG', '', 'Flush', '', 'Kuryente', '', 'Water District', '', 'Kinokolekta', '', 'Radyo/Stereo,Telebisyon,Refrigerator', 'Wheelchair,Tricycle', 'Medical Transport', 'Pension,Social Security,Government Benefits', 'Senior Citizens Discount', 'Wala', 'Gloria Ramos', 'Senior Citizens Coordinator'),
(210, 'Gabriel', 'Andrew', 'Moreno', '1998-08-01', 27, 'Married', 'Male', '09012345678', 'gabriel.moreno@email.com', '1010', 'Business Owner', '2025-08-24 05:55:56', 'pending', 'Pag-aari', '', 'Pag-aari', '', 'Wala', 'LPG', '', 'Flush', '', 'Kuryente', '', 'Water District', '', 'Kinokolekta', '', 'Radyo/Stereo,Telebisyon,Refrigerator,Muwebles,Computer,Air Conditioner', 'Kotse,Delivery Van,Motorsiklo', 'Business Vehicle', 'Food Delivery,E-commerce,Digital Services', 'Tech Startup', 'Condom', 'Daniel Castro', 'Business Development Officer'),
(211, 'TEST', '', 'TEST', '2025-08-24', 25, 'Unknown', 'Not Specified', '09162291763', 'test@example.com', '101', 'Iba pa', '2025-08-24 12:37:03', 'pending', 'Iba pa', 'TEST', 'Iba pa', 'TEST', 'Wala', 'Iba pa', 'TEST', 'Iba pa', 'TEST', 'Iba pa', 'TEST', 'Iba pa', 'TEST', 'Iba pa', 'TEST', 'Muwebles', 'Iba pa', 'TEST', 'Iba pa', 'TEST', 'Wala', 'TEST', 'TEST');

-- --------------------------------------------------------

--
-- Table structure for table `rfid_access_logs`
--

CREATE TABLE `rfid_access_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rfid_tag` varchar(20) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `access_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rfid_registrations`
--

CREATE TABLE `rfid_registrations` (
  `id` int(11) NOT NULL,
  `rfid_number` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `birth_date` date NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `card_type` enum('resident','employee','visitor') DEFAULT 'resident',
  `status` enum('pending','approved','rejected','active','blocked') DEFAULT 'pending',
  `issued_date` date DEFAULT NULL,
  `expires_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rfid_registrations`
--

INSERT INTO `rfid_registrations` (`id`, `rfid_number`, `first_name`, `middle_name`, `last_name`, `birth_date`, `contact_number`, `address`, `card_type`, `status`, `issued_date`, `expires_date`, `created_at`, `updated_at`) VALUES
(1, '0006894001', 'Mar Yvan', 'Sagun', 'Dela Cruz', '2004-08-07', '09162291763', 'N/A', 'resident', 'pending', NULL, NULL, '2025-08-04 09:30:31', '2025-08-04 09:30:31');

-- --------------------------------------------------------

--
-- Table structure for table `rfid_users`
--

CREATE TABLE `rfid_users` (
  `id` int(11) NOT NULL,
  `rfid_tag` varchar(20) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `id_type` enum('National ID','Drivers License','Passport','Other') DEFAULT 'National ID',
  `id_number` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(50) NOT NULL,
  `button_text` varchar(100) NOT NULL,
  `button_link` varchar(255) NOT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `features` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `title`, `description`, `icon`, `button_text`, `button_link`, `is_featured`, `features`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'Emergency Response', 'Real-time incident reporting with IoT sensors and instant emergency response coordination.', '????', 'Report Incident', 'pages/report.php', 1, 'IoT Enabled,24/7 Monitoring', 0, '2025-08-04 06:22:38', '2025-08-04 06:22:38'),
(2, 'Document Requests', 'Request certificates, clearances, and official documents online with automated processing.', '????', 'Apply Now', 'pages/forms.php', 0, 'Online Processing,Fast Approval', 0, '2025-08-04 06:22:38', '2025-08-04 06:22:38'),
(3, 'Community Census', 'Register as a resident and contribute to our comprehensive community database.', '????', 'Register', 'pages/forms.php', 0, 'Digital Registry,Secure Data', 0, '2025-08-04 06:22:38', '2025-08-04 06:22:38'),
(5, 'TEST', 'TEST', '', 'TEST', 'pages/services.php', 0, 'TEST', 0, '2025-08-04 06:29:39', '2025-08-04 06:29:39');

-- --------------------------------------------------------

--
-- Table structure for table `updates`
--

CREATE TABLE `updates` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `badge_text` varchar(50) NOT NULL,
  `badge_type` enum('important','new','community','info') DEFAULT 'info',
  `date` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `is_priority` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `updates`
--

INSERT INTO `updates` (`id`, `title`, `description`, `badge_text`, `badge_type`, `date`, `status`, `is_priority`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'COVID-19 Vaccination Drive', 'New vaccination schedule available. Free vaccination for all residents. Register online to secure your slot.', 'Important', 'important', 'July 28, 2025', '???? Active', 1, 0, '2025-08-04 09:04:14', '2025-08-04 09:04:14'),
(2, 'Enhanced E-Services Launch', 'Our improved digital platform now offers faster processing, better security, and mobile optimization.', 'New', 'new', 'July 25, 2025', '???? Live', 0, 0, '2025-08-04 09:04:14', '2025-08-04 09:04:14'),
(3, 'Town Fiesta 2025', 'Join us for our annual town celebration. Cultural shows, local food, and community activities for everyone.', 'Community', 'community', 'August 15, 2025', '???? Upcoming', 0, 0, '2025-08-04 09:04:14', '2025-08-04 09:04:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_target_type` (`target_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `business_applications`
--
ALTER TABLE `business_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `certificate_requests`
--
ALTER TABLE `certificate_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_queue_ticket` (`queue_ticket_id`);

--
-- Indexes for table `family_disabilities`
--
ALTER TABLE `family_disabilities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `registration_id` (`registration_id`);

--
-- Indexes for table `family_members`
--
ALTER TABLE `family_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `registration_id` (`registration_id`);

--
-- Indexes for table `family_organizations`
--
ALTER TABLE `family_organizations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `registration_id` (`registration_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `queue_counters`
--
ALTER TABLE `queue_counters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `counter_number` (`counter_number`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `current_ticket_id` (`current_ticket_id`);

--
-- Indexes for table `queue_services`
--
ALTER TABLE `queue_services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_code` (`service_code`);

--
-- Indexes for table `queue_tickets`
--
ALTER TABLE `queue_tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_number` (`ticket_number`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `queue_windows`
--
ALTER TABLE `queue_windows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `window_number` (`window_number`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `current_ticket_id` (`current_ticket_id`);

--
-- Indexes for table `residents`
--
ALTER TABLE `residents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `residents_email` (`email`),
  ADD UNIQUE KEY `residents_phone` (`phone`),
  ADD UNIQUE KEY `residents_rfid_code` (`rfid_code`),
  ADD UNIQUE KEY `residents_rfid` (`rfid`),
  ADD KEY `idx_residents_status` (`status`),
  ADD KEY `idx_residents_rfid_codes` (`rfid_code`,`rfid`);

--
-- Indexes for table `resident_registrations`
--
ALTER TABLE `resident_registrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rfid_access_logs`
--
ALTER TABLE `rfid_access_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rfid_registrations`
--
ALTER TABLE `rfid_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rfid_number` (`rfid_number`);

--
-- Indexes for table `rfid_users`
--
ALTER TABLE `rfid_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rfid_tag` (`rfid_tag`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `updates`
--
ALTER TABLE `updates`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `business_applications`
--
ALTER TABLE `business_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `certificate_requests`
--
ALTER TABLE `certificate_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `family_disabilities`
--
ALTER TABLE `family_disabilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `family_members`
--
ALTER TABLE `family_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=237;

--
-- AUTO_INCREMENT for table `family_organizations`
--
ALTER TABLE `family_organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=217;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `queue_counters`
--
ALTER TABLE `queue_counters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `queue_services`
--
ALTER TABLE `queue_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `queue_tickets`
--
ALTER TABLE `queue_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `queue_windows`
--
ALTER TABLE `queue_windows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `resident_registrations`
--
ALTER TABLE `resident_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=212;

--
-- AUTO_INCREMENT for table `rfid_access_logs`
--
ALTER TABLE `rfid_access_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rfid_registrations`
--
ALTER TABLE `rfid_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rfid_users`
--
ALTER TABLE `rfid_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `updates`
--
ALTER TABLE `updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `business_applications`
--
ALTER TABLE `business_applications`
  ADD CONSTRAINT `business_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `certificate_requests`
--
ALTER TABLE `certificate_requests`
  ADD CONSTRAINT `fk_cert_queue_ticket` FOREIGN KEY (`queue_ticket_id`) REFERENCES `queue_tickets` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `family_disabilities`
--
ALTER TABLE `family_disabilities`
  ADD CONSTRAINT `family_disabilities_ibfk_1` FOREIGN KEY (`registration_id`) REFERENCES `resident_registrations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `family_members`
--
ALTER TABLE `family_members`
  ADD CONSTRAINT `family_members_ibfk_1` FOREIGN KEY (`registration_id`) REFERENCES `resident_registrations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `family_organizations`
--
ALTER TABLE `family_organizations`
  ADD CONSTRAINT `family_organizations_ibfk_1` FOREIGN KEY (`registration_id`) REFERENCES `resident_registrations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `queue_counters`
--
ALTER TABLE `queue_counters`
  ADD CONSTRAINT `queue_counters_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `queue_services` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `queue_counters_ibfk_2` FOREIGN KEY (`current_ticket_id`) REFERENCES `queue_tickets` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `queue_tickets`
--
ALTER TABLE `queue_tickets`
  ADD CONSTRAINT `queue_tickets_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `queue_services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `queue_windows`
--
ALTER TABLE `queue_windows`
  ADD CONSTRAINT `queue_windows_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `queue_services` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `queue_windows_ibfk_2` FOREIGN KEY (`current_ticket_id`) REFERENCES `queue_tickets` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `rfid_access_logs`
--
ALTER TABLE `rfid_access_logs`
  ADD CONSTRAINT `rfid_access_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `rfid_users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
