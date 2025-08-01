<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Extract form data from resident-registration.php (Filipino census form)
        $head_of_family = trim($_POST['headOfFamily'] ?? '');
        $cellphone = trim($_POST['cellphone'] ?? '');
        $house_number = trim($_POST['houseNumber'] ?? '');
        $interviewer = trim($_POST['interviewer'] ?? '');
        $interviewer_title = trim($_POST['interviewerTitle'] ?? '');
        
        // For database compatibility, we'll use head_of_family as the name fields
        $name_parts = explode(' ', $head_of_family);
        $first_name = $name_parts[0] ?? '';
        $last_name = end($name_parts) ?? '';
        $middle_name = count($name_parts) > 2 ? $name_parts[1] : '';
        
        // Set default values for required fields
        $birth_date = date('Y-m-d'); // Default to today if not provided
        $age = 25; // Default age
        $civil_status = 'Unknown';
        $gender = 'Not Specified';
        $contact_number = $cellphone;
        $pangkabuhayan = 'Not Specified';
        
        // Debug: Log what we received
        error_log("Census Registration Submission - Head of Family: $head_of_family, House: $house_number");
        
        // Validation
        $errors = [];
        if (empty($head_of_family)) $errors[] = "Head of family name is required";
        if (empty($house_number)) $errors[] = "House number is required";
        if (empty($interviewer)) $errors[] = "Interviewer name is required";
        
        if (empty($errors)) {
            // Test database connection first
            $pdo->query("SELECT 1");
            
            // Insert into database
            $sql = "INSERT INTO resident_registrations (
                first_name, middle_name, last_name, birth_date, age, 
                civil_status, gender, contact_number, house_number, pangkabuhayan
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $first_name, $middle_name, $last_name, $birth_date, $age,
                $civil_status, $gender, $contact_number, $house_number, $pangkabuhayan
            ]);
            
            if ($result) {
                error_log("Census Registration Inserted Successfully - ID: " . $pdo->lastInsertId());
                $_SESSION['success'] = "Census registration submitted successfully! Reference ID: " . $pdo->lastInsertId();
                header('Location: forms.php?success=1');
                exit;
            } else {
                throw new Exception("Database insertion failed");
            }
        } else {
            error_log("Census Registration Validation Errors: " . implode(', ', $errors));
            $_SESSION['error'] = implode(', ', $errors);
            header('Location: resident-registration.php?error=1');
            exit;
        }
    } catch (Exception $e) {
        error_log("Census Registration Error: " . $e->getMessage());
        $_SESSION['error'] = "Error submitting registration: " . $e->getMessage();
        header('Location: resident-registration.php?error=1');
        exit;
    }
} else {
    error_log("Census Registration - Not POST request: " . $_SERVER['REQUEST_METHOD']);
    header('Location: resident-registration.php');
    exit;
}
?>
