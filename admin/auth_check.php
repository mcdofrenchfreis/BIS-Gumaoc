<?php
session_start();

// Check if user is not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get admin user data
require_once '../includes/db_connect.php';
$stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin_user = $stmt->fetch();

if (!$admin_user) {
    // If user no longer exists in database
    session_destroy();
    header('Location: login.php');
    exit();
}
?> 