<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([ 'ok' => false, 'error' => 'Method Not Allowed' ]);
    exit;
}

// Read JSON body
$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '', true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([ 'ok' => false, 'error' => 'Invalid JSON' ]);
    exit;
}

// Normalize input
$page = isset($data['page']) ? substr((string)$data['page'], 0, 512) : '';
$user_id = isset($data['user_id']) ? $data['user_id'] : ($_SESSION['user_id'] ?? null);
$user_name = isset($data['user_name']) ? substr((string)$data['user_name'], 0, 255) : ($_SESSION['user_name'] ?? 'Guest');
$is_admin = !empty($data['is_admin']) ? 1 : 0;
$ts = isset($data['ts']) ? (int)$data['ts'] : time() * 1000;
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Try to include DB connection if available
$pdo = null;
try {
    $dbPath = __DIR__ . '/includes/db_connect.php';
    if (file_exists($dbPath)) {
        require_once $dbPath; // expected to define $pdo
    }
} catch (Throwable $e) {
    // ignore; we will continue without DB
}

// Optional: Write to database if PDO is present
$inserted_id = null;
if ($pdo instanceof PDO) {
    try {
        // Ensure table exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS assist_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            user_name VARCHAR(255) NOT NULL,
            page VARCHAR(512) NOT NULL,
            is_admin TINYINT(1) NOT NULL DEFAULT 0,
            ip VARCHAR(64) NULL,
            user_agent VARCHAR(512) NULL,
            ts_ms BIGINT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $stmt = $pdo->prepare("INSERT INTO assist_requests (user_id, user_name, page, is_admin, ip, user_agent, ts_ms)
                               VALUES (:user_id, :user_name, :page, :is_admin, :ip, :ua, :ts)");
        $stmt->execute([
            ':user_id' => $user_id,
            ':user_name' => $user_name,
            ':page' => $page ?: '/',
            ':is_admin' => $is_admin,
            ':ip' => substr($ip, 0, 64),
            ':ua' => substr($ua, 0, 512),
            ':ts' => $ts,
        ]);
        $inserted_id = $pdo->lastInsertId();
    } catch (Throwable $e) {
        // DB write failed; continue but include error detail in response
        http_response_code(202);
        echo json_encode([
            'ok' => true,
            'stored' => false,
            'id' => null,
            'message' => 'Assistance request received but not stored',
            'error' => 'db_write_failed'
        ]);
        exit;
    }
}

// Success response
http_response_code(200);
echo json_encode([
    'ok' => true,
    'stored' => (bool)$pdo,
    'id' => $inserted_id,
    'message' => 'Assistance request received',
]);
