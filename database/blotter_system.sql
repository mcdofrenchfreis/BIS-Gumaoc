-- Barangay Blotter System Database Tables
-- Run this script to create the blotter and resident status tracking tables

-- Create barangay_blotter table for recording complaints, incidents, and disputes
CREATE TABLE IF NOT EXISTS `barangay_blotter` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `blotter_number` varchar(50) NOT NULL UNIQUE,
    `incident_type` enum('complaint', 'incident', 'dispute', 'violation', 'other') NOT NULL,
    `complainant_id` int(11) DEFAULT NULL,
    `complainant_name` varchar(255) NOT NULL,
    `complainant_address` varchar(500) NOT NULL,
    `complainant_contact` varchar(20) DEFAULT NULL,
    `respondent_id` int(11) DEFAULT NULL,
    `respondent_name` varchar(255) NOT NULL,
    `respondent_address` varchar(500) NOT NULL,
    `respondent_contact` varchar(20) DEFAULT NULL,
    `incident_date` datetime NOT NULL,
    `reported_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `location` varchar(500) NOT NULL,
    `description` text NOT NULL,
    `classification` enum('minor', 'major', 'critical') DEFAULT 'minor',
    `status` enum('filed', 'under_investigation', 'mediation', 'resolved', 'dismissed', 'referred_to_court') DEFAULT 'filed',
    `investigating_officer` varchar(255) DEFAULT NULL,
    `settlement_details` text DEFAULT NULL,
    `action_taken` text DEFAULT NULL,
    `case_disposition` text DEFAULT NULL,
    `created_by` varchar(100) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `complainant_id` (`complainant_id`),
    KEY `respondent_id` (`respondent_id`),
    KEY `incident_date` (`incident_date`),
    KEY `status` (`status`),
    KEY `classification` (`classification`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create resident_status table for tracking resident records and clearances
CREATE TABLE IF NOT EXISTS `resident_status` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `resident_id` int(11) NOT NULL,
    `resident_name` varchar(255) NOT NULL,
    `record_status` enum('good', 'minor_issues', 'major_issues', 'critical') DEFAULT 'good',
    `total_complaints` int(11) DEFAULT 0,
    `total_incidents` int(11) DEFAULT 0,
    `pending_cases` int(11) DEFAULT 0,
    `resolved_cases` int(11) DEFAULT 0,
    `requires_captain_clearance` boolean DEFAULT FALSE,
    `captain_clearance_granted` boolean DEFAULT FALSE,
    `captain_clearance_date` datetime DEFAULT NULL,
    `captain_clearance_reason` text DEFAULT NULL,
    `captain_clearance_expires` datetime DEFAULT NULL,
    `last_incident_date` datetime DEFAULT NULL,
    `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `resident_id` (`resident_id`),
    KEY `record_status` (`record_status`),
    KEY `requires_captain_clearance` (`requires_captain_clearance`),
    CONSTRAINT `resident_status_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create blotter_attachments table for supporting documents
CREATE TABLE IF NOT EXISTS `blotter_attachments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `blotter_id` int(11) NOT NULL,
    `file_name` varchar(255) NOT NULL,
    `file_path` varchar(500) NOT NULL,
    `file_type` varchar(50) NOT NULL,
    `file_size` int(11) NOT NULL,
    `uploaded_by` varchar(100) NOT NULL,
    `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `blotter_id` (`blotter_id`),
    CONSTRAINT `blotter_attachments_ibfk_1` FOREIGN KEY (`blotter_id`) REFERENCES `barangay_blotter` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create captain_clearances table for tracking clearances granted by barangay captain
CREATE TABLE IF NOT EXISTS `captain_clearances` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `resident_id` int(11) NOT NULL,
    `clearance_type` enum('form_access', 'certificate_request', 'business_permit', 'general') NOT NULL,
    `reason` text NOT NULL,
    `granted_by` varchar(100) NOT NULL,
    `granted_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at` datetime DEFAULT NULL,
    `status` enum('active', 'expired', 'revoked') DEFAULT 'active',
    `notes` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `resident_id` (`resident_id`),
    KEY `clearance_type` (`clearance_type`),
    KEY `status` (`status`),
    CONSTRAINT `captain_clearances_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create access_logs table for tracking form access attempts
CREATE TABLE IF NOT EXISTS `access_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `resident_id` int(11) DEFAULT NULL,
    `form_type` varchar(100) NOT NULL,
    `access_granted` boolean NOT NULL,
    `reason` varchar(255) NOT NULL,
    `attempted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `resident_id` (`resident_id`),
    KEY `form_type` (`form_type`),
    KEY `attempted_at` (`attempted_at`),
    CONSTRAINT `access_logs_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default admin user for blotter management if not exists
INSERT IGNORE INTO `admin_users` (`username`, `password`, `full_name`, `email`) VALUES 
('blotter_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Blotter Administrator', 'blotter@gumaoc.local');

-- Create function to update resident status based on blotter records
DELIMITER $$
CREATE FUNCTION IF NOT EXISTS update_resident_status(resident_id_param INT) 
RETURNS VARCHAR(20)
READS SQL DATA
DETERMINISTIC
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

-- Create trigger to auto-update resident status when blotter record changes
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS update_resident_status_after_blotter_insert
AFTER INSERT ON barangay_blotter
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

CREATE TRIGGER IF NOT EXISTS update_resident_status_after_blotter_update
AFTER UPDATE ON barangay_blotter
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