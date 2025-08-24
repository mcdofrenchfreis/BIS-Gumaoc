<?php
// Comprehensive Dummy Data Generator for Census Registrations
// Creates 10 diverse test registrations with random emails and complete information

require_once '../includes/db_connect.php';

// Helper function to generate random email
function generateRandomEmail($firstName, $lastName) {
    $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'email.com', 'mail.com'];
    $randomDomain = $domains[array_rand($domains)];
    $randomNumber = rand(100, 999);
    return strtolower($firstName . '.' . $lastName . $randomNumber . '@' . $randomDomain);
}

// Helper function to generate random birth date
function generateBirthDate($age) {
    $currentYear = date('Y');
    $birthYear = $currentYear - $age;
    $month = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
    $day = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
    return "$birthYear-$month-$day";
}

try {
    $pdo->beginTransaction();
    
    echo "🚀 Starting comprehensive dummy data generation...\n\n";
    
    // Prepare registration statement with complete schema
    $registration_stmt = $pdo->prepare("INSERT INTO `resident_registrations` (
        `first_name`, `middle_name`, `last_name`, `age`, `birth_date`, `gender`, `civil_status`, `contact_number`, `house_number`, `pangkabuhayan`, 
        `land_ownership`, `land_ownership_other`, `house_ownership`, `house_ownership_other`, `farmland`, 
        `cooking_energy`, `cooking_energy_other`, `toilet_type`, `toilet_type_other`, `electricity_source`, `electricity_source_other`, 
        `water_source`, `water_source_other`, `waste_disposal`, `waste_disposal_other`, `appliances`, 
        `transportation`, `transportation_other`, `business`, `business_other`, `contraceptive`, 
        `status`, `submitted_at`, `interviewer`, `interviewer_title`
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $registrations = [];
    
    // Registration 1: Tech Professional with Large Extended Family
    $registrations[] = [
        'Angela', 'Marie', 'Santos', 31, generateBirthDate(31), 'Female', 'Married', '09123456789', '101', 'Software Developer', 
        'Pag-aari', '', 'Pag-aari', '', 'Wala', 
        'LPG', '', 'Flush', '', 'Kuryente', '', 
        'Water District', '', 'Kinokolekta', '', 'Radyo/Stereo,Telebisyon,Refrigerator,Muwebles,Computer,Air Conditioner,Washing Machine', 
        'Kotse,Motorsiklo', '', 'Online Business,Freelancing,Software Development', '', 'Pills', 
        'pending', date('Y-m-d H:i:s'), 'Jenny Mendoza', 'Barangay Health Worker'
    ];
    
    // Registration 2: Senior Citizen Community Leader
    $registrations[] = [
        'Ricardo', 'Pablo', 'Dela Rosa', 72, generateBirthDate(72), 'Male', 'Widower', '09234567890', '202', 'Retired Principal', 
        'Pag-aari', '', 'Pag-aari', '', 'Wala', 
        'LPG', '', 'Flush', '', 'Kuryente', '', 
        'Water District', '', 'Kinokolekta', '', 'Radyo/Stereo,Telebisyon,Refrigerator', 
        'Tricycle', '', 'Pension,Government Benefits', '', 'Wala', 
        'approved', date('Y-m-d H:i:s', strtotime('-3 days')), 'Carlos Villanueva', 'Census Enumerator'
    ];
    
    // Registration 3: Young Single Mother
    $registrations[] = [
        'Isabella', 'Grace', 'Fernandez', 24, generateBirthDate(24), 'Female', 'Single', '09345678901', '303', 'Cashier', 
        'Inuupahan', '', 'Umuupa', '', 'Wala', 
        'Gaas', '', 'De-buhos', '', 'Gaas', '', 
        'Poso Artesiyano', '', 'Sinusunog', '', 'Radyo/Stereo,Telebisyon', 
        'Jeep,Tricycle', '', 'Sari-Sari Store', '', 'Condom', 
        'pending', date('Y-m-d H:i:s', strtotime('-6 hours')), 'Maria Gonzales', 'Community Volunteer'
    ];
    
    // Registration 4: Rural Farming Family
    $registrations[] = [
        'Jose', 'Miguel', 'Cabrera', 45, generateBirthDate(45), 'Male', 'Married', '09456789012', '404', 'Farmer', 
        'Pag-aari', '', 'Pag-aari', '', 'Pag-aari', 
        'Kahoy', '', 'Hinuhukay/Balon', '', 'Generator', 'Solar Panel', 
        'Deep Well', 'Spring Water', 'Compost Pit', 'Organic Composting', 'Radyo/Stereo', 
        'Carabao Cart,Motorcle', 'Farm Tractor', 'Rice Farming,Vegetable Garden,Livestock', 'Organic Farming', 'NFP', 
        'rejected', date('Y-m-d H:i:s', strtotime('-1 week')), 'Pedro Reyes', 'Agricultural Extension Worker'
    ];
    
    // Registration 5: Healthcare Worker Family
    $registrations[] = [
        'Dr. Carmen', 'Luna', 'Rodriguez', 38, generateBirthDate(38), 'Female', 'Married', '09567890123', '505', 'Medical Doctor', 
        'Pag-aari', '', 'Pag-aari', '', 'Wala', 
        'LPG', '', 'Flush', '', 'Kuryente', '', 
        'Water District', '', 'Kinokolekta', '', 'Radyo/Stereo,Telebisyon,Refrigerator,Muwebles,Air Conditioner,Computer,Washing Machine', 
        'Kotse,Motorsiklo,Van', '', 'Medical Clinic,Health Services', '', 'IUD', 
        'pending', date('Y-m-d H:i:s', strtotime('-2 hours')), 'Rosa Martinez', 'Public Health Nurse'
    ];
    
    // Registration 6: OFW Family
    $registrations[] = [
        'Michael', 'Jose', 'Cruz', 35, generateBirthDate(35), 'Male', 'Married', '09678901234', '606', 'Overseas Worker (Dubai)', 
        'Pag-aari', '', 'Pag-aari', '', 'Wala', 
        'LPG', '', 'Flush', '', 'Kuryente', '', 
        'Water District', '', 'Kinokolekta', '', 'Radyo/Stereo,Telebisyon,Refrigerator,Muwebles,Air Conditioner,Computer', 
        'Kotse,Van,Motorsiklo', '', 'Remittances,Real Estate,Investment', 'International Money Transfer', 'Pills', 
        'approved', date('Y-m-d H:i:s', strtotime('-5 days')), 'Ana Flores', 'Barangay Secretary'
    ];
    
    // Registration 7: Indigenous Family
    $registrations[] = [
        'Lakandula', 'Bayani', 'Magbanua', 52, generateBirthDate(52), 'Male', 'Married', '09789012345', '707', 'Traditional Healer', 
        'Iba pa', 'Ancestral Domain', 'Iba pa', 'Traditional House', 'Pag-aari', 
        'Kahoy', '', 'Hinuhukay/Balon', '', 'Iba pa', 'Solar Panel', 
        'Iba pa', 'Spring Water', 'Iba pa', 'Natural Composting', 'Radyo/Stereo', 
        'Walking,Tricycle', 'Carabao', 'Herbal Medicine,Handicrafts,Traditional Crafts', 'Cultural Arts', 'NFP', 
        'pending', date('Y-m-d H:i:s', strtotime('-4 hours')), 'Esperanza Santos', 'Indigenous Peoples Affairs Officer'
    ];
    
    // Registration 8: Urban Professional Couple
    $registrations[] = [
        'Alexandra', 'Sophia', 'Tan', 29, generateBirthDate(29), 'Female', 'Married', '09890123456', '808', 'Marketing Manager', 
        'Pag-aari', '', 'Pag-aari', '', 'Wala', 
        'LPG', '', 'Flush', '', 'Kuryente', '', 
        'Water District', '', 'Kinokolekta', '', 'Radyo/Stereo,Telebisyon,Refrigerator,Muwebles,Air Conditioner,Computer,Washing Machine', 
        'Kotse,Motorcycle', '', 'Digital Marketing Agency,Online Business', '', 'Pills', 
        'pending', date('Y-m-d H:i:s', strtotime('-1 day')), 'Roberto Silva', 'IT Support Specialist'
    ];
    
    // Registration 9: Elderly Couple with Disabilities
    $registrations[] = [
        'Corazon', 'Esperanza', 'Villanueva', 78, generateBirthDate(78), 'Female', 'Married', '09901234567', '909', 'Retired Teacher', 
        'Pag-aari', '', 'Pag-aari', '', 'Wala', 
        'LPG', '', 'Flush', '', 'Kuryente', '', 
        'Water District', '', 'Kinokolekta', '', 'Radyo/Stereo,Telebisyon,Refrigerator', 
        'Wheelchair,Tricycle', 'Medical Transport', 'Pension,Social Security,Government Benefits', 'Senior Citizens Discount', 'Wala', 
        'approved', date('Y-m-d H:i:s', strtotime('-2 weeks')), 'Gloria Ramos', 'Senior Citizens Coordinator'
    ];
    
    // Registration 10: Young Entrepreneur Family
    $registrations[] = [
        'Gabriel', 'Andrew', 'Moreno', 27, generateBirthDate(27), 'Male', 'Married', '09012345678', '1010', 'Business Owner', 
        'Pag-aari', '', 'Pag-aari', '', 'Wala', 
        'LPG', '', 'Flush', '', 'Kuryente', '', 
        'Water District', '', 'Kinokolekta', '', 'Radyo/Stereo,Telebisyon,Refrigerator,Muwebles,Computer,Air Conditioner', 
        'Kotse,Delivery Van,Motorsiklo', 'Business Vehicle', 'Food Delivery,E-commerce,Digital Services', 'Tech Startup', 'Condom', 
        'pending', date('Y-m-d H:i:s', strtotime('-30 minutes')), 'Daniel Castro', 'Business Development Officer'
    ];
    
    // Insert all registrations
    $registration_ids = [];
    foreach ($registrations as $index => $reg) {
        $registration_stmt->execute($reg);
        $registration_ids[] = $pdo->lastInsertId();
        echo "✅ Created Registration " . ($index + 1) . ": {$reg[0]} {$reg[2]} (ID: " . end($registration_ids) . ")\n";
    }
    
    echo "\n📋 Adding family members with random emails...\n";
    
    // Family Members with random emails and birth dates
    $family_stmt = $pdo->prepare("INSERT INTO `family_members` (`registration_id`, `full_name`, `relationship`, `birth_date`, `age`, `gender`, `civil_status`, `email`, `occupation`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $family_data = [
        // Registration 1 (Angela Santos) - Tech family
        [$registration_ids[0], 'Marcus Santos Dela Cruz', 'Spouse', generateBirthDate(33), 33, 'Lalaki', 'Married', generateRandomEmail('Marcus', 'DelaCruz'), 'DevOps Engineer'],
        [$registration_ids[0], 'Sophia Santos Dela Cruz', 'Daughter', generateBirthDate(8), 8, 'Babae', 'Single', '', 'Student'],
        [$registration_ids[0], 'Ethan Santos Dela Cruz', 'Son', generateBirthDate(5), 5, 'Lalaki', 'Single', '', 'Pre-school'],
        [$registration_ids[0], 'Elena Santos Rodriguez', 'Mother', generateBirthDate(65), 65, 'Babae', 'Widow', generateRandomEmail('Elena', 'Rodriguez'), 'Retired Nurse'],
        [$registration_ids[0], 'Victor Santos', 'Father-in-law', generateBirthDate(67), 67, 'Lalaki', 'Married', generateRandomEmail('Victor', 'Santos'), 'Retired Engineer'],
        
        // Registration 2 (Ricardo Dela Rosa) - Senior with adult children
        [$registration_ids[1], 'Patricia Dela Rosa Morales', 'Daughter', generateBirthDate(45), 45, 'Babae', 'Married', generateRandomEmail('Patricia', 'Morales'), 'Bank Manager'],
        [$registration_ids[1], 'Roberto Dela Rosa Jr.', 'Son', generateBirthDate(42), 42, 'Lalaki', 'Married', generateRandomEmail('Roberto', 'DelaRosa'), 'Police Officer'],
        [$registration_ids[1], 'Sofia Dela Rosa', 'Granddaughter', generateBirthDate(18), 18, 'Babae', 'Single', generateRandomEmail('Sofia', 'DelaRosa'), 'College Student'],
        
        // Registration 3 (Isabella Fernandez) - Single mother
        [$registration_ids[2], 'Mia Fernandez', 'Daughter', generateBirthDate(4), 4, 'Babae', 'Single', '', 'Pre-school'],
        [$registration_ids[2], 'Rosa Fernandez Martinez', 'Mother', generateBirthDate(48), 48, 'Babae', 'Separated', generateRandomEmail('Rosa', 'Martinez'), 'Factory Worker'],
        
        // Registration 4 (Jose Cabrera) - Farming family
        [$registration_ids[3], 'Luz Cabrera Santos', 'Spouse', generateBirthDate(42), 42, 'Babae', 'Married', generateRandomEmail('Luz', 'Santos'), 'Housewife/Farmer'],
        [$registration_ids[3], 'Juan Cabrera Santos', 'Son', generateBirthDate(19), 19, 'Lalaki', 'Single', generateRandomEmail('Juan', 'Cabrera'), 'Agricultural Student'],
        [$registration_ids[3], 'Maria Cabrera Santos', 'Daughter', generateBirthDate(16), 16, 'Babae', 'Single', '', 'High School Student'],
        [$registration_ids[3], 'Luis Cabrera Santos', 'Son', generateBirthDate(12), 12, 'Lalaki', 'Single', '', 'Elementary Student'],
        
        // Registration 5 (Dr. Carmen Rodriguez) - Healthcare family
        [$registration_ids[4], 'Dr. Paulo Rodriguez', 'Spouse', generateBirthDate(40), 40, 'Lalaki', 'Married', generateRandomEmail('Paulo', 'Rodriguez'), 'Pediatrician'],
        [$registration_ids[4], 'Camila Rodriguez', 'Daughter', generateBirthDate(12), 12, 'Babae', 'Single', '', 'Student'],
        [$registration_ids[4], 'Sebastian Rodriguez', 'Son', generateBirthDate(9), 9, 'Lalaki', 'Single', '', 'Student'],
        
        // Registration 6 (Michael Cruz) - OFW family
        [$registration_ids[5], 'Jennifer Cruz Reyes', 'Spouse', generateBirthDate(32), 32, 'Babae', 'Married', generateRandomEmail('Jennifer', 'Reyes'), 'Teacher'],
        [$registration_ids[5], 'Matthew Cruz Reyes', 'Son', generateBirthDate(10), 10, 'Lalaki', 'Single', '', 'Student'],
        [$registration_ids[5], 'Samantha Cruz Reyes', 'Daughter', generateBirthDate(7), 7, 'Babae', 'Single', '', 'Student'],
        [$registration_ids[5], 'Antonio Cruz', 'Father', generateBirthDate(68), 68, 'Lalaki', 'Married', generateRandomEmail('Antonio', 'Cruz'), 'Retired Government Employee'],
        
        // Registration 7 (Lakandula Magbanua) - Indigenous family
        [$registration_ids[6], 'Mayumi Magbanua', 'Spouse', generateBirthDate(48), 48, 'Babae', 'Married', '', 'Traditional Weaver'],
        [$registration_ids[6], 'Kalaw Magbanua', 'Son', generateBirthDate(22), 22, 'Lalaki', 'Single', generateRandomEmail('Kalaw', 'Magbanua'), 'Cultural Preservation Officer'],
        [$registration_ids[6], 'Tala Magbanua', 'Daughter', generateBirthDate(19), 19, 'Babae', 'Single', '', 'Traditional Arts Student'],
        
        // Registration 8 (Alexandra Tan) - Urban professional
        [$registration_ids[7], 'David Tan Wong', 'Spouse', generateBirthDate(31), 31, 'Lalaki', 'Married', generateRandomEmail('David', 'Wong'), 'Financial Analyst'],
        [$registration_ids[7], 'Olivia Tan Wong', 'Daughter', generateBirthDate(3), 3, 'Babae', 'Single', '', 'Toddler'],
        
        // Registration 9 (Corazon Villanueva) - Elderly couple
        [$registration_ids[8], 'Teodoro Villanueva', 'Spouse', generateBirthDate(81), 81, 'Lalaki', 'Married', generateRandomEmail('Teodoro', 'Villanueva'), 'Retired Principal'],
        [$registration_ids[8], 'Grace Villanueva Santos', 'Daughter', generateBirthDate(52), 52, 'Babae', 'Married', generateRandomEmail('Grace', 'Santos'), 'Social Worker'],
        [$registration_ids[8], 'Paul Villanueva', 'Son', generateBirthDate(48), 48, 'Lalaki', 'Married', generateRandomEmail('Paul', 'Villanueva'), 'Government Employee'],
        
        // Registration 10 (Gabriel Moreno) - Young entrepreneur
        [$registration_ids[9], 'Bianca Moreno Silva', 'Spouse', generateBirthDate(25), 25, 'Babae', 'Married', generateRandomEmail('Bianca', 'Silva'), 'Marketing Coordinator'],
        [$registration_ids[9], 'Lucas Moreno Silva', 'Son', generateBirthDate(2), 2, 'Lalaki', 'Single', '', 'Toddler']
    ];
    
    foreach ($family_data as $member) {
        $family_stmt->execute($member);
    }
    
    echo "✅ Added " . count($family_data) . " family members with random emails\n\n";
    
    echo "🏥 Adding disability records...\n";
    
    // Family Disabilities
    $disability_stmt = $pdo->prepare("INSERT INTO `family_disabilities` (`registration_id`, `name`, `disability_type`) VALUES (?, ?, ?)");
    
    $disabilities = [
        [$registration_ids[0], 'Elena Santos Rodriguez', 'Diabetes Type 2'],
        [$registration_ids[1], 'Roberto Dela Rosa Jr.', 'Hypertension'],
        [$registration_ids[2], 'Isabella Grace Fernandez', 'Mild Asthma'],
        [$registration_ids[3], 'Luis Cabrera Santos', 'Learning Disability - ADHD'],
        [$registration_ids[4], 'Sebastian Rodriguez', 'Speech Therapy'],
        [$registration_ids[6], 'Mayumi Magbanua', 'Partial Hearing Loss'],
        [$registration_ids[7], 'Olivia Tan Wong', 'Developmental Delay'],
        [$registration_ids[8], 'Corazon Esperanza Villanueva', 'Mobility Impairment - Uses Walker'],
        [$registration_ids[8], 'Teodoro Villanueva', 'Vision Impairment - Cataracts'],
        [$registration_ids[9], 'Lucas Moreno Silva', 'Mild Cerebral Palsy']
    ];
    
    foreach ($disabilities as $disability) {
        $disability_stmt->execute($disability);
    }
    
    echo "✅ Added " . count($disabilities) . " disability records\n\n";
    
    echo "🏢 Adding organization memberships...\n";
    
    // Family Organizations
    $org_stmt = $pdo->prepare("INSERT INTO `family_organizations` (`registration_id`, `name`, `organization_type`) VALUES (?, ?, ?)");
    
    $organizations = [
        // Tech Professional Family
        [$registration_ids[0], 'Angela Marie Santos', 'Women in Technology Philippines'],
        [$registration_ids[0], 'Marcus Santos Dela Cruz', 'Philippine Software Industry Association'],
        [$registration_ids[0], 'Elena Santos Rodriguez', 'Retired Nurses Association'],
        
        // Senior Citizen Leader
        [$registration_ids[1], 'Ricardo Pablo Dela Rosa', 'Senior Citizens Federation'],
        [$registration_ids[1], 'Ricardo Pablo Dela Rosa', 'Retired Educators Association'],
        [$registration_ids[1], 'Patricia Dela Rosa Morales', 'Bankers Association of the Philippines'],
        [$registration_ids[1], 'Roberto Dela Rosa Jr.', 'Police Officers Association'],
        
        // Single Mother
        [$registration_ids[2], 'Isabella Grace Fernandez', 'Single Mothers Support Group'],
        [$registration_ids[2], 'Rosa Fernandez Martinez', 'Workers Union Local Chapter'],
        
        // Farming Family
        [$registration_ids[3], 'Jose Miguel Cabrera', 'Farmers Cooperative'],
        [$registration_ids[3], 'Luz Cabrera Santos', 'Rural Women Association'],
        [$registration_ids[3], 'Juan Cabrera Santos', 'Young Farmers Organization'],
        
        // Healthcare Family
        [$registration_ids[4], 'Dr. Carmen Luna Rodriguez', 'Philippine Medical Association'],
        [$registration_ids[4], 'Dr. Paulo Rodriguez', 'Philippine Pediatric Society'],
        [$registration_ids[4], 'Jennifer Cruz Reyes', 'Teachers Association'],
        
        // OFW Family
        [$registration_ids[5], 'Michael Jose Cruz', 'OFW Mutual Aid Society'],
        [$registration_ids[5], 'Jennifer Cruz Reyes', 'Public School Teachers Association'],
        [$registration_ids[5], 'Antonio Cruz', 'Government Retirees Association'],
        
        // Indigenous Family
        [$registration_ids[6], 'Lakandula Bayani Magbanua', 'Indigenous Peoples Council'],
        [$registration_ids[6], 'Mayumi Magbanua', 'Traditional Arts Preservation Society'],
        [$registration_ids[6], 'Kalaw Magbanua', 'Cultural Heritage Foundation'],
        
        // Urban Professional
        [$registration_ids[7], 'Alexandra Sophia Tan', 'Marketing Association Philippines'],
        [$registration_ids[7], 'David Tan Wong', 'Financial Analysts Society'],
        
        // Elderly Couple
        [$registration_ids[8], 'Corazon Esperanza Villanueva', 'Senior Citizens Club'],
        [$registration_ids[8], 'Teodoro Villanueva', 'Retired Principals Association'],
        [$registration_ids[8], 'Grace Villanueva Santos', 'Social Workers Association'],
        [$registration_ids[8], 'Paul Villanueva', 'Government Employees Organization'],
        
        // Young Entrepreneur
        [$registration_ids[9], 'Gabriel Andrew Moreno', 'Young Entrepreneurs Organization'],
        [$registration_ids[9], 'Bianca Moreno Silva', 'Digital Marketing Professionals']
    ];
    
    foreach ($organizations as $org) {
        $org_stmt->execute($org);
    }
    
    echo "✅ Added " . count($organizations) . " organization memberships\n\n";
    
    $pdo->commit();
    
    echo "🎉 SUCCESS! Created comprehensive dummy data with complete livelihood information:\n\n";
    echo "📊 SUMMARY:\n";
    echo "   • Total Registrations: 10 diverse profiles with complete livelihood data\n";
    echo "   • Total Family Members: " . count($family_data) . " with realistic emails and birth dates\n";
    echo "   • Total Disability Records: " . count($disabilities) . " entries\n";
    echo "   • Total Organization Memberships: " . count($organizations) . " entries\n";
    echo "   • Complete Livelihood Data: All 12 sections (A-L) fully populated\n\n";
    
    echo "👥 REGISTRATION PROFILES WITH ENHANCED LIVELIHOOD DATA:\n";
    echo "1. 💻 ANGELA SANTOS - Tech Professional Family (Complete modern livelihood profile)\n";
    echo "2. 👴 RICARDO DELA ROSA - Senior Community Leader (Traditional setup) [APPROVED]\n";
    echo "3. 👩‍👧 ISABELLA FERNANDEZ - Young Single Mother (Basic livelihood setup)\n";
    echo "4. 🌾 JOSE CABRERA - Rural Farming Family (Agricultural focus with renewable energy) [REJECTED]\n";
    echo "5. 🏥 DR. CARMEN RODRIGUEZ - Healthcare Professional Family (High-end setup)\n";
    echo "6. ✈️ MICHAEL CRUZ - OFW Family (International income sources) [APPROVED]\n";
    echo "7. 🏞️ LAKANDULA MAGBANUA - Indigenous Traditional Family (Cultural preservation focus)\n";
    echo "8. 🏙️ ALEXANDRA TAN - Urban Professional Couple (Modern city lifestyle)\n";
    echo "9. 👵👴 CORAZON VILLANUEVA - Elderly Couple (Senior-friendly setup) [APPROVED]\n";
    echo "10. 🚀 GABRIEL MORENO - Young Entrepreneur Family (Business-focused setup)\n\n";
    
    echo "📈 ENHANCED FEATURES:\n";
    echo "   • Complete Schema Coverage: All 35 fields in resident_registrations table\n";
    echo "   • Livelihood Sections A-L: Land ownership, housing, energy sources, etc.\n";
    echo "   • Diverse Economic Profiles: From traditional farming to modern tech businesses\n";
    echo "   • Realistic Demographics: Age-appropriate relationships and occupations\n";
    echo "   • Cultural Diversity: Urban, rural, indigenous, and OFW families\n\n";
    
    echo "🎯 VISIBILITY TESTING: Perfect for testing enhanced Tab 3 (Livelihood) visibility!\n";
    echo "📍 Visit: /admin/view-resident-registrations.php to see the improved UI\n\n";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>