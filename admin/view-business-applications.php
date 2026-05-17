<?php
session_start();
include '../includes/db_connect.php';
require_once '../includes/business_application_status.php';

business_application_ensure_status_schema($pdo);

$ba_statuses = business_application_statuses();
$ba_status_labels = business_application_status_labels();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle status updates (only if admin is logged in)
if ($_POST['action'] ?? '' === 'update_status' && isset($_POST['id'], $_POST['status'])) {
    $redirect_tab = $_POST['tab'] ?? 'active';
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        $id = (int) $_POST['id'];
        $status = strtolower(trim((string) $_POST['status']));

        $curStmt = $pdo->prepare('SELECT status FROM business_applications WHERE id = ?');
        $curStmt->execute([$id]);
        $current_status = strtolower(trim((string) $curStmt->fetchColumn()));

        if ($current_status === 'received') {
            $_SESSION['success'] = 'Completed applications (received by resident) cannot be changed.';
        } elseif (in_array($status, $ba_statuses, true) && business_application_can_transition($current_status, $status)) {
            $stmt = $pdo->prepare('UPDATE business_applications SET status = ? WHERE id = ?');
            $stmt->execute([$status, $id]);
            $_SESSION['success'] = 'Status updated successfully!';
        } else {
            $_SESSION['success'] = 'Invalid status change.';
        }
    }
    header('Location: view-business-applications.php?tab=' . urlencode($redirect_tab));
    exit;
}

// Get filter and search parameters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$active_tab = $_GET['tab'] ?? 'active';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$active_status_filters = ['pending', 'reviewing', 'approved', 'rejected'];
$received_status_filters = ['ready', 'received'];

// Build query
$where_conditions = [];
$where_conditions_ba = [];
$params = [];

if ($active_tab === 'received') {
    $where_conditions[] = "status IN ('ready', 'received')";
    $where_conditions_ba[] = "ba.status IN ('ready', 'received')";

    if ($status_filter && in_array($status_filter, $received_status_filters, true)) {
        $where_conditions[] = 'status = ?';
        $where_conditions_ba[] = 'ba.status = ?';
        $params[] = $status_filter;
    }
} else {
    $where_conditions[] = "status NOT IN ('ready', 'received')";
    $where_conditions_ba[] = "ba.status NOT IN ('ready', 'received')";

    if ($status_filter && in_array($status_filter, $active_status_filters, true)) {
        $where_conditions[] = 'status = ?';
        $where_conditions_ba[] = 'ba.status = ?';
        $params[] = $status_filter;
    }
}

if ($search) {
    $where_conditions[] = "(owner_name LIKE ? OR business_name LIKE ? OR business_type LIKE ?)"; // unaliased for count
    $where_conditions_ba[] = "(ba.owner_name LIKE ? OR ba.business_name LIKE ? OR ba.business_type LIKE ?)"; // aliased for main
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";
$where_clause_ba = $where_conditions_ba ? "WHERE " . implode(" AND ", $where_conditions_ba) : "";

// Get total count
$count_sql = "SELECT COUNT(*) FROM business_applications $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Get records
$sql = "SELECT 
            ba.*, 
            r.first_name AS res_first_name,
            r.middle_name AS res_middle_name,
            r.last_name AS res_last_name,
            r.address AS resident_address,
            r.phone AS resident_phone,
            r.civil_status AS resident_civil_status,
            r.gender AS resident_gender,
            r.birthdate AS resident_birthdate,
            r.birth_place AS resident_birth_place
        FROM business_applications ba
        LEFT JOIN residents r ON ba.user_id = r.id
        $where_clause_ba
        ORDER BY ba.submitted_at DESC
        LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();

$active_count = (int) $pdo->query("SELECT COUNT(*) FROM business_applications WHERE status NOT IN ('ready', 'received')")->fetchColumn();
$received_count = (int) $pdo->query("SELECT COUNT(*) FROM business_applications WHERE status IN ('ready', 'received')")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Applications - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
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
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        .admin-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2e7d32;
        }
        
        .admin-table tbody tr:hover {
            background: rgba(76, 175, 80, 0.05);
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
            margin-bottom: 0.3rem;
            height: 36px;
            min-height: 36px;
            box-sizing: border-box;
        }
        
        .view-form-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
            background: linear-gradient(135deg, #45a049, #4CAF50);
        }
        
        .print-clearance-btn {
            background: linear-gradient(135deg, #ff9800, #f57c00);
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
            margin-bottom: 0.3rem;
            height: 36px;
            min-height: 36px;
            box-sizing: border-box;
        }
        
        .print-clearance-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 152, 0, 0.4);
            background: linear-gradient(135deg, #f57c00, #ff9800);
        }
        
        .view-column {
            min-width: 160px;
        }
        
        .view-column .view-form-btn,
        .view-column .print-clearance-btn {
            width: 160px; /* fixed width */
            justify-content: center;
            margin: 0.15rem auto;
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
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-ready { background: #e1bee7; color: #6a1b9a; }
        .status-received { background: #c8e6c9; color: #1b5e20; }
        .status-reviewing { background: #cce5ff; color: #004085; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
        .ref-number {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 0.3rem 0.6rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            font-family: 'Courier New', monospace;
            display: inline-block;
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
        
        .business-info {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.3rem;
        }
        
        .owner-details {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }
        
        .owner-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }
        
        .contact-info {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.85rem;
            color: #666;
        }
        
        .business-details {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }
        
        .business-name {
            font-weight: 600;
            color: #2e7d32;
            font-size: 1rem;
            margin-bottom: 0.3rem;
        }
        
        .reference-info {
            background: #f8f9fa;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.8rem;
            color: #666;
            font-family: 'Courier New', monospace;
        }
        
        .action-column {
            min-width: 150px;
        }
        
        .view-column {
            text-align: center;
            min-width: 120px;
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
        
        .alert {
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .admin-table {
                overflow-x: auto;
            }
            
            .admin-table table {
                min-width: 800px;
            }
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
            
            .admin-table th,
            .admin-table td {
                padding: 0.8rem 0.5rem;
                font-size: 0.85rem;
            }
        }

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
            padding: 1rem 1.25rem 1.25rem;
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

        .status-locked {
            padding: 0.5rem 0.75rem;
            background: #e8f5e9;
            color: #1b5e20;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
        }
    </style>
 </head>
 <body>
    <?php $base_path = '../'; include __DIR__ . '/../includes/admin_mini_nav.php'; ?>
    <div class="admin-container">
        <!-- Header removed in favor of admin mini nav -->
        <div style="margin-bottom: 1rem; color:#2e7d32; font-weight:700;">
            🏢 Business Applications · <span style="font-weight:600; color:#444;">Total: <?php echo $total_records; ?></span>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                ✅ <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <div class="tab-container">
            <div class="tab-navigation">
                <button type="button" class="tab-button <?php echo $active_tab === 'active' ? 'active' : ''; ?>" onclick="switchBusinessTab('active')">
                    📝 Active Applications
                    <span class="tab-badge"><?php echo $active_count; ?></span>
                </button>
                <button type="button" class="tab-button <?php echo $active_tab === 'received' ? 'active' : ''; ?>" onclick="switchBusinessTab('received')">
                    ✅ Ready / Received
                    <span class="tab-badge"><?php echo $received_count; ?></span>
                </button>
            </div>

            <div class="tab-content <?php echo $active_tab === 'active' ? 'active' : ''; ?>" id="active-tab">
                <form method="GET" class="search-form">
                    <input type="hidden" name="tab" value="active">
                    <select name="status" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <?php foreach ($ba_status_labels as $value => $label): ?>
                            <?php if (!in_array($value, $active_status_filters, true)) { continue; } ?>
                            <option value="<?php echo htmlspecialchars($value); ?>" <?php echo $status_filter === $value ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search active applications...">
                    <button type="submit" class="admin-btn">🔍 Search</button>
                    <a href="view-business-applications.php?tab=active" class="admin-btn">🔄 Clear</a>
                </form>
            </div>

            <div class="tab-content <?php echo $active_tab === 'received' ? 'active' : ''; ?>" id="received-tab">
                <form method="GET" class="search-form">
                    <input type="hidden" name="tab" value="received">
                    <select name="status" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <?php foreach ($ba_status_labels as $value => $label): ?>
                            <?php if (!in_array($value, $received_status_filters, true)) { continue; } ?>
                            <option value="<?php echo htmlspecialchars($value); ?>" <?php echo $status_filter === $value ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search ready or received applications...">
                    <button type="submit" class="admin-btn">🔍 Search</button>
                    <a href="view-business-applications.php?tab=received" class="admin-btn">🔄 Clear</a>
                </form>
            </div>
        </div>
        
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Application Details</th>
                        <th>Business Information</th>
                        <th>Owner Details</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th class="view-column">View Form</th>
                        <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                        <th class="action-column">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                    <tr>
                        <td>
                            <strong>#<?php echo $app['id']; ?></strong>
                        </td>
                        <td>
                            <?php if (!empty($app['reference_no'])): ?>
                                <div class="ref-number">
                                    REF: <?php echo htmlspecialchars($app['reference_no']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($app['application_date'])): ?>
                                <div class="business-info">
                                    📅 <?php echo date('M j, Y', strtotime($app['application_date'])); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($app['or_number'])): ?>
                                <div class="reference-info">
                                    OR: <?php echo htmlspecialchars($app['or_number']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($app['ctc_number'])): ?>
                                <div class="reference-info">
                                    CTC: <?php echo htmlspecialchars($app['ctc_number']); ?>
                                </div>
                            <?php endif; ?>
                            <?php
                                // Attempt to retrieve dedicated images (CTC and Business certificate)
                                $ctcImg = '';
                                $certImg = '';
                                try {
                                    // Try reading from business_attachments table if it exists
                                    $attachmentStmt = $pdo->prepare("SELECT ctc_image, certificate_image FROM business_attachments WHERE business_id = ? LIMIT 1");
                                    if ($attachmentStmt->execute([(int)$app['id']])) {
                                        $att = $attachmentStmt->fetch(PDO::FETCH_ASSOC);
                                        if ($att) {
                                            $ctcImg = $att['ctc_image'] ?? '';
                                            $certImg = $att['certificate_image'] ?? '';
                                        }
                                    }
                                } catch (Exception $e) {
                                    // Table may not exist – fall back to filesystem by reference_no prefix patterns
                                }

                                // Fallback: search upload dir using reference number
                                $uploadDir = realpath(__DIR__ . '/../assets/uploads/business_applications');
                                if ($uploadDir !== false && is_dir($uploadDir)) {
                                    $ref = $app['reference_no'] ?? '';
                                    if ($ref) {
                                        if (empty($ctcImg)) {
                                            foreach (glob($uploadDir . DIRECTORY_SEPARATOR . 'ctc_' . $ref . '*') as $path) {
                                                $ctcImg = basename($path); break;
                                            }
                                        }
                                        if (empty($certImg)) {
                                            foreach (glob($uploadDir . DIRECTORY_SEPARATOR . 'cert_' . $ref . '*') as $path) {
                                                $certImg = basename($path); break;
                                            }
                                        }
                                    }
                                }
                            ?>
                            <?php if (!empty($ctcImg)): ?>
                                <div class="business-info">
                                    🖼️ <a href="../assets/uploads/business_applications/<?php echo htmlspecialchars($ctcImg); ?>" target="_blank">View CTC Image</a>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($certImg)): ?>
                                <div class="business-info">
                                    🖼️ <a href="../assets/uploads/business_applications/<?php echo htmlspecialchars($certImg); ?>" target="_blank">View Business Certificate Image</a>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($app['proof_image'])): ?>
                                <div class="business-info">
                                    📎 <a href="../assets/uploads/business_applications/<?php echo htmlspecialchars($app['proof_image']); ?>" target="_blank">View Proof Image</a>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="business-details">
                                <div class="business-name">
                                    <?php echo htmlspecialchars($app['business_name']); ?>
                                </div>
                                <?php if (!empty($app['business_location'])): ?>
                                    <div class="business-info">
                                        📍 <?php echo htmlspecialchars(substr($app['business_location'], 0, 60)); ?><?php echo strlen($app['business_location']) > 60 ? '...' : ''; ?>
                                    </div>
                                <?php elseif (!empty($app['business_address'])): ?>
                                    <div class="business-info">
                                        📍 <?php echo htmlspecialchars(substr($app['business_address'], 0, 60)); ?><?php echo strlen($app['business_address']) > 60 ? '...' : ''; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php
                                // Derive full name (prefer explicit first/middle/last if present)
                                $display_full_name = trim(
                                    ($app['first_name'] ?? '') . ' ' .
                                    (!empty($app['middle_name']) ? $app['middle_name'] . ' ' : '') .
                                    ($app['last_name'] ?? '')
                                );
                                if ($display_full_name === '') {
                                    $display_full_name = trim(
                                        ($app['res_first_name'] ?? '') . ' ' .
                                        (!empty($app['res_middle_name']) ? $app['res_middle_name'] . ' ' : '') .
                                        ($app['res_last_name'] ?? '')
                                    );
                                }

                                // Contact number preference (avoid placeholder)
                                $display_contact = (!empty($app['contact_number']) && $app['contact_number'] !== '09000000000')
                                    ? $app['contact_number']
                                    : ($app['resident_phone'] ?? '');

                                // Address preference: owner_address -> resident address
                                $display_address = $app['owner_address'] ?? '';
                                if (empty($display_address)) {
                                    $display_address = $app['resident_address'] ?? '';
                                }

                                // Birthdate, age computation
                                $birthdate = $app['resident_birthdate'] ?? null;
                                $age_text = '';
                                if (!empty($birthdate)) {
                                    try {
                                        $dob = new DateTime($birthdate);
                                        $now = new DateTime();
                                        $age = $dob->diff($now)->y;
                                        $age_text = $dob->format('M j, Y') . ' (Age ' . $age . ')';
                                    } catch (Exception $e) {
                                        $age_text = htmlspecialchars((string)$birthdate);
                                    }
                                }
                            ?>
                            <div class="owner-details">
                                <div class="owner-name">
                                    <?php echo htmlspecialchars($display_full_name ?: ($app['owner_name'] ?? '')); ?>
                                </div>
                                <?php if (!empty($display_address)): ?>
                                    <div class="business-info">
                                        🏠 <?php echo htmlspecialchars($display_address); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($display_contact)): ?>
                                    <div class="contact-info">
                                        📞 <?php echo htmlspecialchars($display_contact); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($app['resident_civil_status'])): ?>
                                    <div class="business-info">💍 <?php echo htmlspecialchars($app['resident_civil_status']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($app['resident_gender'])): ?>
                                    <div class="business-info">⚧ <?php echo htmlspecialchars($app['resident_gender']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($age_text)): ?>
                                    <div class="business-info">🎂 <?php echo $age_text; ?></div>
                                <?php endif; ?>
                                <?php if (!empty($app['resident_birth_place'])): ?>
                                    <div class="business-info">🗺️ <?php echo htmlspecialchars($app['resident_birth_place']); ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo htmlspecialchars($app['status']); ?>">
                                <?php echo htmlspecialchars(business_application_status_label($app['status'])); ?>
                                <?php if ($app['status'] === 'received'): ?> ✓<?php endif; ?>
                            </span>
                        </td>
                        <td>
                            <div class="business-info">
                                <?php echo date('M j, Y', strtotime($app['submitted_at'])); ?>
                            </div>
                            <div class="business-info">
                                <?php echo date('g:i A', strtotime($app['submitted_at'])); ?>
                            </div>
                        </td>
                        <td class="view-column">
                            <a
                                href="get-business-application-summary.php?id=<?php echo (int)$app['id']; ?>"
                                target="_blank"
                                class="view-form-btn"
                                title="Open summary in new tab"
                            >
                                👁️ View Summary
                            </a>
                            <?php if ($app['status'] === 'approved'): ?>
                                <button type="button"
                                    class="print-clearance-btn js-print-biz-clearance-btn"
                                    data-application-id="<?php echo (int) $app['id']; ?>"
                                    data-business-name="<?php echo htmlspecialchars((string) ($app['business_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    🖨️ Print Clearance
                                </button>
                            <?php endif; ?>
                        </td>
                        <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                        <td class="action-column">
                            <?php if ($app['status'] !== 'received'): ?>
                            <form method="POST" style="margin-bottom: 0.5rem;">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo $app['id']; ?>">
                                <input type="hidden" name="tab" value="<?php echo htmlspecialchars($active_tab); ?>">
                                <select name="status" class="action-select" onchange="this.form.submit()">
                                    <?php
                                    $cur = $app['status'];
                                    echo '<option value="' . htmlspecialchars($cur) . '" selected>'
                                        . htmlspecialchars(business_application_status_label($cur)) . '</option>';
                                    foreach ($ba_statuses as $st) {
                                        if ($st !== $cur && business_application_can_transition($cur, $st)) {
                                            echo '<option value="' . htmlspecialchars($st) . '">→ '
                                                . htmlspecialchars(business_application_status_label($st)) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </form>
                            <?php else: ?>
                            <div class="status-locked">Received by Resident</div>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (empty($applications)): ?>
            <div style="text-align: center; padding: 3rem;">
                <?php if ($active_tab === 'received'): ?>
                    <h3 style="color: #666; margin-bottom: 1rem;">✅ No Ready or Received Applications</h3>
                    <p style="color: #999; margin-bottom: 1.5rem;">No applications ready for pickup or marked received match your filters.</p>
                    <a href="view-business-applications.php?tab=received" class="admin-btn">Clear Filters</a>
                <?php else: ?>
                    <h3 style="color: #666; margin-bottom: 1rem;">📭 No Active Applications Found</h3>
                    <p style="color: #999; margin-bottom: 1.5rem;">No active business applications match your current filters.</p>
                    <a href="view-business-applications.php?tab=active" class="admin-btn">Clear Filters</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page-1; ?>&tab=<?php echo urlencode($active_tab); ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">« Previous</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&tab=<?php echo urlencode($active_tab); ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page+1; ?>&tab=<?php echo urlencode($active_tab); ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">Next »</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <div id="bizPrintModal" class="cert-print-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="bizPrintModalTitle">
        <div class="cert-print-modal" onclick="event.stopPropagation()">
            <div class="cert-print-modal-header">
                <h3 id="bizPrintModalTitle">Print business clearance</h3>
                <div class="cert-print-modal-actions">
                    <button type="button" class="btn-print" onclick="printBusinessClearanceFromModal()">🖨️ Print</button>
                    <button type="button" class="btn-close" onclick="closeBusinessClearancePrintModal()">Close</button>
                </div>
            </div>
            <div class="cert-print-modal-body" id="bizPrintModalBody">
                <div id="bizPrintError" class="cert-print-error" role="alert"></div>
                <div class="cert-print-loading" id="bizPrintLoading">Loading clearance preview…</div>
                <iframe id="bizPrintFrame" title="Business clearance print preview"></iframe>
            </div>
        </div>
    </div>

    <script>
        function showBizPrintError(message) {
            const err = document.getElementById('bizPrintError');
            const frame = document.getElementById('bizPrintFrame');
            if (err) {
                err.textContent = message;
                err.classList.add('visible');
            }
            if (frame) {
                frame.style.display = 'none';
            }
        }

        function clearBizPrintError() {
            const err = document.getElementById('bizPrintError');
            const frame = document.getElementById('bizPrintFrame');
            if (err) {
                err.textContent = '';
                err.classList.remove('visible');
            }
            if (frame) {
                frame.style.display = 'block';
            }
        }

        function openBusinessClearancePrint(applicationId, businessName) {
            const modal = document.getElementById('bizPrintModal');
            const frame = document.getElementById('bizPrintFrame');
            const title = document.getElementById('bizPrintModalTitle');
            const body = document.getElementById('bizPrintModalBody');
            const loading = document.getElementById('bizPrintLoading');
            if (!modal || !frame) {
                alert('Print preview is not available. Please refresh the page and try again.');
                return;
            }

            if (title) {
                title.textContent = 'Print: ' + (businessName || 'Business Clearance') + ' (Application #' + applicationId + ')';
            }
            clearBizPrintError();
            if (body) {
                body.classList.remove('loaded');
            }
            if (loading) {
                loading.style.display = 'flex';
            }

            const printUrl = new URL('print-business-clearance.php', window.location.href);
            printUrl.searchParams.set('id', String(applicationId));
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
                showBizPrintError('Failed to load the business clearance preview. Please try again.');
            };

            frame.src = printUrl.href;
            modal.classList.add('open');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeBusinessClearancePrintModal() {
            const modal = document.getElementById('bizPrintModal');
            const frame = document.getElementById('bizPrintFrame');
            const body = document.getElementById('bizPrintModalBody');
            const loading = document.getElementById('bizPrintLoading');
            if (modal) {
                modal.classList.remove('open');
                modal.style.display = 'none';
            }
            document.body.style.overflow = '';
            clearBizPrintError();
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

        function printBusinessClearanceFromModal() {
            const frame = document.getElementById('bizPrintFrame');
            const err = document.getElementById('bizPrintError');
            if (err && err.classList.contains('visible')) {
                return;
            }
            if (!frame || !frame.src || frame.src === 'about:blank') {
                alert('Clearance preview is still loading. Please wait a moment and try again.');
                return;
            }
            try {
                const win = frame.contentWindow;
                if (win) {
                    win.focus();
                    win.print();
                }
            } catch (e) {
                alert('Could not open the print dialog. Please try again.');
            }
        }

        function switchBusinessTab(tab) {
            try {
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                url.searchParams.delete('page');
                url.searchParams.delete('status');
                url.searchParams.delete('search');
                window.location.href = url.toString();
            } catch (e) {
                window.location.href = 'view-business-applications.php?tab=' + encodeURIComponent(tab);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('bizPrintModal');
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.js-print-biz-clearance-btn');
                if (btn) {
                    e.preventDefault();
                    openBusinessClearancePrint(
                        btn.getAttribute('data-application-id'),
                        btn.getAttribute('data-business-name')
                    );
                }
            });

            modal?.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeBusinessClearancePrintModal();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && modal && modal.classList.contains('open')) {
                    closeBusinessClearancePrintModal();
                }
            });
        });
    </script>
</body>
</html>
