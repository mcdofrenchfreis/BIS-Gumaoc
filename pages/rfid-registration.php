<?php
session_start();
include '../includes/db_connect.php';

$base_path = '../';
$page_title = 'RFID Registration - Barangay Gumaoc East';
$header_title = 'RFID Card Registration';
$header_subtitle = 'Register your RFID card for quick access to barangay services';

// Handle RFID registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rfid_number = trim($_POST['rfid_number'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    $contact_number = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $card_type = $_POST['card_type'] ?? 'resident';
    
    $errors = [];
    
    // Validation
    if (empty($rfid_number)) {
        $errors[] = "RFID number is required";
    }
    if (empty($first_name)) {
        $errors[] = "First name is required";
    }
    if (empty($last_name)) {
        $errors[] = "Last name is required";
    }
    if (empty($birth_date)) {
        $errors[] = "Birth date is required";
    }
    if (empty($contact_number)) {
        $errors[] = "Contact number is required";
    }
    if (empty($address)) {
        $errors[] = "Address is required";
    }
    
    // Check if RFID number already exists
    if (empty($errors)) {
        $check_stmt = $pdo->prepare("SELECT id FROM rfid_registrations WHERE rfid_number = ?");
        $check_stmt->execute([$rfid_number]);
        if ($check_stmt->fetch()) {
            $errors[] = "RFID number already registered";
        }
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO rfid_registrations (rfid_number, first_name, middle_name, last_name, birth_date, contact_number, address, card_type, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
            if ($stmt->execute([$rfid_number, $first_name, $middle_name, $last_name, $birth_date, $contact_number, $address, $card_type])) {
                $_SESSION['success'] = "RFID registration submitted successfully! Your application will be processed within 2-3 business days.";
                header('Location: rfid-registration.php?success=1');
                exit;
            }
        } catch (Exception $e) {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}

// Create RFID registrations table if it doesn't exist
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS rfid_registrations (
            id INT PRIMARY KEY AUTO_INCREMENT,
            rfid_number VARCHAR(50) UNIQUE NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            middle_name VARCHAR(100),
            last_name VARCHAR(100) NOT NULL,
            birth_date DATE NOT NULL,
            contact_number VARCHAR(20) NOT NULL,
            address TEXT NOT NULL,
            card_type ENUM('resident', 'employee', 'visitor') DEFAULT 'resident',
            status ENUM('pending', 'approved', 'rejected', 'active', 'blocked') DEFAULT 'pending',
            issued_date DATE NULL,
            expires_date DATE NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
} catch (Exception $e) {
    // Handle table creation error
}

include '../includes/header.php';
include '../includes/navigation.php';
?>

<div class="container">
    <div class="section">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <h4>✅ Registration Successful!</h4>
                <p><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <h4>❌ Registration Failed</h4>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="rfid-container">
            <div class="rfid-header">
                <div class="rfid-icon">🏷️</div>
                <h2>RFID Card Registration</h2>
                <p>Register your RFID card to access barangay services quickly and securely</p>
            </div>
            
            <div class="rfid-benefits">
                <h3>🎯 Benefits of RFID Registration</h3>
                <div class="benefits-grid">
                    <div class="benefit-item">
                        <span class="benefit-icon">⚡</span>
                        <h4>Quick Access</h4>
                        <p>Fast service processing with just a tap</p>
                    </div>
                    <div class="benefit-item">
                        <span class="benefit-icon">🔒</span>
                        <h4>Secure</h4>
                        <p>Encrypted data protection</p>
                    </div>
                    <div class="benefit-item">
                        <span class="benefit-icon">📱</span>
                        <h4>Digital Records</h4>
                        <p>Automated record keeping</p>
                    </div>
                    <div class="benefit-item">
                        <span class="benefit-icon">✅</span>
                        <h4>Verified Identity</h4>
                        <p>Instant identity verification</p>
                    </div>
                </div>
            </div>
            
            <form method="POST" class="rfid-form">
                <div class="form-section">
                    <h3>🏷️ RFID Card Information</h3>
                    <div class="rfid-input-group">
                        <label for="rfid_number">RFID Card Number *</label>
                        <input type="text" 
                               id="rfid_number" 
                               name="rfid_number" 
                               placeholder="Scan or enter RFID number" 
                               value="<?php echo htmlspecialchars($_POST['rfid_number'] ?? ''); ?>"
                               required 
                               autofocus>
                        <small class="input-help">📖 Place your RFID card near the reader or enter the number manually</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="card_type">Card Type *</label>
                        <select id="card_type" name="card_type" required>
                            <option value="resident" <?php echo ($_POST['card_type'] ?? '') === 'resident' ? 'selected' : ''; ?>>Resident</option>
                            <option value="employee" <?php echo ($_POST['card_type'] ?? '') === 'employee' ? 'selected' : ''; ?>>Barangay Employee</option>
                            <option value="visitor" <?php echo ($_POST['card_type'] ?? '') === 'visitor' ? 'selected' : ''; ?>>Visitor</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>👤 Personal Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($_POST['middle_name'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="birth_date">Birth Date *</label>
                            <input type="date" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($_POST['birth_date'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>📞 Contact Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="contact_number">Contact Number *</label>
                            <input type="tel" 
                                   id="contact_number" 
                                   name="contact_number" 
                                   placeholder="09XXXXXXXXX" 
                                   pattern="[0-9]{11}" 
                                   maxlength="11"
                                   value="<?php echo htmlspecialchars($_POST['contact_number'] ?? ''); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="address">Complete Address *</label>
                            <textarea id="address" name="address" rows="3" placeholder="House No., Street, Sitio/Purok, Barangay" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <span class="btn-icon">📝</span>
                        Register RFID Card
                    </button>
                    <button type="reset" class="btn-secondary">
                        <span class="btn-icon">🔄</span>
                        Clear Form
                    </button>
                </div>
            </form>
            
            <div class="rfid-info">
                <h3>ℹ️ Important Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <h4>📋 Required Documents</h4>
                        <ul>
                            <li>Valid ID (copy)</li>
                            <li>Proof of residency</li>
                            <li>Barangay clearance</li>
                        </ul>
                    </div>
                    
                    <div class="info-item">
                        <h4>⏰ Processing Time</h4>
                        <ul>
                            <li>2-3 business days</li>
                            <li>Email notification</li>
                            <li>SMS confirmation</li>
                        </ul>
                    </div>
                    
                    <div class="info-item">
                        <h4>💰 Fees</h4>
                        <ul>
                            <li>Residents: ₱50.00</li>
                            <li>Employees: Free</li>
                            <li>Visitors: ₱100.00</li>
                        </ul>
                    </div>
                    
                    <div class="info-item">
                        <h4>📞 Contact Support</h4>
                        <ul>
                            <li>Phone: (02) 123-4567</li>
                            <li>Email: rfid@gumaoc.gov.ph</li>
                            <li>Office Hours: 8AM-5PM</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rfid-container {
    max-width: 1000px;
    margin: 0 auto;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.rfid-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-align: center;
    padding: 3rem 2rem;
}

.rfid-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.rfid-header h2 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

.rfid-header p {
    font-size: 1.2rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
}

.rfid-benefits {
    padding: 2rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.rfid-benefits h3 {
    text-align: center;
    color: #495057;
    margin-bottom: 2rem;
    font-size: 1.5rem;
}

.benefits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.benefit-item {
    text-align: center;
    padding: 1.5rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease;
}

.benefit-item:hover {
    transform: translateY(-5px);
}

.benefit-icon {
    font-size: 2.5rem;
    display: block;
    margin-bottom: 1rem;
}

.benefit-item h4 {
    color: #495057;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.benefit-item p {
    color: #6c757d;
    font-size: 0.9rem;
    line-height: 1.5;
}

.rfid-form {
    padding: 2rem;
}

.form-section {
    margin-bottom: 2.5rem;
}

.form-section h3 {
    color: #495057;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 0.5rem;
    margin-bottom: 1.5rem;
    font-size: 1.3rem;
}

.rfid-input-group {
    margin-bottom: 1.5rem;
}

.rfid-input-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #495057;
}

.rfid-input-group input {
    width: 100%;
    padding: 1rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 1.1rem;
    font-family: 'Courier New', monospace;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.rfid-input-group input:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.input-help {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.9rem;
    color: #6c757d;
    font-style: italic;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
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
    color: #495057;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.8rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e9ecef;
}

.btn-primary,
.btn-secondary {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    border: none;
    border-radius: 10px;
    font-size: 1.1rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.btn-icon {
    font-size: 1.2rem;
}

.rfid-info {
    padding: 2rem;
    background: #f8f9fa;
}

.rfid-info h3 {
    text-align: center;
    color: #495057;
    margin-bottom: 2rem;
    font-size: 1.5rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
}

.info-item {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.info-item h4 {
    color: #495057;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.info-item ul {
    list-style: none;
    padding: 0;
}

.info-item li {
    padding: 0.3rem 0;
    color: #6c757d;
    font-size: 0.9rem;
}

.info-item li::before {
    content: "• ";
    color: #667eea;
    font-weight: bold;
    margin-right: 0.5rem;
}

.alert {
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    border-left: 4px solid;
}

.alert-success {
    background: #d4edda;
    border-color: #28a745;
    color: #155724;
}

.alert-error {
    background: #f8d7da;
    border-color: #dc3545;
    color: #721c24;
}

.alert h4 {
    margin-bottom: 0.5rem;
    font-size: 1.2rem;
}

.alert ul {
    margin: 0;
    padding-left: 1.5rem;
}

@media (max-width: 768px) {
    .rfid-header {
        padding: 2rem 1rem;
    }
    
    .rfid-header h2 {
        font-size: 2rem;
    }
    
    .rfid-form {
        padding: 1.5rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn-primary,
    .btn-secondary {
        justify-content: center;
    }
    
    .benefits-grid,
    .info-grid {
        grid-template-columns: 1fr;
    }
}

/* RFID Reader Animation */
.rfid-input-group input:focus {
    animation: rfidScan 2s infinite;
}

@keyframes rfidScan {
    0%, 100% { box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
    50% { box-shadow: 0 0 0 6px rgba(102, 126, 234, 0.2); }
}
</style>

<script>
// Auto-detect RFID input (simulated)
document.getElementById('rfid_number').addEventListener('input', function(e) {
    const value = e.target.value;
    
    // If input looks like RFID (numbers/letters), auto-format
    if (value.length >= 8) {
        // Add visual feedback
        e.target.style.background = '#e8f5e8';
        e.target.style.borderColor = '#28a745';
        
        // Auto-focus next field after short delay
        setTimeout(() => {
            document.getElementById('first_name').focus();
        }, 500);
    } else {
        e.target.style.background = '#f8f9fa';
        e.target.style.borderColor = '#e9ecef';
    }
});

// Format contact number
document.getElementById('contact_number').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/[^0-9]/g, '');
});

// Validate form before submission
document.querySelector('.rfid-form').addEventListener('submit', function(e) {
    const rfidNumber = document.getElementById('rfid_number').value;
    const contactNumber = document.getElementById('contact_number').value;
    
    if (rfidNumber.length < 8) {
        alert('⚠️ RFID number must be at least 8 characters long');
        e.preventDefault();
        return false;
    }
    
    if (contactNumber.length !== 11 || !contactNumber.startsWith('09')) {
        alert('⚠️ Please enter a valid Philippine mobile number (09XXXXXXXXX)');
        e.preventDefault();
        return false;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('.btn-primary');
    submitBtn.innerHTML = '<span class="btn-icon">⏳</span>Processing...';
    submitBtn.disabled = true;
});
</script>

<?php include '../includes/footer.php'; ?>