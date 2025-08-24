<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Extract basic form data
        $head_of_family = trim($_POST['headOfFamily'] ?? '');
        $cellphone = trim($_POST['cellphone'] ?? '');
        $house_number = trim($_POST['houseNumber'] ?? '');
        $interviewer = trim($_POST['interviewer'] ?? '');
        $interviewer_title = trim($_POST['interviewerTitle'] ?? '');
        
        // Extract household economics data
        $land_ownership = $_POST['landOwnership'] ?? '';
        $land_ownership_other = $_POST['landOwnershipOther'] ?? '';
        $house_ownership = $_POST['houseOwnership'] ?? '';
        $house_ownership_other = $_POST['houseOwnershipOther'] ?? '';
        $farmland = $_POST['farmland'] ?? '';
        $cooking_energy = $_POST['cookingEnergy'] ?? '';
        $cooking_energy_other = $_POST['cookingEnergyOther'] ?? '';
        $toilet_type = $_POST['toiletType'] ?? '';
        $toilet_type_other = $_POST['toiletTypeOther'] ?? '';
        $electricity_source = $_POST['electricitySource'] ?? '';
        $electricity_source_other = $_POST['electricitySourceOther'] ?? '';
        $water_source = $_POST['waterSource'] ?? '';
        $water_source_other = $_POST['waterSourceOther'] ?? '';
        $waste_disposal = $_POST['wasteDisposal'] ?? '';
        $waste_disposal_other = $_POST['wasteDisposalOther'] ?? '';
        
        // Process checkbox arrays
        $appliances = isset($_POST['appliances']) ? implode(',', $_POST['appliances']) : '';
        $transportation = isset($_POST['transportation']) ? implode(',', $_POST['transportation']) : '';
        $transportation_other = $_POST['transportationOther'] ?? '';
        $business = isset($_POST['business']) ? implode(',', $_POST['business']) : '';
        $business_other = $_POST['businessOther'] ?? '';
        $contraceptive = isset($_POST['contraceptive']) ? implode(',', $_POST['contraceptive']) : '';
        
        // For database compatibility, use head_of_family as the name fields
        $name_parts = explode(' ', $head_of_family);
        $first_name = $name_parts[0] ?? '';
        $last_name = end($name_parts) ?? '';
        $middle_name = count($name_parts) > 2 ? $name_parts[1] : '';
        
        // Set default values for required fields
        $birth_date = date('Y-m-d');
        $age = 25;
        $civil_status = 'Unknown';
        $gender = 'Not Specified';
        $contact_number = $cellphone;
        $pangkabuhayan = $land_ownership ?: 'Not Specified';
        
        // Validation
        $errors = [];
        if (empty($head_of_family)) $errors[] = "Head of family name is required";
        if (empty($house_number)) $errors[] = "House number is required";
        if (empty($interviewer)) $errors[] = "Interviewer name is required";
        
        if (empty($errors)) {
            // Start transaction
            $pdo->beginTransaction();
            
            // Insert main registration
            $sql = "INSERT INTO resident_registrations (
                first_name, middle_name, last_name, birth_date, age, 
                civil_status, gender, contact_number, house_number, pangkabuhayan,
                land_ownership, land_ownership_other, house_ownership, house_ownership_other,
                farmland, cooking_energy, cooking_energy_other, toilet_type, toilet_type_other,
                electricity_source, electricity_source_other, water_source, water_source_other,
                waste_disposal, waste_disposal_other, appliances, transportation, transportation_other,
                business, business_other, contraceptive, interviewer, interviewer_title
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $first_name, $middle_name, $last_name, $birth_date, $age,
                $civil_status, $gender, $contact_number, $house_number, $pangkabuhayan,
                $land_ownership, $land_ownership_other, $house_ownership, $house_ownership_other,
                $farmland, $cooking_energy, $cooking_energy_other, $toilet_type, $toilet_type_other,
                $electricity_source, $electricity_source_other, $water_source, $water_source_other,
                $waste_disposal, $waste_disposal_other, $appliances, $transportation, $transportation_other,
                $business, $business_other, $contraceptive, $interviewer, $interviewer_title
            ]);
            
            if (!$result) {
                throw new Exception("Failed to insert main registration");
            }
            
            $registration_id = $pdo->lastInsertId();
            
            // Insert family members
            if (isset($_POST['familyName']) && is_array($_POST['familyName'])) {
                $family_stmt = $pdo->prepare("INSERT INTO family_members (registration_id, full_name, relationship, age, gender, civil_status, email, occupation) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                
                foreach ($_POST['familyName'] as $index => $name) {
                    if (!empty(trim($name))) {
                        $relationship = $_POST['familyRelation'][$index] ?? '';
                        $age = !empty($_POST['familyAge'][$index]) ? (int)$_POST['familyAge'][$index] : null;
                        $gender = $_POST['familyGender'][$index] ?? '';
                        $civil_status = $_POST['familyCivilStatus'][$index] ?? '';
                        $email = $_POST['familyEmail'][$index] ?? '';
                        $occupation = $_POST['familyOccupation'][$index] ?? '';
                        
                        $family_stmt->execute([$registration_id, trim($name), $relationship, $age, $gender, $civil_status, $email, $occupation]);
                    }
                }
            }
            
            // Insert family members with disabilities
            if (isset($_POST['disabilityName']) && is_array($_POST['disabilityName'])) {
                $disability_stmt = $pdo->prepare("INSERT INTO family_disabilities (registration_id, name, disability_type) VALUES (?, ?, ?)");
                
                foreach ($_POST['disabilityName'] as $index => $name) {
                    if (!empty(trim($name)) && !empty(trim($_POST['disabilityType'][$index] ?? ''))) {
                        $disability_stmt->execute([$registration_id, trim($name), trim($_POST['disabilityType'][$index])]);
                    }
                }
            }
            
            // Insert family members in organizations
            if (isset($_POST['organizationName']) && is_array($_POST['organizationName'])) {
                $org_stmt = $pdo->prepare("INSERT INTO family_organizations (registration_id, name, organization_type) VALUES (?, ?, ?)");
                
                foreach ($_POST['organizationName'] as $index => $name) {
                    if (!empty(trim($name)) && !empty(trim($_POST['organizationType'][$index] ?? ''))) {
                        $org_stmt->execute([$registration_id, trim($name), trim($_POST['organizationType'][$index])]);
                    }
                }
            }
            
            // Commit transaction
            $pdo->commit();
            
            error_log("Complete Census Registration Inserted Successfully - ID: " . $registration_id);
            $_SESSION['success'] = "Census registration submitted successfully! Reference ID: " . $registration_id;
            header('Location: forms.php?success=1');
            exit;
            
        } else {
            error_log("Census Registration Validation Errors: " . implode(', ', $errors));
            $_SESSION['error'] = implode(', ', $errors);
            header('Location: resident-registration.php?error=1');
            exit;
        }
    } catch (Exception $e) {
        $pdo->rollBack();
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
