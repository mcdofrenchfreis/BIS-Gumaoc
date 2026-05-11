-- =====================================================
-- BIS Gumaoc Barangay Information System
-- XAMPP MySQL Database Seed File
-- Generated: May 11, 2026
-- =====================================================
-- 
-- This file contains the complete database structure and seed data
-- for the Barangay Information System of Gumaoc East
-- 
-- Instructions:
-- 1. Start XAMPP and ensure MySQL is running
-- 2. Import this file using phpMyAdmin or MySQL command line
-- 3. Create database named: gumaoc_db (or modify below)
-- 4. Run this script to create tables and insert sample data
-- =====================================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `gumaoc_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `gumaoc_db`;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables to ensure clean installation
DROP TABLE IF EXISTS `admin_logs`;
DROP TABLE IF EXISTS `access_logs`;
DROP TABLE IF EXISTS `blotter_attachments`;
DROP TABLE IF EXISTS `barangay_blotter`;
DROP TABLE IF EXISTS `captain_clearances`;
DROP TABLE IF EXISTS `business_applications`;
DROP TABLE IF EXISTS `certificate_requests`;
DROP TABLE IF EXISTS `family_disabilities`;
DROP TABLE IF EXISTS `family_members`;
DROP TABLE IF EXISTS `family_organizations`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `queue_tickets`;
DROP TABLE IF EXISTS `queue_windows`;
DROP TABLE IF EXISTS `queue_counters`;
DROP TABLE IF EXISTS `queue_services`;
DROP TABLE IF EXISTS `resident_status`;
DROP TABLE IF EXISTS `resident_registrations`;
DROP TABLE IF EXISTS `residents`;
DROP TABLE IF EXISTS `rfid_access_logs`;
DROP TABLE IF EXISTS `rfid_registrations`;
DROP TABLE IF EXISTS `rfid_users`;
DROP TABLE IF EXISTS `scanned_rfid_codes`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `updates`;
DROP TABLE IF EXISTS `user_reports`;
DROP TABLE IF EXISTS `admin_users`;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- CORE ADMINISTRATION TABLES
-- =====================================================

-- Admin users table
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('super_admin','admin') NOT NULL DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default admin users
INSERT INTO `admin_users` (`id`, `username`, `password`, `full_name`, `email`, `role`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@gumaoc.local', 'super_admin'),
(2, 'blotter_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Blotter Administrator', 'blotter@gumaoc.local', 'admin');

-- Admin logs table
CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` varchar(100) DEFAULT 'system',
  `action_type` varchar(50) NOT NULL,
  `target_type` varchar(50) NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `action_type` (`action_type`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- RESIDENT MANAGEMENT TABLES
-- =====================================================

-- Residents table (main user accounts)
CREATE TABLE `residents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `relationship_to_head` varchar(100) DEFAULT NULL COMMENT 'Relationship to head of family',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `rfid_code` (`rfid_code`),
  KEY `status` (`status`),
  KEY `barangay` (`barangay`),
  KEY `house_number` (`house_number`),
  KEY `interviewer` (`interviewer`),
  KEY `birth_place` (`birth_place`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample resident data
INSERT INTO `residents` (`id`, `first_name`, `middle_name`, `last_name`, `email`, `phone`, `password`, `address`, `house_number`, `barangay`, `sitio`, `interviewer`, `interviewer_title`, `birthdate`, `birth_place`, `gender`, `civil_status`, `rfid_code`, `rfid`, `status`, `profile_complete`) VALUES
(1, 'Juan', 'Santos', 'Dela Cruz', 'juan.delacruz@email.com', '09123456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'House 101, Block 2 Lot 15, Australia Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '101', 'Gumaoc East', 'BLOCK', 'Roberto Bernardo', 'Barangay Officer', '1990-05-15', 'San Jose del Monte, Bulacan', 'Male', 'Single', '0001234567', '0001234567', 'active', 1),
(2, 'Maria', 'Reyes', 'Santos', 'maria.santos@email.com', '09234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'House 205, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', '205', 'Gumaoc East', 'BLOCK', 'Elena Cruz', 'Barangay Staff', '1985-08-22', 'Quezon City', 'Female', 'Married', '0007654321', '0007654321', 'active', 1),
(3, 'Antonio', 'Lopez', 'Garcia', 'antonio.garcia@email.com', '09345678901', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'House 45, Mabini Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '45', 'Gumaoc East', 'BLOCK', 'Carlos Mendez', 'Barangay Officer', '1978-03-10', 'Caloocan City', 'Male', 'Married', NULL, NULL, 'active', 1);

-- Resident registrations table (census data)
CREATE TABLE `resident_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `resident_organization` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `submitted_at` (`submitted_at`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample registration data
INSERT INTO `resident_registrations` (`id`, `first_name`, `middle_name`, `last_name`, `birth_date`, `birth_place`, `age`, `civil_status`, `gender`, `contact_number`, `email`, `house_number`, `address`, `pangkabuhayan`, `status`, `land_ownership`, `house_ownership`, `farmland`, `cooking_energy`, `toilet_type`, `electricity_source`, `water_source`, `waste_disposal`, `appliances`, `transportation`, `business`, `contraceptive`, `interviewer`, `interviewer_title`) VALUES
(1, 'Juan', 'Santos', 'Dela Cruz', '1990-05-15', 'San Jose del Monte, Bulacan', 35, 'Single', 'Male', '09123456789', 'juan.delacruz@email.com', '101', 'Block 2 Lot 15, Australia Street', 'Pag-aari', 'approved', 'Pag-aari', 'Pag-aari', 'Wala', 'Kuryente', 'Flush', 'Kuryente', 'Water District', 'Kinokolekta', 'Radyo,Telebisyon,Refrigerator', 'Kotse', 'Sari-Sari Store', 'Condom', 'Roberto Bernardo', 'Barangay Officer'),
(2, 'Maria', 'Reyes', 'Santos', '1985-08-22', 'Quezon City', 40, 'Married', 'Female', '09234567890', 'maria.santos@email.com', '205', 'Rizal Avenue', 'Employment', 'pending', 'Sariling Lupa', 'Sariling Bahay', 'Wala', 'LPG', 'Flush', 'Kuryente', 'Water District', 'Kinokolekta', 'Telebisyon', 'Motorsiklo', 'Patahian', 'Pills', 'Elena Cruz', 'Barangay Staff');

-- Family members table
CREATE TABLE `family_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `registration_id` (`registration_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample family members
INSERT INTO `family_members` (`id`, `registration_id`, `full_name`, `relationship`, `birth_date`, `age`, `gender`, `civil_status`, `email`, `education`, `occupation`, `is_deceased`, `has_account`, `skills`, `monthly_income`) VALUES
(1, 1, 'Roberto Dela Cruz', 'Ama', '1960-03-20', 65, 'Lalaki', 'Married', 'roberto.dlc@email.com', 'College', 'Retired', 0, 0, 'Carpentry', 15000.00),
(2, 1, 'Elena Dela Cruz', 'Ina', '1962-07-15', 63, 'Babae', 'Married', 'elena.dlc@email.com', 'High School', 'Housewife', 0, 0, 'Cooking', 0.00),
(3, 2, 'Pedro Santos', 'Asawa', '1980-11-30', 44, 'Lalaki', 'Married', 'pedro.santos@email.com', 'College', 'Driver', 0, 0, 'Driving', 18000.00);

-- Family organizations table
CREATE TABLE `family_organizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `registration_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `organization_type` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `registration_id` (`registration_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample family organizations
INSERT INTO `family_organizations` (`id`, `registration_id`, `name`, `organization_type`) VALUES
(1, 1, 'Juan Dela Cruz', 'Senior Citizens Association'),
(2, 2, 'Maria Santos', 'Women\'s Organization');

-- Family disabilities table
CREATE TABLE `family_disabilities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `registration_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `disability_type` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `registration_id` (`registration_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- CERTIFICATE AND BUSINESS APPLICATIONS
-- =====================================================

-- Certificate requests table
CREATE TABLE `certificate_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `notes` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `certificate_type` (`certificate_type`),
  KEY `submitted_at` (`submitted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample certificate requests
INSERT INTO `certificate_requests` (`id`, `user_id`, `full_name`, `address`, `mobile_number`, `civil_status`, `gender`, `birth_date`, `birth_place`, `certificate_type`, `purpose`, `status`, `notes`) VALUES
(1, 1, 'Juan Santos Dela Cruz', 'House 101, Block 2 Lot 15, Australia Street, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09123456789', 'Single', 'Male', '1990-05-15', 'San Jose del Monte, Bulacan', 'BRGY. CLEARANCE', 'For employment purposes', 'pending', 'Standard clearance request'),
(2, 2, 'Maria Reyes Santos', 'House 205, Rizal Avenue, Barangay Gumaoc East, San Jose del Monte, Bulacan', '09234567890', 'Married', 'Female', '1985-08-22', 'Quezon City', 'BRGY. INDIGENCY', 'For scholarship application', 'processing', 'Student indigency certificate');

-- Business applications table
CREATE TABLE `business_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `status` enum('pending','reviewing','approved','rejected') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `business_type` (`business_type`),
  KEY `submitted_at` (`submitted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample business applications
INSERT INTO `business_applications` (`id`, `user_id`, `business_name`, `business_type`, `business_address`, `owner_name`, `owner_address`, `contact_number`, `years_operation`, `investment_capital`, `status`) VALUES
(1, 1, 'Dela Cruz Sari-Sari Store', 'Sari-Sari Store', 'House 101, Block 2 Lot 15, Australia Street, Barangay Gumaoc East', 'Juan Santos Dela Cruz', 'House 101, Block 2 Lot 15, Australia Street, Barangay Gumaoc East', '09123456789', 2, 50000.00, 'pending'),
(2, 2, 'Santos Carinderia', 'Food Establishment', 'House 205, Rizal Avenue, Barangay Gumaoc East', 'Maria Reyes Santos', 'House 205, Rizal Avenue, Barangay Gumaoc East', '09234567890', 1, 75000.00, 'reviewing');

-- =====================================================
-- BLOTTER AND SECURITY SYSTEM
-- =====================================================

-- Barangay blotter table
CREATE TABLE `barangay_blotter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `blotter_number` (`blotter_number`),
  KEY `complainant_id` (`complainant_id`),
  KEY `respondent_id` (`respondent_id`),
  KEY `incident_date` (`incident_date`),
  KEY `status` (`status`),
  KEY `classification` (`classification`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample blotter entries
INSERT INTO `barangay_blotter` (`id`, `blotter_number`, `incident_type`, `complainant_name`, `complainant_address`, `complainant_contact`, `respondent_name`, `respondent_address`, `respondent_contact`, `incident_date`, `location`, `description`, `classification`, `status`, `created_by`) VALUES
(1, 'BLT-2025-001', 'complaint', 'Juan Santos Dela Cruz', 'House 101, Block 2 Lot 15, Australia Street, Barangay Gumaoc East', '09123456789', 'Pedro Reyes', 'House 210, Mabini Street, Barangay Gumaoc East', '09876543210', '2025-05-10 14:30:00', 'Corner of Australia Street and Rizal Avenue', 'Noise disturbance from loud music', 'minor', 'filed', 'admin'),
(2, 'BLT-2025-002', 'dispute', 'Maria Reyes Santos', 'House 205, Rizal Avenue, Barangay Gumaoc East', '09234567890', 'Antonio Garcia', 'House 45, Mabini Street, Barangay Gumaoc East', '09345678901', '2025-05-09 09:15:00', 'Rizal Avenue', 'Property boundary dispute', 'major', 'under_investigation', 'admin');

-- Blotter attachments table
CREATE TABLE `blotter_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blotter_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_by` varchar(100) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `blotter_id` (`blotter_id`),
  CONSTRAINT `blotter_attachments_ibfk_1` FOREIGN KEY (`blotter_id`) REFERENCES `barangay_blotter` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Resident status table (for tracking resident records and clearances)
CREATE TABLE `resident_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `resident_id` (`resident_id`),
  KEY `record_status` (`record_status`),
  KEY `requires_captain_clearance` (`requires_captain_clearance`),
  CONSTRAINT `resident_status_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Captain clearances table
CREATE TABLE `captain_clearances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resident_id` int(11) NOT NULL,
  `clearance_type` enum('form_access','certificate_request','business_permit','general') NOT NULL,
  `reason` text NOT NULL,
  `granted_by` varchar(100) NOT NULL,
  `granted_date` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  `status` enum('active','expired','revoked') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `resident_id` (`resident_id`),
  KEY `clearance_type` (`clearance_type`),
  KEY `status` (`status`),
  CONSTRAINT `captain_clearances_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- QUEUE MANAGEMENT SYSTEM
-- =====================================================

-- Queue services table
CREATE TABLE `queue_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_name` varchar(100) NOT NULL,
  `service_code` varchar(10) NOT NULL,
  `description` text DEFAULT NULL,
  `estimated_time` int(11) DEFAULT 15 COMMENT 'Estimated time in minutes',
  `max_daily_capacity` int(11) DEFAULT 50,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_code` (`service_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default queue services
INSERT INTO `queue_services` (`id`, `service_name`, `service_code`, `description`, `estimated_time`, `max_daily_capacity`, `is_active`) VALUES
(1, 'Barangay Clearance', 'BC', 'Processing of Barangay Clearance certificates', 15, 50, 1),
(2, 'Barangay Indigency', 'BI', 'Processing of Barangay Indigency certificates', 15, 30, 1),
(3, 'Tricycle Permit', 'TP', 'Processing of Tricycle Operator Permits', 25, 20, 1),
(4, 'Proof of Residency', 'PR', 'Processing of Proof of Residency certificates', 10, 40, 1),
(5, 'General Services', 'GS', 'Other barangay services and inquiries', 20, 30, 1),
(6, 'Business Permit', 'BP', 'Business permit applications and renewals', 30, 15, 1);

-- Queue counters table
CREATE TABLE `queue_counters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `counter_number` varchar(10) NOT NULL,
  `counter_name` varchar(50) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `operator_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `current_ticket_id` int(11) DEFAULT NULL,
  `last_called_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `counter_number` (`counter_number`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default queue counters
INSERT INTO `queue_counters` (`id`, `counter_number`, `counter_name`, `service_id`, `is_active`) VALUES
(1, 'C1', 'Counter 1 - All Certificates', 1, 1),
(2, 'C2', 'Counter 2 - Business Applications', 6, 1),
(3, 'C3', 'Counter 3 - General', 5, 1);

-- Queue tickets table
CREATE TABLE `queue_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_number` (`ticket_number`),
  KEY `service_id` (`service_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample queue tickets
INSERT INTO `queue_tickets` (`id`, `ticket_number`, `service_id`, `customer_name`, `mobile_number`, `purpose`, `priority_level`, `status`, `queue_position`) VALUES
(1, 'BC-20250511-001', 1, 'Juan Santos Dela Cruz', '09123456789', 'Barangay Clearance for Employment', 'normal', 'waiting', 1),
(2, 'BI-20250511-001', 2, 'Maria Reyes Santos', '09234567890', 'Indigency Certificate for Scholarship', 'priority', 'waiting', 1);

-- Queue windows table
CREATE TABLE `queue_windows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `window_number` varchar(10) NOT NULL,
  `window_name` varchar(50) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `operator_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `current_ticket_id` int(11) DEFAULT NULL,
  `last_called_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `window_number` (`window_number`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default queue windows
INSERT INTO `queue_windows` (`id`, `window_number`, `window_name`, `service_id`, `is_active`) VALUES
(1, 'W1', 'Window 1 - Certificates', 1, 1),
(2, 'W2', 'Window 2 - Permits', 3, 1),
(3, 'W3', 'Window 3 - General Services', 5, 1);

-- =====================================================
-- RFID SYSTEM
-- =====================================================

-- Scanned RFID codes table
CREATE TABLE `scanned_rfid_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rfid_code` varchar(50) NOT NULL,
  `status` enum('available','assigned','disabled','archived') DEFAULT 'available',
  `scanned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_at` timestamp NULL DEFAULT NULL,
  `assigned_to_resident_id` int(11) DEFAULT NULL,
  `assigned_to_email` varchar(255) DEFAULT NULL,
  `scanned_by_admin_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rfid_code` (`rfid_code`),
  KEY `status` (`status`),
  KEY `assigned_to_resident_id` (`assigned_to_resident_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample RFID codes
INSERT INTO `scanned_rfid_codes` (`id`, `rfid_code`, `status`, `assigned_at`, `assigned_to_resident_id`, `assigned_to_email`, `scanned_by_admin_id`) VALUES
(1, '0001234567', 'assigned', '2025-05-11 10:30:00', 1, 'juan.delacruz@email.com', 1),
(2, '0007654321', 'assigned', '2025-05-11 11:15:00', 2, 'maria.santos@email.com', 1);

-- RFID registrations table
CREATE TABLE `rfid_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rfid_number` (`rfid_number`),
  KEY `status` (`status`),
  KEY `card_type` (`card_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- RFID users table
CREATE TABLE `rfid_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rfid_tag` varchar(20) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `id_type` enum('National ID','Drivers License','Passport','Other') DEFAULT 'National ID',
  `id_number` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rfid_tag` (`rfid_tag`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- RFID access logs table
CREATE TABLE `rfid_access_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `rfid_tag` varchar(20) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `access_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `rfid_tag` (`rfid_tag`),
  KEY `access_time` (`access_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- NOTIFICATIONS AND LOGGING
-- =====================================================

-- Notifications table
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error','queue','certificate') DEFAULT 'info',
  `action_url` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `type` (`type`),
  KEY `is_read` (`is_read`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Access logs table
CREATE TABLE `access_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resident_id` int(11) DEFAULT NULL,
  `form_type` varchar(100) NOT NULL,
  `access_granted` tinyint(1) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resident_id` (`resident_id`),
  KEY `form_type` (`form_type`),
  KEY `attempted_at` (`attempted_at`),
  CONSTRAINT `access_logs_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- CONTENT MANAGEMENT
-- =====================================================

-- Services table (for homepage display)
CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(50) NOT NULL,
  `button_text` varchar(100) NOT NULL,
  `button_link` varchar(255) NOT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `features` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `is_featured` (`is_featured`),
  KEY `display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default services
INSERT INTO `services` (`id`, `title`, `description`, `icon`, `button_text`, `button_link`, `is_featured`, `features`, `display_order`) VALUES
(1, 'Emergency Response', 'Real-time incident reporting with IoT sensors and instant emergency response coordination.', '🚨', 'Report Incident', 'pages/report.php', 1, 'IoT Enabled,24/7 Monitoring', 0),
(2, 'Document Requests', 'Request certificates, clearances, and official documents online with automated processing.', '📄', 'Apply Now', 'pages/forms.php', 0, 'Online Processing,Fast Approval', 0),
(3, 'Community Census', 'Register as a resident and contribute to our comprehensive community database.', '👥', 'Register', 'pages/forms.php', 0, 'Digital Registry,Secure Data', 0);

-- Updates table (for news and announcements)
CREATE TABLE `updates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `badge_text` varchar(50) NOT NULL,
  `badge_type` enum('important','new','community','info') DEFAULT 'info',
  `date` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `is_priority` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `badge_type` (`badge_type`),
  KEY `is_priority` (`is_priority`),
  KEY `display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default updates
INSERT INTO `updates` (`id`, `title`, `description`, `badge_text`, `badge_type`, `date`, `status`, `is_priority`, `display_order`) VALUES
(1, 'COVID-19 Vaccination Drive', 'New vaccination schedule available. Free vaccination for all residents. Register online to secure your slot.', 'Important', 'important', 'May 15, 2026', '🟢 Active', 1, 0),
(2, 'Enhanced E-Services Launch', 'Our improved digital platform now offers faster processing, better security, and mobile optimization.', 'New', 'new', 'May 10, 2026', '🟢 Live', 0, 0),
(3, 'Town Fiesta 2026', 'Join us for our annual town celebration. Cultural shows, local food, and community activities for everyone.', 'Community', 'community', 'June 1, 2026', '🔵 Upcoming', 0, 0);

-- User reports table
CREATE TABLE `user_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `incident_type` (`incident_type`),
  KEY `status` (`status`),
  KEY `priority` (`priority`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- STORED PROCEDURES AND FUNCTIONS
-- =====================================================

DELIMITER $$

-- Function to update resident status based on blotter records
CREATE FUNCTION `update_resident_status` (`resident_id_param` INT) RETURNS VARCHAR(20) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC READS SQL DATA
BEGIN
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

-- =====================================================
-- TRIGGERS
-- =====================================================

DELIMITER $$

-- Trigger to auto-update resident status when blotter record changes
CREATE TRIGGER `update_resident_status_after_blotter_insert`
AFTER INSERT ON `barangay_blotter`
FOR EACH ROW
BEGIN
    -- Update complainant status if they are a registered resident
    IF NEW.complainant_id IS NOT NULL THEN
        SET @result = update_resident_status(NEW.complainant_id);
    END IF;
    
    -- Update respondent status if they are a registered resident
    IF NEW.respondent_id IS NOT NULL THEN
        SET @result = update_resident_status(NEW.respondent_id);
    END IF;
END$$

CREATE TRIGGER `update_resident_status_after_blotter_update`
AFTER UPDATE ON `barangay_blotter`
FOR EACH ROW
BEGIN
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
END$$

DELIMITER ;

-- =====================================================
-- FINAL SETUP AND INDEXES
-- =====================================================

-- Create additional indexes for performance
CREATE INDEX idx_residents_name ON residents(first_name, last_name);
CREATE INDEX idx_resident_registrations_status ON resident_registrations(status, submitted_at);
CREATE INDEX idx_certificate_requests_status ON certificate_requests(status, submitted_at);
CREATE INDEX idx_barangay_blotter_status ON barangay_blotter(status, incident_date);
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read);

-- Reset auto-increment values to start from 1 for clean installation
ALTER TABLE admin_users AUTO_INCREMENT = 1;
ALTER TABLE residents AUTO_INCREMENT = 1;
ALTER TABLE resident_registrations AUTO_INCREMENT = 1;
ALTER TABLE certificate_requests AUTO_INCREMENT = 1;
ALTER TABLE business_applications AUTO_INCREMENT = 1;
ALTER TABLE barangay_blotter AUTO_INCREMENT = 1;
ALTER TABLE queue_tickets AUTO_INCREMENT = 1;

-- =====================================================
-- IMPORT COMPLETION MESSAGE
-- =====================================================

SELECT 'Database setup completed successfully!' AS status;
SELECT 'Default admin credentials:' AS info;
SELECT 'Username: admin' AS username;
SELECT 'Password: admin123' AS password;
SELECT 'Database: gumaoc_db' AS database_name;
SELECT 'Total tables created:' AS message, COUNT(*) AS table_count FROM information_schema.tables WHERE table_schema = 'gumaoc_db';

-- =====================================================
-- END OF SEED FILE
-- =====================================================
