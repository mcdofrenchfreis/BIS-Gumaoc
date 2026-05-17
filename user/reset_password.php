<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once '../includes/db_connect.php';

$page_title = 'Reset Password';
$base_path = '../';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
    
    if (empty($email) || empty($contact_number)) {
        $error = "Please provide both Email and Contact Number";
    } else {
        // Check if the email and contact_number match
        $stmt = $pdo->prepare("SELECT * FROM residents WHERE email = ? AND phone = ? AND status = 'active'");
        $stmt->execute([$email, $contact_number]);
        $resident = $stmt->fetch();
        
        if ($resident) {
            // Generate new password
            $new_password = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password in database
            $update_stmt = $pdo->prepare("UPDATE residents SET password = ? WHERE id = ?");
            if ($update_stmt->execute([$hashed_password, $resident['id']])) {
                $message = "Password reset successful! Your new password is: <strong>" . $new_password . "</strong><br><br>Please save this password and use it to login.";
            } else {
                $error = "Failed to reset password. Please try again.";
            }
        } else {
            $error = "Invalid Email or Contact Number. Please check your details.";
        }
    }
}

include '../includes/header.php';
?>
<link rel="stylesheet" href="css/mobile.css">

<style>
.reset-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f7faf7;
    padding: 20px;
    position: relative;
}

.reset-container {
    width: 100%;
    max-width: 400px;
    z-index: 1;
}

.reset-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 40px;
    box-shadow: 0 8px 32px rgba(27, 94, 32, 0.1);
    border: 1px solid #e8f5e9;
}

.reset-header {
    text-align: center;
    margin-bottom: 30px;
}

.reset-header h2 {
    color: #333;
    margin-bottom: 10px;
    font-size: 28px;
}

.reset-header p {
    color: #666;
    font-size: 14px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e1e5e9;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.form-group input:focus {
    outline: none;
    border-color: #2e7d32;
    box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.12);
}

.btn-reset {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-reset:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(27, 94, 32, 0.22);
}

.btn-reset i {
    font-size: 18px;
}

.error-message {
    background: #fee;
    color: #c33;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #fcc;
    text-align: center;
}

.success-message {
    background: #efe;
    color: #363;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #cfc;
    text-align: center;
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

.back-link {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e1e5e9;
}

.back-link a {
    color: #2e7d32;
    text-decoration: none;
    font-weight: 500;
}

.back-link a:hover {
    text-decoration: underline;
}
</style>

<div class="reset-wrapper">
    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <h2>Reset Password</h2>
                <p>Enter your Email and Contact Number to reset your password</p>
            </div>
            
            <div class="info-box">
                <h4>🔐 Password Reset</h4>
                <p>You will receive a new auto-generated password. Please save it securely.</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="success-message">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="your.email@example.com">
                </div>
                
                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" id="contact_number" name="contact_number" required placeholder="e.g., 09123456789">
                </div>
                
                <button type="submit" class="btn-reset">
                    <i class="fas fa-key"></i>
                    Reset Password
                </button>
            </form>
            
            <div class="back-link">
                <p><a href="login.php">← Back to Login</a></p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 