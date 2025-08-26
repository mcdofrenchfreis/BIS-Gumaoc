<?php
require_once '../includes/db_connect.php';

echo "<h2>User System Database Setup</h2>";

try {
    // The user system now uses the main 'residents' table for authentication
    // Check if residents table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'residents'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Residents table found - authentication system ready<br>";
    } else {
        echo "❌ Residents table not found - please run main database setup first<br>";
    }

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
        FOREIGN KEY (user_id) REFERENCES residents(id) ON DELETE CASCADE
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
        FOREIGN KEY (user_id) REFERENCES residents(id) ON DELETE CASCADE
    )");
    echo "✅ User service requests table created successfully<br>";

    echo "<br><div style='background:#e8f5e8;padding:15px;border-radius:5px;'>";
    echo "<h3>ℹ️ Important Information</h3>";
    echo "<p><strong>Authentication System Updated:</strong></p>";
    echo "<ul>";
    echo "<li>The user system now integrates with the main residents table</li>";
    echo "<li>Users can login with Email + Password OR RFID</li>";
    echo "<li>To create user accounts, use the census registration system</li>";
    echo "<li>All existing residents in the database can login to the user portal</li>";
    echo "</ul>";
    echo "<p><strong>User Portal Features:</strong></p>";
    echo "<ul>";
    echo "<li>Dashboard with user information</li>";
    echo "<li>Report submission and tracking</li>";
    echo "<li>Access to e-services</li>";
    echo "<li>RFID-based quick access</li>";
    echo "</ul>";
    echo "</div>";

    echo "<br><strong>Setup completed successfully!</strong><br>";
    echo "<a href='login.php' style='display:inline-block;margin:10px;padding:10px 20px;background:#28a745;color:white;text-decoration:none;border-radius:5px;'>Go to User Login</a>";
    echo "<a href='../pages/resident-registration.php' style='display:inline-block;margin:10px;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:5px;'>Complete Census Registration</a>";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?> 