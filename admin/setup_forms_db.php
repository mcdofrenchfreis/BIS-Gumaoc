<?php
// Database setup script for form submissions
include '../includes/db_connect.php';

try {
    // Create resident_registrations table
    $pdo->exec("CREATE TABLE IF NOT EXISTS resident_registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        middle_name VARCHAR(100),
        last_name VARCHAR(100) NOT NULL,
        birth_date DATE NOT NULL,
        age INT NOT NULL,
        civil_status VARCHAR(50) NOT NULL,
        gender VARCHAR(20) NOT NULL,
        contact_number VARCHAR(20),
        house_number VARCHAR(20),
        pangkabuhayan VARCHAR(100),
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'
    )");

    // Create certificate_requests table
    $pdo->exec("CREATE TABLE IF NOT EXISTS certificate_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        certificate_type VARCHAR(100) NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        middle_name VARCHAR(100),
        last_name VARCHAR(100) NOT NULL,
        birth_date DATE NOT NULL,
        age INT NOT NULL,
        birth_place VARCHAR(200),
        civil_status VARCHAR(50) NOT NULL,
        gender VARCHAR(20) NOT NULL,
        contact_number VARCHAR(20),
        house_number VARCHAR(20),
        address TEXT,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'
    )");

    // Create business_applications table
    $pdo->exec("CREATE TABLE IF NOT EXISTS business_applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reference_number VARCHAR(50) NOT NULL,
        application_date DATE NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        middle_name VARCHAR(100),
        last_name VARCHAR(100) NOT NULL,
        business_name VARCHAR(200) NOT NULL,
        business_address_1 VARCHAR(200) NOT NULL,
        business_address_2 VARCHAR(200),
        house_address TEXT NOT NULL,
        or_number VARCHAR(50) NOT NULL,
        ctc_number VARCHAR(50) NOT NULL,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'
    )");

    // Create admin_users table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        full_name VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Insert default admin user (password: admin123)
    $pdo->exec("INSERT IGNORE INTO admin_users (username, password, email, full_name) 
                VALUES ('admin', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@gumaoc.gov.ph', 'System Administrator')");

    echo "✅ Database tables created successfully!<br>";
    echo "✅ Default admin user created (username: admin, password: admin123)<br>";
    echo "✅ Form submission system is ready!<br><br>";
    echo "<a href='login.php'>Go to Admin Login</a>";

} catch (Exception $e) {
    echo "❌ Error setting up database: " . $e->getMessage();
}
?>
