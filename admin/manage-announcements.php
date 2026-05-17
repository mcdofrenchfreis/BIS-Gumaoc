<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/AdminLogger.php';
require_once '../includes/announcement_image.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$logger = new AdminLogger($pdo);

$pdo->exec("
    CREATE TABLE IF NOT EXISTS updates (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        badge_text VARCHAR(50) NOT NULL,
        badge_type ENUM('important', 'new', 'community', 'info') DEFAULT 'info',
        date VARCHAR(50) NOT NULL,
        status VARCHAR(50) NOT NULL,
        is_priority TINYINT(1) DEFAULT 0,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
");
announcement_ensure_image_column($pdo);
announcement_upload_dir();

$badge_presets = [
    'important' => ['label' => 'Important', 'hint' => 'Urgent or critical updates', 'icon' => 'fa-exclamation-circle'],
    'new'       => ['label' => 'New', 'hint' => 'Recently launched services', 'icon' => 'fa-star'],
    'community' => ['label' => 'Community', 'hint' => 'Events and gatherings', 'icon' => 'fa-users'],
    'info'      => ['label' => 'General Info', 'hint' => 'Regular barangay news', 'icon' => 'fa-info-circle'],
];
$allowed_badges = array_keys($badge_presets);
$allowed_statuses = ['published', 'draft', 'archived'];

function format_display_date(string $dateInput): string
{
    $ts = strtotime($dateInput);
    return $ts ? date('F j, Y', $ts) : $dateInput;
}

function status_label(string $status): string
{
    $map = ['published' => 'Published', 'draft' => 'Draft', 'archived' => 'Archived'];
    $key = strtolower(trim($status));
    return $map[$key] ?? ucfirst($status);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $badge_type = $_POST['badge_type'] ?? 'info';
        $date_raw = trim($_POST['date'] ?? '');
        $status = strtolower(trim($_POST['status'] ?? 'draft'));
        $is_priority = isset($_POST['is_priority']) ? 1 : 0;
        $display_order = (int)($_POST['display_order'] ?? 0);

        if (!in_array($badge_type, $allowed_badges, true)) {
            $badge_type = 'info';
        }
        $badge_text = $badge_presets[$badge_type]['label'];
        if (!in_array($status, $allowed_statuses, true)) {
            $status = 'draft';
        }

        if ($title === '' || $description === '') {
            $_SESSION['toast_message'] = 'Please add a title and message for the announcement.';
            $_SESSION['toast_type'] = 'error';
        } else {
            $currentImage = null;
            if ($id > 0) {
                $imgStmt = $pdo->prepare('SELECT image FROM updates WHERE id = ?');
                $imgStmt->execute([$id]);
                $currentImage = $imgStmt->fetchColumn() ?: null;
            }

            $imageResult = announcement_process_image_upload(
                $_FILES['announcement_image'] ?? null,
                $currentImage ? (string) $currentImage : null,
                isset($_POST['remove_image'])
            );

            if (!$imageResult['ok']) {
                $_SESSION['toast_message'] = $imageResult['error'];
                $_SESSION['toast_type'] = 'error';
                header('Location: manage-announcements.php' . ($id > 0 ? '?edit=' . $id : '?new=1'));
                exit;
            }

            $imageFilename = $imageResult['filename'];
            $display_date = $date_raw !== '' ? format_display_date($date_raw) : date('F j, Y');

            if ($id > 0) {
                $stmt = $pdo->prepare("
                    UPDATE updates
                    SET title = ?, description = ?, image = ?, badge_text = ?, badge_type = ?,
                        date = ?, status = ?, is_priority = ?, display_order = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $title, $description, $imageFilename, $badge_text, $badge_type,
                    $display_date, $status, $is_priority, $display_order, $id
                ]);
                $logger->log('update', 'announcement', "Updated announcement #$id: $title", $id);
                $_SESSION['toast_message'] = 'Announcement updated successfully.';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO updates (title, description, image, badge_text, badge_type, date, status, is_priority, display_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $title, $description, $imageFilename, $badge_text, $badge_type,
                    $display_date, $status, $is_priority, $display_order
                ]);
                $newId = (int)$pdo->lastInsertId();
                $logger->log('create', 'announcement', "Created announcement #$newId: $title", $newId);
                $_SESSION['toast_message'] = $status === 'published'
                    ? 'Announcement published! Residents can see it now.'
                    : 'Draft saved. Publish when you are ready.';
            }
            $_SESSION['toast_type'] = 'success';
        }
    } elseif ($action === 'delete' && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("SELECT title, image FROM updates WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            announcement_delete_image_file($row['image'] ?? null);
            $pdo->prepare("DELETE FROM updates WHERE id = ?")->execute([$id]);
            $logger->log('delete', 'announcement', "Deleted announcement #$id: {$row['title']}", $id);
            $_SESSION['toast_message'] = 'Announcement removed.';
            $_SESSION['toast_type'] = 'success';
        }
    } elseif ($action === 'toggle_publish' && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("SELECT title, status FROM updates WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $current = strtolower(trim($row['status']));
            $newStatus = ($current === 'published') ? 'draft' : 'published';
            $pdo->prepare("UPDATE updates SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
            $logger->log('status_update', 'announcement', "Changed announcement #$id status to $newStatus", $id);
            $_SESSION['toast_message'] = $newStatus === 'published'
                ? 'Announcement is now live for residents.'
                : 'Announcement moved to draft.';
            $_SESSION['toast_type'] = 'success';
        }
    }

    header('Location: manage-announcements.php');
    exit;
}

$logger->log('page_view', 'admin_panel', 'Viewed manage announcements admin page');

$edit_id = (int)($_GET['edit'] ?? 0);
$edit_item = null;
if ($edit_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM updates WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_item = $stmt->fetch(PDO::FETCH_ASSOC);
}

$announcements = $pdo->query("
    SELECT * FROM updates
    ORDER BY is_priority DESC, display_order ASC, created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$toast_message = $_SESSION['toast_message'] ?? '';
$toast_type = $_SESSION['toast_type'] ?? '';
unset($_SESSION['toast_message'], $_SESSION['toast_type']);

$form = $edit_item ?: [
    'id' => 0,
    'title' => '',
    'description' => '',
    'badge_type' => 'info',
    'date' => date('Y-m-d'),
    'status' => 'draft',
    'is_priority' => 0,
    'display_order' => 0,
    'image' => '',
];

$form_date_value = date('Y-m-d');
if (!empty($edit_item['date'])) {
    $parsed = strtotime($edit_item['date']);
    if ($parsed) {
        $form_date_value = date('Y-m-d', $parsed);
    }
}

$edit_status = strtolower(trim($form['status'] ?? 'draft'));
if (!in_array($edit_status, $allowed_statuses, true)) {
    $edit_status = 'published';
}

$show_form = $edit_item || isset($_GET['new']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Announcements - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --green-dark: #1b5e20;
            --green: #2e7d32;
            --green-light: #e8f5e9;
            --border: #e5ebe5;
            --text: #1a1a1a;
            --muted: #5f6b5f;
        }
        * { box-sizing: border-box; }
        body {
            background: #f4f7f4;
            margin: 0;
            padding: 76px 0 3rem;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--text);
            line-height: 1.5;
        }
        .wrap { max-width: 820px; margin: 0 auto; padding: 0 1.25rem; }

        .top-bar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .top-bar h1 {
            margin: 0 0 0.25rem;
            font-size: 1.5rem;
            color: var(--green-dark);
        }
        .top-bar p { margin: 0; color: var(--muted); font-size: 0.95rem; }

        .btn-new {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1.2rem;
            background: var(--green);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(46, 125, 50, 0.25);
            transition: background 0.2s, transform 0.15s;
        }
        .btn-new:hover { background: var(--green-dark); transform: translateY(-1px); }

        /* Form panel */
        .form-panel {
            background: #fff;
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 8px 32px rgba(27, 94, 32, 0.06);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        .form-panel-header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, var(--green-dark), var(--green));
            color: #fff;
        }
        .form-panel-header h2 {
            margin: 0 0 0.2rem;
            font-size: 1.2rem;
            font-weight: 700;
        }
        .form-panel-header p { margin: 0; font-size: 0.88rem; opacity: 0.92; }

        .form-body { padding: 1.5rem; }

        .step {
            margin-bottom: 1.75rem;
            padding-bottom: 1.75rem;
            border-bottom: 1px solid var(--border);
        }
        .step:last-of-type { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .step-label {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1rem;
        }
        .step-num {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--green-light);
            color: var(--green-dark);
            font-weight: 700;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .step-title { font-weight: 700; color: var(--green-dark); font-size: 1rem; }
        .step-hint { font-size: 0.82rem; color: var(--muted); margin-top: 0.15rem; }

        .field { margin-bottom: 1rem; }
        .field:last-child { margin-bottom: 0; }
        .field label {
            display: block;
            font-weight: 600;
            font-size: 0.88rem;
            color: #333;
            margin-bottom: 0.4rem;
        }
        .field .hint {
            font-size: 0.8rem;
            color: var(--muted);
            font-weight: 400;
            margin-top: 0.2rem;
        }
        .field input[type="text"],
        .field input[type="date"],
        .field input[type="number"],
        .field textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fafcfa;
        }
        .field input:focus,
        .field textarea:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.12);
            background: #fff;
        }
        .field textarea { min-height: 140px; resize: vertical; line-height: 1.55; }
        .char-count { text-align: right; font-size: 0.75rem; color: var(--muted); margin-top: 0.25rem; }

        .photo-upload {
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 1.25rem;
            background: #fafcfa;
            text-align: center;
        }
        .photo-upload.has-preview { padding: 0.75rem; text-align: left; }
        .photo-upload input[type="file"] {
            width: 100%;
            font-size: 0.88rem;
            margin-top: 0.5rem;
        }
        .photo-preview-wrap { margin-bottom: 0.75rem; }
        .photo-preview {
            width: 100%;
            max-height: 220px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid var(--border);
            display: block;
        }
        .photo-current {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            flex-wrap: wrap;
        }
        .photo-current img {
            width: 140px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid var(--border);
        }
        .remove-photo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.75rem;
            font-size: 0.88rem;
            color: #b91c1c;
            cursor: pointer;
        }
        .remove-photo input { accent-color: #c62828; }

        .ann-card-with-thumb {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }
        .ann-thumb {
            width: 72px;
            height: 72px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
            border: 1px solid var(--border);
        }
        .ann-card-body { flex: 1; min-width: 0; }

        /* Category cards */
        .category-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.65rem;
        }
        @media (min-width: 520px) { .category-grid { grid-template-columns: repeat(4, 1fr); } }
        .category-option { position: relative; }
        .category-option input { position: absolute; opacity: 0; pointer-events: none; }
        .category-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 0.85rem 0.5rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            background: #fafcfa;
            min-height: 100%;
        }
        .category-card i { font-size: 1.35rem; margin-bottom: 0.35rem; color: var(--muted); }
        .category-card strong { font-size: 0.82rem; color: #333; }
        .category-option input:checked + .category-card {
            border-color: var(--green);
            background: var(--green-light);
            box-shadow: 0 0 0 1px var(--green);
        }
        .category-option input:checked + .category-card i { color: var(--green-dark); }
        .cat-important input:checked + .category-card { border-color: #c62828; background: #ffebee; }
        .cat-important input:checked + .category-card i { color: #c62828; }
        .cat-new input:checked + .category-card { border-color: #1565c0; background: #e3f2fd; }
        .cat-new input:checked + .category-card i { color: #1565c0; }
        .cat-community input:checked + .category-card { border-color: #e65100; background: #fff3e0; }
        .cat-community input:checked + .category-card i { color: #e65100; }

        /* Status options */
        .status-options { display: flex; flex-direction: column; gap: 0.5rem; }
        .status-option { position: relative; }
        .status-option input { position: absolute; opacity: 0; }
        .status-card {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.85rem 1rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            cursor: pointer;
            background: #fafcfa;
            transition: all 0.2s;
        }
        .status-card i { font-size: 1.25rem; width: 1.5rem; text-align: center; }
        .status-card .sc-title { font-weight: 600; font-size: 0.95rem; }
        .status-card .sc-desc { font-size: 0.8rem; color: var(--muted); }
        .status-option input:checked + .status-card {
            border-color: var(--green);
            background: var(--green-light);
        }
        .status-option[data-status="published"] input:checked + .status-card {
            border-color: var(--green);
            background: #e8f5e9;
        }
        .status-option[data-status="draft"] input:checked + .status-card {
            border-color: #9e9e9e;
            background: #f5f5f5;
        }
        .status-option[data-status="archived"] input:checked + .status-card {
            border-color: #f59e0b;
            background: #fffbeb;
        }

        .priority-toggle {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.85rem 1rem;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 12px;
            margin-top: 1rem;
            cursor: pointer;
        }
        .priority-toggle input { width: 18px; height: 18px; accent-color: #f59e0b; }
        .priority-toggle span { font-size: 0.9rem; }

        details.advanced {
            margin-top: 1rem;
            font-size: 0.88rem;
        }
        details.advanced summary {
            cursor: pointer;
            color: var(--muted);
            font-weight: 600;
            padding: 0.5rem 0;
        }
        details.advanced .field { margin-top: 0.75rem; max-width: 200px; }

        .form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            padding: 1.25rem 1.5rem;
            background: #f7faf7;
            border-top: 1px solid var(--border);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 0.75rem 1.35rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-publish { background: var(--green); color: #fff; flex: 1; min-width: 160px; }
        .btn-publish:hover { background: var(--green-dark); }
        .btn-draft { background: #fff; color: #555; border: 1.5px solid var(--border); }
        .btn-draft:hover { background: #f5f5f5; }
        .btn-cancel { background: transparent; color: var(--muted); padding: 0.75rem 1rem; }
        .btn-cancel:hover { color: #333; }

        /* List */
        .list-section h2 {
            font-size: 1.1rem;
            color: var(--green-dark);
            margin: 0 0 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .list-count {
            background: var(--green-light);
            color: var(--green-dark);
            font-size: 0.8rem;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            font-weight: 700;
        }
        .ann-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1.15rem 1.25rem;
            margin-bottom: 0.75rem;
            transition: box-shadow 0.2s;
        }
        .ann-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.05); }
        .ann-card.priority { border-left: 4px solid #f59e0b; }
        .ann-card-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.5rem; }
        .ann-card-head h3 { margin: 0; font-size: 1.05rem; color: var(--text); }
        .ann-badges { display: flex; flex-wrap: wrap; gap: 0.35rem; flex-shrink: 0; }
        .pill {
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
        }
        .pill-published { background: #dcfce7; color: #166534; }
        .pill-draft { background: #f3f4f6; color: #4b5563; }
        .pill-archived { background: #fef3c7; color: #92400e; }
        .pill-important { background: #fee2e2; color: #b91c1c; }
        .pill-new { background: #dbeafe; color: #1d4ed8; }
        .pill-community { background: #fef3c7; color: #b45309; }
        .pill-info { background: #e8f5e9; color: #1b5e20; }
        .ann-excerpt {
            color: var(--muted);
            font-size: 0.9rem;
            margin: 0 0 0.65rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .ann-meta { font-size: 0.8rem; color: #888; margin-bottom: 0.75rem; }
        .ann-actions { display: flex; flex-wrap: wrap; gap: 0.4rem; }
        .ann-actions .btn { padding: 0.45rem 0.75rem; font-size: 0.82rem; }
        .btn-edit { background: #f3f4f6; color: #374151; }
        .btn-pub { background: var(--green-light); color: var(--green-dark); }
        .btn-del { background: #fee2e2; color: #b91c1c; }

        .empty-list {
            text-align: center;
            padding: 2.5rem 1.5rem;
            background: #fff;
            border-radius: 14px;
            border: 1px dashed #c8e6c9;
            color: var(--muted);
        }
        .empty-list i { font-size: 2.5rem; color: #a5d6a7; margin-bottom: 0.75rem; }

        .toast {
            position: fixed;
            top: 76px;
            left: 50%;
            transform: translateX(-50%);
            padding: 0.85rem 1.25rem;
            border-radius: 12px;
            color: #fff;
            font-weight: 600;
            font-size: 0.95rem;
            z-index: 2000;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            max-width: 90%;
            text-align: center;
        }
        .toast.success { background: var(--green); }
        .toast.error { background: #c62828; }
    </style>
</head>
<body>
<?php $base_path = '../'; include __DIR__ . '/../includes/admin_mini_nav.php'; ?>

<?php if ($toast_message): ?>
<div class="toast <?php echo htmlspecialchars($toast_type === 'error' ? 'error' : 'success'); ?>" id="toast">
    <?php echo htmlspecialchars($toast_message); ?>
</div>
<?php endif; ?>

<div class="wrap">
    <div class="top-bar">
        <div>
            <h1><i class="fas fa-bullhorn"></i> Announcements</h1>
            <p>Share news and updates with barangay residents.</p>
        </div>
        <?php if (!$show_form): ?>
        <a href="manage-announcements.php?new=1" class="btn-new">
            <i class="fas fa-plus"></i> New announcement
        </a>
        <?php endif; ?>
    </div>

    <?php if ($show_form): ?>
    <div class="form-panel" id="announcement-form">
        <div class="form-panel-header">
            <h2><?php echo $edit_item ? 'Edit announcement' : 'Create announcement'; ?></h2>
            <p>Fill in each section below — residents will see this on their Announcements page.</p>
        </div>

        <form method="post" action="manage-announcements.php" id="annForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?php echo (int)$form['id']; ?>">
            <input type="hidden" name="status" id="statusField" value="<?php echo htmlspecialchars($edit_status); ?>">

            <div class="form-body">
                <!-- Step 1 -->
                <section class="step">
                    <div class="step-label">
                        <span class="step-num">1</span>
                        <div>
                            <div class="step-title">Write your message</div>
                            <p class="step-hint">Keep the title short; use the message for details.</p>
                        </div>
                    </div>
                    <div class="field">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" required maxlength="255"
                               placeholder="e.g. Free vaccination drive this Saturday"
                               value="<?php echo htmlspecialchars($form['title']); ?>">
                    </div>
                    <div class="field">
                        <label for="description">Message</label>
                        <textarea id="description" name="description" required maxlength="2000"
                                  placeholder="What should residents know? Include date, time, location, or how to join."><?php echo htmlspecialchars($form['description']); ?></textarea>
                        <p class="char-count"><span id="descCount">0</span> / 2000</p>
                    </div>
                    <div class="field">
                        <label>Photo <span style="font-weight:400;color:var(--muted)">(optional)</span></label>
                        <p class="hint" style="margin-bottom:0.65rem;">Add a banner or event photo. You can publish with or without one.</p>
                        <?php
                        $existingImage = !empty($form['image']) ? (string) $form['image'] : '';
                        $existingImageUrl = $existingImage !== '' ? announcement_image_url($existingImage, '../') : '';
                        ?>
                        <?php if ($existingImage !== ''): ?>
                        <div class="photo-current" id="currentPhotoBlock">
                            <img src="<?php echo htmlspecialchars($existingImageUrl); ?>" alt="Current announcement photo">
                            <div>
                                <p class="hint" style="margin:0 0 0.5rem;">Current photo is saved. Upload a new file to replace it.</p>
                                <label class="remove-photo">
                                    <input type="checkbox" name="remove_image" value="1" id="removeImage">
                                    Remove this photo
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="photo-upload" id="photoUploadBox">
                            <div class="photo-preview-wrap" id="photoPreviewWrap" hidden>
                                <img src="" alt="" class="photo-preview" id="photoPreview">
                            </div>
                            <label for="announcement_image" style="font-weight:600;font-size:0.9rem;color:var(--green-dark);">
                                <i class="fas fa-image"></i> Choose photo
                            </label>
                            <input type="file" name="announcement_image" id="announcement_image"
                                   accept="image/jpeg,image/png,image/webp,image/gif">
                            <p class="hint" style="margin:0.5rem 0 0;">JPG, PNG, WebP, or GIF · max 5MB</p>
                        </div>
                    </div>
                </section>

                <!-- Step 2 -->
                <section class="step">
                    <div class="step-label">
                        <span class="step-num">2</span>
                        <div>
                            <div class="step-title">When is this relevant?</div>
                        </div>
                    </div>
                    <div class="field">
                        <label for="date">Display date</label>
                        <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($form_date_value); ?>">
                        <p class="hint">Shown to residents on the announcement card.</p>
                    </div>
                </section>

                <!-- Step 3 -->
                <section class="step">
                    <div class="step-label">
                        <span class="step-num">3</span>
                        <div><div class="step-title">Choose a category</div></div>
                    </div>
                    <div class="category-grid">
                        <?php foreach ($badge_presets as $key => $preset): ?>
                        <label class="category-option cat-<?php echo $key; ?>">
                            <input type="radio" name="badge_type" value="<?php echo $key; ?>"
                                <?php echo ($form['badge_type'] === $key) ? 'checked' : ''; ?>>
                            <span class="category-card">
                                <i class="fas <?php echo $preset['icon']; ?>"></i>
                                <strong><?php echo htmlspecialchars($preset['label']); ?></strong>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Step 4 -->
                <section class="step">
                    <div class="step-label">
                        <span class="step-num">4</span>
                        <div><div class="step-title">Publishing options</div></div>
                    </div>
                    <div class="status-options">
                        <label class="status-option" data-status="published">
                            <input type="radio" name="status_radio" value="published"
                                <?php echo $edit_status === 'published' ? 'checked' : ''; ?>>
                            <span class="status-card">
                                <i class="fas fa-check-circle" style="color:#2e7d32"></i>
                                <span>
                                    <span class="sc-title">Publish now</span>
                                    <span class="sc-desc">Visible to all residents immediately</span>
                                </span>
                            </span>
                        </label>
                        <label class="status-option" data-status="draft">
                            <input type="radio" name="status_radio" value="draft"
                                <?php echo $edit_status === 'draft' ? 'checked' : ''; ?>>
                            <span class="status-card">
                                <i class="fas fa-file-alt" style="color:#757575"></i>
                                <span>
                                    <span class="sc-title">Save as draft</span>
                                    <span class="sc-desc">Only you can see it until you publish</span>
                                </span>
                            </span>
                        </label>
                        <label class="status-option" data-status="archived">
                            <input type="radio" name="status_radio" value="archived"
                                <?php echo $edit_status === 'archived' ? 'checked' : ''; ?>>
                            <span class="status-card">
                                <i class="fas fa-archive" style="color:#f59e0b"></i>
                                <span>
                                    <span class="sc-title">Archive</span>
                                    <span class="sc-desc">Hide from residents but keep on record</span>
                                </span>
                            </span>
                        </label>
                    </div>

                    <label class="priority-toggle">
                        <input type="checkbox" name="is_priority" value="1"
                            <?php echo !empty($form['is_priority']) ? 'checked' : ''; ?>>
                        <span><strong>Pin as priority</strong> — shows at the top of the list</span>
                    </label>

                    <details class="advanced">
                        <summary>Advanced options</summary>
                        <div class="field">
                            <label for="display_order">Sort order (lower = higher)</label>
                            <input type="number" id="display_order" name="display_order" min="0"
                                   value="<?php echo (int)$form['display_order']; ?>">
                        </div>
                    </details>
                </section>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-publish" id="submitBtn">
                    <i class="fas fa-bullhorn"></i>
                    <span id="submitLabel"><?php echo $edit_status === 'published' ? 'Save & publish' : ($edit_status === 'archived' ? 'Save archived' : 'Save draft'); ?></span>
                </button>
                <a href="manage-announcements.php" class="btn btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <section class="list-section">
        <h2>Your announcements <span class="list-count"><?php echo count($announcements); ?></span></h2>

        <?php if (empty($announcements)): ?>
        <div class="empty-list">
            <i class="fas fa-bullhorn"></i>
            <p>No announcements yet. Click <strong>New announcement</strong> to get started.</p>
        </div>
        <?php else: ?>
            <?php foreach ($announcements as $item):
                $st = strtolower(trim($item['status']));
                $statusClass = in_array($st, $allowed_statuses, true) ? "pill-$st" : 'pill-published';
                $badgeClass = 'pill-' . ($item['badge_type'] ?? 'info');
            ?>
            <article class="ann-card <?php echo $item['is_priority'] ? 'priority' : ''; ?>">
                <div class="ann-card-with-thumb">
                <?php if (!empty($item['image'])): ?>
                <img class="ann-thumb" src="<?php echo htmlspecialchars(announcement_image_url($item['image'], '../')); ?>" alt="">
                <?php endif; ?>
                <div class="ann-card-body">
                <div class="ann-card-head">
                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    <div class="ann-badges">
                        <span class="pill <?php echo htmlspecialchars($badgeClass); ?>"><?php echo htmlspecialchars($item['badge_text']); ?></span>
                        <span class="pill <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars(status_label($item['status'])); ?></span>
                    </div>
                </div>
                <p class="ann-excerpt"><?php echo htmlspecialchars($item['description']); ?></p>
                <p class="ann-meta">
                    <i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($item['date']); ?>
                    <?php if ($item['is_priority']): ?> &middot; <i class="fas fa-thumbtack"></i> Priority<?php endif; ?>
                    <?php if (!empty($item['image'])): ?> &middot; <i class="fas fa-image"></i> Has photo<?php endif; ?>
                </p>
                <div class="ann-actions">
                    <a href="manage-announcements.php?edit=<?php echo (int)$item['id']; ?>" class="btn btn-edit">
                        <i class="fas fa-pen"></i> Edit
                    </a>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="action" value="toggle_publish">
                        <input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>">
                        <button type="submit" class="btn btn-pub">
                            <i class="fas fa-<?php echo $st === 'published' ? 'eye-slash' : 'bullhorn'; ?>"></i>
                            <?php echo $st === 'published' ? 'Unpublish' : 'Publish'; ?>
                        </button>
                    </form>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Remove this announcement permanently?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>">
                        <button type="submit" class="btn btn-del"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
                </div>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toast = document.getElementById('toast');
    if (toast) setTimeout(() => toast.remove(), 4500);

    const desc = document.getElementById('description');
    const descCount = document.getElementById('descCount');
    const statusField = document.getElementById('statusField');
    const submitLabel = document.getElementById('submitLabel');
    const statusRadios = document.querySelectorAll('input[name="status_radio"]');

    function updateDescCount() {
        if (desc && descCount) descCount.textContent = desc.value.length;
    }
    if (desc) {
        desc.addEventListener('input', updateDescCount);
        updateDescCount();
    }

    function syncStatus() {
        const checked = document.querySelector('input[name="status_radio"]:checked');
        if (!checked || !statusField) return;
        statusField.value = checked.value;
        if (submitLabel) {
            const labels = { published: 'Save & publish', draft: 'Save as draft', archived: 'Save archived' };
            submitLabel.textContent = labels[checked.value] || 'Save';
        }
    }
    statusRadios.forEach(r => r.addEventListener('change', syncStatus));
    syncStatus();

    const photoInput = document.getElementById('announcement_image');
    const photoPreview = document.getElementById('photoPreview');
    const photoPreviewWrap = document.getElementById('photoPreviewWrap');
    const photoUploadBox = document.getElementById('photoUploadBox');
    const removeImage = document.getElementById('removeImage');

    if (photoInput && photoPreview && photoPreviewWrap) {
        photoInput.addEventListener('change', function() {
            const file = photoInput.files && photoInput.files[0];
            if (!file) {
                photoPreviewWrap.hidden = true;
                if (photoUploadBox) photoUploadBox.classList.remove('has-preview');
                return;
            }
            if (removeImage) removeImage.checked = false;
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.src = e.target.result;
                photoPreviewWrap.hidden = false;
                if (photoUploadBox) photoUploadBox.classList.add('has-preview');
            };
            reader.readAsDataURL(file);
        });
    }

    if (removeImage) {
        removeImage.addEventListener('change', function() {
            if (removeImage.checked && photoInput) {
                photoInput.value = '';
                if (photoPreviewWrap) photoPreviewWrap.hidden = true;
                if (photoUploadBox) photoUploadBox.classList.remove('has-preview');
            }
        });
    }

    <?php if ($show_form): ?>
    const form = document.getElementById('announcement-form');
    if (form) form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    <?php endif; ?>
});
</script>
</body>
</html>
