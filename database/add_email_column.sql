-- Migration script to add email column to resident_registrations table
-- Run this script if you already have the database set up and need to add the email field

-- Add email column to resident_registrations table
ALTER TABLE `resident_registrations` 
ADD COLUMN `email` varchar(255) DEFAULT NULL 
AFTER `contact_number`;

-- Update existing records (optional - you can leave existing records with NULL email)
-- UPDATE `resident_registrations` SET `email` = NULL WHERE `email` IS NULL;

-- Verify the column was added successfully
-- DESCRIBE `resident_registrations`;