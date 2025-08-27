-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 27, 2025 at 04:35 PM
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

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `update_resident_status` (`resident_id_param` INT) RETURNS VARCHAR(20) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC READS SQL DATA BEGIN
    DECLARE complaint_count INT DEFAULT 0;
    DECLARE incident_count INT DEFAULT 0;
    DECLARE pending_count INT DEFAULT 0;
    DECLARE resolved_count INT DEFAULT 0;
    DECLARE new_status VARCHAR(20) DEFAULT 'good';
    DECLARE requires_clearance BOOLEAN DEFAULT FALSE;
    
    -- Count complaints where resident is complainant
    SELECT COUNT(*) INTO complaint_count 
    FROM barangay_blotter 
    WHERE complainant_id = resident_id_param;
    
    -- Count incidents where resident is respondent
    SELECT COUNT(*) INTO incident_count 
    FROM barangay_blotter 
    WHERE respondent_id = resident_id_param;
    
    -- Count pending cases where resident is respondent
    SELECT COUNT(*) INTO pending_count 
    FROM barangay_blotter 
    WHERE respondent_id = resident_id_param 
    AND status IN ('filed', 'under_investigation', 'mediation');
    
    -- Count resolved cases where resident is respondent
    SELECT COUNT(*) INTO resolved_count 
    FROM barangay_blotter 
    WHERE respondent_id = resident_id_param 
    AND status IN ('resolved', 'dismissed');
    
    -- Determine status based on incident count and pending cases
    IF incident_count = 0 THEN
        SET new_status = 'good';
        SET requires_clearance = FALSE;
    ELSEIF incident_count <= 2 AND pending_count = 0 THEN
        SET new_status = 'minor_issues';
        SET requires_clearance = FALSE;
    ELSEIF incident_count <= 5 OR pending_count > 0 THEN
        SET new_status = 'major_issues';
        SET requires_clearance = TRUE;
    ELSE
        SET new_status = 'critical';
        SET requires_clearance = TRUE;
    END IF;
    
    -- Update or insert resident status
    INSERT INTO resident_status (
        resident_id, resident_name, record_status, total_complaints, 
        total_incidents, pending_cases, resolved_cases, requires_captain_clearance
    ) VALUES (
        resident_id_param, 
        (SELECT CONCAT(first_name, ' ', IFNULL(middle_name, ''), ' ', last_name) FROM residents WHERE id = resident_id_param),
        new_status, complaint_count, incident_count, pending_count, resolved_count, requires_clearance
    ) ON DUPLICATE KEY UPDATE
        record_status = new_status,
        total_complaints = complaint_count,
        total_incidents = incident_count,
        pending_cases = pending_count,
        resolved_cases = resolved_count,
        requires_captain_clearance = requires_clearance,
        last_updated = CURRENT_TIMESTAMP;
    
    RETURN new_status;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `access_logs`
--

CREATE TABLE `access_logs` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) DEFAULT NULL,
  `form_type` varchar(100) NOT NULL,
  `access_granted` tinyint(1) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(54, 'admin', 'form_view', 'resident_registration', 211, 'Viewed resident registration form ID #211', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-24 12:41:55'),
(55, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 11:49:49'),
(56, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed manage updates admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 12:24:03'),
(57, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed manage updates admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 13:29:28'),
(58, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 13:29:44'),
(59, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed manage updates admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 13:42:43'),
(60, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 13:42:47'),
(61, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 14:25:08'),
(62, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed manage updates admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 14:25:12'),
(63, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 14:32:43'),
(64, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 03:42:07'),
(65, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 04:37:18'),
(66, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 08:33:42'),
(67, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 09:25:32'),
(68, 'admin', 'admin_logout', 'admin_auth', NULL, 'Admin logout for username: admin', '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 09:42:59'),
(69, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 10:12:59'),
(70, 'admin', 'admin_logout', 'admin_auth', NULL, 'Admin logout for username: admin', '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 10:32:57'),
(71, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 10:33:02'),
(72, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed manage updates admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 10:33:04'),
(73, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 10:33:08'),
(74, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 13:20:09'),
(75, 'admin', 'form_view', 'resident_registration', 218, 'Viewed resident registration form ID #218', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 13:22:05'),
(76, 'admin', 'status_update', 'resident_registration', 218, 'Updated resident_registration ID #218 status from \'pending\' to \'approved\'', '{\"old_status\":\"pending\",\"new_status\":\"approved\",\"timestamp\":\"2025-08-27 15:27:44\",\"applicant_name\":\"Mar Yvan Dela Cruz\",\"registration_type\":\"resident_registration\",\"processing_time\":\"2025-08-27 15:27:44\",\"admin_action\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 13:27:44'),
(77, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 14:03:34'),
(78, 'admin', 'form_view', 'resident_registration', 219, 'Viewed resident registration form ID #219', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 14:03:43'),
(79, 'admin', 'form_view', 'resident_registration', 219, 'Viewed resident registration form ID #219', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 14:08:06'),
(80, 'admin', 'form_view', 'resident_registration', 219, 'Viewed resident registration form ID #219', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 14:12:38'),
(81, 'admin', 'form_view', 'resident_registration', 219, 'Viewed resident registration form ID #219', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 14:13:46'),
(82, 'admin', 'form_view', 'resident_registration', 219, 'Viewed resident registration form ID #219', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 14:20:49'),
(83, 'admin', 'form_view', 'resident_registration', 219, 'Viewed resident registration form ID #219', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 14:25:07'),
(84, 'admin', 'form_view', 'resident_registration', 220, 'Viewed resident registration form ID #220', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 14:29:03');

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
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@gumaoc.local', 'super_admin', '2025-08-01 07:44:29', '2025-08-01 07:44:29'),
(2, 'blotter_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Blotter Administrator', 'blotter@gumaoc.local', 'admin', '2025-08-25 05:45:36', '2025-08-25 05:45:36');

-- --------------------------------------------------------

--
-- Table structure for table `barangay_blotter`
--

CREATE TABLE `barangay_blotter` (
  `id` int(11) NOT NULL,
  `blotter_number` varchar(50) NOT NULL,
  `incident_type` enum('complaint','incident','dispute','violation','other') NOT NULL,
  `complainant_id` int(11) DEFAULT NULL,
  `complainant_name` varchar(255) NOT NULL,
  `complainant_address` varchar(500) NOT NULL,
  `complainant_contact` varchar(20) DEFAULT NULL,
  `respondent_id` int(11) DEFAULT NULL,
  `respondent_name` varchar(255) NOT NULL,
  `respondent_address` varchar(500) NOT NULL,
  `respondent_contact` varchar(20) DEFAULT NULL,
  `incident_date` datetime NOT NULL,
  `reported_date` datetime NOT NULL DEFAULT current_timestamp(),
  `location` varchar(500) NOT NULL,
  `description` text NOT NULL,
  `classification` enum('minor','major','critical') DEFAULT 'minor',
  `status` enum('filed','under_investigation','mediation','resolved','dismissed','referred_to_court') DEFAULT 'filed',
  `investigating_officer` varchar(255) DEFAULT NULL,
  `settlement_details` text DEFAULT NULL,
  `action_taken` text DEFAULT NULL,
  `case_disposition` text DEFAULT NULL,
  `created_by` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `barangay_blotter`
--
DELIMITER $$
CREATE TRIGGER `update_resident_status_after_blotter_insert` AFTER INSERT ON `barangay_blotter` FOR EACH ROW BEGIN
    -- Update complainant status if they are a registered resident
    IF NEW.complainant_id IS NOT NULL THEN
        SET @result = update_resident_status(NEW.complainant_id);
    END IF;
    
    -- Update respondent status if they are a registered resident
    IF NEW.respondent_id IS NOT NULL THEN
        SET @result = update_resident_status(NEW.respondent_id);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_resident_status_after_blotter_update` AFTER UPDATE ON `barangay_blotter` FOR EACH ROW BEGIN
    -- Update complainant status if they are a registered resident
    IF NEW.complainant_id IS NOT NULL THEN
        SET @result = update_resident_status(NEW.complainant_id);
    END IF;
    
    -- Update respondent status if they are a registered resident
    IF NEW.respondent_id IS NOT NULL THEN
        SET @result = update_resident_status(NEW.respondent_id);
    END IF;
    
    -- Also update old complainant/respondent if they changed
    IF OLD.complainant_id IS NOT NULL AND OLD.complainant_id != NEW.complainant_id THEN
        SET @result = update_resident_status(OLD.complainant_id);
    END IF;
    
    IF OLD.respondent_id IS NOT NULL AND OLD.respondent_id != NEW.respondent_id THEN
        SET @result = update_resident_status(OLD.respondent_id);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `blotter_attachments`
--

CREATE TABLE `blotter_attachments` (
  `id` int(11) NOT NULL,
  `blotter_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_by` varchar(100) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `business_description` text DEFAULT NULL,
  `capital_amount` decimal(15,2) DEFAULT NULL,
  `owner_name` varchar(255) NOT NULL,
  `owner_address` text DEFAULT NULL,
  `owner_contact` varchar(20) DEFAULT NULL,
  `contact_number` varchar(20) NOT NULL,
  `years_operation` int(11) NOT NULL,
  `investment_capital` decimal(15,2) NOT NULL,
  `proof_image` varchar(255) DEFAULT NULL COMMENT 'Optional proof image filename for the business application',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','reviewing','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `captain_clearances`
--

CREATE TABLE `captain_clearances` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `clearance_type` enum('form_access','certificate_request','business_permit','general') NOT NULL,
  `reason` text NOT NULL,
  `granted_by` varchar(100) NOT NULL,
  `granted_date` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  `status` enum('active','expired','revoked') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certificate_requests`
--

CREATE TABLE `certificate_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
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
  `additional_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_data`)),
  `proof_image` varchar(255) DEFAULT NULL,
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
  `queue_ticket_number` varchar(20) DEFAULT NULL,
  `notes` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(80, 220, 'TEST ME PLEASE', 'TEST ME PLEASE', '2025-08-27 14:28:52');

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
(243, 220, 'TEST ME PLEASE', 'Anak', '2014-07-08', 11, 'Lalaki', 'Single', 'testmeplease@test.com', NULL, 'TEST ME PLEASE', NULL, NULL, '2025-08-27 14:28:45');

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
(219, 220, 'TEST ME PLEASE', 'TEST ME PLEASE', '2025-08-27 14:28:52');

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
(1, 'C1', 'Counter 1 - All Certificates', 1, NULL, 1, 90, '2025-08-27 09:41:51', '2025-08-21 00:17:36', '2025-08-27 09:41:51'),
(2, 'C2', 'Counter 2 - Business Applications', 6, NULL, 1, 89, '2025-08-27 09:41:50', '2025-08-21 00:17:36', '2025-08-27 09:41:50'),
(3, 'C3', 'Counter 3 - General', 5, NULL, 1, 88, '2025-08-27 09:41:50', '2025-08-21 00:17:36', '2025-08-27 09:41:50');

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
(88, 'GS-20250827-001', 5, 'Patricia Morales', '09878863389', NULL, 'Form Submission', 'priority', 'serving', 1, '2025-08-27 11:39:11', '2025-08-27 09:41:50', '2025-08-27 09:41:50', NULL, NULL, NULL, '2025-08-27 09:39:11', '2025-08-27 09:41:50'),
(89, 'BP-20250827-001', 6, 'Pedro Perez', '09260495790', NULL, 'Document Verification', 'normal', 'serving', 1, '2025-08-27 11:39:11', '2025-08-27 09:41:50', '2025-08-27 09:41:50', NULL, NULL, NULL, '2025-08-27 09:39:11', '2025-08-27 09:41:50'),
(90, 'PR-20250827-001', 4, 'Maria Torres', '09791067451', NULL, 'Document Verification', 'normal', 'serving', 1, '2025-08-27 11:39:11', '2025-08-27 09:41:51', '2025-08-27 09:41:51', NULL, NULL, NULL, '2025-08-27 09:39:11', '2025-08-27 09:41:51'),
(91, 'BI-20250827-001', 2, 'Ana Torres', '09961597193', NULL, 'Permit Application', '', 'waiting', 1, '2025-08-27 11:39:11', NULL, NULL, NULL, NULL, NULL, '2025-08-27 09:39:11', '2025-08-27 09:39:11'),
(92, 'GS-20250827-002', 5, 'Elena Flores', '09969421938', NULL, 'Certificate Request', 'normal', 'waiting', 2, '2025-08-27 11:59:11', NULL, NULL, NULL, NULL, NULL, '2025-08-27 09:39:11', '2025-08-27 09:39:11'),
(93, 'GS-20250827-003', 5, 'Pedro Jimenez', '09882382417', NULL, 'Complaint Filing', 'normal', 'waiting', 3, '2025-08-27 12:19:11', NULL, NULL, NULL, NULL, NULL, '2025-08-27 09:39:11', '2025-08-27 09:39:11'),
(94, 'GS-20250827-004', 5, 'Luz Flores', '09286032224', NULL, 'Complaint Filing', 'normal', 'waiting', 4, '2025-08-27 12:39:12', NULL, NULL, NULL, NULL, NULL, '2025-08-27 09:39:12', '2025-08-27 09:39:12'),
(95, 'GS-20250827-005', 5, 'Ana Fernandez', '09756840517', NULL, 'Permit Application', 'normal', 'waiting', 5, '2025-08-27 12:59:12', NULL, NULL, NULL, NULL, NULL, '2025-08-27 09:39:12', '2025-08-27 09:39:12'),
(96, 'GS-20250827-006', 5, 'Ana Hernandez', '09124901236', NULL, 'Certificate Request', 'normal', 'waiting', 6, '2025-08-27 13:19:12', NULL, NULL, NULL, NULL, NULL, '2025-08-27 09:39:12', '2025-08-27 09:39:12'),
(97, 'BP-20250827-002', 6, 'Ana Torres', '09415613485', NULL, 'Complaint Filing', 'normal', 'waiting', 2, '2025-08-27 12:09:12', NULL, NULL, NULL, NULL, NULL, '2025-08-27 09:39:12', '2025-08-27 09:39:12'),
(98, 'GS-20250827-007', 5, 'Isabel Mendoza', '09525455004', NULL, 'Complaint Filing', '', 'waiting', 7, '2025-08-27 13:39:12', NULL, NULL, NULL, NULL, NULL, '2025-08-27 09:39:12', '2025-08-27 09:39:12'),
(99, 'BP-20250827-003', 6, 'Teresa Gonzales', '09464282067', NULL, 'Certificate Request', 'normal', 'waiting', 3, '2025-08-27 12:39:12', NULL, NULL, NULL, NULL, NULL, '2025-08-27 09:39:12', '2025-08-27 09:39:12'),
(100, 'GS-20250827-008', 5, 'Carlos Reyes', '09328534360', NULL, 'Permit Application', 'normal', 'waiting', 8, '2025-08-27 13:59:12', NULL, NULL, NULL, NULL, NULL, '2025-08-27 09:39:12', '2025-08-27 09:39:12'),
(101, 'TP-20250827-001', 3, 'Jose Hernandez', '09342673127', NULL, 'General Inquiry', 'priority', 'waiting', 1, '2025-08-27 11:39:12', NULL, NULL, NULL, NULL, NULL, '2025-08-27 09:39:12', '2025-08-27 09:39:12'),
(102, 'GS-20250827-009', 5, 'Carmen Perez', '09458775308', NULL, 'Document Verification', 'normal', 'waiting', 9, '2025-08-27 14:19:12', NULL, NULL, NULL, NULL, NULL, '2025-08-27 09:39:12', '2025-08-27 09:39:12'),
(103, 'TP-20250827-002', 3, 'Francisco Flores', '09556067433', NULL, 'Form Submission', 'priority', 'waiting', 2, '2025-08-27 12:04:12', NULL, NULL, NULL, NULL, NULL, '2025-08-27 09:39:12', '2025-08-27 09:39:12'),
(104, 'BC-20250827-001', 1, 'Ana Torres', '09169580701', NULL, 'Complaint Filing', 'priority', 'waiting', 1, '2025-08-27 11:39:12', NULL, NULL, NULL, NULL, NULL, '2025-08-27 09:39:12', '2025-08-27 09:39:12'),
(105, 'PR-20250827-002', 4, 'Antonio Mendoza', '09607725434', NULL, 'Complaint Filing', 'normal', 'waiting', 2, '2025-08-27 11:49:12', NULL, NULL, NULL, NULL, NULL, '2025-08-27 09:39:12', '2025-08-27 09:39:12'),
(106, 'GS-20250827-010', 5, 'Juan Flores', '09498584063', NULL, 'Registration Update', 'normal', 'waiting', 10, '2025-08-27 14:39:12', NULL, NULL, NULL, NULL, NULL, '2025-08-27 09:39:12', '2025-08-27 09:39:12'),
(107, 'TP-20250827-003', 3, 'Ana Fernandez', '09200638114', NULL, 'Document Verification', 'normal', 'waiting', 3, '2025-08-27 12:29:12', NULL, NULL, NULL, NULL, NULL, '2025-08-27 09:39:12', '2025-08-27 09:39:12'),
(108, 'GS-20250827-011', 5, 'Mar Yvan Sagun Dela Cruz', '09162291763', NULL, 'resident_registration: Resident Census Registration Processing', 'normal', 'waiting', 10, '2025-08-27 18:16:23', NULL, NULL, NULL, NULL, NULL, '2025-08-27 13:16:23', '2025-08-27 13:16:23'),
(109, 'GS-20250827-012', 5, 'Mar Yvan Sagun Dela Cruz', '09162291763', NULL, 'resident_registration: Resident Census Registration Processing', 'normal', 'waiting', 11, '2025-08-27 19:23:17', NULL, NULL, NULL, NULL, NULL, '2025-08-27 14:03:17', '2025-08-27 14:03:17'),
(110, 'GS-20250827-013', 5, 'Mar Yvan Sagun Dela Cruz', '09162291763', NULL, 'resident_registration: Resident Census Registration Processing', 'normal', 'waiting', 12, '2025-08-27 20:08:52', NULL, NULL, NULL, NULL, NULL, '2025-08-27 14:28:52', '2025-08-27 14:28:52');

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
  `house_number` varchar(20) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT 'Gumaoc East',
  `sitio` varchar(100) DEFAULT 'BLOCK',
  `interviewer` varchar(255) DEFAULT NULL,
  `interviewer_title` varchar(255) DEFAULT NULL,
  `birthdate` date NOT NULL,
  `birth_place` varchar(255) DEFAULT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `civil_status` enum('Single','Married','Widowed','Separated','Divorced') NOT NULL,
  `rfid_code` varchar(50) DEFAULT NULL,
  `rfid` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'active',
  `reset_otp` varchar(6) DEFAULT NULL,
  `otp_expiry` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_complete` tinyint(1) DEFAULT 1 COMMENT '1 = Complete Profile, 0 = Incomplete Profile',
  `created_by` int(11) DEFAULT NULL COMMENT 'ID of user who registered this family member',
  `relationship_to_head` varchar(100) DEFAULT NULL COMMENT 'Relationship to head of family'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `residents`
--

INSERT INTO `residents` (`id`, `first_name`, `middle_name`, `last_name`, `email`, `phone`, `password`, `address`, `house_number`, `barangay`, `sitio`, `interviewer`, `interviewer_title`, `birthdate`, `birth_place`, `gender`, `civil_status`, `rfid_code`, `rfid`, `status`, `reset_otp`, `otp_expiry`, `created_at`, `updated_at`, `profile_complete`, `created_by`, `relationship_to_head`) VALUES
(19, 'Mar Yvan', 'Sagun', 'Dela Cruz', 'biofrostyv@gmail.com', '09162291763', '$2y$10$Jfq4WZ.F1xkWLrsJ6QP0Ie0A7Ly9R0DcDXC4aZOaoBdWW52ZPejZq', 'House 101, TEST ME PLEASE, Barangay Gumaoc East, San Jose del Monte, Bulacan, Philippines', '101', 'Gumaoc East', 'BLOCK', 'TEST ME PLEASE', 'TEST ME PLEASE', '2004-07-08', 'Caloocan City', 'Male', 'Single', '0006894001', '0006894001', 'active', NULL, NULL, '2025-08-27 14:28:45', '2025-08-27 14:28:45', 1, NULL, NULL),
(20, 'TEST', 'ME', 'PLEASE', 'testmeplease@test.com', '', '$2y$10$upbHol3uSqFdCnhTisj5TOr9MlGjvMAuaZLiBbkoz1mFkoxLtu9Mq', 'House 101, TEST ME PLEASE, Barangay Gumaoc East, San Jose del Monte, Bulacan, Philippines', '101', 'Gumaoc East', 'BLOCK', 'TEST ME PLEASE', 'TEST ME PLEASE', '2014-07-08', 'Unknown', 'Male', 'Single', '0005810785', '0005810785', 'active', NULL, NULL, '2025-08-27 14:28:45', '2025-08-27 14:28:45', 0, 19, 'Anak');

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
  `birth_place` varchar(255) DEFAULT NULL,
  `age` int(11) NOT NULL,
  `civil_status` varchar(50) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `house_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
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

INSERT INTO `resident_registrations` (`id`, `first_name`, `middle_name`, `last_name`, `birth_date`, `birth_place`, `age`, `civil_status`, `gender`, `contact_number`, `email`, `house_number`, `address`, `pangkabuhayan`, `submitted_at`, `status`, `land_ownership`, `land_ownership_other`, `house_ownership`, `house_ownership_other`, `farmland`, `cooking_energy`, `cooking_energy_other`, `toilet_type`, `toilet_type_other`, `electricity_source`, `electricity_source_other`, `water_source`, `water_source_other`, `waste_disposal`, `waste_disposal_other`, `appliances`, `transportation`, `transportation_other`, `business`, `business_other`, `contraceptive`, `interviewer`, `interviewer_title`) VALUES
(220, 'Mar Yvan', 'Sagun', 'Dela Cruz', '2004-07-08', 'Caloocan City', 21, 'Single', 'Male', '09162291763', 'biofrostyv@gmail.com', '101', 'TEST ME PLEASE', 'Iba pa', '2025-08-27 14:28:45', 'pending', 'Iba pa', 'TEST ME PLEASE', 'Iba pa', 'TEST ME PLEASE', 'Wala', 'Iba pa', 'TEST ME PLEASE', 'Iba pa', 'TEST ME PLEASE', 'Iba pa', 'TEST ME PLEASE', 'Iba pa', 'TEST ME PLEASE', 'Iba pa', 'TEST ME PLEASE', 'Muwebles', 'Iba pa', 'TEST ME PLEASE', 'Iba pa', 'TEST ME PLEASE', 'Wala', 'TEST ME PLEASE', 'TEST ME PLEASE');

-- --------------------------------------------------------

--
-- Table structure for table `resident_status`
--

CREATE TABLE `resident_status` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `resident_name` varchar(255) NOT NULL,
  `record_status` enum('good','minor_issues','major_issues','critical') DEFAULT 'good',
  `total_complaints` int(11) DEFAULT 0,
  `total_incidents` int(11) DEFAULT 0,
  `pending_cases` int(11) DEFAULT 0,
  `resolved_cases` int(11) DEFAULT 0,
  `requires_captain_clearance` tinyint(1) DEFAULT 0,
  `captain_clearance_granted` tinyint(1) DEFAULT 0,
  `captain_clearance_date` datetime DEFAULT NULL,
  `captain_clearance_reason` text DEFAULT NULL,
  `captain_clearance_expires` datetime DEFAULT NULL,
  `last_incident_date` datetime DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `scanned_rfid_codes`
--

CREATE TABLE `scanned_rfid_codes` (
  `id` int(11) NOT NULL,
  `rfid_code` varchar(50) NOT NULL,
  `status` enum('available','assigned','disabled','archived') DEFAULT 'available',
  `scanned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_at` timestamp NULL DEFAULT NULL,
  `assigned_to_resident_id` int(11) DEFAULT NULL,
  `assigned_to_email` varchar(255) DEFAULT NULL,
  `scanned_by_admin_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scanned_rfid_codes`
--

INSERT INTO `scanned_rfid_codes` (`id`, `rfid_code`, `status`, `scanned_at`, `assigned_at`, `assigned_to_resident_id`, `assigned_to_email`, `scanned_by_admin_id`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'TEST001', 'archived', '2025-08-26 11:53:26', NULL, NULL, NULL, NULL, 'Test RFID code for development', '2025-08-26 11:53:26', '2025-08-26 13:05:28'),
(2, 'TEST002', 'archived', '2025-08-26 11:53:26', NULL, NULL, NULL, NULL, 'Test RFID code for development', '2025-08-26 11:53:26', '2025-08-26 13:05:30'),
(3, 'TEST003', 'archived', '2025-08-26 11:53:26', NULL, NULL, NULL, NULL, 'Test RFID code for development', '2025-08-26 11:53:26', '2025-08-26 13:05:32'),
(4, '0006954375', 'assigned', '2025-08-26 13:05:56', '2025-08-26 13:09:35', 10, 'biofrostyv@gmail.com', 1, '', '2025-08-26 13:05:56', '2025-08-26 13:09:35'),
(5, '0005805639', 'assigned', '2025-08-26 13:06:46', '2025-08-27 04:25:22', 15, 'biofrostyv@gmail.com', 1, '', '2025-08-26 13:06:46', '2025-08-27 04:25:22'),
(6, '0006909504', 'assigned', '2025-08-26 13:07:29', '2025-08-27 13:16:23', 16, 'biofrostyv@gmail.com', 1, '', '2025-08-26 13:07:29', '2025-08-27 13:16:23'),
(7, '0006892606', 'assigned', '2025-08-26 13:07:31', '2025-08-27 14:03:10', 17, 'biofrostyv@gmail.com', 1, '', '2025-08-26 13:07:31', '2025-08-27 14:03:10'),
(8, '0005794103', 'assigned', '2025-08-26 13:07:38', '2025-08-27 14:03:10', 18, 'testmeplease@test.com', 1, '', '2025-08-26 13:07:38', '2025-08-27 14:03:10'),
(9, '0006894001', 'assigned', '2025-08-26 13:07:41', '2025-08-27 14:28:45', 19, 'biofrostyv@gmail.com', 1, '', '2025-08-26 13:07:41', '2025-08-27 14:28:45'),
(10, '0005810785', 'assigned', '2025-08-26 13:07:44', '2025-08-27 14:28:45', 20, 'testmeplease@test.com', 1, '', '2025-08-26 13:07:44', '2025-08-27 14:28:45'),
(11, '0005797452', 'available', '2025-08-26 13:07:46', NULL, NULL, NULL, 1, '', '2025-08-26 13:07:46', '2025-08-26 13:07:46'),
(12, '0005845889', 'available', '2025-08-26 13:07:48', NULL, NULL, NULL, 1, '', '2025-08-26 13:07:48', '2025-08-26 13:07:48'),
(13, '0005786280', 'available', '2025-08-26 13:07:50', NULL, NULL, NULL, 1, '', '2025-08-26 13:07:50', '2025-08-26 13:07:50');

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
(3, 'Community Census', 'Register as a resident and contribute to our comprehensive community database.', '????', 'Register', 'pages/forms.php', 0, 'Digital Registry,Secure Data', 0, '2025-08-04 06:22:38', '2025-08-04 06:22:38');

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

-- --------------------------------------------------------

--
-- Table structure for table `user_reports`
--

CREATE TABLE `user_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `incident_type` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `contact_number` varchar(20) NOT NULL,
  `proof_image` varchar(255) DEFAULT NULL COMMENT 'Optional proof image filename for the report',
  `status` enum('pending','processing','completed','rejected') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access_logs`
--
ALTER TABLE `access_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`),
  ADD KEY `form_type` (`form_type`),
  ADD KEY `attempted_at` (`attempted_at`);

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
-- Indexes for table `barangay_blotter`
--
ALTER TABLE `barangay_blotter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blotter_number` (`blotter_number`),
  ADD KEY `complainant_id` (`complainant_id`),
  ADD KEY `respondent_id` (`respondent_id`),
  ADD KEY `incident_date` (`incident_date`),
  ADD KEY `status` (`status`),
  ADD KEY `classification` (`classification`);

--
-- Indexes for table `blotter_attachments`
--
ALTER TABLE `blotter_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blotter_id` (`blotter_id`);

--
-- Indexes for table `business_applications`
--
ALTER TABLE `business_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `captain_clearances`
--
ALTER TABLE `captain_clearances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`),
  ADD KEY `clearance_type` (`clearance_type`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `certificate_requests`
--
ALTER TABLE `certificate_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_queue_ticket` (`queue_ticket_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_certificate_type` (`certificate_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_submitted_at` (`submitted_at`);

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
  ADD KEY `idx_residents_rfid_codes` (`rfid_code`,`rfid`),
  ADD KEY `idx_house_number` (`house_number`),
  ADD KEY `idx_interviewer` (`interviewer`),
  ADD KEY `idx_birth_place` (`birth_place`),
  ADD KEY `fk_residents_created_by` (`created_by`);

--
-- Indexes for table `resident_registrations`
--
ALTER TABLE `resident_registrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `resident_status`
--
ALTER TABLE `resident_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `resident_id` (`resident_id`),
  ADD KEY `record_status` (`record_status`),
  ADD KEY `requires_captain_clearance` (`requires_captain_clearance`);

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
-- Indexes for table `scanned_rfid_codes`
--
ALTER TABLE `scanned_rfid_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rfid_code` (`rfid_code`),
  ADD KEY `idx_rfid_code` (`rfid_code`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_scanned_at` (`scanned_at`),
  ADD KEY `scanned_by_admin_id` (`scanned_by_admin_id`);

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
-- Indexes for table `user_reports`
--
ALTER TABLE `user_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access_logs`
--
ALTER TABLE `access_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `barangay_blotter`
--
ALTER TABLE `barangay_blotter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blotter_attachments`
--
ALTER TABLE `blotter_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `business_applications`
--
ALTER TABLE `business_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `captain_clearances`
--
ALTER TABLE `captain_clearances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `certificate_requests`
--
ALTER TABLE `certificate_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `family_disabilities`
--
ALTER TABLE `family_disabilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `family_members`
--
ALTER TABLE `family_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=244;

--
-- AUTO_INCREMENT for table `family_organizations`
--
ALTER TABLE `family_organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=220;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `queue_windows`
--
ALTER TABLE `queue_windows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `resident_registrations`
--
ALTER TABLE `resident_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=221;

--
-- AUTO_INCREMENT for table `resident_status`
--
ALTER TABLE `resident_status`
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
-- AUTO_INCREMENT for table `scanned_rfid_codes`
--
ALTER TABLE `scanned_rfid_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
-- AUTO_INCREMENT for table `user_reports`
--
ALTER TABLE `user_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `access_logs`
--
ALTER TABLE `access_logs`
  ADD CONSTRAINT `access_logs_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `blotter_attachments`
--
ALTER TABLE `blotter_attachments`
  ADD CONSTRAINT `blotter_attachments_ibfk_1` FOREIGN KEY (`blotter_id`) REFERENCES `barangay_blotter` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `business_applications`
--
ALTER TABLE `business_applications`
  ADD CONSTRAINT `business_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `captain_clearances`
--
ALTER TABLE `captain_clearances`
  ADD CONSTRAINT `captain_clearances_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `certificate_requests`
--
ALTER TABLE `certificate_requests`
  ADD CONSTRAINT `certificate_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `residents` (`id`),
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
-- Constraints for table `residents`
--
ALTER TABLE `residents`
  ADD CONSTRAINT `fk_residents_created_by` FOREIGN KEY (`created_by`) REFERENCES `residents` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `resident_status`
--
ALTER TABLE `resident_status`
  ADD CONSTRAINT `resident_status_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rfid_access_logs`
--
ALTER TABLE `rfid_access_logs`
  ADD CONSTRAINT `rfid_access_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `rfid_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scanned_rfid_codes`
--
ALTER TABLE `scanned_rfid_codes`
  ADD CONSTRAINT `scanned_rfid_codes_ibfk_1` FOREIGN KEY (`scanned_by_admin_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_reports`
--
ALTER TABLE `user_reports`
  ADD CONSTRAINT `user_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
