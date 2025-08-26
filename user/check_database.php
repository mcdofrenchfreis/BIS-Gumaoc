<?php
/**
 * Database Check Script
 * Run this to verify the certificate_requests table structure
 */

require_once '../includes/db_connect.php';

try {
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'certificate_requests'");
    $table_exists = $stmt->rowCount() > 0;
    
    echo "<h2>Database Check Results</h2>\n";
    
    if (!$table_exists) {
        echo "❌ <strong>Certificate_requests table does NOT exist!</strong><br>\n";
        echo "Please run setup_certificate_system.php to create the table.<br><br>\n";
        
        // Show available tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<strong>Available tables:</strong><br>\n";
        foreach ($tables as $table) {
            echo "- " . $table . "<br>\n";
        }
    } else {
        echo "✅ Certificate_requests table exists!<br><br>\n";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE certificate_requests");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<strong>Table Structure:</strong><br>\n";
        echo "<table border='1' cellpadding='5'>\n";
        echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>\n";
        
        foreach ($columns as $column) {
            echo "<tr>\n";
            echo "<td>" . $column['Field'] . "</td>\n";
            echo "<td>" . $column['Type'] . "</td>\n";
            echo "<td>" . $column['Null'] . "</td>\n";
            echo "<td>" . $column['Key'] . "</td>\n";
            echo "<td>" . $column['Default'] . "</td>\n";
            echo "</tr>\n";
        }
        echo "</table><br>\n";
        
        // Check if processed_at column exists
        $has_processed_at = false;
        foreach ($columns as $column) {
            if ($column['Field'] === 'processed_at') {
                $has_processed_at = true;
                break;
            }
        }
        
        if (!$has_processed_at) {
            echo "⚠️ <strong>Warning:</strong> 'processed_at' column is missing. This is expected for the simplified version.<br>\n";
        } else {
            echo "✅ 'processed_at' column exists.<br>\n";
        }
        
        // Show sample data if any
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM certificate_requests");
        $count = $stmt->fetch()['count'];
        echo "<br><strong>Records in table:</strong> " . $count . "<br>\n";
        
        if ($count > 0) {
            $stmt = $pdo->query("SELECT * FROM certificate_requests ORDER BY submitted_at DESC LIMIT 3");
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<br><strong>Sample Records:</strong><br>\n";
            foreach ($requests as $request) {
                echo "- ID: " . $request['id'] . ", Type: " . $request['certificate_type'] . ", Status: " . $request['status'] . "<br>\n";
            }
        }
    }
    
} catch (PDOException $e) {
    echo "❌ <strong>Database Error:</strong> " . $e->getMessage() . "<br>\n";
    echo "Check your database connection settings.<br>\n";
}
?>