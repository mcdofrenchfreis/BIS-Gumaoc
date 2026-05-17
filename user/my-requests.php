<?php
require_once 'auth_check.php';
require_once '../includes/db_connect.php';
require_once '../includes/certificate_request_status.php';

certificate_request_ensure_status_schema($pdo);

$page_title = 'My Certificate Requests - Barangay Gumaoc East';
$current_page = 'my-requests';

$success_message = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'confirm_received') {
    $reqId = (int)($_POST['id'] ?? 0);
    if ($reqId > 0 && !empty($_SESSION['user_id'])) {
        $chk = $pdo->prepare("SELECT status FROM certificate_requests WHERE id = ? AND user_id = ?");
        $chk->execute([$reqId, $_SESSION['user_id']]);
        $cur = $chk->fetchColumn();
        if ($cur === 'released') {
            $pdo->prepare("UPDATE certificate_requests SET status = 'received' WHERE id = ?")->execute([$reqId]);
            $_SESSION['success'] = 'Thank you! Your certificate receipt has been recorded.';
        }
    }
    header('Location: my-requests.php');
    exit;
}

$status_config = certificate_request_user_status_config();
$tracker_steps = certificate_request_tracker_steps();

// Get user's certificate requests
$requests = [];
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT id, certificate_type, full_name, status, submitted_at, notes
            FROM certificate_requests 
            WHERE user_id = ? 
            ORDER BY submitted_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_message = "Error loading requests: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/background.css">
    <link rel="stylesheet" href="css/mobile.css">
    <link rel="stylesheet" href="css/forms-portal.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f7faf7;
            min-height: 100vh;
            padding: 0;
            opacity: 0;
            animation: fadeInPage 0.8s ease-out 0.3s forwards;
        }
        
        @keyframes fadeInPage {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .container {
            max-width: 1200px;
            margin: 90px auto 0;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideUp 0.8s ease-out 0.5s both;
        }
        
        @keyframes slideUp {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .header {
            background: linear-gradient(135deg, #1b5e20, #2e7d32);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .page-nav-link {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .page-nav-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .new-request-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .new-request-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .content {
            padding: 40px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .requests-grid {
            display: grid;
            gap: 20px;
        }

        .request-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .request-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .request-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #495057;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            color: white;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .certificate-info {
            margin-bottom: 20px;
        }

        .certificate-type {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2e7d32;
            margin-bottom: 8px;
        }

        .certificate-name {
            color: #6c757d;
            font-size: 1rem;
        }

        .request-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .detail-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
        }

        .detail-value {
            font-weight: 600;
            color: #495057;
        }

        .notes-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
        }

        .notes-section h4 {
            color: #495057;
            margin-bottom: 10px;
            font-size: 1rem;
        }

        .notes-text {
            color: #6c757d;
            font-style: italic;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #e9ecef;
        }

        .empty-state h3 {
            margin-bottom: 15px;
            color: #495057;
        }

        .empty-state p {
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .btn {
            background: linear-gradient(135deg, #1b5e20, #2e7d32);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
        }

        .alert-success {
            background: #e8f5e9;
            color: #1b5e20;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #c8e6c9;
        }

        .tracker {
            display: flex;
            justify-content: space-between;
            margin: 16px 0 20px;
            position: relative;
            padding: 0 4px;
        }
        .tracker::before {
            content: '';
            position: absolute;
            top: 14px;
            left: 5%;
            right: 5%;
            height: 3px;
            background: #e0e0e0;
            z-index: 0;
        }
        .tracker-step { flex: 1; text-align: center; position: relative; z-index: 1; min-width: 0; }
        .tracker-dot {
            width: 30px; height: 30px; border-radius: 50%; background: #e0e0e0; color: #888;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 6px;
            font-size: 0.75rem; font-weight: 700; border: 2px solid #fff;
        }
        .tracker-step.done .tracker-dot { background: #2e7d32; color: #fff; }
        .tracker-step.active .tracker-dot { background: #1565c0; color: #fff; }
        .tracker-label { font-size: 0.68rem; color: #666; font-weight: 600; line-height: 1.2; }
        .tracker-step.done .tracker-label, .tracker-step.active .tracker-label { color: #333; }

        .confirm-received {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e9ecef;
        }
        .btn-confirm {
            background: #6a1b9a;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .btn-confirm:hover { background: #4a148c; }

        @media (max-width: 768px) {
            .page-nav-link, .new-request-btn {
                position: static;
                display: inline-block;
                margin: 0 0.5rem 0.5rem 0;
            }

            .request-header {
                flex-direction: column;
                align-items: stretch;
            }

            .request-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="user-form-page">

    <?php include 'navbar_component.php'; ?>

    <div class="container">
        <div class="content">
            <?php if ($success_message): ?>
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($requests)): ?>
                <div class="empty-state">
                    <i class="fas fa-file-alt"></i>
                    <h3>No Certificate Requests Yet</h3>
                    <p>You haven't submitted any certificate requests. Start by requesting a certificate below.</p>
                    <a href="certificate-request.php" class="btn">
                        <i class="fas fa-plus"></i> Request Certificate
                    </a>
                </div>
            <?php else: ?>
                <div class="requests-grid">
                    <?php foreach ($requests as $request): ?>
                        <?php 
                        $status = $request['status'];
                        $config = $status_config[$status] ?? ['label' => ucfirst($status), 'color' => '#6c757d', 'icon' => 'info'];
                        $currentStep = certificate_request_tracker_step($status);
                        ?>
                        <div class="request-card">
                            <div class="request-header">
                                <div class="request-title">
                                    <?php echo htmlspecialchars($request['certificate_type']); ?>
                                </div>
                                <div class="status-badge" style="background-color: <?php echo $config['color']; ?>">
                                    <i class="fas fa-<?php echo $config['icon']; ?>"></i>
                                    <?php echo $config['label']; ?>
                                </div>

                            </div>

                            <div class="tracker" aria-label="Certificate progress">
                                <?php foreach ($tracker_steps as $step):
                                    $classes = ['tracker-step'];
                                    if ($step['num'] < $currentStep) { $classes[] = 'done'; }
                                    elseif ($step['num'] === $currentStep) { $classes[] = 'active'; }
                                ?>
                                <div class="<?php echo implode(' ', $classes); ?>">
                                    <div class="tracker-dot">
                                        <?php echo $step['num'] < $currentStep ? '<i class="fas fa-check"></i>' : $step['num']; ?>
                                    </div>
                                    <div class="tracker-label"><?php echo htmlspecialchars($step['label']); ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="certificate-info">
                                <div class="certificate-name"><?php echo htmlspecialchars($request['full_name']); ?></div>
                            </div>
                            
                            <div class="request-details">
                                <div class="detail-item">
                                    <span class="detail-label">Submitted</span>
                                    <span class="detail-value">
                                        <?php echo date('M j, Y \a\t g:i A', strtotime($request['submitted_at'])); ?>
                                    </span>
                                </div>
                                
                                <div class="detail-item">
                                    <span class="detail-label">Processing Time</span>
                                    <span class="detail-value">
                                        <?php
                                        $submitted = new DateTime($request['submitted_at']);
                                        $now = new DateTime();
                                        $diff = $submitted->diff($now);
                                        
                                        if ($diff->days > 0) {
                                            echo $diff->days . ' day' . ($diff->days > 1 ? 's' : '');
                                        } elseif ($diff->h > 0) {
                                            echo $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
                                        } else {
                                            echo $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
                                        }
                                        ?>
                                    </span>
                                </div>
                                
                                <div class="detail-item">
                                    <span class="detail-label">Status</span>
                                    <span class="detail-value">
                                        <?php echo ucfirst($request['status']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <?php if (!empty($request['notes'])): ?>
                            <div class="notes-section">
                                <h4><i class="fas fa-sticky-note"></i> Administrative Notes</h4>
                                <div class="notes-text"><?php echo htmlspecialchars($request['notes']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($status === 'released'): ?>
                            <div class="confirm-received">
                                <p style="color:#555;font-size:0.9rem;margin-bottom:10px;">
                                    Pick up your certificate at the barangay hall, then confirm receipt below.
                                </p>
                                <form method="post">
                                    <input type="hidden" name="action" value="confirm_received">
                                    <input type="hidden" name="id" value="<?php echo (int)$request['id']; ?>">
                                    <button type="submit" class="btn-confirm">
                                        <i class="fas fa-check-double"></i> I have received my certificate
                                    </button>
                                </form>
                            </div>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Page loader functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-refresh page every 5 minutes to check for status updates
            setInterval(function() {
                window.location.reload();
            }, 300000); // 5 minutes
            
            // Add notification if there are ready certificates
            const readyRequests = document.querySelectorAll('.status-badge[style*="#2e7d32"]').length;
            
            if (readyRequests > 0) {
                // Show browser notification if permitted
                if ("Notification" in window && Notification.permission === "granted") {
                    new Notification("Certificate Ready!", {
                        body: `You have ${readyRequests} certificate(s) ready for pickup at the Barangay Hall.`,
                        icon: "/favicon.ico"
                    });
                }
            }
        });

        // Request notification permission
        if ("Notification" in window && Notification.permission === "default") {
            Notification.requestPermission();
        }
    </script>
</body>
</html>