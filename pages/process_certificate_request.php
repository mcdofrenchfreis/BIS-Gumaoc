<?php
session_start();
include '../includes/db_connect.php';
include '../includes/QueueManager.php'; // Add this line

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: certificate-request.php');
    exit;
}

try {
    // Get user ID if logged in (for business applications), null for guest users
    $user_id = $_SESSION['user_id'] ?? null;
    
    // Get form data
    $certificate_type = $_POST['certificateType'] ?? '';
    $first_name = trim($_POST['firstName'] ?? '');
    $middle_name = trim($_POST['middleName'] ?? '');
    $last_name = trim($_POST['lastName'] ?? '');
    $address = trim($_POST['address1'] ?? '');
    $mobile_number = trim($_POST['mobileNumber'] ?? '');
    $civil_status = $_POST['civilStatus'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $birth_date = $_POST['birthdate'] ?? '';
    $birth_place = trim($_POST['birthplace'] ?? '');
    $citizenship = trim($_POST['citizenship'] ?? '') ?: 'Filipino';
    $years_of_residence = !empty($_POST['yearsOfResidence']) ? (int)$_POST['yearsOfResidence'] : null;
    $purpose = trim($_POST['purpose'] ?? '');
    
    // Validate required fields
    if (empty($certificate_type) || empty($first_name) || empty($last_name) || 
        empty($address) || empty($civil_status) || empty($gender) || 
        empty($birth_date) || empty($birth_place) || empty($purpose)) {
        throw new Exception('Please fill in all required fields.');
    }
    
    // Validate certificate type
    $allowed_types = ['BRGY. CLEARANCE', 'BRGY. INDIGENCY', 'TRICYCLE PERMIT', 'PROOF OF RESIDENCY', 'BUSINESS APPLICATION'];
    if (!in_array($certificate_type, $allowed_types)) {
        throw new Exception('Invalid certificate type selected.');
    }
    
    // Construct full name
    $full_name = $first_name;
    if (!empty($middle_name)) {
        $full_name .= ' ' . $middle_name;
    }
    $full_name .= ' ' . $last_name;
    
    // Format mobile number
    if (!empty($mobile_number)) {
        // Remove any existing +63 prefix and ensure it starts with +63
        $mobile_number = preg_replace('/^\+?63/', '', $mobile_number);
        $mobile_number = '+63' . $mobile_number;
        
        // Validate mobile number format
        if (!preg_match('/^\+639[0-9]{9}$/', $mobile_number)) {
            throw new Exception('Please enter a valid Philippine mobile number.');
        }
    } else {
        $mobile_number = null;
    }
    
    // Initialize tricycle permit fields
    $vehicle_make_type = null;
    $motor_no = null;
    $chassis_no = null;
    $plate_no = null;
    $vehicle_color = null;
    $year_model = null;
    $body_no = null;
    $operator_license = null;
    
    // Handle tricycle permit specific fields
    if ($certificate_type === 'TRICYCLE PERMIT') {
        $vehicle_make_type = trim($_POST['makeType'] ?? '');
        $motor_no = trim($_POST['motorNo'] ?? '');
        $chassis_no = trim($_POST['chassisNo'] ?? '');
        $plate_no = trim($_POST['plateNo'] ?? '');
        $vehicle_color = trim($_POST['vehicleColor'] ?? '') ?: null;
        $year_model = !empty($_POST['yearModel']) ? (int)$_POST['yearModel'] : null;
        $body_no = trim($_POST['bodyNo'] ?? '') ?: null;
        $operator_license = trim($_POST['operatorLicense'] ?? '') ?: null;
        
        // Validate required tricycle fields
        if (empty($vehicle_make_type) || empty($motor_no) || empty($chassis_no) || empty($plate_no)) {
            throw new Exception('Please fill in all required tricycle details (Make/Type, Motor No., Chassis No., and Plate No.).');
        }
        
        // Validate year model if provided
        if ($year_model !== null) {
            $current_year = date('Y');
            if ($year_model < 1980 || $year_model > $current_year) {
                throw new Exception("Year model must be between 1980 and {$current_year}.");
            }
        }
        
        // Validate operator license format if provided
        if ($operator_license !== null && !preg_match('/^[0-9\-]+$/', $operator_license)) {
            throw new Exception('Operator license should contain only numbers and dashes.');
        }
    }
    
    // Initialize business application fields
    $business_name = null;
    $business_location = null;
    $business_owner_address = null;
    $business_or_number = null;
    $business_ctc_number = null;
    $business_application_date = null;
    $business_reference_no = null;
    
    // Handle business application specific fields
    if ($certificate_type === 'BUSINESS APPLICATION') {
        $business_name = trim($_POST['businessName'] ?? '');
        $business_location = trim($_POST['businessLocation'] ?? '');
        $business_owner_address = trim($_POST['businessOwnerAddress'] ?? '');
        $business_or_number = trim($_POST['businessOrNumber'] ?? '');
        $business_ctc_number = trim($_POST['businessCtcNumber'] ?? '');
        $business_application_date = $_POST['businessApplicationDate'] ?? '';
        $business_reference_no = trim($_POST['businessReferenceNo'] ?? '');
        
        // Validate required business fields
        if (empty($business_name) || empty($business_location) || empty($business_owner_address) || 
            empty($business_or_number) || empty($business_ctc_number)) {
            throw new Exception('Please fill in all required business application fields.');
        }
        
        // Generate reference number if not provided
        if (empty($business_reference_no)) {
            $year = date('Y');
            $random_num = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $business_reference_no = "BA-{$year}-{$random_num}";
        }
        
        // Validate business application date
        if (!empty($business_application_date)) {
            $business_date_obj = DateTime::createFromFormat('Y-m-d', $business_application_date);
            if (!$business_date_obj || $business_date_obj->format('Y-m-d') !== $business_application_date) {
                throw new Exception('Please enter a valid business application date.');
            }
        }
        
        // Handle file uploads
        $ctc_image_path = null;
        $certificate_image_path = null;
        
        // Create upload directory if it doesn't exist
        $upload_dir = '../assets/uploads/business_applications/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Handle CTC image upload
        if (isset($_FILES['ctcImage']) && $_FILES['ctcImage']['error'] === UPLOAD_ERR_OK) {
            $ctc_image_path = handleFileUpload($_FILES['ctcImage'], $upload_dir, 'ctc_' . $business_reference_no);
        }
        
        // Handle certificate image upload
        if (isset($_FILES['certificateImage']) && $_FILES['certificateImage']['error'] === UPLOAD_ERR_OK) {
            $certificate_image_path = handleFileUpload($_FILES['certificateImage'], $upload_dir, 'cert_' . $business_reference_no);
        }
    }
    
    // Validate birth date
    $birth_date_obj = DateTime::createFromFormat('Y-m-d', $birth_date);
    if (!$birth_date_obj || $birth_date_obj->format('Y-m-d') !== $birth_date) {
        throw new Exception('Please enter a valid birth date.');
    }
    
    // Check if birth date is not in the future
    if ($birth_date_obj > new DateTime()) {
        throw new Exception('Birth date cannot be in the future.');
    }
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Handle business application differently
    if ($certificate_type === 'BUSINESS APPLICATION') {
        // Insert into business_applications table
        $business_sql = "INSERT INTO business_applications (
            user_id, reference_no, application_date, first_name, middle_name, last_name,
            business_name, business_type, business_address, business_location,
            owner_name, owner_address, contact_number, or_number, ctc_number,
            years_operation, investment_capital, status, submitted_at
        ) VALUES (
            :user_id, :reference_no, :application_date, :first_name, :middle_name, :last_name,
            :business_name, 'General Business', :business_location, :business_location,
            :owner_name, :business_owner_address, :mobile_number, :or_number, :ctc_number,
            1, 0.00, 'pending', NOW()
        )";
        
        $business_stmt = $pdo->prepare($business_sql);
        
        // Bind business application parameters
        $business_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $business_stmt->bindParam(':reference_no', $business_reference_no);
        $business_stmt->bindParam(':application_date', $business_application_date ?: date('Y-m-d'));
        $business_stmt->bindParam(':first_name', $first_name);
        $business_stmt->bindParam(':middle_name', $middle_name);
        $business_stmt->bindParam(':last_name', $last_name);
        $business_stmt->bindParam(':business_name', $business_name);
        $business_stmt->bindParam(':business_location', $business_location);
        $business_stmt->bindParam(':owner_name', $full_name);
        $business_stmt->bindParam(':business_owner_address', $business_owner_address);
        $business_stmt->bindParam(':mobile_number', $mobile_number);
        $business_stmt->bindParam(':or_number', $business_or_number);
        $business_stmt->bindParam(':ctc_number', $business_ctc_number);
        
        if ($business_stmt->execute()) {
            $request_id = $pdo->lastInsertId();
            
            // Store file attachment information separately if files were uploaded
            if ($ctc_image_path || $certificate_image_path) {
                $attachments_sql = "INSERT INTO business_attachments (business_id, ctc_image, certificate_image) VALUES (?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE ctc_image = VALUES(ctc_image), certificate_image = VALUES(certificate_image)";
                try {
                    $attachments_stmt = $pdo->prepare($attachments_sql);
                    $attachments_stmt->execute([$request_id, $ctc_image_path, $certificate_image_path]);
                } catch (PDOException $e) {
                    // If the table doesn't exist, log the file paths for future reference
                    error_log("Business application attachments - ID: {$request_id}, CTC: {$ctc_image_path}, Certificate: {$certificate_image_path}");
                }
            }
            
            // Generate queue ticket for business application using enhanced method
            $queueManager = new QueueManager($pdo);
            
            $queue_result = $queueManager->generateTicketForForm(
                'business_application',
                $full_name,
                $mobile_number,
                "Business Application: {$business_name}"
            );
            
            if ($queue_result['success']) {
                // Commit transaction
                $pdo->commit();
                
                $_SESSION['success'] = "Your Business Permit Application has been successfully submitted!<br>
                                       <strong>Reference No:</strong> {$business_reference_no}<br>
                                       <strong>Application ID:</strong> #{$request_id}<br>
                                       <strong>Queue Ticket:</strong> {$queue_result['ticket_number']}<br>
                                       <strong>Estimated Time:</strong> {$queue_result['estimated_time']}<br>
                                       Please save your reference number and queue ticket for tracking.";
                
                // Store queue info in session
                $_SESSION['queue_ticket_number'] = $queue_result['ticket_number'];
                $_SESSION['queue_position'] = $queue_result['queue_position'];
                $_SESSION['service_name'] = $queue_result['service_name'];
                
            } else {
                $pdo->commit();
                $_SESSION['success'] = "Your Business Permit Application has been successfully submitted! Reference: {$business_reference_no}, Application ID: #{$request_id}. However, there was an issue generating your queue ticket.";
            }
            
            error_log("Business application submitted - ID: {$request_id}, Reference: {$business_reference_no}, Business: {$business_name}, Owner: {$full_name}");
            
        } else {
            throw new Exception('Failed to submit business application. Please try again.');
        }
        
    } else {
        // Handle regular certificate requests
        // Prepare SQL statement
        $sql = "INSERT INTO certificate_requests (
            full_name, address, mobile_number, civil_status, gender, 
            birth_date, birth_place, citizenship, years_of_residence, 
            certificate_type, purpose, vehicle_make_type, motor_no, 
            chassis_no, plate_no, vehicle_color, year_model, body_no, 
            operator_license, submitted_at, status
        ) VALUES (
            :full_name, :address, :mobile_number, :civil_status, :gender,
            :birth_date, :birth_place, :citizenship, :years_of_residence,
            :certificate_type, :purpose, :vehicle_make_type, :motor_no,
            :chassis_no, :plate_no, :vehicle_color, :year_model, :body_no,
            :operator_license, NOW(), 'pending'
        )";
        
        $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':mobile_number', $mobile_number);
    $stmt->bindParam(':civil_status', $civil_status);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':birth_date', $birth_date);
    $stmt->bindParam(':birth_place', $birth_place);
    $stmt->bindParam(':citizenship', $citizenship);
    $stmt->bindParam(':years_of_residence', $years_of_residence, PDO::PARAM_INT);
    $stmt->bindParam(':certificate_type', $certificate_type);
    $stmt->bindParam(':purpose', $purpose);
    $stmt->bindParam(':vehicle_make_type', $vehicle_make_type);
    $stmt->bindParam(':motor_no', $motor_no);
    $stmt->bindParam(':chassis_no', $chassis_no);
    $stmt->bindParam(':plate_no', $plate_no);
    $stmt->bindParam(':vehicle_color', $vehicle_color);
    $stmt->bindParam(':year_model', $year_model, PDO::PARAM_INT);
    $stmt->bindParam(':body_no', $body_no);
    $stmt->bindParam(':operator_license', $operator_license);
    
    // Execute the statement
    if ($stmt->execute()) {
        $request_id = $pdo->lastInsertId();
        
        // NEW: Generate queue ticket automatically using enhanced method
        $queueManager = new QueueManager($pdo);
        
        // Determine the specific form type for better queue categorization
        $form_type = strtolower(str_replace([' ', '.'], '_', $certificate_type));
        $queue_form_type = $form_type;
        
        // Map to more specific queue services
        $service_mapping = [
            'brgy_clearance' => 'brgy_clearance',
            'brgy_indigency' => 'brgy_indigency', 
            'tricycle_permit' => 'tricycle_permit',
            'proof_of_residency' => 'proof_residency',
            'business_application' => 'business_application'
        ];
        
        $final_form_type = $service_mapping[$form_type] ?? 'certificate_request';
        
        $queue_result = $queueManager->generateTicketForForm(
            $final_form_type,
            $full_name,
            $mobile_number,
            "Certificate Request: {$certificate_type}"
        );
        
        if ($queue_result['success']) {
            // Update certificate request with queue ticket info
            $update_stmt = $pdo->prepare("UPDATE certificate_requests SET queue_ticket_id = ?, queue_ticket_number = ? WHERE id = ?");
            $update_stmt->execute([$queue_result['ticket_id'], $queue_result['ticket_number'], $request_id]);
            
            // Commit transaction
            $pdo->commit();
            
            // Set success message with queue info
            $certificate_display = $certificate_type;
            if ($certificate_type === 'TRICYCLE PERMIT') {
                $certificate_display = 'Tricycle Permit';
            } elseif ($certificate_type === 'BRGY. CLEARANCE') {
                $certificate_display = 'Barangay Clearance';
            } elseif ($certificate_type === 'BRGY. INDIGENCY') {
                $certificate_display = 'Barangay Indigency Certificate';
            } elseif ($certificate_type === 'PROOF OF RESIDENCY') {
                $certificate_display = 'Proof of Residency Certificate';
            } elseif ($certificate_type === 'BUSINESS APPLICATION') {
                $certificate_display = 'Business Permit Application';
            }
            
            $_SESSION['success'] = "Your {$certificate_display} request has been successfully submitted!<br>
                                   <strong>Request ID:</strong> #{$request_id}<br>
                                   <strong>Queue Ticket:</strong> {$queue_result['ticket_number']}<br>
                                   <strong>Estimated Time:</strong> {$queue_result['estimated_time']}<br>
                                   Please save your queue ticket number for tracking your request.";
            
            // Store queue info in session for redirect
            $_SESSION['queue_ticket_number'] = $queue_result['ticket_number'];
            $_SESSION['queue_position'] = $queue_result['queue_position'];
            $_SESSION['service_name'] = $queue_result['service_name'];
            
        } else {
            // Commit certificate request even if queue fails
            $pdo->commit();
            
            $_SESSION['success'] = "Your {$certificate_display} request has been successfully submitted! Request ID: #{$request_id}. However, there was an issue generating your queue ticket. Please visit the office or call for assistance.";
        }
        
        // Log successful submission
        error_log("Certificate request submitted - ID: {$request_id}, Type: {$certificate_type}, Name: {$full_name}, Queue: " . ($queue_result['success'] ? $queue_result['ticket_number'] : 'Failed'));
        
    } else {
        throw new Exception('Failed to submit certificate request. Please try again.');
    }
    
    } // End of else block for regular certificate processing
    
} catch (Exception $e) {
    // Rollback transaction if it was started
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    $_SESSION['error'] = $e->getMessage();
    error_log("Certificate request error: " . $e->getMessage());
    
} catch (PDOException $e) {
    // Rollback transaction if it was started
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    $_SESSION['error'] = 'Database error occurred. Please try again later.';
    error_log("Certificate request database error: " . $e->getMessage());
}

// Redirect back to form
header('Location: certificate-request.php');
exit;

// File upload handling function
function handleFileUpload($file, $upload_dir, $prefix) {
    // Validate file size (5MB limit)
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        throw new Exception('File size must be less than 5MB');
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    $file_type = $file['type'];
    if (!in_array($file_type, $allowed_types)) {
        throw new Exception('Only PNG, JPG, and PDF files are allowed');
    }
    
    // Get file extension
    $file_info = pathinfo($file['name']);
    $extension = strtolower($file_info['extension']);
    
    // Generate unique filename
    $filename = $prefix . '_' . time() . '_' . uniqid() . '.' . $extension;
    $file_path = $upload_dir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        throw new Exception('Failed to upload file');
    }
    
    return $filename; // Return just the filename, not the full path
}
?>
