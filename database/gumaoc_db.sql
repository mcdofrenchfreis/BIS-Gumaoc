-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 24, 2025 at 03:13 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u138614204_gumaoc_db`
--

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`u138614204_gumaoc`@`127.0.0.1` FUNCTION `update_resident_status` (`resident_id_param` INT) RETURNS VARCHAR(20) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC READS SQL DATA BEGIN
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
(84, 'admin', 'form_view', 'resident_registration', 220, 'Viewed resident registration form ID #220', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 14:29:03'),
(85, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:07:57'),
(86, 'admin', 'status_update', 'resident_registration', 220, 'Updated resident_registration ID #220 status from \'pending\' to \'approved\'', '{\"old_status\":\"pending\",\"new_status\":\"approved\",\"timestamp\":\"2025-08-27 20:14:48\",\"applicant_name\":\"Mar Yvan Dela Cruz\",\"registration_type\":\"resident_registration\",\"processing_time\":\"2025-08-27 20:14:48\",\"admin_action\":true,\"email_sent\":false,\"email_address\":\"biofrostyv@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:14:48'),
(87, 'admin', 'status_update', 'resident_registration', 220, 'Updated resident_registration ID #220 status from \'pending\' to \'approved\'', '{\"old_status\":\"pending\",\"new_status\":\"approved\",\"timestamp\":\"2025-08-27 20:16:02\",\"applicant_name\":\"Mar Yvan Dela Cruz\",\"registration_type\":\"resident_registration\",\"processing_time\":\"2025-08-27 20:16:02\",\"admin_action\":true,\"email_sent\":false,\"email_address\":\"biofrostyv@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:16:02'),
(88, 'admin', 'status_update', 'resident_registration', 220, 'Updated resident_registration ID #220 status from \'pending\' to \'approved\'', '{\"old_status\":\"pending\",\"new_status\":\"approved\",\"timestamp\":\"2025-08-27 20:24:07\",\"applicant_name\":\"Mar Yvan Dela Cruz\",\"registration_type\":\"resident_registration\",\"processing_time\":\"2025-08-27 20:24:07\",\"admin_action\":true,\"email_sent\":true,\"email_address\":\"biofrostyv@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:24:07'),
(89, 'admin', 'status_update', 'resident_registration', 220, 'Updated resident_registration ID #220 status from \'pending\' to \'approved\'', '{\"old_status\":\"pending\",\"new_status\":\"approved\",\"timestamp\":\"2025-08-27 20:25:28\",\"applicant_name\":\"Mar Yvan Dela Cruz\",\"registration_type\":\"resident_registration\",\"processing_time\":\"2025-08-27 20:25:28\",\"admin_action\":true,\"email_sent\":true,\"email_address\":\"biofrostyv@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:25:28'),
(90, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:26:33'),
(91, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:26:39'),
(92, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:31:17'),
(93, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:39:40'),
(94, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:39:42'),
(95, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:39:46'),
(96, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:41:50'),
(97, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:41:57'),
(98, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:46:21'),
(99, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:46:26'),
(100, 'admin', 'print_action', 'certificate_request', 35, 'Printed indigency_certificate for certificate_request ID #35', '{\"print_type\":\"indigency_certificate\",\"print_timestamp\":\"2025-08-27 20:47:39\",\"certificate_type\":\"BRGY. INDIGENCY\",\"applicant_name\":\"Juan Las Santos\",\"certificate_number\":\"IND-00035-2025\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:47:39'),
(101, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:47:54'),
(102, 'admin', 'print_action', 'certificate_request', 35, 'Printed indigency_certificate for certificate_request ID #35', '{\"print_type\":\"indigency_certificate\",\"print_timestamp\":\"2025-08-27 20:48:14\",\"certificate_type\":\"BRGY. INDIGENCY\",\"applicant_name\":\"Juan Las Santos\",\"certificate_number\":\"IND-00035-2025\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:48:14'),
(103, 'admin', 'print_action', 'certificate_request', 27, 'Printed barangay_clearance for certificate_request ID #27', '{\"print_type\":\"barangay_clearance\",\"print_timestamp\":\"2025-08-27 20:49:46\",\"certificate_type\":\"BRGY. CLEARANCE\",\"applicant_name\":\"Ana Santa Tomas\",\"certificate_number\":\"BC-00027-2025\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:49:46'),
(104, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:53:52'),
(105, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:56:03'),
(106, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:59:35'),
(107, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 18:59:40'),
(108, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:00:48'),
(109, 'admin', 'print_action', 'certificate_request', 27, 'Printed barangay_clearance for certificate_request ID #27', '{\"print_type\":\"barangay_clearance\",\"print_timestamp\":\"2025-08-27 21:00:52\",\"certificate_type\":\"BRGY. CLEARANCE\",\"applicant_name\":\"Ana Santa Tomas\",\"certificate_number\":\"BC-00027-2025\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:00:52'),
(110, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:00:53'),
(111, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:01:07'),
(112, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:05:02'),
(113, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:05:07'),
(114, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:05:10'),
(115, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:05:24'),
(116, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:05:24'),
(117, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:05:25'),
(118, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:05:29'),
(119, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:05:31'),
(120, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:05:33'),
(121, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:08:19'),
(122, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:08:37'),
(123, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:08:37'),
(124, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:08:38'),
(125, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:08:39'),
(126, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:08:39'),
(127, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:08:41'),
(128, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:08:42'),
(129, 'admin', 'status_update', 'certificate_request', 28, 'Updated certificate_request ID #28 status from \'pending\' to \'processing\'', '{\"old_status\":\"pending\",\"new_status\":\"processing\",\"timestamp\":\"2025-08-27 21:09:05\",\"certificate_type\":\"BRGY. INDIGENCY\",\"applicant_name\":\"Rosa Delos Villanueva\",\"queue_ticket_id\":null,\"queue_status_updated\":\"no_queue_ticket\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:09:05'),
(130, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:09:05'),
(131, 'admin', 'status_update', 'certificate_request', 35, 'Updated certificate_request ID #35 status from \'processing\' to \'ready\'', '{\"old_status\":\"processing\",\"new_status\":\"ready\",\"timestamp\":\"2025-08-27 21:09:09\",\"certificate_type\":\"BRGY. INDIGENCY\",\"applicant_name\":\"Juan Las Santos\",\"queue_ticket_id\":null,\"queue_status_updated\":\"no_queue_ticket\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:09:09'),
(132, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:09:09'),
(133, 'admin', 'status_update', 'certificate_request', 35, 'Updated certificate_request ID #35 status from \'ready\' to \'released\'', '{\"old_status\":\"ready\",\"new_status\":\"released\",\"timestamp\":\"2025-08-27 21:09:12\",\"certificate_type\":\"BRGY. INDIGENCY\",\"applicant_name\":\"Juan Las Santos\",\"queue_ticket_id\":null,\"queue_status_updated\":\"no_queue_ticket\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:09:12'),
(134, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:09:12'),
(135, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:09:15'),
(136, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:09:24'),
(137, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:10:34'),
(138, 'admin', 'print_action', 'certificate_request', 27, 'Printed barangay_clearance for certificate_request ID #27', '{\"print_type\":\"barangay_clearance\",\"print_timestamp\":\"2025-08-27 21:10:36\",\"certificate_type\":\"BRGY. CLEARANCE\",\"applicant_name\":\"Ana Santa Tomas\",\"certificate_number\":\"BC-00027-2025\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:10:36'),
(139, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 19:10:37'),
(140, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '112.211.7.167', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 20:10:35'),
(141, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '110.54.166.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 21:55:17'),
(142, 'admin', 'form_view', 'resident_registration', 281, 'Viewed resident registration form ID #281', NULL, '110.54.166.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 21:56:19'),
(143, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '131.226.97.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 21:56:42'),
(144, 'admin', 'status_update', 'resident_registration', 281, 'Updated resident_registration ID #281 status from \'pending\' to \'approved\'', '{\"old_status\":\"pending\",\"new_status\":\"approved\",\"timestamp\":\"2025-08-27 21:57:12\",\"applicant_name\":\"Mar Yvan Dela Cruz\",\"registration_type\":\"resident_registration\",\"processing_time\":\"2025-08-27 21:57:12\",\"admin_action\":true,\"email_sent\":true,\"email_address\":\"biofrostyv@gmail.com\"}', '110.54.166.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 21:57:12'),
(145, 'admin', 'admin_logout', 'admin_auth', NULL, 'Admin logout for username: admin', '{\"username\":\"admin\"}', '110.54.166.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 22:01:02'),
(146, 'admin', 'status_update', 'resident_registration', 282, 'Updated resident_registration ID #282 status from \'pending\' to \'approved\'', '{\"old_status\":\"pending\",\"new_status\":\"approved\",\"timestamp\":\"2025-08-27 22:08:15\",\"applicant_name\":\"Mar Yvan Dela Cruz\",\"registration_type\":\"resident_registration\",\"processing_time\":\"2025-08-27 22:08:15\",\"admin_action\":true,\"email_sent\":true,\"email_address\":\"biofrostyv@gmail.com\"}', '131.226.97.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 22:08:15'),
(147, 'admin', 'status_update', 'resident_registration', 283, 'Updated resident_registration ID #283 status from \'pending\' to \'approved\'', '{\"old_status\":\"pending\",\"new_status\":\"approved\",\"timestamp\":\"2025-08-27 22:17:33\",\"applicant_name\":\"Archie Verania\",\"registration_type\":\"resident_registration\",\"processing_time\":\"2025-08-27 22:17:33\",\"admin_action\":true,\"email_sent\":true,\"email_address\":\"archieverania1130@gmail.com\"}', '131.226.97.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 22:17:33'),
(148, 'admin', 'status_update', 'resident_registration', 284, 'Updated resident_registration ID #284 status from \'pending\' to \'approved\'', '{\"old_status\":\"pending\",\"new_status\":\"approved\",\"timestamp\":\"2025-08-27 22:22:42\",\"applicant_name\":\"Job Matthew Bernardo\",\"registration_type\":\"resident_registration\",\"processing_time\":\"2025-08-27 22:22:42\",\"admin_action\":true,\"email_sent\":true,\"email_address\":\"jobmatthewbernardo@gmail.com\"}', '131.226.97.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 22:22:42'),
(149, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '131.226.97.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 22:34:37'),
(150, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '131.226.97.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 22:34:43'),
(151, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '131.226.97.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 22:37:10'),
(152, 'admin', 'print_action', 'certificate_request', 38, 'Printed barangay_clearance for certificate_request ID #38', '{\"print_type\":\"barangay_clearance\",\"print_timestamp\":\"2025-08-27 22:37:20\",\"certificate_type\":\"BRGY. CLEARANCE\",\"applicant_name\":\"Teresa Santa Aquino\",\"certificate_number\":\"BC-00038-2025\"}', '131.226.97.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 22:37:20'),
(153, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '131.226.97.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 22:37:37'),
(154, 'admin', 'print_action', 'certificate_request', 22, 'Printed residency_certificate for certificate_request ID #22', '{\"print_type\":\"residency_certificate\",\"print_timestamp\":\"2025-08-27 22:38:22\",\"certificate_type\":\"CERTIFICATION OF RESIDENCY\",\"applicant_name\":\"Carmen Santa Aguilar\",\"certificate_number\":\"RES-00022-2025\"}', '131.226.97.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 22:38:22'),
(155, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '131.226.97.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 22:38:38'),
(156, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '131.226.97.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 22:40:31'),
(157, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '131.226.97.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 22:40:59'),
(158, 'admin', 'print_action', 'certificate_request', 21, 'Printed cedula_certificate for certificate_request ID #21', '{\"print_type\":\"cedula_certificate\",\"print_timestamp\":\"2025-08-27 22:41:02\",\"certificate_type\":\"CEDULA\\/CTC\",\"applicant_name\":\"Pedro Las Andres\",\"certificate_number\":\"CTC202500000021\"}', '131.226.97.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 22:41:02'),
(159, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '131.226.97.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 22:41:15'),
(160, 'admin', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '131.226.97.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 22:41:24'),
(161, 'admin', 'admin_logout', 'admin_auth', NULL, 'Admin logout for username: admin', '{\"username\":\"admin\"}', '131.226.97.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 22:41:53'),
(162, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '110.54.166.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 23:04:51'),
(163, 'admin', 'form_view', 'resident_registration', 306, 'Viewed resident registration form ID #306', NULL, '110.54.166.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 23:44:36'),
(164, 'admin', 'form_view', 'resident_registration', 306, 'Viewed resident registration form ID #306', NULL, '110.54.166.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 23:47:52'),
(165, 'admin', 'status_update', 'resident_registration', 306, 'Updated resident_registration ID #306 status from \'pending\' to \'approved\'', '{\"old_status\":\"pending\",\"new_status\":\"approved\",\"timestamp\":\"2025-08-27 23:48:20\",\"applicant_name\":\"Yvan Dela Cruz\",\"registration_type\":\"resident_registration\",\"processing_time\":\"2025-08-27 23:48:20\",\"admin_action\":true,\"email_sent\":true,\"email_address\":\"lanmimiami1923@gmail.com\"}', '110.54.166.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-27 23:48:20'),
(166, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '112.211.15.247', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-13 09:59:50'),
(167, 'admin', 'form_view', 'resident_registration', 306, 'Viewed resident registration form ID #306', NULL, '112.211.15.247', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-13 09:59:58'),
(168, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '112.211.15.247', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 12:33:54');
INSERT INTO `admin_logs` (`id`, `admin_id`, `action_type`, `target_type`, `target_id`, `description`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(169, 'admin', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin', '{\"username\":\"admin\",\"success\":true}', '112.211.15.247', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 01:41:50');

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

--
-- Dumping data for table `business_applications`
--

INSERT INTO `business_applications` (`id`, `user_id`, `reference_no`, `application_date`, `first_name`, `middle_name`, `last_name`, `business_location`, `or_number`, `ctc_number`, `business_name`, `business_type`, `business_address`, `business_description`, `capital_amount`, `owner_name`, `owner_address`, `owner_contact`, `contact_number`, `years_operation`, `investment_capital`, `proof_image`, `submitted_at`, `status`) VALUES
(3, 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Aquino Family Trading', 'Trading', 'House 257, Luna Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Fernando Ng Aquino', 'House 77, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09120311022', 14, 185433.00, NULL, '2025-07-25 12:39:25', 'rejected'),
(4, 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Bautista Family General Merchandise', 'General Merchandise', 'House 486, Del Pilar Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Fernando Del Bautista', 'House 371, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09547163935', 6, 328775.00, NULL, '2025-08-19 12:39:25', 'rejected'),
(5, 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Aguilar Family Trading', 'Trading', 'House 407, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Jose Ng Aguilar', 'House 112, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09425753589', 12, 133485.00, NULL, '2025-08-26 12:39:25', 'reviewing'),
(6, 19, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mendoza Family Trading', 'Trading', 'House 31, Bonifacio Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Carmen Ng Mendoza', 'House 35, Bonifacio Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09249443854', 8, 77150.00, NULL, '2025-08-17 12:39:25', 'rejected'),
(7, 19, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mercado Family Services', 'Services', 'House 303, Luna Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Carlos Ng Mercado', 'House 92, Luna Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09237068237', 11, 184879.00, NULL, '2025-07-31 12:39:25', 'pending'),
(8, 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Santos Family Sari-Sari Store', 'Sari-Sari Store', 'House 320, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Luz Del Santos', 'House 376, Magsaysay Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09255439404', 13, 67665.00, NULL, '2025-08-26 12:39:25', 'approved'),
(9, 19, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Bautista Family Sari-Sari Store', 'Sari-Sari Store', 'House 386, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Antonio San Bautista', 'House 45, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09828692295', 1, 469627.00, NULL, '2025-07-26 12:39:25', 'pending'),
(10, 19, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Rivera Family Trading', 'Trading', 'House 188, Luna Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Esperanza Dela Rivera', 'House 297, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09567040854', 10, 300052.00, NULL, '2025-07-15 12:39:25', 'reviewing'),
(11, 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Aquino Family Food Establishment', 'Food Establishment', 'House 105, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Antonio Delos Aquino', 'House 171, Magsaysay Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09382523124', 12, 452989.00, NULL, '2025-07-30 12:39:25', 'rejected'),
(12, 19, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Rivera Family Trading', 'Trading', 'House 489, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Carlos Santa Rivera', 'House 498, Magsaysay Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09581775044', 12, 10252.00, NULL, '2025-07-30 12:39:25', 'rejected'),
(13, 19, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Tomas Family Trading', 'Trading', 'House 396, Luna Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Carlos Las Tomas', 'House 302, Del Pilar Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09802358413', 8, 58329.00, NULL, '2025-08-26 12:39:25', 'pending'),
(14, 19, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Bautista Family Sari-Sari Store', 'Sari-Sari Store', 'House 3, Del Pilar Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Miguel De Bautista', 'House 168, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09120452021', 13, 253349.00, NULL, '2025-08-23 12:39:25', 'pending'),
(15, 19, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Castillo Family General Merchandise', 'General Merchandise', 'House 308, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Isabel Santa Castillo', 'House 471, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09253939187', 1, 280489.00, NULL, '2025-08-10 12:39:25', 'pending'),
(16, 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Castillo Family Services', 'Services', 'House 112, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Ana Dela Castillo', 'House 260, Magsaysay Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09870534126', 5, 478645.00, NULL, '2025-08-23 12:39:25', 'pending'),
(17, 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Bautista Family Sari-Sari Store', 'Sari-Sari Store', 'House 348, Bonifacio Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Carlos Ng Bautista', 'House 115, Luna Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09701262105', 14, 390339.00, NULL, '2025-07-23 12:39:25', 'rejected'),
(18, 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Marquez Family Trading', 'Trading', 'House 99, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Antonio Santa Marquez', 'House 153, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09399976791', 7, 148798.00, NULL, '2025-07-28 12:39:25', 'reviewing'),
(19, 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mercado Family Food Establishment', 'Food Establishment', 'House 51, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Juan Del Mercado', 'House 373, Del Pilar Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09683676016', 11, 101291.00, NULL, '2025-08-27 12:39:25', 'reviewing'),
(20, 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ramos Family Services', 'Services', 'House 221, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Antonio Dela Ramos', 'House 312, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09553051111', 7, 457546.00, NULL, '2025-07-26 12:39:25', 'pending'),
(21, 19, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Torres Family Services', 'Services', 'House 370, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Elena Mga Torres', 'House 482, Bonifacio Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09718545184', 3, 213023.00, NULL, '2025-08-22 12:39:25', 'reviewing'),
(22, 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Tomas Family Food Establishment', 'Food Establishment', 'House 430, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Rosa Mga Tomas', 'House 421, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09148267748', 15, 129866.00, NULL, '2025-08-17 12:39:25', 'approved'),
(23, 26, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mendoza Family Food Establishment', 'Food Establishment', 'House 39, Del Pilar Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Fernando Los Mendoza', 'House 439, Luna Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09204554627', 10, 69088.00, NULL, '2025-07-22 22:34:11', 'pending'),
(24, 24, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Castillo Family Services', 'Services', 'House 497, Luna Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Jose Las Castillo', 'House 298, Luna Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09801224595', 12, 207539.00, NULL, '2025-08-24 22:34:11', 'rejected'),
(25, 23, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Santos Family General Merchandise', 'General Merchandise', 'House 407, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Esperanza Santa Santos', 'House 133, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09440743801', 2, 388016.00, NULL, '2025-07-17 22:34:11', 'reviewing'),
(26, 27, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Tomas Family Food Establishment', 'Food Establishment', 'House 210, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Luz De Tomas', 'House 412, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09361645908', 3, 264832.00, NULL, '2025-08-14 22:34:11', 'reviewing'),
(27, 24, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Bautista Family Food Establishment', 'Food Establishment', 'House 453, Luna Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Fernando Ng Bautista', 'House 346, Del Pilar Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09111839156', 15, 417132.00, NULL, '2025-07-30 22:34:11', 'pending'),
(28, 25, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mendoza Family Services', 'Services', 'House 416, Magsaysay Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Teresa San Mendoza', 'House 115, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09271835003', 5, 241893.00, NULL, '2025-08-02 22:34:11', 'rejected'),
(29, 27, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dela Cruz Family General Merchandise', 'General Merchandise', 'House 457, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Roberto Dela Dela Cruz', 'House 459, Magsaysay Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09429135729', 3, 108550.00, NULL, '2025-08-08 22:34:11', 'rejected'),
(30, 23, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Castillo Family Trading', 'Trading', 'House 497, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Luz Santa Castillo', 'House 113, Del Pilar Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09380386252', 1, 424327.00, NULL, '2025-07-16 22:34:11', 'rejected'),
(31, 23, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ocampo Family General Merchandise', 'General Merchandise', 'House 190, Bonifacio Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Miguel Santa Ocampo', 'House 119, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09504510148', 6, 62983.00, NULL, '2025-08-02 22:34:11', 'approved'),
(32, 27, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Torres Family Sari-Sari Store', 'Sari-Sari Store', 'House 361, Magsaysay Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Ana San Torres', 'House 102, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09127222483', 6, 263048.00, NULL, '2025-08-22 22:34:11', 'approved'),
(33, 27, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dela Cruz Family Sari-Sari Store', 'Sari-Sari Store', 'House 127, Magsaysay Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Carlos San Dela Cruz', 'House 223, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09481716202', 11, 17545.00, NULL, '2025-07-24 22:34:11', 'pending'),
(34, 23, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dela Cruz Family Services', 'Services', 'House 150, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Rosa Santa Dela Cruz', 'House 210, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09122654668', 9, 37252.00, NULL, '2025-08-21 22:34:11', 'approved'),
(35, 23, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Tomas Family Services', 'Services', 'House 102, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Rosa De Tomas', 'House 9, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09117580587', 15, 82360.00, NULL, '2025-08-12 22:34:11', 'pending'),
(36, 26, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Aquino Family Trading', 'Trading', 'House 433, Luna Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Carmen Los Aquino', 'House 212, Luna Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09707455118', 3, 463507.00, NULL, '2025-08-25 22:34:11', 'approved'),
(37, 23, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Marquez Family Sari-Sari Store', 'Sari-Sari Store', 'House 213, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Manuel Mga Marquez', 'House 334, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09806508757', 2, 17867.00, NULL, '2025-07-14 22:34:11', 'approved'),
(38, 25, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Bautista Family Food Establishment', 'Food Establishment', 'House 12, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Jose Dela Bautista', 'House 148, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09117899017', 8, 481710.00, NULL, '2025-08-16 22:34:11', 'approved'),
(39, 26, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Tomas Family Sari-Sari Store', 'Sari-Sari Store', 'House 165, Magsaysay Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Pedro Ng Tomas', 'House 53, Del Pilar Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09626869658', 12, 479637.00, NULL, '2025-08-03 22:34:11', 'approved'),
(40, 24, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Andres Family Food Establishment', 'Food Establishment', 'House 36, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Manuel Delos Andres', 'House 184, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09881540516', 11, 397211.00, NULL, '2025-07-27 22:34:11', 'approved'),
(41, 23, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Tomas Family Sari-Sari Store', 'Sari-Sari Store', 'House 315, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Carlos Santa Tomas', 'House 21, Del Pilar Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09612746001', 12, 288005.00, NULL, '2025-08-04 22:34:11', 'pending'),
(42, 26, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mendoza Family Services', 'Services', 'House 411, Magsaysay Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, NULL, 'Juan Los Mendoza', 'House 98, Bonifacio Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', NULL, '09380447118', 14, 309296.00, NULL, '2025-08-12 22:34:11', 'approved');

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
  `photo_2x2` varchar(255) DEFAULT NULL COMMENT 'Optional 2x2 passport photo filename for specific certificate types',
  `vehicle_make_type` varchar(255) DEFAULT NULL,
  `motor_no` varchar(100) DEFAULT NULL,
  `chassis_no` varchar(100) DEFAULT NULL,
  `plate_no` varchar(50) DEFAULT NULL,
  `vehicle_color` varchar(50) DEFAULT NULL,
  `year_model` int(4) DEFAULT NULL,
  `body_no` varchar(100) DEFAULT NULL,
  `operator_license` varchar(100) DEFAULT NULL,
  `tricycle_photo` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','processing','ready','released') DEFAULT 'pending',
  `queue_ticket_id` int(11) DEFAULT NULL,
  `queue_ticket_number` varchar(20) DEFAULT NULL,
  `notes` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificate_requests`
--

INSERT INTO `certificate_requests` (`id`, `user_id`, `full_name`, `address`, `mobile_number`, `civil_status`, `gender`, `birth_date`, `birth_place`, `citizenship`, `years_of_residence`, `certificate_type`, `purpose`, `additional_data`, `proof_image`, `photo_2x2`, `vehicle_make_type`, `motor_no`, `chassis_no`, `plate_no`, `vehicle_color`, `year_model`, `body_no`, `operator_license`, `tricycle_photo`, `submitted_at`, `status`, `queue_ticket_id`, `queue_ticket_number`, `notes`) VALUES
(17, 19, 'Antonio Ng Castillo', 'House 127, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09488593874', 'Widowed', 'Female', '1999-01-16', 'San Jose del Monte, Bulacan', 'Filipino', 26, 'BRGY. ID', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-10 12:39:25', 'pending', NULL, NULL, ''),
(18, 19, 'Carlos Ng Castillo', 'House 173, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09223352560', 'Widowed', 'Male', '1986-07-09', 'San Jose del Monte, Bulacan', 'Filipino', 13, 'CEDULA', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-28 12:39:25', 'pending', NULL, NULL, ''),
(19, 19, 'Isabel Las Ocampo', 'House 104, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09575902076', 'Widowed', 'Male', '1963-12-27', 'San Jose del Monte, Bulacan', 'Filipino', 23, 'BRGY. CLEARANCE', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-14 12:39:25', 'processing', NULL, NULL, ''),
(20, 19, 'Pedro Delos Romualdez', 'House 168, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09206442465', 'Widowed', 'Female', '1986-03-15', 'San Jose del Monte, Bulacan', 'Filipino', 16, 'BRGY. INDIGENCY', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 12:39:25', 'processing', NULL, NULL, ''),
(21, 20, 'Pedro Las Andres', 'House 170, Del Pilar Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09274883888', 'Separated', 'Male', '1963-12-04', 'San Jose del Monte, Bulacan', 'Filipino', 16, 'CEDULA', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-09 12:39:25', 'processing', NULL, NULL, ''),
(22, 19, 'Carmen Santa Aguilar', 'House 228, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09570465186', 'Married', 'Male', '1975-04-07', 'San Jose del Monte, Bulacan', 'Filipino', 28, 'PROOF OF RESIDENCY', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-05 12:39:25', 'processing', NULL, NULL, ''),
(23, 20, 'Miguel Las Reyes', 'House 182, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09767103209', 'Single', 'Female', '1987-11-15', 'San Jose del Monte, Bulacan', 'Filipino', 23, 'BRGY. ID', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-14 12:39:25', 'ready', NULL, NULL, ''),
(24, 20, 'Juan Santa Torres', 'House 360, Bonifacio Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09376626756', 'Widowed', 'Male', '1964-07-18', 'San Jose del Monte, Bulacan', 'Filipino', 28, 'BRGY. CLEARANCE', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-29 12:39:25', 'pending', NULL, NULL, ''),
(25, 19, 'Juan Dela Cruz', 'House 194, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09408923564', 'Widowed', 'Female', '1961-12-08', 'San Jose del Monte, Bulacan', 'Filipino', 6, 'PROOF OF RESIDENCY', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-14 12:39:25', 'pending', NULL, NULL, ''),
(26, 20, 'Maria Delos Bautista', 'House 237, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09818627579', 'Married', 'Male', '1971-09-19', 'San Jose del Monte, Bulacan', 'Filipino', 23, 'CEDULA', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-10 12:39:25', 'released', NULL, NULL, ''),
(27, 19, 'Ana Santa Tomas', 'House 461, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09143775585', 'Single', 'Female', '1955-03-21', 'San Jose del Monte, Bulacan', 'Filipino', 11, 'BRGY. CLEARANCE', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-14 12:39:25', 'processing', NULL, NULL, ''),
(28, 19, 'Rosa Delos Villanueva', 'House 357, Bonifacio Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09933097578', 'Widowed', 'Male', '1985-12-26', 'San Jose del Monte, Bulacan', 'Filipino', 17, 'BRGY. INDIGENCY', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-24 12:39:25', 'processing', NULL, NULL, ''),
(29, 19, 'Carmen Las Mendoza', 'House 467, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09567120240', 'Married', 'Male', '1975-01-03', 'San Jose del Monte, Bulacan', 'Filipino', 29, 'BRGY. INDIGENCY', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-26 12:39:25', 'ready', NULL, NULL, ''),
(30, 20, 'Teresa Las Aquino', 'House 405, Magsaysay Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09680355570', 'Separated', 'Male', '1970-10-01', 'San Jose del Monte, Bulacan', 'Filipino', 30, 'BRGY. ID', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-28 12:39:25', 'released', NULL, NULL, ''),
(31, 19, 'Miguel De Bautista', 'House 18, Magsaysay Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09492361485', 'Widowed', 'Female', '1963-01-14', 'San Jose del Monte, Bulacan', 'Filipino', 24, 'BRGY. INDIGENCY', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-27 12:39:25', 'ready', NULL, NULL, ''),
(32, 20, 'Miguel De Marquez', 'House 356, Luna Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09193056824', 'Married', 'Female', '1983-08-19', 'San Jose del Monte, Bulacan', 'Filipino', 7, 'CEDULA', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-20 12:39:25', 'released', NULL, NULL, ''),
(33, 19, 'Manuel Mga Mendoza', 'House 351, Del Pilar Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09179517833', 'Widowed', 'Male', '1957-04-07', 'San Jose del Monte, Bulacan', 'Filipino', 11, 'CEDULA', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 12:39:25', 'ready', NULL, NULL, ''),
(34, 19, 'Manuel Del Mercado', 'House 415, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09285941152', 'Married', 'Female', '1983-10-27', 'San Jose del Monte, Bulacan', 'Filipino', 8, 'PROOF OF RESIDENCY', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-24 12:39:25', 'released', NULL, NULL, ''),
(35, 20, 'Juan Las Santos', 'House 350, Bonifacio Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09282222306', 'Widowed', 'Female', '1987-01-10', 'San Jose del Monte, Bulacan', 'Filipino', 15, 'BRGY. INDIGENCY', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-26 12:39:25', 'released', NULL, NULL, ''),
(36, 20, 'Francisco De Santos', 'House 323, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09229682857', 'Separated', 'Female', '1978-08-20', 'San Jose del Monte, Bulacan', 'Filipino', 19, 'BRGY. INDIGENCY', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-09 12:39:25', 'pending', NULL, NULL, ''),
(37, 23, 'Ana San Santos', 'House 468, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09406003657', 'Widowed', 'Male', '2000-02-07', 'San Jose del Monte, Bulacan', 'Filipino', 17, 'CEDULA', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-19 22:34:11', 'released', NULL, NULL, ''),
(38, 26, 'Teresa Santa Aquino', 'House 159, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09207716925', 'Married', 'Male', '1985-08-01', 'San Jose del Monte, Bulacan', 'Filipino', 28, 'BRGY. CLEARANCE', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-16 22:34:11', 'processing', NULL, NULL, ''),
(39, 27, 'Esperanza De Ocampo', 'House 102, Bonifacio Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09631177894', 'Single', 'Female', '2000-07-09', 'San Jose del Monte, Bulacan', 'Filipino', 20, 'CEDULA', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-01 22:34:11', 'released', NULL, NULL, ''),
(40, 26, 'Miguel Las Dela Cruz', 'House 89, Bonifacio Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09694000282', 'Separated', 'Male', '2004-11-11', 'San Jose del Monte, Bulacan', 'Filipino', 9, 'PROOF OF RESIDENCY', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-03 22:34:11', 'pending', NULL, NULL, ''),
(41, 23, 'Carlos Delos Tomas', 'House 28, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09330376779', 'Separated', 'Female', '1962-02-05', 'San Jose del Monte, Bulacan', 'Filipino', 12, 'BRGY. CLEARANCE', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-21 22:34:11', 'released', NULL, NULL, ''),
(42, 23, 'Carmen Ng Bautista', 'House 335, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09284733839', 'Married', 'Male', '1956-06-08', 'San Jose del Monte, Bulacan', 'Filipino', 5, 'BRGY. CLEARANCE', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-07 22:34:11', 'ready', NULL, NULL, ''),
(43, 25, 'Rosa Mga Villanueva', 'House 453, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09405637105', 'Single', 'Male', '1966-04-22', 'San Jose del Monte, Bulacan', 'Filipino', 1, 'CEDULA', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-17 22:34:11', 'processing', NULL, NULL, ''),
(44, 23, 'Manuel San Cruz', 'House 185, Del Pilar Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09603697934', 'Separated', 'Female', '1958-07-18', 'San Jose del Monte, Bulacan', 'Filipino', 15, 'BRGY. ID', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-27 22:34:11', 'released', NULL, NULL, ''),
(45, 24, 'Rosa Ng Ocampo', 'House 465, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09682716165', 'Separated', 'Male', '1966-08-11', 'San Jose del Monte, Bulacan', 'Filipino', 26, 'BRGY. CLEARANCE', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-11 22:34:11', 'ready', NULL, NULL, ''),
(46, 24, 'Miguel Mga Mercado', 'House 298, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09361573963', 'Single', 'Male', '1966-08-05', 'San Jose del Monte, Bulacan', 'Filipino', 20, 'CEDULA', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-26 22:34:11', 'released', NULL, NULL, ''),
(47, 23, 'Maria Las Romualdez', 'House 279, Bonifacio Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09735941355', 'Widowed', 'Male', '1978-07-12', 'San Jose del Monte, Bulacan', 'Filipino', 30, 'BRGY. CLEARANCE', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-29 22:34:11', 'processing', NULL, NULL, ''),
(48, 25, 'Roberto Santa Cruz', 'House 396, Magsaysay Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09371181034', 'Separated', 'Female', '1970-01-07', 'San Jose del Monte, Bulacan', 'Filipino', 8, 'CEDULA', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-08 22:34:11', 'released', NULL, NULL, ''),
(49, 23, 'Juan Ng Reyes', 'House 343, Luna Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09511181455', 'Married', 'Female', '1965-11-16', 'San Jose del Monte, Bulacan', 'Filipino', 4, 'BRGY. INDIGENCY', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-29 22:34:11', 'processing', NULL, NULL, ''),
(50, 23, 'Isabel Delos Aguilar', 'House 129, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09217110577', 'Single', 'Female', '1994-10-25', 'San Jose del Monte, Bulacan', 'Filipino', 17, 'BRGY. CLEARANCE', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-08 22:34:11', 'ready', NULL, NULL, ''),
(51, 24, 'Carlos San Mendoza', 'House 482, Luna Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09489339800', 'Widowed', 'Female', '1966-03-12', 'San Jose del Monte, Bulacan', 'Filipino', 29, 'BRGY. ID', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-18 22:34:11', 'released', NULL, NULL, ''),
(52, 25, 'Antonio Los Dela Cruz', 'House 345, Del Pilar Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09182557870', 'Widowed', 'Male', '1976-05-10', 'San Jose del Monte, Bulacan', 'Filipino', 11, 'BRGY. ID', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-23 22:34:11', 'pending', NULL, NULL, ''),
(53, 26, 'Miguel San Reyes', 'House 407, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09599790138', 'Married', 'Female', '1959-11-24', 'San Jose del Monte, Bulacan', 'Filipino', 21, 'BRGY. CLEARANCE', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-15 22:34:11', 'pending', NULL, NULL, ''),
(54, 24, 'Teresa Las Mendoza', 'House 185, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09237031681', 'Widowed', 'Female', '1963-03-28', 'San Jose del Monte, Bulacan', 'Filipino', 14, 'CEDULA', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-05 22:34:11', 'ready', NULL, NULL, ''),
(55, 26, 'Antonio De Marquez', 'House 355, Aguinaldo Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09519169495', 'Separated', 'Male', '1997-09-26', 'San Jose del Monte, Bulacan', 'Filipino', 21, 'BRGY. ID', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-17 22:34:11', 'released', NULL, NULL, ''),
(56, 24, 'Esperanza Delos Romualdez', 'House 365, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09220807932', 'Married', 'Female', '2004-01-20', 'San Jose del Monte, Bulacan', 'Filipino', 23, 'BRGY. CLEARANCE', 'For employment and legal purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-15 22:34:11', 'released', NULL, NULL, '');

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
  `is_deceased` tinyint(1) DEFAULT 0,
  `has_account` tinyint(1) DEFAULT 0,
  `skills` varchar(255) DEFAULT NULL,
  `monthly_income` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_members`
--

INSERT INTO `family_members` (`id`, `registration_id`, `full_name`, `relationship`, `birth_date`, `age`, `gender`, `civil_status`, `email`, `education`, `occupation`, `is_deceased`, `has_account`, `skills`, `monthly_income`, `created_at`) VALUES
(245, 284, 'Luisa Bernardo', 'Ina', '1977-04-07', 48, 'Babae', 'Married', 'luisamagtibay77@gmail.com', NULL, 'Businesswoman', 0, 0, NULL, NULL, '2025-08-27 22:20:39'),
(246, 285, 'Job Bernardo', 'Asawa', '2003-02-21', 22, 'Lalaki', 'Married', 'jobmatthewbernardo@gmail.com', NULL, '1x Crypto', 0, 0, NULL, NULL, '2025-08-27 22:26:00');

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
(220, 284, 'Christian Bernardo', 'EAGLES', '2025-08-27 22:20:45'),
(221, 285, 'Job Bernardo', '1X', '2025-08-27 22:26:03');

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
(1, 'C1', 'Counter 1 - All Certificates', 1, NULL, 1, NULL, '2025-08-28 00:05:51', '2025-08-21 00:17:36', '2025-09-21 12:34:28'),
(2, 'C2', 'Counter 2 - Business Applications', 6, NULL, 1, NULL, '2025-08-28 00:06:37', '2025-08-21 00:17:36', '2025-09-21 12:34:29'),
(3, 'C3', 'Counter 3 - General', 5, NULL, 1, NULL, '2025-08-27 22:06:51', '2025-08-21 00:17:36', '2025-09-21 12:34:29');

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
(112, 'GS-20250827-015', 5, 'Mar Yvan Sagun Dela Cruz', '09162291763', NULL, 'resident_registration: Resident Census Registration Processing', 'normal', 'serving', 14, '2025-08-28 02:24:19', '2025-08-27 22:06:51', '2025-08-27 22:06:51', NULL, NULL, NULL, '2025-08-27 22:04:19', '2025-08-27 22:06:51'),
(113, 'GS-20250827-002', 5, 'Archie Macalindong Verania', '09062668190', NULL, 'resident_registration: Resident Census Registration Processing', 'normal', 'waiting', 1, '2025-08-27 22:16:23', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:16:23', '2025-08-27 22:16:23'),
(114, 'GS-20250827-003', 5, 'Job Matthew Magtibay Bernardo', '09274797949', NULL, 'resident_registration: Resident Census Registration Processing', 'normal', 'waiting', 2, '2025-08-27 22:40:45', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:20:45', '2025-08-27 22:20:45'),
(115, 'GS-20250827-004', 5, 'Ghin Fernandez Dacanay', '09765431907', NULL, 'resident_registration: Resident Census Registration Processing', 'normal', 'waiting', 3, '2025-08-27 23:06:03', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:26:03', '2025-08-27 22:26:03'),
(116, 'GS-20250827-005', 5, 'Manuel Reyes', '09405921728', NULL, 'Form Submission', '', 'waiting', 4, '2025-08-27 23:35:25', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:25', '2025-08-27 22:35:25'),
(117, 'BP-20250827-001', 6, 'Carlos Lopez', '09510153015', NULL, 'Certificate Request', 'normal', 'waiting', 1, '2025-08-27 22:35:25', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:25', '2025-08-27 22:35:25'),
(118, 'BC-20250827-001', 1, 'Ana Jimenez', '09653280579', NULL, 'Permit Application', 'normal', 'waiting', 1, '2025-08-27 22:35:25', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:25', '2025-08-27 22:35:25'),
(119, 'TP-20250827-001', 3, 'Teresa Morales', '09241334711', NULL, 'Form Submission', '', 'waiting', 1, '2025-08-27 22:35:25', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:25', '2025-08-27 22:35:25'),
(120, 'GS-20250827-006', 5, 'Roberto Rivera', '09727381897', NULL, 'Information Request', 'priority', 'waiting', 5, '2025-08-27 23:55:25', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:25', '2025-08-27 22:35:25'),
(121, 'BI-20250827-001', 2, 'Roberto Hernandez', '09770849516', NULL, 'General Inquiry', 'normal', 'waiting', 1, '2025-08-27 22:35:25', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:25', '2025-08-27 22:35:25'),
(122, 'GS-20250827-007', 5, 'Daniel Jimenez', '09282622474', NULL, 'Document Verification', 'normal', 'waiting', 6, '2025-08-28 00:15:25', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:25', '2025-08-27 22:35:25'),
(123, 'GS-20250827-008', 5, 'Elena Garcia', '09810384624', NULL, 'Information Request', '', 'waiting', 7, '2025-08-28 00:35:25', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:25', '2025-08-27 22:35:25'),
(124, 'GS-20250827-009', 5, 'Miguel Reyes', '09580866091', NULL, 'Certificate Request', 'normal', 'waiting', 8, '2025-08-28 00:55:25', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:25', '2025-08-27 22:35:25'),
(125, 'BC-20250827-002', 1, 'Roberto Jimenez', '09865497199', NULL, 'Certificate Request', 'normal', 'waiting', 2, '2025-08-27 22:50:25', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:25', '2025-08-27 22:35:25'),
(126, 'BC-20250827-003', 1, 'Miguel Rivera', '09270539569', NULL, 'Registration Update', 'normal', 'waiting', 3, '2025-08-27 23:05:26', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:26', '2025-08-27 22:35:26'),
(127, 'GS-20250827-010', 5, 'Elena Garcia', '09255338760', NULL, 'Certificate Request', '', 'waiting', 9, '2025-08-28 01:15:26', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:26', '2025-08-27 22:35:26'),
(128, 'BP-20250827-002', 6, 'Miguel Rivera', '09510080660', NULL, 'Form Submission', '', 'waiting', 2, '2025-08-27 23:05:26', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:26', '2025-08-27 22:35:26'),
(129, 'BI-20250827-002', 2, 'Maria Fernandez', '09461678337', NULL, 'Information Request', '', 'waiting', 2, '2025-08-27 22:50:26', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:26', '2025-08-27 22:35:26'),
(130, 'GS-20250827-011', 5, 'Francisco Gomez', '09132474885', NULL, 'Form Submission', '', 'waiting', 10, '2025-08-28 01:35:26', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:26', '2025-08-27 22:35:26'),
(131, 'BC-20250827-004', 1, 'Rosa Gonzales', '09405077396', NULL, 'Certificate Request', '', 'waiting', 4, '2025-08-27 23:20:26', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:26', '2025-08-27 22:35:26'),
(132, 'BC-20250827-005', 1, 'Luz Ocampo', '09416227675', NULL, 'Permit Application', 'priority', 'waiting', 5, '2025-08-27 23:35:26', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:26', '2025-08-27 22:35:26'),
(133, 'BC-20250827-006', 1, 'Patricia Rodriguez', '09796342258', NULL, 'Complaint Filing', '', 'waiting', 6, '2025-08-27 23:50:26', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:26', '2025-08-27 22:35:26'),
(134, 'BP-20250827-003', 6, 'Jose Garcia', '09124211441', NULL, 'Certificate Request', '', 'waiting', 3, '2025-08-27 23:35:26', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:26', '2025-08-27 22:35:26'),
(135, 'GS-20250827-012', 5, 'Carmen Torres', '09147610455', NULL, 'Document Verification', '', 'waiting', 11, '2025-08-28 01:55:26', NULL, NULL, NULL, NULL, NULL, '2025-08-27 22:35:26', '2025-08-27 22:35:26'),
(136, 'GS-20250827-013', 5, 'Yvan Sagun Dela Cruz', '09306663135', NULL, 'resident_registration: Resident Census Registration Processing', 'normal', 'waiting', 12, '2025-08-28 03:19:27', NULL, NULL, NULL, NULL, NULL, '2025-08-27 23:39:27', '2025-08-27 23:39:27'),
(137, 'GS-20250828-001', 5, 'Carmen Fernandez', '09211757540', NULL, 'Complaint Filing', '', 'waiting', 1, '2025-08-28 00:05:17', NULL, NULL, NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:05:17'),
(138, 'GS-20250828-002', 5, 'Isabel Jimenez', '09727477333', NULL, 'Permit Application', 'normal', 'waiting', 2, '2025-08-28 00:25:17', NULL, NULL, NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:05:17'),
(139, 'GS-20250828-003', 5, 'Patricia Santos', '09718195110', NULL, 'Registration Update', 'normal', 'waiting', 3, '2025-08-28 00:45:17', NULL, NULL, NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:05:17'),
(140, 'PR-20250828-001', 4, 'Manuel Mendoza', '09666583990', NULL, 'General Inquiry', 'normal', 'serving', 1, '2025-08-28 00:05:17', '2025-08-28 00:05:51', '2025-08-28 00:05:51', NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:05:51'),
(141, 'PR-20250828-002', 4, 'Carlos Fernandez', '09908561022', NULL, 'Document Verification', 'priority', 'waiting', 2, '2025-08-28 00:15:17', NULL, NULL, NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:05:17'),
(142, 'PR-20250828-003', 4, 'Elena Jimenez', '09281559551', NULL, 'Registration Update', 'normal', 'waiting', 3, '2025-08-28 00:25:17', NULL, NULL, NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:05:17'),
(143, 'BP-20250828-001', 6, 'Carlos Ocampo', '09947601996', NULL, 'Certificate Request', '', 'serving', 1, '2025-08-28 00:05:17', '2025-08-28 00:06:37', '2025-08-28 00:06:37', NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:06:37'),
(144, 'GS-20250828-004', 5, 'Antonio Gomez', '09526199418', NULL, 'Information Request', 'normal', 'waiting', 4, '2025-08-28 01:05:17', NULL, NULL, NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:05:17'),
(145, 'GS-20250828-005', 5, 'Ana Rivera', '09190329240', NULL, 'Form Submission', '', 'waiting', 5, '2025-08-28 01:25:17', NULL, NULL, NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:05:17'),
(146, 'GS-20250828-006', 5, 'Daniel Santos', '09239771051', NULL, 'Document Verification', 'normal', 'waiting', 6, '2025-08-28 01:45:17', NULL, NULL, NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:05:17'),
(147, 'GS-20250828-007', 5, 'Pedro Fernandez', '09602556529', NULL, 'Permit Application', 'priority', 'waiting', 7, '2025-08-28 02:05:17', NULL, NULL, NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:05:17'),
(148, 'PR-20250828-004', 4, 'Rosa Gonzales', '09355223907', NULL, 'Registration Update', 'normal', 'waiting', 4, '2025-08-28 00:35:17', NULL, NULL, NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:05:17'),
(149, 'GS-20250828-008', 5, 'Patricia Gonzales', '09555926147', NULL, 'Permit Application', 'normal', 'waiting', 8, '2025-08-28 02:25:17', NULL, NULL, NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:05:17'),
(150, 'BP-20250828-002', 6, 'Isabel Lopez', '09933365684', NULL, 'Certificate Request', '', 'waiting', 2, '2025-08-28 00:35:17', NULL, NULL, NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:05:17'),
(151, 'PR-20250828-005', 4, 'Maria Gonzales', '09461158054', NULL, 'Document Verification', 'priority', 'waiting', 5, '2025-08-28 00:45:17', NULL, NULL, NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:05:17'),
(152, 'BC-20250828-001', 1, 'Carlos Gomez', '09745340159', NULL, 'Certificate Request', 'priority', 'waiting', 1, '2025-08-28 00:05:17', NULL, NULL, NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:05:17'),
(153, 'GS-20250828-009', 5, 'Carmen Morales', '09239097380', NULL, 'Information Request', 'normal', 'waiting', 9, '2025-08-28 02:45:17', NULL, NULL, NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:05:17'),
(154, 'PR-20250828-006', 4, 'Patricia Flores', '09942259257', NULL, 'Document Verification', 'normal', 'waiting', 6, '2025-08-28 00:55:17', NULL, NULL, NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:05:17'),
(155, 'TP-20250828-001', 3, 'Juan Torres', '09129540651', NULL, 'Registration Update', 'normal', 'waiting', 1, '2025-08-28 00:05:17', NULL, NULL, NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:05:17'),
(156, 'GS-20250828-010', 5, 'Manuel Diaz', '09546855904', NULL, 'Certificate Request', 'normal', 'waiting', 10, '2025-08-28 03:05:17', NULL, NULL, NULL, NULL, NULL, '2025-08-28 00:05:17', '2025-08-28 00:05:17'),
(157, 'GS-20250921-001', 5, 'Test Customer 123433', '09123456789', NULL, 'Test General Service', 'normal', 'waiting', 1, '2025-09-21 12:34:33', NULL, NULL, NULL, NULL, NULL, '2025-09-21 12:34:33', '2025-09-21 12:34:33');

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
(23, 'Mar Yvan', 'Sagun', 'Dela Cruz', 'biofrostyv@gmail.com', '09162291763', '$2y$10$vwuht5cEWMXBrZIsNr3oY.AjCvZpT9dssKt.zcYu1PZiKhei5gIuO', 'House 101, Block 2 Lot 15 Australia Street, Barangay Gumaoc East, San Jose del Monte, Bulacan, Philippines', '101', 'Gumaoc East', 'BLOCK', 'Roberto Bernardo', 'Barangay Officer', '2004-08-07', 'Caloocan City', 'Male', 'Single', '0005786280', '0005786280', 'active', NULL, NULL, '2025-08-27 22:04:19', '2025-08-27 22:09:33', 1, NULL, NULL),
(24, 'Archie', 'Macalindong', 'Verania', 'archieverania1130@gmail.com', '09062668190', '$2y$10$dAphkeZjzDXOJ8T0Qam8euBIBzs0bp6AhuFQn5zSqxz2Cipxp.XVO', 'House 11, Langka, Barangay Gumaoc East, San Jose del Monte, Bulacan, Philippines', '11', 'Gumaoc East', 'BLOCK', 'Jobert Padilla', 'Barangay Officer', '2000-11-30', 'Quezon City', 'Male', 'Single', '0006894001', '0006894001', 'active', NULL, NULL, '2025-08-27 22:16:23', '2025-08-27 22:18:21', 1, NULL, NULL),
(25, 'Job Matthew', 'Magtibay', 'Bernardo', 'jobmatthewbernardo@gmail.com', '09274797949', '$2y$10$4TJ/LdzpAyLdbQvhY3lLA.PboZ/ACXUgnjk6xeBqU.6wTDyXGCFDC', 'House 26, Malanting, Barangay Gumaoc East, San Jose del Monte, Bulacan, Philippines', '26', 'Gumaoc East', 'BLOCK', 'Jobert Bernsz', 'Barangay staff', '2003-02-21', 'Quezon City', 'Male', 'Married', '9NL2Q9ISKS', '9NL2Q9ISKS', 'pending', NULL, NULL, '2025-08-27 22:20:39', '2025-08-27 22:23:05', 1, NULL, NULL),
(26, 'Luisa', '', 'Bernardo', 'luisamagtibay77@gmail.com', '', '$2y$10$6wgShwKefywmEcSjC3GmmO9cnX7YLfZa6Fahk8uP.sPh4uk79HzK6', 'House 26, Malanting, Barangay Gumaoc East, San Jose del Monte, Bulacan, Philippines', '26', 'Gumaoc East', 'BLOCK', 'Jobert Bernsz', 'Barangay staff', '1977-04-07', 'Unknown', 'Female', 'Married', '0005845889', '0005845889', 'pending', NULL, NULL, '2025-08-27 22:20:40', '2025-08-27 22:22:03', 0, 25, 'Ina'),
(27, 'Ghin', 'Fernandez', 'Dacanay', 'ghinnievan4@gmail.com', '09765431907', '$2y$10$KrOvSKQ1UV5zYQGlkvEyWumWpojJRFKSELEIdCAqLBEFwDQhbuwxq', 'House 5, Saint Claire St., Barangay Gumaoc East, San Jose del Monte, Bulacan, Philippines', '5', 'Gumaoc East', 'BLOCK', 'Jeon Jungkook', 'TOP', '2004-08-04', 'Metro Manila', 'Female', 'Married', 'HURY233BGV', 'HURY233BGV', 'pending', NULL, NULL, '2025-08-27 22:26:00', '2025-08-27 22:26:00', 0, NULL, NULL),
(29, 'Yvan', 'Sagun', 'Dela Cruz', 'lanmimiami1923@gmail.com', '09306663135', '$2y$10$cmAd41JSAtmJf1efkKlkVe7cKafP24/yj5nbTYv5N5EeDVGzQhzmC', 'House 101, Block 2 Lot 15 Australia Street, Barangay Gumaoc East, San Jose del Monte, Bulacan, Philippines', '101', 'Gumaoc East', 'BLOCK', 'Roberto Bernardo', 'Barangay Officer', '2004-08-07', 'Caloocan City', 'Male', 'Single', 'J9XAMPF6O6', 'J9XAMPF6O6', 'active', NULL, NULL, '2025-08-27 23:39:27', '2025-08-27 23:48:17', 1, NULL, NULL);

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
  `interviewer_title` varchar(255) DEFAULT NULL,
  `resident_disability` varchar(255) DEFAULT NULL,
  `resident_organization` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resident_registrations`
--

INSERT INTO `resident_registrations` (`id`, `first_name`, `middle_name`, `last_name`, `birth_date`, `birth_place`, `age`, `civil_status`, `gender`, `contact_number`, `email`, `house_number`, `address`, `pangkabuhayan`, `submitted_at`, `status`, `land_ownership`, `land_ownership_other`, `house_ownership`, `house_ownership_other`, `farmland`, `cooking_energy`, `cooking_energy_other`, `toilet_type`, `toilet_type_other`, `electricity_source`, `electricity_source_other`, `water_source`, `water_source_other`, `waste_disposal`, `waste_disposal_other`, `appliances`, `transportation`, `transportation_other`, `business`, `business_other`, `contraceptive`, `interviewer`, `interviewer_title`, `resident_disability`, `resident_organization`) VALUES
(282, 'Mar Yvan', 'Sagun', 'Dela Cruz', '2004-08-07', 'Caloocan City', 21, 'Single', 'Male', '09162291763', 'biofrostyv@gmail.com', '101', 'Block 2 Lot 15 Australia Street', 'Pag-aari', '2025-08-27 22:04:19', 'approved', 'Pag-aari', '', 'Pag-aari', '', 'Pag-aari', 'Kuryente', '', 'Flush', '', 'Kuryente', '', 'Water District', '', 'Itinatapon kung saan', '', 'Radyo/Stereo,Telebisyon,Refrigerator,Muwebles', 'Kotse,Motorsiklo', '', 'Iba pa', 'Computer Cafe', 'Condom', 'Roberto Bernardo', 'Barangay Officer', NULL, NULL),
(283, 'Archie', 'Macalindong', 'Verania', '2000-11-30', 'Quezon City', 24, 'Single', 'Male', '09062668190', 'archieverania1130@gmail.com', '11', 'Langka', 'Pag-aari', '2025-08-27 22:16:23', 'pending', 'Pag-aari', '', 'Pag-aari', '', 'Pag-aari', '', '', 'Flush', '', 'Kuryente', '', 'Poso Artesiyano', '', 'Sinusunog', '', 'Radyo/Stereo,Telebisyon,Refrigerator,Muwebles', 'Kotse', '', 'Patahian', '', 'Condom', 'Jobert Padilla', 'Barangay Officer', NULL, NULL),
(284, 'Job Matthew', 'Magtibay', 'Bernardo', '2003-02-21', 'Quezon City', 22, 'Married', 'Male', '09274797949', 'jobmatthewbernardo@gmail.com', '26', 'Malanting', 'Pag-aari', '2025-08-27 22:20:39', 'pending', 'Pag-aari', '', 'Pag-aari', '', 'Pag-aari', 'Kuryente', '', 'Flush', '', 'Kuryente', '', 'Water District', '', 'Hukay na may takip', '', 'Radyo/Stereo,Telebisyon,Refrigerator,Muwebles', 'Sasakyan', '', 'Sari-Sari Store', '', 'Condom', 'Jobert Bernsz', 'Barangay staff', NULL, NULL),
(285, 'Ghin', 'Fernandez', 'Dacanay', '2004-08-04', 'Metro Manila', 21, 'Married', 'Female', '09765431907', 'ghinnievan4@gmail.com', '5', 'Saint Claire St.', 'Pag-aari', '2025-08-27 22:26:00', 'pending', 'Pag-aari', '', 'Pag-aari', '', 'Pag-aari', 'Gaas', '', 'Flush', '', 'Kuryente', '', 'Water District', '', 'Kinokolekta', '', 'Radyo/Stereo,Telebisyon,Refrigerator,Muwebles', 'Kotse', '', 'Sari-Sari Store', '', 'Wala', 'Jeon Jungkook', 'TOP', NULL, NULL),
(286, 'Maria', 'Santa', 'Aguilar', '2002-06-18', 'San Jose del Monte, Bulacan', 23, 'Separated', 'Male', '09641444451', 'maria.aguilar261@hotmail.com', '49', 'House 49, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-07-29 22:34:11', 'approved', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(287, 'Esperanza', 'Dela', 'Castillo', '2004-07-09', 'San Jose del Monte, Bulacan', 21, 'Widowed', 'Male', '09188593205', 'esperanza.castillo329@yahoo.com', '339', 'House 339, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-08-11 22:34:11', 'pending', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(288, 'Juan', 'Del', 'Romualdez', '2004-12-23', 'San Jose del Monte, Bulacan', 21, 'Single', 'Male', '09497185474', 'juan.romualdez151@gmail.com', '78', 'House 78, Luna Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-07-30 22:34:11', 'rejected', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(289, 'Esperanza', 'Los', 'Torres', '1958-10-24', 'San Jose del Monte, Bulacan', 67, 'Married', 'Male', '09182450972', 'esperanza.torres941@email.com', '215', 'House 215, Magsaysay Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-07-30 22:34:11', 'approved', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(290, 'Antonio', 'Delos', 'Garcia', '1972-05-15', 'San Jose del Monte, Bulacan', 53, 'Widowed', 'Male', '09731574676', 'antonio.garcia979@outlook.com', '399', 'House 399, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-08-22 22:34:11', 'approved', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(291, 'Ricardo', 'Las', 'Aquino', '1976-08-16', 'San Jose del Monte, Bulacan', 49, 'Married', 'Male', '09178928317', 'ricardo.aquino793@hotmail.com', '348', 'House 348, Del Pilar Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-08-09 22:34:11', 'approved', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(292, 'Rosa', 'Delos', 'Mercado', '1986-01-01', 'San Jose del Monte, Bulacan', 39, 'Single', 'Female', '09772259228', 'rosa.mercado105@email.com', '289', 'House 289, Bonifacio Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-08-03 22:34:11', 'approved', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(293, 'Fernando', 'Mga', 'Andres', '1977-07-04', 'San Jose del Monte, Bulacan', 48, 'Married', 'Male', '09190589671', 'fernando.andres240@outlook.com', '295', 'House 295, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-08-17 22:34:11', 'pending', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(294, 'Isabel', 'Santa', 'Ocampo', '2003-03-09', 'San Jose del Monte, Bulacan', 22, 'Married', 'Male', '09445660371', 'isabel.ocampo547@outlook.com', '468', 'House 468, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-08-04 22:34:11', 'approved', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(295, 'Carlos', 'De', 'Ocampo', '1960-07-22', 'San Jose del Monte, Bulacan', 65, 'Widowed', 'Male', '09583354495', 'carlos.ocampo383@proton.me', '380', 'House 380, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-08-13 22:34:11', 'pending', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(296, 'Elena', 'Ng', 'Rivera', '1975-08-14', 'San Jose del Monte, Bulacan', 50, 'Widowed', 'Male', '09712277863', 'elena.rivera645@proton.me', '1', 'House 1, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-08-22 22:34:11', 'pending', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(297, 'Manuel', 'De', 'Castillo', '1981-06-27', 'San Jose del Monte, Bulacan', 44, 'Single', 'Female', '09681182277', 'manuel.castillo939@outlook.com', '324', 'House 324, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-08-05 22:34:11', 'pending', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(298, 'Ricardo', 'De', 'Villanueva', '1948-10-08', 'San Jose del Monte, Bulacan', 77, 'Widowed', 'Male', '09464227943', 'ricardo.villanueva667@yahoo.com', '365', 'House 365, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-08-06 22:34:11', 'rejected', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(299, 'Elena', 'De', 'Romualdez', '1980-07-11', 'San Jose del Monte, Bulacan', 45, 'Married', 'Male', '09516829545', 'elena.romualdez323@hotmail.com', '469', 'House 469, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-07-28 22:34:11', 'approved', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(300, 'Ana', 'Del', 'Romualdez', '2000-01-11', 'San Jose del Monte, Bulacan', 25, 'Married', 'Male', '09610351123', 'ana.romualdez447@proton.me', '382', 'House 382, Magsaysay Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-08-22 22:34:11', 'pending', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(301, 'Elena', 'Del', 'Santos', '1971-09-02', 'San Jose del Monte, Bulacan', 54, 'Single', 'Female', '09192964760', 'elena.santos420@yahoo.com', '461', 'House 461, Magsaysay Road, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-08-06 22:34:11', 'pending', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(302, 'Fernando', 'Mga', 'Marquez', '1957-04-07', 'San Jose del Monte, Bulacan', 68, 'Separated', 'Male', '09940048440', 'fernando.marquez520@yahoo.com', '334', 'House 334, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-08-26 22:34:11', 'rejected', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(303, 'Francisco', 'San', 'Villanueva', '1990-10-28', 'San Jose del Monte, Bulacan', 35, 'Single', 'Female', '09729278331', 'francisco.villanueva720@yahoo.com', '112', 'House 112, Del Pilar Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-08-26 22:34:11', 'approved', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(304, 'Francisco', 'Dela', 'Santos', '1993-10-24', 'San Jose del Monte, Bulacan', 32, 'Single', 'Female', '09676227162', 'francisco.santos996@yahoo.com', '133', 'House 133, Luna Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-08-19 22:34:11', 'rejected', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(305, 'Teresa', 'Santa', 'Bautista', '1951-12-19', 'San Jose del Monte, Bulacan', 74, 'Married', 'Female', '09459782658', 'teresa.bautista231@email.com', '146', 'House 146, Quezon Boulevard, Barangay Gumaoc East, San Jose del Monte, Bulacan', 'Various Livelihood', '2025-08-02 22:34:11', 'rejected', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorcyle', NULL, 'Sari-Sari Store', NULL, 'Pills', 'System Generator', 'Data Entry', NULL, NULL),
(306, 'Yvan', 'Sagun', 'Dela Cruz', '2004-08-07', 'Caloocan City', 21, 'Single', 'Male', '09306663135', 'lanmimiami1923@gmail.com', '101', 'Block 2 Lot 15 Australia Street', 'Inuupahan', '2025-08-27 23:39:27', 'approved', 'Inuupahan', '', 'Umuupa', '', 'Binubuwisan', 'Kuryente', '', 'De-buhos', '', 'Gaas', '', 'Water District', '', 'Hukay na may takip', '', 'Telebisyon', 'Jeep', '', 'Patahian', '', 'IUD', 'Roberto Bernardo', 'Barangay Officer', NULL, NULL);

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
(14, '0005786280', 'assigned', '2025-08-27 21:59:48', '2025-08-27 22:04:19', 23, 'biofrostyv@gmail.com', 1, '', '2025-08-27 21:59:48', '2025-08-27 22:04:19');

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
  ADD KEY `registration_id` (`registration_id`),
  ADD KEY `idx_is_deceased` (`is_deceased`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=170;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `captain_clearances`
--
ALTER TABLE `captain_clearances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `certificate_requests`
--
ALTER TABLE `certificate_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `family_disabilities`
--
ALTER TABLE `family_disabilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `family_members`
--
ALTER TABLE `family_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=247;

--
-- AUTO_INCREMENT for table `family_organizations`
--
ALTER TABLE `family_organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=222;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;

--
-- AUTO_INCREMENT for table `queue_windows`
--
ALTER TABLE `queue_windows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `resident_registrations`
--
ALTER TABLE `resident_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=307;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
