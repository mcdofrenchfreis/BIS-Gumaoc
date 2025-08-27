<?php
// GUMAOC Email Workflow Test Script
// This script tests the email functionality for the resident registration workflow

require_once '../includes/db_connect.php';
require_once '../includes/email_service.php';

// Test email address - REPLACE WITH YOUR ACTUAL EMAIL
$test_email = 'your.email@example.com'; // CHANGE THIS TO YOUR EMAIL
$test_name = 'Test User';

echo "<h1>GUMAOC Email Workflow Test</h1>";
echo "<div style='background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>📋 Registration Workflow:</h3>";
echo "<ol>";
echo "<li><strong>Registration:</strong> User submits form → account created as 'pending' → <strong>confirmation email sent</strong></li>";
echo "<li><strong>Admin Approval:</strong> Admin approves registration → account activated → <strong>credentials email sent</strong></li>";
echo "<li><strong>Login:</strong> User receives RFID & password → can now login</li>";
echo "</ol>";
echo "</div>";

try {
    $emailService = new EmailService();
    
    // Test 1: Registration Confirmation Email (sent during registration)
    echo "<h2>Test 1: Registration Confirmation Email</h2>";
    echo "<p><em>This email is sent when user submits registration (no credentials yet)</em></p>";
    $result1 = $emailService->sendRegistrationConfirmationEmail($test_email, $test_name);
    if ($result1) {
        echo "<p style='color: green;'>✅ Registration confirmation email sent successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Registration confirmation email failed to send.</p>";
    }
    
    echo "<hr>";
    
    // Test 2: Admin Approval Email (sent when admin approves)
    echo "<h2>Test 2: Admin Approval Email with Credentials</h2>";
    echo "<p><em>This email is sent when admin approves registration (contains RFID & password)</em></p>";
    $test_rfid = 'TEST123456';
    $test_password = 'TestPass123';
    $result2 = $emailService->sendApprovalEmail($test_email, $test_name, $test_rfid, $test_password);
    if ($result2) {
        echo "<p style='color: green;'>✅ Approval email with credentials sent successfully!</p>";
        echo "<p><strong>Test RFID:</strong> $test_rfid</p>";
        echo "<p><strong>Test Password:</strong> $test_password</p>";
    } else {
        echo "<p style='color: red;'>❌ Approval email failed to send.</p>";
    }
    
    echo "<hr>";
    
    // Test 3: Rejection Email
    echo "<h2>Test 3: Rejection Email</h2>";
    echo "<p><em>This email is sent when admin rejects registration</em></p>";
    $result3 = $emailService->sendRejectionEmail($test_email, $test_name);
    if ($result3) {
        echo "<p style='color: green;'>✅ Rejection email sent successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Rejection email failed to send.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error during email test: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>Email Configuration Check</h2>";
echo "<p><strong>SMTP Host:</strong> " . (defined('SMTP_HOST') ? SMTP_HOST : 'Not defined') . "</p>";
echo "<p><strong>SMTP Port:</strong> " . (defined('SMTP_PORT') ? SMTP_PORT : 'Not defined') . "</p>";
echo "<p><strong>SMTP Username:</strong> " . (defined('SMTP_USERNAME') ? SMTP_USERNAME : 'Not defined') . "</p>";
echo "<p><strong>From Email:</strong> " . (defined('FROM_EMAIL') ? FROM_EMAIL : 'Not defined') . "</p>";

echo "<h2>Instructions</h2>";
echo "<ol>";
echo "<li>Replace the \$test_email variable above with your actual email address</li>";
echo "<li>Refresh this page to run the tests</li>";
echo "<li>Check your email inbox (and spam folder) for test emails</li>";
echo "<li>Check the PHP error log for detailed debugging information</li>";
echo "</ol>";

// Show recent error log entries
echo "<h2>Recent Error Log Entries (Email related)</h2>";
echo "<p><em>Check your PHP error log file for more detailed information. Common locations:</em></p>";
echo "<ul>";
echo "<li>XAMPP: <code>xampp/apache/logs/error.log</code></li>";
echo "<li>WAMP: <code>wamp/logs/apache_error.log</code></li>";
echo "<li>Linux: <code>/var/log/apache2/error.log</code></li>";
echo "</ul>";

?>