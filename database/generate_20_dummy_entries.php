<?php
/**
 * GUMAOC Dummy Data Generator - 20 Entries Per Module
 * 
 * This script generates 20 dummy entries for:
 * 1. Census Registration (resident_registrations)
 * 2. Certificate Requests (certificate_requests) 
 * 3. Business Applications (business_applications)
 * 
 * Run this script to populate test data for development and testing.
 */

require_once '../includes/db_connect.php';

// Helper function to generate random email
function generateEmail($firstName, $lastName) {
    $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'email.com', 'proton.me'];
    $randomDomain = $domains[array_rand($domains)];
    $randomNumber = rand(100, 999);
    return strtolower($firstName . '.' . $lastName . $randomNumber . '@' . $randomDomain);
}

// Helper function to generate random birth date based on age
function generateBirthDate($age) {
    $currentYear = date('Y');
    $birthYear = $currentYear - $age;
    $month = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
    $day = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
    return "$birthYear-$month-$day";
}

// Helper function to generate random contact number
function generateContactNumber() {
    return '09' . rand(100000000, 999999999);
}

// Helper function to generate random address
function generateAddress($houseNumber) {
    $streets = [
        'Mabini Street', 'Rizal Avenue', 'Luna Street', 'Bonifacio Road', 
        'Del Pilar Avenue', 'Aguinaldo Street', 'Quezon Boulevard', 'Magsaysay Road',
        'Lapu-Lapu Street', 'Gabriela Avenue', 'Heroes Boulevard', 'Independence Road'
    ];
    $street = $streets[array_rand($streets)];
    return "House $houseNumber, $street, Barangay Gumaoc East, San Jose del Monte, Bulacan, Philippines";
}

// Sample data arrays
$filipinoFirstNames = [
    'Maria', 'Jose', 'Antonio', 'Juan', 'Ana', 'Francisco', 'Carmen', 'Rosa', 'Pedro', 'Manuel',
    'Isabel', 'Miguel', 'Elena', 'Carlos', 'Luz', 'Roberto', 'Teresa', 'Fernando', 'Esperanza', 'Ricardo',
    'Gloria', 'Eduardo', 'Cristina', 'Alfredo', 'Dolores', 'Alberto', 'Remedios', 'Rafael', 'Mercedes', 'Alejandro'
];

$filipinoLastNames = [
    'Santos', 'Reyes', 'Cruz', 'Bautista', 'Ocampo', 'Garcia', 'Mendoza', 'Torres', 'Tomas', 'Andres',
    'Marquez', 'Romualdez', 'Mercado', 'Aguilar', 'Dela Cruz', 'Ramos', 'Villanueva', 'Aquino', 'Castillo', 'Rivera',
    'Fernandez', 'Valdez', 'Gonzalez', 'Morales', 'Jimenez', 'Herrera', 'Sandoval', 'Chavez', 'Perez', 'Vargas'
];

$middleNames = [
    'Dela', 'San', 'Mga', 'Delos', 'Ng', 'De', 'Del', 'Las', 'Los', 'Santa',
    'Santos', 'Jose', 'Maria', 'Antonio', 'Cruz', 'Reyes', 'Garcia', 'Lopez', 'Torres', 'Gonzalez'
];

$civilStatuses = ['Single', 'Married', 'Widowed', 'Separated', 'Divorced'];
$genders = ['Male', 'Female'];
$statuses = ['pending', 'approved', 'rejected'];

// Certificate types
$certificateTypes = [
    'BRGY. CLEARANCE', 'BRGY. INDIGENCY', 'CEDULA', 'PROOF OF RESIDENCY', 
    'BRGY. ID', 'FIRST TIME JOB SEEKER', 'COHABITATION', 'BUSINESS PERMIT'
];

// Business types
$businessTypes = [
    'Sari-Sari Store', 'General Merchandise', 'Food Establishment', 'Services',
    'Manufacturing', 'Trading', 'Construction', 'Transportation', 'Agriculture', 'Technology'
];

try {
    $pdo->beginTransaction();
    
    echo "🚀 Starting comprehensive dummy data generation for all modules...\n\n";
    
    // =================================================================
    // 1. GENERATE 20 CENSUS REGISTRATIONS (resident_registrations)
    // =================================================================
    
    echo "📊 Generating 20 Census Registration entries...\n";
    
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
        $houseNumber = rand(1, 999);
        $address = generateAddress($houseNumber);
        $status = $statuses[array_rand($statuses)];
        
        // Generate livelihood data
        $landOwnership = ['Sariling Lupa', 'Nangungupahan', 'Nakikitira', 'Iba pa'][array_rand(['Sariling Lupa', 'Nangungupahan', 'Nakikitira', 'Iba pa'])];
        $houseOwnership = ['Sariling Bahay', 'Nangungupahan', 'Nakikitira', 'Iba pa'][array_rand(['Sariling Bahay', 'Nangungupahan', 'Nakikitira', 'Iba pa'])];
        $farmland = ['Meron', 'Wala'][array_rand(['Meron', 'Wala'])];
        $cookingEnergy = ['LPG', 'Wood', 'Charcoal', 'Gaas', 'Iba pa'][array_rand(['LPG', 'Wood', 'Charcoal', 'Gaas', 'Iba pa'])];
        $toiletType = ['Flush', 'De-buhos', 'Hinuhukay/Balon', 'Iba pa'][array_rand(['Flush', 'De-buhos', 'Hinuhukay/Balon', 'Iba pa'])];
        $electricitySource = ['Kuryente', 'Gaas', 'Kandila', 'Iba pa'][array_rand(['Kuryente', 'Gaas', 'Kandila', 'Iba pa'])];
        $waterSource = ['Water District', 'Nawasa', 'Poso Artesiyano', 'Iba pa'][array_rand(['Water District', 'Nawasa', 'Poso Artesiyano', 'Iba pa'])];
        $wasteDisposal = ['Kinokolekta', 'Sinusunog', 'Hukay na may takip', 'Iba pa'][array_rand(['Kinokolekta', 'Sinusunog', 'Hukay na may takip', 'Iba pa'])];
        $appliances = ['Radyo/Stereo', 'Telebisyon', 'Refrigerator', 'Muwebles'][array_rand(['Radyo/Stereo', 'Telebisyon', 'Refrigerator', 'Muwebles'])];
        $transportation = ['Kotse', 'Motorsiklo', 'Tricycle', 'Jeep'][array_rand(['Kotse', 'Motorsiklo', 'Tricycle', 'Jeep'])];
        $business = ['Sari-Sari Store', 'Rice Mill', '', ''][array_rand(['Sari-Sari Store', 'Rice Mill', '', ''])];
        $contraceptive = ['Pills', 'Condom', 'IUD', 'Wala'][array_rand(['Pills', 'Condom', 'IUD', 'Wala'])];
        
        // Generate random submission date (last 30 days)
        $submittedAt = date('Y-m-d H:i:s', strtotime('-' . rand(0, 30) . ' days'));
        
        $registration_stmt->execute([
            $firstName, $middleName, $lastName, $birthDate, 'San Jose del Monte, Bulacan', $age, $gender, $civilStatus,
            $contactNumber, $email, $houseNumber, $address, 'Various Livelihood',
            $landOwnership, $houseOwnership, $farmland, $cookingEnergy, $toiletType,
            $electricitySource, $waterSource, $wasteDisposal, $appliances, $transportation,
            $business, $contraceptive, 'System Generator', 'Data Entry Clerk', $status, $submittedAt
        ]);
        
        echo "   ✓ Registration #$i: $firstName $lastName (Status: $status)\n";
    }
    
    // =================================================================
    // 2. GENERATE 20 CERTIFICATE REQUESTS (certificate_requests)
    // =================================================================
    
    echo "\n📄 Generating 20 Certificate Request entries...\n";
    
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
        $houseNumber = rand(1, 999);
        $address = generateAddress($houseNumber);
        $certificateType = $certificateTypes[array_rand($certificateTypes)];
        $yearsOfResidence = rand(1, 30);
        $status = ['pending', 'processing', 'ready', 'released'][array_rand(['pending', 'processing', 'ready', 'released'])];
        
        // Generate purpose based on certificate type
        $purposes = [
            'BRGY. CLEARANCE' => 'For employment application',
            'BRGY. INDIGENCY' => 'For scholarship application', 
            'CEDULA' => 'For business permit renewal',
            'PROOF OF RESIDENCY' => 'For bank loan application',
            'BRGY. ID' => 'For identification purposes',
            'FIRST TIME JOB SEEKER' => 'For job application benefits',
            'COHABITATION' => 'For civil union documentation',
            'BUSINESS PERMIT' => 'For new business establishment'
        ];
        $purpose = $purposes[$certificateType] ?? 'For general purposes';
        
        // Generate random submission date (last 60 days)
        $submittedAt = date('Y-m-d H:i:s', strtotime('-' . rand(0, 60) . ' days'));
        
        $certificate_stmt->execute([
            1, // user_id (using default user ID)
            $certificateType, $fullName, $address, $contactNumber,
            $civilStatus, $gender, $birthDate, 'San Jose del Monte, Bulacan', 'Filipino',
            $yearsOfResidence, $purpose, $status, $submittedAt
        ]);
        
        echo "   ✓ Certificate #$i: $fullName - $certificateType (Status: $status)\n";
    }
    
    // =================================================================
    // 3. GENERATE 20 BUSINESS APPLICATIONS (business_applications)
    // =================================================================
    
    echo "\n🏢 Generating 20 Business Application entries...\n";
    
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
        
        // Generate business name based on type and owner
        $businessNames = [
            'Sari-Sari Store' => "$lastName Family Store",
            'General Merchandise' => "$firstName General Merchandise", 
            'Food Establishment' => "$lastName Eatery",
            'Services' => "$firstName Services",
            'Manufacturing' => "$lastName Manufacturing",
            'Trading' => "$firstName Trading Co.",
            'Construction' => "$lastName Construction Services",
            'Transportation' => "$firstName Transport Services",
            'Agriculture' => "$lastName Agricultural Supply",
            'Technology' => "$firstName Tech Solutions"
        ];
        $businessName = $businessNames[$businessType] ?? "$firstName $lastName Business";
        
        $houseNumber = rand(1, 999);
        $businessAddress = generateAddress($houseNumber);
        $ownerHouseNumber = rand(1, 999);
        $ownerAddress = generateAddress($ownerHouseNumber);
        $contactNumber = generateContactNumber();
        $yearsOperation = rand(1, 15);
        $investmentCapital = rand(10000, 500000);
        $status = ['pending', 'reviewing', 'approved', 'rejected'][array_rand(['pending', 'reviewing', 'approved', 'rejected'])];
        
        // Generate random submission date (last 45 days)
        $submittedAt = date('Y-m-d H:i:s', strtotime('-' . rand(0, 45) . ' days'));
        
        $business_stmt->execute([
            1, // user_id (using default user ID)
            $businessName, $businessType, $businessAddress, $ownerName,
            $ownerAddress, $contactNumber, $yearsOperation, $investmentCapital,
            $status, $submittedAt
        ]);
        
        echo "   ✓ Business #$i: $businessName - $businessType (Status: $status)\n";
    }
    
    // Commit all changes
    $pdo->commit();
    
    echo "\n🎉 SUCCESS! Dummy data generation completed!\n\n";
    echo "📊 SUMMARY:\n";
    echo "   • Census Registrations: 20 entries created\n";
    echo "   • Certificate Requests: 20 entries created  \n";
    echo "   • Business Applications: 20 entries created\n\n";
    echo "📈 TOTAL: 60 new dummy entries across all modules\n\n";
    echo "🔍 VERIFICATION:\n";
    echo "   • Check Admin Dashboard for updated counts\n";
    echo "   • Visit each module's admin page to see the new entries\n";
    echo "   • Data includes realistic Filipino names, addresses, and information\n\n";
    echo "✅ Ready for development and testing!\n";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>