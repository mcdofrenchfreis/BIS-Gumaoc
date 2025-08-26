<?php
// Process Certificate Request Handler
require_once 'auth_check.php';
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: certificate-request.php");
    exit;
}

try {
    // Validate required fields
    $required_fields = ['certificateType', 'firstName', 'lastName', 'address1', 'civilStatus', 'gender', 'birthdate', 'birthplace', 'purpose'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: " . $field);
        }
    }
    
    // Sanitize and prepare data
    $certificate_type = trim($_POST['certificateType']);
    $full_name = trim(($_POST['firstName'] ?? '') . ' ' . ($_POST['middleName'] ?? '') . ' ' . ($_POST['lastName'] ?? ''));
    $full_name = preg_replace('/\s+/', ' ', $full_name); // Remove extra spaces
    
    $address = trim($_POST['address1']);
    $mobile_number = $_POST['full_mobile_number'] ?? '';
    if (empty($mobile_number) && !empty($_POST['mobileNumber'])) {
        $mobile_number = '+63' . $_POST['mobileNumber'];
    }
    
    $civil_status = $_POST['civilStatus'];
    $gender = $_POST['gender'];
    $birth_date = $_POST['birthdate'];
    $birth_place = trim($_POST['birthplace']);
    $citizenship = trim($_POST['citizenship']) ?: 'Filipino';
    $years_of_residence = !empty($_POST['yearsOfResidence']) ? (int)$_POST['yearsOfResidence'] : null;
    $purpose = trim($_POST['purpose']);
    
    // Validate mobile number format if provided
    if (!empty($_POST['mobileNumber'])) {
        if (!preg_match('/^9[0-9]{9}$/', $_POST['mobileNumber'])) {
            throw new Exception("Invalid mobile number format. Please enter a valid Philippine mobile number starting with 9.");
        }
    }
    
    // Validate birth date
    $birth_date_obj = new DateTime($birth_date);
    $today = new DateTime();
    $age = $today->diff($birth_date_obj)->y;
    
    if ($age < 0 || $age > 150) {
        throw new Exception("Invalid birth date. Please check your birthdate.");
    }
    
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
        } else {
            throw new Exception("Invalid file type or file too large. Please upload JPG, PNG, or PDF files under 5MB.");
        }
    }
    
    // Handle certificate-specific data
    $additional_data = [];
    
    if ($certificate_type === 'TRICYCLE PERMIT') {
        // Validate required tricycle fields
        $tricycle_required = ['makeType', 'motorNo', 'chassisNo', 'plateNo'];
        foreach ($tricycle_required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Missing required tricycle field: " . $field);
            }
        }
        
        $additional_data = [
            'vehicle_make_type' => trim($_POST['makeType']),
            'motor_no' => trim($_POST['motorNo']),
            'chassis_no' => trim($_POST['chassisNo']),
            'plate_no' => trim($_POST['plateNo']),
            'vehicle_color' => trim($_POST['vehicleColor'] ?? ''),
            'year_model' => !empty($_POST['yearModel']) ? (int)$_POST['yearModel'] : null,
            'body_no' => trim($_POST['bodyNo'] ?? ''),
            'operator_license' => trim($_POST['operatorLicense'] ?? '')
        ];
        
        // Validate year model if provided
        if ($additional_data['year_model']) {
            $current_year = date('Y');
            if ($additional_data['year_model'] < 1980 || $additional_data['year_model'] > $current_year) {
                throw new Exception("Year model must be between 1980 and {$current_year}.");
            }
        }
        
    } elseif ($certificate_type === 'CEDULA/CTC') {
        // Validate required cedula fields
        $cedula_required = ['cedulaYear', 'placeOfIssue', 'dateIssued', 'professionOccupation'];
        foreach ($cedula_required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Missing required cedula field: " . $field);
            }
        }
        
        $additional_data = [
            'cedula_year' => (int)$_POST['cedulaYear'],
            'place_of_issue' => trim($_POST['placeOfIssue']),
            'date_issued' => $_POST['dateIssued'],
            'profession_occupation' => trim($_POST['professionOccupation']),
            'height' => !empty($_POST['height']) ? (float)$_POST['height'] : null,
            'weight' => !empty($_POST['weight']) ? (float)$_POST['weight'] : null,
            'basic_tax_type' => $_POST['basicCommunityTaxType'] ?? 'voluntary',
            'basic_community_tax' => (float)($_POST['basicCommunityTax'] ?? 5.00),
            'gross_receipts_business' => !empty($_POST['grossReceiptsBusiness']) ? (float)$_POST['grossReceiptsBusiness'] : null,
            'salaries_profession' => !empty($_POST['salariesProfession']) ? (float)$_POST['salariesProfession'] : null,
            'income_real_property' => !empty($_POST['incomeRealProperty']) ? (float)$_POST['incomeRealProperty'] : null,
            'total_tax' => !empty($_POST['totalTax']) ? (float)$_POST['totalTax'] : null,
            'interest' => (float)($_POST['interest'] ?? 0.00),
            'total_amount_paid' => !empty($_POST['totalAmountPaid']) ? (float)$_POST['totalAmountPaid'] : null
        ];
        
    }
    
    // Insert certificate request with additional data
    $sql = "INSERT INTO certificate_requests (
        user_id, certificate_type, full_name, address, mobile_number, 
        civil_status, gender, birth_date, birth_place, citizenship, 
        years_of_residence, purpose, additional_data, proof_image, status, submitted_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
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
    
    if ($result) {
        $request_id = $pdo->lastInsertId();
        
        // Create success message based on certificate type
        $cert_name = $certificate_type;
        if ($certificate_type === 'CEDULA/CTC') {
            $cert_name = 'Community Tax Certificate (CTC)';
        } elseif ($certificate_type === 'TRICYCLE PERMIT') {
            $cert_name = 'Tricycle Operating Permit';
        }
        
        $_SESSION['success'] = "Your {$cert_name} request has been submitted successfully! Request ID: #" . str_pad($request_id, 5, '0', STR_PAD_LEFT) . ". You will be notified when it's ready for pickup at the Barangay Hall.";
        
        // Log the submission with additional details
        $log_details = "Certificate request submitted: ID #{$request_id}, Type: {$certificate_type}, User: " . $_SESSION['user_id'];
        if ($proof_image) {
            $log_details .= ", Proof file: {$proof_image}";
        }
        if (!empty($additional_data)) {
            $log_details .= ", Additional data: " . json_encode($additional_data);
        }
        error_log($log_details);
        
    } else {
        throw new Exception("Failed to submit certificate request. Please try again.");
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    error_log("Certificate request error: " . $e->getMessage() . " | User: " . ($_SESSION['user_id'] ?? 'unknown'));
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error occurred. Please try again later.";
    error_log("Certificate request DB error: " . $e->getMessage() . " | User: " . ($_SESSION['user_id'] ?? 'unknown'));
}

// Redirect back to certificate request page
header("Location: certificate-request.php");
exit;
?>