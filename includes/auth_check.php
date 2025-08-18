<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['rfid_authenticated']) || $_SESSION['rfid_authenticated'] !== true) {
    // Get current page to redirect back after login
    $current_page = $_SERVER['REQUEST_URI'];
    header('Location: ../login.php?redirect=' . urlencode($current_page));
    exit();
}
?>