<?php
$base_path = '../';
$page_title = 'Admin Dashboard';
require_once 'auth_check.php';

// Add admin stylesheet and Font Awesome
$additional_css = [
    '<link rel="stylesheet" href="' . $base_path . 'assets/css/admin.css">',
    '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">'
];

// Start the admin wrapper
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Barangay Gumaoc East</title>
    <?php
    if (isset($additional_css)) {
        foreach ($additional_css as $css) {
            echo $css;
        }
    }
    ?>
</head>
<body>
    <div class="admin-wrapper">
        <?php include '../includes/admin_navigation.php'; ?>
        
        <main class="admin-main">
            <header class="content-header">
                <h2><?php echo htmlspecialchars($page_title); ?></h2>
                <?php if (isset($header_actions)): ?>
                <div class="header-actions">
                    <?php echo $header_actions; ?>
                </div>
                <?php endif; ?>
            </header>

            <div class="content-wrapper">
                <div class="dashboard-stats">
                    <?php
                    // Get statistics
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM rfid_cards");
                    $rfid_count = $stmt->fetch()['total'];

                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM incident_reports WHERE status = 'pending'");
                    $pending_reports = $stmt->fetch()['total'];

                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM page_content");
                    $content_count = $stmt->fetch()['total'];
                    ?>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="stat-details">
                            <h3>RFID Cards</h3>
                            <p><?php echo $rfid_count; ?> registered</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Pending Reports</h3>
                            <p><?php echo $pending_reports; ?> reports</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Page Content</h3>
                            <p><?php echo $content_count; ?> sections</p>
                        </div>
                    </div>
                </div>

                <div class="recent-activity">
                    <h3>Recent Activity</h3>
                    <div class="activity-list">
                        <?php
                        $stmt = $pdo->query("
                            SELECT 'report' as type, incident_type as title, created_at as date
                            FROM incident_reports
                            UNION ALL
                            SELECT 'content' as type, CONCAT(page_name, ' - ', section_name) as title, last_updated as date
                            FROM page_content
                            ORDER BY date DESC
                            LIMIT 5
                        ");
                        $activities = $stmt->fetchAll();
                        
                        foreach ($activities as $activity):
                        ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-<?php echo $activity['type'] === 'report' ? 'clipboard-list' : 'file-alt'; ?>"></i>
                            </div>
                            <div class="activity-details">
                                <p><?php echo htmlspecialchars($activity['title']); ?></p>
                                <small><?php echo date('M j, Y g:i A', strtotime($activity['date'])); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="<?php echo $base_path; ?>assets/js/admin.js"></script>
</body>
</html> 