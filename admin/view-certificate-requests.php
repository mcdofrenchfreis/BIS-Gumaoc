<?php
session_start();
include '../includes/db_connect.php';
include '../includes/AdminLogger.php';
require_once '../includes/certificate_request_status.php';

certificate_request_ensure_status_schema($pdo);
$cert_statuses = certificate_request_statuses();
$cert_status_labels = certificate_request_status_labels();

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
    if (in_array($new_status, $cert_statuses, true)) {
        $stmt = $pdo->prepare("SELECT status, certificate_type, full_name FROM certificate_requests WHERE id = ?");
        $stmt->execute([$id]);
        $current_data = $stmt->fetch();
        $current_status = $current_data['status'];

        $status_valid = certificate_request_can_transition($current_status, $new_status);
        $error_message = $status_valid
            ? ''
            : 'Invalid status change. After release, mark as Received when the resident picks up the certificate.';

        if ($current_status === 'received') {
            $status_valid = false;
            $error_message = 'Completed requests (received by resident) cannot be changed.';
        }

        if ($status_valid) {
            // Begin transaction to update both certificate and queue ticket
            $pdo->beginTransaction();
            
            try {
                // Update certificate status
                $stmt = $pdo->prepare("UPDATE certificate_requests SET status = ? WHERE id = ?");
                $result = $stmt->execute([$new_status, $id]);
                
                if (!$result) {
                    throw new Exception("Failed to update certificate status");
                }
                
                // Get the linked queue ticket ID if it exists
                $queue_stmt = $pdo->prepare("SELECT queue_ticket_id FROM certificate_requests WHERE id = ?");
                $queue_stmt->execute([$id]);
                $queue_ticket_id = $queue_stmt->fetchColumn();
                
                // Update linked queue ticket status based on certificate status
                if ($queue_ticket_id) {
                    $queue_status_mapping = [
                        'pending' => 'waiting',
                        'processing' => 'serving',
                        'ready' => 'serving',
                        'released' => 'completed',
                        'received' => 'completed',
                    ];
                    
                    $queue_status = $queue_status_mapping[$new_status] ?? 'waiting';
                    
                    $queue_update_stmt = $pdo->prepare("UPDATE queue_tickets SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $queue_update_result = $queue_update_stmt->execute([$queue_status, $queue_ticket_id]);
                    
                    if (!$queue_update_result) {
                        throw new Exception("Failed to update queue ticket status");
                    }
                }
                
                // Commit transaction
                $pdo->commit();
                
                // Log the status update
                $logger->logStatusUpdate(
                    'certificate_request',
                    $id,
                    $current_status,
                    $new_status,
                    [
                        'certificate_type' => $current_data['certificate_type'],
                        'applicant_name' => $current_data['full_name'],
                        'queue_ticket_id' => $queue_ticket_id,
                        'queue_status_updated' => $queue_ticket_id ? $queue_status : 'no_queue_ticket'
                    ]
                );
                
                $queue_update_msg = $queue_ticket_id ? " and linked queue ticket status updated to " . ucfirst($queue_status) : "";
                $_SESSION['toast_message'] = "The status of Certificate Request ID #$id has been successfully updated to " . ucfirst($new_status) . $queue_update_msg;
                $_SESSION['toast_type'] = 'success';
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $pdo->rollback();
                $_SESSION['toast_message'] = "Failed to update status for Certificate Request ID #$id: " . $e->getMessage();
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
$active_tab = $_GET['tab'] ?? 'active'; // 'active' or 'released'
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query based on active tab
$where_conditions = [];
$params = [];

if ($active_tab === 'released') {
    $where_conditions[] = "cr.status IN ('released', 'received')";
} else {
    $where_conditions[] = "cr.status NOT IN ('released', 'received')";

    if ($status_filter && in_array($status_filter, ['pending', 'processing', 'ready'], true)) {
        $where_conditions = ["cr.status = ?"];
        $params[] = $status_filter;
    }
}

if ($cert_type) {
    $where_conditions[] = "cr.certificate_type = ?";
    $params[] = $cert_type;
}

if ($search) {
    $where_conditions[] = "(cr.full_name LIKE ? OR cr.certificate_type LIKE ? OR cr.purpose LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count
$count_sql = "SELECT COUNT(*) FROM certificate_requests cr $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Get records with all tricycle permit data
$sql = "SELECT cr.*, 
        CASE 
            WHEN cr.certificate_type = 'TRICYCLE PERMIT' THEN 
                CONCAT(cr.vehicle_make_type, ' - ', cr.plate_no)
            ELSE cr.purpose 
        END as display_info,
        qt.ticket_number as queue_ticket,
        qt.status as queue_status
        FROM certificate_requests cr
        LEFT JOIN queue_tickets qt ON cr.queue_ticket_id = qt.id
        $where_clause ORDER BY cr.submitted_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Get certificate types for filter
$cert_types = $pdo->query("SELECT DISTINCT certificate_type FROM certificate_requests ORDER BY certificate_type")->fetchAll(PDO::FETCH_COLUMN);

// Get counts for tab badges
$active_count = $pdo->query("SELECT COUNT(*) FROM certificate_requests WHERE status NOT IN ('released', 'received')")->fetchColumn();
$released_count = $pdo->query("SELECT COUNT(*) FROM certificate_requests WHERE status IN ('released', 'received')")->fetchColumn();

// Check if we should show toast
$show_toast = isset($_SESSION['toast_message']);
$toast_message = $_SESSION['toast_message'] ?? '';
$toast_type = $_SESSION['toast_type'] ?? 'success';

// Clear session variables after getting them
if (isset($_SESSION['toast_message'])) {
    unset($_SESSION['toast_message']);
    unset($_SESSION['toast_type']);
}

require_once __DIR__ . '/../includes/certificate_print_helpers.php';

function canPrintCertificate(?string $certificate_type): bool
{
    if ($certificate_type === null || trim($certificate_type) === '') {
        return false;
    }
    return cr_renderer_for_type($certificate_type) !== null;
}

// Function to get certificate type icon and color
function getCertificateTypeDisplay($certificate_type) {
    $certificate_type = strtoupper(trim($certificate_type));
    
    $displays = [
        'BRGY. INDIGENCY' => ['icon' => '🏠', 'class' => 'cert-indigency'],
        'BRGY. CLEARANCE' => ['icon' => '📋', 'class' => 'cert-clearance'],
        'PROOF OF RESIDENCY' => ['icon' => '🏡', 'class' => 'cert-residency'],
        'TRICYCLE PERMIT' => ['icon' => '🛺', 'class' => 'cert-tricycle']
    ];
    
    return $displays[$certificate_type] ?? ['icon' => '📄', 'class' => 'cert-default'];
}

// Function to display request details based on certificate type
function getRequestDetails($request) {
    if ($request['certificate_type'] === 'TRICYCLE PERMIT') {
        $details = [];
        if (!empty($request['vehicle_make_type'])) {
            $details[] = "Make: " . htmlspecialchars($request['vehicle_make_type']);
        }
        if (!empty($request['plate_no'])) {
            $details[] = "Plate: " . htmlspecialchars($request['plate_no']);
        }
        if (!empty($request['motor_no'])) {
            $details[] = "Motor: " . htmlspecialchars($request['motor_no']);
        }
        return implode(" | ", $details);
    }
    
    return htmlspecialchars(substr($request['purpose'], 0, 50)) . (strlen($request['purpose']) > 50 ? '...' : '');
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
        
        /* Certificate Summary Styles */
        .summary-section {
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        
        .summary-section h3 {
            color: #2e7d32;
            font-size: 16px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .summary-row {
            display: flex;
            margin-bottom: 8px;
            padding: 4px 0;
        }
        
        .summary-label {
            flex: 0 0 40%;
            font-weight: 500;
            color: #555;
        }
        
        .summary-value {
            flex: 0 0 60%;
            color: #333;
        }

        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            padding-top: 90px; /* offset for fixed admin mini nav */
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
            text-align: center;
            border-bottom: 1px solid #eee;
        }
        /* Fix ID column: keep on one line and give a fixed width */
        .admin-table .col-id {
            width: 110px;
            min-width: 110px;
            white-space: nowrap;
            overflow-wrap: normal;
            word-break: normal;
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
            line-height: 1.2;
            height: 36px;
            min-height: 36px;
            box-sizing: border-box;
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
            margin-bottom: 0.3rem;
            line-height: 1.2;
            height: 36px;
            min-height: 36px;
            box-sizing: border-box;
        }

        .print-cert-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
            background: linear-gradient(135deg, #f57c00, #ff9800);
            color: white;
        }

        .cert-print-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 20000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .cert-print-modal-overlay.open {
            display: flex !important;
        }

        .cert-print-modal {
            background: #fff;
            border-radius: 12px;
            width: min(95vw, 920px);
            height: min(92vh, 900px);
            min-height: 480px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .cert-print-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid #e0e0e0;
            background: #f8f9fa;
        }

        .cert-print-modal-header h3 {
            margin: 0;
            font-size: 1rem;
            color: #333;
        }

        .cert-print-modal-actions {
            display: flex;
            gap: 0.5rem;
        }

        .cert-print-modal-actions button {
            border: none;
            border-radius: 6px;
            padding: 0.45rem 0.9rem;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .cert-print-modal-actions .btn-print {
            background: #2e7d32;
            color: #fff;
        }

        .cert-print-modal-actions .btn-close {
            background: #6c757d;
            color: #fff;
        }

        .cert-print-modal-body {
            flex: 1;
            min-height: 360px;
            background: #e8e8e8;
            overflow: hidden;
            position: relative;
        }

        .cert-print-modal-body iframe {
            display: block;
            width: 100%;
            height: 100%;
            min-height: 360px;
            border: 0;
            background: #fff;
        }

        .cert-print-loading {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e8e8e8;
            color: #555;
            font-size: 0.95rem;
            z-index: 1;
        }

        .cert-print-modal-body.loaded .cert-print-loading {
            display: none;
        }

        .cert-print-error {
            display: none;
            padding: 1.25rem;
            color: #842029;
            background: #f8d7da;
            border: 1px solid #f5c2c7;
            border-radius: 8px;
            margin: 1rem;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .cert-print-error.visible {
            display: block;
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
        .status-released { background: #e1bee7; color: #6a1b9a; }
        .status-received { background: #c8e6c9; color: #1b5e20; }
        
        /* Enhanced Certificate Type Badges */
        .cert-type {
            padding: 0.3rem 0.8rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .cert-indigency { background: #fff3e0; color: #f57c00; border: 1px solid #ffcc02; }
        .cert-clearance { background: #e3f2fd; color: #1976d2; border: 1px solid #2196f3; }
        .cert-residency { background: #f3e5f5; color: #7b1fa2; border: 1px solid #9c27b0; }
        .cert-tricycle { background: #fff8e1; color: #f9a825; border: 1px solid #ffc107; }
        .cert-default { background: #f5f5f5; color: #616161; border: 1px solid #9e9e9e; }
        
        .action-select {
            padding: 0.4rem 0.6rem;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 0.85rem;
            background: transparent !important;
            color: #495057;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 140px; /* Fixed width for consistency */
            height: 38px; /* Fixed height for consistency */
            position: relative;
            display: block;
            margin: 0 auto;
            box-sizing: border-box; /* Ensures padding doesn't affect dimensions */
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
        
        
        .summary-container {
            margin-top: 20px;
        }
        
        .summary-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .summary-details {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .summary-row {
            display: flex;
            border-bottom: 1px solid #f5f5f5;
            padding-bottom: 10px;
        }
        
        .summary-label {
            font-weight: bold;
            width: 120px;
            color: #555;
        }
        
        .summary-value {
            flex: 1;
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

        /* Enhanced Details Display */
        .request-details {
            font-size: 0.85rem;
            color: #666;
            line-height: 1.4;
        }

        .tricycle-details {
            background: #fff8e1;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            border-left: 3px solid #ffc107;
            font-family: monospace;
            font-size: 0.8rem;
        }

        .applicant-name {
            font-weight: 600;
            color: #2e7d32;
        }

        /* Mobile responsiveness for tricycle details */
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

            .tricycle-details {
                font-size: 0.75rem;
                padding: 0.2rem 0.4rem;
            }
        }
        
        /* Queue Info Styling */
        .queue-info {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            font-size: 0.8rem;
            min-width: 120px;
        }
        
        .queue-ticket {
            font-family: monospace;
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.75rem;
        }
        
        .queue-status {
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 500;
            text-align: center;
        }
        
        .queue-status.status-waiting {
            background: #fff3cd;
            color: #856404;
        }
        
        .queue-status.status-serving {
            background: #cce5ff;
            color: #0066cc;
        }
        
        .queue-status.status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .queue-status.status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .no-queue {
            color: #999;
            font-style: italic;
            font-size: 0.8rem;
            padding: 0.5rem;
            text-align: center;
        }
        
        .queue-sync-indicator {
            color: #28a745;
            font-size: 0.65rem;
            display: flex;
            align-items: center;
            gap: 0.2rem;
            margin-top: 0.1rem;
        }
        
        /* Method of Application Styling */
        .application-method {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            font-size: 0.8rem;
            min-width: 120px;
        }
        
        .method-badge {
            padding: 0.4rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
            display: inline-block;
        }
        
        .online-badge {
            background: linear-gradient(135deg, #4caf50, #388e3c);
            color: white;
            border: 1px solid #2e7d32;
        }
        
        .walk-in-badge {
            background: linear-gradient(135deg, #2196f3, #1976d2);
            color: white;
            border: 1px solid #1565c0;
        }
        
        .queue-details {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            margin-top: 0.3rem;
        }
        
        /* Tab Styling */
        .tab-container {
            background: white;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .tab-navigation {
            display: flex;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        
        .tab-button {
            flex: 1;
            padding: 1rem 1.5rem;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: #6c757d;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .tab-button.active {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
        }
        
        .tab-button:hover:not(.active) {
            background: #e9ecef;
            color: #495057;
        }
        
        .tab-content {
            display: none;
            padding: 0;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .tab-badge {
            background: rgba(255, 255, 255, 0.2);
            color: currentColor;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .tab-button:not(.active) .tab-badge {
            background: #dee2e6;
            color: #6c757d;
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

    <?php $base_path = '../'; include __DIR__ . '/../includes/admin_mini_nav.php'; ?>
    <div class="admin-container">
        <div style="margin-bottom: 1rem; color:#2e7d32; font-weight:700;">
            <span style="font-weight:600;">📄 Certificate Requests</span> · <span style="font-weight:600; color:#444;">Total: <?php echo $total_records; ?></span>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-container">
            <div class="tab-navigation">
                <button class="tab-button <?php echo $active_tab === 'active' ? 'active' : ''; ?>" onclick="switchTab('active')">
                    📝 Active Requests
                    <span class="tab-badge"><?php echo $active_count; ?></span>
                </button>
                <button class="tab-button <?php echo $active_tab === 'released' ? 'active' : ''; ?>" onclick="switchTab('released')">
                    ✅ Released / Received
                    <span class="tab-badge"><?php echo $released_count; ?></span>
                </button>
            </div>
            
            <!-- Active Requests Tab -->
            <div class="tab-content <?php echo $active_tab === 'active' ? 'active' : ''; ?>" id="active-tab">
                <div class="admin-controls">
                    <form method="GET" class="search-form">
                        <input type="hidden" name="tab" value="active">
                        <select name="status" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="ready" <?php echo $status_filter === 'ready' ? 'selected' : ''; ?>>Ready</option>
                        </select>
                        
                        <select name="cert_type" onchange="this.form.submit()">
                            <option value="">All Certificate Types</option>
                            <?php foreach ($cert_types as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $cert_type === $type ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name, purpose, or vehicle details...">
                        <button type="submit" class="admin-btn">🔍 Search</button>
                        <a href="view-certificate-requests.php?tab=active" class="admin-btn">🔄 Clear</a>
                    </form>
                </div>
            </div>
            
            <!-- Released Certificates Tab -->
            <div class="tab-content <?php echo $active_tab === 'released' ? 'active' : ''; ?>" id="released-tab">
                <div class="admin-controls">
                    <form method="GET" class="search-form">
                        <input type="hidden" name="tab" value="released">
                        
                        <select name="cert_type" onchange="this.form.submit()">
                            <option value="">All Certificate Types</option>
                            <?php foreach ($cert_types as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $cert_type === $type ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search released certificates...">
                        <button type="submit" class="admin-btn">🔍 Search</button>
                        <a href="view-certificate-requests.php?tab=released" class="admin-btn">🔄 Clear</a>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
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
                    <?php $certDisplay = getCertificateTypeDisplay($req['certificate_type']); ?>
                    <tr>
                        <?php 
                            $typeNorm = strtoupper(trim($req['certificate_type']));
                            $prefixMap = [
                                'CERTIFICATION OF RESIDENCY' => 'COR-',
                                'BRGY. CLEARANCE' => 'BC-',
                                'BRGY. INDIGENCY' => 'BI-',
                                'TRICYCLE PERMIT' => 'TP-'
                            ];
                            $idPrefix = $prefixMap[$typeNorm] ?? '';
                        ?>
                        <td class="col-id"><strong><?php echo $idPrefix . $req['id']; ?></strong></td>
                        <td>
                            <span class="cert-type <?php echo $certDisplay['class']; ?>">
                                <?php echo $certDisplay['icon']; ?>
                                <?php echo htmlspecialchars($req['certificate_type']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="applicant-name">
                                <?php echo htmlspecialchars($req['full_name']); ?>
                            </div>
                        </td>
                        <td>
                            <div class="request-details">
                                <?php if ($req['certificate_type'] === 'TRICYCLE PERMIT'): ?>
                                    <div class="tricycle-details">
                                        <?php echo getRequestDetails($req); ?>
                                    </div>
                                <?php else: ?>
                                    <?php echo getRequestDetails($req); ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo htmlspecialchars($req['status']); ?> <?php echo $req['status'] === 'processing' ? 'print-available' : ''; ?>">
                                <?php echo htmlspecialchars(certificate_request_status_label($req['status'])); ?>
                                <?php if ($req['status'] === 'received'): ?>
                                    ✓
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
                                <a href="get-certificate-summary.php?standalone=1&id=<?php echo $req['id']; ?>" target="_blank" class="view-form-btn" onclick="logCertView(<?php echo $req['id']; ?>)">
                                    👁️ View Summary
                                </a>
                                <?php
                                if ($req['status'] === 'processing' && canPrintCertificate($req['certificate_type'])):
                                    $print_label = $req['certificate_type'] === 'TRICYCLE PERMIT' ? 'Permit' : 'Certificate';
                                ?>
                                <button type="button"
                                    class="print-cert-btn js-print-cert-btn"
                                    data-request-id="<?php echo (int) $req['id']; ?>"
                                    data-cert-type="<?php echo htmlspecialchars((string) $req['certificate_type'], ENT_QUOTES, 'UTF-8'); ?>">
                                    🖨️ Print <?php echo htmlspecialchars($print_label); ?>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($req['status'] !== 'received'): ?>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo $req['id']; ?>">
                                <select name="status" class="action-select" onchange="this.form.submit()">
                                    <?php
                                    $cur = $req['status'];
                                    echo '<option value="' . htmlspecialchars($cur) . '" selected>'
                                        . htmlspecialchars(certificate_request_status_label($cur)) . '</option>';
                                    foreach ($cert_statuses as $st) {
                                        if ($st !== $cur && certificate_request_can_transition($cur, $st)) {
                                            echo '<option value="' . htmlspecialchars($st) . '">→ '
                                                . htmlspecialchars(certificate_request_status_label($st)) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </form>
                            <?php else: ?>
                            <div class="action-select status-locked">
                                Received by Resident
                            </div>
                            <?php endif; ?>
                        </td>
                        
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (empty($requests)): ?>
            <div style="text-align: center; padding: 3rem;">
                <?php if ($active_tab === 'released'): ?>
                    <h3 style="color: #666; margin-bottom: 1rem;">✅ No Released Certificates Found</h3>
                    <p style="color: #999; margin-bottom: 1.5rem;">No released certificates match your current filters.</p>
                    <a href="view-certificate-requests.php?tab=released" class="admin-btn">Clear Filters</a>
                <?php else: ?>
                    <h3 style="color: #666; margin-bottom: 1rem;">📭 No Active Requests Found</h3>
                    <p style="color: #999; margin-bottom: 1.5rem;">No active certificate requests match your current filters.</p>
                    <a href="view-certificate-requests.php?tab=active" class="admin-btn">Clear Filters</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page-1; ?>&tab=<?php echo $active_tab; ?>&status=<?php echo $status_filter; ?>&cert_type=<?php echo urlencode($cert_type); ?>&search=<?php echo urlencode($search); ?>">« Previous</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&tab=<?php echo $active_tab; ?>&status=<?php echo $status_filter; ?>&cert_type=<?php echo urlencode($cert_type); ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page+1; ?>&tab=<?php echo $active_tab; ?>&status=<?php echo $status_filter; ?>&cert_type=<?php echo urlencode($cert_type); ?>&search=<?php echo urlencode($search); ?>">Next »</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <div id="certPrintModal" class="cert-print-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="certPrintModalTitle">
        <div class="cert-print-modal" onclick="event.stopPropagation()">
            <div class="cert-print-modal-header">
                <h3 id="certPrintModalTitle">Print certificate</h3>
                <div class="cert-print-modal-actions">
                    <button type="button" class="btn-print" onclick="printCertificateFromModal()">🖨️ Print</button>
                    <button type="button" class="btn-close" onclick="closeCertificatePrintModal()">Close</button>
                </div>
            </div>
            <div class="cert-print-modal-body" id="certPrintModalBody">
                <div id="certPrintError" class="cert-print-error" role="alert"></div>
                <div class="cert-print-loading" id="certPrintLoading">Loading certificate preview…</div>
                <iframe id="certPrintFrame" title="Certificate print preview"></iframe>
            </div>
        </div>
    </div>
    
    <script>
        function logCertView(requestId) {
            fetch('../includes/log-action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'form_view',
                    target_type: 'certificate_request',
                    target_id: requestId,
                    description: 'Viewed certificate request summary ID #' + requestId
                })
            }).catch(function () {});
        }

        function showCertPrintError(message) {
            const err = document.getElementById('certPrintError');
            const frame = document.getElementById('certPrintFrame');
            if (err) {
                err.textContent = message;
                err.classList.add('visible');
            }
            if (frame) {
                frame.style.display = 'none';
            }
        }

        function clearCertPrintError() {
            const err = document.getElementById('certPrintError');
            const frame = document.getElementById('certPrintFrame');
            if (err) {
                err.textContent = '';
                err.classList.remove('visible');
            }
            if (frame) {
                frame.style.display = 'block';
            }
        }

        function openCertificatePrint(requestId, certType) {
            const modal = document.getElementById('certPrintModal');
            const frame = document.getElementById('certPrintFrame');
            const title = document.getElementById('certPrintModalTitle');
            const body = document.getElementById('certPrintModalBody');
            const loading = document.getElementById('certPrintLoading');
            if (!modal || !frame) {
                alert('Print preview is not available. Please refresh the page and try again.');
                return;
            }

            if (title) {
                title.textContent = 'Print: ' + (certType || 'Certificate') + ' (Request #' + requestId + ')';
            }
            clearCertPrintError();
            if (body) {
                body.classList.remove('loaded');
            }
            if (loading) {
                loading.style.display = 'flex';
            }

            const printUrl = new URL('print-certificate.php', window.location.href);
            printUrl.searchParams.set('id', String(requestId));
            printUrl.searchParams.set('embed', '1');

            frame.onload = function () {
                if (body) {
                    body.classList.add('loaded');
                }
                if (loading) {
                    loading.style.display = 'none';
                }
            };

            frame.onerror = function () {
                showCertPrintError('Failed to load the certificate preview. Please try again.');
            };

            frame.src = printUrl.href;
            modal.classList.add('open');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeCertificatePrintModal() {
            const modal = document.getElementById('certPrintModal');
            const frame = document.getElementById('certPrintFrame');
            const body = document.getElementById('certPrintModalBody');
            const loading = document.getElementById('certPrintLoading');
            if (modal) {
                modal.classList.remove('open');
                modal.style.display = 'none';
            }
            document.body.style.overflow = '';
            clearCertPrintError();
            if (body) {
                body.classList.remove('loaded');
            }
            if (loading) {
                loading.style.display = 'flex';
            }
            if (frame) {
                frame.onload = null;
                frame.onerror = null;
                frame.src = 'about:blank';
            }
        }

        function printCertificateFromModal() {
            const frame = document.getElementById('certPrintFrame');
            const err = document.getElementById('certPrintError');
            if (err && err.classList.contains('visible')) {
                return;
            }
            if (!frame || !frame.src || frame.src === 'about:blank') {
                alert('Certificate preview is still loading. Please wait a moment and try again.');
                return;
            }
            try {
                const win = frame.contentWindow;
                if (win) {
                    win.focus();
                    win.print();
                }
            } catch (err) {
                alert('Could not open the print dialog. Please try again.');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('certPrintModal');
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.js-print-cert-btn');
                if (btn) {
                    e.preventDefault();
                    openCertificatePrint(btn.getAttribute('data-request-id'), btn.getAttribute('data-cert-type'));
                }
            });

            modal?.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeCertificatePrintModal();
                }
            });
        });

        function switchTab(tab) {
            try {
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                url.searchParams.delete('page');
                window.location.href = url.toString();
            } catch (e) {
                // Fallback
                window.location.href = 'view-certificate-requests.php?tab=' + encodeURIComponent(tab);
            }
        }

        function viewCertificateSummary(requestId) {
            // Log the form view action (non-blocking)
            fetch('../includes/log-action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'form_view',
                    target_type: 'certificate_request',
                    target_id: requestId,
                    description: `Viewed certificate request summary ID #${requestId}`
                })
            });
            
            // Open the existing summary endpoint in a new tab (works even if certificate-summary.php isn't deployed)
            const parts = window.location.pathname.split('/admin/');
            const base = parts[0]; // '' if root, or '/GUMAOC' if under subfolder
            const url = `${base}/admin/get-certificate-summary.php?id=${encodeURIComponent(requestId)}`;
            window.open(url, '_blank');
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

        // Close toast or print modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const printModal = document.getElementById('certPrintModal');
                if (printModal && printModal.classList.contains('open')) {
                    closeCertificatePrintModal();
                    return;
                }
                hideToast();
            }
        });
        
    </script>
</body>
</html>
