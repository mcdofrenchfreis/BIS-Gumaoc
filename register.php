<?php
session_start();
require_once 'includes/db_connect.php';

// If already logged in, redirect to dashboard or home
if (isset($_SESSION['rfid_authenticated']) && $_SESSION['rfid_authenticated'] === true) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// Handle Registration
if ($_POST && isset($_POST['register'])) {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Address components
    $street_address = trim($_POST['street_address']);
    $subdivision = trim($_POST['subdivision']);
    $purok = trim($_POST['purok']);
    $zip_code = trim($_POST['zip_code']);
    
    // Combine address
    $address_parts = array_filter([$street_address, $subdivision, $purok, 'Barangay Gumaoc East', 'San Jose del Monte', 'Bulacan', $zip_code]);
    $address = implode(', ', $address_parts);
    
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $civil_status = $_POST['civil_status'];
    $rfid_code = trim($_POST['rfid_code']);
    
    // Validation
    if ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM residents WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email address already registered.';
            } else {
                // Check if phone already exists
                $stmt = $pdo->prepare("SELECT id FROM residents WHERE phone = ?");
                $stmt->execute([$phone]);
                if ($stmt->fetch()) {
                    $error = 'Phone number already registered.';
                } else {
                    // Check if RFID already exists (if provided)
                    if (!empty($rfid_code)) {
                        $stmt = $pdo->prepare("SELECT id FROM residents WHERE rfid_code = ? OR rfid = ?");
                        $stmt->execute([$rfid_code, $rfid_code]);
                        if ($stmt->fetch()) {
                            $error = 'RFID code already registered.';
                        }
                    }
                    
                    if (empty($error)) {
                        // Hash password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Insert new resident
                        $stmt = $pdo->prepare("
                            INSERT INTO residents (
                                first_name, middle_name, last_name, email, phone, 
                                password, address, street_address, subdivision, purok, zip_code,
                                birthdate, gender, civil_status, 
                                rfid_code, rfid, status, created_at
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
                        ");
                        
                        $stmt->execute([
                            $first_name, $middle_name, $last_name, $email, $phone,
                            $hashed_password, $address, $street_address, $subdivision, $purok, $zip_code,
                            $birthdate, $gender, $civil_status,
                            $rfid_code, $rfid_code
                        ]);
                        
                        $success = 'Registration successful! You can now login with your credentials.';
                        
                        // Clear form data
                        $_POST = array();
                    }
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - GUMAOC</title>
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
            padding: 20px 0;
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
        
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 900px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 2;
            margin: 20px;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h2 {
            color: #2d5a27;
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .register-header p {
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
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.two-cols {
            grid-column: span 2;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
            width: 100%;
            justify-content: center;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2d5a27;
            font-weight: 600;
            font-size: 14px;
        }
        
        .optional {
            color: #666;
            font-weight: 400;
            font-size: 12px;
        }
        
        .locked-field {
            color: #4a7c59;
            font-style: italic;
            font-size: 12px;
            margin-top: 5px;
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
            background: rgba(255, 255, 255, 0.95);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4a7c59;
            box-shadow: 0 0 0 3px rgba(74, 124, 89, 0.15), inset 0 2px 4px rgba(0, 0, 0, 0.05);
            background: white;
        }
        
        .form-group input:disabled,
        .form-group select:disabled {
            background: rgba(240, 240, 240, 0.8);
            color: #666;
            cursor: not-allowed;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .required {
            color: #dc3545;
        }
        
        .address-section {
            grid-column: 1 / -1;
            background: rgba(232, 245, 233, 0.3);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid rgba(74, 124, 89, 0.2);
            margin-bottom: 10px;
        }
        
        .address-section h3 {
            color: #2d5a27;
            margin-bottom: 15px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .address-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
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
        
        /* Responsive design */
        @media (max-width: 1024px) {
            .register-container {
                max-width: 800px;
            }
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .address-grid {
                grid-template-columns: 1fr;
            }
            
            .register-container {
                margin: 10px;
                padding: 30px 25px;
                max-width: 600px;
            }
            
            .register-header h2 {
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
        
        @media (max-width: 480px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group.two-cols {
                grid-column: span 1;
            }
        }
        
        /* Loading state for background */
        body {
            background-color: #2d5a27;
        }
        
        .password-strength {
            font-size: 12px;
            margin-top: 5px;
            color: #666;
        }
        
        .password-strength.weak { color: #dc3545; }
        .password-strength.medium { color: #ffc107; }
        .password-strength.strong { color: #28a745; }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <div class="brgy-logo">BRGY</div>
            <h2>GUMAOC EAST</h2>
            <p>Register for Barangay Services</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-grid">
                <!-- First Row: Names -->
                <div class="form-group">
                    <label for="first_name">👤 First Name <span class="required">*</span></label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="middle_name">👤 Middle Name <span class="optional">(optional)</span></label>
                    <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($_POST['middle_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="last_name">👤 Last Name <span class="required">*</span></label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                </div>
                
                <!-- Address Section -->
                <div class="address-section">
                    <h3>🏠 Address Information</h3>
                    <div class="address-grid">
                        <div class="form-group">
                            <label for="street_address">🏠 Street Address <span class="required">*</span></label>
                            <input type="text" id="street_address" name="street_address" placeholder="House No./Lot/Block, Street Name" value="<?php echo htmlspecialchars($_POST['street_address'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subdivision">🏘️ Subdivision/Village <span class="optional">(optional)</span></label>
                            <input type="text" id="subdivision" name="subdivision" placeholder="Subdivision or Village name" value="<?php echo htmlspecialchars($_POST['subdivision'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="purok">📍 Purok/Zone <span class="optional">(optional)</span></label>
                            <select id="purok" name="purok">
                                <option value="">Select Purok/Zone</option>
                                <option value="Purok 1" <?php echo (($_POST['purok'] ?? '') === 'Purok 1') ? 'selected' : ''; ?>>Purok 1</option>
                                <option value="Purok 2" <?php echo (($_POST['purok'] ?? '') === 'Purok 2') ? 'selected' : ''; ?>>Purok 2</option>
                                <option value="Purok 3" <?php echo (($_POST['purok'] ?? '') === 'Purok 3') ? 'selected' : ''; ?>>Purok 3</option>
                                <option value="Purok 4" <?php echo (($_POST['purok'] ?? '') === 'Purok 4') ? 'selected' : ''; ?>>Purok 4</option>
                                <option value="Purok 5" <?php echo (($_POST['purok'] ?? '') === 'Purok 5') ? 'selected' : ''; ?>>Purok 5</option>
                                <option value="Zone 1" <?php echo (($_POST['purok'] ?? '') === 'Zone 1') ? 'selected' : ''; ?>>Zone 1</option>
                                <option value="Zone 2" <?php echo (($_POST['purok'] ?? '') === 'Zone 2') ? 'selected' : ''; ?>>Zone 2</option>
                                <option value="Zone 3" <?php echo (($_POST['purok'] ?? '') === 'Zone 3') ? 'selected' : ''; ?>>Zone 3</option>
                            </select>
                        </div>
                        
                        <div class="form-group zip-input-container">
                            <label for="zip_code">📮 ZIP Code <span class="required">*</span></label>
                            <select id="zip_code" name="zip_code" required>
                                <option value="">Select ZIP Code</option>
                                <option value="3023" <?php echo (($_POST['zip_code'] ?? '') === '3023') ? 'selected' : ''; ?>>3023 - San Jose del Monte (Poblacion)</option>
                                <option value="3024" <?php echo (($_POST['zip_code'] ?? '') === '3024') ? 'selected' : ''; ?>>3024 - San Jose del Monte (Muzon)</option>
                                <option value="3025" <?php echo (($_POST['zip_code'] ?? '') === '3025') ? 'selected' : ''; ?>>3025 - San Jose del Monte (Tungkong Mangga)</option>
                                <option value="3026" <?php echo (($_POST['zip_code'] ?? '') === '3026') ? 'selected' : ''; ?>>3026 - San Jose del Monte (Kaybanban)</option>
                                <option value="3027" <?php echo (($_POST['zip_code'] ?? '') === '3027') ? 'selected' : ''; ?>>3027 - San Jose del Monte (Minuyan)</option>
                                <option value="3028" <?php echo (($_POST['zip_code'] ?? '') === '3028') ? 'selected' : ''; ?>>3028 - San Jose del Monte (Gaya-gaya)</option>
                                <option value="3029" <?php echo (($_POST['zip_code'] ?? '') === '3029') ? 'selected' : ''; ?>>3029 - San Jose del Monte (Kaypian)</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Locked Location Info -->
                    <div style="margin-top: 15px; padding: 10px; background: rgba(74, 124, 89, 0.1); border-radius: 8px;">
                        <div class="locked-field">
                            🔒 <strong>Fixed Location:</strong> Barangay Gumaoc East, San Jose del Monte, Bulacan, Philippines
                        </div>
                    </div>
                </div>
                
                <!-- Personal Info Row -->
                <div class="form-group">
                    <label for="birthdate">📅 Birthdate <span class="required">*</span></label>
                    <input type="date" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($_POST['birthdate'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="gender">⚧ Gender <span class="required">*</span></label>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo (($_POST['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo (($_POST['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo (($_POST['gender'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="civil_status">💍 Civil Status <span class="required">*</span></label>
                    <select id="civil_status" name="civil_status" required>
                        <option value="">Select Civil Status</option>
                        <option value="Single" <?php echo (($_POST['civil_status'] ?? '') === 'Single') ? 'selected' : ''; ?>>Single</option>
                        <option value="Married" <?php echo (($_POST['civil_status'] ?? '') === 'Married') ? 'selected' : ''; ?>>Married</option>
                        <option value="Widowed" <?php echo (($_POST['civil_status'] ?? '') === 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                        <option value="Separated" <?php echo (($_POST['civil_status'] ?? '') === 'Separated') ? 'selected' : ''; ?>>Separated</option>
                    </select>
                </div>
                
                <!-- Email & Phone Number Row - Full Width -->
                <div class="form-group full-width" style="display: flex; gap: 20px; justify-content: center;">
                    <div style="flex:1;">
                        <label for="email">📧 Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    <div style="flex:1;">
                        <label for="phone">📱 Phone Number <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                </div>
                
                <!-- RFID Section -->
                <div class="form-group full-width" style="background: rgba(116, 185, 255, 0.1); padding: 20px; border-radius: 15px; border: 1px solid rgba(116, 185, 255, 0.3); margin: 20px 0;">
                    <h3 style="color: #2d5a27; margin-bottom: 15px; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                        🏷️ RFID Registration (Optional)
                    </h3>
                    <label for="rfid_code">🏷️ RFID Code <span class="optional">(Tap your RFID card in the field below)</span></label>
                    <input type="text" id="rfid_code" name="rfid_code" placeholder="Tap your RFID card here or leave empty" value="<?php echo htmlspecialchars($_POST['rfid_code'] ?? ''); ?>" style="font-family: monospace; letter-spacing: 2px;">
                    <div style="margin-top: 10px; font-size: 12px; color: #666;">
                        💡 <strong>Tip:</strong> You can register your RFID card now for faster login, or add it later from your profile.
                    </div>
                </div>
                
                <!-- Password & Confirm Password Row - Full Width -->
                <div class="form-group full-width" style="display: flex; gap: 20px; justify-content: center;">
                    <div style="flex:1;">
                        <label for="password">🔐 Password <span class="required">*</span></label>
                        <input type="password" id="password" name="password" required>
                        <div id="password-strength" class="password-strength"></div>
                    </div>
                    <div style="flex:1;">
                        <label for="confirm_password">🔐 Confirm Password <span class="required">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <!-- Empty cell for spacing -->
                </div>
            </div>
            
            <button type="submit" name="register" class="btn">📝 Create Account</button>
        </form>
        
        <div class="links">
            <a href="login.php">🔐 Already have an account? Login</a>
            <a href="index.php">🏠 Back to Home</a>
        </div>
    </div>

    <script>
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('password-strength');
            
            if (password.length === 0) {
                strengthDiv.textContent = '';
                strengthDiv.className = 'password-strength';
                return;
            }
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            if (strength < 2) {
                strengthDiv.textContent = 'Weak password';
                strengthDiv.className = 'password-strength weak';
            } else if (strength < 4) {
                strengthDiv.textContent = 'Medium password';
                strengthDiv.className = 'password-strength medium';
            } else {
                strengthDiv.textContent = 'Strong password';
                strengthDiv.className = 'password-strength strong';
            }
        });
        
        // Auto-capitalize names
        ['first_name', 'middle_name', 'last_name'].forEach(fieldId => {
            document.getElementById(fieldId).addEventListener('input', function() {
                this.value = this.value.replace(/\b\w/g, l => l.toUpperCase());
            });
        });
        
        // Format phone number
        document.getElementById('phone').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            this.value = value;
        });
        
        // Password confirmation check
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#e0e0e0';
            }
        });
        
        // Set minimum birthdate (18 years ago)
        const today = new Date();
        const minDate = new Date(today.getFullYear() - 100, today.getMonth(), today.getDate());
        const maxDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
        
        document.getElementById('birthdate').min = minDate.toISOString().split('T')[0];
        document.getElementById('birthdate').max = maxDate.toISOString().split('T')[0];
    </script>
</body>
</html>