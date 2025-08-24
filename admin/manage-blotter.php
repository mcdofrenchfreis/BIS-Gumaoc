<?php
session_start();
include '../includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle form submissions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add_blotter':
                    // Generate blotter number
                    $year = date('Y');
                    $stmt = $pdo->query("SELECT COUNT(*) + 1 as next_num FROM barangay_blotter WHERE YEAR(created_at) = $year");
                    $next_num = $stmt->fetch()['next_num'];
                    $blotter_number = "BL-$year-" . str_pad($next_num, 4, '0', STR_PAD_LEFT);
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO barangay_blotter (
                            blotter_number, incident_type, complainant_id, complainant_name, 
                            complainant_address, complainant_contact, respondent_id, respondent_name, 
                            respondent_address, respondent_contact, incident_date, location, 
                            description, classification, investigating_officer, created_by
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->execute([
                        $blotter_number,
                        $_POST['incident_type'],
                        $_POST['complainant_resident_id'] ?: null,
                        $_POST['complainant_name'],
                        $_POST['complainant_address'],
                        $_POST['complainant_contact'],
                        $_POST['respondent_resident_id'] ?: null,
                        $_POST['respondent_name'],
                        $_POST['respondent_address'],
                        $_POST['respondent_contact'],
                        $_POST['incident_date'],
                        $_POST['location'],
                        $_POST['description'],
                        $_POST['classification'],
                        $_POST['investigating_officer'],
                        $_SESSION['admin_username'] ?? 'admin'
                    ]);
                    
                    $success_message = "Blotter record added successfully! Blotter Number: $blotter_number";
                    break;
                    
                case 'update_status':
                    $stmt = $pdo->prepare("
                        UPDATE barangay_blotter 
                        SET status = ?, action_taken = ?, settlement_details = ? 
                        WHERE id = ?
                    ");
                    
                    $stmt->execute([
                        $_POST['status'],
                        $_POST['action_taken'],
                        $_POST['settlement_details'],
                        $_POST['blotter_id']
                    ]);
                    
                    $success_message = "Blotter record updated successfully!";
                    break;
            }
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Get blotter records with search
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(complainant_name LIKE ? OR respondent_name LIKE ? OR blotter_number LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

$stmt = $pdo->prepare("
    SELECT * FROM barangay_blotter 
    $where_clause
    ORDER BY created_at DESC 
    LIMIT 50
");
$stmt->execute($params);
$blotter_records = $stmt->fetchAll();

// Get residents for dropdown
$residents_stmt = $pdo->query("SELECT id, first_name, middle_name, last_name FROM residents ORDER BY last_name, first_name");
$residents = $residents_stmt->fetchAll();

// Get statistics
$stats_stmt = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'filed' THEN 1 ELSE 0 END) as filed,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
        SUM(CASE WHEN classification = 'critical' THEN 1 ELSE 0 END) as critical
    FROM barangay_blotter
");
$stats = $stats_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blotter Management - Barangay Gumaoc East</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #2c5530 0%, #3e7b3e 100%); color: white; padding: 30px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
        .stat-number { font-size: 2em; font-weight: bold; color: #2c5530; }
        .controls { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .form-grid { display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 15px; align-items: end; margin-bottom: 15px; }
        .form-group { display: flex; flex-direction: column; }
        .form-control { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-weight: 500; }
        .btn-primary { background: #2c5530; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-sm { padding: 4px 8px; font-size: 12px; }
        .table-container { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .table th { background: #f8f9fa; font-weight: 600; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 500; text-transform: uppercase; }
        .status-filed { background: #e3f2fd; color: #1976d2; }
        .status-resolved { background: #e8f5e8; color: #388e3c; }
        .status-under_investigation { background: #fff3e0; color: #f57c00; }
        .alert { padding: 12px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 5% auto; padding: 0; border-radius: 8px; width: 90%; max-width: 600px; }
        .modal-header { background: #2c5530; color: white; padding: 15px 20px; border-radius: 8px 8px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .modal-body { padding: 20px; }
        .close { color: white; font-size: 24px; cursor: pointer; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .form-full { grid-column: 1 / -1; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-clipboard-list"></i> Barangay Blotter Management</h1>
            <p>Record and manage complaints, incidents, and disputes</p>
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
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div>Total Records</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['filed']; ?></div>
                <div>Filed Cases</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['resolved']; ?></div>
                <div>Resolved Cases</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['critical']; ?></div>
                <div>Critical Cases</div>
            </div>
        </div>
        
        <!-- Controls -->
        <div class="controls">
            <form method="GET" class="form-grid">
                <div class="form-group">
                    <label>Search Records</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by name or blotter number..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status_filter" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="filed" <?php echo $status_filter === 'filed' ? 'selected' : ''; ?>>Filed</option>
                        <option value="under_investigation" <?php echo $status_filter === 'under_investigation' ? 'selected' : ''; ?>>Under Investigation</option>
                        <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-success" onclick="openAddModal()"><i class="fas fa-plus"></i> Add Record</button>
                </div>
            </form>
            
            <div style="margin-top: 15px;">
                <a href="blotter-reports.php" class="btn btn-secondary"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="captain-clearances.php" class="btn btn-warning"><i class="fas fa-user-shield"></i> Clearances</a>
                <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Dashboard</a>
            </div>
        </div>
        
        <!-- Records Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Blotter #</th>
                        <th>Type</th>
                        <th>Complainant</th>
                        <th>Respondent</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($blotter_records)): ?>
                        <tr><td colspan="7" style="text-align: center; padding: 40px; color: #666;">No records found</td></tr>
                    <?php else: ?>
                        <?php foreach ($blotter_records as $record): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($record['blotter_number']); ?></strong></td>
                                <td><?php echo ucfirst($record['incident_type']); ?></td>
                                <td><?php echo htmlspecialchars($record['complainant_name']); ?></td>
                                <td><?php echo htmlspecialchars($record['respondent_name']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($record['incident_date'])); ?></td>
                                <td><span class="status-badge status-<?php echo $record['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $record['status'])); ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="viewRecord(<?php echo $record['id']; ?>)"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-warning" onclick="updateStatus(<?php echo $record['id']; ?>)"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Add Record Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus"></i> Add New Blotter Record</h3>
                <span class="close" onclick="closeModal('addModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add_blotter">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Incident Type *</label>
                            <select name="incident_type" class="form-control" required>
                                <option value="">Select Type</option>
                                <option value="complaint">Complaint</option>
                                <option value="incident">Incident</option>
                                <option value="dispute">Dispute</option>
                                <option value="violation">Violation</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Classification *</label>
                            <select name="classification" class="form-control" required>
                                <option value="minor">Minor</option>
                                <option value="major">Major</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Complainant (Resident)</label>
                            <select name="complainant_resident_id" class="form-control">
                                <option value="">Select if resident</option>
                                <?php foreach ($residents as $resident): ?>
                                    <option value="<?php echo $resident['id']; ?>">
                                        <?php echo htmlspecialchars($resident['last_name'] . ', ' . $resident['first_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Complainant Name *</label>
                            <input type="text" name="complainant_name" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group form-full">
                        <label>Complainant Address *</label>
                        <input type="text" name="complainant_address" class="form-control" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Complainant Contact</label>
                            <input type="text" name="complainant_contact" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Incident Date *</label>
                            <input type="datetime-local" name="incident_date" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Respondent (Resident)</label>
                            <select name="respondent_resident_id" class="form-control">
                                <option value="">Select if resident</option>
                                <?php foreach ($residents as $resident): ?>
                                    <option value="<?php echo $resident['id']; ?>">
                                        <?php echo htmlspecialchars($resident['last_name'] . ', ' . $resident['first_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Respondent Name *</label>
                            <input type="text" name="respondent_name" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group form-full">
                        <label>Respondent Address *</label>
                        <input type="text" name="respondent_address" class="form-control" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Respondent Contact</label>
                            <input type="text" name="respondent_contact" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Investigating Officer</label>
                            <input type="text" name="investigating_officer" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group form-full">
                        <label>Location *</label>
                        <input type="text" name="location" class="form-control" required>
                    </div>
                    
                    <div class="form-group form-full">
                        <label>Description *</label>
                        <textarea name="description" class="form-control" rows="4" required></textarea>
                    </div>
                    
                    <div style="text-align: right; margin-top: 20px;">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Update Status Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Update Status</h3>
                <span class="close" onclick="closeModal('statusModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="blotter_id" id="status_blotter_id">
                    
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" class="form-control" required>
                            <option value="filed">Filed</option>
                            <option value="under_investigation">Under Investigation</option>
                            <option value="mediation">Mediation</option>
                            <option value="resolved">Resolved</option>
                            <option value="dismissed">Dismissed</option>
                            <option value="referred_to_court">Referred to Court</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Action Taken</label>
                        <textarea name="action_taken" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Settlement Details</label>
                        <textarea name="settlement_details" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div style="text-align: right; margin-top: 20px;">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('statusModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function viewRecord(id) {
            // Implement view functionality
            alert('View record ' + id);
        }
        
        function updateStatus(id) {
            document.getElementById('status_blotter_id').value = id;
            document.getElementById('statusModal').style.display = 'block';
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