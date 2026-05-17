-- User photos for certificate requests (1x1 ID photos)
CREATE TABLE IF NOT EXISTS `user_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `certificate_request_id` int(11) DEFAULT NULL,
  `photo_filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `photo_path` varchar(500) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_photos_user` (`user_id`),
  KEY `idx_user_photos_request` (`certificate_request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Optional link from certificate_requests to user_photos (skip if column already exists)
SET @col_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'certificate_requests'
    AND COLUMN_NAME = 'photo_id'
);
SET @sql = IF(
  @col_exists = 0,
  'ALTER TABLE `certificate_requests` ADD COLUMN `photo_id` int(11) DEFAULT NULL AFTER `photo_2x2`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
