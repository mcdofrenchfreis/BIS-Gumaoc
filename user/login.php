<?php
require_once __DIR__ . '/session_bootstrap.php';
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
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_type'] = 'resident';
            $_SESSION['user_email'] = $user['email'];
            
            // Ensure session is flushed before redirect
            session_write_close();

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo htmlspecialchars($page_title); ?> - Barangay Gumaoc East</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/background.css">
    <link rel="stylesheet" href="css/mobile.css">
    <style>
        :root {
            --primary-color: #28a745;
            --primary-hover: #218838;
            --text-color: #1a2c38;
            --error-bg: #f8d7da;
            --error-border: #f5c6cb;
            --error-text: #721c24;
        }

        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f7faf7;
            padding: 20px;
            padding-top: 72px;
            box-sizing: border-box;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            position: relative;
        }

        .login-card {
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(27, 94, 32, 0.1);
            border: 1px solid #e8f5e9;
            box-sizing: border-box;
        }

        .login-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .login-logo {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            object-position: center;
            transform: scale(1.1);
            transform-origin: center center;
            display: block;
            margin: 0 auto 16px;
            box-shadow: 0 4px 14px rgba(27, 94, 32, 0.15);
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
            background: #f7faf7;
            border: 1px solid #e8f5e9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .info-box h4 {
            color: #1b5e20;
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
            box-sizing: border-box;
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
            box-sizing: border-box;
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
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
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
            top: 16px;
            left: 16px;
            padding: 10px 16px;
            min-height: 44px;
            background: #ffffff;
            color: #1b5e20;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: box-shadow 0.2s ease, background 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #e8f5e9;
            box-shadow: 0 2px 8px rgba(27, 94, 32, 0.08);
            z-index: 1000;
        }

        .back-btn:hover {
            background: #f7faf7;
            box-shadow: 0 4px 12px rgba(27, 94, 32, 0.12);
            color: #145218;
        }

        .back-btn i {
            font-size: 16px;
        }
    </style>
</head>
<body>
    <a href="<?php echo htmlspecialchars($base_path); ?>index.php" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        Back to Home
    </a>
    
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <img src="<?php echo htmlspecialchars($base_path); ?>assets/images/logo.png" alt="" class="login-logo" width="72" height="72">
                    <h2>Resident Login</h2>
                </div>
                
                <div class="info-box">
                    <h4>🔐 Welcome</h4>
                    <p>Please login using your email and password.</p>
                </div>
                
                <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                
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
                        Login
                    </button>
                </form>
                
                <div class="register-link">
                    <a href="../pages/resident-registration.php">Complete Census Registration</a>
                    <a href="reset_password.php">Forgot Password?</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>