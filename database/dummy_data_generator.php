<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GUMAOC Dummy Data Generator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            color: #2c5530;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .btn {
            background: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #45a049;
        }
        .output {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
            font-family: monospace;
            white-space: pre-line;
            max-height: 400px;
            overflow-y: auto;
        }
        .success { color: #2e7d32; }
        .error { color: #d32f2f; }
        .info { color: #1976d2; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 GUMAOC Dummy Data Generator</h1>
            <p>Generate 20 test entries for each module</p>
        </div>

        <div style="text-align: center;">
            <button class="btn" onclick="generateData()">🚀 Generate All Dummy Data</button>
            <button class="btn" onclick="clearOutput()">🧹 Clear Output</button>
        </div>

        <div class="output" id="output">
            Click "Generate All Dummy Data" to create 20 test entries for:
            📊 Census Registrations
            📄 Certificate Requests  
            🏢 Business Applications
        </div>
    </div>

    <script>
        function generateData() {
            const output = document.getElementById('output');
            output.innerHTML = '<div class="info">🔄 Starting dummy data generation...</div>';
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=generate'
            })
            .then(response => response.text())
            .then(data => {
                output.innerHTML = data;
            })
            .catch(error => {
                output.innerHTML = '<div class="error">❌ Error: ' + error + '</div>';
            });
        }

        function clearOutput() {
            document.getElementById('output').innerHTML = 'Output cleared. Ready for new generation.';
        }
    </script>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'generate') {
    // Include database connection
    require_once '../includes/db_connect.php';
    
    // Helper functions
    function generateEmail($firstName, $lastName) {
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'email.com', 'proton.me'];
        $randomDomain = $domains[array_rand($domains)];
        $randomNumber = rand(100, 999);
        return strtolower($firstName . '.' . $lastName . $randomNumber . '@' . $randomDomain);
    }
    
    function generateBirthDate($age) {
        $currentYear = date('Y');
        $birthYear = $currentYear - $age;
        $month = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
        $day = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
        return "$birthYear-$month-$day";
    }
    
    function generateContactNumber() {
        return '09' . rand(100000000, 999999999);
    }
    
    function generateAddress($houseNumber) {
        $streets = [
            'Mabini Street', 'Rizal Avenue', 'Luna Street', 'Bonifacio Road', 
            'Del Pilar Avenue', 'Aguinaldo Street', 'Quezon Boulevard', 'Magsaysay Road'
        ];
        $street = $streets[array_rand($streets)];
        return "House $houseNumber, $street, Barangay Gumaoc East, San Jose del Monte, Bulacan";
    }
    
    // Sample data arrays
    $filipinoFirstNames = [
        'Maria', 'Jose', 'Antonio', 'Juan', 'Ana', 'Francisco', 'Carmen', 'Rosa', 'Pedro', 'Manuel',
        'Isabel', 'Miguel', 'Elena', 'Carlos', 'Luz', 'Roberto', 'Teresa', 'Fernando', 'Esperanza', 'Ricardo'
    ];
    
    $filipinoLastNames = [
        'Santos', 'Reyes', 'Cruz', 'Bautista', 'Ocampo', 'Garcia', 'Mendoza', 'Torres', 'Tomas', 'Andres',
        'Marquez', 'Romualdez', 'Mercado', 'Aguilar', 'Dela Cruz', 'Ramos', 'Villanueva', 'Aquino', 'Castillo', 'Rivera'
    ];
    
    $middleNames = ['Dela', 'San', 'Mga', 'Delos', 'Ng', 'De', 'Del', 'Las', 'Los', 'Santa'];
    $civilStatuses = ['Single', 'Married', 'Widowed', 'Separated'];
    $genders = ['Male', 'Female'];
    
    try {
        $pdo->beginTransaction();
        
        echo '<div class="info">🚀 Starting comprehensive dummy data generation...</div>';
        
        // Get existing user IDs from residents table
        $userIds = [];
        $userQuery = $pdo->query("SELECT id FROM residents ORDER BY id LIMIT 10");
        while ($row = $userQuery->fetch()) {
            $userIds[] = $row['id'];
        }
        
        // If no users exist, create a few basic resident records first
        if (empty($userIds)) {
            echo '<div class="info">⚠️ No existing users found. Creating 3 basic resident records first...</div>';
            
            $basic_resident_stmt = $pdo->prepare("INSERT INTO `residents` (
                `first_name`, `middle_name`, `last_name`, `email`, `phone`, `password`,
                `address`, `house_number`, `barangay`, `sitio`,
                `birthdate`, `birth_place`, `gender`, `civil_status`, `rfid_code`, `rfid`,
                `status`, `profile_complete`, `created_at`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 1, NOW())");
            
            for ($i = 1; $i <= 3; $i++) {
                $firstName = $filipinoFirstNames[array_rand($filipinoFirstNames)];
                $lastName = $filipinoLastNames[array_rand($filipinoLastNames)];
                $middleName = $middleNames[array_rand($middleNames)];
                $email = generateEmail($firstName, $lastName);
                $contactNumber = generateContactNumber();
                $houseNumber = rand(1, 100);
                $address = generateAddress($houseNumber);
                $age = rand(25, 50);
                $birthDate = generateBirthDate($age);
                $gender = $genders[array_rand($genders)];
                $civilStatus = $civilStatuses[array_rand($civilStatuses)];
                $rfidCode = 'RF' . str_pad($i, 8, '0', STR_PAD_LEFT);
                
                $basic_resident_stmt->execute([
                    $firstName, $middleName, $lastName, $email, $contactNumber, password_hash('password', PASSWORD_DEFAULT),
                    $address, $houseNumber, 'Gumaoc East', 'Block 1',
                    $birthDate, 'San Jose del Monte, Bulacan', $gender, $civilStatus, $rfidCode, $rfidCode
                ]);
                
                $userIds[] = $pdo->lastInsertId();
                echo '<div class="success">   ✓ Created basic resident #' . $i . ': ' . $firstName . ' ' . $lastName . '</div>';
            }
        }
        
        echo '<div class="info">📊 Found ' . count($userIds) . ' existing user(s) for foreign key references</div>';
        
        // Function to get a random valid user ID
        function getRandomUserId($userIds) {
            return $userIds[array_rand($userIds)];
        }
        
        // ===== CENSUS REGISTRATIONS =====
        echo '<div class="info">📊 Generating 20 Census Registration entries...</div>';
        
        $registration_stmt = $pdo->prepare("INSERT INTO `resident_registrations` (
            `first_name`, `middle_name`, `last_name`, `birth_date`, `birth_place`, `age`, `gender`, `civil_status`, 
            `contact_number`, `email`, `house_number`, `address`, `pangkabuhayan`, 
            `land_ownership`, `house_ownership`, `farmland`, `cooking_energy`, `toilet_type`, 
            `electricity_source`, `water_source`, `waste_disposal`, `appliances`, `transportation`, 
            `business`, `contraceptive`, `interviewer`, `interviewer_title`, `status`, `submitted_at`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        for ($i = 1; $i <= 20; $i++) {
            $firstName = $filipinoFirstNames[array_rand($filipinoFirstNames)];
            $lastName = $filipinoLastNames[array_rand($filipinoLastNames)];
            $middleName = $middleNames[array_rand($middleNames)];
            $age = rand(18, 80);
            $birthDate = generateBirthDate($age);
            $gender = $genders[array_rand($genders)];
            $civilStatus = $civilStatuses[array_rand($civilStatuses)];
            $contactNumber = generateContactNumber();
            $email = generateEmail($firstName, $lastName);
            $houseNumber = rand(1, 500);
            $address = generateAddress($houseNumber);
            $status = ['pending', 'approved', 'rejected'][array_rand(['pending', 'approved', 'rejected'])];
            
            $submittedAt = date('Y-m-d H:i:s', strtotime('-' . rand(0, 30) . ' days'));
            
            $registration_stmt->execute([
                $firstName, $middleName, $lastName, $birthDate, 'San Jose del Monte, Bulacan', $age, $gender, $civilStatus,
                $contactNumber, $email, $houseNumber, $address, 'Various Livelihood',
                'Sariling Lupa', 'Sariling Bahay', 'Wala', 'LPG', 'Flush',
                'Kuryente', 'Water District', 'Kinokolekta', 'Telebisyon', 'Motorcyle',
                'Sari-Sari Store', 'Pills', 'System Generator', 'Data Entry', $status, $submittedAt
            ]);
            
            echo '<div class="success">   ✓ Registration #' . $i . ': ' . $firstName . ' ' . $lastName . ' (Status: ' . $status . ')</div>';
        }
        
        // ===== CERTIFICATE REQUESTS =====
        echo '<div class="info">📄 Generating 20 Certificate Request entries...</div>';
        
        $certificateTypes = ['BRGY. CLEARANCE', 'BRGY. INDIGENCY', 'CEDULA', 'PROOF OF RESIDENCY', 'BRGY. ID'];
        
        $certificate_stmt = $pdo->prepare("INSERT INTO `certificate_requests` (
            `user_id`, `certificate_type`, `full_name`, `address`, `mobile_number`, 
            `civil_status`, `gender`, `birth_date`, `birth_place`, `citizenship`, 
            `years_of_residence`, `purpose`, `status`, `submitted_at`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        for ($i = 1; $i <= 20; $i++) {
            $firstName = $filipinoFirstNames[array_rand($filipinoFirstNames)];
            $lastName = $filipinoLastNames[array_rand($filipinoLastNames)];
            $middleName = $middleNames[array_rand($middleNames)];
            $fullName = "$firstName $middleName $lastName";
            $age = rand(18, 70);
            $birthDate = generateBirthDate($age);
            $gender = $genders[array_rand($genders)];
            $civilStatus = $civilStatuses[array_rand($civilStatuses)];
            $contactNumber = generateContactNumber();
            $houseNumber = rand(1, 500);
            $address = generateAddress($houseNumber);
            $certificateType = $certificateTypes[array_rand($certificateTypes)];
            $yearsOfResidence = rand(1, 30);
            $status = ['pending', 'processing', 'ready', 'released'][array_rand(['pending', 'processing', 'ready', 'released'])];
            $purpose = 'For employment and legal purposes';
            
            $submittedAt = date('Y-m-d H:i:s', strtotime('-' . rand(0, 60) . ' days'));
            
            // Use a valid user ID
            $userId = getRandomUserId($userIds);
            
            $certificate_stmt->execute([
                $userId, $certificateType, $fullName, $address, $contactNumber,
                $civilStatus, $gender, $birthDate, 'San Jose del Monte, Bulacan', 'Filipino',
                $yearsOfResidence, $purpose, $status, $submittedAt
            ]);
            
            echo '<div class="success">   ✓ Certificate #' . $i . ': ' . $fullName . ' - ' . $certificateType . ' (Status: ' . $status . ')</div>';
        }
        
        // ===== BUSINESS APPLICATIONS =====
        echo '<div class="info">🏢 Generating 20 Business Application entries...</div>';
        
        $businessTypes = ['Sari-Sari Store', 'General Merchandise', 'Food Establishment', 'Services', 'Trading'];
        
        $business_stmt = $pdo->prepare("INSERT INTO `business_applications` (
            `user_id`, `business_name`, `business_type`, `business_address`, `owner_name`, 
            `owner_address`, `contact_number`, `years_operation`, `investment_capital`, 
            `status`, `submitted_at`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        for ($i = 1; $i <= 20; $i++) {
            $firstName = $filipinoFirstNames[array_rand($filipinoFirstNames)];
            $lastName = $filipinoLastNames[array_rand($filipinoLastNames)];
            $middleName = $middleNames[array_rand($middleNames)];
            $ownerName = "$firstName $middleName $lastName";
            $businessType = $businessTypes[array_rand($businessTypes)];
            $businessName = "$lastName Family $businessType";
            
            $houseNumber = rand(1, 500);
            $businessAddress = generateAddress($houseNumber);
            $ownerHouseNumber = rand(1, 500);
            $ownerAddress = generateAddress($ownerHouseNumber);
            $contactNumber = generateContactNumber();
            $yearsOperation = rand(1, 15);
            $investmentCapital = rand(10000, 500000);
            $status = ['pending', 'reviewing', 'approved', 'rejected'][array_rand(['pending', 'reviewing', 'approved', 'rejected'])];
            
            $submittedAt = date('Y-m-d H:i:s', strtotime('-' . rand(0, 45) . ' days'));
            
            // Use a valid user ID
            $userId = getRandomUserId($userIds);
            
            $business_stmt->execute([
                $userId, $businessName, $businessType, $businessAddress, $ownerName,
                $ownerAddress, $contactNumber, $yearsOperation, $investmentCapital,
                $status, $submittedAt
            ]);
            
            echo '<div class="success">   ✓ Business #' . $i . ': ' . $businessName . ' - ' . $businessType . ' (Status: ' . $status . ')</div>';
        }
        
        $pdo->commit();
        
        echo '<div class="success">🎉 SUCCESS! Dummy data generation completed!</div>';
        echo '<div class="info">📊 SUMMARY:
   • Census Registrations: 20 entries created
   • Certificate Requests: 20 entries created  
   • Business Applications: 20 entries created

📈 TOTAL: 60 new dummy entries across all modules

🔍 VERIFICATION:
   • Check Admin Dashboard for updated counts
   • Visit each module\'s admin page to see the new entries
   • Data includes realistic Filipino names and addresses

✅ Ready for development and testing!</div>';
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo '<div class="error">❌ ERROR: ' . $e->getMessage() . '</div>';
        echo '<div class="error">Stack trace: ' . $e->getTraceAsString() . '</div>';
    }
    
    exit;
}
?>