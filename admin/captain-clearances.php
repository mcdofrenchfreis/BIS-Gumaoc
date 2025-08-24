<?php
session_start();
include '../includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'grant_clearance':
                    $expires_at = null;
                    if (!empty($_POST['expires_at'])) {
                        $expires_at = $_POST['expires_at'];
                    }
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO captain_clearances (
                            resident_id, clearance_type, reason, granted_by, expires_at, notes
                        ) VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->execute([
                        $_POST['resident_id'],
                        $_POST['clearance_type'],
                        $_POST['reason'],
                        $_SESSION['admin_username'] ?? 'admin',
                        $expires_at,
                        $_POST['notes']
                    ]);
                    
                    // Update resident status
                    $update_stmt = $pdo->prepare("
                        UPDATE resident_status 
                        SET captain_clearance_granted = 1, 
                            captain_clearance_date = NOW(),
                            captain_clearance_reason = ?,
                            captain_clearance_expires = ?
                        WHERE resident_id = ?
                    ");
                    $update_stmt->execute([$_POST['reason'], $expires_at, $_POST['resident_id']]);
                    
                    $success_message = "Captain clearance granted successfully!";
                    break;
                    
                case 'revoke_clearance':
                    $stmt = $pdo->prepare("UPDATE captain_clearances SET status = 'revoked' WHERE id = ?");
                    $stmt->execute([$_POST['clearance_id']]);
                    
                    // Update resident status
                    $clearance = $pdo->prepare("SELECT resident_id FROM captain_clearances WHERE id = ?");
                    $clearance->execute([$_POST['clearance_id']]);
                    $resident_id = $clearance->fetchColumn();
                    
                    if ($resident_id) {
                        $update_stmt = $pdo->prepare("
                            UPDATE resident_status 
                            SET captain_clearance_granted = 0, 
                                captain_clearance_date = NULL,
                                captain_clearance_reason = NULL,
                                captain_clearance_expires = NULL
                            WHERE resident_id = ?
                        ");
                        $update_stmt->execute([$resident_id]);
                    }
                    
                    $success_message = "Captain clearance revoked successfully!";
                    break;
            }
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Get residents requiring clearance
$requiring_clearance = $pdo->query("
    SELECT 
        rs.*,
        r.first_name, r.middle_name, r.last_name, r.email, r.phone,
        COUNT(cc.id) as active_clearances
    FROM resident_status rs
    JOIN residents r ON rs.resident_id = r.id
    LEFT JOIN captain_clearances cc ON rs.resident_id = cc.resident_id AND cc.status = 'active'
    WHERE rs.requires_captain_clearance = 1
    GROUP BY rs.id
    ORDER BY rs.last_updated DESC
");
$residents_requiring = $requiring_clearance->fetchAll();

// Get active clearances
$active_clearances = $pdo->query("
    SELECT 
        cc.*,
        r.first_name, r.middle_name, r.last_name, r.email, r.phone
    FROM captain_clearances cc
    JOIN residents r ON cc.resident_id = r.id
    WHERE cc.status = 'active'
    ORDER BY cc.granted_date DESC
");
$clearances = $active_clearances->fetchAll();

// Get all residents for dropdown
$all_residents = $pdo->query("
    SELECT r.*, rs.record_status, rs.requires_captain_clearance
    FROM residents r
    LEFT JOIN resident_status rs ON r.id = rs.resident_id
    ORDER BY r.last_name, r.first_name
");
$residents = $all_residents->fetchAll();

// Get clearance statistics
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_clearances,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_clearances,
        SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired_clearances,
        SUM(CASE WHEN status = 'revoked' THEN 1 ELSE 0 END) as revoked_clearances,
        (SELECT COUNT(*) FROM resident_status WHERE requires_captain_clearance = 1) as residents_requiring
    FROM captain_clearances
")->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Captain Clearances - Barangay Gumaoc East</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%); color: white; padding: 30px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; border-left: 4px solid #8B4513; }
        .stat-number { font-size: 2em; font-weight: bold; color: #8B4513; }
        .controls { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-weight: 500; margin: 2px; }
        .btn-primary { background: #8B4513; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-sm { padding: 4px 8px; font-size: 12px; }
        .section { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; overflow: hidden; }
        .section-header { background: #8B4513; color: white; padding: 15px 20px; font-weight: 600; }
        .section-body { padding: 20px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .table th { background: #f8f9fa; font-weight: 600; }
        .table tbody tr:hover { background: #f8f9fa; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 500; text-transform: uppercase; }
        .status-good { background: #e8f5e8; color: #388e3c; }
        .status-minor_issues { background: #fff3e0; color: #f57c00; }
        .status-major_issues { background: #ffebee; color: #d32f2f; }
        .status-critical { background: #ffebee; color: #8B0000; }
        .clearance-active { background: #e8f5e8; color: #388e3c; }
        .clearance-expired { background: #f5f5f5; color: #666; }
        .clearance-revoked { background: #ffebee; color: #d32f2f; }
        .alert { padding: 12px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 5% auto; padding: 0; border-radius: 8px; width: 90%; max-width: 600px; }
        .modal-header { background: #8B4513; color: white; padding: 15px 20px; border-radius: 8px 8px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .modal-body { padding: 20px; }
        .close { color: white; font-size: 24px; cursor: pointer; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-control { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .text-center { text-align: center; }
        .text-muted { color: #666; }
        .badge-count { background: #dc3545; color: white; border-radius: 10px; padding: 2px 6px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-user-shield"></i> Barangay Captain Clearances</h1>
            <p>Manage clearances for residents with records</p>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['residents_requiring']; ?></div>
                <div>Requiring Clearance</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['active_clearances']; ?></div>
                <div>Active Clearances</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['expired_clearances']; ?></div>
                <div>Expired Clearances</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['revoked_clearances']; ?></div>
                <div>Revoked Clearances</div>
            </div>
        </div>
        
        <!-- Controls -->
        <div class="controls">
            <button type="button" class="btn btn-success" onclick="openGrantModal()">
                <i class="fas fa-user-check"></i> Grant New Clearance
            </button>
            <a href="manage-blotter.php" class="btn btn-secondary">
                <i class="fas fa-clipboard-list"></i> Blotter Records
            </a>
            <a href="blotter-reports.php" class="btn btn-secondary">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
        
        <!-- Residents Requiring Clearance -->
        <div class="section">
            <div class="section-header">
                <i class="fas fa-exclamation-triangle"></i> Residents Requiring Captain Clearance
                <?php if (count($residents_requiring) > 0): ?>
                    <span class="badge-count"><?php echo count($residents_requiring); ?></span>
                <?php endif; ?>
            </div>
            <div class="section-body">
                <?php if (empty($residents_requiring)): ?>
                    <div class="text-center text-muted" style="padding: 40px;">
                        <i class="fas fa-check-circle" style="font-size: 3em; margin-bottom: 20px; opacity: 0.3;"></i><br>
                        No residents currently require captain clearance.
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Resident</th>
                                <th>Contact</th>
                                <th>Record Status</th>
                                <th>Incidents</th>
                                <th>Pending Cases</th>
                                <th>Active Clearances</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($residents_requiring as $resident): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($resident['first_name'] . ' ' . ($resident['middle_name'] ? $resident['middle_name'] . ' ' : '') . $resident['last_name']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($resident['email']); ?><br>
                                        <small><?php echo htmlspecialchars($resident['phone']); ?></small>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $resident['record_status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $resident['record_status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $resident['total_incidents']; ?></td>
                                    <td><?php echo $resident['pending_cases']; ?></td>
                                    <td><?php echo $resident['active_clearances']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-success" onclick="grantClearance(<?php echo $resident['resident_id']; ?>, '<?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']); ?>')">
                                            <i class="fas fa-user-check"></i> Grant
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Active Clearances -->
        <div class="section">
            <div class="section-header">
                <i class="fas fa-shield-alt"></i> Active Clearances
            </div>
            <div class="section-body">
                <?php if (empty($clearances)): ?>
                    <div class="text-center text-muted" style="padding: 40px;">
                        <i class="fas fa-shield-alt" style="font-size: 3em; margin-bottom: 20px; opacity: 0.3;"></i><br>
                        No active clearances found.
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Resident</th>
                                <th>Clearance Type</th>
                                <th>Reason</th>
                                <th>Granted By</th>
                                <th>Granted Date</th>
                                <th>Expires</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clearances as $clearance): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($clearance['first_name'] . ' ' . ($clearance['middle_name'] ? $clearance['middle_name'] . ' ' : '') . $clearance['last_name']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($clearance['email']); ?></small>
                                    </td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $clearance['clearance_type'])); ?></td>
                                    <td><?php echo htmlspecialchars($clearance['reason']); ?></td>
                                    <td><?php echo htmlspecialchars($clearance['granted_by']); ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($clearance['granted_date'])); ?></td>
                                    <td>
                                        <?php if ($clearance['expires_at']): ?>
                                            <?php 
                                            $expires = strtotime($clearance['expires_at']);
                                            $now = time();
                                            if ($expires < $now): ?>
                                                <span class="status-badge clearance-expired">Expired</span>
                                            <?php else: ?>
                                                <?php echo date('M j, Y', $expires); ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <em>No expiry</em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="revokeClearance(<?php echo $clearance['id']; ?>)">
                                            <i class="fas fa-times"></i> Revoke
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Grant Clearance Modal -->
    <div id="grantModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-check"></i> Grant Captain Clearance</h3>
                <span class="close" onclick="closeModal('grantModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="action" value="grant_clearance">
                    <input type="hidden" name="resident_id" id="grant_resident_id">
                    
                    <div class="form-group">
                        <label>Resident</label>
                        <select name="resident_id" id="grant_resident_select" class="form-control" required>
                            <option value="">Select Resident</option>
                            <?php foreach ($residents as $resident): ?>
                                <option value="<?php echo $resident['id']; ?>" 
                                        data-status="<?php echo $resident['record_status'] ?? 'good'; ?>"
                                        data-requires="<?php echo $resident['requires_captain_clearance'] ?? 0; ?>">
                                    <?php echo htmlspecialchars($resident['last_name'] . ', ' . $resident['first_name'] . ' ' . ($resident['middle_name'] ?: '')); ?>
                                    <?php if ($resident['requires_captain_clearance']): ?>
                                        <strong>(Requires Clearance)</strong>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Clearance Type *</label>
                            <select name="clearance_type" class="form-control" required>
                                <option value="form_access">Form Access</option>
                                <option value="certificate_request">Certificate Request</option>
                                <option value="business_permit">Business Permit</option>
                                <option value="general">General Clearance</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Expires At</label>
                            <input type="datetime-local" name="expires_at" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Reason *</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Explain why clearance is being granted..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Additional Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any additional notes or conditions..."></textarea>
                    </div>
                    
                    <div style="text-align: right; margin-top: 20px;">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('grantModal')">Cancel</button>
                        <button type="submit" class="btn btn-success">Grant Clearance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Revoke Clearance Modal -->
    <div id="revokeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-times"></i> Revoke Clearance</h3>
                <span class="close" onclick="closeModal('revokeModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="action" value="revoke_clearance">
                    <input type="hidden" name="clearance_id" id="revoke_clearance_id">
                    
                    <p>Are you sure you want to revoke this clearance? This action cannot be undone.</p>
                    
                    <div style="text-align: right; margin-top: 20px;">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('revokeModal')">Cancel</button>
                        <button type="submit" class="btn btn-danger">Revoke Clearance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function openGrantModal() {
            document.getElementById('grantModal').style.display = 'block';
        }
        
        function grantClearance(residentId, residentName) {
            document.getElementById('grant_resident_id').value = residentId;
            document.getElementById('grant_resident_select').value = residentId;
            document.getElementById('grantModal').style.display = 'block';
        }
        
        function revokeClearance(clearanceId) {
            document.getElementById('revoke_clearance_id').value = clearanceId;
            document.getElementById('revokeModal').style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>