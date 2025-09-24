-- Migration script to add deceased status column for family members
-- Run this script to update existing database with deceased status field for family members

-- Add is_deceased column to family_members table
ALTER TABLE `family_members` 
ADD COLUMN IF NOT EXISTS `is_deceased` tinyint(1) DEFAULT 0 AFTER `occupation`;

-- Add index for better query performance on is_deceased column
ALTER TABLE `family_members` 
ADD INDEX IF NOT EXISTS `idx_is_deceased` (`is_deceased`);

-- Display current table structure to verify changes
DESCRIBE `family_members`;

-- Display sample data to verify migration
SELECT id, full_name, is_deceased FROM `family_members` LIMIT 10;