<?php
session_start();
include '../includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'restore_rfid':
                $rfid_id = (int)$_POST['rfid_id'];
                
                $stmt = $pdo->prepare("UPDATE scanned_rfid_codes SET status = 'available' WHERE id = ?");
                if ($stmt->execute([$rfid_id])) {
                    $_SESSION['success'] = "RFID code restored successfully!";
                } else {
                    $_SESSION['error'] = "Failed to restore RFID code.";
                }
                break;
                
            case 'delete_rfid':
                $rfid_id = (int)$_POST['rfid_id'];
                
                $stmt = $pdo->prepare("DELETE FROM scanned_rfid_codes WHERE id = ?");
                if ($stmt->execute([$rfid_id])) {
                    $_SESSION['success'] = "RFID code deleted permanently!";
                } else {
                    $_SESSION['error'] = "Failed to delete RFID code.";
                }
                break;
        }
        header('Location: archived-rfid.php');
        exit;
    }
}

// Fetch archived RFID codes with pagination
$page = (int)($_GET['page'] ?? 1);
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count of archived codes
$total_stmt = $pdo->query("SELECT COUNT(*) FROM scanned_rfid_codes WHERE status = 'archived'");
$total_codes = $total_stmt->fetchColumn();
$total_pages = ceil($total_codes / $per_page);

// Fetch archived codes for current page
$codes_stmt = $pdo->prepare("
    SELECT s.*, 
           r.first_name, r.last_name, r.email as resident_email
    FROM scanned_rfid_codes s 
    LEFT JOIN residents r ON s.assigned_to_resident_id = r.id 
    WHERE s.status = 'archived'
    ORDER BY s.scanned_at DESC 
    LIMIT $per_page OFFSET $offset
");
$codes_stmt->execute();
$archived_codes = $codes_stmt->fetchAll(PDO::FETCH_ASSOC);

$base_path = '../';
$page_title = 'Archived RFID Codes - Admin Panel';
$header_title = 'Archived RFID Codes';
$header_subtitle = 'View and manage archived RFID codes';

include '../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="content-header">
        <h1>📦 Archived RFID Codes</h1>
        <p>View and manage archived RFID codes</p>
        <a href="rfid-scanner.php" class="btn-back">← Back to Scanner</a>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon">📦</div>
            <div class="stat-info">
                <h3><?php echo $total_codes; ?></h3>
                <p>Archived Codes</p>
            </div>
        </div>
    </div>

    <!-- Archived RFID Codes List -->
    <div class="codes-section">
        <div class="section-header">
            <h3>📋 Archived RFID Codes</h3>
        </div>
        
        <?php if (empty($archived_codes)): ?>
            <div class="no-codes">
                <p>No archived RFID codes found.</p>
            </div>
        <?php else: ?>
            <div class="codes-table-wrapper">
                <table class="codes-table">
                    <thead>
                        <tr>
                            <th>RFID Code</th>
                            <th>Scanned Date</th>
                            <th>Archived Date</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($archived_codes as $code): ?>
                        <tr>
                            <td class="rfid-code"><?php echo htmlspecialchars($code['rfid_code']); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($code['scanned_at'])); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($code['updated_at'])); ?></td>
                            <td><?php echo htmlspecialchars($code['notes'] ?: 'No notes'); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Restore this RFID code?')">
                                        <input type="hidden" name="action" value="restore_rfid">
                                        <input type="hidden" name="rfid_id" value="<?php echo $code['id']; ?>">
                                        <button type="submit" class="btn-action btn-restore" title="Restore">↩️</button>
                                    </form>
                                    
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Permanently delete this RFID code? This action cannot be undone.')">
                                        <input type="hidden" name="action" value="delete_rfid">
                                        <input type="hidden" name="rfid_id" value="<?php echo $code['id']; ?>">
                                        <button type="submit" class="btn-action btn-delete" title="Delete Permanently">🗑️</button>
                                    </form>
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
        <?php endif; ?>
    </div>
</div>

<style>
/* RFID Scanner Styles */
.admin-content { padding: 2rem; max-width: 1400px; margin: 0 auto; }
.content-header { margin-bottom: 2rem; position: relative; }
.content-header h1 { color: #2c3e50; font-size: 2rem; margin-bottom: 0.5rem; }
.content-header p { color: #7f8c8d; }
.btn-back {
    position: absolute;
    right: 0;
    top: 0;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.9), rgba(41, 128, 185, 0.9));
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}
.btn-back:hover {
    background: linear-gradient(135deg, rgba(41, 128, 185, 0.9), rgba(52, 152, 219, 0.9));
    transform: translateY(-2px);
}

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
.stat-card.total { border-left: 4px solid #9b59b6; }

.stat-icon { font-size: 2rem; }
.stat-info h3 { margin: 0; font-size: 1.8rem; color: #2c3e50; }
.stat-info p { margin: 0; color: #7f8c8d; }

.codes-section { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.section-header { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 1.5rem; 
}

.no-codes {
    text-align: center;
    padding: 3rem;
    color: #7f8c8d;
}

.codes-table { width: 100%; border-collapse: collapse; }
.codes-table th, .codes-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #ecf0f1; }
.codes-table th { background: #f8f9fa; font-weight: 600; color: #2c3e50; }

.rfid-code { font-family: monospace; font-size: 1.1rem; font-weight: 600; }

.action-buttons { display: flex; gap: 0.5rem; }
.btn-action { 
    padding: 0.5rem; 
    border: none; 
    border-radius: 4px; 
    cursor: pointer; 
    font-size: 0.9rem; 
    transition: all 0.2s ease; 
}
.btn-restore:hover { background: #d5f4e6; }
.btn-delete:hover { background: #fadbd8; }

.pagination { 
    display: flex; 
    justify-content: center; 
    align-items: center; 
    gap: 1rem; 
    margin-top: 2rem; 
}
.pagination-btn { 
    padding: 0.5rem 1rem; 
    background: rgba(52, 152, 219, 0.9); 
    color: white; 
    text-decoration: none; 
    border-radius: 4px; 
}
.pagination-btn:hover { background: rgba(41, 128, 185, 0.9); }

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

@media (max-width: 768px) {
    .admin-content { padding: 1rem; }
    .stats-grid { grid-template-columns: 1fr; }
    .section-header { flex-direction: column; gap: 1rem; }
    .codes-table-wrapper { overflow-x: auto; }
    .btn-back {
        position: relative;
        margin-top: 1rem;
    }
}
</style>

<script>
// Show toast notifications
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['success'])): ?>
        showToast('<?php echo addslashes($_SESSION['success']); ?>', 'success');
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        showToast('<?php echo addslashes($_SESSION['error']); ?>', 'error');
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
});

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
</script>

<?php include '../includes/admin_footer.php'; ?>