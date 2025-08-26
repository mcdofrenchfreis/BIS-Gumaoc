<?php
/**
 * Setup Certificate Requests System
 * Run this script once to create the certificate_requests table
 */

require_once '../includes/db_connect.php';

try {
    // Create certificate_requests table
    $sql = "
    CREATE TABLE IF NOT EXISTS certificate_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        certificate_type VARCHAR(100) NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        address TEXT NOT NULL,
        mobile_number VARCHAR(20),
        civil_status VARCHAR(50),
        gender VARCHAR(10),
        birth_date DATE,
        birth_place VARCHAR(255),
        citizenship VARCHAR(100) DEFAULT 'Filipino',
        years_of_residence INT,
        purpose TEXT NOT NULL,
        status ENUM('pending', 'processing', 'ready', 'released') DEFAULT 'pending',
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        notes TEXT,
        
        INDEX idx_user_id (user_id),
        INDEX idx_status (status),
        INDEX idx_certificate_type (certificate_type),
        INDEX idx_submitted_at (submitted_at)
    )";
    
    $pdo->exec($sql);
    
    echo "✅ Certificate Requests table created successfully!\n";
    echo "✅ Database setup completed!\n";
    echo "\nYou can now use:\n";
    echo "- /user/certificate-request.php - Request certificates\n";
    echo "- /user/my-requests.php - View request status\n";
    
} catch (PDOException $e) {
    echo "❌ Error creating table: " . $e->getMessage() . "\n";
    exit(1);
}
?>