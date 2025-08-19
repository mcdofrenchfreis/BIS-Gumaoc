<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['rfid_authenticated']) || $_SESSION['rfid_authenticated'] !== true) {
    // Set session message for toast notification
    $_SESSION['auth_error'] = 'You need to log in to access this form. Please authenticate with your RFID card or use the login page.';
    
    // Get current page to redirect back after login
    $current_page = $_SERVER['REQUEST_URI'];
    header('Location: ../login.php?redirect=' . urlencode($current_page));
    exit();
}
?>