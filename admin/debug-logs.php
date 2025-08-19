<?php
session_start();
include '../includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

echo "<h2>🔍 Database Debug Information</h2>";

try {
    // Check if admin_logs table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'admin_logs'")->fetchAll();
    if (empty($tables)) {
        echo "<p>❌ <strong>admin_logs table does NOT exist!</strong></p>";
        
        // Create the table
        echo "<p>🔧 Creating admin_logs table...</p>";
        $sql = "CREATE TABLE admin_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id VARCHAR(100) DEFAULT 'system',
            action_type VARCHAR(50) NOT NULL,
            target_type VARCHAR(50) NOT NULL,
            target_id INT NULL,
            description TEXT NOT NULL,
            details JSON NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_admin_id (admin_id),
            INDEX idx_action_type (action_type),
            INDEX idx_target_type (target_type),
            INDEX idx_created_at (created_at)
        )";
        
        $pdo->exec($sql);
        echo "<p>✅ admin_logs table created successfully!</p>";
    } else {
        echo "<p>✅ admin_logs table exists</p>";
    }
    
    // Check table structure
    echo "<h3>📋 Table Structure:</h3>";
    $columns = $pdo->query("DESCRIBE admin_logs")->fetchAll();
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check record count
    $count = $pdo->query("SELECT COUNT(*) FROM admin_logs")->fetchColumn();
    echo "<p>📊 Total records in admin_logs: <strong>$count</strong></p>";
    
    // Show recent records
    if ($count > 0) {
        echo "<h3>📝 Recent Log Entries (Last 5):</h3>";
        $recent = $pdo->query("SELECT * FROM admin_logs ORDER BY created_at DESC LIMIT 5")->fetchAll();
        echo "<table border='1' style='border-collapse: collapse; width: 100%; font-size: 12px;'>";
        echo "<tr><th>ID</th><th>Admin</th><th>Action</th><th>Target</th><th>Description</th><th>Created</th></tr>";
        foreach ($recent as $log) {
            echo "<tr>";
            echo "<td>{$log['id']}</td>";
            echo "<td>{$log['admin_id']}</td>";
            echo "<td>{$log['action_type']}</td>";
            echo "<td>{$log['target_type']}</td>";
            echo "<td>" . substr($log['description'], 0, 50) . "...</td>";
            echo "<td>{$log['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test logging functionality
    echo "<h3>🧪 Testing Logger Class:</h3>";
    include '../includes/AdminLogger.php';
    $logger = new AdminLogger($pdo);
    
    $test_result = $logger->log(
        'debug_test', 
        'system_debug', 
        'Debug test log entry from debug script', 
        null, 
        ['test_time' => date('Y-m-d H:i:s'), 'test_user' => 'debug']
    );
    
    echo "<p>Test log result: " . ($test_result ? "✅ SUCCESS" : "❌ FAILED") . "</p>";
    
    // Check again after test
    $new_count = $pdo->query("SELECT COUNT(*) FROM admin_logs")->fetchColumn();
    echo "<p>Record count after test: <strong>$new_count</strong></p>";
    
    // Check for errors
    $errors = $pdo->errorInfo();
    if ($errors[0] !== '00000') {
        echo "<p>❌ Database Error: " . $errors[2] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='view-logs.php'>📊 View Logs</a> | <a href='dashboard.php'>🏠 Dashboard</a></p>";
?>