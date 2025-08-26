<?php
require_once 'auth_check.php';

$page_title = 'Certificate Request - Barangay Gumaoc East';
$current_page = 'certificate-request';

// Initialize database connection
require_once '../includes/db_connect.php';

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $certificate_type = $_POST['certificateType'] ?? '';
        $full_name = trim(($_POST['firstName'] ?? '') . ' ' . ($_POST['middleName'] ?? '') . ' ' . ($_POST['lastName'] ?? ''));
        $address = $_POST['address1'] ?? '';
        $mobile_number = $_POST['full_mobile_number'] ?? $_POST['mobileNumber'] ?? '';
        $civil_status = $_POST['civilStatus'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $birth_date = $_POST['birthdate'] ?? '';
        $birth_place = $_POST['birthplace'] ?? '';
        $citizenship = $_POST['citizenship'] ?? 'Filipino';
        $years_of_residence = $_POST['yearsOfResidence'] ?? null;
        $purpose = $_POST['purpose'] ?? '';
        
        // Handle file upload for proof image
        $proof_image = null;
        if (isset($_FILES['proofImage']) && $_FILES['proofImage']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/certificate_proof/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['proofImage']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
            
            if (in_array($file_extension, $allowed_extensions) && $_FILES['proofImage']['size'] <= 5 * 1024 * 1024) {
                $filename = 'cert_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
                $filepath = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['proofImage']['tmp_name'], $filepath)) {
                    $proof_image = $filename;
                }
            }
        }
        
        // Handle certificate-specific data
        $additional_data = [];
        
        if ($certificate_type === 'TRICYCLE PERMIT') {
            $additional_data = [
                'vehicle_make_type' => $_POST['makeType'] ?? '',
                'motor_no' => $_POST['motorNo'] ?? '',
                'chassis_no' => $_POST['chassisNo'] ?? '',
                'plate_no' => $_POST['plateNo'] ?? '',
                'vehicle_color' => $_POST['vehicleColor'] ?? '',
                'year_model' => $_POST['yearModel'] ?? null,
                'body_no' => $_POST['bodyNo'] ?? '',
                'operator_license' => $_POST['operatorLicense'] ?? ''
            ];
        } elseif ($certificate_type === 'CEDULA/CTC') {
            $additional_data = [
                'cedula_year' => $_POST['cedulaYear'] ?? date('Y'),
                'place_of_issue' => $_POST['placeOfIssue'] ?? '',
                'date_issued' => $_POST['dateIssued'] ?? '',
                'profession_occupation' => $_POST['professionOccupation'] ?? '',
                'height' => $_POST['height'] ?? null,
                'weight' => $_POST['weight'] ?? null,
                'basic_tax_type' => $_POST['basicCommunityTaxType'] ?? 'voluntary',
                'basic_community_tax' => $_POST['basicCommunityTax'] ?? '5.00',
                'gross_receipts_business' => $_POST['grossReceiptsBusiness'] ?? null,
                'salaries_profession' => $_POST['salariesProfession'] ?? null,
                'income_real_property' => $_POST['incomeRealProperty'] ?? null,
                'total_tax' => $_POST['totalTax'] ?? null,
                'interest' => $_POST['interest'] ?? '0.00',
                'total_amount_paid' => $_POST['totalAmountPaid'] ?? null
            ];
        }
        
        // Prepare SQL for certificate request
        $sql = "INSERT INTO certificate_requests (
            user_id, certificate_type, full_name, address, mobile_number, 
            civil_status, gender, birth_date, birth_place, citizenship, 
            years_of_residence, purpose, additional_data, proof_image, status, submitted_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_SESSION['user_id'],
            $certificate_type,
            $full_name,
            $address,
            $mobile_number,
            $civil_status,
            $gender,
            $birth_date,
            $birth_place,
            $citizenship,
            $years_of_residence,
            $purpose,
            json_encode($additional_data),
            $proof_image
        ]);
        
        $_SESSION['success'] = "Certificate request submitted successfully! You will be notified when it's ready.";
        header("Location: certificate-request.php");
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error submitting request: " . $e->getMessage();
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            min-height: 100vh;
            padding: 20px;
            opacity: 0;
            animation: fadeInPage 0.8s ease-out 0.3s forwards;
        }
        
        @keyframes fadeInPage {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container {
            max-width: 1200px;
            margin: 90px auto 0;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideUp 0.8s ease-out 0.5s both;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .page-nav-link {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .page-nav-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .content {
            padding: 40px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
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

        .certificate-selection {
            text-align: center;
            margin-bottom: 40px;
        }

        .certificate-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .certificate-card {
            background: #f8f9fa;
            border: 3px solid #e9ecef;
            border-radius: 15px;
            padding: 30px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .certificate-card:hover {
            background: rgba(40, 167, 69, 0.1);
            border-color: #28a745;
            transform: translateY(-5px);
        }

        .certificate-card.selected {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border-color: #28a745;
        }

        .certificate-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
        }

        .certificate-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .certificate-description {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .form-container {
            display: none;
            margin-top: 40px;
        }

        .form-container.show {
            display: block;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .form-section h3 {
            color: #28a745;
            margin-bottom: 20px;
            font-size: 1.3rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
        }

        .back-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .back-btn:hover {
            background: #5a6268;
        }

        .submit-container {
            text-align: center;
            margin-top: 40px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.3);
        }

        .mobile-input-container {
            display: flex;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }

        .country-code {
            background: #f8f9fa;
            padding: 12px 15px;
            border-right: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .mobile-input-container input {
            border: none;
            flex: 1;
        }

        /* Certificate-specific sections */
        .tax-section {
            background: rgba(255, 235, 59, 0.1);
            border: 1px solid #ffeb3b;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }

        .tax-section h4 {
            color: #f57f17;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .radio-group {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-weight: 500;
        }

        .radio-option input[type="radio"] {
            width: 16px;
            height: 16px;
            accent-color: #28a745;
        }

        /* File upload styling */
        .file-upload-container {
            position: relative;
            border: 2px dashed #d0d7de;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-upload-container:hover {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.05);
        }

        .file-upload-container.drag-over {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.1);
            transform: scale(1.02);
        }

        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            top: 0;
            left: 0;
        }

        .file-upload-display {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            pointer-events: none;
        }

        .file-upload-icon {
            font-size: 3rem;
            color: #6c757d;
            transition: all 0.3s ease;
        }

        .file-upload-container:hover .file-upload-icon {
            color: #28a745;
            transform: scale(1.1);
        }

        .file-upload-text {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .file-upload-label {
            font-weight: 600;
            color: #495057;
            font-size: 16px;
        }

        .file-upload-hint {
            font-size: 14px;
            color: #6c757d;
        }

        .file-preview {
            margin-top: 20px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .file-preview img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 4px;
        }

        .file-preview-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .file-preview-icon {
            font-size: 1.5rem;
            color: #28a745;
        }

        .file-preview-details {
            flex: 1;
        }

        .file-preview-name {
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }

        .file-preview-size {
            font-size: 12px;
            color: #6c757d;
        }

        .file-remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: all 0.3s ease;
        }

        .file-remove-btn:hover {
            background: #c82333;
            transform: scale(1.1);
        }

        .section-description {
            color: #6c757d;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        /* Loading state */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 32px;
            height: 32px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #28a745;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            transform: translate(-50%, -50%);
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Enhanced mobile responsiveness */
        @media (max-width: 768px) {
            .radio-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .file-upload-container {
                padding: 20px;
            }
            
            .file-upload-icon {
                font-size: 2rem;
            }
            
            .tax-section {
                padding: 15px;
            }
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .content {
                padding: 20px;
            }
            
            .certificate-grid {
                grid-template-columns: 1fr;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>


    <?php include 'navbar_component.php'; ?>

    <div class="container">
        <div class="content">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($current_user): ?>
                <div class="alert alert-success auto-populated-notice">
                    <i class="fas fa-user-check"></i> Your personal information has been automatically filled from your profile. Please review and update if necessary.
                </div>
            <?php endif; ?>

            <div class="certificate-selection" id="selectionSection">
                <h2>Select Certificate Type</h2>
                <p>Choose the type of certificate you would like to request</p>
                
                <div class="certificate-grid">
                    <div class="certificate-card" data-type="BRGY. CLEARANCE">
                        <i class="fas fa-home certificate-icon"></i>
                        <div class="certificate-title">Barangay Clearance</div>
                        <div class="certificate-description">Certificate of good moral character</div>
                    </div>
                    
                    <div class="certificate-card" data-type="BRGY. INDIGENCY">
                        <i class="fas fa-hand-holding-heart certificate-icon"></i>
                        <div class="certificate-title">Indigency Certificate</div>
                        <div class="certificate-description">Certificate of financial status</div>
                    </div>
                    
                    <div class="certificate-card" data-type="PROOF OF RESIDENCY">
                        <i class="fas fa-map-marker-alt certificate-icon"></i>
                        <div class="certificate-title">Residency Certificate</div>
                        <div class="certificate-description">Proof of residence</div>
                    </div>
                    
                    <div class="certificate-card" data-type="TRICYCLE PERMIT">
                        <i class="fas fa-motorcycle certificate-icon"></i>
                        <div class="certificate-title">Tricycle Permit</div>
                        <div class="certificate-description">Operating permit for tricycle</div>
                    </div>
                    
                    <div class="certificate-card" data-type="CEDULA/CTC">
                        <i class="fas fa-file-alt certificate-icon"></i>
                        <div class="certificate-title">Community Tax Certificate</div>
                        <div class="certificate-description">Cedula/CTC Document</div>
                    </div>
                </div>
            </div>

            <div class="form-container" id="formContainer">
                <button class="back-btn" onclick="showSelection()">
                    <i class="fas fa-arrow-left"></i> Back to Selection
                </button>
                
                <form method="POST" action="process_certificate_request.php" id="certificateForm" enctype="multipart/form-data">
                    <input type="hidden" id="selectedType" name="certificateType" value="">
                    
                    <div class="form-section">
                        <h3><i class="fas fa-user"></i> Personal Information</h3>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="firstName">First Name *</label>
                                <input type="text" id="firstName" name="firstName" required 
                                       value="<?php echo htmlspecialchars($current_user['first_name'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="middleName">Middle Name</label>
                                <input type="text" id="middleName" name="middleName" 
                                       value="<?php echo htmlspecialchars($current_user['middle_name'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="lastName">Last Name *</label>
                                <input type="text" id="lastName" name="lastName" required 
                                       value="<?php echo htmlspecialchars($current_user['last_name'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="address1">Address *</label>
                                <input type="text" id="address1" name="address1" required 
                                       placeholder="Street Address, Barangay Gumaoc East"
                                       value="<?php echo htmlspecialchars($current_user['address'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="mobileNumber">Mobile Number</label>
                                <div class="mobile-input-container">
                                    <div class="country-code">
                                        <span>🇵🇭</span>
                                        <span>+63</span>
                                    </div>
                                    <input type="tel" id="mobileNumber" name="mobileNumber" 
                                           placeholder="9XX XXX XXXX" maxlength="10"
                                           value="<?php 
                                           if ($current_user && $current_user['phone']) {
                                               $phone = $current_user['phone'];
                                               if (substr($phone, 0, 3) === '+63') {
                                                   echo substr($phone, 3);
                                               } else {
                                                   echo $phone;
                                               }
                                           }
                                           ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="civilStatus">Civil Status *</label>
                                <select id="civilStatus" name="civilStatus" required>
                                    <option value="">Select Civil Status</option>
                                    <option value="Single" <?php echo ($current_user['civil_status'] ?? '') === 'Single' ? 'selected' : ''; ?>>Single</option>
                                    <option value="Married" <?php echo ($current_user['civil_status'] ?? '') === 'Married' ? 'selected' : ''; ?>>Married</option>
                                    <option value="Divorced" <?php echo ($current_user['civil_status'] ?? '') === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                                    <option value="Widowed" <?php echo ($current_user['civil_status'] ?? '') === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="gender">Gender *</label>
                                <select id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($current_user['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($current_user['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="birthdate">Birthdate *</label>
                                <input type="date" id="birthdate" name="birthdate" required 
                                       value="<?php echo htmlspecialchars($current_user['birthdate'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="birthplace">Birthplace *</label>
                                <input type="text" id="birthplace" name="birthplace" required 
                                       placeholder="City/Municipality, Province"
                                       value="<?php echo htmlspecialchars($current_user['birth_place'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="citizenship">Citizenship</label>
                                <input type="text" id="citizenship" name="citizenship" 
                                       value="Filipino" placeholder="Filipino">
                            </div>
                            
                            <div class="form-group">
                                <label for="yearsOfResidence">Years of Residence</label>
                                <input type="number" id="yearsOfResidence" name="yearsOfResidence" 
                                       min="0" placeholder="Number of years">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="purpose">Purpose *</label>
                            <textarea id="purpose" name="purpose" required rows="3" 
                                      placeholder="State the purpose for requesting this certificate..."></textarea>
                        </div>
                    </div>
                    
                    <!-- Tricycle Permit Details Section -->
                    <div class="form-section" id="tricycleSection" style="display: none;">
                        <h3><i class="fas fa-motorcycle"></i> Tricycle Vehicle Information</h3>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="makeType">Make and Type *</label>
                                <input type="text" id="makeType" name="makeType" 
                                       placeholder="e.g., Honda TMX-155">
                            </div>
                            
                            <div class="form-group">
                                <label for="motorNo">Motor No. *</label>
                                <input type="text" id="motorNo" name="motorNo" 
                                       placeholder="Engine/Motor Number">
                            </div>
                            
                            <div class="form-group">
                                <label for="chassisNo">Chassis No. *</label>
                                <input type="text" id="chassisNo" name="chassisNo" 
                                       placeholder="Chassis Number">
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="plateNo">Plate No. *</label>
                                <input type="text" id="plateNo" name="plateNo" 
                                       placeholder="License Plate Number">
                            </div>
                            
                            <div class="form-group">
                                <label for="vehicleColor">Vehicle Color</label>
                                <input type="text" id="vehicleColor" name="vehicleColor" 
                                       placeholder="Primary color of tricycle">
                            </div>
                            
                            <div class="form-group">
                                <label for="yearModel">Year Model</label>
                                <input type="number" id="yearModel" name="yearModel" 
                                       placeholder="e.g., 2020" min="1980" max="<?php echo date('Y'); ?>">
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="bodyNo">Body No.</label>
                                <input type="text" id="bodyNo" name="bodyNo" 
                                       placeholder="Body/Frame Number (if applicable)">
                            </div>
                            
                            <div class="form-group">
                                <label for="operatorLicense">Operator's License No.</label>
                                <input type="text" id="operatorLicense" name="operatorLicense" 
                                       placeholder="License Number">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cedula/CTC Details Section -->
                    <div class="form-section" id="cedulaSection" style="display: none;">
                        <h3><i class="fas fa-file-invoice-dollar"></i> Community Tax Certificate Details</h3>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="cedulaYear">Year *</label>
                                <input type="number" id="cedulaYear" name="cedulaYear" 
                                       value="<?php echo date('Y'); ?>" 
                                       min="<?php echo date('Y') - 5; ?>" 
                                       max="<?php echo date('Y') + 1; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="placeOfIssue">Place of Issue *</label>
                                <input type="text" id="placeOfIssue" name="placeOfIssue" 
                                       value="San Jose Del Monte City, Bulacan" 
                                       placeholder="City/Municipality, Province">
                            </div>
                            
                            <div class="form-group">
                                <label for="dateIssued">Date Issued *</label>
                                <input type="date" id="dateIssued" name="dateIssued" 
                                       value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="professionOccupation">Profession/Occupation *</label>
                                <input type="text" id="professionOccupation" name="professionOccupation" 
                                       placeholder="e.g., Teacher, Driver, Farmer">
                            </div>
                            
                            <div class="form-group">
                                <label for="height">Height (cm)</label>
                                <input type="number" id="height" name="height" 
                                       placeholder="e.g., 170" min="100" max="250">
                            </div>
                            
                            <div class="form-group">
                                <label for="weight">Weight (kg)</label>
                                <input type="number" id="weight" name="weight" 
                                       placeholder="e.g., 65" min="20" max="200">
                            </div>
                        </div>
                        
                        <!-- Tax Information -->
                        <div class="tax-section">
                            <h4>Tax Information</h4>
                            
                            <div class="form-group">
                                <label>Basic Community Tax *</label>
                                <div class="radio-group">
                                    <label class="radio-option">
                                        <input type="radio" name="basicCommunityTaxType" value="voluntary" checked>
                                        <span>Voluntary (₱5.00)</span>
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="basicCommunityTaxType" value="exempted">
                                        <span>Exempted (₱1.00)</span>
                                    </label>
                                </div>
                                <input type="number" id="basicCommunityTax" name="basicCommunityTax" 
                                       value="5.00" step="0.01" readonly>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="grossReceiptsBusiness">Gross Receipts from Business</label>
                                    <input type="number" id="grossReceiptsBusiness" name="grossReceiptsBusiness" 
                                           placeholder="0.00" step="0.01">
                                </div>
                                
                                <div class="form-group">
                                    <label for="salariesProfession">Salaries from Profession</label>
                                    <input type="number" id="salariesProfession" name="salariesProfession" 
                                           placeholder="0.00" step="0.01">
                                </div>
                                
                                <div class="form-group">
                                    <label for="incomeRealProperty">Income from Real Property</label>
                                    <input type="number" id="incomeRealProperty" name="incomeRealProperty" 
                                           placeholder="0.00" step="0.01">
                                </div>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="totalTax">Total Tax</label>
                                    <input type="number" id="totalTax" name="totalTax" 
                                           placeholder="0.00" step="0.01" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="interest">Interest</label>
                                    <input type="number" id="interest" name="interest" 
                                           value="0.00" step="0.01">
                                </div>
                                
                                <div class="form-group">
                                    <label for="totalAmountPaid">Total Amount</label>
                                    <input type="number" id="totalAmountPaid" name="totalAmountPaid" 
                                           placeholder="0.00" step="0.01" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Optional Proof Image Section -->
                    <div class="form-section">
                        <h3><i class="fas fa-camera"></i> Optional Proof Image</h3>
                        <p class="section-description">Upload supporting documents or proof (optional)</p>
                        
                        <div class="file-upload-container">
                            <input type="file" id="proofImage" name="proofImage" 
                                   accept="image/*,.pdf" class="file-input">
                            <div class="file-upload-display">
                                <div class="file-upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <div class="file-upload-text">
                                    <span class="file-upload-label">Click to upload or drag and drop</span>
                                    <span class="file-upload-hint">PNG, JPG, PDF up to 5MB</span>
                                </div>
                            </div>
                            <div class="file-preview" id="filePreview" style="display: none;"></div>
                        </div>
                    </div>
                    
                    <div class="submit-container">
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all components
            setupCertificateSelection();
            setupMobileValidation();
            setupTaxCalculation();
            setupFileUpload();
            
            // Ensure all certificate sections are hidden initially
            hideCertificateSections();
            
            // Debug: Log available certificate sections
            console.log('Certificate sections initialized:', {
                tricycle: !!document.getElementById('tricycleSection'),
                cedula: !!document.getElementById('cedulaSection')
            });
        });

        function setupCertificateSelection() {
            const cards = document.querySelectorAll('.certificate-card');
            
            cards.forEach(card => {
                card.addEventListener('click', function() {
                    // Remove selection from all cards
                    cards.forEach(c => c.classList.remove('selected'));
                    
                    // Select clicked card
                    this.classList.add('selected');
                    
                    // Get certificate type
                    const type = this.getAttribute('data-type');
                    const selectedTypeInput = document.getElementById('selectedType');
                    if (selectedTypeInput) {
                        selectedTypeInput.value = type;
                    }
                    
                    // Hide all certificate sections immediately before showing form
                    hideCertificateSections();
                    
                    // Show form after delay with proper certificate type
                    setTimeout(() => {
                        showForm(type);
                    }, 200);
                });
            });
        }

        function showForm(certificateType) {
            console.log('Showing form for certificate type:', certificateType);
            
            document.getElementById('selectionSection').style.display = 'none';
            
            // Hide all certificate-specific sections first
            hideCertificateSections();
            
            // Show the form container
            document.getElementById('formContainer').classList.add('show');
            document.getElementById('formContainer').style.display = 'block';
            
            // Show relevant section based on certificate type after a brief delay
            setTimeout(() => {
                if (certificateType === 'TRICYCLE PERMIT') {
                    const section = document.getElementById('tricycleSection');
                    if (section) {
                        section.style.display = 'block';
                        console.log('Tricycle section shown');
                    }
                    setRequiredFields('tricycle');
                } else if (certificateType === 'CEDULA/CTC') {
                    const section = document.getElementById('cedulaSection');
                    if (section) {
                        section.style.display = 'block';
                        console.log('Cedula section shown');
                    }
                    setRequiredFields('cedula');
                } else {
                    console.log('Standard certificate type, no additional section needed');
                }
                
                // Debug: Log current section visibility
                logSectionVisibility();
            }, 100);
        }
        
        function logSectionVisibility() {
            const tricycleSection = document.getElementById('tricycleSection');
            const cedulaSection = document.getElementById('cedulaSection');
            
            console.log('Section visibility:', {
                tricycle: tricycleSection ? tricycleSection.style.display : 'not found',
                cedula: cedulaSection ? cedulaSection.style.display : 'not found'
            });
        }

        function hideCertificateSections() {
            console.log('Hiding all certificate sections');
            
            // Force hide all certificate-specific sections
            const tricycleSection = document.getElementById('tricycleSection');
            const cedulaSection = document.getElementById('cedulaSection');
            
            if (tricycleSection) {
                tricycleSection.style.display = 'none';
                console.log('Tricycle section hidden');
            }
            if (cedulaSection) {
                cedulaSection.style.display = 'none';
                console.log('Cedula section hidden');
            }
            
            // Remove required attributes from all certificate-specific fields
            document.querySelectorAll('#tricycleSection input, #cedulaSection input').forEach(input => {
                input.removeAttribute('required');
            });
            
            // Debug: Verify all sections are hidden
            logSectionVisibility();
        }

        function setRequiredFields(certificateType) {
            if (certificateType === 'tricycle') {
                // Set required fields for tricycle permit
                const requiredFields = ['makeType', 'motorNo', 'chassisNo', 'plateNo'];
                requiredFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field) field.setAttribute('required', 'required');
                });
            } else if (certificateType === 'cedula') {
                // Set required fields for cedula
                const requiredFields = ['cedulaYear', 'placeOfIssue', 'dateIssued', 'professionOccupation'];
                requiredFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field) field.setAttribute('required', 'required');
                });
            }
        }

        function showSelection() {
            // Hide form container
            document.getElementById('formContainer').classList.remove('show');
            setTimeout(() => {
                document.getElementById('formContainer').style.display = 'none';
            }, 300);
            
            // Show selection section
            document.getElementById('selectionSection').style.display = 'block';
            
            // Clear all selections
            document.querySelectorAll('.certificate-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Clear selected type
            const selectedTypeInput = document.getElementById('selectedType');
            if (selectedTypeInput) selectedTypeInput.value = '';
            
            // Hide all certificate sections immediately
            hideCertificateSections();
            
            // Reset the entire form to clear all data
            const form = document.getElementById('certificateForm');
            if (form) {
                form.reset();
                
                // Re-populate user data after reset
                setTimeout(() => {
                    // The auto-populate functionality will handle this
                    <?php if ($current_user): ?>
                    // Re-populate basic user data
                    const firstNameField = document.getElementById('firstName');
                    const middleNameField = document.getElementById('middleName');
                    const lastNameField = document.getElementById('lastName');
                    const addressField = document.getElementById('address1');
                    const mobileField = document.getElementById('mobileNumber');
                    const civilStatusField = document.getElementById('civilStatus');
                    const genderField = document.getElementById('gender');
                    const birthdateField = document.getElementById('birthdate');
                    const birthplaceField = document.getElementById('birthplace');
                    
                    if (firstNameField) firstNameField.value = '<?php echo htmlspecialchars($current_user['first_name'] ?? ''); ?>';
                    if (middleNameField) middleNameField.value = '<?php echo htmlspecialchars($current_user['middle_name'] ?? ''); ?>';
                    if (lastNameField) lastNameField.value = '<?php echo htmlspecialchars($current_user['last_name'] ?? ''); ?>';
                    if (addressField) addressField.value = '<?php echo htmlspecialchars($current_user['address'] ?? ''); ?>';
                    if (mobileField) {
                        let phone = '<?php 
                        if ($current_user && $current_user['phone']) {
                            $phone = $current_user['phone'];
                            if (substr($phone, 0, 3) === '+63') {
                                echo substr($phone, 3);
                            } else {
                                echo $phone;
                            }
                        }
                        ?>';
                        mobileField.value = phone;
                    }
                    if (civilStatusField) civilStatusField.value = '<?php echo ($current_user['civil_status'] ?? ''); ?>';
                    if (genderField) genderField.value = '<?php echo ($current_user['gender'] ?? ''); ?>';
                    if (birthdateField) birthdateField.value = '<?php echo htmlspecialchars($current_user['birthdate'] ?? ''); ?>';
                    if (birthplaceField) birthplaceField.value = '<?php echo htmlspecialchars($current_user['birth_place'] ?? ''); ?>';
                    <?php endif; ?>
                }, 100);
            }
        }

        function setupMobileValidation() {
            const mobileInput = document.getElementById('mobileNumber');
            
            mobileInput.addEventListener('input', function() {
                // Remove non-digits
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Limit to 10 digits
                if (this.value.length > 10) {
                    this.value = this.value.substring(0, 10);
                }
                
                // Must start with 9
                if (this.value.length > 0 && this.value[0] !== '9') {
                    this.value = '9' + this.value.substring(1);
                }
            });
        }

        function setupTaxCalculation() {
            // Tax type radio buttons
            const taxTypeRadios = document.querySelectorAll('input[name="basicCommunityTaxType"]');
            taxTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    const basicTaxField = document.getElementById('basicCommunityTax');
                    if (this.value === 'voluntary') {
                        basicTaxField.value = '5.00';
                    } else if (this.value === 'exempted') {
                        basicTaxField.value = '1.00';
                    }
                    calculateTotalTax();
                });
            });
            
            // Income fields for additional tax calculation
            const incomeFields = ['grossReceiptsBusiness', 'salariesProfession', 'incomeRealProperty', 'interest'];
            incomeFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('input', calculateTotalTax);
                }
            });
        }

        function calculateTotalTax() {
            const basicTax = parseFloat(document.getElementById('basicCommunityTax').value) || 0;
            const grossReceipts = parseFloat(document.getElementById('grossReceiptsBusiness').value) || 0;
            const salaries = parseFloat(document.getElementById('salariesProfession').value) || 0;
            const realProperty = parseFloat(document.getElementById('incomeRealProperty').value) || 0;
            const interest = parseFloat(document.getElementById('interest').value) || 0;
            
            // Calculate additional tax (1 peso for every 1000 pesos of income)
            let additionalTax = 0;
            if (grossReceipts > 0) {
                additionalTax += Math.floor(grossReceipts / 1000);
            }
            if (salaries > 0) {
                additionalTax += Math.floor(salaries / 1000);
            }
            if (realProperty > 0) {
                additionalTax += Math.floor(realProperty / 1000);
            }
            
            // Maximum additional tax is 5000
            additionalTax = Math.min(additionalTax, 5000);
            
            const totalTax = basicTax + additionalTax;
            const totalAmount = totalTax + interest;
            
            document.getElementById('totalTax').value = totalTax.toFixed(2);
            document.getElementById('totalAmountPaid').value = totalAmount.toFixed(2);
        }

        function setupFileUpload() {
            const fileInput = document.getElementById('proofImage');
            const fileContainer = document.querySelector('.file-upload-container');
            const filePreview = document.getElementById('filePreview');
            
            // Handle file selection
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    showFilePreview(file);
                }
            });
            
            // Handle drag and drop
            fileContainer.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('drag-over');
            });
            
            fileContainer.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('drag-over');
            });
            
            fileContainer.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('drag-over');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    showFilePreview(files[0]);
                }
            });
        }

        function showFilePreview(file) {
            const preview = document.getElementById('filePreview');
            const fileSize = formatFileSize(file.size);
            
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <div class="file-preview-info">
                            <div class="file-preview-icon">🖼️</div>
                            <div class="file-preview-details">
                                <div class="file-preview-name">${file.name}</div>
                                <div class="file-preview-size">${fileSize}</div>
                            </div>
                            <button type="button" class="file-remove-btn" onclick="removeFile()">
                                ×
                            </button>
                        </div>
                    `;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = `
                    <div class="file-preview-info">
                        <div class="file-preview-icon">📄</div>
                        <div class="file-preview-details">
                            <div class="file-preview-name">${file.name}</div>
                            <div class="file-preview-size">${fileSize}</div>
                        </div>
                        <button type="button" class="file-remove-btn" onclick="removeFile()">
                            ×
                        </button>
                    </div>
                `;
                preview.style.display = 'block';
            }
        }

        function removeFile() {
            document.getElementById('proofImage').value = '';
            document.getElementById('filePreview').style.display = 'none';
            document.getElementById('filePreview').innerHTML = '';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Enhanced form validation
        document.getElementById('certificateForm').addEventListener('submit', function(e) {
            const mobileInput = document.getElementById('mobileNumber');
            
            // Mobile number validation
            if (mobileInput.value) {
                const mobilePattern = /^9[0-9]{9}$/;
                if (!mobilePattern.test(mobileInput.value)) {
                    e.preventDefault();
                    alert('Please enter a valid Philippine mobile number starting with 9 (10 digits total)');
                    mobileInput.focus();
                    return;
                }
                
                // Add full mobile number as hidden field
                const fullNumber = '+63' + mobileInput.value;
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'full_mobile_number';
                hiddenInput.value = fullNumber;
                this.appendChild(hiddenInput);
            }
            
            // Validate certificate-specific fields
            const certificateType = document.getElementById('selectedType').value;
            
            if (certificateType === 'TRICYCLE PERMIT') {
                const yearModel = document.getElementById('yearModel').value;
                if (yearModel) {
                    const year = parseInt(yearModel);
                    const currentYear = new Date().getFullYear();
                    if (year < 1980 || year > currentYear) {
                        e.preventDefault();
                        alert(`Please enter a valid year between 1980 and ${currentYear}`);
                        document.getElementById('yearModel').focus();
                        return;
                    }
                }
            }
            
            if (certificateType === 'CEDULA/CTC') {
                const totalTax = parseFloat(document.getElementById('totalTax').value) || 0;
                if (totalTax > 5005) { // 5000 max additional + 5 basic
                    e.preventDefault();
                    alert('Total tax cannot exceed ₱5,005.00. Please check your income amounts.');
                    return;
                }
            }
        });
    </script>
</body>
</html>