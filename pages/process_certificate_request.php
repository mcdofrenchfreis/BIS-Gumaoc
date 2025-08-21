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
    $allowed_types = ['BRGY. CLEARANCE', 'BRGY. INDIGENCY', 'TRICYCLE PERMIT', 'PROOF OF RESIDENCY'];
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
        
        // NEW: Generate queue ticket automatically
        $queueManager = new QueueManager($pdo);
        
        // Map certificate types to queue services (using the IDs from our inserted data)
        $service_mapping = [
            'BRGY. CLEARANCE' => 1,      // Barangay Clearance
            'BRGY. INDIGENCY' => 2,      // Barangay Indigency  
            'TRICYCLE PERMIT' => 3,      // Tricycle Permit
            'PROOF OF RESIDENCY' => 4    // Proof of Residency
        ];
        
        $service_id = $service_mapping[$certificate_type] ?? 5; // Default to General Services
        
        // Generate queue ticket
        $queue_result = $queueManager->generateTicket(
            $service_id,
            $full_name,
            $mobile_number,
            null, // user_id (for guest users)
            "Certificate Request: {$certificate_type}",
            'normal' // priority level
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
?>
