<?php
// Test script for updated admin dashboard
echo "🔍 Testing Updated Admin Dashboard\n";
echo str_repeat("=", 50) . "\n";

// Test database connection and RFID table
try {
    $pdo = new PDO('mysql:host=localhost;dbname=gumaoc_db;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection successful\n";
    
    // Check if scanned_rfid_codes table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'scanned_rfid_codes'");
    if ($stmt->rowCount() > 0) {
        echo "✅ scanned_rfid_codes table exists\n";
        
        // Get RFID statistics
        $available = $pdo->query("SELECT COUNT(*) FROM scanned_rfid_codes WHERE status = 'available'")->fetchColumn();
        $assigned = $pdo->query("SELECT COUNT(*) FROM scanned_rfid_codes WHERE status = 'assigned'")->fetchColumn();
        
        echo "📊 RFID Statistics:\n";
        echo "   Available codes: $available\n";
        echo "   Assigned codes: $assigned\n";
        
    } else {
        echo "⚠️  scanned_rfid_codes table not found - creating...\n";
        
        // Create the table
        $sql = file_get_contents('../database/add_scanned_rfid_codes.sql');
        $pdo->exec($sql);
        echo "✅ Table created successfully\n";
        
        // Add some test data
        $pdo->exec("INSERT IGNORE INTO scanned_rfid_codes (rfid_code, status, notes) VALUES 
                    ('TEST001', 'available', 'Test code 1'),
                    ('TEST002', 'available', 'Test code 2'),
                    ('TEST003', 'assigned', 'Test assigned code')");
        echo "✅ Test data added\n";
    }
    
    // Test other required tables
    $tables_to_check = ['resident_registrations', 'certificate_requests', 'business_applications', 'services', 'updates'];
    
    foreach ($tables_to_check as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' exists\n";
        } else {
            echo "⚠️  Table '$table' not found (this is expected for a new installation)\n";
        }
    }
    
    echo "\n🎉 Dashboard should now display:\n";
    echo "   - RFID statistics in the stats cards\n";
    echo "   - RFID Scanner and RFID Management in top navigation\n";
    echo "   - RFID management tools in the expanded management section\n";
    echo "   - No recent activity section (removed)\n";
    
    echo "\n📋 Next steps:\n";
    echo "   1. Access admin dashboard: http://localhost:8000/admin/dashboard.php\n";
    echo "   2. Check RFID Scanner: http://localhost:8000/admin/rfid-scanner.php\n";
    echo "   3. Check RFID Management: http://localhost:8000/admin/manage-rfid.php\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
?>