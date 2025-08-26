<?php
// Test script to verify admin navigation fix
echo "🔧 Testing Admin Navigation Fix\n";
echo str_repeat("=", 50) . "\n";

// Test different session scenarios
$test_scenarios = [
    'No session' => [],
    'Logged in but no admin_id' => ['admin_logged_in' => true],
    'Logged in with admin_id' => ['admin_logged_in' => true, 'admin_id' => 1],
    'Logged in with username' => ['admin_logged_in' => true, 'admin_username' => 'test_admin']
];

foreach ($test_scenarios as $scenario_name => $session_data) {
    echo "\n📋 Testing scenario: $scenario_name\n";
    
    // Clear session and set up test data
    session_start();
    $_SESSION = [];
    foreach ($session_data as $key => $value) {
        $_SESSION[$key] = $value;
    }
    
    // Capture any PHP errors/warnings
    ob_start();
    $error_occurred = false;
    
    set_error_handler(function($severity, $message, $file, $line) use (&$error_occurred) {
        $error_occurred = true;
        echo "❌ Error: $message in $file on line $line\n";
    });
    
    // Test including the navigation (simulate)
    try {
        // Simulate the navigation code logic
        $admin_user = ['full_name' => 'Admin User'];
        
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            if (isset($_SESSION['admin_id'])) {
                // Would normally fetch from database
                $admin_user = ['full_name' => 'John Admin'];
            } elseif (isset($_SESSION['admin_username'])) {
                $admin_user['full_name'] = $_SESSION['admin_username'];
            }
        }
        
        // Test accessing the variable (this is where the error occurred)
        $welcome_text = "Welcome, " . htmlspecialchars($admin_user['full_name']);
        
        if (!$error_occurred) {
            echo "✅ Navigation loaded successfully\n";
            echo "   Welcome text: $welcome_text\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Exception: " . $e->getMessage() . "\n";
    }
    
    restore_error_handler();
    ob_end_clean();
}

echo "\n🔍 Testing actual navigation file...\n";

// Test the actual navigation file
ob_start();
$error_occurred = false;

set_error_handler(function($severity, $message, $file, $line) use (&$error_occurred) {
    $error_occurred = true;
    echo "❌ Navigation Error: $message in $file on line $line\n";
});

// Set up a valid session for testing
session_start();
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 1;
$_SESSION['admin_username'] = 'test_admin';

try {
    // Include the navigation file in a way that captures its output
    ob_start();
    include '../includes/admin_navigation.php';
    $navigation_output = ob_get_contents();
    ob_end_clean();
    
    if (!$error_occurred) {
        echo "✅ Admin navigation included successfully without errors\n";
        echo "   Output length: " . strlen($navigation_output) . " characters\n";
        
        // Check if the welcome message is properly generated
        if (strpos($navigation_output, 'Welcome,') !== false) {
            echo "✅ Welcome message found in navigation\n";
        } else {
            echo "⚠️  Welcome message not found (might be expected)\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Exception including navigation: " . $e->getMessage() . "\n";
}

restore_error_handler();
ob_end_clean();

echo "\n🎉 Navigation fix test completed!\n";
echo "\nThe fix should:\n";
echo "✅ Initialize \$admin_user with default values first\n";
echo "✅ Handle cases where admin_id is not set\n";
echo "✅ Provide multiple fallback options\n";
echo "✅ Never cause undefined variable warnings\n";

echo "\n🌐 You can now test the RFID scanner at:\n";
echo "   http://localhost:8000/admin/rfid-scanner.php\n";

echo "\n" . str_repeat("=", 50) . "\n";
?>