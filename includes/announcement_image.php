<?php

function announcement_ensure_image_column(PDO $pdo): void
{
    $stmt = $pdo->query("SHOW COLUMNS FROM updates LIKE 'image'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE updates ADD COLUMN image VARCHAR(255) NULL DEFAULT NULL AFTER description");
    }
}

function announcement_upload_dir(): string
{
    $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'announcements' . DIRECTORY_SEPARATOR;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir;
}

function announcement_image_url(string $filename, string $basePath = '../'): string
{
    return $basePath . 'uploads/announcements/' . rawurlencode(basename($filename));
}

function announcement_delete_image_file(?string $filename): void
{
    if ($filename === null || $filename === '') {
        return;
    }
    $path = announcement_upload_dir() . basename($filename);
    if (is_file($path)) {
        unlink($path);
    }
}

/**
 * @param array<string,mixed>|null $file
 * @return array{ok: bool, filename: ?string, error: ?string}
 */
function announcement_process_image_upload(?array $file, ?string $currentFilename, bool $removeRequested): array
{
    if ($removeRequested) {
        announcement_delete_image_file($currentFilename);
        return ['ok' => true, 'filename' => null, 'error' => null];
    }

    if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'filename' => $currentFilename, 'error' => null];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return [
            'ok' => false,
            'filename' => $currentFilename,
            'error' => 'Photo upload failed. Please try again.',
        ];
    }

    $maxSize = 5 * 1024 * 1024;
    if (($file['size'] ?? 0) > $maxSize) {
        return [
            'ok' => false,
            'filename' => $currentFilename,
            'error' => 'Photo must be 5MB or smaller.',
        ];
    }

    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($extension, $allowed, true)) {
        return [
            'ok' => false,
            'filename' => $currentFilename,
            'error' => 'Photo must be JPG, PNG, WebP, or GIF.',
        ];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : ($file['type'] ?? '');
    if ($finfo) {
        finfo_close($finfo);
    }
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if ($mime && !in_array($mime, $allowedMimes, true)) {
        return [
            'ok' => false,
            'filename' => $currentFilename,
            'error' => 'Invalid image file type.',
        ];
    }

    $filename = 'ann_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $dest = announcement_upload_dir() . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return [
            'ok' => false,
            'filename' => $currentFilename,
            'error' => 'Could not save the photo. Check folder permissions.',
        ];
    }

    if ($currentFilename && $currentFilename !== $filename) {
        announcement_delete_image_file($currentFilename);
    }

    return ['ok' => true, 'filename' => $filename, 'error' => null];
}
