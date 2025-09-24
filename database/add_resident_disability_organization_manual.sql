-- Migration script to add disability and organization columns for main resident in Tab 1
-- Run this script to update existing database with new resident fields

-- Add disability column if it doesn't exist
ALTER TABLE `resident_registrations` 
ADD COLUMN `resident_disability` varchar(255) DEFAULT NULL AFTER `interviewer_title`;

-- Add organization column if it doesn't exist  
ALTER TABLE `resident_registrations` 
ADD COLUMN `resident_organization` varchar(255) DEFAULT NULL AFTER `resident_disability`;

-- Display current table structure to verify changes
DESCRIBE `resident_registrations`;

-- Display sample data to verify migration
SELECT id, first_name, last_name, resident_disability, resident_organization FROM `resident_registrations` LIMIT 5;