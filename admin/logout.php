<?php
session_start();
include '../includes/db_connect.php';
include '../includes/AdminLogger.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $logger = new AdminLogger($pdo);
    $username = $_SESSION['admin_username'] ?? 'unknown';
    
    // Log the logout
    $logger->logAdminLogout($username);
    
    // Clear session
    session_destroy();
}

header('Location: login.php');
exit;
?>