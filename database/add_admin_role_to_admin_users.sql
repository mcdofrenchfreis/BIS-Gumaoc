-- Idempotent migration to add/normalize role column for role-based authentication

-- 1) Add the column only if it doesn't exist yet
ALTER TABLE `admin_users`
  ADD COLUMN IF NOT EXISTS `role` ENUM('secretary','treasurer','captain') NOT NULL DEFAULT 'secretary' AFTER `username`;

-- 2) Normalize the column definition to ensure all enum values and default are set correctly
ALTER TABLE `admin_users`
  MODIFY COLUMN `role` ENUM('secretary','treasurer','captain') NOT NULL DEFAULT 'secretary';

-- 3) Ensure an index exists on role (use CREATE INDEX IF NOT EXISTS for MySQL 8+/MariaDB)
CREATE INDEX IF NOT EXISTS `idx_admin_users_role` ON `admin_users` (`role`);
