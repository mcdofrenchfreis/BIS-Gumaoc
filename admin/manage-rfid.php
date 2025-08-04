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
            case 'approve_registration':
                $reg_id = (int)$_POST['reg_id'];
                $stmt = $pdo->prepare("UPDATE rfid_registrations SET status = 'approved', approved_date = NOW() WHERE id = ?");
                if ($stmt->execute([$reg_id])) {
                    // Also create entry in rfid_users table for approved registrations
                    $reg_stmt = $pdo->prepare("SELECT * FROM rfid_registrations WHERE id = ?");
                    $reg_stmt->execute([$reg_id]);
                    $registration = $reg_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($registration) {
                        $full_name = $registration['first_name'] . ' ' . ($registration['middle_name'] ? $registration['middle_name'] . ' ' : '') . $registration['last_name'];
                        $user_stmt = $pdo->prepare("INSERT INTO rfid_users (rfid_tag, full_name, email, phone, address, id_type, id_number, status, birth_date) VALUES (?, ?, '', ?, ?, 'Other', '', 'active', ?)");
                        $user_stmt->execute([$registration['rfid_number'], $full_name, $registration['contact_number'], $registration['address'], $registration['birth_date']]);
                    }
                    $_SESSION['success'] = "Registration approved successfully!";
                } else {
                    $_SESSION['error'] = "Failed to approve registration.";
                }
                break;
                
            case 'reject_registration':
                $reg_id = (int)$_POST['reg_id'];
                $stmt = $pdo->prepare("UPDATE rfid_registrations SET status = 'rejected' WHERE id = ?");
                if ($stmt->execute([$reg_id])) {
                    $_SESSION['success'] = "Registration rejected.";
                } else {
                    $_SESSION['error'] = "Failed to reject registration.";
                }
                break;
                
            case 'delete_registration':
                $reg_id = (int)$_POST['reg_id'];
                $stmt = $pdo->prepare("DELETE FROM rfid_registrations WHERE id = ?");
                if ($stmt->execute([$reg_id])) {
                    $_SESSION['success'] = "Registration deleted successfully!";
                } else {
                    $_SESSION['error'] = "Failed to delete registration.";
                }
                break;
                
            case 'register_rfid':
                $rfid_tag = trim($_POST['rfid_tag']);
                $full_name = htmlspecialchars($_POST['full_name']);
                $email = htmlspecialchars($_POST['email']);
                $phone = htmlspecialchars($_POST['phone']);
                $address = htmlspecialchars($_POST['address']);
                $id_type = htmlspecialchars($_POST['id_type']);
                $id_number = htmlspecialchars($_POST['id_number']);
                $status = 'active';
                
                // Check if RFID tag already exists
                $check_stmt = $pdo->prepare("SELECT id FROM rfid_users WHERE rfid_tag = ?");
                $check_stmt->execute([$rfid_tag]);
                
                if ($check_stmt->rowCount() > 0) {
                    $_SESSION['error'] = "RFID tag already registered!";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO rfid_users (rfid_tag, full_name, email, phone, address, id_type, id_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    if ($stmt->execute([$rfid_tag, $full_name, $email, $phone, $address, $id_type, $id_number, $status])) {
                        $_SESSION['success'] = "RFID registration successful!";
                    } else {
                        $_SESSION['error'] = "Failed to register RFID.";
                    }
                }
                break;
                
            case 'update_rfid':
                $user_id = (int)$_POST['user_id'];
                $full_name = htmlspecialchars($_POST['full_name']);
                $email = htmlspecialchars($_POST['email']);
                $phone = htmlspecialchars($_POST['phone']);
                $address = htmlspecialchars($_POST['address']);
                $id_type = htmlspecialchars($_POST['id_type']);
                $id_number = htmlspecialchars($_POST['id_number']);
                $status = htmlspecialchars($_POST['status']);
                
                $stmt = $pdo->prepare("UPDATE rfid_users SET full_name = ?, email = ?, phone = ?, address = ?, id_type = ?, id_number = ?, status = ? WHERE id = ?");
                if ($stmt->execute([$full_name, $email, $phone, $address, $id_type, $id_number, $status, $user_id])) {
                    $_SESSION['success'] = "RFID user updated successfully!";
                } else {
                    $_SESSION['error'] = "Failed to update user.";
                }
                break;
                
            case 'delete_rfid':
                $user_id = (int)$_POST['user_id'];
                $stmt = $pdo->prepare("DELETE FROM rfid_users WHERE id = ?");
                if ($stmt->execute([$user_id])) {
                    $_SESSION['success'] = "RFID user deleted successfully!";
                } else {
                    $_SESSION['error'] = "Failed to delete user.";
                }
                break;
                
            case 'log_access':
                $rfid_tag = trim($_POST['rfid_tag']);
                
                // Check if RFID exists and is active
                $user_stmt = $pdo->prepare("SELECT * FROM rfid_users WHERE rfid_tag = ? AND status = 'active'");
                $user_stmt->execute([$rfid_tag]);
                $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    $log_stmt = $pdo->prepare("INSERT INTO rfid_access_logs (user_id, rfid_tag, full_name, access_time) VALUES (?, ?, ?, NOW())");
                    if ($log_stmt->execute([$user['id'], $rfid_tag, $user['full_name']])) {
                        $_SESSION['success'] = "Access logged for " . $user['full_name'];
                    } else {
                        $_SESSION['error'] = "Failed to log access.";
                    }
                } else {
                    $_SESSION['error'] = "RFID tag not found or inactive!";
                }
                break;
        }
        header('Location: manage-rfid.php');
        exit;
    }
}

// Create RFID tables if they don't exist
$pdo->exec("
    CREATE TABLE IF NOT EXISTS rfid_users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        rfid_tag VARCHAR(20) UNIQUE NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(255),
        phone VARCHAR(20),
        address TEXT,
        id_type ENUM('National ID', 'Drivers License', 'Passport', 'Other') DEFAULT 'National ID',
        id_number VARCHAR(50),
        birth_date DATE,
        status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS rfid_access_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        rfid_tag VARCHAR(20),
        full_name VARCHAR(255),
        access_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES rfid_users(id) ON DELETE CASCADE
    )
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS rfid_registrations (
        id INT PRIMARY KEY AUTO_INCREMENT,
        rfid_number VARCHAR(50) UNIQUE NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        middle_name VARCHAR(100),
        last_name VARCHAR(100) NOT NULL,
        birth_date DATE NOT NULL,
        contact_number VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        card_type ENUM('resident', 'employee', 'visitor') DEFAULT 'resident',
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        approved_date TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
");

// Fetch pending registrations from user-side
$pending_registrations = $pdo->query("SELECT * FROM rfid_registrations WHERE status = 'pending' ORDER BY created_at ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all processed registrations
$processed_registrations = $pdo->query("SELECT * FROM rfid_registrations WHERE status != 'pending' ORDER BY updated_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all RFID users
$users = $pdo->query("SELECT * FROM rfid_users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent access logs
$recent_logs = $pdo->query("SELECT * FROM rfid_access_logs ORDER BY access_time DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

$base_path = '../';
$page_title = 'RFID Management - Admin Panel';
$header_title = 'RFID Access Management';
$header_subtitle = 'Register and manage RFID access cards';

include '../includes/header.php';
?>

<body>
    <div class="admin-container">
        <div class="admin-header">
            <div>
                <h1>📡 RFID Access Management</h1>
                <p>Register and manage RFID access cards for government services</p>
            </div>
            <div class="admin-nav-buttons">
                <a href="dashboard.php" class="admin-btn">📊 Dashboard</a>
                <a href="manage-services.php" class="admin-btn">⚙️ Services</a>
                <a href="manage-updates.php" class="admin-btn">📢 Updates</a>
                <a href="../pages/rfid-registration.php" class="admin-btn" target="_blank">📝 User Registration</a>
                <a href="../index.php" class="admin-btn">🏠 View Site</a>
            </div>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <strong>Success!</strong> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <strong>Error!</strong> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-info">
                    <h3><?php echo count($pending_registrations); ?></h3>
                    <p>Pending Registrations</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3><?php echo count($users); ?></h3>
                    <p>Active RFID Users</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-info">
                    <h3><?php echo count($recent_logs); ?></h3>
                    <p>Recent Access Logs</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3><?php echo count($processed_registrations); ?></h3>
                    <p>Processed Applications</p>
                </div>
            </div>
        </div>

        <!-- Pending User Registrations -->
        <?php if (!empty($pending_registrations)): ?>
        <div class="admin-section urgent">
            <h2>⏳ Pending User Registrations (<?php echo count($pending_registrations); ?>)</h2>
            <div class="pending-grid">
                <?php foreach ($pending_registrations as $reg): ?>
                    <div class="pending-card">
                        <div class="pending-header">
                            <h3><?php echo htmlspecialchars($reg['first_name'] . ' ' . ($reg['middle_name'] ? $reg['middle_name'] . ' ' : '') . $reg['last_name']); ?></h3>
                            <span class="card-type-badge type-<?php echo $reg['card_type']; ?>">
                                <?php echo ucfirst($reg['card_type']); ?>
                            </span>
                        </div>
                        
                        <div class="pending-details">
                            <div class="detail-row">
                                <strong>RFID Number:</strong>
                                <code><?php echo htmlspecialchars($reg['rfid_number']); ?></code>
                            </div>
                            <div class="detail-row">
                                <strong>Birth Date:</strong>
                                <?php echo date('M j, Y', strtotime($reg['birth_date'])); ?>
                            </div>
                            <div class="detail-row">
                                <strong>Contact:</strong>
                                <?php echo htmlspecialchars($reg['contact_number']); ?>
                            </div>
                            <div class="detail-row">
                                <strong>Address:</strong>
                                <?php echo htmlspecialchars(substr($reg['address'], 0, 50)) . (strlen($reg['address']) > 50 ? '...' : ''); ?>
                            </div>
                            <div class="detail-row">
                                <strong>Applied:</strong>
                                <?php echo date('M j, Y g:i A', strtotime($reg['created_at'])); ?>
                            </div>
                        </div>
                        
                        <div class="pending-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="approve_registration">
                                <input type="hidden" name="reg_id" value="<?php echo $reg['id']; ?>">
                                <button type="submit" class="admin-btn btn-success" onclick="return confirm('Approve this RFID registration?')">
                                    ✅ Approve
                                </button>
                            </form>
                            
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="reject_registration">
                                <input type="hidden" name="reg_id" value="<?php echo $reg['id']; ?>">
                                <button type="submit" class="admin-btn btn-danger" onclick="return confirm('Reject this RFID registration?')">
                                    ❌ Reject
                                </button>
                            </form>
                            
                            <button class="admin-btn btn-info" onclick="viewRegistrationDetails(<?php echo $reg['id']; ?>)">
                                👁️ View Details
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Access Section -->
        <div class="admin-section">
            <h2>🚪 Quick Access Log</h2>
            <form method="POST" class="access-form">
                <input type="hidden" name="action" value="log_access">
                <div class="access-input-group">
                    <label>Scan RFID Card:</label>
                    <input type="text" name="rfid_tag" id="quick-access-rfid" placeholder="Place RFID card on reader..." autocomplete="off" autofocus>
                    <button type="submit" class="admin-btn btn-primary">Log Access</button>
                </div>
                <p class="help-text">💡 Simply tap/scan the RFID card on your reader and press Enter</p>
            </form>
        </div>

        <!-- Register New RFID (Admin) -->
        <div class="admin-section">
            <h2>➕ Register New RFID Card (Admin)</h2>
            <form method="POST" class="rfid-form">
                <input type="hidden" name="action" value="register_rfid">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>RFID Tag ID *</label>
                        <input type="text" name="rfid_tag" id="rfid-input" placeholder="Scan RFID card..." autocomplete="off" required>
                        <small>Tap the RFID card on your reader</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email">
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone">
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Address</label>
                        <textarea name="address" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>ID Type</label>
                        <select name="id_type" required>
                            <option value="National ID">National ID</option>
                            <option value="Drivers License">Driver's License</option>
                            <option value="Passport">Passport</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>ID Number</label>
                        <input type="text" name="id_number">
                    </div>
                </div>
                
                <button type="submit" class="admin-btn btn-primary">Register RFID Card</button>
            </form>
        </div>

        <!-- Recent Access Logs -->
        <div class="admin-section">
            <h2>📋 Recent Access Logs</h2>
            <div class="logs-container">
                <?php if (empty($recent_logs)): ?>
                    <p class="no-data">No access logs yet.</p>
                <?php else: ?>
                    <table class="logs-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Name</th>
                                <th>RFID Tag</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_logs as $log): ?>
                                <tr>
                                    <td><?php echo date('M j, Y g:i A', strtotime($log['access_time'])); ?></td>
                                    <td><?php echo htmlspecialchars($log['full_name']); ?></td>
                                    <td><code><?php echo htmlspecialchars($log['rfid_tag']); ?></code></td>
                                    <td><span class="status-badge status-success">✅ Granted</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Processed Registrations -->
        <?php if (!empty($processed_registrations)): ?>
        <div class="admin-section">
            <h2>📋 Processed Registrations (<?php echo count($processed_registrations); ?>)</h2>
            <div class="processed-container">
                <table class="processed-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>RFID Number</th>
                            <th>Card Type</th>
                            <th>Status</th>
                            <th>Processed Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($processed_registrations as $reg): ?>
                            <tr class="registration-row status-<?php echo $reg['status']; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($reg['first_name'] . ' ' . ($reg['middle_name'] ? $reg['middle_name'] . ' ' : '') . $reg['last_name']); ?></strong>
                                </td>
                                <td>
                                    <code><?php echo htmlspecialchars($reg['rfid_number']); ?></code>
                                </td>
                                <td>
                                    <span class="card-type-badge type-<?php echo $reg['card_type']; ?>">
                                        <?php echo ucfirst($reg['card_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $reg['status']; ?>">
                                        <?php echo $reg['status'] === 'approved' ? '✅ Approved' : '❌ Rejected'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $reg['approved_date'] ? date('M j, Y g:i A', strtotime($reg['approved_date'])) : date('M j, Y g:i A', strtotime($reg['updated_at'])); ?>
                                </td>
                                <td>
                                    <button class="action-btn view-btn" onclick="viewRegistrationDetails(<?php echo $reg['id']; ?>)" title="View Details">
                                        👁️
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_registration">
                                        <input type="hidden" name="reg_id" value="<?php echo $reg['id']; ?>">
                                        <button type="submit" class="action-btn delete-btn" onclick="return confirm('Delete this registration record?')" title="Delete">
                                            🗑️
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Registered Users -->
        <div class="admin-section">
            <h2>👥 Registered RFID Users (<?php echo count($users); ?>)</h2>
            <div class="users-list">
                <?php if (empty($users)): ?>
                    <p class="no-data">No RFID users registered yet.</p>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <div class="user-card">
                            <div class="user-info">
                                <div class="user-header">
                                    <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </div>
                                <div class="user-details">
                                    <p><strong>RFID:</strong> <code><?php echo htmlspecialchars($user['rfid_tag']); ?></code></p>
                                    <?php if ($user['email']): ?>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($user['phone']): ?>
                                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($user['birth_date']): ?>
                                        <p><strong>Birth Date:</strong> <?php echo date('M j, Y', strtotime($user['birth_date'])); ?></p>
                                    <?php endif; ?>
                                    <?php if ($user['id_number']): ?>
                                        <p><strong><?php echo htmlspecialchars($user['id_type']); ?>:</strong> <?php echo htmlspecialchars($user['id_number']); ?></p>
                                    <?php endif; ?>
                                    <p><strong>Registered:</strong> <?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
                                </div>
                            </div>
                            
                            <div class="user-actions">
                                <button class="admin-btn btn-small" onclick="editUser(<?php echo $user['id']; ?>)">✏️ Edit</button>
                                <button class="admin-btn btn-danger btn-small" onclick="deleteUser(<?php echo $user['id']; ?>)">🗑️ Delete</button>
                            </div>
                            
                            <!-- Edit Form (Hidden by default) -->
                            <div id="edit-form-<?php echo $user['id']; ?>" class="edit-form" style="display: none;">
                                <form method="POST" class="rfid-form">
                                    <input type="hidden" name="action" value="update_rfid">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label>Full Name</label>
                                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Phone</label>
                                            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="status" required>
                                                <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                <option value="suspended" <?php echo $user['status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group full-width">
                                            <label>Address</label>
                                            <textarea name="address" rows="2"><?php echo htmlspecialchars($user['address']); ?></textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>ID Type</label>
                                            <select name="id_type" required>
                                                <option value="National ID" <?php echo $user['id_type'] === 'National ID' ? 'selected' : ''; ?>>National ID</option>
                                                <option value="Drivers License" <?php echo $user['id_type'] === 'Drivers License' ? 'selected' : ''; ?>>Driver's License</option>
                                                <option value="Passport" <?php echo $user['id_type'] === 'Passport' ? 'selected' : ''; ?>>Passport</option>
                                                <option value="Other" <?php echo $user['id_type'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>ID Number</label>
                                            <input type="text" name="id_number" value="<?php echo htmlspecialchars($user['id_number']); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="admin-btn btn-primary">Update User</button>
                                        <button type="button" class="admin-btn" onclick="cancelEdit(<?php echo $user['id']; ?>)">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Registration Details Modal -->
    <div id="registrationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>📝 Registration Details</h3>
                <span class="modal-close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>

    <style>
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #1976d2 0%, #2196f3 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-nav-buttons {
            display: flex;
            gap: 1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .stat-icon {
            font-size: 2.5rem;
        }
        
        .stat-info h3 {
            font-size: 2rem;
            margin: 0;
            color: #495057;
        }
        
        .stat-info p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .admin-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .admin-section.urgent {
            border: 2px solid #ff9800;
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
        }
        
        .admin-section h2 {
            color: #1976d2;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }
        
        .admin-section.urgent h2 {
            color: #f57c00;
        }
        
        .pending-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
        }
        
        .pending-card {
            background: white;
            border: 1px solid #ffcc02;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(255, 152, 0, 0.1);
        }
        
        .pending-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .pending-header h3 {
            margin: 0;
            color: #f57c00;
            font-size: 1.1rem;
        }
        
        .pending-details {
            margin-bottom: 1.5rem;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .detail-row strong {
            color: #495057;
            min-width: 100px;
        }
        
        .detail-row code {
            background: #fff3e0;
            color: #f57c00;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-family: monospace;
        }
        
        .pending-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .card-type-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .type-resident { background: #d1ecf1; color: #0c5460; }
        .type-employee { background: #d4edda; color: #155724; }
        .type-visitor { background: #f8d7da; color: #721c24; }
        
        .access-form {
            background: #e3f2fd;
            padding: 1.5rem;
            border-radius: 12px;
            border: 2px solid #2196f3;
        }
        
        .access-input-group {
            display: flex;
            gap: 1rem;
            align-items: end;
        }
        
        .access-input-group label {
            font-weight: 600;
            color: #1976d2;
            min-width: 120px;
        }
        
        .access-input-group input {
            flex: 1;
            padding: 1rem;
            border: 2px solid #2196f3;
            border-radius: 8px;
            font-size: 1.1rem;
            font-family: monospace;
        }
        
        .help-text {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #666;
        }
        
        .rfid-form {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2196f3;
        }
        
        .form-group small {
            color: #666;
            font-size: 0.8rem;
        }
        
        #rfid-input {
            font-family: monospace;
            background: #fff3e0;
            border-color: #ff9800;
        }
        
        .admin-btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: #1976d2;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1565c0;
        }
        
        .btn-success {
            background: #4caf50;
            color: white;
        }
        
        .btn-success:hover {
            background: #45a049;
        }
        
        .btn-danger {
            background: #d32f2f;
            color: white;
        }
        
        .btn-danger:hover {
            background: #b71c1c;
        }
        
        .btn-info {
            background: #2196f3;
            color: white;
        }
        
        .btn-info:hover {
            background: #1976d2;
        }
        
        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }
        
        .logs-container,
        .processed-container {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1rem;
            overflow-x: auto;
        }
        
        .logs-table,
        .processed-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            font-size: 0.9rem;
        }
        
        .logs-table th,
        .logs-table td,
        .processed-table th,
        .processed-table td {
            padding: 1rem 0.8rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .logs-table th,
        .processed-table th {
            background: #2196f3;
            color: white;
            font-weight: 600;
        }
        
        .logs-table code,
        .processed-table code {
            background: #f5f5f5;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-family: monospace;
        }
        
        .registration-row:hover {
            background: #f8f9fa;
        }
        
        .registration-row.status-approved {
            background: rgba(76, 175, 80, 0.05);
        }
        
        .registration-row.status-rejected {
            background: rgba(211, 47, 47, 0.05);
        }
        
        .users-list {
            display: grid;
            gap: 1.5rem;
        }
        
        .user-card {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 1.5rem;
            background: #f8f9fa;
        }
        
        .user-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .user-info h3 {
            margin: 0;
            color: #1976d2;
        }
        
        .user-details p {
            margin: 0.5rem 0;
            color: #666;
        }
        
        .user-details code {
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-family: monospace;
            font-weight: bold;
        }
        
        .user-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .edit-form {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e0e0e0;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
        }
        
        .action-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            padding: 0.5rem;
            margin: 0 0.2rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            background: #f8f9fa;
            transform: scale(1.1);
        }
        
        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #c8e6c9;
            color: #2e7d32;
        }
        
        .status-inactive {
            background: #ffecb3;
            color: #f57c00;
        }
        
        .status-suspended {
            background: #ffcdd2;
            color: #d32f2f;
        }
        
        .status-success {
            background: #c8e6c9;
            color: #2e7d32;
        }
        
        .status-approved {
            background: #c8e6c9;
            color: #2e7d32;
        }
        
        .status-rejected {
            background: #ffcdd2;
            color: #d32f2f;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #1976d2 0%, #2196f3 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-close {
            font-size: 2rem;
            cursor: pointer;
            line-height: 1;
        }
        
        .modal-body {
            padding: 2rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 2rem;
        }
        
        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .admin-nav-buttons {
                flex-direction: column;
                width: 100%;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .pending-grid {
                grid-template-columns: 1fr;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .access-input-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            .user-header {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
            
            .user-actions {
                justify-content: center;
            }
            
            .pending-actions {
                flex-direction: column;
            }
            
            .detail-row {
                flex-direction: column;
                gap: 0.2rem;
            }
            
            .logs-table,
            .processed-table {
                font-size: 0.8rem;
            }
            
            .logs-table th,
            .logs-table td,
            .processed-table th,
            .processed-table td {
                padding: 0.5rem 0.3rem;
            }
        }
    </style>

    <script>
        // Store all registrations for modal display
        const registrations = <?php echo json_encode(array_merge($pending_registrations, $processed_registrations)); ?>;
        
        // Auto-focus RFID input and handle automatic submission
        document.addEventListener('DOMContentLoaded', function() {
            const rfidInputs = document.querySelectorAll('#rfid-input, #quick-access-rfid');
            
            rfidInputs.forEach(input => {
                input.addEventListener('input', function() {
                    // Most RFID readers send data followed by Enter
                    // Convert to uppercase for consistency
                    this.value = this.value.toUpperCase();
                });
                
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        // For quick access, auto-submit
                        if (this.id === 'quick-access-rfid' && this.value.trim().length > 0) {
                            this.form.submit();
                        }
                    }
                });
            });
        });
        
        function viewRegistrationDetails(regId) {
            const registration = registrations.find(r => r.id == regId);
            
            if (registration) {
                const modalBody = document.getElementById('modalBody');
                modalBody.innerHTML = `
                    <div class="registration-details">
                        <div class="detail-section">
                            <h4>🏷️ Card Information</h4>
                            <div class="detail-grid">
                                <div><strong>RFID Number:</strong> <code>${registration.rfid_number}</code></div>
                                <div><strong>Card Type:</strong> <span class="card-type-badge type-${registration.card_type}">${registration.card_type.charAt(0).toUpperCase() + registration.card_type.slice(1)}</span></div>
                                <div><strong>Status:</strong> <span class="status-badge status-${registration.status}">${registration.status.charAt(0).toUpperCase() + registration.status.slice(1)}</span></div>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h4>👤 Personal Information</h4>
                            <div class="detail-grid">
                                <div><strong>Full Name:</strong> ${registration.first_name} ${registration.middle_name || ''} ${registration.last_name}</div>
                                <div><strong>Birth Date:</strong> ${new Date(registration.birth_date).toLocaleDateString()}</div>
                                <div><strong>Contact Number:</strong> ${registration.contact_number}</div>
                                <div><strong>Address:</strong> ${registration.address}</div>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h4>📅 Registration Information</h4>
                            <div class="detail-grid">
                                <div><strong>Applied:</strong> ${new Date(registration.created_at).toLocaleString()}</div>
                                <div><strong>Last Updated:</strong> ${new Date(registration.updated_at).toLocaleString()}</div>
                                ${registration.approved_date ? `<div><strong>Processed Date:</strong> ${new Date(registration.approved_date).toLocaleString()}</div>` : ''}
                            </div>
                        </div>
                    </div>
                `;
                
                document.getElementById('registrationModal').style.display = 'block';
            }
        }
        
        function closeModal() {
            document.getElementById('registrationModal').style.display = 'none';
        }
        
        function editUser(userId) {
            // Hide all edit forms
            document.querySelectorAll('.edit-form').forEach(form => {
                form.style.display = 'none';
            });
            
            // Show the selected edit form
            document.getElementById('edit-form-' + userId).style.display = 'block';
        }
        
        function cancelEdit(userId) {
            document.getElementById('edit-form-' + userId).style.display = 'none';
        }
        
        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this RFID user? This will also delete all access logs. This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_rfid">
                    <input type="hidden" name="user_id" value="${userId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('registrationModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
        
        // Auto-refresh access logs every 30 seconds
        setInterval(function() {
            // Only refresh if not currently editing and no modal is open
            if (document.querySelectorAll('.edit-form[style*="block"]').length === 0 && 
                document.getElementById('registrationModal').style.display === 'none') {
                location.reload();
            }
        }, 30000);
    </script>
</body>
</html>