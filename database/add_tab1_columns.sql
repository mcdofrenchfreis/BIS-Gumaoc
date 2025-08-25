-- Migration script to add Tab 1 columns to residents table
-- Run this script to add missing columns for storing Tab 1 registration data
-- This script safely checks for existing columns before adding them

-- Add house_number column if it doesn't exist
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'residents' 
AND column_name = 'house_number';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `residents` ADD COLUMN `house_number` varchar(20) DEFAULT NULL AFTER `address`', 
    'SELECT "Column house_number already exists" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add barangay column if it doesn't exist
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'residents' 
AND column_name = 'barangay';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `residents` ADD COLUMN `barangay` varchar(100) DEFAULT "Gumaoc East" AFTER `house_number`', 
    'SELECT "Column barangay already exists" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add sitio column if it doesn't exist
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'residents' 
AND column_name = 'sitio';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `residents` ADD COLUMN `sitio` varchar(100) DEFAULT "BLOCK" AFTER `barangay`', 
    'SELECT "Column sitio already exists" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add interviewer column if it doesn't exist
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'residents' 
AND column_name = 'interviewer';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `residents` ADD COLUMN `interviewer` varchar(255) DEFAULT NULL AFTER `sitio`', 
    'SELECT "Column interviewer already exists" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add interviewer_title column if it doesn't exist
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'residents' 
AND column_name = 'interviewer_title';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `residents` ADD COLUMN `interviewer_title` varchar(255) DEFAULT NULL AFTER `interviewer`', 
    'SELECT "Column interviewer_title already exists" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add birth_place column if it doesn't exist
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'residents' 
AND column_name = 'birth_place';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `residents` ADD COLUMN `birth_place` varchar(255) DEFAULT NULL AFTER `birthdate`', 
    'SELECT "Column birth_place already exists" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update any existing records to have default barangay and sitio values
UPDATE `residents` 
SET `barangay` = 'Gumaoc East', 
    `sitio` = 'BLOCK' 
WHERE `barangay` IS NULL OR `sitio` IS NULL;

-- Create indexes if they don't exist
-- Note: MySQL will show warnings if indexes already exist, but won't fail

-- Create index for house_number for better performance
SET @index_exists = 0;
SELECT COUNT(*) INTO @index_exists 
FROM information_schema.statistics 
WHERE table_schema = DATABASE() 
AND table_name = 'residents' 
AND index_name = 'idx_house_number';

SET @sql = IF(@index_exists = 0, 
    'CREATE INDEX `idx_house_number` ON `residents` (`house_number`)', 
    'SELECT "Index idx_house_number already exists" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create index for interviewer for reporting purposes
SET @index_exists = 0;
SELECT COUNT(*) INTO @index_exists 
FROM information_schema.statistics 
WHERE table_schema = DATABASE() 
AND table_name = 'residents' 
AND index_name = 'idx_interviewer';

SET @sql = IF(@index_exists = 0, 
    'CREATE INDEX `idx_interviewer` ON `residents` (`interviewer`)', 
    'SELECT "Index idx_interviewer already exists" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create index for birth_place for search purposes
SET @index_exists = 0;
SELECT COUNT(*) INTO @index_exists 
FROM information_schema.statistics 
WHERE table_schema = DATABASE() 
AND table_name = 'residents' 
AND index_name = 'idx_birth_place';

SET @sql = IF(@index_exists = 0, 
    'CREATE INDEX `idx_birth_place` ON `residents` (`birth_place`)', 
    'SELECT "Index idx_birth_place already exists" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Show the updated table structure
SELECT 'Migration completed successfully!' as status;
DESCRIBE `residents`;