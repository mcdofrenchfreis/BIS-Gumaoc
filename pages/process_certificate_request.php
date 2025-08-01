<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Extract form data from certificate-request.php
        $certificate_type = $_POST['certificateType'] ?? '';
        $first_name = trim($_POST['firstName'] ?? '');
        $middle_name = trim($_POST['middleName'] ?? '');
        $last_name = trim($_POST['lastName'] ?? '');
        $address1 = trim($_POST['address1'] ?? '');
        $address2 = trim($_POST['address2'] ?? '');
        $mobile_number = trim($_POST['mobileNumber'] ?? '');
        $civil_status = $_POST['civilStatus'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $birth_date = $_POST['birthdate'] ?? '';
        $birth_place = trim($_POST['birthplace'] ?? '');
        $citizenship = trim($_POST['citizenship'] ?? '');
        $years_of_residence = $_POST['yearsOfResidence'] ?? null;
        $purpose = trim($_POST['purpose'] ?? '');
        
        // Combine names and address
        $full_name = trim($first_name . ' ' . $middle_name . ' ' . $last_name);
        $full_address = trim($address1 . ' ' . $address2);
        
        // Debug: Log what we received
        error_log("Certificate Request Submission - Type: $certificate_type, Name: $full_name");
        
        // Validation
        $errors = [];
        if (empty($certificate_type)) $errors[] = "Certificate type is required";
        if (empty($first_name)) $errors[] = "First name is required";
        if (empty($last_name)) $errors[] = "Last name is required";
        if (empty($civil_status)) $errors[] = "Civil status is required";
        if (empty($gender)) $errors[] = "Gender is required";
        if (empty($birth_date)) $errors[] = "Birth date is required";
        if (empty($birth_place)) $errors[] = "Birth place is required";
        if (empty($purpose)) $errors[] = "Purpose is required";
        if (empty($address1)) $errors[] = "Address is required";
        
        if (empty($errors)) {
            // Test database connection first
            $pdo->query("SELECT 1");
            
            // Insert into database using the updated table structure
            $sql = "INSERT INTO certificate_requests (
                full_name, address, mobile_number, civil_status, gender, 
                birth_date, birth_place, citizenship, years_of_residence, 
                certificate_type, purpose
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $full_name, $full_address, $mobile_number, $civil_status, $gender,
                $birth_date, $birth_place, $citizenship, $years_of_residence,
                $certificate_type, $purpose
            ]);
            
            if ($result) {
                error_log("Certificate Request Inserted Successfully - ID: " . $pdo->lastInsertId());
                $_SESSION['success'] = "Your certificate request has been successfully submitted! Your reference ID is: " . $pdo->lastInsertId();
                header('Location: certificate-request.php?success=1');
                exit;
            } else {
                throw new Exception("Database insertion failed");
            }
        } else {
            error_log("Certificate Request Validation Errors: " . implode(', ', $errors));
            $_SESSION['error'] = implode(', ', $errors);
            header('Location: certificate-request.php?error=1');
            exit;
        }
    } catch (Exception $e) {
        error_log("Certificate Request Error: " . $e->getMessage());
        $_SESSION['error'] = "Error submitting request: " . $e->getMessage();
        header('Location: certificate-request.php?error=1');
        exit;
    }
} else {
    error_log("Certificate Request - Not POST request: " . $_SERVER['REQUEST_METHOD']);
    header('Location: certificate-request.php');
    exit;
}
?>
