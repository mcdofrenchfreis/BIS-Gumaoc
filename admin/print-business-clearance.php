<?php

declare(strict_types=1);

session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/AdminLogger.php';

$request_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$embed = isset($_GET['embed']) && $_GET['embed'] === '1';

if ($request_id <= 0) {
    http_response_code(400);
    echo 'Business application ID is required.';
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM business_applications WHERE id = ?');
$stmt->execute([$request_id]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    http_response_code(404);
    echo 'Business application not found.';
    exit;
}

if (strtolower((string) ($application['status'] ?? '')) !== 'approved') {
    http_response_code(403);
    echo 'Business clearance can only be printed for approved applications.';
    exit;
}

$clearance_number = 'BBC-' . date('Y') . '-' . str_pad((string) $request_id, 4, '0', STR_PAD_LEFT);
$current_date = date('F j, Y');
$valid_until = date('F j, Y', strtotime('+1 year'));

$logger = new AdminLogger($pdo);
$logger->logPrintAction(
    'business_application',
    $request_id,
    'business_clearance',
    [
        'business_name' => $application['business_name'] ?? '',
        'owner_name' => $application['owner_name'] ?? '',
        'clearance_number' => $clearance_number,
        'print_timestamp' => date('Y-m-d H:i:s'),
    ]
);

$render_file = __DIR__ . '/../includes/certificate_render/business_clearance.php';
if (!is_file($render_file)) {
    http_response_code(500);
    echo 'Print template is missing.';
    exit;
}

try {
    include $render_file;
} catch (Throwable $e) {
    http_response_code(500);
    if ($embed) {
        echo '<p style="font-family:Arial,sans-serif;padding:16px;color:#842029;">Unable to render business clearance: '
            . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    } else {
        echo 'Unable to render business clearance.';
    }
    error_log('print-business-clearance render error: ' . $e->getMessage());
}
