<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/db_connect.php';
require_once '../includes/email_service.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Extract basic form data - Updated to handle separate name fields
        $first_name = trim($_POST['firstName'] ?? '');
        $middle_name = trim($_POST['middleName'] ?? '');
        $last_name = trim($_POST['lastName'] ?? '');
        
        // Backward compatibility - if separate name fields are empty, try legacy headOfFamily
        if (empty($first_name) && empty($last_name) && !empty($_POST['headOfFamily'])) {
            $head_of_family_legacy = trim($_POST['headOfFamily']);
            $name_parts = explode(' ', $head_of_family_legacy);
            $first_name = $name_parts[0] ?? '';
            $last_name = end($name_parts) ?? '';
            $middle_name = count($name_parts) > 2 ? $name_parts[1] : '';
        }
        
        // Build full name for compatibility
        $head_of_family = trim($first_name . ' ' . $middle_name . ' ' . $last_name);
        $head_of_family = preg_replace('/\s+/', ' ', $head_of_family); // Normalize spaces
        
        $cellphone = trim($_POST['cellphone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $birthday = trim($_POST['birthday'] ?? '');
        $birth_place = trim($_POST['birthPlace'] ?? '');
        $house_number = trim($_POST['houseNumber'] ?? '');
        $street_address = trim($_POST['streetAddress'] ?? '');
        $interviewer = trim($_POST['interviewer'] ?? '');
        $interviewer_title = trim($_POST['interviewerTitle'] ?? '');
        
        // New fields for resident disability and organization (Tab 1)
        $resident_disability = trim($_POST['residentDisability'] ?? '');
        $resident_organization = trim($_POST['residentOrganization'] ?? '');
        
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
        
        // Name fields are already extracted above
        // No need to split head_of_family since we have separate fields
        
        // Set default values for required fields
        $contact_number = $cellphone;
        $pangkabuhayan = $land_ownership ?: 'Not Specified';
        
        // Calculate age from birthday if provided
        $age = 25; // Default age
        if (!empty($birthday)) {
            $birth_date = $birthday;
            $birth_year = date('Y', strtotime($birthday));
            $current_year = date('Y');
            $age = $current_year - $birth_year;
            // Adjust for birthday not yet occurred this year
            if (date('md') < date('md', strtotime($birthday))) {
                $age--;
            }
        } else {
            // If no birthday provided, estimate from age
            $estimated_birth_year = date('Y') - $age;
            $birth_date = "$estimated_birth_year-01-01";
        }
        
        $civil_status = trim($_POST['civilStatus'] ?? 'Unknown');
        $gender = trim($_POST['gender'] ?? 'Not Specified');
        
        // Validation - Updated for separate name fields
        $errors = [];
        if (empty($first_name)) $errors[] = "First name is required";
        if (empty($last_name)) $errors[] = "Last name is required";
        if (empty($birthday)) $errors[] = "Date of birth is required";
        if (empty($birth_place)) $errors[] = "Place of birth is required";
        if (empty($gender) || $gender === 'Not Specified') $errors[] = "Gender is required";
        if (empty($civil_status) || $civil_status === 'Unknown') $errors[] = "Civil status is required";
        if (empty($house_number)) $errors[] = "House number is required";
        if (empty($street_address)) $errors[] = "Street address is required";
        
        if (empty($errors)) {
            // Start transaction
            $pdo->beginTransaction();
            
            // Generate RFID and temporary password for the new user
            $generated_rfid = EmailService::generateUniqueRFID($pdo);
            $temp_password = EmailService::generateTempPassword();
            $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
            
            // Create a full address from street address and house number
            $full_address = "House $house_number, $street_address, Barangay Gumaoc East, San Jose del Monte, Bulacan, Philippines";
            
            // Estimate birthdate from age (current year - age)
            $estimated_birth_year = date('Y') - $age;
            $estimated_birthdate = "$estimated_birth_year-01-01";
            
            // Insert into residents table (main user table) with pending status
            $residents_sql = "INSERT INTO residents (
                first_name, middle_name, last_name, email, phone, password,
                address, house_number, barangay, sitio, interviewer, interviewer_title,
                birthdate, birth_place, gender, civil_status, rfid_code, rfid,
                status, profile_complete, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 0, NOW())";
            
            $residents_stmt = $pdo->prepare($residents_sql);
            $residents_result = $residents_stmt->execute([
                $first_name, $middle_name, $last_name, $email, $contact_number, $hashed_password,
                $full_address, $house_number, 'Gumaoc East', 'BLOCK', $interviewer, $interviewer_title,
                $birth_date, $birth_place, $gender, $civil_status, $generated_rfid, $generated_rfid
            ]);
            
            if (!$residents_result) {
                throw new Exception("Failed to create resident account");
            }
            
            $resident_id = $pdo->lastInsertId();
            
            // Assign the RFID code to this resident in the scanned codes table
            EmailService::assignRFIDCode($pdo, $generated_rfid, $resident_id, $email);
            
            // Insert main registration (for census tracking)
            $sql = "INSERT INTO resident_registrations (
                first_name, middle_name, last_name, birth_date, birth_place, age, 
                civil_status, gender, contact_number, email, house_number, address, pangkabuhayan,
                land_ownership, land_ownership_other, house_ownership, house_ownership_other,
                farmland, cooking_energy, cooking_energy_other, toilet_type, toilet_type_other,
                electricity_source, electricity_source_other, water_source, water_source_other,
                waste_disposal, waste_disposal_other, appliances, transportation, transportation_other,
                business, business_other, contraceptive, interviewer, interviewer_title,
                resident_disability, resident_organization
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $first_name, $middle_name, $last_name, $birth_date, $birth_place, $age,
                $civil_status, $gender, $contact_number, $email, $house_number, $street_address, $pangkabuhayan,
                $land_ownership, $land_ownership_other, $house_ownership, $house_ownership_other,
                $farmland, $cooking_energy, $cooking_energy_other, $toilet_type, $toilet_type_other,
                $electricity_source, $electricity_source_other, $water_source, $water_source_other,
                $waste_disposal, $waste_disposal_other, $appliances, $transportation, $transportation_other,
                $business, $business_other, $contraceptive, $interviewer, $interviewer_title,
                $resident_disability, $resident_organization
            ]);
            
            if (!$result) {
                throw new Exception("Failed to insert census registration");
            }
            
            $registration_id = $pdo->lastInsertId();
            
            // Insert family members and send email notifications
            $family_email_count = 0;
            $family_email_success = 0;
            $family_users_created = 0;
            if (isset($_POST['familyName']) && is_array($_POST['familyName'])) {
                $family_stmt = $pdo->prepare("INSERT INTO family_members (registration_id, full_name, relationship, birth_date, age, gender, civil_status, email, occupation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                // Get registrant's full name for email notifications
                $registrant_full_name = trim($first_name . ' ' . $middle_name . ' ' . $last_name);
                
                foreach ($_POST['familyName'] as $index => $name) {
                    if (!empty(trim($name))) {
                        $relationship = $_POST['familyRelation'][$index] ?? '';
                        $family_birth_date = $_POST['familyBirthDate'][$index] ?? null;
                        $family_age = !empty($_POST['familyAge'][$index]) ? (int)$_POST['familyAge'][$index] : null;
                        $family_gender = $_POST['familyGender'][$index] ?? '';
                        // Gender values are already in Filipino (Lalaki/Babae) from the form
                        $family_civil_status = $_POST['familyCivilStatus'][$index] ?? '';
                        $family_email = trim($_POST['familyEmail'][$index] ?? '');
                        $occupation = $_POST['familyOccupation'][$index] ?? '';
                        // Get deceased status
                        $is_deceased = isset($_POST['isDeceased'][$index]) ? 1 : 0;
                        
                        // Convert empty birth date to null
                        if (empty($family_birth_date)) {
                            $family_birth_date = null;
                        }
                        
                        // Insert family member into database
                        $family_stmt = $pdo->prepare("INSERT INTO family_members (registration_id, full_name, relationship, birth_date, age, gender, civil_status, email, occupation, is_deceased) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $family_stmt->execute([$registration_id, trim($name), $relationship, $family_birth_date, $family_age, $family_gender, $family_civil_status, $family_email, $occupation, $is_deceased]);
                        
                        // Create family member as user in residents table if they have an email
                        if (!empty($family_email) && filter_var($family_email, FILTER_VALIDATE_EMAIL)) {
                            try {
                                // Check if family member already exists as a user
                                $check_user_stmt = $pdo->prepare("SELECT COUNT(*) FROM residents WHERE email = ?");
                                $check_user_stmt->execute([$family_email]);
                                $user_exists = $check_user_stmt->fetchColumn() > 0;
                                
                                if (!$user_exists) {
                                    // Parse family member name into components
                                    $name_parts = explode(' ', trim($name));
                                    $family_first_name = $name_parts[0] ?? '';
                                    $family_last_name = end($name_parts) ?? '';
                                    $family_middle_name = count($name_parts) > 2 ? $name_parts[1] : '';
                                    
                                    // Generate RFID and password for family member
                                    $family_rfid = EmailService::generateUniqueRFID($pdo);
                                    $family_temp_password = EmailService::generateTempPassword();
                                    $family_hashed_password = password_hash($family_temp_password, PASSWORD_DEFAULT);
                                    
                                    // Use provided birth date or estimate from age
                                    if (!empty($family_birth_date)) {
                                        $family_birth_date_for_user = $family_birth_date;
                                    } else {
                                        // Estimate birth date from age
                                        $family_birth_year = date('Y') - ($family_age ?: 25);
                                        $family_birth_date_for_user = "$family_birth_year-01-01";
                                    }
                                    
                                    // Set default values for incomplete profile
                                    // Convert Filipino gender values to English for residents table (which uses English enum)
                                    if ($family_gender === 'Lalaki') {
                                        $family_gender_standard = 'Male';
                                    } elseif ($family_gender === 'Babae') {
                                        $family_gender_standard = 'Female';
                                    } else {
                                        $family_gender_standard = 'Male'; // Default
                                    }
                                    $family_civil_status_standard = in_array($family_civil_status, ['Single', 'Married', 'Widowed', 'Separated', 'Divorced']) ? $family_civil_status : 'Single';
                                    
                                    // Insert family member as resident user with pending status and incomplete profile
                                    $family_residents_sql = "INSERT INTO residents (
                                        first_name, middle_name, last_name, email, phone, password,
                                        address, house_number, barangay, sitio, interviewer, interviewer_title,
                                        birthdate, birth_place, gender, civil_status, rfid_code, rfid,
                                        status, profile_complete, created_by, relationship_to_head, created_at
                                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 0, ?, ?, NOW())";
                                    
                                    $family_residents_stmt = $pdo->prepare($family_residents_sql);
                                    $family_residents_result = $family_residents_stmt->execute([
                                        $family_first_name, $family_middle_name, $family_last_name, $family_email, '', $family_hashed_password,
                                        $full_address, $house_number, 'Gumaoc East', 'BLOCK', $interviewer, $interviewer_title,
                                        $family_birth_date_for_user, 'Unknown', $family_gender_standard, $family_civil_status_standard, $family_rfid, $family_rfid,
                                        $resident_id, $relationship
                                    ]);
                                    
                                    if ($family_residents_result) {
                                        $family_users_created++;
                                        $family_resident_id = $pdo->lastInsertId();
                                        
                                        // Assign the RFID code to this family member
                                        EmailService::assignRFIDCode($pdo, $family_rfid, $family_resident_id, $family_email);
                                        
                                        // Send confirmation email to family member instead of activation email
                                        try {
                                            $emailService = new EmailService();
                                            $family_confirmation_sent = $emailService->sendRegistrationConfirmationEmail(
                                                $family_email,
                                                trim($name)
                                            );
                                            
                                            if ($family_confirmation_sent) {
                                                error_log("Family member confirmation email sent to: $family_email (" . trim($name) . ")");
                                            } else {
                                                error_log("Failed to send family member confirmation email to: $family_email");
                                            }
                                        } catch (Exception $e) {
                                            error_log("Family member confirmation email error for $family_email: " . $e->getMessage());
                                        }
                                    }
                                }
                            } catch (Exception $e) {
                                error_log("Error creating family member user account for $family_email: " . $e->getMessage());
                            }
                        }
                        
                        // Send family member notification email
                        if (!empty($family_email) && filter_var($family_email, FILTER_VALIDATE_EMAIL)) {
                            $family_email_count++;
                            try {
                                $emailService = new EmailService();
                                $email_sent = $emailService->sendFamilyMemberNotification(
                                    $family_email,
                                    trim($name),
                                    $registrant_full_name,
                                    $relationship
                                );
                                
                                if ($email_sent) {
                                    $family_email_success++;
                                    error_log("Family member notification sent to: $family_email (" . trim($name) . ")");
                                } else {
                                    error_log("Failed to send family member notification to: $family_email");
                                }
                            } catch (Exception $e) {
                                error_log("Family member email notification error for $family_email: " . $e->getMessage());
                            }
                        }
                    }
                }
            }
            
            // Insert family members with disabilities (from the merged tab data)
            if (isset($_POST['familyName']) && is_array($_POST['familyName']) && isset($_POST['disabilityType']) && is_array($_POST['disabilityType'])) {
                $disability_stmt = $pdo->prepare("INSERT INTO family_disabilities (registration_id, name, disability_type) VALUES (?, ?, ?)");
                
                foreach ($_POST['familyName'] as $index => $name) {
                    if (!empty(trim($name)) && !empty(trim($_POST['disabilityType'][$index] ?? ''))) {
                        $disability_stmt->execute([$registration_id, trim($name), trim($_POST['disabilityType'][$index])]);
                    }
                }
            }
            
            // Insert family members in organizations (from the merged tab data)
            if (isset($_POST['familyName']) && is_array($_POST['familyName']) && isset($_POST['organizationType']) && is_array($_POST['organizationType'])) {
                $org_stmt = $pdo->prepare("INSERT INTO family_organizations (registration_id, name, organization_type) VALUES (?, ?, ?)");
                
                foreach ($_POST['familyName'] as $index => $name) {
                    if (!empty(trim($name)) && !empty(trim($_POST['organizationType'][$index] ?? ''))) {
                        $org_stmt->execute([$registration_id, trim($name), trim($_POST['organizationType'][$index])]);
                    }
                }
            }
            
            // Commented out the old disability and organization insertion code since it's now merged
            /*
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
            */
            
            // Commit transaction
            $pdo->commit();
            
            // Add registration to queue system instead of sending RFID credentials
            $queue_added = false;
            $queue_ticket_number = null;
            
            try {
                // Include the QueueManager
                require_once '../includes/QueueManager.php';
                $queueManager = new QueueManager($pdo);
                
                $customer_name = trim($first_name . ' ' . $middle_name . ' ' . $last_name);
                
                // Generate queue ticket using enhanced method
                $queue_result = $queueManager->generateTicketForForm(
                    'resident_registration',
                    $customer_name,
                    $contact_number,
                    'Resident Census Registration Processing'
                );
                
                if ($queue_result['success']) {
                    $queue_added = true;
                    $queue_ticket_number = $queue_result['ticket_number'];
                    error_log("Registration added to queue successfully. Ticket: " . $queue_ticket_number);
                } else {
                    error_log("Failed to add registration to queue: " . ($queue_result['message'] ?? 'Unknown error'));
                }
            } catch (Exception $e) {
                error_log("Queue system error: " . $e->getMessage());
            }
            
            // Send confirmation email instead of RFID activation email
            $email_sent = false;
            if (!empty($email)) {
                try {
                    $emailService = new EmailService();
                    $full_name = trim($first_name . ' ' . $middle_name . ' ' . $last_name);
                    $email_sent = $emailService->sendRegistrationConfirmationEmail(
                        $email,
                        $full_name
                    );
                } catch (Exception $e) {
                    error_log("Email sending failed: " . $e->getMessage());
                }
            }
            
            error_log("Complete Registration Created - Resident ID: $resident_id, Registration ID: $registration_id, Queue Added: " . ($queue_added ? 'Yes' : 'No') . ", Email Sent: " . ($email_sent ? 'Yes' : 'No') . ", Family Notifications: $family_email_success/$family_email_count, Family Users Created: $family_users_created");
            
            // Build success message with enhanced clarity and HTML formatting
            $success_message = '<div class="success-content">';
            $success_message .= '<div class="success-header">🎉 <strong>Registration Successfully Completed!</strong></div>';
            
            // Add registrant information section
            $full_name = trim($first_name . ' ' . $middle_name . ' ' . $last_name);
            $success_message .= '<div class="success-section">';
            $success_message .= '<div class="section-title">📝 Registration Details</div>';
            $success_message .= '<div class="section-content">';
            $success_message .= '<div class="info-row"><strong>Registrant:</strong> ' . htmlspecialchars($full_name) . '</div>';
            $success_message .= '<div class="info-row"><strong>House Number:</strong> ' . htmlspecialchars($house_number) . '</div>';
            $success_message .= '</div></div>';
            
            // Queue information section
            if ($queue_added && isset($queue_result['success']) && $queue_result['success']) {
                $success_message .= '<div class="success-section highlight-section">';
                $success_message .= '<div class="section-title">🎫 Queue Information</div>';
                $success_message .= '<div class="section-content">';
                $success_message .= '<div class="info-row"><strong>Ticket Number:</strong> <span class="highlight-text">' . htmlspecialchars($queue_result['ticket_number']) . '</span></div>';
                $success_message .= '<div class="info-row"><strong>Queue Position:</strong> #' . htmlspecialchars($queue_result['queue_position']) . '</div>';
                $success_message .= '<div class="info-row"><strong>Estimated Processing Time:</strong> ' . htmlspecialchars($queue_result['estimated_time']) . '</div>';
                $success_message .= '<div class="info-row"><strong>Service:</strong> ' . htmlspecialchars($queue_result['service_name'] ?? 'Resident Registration') . '</div>';
                $success_message .= '</div></div>';
                
                // Store queue information in session for display
                $_SESSION['queue_ticket_number'] = $queue_result['ticket_number'];
                $_SESSION['queue_position'] = $queue_result['queue_position'];
                $_SESSION['service_name'] = $queue_result['service_name'] ?? 'Resident Registration';
                $_SESSION['estimated_time'] = $queue_result['estimated_time'];
            } elseif ($queue_added) {
                $success_message .= '<div class="success-section highlight-section">';
                $success_message .= '<div class="section-title">🎫 Queue Information</div>';
                $success_message .= '<div class="section-content">';
                $success_message .= '<div class="info-row"><strong>Ticket Number:</strong> <span class="highlight-text">' . htmlspecialchars($queue_ticket_number) . '</span></div>';
                $success_message .= '<div class="info-row">Your registration has been added to the processing queue</div>';
                $success_message .= '</div></div>';
            }
            
            // Email notification section
            $success_message .= '<div class="success-section">';
            $success_message .= '<div class="section-title">📧 Email Notifications</div>';
            $success_message .= '<div class="section-content">';
            if ($email_sent) {
                $success_message .= '<div class="info-row success-item">✅ Confirmation email sent to: <strong>' . htmlspecialchars($email) . '</strong></div>';
            } else {
                $success_message .= '<div class="info-row warning-item">⚠️ Confirmation email could not be sent - please contact the administrator</div>';
            }
            
            // Family member notifications
            if ($family_email_count > 0) {
                if ($family_email_success == $family_email_count) {
                    $success_message .= '<div class="info-row success-item">✅ Family notifications sent to ' . $family_email_success . ' member(s)</div>';
                } else {
                    $success_message .= '<div class="info-row warning-item">⚠️ Family notifications: ' . $family_email_success . ' of ' . $family_email_count . ' sent successfully</div>';
                }
            }
            
            // Family user creation info
            if ($family_users_created > 0) {
                $success_message .= '<div class="info-row success-item">✅ ' . $family_users_created . ' family member(s) registered as new users</div>';
            }
            $success_message .= '</div></div>';
            
            // Next steps section
            $success_message .= '<div class="success-section next-steps-section">';
            $success_message .= '<div class="section-title">📋 What Happens Next?</div>';
            $success_message .= '<div class="section-content">';
            $success_message .= '<div class="step-item">1. Your registration is being processed by our team</div>';
            $success_message .= '<div class="step-item">2. You will receive login credentials once approved</div>';
            $success_message .= '<div class="step-item">3. Keep your queue ticket number for reference</div>';
            $success_message .= '<div class="step-item">4. Check queue status using the "Queue" link in navigation</div>';
            $success_message .= '</div></div>';
            
            $success_message .= '<div class="success-footer">Thank you for registering with <strong>Barangay Gumaoc East</strong>!</div>';
            $success_message .= '</div>';
            
            $_SESSION['success'] = $success_message;
            
            header('Location: resident-registration.php?success=1');
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
