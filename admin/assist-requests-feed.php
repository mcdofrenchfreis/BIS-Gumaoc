<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Optional: basic access control (comment out if not desired)
$requireAdmin = isset($_SESSION['is_admin']);
$isAllowed = !empty($_SESSION['rfid_authenticated']);
if ($requireAdmin) {
    $isAllowed = $isAllowed && !empty($_SESSION['is_admin']);
}
if (!$isAllowed) {
    http_response_code(200);
    echo json_encode(['last_id' => 0, 'count' => 0]);
    exit;
}

$pdo = null;
try {
    $dbPath = __DIR__ . '/../includes/db_connect.php';
    if (file_exists($dbPath)) {
        require_once $dbPath; // should define $pdo
    }
} catch (Throwable $e) {
    // ignore
}

$lastId = 0;
$total = 0;
if ($pdo instanceof PDO) {
    try {
        $stmt = $pdo->query('SELECT MAX(id) AS max_id, COUNT(*) AS total FROM assist_requests');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $lastId = (int)($row['max_id'] ?? 0);
            $total = (int)($row['total'] ?? 0);
        }
    } catch (Throwable $e) {
        // ignore; will return zeros
    }
}

echo json_encode(['last_id' => $lastId, 'count' => $total]);
