<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/auth_check.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Page meta for shared header
$page_title = 'User Reports - Admin Panel';
$page_description = 'Review, filter, and update status of submitted user reports';
$base_path = '../';

// Handle status updates
$update_message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowed = ['processing', 'completed', 'rejected', 'pending'];
    if (isset($_POST['bulk_action'], $_POST['report_ids']) && is_array($_POST['report_ids'])) {
        $action = $_POST['bulk_action'];
        $ids = array_map('intval', $_POST['report_ids']);
        $ids = array_values(array_filter($ids));
        if ($ids && in_array($action, $allowed, true)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("UPDATE user_reports SET status = ?, updated_at = NOW() WHERE id IN ($placeholders)");
            if ($stmt->execute(array_merge([$action], $ids))) {
                $update_message = count($ids) . " report(s) updated to {$action}.";
            }
        }
    } elseif (isset($_POST['action'], $_POST['report_id'])) {
        $reportId = (int)$_POST['report_id'];
        $action = $_POST['action'];
        if (in_array($action, $allowed, true)) {
            $stmt = $pdo->prepare("UPDATE user_reports SET status = ?, updated_at = NOW() WHERE id = ?");
            if ($stmt->execute([$action, $reportId])) {
                $update_message = "Report #{$reportId} updated to {$action}.";
            }
        }
    }
}

// Filters
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$priority = isset($_GET['priority']) ? trim($_GET['priority']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where = [];
$params = [];
if ($status !== '' && in_array($status, ['pending','processing','completed','rejected'], true)) {
    $where[] = 'ur.status = ?';
    $params[] = $status;
}
if ($priority !== '' && in_array($priority, ['low','medium','high'], true)) {
    $where[] = 'ur.priority = ?';
    $params[] = $priority;
}
if ($search !== '') {
    $where[] = '(ur.location LIKE ? OR ur.description LIKE ? OR r.first_name LIKE ? OR r.last_name LIKE ?)';
    $like = "%{$search}%";
    array_push($params, $like, $like, $like, $like);
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Summary
$summary = $pdo->query("SELECT 
    SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) pending,
    SUM(CASE WHEN status='processing' THEN 1 ELSE 0 END) processing,
    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) completed,
    SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) rejected,
    COUNT(*) total
FROM user_reports")->fetch(PDO::FETCH_ASSOC);

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$requested_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 15;
// Enforce minimum 10 per page, clamp max to 100
$per_page = max(10, min($requested_per_page, 100));
$offset = ($page - 1) * $per_page;

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM user_reports ur LEFT JOIN residents r ON r.id = ur.user_id $whereSql");
$countStmt->execute($params);
$total_rows = (int)$countStmt->fetchColumn();
$total_pages = max(1, (int)ceil($total_rows / $per_page));

$stmt = $pdo->prepare("SELECT ur.*, r.first_name, r.last_name FROM user_reports ur 
LEFT JOIN residents r ON r.id = ur.user_id 
$whereSql ORDER BY ur.created_at DESC LIMIT $per_page OFFSET $offset");
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .page-container { max-width: 1400px; margin: 0 auto; padding: 90px 20px 24px; }
        .page-header { background: #fff; padding: 16px 18px; border-radius: 12px; margin-bottom: 14px; border: 1px solid #e9ecef; display: grid; grid-template-columns: 1fr auto; align-items: center; }
        .cards { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; margin-bottom: 12px; }
        .card { background:#fff; border:1px solid #e9ecef; border-radius: 10px; padding: 12px; }
        .card h4 { margin: 0 0 6px; font-size: 13px; color:#2e7d32; text-transform: uppercase; }
        .card .num { font-size: 22px; font-weight: 700; color:#1b5e20; }
        .filters { display:grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; background:#fff; border:1px solid #e9ecef; padding: 12px; border-radius: 10px; margin-bottom: 12px; }
        .filters input, .filters select { padding: 8px 10px; border:1px solid #c8e6c9; border-radius:8px; }
        .table { width: 100%; border-collapse: collapse; background:#fff; border:1px solid #e9ecef; border-radius: 10px; overflow: hidden; }
        .table th, .table td { padding: 10px; border-bottom:1px solid #f1f3f5; text-align: left; vertical-align: top; }
        .table th { background:#f8faf8; font-size: 13px; color:#2e7d32; text-transform: uppercase; }
        .table tbody tr:nth-child(odd) { background:#fcfdfc; }
        .row-high { box-shadow: inset 4px 0 0 #e53935; }
        .row-medium { box-shadow: inset 4px 0 0 #fb8c00; }
        .status { padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; border:1px solid #e9ecef; }
        .status.pending { background:#fff3cd; color:#856404; border-color:#ffe8a1; }
        .status.processing { background:#e3f2fd; color:#0d47a1; border-color:#bbdefb; }
        .status.completed { background:#d4edda; color:#155724; border-color:#c3e6cb; }
        .status.rejected { background:#f8d7da; color:#721c24; border-color:#f5c6cb; }
        .badge { padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; border:1px solid transparent; display:inline-block; }
        .badge.low { background:#e8f5e9; color:#1b5e20; border-color:#a5d6a7; }
        .badge.medium { background:#fff8e1; color:#8d6e63; border-color:#ffe082; }
        .badge.high { background:#ffebee; color:#b71c1c; border-color:#ef9a9a; }
        .actions { display:flex; gap:6px; }
        .btn { padding: 6px 10px; border:none; border-radius:8px; cursor:pointer; background:#2e7d32; color:#fff; }
        .btn.secondary { background:#6c757d; }
        .btn.warn { background:#b23c17; }
        .pagination { display:flex; gap: 6px; margin-top: 12px; }
        .page-link { padding:6px 10px; background:#fff; border:1px solid #e9ecef; border-radius:8px; text-decoration:none; color:#2e7d32; }
        .filters .submit { align-self: end; }
        .desc { color:#555; font-size: 13px; }
        .img-thumb { max-width: 80px; max-height: 60px; border-radius:6px; border:1px solid #e9ecef; cursor: pointer; }
        .bulk-bar { display:flex; gap:10px; align-items:center; background:#fff; border:1px solid #e9ecef; border-radius:10px; padding:10px; margin: 10px 0; }
        .muted { color:#777; font-size:12px; }
        .link { color:#1b6e1b; text-decoration:none; font-weight:600; }
    </style>
</head>
<body>
<?php $base_path = '../'; include $base_path . 'includes/admin_mini_nav.php'; ?>
    <script>
        function setStatus(id, status) {
            const form = document.getElementById('statusForm-' + id);
            if (!form) return;
            const current = form.getAttribute('data-current');
            if (current === status) return;
            if (!confirm('Set report #' + id + ' to ' + status + '?')) return;
            form.querySelector('input[name="action"]').value = status;
            form.submit();
        }
        function viewImage(src) {
            const w = window.open('', '_blank');
            w.document.write('<img src="' + src + '" style="max-width:100%" />');
        }
        function toggleSelectAll(source) {
            const checks = document.querySelectorAll('.row-check');
            checks.forEach(c => c.checked = source.checked);
            updateBulkSelected();
        }
        function updateBulkSelected() {
            const checks = Array.from(document.querySelectorAll('.row-check:checked'));
            const count = checks.length;
            const sel = document.getElementById('bulkSelectedCount');
            const btn = document.getElementById('bulkApplyBtn');
            if (sel) sel.textContent = count;
            if (btn) btn.disabled = count === 0;
        }
    </script>
    <div class="page-container">
        <div class="page-header">
            <div>
                <h2 style="margin:0;">User Reports</h2>
                <div style="color:#666; font-size:13px;">Review, filter, and update status of submitted reports</div>
            </div>
        </div>

        <?php if ($update_message): ?>
            <div style="background:#f1f9f1; border:1px solid #a3c293; color:#2c662d; padding:10px; border-radius:8px; margin-bottom:10px;">
                <?php echo htmlspecialchars($update_message); ?>
            </div>
        <?php endif; ?>

        <div class="cards">
            <div class="card"><h4>Total</h4><div class="num"><?php echo (int)($summary['total'] ?? 0); ?></div></div>
            <div class="card"><h4>Pending</h4><div class="num"><?php echo (int)($summary['pending'] ?? 0); ?></div></div>
            <div class="card"><h4>Processing</h4><div class="num"><?php echo (int)($summary['processing'] ?? 0); ?></div></div>
            <div class="card"><h4>Completed</h4><div class="num"><?php echo (int)($summary['completed'] ?? 0); ?></div></div>
            <div class="card"><h4>Rejected</h4><div class="num"><?php echo (int)($summary['rejected'] ?? 0); ?></div></div>
        </div>

        <form class="filters" method="get">
            <div>
                <label for="status" style="font-size:12px; color:#2e7d32; font-weight:600;">Status</label>
                <select id="status" name="status">
                    <option value="">All</option>
                    <option value="pending" <?php echo $status==='pending'?'selected':''; ?>>Pending</option>
                    <option value="processing" <?php echo $status==='processing'?'selected':''; ?>>Processing</option>
                    <option value="completed" <?php echo $status==='completed'?'selected':''; ?>>Completed</option>
                    <option value="rejected" <?php echo $status==='rejected'?'selected':''; ?>>Rejected</option>
                </select>
            </div>
            <div>
                <label for="priority" style="font-size:12px; color:#2e7d32; font-weight:600;">Priority</label>
                <select id="priority" name="priority">
                    <option value="">All</option>
                    <option value="low" <?php echo $priority==='low'?'selected':''; ?>>Low</option>
                    <option value="medium" <?php echo $priority==='medium'?'selected':''; ?>>Medium</option>
                    <option value="high" <?php echo $priority==='high'?'selected':''; ?>>High</option>
                </select>
            </div>
            <div style="grid-column: span 2;">
                <label for="search" style="font-size:12px; color:#2e7d32; font-weight:600;">Search</label>
                <input id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name, location, description..." />
            </div>
            <div>
                <label for="per_page" style="font-size:12px; color:#2e7d32; font-weight:600;">Per Page</label>
                <select id="per_page" name="per_page">
                    <?php $ppOpts = [10,15,25,50]; foreach ($ppOpts as $opt): ?>
                        <option value="<?php echo $opt; ?>" <?php echo ($per_page === $opt) ? 'selected' : ''; ?>><?php echo $opt; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="submit">
                <button class="btn" type="submit">Apply</button>
            </div>
        </form>

        <form method="post" class="bulk-bar" onsubmit="return confirm('Apply bulk action to selected reports?');">
            <div class="muted">Selected: <strong id="bulkSelectedCount">0</strong></div>
            <select name="bulk_action">
                <option value="">Bulk action</option>
                <option value="processing">Mark as Processing</option>
                <option value="completed">Mark as Completed</option>
                <option value="rejected">Mark as Rejected</option>
            </select>
            <button id="bulkApplyBtn" class="btn" type="submit" disabled>Apply</button>
        </form>

        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th><input type="checkbox" onclick="toggleSelectAll(this)"></th>
                        <th>ID</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Description</th>
                        <th>Proof</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$rows): ?>
                        <tr><td colspan="11" style="text-align:center; color:#666;">No reports found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr class="<?php echo $row['priority']==='high'?'row-high':($row['priority']==='medium'?'row-medium':''); ?>">
                                <td><input class="row-check" type="checkbox" name="report_ids[]" value="<?php echo (int)$row['id']; ?>" onchange="updateBulkSelected()"></td>
                                <td>#<?php echo (int)$row['id']; ?></td>
                                <td><?php echo htmlspecialchars(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))); ?> (ID: <?php echo (int)$row['user_id']; ?>)</td>
                                <td><?php echo htmlspecialchars($row['incident_type']); ?></td>
                                <td><span class="badge <?php echo htmlspecialchars($row['priority']); ?>"><?php echo ucfirst($row['priority']); ?></span></td>
                                <td><span class="status <?php echo htmlspecialchars($row['status']); ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td class="desc"><?php echo nl2br(htmlspecialchars(mb_strimwidth($row['description'], 0, 120, '…'))); ?></td>
                                <td>
                                    <?php if (!empty($row['proof_image'])): ?>
                                        <img class="img-thumb" src="../assets/images/reports/<?php echo htmlspecialchars($row['proof_image']); ?>" alt="Proof" onclick="viewImage(this.src)" />
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <form id="statusForm-<?php echo (int)$row['id']; ?>" method="post" class="actions" data-current="<?php echo htmlspecialchars($row['status']); ?>">
                                        <input type="hidden" name="report_id" value="<?php echo (int)$row['id']; ?>" />
                                        <input type="hidden" name="action" value="" />
                                        <button class="btn secondary" type="button" onclick="setStatus(<?php echo (int)$row['id']; ?>, 'processing')">Processing</button>
                                        <button class="btn" type="button" onclick="setStatus(<?php echo (int)$row['id']; ?>, 'completed')">Completed</button>
                                        <button class="btn warn" type="button" onclick="setStatus(<?php echo (int)$row['id']; ?>, 'rejected')">Rejected</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $p, 'per_page' => $per_page])); ?>" <?php echo $p===$page?'style="background:#e8f5e9; border-color:#a5d6a7;"':''; ?>><?php echo $p; ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
