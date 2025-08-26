<?php
// Test script to verify RFID scanner SQL fix
echo "🔧 Testing RFID Scanner SQL Fix\n";
echo str_repeat("=", 50) . "\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=gumaoc_db;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection successful\n";
    
    // Test pagination variables (same as in rfid-scanner.php)
    $page = 1;
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    
    echo "📊 Testing pagination query with LIMIT and OFFSET...\n";
    echo "   Page: $page, Per page: $per_page, Offset: $offset\n";
    
    // Test the fixed query
    $codes_stmt = $pdo->prepare("
        SELECT s.*, 
               r.first_name, r.last_name, r.email as resident_email
        FROM scanned_rfid_codes s 
        LEFT JOIN residents r ON s.assigned_to_resident_id = r.id 
        ORDER BY s.scanned_at DESC 
        LIMIT $per_page OFFSET $offset
    ");
    
    $codes_stmt->execute();
    $rfid_codes = $codes_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ SQL query executed successfully!\n";
    echo "📋 Retrieved " . count($rfid_codes) . " RFID codes\n";
    
    // Test statistics query
    $stats_stmt = $pdo->query("
        SELECT 
            status,
            COUNT(*) as count 
        FROM scanned_rfid_codes 
        GROUP BY status
    ");
    
    $stats = [];
    while ($row = $stats_stmt->fetch()) {
        $stats[$row['status']] = $row['count'];
    }
    
    echo "✅ Statistics query executed successfully!\n";
    echo "📊 RFID Statistics:\n";
    echo "   Available: " . ($stats['available'] ?? 0) . "\n";
    echo "   Assigned: " . ($stats['assigned'] ?? 0) . "\n";
    echo "   Disabled: " . ($stats['disabled'] ?? 0) . "\n";
    
    // Test total count query
    $total_stmt = $pdo->query("SELECT COUNT(*) FROM scanned_rfid_codes");
    $total_codes = $total_stmt->fetchColumn();
    $total_pages = ceil($total_codes / $per_page);
    
    echo "✅ Total count query executed successfully!\n";
    echo "📊 Total codes: $total_codes\n";
    echo "📄 Total pages: $total_pages\n";
    
    echo "\n🎉 All RFID scanner queries are working correctly!\n";
    echo "\n📋 The error has been fixed:\n";
    echo "   - LIMIT and OFFSET are now used directly in the query\n";
    echo "   - No more PDO parameter binding issues\n";
    echo "   - All pagination functionality should work properly\n";
    
    echo "\n🌐 You can now access the RFID scanner at:\n";
    echo "   http://localhost:8000/admin/rfid-scanner.php\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
?>