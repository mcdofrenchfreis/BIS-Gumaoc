<?php
session_start();
include '../includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle status updates
if ($_POST['action'] ?? '' === 'update_status' && isset($_POST['id'], $_POST['status'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    $allowed_statuses = ['pending', 'reviewing', 'approved', 'rejected'];
    
    if (in_array($status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE business_applications SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        $_SESSION['success'] = "Status updated successfully!";
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
$params = [];

if ($status_filter && in_array($status_filter, ['pending', 'reviewing', 'approved', 'rejected'])) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(owner_name LIKE ? OR business_name LIKE ? OR business_type LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count
$count_sql = "SELECT COUNT(*) FROM business_applications $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Get records
$sql = "SELECT * FROM business_applications $where_clause ORDER BY submitted_at DESC LIMIT $per_page OFFSET $offset";
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
    <div class="admin-container">
        <div class="admin-header">
            <div>
                <h1>🏢 Business Applications</h1>
                <p>Total: <?php echo $total_records; ?> applications</p>
            </div>
            <a href="dashboard.php" class="admin-btn">← Back to Dashboard</a>
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
                        <th class="action-column">Actions</th>
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
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="owner-details">
                                <div class="owner-name">
                                    <?php 
                                    if (!empty($app['first_name']) || !empty($app['last_name'])) {
                                        echo htmlspecialchars(trim($app['first_name'] . ' ' . ($app['middle_name'] ? $app['middle_name'] . ' ' : '') . $app['last_name']));
                                    } else {
                                        echo htmlspecialchars($app['owner_name']);
                                    }
                                    ?>
                                </div>
                                <?php if (!empty($app['contact_number']) && $app['contact_number'] !== '09000000000'): ?>
                                    <div class="contact-info">
                                        📞 <?php echo htmlspecialchars($app['contact_number']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($app['owner_address'])): ?>
                                    <div class="business-info">
                                        🏠 <?php echo htmlspecialchars(substr($app['owner_address'], 0, 50)); ?><?php echo strlen($app['owner_address']) > 50 ? '...' : ''; ?>
                                    </div>
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
                            <button onclick="viewFormDetails(<?php echo $app['id']; ?>)" class="view-form-btn">
                                👁️ View Form
                            </button>
                        </td>
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
                            <?php if ($app['status'] === 'approved'): ?>
                                <a href="generate-business-clearance.php?id=<?php echo $app['id']; ?>" class="admin-btn" target="_blank" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">
                                    📄 Generate Clearance
                                </a>
                            <?php endif; ?>
                        </td>
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

    <script>
    function viewFormDetails(applicationId) {
        // Open the business application form in a new tab instead of popup window
        window.open('../pages/business-application.php?admin_view=' + applicationId + '&readonly=1', '_blank');
    }
    </script>
</body>
</html>
