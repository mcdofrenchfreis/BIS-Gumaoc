<?php
session_start();
require_once '../includes/db_connect.php';

$page_title = 'User Login';
$base_path = '../';

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        // Email/Password login
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        $stmt = $pdo->prepare("SELECT * FROM residents WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && $user['password'] && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_type'] = 'resident';
            $_SESSION['user_email'] = $user['email'];
            
            // Check if profile is complete
            if (isset($user['profile_complete']) && $user['profile_complete'] == 0) {
                $_SESSION['profile_incomplete'] = true;
                header('Location: ../pages/complete-profile.php');
                exit;
            }
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Invalid email or password";
        }
    } elseif (isset($_POST['rfid_code'])) {
        // RFID login
        $rfid_code = trim($_POST['rfid_code']);
        
        $stmt = $pdo->prepare("SELECT * FROM residents WHERE (rfid_code = ? OR rfid = ?) AND status = 'active'");
        $stmt->execute([$rfid_code, $rfid_code]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_type'] = 'resident';
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['rfid_authenticated'] = true;
            
            // Check if profile is complete
            if (isset($user['profile_complete']) && $user['profile_complete'] == 0) {
                $_SESSION['profile_incomplete'] = true;
                header('Location: ../pages/complete-profile.php');
                exit;
            }
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Invalid RFID or user not found";
        }
    }
}

// Add admin stylesheet and Font Awesome
$additional_css = [
    '<link rel="stylesheet" href="' . $base_path . 'assets/css/admin.css">',
    '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">'
];
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
        :root {
            --primary-color: #28a745;
            --primary-hover: #218838;
            --text-color: #1a2c38;
            --error-bg: #f8d7da;
            --error-border: #f5c6cb;
            --error-text: #721c24;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url('../assets/images/background.jpg') no-repeat center center;
            background-size: cover;
            padding: 20px;
            position: relative;
        }

        .login-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 2;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
            color: var(--text-color);
            font-size: 28px;
            margin: 0;
            font-weight: 600;
        }

        .alert {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background-color: var(--error-bg);
            border: 1px solid var(--error-border);
            color: var(--error-text);
        }

        .info-box {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .info-box h4 {
            color: #1976d2;
            margin: 0 0 8px 0;
            font-size: 16px;
        }

        .info-box p {
            color: #424242;
            margin: 0;
            font-size: 14px;
            line-height: 1.4;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: var(--text-color);
            font-size: 14px;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
            outline: none;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
        }

        .btn-login i {
            font-size: 18px;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e1e5e9;
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            margin: 0 10px;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* Add animation for the login card */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            animation: fadeIn 0.5s ease-out;
        }

        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.9);
            color: var(--text-color);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            z-index: 1000;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            color: var(--text-color);
        }

        .back-btn i {
            font-size: 16px;
        }
        
        .login-tabs {
            margin-bottom: 20px;
        }
        
        .tab-buttons {
            display: flex;
            margin-bottom: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 4px;
        }
        
        .tab-button {
            flex: 1;
            padding: 12px 16px;
            background: transparent;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .tab-button.active {
            background: var(--primary-color);
            color: white;
        }
        
        .tab-button:hover:not(.active) {
            background: #e9ecef;
            color: #333;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .btn-rfid {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        }
        
        .btn-rfid:hover {
            background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
        }
        
        .rfid-input {
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .rfid-instructions {
            text-align: center;
            margin-top: 15px;
            color: #666;
            font-size: 12px;
        }
        
        .rfid-instructions i {
            color: #17a2b8;
            margin-right: 5px;
        }
    </style>
    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to selected button
            event.target.classList.add('active');
            
            // Focus on first input of active tab
            setTimeout(() => {
                const activeTab = document.querySelector('.tab-content.active');
                const firstInput = activeTab.querySelector('input');
                if (firstInput) {
                    firstInput.focus();
                }
            }, 100);
        }
        
        // Auto-submit RFID when code is detected (assuming 10+ character RFID codes)
        document.addEventListener('DOMContentLoaded', function() {
            const rfidInput = document.getElementById('rfid_code');
            if (rfidInput) {
                rfidInput.addEventListener('input', function() {
                    if (this.value.length >= 10) {
                        // Auto-submit after short delay to allow complete scan
                        setTimeout(() => {
                            this.closest('form').submit();
                        }, 500);
                    }
                });
            }
        });
    </script>
</head>
<body>
    <a href="../index.php" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        Back to Home
    </a>
    
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <h2>Resident Login</h2>
                </div>
                
                <div class="info-box">
                    <h4>🔐 Login Options</h4>
                    <p>Login using your email and password, or scan your RFID card.</p>
                </div>
                
                <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                
                <!-- Login Method Tabs -->
                <div class="login-tabs">
                    <div class="tab-buttons">
                        <button type="button" class="tab-button active" onclick="showTab('email')">
                            <i class="fas fa-envelope"></i> Email Login
                        </button>
                        <button type="button" class="tab-button" onclick="showTab('rfid')">
                            <i class="fas fa-credit-card"></i> RFID Login
                        </button>
                    </div>
                    
                    <!-- Email Login Tab -->
                    <div id="email-tab" class="tab-content active">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" required autofocus placeholder="your.email@example.com">
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                            
                            <button type="submit" class="btn-login">
                                <i class="fas fa-sign-in-alt"></i>
                                Login with Email
                            </button>
                        </form>
                    </div>
                    
                    <!-- RFID Login Tab -->
                    <div id="rfid-tab" class="tab-content">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="rfid_code">RFID Card</label>
                                <input type="text" id="rfid_code" name="rfid_code" placeholder="Scan or enter RFID code" class="rfid-input">
                            </div>
                            
                            <button type="submit" class="btn-login btn-rfid">
                                <i class="fas fa-credit-card"></i>
                                Login with RFID
                            </button>
                            
                            <div class="rfid-instructions">
                                <small>
                                    <i class="fas fa-info-circle"></i>
                                    Place your RFID card near the reader or manually enter your RFID code
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="register-link">
                    <a href="../pages/resident-registration.php">Complete Census Registration</a>
                    <a href="reset_password.php">Forgot Password?</a>
                    <a href="../rfid-login.php">Quick RFID Access</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>