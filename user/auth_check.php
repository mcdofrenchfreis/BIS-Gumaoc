<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user data from residents table (main authentication system)
$stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ? AND status = 'active'");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    // If user no longer exists in database or is not active
    session_destroy();
    header('Location: login.php');
    exit;
}

// Set user type for backward compatibility
$_SESSION['user_type'] = 'resident';
?> 