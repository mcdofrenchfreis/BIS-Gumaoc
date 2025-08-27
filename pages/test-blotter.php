<?php
// Simple test script for blotter detection system
session_start();

// Test the blotter check directly
$_POST['action'] = 'check_blotter';
$_POST['first_name'] = 'Mar Yvan';
$_POST['middle_name'] = 'Sagun';
$_POST['last_name'] = 'Dela Cruz';

echo "<h2>Testing Blotter Detection System</h2>";
echo "<p><strong>Testing with:</strong></p>";
echo "<ul>";
echo "<li>First Name: " . htmlspecialchars($_POST['first_name']) . "</li>";
echo "<li>Middle Name: " . htmlspecialchars($_POST['middle_name']) . "</li>";
echo "<li>Last Name: " . htmlspecialchars($_POST['last_name']) . "</li>";
echo "</ul>";

echo "<h3>Response from check-blotter.php:</h3>";
echo "<pre>";

// Capture the output
ob_start();
include 'check-blotter.php';
$output = ob_get_clean();

echo htmlspecialchars($output);
echo "</pre>";

echo "<h3>JSON Decoded Response:</h3>";
$response = json_decode($output, true);
if ($response) {
    echo "<pre>";
    print_r($response);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>Failed to decode JSON response</p>";
}
?>