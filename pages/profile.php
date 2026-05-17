<?php
/**
 * Legacy URL — profile lives in the user portal (different navigation).
 */
require_once __DIR__ . '/../user/session_bootstrap.php';
require_once __DIR__ . '/../user/includes/portal_urls.php';
header('Location: ' . user_portal_url('profile.php'), true, 302);
exit;
