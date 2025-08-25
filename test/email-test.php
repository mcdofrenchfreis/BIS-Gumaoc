<?php
// Email Test Script for GUMAOC System
// Run this script to test if email functionality is working

require_once '../includes/email_service.php';

echo "<h2>📧 GUMAOC Email System Test</h2>\n";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>\n";

// Check email configuration
echo "<h3>1. Email Configuration Check:</h3>\n";
if (SMTP_USERNAME === 'your-gmail@gmail.com' || SMTP_PASSWORD === 'your-app-password') {
    echo "<p class='error'>❌ Email not configured! Please update email_config.php with your SMTP credentials.</p>\n";
    echo "<div style='background:#fff3cd;padding:15px;border-radius:5px;margin:10px 0;'>\n";
    echo "<h4>📋 How to Configure Email:</h4>\n";
    echo "<ol>\n";
    echo "<li><strong>For Gmail:</strong> Use your Gmail address and generate an App Password</li>\n";
    echo "<li><strong>App Password:</strong> Go to Google Account → Security → 2-Step Verification → App passwords</li>\n";
    echo "<li><strong>Update:</strong> Edit <code>includes/email_config.php</code></li>\n";
    echo "<li><strong>Replace:</strong> 'your-gmail@gmail.com' with your email</li>\n";
    echo "<li><strong>Replace:</strong> 'your-app-password' with your generated app password</li>\n";
    echo "</ol>\n";
    echo "</div>\n";
} else {
    echo "<p class='success'>✅ Email configuration appears to be set up</p>\n";
    echo "<p>SMTP Host: " . SMTP_HOST . "</p>\n";
    echo "<p>SMTP Port: " . SMTP_PORT . "</p>\n";
    echo "<p>Username: " . SMTP_USERNAME . "</p>\n";
    echo "<p>From Email: " . FROM_EMAIL . "</p>\n";
}

// Test email sending (only if configured)
echo "<h3>2. Email Sending Test:</h3>\n";
if (SMTP_USERNAME !== 'your-gmail@gmail.com' && SMTP_PASSWORD !== 'your-app-password') {
    try {
        $emailService = new EmailService();
        
        // Test with a sample email (replace with your test email)
        $test_email = "test@example.com"; // Change this to your test email
        $test_name = "Test User";
        $test_rfid = "RF123456789";
        $test_password = "TempPass123";
        
        echo "<p><strong>Testing RFID Activation Email...</strong></p>\n";
        $result = $emailService->sendRFIDActivationEmail($test_email, $test_name, $test_rfid, $test_password);
        
        if ($result) {
            echo "<p class='success'>✅ Test email sent successfully to $test_email</p>\n";
        } else {
            echo "<p class='error'>❌ Failed to send test email</p>\n";
        }
        
        // Test family member notification
        echo "<p><strong>Testing Family Member Notification...</strong></p>\n";
        $result2 = $emailService->sendFamilyMemberNotification($test_email, $test_name, "Head of Family", "Child");
        
        if ($result2) {
            echo "<p class='success'>✅ Family notification email sent successfully</p>\n";
        } else {
            echo "<p class='error'>❌ Failed to send family notification email</p>\n";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Email test failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    }
} else {
    echo "<p class='warning'>⚠️ Skipping email test - Please configure email settings first</p>\n";
}

// Check PHP error log for email-related errors
echo "<h3>3. Recent Email Errors (Check PHP Error Log):</h3>\n";
echo "<p>Check your PHP error log for entries containing 'Email' or 'SMTP' to see specific error messages.</p>\n";
echo "<p><strong>Common error log locations:</strong></p>\n";
echo "<ul>\n";
echo "<li>XAMPP: <code>C:\\xampp\\php\\logs\\php_error_log</code></li>\n";
echo "<li>Or check: <code>" . ini_get('error_log') . "</code></li>\n";
echo "</ul>\n";

// Instructions for fixing common issues
echo "<h3>4. Troubleshooting Steps:</h3>\n";
echo "<div style='background:#f8f9fa;padding:15px;border-radius:5px;'>\n";
echo "<h4>If emails are not sending:</h4>\n";
echo "<ol>\n";
echo "<li><strong>Configure SMTP:</strong> Update email_config.php with valid credentials</li>\n";
echo "<li><strong>Gmail Users:</strong> Enable 2FA and generate App Password</li>\n";
echo "<li><strong>Check Firewall:</strong> Ensure port 587 (TLS) or 465 (SSL) is open</li>\n";
echo "<li><strong>Test Connection:</strong> Try connecting to SMTP server manually</li>\n";
echo "<li><strong>Check Logs:</strong> Look for specific error messages in PHP error log</li>\n";
echo "</ol>\n";
echo "</div>\n";

echo "<h3>5. Quick Fix for Testing:</h3>\n";
echo "<div style='background:#e8f5e8;padding:15px;border-radius:5px;'>\n";
echo "<p><strong>For immediate testing without email setup:</strong></p>\n";
echo "<p>1. The system will still work - users will be created successfully</p>\n";
echo "<p>2. RFID codes and passwords are logged in PHP error log</p>\n";
echo "<p>3. Success messages will indicate 'contact administrator for credentials'</p>\n";
echo "<p>4. You can find user credentials in the database <code>residents</code> table</p>\n";
echo "</div>\n";

echo "<hr>\n";
echo "<p><em>Run this test after updating your email configuration to verify functionality.</em></p>\n";
?>