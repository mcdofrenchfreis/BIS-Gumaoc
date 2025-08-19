<?php
session_start();
include '../includes/db_connect.php';
include '../includes/AdminLogger.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$logger = new AdminLogger($pdo);

echo "<h2>🧪 Testing Logging System</h2>";

// Test different types of logs
$tests = [
    ['page_view', 'admin_panel', 'Viewed dashboard page'],
    ['status_update', 'certificate_request', 'Updated certificate request status', 123, ['old_status' => 'pending', 'new_status' => 'processing']],
    ['print_action', 'certificate_request', 'Printed residency certificate', 456, ['certificate_type' => 'RESIDENCY', 'applicant_name' => 'Test User']],
    ['admin_login', 'admin_auth', 'Admin login successful', null, ['username' => 'admin', 'success' => true]],
    ['form_view', 'resident_registration', 'Viewed registration form', 789, ['view_mode' => 'readonly']]
];

foreach ($tests as $i => $test) {
    $result = $logger->log($test[0], $test[1], $test[2], $test[3] ?? null, $test[4] ?? null);
    echo "<p>Test " . ($i + 1) . ": " . ($result ? "✅ SUCCESS" : "❌ FAILED") . " - {$test[2]}</p>";
}

// Check if logs were created
$count = $pdo->query("SELECT COUNT(*) FROM admin_logs")->fetchColumn();
echo "<h3>📊 Total logs after test: $count</h3>";

echo "<p><a href='view-logs.php'>📊 View Logs</a> | <a href='dashboard.php'>🏠 Dashboard</a></p>";
?>