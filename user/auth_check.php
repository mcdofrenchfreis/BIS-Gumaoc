<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check user type and get appropriate data
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'resident') {
    // Resident user - check resident_registrations table
    $stmt = $pdo->prepare("SELECT * FROM resident_registrations WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // If user no longer exists in database
        session_destroy();
        header('Location: login.php');
        exit;
    }
} else {
    // Legacy user - check users table
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // If user no longer exists in database
        session_destroy();
        header('Location: login.php');
        exit;
    }
}
?> 