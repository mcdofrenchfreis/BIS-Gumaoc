<?php
session_start();
include 'db_connect.php';
include 'AdminLogger.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'], $input['target_type'], $input['description'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$logger = new AdminLogger($pdo);

$result = $logger->log(
    $input['action'],
    $input['target_type'],
    $input['description'],
    $input['target_id'] ?? null,
    $input['details'] ?? null
);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to log action']);
}
?>