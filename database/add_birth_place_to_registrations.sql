-- Migration script to add birth_place column to resident_registrations table
-- Run this script to fix the place of birth display issue

-- Check if birth_place column exists
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'resident_registrations' 
AND column_name = 'birth_place';

-- Add birth_place column if it doesn't exist
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `resident_registrations` ADD COLUMN `birth_place` varchar(255) DEFAULT NULL AFTER `birth_date`', 
    'SELECT "Column birth_place already exists in resident_registrations table" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Show the updated table structure
SELECT 'Migration completed successfully! birth_place column added to resident_registrations table.' as status;
DESCRIBE `resident_registrations`;