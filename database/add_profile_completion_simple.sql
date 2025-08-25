-- Simple migration script - Run each statement one by one in phpMyAdmin
-- Copy and paste each ALTER TABLE statement separately

-- Step 1: Add profile_complete column
ALTER TABLE `residents` 
ADD COLUMN `profile_complete` TINYINT(1) DEFAULT 1 
COMMENT '1 = Complete Profile, 0 = Incomplete Profile';

-- Step 2: Add created_by column  
ALTER TABLE `residents` 
ADD COLUMN `created_by` INT(11) DEFAULT NULL 
COMMENT 'ID of user who registered this family member';

-- Step 3: Add relationship_to_head column
ALTER TABLE `residents` 
ADD COLUMN `relationship_to_head` VARCHAR(100) DEFAULT NULL 
COMMENT 'Relationship to head of family';

-- Step 4: Update existing users to have complete profiles
UPDATE `residents` SET `profile_complete` = 1;