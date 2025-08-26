-- Update the scanned_rfid_codes table to include 'archived' status
ALTER TABLE scanned_rfid_codes MODIFY COLUMN status ENUM('available', 'assigned', 'disabled', 'archived') DEFAULT 'available';