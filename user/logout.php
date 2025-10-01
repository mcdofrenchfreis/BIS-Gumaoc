<?php
require_once __DIR__ . '/session_bootstrap.php';

// Clear all session variables
$_SESSION = [];

// Delete the session cookie for the user path
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// Destroy the session
session_destroy();

header('Location: login.php');
exit;
?>