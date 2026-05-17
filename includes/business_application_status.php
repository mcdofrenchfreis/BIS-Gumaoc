<?php

/**
 * Business application statuses — "received" means the resident picked up the clearance/certificate.
 */

function business_application_ensure_status_schema(PDO $pdo): void
{
    $stmt = $pdo->query("SHOW COLUMNS FROM business_applications LIKE 'status'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return;
    }

    $type = (string) ($row['Type'] ?? '');
    $needsReady = stripos($type, "'ready'") === false;
    $needsReceived = stripos($type, "'received'") === false;

    if ($needsReady || $needsReceived || stripos($type, "'pending','received','reviewing'") !== false) {
        // Old "received" meant barangay inbox — move to reviewing before enum change
        if (stripos($type, "'pending','received','reviewing'") !== false) {
            $pdo->exec("
                UPDATE business_applications
                SET status = 'reviewing'
                WHERE status = 'received'
            ");
        }

        $pdo->exec("
            ALTER TABLE business_applications
            MODIFY COLUMN status ENUM('pending','reviewing','approved','ready','received','rejected')
            DEFAULT 'pending'
        ");
    }
}

function business_application_statuses(): array
{
    return ['pending', 'reviewing', 'approved', 'ready', 'received', 'rejected'];
}

function business_application_status_labels(): array
{
    return [
        'pending' => 'Pending',
        'reviewing' => 'Reviewing',
        'approved' => 'Approved',
        'ready' => 'Ready for Pickup',
        'received' => 'Received by Resident',
        'rejected' => 'Rejected',
    ];
}

function business_application_status_label(string $status): string
{
    $labels = business_application_status_labels();
    $key = strtolower(trim($status));
    return $labels[$key] ?? ucfirst($key);
}

function business_application_tracker_step(string $status): int
{
    $map = [
        'pending' => 1,
        'reviewing' => 2,
        'approved' => 3,
        'ready' => 4,
        'received' => 5,
        'rejected' => 3,
    ];
    return $map[strtolower(trim($status))] ?? 1;
}

function business_application_tracker_steps(string $status): array
{
    $isRejected = (strtolower(trim($status)) === 'rejected');
    if ($isRejected) {
        return [
            ['num' => 1, 'label' => 'Submitted'],
            ['num' => 2, 'label' => 'Under Review'],
            ['num' => 3, 'label' => 'Not Approved'],
        ];
    }
    return [
        ['num' => 1, 'label' => 'Submitted'],
        ['num' => 2, 'label' => 'Under Review'],
        ['num' => 3, 'label' => 'Approved'],
        ['num' => 4, 'label' => 'Ready'],
        ['num' => 5, 'label' => 'Received'],
    ];
}

function business_application_user_status_config(): array
{
    return [
        'pending' => [
            'label' => 'Submitted',
            'color' => '#f59e0b',
            'icon' => 'paper-plane',
            'step' => 1,
            'message' => 'Your application was submitted and is waiting for review.',
        ],
        'reviewing' => [
            'label' => 'Under Review',
            'color' => '#1565c0',
            'icon' => 'search',
            'step' => 2,
            'message' => 'Barangay staff are reviewing your documents and business details.',
        ],
        'approved' => [
            'label' => 'Approved',
            'color' => '#2e7d32',
            'icon' => 'check-circle',
            'step' => 3,
            'message' => 'Your application is approved. Your clearance will be prepared for pickup.',
        ],
        'ready' => [
            'label' => 'Ready for Pickup',
            'color' => '#6a1b9a',
            'icon' => 'file-alt',
            'step' => 4,
            'message' => 'Your business clearance is ready at the barangay hall. Please pick it up.',
        ],
        'received' => [
            'label' => 'Received',
            'color' => '#1b5e20',
            'icon' => 'check-double',
            'step' => 5,
            'message' => 'You have received your business clearance. This application is complete.',
        ],
        'rejected' => [
            'label' => 'Rejected',
            'color' => '#c62828',
            'icon' => 'times-circle',
            'step' => 3,
            'message' => 'This application was not approved. Contact the barangay office for details.',
        ],
    ];
}

function business_application_can_transition(string $from, string $to): bool
{
    $from = strtolower(trim($from));
    $to = strtolower(trim($to));
    if ($from === $to) {
        return true;
    }
    $allowed = [
        'pending' => ['reviewing', 'approved', 'rejected'],
        'reviewing' => ['approved', 'rejected'],
        'approved' => ['ready', 'received', 'rejected'],
        'ready' => ['received'],
        'received' => [],
        'rejected' => [],
    ];
    return in_array($to, $allowed[$from] ?? [], true);
}
