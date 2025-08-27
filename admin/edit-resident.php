<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/AdminLogger.php';
require_once '../includes/email_service.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Initialize logger
$logger = new AdminLogger($pdo);

$error = '';
$success = '';
$resident_id = (int)($_GET['id'] ?? 0);

if ($resident_id <= 0) {
    header('Location: view-residents.php');
    exit;
}

// Fetch resident data
try {
    $stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ?");
    $stmt->execute([$resident_id]);
    $resident = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resident) {
        $_SESSION['toast_message'] = "Resident not found.";
        $_SESSION['toast_type'] = 'error';
        header('Location: view-residents.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'Database error while fetching resident data.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_resident'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $birthdate = $_POST['birthdate'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $civil_status = $_POST['civil_status'] ?? '';
    $status = $_POST['status'] ?? '';
    $rfid_code = trim($_POST['rfid_code'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($first_name)) $errors[] = "First name is required.";
    if (empty($last_name)) $errors[] = "Last name is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($phone)) $errors[] = "Phone number is required.";
    if (empty($address)) $errors[] = "Address is required.";
    if (empty($birthdate)) $errors[] = "Birthdate is required.";
    if (empty($gender)) $errors[] = "Gender is required.";
    if (empty($civil_status)) $errors[] = "Civil status is required.";
    if (empty($status)) $errors[] = "Status is required.";
    
    // Validate email format
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    // Validate phone format (basic)
    if (!empty($phone) && !preg_match('/^[0-9+\-\s()]+$/', $phone)) {
        $errors[] = "Invalid phone format.";
    }
    
    // Check for duplicate email (excluding current resident)
    if (!empty($email)) {
        $stmt = $pdo->prepare("SELECT id FROM residents WHERE email = ? AND id != ?");
        $stmt->execute([$email, $resident_id]);
        if ($stmt->fetch()) {
            $errors[] = "Email address is already in use by another resident.";
        }
    }
    
    // Check for duplicate phone (excluding current resident)
    if (!empty($phone)) {
        $stmt = $pdo->prepare("SELECT id FROM residents WHERE phone = ? AND id != ?");
        $stmt->execute([$phone, $resident_id]);
        if ($stmt->fetch()) {
            $errors[] = "Phone number is already in use by another resident.";
        }
    }
    
    // Check for duplicate RFID (excluding current resident)
    if (!empty($rfid_code)) {
        $stmt = $pdo->prepare("SELECT id FROM residents WHERE (rfid_code = ? OR rfid = ?) AND id != ?");
        $stmt->execute([$rfid_code, $rfid_code, $resident_id]);
        if ($stmt->fetch()) {
            $errors[] = "RFID code is already in use by another resident.";
        }
    }
    
    if (empty($errors)) {
        try {
            // Store original data for logging
            $original_data = $resident;
            
            // Check if we're activating a pending user without RFID
            $needsRFIDGeneration = ($original_data['status'] === 'pending' && 
                                  $status === 'active' && 
                                  empty($original_data['rfid_code']) &&
                                  empty($rfid_code));
            
            $final_rfid_code = $rfid_code;
            $generated_password = null;
            $email_sent = false;
            
            if ($needsRFIDGeneration) {
                // Auto-generate RFID and password for activation
                $final_rfid_code = EmailService::generateUniqueRFID($pdo);
                $generated_password = EmailService::generateTempPassword();
                
                // Update resident with generated credentials
                $hashed_password = password_hash($generated_password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("
                    UPDATE residents 
                    SET first_name = ?, middle_name = ?, last_name = ?, email = ?, phone = ?, 
                        address = ?, birthdate = ?, gender = ?, civil_status = ?, status = ?, 
                        rfid_code = ?, password = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                $result = $stmt->execute([
                    $first_name, $middle_name, $last_name, $email, $phone,
                    $address, $birthdate, $gender, $civil_status, $status,
                    $final_rfid_code, $hashed_password, $resident_id
                ]);
                
                if ($result) {
                    // Send confirmation email instead of activation email
                    $emailService = new EmailService();
                    $resident_name = $first_name . ' ' . $last_name;
                    $email_sent = $emailService->sendRegistrationConfirmationEmail(
                        $email,
                        $resident_name
                    );
                    
                    if ($email_sent) {
                        $success = "Resident activated successfully! A confirmation email has been sent to {$email}";
                    } else {
                        $success = "Resident activated, but email delivery failed. Please contact resident manually.";
                    }
                }
                
            } else {
                // Regular update without RFID generation
                $stmt = $pdo->prepare("
                    UPDATE residents 
                    SET first_name = ?, middle_name = ?, last_name = ?, email = ?, phone = ?, 
                        address = ?, birthdate = ?, gender = ?, civil_status = ?, status = ?, 
                        rfid_code = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                $result = $stmt->execute([
                    $first_name, $middle_name, $last_name, $email, $phone,
                    $address, $birthdate, $gender, $civil_status, $status,
                    $final_rfid_code ?: null, $resident_id
                ]);
                
                if ($result) {
                    $success = "Resident profile updated successfully!";
                }
            }
            
            if ($result) {
                // Log the changes
                $changes = [];
                $fields_to_check = [
                    'first_name', 'middle_name', 'last_name', 'email', 'phone',
                    'address', 'birthdate', 'gender', 'civil_status', 'status'
                ];
                
                // Add RFID to changes if it was modified
                if ($final_rfid_code !== ($original_data['rfid_code'] ?? '')) {
                    $changes['rfid_code'] = [
                        'old' => $original_data['rfid_code'] ?? '',
                        'new' => $final_rfid_code
                    ];
                }
                
                foreach ($fields_to_check as $field) {
                    $old_value = $original_data[$field] ?? '';
                    $new_value = ${$field} ?? '';
                    
                    if ($old_value !== $new_value) {
                        $changes[$field] = [
                            'old' => $old_value,
                            'new' => $new_value
                        ];
                    }
                }
                
                if (!empty($changes)) {
                    $log_details = [
                        'changes' => $changes,
                        'resident_name' => $first_name . ' ' . $last_name,
                        'admin_action' => true,
                        'fields_changed' => array_keys($changes)
                    ];
                    
                    if ($needsRFIDGeneration) {
                        $log_details['auto_generated_rfid'] = $final_rfid_code;
                        $log_details['auto_generated_password'] = true;
                        $log_details['email_sent'] = $email_sent;
                        $log_details['activation_email'] = $email;
                        
                        $logger->log(
                            'activation_with_rfid',
                            'resident',
                            "Activated resident {$first_name} {$last_name} (ID: {$resident_id}) with auto-generated RFID and credentials",
                            $resident_id,
                            $log_details
                        );
                    } else {
                        $logger->log(
                            'update',
                            'resident',
                            "Updated resident profile for {$first_name} {$last_name} (ID: {$resident_id})",
                            $resident_id,
                            $log_details
                        );
                    }
                }
                
                if (!isset($success)) {
                    $success = "Resident profile updated successfully!";
                }
                
                // Refresh resident data
                $stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ?");
                $stmt->execute([$resident_id]);
                $resident = $stmt->fetch(PDO::FETCH_ASSOC);
                
            } else {
                $error = "Failed to update resident profile.";
            }
            
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
            $logger->log('error', 'resident', "Failed to update resident ID {$resident_id}: " . $e->getMessage(), $resident_id);
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($new_password)) {
        $error = "New password is required.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Password confirmation does not match.";
    } else {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE residents SET password = ? WHERE id = ?");
            $result = $stmt->execute([$hashed_password, $resident_id]);
            
            if ($result) {
                $logger->log(
                    'password_reset',
                    'resident',
                    "Password reset for resident {$resident['first_name']} {$resident['last_name']} (ID: {$resident_id})",
                    $resident_id,
                    [
                        'resident_name' => $resident['first_name'] . ' ' . $resident['last_name'],
                        'admin_action' => true
                    ]
                );
                
                $success = "Password reset successfully!";
            } else {
                $error = "Failed to reset password.";
            }
        } catch (PDOException $e) {
            $error = "Database error while resetting password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Resident - <?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']); ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .admin-container {
            max-width: 1200px;
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
        
        .profile-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid #eee;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #4caf50, #45a049);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            font-weight: bold;
            color: white;
        }
        
        .profile-content {
            padding: 2rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: white;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: #212529;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }
        
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }
        
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .status-pending { background: #fff3cd; color: #856404; }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
        }
        
        .alert-success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #4caf50;
        }
        
        .info-label {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            font-weight: 600;
            color: #333;
        }
        
        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2e7d32;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }
        
        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .password-section {
            background: #fff8e1;
            border: 1px solid #ffecb3;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        
        .important {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .important h4 {
            margin-top: 0;
            color: #1976d2;
        }
        
        .important ul {
            margin-bottom: 0;
        }
        
        .important li {
            margin-bottom: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                padding: 1rem;
            }
            
            .admin-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <div class="admin-header">
            <div>
                <h1>Edit Resident Profile</h1>
                <p>Modify resident information and account settings</p>
            </div>
            <div>
                <a href="view-residents.php" class="btn btn-secondary">
                    ← Back to Residents
                </a>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($error): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <strong>Success:</strong> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <!-- Profile Card -->
        <div class="profile-card">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($resident['first_name'], 0, 1) . substr($resident['last_name'], 0, 1)); ?>
                </div>
                <h2><?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']); ?></h2>
                <p style="color: #666; margin: 0.5rem 0;">Resident ID: #<?php echo str_pad($resident['id'], 4, '0', STR_PAD_LEFT); ?></p>
                <span class="status-badge status-<?php echo $resident['status']; ?>">
                    <?php echo ucfirst($resident['status']); ?>
                </span>
            </div>

            <!-- Profile Content -->
            <div class="profile-content">
                <!-- Quick Info -->
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($resident['email']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($resident['phone']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">RFID Status</div>
                        <div class="info-value">
                            <?php if ($resident['rfid_code']): ?>
                                ✅ Registered (<?php echo htmlspecialchars($resident['rfid_code']); ?>)
                            <?php else: ?>
                                ❌ Not Registered
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Registered Date</div>
                        <div class="info-value"><?php echo date('F j, Y', strtotime($resident['created_at'])); ?></div>
                    </div>
                </div>

                <!-- Edit Form -->
                <form method="POST" id="editForm">
                    <div class="section-title">Personal Information</div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($resident['first_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" 
                                   value="<?php echo htmlspecialchars($resident['middle_name'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($resident['last_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="birthdate">Birthdate *</label>
                            <input type="date" id="birthdate" name="birthdate" 
                                   value="<?php echo $resident['birthdate']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="gender">Gender *</label>
                            <select id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo $resident['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo $resident['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo $resident['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="civil_status">Civil Status *</label>
                            <select id="civil_status" name="civil_status" required>
                                <option value="">Select Status</option>
                                <option value="Single" <?php echo $resident['civil_status'] === 'Single' ? 'selected' : ''; ?>>Single</option>
                                <option value="Married" <?php echo $resident['civil_status'] === 'Married' ? 'selected' : ''; ?>>Married</option>
                                <option value="Widowed" <?php echo $resident['civil_status'] === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                                <option value="Separated" <?php echo $resident['civil_status'] === 'Separated' ? 'selected' : ''; ?>>Separated</option>
                                <option value="Divorced" <?php echo $resident['civil_status'] === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                            </select>
                        </div>
                    </div>

                    <div class="section-title">Contact Information</div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($resident['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($resident['phone']); ?>" required>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="address">Address *</label>
                            <textarea id="address" name="address" required><?php echo htmlspecialchars($resident['address']); ?></textarea>
                        </div>
                    </div>

                    <div class="section-title">Account Settings</div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="status">Account Status *</label>
                            <select id="status" name="status" required>
                                <option value="active" <?php echo $resident['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $resident['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="pending" <?php echo $resident['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="rfid_code">RFID Code</label>
                            <input type="text" id="rfid_code" name="rfid_code" 
                                   value="<?php echo htmlspecialchars($resident['rfid_code'] ?? ''); ?>"
                                   placeholder="Enter RFID code">
                        </div>
                    </div>
                    
                    <div class="important">
                        <h4>📋 Account Activation Notice:</h4>
                        <p>When you change a <strong>pending</strong> resident to <strong>active</strong> status:</p>
                        <ul>
                            <li>If no RFID is set, the system will automatically generate one</li>
                            <li>A temporary password will be created and emailed to the resident</li>
                            <li>The resident will receive login credentials via email</li>
                            <li>This happens automatically to streamline the activation process</li>
                        </ul>
                    </div>

                    <div class="button-group">
                        <button type="submit" name="update_resident" class="btn btn-primary">
                            💾 Save Changes
                        </button>
                        <a href="view-residents.php" class="btn btn-secondary">
                            ❌ Cancel
                        </a>
                    </div>
                </form>

                <!-- Password Reset Section -->
                <div class="password-section">
                    <div class="section-title">🔐 Password Management</div>
                    <p style="color: #666; margin-bottom: 1rem;">Reset the resident's password. They will need to use the new password to log in.</p>
                    
                    <form method="POST" id="passwordForm">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" 
                                       placeholder="Enter new password (min 6 characters)">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" 
                                       placeholder="Confirm new password">
                            </div>
                        </div>
                        
                        <button type="submit" name="reset_password" class="btn btn-warning"
                                onclick="return confirm('Are you sure you want to reset this resident\'s password?')">
                            🔄 Reset Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Form validation
        document.getElementById('editForm').addEventListener('submit', function(e) {
            const requiredFields = ['first_name', 'last_name', 'email', 'phone', 'address', 'birthdate', 'gender', 'civil_status', 'status'];
            let isValid = true;
            
            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    input.style.borderColor = '#dc3545';
                    isValid = false;
                } else {
                    input.style.borderColor = '#e9ecef';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            // Email validation
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return false;
            }
            
            return confirm('Are you sure you want to save these changes?');
        });

        // Password form validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (!newPassword) {
                e.preventDefault();
                alert('Please enter a new password.');
                return false;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                return false;
            }
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Password confirmation does not match.');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>