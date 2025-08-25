-- Safe migration script that checks for existing columns first
-- Run this in phpMyAdmin SQL tab

-- Check and add profile_complete column
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'residents' 
    AND COLUMN_NAME = 'profile_complete'
);

SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE `residents` ADD COLUMN `profile_complete` TINYINT(1) DEFAULT 1 COMMENT ''1 = Complete Profile, 0 = Incomplete Profile'';',
    'SELECT ''Column profile_complete already exists'' as message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add created_by column
SET @column_exists2 = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'residents' 
    AND COLUMN_NAME = 'created_by'
);

SET @sql2 = IF(@column_exists2 = 0, 
    'ALTER TABLE `residents` ADD COLUMN `created_by` INT(11) DEFAULT NULL COMMENT ''ID of user who registered this family member'';',
    'SELECT ''Column created_by already exists'' as message;'
);

PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- Check and add relationship_to_head column
SET @column_exists3 = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'residents' 
    AND COLUMN_NAME = 'relationship_to_head'
);

SET @sql3 = IF(@column_exists3 = 0, 
    'ALTER TABLE `residents` ADD COLUMN `relationship_to_head` VARCHAR(100) DEFAULT NULL COMMENT ''Relationship to head of family'';',
    'SELECT ''Column relationship_to_head already exists'' as message;'
);

PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

-- Show final status
SELECT 'Migration completed! Check the results above.' as status;