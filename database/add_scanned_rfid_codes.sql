-- Add table for storing scanned RFID codes
-- This table stores RFID codes that are scanned/tapped by admin before assignment to residents

CREATE TABLE IF NOT EXISTS scanned_rfid_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rfid_code VARCHAR(50) UNIQUE NOT NULL,
    status ENUM('available', 'assigned', 'disabled') DEFAULT 'available',
    scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_at TIMESTAMP NULL,
    assigned_to_resident_id INT NULL,
    assigned_to_email VARCHAR(255) NULL,
    scanned_by_admin_id INT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Add indexes for performance
    INDEX idx_rfid_code (rfid_code),
    INDEX idx_status (status),
    INDEX idx_scanned_at (scanned_at),
    
    -- Foreign key constraint (if admin_users table exists)
    FOREIGN KEY (scanned_by_admin_id) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- Add some sample data for testing (optional - can be removed in production)
INSERT IGNORE INTO scanned_rfid_codes (rfid_code, status, notes) VALUES
('TEST001', 'available', 'Test RFID code for development'),
('TEST002', 'available', 'Test RFID code for development'),
('TEST003', 'available', 'Test RFID code for development');