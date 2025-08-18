<?php
$page_title = 'My Profile - GUMAOC';
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: logout.php');
        exit();
    }
} catch (PDOException $e) {
    $error = 'Database error. Please try again.';
}

// Handle profile update
if ($_POST && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $civil_status = $_POST['civil_status'];
    
    try {
        // Check if phone already exists for other users
        $stmt = $pdo->prepare("SELECT id FROM residents WHERE phone = ? AND id != ?");
        $stmt->execute([$phone, $user_id]);
        if ($stmt->fetch()) {
            $error = 'Phone number already registered by another user.';
        } else {
            // Update profile
            $stmt = $pdo->prepare("
                UPDATE residents 
                SET first_name = ?, middle_name = ?, last_name = ?, 
                    phone = ?, address = ?, civil_status = ?
                WHERE id = ?
            ");
            $stmt->execute([$first_name, $middle_name, $last_name, $phone, $address, $civil_status, $user_id]);
            
            // Update session
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            
            $success = 'Profile updated successfully!';
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        }
    } catch (PDOException $e) {
        $error = 'Database error. Please try again.';
    }
}

// Handle password change
if ($_POST && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } elseif (!password_verify($current_password, $user['password'])) {
        $error = 'Current password is incorrect.';
    } else {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE residents SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);
            
            $success = 'Password changed successfully!';
        } catch (PDOException $e) {
            $error = 'Database error. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<style>
.profile-container {
    max-width: 1000px;
    margin: 40px auto;
    padding: 0 20px;
}

.profile-header {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(15px);
    border-radius: 20px;
    padding: 40px;
    text-align: center;
    margin-bottom: 30px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.profile-avatar {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, #4a7c59, #357a3c);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 48px;
    font-weight: bold;
    margin: 0 auto 20px;
    box-shadow: 0 10px 30px rgba(74, 124, 89, 0.3);
}

.profile-name {
    font-size: 32px;
    font-weight: 700;
    color: #2d5a27;
    margin-bottom: 10px;
}

.profile-email {
    font-size: 16px;
    color: #666;
    margin-bottom: 20px;
}

.profile-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.stat-card {
    background: rgba(74, 124, 89, 0.1);
    padding: 20px;
    border-radius: 15px;
    text-align: center;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #2d5a27;
}

.stat-label {
    font-size: 14px;
    color: #666;
    margin-top: 5px;
}

.profile-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.profile-section {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(15px);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.section-title {
    font-size: 24px;
    font-weight: 600;
    color: #2d5a27;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #2d5a27;
    font-weight: 600;
    font-size: 14px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #4a7c59;
    box-shadow: 0 0 0 3px rgba(74, 124, 89, 0.15);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.btn {
    background: linear-gradient(135deg, #4a7c59, #357a3c);
    color: white;
    border: none;
    border-radius: 10px;
    padding: 12px 24px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(74, 124, 89, 0.3);
}

.btn:hover {
    background: linear-gradient(135deg, #357a3c, #2d5a27);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(74, 124, 89, 0.4);
}

.btn-full {
    width: 100%;
}

.alert {
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda, #a3d9a5);
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: linear-gradient(135deg, #f8d7da, #f1aeb5);
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #2d5a27;
}

.info-value {
    color: #666;
}

.rfid-section {
    background: linear-gradient(135deg, rgba(74, 124, 89, 0.1), rgba(53, 122, 60, 0.05));
    border: 2px dashed #4a7c59;
    border-radius: 15px;
    padding: 20px;
    text-align: center;
    margin-top: 20px;
}

@media (max-width: 768px) {
    .profile-content {
        grid-template-columns: 1fr;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .profile-container {
        padding: 0 10px;
    }
    
    .profile-header {
        padding: 30px 20px;
    }
    
    .profile-name {
        font-size: 24px;
    }
    
    .profile-avatar {
        width: 80px;
        height: 80px;
        font-size: 32px;
    }
}
</style>

<div class="profile-container">
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <div class="profile-header">
        <div class="profile-avatar">
            <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
        </div>
        <div class="profile-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
        <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
        
        <div class="profile-stats">
            <div class="stat-card">
                <div class="stat-number">5</div>
                <div class="stat-label">Applications Submitted</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">3</div>
                <div class="stat-label">Pending Requests</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo date('M Y', strtotime($user['created_at'])); ?></div>
                <div class="stat-label">Member Since</div>
            </div>
        </div>
    </div>
    
    <div class="profile-content">
        <!-- Personal Information -->
        <div class="profile-section">
            <h3 class="section-title">👤 Personal Information</h3>
            
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="middle_name">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="civil_status">Civil Status</label>
                        <select id="civil_status" name="civil_status" required>
                            <option value="Single" <?php echo ($user['civil_status'] === 'Single') ? 'selected' : ''; ?>>Single</option>
                            <option value="Married" <?php echo ($user['civil_status'] === 'Married') ? 'selected' : ''; ?>>Married</option>
                            <option value="Widowed" <?php echo ($user['civil_status'] === 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                            <option value="Separated" <?php echo ($user['civil_status'] === 'Separated') ? 'selected' : ''; ?>>Separated</option>
                        </select>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                </div>
                
                <button type="submit" name="update_profile" class="btn btn-full">💾 Update Profile</button>
            </form>
        </div>
        
        <!-- Account Security -->
        <div class="profile-section">
            <h3 class="section-title">🔒 Account Security</h3>
            
            <div class="info-item">
                <span class="info-label">Email</span>
                <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Account Status</span>
                <span class="info-value" style="color: #28a745;">✅ Active</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Member Since</span>
                <span class="info-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
            </div>
            
            <form method="POST" style="margin-top: 30px;">
                <h4 style="color: #2d5a27; margin-bottom: 20px;">Change Password</h4>
                
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" name="change_password" class="btn btn-full">🔑 Change Password</button>
            </form>
            
            <div class="rfid-section">
                <h4 style="color: #4a7c59; margin-bottom: 10px;">🏷️ RFID Card</h4>
                <?php if ($user['rfid_code']): ?>
                    <p style="color: #28a745; margin: 0;">✅ RFID Card Linked</p>
                    <p style="font-size: 12px; color: #666; margin: 5px 0 0 0;">Code: <?php echo htmlspecialchars($user['rfid_code']); ?></p>
                <?php else: ?>
                    <p style="color: #ffc107; margin: 0;">⚠️ No RFID Card Linked</p>
                    <a href="link-rfid.php" style="color: #4a7c59; text-decoration: none; font-weight: 600;">Link RFID Card →</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>