<?php
session_start();
$page_title = 'Business Permit Application - Barangay Gumaoc East';
$current_page = 'business-application';

require_once '../includes/db_connect.php';

// Check for admin view mode (readonly)
$admin_view = isset($_GET['admin_view']) && isset($_GET['readonly']);
$application_id = $_GET['admin_view'] ?? null;

// Get current user data for auto-population
$current_user = null;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $current_user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $current_user = null;
    }
}

// Generate reference number
$reference_no = 'BA-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            throw new Exception("User session not found. Please log in again.");
        }
        
        // Handle file upload
        $proof_image = null;
        if (isset($_FILES['proofImage']) && $_FILES['proofImage']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/business_proof/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['proofImage']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
            
            if (in_array($file_extension, $allowed_extensions) && $_FILES['proofImage']['size'] <= 5 * 1024 * 1024) {
                $filename = 'business_' . $user_id . '_' . time() . '.' . $file_extension;
                $filepath = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['proofImage']['tmp_name'], $filepath)) {
                    $proof_image = $filename;
                }
            }
        }
        
        // Extract mobile number
        $mobile_number = $_POST['full_mobile_number'] ?? '';
        if (empty($mobile_number) && !empty($_POST['mobileNumber'])) {
            $mobile_number = '+63' . $_POST['mobileNumber'];
        }
        
        // Combine names
        $owner_name = trim($_POST['first_name'] . ' ' . ($_POST['middle_name'] ? $_POST['middle_name'] . ' ' : '') . $_POST['last_name']);
        
        // Insert into database
        $stmt = $pdo->prepare("INSERT INTO business_applications 
            (user_id, reference_no, application_date, first_name, middle_name, last_name, 
             business_name, business_type, business_address, business_location, owner_name, 
             owner_address, contact_number, or_number, ctc_number, years_operation, 
             investment_capital, proof_image, status, submitted_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
        
        $result = $stmt->execute([
            $user_id, $_POST['reference_no'], $_POST['application_date'],
            $_POST['first_name'], $_POST['middle_name'] ?: null, $_POST['last_name'],
            $_POST['business_name'], $_POST['business_type'] ?? 'General Business',
            $_POST['business_location'], $_POST['business_location'], $owner_name,
            $_POST['owner_address'], $mobile_number ?: null, $_POST['or_number'],
            $_POST['ctc_number'], (int)($_POST['years_operation'] ?? 1),
            (float)($_POST['investment_capital'] ?? 0.00), $proof_image
        ]);
        
        if ($result) {
            $_SESSION['success'] = "Business permit application submitted successfully! Reference: " . $_POST['reference_no'];
            header('Location: business-application.php');
            exit;
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            min-height: 100vh; padding: 20px;
            opacity: 0; animation: fadeInPage 0.8s ease-out 0.3s forwards;
        }
        @keyframes fadeInPage { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .container {
            max-width: 1000px; margin: 90px auto 0; background: white;
            border-radius: 20px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden; animation: slideUp 0.8s ease-out 0.5s both;
        }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

        .header {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white; padding: 40px; text-align: center; position: relative;
        }
        .header h1 { font-size: 2.5rem; margin-bottom: 10px; font-weight: 700; }
        .header p { font-size: 1.2rem; opacity: 0.9; }
        .back-link {
            position: absolute; top: 20px; left: 20px;
            background: rgba(255, 255, 255, 0.2); color: white;
            padding: 10px 20px; border-radius: 25px; text-decoration: none;
            font-weight: 500; transition: all 0.3s ease;
        }
        .back-link:hover { background: rgba(255, 255, 255, 0.3); transform: translateY(-2px); }

        .content { padding: 40px; }
        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 500; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .form-section { background: #f8f9fa; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .form-section h3 { color: #4CAF50; margin-bottom: 20px; font-size: 1.3rem; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #495057; }
        .required { color: #e74c3c; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 12px 15px; border: 2px solid #e9ecef;
            border-radius: 10px; font-size: 14px; transition: all 0.3s ease;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: #4CAF50; box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        .name-row { display: grid; grid-template-columns: 1fr 0.8fr 1fr; gap: 15px; }
        .mobile-input-container {
            display: flex; align-items: center; border: 2px solid #e9ecef;
            border-radius: 10px; overflow: hidden; transition: all 0.3s ease;
        }
        .mobile-input-container:focus-within { border-color: #4CAF50; box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1); }
        .country-code {
            background: #f8f9fa; padding: 12px 15px; border-right: 1px solid #e9ecef;
            font-weight: 600; color: #495057; display: flex; align-items: center; gap: 5px;
        }
        .mobile-input-container input { border: none; padding: 12px 15px; flex: 1; font-size: 14px; }
        .mobile-input-container input:focus { outline: none; box-shadow: none; }

        .file-upload-container {
            position: relative; background: #f8f9fa; border: 2px dashed #dee2e6;
            border-radius: 10px; padding: 30px; text-align: center;
            transition: all 0.3s ease; cursor: pointer;
        }
        .file-upload-container:hover { border-color: #4CAF50; background: rgba(76, 175, 80, 0.05); }
        .file-input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .file-upload-display { pointer-events: none; }
        .file-upload-icon { font-size: 2.5rem; color: #6c757d; margin-bottom: 15px; }
        .file-upload-label { display: block; font-weight: 600; margin-bottom: 5px; }
        .file-upload-hint { display: block; font-size: 0.9rem; color: #6c757d; }

        .form-actions {
            display: flex; gap: 15px; justify-content: center;
            margin-top: 30px; padding-top: 30px; border-top: 2px solid #e9ecef;
        }
        .submit-btn, .reset-btn {
            display: flex; align-items: center; gap: 10px; padding: 15px 30px;
            border: none; border-radius: 25px; font-size: 16px; font-weight: 600;
            cursor: pointer; transition: all 0.3s ease;
        }
        .submit-btn {
            background: linear-gradient(135deg, #4CAF50, #45a049); color: white;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4); }
        .reset-btn {
            background: linear-gradient(135deg, #6c757d, #5a6268); color: white;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }
        .reset-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4); }

        @media (max-width: 768px) {
            .container { margin: 20px auto; } .header { padding: 30px 20px; }
            .header h1 { font-size: 2rem; } .content { padding: 30px 20px; }
            .name-row { grid-template-columns: 1fr; } .form-grid { grid-template-columns: 1fr; }
            .form-actions { flex-direction: column; align-items: center; }
            .submit-btn, .reset-btn { width: 100%; max-width: 300px; justify-content: center; }
        }
    </style>
</head>
<body>
    <?php include 'navbar_component.php'; ?>

    <div class="container">
        <div class="header">
            <a href="e-services.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to E-Services
            </a>
            <h1>🏢 Business Permit Application</h1>
            <p>Apply for your business permit online</p>
        </div>

        <div class="content">
            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="businessForm">
                <input type="hidden" name="reference_no" value="<?php echo htmlspecialchars($reference_no); ?>">
                
                <div class="form-section">
                    <h3><i class="fas fa-calendar"></i> Application Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="application_date">📅 Application Date <span class="required">*</span></label>
                            <input type="date" id="application_date" name="application_date" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="reference_display">📄 Reference Number</label>
                            <input type="text" value="<?php echo htmlspecialchars($reference_no); ?>" readonly 
                                   style="background: #e9ecef; font-family: 'Courier New', monospace;">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-user"></i> Personal Information</h3>
                    <div class="form-group full-width">
                        <label>👤 Full Name <span class="required">*</span></label>
                        <div class="name-row">
                            <input type="text" name="first_name" placeholder="First Name" 
                                   value="<?php echo htmlspecialchars($current_user['first_name'] ?? ''); ?>" required>
                            <input type="text" name="middle_name" placeholder="Middle Name (Optional)" 
                                   value="<?php echo htmlspecialchars($current_user['middle_name'] ?? ''); ?>">
                            <input type="text" name="last_name" placeholder="Last Name" 
                                   value="<?php echo htmlspecialchars($current_user['last_name'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="owner_address">🏠 Home Address <span class="required">*</span></label>
                            <textarea name="owner_address" rows="3" placeholder="Enter your complete home address..." required><?php echo htmlspecialchars($current_user['address'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="mobileNumber">📱 Mobile Number</label>
                            <div class="mobile-input-container">
                                <div class="country-code"><span>🇵🇭</span><span>+63</span></div>
                                <input type="tel" id="mobileNumber" name="mobileNumber" placeholder="9XX XXX XXXX" maxlength="10"
                                       value="<?php 
                                       if ($current_user && $current_user['phone']) {
                                           $phone = $current_user['phone'];
                                           echo substr($phone, 0, 3) === '+63' ? substr($phone, 3) : $phone;
                                       }
                                       ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-building"></i> Business Information</h3>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="business_name">🏪 Business Name <span class="required">*</span></label>
                            <input type="text" name="business_name" placeholder="Enter the name of your business" required>
                        </div>
                        <div class="form-group">
                            <label for="business_type">🏷️ Business Type <span class="required">*</span></label>
                            <select name="business_type" required>
                                <option value="">Select Business Type</option>
                                <option value="General Business">General Business</option>
                                <option value="Retail Store">Retail Store</option>
                                <option value="Restaurant/Food Service">Restaurant/Food Service</option>
                                <option value="Service Business">Service Business</option>
                                <option value="Home Business">Home Business</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="years_operation">📅 Years of Operation</label>
                            <input type="number" name="years_operation" value="1" min="0" max="100">
                        </div>
                        <div class="form-group">
                            <label for="investment_capital">💰 Investment Capital</label>
                            <input type="number" name="investment_capital" value="0.00" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="form-group full-width">
                        <label for="business_location">🏢 Business Address <span class="required">*</span></label>
                        <textarea name="business_location" rows="3" placeholder="Enter complete business address..." required></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-receipt"></i> Required Documents</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="or_number">🧾 OR Number <span class="required">*</span></label>
                            <input type="text" name="or_number" placeholder="Official Receipt Number" required>
                        </div>
                        <div class="form-group">
                            <label for="ctc_number">📋 CTC Number <span class="required">*</span></label>
                            <input type="text" name="ctc_number" placeholder="Community Tax Certificate Number" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3><i class="fas fa-camera"></i> Supporting Documents (Optional)</h3>
                    <div class="file-upload-container">
                        <input type="file" id="proofImage" name="proofImage" accept="image/*,.pdf" class="file-input">
                        <div class="file-upload-display">
                            <div class="file-upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                            <div class="file-upload-text">
                                <span class="file-upload-label">Click to upload or drag and drop</span>
                                <span class="file-upload-hint">PNG, JPG, PDF up to 5MB</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane"></i> Submit Application
                    </button>
                    <button type="reset" class="reset-btn">
                        <i class="fas fa-undo"></i> Reset Form
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        setupMobileValidation();
        setupFormValidation();
    });

    function setupMobileValidation() {
        const mobileInput = document.getElementById('mobileNumber');
        if (mobileInput) {
            mobileInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length > 10) this.value = this.value.substring(0, 10);
                if (this.value.length > 0 && this.value[0] !== '9') {
                    this.value = '9' + this.value.substring(1);
                }
            });
        }
    }

    function setupFormValidation() {
        const form = document.getElementById('businessForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const mobileInput = document.getElementById('mobileNumber');
                if (mobileInput && mobileInput.value) {
                    const mobilePattern = /^9[0-9]{9}$/;
                    if (!mobilePattern.test(mobileInput.value)) {
                        e.preventDefault();
                        alert('Please enter a valid Philippine mobile number starting with 9');
                        return;
                    }
                    const fullNumber = '+63' + mobileInput.value;
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'full_mobile_number';
                    hiddenInput.value = fullNumber;
                    this.appendChild(hiddenInput);
                }
            });
        }
    }
    </script>
</body>
</html>