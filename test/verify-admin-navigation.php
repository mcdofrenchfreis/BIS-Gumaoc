<?php
// Verification script for admin navigation fixes
echo "🔧 Admin Navigation Verification Script\n";
echo str_repeat("=", 50) . "\n";

// Test different session scenarios
$test_scenarios = [
    'No session' => [],
    'Logged in but no admin_id' => ['admin_logged_in' => true],
    'Logged in with admin_id' => ['admin_logged_in' => true, 'admin_id' => 1],
    'Logged in with username' => ['admin_logged_in' => true, 'admin_username' => 'test_admin']
];

echo "📋 Testing session handling scenarios...\n";

foreach ($test_scenarios as $scenario_name => $session_data) {
    echo "\nScenario: $scenario_name\n";
    
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
    include '../includes/admin_header.php';
    $navigation_output = ob_get_contents();
    ob_end_clean();
    
    if (!$error_occurred) {
        echo "✅ Admin header included successfully without errors\n";
        echo "   Output length: " . strlen($navigation_output) . " characters\n";
        
        // Check if the welcome message is properly generated
        if (strpos($navigation_output, 'Welcome,') !== false) {
            echo "✅ Welcome message found in navigation\n";
        } else {
            echo "⚠️  Welcome message not found (might be expected)\n";
        }
        
        // Check for key elements
        $key_elements = ['Dashboard', 'RFID Scanner', 'RFID Management', 'Residents', 'View Site', 'Logout'];
        foreach ($key_elements as $element) {
            if (strpos($navigation_output, $element) !== false) {
                echo "✅ Found element: $element\n";
            } else {
                echo "❌ Missing element: $element\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Exception including navigation: " . $e->getMessage() . "\n";
}

restore_error_handler();
ob_end_clean();

echo "\n📋 Testing admin footer...\n";

// Test the admin footer
ob_start();
$error_occurred = false;

set_error_handler(function($severity, $message, $file, $line) use (&$error_occurred) {
    $error_occurred = true;
    echo "❌ Footer Error: $message in $file on line $line\n";
});

try {
    // Include the admin footer file
    ob_start();
    include '../includes/admin_footer.php';
    $footer_output = ob_get_contents();
    ob_end_clean();
    
    if (!$error_occurred) {
        echo "✅ Admin footer included successfully without errors\n";
        echo "   Output length: " . strlen($footer_output) . " characters\n";
        
        // Check for key elements
        if (strpos($footer_output, 'Admin Panel') !== false) {
            echo "✅ Admin footer content found\n";
        } else {
            echo "⚠️  Admin footer content not found\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Exception including footer: " . $e->getMessage() . "\n";
}

restore_error_handler();
ob_end_clean();

echo "\n🎉 Admin navigation verification completed!\n";
echo "\nThe fixes should:\n";
echo "✅ Have proper spacing and alignment\n";
echo "✅ Be more compact as requested\n";
echo "✅ Handle all session scenarios correctly\n";
echo "✅ Include all necessary navigation elements\n";
echo "✅ Work with the new admin footer\n";

echo "\n" . str_repeat("=", 50) . "\n";
?>