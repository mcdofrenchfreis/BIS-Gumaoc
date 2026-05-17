<?php
require_once 'auth_check.php';
require_once '../includes/db_connect.php';
require_once '../includes/business_application_status.php';

business_application_ensure_status_schema($pdo);

$page_title = 'Track Business Applications';
$current_page = 'my-business-applications';

$success_message = $_SESSION['success'] ?? '';
$error_message = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$status_config = business_application_user_status_config();

$applications = [];
if (!empty($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT id, reference_no, business_name, business_type, business_address,
                   owner_name, contact_number, status, submitted_at, application_date
            FROM business_applications
            WHERE user_id = ?
            ORDER BY submitted_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_message = $error_message ?: 'Unable to load your applications. Please try again later.';
    }
}

function business_reference_label(array $app): string
{
    if (!empty($app['reference_no'])) {
        return (string) $app['reference_no'];
    }
    return 'BA-' . str_pad((string) $app['id'], 4, '0', STR_PAD_LEFT);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo htmlspecialchars($page_title); ?> - Barangay Gumaoc East</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/background.css">
    <link rel="stylesheet" href="css/mobile.css">
    <link rel="stylesheet" href="css/forms-portal.css">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f7faf7;
            min-height: 100vh;
        }
        .container {
            max-width: 960px;
            margin: 90px auto 40px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1b5e20, #2e7d32);
            color: #fff;
            padding: 36px 32px;
            text-align: center;
        }
        .header h1 { font-size: 1.85rem; margin-bottom: 8px; }
        .header p { opacity: 0.92; font-size: 1rem; }
        .content { padding: 32px; }
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-success { background: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; }
        .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .top-actions {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 24px;
        }
        .btn {
            background: linear-gradient(135deg, #1b5e20, #2e7d32);
            color: #fff;
            padding: 12px 22px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(46, 125, 50, 0.25);
        }
        .apps-grid { display: grid; gap: 22px; }
        .app-card {
            border: 1px solid #e8f0e8;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
        }
        .app-card-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }
        .app-title { font-size: 1.2rem; font-weight: 700; color: #1a1a1a; margin: 0 0 4px; }
        .app-ref { font-size: 0.85rem; color: #666; }
        .status-badge {
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #fff;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        .tracker {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin: 8px 0 20px;
            position: relative;
            padding: 0 4px;
        }
        .tracker::before {
            content: '';
            position: absolute;
            top: 16px;
            left: 5%;
            right: 5%;
            height: 3px;
            background: #e0e0e0;
            z-index: 0;
        }
        .tracker-step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
            min-width: 0;
        }
        .tracker-dot {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #888;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-size: 0.8rem;
            font-weight: 700;
            border: 3px solid #fff;
            box-shadow: 0 0 0 1px #e0e0e0;
        }
        .tracker-step.done .tracker-dot {
            background: #2e7d32;
            color: #fff;
            box-shadow: 0 0 0 1px #2e7d32;
        }
        .tracker-step.active .tracker-dot {
            background: #1565c0;
            color: #fff;
            box-shadow: 0 0 0 1px #1565c0;
        }
        .tracker-step.rejected.done .tracker-dot,
        .tracker-step.rejected.active .tracker-dot {
            background: #c62828;
            box-shadow: 0 0 0 1px #c62828;
        }
        .tracker-label {
            font-size: 0.72rem;
            color: #666;
            font-weight: 600;
            line-height: 1.3;
        }
        .tracker-step.done .tracker-label,
        .tracker-step.active .tracker-label { color: #333; }
        .status-message {
            background: #f7faf7;
            border: 1px solid #e8f5e9;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 0.9rem;
            color: #444;
            margin-bottom: 16px;
        }
        .app-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
        }
        .detail-item { display: flex; flex-direction: column; gap: 3px; }
        .detail-label { font-size: 0.8rem; color: #888; font-weight: 500; }
        .detail-value { font-weight: 600; color: #333; font-size: 0.95rem; }
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }
        .empty-state i { font-size: 3.5rem; color: #c8e6c9; margin-bottom: 16px; }
        .empty-state h3 { color: #1b5e20; margin-bottom: 10px; }
        @media (max-width: 600px) {
            .content { padding: 20px; }
            .tracker-label { font-size: 0.65rem; }
            .tracker-dot { width: 28px; height: 28px; font-size: 0.7rem; }
        }
    </style>
</head>
<body class="user-form-page">
<?php include 'navbar_component.php'; ?>

<div class="container">
    <div class="header">
        <h1><i class="fas fa-building"></i> Business Application Tracker</h1>
        <p>Follow the progress of your permit and clearance applications</p>
    </div>

    <div class="content">
        <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
        </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($applications)): ?>
        <div class="top-actions">
            <a href="business-application.php" class="btn"><i class="fas fa-plus"></i> New application</a>
        </div>
        <?php endif; ?>

        <?php if (empty($applications)): ?>
        <div class="empty-state">
            <i class="fas fa-clipboard-list"></i>
            <h3>No applications yet</h3>
            <p>Submit a business permit application, then track its status here.</p>
            <a href="business-application.php" class="btn" style="margin-top:16px;">
                <i class="fas fa-file-signature"></i> Apply now
            </a>
        </div>
        <?php else: ?>
        <div class="apps-grid">
            <?php foreach ($applications as $app):
                $status = $app['status'] ?? 'pending';
                $config = $status_config[$status] ?? $status_config['pending'];
                $steps = business_application_tracker_steps($status);
                $currentStep = business_application_tracker_step($status);
                $isRejected = ($status === 'rejected');
            ?>
            <article class="app-card">
                <div class="app-card-head">
                    <div>
                        <h2 class="app-title"><?php echo htmlspecialchars($app['business_name']); ?></h2>
                        <p class="app-ref">Ref: <?php echo htmlspecialchars(business_reference_label($app)); ?></p>
                    </div>
                    <span class="status-badge" style="background:<?php echo $config['color']; ?>">
                        <i class="fas fa-<?php echo $config['icon']; ?>"></i>
                        <?php echo htmlspecialchars($config['label']); ?>
                    </span>
                </div>

                <div class="tracker" aria-label="Application progress">
                    <?php foreach ($steps as $step):
                        $classes = ['tracker-step'];
                        if ($step['num'] < $currentStep) {
                            $classes[] = 'done';
                        } elseif ($step['num'] === $currentStep) {
                            $classes[] = 'active';
                            if ($isRejected && $step['num'] === 3) {
                                $classes[] = 'rejected';
                            }
                        }
                        if ($isRejected && $step['num'] === 3 && $step['num'] <= $currentStep) {
                            $classes[] = 'rejected';
                        }
                    ?>
                    <div class="<?php echo implode(' ', $classes); ?>">
                        <div class="tracker-dot">
                            <?php if ($step['num'] < $currentStep): ?>
                                <i class="fas fa-check"></i>
                            <?php else: ?>
                                <?php echo $step['num']; ?>
                            <?php endif; ?>
                        </div>
                        <div class="tracker-label"><?php echo htmlspecialchars($step['label']); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <p class="status-message">
                    <i class="fas fa-info-circle" style="color:#2e7d32"></i>
                    <?php echo htmlspecialchars($config['message']); ?>
                </p>

                <div class="app-details">
                    <div class="detail-item">
                        <span class="detail-label">Business type</span>
                        <span class="detail-value"><?php echo htmlspecialchars($app['business_type']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Owner</span>
                        <span class="detail-value"><?php echo htmlspecialchars($app['owner_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Submitted</span>
                        <span class="detail-value"><?php echo date('M j, Y g:i A', strtotime($app['submitted_at'])); ?></span>
                    </div>
                    <?php if (!empty($app['contact_number'])): ?>
                    <div class="detail-item">
                        <span class="detail-label">Contact</span>
                        <span class="detail-value"><?php echo htmlspecialchars($app['contact_number']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    setInterval(function() { window.location.reload(); }, 300000);
});
</script>
</body>
</html>
