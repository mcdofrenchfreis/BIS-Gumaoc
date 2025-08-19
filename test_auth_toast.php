<?php
session_start();

// Simulate being logged out
unset($_SESSION['rfid_authenticated']);

// Set auth error message that would be displayed on any page
$_SESSION['auth_error'] = 'You need to log in to access this form. Please authenticate with your RFID card or use the login page.';

echo "
<!DOCTYPE html>
<html>
<head>
    <title>Test Auth Toast</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .test-link { display: inline-block; margin: 10px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; }
        .test-link:hover { background: #45a049; }
    </style>
</head>
<body>
    <h1>🧪 Auth Toast Test Page</h1>
    
    <div class='test-section'>
        <h2>Test Scenario</h2>
        <p>You are currently logged out. Click the links below to test the auth toast system:</p>
        
        <h3>Protected Forms (should show toast and redirect to login):</h3>
        <a href='pages/certificate-request.php' class='test-link'>📄 Certificate Request Form</a>
        <a href='pages/business-application.php' class='test-link'>🏢 Business Application Form</a>
        
        <h3>Public Pages (should work normally):</h3>
        <a href='pages/forms.php' class='test-link'>📋 Forms Page</a>
        <a href='pages/services.php' class='test-link'>💻 Services Page</a>
        <a href='pages/resident-registration.php' class='test-link'>👥 Census Registration</a>
        
        <h3>Login Page (should show the auth error toast):</h3>
        <a href='login.php' class='test-link'>🔐 Login Page</a>
    </div>
    
    <div class='test-section'>
        <h2>Expected Behavior</h2>
        <ul>
            <li><strong>Certificate Request & Business Application:</strong> Should redirect to login page and show toast message</li>
            <li><strong>Login Page:</strong> Should display the auth error toast at the top</li>
            <li><strong>Other Pages:</strong> Should work normally without toast (unless they include header.php)</li>
        </ul>
    </div>
    
    <div class='test-section'>
        <h2>Session Info</h2>
        <p><strong>Logged In:</strong> " . (isset($_SESSION['rfid_authenticated']) && $_SESSION['rfid_authenticated'] ? 'Yes' : 'No') . "</p>
        <p><strong>Auth Error Set:</strong> " . (isset($_SESSION['auth_error']) ? 'Yes - ' . $_SESSION['auth_error'] : 'No') . "</p>
    </div>
</body>
</html>";
?>
