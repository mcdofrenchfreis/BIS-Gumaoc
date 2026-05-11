<?php
// User area session bootstrap
// Must be included BEFORE any output

// Unique session name for user area
session_name('GUMAOC_USER_SESSID');

// Use hosting provider's default session storage to avoid cross-request mismatches

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
$host = $_SERVER['HTTP_HOST'] ?? '';
$cookieParams = [
    'lifetime' => 0, // session cookie
    'path' => '/', // full site scope
    'domain' => $host ?: '', // current host
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
];
// Set session ini settings BEFORE session starts
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

session_set_cookie_params($cookieParams);

// Start session if not started
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
