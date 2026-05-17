<?php
/**
 * Resident profile — user portal only (uses user navbar).
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/includes/portal_urls.php';

$current_page = 'profile';
$page_title = 'My Profile';
$base_path = '../';
$settings_href = user_portal_url('settings.php');
$services_href = user_portal_url('dashboard.php');

$user_id = $_SESSION['user_id'];
$error = '';

try {
    $stmt = $pdo->prepare('SELECT * FROM residents WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found');
    }
} catch (Exception $e) {
    $error = 'Error loading profile data.';
    $user = is_array($user ?? null) ? $user : [];
}

try {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'ready' THEN 1 ELSE 0 END) as ready
         FROM certificate_requests WHERE user_id = ?"
    );
    $stmt->execute([$user_id]);
    $cert_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare(
        "SELECT COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved
         FROM business_applications WHERE user_id = ?"
    );
    $stmt->execute([$user_id]);
    $business_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    $recent_activities = [];

    $stmt = $pdo->prepare(
        "SELECT 'certificate' as type, certificate_type as item, status, created_at as date
         FROM certificate_requests WHERE user_id = ?
         ORDER BY created_at DESC LIMIT 5"
    );
    $stmt->execute([$user_id]);
    $recent_activities = array_merge($recent_activities, $stmt->fetchAll(PDO::FETCH_ASSOC));

    $stmt = $pdo->prepare(
        "SELECT 'business' as type, business_name as item, status, submitted_at as date
         FROM business_applications WHERE user_id = ?
         ORDER BY submitted_at DESC LIMIT 5"
    );
    $stmt->execute([$user_id]);
    $recent_activities = array_merge($recent_activities, $stmt->fetchAll(PDO::FETCH_ASSOC));

    usort($recent_activities, function ($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    $recent_activities = array_slice($recent_activities, 0, 5);
} catch (Exception $e) {
    $cert_stats = ['total' => 0, 'pending' => 0, 'ready' => 0];
    $business_stats = ['total' => 0, 'pending' => 0, 'approved' => 0];
    $recent_activities = [];
}

$has_rfid = !empty($user['rfid_code']) || !empty($user['rfid']);
$full_name = trim(
    ($user['first_name'] ?? '') . ' ' .
    (!empty($user['middle_name']) ? $user['middle_name'] . ' ' : '') .
    ($user['last_name'] ?? '')
);
$initials = strtoupper(
    substr($user['first_name'] ?? 'U', 0, 1) .
    substr($user['last_name'] ?? '', 0, 1)
);
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
        .dashboard-content {
            padding-top: calc(var(--user-nav-total, 70px) + 1rem);
            min-height: 100vh;
        }

        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 40px;
        }

        .profile-header {
            background: linear-gradient(135deg, #2e7d32, #43a047);
            color: white;
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 40px rgba(46, 125, 50, 0.25);
        }

        .profile-avatar {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .avatar-circle {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .profile-info h1 {
            margin: 0 0 0.5rem;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .profile-email {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .profile-actions .btn-settings {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .profile-actions .btn-settings:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-bg, #fff);
            padding: 1.5rem;
            border-radius: 16px;
            border: 1px solid var(--card-border, #e8f5e9);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            font-size: 2rem;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #2e7d32, #43a047);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-info h3 {
            margin: 0 0 0.3rem;
            font-size: 2rem;
            font-weight: 700;
            color: #2e7d32;
        }

        .stat-info p {
            margin: 0 0 0.5rem;
            color: #666;
            font-weight: 500;
        }

        .stat-breakdown {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.85rem;
        }

        .stat-pending { color: #f39c12; }
        .stat-ready, .stat-approved { color: #27ae60; }
        .stat-date { color: #666; }

        .info-section,
        .activities-section {
            background: var(--card-bg, #fff);
            border: 1px solid var(--card-border, #e8f5e9);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .section-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f8f2;
        }

        .section-header h2 {
            margin: 0;
            color: #2e7d32;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .info-item label {
            font-weight: 600;
            color: #666;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 0.35rem;
        }

        .info-item span {
            font-size: 1rem;
            color: #2c3e50;
            font-weight: 500;
        }

        .status-active { color: #27ae60 !important; font-weight: 600 !important; }
        .status-inactive { color: #e74c3c !important; font-weight: 600 !important; }

        .activities-list { display: flex; flex-direction: column; gap: 1rem; }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .activity-icon {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .activity-info { flex: 1; }
        .activity-info h4 { margin: 0 0 0.25rem; font-size: 1.05rem; }
        .activity-info p { margin: 0; color: #666; font-size: 0.9rem; }
        .activity-status { text-align: right; }

        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-ready, .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }

        .activity-date { display: block; font-size: 0.8rem; color: #999; margin-top: 0.25rem; }

        .no-activities { text-align: center; padding: 2rem 1rem; }
        .no-activities-icon { font-size: 3rem; margin-bottom: 0.75rem; }

        .btn-browse {
            display: inline-block;
            margin-top: 1rem;
            background: linear-gradient(135deg, #2e7d32, #43a047);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
        }

        .alert-error {
            background: #fdecea;
            color: #c62828;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                gap: 1.25rem;
                text-align: center;
            }

            .profile-avatar { flex-direction: column; }

            .stats-grid,
            .info-grid { grid-template-columns: 1fr; }

            .activity-item {
                flex-direction: column;
                text-align: center;
            }

            .activity-status { text-align: center; }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/navbar_component.php'; ?>

    <div class="dashboard-content">
    <div class="profile-container">
        <?php if ($error): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif (!empty($user)): ?>

        <div class="profile-header">
            <div class="profile-avatar">
                <div class="avatar-circle"><?php echo htmlspecialchars($initials); ?></div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))); ?></h1>
                    <p class="profile-email"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                </div>
            </div>
            <div class="profile-actions">
                <a href="<?php echo htmlspecialchars($settings_href); ?>" class="btn-settings">
                    <i class="fas fa-cog"></i> Account Settings
                </a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-info">
                    <h3><?php echo (int) ($cert_stats['total'] ?? 0); ?></h3>
                    <p>Certificate Requests</p>
                    <div class="stat-breakdown">
                        <span class="stat-pending"><?php echo (int) ($cert_stats['pending'] ?? 0); ?> Pending</span>
                        <span class="stat-ready"><?php echo (int) ($cert_stats['ready'] ?? 0); ?> Ready</span>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🏢</div>
                <div class="stat-info">
                    <h3><?php echo (int) ($business_stats['total'] ?? 0); ?></h3>
                    <p>Business Applications</p>
                    <div class="stat-breakdown">
                        <span class="stat-pending"><?php echo (int) ($business_stats['pending'] ?? 0); ?> Pending</span>
                        <span class="stat-approved"><?php echo (int) ($business_stats['approved'] ?? 0); ?> Approved</span>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-info">
                    <h3><?php echo !empty($user['created_at']) ? date('Y', strtotime($user['created_at'])) : date('Y'); ?></h3>
                    <p>Member Since</p>
                    <div class="stat-breakdown">
                        <span class="stat-date">
                            <?php echo !empty($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : '—'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="section-header"><h2>👤 Personal Information</h2></div>
            <div class="info-grid">
                <div class="info-item">
                    <label>Full Name</label>
                    <span><?php echo htmlspecialchars($full_name); ?></span>
                </div>
                <div class="info-item">
                    <label>Email Address</label>
                    <span><?php echo htmlspecialchars($user['email'] ?? 'Not provided'); ?></span>
                </div>
                <div class="info-item">
                    <label>Phone Number</label>
                    <span><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></span>
                </div>
                <div class="info-item">
                    <label>Address</label>
                    <span><?php echo htmlspecialchars($user['address'] ?? 'Not provided'); ?></span>
                </div>
                <div class="info-item">
                    <label>RFID Status</label>
                    <span class="<?php echo $has_rfid ? 'status-active' : 'status-inactive'; ?>">
                        <?php echo $has_rfid ? '✅ Registered' : '❌ Not Registered'; ?>
                    </span>
                </div>
                <div class="info-item">
                    <label>Account Status</label>
                    <span class="status-active">✅ Active</span>
                </div>
            </div>
        </div>

        <div class="activities-section">
            <div class="section-header"><h2>📊 Recent Activities</h2></div>
            <?php if (!empty($recent_activities)): ?>
            <div class="activities-list">
                <?php foreach ($recent_activities as $activity): ?>
                <div class="activity-item">
                    <div class="activity-icon"><?php echo $activity['type'] === 'certificate' ? '📋' : '🏢'; ?></div>
                    <div class="activity-info">
                        <h4><?php echo htmlspecialchars($activity['item']); ?></h4>
                        <p><?php echo ucfirst($activity['type']); ?> Application</p>
                    </div>
                    <div class="activity-status">
                        <span class="status-badge status-<?php echo htmlspecialchars($activity['status']); ?>">
                            <?php echo ucfirst($activity['status']); ?>
                        </span>
                        <span class="activity-date"><?php echo date('M j, Y', strtotime($activity['date'])); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-activities">
                <div class="no-activities-icon">📭</div>
                <h3>No Recent Activities</h3>
                <p>You haven't submitted any applications yet.</p>
                <a href="<?php echo htmlspecialchars($services_href); ?>" class="btn-browse">Browse Services</a>
            </div>
            <?php endif; ?>
        </div>

        <?php endif; ?>
    </div>
    </div>
</body>
</html>
