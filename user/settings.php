<?php
require_once 'auth_check.php';
$page_title = 'Account Settings';
$current_page = 'settings';

// Check for success/error messages
$success_message = '';
$error_message = '';
if (isset($_SESSION['settings_success'])) {
    $success_message = $_SESSION['settings_success'];
    unset($_SESSION['settings_success']);
}
if (isset($_SESSION['settings_error'])) {
    $error_message = $_SESSION['settings_error'];
    unset($_SESSION['settings_error']);
}

$display_name = $user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Barangay Gumaoc East</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/background.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            min-height: 100vh;
            line-height: 1.6;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx=".5" cy=".5" r=".5"><stop offset="0%" stop-color="%23ffffff" stop-opacity=".1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)"/><circle cx="800" cy="300" r="150" fill="url(%23a)"/><circle cx="400" cy="700" r="120" fill="url(%23a)"/></svg>');
            opacity: 0.3;
            z-index: 0;
        }
        
        .main-content {
            margin-top: 70px;
            padding: 30px 20px;
            position: relative;
            z-index: 1;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-title {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .page-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.2rem;
            font-weight: 400;
        }
        
        .settings-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(25px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .card-subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        .form-input {
            width: 100%;
            padding: 15px 18px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            color: #333;
        }
        
        .form-input:focus {
            border-color: #2e7d32;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
            outline: none;
        }
        
        .form-input:invalid {
            border-color: #dc3545;
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            text-align: center;
            min-width: 150px;
            justify-content: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            color: white;
            box-shadow: 0 4px 15px rgba(46, 125, 50, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(46, 125, 50, 0.4);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #333;
            border: 2px solid #e1e5e9;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
            transform: translateY(-1px);
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .settings-card {
                padding: 25px;
                margin: 0 10px 20px;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .info-box {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .info-box h4 {
            color: #1565c0;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }
        
        .info-box p {
            color: #1976d2;
            margin: 0;
            font-size: 0.9rem;
        }
        
        .current-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #2e7d32;
        }
        
        .current-info strong {
            color: #2e7d32;
        }
        
        .password-requirements {
            font-size: 0.85rem;
            color: #666;
            margin-top: 8px;
            padding-left: 8px;
        }
        
        .password-requirements ul {
            margin: 8px 0 0 15px;
        }
        
        .password-requirements li {
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    <?php include 'navbar_component.php'; ?>
    
    <div class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Account Settings</h1>
                <p class="page-subtitle">Manage your email and password settings</p>
            </div>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Update Email Section -->
            <div class="settings-card">
                <h2 class="card-title">
                    <i class="fas fa-envelope"></i>
                    Update Email Address
                </h2>
                <p class="card-subtitle">Change your email address for login and notifications</p>
                
                <div class="current-info">
                    <strong>Current Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                </div>
                
                <form method="POST" action="process_settings.php" id="emailForm">
                    <input type="hidden" name="action" value="update_email">
                    
                    <div class="form-group">
                        <label for="new_email" class="form-label">New Email Address</label>
                        <input 
                            type="email" 
                            id="new_email" 
                            name="new_email" 
                            class="form-input" 
                            required
                            placeholder="Enter your new email address"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_email" class="form-label">Confirm Email Address</label>
                        <input 
                            type="email" 
                            id="confirm_email" 
                            name="confirm_email" 
                            class="form-input" 
                            required
                            placeholder="Confirm your new email address"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="current_password_email" class="form-label">Current Password</label>
                        <input 
                            type="password" 
                            id="current_password_email" 
                            name="current_password" 
                            class="form-input" 
                            required
                            placeholder="Enter your current password to confirm"
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Update Email
                    </button>
                </form>
            </div>
            
            <!-- Update Password Section -->
            <div class="settings-card">
                <h2 class="card-title">
                    <i class="fas fa-lock"></i>
                    Update Password
                </h2>
                <p class="card-subtitle">Change your password to keep your account secure</p>
                
                <div class="info-box">
                    <h4><i class="fas fa-info-circle"></i> Password Security</h4>
                    <p>Choose a strong password to protect your account. Your password will be encrypted and securely stored.</p>
                </div>
                
                <form method="POST" action="process_settings.php" id="passwordForm">
                    <input type="hidden" name="action" value="update_password">
                    
                    <div class="form-group">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input 
                            type="password" 
                            id="current_password" 
                            name="current_password" 
                            class="form-input" 
                            required
                            placeholder="Enter your current password"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password" class="form-label">New Password</label>
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            class="form-input" 
                            required
                            minlength="6"
                            placeholder="Enter your new password"
                        >
                        <div class="password-requirements">
                            <strong>Password Requirements:</strong>
                            <ul>
                                <li>Minimum 6 characters long</li>
                                <li>Should contain a mix of letters and numbers</li>
                                <li>Avoid using easily guessable information</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="form-input" 
                            required
                            minlength="6"
                            placeholder="Confirm your new password"
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key"></i>
                        Update Password
                    </button>
                </form>
            </div>
            
            <!-- Account Information -->
            <div class="settings-card">
                <h2 class="card-title">
                    <i class="fas fa-user"></i>
                    Account Information
                </h2>
                <p class="card-subtitle">Your current account details</p>
                
                <div class="form-row">
                    <div>
                        <label class="form-label">Full Name</label>
                        <div class="current-info">
                            <?php echo htmlspecialchars($display_name); ?>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Phone Number</label>
                        <div class="current-info">
                            <?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div>
                        <label class="form-label">Status</label>
                        <div class="current-info">
                            <i class="fas fa-check-circle" style="color: #4caf50;"></i>
                            <?php echo ucfirst($user['status']); ?>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Member Since</label>
                        <div class="current-info">
                            <?php echo date('F j, Y', strtotime($user['created_at'])); ?>
                        </div>
                    </div>
                </div>
                
                <div class="info-box" style="margin-top: 25px;">
                    <h4><i class="fas fa-info-circle"></i> Update Profile Information</h4>
                    <p>To update your name, phone number, or other personal information, please contact the barangay office or visit in person with valid identification.</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Form validation
        document.getElementById('emailForm').addEventListener('submit', function(e) {
            const newEmail = document.getElementById('new_email').value;
            const confirmEmail = document.getElementById('confirm_email').value;
            
            if (newEmail !== confirmEmail) {
                e.preventDefault();
                alert('Email addresses do not match. Please try again.');
                return false;
            }
        });
        
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match. Please try again.');
                return false;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                return false;
            }
        });
    </script>
</body>
</html>