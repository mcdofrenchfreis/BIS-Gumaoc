<?php
session_start();

// Test both success and error scenarios
if (isset($_GET['test'])) {
    switch ($_GET['test']) {
        case 'success':
            $_SESSION['success'] = "Registration successful! Login credentials have been sent to test@example.com. You can now login with your RFID or email and password once you receive your credentials.";
            break;
        case 'error':
            $_SESSION['error'] = "Test error message to verify error modal functionality.";
            break;
        case 'clear':
            unset($_SESSION['success']);
            unset($_SESSION['error']);
            break;
    }
    
    // Redirect to registration page
    header('Location: ../pages/resident-registration.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modal Test - GUMAOC System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .test-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .test-btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .test-btn:hover {
            background: #0056b3;
        }
        
        .test-btn.success {
            background: #28a745;
        }
        
        .test-btn.success:hover {
            background: #1e7e34;
        }
        
        .test-btn.error {
            background: #dc3545;
        }
        
        .test-btn.error:hover {
            background: #c82333;
        }
        
        .test-btn.clear {
            background: #6c757d;
        }
        
        .test-btn.clear:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🧪 Modal Test Interface</h1>
        <p>Use these buttons to test the modal functionality in the resident registration system:</p>
        
        <h3>Test Scenarios:</h3>
        
        <a href="?test=success" class="test-btn success">
            ✅ Test Success Modal
        </a>
        <p><small>This will simulate a successful registration and show the success modal with 2-minute timer.</small></p>
        
        <a href="?test=error" class="test-btn error">
            ❌ Test Error Modal
        </a>
        <p><small>This will simulate a registration error and show the error modal.</small></p>
        
        <a href="?test=clear" class="test-btn clear">
            🧹 Clear Sessions
        </a>
        <p><small>This will clear all session messages and return to normal form.</small></p>
        
        <hr>
        
        <h3>Direct Access:</h3>
        <a href="../pages/resident-registration.php" class="test-btn">
            📝 Go to Registration Form
        </a>
        <p><small>Go directly to the registration form to see normal behavior.</small></p>
        
        <hr>
        
        <h3>What to Test:</h3>
        <ul>
            <li><strong>Success Modal:</strong> Should show large green modal with bold text and timer counting down from 120 seconds</li>
            <li><strong>Error Modal:</strong> Should show large red modal with bold text and X close button</li>
            <li><strong>Modal Size:</strong> Should be larger and more prominent (800px max width)</li>
            <li><strong>Font Weight:</strong> All text should be bold and easily readable</li>
            <li><strong>Modal Position:</strong> Should appear near the top of the page, just below the header</li>
            <li><strong>Privacy Notice:</strong> Should NOT show when modals are present</li>
            <li><strong>Close Functionality:</strong> Test larger X button, click outside, and Esc key</li>
            <li><strong>Auto-close:</strong> Success modal should auto-close after 2 minutes with bold countdown</li>
            <li><strong>Responsive:</strong> Test on different screen sizes to ensure proper sizing and readability</li>
        </ul>
    </div>
</body>
</html>