<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

// Enable CORS if needed
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Add debugging
error_log("Validation request received: " . print_r($input, true));

$type = $input['type'] ?? '';

// For email validation, we use 'value', for name validation we use separate fields
if (empty($type)) {
    error_log("Validation error: type is empty");
    echo json_encode(['valid' => false, 'message' => 'Invalid input - type required']);
    exit;
}

// Additional validation based on type
if ($type === 'email') {
    $value = trim($input['value'] ?? '');
    if (empty($value)) {
        echo json_encode(['valid' => false, 'message' => 'Email value required']);
        exit;
    }
} elseif ($type === 'name') {
    $firstName = trim($input['firstName'] ?? '');
    $lastName = trim($input['lastName'] ?? '');
    if (empty($firstName) || empty($lastName)) {
        echo json_encode(['valid' => false, 'message' => 'First name and last name are required']);
        exit;
    }
} else {
    echo json_encode(['valid' => false, 'message' => 'Invalid validation type']);
    exit;
}

try {
    switch ($type) {
        case 'email':
            $value = trim($input['value'] ?? ''); // Re-extract for this case
            // Check if email exists in residents table
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM residents WHERE email = ?");
            $stmt->execute([$value]);
            $exists = $stmt->fetchColumn() > 0;
            
            if ($exists) {
                echo json_encode(['valid' => false, 'message' => 'Email already exists']);
            } else {
                echo json_encode(['valid' => true, 'message' => 'Email is available']);
            }
            break;
            
        case 'name':
            // Check name combination - we'll check against full name combinations
            $firstName = $input['firstName'] ?? '';
            $middleName = $input['middleName'] ?? '';
            $lastName = $input['lastName'] ?? '';
            
            error_log("Name validation - firstName: '$firstName', middleName: '$middleName', lastName: '$lastName'");
            
            // Build full name for comparison
            $fullName = trim($firstName . ' ' . $middleName . ' ' . $lastName);
            $fullName = preg_replace('/\s+/', ' ', $fullName); // Normalize spaces
            
            error_log("Full name for validation: '$fullName'");
            
            if (empty($fullName)) {
                error_log("Name validation failed: full name is empty");
                echo json_encode(['valid' => false, 'message' => 'Name cannot be empty']);
                break;
            }
            
            // Check if this exact name combination exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM residents WHERE TRIM(CONCAT_WS(' ', first_name, middle_name, last_name)) = ?");
            $stmt->execute([$fullName]);
            $exists = $stmt->fetchColumn() > 0;
            
            error_log("Name exists in database: " . ($exists ? 'true' : 'false'));
            
            if ($exists) {
                echo json_encode(['valid' => false, 'message' => 'This name combination already exists']);
            } else {
                echo json_encode(['valid' => true, 'message' => 'Name is available']);
            }
            break;
            
        default:
            echo json_encode(['valid' => false, 'message' => 'Invalid validation type']);
    }
    
} catch (Exception $e) {
    error_log("Validation error: " . $e->getMessage());
    echo json_encode(['valid' => false, 'message' => 'Validation error occurred']);
}
?>