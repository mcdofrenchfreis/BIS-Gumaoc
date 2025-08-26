<?php
require_once '../includes/db_connect.php';

// Create user_reports table with correct foreign key reference to residents table
$sql = "CREATE TABLE IF NOT EXISTS `user_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `incident_type` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('low', 'medium', 'high') DEFAULT 'medium',
  `contact_number` varchar(20) NOT NULL,
  `status` enum('pending', 'processing', 'completed', 'rejected') DEFAULT 'pending',
  `admin_notes` text,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

try {
    $pdo->exec($sql);
    echo "✅ user_reports table created successfully!<br>";
    
    // Test the table structure
    $result = $pdo->query("DESCRIBE user_reports");
    echo "<h3>Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>✅ The user_reports table has been created successfully!</strong></p>";
    echo "<p><a href='reports.php'>← Go back to Reports page</a></p>";
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "ℹ️ Table already exists. Checking structure...<br>";
        
        // Check if the foreign key references the correct table
        $result = $pdo->query("
            SELECT 
                CONSTRAINT_NAME,
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM 
                information_schema.KEY_COLUMN_USAGE 
            WHERE 
                TABLE_SCHEMA = 'gumaoc_db' 
                AND TABLE_NAME = 'user_reports' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        echo "<h3>Foreign Key Constraints:</h3>";
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "Constraint: {$row['CONSTRAINT_NAME']} - References: {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}<br>";
        }
        
        // If the foreign key references resident_registrations, we need to fix it
        $fkResult = $pdo->query("
            SELECT REFERENCED_TABLE_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = 'gumaoc_db' 
                AND TABLE_NAME = 'user_reports' 
                AND COLUMN_NAME = 'user_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        $fkRow = $fkResult->fetch(PDO::FETCH_ASSOC);
        
        if ($fkRow && $fkRow['REFERENCED_TABLE_NAME'] === 'resident_registrations') {
            echo "<p><strong>⚠️ Foreign key references old table 'resident_registrations'. Fixing...</strong></p>";
            
            try {
                // Drop the old foreign key
                $pdo->exec("ALTER TABLE user_reports DROP FOREIGN KEY user_reports_ibfk_1");
                echo "✅ Dropped old foreign key constraint<br>";
                
                // Add new foreign key referencing residents table
                $pdo->exec("ALTER TABLE user_reports ADD CONSTRAINT user_reports_ibfk_1 FOREIGN KEY (user_id) REFERENCES residents(id) ON DELETE CASCADE");
                echo "✅ Added new foreign key constraint to residents table<br>";
                
            } catch (PDOException $e2) {
                echo "❌ Error updating foreign key: " . $e2->getMessage() . "<br>";
            }
        }
        
        echo "<p><strong>✅ Table structure verified and updated!</strong></p>";
        echo "<p><a href='reports.php'>← Go back to Reports page</a></p>";
        
    } else {
        echo "❌ Error creating table: " . $e->getMessage();
    }
}
?>