<?php
session_start();

$page_title = 'User Registration';
$base_path = '../';

include '../includes/header.php';
  ?>
  <link rel="stylesheet" href="css/background.css">
  
  <style>
.register-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
}

.register-container {
    width: 100%;
    max-width: 600px;
    z-index: 1;
}

.register-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    text-align: center;
}

.register-header h2 {
    color: #333;
    margin-bottom: 20px;
    font-size: 28px;
}

.info-box {
    background: #e3f2fd;
    border: 1px solid #2196f3;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
    text-align: left;
}

.info-box h4 {
    color: #1976d2;
    margin: 0 0 15px 0;
    font-size: 18px;
}

.info-box p {
    color: #424242;
    margin: 10px 0;
    line-height: 1.6;
}

.btn-register {
    display: inline-block;
    padding: 15px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s ease;
    margin: 10px;
}

.btn-register:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    color: white;
}

.btn-secondary {
    background: #6c757d;
}

.btn-secondary:hover {
    background: #5a6268;
    box-shadow: 0 10px 20px rgba(108, 117, 125, 0.3);
}
</style>

<div class="register-wrapper">
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h2>👋 Welcome to User Registration</h2>
            </div>
            
            <div class="info-box">
                <h4>📋 Complete Census Registration</h4>
                <p><strong>To access user services, please complete the census registration process:</strong></p>
                <p>• Fill out the comprehensive resident registration form</p>
                <p>• Provide your family and household information</p>
                <p>• Receive your login credentials automatically</p>
                <p>• Access all available e-services</p>
                
                <h4 style="margin-top: 20px;">🏠 What You'll Get:</h4>
                <p>• Personal user account</p>
                <p>• RFID access (optional)</p>
                <p>• Access to document requests</p>
                <p>• Community service applications</p>
                <p>• Emergency reporting system</p>
            </div>
            
            <div style="margin: 30px 0;">
                <a href="../pages/resident-registration.php" class="btn-register">
                    📝 Complete Census Registration
                </a>
            </div>
            
            <div style="margin-top: 30px;">
                <p style="color: #666;">Already completed registration?</p>
                <a href="login.php" class="btn-register btn-secondary">
                    🔑 Login Here
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>