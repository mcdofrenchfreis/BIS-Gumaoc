<?php
// Test script for admin navigation fix
echo "🔧 Testing Admin Navigation Fix\n";
echo str_repeat("=", 50) . "\n";

// Start session and simulate admin login
session_start();

try {
    // Simulate database connection
    $pdo = new PDO('mysql:host=localhost;dbname=gumaoc_db;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection successful\n";
    
    // Check if admin_users table exists and has data
    $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ admin_users table exists\n";
        
        // Check if there are any admin users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM admin_users");
        $count = $stmt->fetchColumn();
        echo "📊 Admin users in database: $count\n";
        
        if ($count > 0) {
            // Get first admin user for testing
            $stmt = $pdo->query("SELECT * FROM admin_users LIMIT 1");
            $test_admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($test_admin) {
                echo "✅ Found test admin user: " . htmlspecialchars($test_admin['username']) . "\n";
                
                // Simulate admin session
                $_SESSION['admin_id'] = $test_admin['id'];
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $test_admin['username'];
                
                echo "📝 Simulated admin session:\n";
                echo "   - Admin ID: " . $test_admin['id'] . "\n";
                echo "   - Username: " . htmlspecialchars($test_admin['username']) . "\n";
                echo "   - Full Name: " . htmlspecialchars($test_admin['full_name'] ?? 'Not set') . "\n";
            }
        } else {
            echo "⚠️  No admin users found. Creating default admin...\n";
            
            // Create default admin user for testing
            $default_password = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, full_name, email) VALUES (?, ?, ?, ?)");
            $stmt->execute(['admin', $default_password, 'Administrator', 'admin@gumaoc.gov.ph']);
            
            $admin_id = $pdo->lastInsertId();
            $_SESSION['admin_id'] = $admin_id;
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = 'admin';
            
            echo "✅ Default admin created (username: admin, password: admin123)\n";
        }
    } else {
        echo "⚠️  admin_users table not found. Creating...\n";
        
        // Create admin_users table
        $pdo->exec("
            CREATE TABLE admin_users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                full_name VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                role ENUM('admin', 'super_admin') DEFAULT 'admin',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // Create default admin
        $default_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, full_name, email) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', $default_password, 'Administrator', 'admin@gumaoc.gov.ph']);
        
        $admin_id = $pdo->lastInsertId();
        $_SESSION['admin_id'] = $admin_id;
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = 'admin';
        
        echo "✅ admin_users table and default admin created\n";
    }
    
    echo "\n🧪 Testing admin navigation include...\n";
    
    // Capture output to test for errors
    ob_start();
    $error_occurred = false;
    
    // Set error handler to catch warnings
    set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$error_occurred) {
        $error_occurred = true;
        echo "❌ Error caught: $errstr in $errfile on line $errline\n";
    });
    
    // Test including the admin navigation
    include '../includes/admin_navigation.php';
    
    // Restore error handler
    restore_error_handler();
    
    $navigation_output = ob_get_clean();
    
    if (!$error_occurred) {
        echo "✅ Admin navigation included successfully without errors!\n";
        echo "📏 Navigation HTML length: " . strlen($navigation_output) . " characters\n";
        
        // Check if admin user name appears in output
        if (strpos($navigation_output, 'Welcome,') !== false) {
            echo "✅ Admin welcome message found in navigation\n";
        } else {
            echo "⚠️  Admin welcome message not found (might be expected if layout is different)\n";
        }
        
        if (strpos($navigation_output, 'Undefined variable') === false && 
            strpos($navigation_output, 'Trying to access array offset') === false) {
            echo "✅ No undefined variable errors in output\n";
        } else {
            echo "❌ Still contains variable errors\n";
        }
    } else {
        echo "❌ Errors occurred while including navigation\n";
    }
    
    echo "\n🎉 Admin Navigation Test Results:\n";
    echo "   - Database connection: ✅ Working\n";
    echo "   - Admin session: ✅ Simulated\n";
    echo "   - Navigation include: " . (!$error_occurred ? "✅ No errors" : "❌ Has errors") . "\n";
    echo "   - Variable warnings: " . (!$error_occurred ? "✅ Fixed" : "❌ Still present") . "\n";
    
    echo "\n📋 You can now test the admin pages:\n";
    echo "   - Dashboard: http://localhost:8000/admin/dashboard.php\n";
    echo "   - RFID Scanner: http://localhost:8000/admin/rfid-scanner.php\n";
    echo "   - Login: http://localhost:8000/admin/login.php (admin/admin123)\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
?>