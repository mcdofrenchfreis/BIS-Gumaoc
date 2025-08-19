-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2025 at 03:28 PM
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
(8, 'admin', 'form_view', 'resident_registration', 789, 'Viewed registration form', '{\"view_mode\":\"readonly\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-19 13:16:36');

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
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','processing','ready','released') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificate_requests`
--

INSERT INTO `certificate_requests` (`id`, `full_name`, `address`, `mobile_number`, `civil_status`, `gender`, `birth_date`, `birth_place`, `citizenship`, `years_of_residence`, `certificate_type`, `purpose`, `submitted_at`, `status`) VALUES
(1, 'zz zzz z', 'zz zz', NULL, NULL, NULL, '2025-08-16', 'zzz', NULL, NULL, 'CEDULA', 'zz', '2025-08-01 07:53:25', 'pending'),
(2, 'test test test', 'test test', '09162291763', 'Single', 'Male', '2025-08-01', 'test', 'Filipino', 3, 'CEDULA', 'test', '2025-08-01 08:28:08', 'processing'),
(3, 'test test test', 'test test', '09162291763', 'Single', 'Male', '2025-08-02', 'test', 'Filipino', 3, 'CEDULA', 'test', '2025-08-01 08:31:17', 'processing'),
(4, 'test test test', 'test test', '09162291763', 'Single', 'Male', '2025-08-01', 'test', 'Filipino', 1, 'CEDULA', 'test', '2025-08-01 08:33:11', 'ready'),
(5, 'TEST TEST TEST', 'TEST TEST', '09162291763', 'Single', 'Male', '2004-08-07', 'TEST', 'Filipino', 321, 'BRGY. INDIGENCY', 'TEST', '2025-08-19 12:52:46', 'processing');

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
(1, 2, 'Lola Rosa Cruz', 'Visual Impairment', '2025-08-01 09:08:43');

-- --------------------------------------------------------

--
-- Table structure for table `family_members`
--

CREATE TABLE `family_members` (
  `id` int(11) NOT NULL,
  `registration_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `birth_date` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `civil_status` varchar(50) DEFAULT NULL,
  `education` varchar(100) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `skills` varchar(255) DEFAULT NULL,
  `monthly_income` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_members`
--

INSERT INTO `family_members` (`id`, `registration_id`, `full_name`, `birth_date`, `age`, `civil_status`, `education`, `occupation`, `skills`, `monthly_income`, `created_at`) VALUES
(1, 2, 'Maria Santos Cruz', '1985-03-20', 38, 'Married', 'College Graduate', 'Teacher', 'Teaching, Computer Skills', 25000.00, '2025-08-01 09:08:43'),
(2, 2, 'Pedro Juan Cruz', '2010-08-10', 13, 'Single', 'Grade 7', 'Student', 'Sports, Drawing', 0.00, '2025-08-01 09:08:43'),
(3, 2, 'Ana Maria Cruz', '2015-12-05', 8, 'Single', 'Grade 2', 'Student', 'Reading, Singing', 0.00, '2025-08-01 09:08:43');

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
(1, 2, 'Juan Santos Cruz', 'Barangay Council', '2025-08-01 09:08:43'),
(2, 2, 'Maria Santos Cruz', 'Parent-Teacher Association', '2025-08-01 09:08:43');

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

INSERT INTO `resident_registrations` (`id`, `first_name`, `middle_name`, `last_name`, `birth_date`, `age`, `civil_status`, `gender`, `contact_number`, `house_number`, `pangkabuhayan`, `submitted_at`, `status`, `land_ownership`, `land_ownership_other`, `house_ownership`, `house_ownership_other`, `farmland`, `cooking_energy`, `cooking_energy_other`, `toilet_type`, `toilet_type_other`, `electricity_source`, `electricity_source_other`, `water_source`, `water_source_other`, `waste_disposal`, `waste_disposal_other`, `appliances`, `transportation`, `transportation_other`, `business`, `business_other`, `contraceptive`, `interviewer`, `interviewer_title`) VALUES
(1, 'test', '', 'test', '2025-08-01', 25, 'Unknown', 'Not Specified', '09162291763', '3', 'Not Specified', '2025-08-01 08:49:15', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'Juan', 'Santos', 'Cruz', '1980-05-15', 43, 'Married', 'Male', '09123456789', '123', 'Pag-aari', '2025-08-01 09:08:43', 'approved', 'Pag-aari', NULL, 'Pag-aari', NULL, 'Pag-aari', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon,Refrigerator', 'Kotse,Motorsiklo', NULL, 'Sari-Sari Store', NULL, 'Wala', 'Maria Garcia', 'Barangay Health Worker');

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
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `family_disabilities`
--
ALTER TABLE `family_disabilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `family_members`
--
ALTER TABLE `family_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `family_organizations`
--
ALTER TABLE `family_organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `resident_registrations`
--
ALTER TABLE `resident_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- Constraints for table `rfid_access_logs`
--
ALTER TABLE `rfid_access_logs`
  ADD CONSTRAINT `rfid_access_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `rfid_users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
