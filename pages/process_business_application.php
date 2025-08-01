<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize and validate input data from business-application.php
        $business_name = trim($_POST['business_name'] ?? '');
        $business_type = 'General Business'; // Default since form doesn't have this field
        $business_address = trim($_POST['business_address_1'] ?? '') . ' ' . trim($_POST['business_address_2'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $middle_name = trim($_POST['middle_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $owner_name = trim($first_name . ' ' . $middle_name . ' ' . $last_name);
        $owner_address = trim($_POST['house_address'] ?? '');
        $contact_number = '09000000000'; // Default since form doesn't have this field
        $years_operation = 1; // Default since form doesn't have this field
        $investment_capital = 0.00; // Default since form doesn't have this field
        
        // Debug: Log what we received
        error_log("Business Application Submission - Business: $business_name, Owner: $owner_name");
        
        // Basic validation
        $errors = [];
        if (empty($first_name)) $errors[] = "First name is required";
        if (empty($last_name)) $errors[] = "Last name is required";
        if (empty($business_name)) $errors[] = "Business name is required";
        if (empty($_POST['business_address_1'])) $errors[] = "Business address is required";
        if (empty($owner_address)) $errors[] = "House address is required";
        
        if (empty($errors)) {
            // Test database connection first
            $pdo->query("SELECT 1");
            
            // Insert into database using the correct table structure
            $sql = "INSERT INTO business_applications (
                business_name, business_type, business_address, owner_name, owner_address, 
                contact_number, years_operation, investment_capital
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $business_name, $business_type, $business_address, $owner_name, $owner_address,
                $contact_number, $years_operation, $investment_capital
            ]);
            
            if ($result) {
                error_log("Business Application Inserted Successfully - ID: " . $pdo->lastInsertId());
                $_SESSION['success'] = "Business application submitted successfully! Reference ID: " . $pdo->lastInsertId();
                header('Location: business-application.php?success=1');
                exit;
            } else {
                throw new Exception("Database insertion failed");
            }
        } else {
            error_log("Business Application Validation Errors: " . implode(', ', $errors));
            $_SESSION['error'] = implode(', ', $errors);
            header('Location: business-application.php?error=1');
            exit;
        }
    } catch (Exception $e) {
        error_log("Business Application Error: " . $e->getMessage());
        $_SESSION['error'] = "Error submitting application: " . $e->getMessage();
        header('Location: business-application.php?error=1');
        exit;
    }
} else {
    error_log("Business Application - Not POST request: " . $_SERVER['REQUEST_METHOD']);
    header('Location: business-application.php');
    exit;
}
?>
