<?php

/**
 * Certificate request statuses — "received" means the resident picked up the certificate.
 */

function certificate_request_ensure_status_schema(PDO $pdo): void
{
    $stmt = $pdo->query("SHOW COLUMNS FROM certificate_requests LIKE 'status'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return;
    }
    if (stripos((string) $row['Type'], "'received'") === false) {
        $pdo->exec("
            ALTER TABLE certificate_requests
            MODIFY COLUMN status ENUM('pending','processing','ready','released','received')
            DEFAULT 'pending'
        ");
    }
}

function certificate_request_statuses(): array
{
    return ['pending', 'processing', 'ready', 'released', 'received'];
}

function certificate_request_status_labels(): array
{
    return [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'ready' => 'Ready for Pickup',
        'released' => 'Released',
        'received' => 'Received by Resident',
    ];
}

function certificate_request_status_label(string $status): string
{
    $labels = certificate_request_status_labels();
    $key = strtolower(trim($status));
    return $labels[$key] ?? ucfirst($key);
}

function certificate_request_tracker_step(string $status): int
{
    $map = [
        'pending' => 1,
        'processing' => 2,
        'ready' => 3,
        'released' => 4,
        'received' => 5,
    ];
    return $map[strtolower(trim($status))] ?? 1;
}

function certificate_request_tracker_steps(): array
{
    return [
        ['num' => 1, 'label' => 'Submitted'],
        ['num' => 2, 'label' => 'Processing'],
        ['num' => 3, 'label' => 'Ready'],
        ['num' => 4, 'label' => 'Released'],
        ['num' => 5, 'label' => 'Received'],
    ];
}

function certificate_request_user_status_config(): array
{
    return [
        'pending' => [
            'label' => 'Pending Review',
            'color' => '#f59e0b',
            'icon' => 'clock',
        ],
        'processing' => [
            'label' => 'Being Processed',
            'color' => '#1565c0',
            'icon' => 'cog',
        ],
        'ready' => [
            'label' => 'Ready for Pickup',
            'color' => '#2e7d32',
            'icon' => 'check-circle',
        ],
        'released' => [
            'label' => 'Released — Pick Up',
            'color' => '#6a1b9a',
            'icon' => 'box-open',
        ],
        'received' => [
            'label' => 'Received',
            'color' => '#1b5e20',
            'icon' => 'check-double',
        ],
    ];
}

function certificate_request_can_transition(string $from, string $to): bool
{
    $from = strtolower(trim($from));
    $to = strtolower(trim($to));
    if ($from === $to) {
        return true;
    }
    $allowed = [
        'pending' => ['processing', 'ready', 'released', 'received'],
        'processing' => ['ready', 'released', 'received'],
        'ready' => ['released', 'received'],
        'released' => ['received'],
        'received' => [],
    ];
    return in_array($to, $allowed[$from] ?? [], true);
}
