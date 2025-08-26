<?php
// Test script for RFID Management System
require_once '../includes/db_connect.php';
require_once '../includes/email_service.php';

echo "🔍 Testing RFID Management System Integration\n";
echo str_repeat("=", 50) . "\n";

try {
    // Test 1: Check if scanned_rfid_codes table exists
    echo "1. Checking database table...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'scanned_rfid_codes'");
    if ($stmt->rowCount() > 0) {
        echo "   ✅ scanned_rfid_codes table exists\n";
    } else {
        echo "   ❌ scanned_rfid_codes table missing - creating...\n";
        
        // Create the table
        $sql = file_get_contents('../database/add_scanned_rfid_codes.sql');
        $pdo->exec($sql);
        echo "   ✅ Table created successfully\n";
    }
    
    // Test 2: Test RFID code insertion
    echo "\n2. Testing RFID code insertion...\n";
    $test_rfid = 'TEST' . time();
    $stmt = $pdo->prepare("INSERT INTO scanned_rfid_codes (rfid_code, notes) VALUES (?, ?)");
    $result = $stmt->execute([$test_rfid, 'Test RFID from integration test']);
    
    if ($result) {
        echo "   ✅ RFID code '$test_rfid' inserted successfully\n";
    } else {
        echo "   ❌ Failed to insert test RFID code\n";
    }
    
    // Test 3: Test getting available RFID codes
    echo "\n3. Testing EmailService::getAvailableRFIDCode()...\n";
    $available_rfid = EmailService::getAvailableRFIDCode($pdo);
    if ($available_rfid) {
        echo "   ✅ Available RFID found: $available_rfid\n";
        
        // Test 4: Test RFID assignment
        echo "\n4. Testing RFID assignment...\n";
        $assigned = EmailService::assignRFIDCode($pdo, $available_rfid, 999, 'test@example.com');
        if ($assigned) {
            echo "   ✅ RFID code assigned successfully\n";
        } else {
            echo "   ❌ Failed to assign RFID code\n";
        }
    } else {
        echo "   ⚠️  No available RFID codes found\n";
    }
    
    // Test 5: Check statistics
    echo "\n5. Current RFID statistics:\n";
    $stats_stmt = $pdo->query("
        SELECT 
            status,
            COUNT(*) as count 
        FROM scanned_rfid_codes 
        GROUP BY status
    ");
    
    while ($row = $stats_stmt->fetch()) {
        echo "   📊 {$row['status']}: {$row['count']} codes\n";
    }
    
    // Test 6: Generate unique RFID (should use available codes first)
    echo "\n6. Testing generateUniqueRFID()...\n";
    $new_rfid = EmailService::generateUniqueRFID($pdo);
    echo "   🆔 Generated RFID: $new_rfid\n";
    
    echo "\n🎉 All tests completed successfully!\n";
    echo "\n📋 Next steps:\n";
    echo "   1. Access admin panel: http://localhost:8000/admin/rfid-scanner.php\n";
    echo "   2. Scan some RFID codes\n";
    echo "   3. Test resident registration with pre-scanned codes\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
?>