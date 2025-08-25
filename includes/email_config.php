<?php
// Email configuration for GUMAOC System
// Update these settings with your actual SMTP credentials

// Gmail SMTP Configuration (recommended for testing)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'gumaoctest@gmail.com'); // Replace with your Gmail address
define('SMTP_PASSWORD', 'lxdi azyw utis dlmd'); // Replace with Gmail App Password (not regular password)
define('SMTP_ENCRYPTION', 'tls');
define('FROM_EMAIL', 'noreply@gumaoc.local');
define('FROM_NAME', 'GUMAOC East Barangay System');

// Alternative: Local SMTP (for development only)
// Uncomment these lines if you want to use local SMTP instead
/*
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 25);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_ENCRYPTION', '');
define('FROM_EMAIL', 'noreply@localhost');
define('FROM_NAME', 'GUMAOC System');
*/

// Email validation
if (SMTP_USERNAME === 'your-gmail@gmail.com' || SMTP_PASSWORD === 'your-app-password') {
    // Emails will fail if not configured
    error_log('WARNING: Email configuration not set up. Please update email_config.php with your SMTP credentials.');
}
?>