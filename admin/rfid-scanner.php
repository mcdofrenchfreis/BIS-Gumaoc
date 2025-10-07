<?php
session_start();
include '../includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Create scanned_rfid_codes table if it doesn't exist
$pdo->exec("
    CREATE TABLE IF NOT EXISTS scanned_rfid_codes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        rfid_code VARCHAR(50) UNIQUE NOT NULL,
        status ENUM('available', 'assigned', 'disabled', 'archived') DEFAULT 'available',
        scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        assigned_at TIMESTAMP NULL,
        assigned_to_resident_id INT NULL,
        assigned_to_email VARCHAR(255) NULL,
        scanned_by_admin_id INT NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        INDEX idx_rfid_code (rfid_code),
        INDEX idx_status (status)
    )
");

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'scan_rfid':
                $rfid_code = trim($_POST['rfid_code']);
                $notes = trim($_POST['notes'] ?? '');
                
                if (!empty($rfid_code)) {
                    // Check if RFID already exists
                    $check_stmt = $pdo->prepare("SELECT id, status FROM scanned_rfid_codes WHERE rfid_code = ?");
                    $check_stmt->execute([$rfid_code]);
                    $existing = $check_stmt->fetch();
                    
                    if ($existing) {
                        $_SESSION['error'] = "RFID code already exists with status: " . ucfirst($existing['status']);
                    } else {
                        // Insert new RFID code
                        $stmt = $pdo->prepare("INSERT INTO scanned_rfid_codes (rfid_code, notes, scanned_by_admin_id) VALUES (?, ?, ?)");
                        if ($stmt->execute([$rfid_code, $notes, $_SESSION['admin_id'] ?? null])) {
                            $_SESSION['success'] = "RFID code '$rfid_code' scanned and added successfully!";
                        } else {
                            $_SESSION['error'] = "Failed to add RFID code.";
                        }
                    }
                }
                break;
                
            case 'update_status':
                $rfid_id = (int)$_POST['rfid_id'];
                $new_status = $_POST['new_status'];
                
                $stmt = $pdo->prepare("UPDATE scanned_rfid_codes SET status = ? WHERE id = ?");
                if ($stmt->execute([$new_status, $rfid_id])) {
                    $_SESSION['success'] = "RFID status updated successfully!";
                } else {
                    $_SESSION['error'] = "Failed to update RFID status.";
                }
                break;
                
            case 'archive_rfid':
                $rfid_id = (int)$_POST['rfid_id'];
                
                $stmt = $pdo->prepare("UPDATE scanned_rfid_codes SET status = 'archived' WHERE id = ?");
                if ($stmt->execute([$rfid_id])) {
                    $_SESSION['success'] = "RFID code archived successfully!";
                } else {
                    $_SESSION['error'] = "Failed to archive RFID code.";
                }
                break;

            case 'edit_rfid':
                $rfid_id = (int)($_POST['rfid_id'] ?? 0);
                $new_status = $_POST['status'] ?? 'available';
                $scanned_at_input = trim($_POST['scanned_at'] ?? ''); // expects datetime-local format
                $assigned_email = trim($_POST['assigned_email'] ?? '');
                $notes_input = isset($_POST['notes']) ? trim($_POST['notes']) : null;

                // Normalize scanned_at to Y-m-d H:i:s
                $new_scanned_at = null;
                if ($scanned_at_input !== '') {
                    // Replace 'T' from datetime-local to space
                    $temp = str_replace('T', ' ', $scanned_at_input);
                    $timestamp = strtotime($temp);
                    if ($timestamp !== false) {
                        $new_scanned_at = date('Y-m-d H:i:s', $timestamp);
                    }
                }

                // Determine assignment
                $assigned_to_resident_id = null;
                $assigned_to_email = null;
                $set_assigned_at_to_now = false;

                if ($assigned_email !== '') {
                    // Try to find resident by email
                    $find_res_stmt = $pdo->prepare("SELECT id, email FROM residents WHERE email = ? LIMIT 1");
                    $find_res_stmt->execute([$assigned_email]);
                    $resident = $find_res_stmt->fetch(PDO::FETCH_ASSOC);
                    if ($resident) {
                        $assigned_to_resident_id = (int)$resident['id'];
                        $assigned_to_email = $resident['email'];
                    } else {
                        // Keep as free-form email if not found in residents
                        $assigned_to_resident_id = null;
                        $assigned_to_email = $assigned_email;
                    }
                    $set_assigned_at_to_now = true;
                    // If assigned info is provided but status isn't 'assigned', keep user's chosen status
                } else {
                    // No assignment -> clear assignment fields
                    $assigned_to_resident_id = null;
                    $assigned_to_email = null;
                }

                try {
                    // Build update dynamically
                    $sql = "UPDATE scanned_rfid_codes 
                            SET status = :status,
                                scanned_at = COALESCE(:scanned_at, scanned_at),
                                assigned_to_resident_id = :assigned_to_resident_id,
                                assigned_to_email = :assigned_to_email,
                                assigned_at = :assigned_at,
                                notes = :notes
                            WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':status', $new_status);
                    $stmt->bindValue(':scanned_at', $new_scanned_at);
                    if ($assigned_to_resident_id === null) {
                        $stmt->bindValue(':assigned_to_resident_id', null, PDO::PARAM_NULL);
                    } else {
                        $stmt->bindValue(':assigned_to_resident_id', (int)$assigned_to_resident_id, PDO::PARAM_INT);
                    }
                    // Use PARAM_STR for email, allow null
                    if ($assigned_to_email === null) {
                        $stmt->bindValue(':assigned_to_email', null, PDO::PARAM_NULL);
                    } else {
                        $stmt->bindValue(':assigned_to_email', $assigned_to_email);
                    }
                    if ($set_assigned_at_to_now) {
                        $stmt->bindValue(':assigned_at', date('Y-m-d H:i:s'));
                    } else {
                        $stmt->bindValue(':assigned_at', null, PDO::PARAM_NULL);
                    }
                    if ($notes_input === null || $notes_input === '') {
                        $stmt->bindValue(':notes', null, PDO::PARAM_NULL);
                    } else {
                        $stmt->bindValue(':notes', $notes_input);
                    }
                    $stmt->bindValue(':id', $rfid_id, PDO::PARAM_INT);

                    if ($stmt->execute()) {
                        $_SESSION['success'] = "RFID details updated successfully!";
                    } else {
                        $_SESSION['error'] = "Failed to update RFID details.";
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = "Error updating RFID: " . $e->getMessage();
                }
                break;
        }
        header('Location: rfid-scanner.php');
        exit;
    }
}

// Fetch RFID codes with pagination
$page = (int)($_GET['page'] ?? 1);
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count
$total_stmt = $pdo->query("SELECT COUNT(*) FROM scanned_rfid_codes WHERE status != 'archived'");
$total_codes = $total_stmt->fetchColumn();
$total_pages = ceil($total_codes / $per_page);

// Fetch codes for current page (excluding archived)
$codes_stmt = $pdo->prepare("
    SELECT s.*, 
           r.first_name, r.last_name, r.email as resident_email
    FROM scanned_rfid_codes s 
    LEFT JOIN residents r ON s.assigned_to_resident_id = r.id 
    WHERE s.status != 'archived'
    ORDER BY s.scanned_at DESC 
    LIMIT $per_page OFFSET $offset
");
$codes_stmt->execute();
$rfid_codes = $codes_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stats_stmt = $pdo->query("
    SELECT 
        status,
        COUNT(*) as count 
    FROM scanned_rfid_codes 
    WHERE status != 'archived'
    GROUP BY status
");
$stats = [];
while ($row = $stats_stmt->fetch()) {
    $stats[$row['status']] = $row['count'];
}

$base_path = '../';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID Scanner - Admin Panel</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #ffffff; margin: 0; padding: 90px 0 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    </style>
</head>
<body>
    <?php
        // Mini admin navigation (fixed at top)
        $base_path = '../';
        include '../includes/admin_mini_nav.php';
    ?>

<div class="admin-content">
    <div class="content-header">
        <h1>📱 RFID Scanner Management</h1>
        <p>Scan RFID codes to add them to the available pool for resident registration</p>
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card available">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <h3><?php echo $stats['available'] ?? 0; ?></h3>
                <p>Available Codes</p>
            </div>
        </div>
        <div class="stat-card assigned">
            <div class="stat-icon">👤</div>
            <div class="stat-info">
                <h3><?php echo $stats['assigned'] ?? 0; ?></h3>
                <p>Assigned Codes</p>
            </div>
        </div>
        <div class="stat-card disabled">
            <div class="stat-icon">❌</div>
            <div class="stat-info">
                <h3><?php echo $stats['disabled'] ?? 0; ?></h3>
                <p>Disabled Codes</p>
            </div>
        </div>
        <div class="stat-card total">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
                <h3><?php echo $total_codes; ?></h3>
                <p>Total Codes</p>
            </div>
        </div>
    </div>

    <!-- RFID Scanner Form -->
    <div class="scanner-section">
        <div class="scanner-card">
            <div class="scanner-header">
                <h3>🔍 Scan New RFID Code</h3>
                <p>Tap or scan an RFID card to add it to the system</p>
            </div>
            
            <form method="POST" class="scanner-form" id="scanForm">
                <input type="hidden" name="action" value="scan_rfid">
                
                <div class="scanner-input-group">
                    <div class="input-wrapper">
                        <input type="text" id="rfidInput" name="rfid_code" 
                               placeholder="Tap RFID card or enter code manually..." 
                               class="scanner-input" 
                               autocomplete="off" 
                               autofocus>
                        <div class="scan-indicator" id="scanIndicator">
                            <span class="pulse"></span>
                            <span class="text">Ready to scan...</span>
                        </div>
                    </div>
                    
                    <div class="notes-wrapper">
                        <textarea name="notes" placeholder="Optional notes..." class="notes-input"></textarea>
                    </div>
                    
                    <button type="submit" class="btn-scan">
                        <span class="btn-icon">📱</span>
                        Add RFID Code
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- RFID Codes List -->
    <div class="codes-section">
        <div class="section-header">
            <h3>📋 Scanned RFID Codes</h3>
            <div class="filter-controls">
                <select onchange="filterByStatus(this.value)" class="status-filter">
                    <option value="">All Status</option>
                    <option value="available">Available</option>
                    <option value="assigned">Assigned</option>
                    <option value="disabled">Disabled</option>
                </select>
                <a href="archived-rfid.php" class="btn-archive-view">View Archived</a>
            </div>
        </div>
        
        <div class="codes-table-wrapper">
            <table class="codes-table">
                <thead>
                    <tr>
                        <th>RFID Code</th>
                        <th>Status</th>
                        <th>Scanned Date</th>
                        <th>Assigned To</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rfid_codes as $code): ?>
                    <tr class="status-<?php echo $code['status']; ?>">
                        <td class="rfid-code"><?php echo htmlspecialchars($code['rfid_code']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $code['status']; ?>">
                                <?php echo ucfirst($code['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y g:i A', strtotime($code['scanned_at'])); ?></td>
                        <td>
                            <?php if ($code['assigned_to_resident_id']): ?>
                                <span class="assigned-info">
                                    <?php echo htmlspecialchars($code['first_name'] . ' ' . $code['last_name']); ?><br>
                                    <small><?php echo htmlspecialchars($code['resident_email']); ?></small>
                                </span>
                            <?php else: ?>
                                <span class="not-assigned">Not assigned</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($code['notes'] ?: 'No notes'); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button 
                                    type="button" 
                                    class="btn-action btn-edit" 
                                    title="Edit"
                                    data-id="<?php echo $code['id']; ?>"
                                    data-rfid="<?php echo htmlspecialchars($code['rfid_code']); ?>"
                                    data-status="<?php echo $code['status']; ?>"
                                    data-scanned="<?php echo htmlspecialchars($code['scanned_at']); ?>"
                                    data-assignedresidentid="<?php echo htmlspecialchars($code['assigned_to_resident_id'] ?? ''); ?>"
                                    data-assignedemail="<?php echo htmlspecialchars($code['assigned_to_email'] ?? $code['resident_email'] ?? ''); ?>"
                                    data-notes="<?php echo htmlspecialchars($code['notes'] ?? '', ENT_QUOTES); ?>"
                                    onclick="openEditModal(this)">✏️</button>
                                <?php if ($code['status'] === 'available'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="rfid_id" value="<?php echo $code['id']; ?>">
                                        <input type="hidden" name="new_status" value="disabled">
                                        <button type="submit" class="btn-action btn-disable" title="Disable">❌</button>
                                    </form>
                                <?php elseif ($code['status'] === 'disabled'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="rfid_id" value="<?php echo $code['id']; ?>">
                                        <input type="hidden" name="new_status" value="available">
                                        <button type="submit" class="btn-action btn-enable" title="Enable">✅</button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($code['status'] !== 'assigned'): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Archive this RFID code?')">
                                        <input type="hidden" name="action" value="archive_rfid">
                                        <input type="hidden" name="rfid_id" value="<?php echo $code['id']; ?>">
                                        <button type="submit" class="btn-action btn-archive" title="Archive">📦</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn">← Previous</a>
            <?php endif; ?>
            
            <span class="pagination-info">
                Page <?php echo $page; ?> of <?php echo $total_pages; ?>
            </span>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn">Next →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* RFID Scanner Styles */
.admin-content { padding: 2rem; max-width: 1400px; margin: 0 auto; }
.content-header { margin-bottom: 2rem; }
.content-header h1 { color: #2c3e50; font-size: 2rem; margin-bottom: 0.5rem; }
.content-header p { color: #7f8c8d; }

.stats-grid { 
    display: grid; 
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
    gap: 1.5rem; 
    margin-bottom: 2rem; 
}

.stat-card { 
    background: white; 
    border-radius: 12px; 
    padding: 1.5rem; 
    box-shadow: 0 2px 8px rgba(0,0,0,0.1); 
    display: flex; 
    align-items: center; 
    gap: 1rem; 
}
.stat-card.available { border-left: 4px solid #27ae60; }
.stat-card.assigned { border-left: 4px solid #3498db; }
.stat-card.disabled { border-left: 4px solid #e74c3c; }
.stat-card.total { border-left: 4px solid #9b59b6; }

.stat-icon { font-size: 2rem; }
.stat-info h3 { margin: 0; font-size: 1.8rem; color: #2c3e50; }
.stat-info p { margin: 0; color: #7f8c8d; }

.scanner-section { margin-bottom: 2rem; }
.scanner-card { 
    background: white; 
    border-radius: 12px; 
    padding: 2rem; 
    box-shadow: 0 2px 8px rgba(0,0,0,0.1); 
    overflow: hidden; /* keep children within card */
    box-sizing: border-box;
}
.scanner-section { display: flex; justify-content: center; }
.scanner-card { max-width: 720px; width: 100%; margin: 0 auto; }
.scanner-header { text-align: center; margin-bottom: 2rem; }
.scanner-header h3 { color: #2c3e50; margin-bottom: 0.5rem; }

.scanner-input-group { max-width: 600px; margin: 0 auto; }
.input-wrapper { position: relative; margin-bottom: 1rem; width: 100%; }
.notes-wrapper { width: 100%; }

.scanner-input { 
    width: 100%; 
    padding: 1rem; 
    font-size: 1.1rem; 
    border: 2px solid #ecf0f1; 
    border-radius: 8px; 
    background: #f8f9fa; 
    text-align: center; 
    font-family: monospace; 
    letter-spacing: 1px;
    padding-right: 180px; /* Make space for the indicator */
    box-sizing: border-box; /* prevent overflow from padding/border */
}
.scanner-input:focus { 
    outline: none; 
    border-color: #27ae60; 
    background: white; 
    box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.1); 
}

.scan-indicator { 
    position: absolute; 
    right: 10px; 
    top: 50%; 
    transform: translateY(-50%); 
    display: flex; 
    align-items: center; 
    gap: 0.5rem; 
    background: rgba(255, 255, 255, 0.8);
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    pointer-events: none; /* do not block clicks */
    z-index: 1;
}
.pulse { 
    width: 10px; 
    height: 10px; 
    background: #27ae60; 
    border-radius: 50%; 
    animation: pulse 1.5s infinite; 
}
@keyframes pulse { 
    0%, 100% { opacity: 1; transform: scale(1); } 
    50% { opacity: 0.5; transform: scale(1.2); } 
}

.notes-input { 
    width: 100%; 
    padding: 0.8rem; 
    border: 2px solid #ecf0f1; 
    border-radius: 8px; 
    resize: vertical; 
    min-height: 60px; 
    margin-bottom: 1rem; 
}

.btn-scan { 
    width: 100%; 
    padding: 1rem; 
    background: linear-gradient(135deg, rgba(39, 174, 96, 0.9), rgba(46, 204, 113, 0.9)); 
    color: white; 
    border: none; 
    border-radius: 8px; 
    font-size: 1.1rem; 
    font-weight: 600; 
    cursor: pointer; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    gap: 0.5rem; 
    transition: all 0.3s ease; 
}
.btn-scan:hover { background: linear-gradient(135deg, rgba(46, 204, 113, 0.9), rgba(39, 174, 96, 0.9)); transform: translateY(-2px); }

.codes-section { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); position: relative; z-index: 2; }
.section-header { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 1.5rem; 
}

.filter-controls { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; width: 100%; max-width: 360px; }
.status-filter { width: 100%; box-sizing: border-box; padding: 0.5rem 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; background: #fff; }
.btn-archive-view {
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, rgba(155, 89, 182, 0.9), rgba(142, 68, 173, 0.9));
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    display: inline-block;
    text-align: center;
    width: 100%;
}

.btn-archive-view:hover {
    background: linear-gradient(135deg, rgba(142, 68, 173, 0.9), rgba(155, 89, 182, 0.9));
    transform: translateY(-2px);
}

.codes-table { width: 100%; border-collapse: collapse; }
.codes-table th, .codes-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #ecf0f1; }
.codes-table th { background: #f8f9fa; font-weight: 600; color: #2c3e50; }

.rfid-code { font-family: monospace; font-size: 1.1rem; font-weight: 600; }
.status-badge { 
    padding: 0.3rem 0.8rem; 
    border-radius: 20px; 
    font-size: 0.9rem; 
    font-weight: 600; 
}
.status-badge.status-available { background: #d5f4e6; color: #27ae60; }
.status-badge.status-assigned { background: #daedf7; color: #3498db; }
.status-badge.status-disabled { background: #fadbd8; color: #e74c3c; }
.status-badge.status-archived { background: #e8f4f8; color: #3498db; }

.action-buttons { display: flex; gap: 0.5rem; }
.btn-action { 
    padding: 0.5rem; 
    border: none; 
    border-radius: 4px; 
    cursor: pointer; 
    font-size: 0.9rem; 
    transition: all 0.2s ease; 
}
.btn-disable:hover { background: #fadbd8; }
.btn-enable:hover { background: #d5f4e6; }
.btn-archive:hover { background: #e8f4f8; }
.btn-edit:hover { background: #f1f2f6; }

.pagination { 
    display: flex; 
    justify-content: center; 
    align-items: center; 
    gap: 1rem; 
    margin-top: 2rem; 
}
.pagination-btn { 
    padding: 0.5rem 1rem; 
    background: rgba(39, 174, 96, 0.9); 
    color: white; 
    text-decoration: none; 
    border-radius: 4px; 
}
.pagination-btn:hover { background: rgba(46, 204, 113, 0.9); }

/* Toast Notification Styles */
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    color: white;
    font-weight: 500;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 10000;
    transform: translateX(120%);
    transition: transform 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.toast.show {
    transform: translateX(0);
}

.toast.success {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
}

.toast.error {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 20000;
}
.modal {
    background: #fff;
    border-radius: 12px;
    width: 95%;
    max-width: 640px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    max-height: 90vh; /* responsive height */
    display: flex;
    flex-direction: column;
    margin: 16px;
}
.modal-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #ecf0f1;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.modal-header h4 { margin: 0; color: #2c3e50; }
.modal-close { background: transparent; border: none; font-size: 1.25rem; cursor: pointer; }
.modal-body { padding: 1rem 1.25rem; overflow-y: auto; flex: 1; min-height: 0; }
.form-row { margin-bottom: 0.9rem; }
.form-row label { display: block; font-weight: 600; margin-bottom: 0.35rem; color: #2c3e50; }
.form-row input, .form-row select { width: 100%; padding: 0.6rem 0.7rem; border: 2px solid #ecf0f1; border-radius: 8px; }
.modal-footer { padding: 1rem 1.25rem; border-top: 1px solid #ecf0f1; display: flex; gap: 0.5rem; justify-content: flex-end; }
.btn-secondary { background: #ecf0f1; border: none; padding: 0.6rem 1rem; border-radius: 6px; cursor: pointer; }
.btn-primary { background: rgba(39, 174, 96, 0.9); color: #fff; border: none; padding: 0.6rem 1rem; border-radius: 6px; cursor: pointer; }

@media (max-width: 768px) {
    .admin-content { padding: 1rem; }
    .stats-grid { grid-template-columns: 1fr; }
    .section-header { flex-direction: column; gap: 1rem; }
    .codes-table-wrapper { overflow-x: auto; }
    .scanner-input {
        padding-right: 10px; /* Remove padding on mobile */
        max-width: 100%;
    }
    .scan-indicator {
        position: relative;
        right: auto;
        top: auto;
        transform: none;
        margin-top: 0.5rem;
        justify-content: center;
    }
}
</style>

<script>
// Auto-focus RFID input and handle scanning
document.addEventListener('DOMContentLoaded', function() {
    const rfidInput = document.getElementById('rfidInput');
    const scanIndicator = document.getElementById('scanIndicator');
    
    // Keep focus on input
    rfidInput.focus();
    
    // Auto-submit after RFID scan (usually ends with Enter)
    rfidInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (this.value.trim().length > 0) {
                document.getElementById('scanForm').submit();
            }
        }
    });
    
    // Visual feedback when typing
    rfidInput.addEventListener('input', function() {
        if (this.value.length > 0) {
            scanIndicator.innerHTML = '<span class="pulse"></span><span class="text">Code detected!</span>';
        } else {
            scanIndicator.innerHTML = '<span class="pulse"></span><span class="text">Ready to scan...</span>';
        }
    });
    
    // Keep focus when window is focused (but not when modal is open or user is interacting elsewhere)
    window.addEventListener('focus', function() {
        const overlay = document.getElementById('editModalOverlay');
        const form = document.getElementById('editForm');
        if (!overlay || !form) return;
        const modalOpen = overlay.style.display === 'flex';
        const active = document.activeElement && document.activeElement !== document.body;
        if (!modalOpen && !active) {
            rfidInput.focus();
        }
    });
    // Close modal when clicking outside content
    const overlay = document.getElementById('editModalOverlay');
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeEditModal();
            }
        });
    }
});

// Filter functionality
function filterByStatus(status) {
    const rows = document.querySelectorAll('.codes-table tbody tr');
    rows.forEach(row => {
        if (status === '' || row.classList.contains('status-' + status)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Open edit modal with prefilled values
function openEditModal(btn) {
    const overlay = document.getElementById('editModalOverlay');
    const form = document.getElementById('editForm');
    if (!overlay || !form) return;

    // Fill fields
    form.querySelector('input[name="rfid_id"]').value = btn.dataset.id || '';
    const rfidCodeEl = document.getElementById('edit_rfid_code');
    if (rfidCodeEl) rfidCodeEl.textContent = btn.dataset.rfid || '';

    // Status
    const statusSel = form.querySelector('select[name="status"]');
    if (statusSel) statusSel.value = btn.dataset.status || 'available';

    // Scanned at -> convert "YYYY-MM-DD HH:MM:SS" to datetime-local value "YYYY-MM-DDTHH:MM"
    const scannedRaw = btn.dataset.scanned || '';
    let dtValue = '';
    if (scannedRaw) {
        const parts = scannedRaw.replace(' ', 'T').slice(0,16);
        dtValue = parts;
    }
    const scannedEl = form.querySelector('input[name="scanned_at"]');
    if (scannedEl) scannedEl.value = dtValue;

    // Assigned email
    const emailEl = form.querySelector('input[name="assigned_email"]');
    if (emailEl) emailEl.value = btn.dataset.assignedemail || '';

    // Notes
    const notesEl = form.querySelector('textarea[name="notes"]');
    if (notesEl) notesEl.value = btn.dataset.notes || '';

    overlay.style.display = 'flex';
}

function closeEditModal() {
    const overlay = document.getElementById('editModalOverlay');
    if (overlay) overlay.style.display = 'none';
}

// Toast notification function
function showToast(message, type) {
    // Remove any existing toasts
    const existingToast = document.querySelector('.toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        ${type === 'success' ? '✅' : '❌'} 
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    // Hide toast after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// Auto-refresh to keep data current (optional)
setInterval(function() {
    // Uncomment to enable auto-refresh every 30 seconds
    // window.location.reload();
}, 30000);
</script>

<!-- Edit RFID Modal -->
<div id="editModalOverlay" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h4>Edit RFID: <span id="edit_rfid_code"></span></h4>
            <button class="modal-close" onclick="closeEditModal()">✖</button>
        </div>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="edit_rfid">
            <input type="hidden" name="rfid_id" value="">
            <div class="modal-body">
                <div class="form-row">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="available">Available</option>
                        <option value="assigned">Assigned</option>
                        <option value="disabled">Disabled</option>
                    </select>
                </div>
                <div class="form-row">
                    <label>Scanned Date</label>
                    <input type="datetime-local" name="scanned_at">
                </div>
                <div class="form-row">
                    <label>Assigned To (Resident Email)</label>
                    <input type="email" name="assigned_email" placeholder="name@example.com">
                </div>
                <div class="form-row">
                    <label>Notes</label>
                    <textarea name="notes" rows="3" placeholder="Optional notes..."></textarea>
                </div>
                <small>Leave "Assigned To" empty to unassign. If the email matches a resident, it will be linked automatically.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>