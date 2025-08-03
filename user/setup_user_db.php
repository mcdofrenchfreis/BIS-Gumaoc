<?php
require_once '../includes/db_connect.php';

echo "<h2>Setting up User Database Tables</h2>";

try {
    // Create users table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✅ Users table created successfully<br>";

    // Create user_reports table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        incident_type VARCHAR(100) NOT NULL,
        location VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        contact_number VARCHAR(20) NOT NULL,
        status ENUM('pending', 'processing', 'completed', 'rejected') DEFAULT 'pending',
        admin_notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "✅ User reports table created successfully<br>";

    // Create user_service_requests table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_service_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        service_type VARCHAR(100) NOT NULL,
        request_details TEXT NOT NULL,
        status ENUM('pending', 'processing', 'completed', 'rejected') DEFAULT 'pending',
        admin_notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "✅ User service requests table created successfully<br>";

    // Insert sample user for testing (password: user123)
    $sample_password = password_hash('user123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO users (full_name, email, password, phone, address) 
                VALUES ('John Doe', 'user@example.com', '$sample_password', '09123456789', '123 Main Street, Gumaoc East')");
    echo "✅ Sample user created (email: user@example.com, password: user123)<br>";

    echo "<br><strong>Setup completed successfully!</strong><br>";
    echo "<a href='login.php'>Go to User Login</a>";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?> 