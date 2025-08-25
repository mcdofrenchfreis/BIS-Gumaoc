-- Add profile completion status tracking to residents table
-- This migration adds columns to track if a family member's profile is complete
-- Run this script in phpMyAdmin SQL tab

-- Add profile_complete column (1 = Complete Profile, 0 = Incomplete Profile)
ALTER TABLE `residents` 
ADD COLUMN `profile_complete` TINYINT(1) DEFAULT 1 COMMENT '1 = Complete Profile, 0 = Incomplete Profile';

-- Add created_by column to track who registered this user (for family member tracking)
ALTER TABLE `residents` 
ADD COLUMN `created_by` INT(11) DEFAULT NULL COMMENT 'ID of user who registered this family member';

-- Add relationship_to_head column to store family relationship
ALTER TABLE `residents` 
ADD COLUMN `relationship_to_head` VARCHAR(100) DEFAULT NULL COMMENT 'Relationship to head of family';

-- Add foreign key constraint for created_by (optional)
ALTER TABLE `residents` 
ADD CONSTRAINT `fk_residents_created_by` 
FOREIGN KEY (`created_by`) REFERENCES `residents`(`id`) ON DELETE SET NULL;

-- Update existing users to have complete profiles
UPDATE `residents` SET `profile_complete` = 1 WHERE `profile_complete` IS NULL;

-- Show success message
SELECT 'Profile completion columns added successfully!' as message;