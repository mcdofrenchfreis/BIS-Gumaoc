<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ? AND status = 'active'");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Function to log settings changes
function logSettingsChange($pdo, $user_id, $action, $details) {
    try {
        $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, details, timestamp) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $action, $details]);
    } catch (Exception $e) {
        // Continue even if logging fails
        error_log("Settings log error: " . $e->getMessage());
    }
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_email') {
        // Update Email
        $new_email = trim($_POST['new_email'] ?? '');
        $confirm_email = trim($_POST['confirm_email'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        
        // Validation
        if (empty($new_email) || empty($confirm_email) || empty($current_password)) {
            $_SESSION['settings_error'] = 'All fields are required.';
            header('Location: settings.php');
            exit;
        }
        
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['settings_error'] = 'Please enter a valid email address.';
            header('Location: settings.php');
            exit;
        }
        
        if ($new_email !== $confirm_email) {
            $_SESSION['settings_error'] = 'Email addresses do not match.';
            header('Location: settings.php');
            exit;
        }
        
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $_SESSION['settings_error'] = 'Current password is incorrect.';
            header('Location: settings.php');
            exit;
        }
        
        // Check if email already exists
        $check_stmt = $pdo->prepare("SELECT id FROM residents WHERE email = ? AND id != ?");
        $check_stmt->execute([$new_email, $user['id']]);
        if ($check_stmt->fetch()) {
            $_SESSION['settings_error'] = 'This email address is already in use by another account.';
            header('Location: settings.php');
            exit;
        }
        
        try {
            // Update email
            $update_stmt = $pdo->prepare("UPDATE residents SET email = ?, updated_at = NOW() WHERE id = ?");
            $update_stmt->execute([$new_email, $user['id']]);
            
            // Log the change
            logSettingsChange($pdo, $user['id'], 'Email Updated', "Email changed from {$user['email']} to {$new_email}");
            
            $_SESSION['settings_success'] = 'Email address updated successfully.';
            header('Location: settings.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry error
                $_SESSION['settings_error'] = 'This email address is already in use.';
            } else {
                $_SESSION['settings_error'] = 'An error occurred while updating your email. Please try again.';
                error_log("Email update error: " . $e->getMessage());
            }
            header('Location: settings.php');
            exit;
        }
        
    } elseif ($action === 'update_password') {
        // Update Password
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $_SESSION['settings_error'] = 'All fields are required.';
            header('Location: settings.php');
            exit;
        }
        
        if (strlen($new_password) < 6) {
            $_SESSION['settings_error'] = 'New password must be at least 6 characters long.';
            header('Location: settings.php');
            exit;
        }
        
        if ($new_password !== $confirm_password) {
            $_SESSION['settings_error'] = 'New passwords do not match.';
            header('Location: settings.php');
            exit;
        }
        
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $_SESSION['settings_error'] = 'Current password is incorrect.';
            header('Location: settings.php');
            exit;
        }
        
        // Check if new password is same as current
        if (password_verify($new_password, $user['password'])) {
            $_SESSION['settings_error'] = 'New password cannot be the same as your current password.';
            header('Location: settings.php');
            exit;
        }
        
        try {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $update_stmt = $pdo->prepare("UPDATE residents SET password = ?, updated_at = NOW() WHERE id = ?");
            $update_stmt->execute([$hashed_password, $user['id']]);
            
            // Log the change
            logSettingsChange($pdo, $user['id'], 'Password Updated', "Password changed for user: {$user['email']}");
            
            $_SESSION['settings_success'] = 'Password updated successfully.';
            header('Location: settings.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['settings_error'] = 'An error occurred while updating your password. Please try again.';
            error_log("Password update error: " . $e->getMessage());
            header('Location: settings.php');
            exit;
        }
        
    } else {
        $_SESSION['settings_error'] = 'Invalid action specified.';
        header('Location: settings.php');
        exit;
    }
} else {
    // Not a POST request
    header('Location: settings.php');
    exit;
}
?>