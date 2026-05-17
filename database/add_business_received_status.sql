-- "received" = resident picked up business clearance; "ready" = ready for pickup
UPDATE `business_applications` SET `status` = 'reviewing' WHERE `status` = 'received';

ALTER TABLE `business_applications`
  MODIFY COLUMN `status` ENUM('pending','reviewing','approved','ready','received','rejected') DEFAULT 'pending';
