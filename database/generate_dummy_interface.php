<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Dummy Census Data</title>
    <style>
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #2e7d32;
            text-align: center;
            margin-bottom: 2rem;
        }
        .btn {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 1rem;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(76, 175, 80, 0.4);
        }
        .result {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 1rem;
            border-left: 5px solid #4CAF50;
            white-space: pre-line;
            font-family: 'Courier New', monospace;
        }
        .success {
            color: #155724;
            background: #d4edda;
            border-color: #28a745;
        }
        .error {
            color: #721c24;
            background: #f8d7da;
            border-color: #dc3545;
        }
        .info {
            background: #e7f3ff;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎲 Census Registration Dummy Data Generator</h1>
        
        <div class="info">
            <strong>📝 This tool will create 5 diverse census registration entries:</strong><br>
            • Large family with multiple disabilities and organizations<br>
            • Single professional with basic info only<br>
            • Senior citizen with organizations<br>
            • Young family with disabilities<br>
            • Complete family with all data types<br><br>
            <strong>Perfect for testing the enhanced admin view features!</strong>
        </div>
        
        <form method="POST">
            <button type="submit" name="generate" class="btn">
                🚀 Generate Dummy Census Data
            </button>
        </form>
        
        <?php if (isset($_POST['generate'])): ?>
            <div class="result <?php echo isset($success) && $success ? 'success' : 'error'; ?>">
                <?php
                try {
                    require_once '../includes/db_connect.php';
                    
                    $pdo->beginTransaction();
                    
                    // Registration 1: Large Family with Multiple Disabilities and Organizations
                    $stmt1 = $pdo->prepare("INSERT INTO `resident_registrations` (
                        `first_name`, `middle_name`, `last_name`, `age`, `birth_date`, `gender`, `civil_status`, `contact_number`, `house_number`, `pangkabuhayan`, `cooking_energy`, `toilet_type`, `electricity_source`, `water_source`, `waste_disposal`, `appliances`, `transportation`, `business`, `contraceptive`, `status`, `submitted_at`, `interviewer`, `interviewer_title`
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    $stmt1->execute([
                        'Maria', 'Santos', 'Cruz', 34, '1990-03-15', 'Female', 'Married', '09171234567', '123', 'Teacher', 'LPG', 'Flush', 'Kuryente', 'Water District', 'Kinokolekta', 'Radyo/Stereo,Telebisyon,Refrigerator,Muwebles', 'Kotse,Motorsiklo', 'Sari-Sari Store', 'Pills', 'pending', date('Y-m-d H:i:s'), 'Admin User', 'Data Entry Clerk'
                    ]);
                    $reg1_id = $pdo->lastInsertId();
                    
                    // Registration 2: Single Professional with Basic Info Only
                    $stmt1->execute([
                        'John', 'Miguel', 'Reyes', 28, '1996-08-22', 'Male', 'Single', '09287654321', '456', 'Software Engineer', 'Gaas', 'De-buhos', 'Kuryente', 'Nawasa', 'Sinusunog', 'Telebisyon,Refrigerator', 'Motorsiklo', '', 'Wala', 'approved', date('Y-m-d H:i:s', strtotime('-2 days')), 'Admin User', 'Data Entry Clerk'
                    ]);
                    $reg2_id = $pdo->lastInsertId();
                    
                    // Registration 3: Senior Citizen with Organizations
                    $stmt1->execute([
                        'Rosa', 'Dela', 'Cruz', 68, '1956-12-05', 'Female', 'Widow', '09345678901', '789', 'Retired', 'Wood', 'Hinuhukay/Balon', 'Gaas', 'Poso Artesiyano', 'Hukay na may takip', 'Radyo/Stereo', 'Tricycle', '', 'Wala', 'pending', date('Y-m-d H:i:s', strtotime('-1 day')), 'Admin User', 'Data Entry Clerk'
                    ]);
                    $reg3_id = $pdo->lastInsertId();
                    
                    // Registration 4: Young Family with Disabilities
                    $stmt1->execute([
                        'Pedro', 'Jose', 'Garcia', 32, '1992-05-18', 'Male', 'Married', '09456789012', '321', 'Construction Worker', 'LPG', 'Flush', 'Kuryente', 'Water District', 'Kinokolekta', 'Radyo/Stereo,Telebisyon', 'Jeep', 'Rice Mill', 'Condom', 'rejected', date('Y-m-d H:i:s', strtotime('-5 days')), 'Admin User', 'Data Entry Clerk'
                    ]);
                    $reg4_id = $pdo->lastInsertId();
                    
                    // Registration 5: Complete Family with All Data Types
                    $stmt1->execute([
                        'Ana', 'Luz', 'Villanueva', 29, '1995-11-30', 'Female', 'Married', '09567890123', '654', 'Nurse', 'LPG', 'Flush', 'Kuryente', 'Water District', 'Kinokolekta', 'Radyo/Stereo,Telebisyon,Refrigerator,Muwebles', 'Sasakyan,Kotse,Motorsiklo', 'Sari-Sari Store,Patahian', 'IUD', 'pending', date('Y-m-d H:i:s', strtotime('-3 hours')), 'Admin User', 'Data Entry Clerk'
                    ]);
                    $reg5_id = $pdo->lastInsertId();
                    
                    // Insert Family Members
                    $family_stmt = $pdo->prepare("INSERT INTO `family_members` (`registration_id`, `full_name`, `relationship`, `age`, `gender`, `civil_status`, `email`, `occupation`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    // Family Members for Registration 1 (Maria Cruz)
                    $family_members_1 = [
                        [$reg1_id, 'Roberto Santos Cruz', 'Spouse', 36, 'Male', 'Married', 'roberto.cruz@email.com', 'Engineer'],
                        [$reg1_id, 'Sofia Cruz Santos', 'Daughter', 12, 'Female', 'Single', '', 'Student'],
                        [$reg1_id, 'Miguel Cruz Santos', 'Son', 10, 'Male', 'Single', '', 'Student'],
                        [$reg1_id, 'Carmen Santos Cruz', 'Mother-in-law', 65, 'Female', 'Widow', 'carmen.santos@email.com', 'Retired'],
                        [$reg1_id, 'Luis Cruz Santos', 'Son', 8, 'Male', 'Single', '', 'Student']
                    ];
                    
                    // Family Members for Registration 3 (Rosa Dela Cruz)
                    $family_members_3 = [
                        [$reg3_id, 'Eduardo Dela Cruz Jr.', 'Son', 45, 'Male', 'Married', 'eduardo.delacruz@email.com', 'Driver'],
                        [$reg3_id, 'Melinda Cruz Soriano', 'Daughter', 42, 'Female', 'Married', 'melinda.soriano@email.com', 'Housewife']
                    ];
                    
                    // Family Members for Registration 4 (Pedro Garcia)
                    $family_members_4 = [
                        [$reg4_id, 'Isabella Garcia Lopez', 'Spouse', 28, 'Female', 'Married', 'isabella.garcia@email.com', 'Housewife'],
                        [$reg4_id, 'Carlos Garcia Lopez', 'Son', 6, 'Male', 'Single', '', 'Student'],
                        [$reg4_id, 'Elena Garcia Lopez', 'Daughter', 4, 'Female', 'Single', '', 'Pre-school']
                    ];
                    
                    // Family Members for Registration 5 (Ana Villanueva)
                    $family_members_5 = [
                        [$reg5_id, 'Mark Anthony Villanueva', 'Spouse', 31, 'Male', 'Married', 'mark.villanueva@email.com', 'Business Owner'],
                        [$reg5_id, 'Sophie Villanueva Santos', 'Daughter', 7, 'Female', 'Single', '', 'Student'],
                        [$reg5_id, 'Gabriel Villanueva Santos', 'Son', 5, 'Male', 'Single', '', 'Pre-school'],
                        [$reg5_id, 'Lourdes Santos Villanueva', 'Mother', 58, 'Female', 'Widow', 'lourdes.santos@email.com', 'Retired Teacher']
                    ];
                    
                    // Insert all family members
                    foreach (array_merge($family_members_1, $family_members_3, $family_members_4, $family_members_5) as $member) {
                        $family_stmt->execute($member);
                    }
                    
                    // Insert Family Disabilities
                    $disability_stmt = $pdo->prepare("INSERT INTO `family_disabilities` (`registration_id`, `name`, `disability_type`) VALUES (?, ?, ?)");
                    
                    $disabilities = [
                        [$reg1_id, 'Miguel Cruz Santos', 'Hearing Impairment'],
                        [$reg1_id, 'Carmen Santos Cruz', 'Mobility Impairment - Uses Walking Cane'],
                        [$reg4_id, 'Carlos Garcia Lopez', 'Autism Spectrum Disorder'],
                        [$reg4_id, 'Pedro Jose Garcia', 'Partial Vision Impairment'],
                        [$reg5_id, 'Lourdes Santos Villanueva', 'Arthritis - Joint Mobility Issues']
                    ];
                    
                    foreach ($disabilities as $disability) {
                        $disability_stmt->execute($disability);
                    }
                    
                    // Insert Family Organizations
                    $org_stmt = $pdo->prepare("INSERT INTO `family_organizations` (`registration_id`, `name`, `organization_type`) VALUES (?, ?, ?)");
                    
                    $organizations = [
                        [$reg1_id, 'Maria Santos Cruz', 'Teachers Association of Bulacan'],
                        [$reg1_id, 'Roberto Santos Cruz', 'Engineers Society Philippines'],
                        [$reg1_id, 'Carmen Santos Cruz', 'Senior Citizens Association'],
                        [$reg3_id, 'Rosa Dela Cruz', 'Senior Citizens Club'],
                        [$reg3_id, 'Rosa Dela Cruz', 'Barangay Health Workers Association'],
                        [$reg3_id, 'Eduardo Dela Cruz Jr.', 'Drivers Union Local Chapter'],
                        [$reg5_id, 'Ana Luz Villanueva', 'Philippine Nurses Association'],
                        [$reg5_id, 'Mark Anthony Villanueva', 'Chamber of Commerce'],
                        [$reg5_id, 'Ana Luz Villanueva', 'Barangay Health Committee'],
                        [$reg5_id, 'Lourdes Santos Villanueva', 'Retired Teachers Association']
                    ];
                    
                    foreach ($organizations as $org) {
                        $org_stmt->execute($org);
                    }
                    
                    $pdo->commit();
                    $success = true;
                    
                    echo "✅ SUCCESS! Created 5 dummy census registrations:\n\n";
                    echo "1. 📊 MARIA CRUZ (ID: $reg1_id) - Large family with 5 members, 2 disabilities, 3 organizations [PENDING]\n";
                    echo "2. 👤 JOHN REYES (ID: $reg2_id) - Single professional, basic info only [APPROVED]\n";
                    echo "3. 👵 ROSA DELA CRUZ (ID: $reg3_id) - Senior with 2 family members, 0 disabilities, 3 organizations [PENDING]\n";
                    echo "4. 👨‍👩‍👧‍👦 PEDRO GARCIA (ID: $reg4_id) - Young family with 3 members, 2 disabilities, 0 organizations [REJECTED]\n";
                    echo "5. 🏆 ANA VILLANUEVA (ID: $reg5_id) - Complete family with 4 members, 1 disability, 4 organizations [PENDING]\n\n";
                    echo "🎯 Data Summary:\n";
                    echo "   • Total Family Members: 14 across all registrations\n";
                    echo "   • Total Disability Records: 5 entries\n";
                    echo "   • Total Organization Memberships: 10 entries\n";
                    echo "   • Status Distribution: 3 Pending, 1 Approved, 1 Rejected\n\n";
                    echo "🚀 Visit the admin panel to see the enhanced view with data badges!\n";
                    echo "📍 Go to: /admin/view-resident-registrations.php\n";
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $success = false;
                    echo "❌ ERROR: " . $e->getMessage() . "\n";
                    echo "Make sure the database tables exist. Run the migration script first.\n";
                }
                ?>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 2rem; text-align: center; color: #666;">
            <p><strong>💡 Note:</strong> This tool creates realistic test data for the enhanced census registration system.</p>
            <p>Each registration includes different combinations of family members, disabilities, and organization memberships.</p>
        </div>
    </div>
</body>
</html>