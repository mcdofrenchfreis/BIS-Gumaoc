<?php

declare(strict_types=1);

session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/certificate_print_helpers.php';
require_once __DIR__ . '/../includes/AdminLogger.php';

$request_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$embed = isset($_GET['embed']) && $_GET['embed'] === '1';

if ($request_id <= 0) {
    http_response_code(400);
    echo 'Certificate request ID is required.';
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM certificate_requests WHERE id = ?');
$stmt->execute([$request_id]);
$certificate_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$certificate_data) {
    http_response_code(404);
    echo 'Certificate request not found.';
    exit;
}

if (strtolower((string) ($certificate_data['status'] ?? '')) !== 'processing') {
    http_response_code(403);
    echo 'Certificate can only be printed while the request is in processing status.';
    exit;
}

$cert_type = strtoupper(trim((string) ($certificate_data['certificate_type'] ?? '')));
$renderer = cr_renderer_for_type($cert_type);

if ($renderer === null) {
    http_response_code(400);
    echo 'No print template is available for certificate type: ' . htmlspecialchars($cert_type);
    exit;
}

$render_file = __DIR__ . '/../includes/certificate_render/' . $renderer . '.php';
if (!is_file($render_file)) {
    http_response_code(500);
    echo 'Print template is missing.';
    exit;
}

$log_types = [
    'indigency' => 'indigency_certificate',
    'barangay_clearance' => 'barangay_clearance',
    'residency' => 'residency_certificate',
    'tricycle_permit' => 'tricycle_permit',
    'cedula' => 'cedula_certificate',
];

$logger = new AdminLogger($pdo);
$logger->logPrintAction(
    'certificate_request',
    $request_id,
    $log_types[$renderer] ?? $renderer,
    [
        'certificate_type' => $certificate_data['certificate_type'],
        'applicant_name' => $certificate_data['full_name'],
        'print_timestamp' => date('Y-m-d H:i:s'),
    ]
);

try {
    include $render_file;
} catch (Throwable $e) {
    http_response_code(500);
    if ($embed) {
        echo '<p style="font-family:Arial,sans-serif;padding:16px;color:#842029;">Unable to render certificate: '
            . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    } else {
        echo 'Unable to render certificate.';
    }
    error_log('print-certificate render error: ' . $e->getMessage());
}
