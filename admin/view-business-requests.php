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
    $allowed_statuses = ['pending', 'processing', 'approved', 'rejected'];
    
    if (in_array($status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE business_applications SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        $_SESSION['success'] = "Status updated successfully!";
    }
    header('Location: view-business-requests.php');
    exit;
}

// Get filter and search parameters
$status_filter = $_GET['status'] ?? '';
$business_type = $_GET['business_type'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = [];
$params = [];

if ($status_filter && in_array($status_filter, ['pending', 'processing', 'approved', 'rejected'])) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if ($business_type) {
    $where_conditions[] = "business_type = ?";
    $params[] = $business_type;
}

if ($search) {
    $where_conditions[] = "(business_name LIKE ? OR owner_name LIKE ?)";
    $search_term = "%$search%";
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
$requests = $stmt->fetchAll();

// Get business types for filter
$business_types = $pdo->query("SELECT DISTINCT business_type FROM business_applications ORDER BY business_type")->fetchAll(PDO::FETCH_COLUMN);
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
            background: linear-gradient(135deg, #28a745, #20c997);
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
        }
        
        .view-form-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }
        
        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #0066cc; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
        .business-type {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .action-select {
            padding: 0.3rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
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
        
        .admin-btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: linear-gradient(45deg, #4CAF50, #2196F3);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            border: none;
            cursor: pointer;
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
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <div class="admin-controls">
            <form method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
                
                <select name="business_type" onchange="this.form.submit()">
                    <option value="">All Business Types</option>
                    <?php foreach ($business_types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $business_type === $type ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by business or owner name...">
                <button type="submit" class="admin-btn">Search</button>
                <a href="view-business-requests.php" class="admin-btn">Clear</a>
            </form>
        </div>
        
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Business Name</th>
                        <th>Business Type</th>
                        <th>Owner Name</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>View Form</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                    <tr>
                        <td><?php echo $req['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($req['business_name']); ?></strong>
                        </td>
                        <td>
                            <span class="business-type">
                                <?php echo htmlspecialchars($req['business_type']); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($req['owner_name']); ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $req['status']; ?>">
                                <?php echo ucfirst($req['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($req['submitted_at'])); ?></td>
                        <td>
                            <button onclick="viewFormDetails(<?php echo $req['id']; ?>)" class="view-form-btn">
                                👁️ View Form
                            </button>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo $req['id']; ?>">
                                <select name="status" class="action-select" onchange="this.form.submit()">
                                    <option value="pending" <?php echo $req['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $req['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="approved" <?php echo $req['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo $req['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (empty($requests)): ?>
            <div style="text-align: center; padding: 3rem;">
                <h3>📭 No Business Applications Found</h3>
                <p>No business applications match your current filters.</p>
                <a href="view-business-requests.php" class="admin-btn">Clear Filters</a>
            </div>
            <?php endif; ?>
        </div>
        
        <script>
        function viewFormDetails(requestId) {
            // Open the business application form with pre-filled data
            window.open('../pages/business-application.php?admin_view=' + requestId + '&readonly=1', '_blank');
        }
        </script>
        
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page-1; ?>&status=<?php echo $status_filter; ?>&business_type=<?php echo urlencode($business_type); ?>&search=<?php echo urlencode($search); ?>">« Previous</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&business_type=<?php echo urlencode($business_type); ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page+1; ?>&status=<?php echo $status_filter; ?>&business_type=<?php echo urlencode($business_type); ?>&search=<?php echo urlencode($search); ?>">Next »</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>