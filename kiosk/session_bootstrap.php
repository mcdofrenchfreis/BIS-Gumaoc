<?php
// Kiosk area session bootstrap
// Must be included BEFORE any output

// Unique session name for kiosk area
session_name('GUMAOC_KIOSK_SESSID');

// Use hosting provider's default session storage to avoid cross-request mismatches

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
$host = $_SERVER['HTTP_HOST'] ?? '';
$cookieParams = [
    'lifetime' => 0, // session cookie
    'path' => '/', // full site scope
    'domain' => $host ?: '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
];

session_set_cookie_params($cookieParams);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
