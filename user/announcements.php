<?php
require_once 'auth_check.php';
require_once '../includes/db_connect.php';
require_once '../includes/announcement_image.php';

announcement_ensure_image_column($pdo);

$page_title = 'Announcements';
$current_page = 'announcements';
$base_path = '../';

$announcements = [];
try {
    $stmt = $pdo->query("
        SELECT id, title, description, image, badge_text, badge_type, date, status, is_priority, created_at
        FROM updates
        WHERE LOWER(TRIM(status)) NOT IN ('draft', 'archived')
        ORDER BY is_priority DESC, display_order ASC, created_at DESC
    ");
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = 'Unable to load announcements. Please try again later.';
}

$badge_styles = [
    'important' => ['class' => 'badge-important', 'icon' => 'exclamation-circle'],
    'new' => ['class' => 'badge-new', 'icon' => 'star'],
    'community' => ['class' => 'badge-community', 'icon' => 'users'],
    'info' => ['class' => 'badge-info', 'icon' => 'info-circle'],
];

$display_name = $user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo htmlspecialchars($page_title); ?> - Barangay Gumaoc East</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/background.css">
    <link rel="stylesheet" href="css/mobile.css">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f7faf7;
            min-height: 100vh;
            line-height: 1.6;
        }
        .main-content {
            margin-top: 70px;
            padding: 30px 20px 50px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .page-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .page-title {
            color: #1b5e20;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .page-subtitle {
            color: #555;
            font-size: 1rem;
        }
        .announcement-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 16px;
            border: 1px solid #e8f5e9;
            box-shadow: 0 8px 24px rgba(27, 94, 32, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .announcement-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(27, 94, 32, 0.1);
        }
        .announcement-card.priority {
            border-left: 4px solid #f59e0b;
        }
        .card-image {
            width: 100%;
            max-height: 280px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 16px;
            display: block;
            border: 1px solid #e8f5e9;
        }
        .card-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0;
            flex: 1 1 100%;
        }
        @media (min-width: 600px) {
            .card-title { flex: 1 1 auto; }
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-important { background: #fee2e2; color: #b91c1c; }
        .badge-new { background: #dbeafe; color: #1d4ed8; }
        .badge-community { background: #fef3c7; color: #b45309; }
        .badge-info { background: #e8f5e9; color: #1b5e20; }
        .priority-tag {
            background: #fff7ed;
            color: #c2410c;
            border: 1px solid #fed7aa;
        }
        .card-date {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 12px;
        }
        .card-date i { color: #2e7d32; margin-right: 6px; }
        .card-body {
            color: #444;
            font-size: 0.95rem;
            white-space: pre-wrap;
        }
        .empty-state {
            text-align: center;
            padding: 60px 24px;
            background: #fff;
            border-radius: 16px;
            border: 1px dashed #c8e6c9;
        }
        .empty-state i {
            font-size: 3rem;
            color: #a5d6a7;
            margin-bottom: 16px;
        }
        .empty-state h3 {
            color: #1b5e20;
            margin-bottom: 8px;
        }
        .empty-state p { color: #666; }
        .alert-error {
            background: #ffebee;
            color: #c62828;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #ffcdd2;
        }
    </style>
</head>
<body>
<?php include 'navbar_component.php'; ?>

<main class="main-content">
    <div class="container">
        <header class="page-header">
            <h1 class="page-title"><i class="fas fa-bullhorn"></i> Announcements</h1>
            <p class="page-subtitle">Official updates and news from Barangay Gumaoc East</p>
        </header>

        <?php if (!empty($error_message)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (empty($announcements) && empty($error_message)): ?>
        <div class="empty-state">
            <i class="fas fa-bullhorn"></i>
            <h3>No announcements yet</h3>
            <p>Check back later for barangay news and important updates.</p>
        </div>
        <?php else: ?>
            <?php foreach ($announcements as $item):
                $type = $item['badge_type'] ?? 'info';
                $style = $badge_styles[$type] ?? $badge_styles['info'];
            ?>
            <article class="announcement-card <?php echo $item['is_priority'] ? 'priority' : ''; ?>">
                <?php if (!empty($item['image'])): ?>
                <img class="card-image"
                     src="<?php echo htmlspecialchars(announcement_image_url($item['image'], '../')); ?>"
                     alt="<?php echo htmlspecialchars($item['title']); ?>">
                <?php endif; ?>
                <div class="card-header">
                    <h2 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h2>
                    <span class="badge <?php echo htmlspecialchars($style['class']); ?>">
                        <i class="fas fa-<?php echo htmlspecialchars($style['icon']); ?>"></i>
                        <?php echo htmlspecialchars($item['badge_text']); ?>
                    </span>
                    <?php if ($item['is_priority']): ?>
                    <span class="badge priority-tag"><i class="fas fa-thumbtack"></i> Priority</span>
                    <?php endif; ?>
                </div>
                <p class="card-date">
                    <i class="fas fa-calendar-alt"></i>
                    <?php echo htmlspecialchars($item['date']); ?>
                </p>
                <div class="card-body"><?php echo nl2br(htmlspecialchars($item['description'])); ?></div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
