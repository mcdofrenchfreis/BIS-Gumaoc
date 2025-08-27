<?php
session_start();
include '../includes/db_connect.php';
include '../includes/AdminLogger.php';
include '../includes/email_service.php';

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
    $allowed_statuses = ['pending', 'approved', 'rejected'];
    
    if (in_array($new_status, $allowed_statuses)) {
        // Get current status first
        $stmt = $pdo->prepare("SELECT status, first_name, last_name, email FROM resident_registrations WHERE id = ?");
        $stmt->execute([$id]);
        $current_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $current_status = $current_data['status'];
        
        // Check status progression rules
        $status_valid = false;
        $error_message = '';
        
        if ($current_status === 'pending') {
            // From pending, can go to approved or rejected
            if (in_array($new_status, ['approved', 'rejected'])) {
                $status_valid = true;
            } else {
                $error_message = "From pending status, you can only approve or reject the registration.";
            }
        } elseif ($current_status === 'approved') {
            // From approved, status is locked
            $error_message = "Approved registrations cannot be changed. Status is locked.";
        } elseif ($current_status === 'rejected') {
            // From rejected, status is locked
            $error_message = "Rejected registrations cannot be changed. Status is locked.";
        }
        
        if ($status_valid) {
            $stmt = $pdo->prepare("UPDATE resident_registrations SET status = ? WHERE id = ?");
            $result = $stmt->execute([$new_status, $id]);
            
            if ($result) {
                // Get full registration data for email processing
                $stmt = $pdo->prepare("SELECT * FROM resident_registrations WHERE id = ?");
                $stmt->execute([$id]);
                $registration_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $email_sent = false;
                $email_message = '';
                
                // Process emails based on status change
                if ($new_status === 'approved' && $registration_data['email']) {
                    try {
                        // Generate RFID and temporary password
                        $generated_rfid = EmailService::generateUniqueRFID($pdo);
                        $temp_password = EmailService::generateTempPassword();
                        $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
                        
                        // Look for existing resident account (should exist as 'pending' from registration)
                        $check_stmt = $pdo->prepare("SELECT id, status FROM residents WHERE email = ?");
                        $check_stmt->execute([$registration_data['email']]);
                        $existing_resident = $check_stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($existing_resident) {
                            // Account exists - activate it with new RFID and password
                            $resident_id = $existing_resident['id'];
                            
                            // Update existing resident: activate account, set new RFID and password
                            $update_sql = "UPDATE residents SET 
                                rfid_code = ?, rfid = ?, password = ?, 
                                status = 'active', profile_complete = 1, updated_at = NOW() 
                                WHERE id = ?";
                            $update_stmt = $pdo->prepare($update_sql);
                            $update_result = $update_stmt->execute([
                                $generated_rfid, $generated_rfid, $hashed_password, $resident_id
                            ]);
                            
                            if ($update_result) {
                                // Assign the RFID code
                                EmailService::assignRFIDCode($pdo, $generated_rfid, $resident_id, $registration_data['email']);
                                
                                // Send approval email with credentials
                                $emailService = new EmailService();
                                $resident_name = trim($registration_data['first_name'] . ' ' . $registration_data['last_name']);
                                
                                // Log email attempt
                                error_log("Admin Approval: Sending credentials to: {$registration_data['email']} for {$resident_name} with RFID: {$generated_rfid}");
                                
                                $email_sent = $emailService->sendApprovalEmail(
                                    $registration_data['email'],
                                    $resident_name,
                                    $generated_rfid,
                                    $temp_password
                                );
                                
                                // Log email result
                                error_log("Admin Approval Email Result: " . ($email_sent ? 'SUCCESS' : 'FAILED') . " for {$registration_data['email']}");
                                
                                if ($email_sent) {
                                    $email_message = " Account activated successfully! Login credentials with RFID ({$generated_rfid}) sent to {$registration_data['email']}";
                                } else {
                                    $email_message = " Account activated with RFID ({$generated_rfid}), but email delivery failed. Please contact resident manually.";
                                }
                            } else {
                                $email_message = " Registration approved, but failed to activate resident account.";
                            }
                        } else {
                            // No existing account found - this shouldn't happen with the new workflow
                            error_log("Warning: No resident account found for approved registration {$registration_data['email']}");
                            $email_message = " Registration approved, but no resident account found. Please check the registration process.";
                        }
                        
                    } catch (Exception $e) {
                        error_log("Approval process error: " . $e->getMessage());
                        $email_message = " Registration approved, but there was an error processing the account activation.";
                    }
                    
                } elseif ($new_status === 'rejected' && $registration_data['email']) {
                    try {
                        // Send rejection email
                        $emailService = new EmailService();
                        $resident_name = $registration_data['first_name'] . ' ' . $registration_data['last_name'];
                        $email_sent = $emailService->sendRejectionEmail(
                            $registration_data['email'],
                            $resident_name
                        );
                        
                        if ($email_sent) {
                            $email_message = " Rejection notification sent to {$registration_data['email']}";
                        } else {
                            $email_message = " Registration rejected, but email delivery failed. Please contact resident manually.";
                        }
                        
                    } catch (Exception $e) {
                        error_log("Rejection email error: " . $e->getMessage());
                        $email_message = " Registration rejected, but there was an error sending the notification email.";
                    }
                }
                
                // Enhanced logging with more details
                $logger->logStatusUpdate(
                    'resident_registration',
                    $id,
                    $current_status,
                    $new_status,
                    [
                        'applicant_name' => $current_data['first_name'] . ' ' . $current_data['last_name'],
                        'registration_type' => 'resident_registration',
                        'processing_time' => date('Y-m-d H:i:s'),
                        'admin_action' => true,
                        'email_sent' => $email_sent,
                        'email_address' => $registration_data['email'] ?? 'N/A'
                    ]
                );
                
                $_SESSION['toast_message'] = "The status of Registration ID #$id has been successfully updated to " . ucfirst($new_status) . "." . $email_message;
                $_SESSION['toast_type'] = 'success';
            } else {
                $logger->log('error', 'resident_registration', "Failed to update status for Registration ID #$id", $id);
                $_SESSION['toast_message'] = "Failed to update status for Registration ID #$id";
                $_SESSION['toast_type'] = 'error';
            }
        } else {
            $logger->log('warning', 'resident_registration', "Invalid status change attempt for Registration ID #$id: {$error_message}", $id);
            $_SESSION['toast_message'] = $error_message;
            $_SESSION['toast_type'] = 'error';
        }
    } else {
        $_SESSION['toast_message'] = "Invalid status selected for Registration ID #$id";
        $_SESSION['toast_type'] = 'error';
    }
    
    header('Location: view-resident-registrations.php');
    exit;
}

// Get filter and search parameters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = [];
$params = [];

if ($status_filter && in_array($status_filter, ['pending', 'approved', 'rejected'])) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR middle_name LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count
$count_sql = "SELECT COUNT(*) FROM resident_registrations $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Get records with additional data counts
$sql = "SELECT rr.id, rr.first_name, rr.middle_name, rr.last_name, rr.age, rr.gender, 
        rr.birth_date, rr.birth_place, rr.house_number, rr.email, rr.status, rr.submitted_at,
        (SELECT COUNT(*) FROM family_members fm WHERE fm.registration_id = rr.id) as family_count,
        (SELECT COUNT(*) FROM family_disabilities fd WHERE fd.registration_id = rr.id) as disability_count,
        (SELECT COUNT(*) FROM family_organizations fo WHERE fo.registration_id = rr.id) as organization_count
        FROM resident_registrations rr $where_clause ORDER BY submitted_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$registrations = $stmt->fetchAll();

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
    <title>Census Registrations - Admin</title>
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

        @media (max-width: 768px) {
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

        @media (max-width: 480px) {
            .toast {
                min-width: 280px;
            }
            
            .toast-content {
                padding: 18px 20px;
                gap: 12px;
            }
        }

        /* Existing styles */
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
            padding: 0.6rem 1.2rem;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            white-space: nowrap;
        }
        
        .view-form-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
            background: linear-gradient(135deg, #45a049, #4CAF50);
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
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
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

        .action-select option:disabled {
            color: #6c757d;
            background: #f8f9fa;
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

        /* Responsive Design */
        /* Family Data Summary Styles */
        .family-data-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
            align-items: center;
        }
        
        .data-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.2rem 0.4rem;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 600;
            white-space: nowrap;
            cursor: help;
        }
        
        .family-badge {
            background: rgba(76, 175, 80, 0.1);
            color: #2e7d32;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }
        
        .disability-badge {
            background: rgba(76, 175, 80, 0.1);
            color: #2e7d32;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }
        
        .organization-badge {
            background: rgba(76, 175, 80, 0.1);
            color: #2e7d32;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }
        
        .basic-badge {
            background: rgba(76, 175, 80, 0.1);
            color: #2e7d32;
            border: 1px solid rgba(76, 175, 80, 0.3);
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
            
            .search-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-form input[type="text"] {
                min-width: 100%;
            }
            
            .admin-table {
                overflow-x: auto;
            }
            
            .admin-table table {
                min-width: 800px;
            }
            
            .admin-table th,
            .admin-table td {
                padding: 0.8rem 0.5rem;
                font-size: 0.85rem;
            }
            
            .family-data-summary {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.2rem;
            }
            
            .data-badge {
                font-size: 0.65rem;
                padding: 0.15rem 0.3rem;
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
                <h1>👥 Census Registrations</h1>
                <p>Total: <?php echo $total_records; ?> registrations</p>
            </div>
            <a href="dashboard.php" class="admin-btn">← Back to Dashboard</a>
        </div>
        
        <div class="admin-controls">
            <form method="GET" class="search-form">
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
                
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name...">
                <button type="submit" class="admin-btn">🔍 Search</button>
                <a href="view-resident-registrations.php" class="admin-btn">🔄 Clear</a>
            </form>
        </div>
        
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Birth Info</th>
                        <th>Family Data</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>View Form</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $reg): ?>
                    <tr>
                        <td><strong>#<?php echo $reg['id']; ?></strong></td>
                        <td>
                            <strong><?php echo htmlspecialchars($reg['first_name'] . ' ' . $reg['last_name']); ?></strong>
                            <?php if ($reg['middle_name'] ?? false): ?>
                                <br><small style="color: #666;"><?php echo htmlspecialchars($reg['middle_name']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $reg['age']; ?></td>
                        <td><?php echo htmlspecialchars($reg['gender']); ?></td>
                        <td>
                            <div style="font-size: 0.85rem;">
                                <?php if ($reg['birth_date'] ?? false): ?>
                                    <strong><?php echo date('M j, Y', strtotime($reg['birth_date'])); ?></strong>
                                <?php else: ?>
                                    <span style="color: #999; font-style: italic;">No birth date</span>
                                <?php endif; ?>
                                <?php if ($reg['birth_place'] ?? false): ?>
                                    <br><small style="color: #666; font-style: italic;">📍 <?php echo htmlspecialchars($reg['birth_place']); ?></small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="family-data-summary">
                                <?php if ($reg['family_count'] > 0): ?>
                                    <span class="data-badge family-badge" title="<?php echo $reg['family_count']; ?> family member(s)">👨‍👩‍👧‍👦 <?php echo $reg['family_count']; ?></span>
                                <?php endif; ?>
                                <?php if ($reg['disability_count'] > 0): ?>
                                    <span class="data-badge disability-badge" title="<?php echo $reg['disability_count']; ?> disability record(s)">♿ <?php echo $reg['disability_count']; ?></span>
                                <?php endif; ?>
                                <?php if ($reg['organization_count'] > 0): ?>
                                    <span class="data-badge organization-badge" title="<?php echo $reg['organization_count']; ?> organization membership(s)">🏢 <?php echo $reg['organization_count']; ?></span>
                                <?php endif; ?>
                                <?php if ($reg['family_count'] == 0 && $reg['disability_count'] == 0 && $reg['organization_count'] == 0): ?>
                                    <span class="data-badge basic-badge">📋 Basic Info Only</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $reg['status']; ?>">
                                <?php echo ucfirst($reg['status']); ?>
                                <?php if ($reg['status'] !== 'pending'): ?>
                                    🔒
                                <?php endif; ?>
                            </span>
                        </td>
                        <td>
                            <div><?php echo date('M j, Y', strtotime($reg['submitted_at'])); ?></div>
                            <small style="color: #666;"><?php echo date('g:i A', strtotime($reg['submitted_at'])); ?></small>
                        </td>
                        <td>
                            <button onclick="viewRegistrationDetails(<?php echo $reg['id']; ?>)" class="view-form-btn" 
                                    title="View complete registration form including personal info, family members, disabilities, and organizations">
                                👁️ View Complete Form
                            </button>
                        </td>
                        <td>
                            <?php if ($reg['status'] === 'pending'): ?>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo $reg['id']; ?>">
                                <select name="status" class="action-select" onchange="this.form.submit()">
                                    <option value="pending" selected>Pending</option>
                                    <option value="approved">Approve</option>
                                    <option value="rejected">Reject</option>
                                </select>
                            </form>
                            <?php else: ?>
                            <div class="action-select status-locked">
                                <?php echo ucfirst($reg['status']); ?> (Locked)
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (empty($registrations)): ?>
            <div style="text-align: center; padding: 3rem;">
                <h3 style="color: #666; margin-bottom: 1rem;">📭 No Census Registrations Found</h3>
                <p style="color: #999; margin-bottom: 1.5rem;">No resident registrations match your current filters.</p>
                <a href="view-resident-registrations.php" class="admin-btn">Clear Filters</a>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page-1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">« Previous</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page+1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">Next »</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function viewRegistrationDetails(registrationId) {
            // Log the form view action
            fetch('../includes/log-action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'form_view',
                    target_type: 'resident_registration',
                    target_id: registrationId,
                    description: `Viewed resident registration form ID #${registrationId}`
                })
            });
            
            // Open the resident registration form with pre-filled data in new tab
            window.open('../pages/resident-registration.php?admin_view=' + registrationId + '&readonly=1', '_blank');
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
