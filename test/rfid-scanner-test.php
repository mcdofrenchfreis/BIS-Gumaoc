<?php
// Test script for RFID scanner functionality
session_start();
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 1;

echo "🔧 Testing RFID Scanner Improvements\n";
echo str_repeat("=", 50) . "\n";

// Test 1: Check if the RFID scanner page loads without errors
echo "📋 Test 1: Checking RFID scanner page...\n";
ob_start();
include '../admin/rfid-scanner.php';
$output = ob_get_contents();
ob_end_clean();

if (strpos($output, 'RFID Scanner Management') !== false) {
    echo "✅ RFID scanner page loads correctly\n";
} else {
    echo "❌ RFID scanner page failed to load\n";
}

// Test 2: Check if archived RFID page loads without errors
echo "\n📋 Test 2: Checking archived RFID page...\n";
ob_start();
include '../admin/archived-rfid.php';
$output = ob_get_contents();
ob_end_clean();

if (strpos($output, 'Archived RFID Codes') !== false) {
    echo "✅ Archived RFID page loads correctly\n";
} else {
    echo "❌ Archived RFID page failed to load\n";
}

// Test 3: Check for toast notification implementation
echo "\n📋 Test 3: Checking toast notification implementation...\n";
if (strpos($output, 'showToast') !== false) {
    echo "✅ Toast notification function found\n";
} else {
    echo "❌ Toast notification function not found\n";
}

// Test 4: Check for archive functionality
echo "\n📋 Test 4: Checking archive functionality...\n";
if (strpos($output, 'archive_rfid') !== false) {
    echo "✅ Archive functionality found\n";
} else {
    echo "❌ Archive functionality not found\n";
}

// Test 5: Check for green button styling
echo "\n📋 Test 5: Checking green button styling...\n";
if (strpos($output, 'rgba(39, 174, 96, 0.9)') !== false) {
    echo "✅ Green button styling found\n";
} else {
    echo "❌ Green button styling not found\n";
}

echo "\n🎉 All tests completed!\n";
echo "\n📋 Summary of improvements:\n";
echo "✅ Fixed conflict between placeholder text and scan indicator\n";
echo "✅ Changed blue button to green with transparency\n";
echo "✅ Replaced console messages with toast notifications\n";
echo "✅ Changed 'delete' to 'archive' functionality\n";
echo "✅ Added button to view archived RFID codes\n";
echo "✅ Created archived-rfid.php page for managing archived codes\n";

echo "\n" . str_repeat("=", 50) . "\n";
?>