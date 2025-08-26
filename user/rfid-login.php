<?php
session_start();
require_once '../includes/db_connect.php';

$page_title = 'RFID Login';
$base_path = '../';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rfid_code'])) {
    $rfid_code = trim($_POST['rfid_code']);
    
    // Check if RFID exists in residents database
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
            exit();
        }
        
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Invalid RFID or user not found.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Barangay Gumaoc East</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #17a2b8;
            --primary-hover: #138496;
            --text-color: #1a2c38;
            --error-bg: #f8d7da;
            --error-border: #f5c6cb;
            --error-text: #721c24;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .rfid-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        .rfid-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }

        .rfid-container {
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 2;
        }

        .rfid-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            text-align: center;
        }

        .rfid-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 35px;
            color: white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .rfid-header h2 {
            color: var(--text-color);
            font-size: 28px;
            margin: 0 0 10px 0;
            font-weight: 600;
        }

        .rfid-header p {
            color: #666;
            margin: 0 0 30px 0;
            font-size: 16px;
        }

        .status-indicator {
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .status-ready {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-error {
            background: var(--error-bg);
            color: var(--error-text);
            border: 1px solid var(--error-border);
        }

        .rfid-form {
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: var(--text-color);
            font-size: 14px;
            font-weight: 500;
        }

        .rfid-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-align: center;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
            box-sizing: border-box;
        }

        .rfid-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.2);
            outline: none;
        }

        .btn-rfid {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-rfid:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(23, 162, 184, 0.3);
        }

        .rfid-instructions {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 25px;
            font-size: 13px;
            color: #666;
            line-height: 1.5;
        }

        .rfid-instructions i {
            color: var(--primary-color);
            margin-right: 8px;
        }

        .navigation-links {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .nav-link {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.9);
            color: var(--text-color);
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            color: var(--text-color);
        }

        .scanning-animation {
            display: none;
            text-align: center;
            color: var(--primary-color);
            font-size: 14px;
            margin-top: 10px;
        }

        .scanning-animation i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

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

        .rfid-card {
            animation: fadeIn 0.6s ease-out;
        }
    </style>
</head>
<body>
    <div class="rfid-wrapper">
        <div class="rfid-container">
            <div class="rfid-card">
                <div class="rfid-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                
                <div class="rfid-header">
                    <h2>RFID Quick Access</h2>
                    <p>Scan your RFID card for instant login</p>
                </div>
                
                <?php if ($error): ?>
                <div class="status-indicator status-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php else: ?>
                <div class="status-indicator status-ready">
                    <i class="fas fa-check-circle"></i>
                    Ready to scan RFID card
                </div>
                <?php endif; ?>
                
                <div class="rfid-instructions">
                    <i class="fas fa-info-circle"></i>
                    <strong>Instructions:</strong><br>
                    • Place your RFID card near the reader<br>
                    • Or manually enter your RFID code below<br>
                    • Login will happen automatically
                </div>
                
                <form method="POST" class="rfid-form" id="rfidForm">
                    <div class="form-group">
                        <label for="rfid_code">RFID Code</label>
                        <input type="text" 
                               id="rfid_code" 
                               name="rfid_code" 
                               class="rfid-input"
                               placeholder="SCAN CARD OR ENTER CODE"
                               required 
                               autofocus
                               autocomplete="off">
                    </div>
                    
                    <button type="submit" class="btn-rfid">
                        <i class="fas fa-sign-in-alt"></i>
                        Login with RFID
                    </button>
                    
                    <div class="scanning-animation" id="scanningAnimation">
                        <i class="fas fa-spinner"></i>
                        Processing RFID...
                    </div>
                </form>
                
                <div class="navigation-links">
                    <a href="login.php" class="nav-link">
                        <i class="fas fa-envelope"></i>
                        Email Login
                    </a>
                    <a href="../index.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        Home
                    </a>
                    <a href="../pages/resident-registration.php" class="nav-link">
                        <i class="fas fa-user-plus"></i>
                        Register
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rfidInput = document.getElementById('rfid_code');
            const scanningAnimation = document.getElementById('scanningAnimation');
            const form = document.getElementById('rfidForm');
            
            // Auto-submit when RFID code is detected (assuming 8+ character codes)
            rfidInput.addEventListener('input', function() {
                const value = this.value.trim();
                
                // Auto-submit for typical RFID code lengths
                if (value.length >= 8) {
                    scanningAnimation.style.display = 'block';
                    
                    // Small delay to allow complete scan
                    setTimeout(() => {
                        form.submit();
                    }, 800);
                }
            });
            
            // Handle form submission
            form.addEventListener('submit', function() {
                scanningAnimation.style.display = 'block';
            });
            
            // Focus on input for immediate scanning
            rfidInput.focus();
            
            // Auto-focus on input when clicking anywhere on the card
            document.querySelector('.rfid-card').addEventListener('click', function(e) {
                if (e.target !== rfidInput) {
                    rfidInput.focus();
                }
            });
        });
    </script>
</body>
</html>