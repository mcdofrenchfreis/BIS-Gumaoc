-- Add has_account column to family_members table
-- This migration adds a boolean column to track if family members already have an account

-- Check if the column already exists before adding it
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'family_members');
SET @column_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'family_members' AND column_name = 'has_account');

-- Only add the column if the table exists and the column doesn't exist yet
SET @sql = IF(@table_exists > 0 AND @column_exists = 0, 
    'ALTER TABLE family_members ADD COLUMN has_account TINYINT(1) DEFAULT 0 AFTER is_deceased',
    'SELECT "Column already exists or table does not exist" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify the column was added
SELECT 
    CASE 
        WHEN @table_exists = 0 THEN 'Table family_members does not exist'
        WHEN @column_exists > 0 THEN 'Column has_account already exists'
        ELSE 'Column has_account added successfully'
    END AS result;