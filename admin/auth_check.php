<?php
session_start();

// Enhanced session debugging for admin authentication
if (isset($_GET['debug']) && $_GET['debug'] === 'session') {
    header('Content-Type: application/json');
    echo json_encode([
        'session_data' => $_SESSION,
        'session_id' => session_id(),
        'admin_logged_in' => isset($_SESSION['admin_id']),
        'timestamp' => date('c')
    ]);
    exit();
}

// Check if user is not logged in
if (!isset($_SESSION['admin_id'])) {
    // For AJAX requests, return JSON response instead of redirect
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required', 'redirect' => 'login.php']);
        exit();
    }
    header('Location: login.php');
    exit();
}

// Get admin user data
require_once '../includes/db_connect.php';
try {
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin_user = $stmt->fetch();
    
    if (!$admin_user) {
        // If user no longer exists in database
        session_destroy();
        
        // For AJAX requests, return JSON response
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'User no longer exists', 'redirect' => 'login.php']);
            exit();
        }
        
        header('Location: login.php');
        exit();
    }
    
    // Set admin_logged_in flag for API compatibility
    $_SESSION['admin_logged_in'] = true;
    // Persist role in session
    if (empty($_SESSION['admin_role'])) {
        $_SESSION['admin_role'] = isset($admin_user['role']) && $admin_user['role'] !== '' ? $admin_user['role'] : 'secretary';
    }
    if (empty($_SESSION['admin_name'])) {
        if (!empty($admin_user['full_name'])) {
            $_SESSION['admin_name'] = $admin_user['full_name'];
        } elseif (!empty($admin_user['username'])) {
            $_SESSION['admin_name'] = $admin_user['username'];
        }
    }

    // Optional role enforcement: set $REQUIRE_ROLES in the including script
    if (isset($REQUIRE_ROLES) && $REQUIRE_ROLES) {
        $required = is_array($REQUIRE_ROLES) ? $REQUIRE_ROLES : [$REQUIRE_ROLES];
        $role = $_SESSION['admin_role'] ?? 'secretary';
        if (!in_array($role, $required, true)) {
            // Forbidden
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Insufficient permissions', 'required' => $required, 'role' => $role]);
                exit();
            }
            http_response_code(403);
            die('Forbidden: insufficient permissions');
        }
    }
    
} catch (Exception $e) {
    // Database error
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit();
    }
    
    // For regular requests, show error page or redirect
    die('Database error: ' . $e->getMessage());
}
?> 