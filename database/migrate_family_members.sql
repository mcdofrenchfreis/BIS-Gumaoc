-- Migration script to add missing columns and tables for family members, disabilities, and organizations
-- Run this script to update existing database with new family member fields

-- Add relationship column if it doesn't exist
ALTER TABLE `family_members` 
ADD COLUMN IF NOT EXISTS `relationship` varchar(100) DEFAULT NULL AFTER `full_name`;

-- Add gender column if it doesn't exist  
ALTER TABLE `family_members` 
ADD COLUMN IF NOT EXISTS `gender` enum('Lalaki','Babae') DEFAULT NULL AFTER `age`;

-- Add email column if it doesn't exist
ALTER TABLE `family_members` 
ADD COLUMN IF NOT EXISTS `email` varchar(255) DEFAULT NULL AFTER `civil_status`;

-- Create family_disabilities table if it doesn't exist
CREATE TABLE IF NOT EXISTS `family_disabilities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `registration_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `disability_type` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_registration_id` (`registration_id`),
  FOREIGN KEY (`registration_id`) REFERENCES `resident_registrations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create family_organizations table if it doesn't exist
CREATE TABLE IF NOT EXISTS `family_organizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `registration_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `organization_type` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_registration_id` (`registration_id`),
  FOREIGN KEY (`registration_id`) REFERENCES `resident_registrations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Update any existing records to set default values if needed
-- (Optional: You can customize these default values based on your requirements)

-- Display current table structures to verify changes
DESCRIBE `family_members`;
DESCRIBE `family_disabilities`;
DESCRIBE `family_organizations`;

-- Display sample data to verify migration
SELECT * FROM `family_members` LIMIT 5;
SELECT * FROM `family_disabilities` LIMIT 5;
SELECT * FROM `family_organizations` LIMIT 5;