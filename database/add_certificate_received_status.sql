-- "received" = resident picked up the certificate (after released)
ALTER TABLE `certificate_requests`
  MODIFY COLUMN `status` ENUM('pending','processing','ready','released','received') DEFAULT 'pending';
