<?php

declare(strict_types=1);

/**
 * Maps normalized certificate_type to renderer slug (filename without .php).
 */
function cr_renderer_for_type(string $certificate_type): ?string
{
    $certificate_type = strtoupper(trim($certificate_type));

    $map = [
        'BRGY. INDIGENCY' => 'indigency',
        'INDIGENCY' => 'indigency',
        'BRGY. CLEARANCE' => 'barangay_clearance',
        'BARANGAY CLEARANCE' => 'barangay_clearance',
        'CLEARANCE' => 'barangay_clearance',
        'BARANGAY CERTIFICATE' => 'barangay_clearance',
        'CLEARANCE CERTIFICATE' => 'barangay_clearance',
        'CERTIFICATION OF RESIDENCY' => 'residency',
        'RESIDENCY' => 'residency',
        'PROOF OF RESIDENCY' => 'residency',
        'CERTIFICATE OF RESIDENCY' => 'residency',
        'RESIDENCY CERTIFICATE' => 'residency',
        'TRICYCLE PERMIT' => 'tricycle_permit',
        'CEDULA' => 'cedula',
        'CEDULA/CTC' => 'cedula',
    ];

    return $map[$certificate_type] ?? null;
}

/**
 * Resolve applicant photo path (relative to site root, prefixed with ../ from admin).
 */
function cr_resolve_photo_path(PDO $pdo, array $certificate_data, int $request_id): string
{
    $photoSrc = '../assets/images/forms/1x1.jpeg';

    try {
        if (!empty($certificate_data['photo_path'])) {
            return '../' . ltrim($certificate_data['photo_path'], '/\\');
        }
        if (!empty($certificate_data['photo_id'])) {
            $stmtPhoto = $pdo->prepare('SELECT photo_path FROM user_photos WHERE id = ? AND is_active = 1');
            $stmtPhoto->execute([$certificate_data['photo_id']]);
            $photo = $stmtPhoto->fetch(PDO::FETCH_ASSOC);
            if ($photo && !empty($photo['photo_path'])) {
                return '../' . ltrim($photo['photo_path'], '/\\');
            }
        }
        if (!empty($certificate_data['user_id'])) {
            $stmtPhoto = $pdo->prepare(
                'SELECT photo_path FROM user_photos WHERE user_id = ? AND certificate_request_id = ? AND is_active = 1 ORDER BY uploaded_at DESC LIMIT 1'
            );
            $stmtPhoto->execute([$certificate_data['user_id'], $request_id]);
            $photo = $stmtPhoto->fetch(PDO::FETCH_ASSOC);
            if ($photo && !empty($photo['photo_path'])) {
                return '../' . ltrim($photo['photo_path'], '/\\');
            }
            $stmtLatest = $pdo->prepare(
                'SELECT photo_path FROM user_photos WHERE user_id = ? AND is_active = 1 ORDER BY is_default DESC, uploaded_at DESC LIMIT 1'
            );
            $stmtLatest->execute([$certificate_data['user_id']]);
            $latest = $stmtLatest->fetch(PDO::FETCH_ASSOC);
            if ($latest && !empty($latest['photo_path'])) {
                return '../' . ltrim($latest['photo_path'], '/\\');
            }
        }
        if (!empty($certificate_data['photo_2x2'])) {
            return '../uploads/user_photos/' . ltrim($certificate_data['photo_2x2'], '/\\');
        }
    } catch (Exception $e) {
        // keep default placeholder
    }

    return $photoSrc;
}

function cr_calculate_age(array $certificate_data): int
{
    $raw = $certificate_data['birth_date'] ?? $certificate_data['cedula_date_of_birth'] ?? null;
    if (empty($raw)) {
        return 0;
    }
    try {
        $birth = new DateTime((string) $raw);
        return (new DateTime())->diff($birth)->y;
    } catch (Exception $e) {
        return 0;
    }
}

function cr_format_birth_date(array $certificate_data, string $format = 'F j, Y'): string
{
    $raw = $certificate_data['birth_date'] ?? $certificate_data['cedula_date_of_birth'] ?? null;
    if (empty($raw)) {
        return '';
    }
    $ts = strtotime((string) $raw);
    return $ts !== false ? date($format, $ts) : '';
}

function cr_parse_additional_data(array $certificate_data): array
{
    if (empty($certificate_data['additional_data'])) {
        return [];
    }
    try {
        $decoded = json_decode($certificate_data['additional_data'], true);
        return is_array($decoded) ? $decoded : [];
    } catch (Exception $e) {
        return [];
    }
}
