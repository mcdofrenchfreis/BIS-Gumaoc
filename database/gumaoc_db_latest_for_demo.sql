-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2026 at 07:14 AM
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
(1, 'system', 'admin_login', 'admin_auth', NULL, 'Admin login failed for username: admin', '{\"username\":\"admin\",\"success\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-11 14:56:29'),
(2, 'system', 'admin_login', 'admin_auth', NULL, 'Admin login failed for username: admin', '{\"username\":\"admin\",\"success\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-11 14:56:36'),
(3, 'system', 'admin_login', 'admin_auth', NULL, 'Admin login failed for username: admin_test', '{\"username\":\"admin_test\",\"success\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-11 14:58:46'),
(4, 'admin_test', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin_test', '{\"username\":\"admin_test\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-11 14:58:55'),
(5, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-11 14:59:03'),
(6, 'admin_test', 'status_update', 'resident_registration', 3, 'Updated resident_registration ID #3 status from \'pending\' to \'approved\'', '{\"old_status\":\"pending\",\"new_status\":\"approved\",\"timestamp\":\"2026-05-11 16:59:14\",\"applicant_name\":\"Archie Verania\",\"registration_type\":\"resident_registration\",\"processing_time\":\"2026-05-11 16:59:14\",\"admin_action\":true,\"email_sent\":false,\"email_address\":\"archieverania1130@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-11 14:59:14'),
(7, 'admin_test', 'form_view', 'resident_registration', 3, 'Viewed resident registration form ID #3', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-11 14:59:18'),
(8, 'admin_test', 'form_view', 'resident_registration', 3, 'Viewed resident registration form ID #3', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-11 15:01:05'),
(9, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-11 15:01:09'),
(10, 'admin_test', 'print_action', 'certificate_request', 2, 'Printed indigency_certificate for certificate_request ID #2', '{\"print_type\":\"indigency_certificate\",\"print_timestamp\":\"2026-05-11 17:01:12\",\"certificate_type\":\"BRGY. INDIGENCY\",\"applicant_name\":\"Maria Reyes Santos\",\"certificate_number\":\"IND-00002-2026\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-11 15:01:12'),
(11, 'admin_test', 'status_update', 'certificate_request', 1, 'Updated certificate_request ID #1 status from \'pending\' to \'processing\'', '{\"old_status\":\"pending\",\"new_status\":\"processing\",\"timestamp\":\"2026-05-11 17:01:42\",\"certificate_type\":\"BRGY. CLEARANCE\",\"applicant_name\":\"Juan Santos Dela Cruz\",\"queue_ticket_id\":null,\"queue_status_updated\":\"no_queue_ticket\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-11 15:01:42'),
(12, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-11 15:01:42'),
(13, 'admin_test', 'print_action', 'certificate_request', 1, 'Printed barangay_clearance for certificate_request ID #1', '{\"print_type\":\"barangay_clearance\",\"print_timestamp\":\"2026-05-11 17:01:44\",\"certificate_type\":\"BRGY. CLEARANCE\",\"applicant_name\":\"Juan Santos Dela Cruz\",\"certificate_number\":\"BC-00001-2026\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-11 15:01:44'),
(14, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-11 15:02:57'),
(15, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-11 15:02:59'),
(16, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-11 15:03:00'),
(17, 'admin_test', 'form_view', 'resident_registration', 2, 'Viewed resident registration form ID #2', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-11 15:08:09'),
(18, 'admin_test', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin_test', '{\"username\":\"admin_test\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 06:04:04'),
(19, 'admin_test', 'status_update', 'resident_registration', 3, 'Updated resident_registration ID #3 status from \'approved\' to \'pending\'', '{\"old_status\":\"approved\",\"new_status\":\"pending\",\"timestamp\":\"2026-05-12 08:04:10\",\"applicant_name\":\"Archie Verania\",\"registration_type\":\"resident_registration\",\"processing_time\":\"2026-05-12 08:04:10\",\"admin_action\":true,\"email_sent\":false,\"email_address\":\"archieverania1130@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 06:04:10'),
(20, 'admin_test', 'status_update', 'resident_registration', 3, 'Updated resident_registration ID #3 status from \'pending\' to \'approved\'', '{\"old_status\":\"pending\",\"new_status\":\"approved\",\"timestamp\":\"2026-05-12 08:04:22\",\"applicant_name\":\"Archie Verania\",\"registration_type\":\"resident_registration\",\"processing_time\":\"2026-05-12 08:04:22\",\"admin_action\":true,\"email_sent\":true,\"email_address\":\"archieverania1130@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 06:04:22'),
(21, 'system', 'admin_login', 'admin_auth', NULL, 'Admin login failed for username: admin_test', '{\"username\":\"admin_test\",\"success\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 12:54:00'),
(22, 'system', 'admin_login', 'admin_auth', NULL, 'Admin login failed for username: admin_test', '{\"username\":\"admin_test\",\"success\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 12:54:08'),
(23, 'admin_test', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin_test', '{\"username\":\"admin_test\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 12:54:15'),
(24, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 12:54:17'),
(25, 'admin_test', 'print_action', 'certificate_request', 1, 'Printed barangay_clearance for certificate_request ID #1', '{\"print_type\":\"barangay_clearance\",\"print_timestamp\":\"2026-05-15 14:54:19\",\"certificate_type\":\"BRGY. CLEARANCE\",\"applicant_name\":\"Juan Santos Dela Cruz\",\"certificate_number\":\"BC-00001-2026\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 12:54:19'),
(26, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:04:43'),
(27, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:05:00'),
(28, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:05:10'),
(29, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:05:23'),
(30, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:07:57'),
(31, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:08:47'),
(32, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:11:44'),
(33, 'admin_test', 'print_action', 'certificate_request', 1, 'Printed barangay_clearance for certificate_request ID #1', '{\"print_type\":\"barangay_clearance\",\"print_timestamp\":\"2026-05-15 15:11:45\",\"certificate_type\":\"BRGY. CLEARANCE\",\"applicant_name\":\"Juan Santos Dela Cruz\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:11:45'),
(34, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:34:36'),
(35, 'admin_test', 'print_action', 'certificate_request', 1, 'Printed barangay_clearance for certificate_request ID #1', '{\"print_type\":\"barangay_clearance\",\"print_timestamp\":\"2026-05-15 15:34:38\",\"certificate_type\":\"BRGY. CLEARANCE\",\"applicant_name\":\"Juan Santos Dela Cruz\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:34:38'),
(36, 'admin_test', 'print_action', 'certificate_request', 2, 'Printed indigency_certificate for certificate_request ID #2', '{\"print_type\":\"indigency_certificate\",\"print_timestamp\":\"2026-05-15 15:34:44\",\"certificate_type\":\"BRGY. INDIGENCY\",\"applicant_name\":\"Maria Reyes Santos\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:34:44'),
(37, 'admin_test', 'print_action', 'certificate_request', 2, 'Printed indigency_certificate for certificate_request ID #2', '{\"print_type\":\"indigency_certificate\",\"print_timestamp\":\"2026-05-15 15:37:12\",\"certificate_type\":\"BRGY. INDIGENCY\",\"applicant_name\":\"Maria Reyes Santos\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:37:12'),
(38, 'admin_test', 'print_action', 'certificate_request', 1, 'Printed barangay_clearance for certificate_request ID #1', '{\"print_type\":\"barangay_clearance\",\"print_timestamp\":\"2026-05-15 15:37:21\",\"certificate_type\":\"BRGY. CLEARANCE\",\"applicant_name\":\"Juan Santos Dela Cruz\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:37:21'),
(39, 'admin_test', 'print_action', 'certificate_request', 2, 'Printed indigency_certificate for certificate_request ID #2', '{\"print_type\":\"indigency_certificate\",\"print_timestamp\":\"2026-05-15 15:37:27\",\"certificate_type\":\"BRGY. INDIGENCY\",\"applicant_name\":\"Maria Reyes Santos\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:37:27'),
(40, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:39:00'),
(41, 'admin_test', 'print_action', 'certificate_request', 1, 'Printed barangay_clearance for certificate_request ID #1', '{\"print_type\":\"barangay_clearance\",\"print_timestamp\":\"2026-05-15 15:39:02\",\"certificate_type\":\"BRGY. CLEARANCE\",\"applicant_name\":\"Juan Santos Dela Cruz\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:39:02'),
(42, 'admin_test', 'print_action', 'certificate_request', 2, 'Printed indigency_certificate for certificate_request ID #2', '{\"print_type\":\"indigency_certificate\",\"print_timestamp\":\"2026-05-15 15:39:05\",\"certificate_type\":\"BRGY. INDIGENCY\",\"applicant_name\":\"Maria Reyes Santos\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:39:05'),
(43, 'admin_test', 'print_action', 'certificate_request', 2, 'Printed indigency_certificate for certificate_request ID #2', '{\"print_type\":\"indigency_certificate\",\"print_timestamp\":\"2026-05-15 15:39:17\",\"certificate_type\":\"BRGY. INDIGENCY\",\"applicant_name\":\"Maria Reyes Santos\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:39:17'),
(44, 'admin_test', 'print_action', 'certificate_request', 2, 'Printed indigency_certificate for certificate_request ID #2', '{\"print_type\":\"indigency_certificate\",\"print_timestamp\":\"2026-05-15 15:45:46\",\"certificate_type\":\"BRGY. INDIGENCY\",\"applicant_name\":\"Maria Reyes Santos\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:45:46'),
(45, 'admin_test', 'print_action', 'certificate_request', 1, 'Printed barangay_clearance for certificate_request ID #1', '{\"print_type\":\"barangay_clearance\",\"print_timestamp\":\"2026-05-15 15:45:49\",\"certificate_type\":\"BRGY. CLEARANCE\",\"applicant_name\":\"Juan Santos Dela Cruz\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:45:49'),
(46, 'admin_test', 'print_action', 'business_application', 1, 'Printed business_clearance for business_application ID #1', '{\"print_type\":\"business_clearance\",\"print_timestamp\":\"2026-05-15 15:50:08\",\"business_name\":\"Dela Cruz Sari-Sari Store\",\"owner_name\":\"Juan Santos Dela Cruz\",\"clearance_number\":\"BBC-2026-0001\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:50:08'),
(47, 'admin_test', 'print_action', 'business_application', 1, 'Printed business_clearance for business_application ID #1', '{\"print_type\":\"business_clearance\",\"print_timestamp\":\"2026-05-15 15:53:04\",\"business_name\":\"Dela Cruz Sari-Sari Store\",\"owner_name\":\"Juan Santos Dela Cruz\",\"clearance_number\":\"BBC-2026-0001\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 13:53:04'),
(48, 'system', 'admin_login', 'admin_auth', NULL, 'Admin login failed for username: admin_test', '{\"username\":\"admin_test\",\"success\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:13:15'),
(49, 'admin_test', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin_test', '{\"username\":\"admin_test\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:13:22'),
(50, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:13:25'),
(51, 'admin_test', 'form_view', 'certificate_request', 4, 'Viewed certificate request summary ID #4', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:13:28'),
(52, 'admin_test', 'status_update', 'certificate_request', 4, 'Updated certificate_request ID #4 status from \'pending\' to \'processing\'', '{\"old_status\":\"pending\",\"new_status\":\"processing\",\"timestamp\":\"2026-05-16 19:13:34\",\"certificate_type\":\"BRGY. CLEARANCE\",\"applicant_name\":\"Archie  Verania\",\"queue_ticket_id\":null,\"queue_status_updated\":\"no_queue_ticket\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:13:34'),
(53, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:13:34'),
(54, 'admin_test', 'print_action', 'certificate_request', 4, 'Printed barangay_clearance for certificate_request ID #4', '{\"print_type\":\"barangay_clearance\",\"print_timestamp\":\"2026-05-16 19:13:36\",\"certificate_type\":\"BRGY. CLEARANCE\",\"applicant_name\":\"Archie  Verania\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:13:36'),
(55, 'admin_test', 'print_action', 'certificate_request', 1, 'Printed barangay_clearance for certificate_request ID #1', '{\"print_type\":\"barangay_clearance\",\"print_timestamp\":\"2026-05-16 19:13:41\",\"certificate_type\":\"BRGY. CLEARANCE\",\"applicant_name\":\"Juan Santos Dela Cruz\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:13:41'),
(56, 'admin_test', 'print_action', 'certificate_request', 2, 'Printed indigency_certificate for certificate_request ID #2', '{\"print_type\":\"indigency_certificate\",\"print_timestamp\":\"2026-05-16 19:13:44\",\"certificate_type\":\"BRGY. INDIGENCY\",\"applicant_name\":\"Maria Reyes Santos\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:13:44'),
(57, 'admin_test', 'print_action', 'certificate_request', 4, 'Printed barangay_clearance for certificate_request ID #4', '{\"print_type\":\"barangay_clearance\",\"print_timestamp\":\"2026-05-16 19:13:46\",\"certificate_type\":\"BRGY. CLEARANCE\",\"applicant_name\":\"Archie  Verania\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:13:46'),
(58, 'admin_test', 'status_update', 'certificate_request', 4, 'Updated certificate_request ID #4 status from \'processing\' to \'ready\'', '{\"old_status\":\"processing\",\"new_status\":\"ready\",\"timestamp\":\"2026-05-16 19:14:13\",\"certificate_type\":\"BRGY. CLEARANCE\",\"applicant_name\":\"Archie  Verania\",\"queue_ticket_id\":null,\"queue_status_updated\":\"no_queue_ticket\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:14:13'),
(59, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:14:13'),
(60, 'admin_test', 'status_update', 'certificate_request', 4, 'Updated certificate_request ID #4 status from \'ready\' to \'released\'', '{\"old_status\":\"ready\",\"new_status\":\"released\",\"timestamp\":\"2026-05-16 19:14:23\",\"certificate_type\":\"BRGY. CLEARANCE\",\"applicant_name\":\"Archie  Verania\",\"queue_ticket_id\":null,\"queue_status_updated\":\"no_queue_ticket\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:14:23'),
(61, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:14:23'),
(62, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:14:49'),
(63, 'admin_test', 'form_view', 'certificate_request', 4, 'Viewed certificate request summary ID #4', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:14:51'),
(64, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:24:46'),
(65, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:25:04'),
(66, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:25:07'),
(67, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:29:35'),
(68, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:29:38'),
(69, 'admin_test', 'create', 'announcement', 4, 'Created announcement #4: test', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:29:58'),
(70, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:29:58'),
(71, 'admin_test', 'status_update', 'announcement', 4, 'Changed announcement #4 status to published', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:30:03'),
(72, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:30:03'),
(73, 'admin_test', 'status_update', 'announcement', 4, 'Changed announcement #4 status to draft', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:30:05'),
(74, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:30:05'),
(75, 'admin_test', 'status_update', 'announcement', 1, 'Changed announcement #1 status to published', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:30:08'),
(76, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:30:08'),
(77, 'admin_test', 'status_update', 'announcement', 1, 'Changed announcement #1 status to draft', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:30:09'),
(78, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:30:09'),
(79, 'admin_test', 'status_update', 'announcement', 1, 'Changed announcement #1 status to published', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:30:10'),
(80, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:30:10'),
(81, 'admin_test', 'status_update', 'announcement', 1, 'Changed announcement #1 status to draft', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:30:13'),
(82, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:30:13'),
(83, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:30:21'),
(84, 'admin_test', 'admin_login', 'admin_auth', NULL, 'Admin login successful for username: admin_test', '{\"username\":\"admin_test\",\"success\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:33:20'),
(85, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:33:23'),
(86, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:33:27'),
(87, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:33:30'),
(88, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:33:35'),
(89, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:33:37'),
(90, 'admin_test', 'delete', 'announcement', 2, 'Deleted announcement #2: Enhanced E-Services Launch', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:34:10'),
(91, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:34:10'),
(92, 'admin_test', 'delete', 'announcement', 3, 'Deleted announcement #3: Town Fiesta 2026', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:34:12'),
(93, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:34:12'),
(94, 'admin_test', 'status_update', 'announcement', 1, 'Changed announcement #1 status to published', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:34:13'),
(95, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:34:13'),
(96, 'admin_test', 'status_update', 'announcement', 4, 'Changed announcement #4 status to published', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:34:15'),
(97, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:34:15'),
(98, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:34:18'),
(99, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:34:45'),
(100, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:34:47'),
(101, 'admin_test', 'create', 'announcement', 5, 'Created announcement #5: test', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:35:24'),
(102, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:35:24'),
(103, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed manage announcements admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:41:31'),
(104, 'admin_test', 'print_action', 'business_application', 1, 'Printed business_clearance for business_application ID #1', '{\"print_type\":\"business_clearance\",\"print_timestamp\":\"2026-05-17 06:42:52\",\"business_name\":\"Dela Cruz Sari-Sari Store\",\"owner_name\":\"Juan Santos Dela Cruz\",\"clearance_number\":\"BBC-2026-0001\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:42:52'),
(105, 'admin_test', 'print_action', 'business_application', 3, 'Printed business_clearance for business_application ID #3', '{\"print_type\":\"business_clearance\",\"print_timestamp\":\"2026-05-17 06:43:14\",\"business_name\":\"Sari Sari Store\",\"owner_name\":\"Archie Verania\",\"clearance_number\":\"BBC-2026-0003\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 04:43:14'),
(106, 'admin_test', 'print_action', 'business_application', 3, 'Printed business_clearance for business_application ID #3', '{\"print_type\":\"business_clearance\",\"print_timestamp\":\"2026-05-17 07:01:51\",\"business_name\":\"Sari Sari Store\",\"owner_name\":\"Archie Verania\",\"clearance_number\":\"BBC-2026-0003\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 05:01:51'),
(107, 'admin_test', 'print_action', 'business_application', 3, 'Printed business_clearance for business_application ID #3', '{\"print_type\":\"business_clearance\",\"print_timestamp\":\"2026-05-17 07:02:09\",\"business_name\":\"Sari Sari Store\",\"owner_name\":\"Archie Verania\",\"clearance_number\":\"BBC-2026-0003\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 05:02:09'),
(108, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 05:06:36'),
(109, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 05:06:38'),
(110, 'admin_test', 'status_update', 'certificate_request', 4, 'Updated certificate_request ID #4 status from \'released\' to \'received\'', '{\"old_status\":\"released\",\"new_status\":\"received\",\"timestamp\":\"2026-05-17 07:06:42\",\"certificate_type\":\"BRGY. CLEARANCE\",\"applicant_name\":\"Archie  Verania\",\"queue_ticket_id\":null,\"queue_status_updated\":\"no_queue_ticket\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 05:06:42'),
(111, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 05:06:42'),
(112, 'admin_test', 'page_view', 'admin_panel', NULL, 'Viewed certificate requests admin page', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 05:06:49');

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
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@gumaoc.local', 'super_admin', '2026-05-11 05:14:40', '2026-05-11 05:14:40'),
(2, 'blotter_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Blotter Administrator', 'blotter@gumaoc.local', 'admin', '2026-05-11 05:14:40', '2026-05-11 05:14:40'),
(3, 'admin_test', '$2y$10$vLzVo3PDi2Sqo2.2OgSqaOHIe0OwJ3qlfsYZ33.zrw7h/7FbOdBKa', 'Test Administrator', 'admin_test@gumaoc.local', 'admin', '2026-05-11 14:58:31', '2026-05-11 14:58:31');

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
-- Dumping data for table `barangay_blotter`
--

INSERT INTO `barangay_blotter` (`id`, `blotter_number`, `incident_type`, `complainant_id`, `complainant_name`, `complainant_address`, `complainant_contact`, `respondent_id`, `respondent_name`, `respondent_address`, `respondent_contact`, `incident_date`, `reported_date`, `location`, `description`, `classification`, `status`, `investigating_officer`, `settlement_details`, `action_taken`, `case_disposition`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'BLT-2025-001', 'complaint', NULL, 'Juan Santos Dela Cruz', 'House 101, Block 2 Lot 15, Australia Street, Barangay Gumaoc East', '09123456789', NULL, 'Pedro Reyes', 'House 210, Mabini Street, Barangay Gumaoc East', '09876543210', '2025-05-10 14:30:00', '2026-05-11 13:14:41', 'Corner of Australia Street and Rizal Avenue', 'Noise disturbance from loud music', 'minor', 'filed', NULL, NULL, NULL, NULL, 'admin', '2026-05-11 05:14:41', '2026-05-11 05:14:41'),
(2, 'BLT-2025-002', 'dispute', NULL, 'Maria Reyes Santos', 'House 205, Rizal Avenue, Barangay Gumaoc East', '09234567890', NULL, 'Antonio Garcia', 'House 45, Mabini Street, Barangay Gumaoc East', '09345678901', '2025-05-09 09:15:00', '2026-05-11 13:14:41', 'Rizal Avenue', 'Property boundary dispute', 'major', 'under_investigation', NULL, NULL, NULL, NULL, 'admin', '2026-05-11 05:14:41', '2026-05-11 05:14:41');

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
  `status` enum('pending','reviewing','approved','ready','received','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `business_applications`
--

INSERT INTO `business_applications` (`id`, `user_id`, `reference_no`, `application_date`, `first_name`, `middle_name`, `last_name`, `business_location`, `or_number`, `ctc_number`, `business_name`, `business_type`, `business_address`, `business_description`, `capital_amount`, `owner_name`, `owner_address`, `owner_contact`, `contact_number`, `years_operation`, `investment_capital`, `proof_image`, `submitted_at`, `status`) VALUES
(1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dela Cruz Sari-Sari Store', 'Sari-Sari Store', 'House 101, Block 2 Lot 15, Australia Street, Barangay Gumaoc East', NULL, NULL, 'Juan Santos Dela Cruz', 'House 101, Block 2 Lot 15, Australia Street, Barangay Gumaoc East', NULL, '09123456789', 2, 50000.00, NULL, '2026-05-11 05:14:41', 'approved'),
(2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Santos Carinderia', 'Food Establishment', 'House 205, Rizal Avenue, Barangay Gumaoc East', NULL, NULL, 'Maria Reyes Santos', 'House 205, Rizal Avenue, Barangay Gumaoc East', NULL, '09234567890', 1, 75000.00, NULL, '2026-05-11 05:14:41', 'reviewing'),
(3, 4, 'BA-2026-1341', '2026-05-17', 'Archie', NULL, 'Verania', 'House 18, Kayle, Barangay Gumaoc East, San Jose del Monte, Bulacan, Philippines', '512345', '512341', 'Sari Sari Store', 'Home Business', 'House 18, Kayle, Barangay Gumaoc East, San Jose del Monte, Bulacan, Philippines', NULL, NULL, 'Archie Verania', 'House 18, Kayle, Barangay Gumaoc East, San Jose del Monte, Bulacan, Philippines', NULL, '+639062668190', 4, 16000.00, NULL, '2026-05-17 04:42:33', 'received');

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
  `photo_id` int(11) DEFAULT NULL,
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
  `status` enum('pending','processing','ready','released','received') DEFAULT 'pending',
  `queue_ticket_id` int(11) DEFAULT NULL,
  `queue_ticket_number` varchar(20) DEFAULT NULL,
  `notes` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificate_requests`
--

INSERT INTO `certificate_requests` (`id`, `user_id`, `full_name`, `address`, `mobile_number`, `civil_status`, `gender`, `birth_date`, `birth_place`, `citizenship`, `years_of_residence`, `certificate_type`, `purpose`, `additional_data`, `proof_image`, `photo_2x2`, `photo_id`, `vehicle_make_type`, `motor_no`, `chassis_no`, `plate_no`, `vehicle_color`, `year_model`, `body_no`, `operator_license`, `tricycle_photo`, `submitted_at`, `status`, `queue_ticket_id`, `queue_ticket_number`, `notes`) VALUES
(1, 1, 'Juan Santos Dela Cruz', 'House 101, Block 2 Lot 15, Australia Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09123456789', 'Single', 'Male', '1990-05-15', 'San Jose del Monte, Bulacan', NULL, NULL, 'BRGY. CLEARANCE', 'For employment purposes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-11 05:14:40', 'processing', NULL, NULL, 'Standard clearance request'),
(2, 2, 'Maria Reyes Santos', 'House 205, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09234567890', 'Married', 'Female', '1985-08-22', 'Quezon City', NULL, NULL, 'BRGY. INDIGENCY', 'For scholarship application', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-11 05:14:40', 'processing', NULL, NULL, 'Student indigency certificate'),
(4, 4, 'Archie  Verania', 'House 18, Kayle, Barangay Gumaoc East, San Jose del Monte, Bulacan, Philippines', '+639062668190', 'Single', 'Male', '2000-11-30', 'Quezon City', 'Filipino', 5, 'BRGY. CLEARANCE', 'Academic', '[]', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-16 17:12:43', 'received', NULL, NULL, '');

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
(1, 1, 'Roberto Dela Cruz', 'Ama', '1960-03-20', 65, 'Lalaki', 'Married', 'roberto.dlc@email.com', 'College', 'Retired', 0, 0, 'Carpentry', 15000.00, '2026-05-11 05:14:40'),
(2, 1, 'Elena Dela Cruz', 'Ina', '1962-07-15', 63, 'Babae', 'Married', 'elena.dlc@email.com', 'High School', 'Housewife', 0, 0, 'Cooking', 0.00, '2026-05-11 05:14:40'),
(3, 2, 'Pedro Santos', 'Asawa', '1980-11-30', 44, 'Lalaki', 'Married', 'pedro.santos@email.com', 'College', 'Driver', 0, 0, 'Driving', 18000.00, '2026-05-11 05:14:40');

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
(1, 1, 'Juan Dela Cruz', 'Senior Citizens Association', '2026-05-11 05:14:40'),
(2, 2, 'Maria Santos', 'Women\'s Organization', '2026-05-11 05:14:40');

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
(1, 'C1', 'Counter 1 - All Certificates', 1, NULL, 1, NULL, NULL, '2026-05-11 05:14:41', '2026-05-11 05:14:41'),
(2, 'C2', 'Counter 2 - Business Applications', 6, NULL, 1, NULL, NULL, '2026-05-11 05:14:41', '2026-05-11 05:14:41'),
(3, 'C3', 'Counter 3 - General', 5, NULL, 1, NULL, NULL, '2026-05-11 05:14:41', '2026-05-11 05:14:41');

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
(1, 'Barangay Clearance', 'BC', 'Processing of Barangay Clearance certificates', 15, 50, 1, '2026-05-11 05:14:41', '2026-05-11 05:14:41'),
(2, 'Barangay Indigency', 'BI', 'Processing of Barangay Indigency certificates', 15, 30, 1, '2026-05-11 05:14:41', '2026-05-11 05:14:41'),
(3, 'Tricycle Permit', 'TP', 'Processing of Tricycle Operator Permits', 25, 20, 1, '2026-05-11 05:14:41', '2026-05-11 05:14:41'),
(4, 'Proof of Residency', 'PR', 'Processing of Proof of Residency certificates', 10, 40, 1, '2026-05-11 05:14:41', '2026-05-11 05:14:41'),
(5, 'General Services', 'GS', 'Other barangay services and inquiries', 20, 30, 1, '2026-05-11 05:14:41', '2026-05-11 05:14:41'),
(6, 'Business Permit', 'BP', 'Business permit applications and renewals', 30, 15, 1, '2026-05-11 05:14:41', '2026-05-11 05:14:41');

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
(1, 'BC-20250511-001', 1, 'Juan Santos Dela Cruz', '09123456789', NULL, 'Barangay Clearance for Employment', 'normal', 'waiting', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-11 05:14:41', '2026-05-11 05:14:41'),
(2, 'BI-20250511-001', 2, 'Maria Reyes Santos', '09234567890', NULL, 'Indigency Certificate for Scholarship', 'priority', 'waiting', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-11 05:14:41', '2026-05-11 05:14:41'),
(3, 'GS-20260511-001', 5, 'Archie  Verania', '09062668190', NULL, 'resident_registration: Resident Census Registration Processing', 'normal', 'waiting', 1, '2026-05-11 16:53:01', NULL, NULL, NULL, NULL, NULL, '2026-05-11 14:53:01', '2026-05-11 14:53:01');

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
(1, 'W1', 'Window 1 - Certificates', 1, NULL, 1, NULL, NULL, '2026-05-11 05:14:41', '2026-05-11 05:14:41'),
(2, 'W2', 'Window 2 - Permits', 3, NULL, 1, NULL, NULL, '2026-05-11 05:14:41', '2026-05-11 05:14:41'),
(3, 'W3', 'Window 3 - General Services', 5, NULL, 1, NULL, NULL, '2026-05-11 05:14:41', '2026-05-11 05:14:41');

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
(1, 'Juan', 'Santos', 'Dela Cruz', 'juan.delacruz@email.com', '09123456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'House 101, Block 2 Lot 15, Australia Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '101', 'Gumaoc East', 'BLOCK', 'Roberto Bernardo', 'Barangay Officer', '1990-05-15', 'San Jose del Monte, Bulacan', 'Male', 'Single', '0001234567', '0001234567', 'active', NULL, NULL, '2026-05-11 05:14:40', '2026-05-11 05:14:40', 1, NULL, NULL),
(2, 'Maria', 'Reyes', 'Santos', 'maria.santos@email.com', '09234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'House 205, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', '205', 'Gumaoc East', 'BLOCK', 'Elena Cruz', 'Barangay Staff', '1985-08-22', 'Quezon City', 'Female', 'Married', '0007654321', '0007654321', 'active', NULL, NULL, '2026-05-11 05:14:40', '2026-05-11 05:14:40', 1, NULL, NULL),
(3, 'Antonio', 'Lopez', 'Garcia', 'antonio.garcia@email.com', '09345678901', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'House 45, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '45', 'Gumaoc East', 'BLOCK', 'Carlos Mendez', 'Barangay Officer', '1978-03-10', 'Caloocan City', 'Male', 'Married', NULL, NULL, 'active', NULL, NULL, '2026-05-11 05:14:40', '2026-05-11 05:14:40', 1, NULL, NULL),
(4, 'Archie', '', 'Verania', 'archieverania1130@gmail.com', '09062668190', '$2y$10$YuGOSojNTw2UmiRCV41pfO9tYyzAw63.ZkdnB3.yUor0mQS67xTKS', 'House 18, Kayle, Barangay Gumaoc East, San Jose del Monte, Bulacan, Philippines', '18', 'Gumaoc East', 'BLOCK', 'Jennilyn B. Dela Cruz', 'Secretary', '2000-11-30', 'Quezon City', 'Male', 'Single', NULL, NULL, 'active', NULL, NULL, '2026-05-11 14:53:01', '2026-05-12 06:04:13', 1, NULL, NULL);

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
(1, 'Juan', 'Santos', 'Dela Cruz', '1990-05-15', 'San Jose del Monte, Bulacan', 35, 'Single', 'Male', '09123456789', 'juan.delacruz@email.com', '101', 'Block 2 Lot 15, Australia Street', 'Pag-aari', '2026-05-11 05:14:40', 'approved', 'Pag-aari', NULL, 'Pag-aari', NULL, 'Wala', 'Kuryente', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Radyo,Telebisyon,Refrigerator', 'Kotse', NULL, 'Sari-Sari Store', NULL, 'Condom', 'Roberto Bernardo', 'Barangay Officer', NULL, NULL),
(2, 'Maria', 'Reyes', 'Santos', '1985-08-22', 'Quezon City', 40, 'Married', 'Female', '09234567890', 'maria.santos@email.com', '205', 'Rizal Avenue', 'Employment', '2026-05-11 05:14:40', 'pending', 'Sariling Lupa', NULL, 'Sariling Bahay', NULL, 'Wala', 'LPG', NULL, 'Flush', NULL, 'Kuryente', NULL, 'Water District', NULL, 'Kinokolekta', NULL, 'Telebisyon', 'Motorsiklo', NULL, 'Patahian', NULL, 'Pills', 'Elena Cruz', 'Barangay Staff', NULL, NULL),
(3, 'Archie', '', 'Verania', '2000-11-30', 'Quezon City', 25, 'Single', 'Male', '09062668190', 'archieverania1130@gmail.com', '18', 'Kayle', 'Pag-aari', '2026-05-11 14:53:01', 'approved', 'Pag-aari', '', 'Pag-aari', '', 'Pag-aari', 'Gaas', '', 'Flush', '', 'Kuryente', '', 'Poso Artesiyano', '', 'Sinusunog', '', 'Radyo/Stereo', 'Sasakyan', '', 'Sari-Sari Store', '', 'Pills,Wala', 'Jennilyn B. Dela Cruz', 'Secretary', '', '');

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
(1, '0001234567', 'assigned', '2026-05-11 05:14:41', '2025-05-11 02:30:00', 1, 'juan.delacruz@email.com', 1, NULL, '2026-05-11 05:14:41', '2026-05-11 05:14:41'),
(2, '0007654321', 'assigned', '2026-05-11 05:14:41', '2025-05-11 03:15:00', 2, 'maria.santos@email.com', 1, NULL, '2026-05-11 05:14:41', '2026-05-11 05:14:41');

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
(1, 'Emergency Response', 'Real-time incident reporting with IoT sensors and instant emergency response coordination.', '🚨', 'Report Incident', 'pages/report.php', 1, 'IoT Enabled,24/7 Monitoring', 0, '2026-05-11 05:14:41', '2026-05-11 05:14:41'),
(2, 'Document Requests', 'Request certificates, clearances, and official documents online with automated processing.', '📄', 'Apply Now', 'pages/forms.php', 0, 'Online Processing,Fast Approval', 0, '2026-05-11 05:14:41', '2026-05-11 05:14:41'),
(3, 'Community Census', 'Register as a resident and contribute to our comprehensive community database.', '👥', 'Register', 'pages/forms.php', 0, 'Digital Registry,Secure Data', 0, '2026-05-11 05:14:41', '2026-05-11 05:14:41');

-- --------------------------------------------------------

--
-- Table structure for table `updates`
--

CREATE TABLE `updates` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
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

INSERT INTO `updates` (`id`, `title`, `description`, `image`, `badge_text`, `badge_type`, `date`, `status`, `is_priority`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'COVID-19 Vaccination Drive', 'New vaccination schedule available. Free vaccination for all residents. Register online to secure your slot.', NULL, 'Important', 'important', 'May 15, 2026', 'published', 1, 0, '2026-05-11 05:14:41', '2026-05-17 04:34:13'),
(4, 'test', 'test', NULL, 'General Info', 'info', 'May 16, 2026', 'published', 0, 0, '2026-05-16 17:29:58', '2026-05-17 04:34:15'),
(5, 'test', 'test', 'ann_1778992524_3bc9a458.jpg', 'General Info', 'info', 'May 17, 2026', 'published', 1, 1, '2026-05-17 04:35:24', '2026-05-17 04:35:24');

-- --------------------------------------------------------

--
-- Table structure for table `user_photos`
--

CREATE TABLE `user_photos` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `certificate_request_id` int(11) DEFAULT NULL,
  `photo_filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `photo_path` varchar(500) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_photos`
--

INSERT INTO `user_photos` (`id`, `user_id`, `certificate_request_id`, `photo_filename`, `original_filename`, `photo_path`, `is_default`, `is_active`, `uploaded_at`) VALUES
(1, 4, 4, 'photo_4_1778951563.jpg', '61X6TToE4fL._UF894,1000_QL80_.jpg', 'uploads/user_photos/photo_4_1778951563.jpg', 1, 1, '2026-05-16 17:12:43');

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
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `action_type` (`action_type`),
  ADD KEY `created_at` (`created_at`);

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
  ADD KEY `classification` (`classification`),
  ADD KEY `idx_barangay_blotter_status` (`status`,`incident_date`);

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
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`),
  ADD KEY `business_type` (`business_type`),
  ADD KEY `submitted_at` (`submitted_at`);

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
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`),
  ADD KEY `certificate_type` (`certificate_type`),
  ADD KEY `submitted_at` (`submitted_at`),
  ADD KEY `idx_certificate_requests_status` (`status`,`submitted_at`);

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
  ADD KEY `user_id` (`user_id`),
  ADD KEY `type` (`type`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `idx_notifications_user_read` (`user_id`,`is_read`);

--
-- Indexes for table `queue_counters`
--
ALTER TABLE `queue_counters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `counter_number` (`counter_number`),
  ADD KEY `service_id` (`service_id`);

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
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `residents`
--
ALTER TABLE `residents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `rfid_code` (`rfid_code`),
  ADD KEY `status` (`status`),
  ADD KEY `barangay` (`barangay`),
  ADD KEY `house_number` (`house_number`),
  ADD KEY `interviewer` (`interviewer`),
  ADD KEY `birth_place` (`birth_place`),
  ADD KEY `idx_residents_name` (`first_name`,`last_name`);

--
-- Indexes for table `resident_registrations`
--
ALTER TABLE `resident_registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `submitted_at` (`submitted_at`),
  ADD KEY `email` (`email`),
  ADD KEY `idx_resident_registrations_status` (`status`,`submitted_at`);

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
  ADD KEY `user_id` (`user_id`),
  ADD KEY `rfid_tag` (`rfid_tag`),
  ADD KEY `access_time` (`access_time`);

--
-- Indexes for table `rfid_registrations`
--
ALTER TABLE `rfid_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rfid_number` (`rfid_number`),
  ADD KEY `status` (`status`),
  ADD KEY `card_type` (`card_type`);

--
-- Indexes for table `rfid_users`
--
ALTER TABLE `rfid_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rfid_tag` (`rfid_tag`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `scanned_rfid_codes`
--
ALTER TABLE `scanned_rfid_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rfid_code` (`rfid_code`),
  ADD KEY `status` (`status`),
  ADD KEY `assigned_to_resident_id` (`assigned_to_resident_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `is_featured` (`is_featured`),
  ADD KEY `display_order` (`display_order`);

--
-- Indexes for table `updates`
--
ALTER TABLE `updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `badge_type` (`badge_type`),
  ADD KEY `is_priority` (`is_priority`),
  ADD KEY `display_order` (`display_order`);

--
-- Indexes for table `user_photos`
--
ALTER TABLE `user_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_photos_user` (`user_id`),
  ADD KEY `idx_user_photos_request` (`certificate_request_id`);

--
-- Indexes for table `user_reports`
--
ALTER TABLE `user_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `incident_type` (`incident_type`),
  ADD KEY `status` (`status`),
  ADD KEY `priority` (`priority`),
  ADD KEY `created_at` (`created_at`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `captain_clearances`
--
ALTER TABLE `captain_clearances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `certificate_requests`
--
ALTER TABLE `certificate_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `family_disabilities`
--
ALTER TABLE `family_disabilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `queue_counters`
--
ALTER TABLE `queue_counters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `queue_services`
--
ALTER TABLE `queue_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `queue_tickets`
--
ALTER TABLE `queue_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `queue_windows`
--
ALTER TABLE `queue_windows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `resident_registrations`
--
ALTER TABLE `resident_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `resident_status`
--
ALTER TABLE `resident_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rfid_access_logs`
--
ALTER TABLE `rfid_access_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rfid_registrations`
--
ALTER TABLE `rfid_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rfid_users`
--
ALTER TABLE `rfid_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `scanned_rfid_codes`
--
ALTER TABLE `scanned_rfid_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `updates`
--
ALTER TABLE `updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_photos`
--
ALTER TABLE `user_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- Constraints for table `captain_clearances`
--
ALTER TABLE `captain_clearances`
  ADD CONSTRAINT `captain_clearances_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resident_status`
--
ALTER TABLE `resident_status`
  ADD CONSTRAINT `resident_status_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
