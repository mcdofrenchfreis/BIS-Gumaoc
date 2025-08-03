-- Update resident_registrations table to add login credentials
-- Run this script to update existing database

-- Add login_id and password columns to resident_registrations table
ALTER TABLE `resident_registrations` 
ADD COLUMN `login_id` varchar(20) DEFAULT NULL AFTER `interviewer_title`,
ADD COLUMN `password` varchar(255) DEFAULT NULL AFTER `login_id`;

-- Add unique constraint on login_id
ALTER TABLE `resident_registrations` 
ADD UNIQUE KEY `login_id` (`login_id`);

-- Create users table if it doesn't exist
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Update existing records to have login credentials (optional - for testing)
-- This will generate login IDs for existing records
UPDATE `resident_registrations` 
SET `login_id` = CONCAT('R', LPAD(id, 4, '0')),
    `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE `login_id` IS NULL;

-- Note: The password above is the hash for 'password' - users should change this
-- You may want to set a different default password or leave it NULL for security 