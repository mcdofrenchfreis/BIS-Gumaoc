<?php
session_start();
include '../includes/db_connect.php';

// Handle status updates (only if admin is logged in)
if ($_POST['action'] ?? '' === 'update_status' && isset($_POST['id'], $_POST['status'])) {
    // Check if admin is logged in for status updates
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        $id = (int)$_POST['id'];
        $status = $_POST['status'];
        $allowed_statuses = ['pending', 'reviewing', 'approved', 'rejected'];
        
        if (in_array($status, $allowed_statuses)) {
            $stmt = $pdo->prepare("UPDATE business_applications SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            $_SESSION['success'] = "Status updated successfully!";
        }
    }
    header('Location: view-business-applications.php');
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
$where_conditions_ba = [];
$params = [];

if ($status_filter && in_array($status_filter, ['pending', 'reviewing', 'approved', 'rejected'])) {
    $where_conditions[] = "status = ?"; // for count query (no alias)
    $where_conditions_ba[] = "ba.status = ?"; // for main query (with alias)
    $params[] = $status_filter;
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
            r.birth_place AS resident_birth_place,
            cr.years_of_residence AS cr_years_of_residence,
            cr.purpose AS cr_purpose
        FROM business_applications ba
        LEFT JOIN residents r ON ba.user_id = r.id
        LEFT JOIN certificate_requests cr
          ON cr.user_id = ba.user_id
         AND cr.submitted_at = (
              SELECT MAX(cr2.submitted_at)
                FROM certificate_requests cr2
               WHERE cr2.user_id = ba.user_id
            )
        $where_clause_ba
        ORDER BY ba.submitted_at DESC
        LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();
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
        
        <div class="admin-controls">
            <form method="GET" class="search-form">
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="reviewing" <?php echo $status_filter === 'reviewing' ? 'selected' : ''; ?>>Reviewing</option>
                    <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
                
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by owner name, business name, or type...">
                <button type="submit" class="admin-btn">🔍 Search</button>
                <a href="view-business-applications.php" class="admin-btn">🔄 Clear</a>
            </form>
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
                                <?php if (!empty($app['cr_years_of_residence'])): ?>
                                    <div class="business-info">📅 Years of Residence: <?php echo htmlspecialchars($app['cr_years_of_residence']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($app['cr_purpose'])): ?>
                                    <div class="business-info">📝 Purpose: <?php echo htmlspecialchars($app['cr_purpose']); ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $app['status']; ?>">
                                <?php echo ucfirst($app['status']); ?>
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
                                <a href="generate-business-clearance.php?id=<?php echo $app['id']; ?>" class="print-clearance-btn" target="_blank">
                                    🖨️ Print Clearance
                                </a>
                            <?php endif; ?>
                        </td>
                        <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                        <td class="action-column">
                            <form method="POST" style="margin-bottom: 0.5rem;">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo $app['id']; ?>">
                                <select name="status" class="action-select" onchange="this.form.submit()">
                                    <option value="pending" <?php echo $app['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="reviewing" <?php echo $app['status'] === 'reviewing' ? 'selected' : ''; ?>>Reviewing</option>
                                    <option value="approved" <?php echo $app['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo $app['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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

    
</body>
</html>
