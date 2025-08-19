<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

$base_path = '../';
$page_title = 'Account Settings - Barangay Gumaoc East';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception("User not found");
    }
} catch (Exception $e) {
    $error = "Error loading account data.";
}

// Handle form submissions
if ($_POST) {
    if (isset($_POST['update_profile'])) {
        // Update profile information
        try {
            $first_name = trim($_POST['first_name']);
            $middle_name = trim($_POST['middle_name']);
            $last_name = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $address = trim($_POST['address']);
            
            // Check if email is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM residents WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                throw new Exception("Email address is already in use by another account.");
            }
            
            $stmt = $pdo->prepare("UPDATE residents SET first_name = ?, middle_name = ?, last_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $result = $stmt->execute([$first_name, $middle_name, $last_name, $email, $phone, $address, $user_id]);
            
            if ($result) {
                $success = "Profile updated successfully!";
                $_SESSION['user_name'] = $first_name . ' ' . $last_name;
                
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    if (isset($_POST['change_password'])) {
        // Change password
        try {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Verify current password
            if (!password_verify($current_password, $user['password'])) {
                throw new Exception("Current password is incorrect.");
            }
            
            // Check if new passwords match
            if ($new_password !== $confirm_password) {
                throw new Exception("New passwords do not match.");
            }
            
            // Validate password strength
            if (strlen($new_password) < 6) {
                throw new Exception("Password must be at least 6 characters long.");
            }
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE residents SET password = ? WHERE id = ?");
            $result = $stmt->execute([$hashed_password, $user_id]);
            
            if ($result) {
                $success = "Password changed successfully!";
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    if (isset($_POST['update_rfid'])) {
        // Update RFID
        try {
            $rfid_code = trim($_POST['rfid_code']);
            
            if (!empty($rfid_code)) {
                // Check if RFID is already in use
                $stmt = $pdo->prepare("SELECT id FROM residents WHERE rfid_code = ? AND id != ?");
                $stmt->execute([$rfid_code, $user_id]);
                if ($stmt->fetch()) {
                    throw new Exception("RFID code is already in use by another account.");
                }
            }
            
            $stmt = $pdo->prepare("UPDATE residents SET rfid_code = ? WHERE id = ?");
            $result = $stmt->execute([$rfid_code ?: null, $user_id]);
            
            if ($result) {
                $success = $rfid_code ? "RFID registered successfully!" : "RFID removed successfully!";
                
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="settings-header">
        <div class="header-content">
            <h1>⚙️ Account Settings</h1>
            <p>Manage your account information and preferences</p>
        </div>
        <a href="profile.php" class="btn btn-secondary">
            <span class="btn-icon">👤</span>
            Back to Profile
        </a>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success">
        <span class="alert-icon">✅</span>
        <span class="alert-message"><?php echo $success; ?></span>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-error">
        <span class="alert-icon">❌</span>
        <span class="alert-message"><?php echo $error; ?></span>
    </div>
    <?php endif; ?>

    <div class="settings-content">
        <!-- Profile Information -->
        <div class="settings-section">
            <div class="section-header">
                <h2>👤 Profile Information</h2>
                <p>Update your personal information and contact details</p>
            </div>
            
            <form method="POST" class="settings-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">First Name <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="middle_name">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" 
                               value="<?php echo htmlspecialchars($user['middle_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <span class="btn-icon">💾</span>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Password Settings -->
        <div class="settings-section">
            <div class="section-header">
                <h2>🔐 Change Password</h2>
                <p>Update your account password for better security</p>
            </div>
            
            <form method="POST" class="settings-form">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="current_password">Current Password <span class="required">*</span></label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password <span class="required">*</span></label>
                        <input type="password" id="new_password" name="new_password" 
                               minlength="6" required>
                        <small class="form-help">Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password <span class="required">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               minlength="6" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <span class="btn-icon">🔐</span>
                        Change Password
                    </button>
                </div>
            </form>
        </div>

        <!-- RFID Settings -->
        <div class="settings-section">
            <div class="section-header">
                <h2>🏷️ RFID Management</h2>
                <p>Register or update your RFID card for quick access</p>
            </div>
            
            <div class="rfid-status">
                <div class="status-info">
                    <div class="status-icon">
                        <?php echo $user['rfid_code'] ? '✅' : '❌'; ?>
                    </div>
                    <div class="status-text">
                        <h3><?php echo $user['rfid_code'] ? 'RFID Registered' : 'No RFID Registered'; ?></h3>
                        <p>
                            <?php if ($user['rfid_code']): ?>
                                Your RFID card is registered and ready to use for quick login.
                            <?php else: ?>
                                Register your RFID card to enable quick login access.
                            <?php endif; ?>
                        </p>
                        <?php if ($user['rfid_code']): ?>
                        <p class="rfid-code">Card ID: <?php echo htmlspecialchars($user['rfid_code']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <form method="POST" class="settings-form">
                <div class="form-group">
                    <label for="rfid_code">RFID Card Code</label>
                    <input type="text" id="rfid_code" name="rfid_code" 
                           value="<?php echo htmlspecialchars($user['rfid_code'] ?? ''); ?>"
                           placeholder="Tap your RFID card here or enter manually"
                           style="font-family: monospace; letter-spacing: 2px;">
                    <small class="form-help">
                        <?php if ($user['rfid_code']): ?>
                            Update your RFID card or leave empty to remove current registration.
                        <?php else: ?>
                            Tap your RFID card in the field above or enter the code manually.
                        <?php endif; ?>
                    </small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="update_rfid" class="btn btn-primary">
                        <span class="btn-icon">🏷️</span>
                        <?php echo $user['rfid_code'] ? 'Update RFID' : 'Register RFID'; ?>
                    </button>
                    <?php if ($user['rfid_code']): ?>
                    <button type="submit" name="update_rfid" class="btn btn-danger" 
                            onclick="document.getElementById('rfid_code').value = ''; return true;">
                        <span class="btn-icon">🗑️</span>
                        Remove RFID
                    </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Account Information -->
        <div class="settings-section">
            <div class="section-header">
                <h2>📊 Account Information</h2>
                <p>View your account details and statistics</p>
            </div>
            
            <div class="account-info">
                <div class="info-grid">
                    <div class="info-item">
                        <label>Resident ID</label>
                        <span class="resident-id">#<?php echo str_pad($user['id'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Account Created</label>
                        <span><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Last Updated</label>
                        <span><?php echo date('F j, Y g:i A', strtotime($user['updated_at'] ?? $user['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Account Status</label>
                        <span class="status-active">✅ Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Global Styles */
body {
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: white;
    color: #2c3e50;
    line-height: 1.6;
}

.container {
    max-width: 1000px;
    margin: 20px auto;
    padding: 20px;
    background: white;
}

/* Settings Header */
.settings-header {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    padding: 2rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 10px 40px rgba(76, 175, 80, 0.3);
}

.header-content h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
    font-weight: 700;
}

.header-content p {
    margin: 0;
    opacity: 0.9;
    font-size: 1.1rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.8rem 1.5rem;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-size: 1rem;
}

.btn-primary {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

.btn-danger {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
    box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
}

/* Alerts */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    font-weight: 500;
}

.alert-success {
    background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(69, 160, 73, 0.1));
    border: 1px solid rgba(76, 175, 80, 0.3);
    color: #2e7d32;
}

.alert-error {
    background: linear-gradient(135deg, rgba(231, 76, 60, 0.1), rgba(192, 57, 43, 0.1));
    border: 1px solid rgba(231, 76, 60, 0.3);
    color: #c0392b;
}

.alert-icon {
    font-size: 1.2rem;
}

/* Settings Sections */
.settings-content {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.settings-section {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.section-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.section-header h2 {
    margin: 0 0 0.5rem 0;
    color: #2e7d32;
    font-size: 1.4rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-header p {
    margin: 0;
    color: #666;
    font-size: 0.95rem;
}

/* Forms */
.settings-form {
    margin-top: 1rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group {
    position: relative;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #2d5a27;
    font-size: 0.95rem;
}

.required {
    color: #e74c3c;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.8rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    font-family: inherit;
    transition: all 0.3s ease;
    background: white;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #4CAF50;
    box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

.form-help {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.85rem;
    color: #666;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-start;
    padding-top: 1rem;
    border-top: 2px solid #f8f9fa;
}

/* RFID Status */
.rfid-status {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.status-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.status-icon {
    font-size: 2rem;
    width: 60px;
    height: 60px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.status-text h3 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
    font-size: 1.2rem;
}

.status-text p {
    margin: 0 0 0.5rem 0;
    color: #666;
}

.rfid-code {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: #4CAF50;
    background: white;
    padding: 0.3rem 0.6rem;
    border-radius: 6px;
    display: inline-block;
}

/* Account Info */
.account-info {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-item label {
    font-weight: 600;
    color: #666;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0;
}

.info-item span {
    font-size: 1rem;
    color: #2c3e50;
    font-weight: 500;
}

.resident-id {
    font-family: 'Courier New', monospace;
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    padding: 0.3rem 0.6rem;
    border-radius: 6px;
    display: inline-block;
    font-weight: 600;
}

.status-active {
    color: #27ae60 !important;
    font-weight: 600 !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        margin: 10px;
        padding: 15px;
    }
    
    .settings-header {
        flex-direction: column;
        gap: 1.5rem;
        text-align: center;
        padding: 1.5rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .status-info {
        flex-direction: column;
        text-align: center;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Auto-focus RFID input for card tapping
document.getElementById('rfid_code').addEventListener('focus', function() {
    this.select();
});

// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (newPassword !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php include '../includes/footer.php'; ?>