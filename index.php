<?php
require_once __DIR__ . '/user/session_bootstrap.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: user/dashboard.php', true, 302);
    exit;
}

header('Location: user/login.php', true, 302);
exit;

