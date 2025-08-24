<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/AdminLogger.php';
require_once '../includes/email_service.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Initialize logger
$logger = new AdminLogger($pdo);

// Handle status updates
if ($_POST['action'] ?? '' === 'update_status' && isset($_POST['id'], $_POST['status'])) {
    $id = (int)$_POST['id'];
    $new_status = $_POST['status'];
    $allowed_statuses = ['active', 'inactive', 'pending'];
    
    if (in_array($new_status, $allowed_statuses)) {
        // Get current data first
        $stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ?");
        $stmt->execute([$id]);
        $current_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($current_data) {
            $current_status = $current_data['status'];
            
            // Check if activating a pending user without RFID
            $needsRFIDGeneration = ($current_status === 'pending' && 
                                  $new_status === 'active' && 
                                  empty($current_data['rfid_code']));
            
            $generated_rfid = null;
            $temp_password = null;
            $email_sent = false;
            
            if ($needsRFIDGeneration) {
                try {
                    // Generate unique RFID and temporary password
                    $generated_rfid = EmailService::generateUniqueRFID($pdo);
                    $temp_password = EmailService::generateTempPassword();
                    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
                    
                    // Update resident with new status, RFID, and password
                    $stmt = $pdo->prepare("UPDATE residents SET status = ?, rfid_code = ?, password = ? WHERE id = ?");
                    $result = $stmt->execute([$new_status, $generated_rfid, $hashed_password, $id]);
                    
                    if ($result) {
                        // Send activation email with RFID and password
                        $emailService = new EmailService();
                        $resident_name = $current_data['first_name'] . ' ' . $current_data['last_name'];
                        $email_sent = $emailService->sendRFIDActivationEmail(
                            $current_data['email'],
                            $resident_name,
                            $generated_rfid,
                            $temp_password
                        );
                        
                        // Enhanced logging with RFID generation details
                        $logger->log(
                            'activation_with_rfid',
                            'resident',
                            "Activated resident {$resident_name} (ID: {$id}) with auto-generated RFID and credentials",
                            $id,
                            [
                                'old_status' => $current_status,
                                'new_status' => $new_status,
                                'generated_rfid' => $generated_rfid,
                                'email_sent' => $email_sent,
                                'resident_name' => $resident_name,
                                'resident_email' => $current_data['email'],
                                'auto_generated' => true,
                                'admin_action' => true
                            ]
                        );
                        
                        if ($email_sent) {
                            $_SESSION['toast_message'] = "Resident ID #$id activated successfully! RFID ($generated_rfid) generated and credentials emailed to {$current_data['email']}";
                            $_SESSION['toast_type'] = 'success';
                        } else {
                            $_SESSION['toast_message'] = "Resident ID #$id activated with RFID ($generated_rfid), but email delivery failed. Please contact resident manually.";
                            $_SESSION['toast_type'] = 'warning';
                        }
                    } else {
                        throw new Exception("Failed to update resident record");
                    }
                    
                } catch (Exception $e) {
                    $logger->log('error', 'resident', "Failed to activate resident ID #$id with RFID generation: " . $e->getMessage(), $id);
                    $_SESSION['toast_message'] = "Failed to activate resident ID #$id: " . $e->getMessage();
                    $_SESSION['toast_type'] = 'error';
                }
                
            } else {
                // Regular status update without RFID generation
                $stmt = $pdo->prepare("UPDATE residents SET status = ? WHERE id = ?");
                $result = $stmt->execute([$new_status, $id]);
                
                if ($result) {
                    // Regular logging
                    $logger->logStatusUpdate(
                        'resident',
                        $id,
                        $current_status,
                        $new_status,
                        [
                            'resident_name' => $current_data['first_name'] . ' ' . $current_data['last_name'],
                            'resident_email' => $current_data['email'],
                            'processing_time' => date('Y-m-d H:i:s'),
                            'admin_action' => true
                        ]
                    );
                    
                    $_SESSION['toast_message'] = "Resident ID #$id status updated to " . ucfirst($new_status);
                    $_SESSION['toast_type'] = 'success';
                } else {
                    $logger->log('error', 'resident', "Failed to update status for Resident ID #$id", $id);
                    $_SESSION['toast_message'] = "Failed to update status for Resident ID #$id";
                    $_SESSION['toast_type'] = 'error';
                }
            }
        }
    } else {
        $_SESSION['toast_message'] = "Invalid status selected";
        $_SESSION['toast_type'] = 'error';
    }
    
    header('Location: view-residents.php');
    exit;
}

// Get filter and search parameters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = [];
$params = [];

if ($status_filter && in_array($status_filter, ['active', 'inactive', 'pending'])) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR middle_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term, $search_term]);
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count
$count_sql = "SELECT COUNT(*) FROM residents $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Get records
$sql = "SELECT * FROM residents $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$residents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if we should show toast
$show_toast = isset($_SESSION['toast_message']);
$toast_message = $_SESSION['toast_message'] ?? '';
$toast_type = $_SESSION['toast_type'] ?? 'success';

// Clear session variables after getting them
if (isset($_SESSION['toast_message'])) {
    unset($_SESSION['toast_message']);
    unset($_SESSION['toast_type']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residents Management - Admin</title>
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

        .toast-warning {
            border-left: 5px solid #ffc107;
            box-shadow: 0 20px 60px rgba(255, 193, 7, 0.2);
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

        /* Main Styles */
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
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: white;
        }
        
        .btn-info {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
        }
        
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }
        
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .status-pending { background: #fff3cd; color: #856404; }
        
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

        .pagination {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
            gap: 0.5rem;
        }

        .pagination a, .pagination span {
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
            color: #495057;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background: #e9ecef;
        }

        .pagination .current {
            background: #4caf50;
            color: white;
            border-color: #4caf50;
        }

        .search-controls {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-box, .filter-select {
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.9rem;
            min-width: 200px;
        }

        .search-box:focus, .filter-select:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
        }

        .stats-summary {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            border-left: 4px solid #4caf50;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2e7d32;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        @media (max-width: 768px) {
            .admin-container {
                padding: 1rem;
            }
            
            .admin-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .admin-controls {
                flex-direction: column;
            }
            
            .search-controls {
                flex-direction: column;
                width: 100%;
            }
            
            .search-box, .filter-select {
                width: 100%;
                min-width: unset;
            }
            
            .admin-table {
                overflow-x: auto;
            }
            
            .admin-table th, .admin-table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.85rem;
                white-space: nowrap;
            }
            
            .pagination {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <!-- Toast Notification -->
    <?php if ($show_toast): ?>
    <div id="toastOverlay" class="toast-overlay show">
        <div id="toast" class="toast toast-<?php echo $toast_type; ?> show">
            <div class="toast-content">
                <div class="toast-icon">
                    <?php 
                        if ($toast_type === 'success') {
                            echo '✅';
                        } elseif ($toast_type === 'warning') {
                            echo '⚠️';
                        } else {
                            echo '❌';
                        }
                    ?>
                </div>
                <div class="toast-message">
                    <?php echo htmlspecialchars($toast_message); ?>
                </div>
                <button class="toast-close" onclick="closeToast()">×</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="admin-container">
        <!-- Header -->
        <div class="admin-header">
            <div>
                <h1>Residents Management</h1>
                <p>View and manage resident profiles and accounts</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-secondary">
                    ← Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Stats Summary -->
        <?php
        $stats_sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
            FROM residents";
        $stats_stmt = $pdo->prepare($stats_sql);
        $stats_stmt->execute();
        $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="stats-summary">
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Residents</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['active']; ?></div>
                <div class="stat-label">Active</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['inactive']; ?></div>
                <div class="stat-label">Inactive</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['pending']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>

        <!-- Controls -->
        <div class="admin-controls">
            <form method="GET" class="search-controls">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search by name, email, or phone..." class="search-box">
                
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                </select>
                
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="view-residents.php" class="btn btn-secondary">Clear</a>
            </form>
        </div>

        <!-- Results Info -->
        <div style="margin-bottom: 1rem; color: #666;">
            Showing <?php echo count($residents); ?> of <?php echo $total_records; ?> residents
            <?php if ($search): ?>
                (filtered by: "<?php echo htmlspecialchars($search); ?>")
            <?php endif; ?>
        </div>

        <!-- Table -->
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>RFID</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($residents)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem; color: #666;">
                            No residents found matching your criteria.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($residents as $resident): ?>
                    <tr>
                        <td>#<?php echo str_pad($resident['id'], 4, '0', STR_PAD_LEFT); ?></td>
                        <td>
                            <strong>
                                <?php echo htmlspecialchars($resident['first_name'] . ' ' . 
                                    ($resident['middle_name'] ? $resident['middle_name'] . ' ' : '') . 
                                    $resident['last_name']); ?>
                            </strong>
                        </td>
                        <td><?php echo htmlspecialchars($resident['email']); ?></td>
                        <td><?php echo htmlspecialchars($resident['phone']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $resident['status']; ?>">
                                <?php echo ucfirst($resident['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($resident['rfid_code']): ?>
                                <span style="color: #28a745;">✓ Registered</span>
                            <?php else: ?>
                                <span style="color: #6c757d;">No RFID</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($resident['created_at'])); ?></td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <a href="edit-resident.php?id=<?php echo $resident['id']; ?>" 
                                   class="btn btn-info" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">
                                    📝 Edit
                                </a>
                                
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="id" value="<?php echo $resident['id']; ?>">
                                    <select name="status" class="action-select" onchange="this.form.submit()">
                                        <option value="">Change Status</option>
                                        <option value="active" <?php echo $resident['status'] === 'active' ? 'disabled' : ''; ?>>
                                            Activate
                                        </option>
                                        <option value="inactive" <?php echo $resident['status'] === 'inactive' ? 'disabled' : ''; ?>>
                                            Deactivate
                                        </option>
                                        <option value="pending" <?php echo $resident['status'] === 'pending' ? 'disabled' : ''; ?>>
                                            Set Pending
                                        </option>
                                    </select>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page-1; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">
                    ← Previous
                </a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page+1; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">
                    Next →
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function closeToast() {
            const overlay = document.getElementById('toastOverlay');
            const toast = document.getElementById('toast');
            
            if (toast && overlay) {
                toast.classList.remove('show');
                overlay.classList.remove('show');
                
                setTimeout(() => {
                    overlay.style.display = 'none';
                }, 300);
            }
        }

        // Auto-close toast after 5 seconds
        <?php if ($show_toast): ?>
        setTimeout(closeToast, 5000);
        <?php endif; ?>
    </script>
</body>
</html>