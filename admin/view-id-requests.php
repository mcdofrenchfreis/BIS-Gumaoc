<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle approve/reject
if (($_POST['action'] ?? '') && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $newStatus = $_POST['action'] === 'approve' ? 'approved' : 'rejected';
    $stmt = $pdo->prepare("UPDATE id_card_requests SET status = ?, reviewed_at = NOW(), reviewed_by = ? WHERE id = ?");
    $stmt->execute([$newStatus, $_SESSION['admin_id'] ?? null, $id]);
    if ($newStatus === 'approved') {
        $rid = (int)($_POST['resident_id'] ?? 0);
        header('Location: ../pages/print-id.php?resident_id=' . $rid . '&request_id=' . $id . '&auto=1');
        exit;
    }
}


// Filters & pagination
$status = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');
$sort_by = $_GET['sort_by'] ?? 'submitted_at';
$sort_order = $_GET['sort_order'] ?? 'DESC';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

$where = [];
$params = [];
if ($status && in_array($status, ['pending','approved','rejected'])) {
	$where[] = 'status = ?';
	$params[] = $status;
}
if ($search !== '') {
	$where[] = '(full_name LIKE ? OR CAST(resident_id AS CHAR) LIKE ? OR CAST(user_id AS CHAR) LIKE ?)';
	$like = "%$search%";
	$params[] = $like; $params[] = $like; $params[] = $like;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Summary counts
$summary = $pdo->query("SELECT 
	SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) AS pending,
	SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) AS approved,
	SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) AS rejected,
	COUNT(*) AS total
FROM id_card_requests")->fetch(PDO::FETCH_ASSOC);

// Total rows
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM id_card_requests $whereSql");
$countStmt->execute($params);
$total_rows = (int)$countStmt->fetchColumn();
$total_pages = max(1, (int)ceil($total_rows / $per_page));

// Validate sort parameters
$allowed_sort_columns = ['id', 'full_name', 'user_id', 'status', 'submitted_at', 'reviewed_at'];
$allowed_sort_orders = ['ASC', 'DESC'];

if (!in_array($sort_by, $allowed_sort_columns)) {
    $sort_by = 'submitted_at';
}
if (!in_array($sort_order, $allowed_sort_orders)) {
    $sort_order = 'DESC';
}

// Paged data
$stmt = $pdo->prepare("SELECT * FROM id_card_requests $whereSql ORDER BY $sort_by $sort_order LIMIT $per_page OFFSET $offset");
$stmt->execute($params);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Card Requests - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 0;
            position: relative;
        }

        /* Admin container to match other admin pages */
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            padding-top: 90px; /* offset for fixed admin mini nav */
            min-height: 100vh;
            box-sizing: border-box;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 25px;
            box-shadow: 0 25px 80px rgba(0, 100, 0, 0.15);
            overflow: hidden;
            position: relative;
            border: 3px solid rgba(0, 100, 0, 0.1);
        }
        
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, #006400, #228B22, #32CD32, #4CAF50, #66BB6A);
        }
        
        .header {
            background: linear-gradient(135deg, #006400 0%, #228B22 50%, #32CD32 100%);
            color: white;
            padding: 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.1;
        }
        
        .title {
            font-size: 32px;
            font-weight: 800;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 20px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .title i {
            font-size: 36px;
            opacity: 0.95;
            color: #90EE90;
        }
        
        .back-btn {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            text-decoration: none;
            padding: 15px 25px;
            border-radius: 50px;
            font-weight: 700;
            transition: all 0.4s ease;
            border: 2px solid rgba(255, 255, 255, 0.4);
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 10px;
            backdrop-filter: blur(10px);
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.35);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.6);
        }
        
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            padding: 35px;
            background: linear-gradient(135deg, #f8fff8 0%, #f0f8f0 100%);
        }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 100, 0, 0.1);
            border: 2px solid rgba(0, 100, 0, 0.05);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #006400, #228B22, #32CD32);
        }
        
        .card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 50px rgba(0, 100, 0, 0.2);
            border-color: rgba(0, 100, 0, 0.2);
        }
        
        .card h4 {
            font-size: 36px;
            font-weight: 800;
            color: #006400;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 100, 0, 0.1);
        }
        
        .card p {
            color: #4a7c59;
            font-size: 15px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .filters-container {
            background: linear-gradient(135deg, #ffffff 0%, #f8fff8 100%);
            padding: 30px 35px;
            border-bottom: 2px solid rgba(0, 100, 0, 0.1);
        }
        
        .filters {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: end;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .filter-group label {
            font-weight: 700;
            color: #2d5a3d;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .filter-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            padding-top: 10px;
            border-top: 1px solid rgba(0, 100, 0, 0.1);
        }
        
        .filters input, .filters select {
            padding: 15px 20px;
            border: 2px solid rgba(0, 100, 0, 0.2);
            border-radius: 15px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: white;
            color: #2d5a3d;
        }
        
        .filters input:focus, .filters select:focus {
            outline: none;
            border-color: #228B22;
            box-shadow: 0 0 0 4px rgba(34, 139, 34, 0.15);
            transform: translateY(-2px);
        }
        
        .filters input {
            min-width: 280px;
        }
        
        .btn {
            border: none;
            padding: 15px 25px;
            border-radius: 15px;
            cursor: pointer;
            font-weight: 700;
            font-size: 15px;
            transition: all 0.4s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn-apply {
            background: linear-gradient(135deg, #228B22, #32CD32);
            color: white;
            box-shadow: 0 6px 20px rgba(34, 139, 34, 0.4);
        }
        
        .btn-apply:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(34, 139, 34, 0.5);
        }
        
        .btn-reset {
            background: linear-gradient(135deg, #f1f8e9, #e8f5e8);
            color: #4a7c59;
            border: 2px solid rgba(0, 100, 0, 0.2);
        }
        
        .btn-reset:hover {
            background: linear-gradient(135deg, #e8f5e8, #dcedc8);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 100, 0, 0.2);
        }
        
        .table-container {
            overflow-x: auto;
            padding: 0 35px 35px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 100, 0, 0.1);
            border: 2px solid rgba(0, 100, 0, 0.05);
        }
        
        th, td {
            padding: 25px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 100, 0, 0.1);
        }
        
        th {
            background: linear-gradient(135deg, #f8fff8, #e8f5e8);
            color: #2d5a3d;
            font-weight: 700;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        td {
            font-size: 15px;
            color: #4a7c59;
            font-weight: 500;
        }
        
        tr:hover {
            background: linear-gradient(135deg, #f8fff8, #f0f8f0);
        }
        
        .badge {
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .badge.pending {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            color: #b8860b;
            border: 2px solid #f39c12;
        }
        
        .badge.approved {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #047857;
            border: 2px solid #10b981;
        }
        
        .badge.rejected {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #b91c1c;
            border: 2px solid #ef4444;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-preview {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 10px 18px;
            font-size: 13px;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .btn-preview:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }
        
        .btn-approve {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 10px 18px;
            font-size: 13px;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .btn-approve:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }
        
        .btn-approve:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            opacity: 0.6;
            box-shadow: none;
        }
        
        .btn-reject {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 10px 18px;
            font-size: 13px;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        
        .btn-reject:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }
        
        .btn-reject:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            opacity: 0.6;
            box-shadow: none;
        }
        
        .pagination {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 35px;
            padding: 0 35px 35px;
        }
        
        .pagination a, .pagination span {
            padding: 15px 20px;
            border: 2px solid rgba(0, 100, 0, 0.2);
            border-radius: 15px;
            text-decoration: none;
            color: #4a7c59;
            font-weight: 700;
            transition: all 0.3s ease;
            background: white;
        }
        
        .pagination a:hover {
            background: linear-gradient(135deg, #f8fff8, #e8f5e8);
            border-color: #228B22;
            color: #228B22;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(34, 139, 34, 0.2);
        }
        
        .pagination .current {
            background: linear-gradient(135deg, #228B22, #32CD32);
            color: white;
            border-color: #228B22;
            box-shadow: 0 6px 20px rgba(34, 139, 34, 0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 35px;
            color: #4a7c59;
        }
        
        .empty-state i {
            font-size: 80px;
            margin-bottom: 25px;
            opacity: 0.6;
            color: #a5d6a7;
        }
        
        .empty-state h3 {
            font-size: 28px;
            margin-bottom: 15px;
            color: #2d5a3d;
            font-weight: 700;
        }
        
        .empty-state p {
            font-size: 18px;
            color: #4a7c59;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .header {
                flex-direction: column;
                gap: 25px;
                text-align: center;
                padding: 30px 25px;
            }
            
            .title {
                font-size: 28px;
            }
            
            .summary {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
                padding: 25px;
            }
            
            .card {
                padding: 25px;
            }
            
            .card h4 {
                font-size: 32px;
            }
            
            .filters-container {
                padding: 25px;
            }
            
            .filter-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .filter-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filters input, .filters select {
                width: 100%;
            }
            
            .table-container {
                padding: 0 25px 25px;
            }
            
            th, td {
                padding: 20px 15px;
            }
            
            .actions {
                flex-direction: column;
                gap: 8px;
            }
            
            .btn {
                padding: 12px 18px;
                font-size: 12px;
            }
        }
        
        @media (max-width: 480px) {
            .summary {
                grid-template-columns: 1fr;
            }
            
            .title {
                font-size: 24px;
            }
            
            .card h4 {
                font-size: 28px;
            }
        }
        
    </style>
</head>
<body>
    <?php $base_path = '../'; include __DIR__ . '/../includes/admin_mini_nav.php'; ?>
    <div class="admin-container">
        <div class="header">
            <div class="title">
                <i class="fas fa-id-card"></i>
                ID Card Requests
            </div>
        </div>
        
        <!-- Admin Mini Navigation included above -->
        
        <?php if (isset($success_message)): ?>
        <div style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #047857; padding: 15px 20px; margin: 20px 35px; border-radius: 10px; border: 2px solid #10b981; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-check-circle" style="font-size: 18px;"></i>
            <strong><?php echo htmlspecialchars($success_message); ?></strong>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
        <div style="background: linear-gradient(135deg, #fee2e2, #fecaca); color: #b91c1c; padding: 15px 20px; margin: 20px 35px; border-radius: 10px; border: 2px solid #ef4444; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-exclamation-circle" style="font-size: 18px;"></i>
            <strong><?php echo htmlspecialchars($error_message); ?></strong>
        </div>
        <?php endif; ?>
        
        <div class="summary">
            <div class="card"><h4><?php echo (int)$summary['total']; ?></h4><p>Total</p></div>
            <div class="card"><h4><?php echo (int)$summary['pending']; ?></h4><p>Pending</p></div>
            <div class="card"><h4><?php echo (int)$summary['approved']; ?></h4><p>Approved</p></div>
            <div class="card"><h4><?php echo (int)$summary['rejected']; ?></h4><p>Rejected</p></div>
        </div>
        <div class="filters-container">
        <form class="filters" method="get">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="search">🔍 Search</label>
                        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search name or ID...">
                    </div>
                    
                    <div class="filter-group">
                        <label for="status">📊 Status</label>
                        <select id="status" name="status">
                <option value="">All Status</option>
                            <option value="pending" <?php echo $status==='pending'?'selected':''; ?>>⏳ Pending</option>
                            <option value="approved" <?php echo $status==='approved'?'selected':''; ?>>✅ Approved</option>
                            <option value="rejected" <?php echo $status==='rejected'?'selected':''; ?>>❌ Rejected</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="sort_by">📋 Sort By</label>
                        <select id="sort_by" name="sort_by">
                            <option value="submitted_at" <?php echo $sort_by==='submitted_at'?'selected':''; ?>>📅 Date Submitted</option>
                            <option value="full_name" <?php echo $sort_by==='full_name'?'selected':''; ?>>👤 Name</option>
                            <option value="user_id" <?php echo $sort_by==='user_id'?'selected':''; ?>>🆔 User ID</option>
                            <option value="status" <?php echo $sort_by==='status'?'selected':''; ?>>📊 Status</option>
                            <option value="id" <?php echo $sort_by==='id'?'selected':''; ?>># ID</option>
                            <option value="reviewed_at" <?php echo $sort_by==='reviewed_at'?'selected':''; ?>>✅ Review Date</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="sort_order">🔄 Order</label>
                        <select id="sort_order" name="sort_order">
                            <option value="DESC" <?php echo $sort_order==='DESC'?'selected':''; ?>>⬇️ Descending</option>
                            <option value="ASC" <?php echo $sort_order==='ASC'?'selected':''; ?>>⬆️ Ascending</option>
            </select>
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button class="btn btn-apply" type="submit">
                        <i class="fas fa-search"></i>
                        Apply Filters
                    </button>
                    <a class="btn btn-reset" href="view-id-requests.php">
                        <i class="fas fa-undo"></i>
                        Reset All
                    </a>
                </div>
        </form>
        </div>
        <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Resident ID</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                <tr>
                    <td><?php echo (int)$r['id']; ?></td>
                    <td><?php echo htmlspecialchars($r['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($r['user_id'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($r['submitted_at']); ?></td>
                    <td><span class="badge <?php echo htmlspecialchars($r['status']); ?>"><?php echo htmlspecialchars(ucfirst($r['status'])); ?></span></td>
                    <td>
                        <form method="post" class="actions">
                            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                            <input type="hidden" name="resident_id" value="<?php echo (int)($r['user_id'] ?? 0); ?>">
                            <a class="btn btn-preview" target="_blank" href="../pages/print-id.php?resident_id=<?php echo (int)($r['user_id'] ?? 0); ?>&request_id=<?php echo (int)$r['id']; ?>">
                                <i class="fas fa-eye"></i>
                                Preview
                            </a>
                            <button class="btn btn-approve" name="action" value="approve" <?php echo $r['status'] !== 'pending' ? 'disabled' : ''; ?>>
                                <i class="fas fa-check"></i>
                                Approve & Print
                            </button>
                            <button class="btn btn-reject" name="action" value="reject" <?php echo $r['status'] !== 'pending' ? 'disabled' : ''; ?>>
                                <i class="fas fa-times"></i>
                                Reject
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php if (empty($requests)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>No ID Requests Found</h3>
            <p>There are currently no ID card requests matching your criteria.</p>
        </div>
        <?php endif; ?>
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i=1; $i <= $total_pages; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>&sort_by=<?php echo urlencode($sort_by); ?>&sort_order=<?php echo urlencode($sort_order); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
