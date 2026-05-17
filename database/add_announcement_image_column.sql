-- Optional photo for barangay announcements (updates table)
ALTER TABLE `updates`
  ADD COLUMN IF NOT EXISTS `image` VARCHAR(255) NULL DEFAULT NULL AFTER `description`;
