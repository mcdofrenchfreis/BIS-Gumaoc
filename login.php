<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/email_service.php';

// If already logged in, redirect to dashboard or home
if (isset($_SESSION['rfid_authenticated']) && $_SESSION['rfid_authenticated'] === true) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';
$otp_sent = false;

// Handle RFID Login
if ($_POST && isset($_POST['rfid_code'])) {
    $rfid_code = trim($_POST['rfid_code']);
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM residents WHERE rfid = ? OR rfid_code = ?");
        $stmt->execute([$rfid_code, $rfid_code]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['rfid_authenticated'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
            header('Location: ' . $redirect);
            exit();
        } else {
            $error = 'Invalid RFID or user not found.';
        }
    } catch (PDOException $e) {
        $error = 'Database error. Please try again.';
    }
}

// Handle Manual Login
if ($_POST && isset($_POST['username']) && isset($_POST['password'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM residents WHERE (email = ? OR phone = ?) AND password IS NOT NULL");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['rfid_authenticated'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
            header('Location: ' . $redirect);
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    } catch (PDOException $e) {
        $error = 'Database error. Please try again.';
    }
}

// Handle Forgot Password
if ($_POST && isset($_POST['forgot_email'])) {
    $email = trim($_POST['forgot_email']);
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM residents WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate OTP
            $otp = sprintf("%06d", mt_rand(1, 999999));
            $otp_expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            // Store OTP in database
            $stmt = $pdo->prepare("UPDATE residents SET reset_otp = ?, otp_expiry = ? WHERE email = ?");
            $stmt->execute([$otp, $otp_expiry, $email]);
            
            // Send OTP via PHPMailer
            $emailService = new EmailService();
            if ($emailService->sendOTP($email, $otp, $user['first_name'])) {
                $_SESSION['reset_email'] = $email;
                $success = 'OTP has been sent to your email address.';
                $otp_sent = true;
            } else {
                $error = 'Failed to send OTP. Please try again.';
            }
        } else {
            $error = 'Email address not found.';
        }
    } catch (PDOException $e) {
        $error = 'Database error. Please try again.';
    }
}

// Handle OTP Verification and Password Reset
if ($_POST && isset($_POST['otp']) && isset($_POST['new_password'])) {
    $otp = trim($_POST['otp']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_SESSION['reset_email'] ?? '';
    
    if ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
        $otp_sent = true;
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM residents WHERE email = ? AND reset_otp = ? AND otp_expiry > NOW()");
            $stmt->execute([$email, $otp]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE residents SET password = ?, reset_otp = NULL, otp_expiry = NULL WHERE email = ?");
                $stmt->execute([$hashed_password, $email]);
                
                unset($_SESSION['reset_email']);
                $success = 'Password has been reset successfully. You can now login.';
                $otp_sent = false;
            } else {
                $error = 'Invalid or expired OTP.';
                $otp_sent = true;
            }
        } catch (PDOException $e) {
            $error = 'Database error. Please try again.';
            $otp_sent = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GUMAOC</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('assets/images/background.jpg') center/cover no-repeat;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        /* Green tint overlay */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, 
                rgba(45, 90, 39, 0.7) 0%, 
                rgba(74, 124, 89, 0.6) 25%, 
                rgba(53, 122, 60, 0.65) 50%, 
                rgba(45, 90, 39, 0.7) 75%, 
                rgba(30, 58, 26, 0.8) 100%);
            z-index: 1;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 2;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
            color: #2d5a27;
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .login-header p {
            color: #4a7c59;
            margin: 0;
            font-size: 16px;
        }
        
        .brgy-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #2d5a27, #4a7c59);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 24px;
            box-shadow: 0 8px 16px rgba(45, 90, 39, 0.4);
            border: 3px solid rgba(255, 255, 255, 0.2);
        }
        
        .error {
            background: linear-gradient(135deg, #f8d7da, #f1aeb5);
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            border-left: 4px solid #dc3545;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
        }
        
        .success {
            background: linear-gradient(135deg, #d4edda, #a3d9a5);
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            border-left: 4px solid #28a745;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2d5a27;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #4a7c59;
            box-shadow: 0 0 0 3px rgba(74, 124, 89, 0.15), inset 0 2px 4px rgba(0, 0, 0, 0.05);
            background: white;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #4a7c59, #357a3c);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 10px;
            box-shadow: 0 6px 20px rgba(74, 124, 89, 0.4);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        .btn:hover {
            background: linear-gradient(135deg, #357a3c, #2d5a27);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(74, 124, 89, 0.5);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #495057, #343a40);
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.5);
        }
        
        .links {
            text-align: center;
            margin-top: 25px;
        }
        
        .links a {
            color: #4a7c59;
            text-decoration: none;
            margin: 0 15px;
            cursor: pointer;
            font-weight: 500;
            transition: color 0.3s ease;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8);
        }
        
        .links a:hover {
            color: #2d5a27;
            text-decoration: underline;
        }
        
        .login-tabs {
            display: flex;
            margin-bottom: 25px;
            border-bottom: 2px solid #e0e0e0;
            border-radius: 10px 10px 0 0;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .tab {
            flex: 1;
            padding: 15px 10px;
            text-align: center;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            background: rgba(74, 124, 89, 0.08);
            font-weight: 500;
        }
        
        .tab.active {
            border-bottom-color: #4a7c59;
            color: #2d5a27;
            font-weight: 600;
            background: rgba(74, 124, 89, 0.15);
            box-shadow: inset 0 2px 4px rgba(74, 124, 89, 0.1);
        }
        
        .tab:hover:not(.active) {
            background: rgba(74, 124, 89, 0.12);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .forgot-link {
            text-align: right;
            margin-top: 15px;
        }
        
        .forgot-link a {
            color: #4a7c59;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .forgot-link a:hover {
            color: #2d5a27;
            text-decoration: underline;
        }
        
        .hidden {
            display: none;
        }
        
        /* Enhanced glass morphism effect */
        .login-container::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, 
                rgba(74, 124, 89, 0.3), 
                rgba(53, 122, 60, 0.2), 
                rgba(45, 90, 39, 0.3));
            border-radius: 22px;
            z-index: -1;
            filter: blur(10px);
        }
        
        /* Responsive design */
        @media (max-width: 480px) {
            .login-container {
                margin: 20px;
                padding: 30px 25px;
            }
            
            .login-header h2 {
                font-size: 24px;
            }
            
            .brgy-logo {
                width: 60px;
                height: 60px;
                font-size: 20px;
            }
            
            body {
                background-attachment: scroll;
            }
        }
        
        /* Animation for form elements */
        .form-group {
            animation: slideInUp 0.6s ease forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        
        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .btn { 
            animation: slideInUp 0.6s ease forwards;
            opacity: 0;
            transform: translateY(20px);
            animation-delay: 0.4s; 
        }
        
        @keyframes slideInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Loading state for background */
        body {
            background-color: #2d5a27;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="brgy-logo">BRGY</div>
            <h2>GUMAOC EAST</h2>
            <p>Barangay Management System</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (!$otp_sent): ?>
        <div class="login-tabs">
            <div class="tab active" onclick="showTab('rfid')">🏷️ RFID Login</div>
            <div class="tab" onclick="showTab('manual')">👤 Manual Login</div>
        </div>
        
        <!-- RFID Login Tab -->
        <div id="rfid-tab" class="tab-content active">
            <form method="POST">
                <div class="form-group">
                    <label for="rfid_code">🏷️ RFID Code</label>
                    <input type="text" id="rfid_code" name="rfid_code" placeholder="Tap your RFID card here" required autofocus>
                </div>
                <button type="submit" class="btn">🔐 Login with RFID</button>
            </form>
        </div>
        
        <!-- Manual Login Tab -->
        <div id="manual-tab" class="tab-content">
            <form method="POST">
                <div class="form-group">
                    <label for="username">📧 Email or Phone</label>
                    <input type="text" id="username" name="username" placeholder="Enter your email or phone number" required>
                </div>
                <div class="form-group">
                    <label for="password">🔒 Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn">🔐 Login</button>
                <div class="forgot-link">
                    <a href="#" onclick="showForgotPassword()">🔑 Forgot Password?</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- Forgot Password Form -->
        <div id="forgot-form" class="<?php echo $otp_sent ? '' : 'hidden'; ?>">
            <?php if (!$otp_sent): ?>
            <form method="POST">
                <div class="form-group">
                    <label for="forgot_email">📧 Email Address</label>
                    <input type="email" id="forgot_email" name="forgot_email" placeholder="Enter your email address" required>
                </div>
                <button type="submit" class="btn">📨 Send OTP</button>
                <button type="button" class="btn btn-secondary" onclick="hideForgotPassword()">↩️ Back to Login</button>
            </form>
            <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="otp">🔢 Enter OTP</label>
                    <input type="text" id="otp" name="otp" placeholder="Enter 6-digit OTP" maxlength="6" required>
                </div>
                <div class="form-group">
                    <label for="new_password">🔒 New Password</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">🔒 Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                </div>
                <button type="submit" class="btn">✅ Reset Password</button>
            </form>
            <?php endif; ?>
        </div>
        
        <div class="links">
            <a href="index.php">🏠 Back to Home</a>
            <a href="register.php">📝 Create Account</a>
            <a href="resident-registration.php">🏷️ Register RFID Only</a>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
            
            // Hide forgot password form
            document.getElementById('forgot-form').classList.add('hidden');
        }
        
        function showForgotPassword() {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.getElementById('forgot-form').classList.remove('hidden');
        }
        
        function hideForgotPassword() {
            document.getElementById('forgot-form').classList.add('hidden');
            document.getElementById('manual-tab').classList.add('active');
        }
        
        // Auto-focus on RFID input when tab is active
        document.addEventListener('DOMContentLoaded', function() {
            const rfidInput = document.getElementById('rfid_code');
            if (rfidInput && document.getElementById('rfid-tab').classList.contains('active')) {
                rfidInput.focus();
            }
        });
    </script>
</body>
</html>