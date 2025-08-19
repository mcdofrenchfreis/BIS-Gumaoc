<?php
session_start();
include '../includes/db_connect.php';
include '../includes/AdminLogger.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$logger = new AdminLogger($pdo);

// Handle status updates
if ($_POST['action'] ?? '' === 'update_status' && isset($_POST['id'], $_POST['status'])) {
    $id = (int)$_POST['id'];
    $new_status = $_POST['status'];
    $allowed_statuses = ['pending', 'processing', 'ready', 'released'];
    
    if (in_array($new_status, $allowed_statuses)) {
        // Get current status first
        $stmt = $pdo->prepare("SELECT status, certificate_type, full_name FROM certificate_requests WHERE id = ?");
        $stmt->execute([$id]);
        $current_data = $stmt->fetch();
        $current_status = $current_data['status'];
        
        // Check status progression rules
        $status_valid = false;
        $error_message = '';
        
        if ($current_status === 'pending') {
            if (in_array($new_status, ['processing', 'ready', 'released'])) {
                $status_valid = true;
            } else {
                $error_message = "From pending status, you can only move to processing, ready, or released.";
            }
        } elseif ($current_status === 'processing') {
            if (in_array($new_status, ['ready', 'released'])) {
                $status_valid = true;
            } else {
                $error_message = "From processing status, you can only move to ready or released.";
            }
        } elseif ($current_status === 'ready') {
            if ($new_status === 'released') {
                $status_valid = true;
            } else {
                $error_message = "From ready status, you can only move to released.";
            }
        } elseif ($current_status === 'released') {
            $error_message = "Released certificates cannot be changed. Status is locked.";
        }
        
        if ($status_valid) {
            $stmt = $pdo->prepare("UPDATE certificate_requests SET status = ? WHERE id = ?");
            $result = $stmt->execute([$new_status, $id]);
            
            if ($result) {
                // Log the status update
                $logger->logStatusUpdate(
                    'certificate_request',
                    $id,
                    $current_status,
                    $new_status,
                    [
                        'certificate_type' => $current_data['certificate_type'],
                        'applicant_name' => $current_data['full_name']
                    ]
                );
                
                $_SESSION['toast_message'] = "The status of Certificate Request ID #$id has been successfully updated to " . ucfirst($new_status);
                $_SESSION['toast_type'] = 'success';
            } else {
                $_SESSION['toast_message'] = "Failed to update status for Certificate Request ID #$id";
                $_SESSION['toast_type'] = 'error';
            }
        } else {
            $_SESSION['toast_message'] = $error_message;
            $_SESSION['toast_type'] = 'error';
        }
    } else {
        $_SESSION['toast_message'] = "Invalid status selected for Certificate Request ID #$id";
        $_SESSION['toast_type'] = 'error';
    }
    
    header('Location: view-certificate-requests.php');
    exit;
}

// Log page view
$logger->log('page_view', 'admin_panel', 'Viewed certificate requests admin page');

// Get filter and search parameters
$status_filter = $_GET['status'] ?? '';
$cert_type = $_GET['cert_type'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = [];
$params = [];

if ($status_filter && in_array($status_filter, ['pending', 'processing', 'ready', 'released'])) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if ($cert_type) {
    $where_conditions[] = "certificate_type = ?";
    $params[] = $cert_type;
}

if ($search) {
    $where_conditions[] = "(full_name LIKE ? OR certificate_type LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count
$count_sql = "SELECT COUNT(*) FROM certificate_requests $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Get records
$sql = "SELECT * FROM certificate_requests $where_clause ORDER BY submitted_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Get certificate types for filter
$cert_types = $pdo->query("SELECT DISTINCT certificate_type FROM certificate_requests ORDER BY certificate_type")->fetchAll(PDO::FETCH_COLUMN);

// Check if we should show toast
$show_toast = isset($_SESSION['toast_message']);
$toast_message = $_SESSION['toast_message'] ?? '';
$toast_type = $_SESSION['toast_type'] ?? 'success';

// Clear session variables after getting them
if (isset($_SESSION['toast_message'])) {
    unset($_SESSION['toast_message']);
    unset($_SESSION['toast_type']);
}

// Function to get print URL based on certificate type
function getPrintUrl($certificate_type, $id) {
    // Normalize certificate type for better matching
    $certificate_type = strtoupper(trim($certificate_type));
    
    $print_urls = [
        'BRGY. INDIGENCY' => '../pages/print-indigency.php',
        'INDIGENCY' => '../pages/print-indigency.php',
        'BRGY. CLEARANCE' => '../pages/print-barangay-clearance.php',
        'BARANGAY CLEARANCE' => '../pages/print-barangay-clearance.php',
        'CLEARANCE' => '../pages/print-barangay-clearance.php',
        'CERTIFICATION OF RESIDENCY' => '../pages/print-residency.php',
        'RESIDENCY' => '../pages/print-residency.php',
        'PROOF OF RESIDENCY' => '../pages/print-residency.php',
        'TRICYCLE PERMIT' => '../pages/print-tricycle-permit.php',
        'CEDULA' => '../pages/print-tricycle-permit.php'
    ];
    
    return isset($print_urls[$certificate_type]) ? $print_urls[$certificate_type] . '?id=' . $id : null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Requests - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        /* Toast Notification Styles */
        .toast-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(5px);
            z-index: 999;
            opacity: 0;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .toast-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }

        .toast {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.8);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            z-index: 1000;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            min-width: 400px;
            max-width: 600px;
            width: 90%;
        }

        .toast.show {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }

        .toast-content {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 24px 28px;
        }

        .toast-success {
            border-left: 5px solid #28a745;
            box-shadow: 0 20px 60px rgba(40, 167, 69, 0.2);
        }

        .toast-error {
            border-left: 5px solid #dc3545;
            box-shadow: 0 20px 60px rgba(220, 53, 69, 0.2);
        }

        .toast-icon {
            font-size: 24px;
            flex-shrink: 0;
        }

        .toast-message {
            flex: 1;
            font-weight: 500;
            color: #333;
            line-height: 1.5;
            font-size: 16px;
        }

        .toast-close {
            background: none;
            border: none;
            font-size: 24px;
            color: #999;
            cursor: pointer;
            padding: 4px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .toast-close:hover {
            background: rgba(0, 0, 0, 0.1);
            color: #666;
            transform: scale(1.1);
        }

        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-controls {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .admin-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .admin-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th, .admin-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .admin-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2e7d32;
        }
        
        .admin-table tbody tr {
            transition: all 0.3s ease;
        }
        
        .admin-table tbody tr:hover {
            background: rgba(40, 167, 69, 0.05);
        }
        
        .view-form-btn {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            text-decoration: none;
            margin-bottom: 0.3rem;
        }
        
        .view-form-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
            background: linear-gradient(135deg, #45a049, #4CAF50);
        }

        .print-cert-btn {
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            text-decoration: none;
        }

        .print-cert-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
            background: linear-gradient(135deg, #f57c00, #ff9800);
            color: white;
        }
        
        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #0066cc; }
        .status-ready { background: #d4edda; color: #155724; }
        .status-released { background: #e2e3e5; color: #383d41; }
        
        .cert-type {
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .action-select {
            padding: 0.4rem 0.6rem;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 0.85rem;
            background: white;
            color: #495057;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 120px;
        }

        .action-select:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
        }

        .action-select:disabled {
            background: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
            border-color: #dee2e6;
        }

        .status-locked {
            background: #e9ecef;
            color: #6c757d;
            border: 2px solid #dee2e6;
            position: relative;
        }

        .status-locked::after {
            content: '🔒';
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 12px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .pagination a, .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #2e7d32;
        }
        
        .pagination .current {
            background: #4caf50;
            color: white;
            border-color: #4caf50;
        }

        .pagination a:hover {
            background: #e8f5e8;
            border-color: #4CAF50;
        }
        
        .admin-btn {
            display: inline-block;
            padding: 0.6rem 1.2rem;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .admin-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .search-form {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
            width: 100%;
        }

        .search-form select,
        .search-form input {
            padding: 0.5rem;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .search-form input[type="text"] {
            flex: 1;
            min-width: 250px;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            align-items: stretch;
            min-width: 140px;
        }

        .action-buttons .view-form-btn,
        .action-buttons .print-cert-btn {
            width: 100%;
            justify-content: center;
            text-align: center;
            white-space: nowrap;
        }

        /* Single button styling when print is not available */
        .action-buttons:has(.view-form-btn:only-child) .view-form-btn {
            margin-bottom: 0;
        }

        /* Visual indicator for status requirements */
        .status-processing .print-available {
            position: relative;
        }

        .status-processing .print-available::after {
            content: "🖨️";
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 12px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .admin-container {
                padding: 1rem;
            }
            
            .admin-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .search-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-form input[type="text"] {
                min-width: 100%;
            }
            
            .admin-table th,
            .admin-table td {
                padding: 0.8rem 0.5rem;
                font-size: 0.85rem;
            }

            .toast {
                min-width: 320px;
                max-width: 90%;
                margin: 0 20px;
            }
            
            .toast-content {
                padding: 20px 24px;
            }
            
            .toast-message {
                font-size: 14px;
            }
            
            .toast-icon {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Toast Notification -->
    <?php if ($show_toast): ?>
    <div class="toast-overlay" id="toastOverlay">
        <div class="toast toast-<?php echo $toast_type; ?>" id="toast">
            <div class="toast-content">
                <span class="toast-icon">
                    <?php echo $toast_type === 'success' ? '✅' : '❌'; ?>
                </span>
                <span class="toast-message">
                    <?php echo htmlspecialchars($toast_message); ?>
                </span>
                <button class="toast-close" onclick="hideToast()">×</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="admin-container">
        <div class="admin-header">
            <div>
                <h1>📄 Certificate Requests</h1>
                <p>Total: <?php echo $total_records; ?> requests</p>
            </div>
            <a href="dashboard.php" class="admin-btn">← Back to Dashboard</a>
        </div>
        
        <div class="admin-controls">
            <form method="GET" class="search-form">
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="ready" <?php echo $status_filter === 'ready' ? 'selected' : ''; ?>>Ready</option>
                    <option value="released" <?php echo $status_filter === 'released' ? 'selected' : ''; ?>>Released</option>
                </select>
                
                <select name="cert_type" onchange="this.form.submit()">
                    <option value="">All Certificate Types</option>
                    <?php foreach ($cert_types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $cert_type === $type ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name...">
                <button type="submit" class="admin-btn">🔍 Search</button>
                <a href="view-certificate-requests.php" class="admin-btn">🔄 Clear</a>
            </form>
        </div>
        
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Certificate Type</th>
                        <th>Applicant Name</th>
                        <th>Purpose</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>View Form</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                    <tr>
                        <td><strong>#<?php echo $req['id']; ?></strong></td>
                        <td>
                            <span class="cert-type">
                                <?php echo htmlspecialchars($req['certificate_type']); ?>
                            </span>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($req['full_name']); ?></strong>
                        </td>
                        <td>
                            <?php echo htmlspecialchars(substr($req['purpose'], 0, 50)); ?>
                            <?php if (strlen($req['purpose']) > 50): ?>...<?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $req['status']; ?> <?php echo $req['status'] === 'processing' ? 'print-available' : ''; ?>">
                                <?php echo ucfirst($req['status']); ?>
                                <?php if ($req['status'] === 'released'): ?>
                                    🔒
                                <?php elseif ($req['status'] === 'processing'): ?>
                                    🖨️
                                <?php endif; ?>
                            </span>
                        </td>
                        <td>
                            <div><?php echo date('M j, Y', strtotime($req['submitted_at'])); ?></div>
                            <small style="color: #666;"><?php echo date('g:i A', strtotime($req['submitted_at'])); ?></small>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button onclick="viewFormDetails(<?php echo $req['id']; ?>)" class="view-form-btn">
                                    👁️ View Form
                                </button>
                                <?php 
                                // Only show print button when status is "processing"
                                if ($req['status'] === 'processing') {
                                    $print_url = getPrintUrl($req['certificate_type'], $req['id']);
                                    if ($print_url): 
                                ?>
                                <a href="<?php echo $print_url; ?>" target="_blank" class="print-cert-btn">
                                    🖨️ Print Certificate
                                </a>
                                <?php 
                                    endif;
                                } 
                                ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($req['status'] !== 'released'): ?>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo $req['id']; ?>">
                                <select name="status" class="action-select" onchange="this.form.submit()">
                                    <option value="pending" <?php echo $req['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $req['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="ready" <?php echo $req['status'] === 'ready' ? 'selected' : ''; ?>>Ready</option>
                                    <option value="released" <?php echo $req['status'] === 'released' ? 'selected' : ''; ?>>Released</option>
                                </select>
                            </form>
                            <?php else: ?>
                            <div class="action-select status-locked">
                                Released (Locked)
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (empty($requests)): ?>
            <div style="text-align: center; padding: 3rem;">
                <h3 style="color: #666; margin-bottom: 1rem;">📭 No Certificate Requests Found</h3>
                <p style="color: #999; margin-bottom: 1.5rem;">No certificate requests match your current filters.</p>
                <a href="view-certificate-requests.php" class="admin-btn">Clear Filters</a>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page-1; ?>&status=<?php echo $status_filter; ?>&cert_type=<?php echo urlencode($cert_type); ?>&search=<?php echo urlencode($search); ?>">« Previous</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&cert_type=<?php echo urlencode($cert_type); ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page+1; ?>&status=<?php echo $status_filter; ?>&cert_type=<?php echo urlencode($cert_type); ?>&search=<?php echo urlencode($search); ?>">Next »</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function viewFormDetails(requestId) {
            // Open the certificate request form with pre-filled data
            window.open('../pages/certificate-request.php?admin_view=' + requestId + '&readonly=1', '_blank');
        }

        // Toast notification functionality
        function showToast() {
            const overlay = document.getElementById('toastOverlay');
            const toast = document.getElementById('toast');
            
            if (overlay && toast) {
                overlay.classList.add('show');
                toast.classList.add('show');
                
                // Auto-hide after 5 seconds
                setTimeout(function() {
                    hideToast();
                }, 5000);
            }
        }

        function hideToast() {
            const overlay = document.getElementById('toastOverlay');
            const toast = document.getElementById('toast');
            
            if (overlay && toast) {
                toast.classList.remove('show');
                overlay.classList.remove('show');
                
                // Remove from DOM after animation
                setTimeout(function() {
                    if (overlay && overlay.parentNode) {
                        overlay.parentNode.removeChild(overlay);
                    }
                }, 400);
            }
        }

        // Show toast on page load if message exists
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($show_toast): ?>
            setTimeout(showToast, 100);
            <?php endif; ?>
        });

        // Close toast when clicking overlay
        document.addEventListener('click', function(e) {
            if (e.target.id === 'toastOverlay') {
                hideToast();
            }
        });

        // Close toast with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideToast();
            }
        });
    </script>
</body>
</html>
