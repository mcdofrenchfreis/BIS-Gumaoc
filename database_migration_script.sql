-- Migration Script: Update gumaoc_db to latest version
-- Date: 2025-08-26
-- This script updates the original database schema to match the latest version

-- 1. Add new tables that are missing in the original database

-- Table: access_logs
CREATE TABLE IF NOT EXISTS `access_logs` (
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

-- Table: barangay_blotter
CREATE TABLE IF NOT EXISTS `barangay_blotter` (
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

-- Table: blotter_attachments
CREATE TABLE IF NOT EXISTS `blotter_attachments` (
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

-- Table: captain_clearances
CREATE TABLE IF NOT EXISTS `captain_clearances` (
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

-- Table: resident_status
CREATE TABLE IF NOT EXISTS `resident_status` (
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

-- Table: user_reports
CREATE TABLE IF NOT EXISTS `user_reports` (
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
  CONSTRAINT `user_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Modify existing tables to match the latest schema

-- Modify admin_users table to add new admin user
INSERT IGNORE INTO `admin_users` (`id`, `username`, `password`, `full_name`, `email`, `role`, `created_at`, `updated_at`) VALUES
(2, 'blotter_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Blotter Administrator', 'blotter@gumaoc.local', 'admin', '2025-08-25 13:45:36', '2025-08-25 13:45:36');

-- Modify business_applications table to add new columns
ALTER TABLE `business_applications` 
  ADD COLUMN IF NOT EXISTS `business_description` text DEFAULT NULL AFTER `business_address`,
  ADD COLUMN IF NOT EXISTS `capital_amount` decimal(15,2) DEFAULT NULL AFTER `business_description`,
  ADD COLUMN IF NOT EXISTS `owner_contact` varchar(20) DEFAULT NULL AFTER `owner_address`,
  ADD COLUMN IF NOT EXISTS `proof_image` varchar(255) DEFAULT NULL COMMENT 'Optional proof image filename for the business application' AFTER `investment_capital`;

-- Modify certificate_requests table to add new columns and modify existing ones
ALTER TABLE `certificate_requests`
  ADD COLUMN IF NOT EXISTS `user_id` int(11) NOT NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `additional_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_data`)) AFTER `purpose`,
  ADD COLUMN IF NOT EXISTS `proof_image` varchar(255) DEFAULT NULL AFTER `additional_data`,
  ADD COLUMN IF NOT EXISTS `notes` text NOT NULL AFTER `queue_ticket_number`,
  ADD KEY IF NOT EXISTS `idx_user_id` (`user_id`),
  ADD KEY IF NOT EXISTS `idx_certificate_type` (`certificate_type`),
  ADD KEY IF NOT EXISTS `idx_status` (`status`),
  ADD KEY IF NOT EXISTS `idx_submitted_at` (`submitted_at`);

-- Update certificate_requests to set user_id for existing records (assuming user_id 9 for the existing record)
UPDATE `certificate_requests` SET `user_id` = 9 WHERE `id` = 14 AND `user_id` = 0;

-- Add foreign key constraint for certificate_requests user_id
-- First, we need to remove any invalid records that would violate the constraint
DELETE FROM `certificate_requests` WHERE `user_id` NOT IN (SELECT `id` FROM `residents`) AND `user_id` != 0;
-- Then add the constraint
ALTER TABLE `certificate_requests` 
  ADD CONSTRAINT `certificate_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `residents` (`id`);

-- 3. Create functions and triggers

-- Drop the function if it exists to avoid conflicts
DROP FUNCTION IF EXISTS `update_resident_status`;

-- Function: update_resident_status
DELIMITER $$
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

-- Drop triggers if they exist to avoid conflicts
DROP TRIGGER IF EXISTS `update_resident_status_after_blotter_insert`;
DROP TRIGGER IF EXISTS `update_resident_status_after_blotter_update`;

-- Trigger: update_resident_status_after_blotter_insert
DELIMITER $$
CREATE TRIGGER `update_resident_status_after_blotter_insert` AFTER INSERT ON `barangay_blotter` FOR EACH ROW 
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
DELIMITER ;

-- Trigger: update_resident_status_after_blotter_update
DELIMITER $$
CREATE TRIGGER `update_resident_status_after_blotter_update` AFTER UPDATE ON `barangay_blotter` FOR EACH ROW 
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

-- 4. Insert sample data for new tables (if needed)

-- Insert sample data for resident_status
INSERT IGNORE INTO `resident_status` (`id`, `resident_id`, `resident_name`, `record_status`, `total_complaints`, `total_incidents`, `pending_cases`, `resolved_cases`, `requires_captain_clearance`, `captain_clearance_granted`, `captain_clearance_date`, `captain_clearance_reason`, `captain_clearance_expires`, `last_incident_date`, `last_updated`, `created_at`) VALUES
(1, 7, 'Mar Yvan Sagun Dela Cruz', 'good', 1, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, '2025-08-25 14:14:28', '2025-08-25 14:14:28'),
(2, 8, 'TEST ME PLEASE', 'major_issues', 0, 1, 1, 0, 1, 0, NULL, NULL, NULL, NULL, '2025-08-25 14:14:28', '2025-08-25 14:14:28');

-- 5. Update AUTO_INCREMENT values to match the latest schema

-- Update AUTO_INCREMENT values for all tables
ALTER TABLE `access_logs` AUTO_INCREMENT = 1;
ALTER TABLE `admin_logs` AUTO_INCREMENT = 57;
ALTER TABLE `admin_users` AUTO_INCREMENT = 3;
ALTER TABLE `barangay_blotter` AUTO_INCREMENT = 2;
ALTER TABLE `blotter_attachments` AUTO_INCREMENT = 1;
ALTER TABLE `business_applications` AUTO_INCREMENT = 3;
ALTER TABLE `captain_clearances` AUTO_INCREMENT = 1;
ALTER TABLE `certificate_requests` AUTO_INCREMENT = 15;
ALTER TABLE `family_disabilities` AUTO_INCREMENT = 79;
ALTER TABLE `family_members` AUTO_INCREMENT = 242;
ALTER TABLE `family_organizations` AUTO_INCREMENT = 218;
ALTER TABLE `notifications` AUTO_INCREMENT = 4;
ALTER TABLE `queue_counters` AUTO_INCREMENT = 5;
ALTER TABLE `queue_services` AUTO_INCREMENT = 8;
ALTER TABLE `queue_tickets` AUTO_INCREMENT = 2;
ALTER TABLE `queue_windows` AUTO_INCREMENT = 4;
ALTER TABLE `residents` AUTO_INCREMENT = 14;
ALTER TABLE `resident_registrations` AUTO_INCREMENT = 217;
ALTER TABLE `resident_status` AUTO_INCREMENT = 3;
ALTER TABLE `rfid_access_logs` AUTO_INCREMENT = 1;
ALTER TABLE `rfid_registrations` AUTO_INCREMENT = 2;
ALTER TABLE `rfid_users` AUTO_INCREMENT = 1;
ALTER TABLE `services` AUTO_INCREMENT = 6;
ALTER TABLE `updates` AUTO_INCREMENT = 4;
ALTER TABLE `user_reports` AUTO_INCREMENT = 1;

-- Migration completed successfully