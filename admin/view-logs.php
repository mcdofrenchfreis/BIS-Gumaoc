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

// Get filter parameters
$admin_filter = $_GET['admin_id'] ?? '';
$action_filter = $_GET['action_type'] ?? '';
$target_filter = $_GET['target_type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build WHERE conditions for direct SQL query
$whereConditions = [];
$params = [];

if (!empty($admin_filter)) {
    $whereConditions[] = "admin_id = ?";
    $params[] = $admin_filter;
}

if (!empty($action_filter)) {
    $whereConditions[] = "action_type = ?";
    $params[] = $action_filter;
}

if (!empty($target_filter)) {
    $whereConditions[] = "target_type = ?";
    $params[] = $target_filter;
}

if (!empty($date_from)) {
    $whereConditions[] = "created_at >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $whereConditions[] = "created_at <= ?";
    $params[] = $date_to . ' 23:59:59';
}

$whereClause = $whereConditions ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Get logs with direct SQL query - MariaDB compatible
$count_sql = "SELECT COUNT(*) FROM admin_logs $whereClause";
$sql = "SELECT * FROM admin_logs $whereClause ORDER BY created_at DESC LIMIT $offset, $per_page";

// Execute count query
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_logs = $count_stmt->fetchColumn();

// Execute main query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_pages = ceil($total_logs / $per_page);

// Get distinct values for filters
$distinct_admins = $pdo->query("SELECT DISTINCT admin_id FROM admin_logs ORDER BY admin_id")->fetchAll(PDO::FETCH_COLUMN);
$distinct_actions = $pdo->query("SELECT DISTINCT action_type FROM admin_logs ORDER BY action_type")->fetchAll(PDO::FETCH_COLUMN);
$distinct_targets = $pdo->query("SELECT DISTINCT target_type FROM admin_logs ORDER BY target_type")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .admin-container {
            max-width: 1600px;
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
        
        .filters-container {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .filters-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }
        
        .filter-group label {
            font-weight: 600;
            color: #2e7d32;
            font-size: 0.9rem;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 0.6rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
        }
        
        .filter-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-top: 1rem;
        }
        
        .logs-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .logs-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .logs-table th,
        .logs-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        .logs-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2e7d32;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .logs-table tbody tr {
            transition: all 0.3s ease;
        }
        
        .logs-table tbody tr:hover {
            background: rgba(76, 175, 80, 0.05);
        }
        
        .action-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }
        
        .action-status_update { background: #e3f2fd; color: #1976d2; }
        .action-form_submission { background: #e8f5e8; color: #2e7d32; }
        .action-admin_login { background: #fff3e0; color: #f57c00; }
        .action-admin_logout { background: #fce4ec; color: #c2185b; }
        .action-form_view { background: #f3e5f5; color: #7b1fa2; }
        .action-print_action { background: #e0f2f1; color: #00695c; }
        .action-page_view { background: #f1f8e9; color: #33691e; }
        .action-content_update { background: #fff8e1; color: #f57f17; }
        .action-content_create { background: #e8f5e8; color: #2e7d32; }
        .action-content_delete { background: #ffebee; color: #c62828; }
        
        .target-badge {
            background: #f5f5f5;
            color: #333;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .admin-badge {
            background: linear-gradient(135deg, #4caf50, #2e7d32);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 16px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .log-description {
            max-width: 300px;
            font-size: 0.9rem;
            line-height: 1.4;
            color: #333;
        }
        
        .log-timestamp {
            font-size: 0.85rem;
            color: #666;
            white-space: nowrap;
        }
        
        .details-toggle {
            background: none;
            border: none;
            color: #4caf50;
            cursor: pointer;
            font-size: 0.8rem;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .details-toggle:hover {
            background: rgba(76, 175, 80, 0.1);
        }
        
        .details-content {
            display: none;
            background: #f8f9fa;
            padding: 0.8rem;
            border-radius: 8px;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #555;
            border-left: 3px solid #4caf50;
        }
        
        .details-content.show {
            display: block;
        }
        
        .ip-address {
            font-family: 'Courier New', monospace;
            background: #f0f0f0;
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: #2e7d32;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .admin-btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .admin-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }
        
        .admin-btn.secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
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
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .admin-container {
                padding: 1rem;
            }
            
            .filters-row {
                grid-template-columns: 1fr;
            }
            
            .filter-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .logs-table {
                overflow-x: auto;
            }
            
            .logs-table table {
                min-width: 800px;
            }
            
            .logs-table th,
            .logs-table td {
                padding: 0.8rem 0.5rem;
                font-size: 0.85rem;
            }
            
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <div>
                <h1>📊 System Activity Logs</h1>
                <p>Total: <?php echo number_format($total_logs); ?> log entries</p>
            </div>
            <a href="dashboard.php" class="admin-btn">← Back to Dashboard</a>
        </div>
        
        <!-- Statistics -->
        <div class="stats-row">
            <?php
            $today_count = $pdo->query("SELECT COUNT(*) FROM admin_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
            $week_count = $pdo->query("SELECT COUNT(*) FROM admin_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
            $month_count = $pdo->query("SELECT COUNT(*) FROM admin_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
            $unique_admins = $pdo->query("SELECT COUNT(DISTINCT admin_id) FROM admin_logs")->fetchColumn();
            ?>
            <div class="stat-card">
                <div class="stat-number"><?php echo $today_count; ?></div>
                <div class="stat-label">Today's Activities</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $week_count; ?></div>
                <div class="stat-label">This Week</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $month_count; ?></div>
                <div class="stat-label">This Month</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $unique_admins; ?></div>
                <div class="stat-label">Active Admins</div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters-container">
            <form method="GET" action="">
                <div class="filters-row">
                    <div class="filter-group">
                        <label for="admin_id">Admin User</label>
                        <select name="admin_id" id="admin_id">
                            <option value="">All Admins</option>
                            <?php foreach ($distinct_admins as $admin): ?>
                                <option value="<?php echo htmlspecialchars($admin); ?>" <?php echo $admin_filter === $admin ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($admin); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="action_type">Action Type</label>
                        <select name="action_type" id="action_type">
                            <option value="">All Actions</option>
                            <?php foreach ($distinct_actions as $action): ?>
                                <option value="<?php echo htmlspecialchars($action); ?>" <?php echo $action_filter === $action ? 'selected' : ''; ?>>
                                    <?php echo ucwords(str_replace('_', ' ', $action)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="target_type">Target Type</label>
                        <select name="target_type" id="target_type">
                            <option value="">All Types</option>
                            <?php foreach ($distinct_targets as $target): ?>
                                <option value="<?php echo htmlspecialchars($target); ?>" <?php echo $target_filter === $target ? 'selected' : ''; ?>>
                                    <?php echo ucwords(str_replace('_', ' ', $target)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="date_from">Date From</label>
                        <input type="date" name="date_from" id="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="date_to">Date To</label>
                        <input type="date" name="date_to" id="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="admin-btn">🔍 Apply Filters</button>
                    <a href="view-logs.php" class="admin-btn secondary">🔄 Clear Filters</a>
                </div>
            </form>
        </div>
        
        <!-- Logs Table -->
        <div class="logs-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Timestamp</th>
                        <th>Admin</th>
                        <th>Action</th>
                        <th>Target</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><strong>#<?php echo $log['id']; ?></strong></td>
                        <td class="log-timestamp">
                            <?php echo date('M j, Y', strtotime($log['created_at'])); ?><br>
                            <small><?php echo date('g:i:s A', strtotime($log['created_at'])); ?></small>
                        </td>
                        <td>
                            <span class="admin-badge">
                                <?php echo htmlspecialchars($log['admin_id']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="action-badge action-<?php echo $log['action_type']; ?>">
                                <?php echo ucwords(str_replace('_', ' ', $log['action_type'])); ?>
                            </span>
                        </td>
                        <td>
                            <span class="target-badge">
                                <?php echo htmlspecialchars($log['target_type']); ?>
                                <?php if ($log['target_id']): ?>
                                    <br><small>#<?php echo $log['target_id']; ?></small>
                                <?php endif; ?>
                            </span>
                        </td>
                        <td>
                            <div class="log-description">
                                <?php echo htmlspecialchars($log['description']); ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($log['ip_address']): ?>
                                <span class="ip-address"><?php echo htmlspecialchars($log['ip_address']); ?></span>
                            <?php else: ?>
                                <small style="color: #999;">N/A</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($log['details']): ?>
                                <button class="details-toggle" onclick="toggleDetails(<?php echo $log['id']; ?>)">
                                    📋 View Details
                                </button>
                                <div class="details-content" id="details-<?php echo $log['id']; ?>">
                                    <pre><?php echo htmlspecialchars(json_encode(json_decode($log['details']), JSON_PRETTY_PRINT)); ?></pre>
                                </div>
                            <?php else: ?>
                                <small style="color: #999;">No details</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (empty($logs)): ?>
            <div style="text-align: center; padding: 3rem;">
                <h3 style="color: #666; margin-bottom: 1rem;">📭 No Log Entries Found</h3>
                <p style="color: #999; margin-bottom: 1.5rem;">No activities match your current filters.</p>
                <a href="view-logs.php" class="admin-btn">Clear Filters</a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page-1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>">« Previous</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page+1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>">Next »</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleDetails(logId) {
            const details = document.getElementById('details-' + logId);
            const button = details.previousElementSibling;
            
            details.classList.toggle('show');
            button.textContent = details.classList.contains('show') ? '📋 Hide Details' : '📋 View Details';
        }
    </script>
</body>
</html>