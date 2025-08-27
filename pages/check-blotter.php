<?php
session_start();
header('Content-Type: application/json');

// Include database connection
include '../includes/db_connect.php';

// Helper function to get common Filipino nicknames
function getNickname($firstName) {
    $firstName = strtolower(trim($firstName));
    
    // Common Filipino nickname mappings
    $nicknames = [
        'maria' => ['maria', 'mary', 'marie', 'may'],
        'jose' => ['jose', 'joey', 'joe', 'pepe'],
        'juan' => ['juan', 'john', 'johnny'],
        'antonio' => ['antonio', 'tony', 'anton'],
        'francisco' => ['francisco', 'frank', 'francis', 'kiko'],
        'manuel' => ['manuel', 'manny', 'manolo'],
        'pedro' => ['pedro', 'pete', 'peter'],
        'miguel' => ['miguel', 'mike', 'michael'],
        'rafael' => ['rafael', 'rafa', 'ralph'],
        'ricardo' => ['ricardo', 'ricky', 'rick'],
        'roberto' => ['roberto', 'robert', 'bob', 'bobby'],
        'eduardo' => ['eduardo', 'eddie', 'ed'],
        'ferdinand' => ['ferdinand', 'ferdie', 'fred'],
        'rodolfo' => ['rodolfo', 'rudy', 'rudolf'],
        'rogelio' => ['rogelio', 'roger', 'rogie'],
        'salvador' => ['salvador', 'sal', 'sally'],
        'anastacia' => ['anastacia', 'stacy', 'ana'],
        'elizabeth' => ['elizabeth', 'liz', 'beth', 'betty'],
        'catherine' => ['catherine', 'cathy', 'kate'],
        'margaret' => ['margaret', 'maggie', 'meg'],
        'patricia' => ['patricia', 'patty', 'pat'],
        'cristina' => ['cristina', 'christina', 'tina', 'chris'],
        'rosario' => ['rosario', 'rosie', 'rose'],
        'remedios' => ['remedios', 'remedy', 'medy'],
        'esperanza' => ['esperanza', 'hope', 'esper'],
        'concepcion' => ['concepcion', 'connie', 'conception'],
        'yvan' => ['yvan', 'ivan', 'van'], // For the test case
    ];
    
    // Look for exact match first
    foreach ($nicknames as $formal => $variations) {
        if (in_array($firstName, $variations)) {
            return $formal; // Return the formal name
        }
    }
    
    // If no nickname found, return original
    return $firstName;
}

// Check if this is an AJAX request for blotter checking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'check_blotter') {
    
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    
    // Validate required fields
    if (empty($first_name) || empty($last_name)) {
        echo json_encode([
            'has_unresolved_issues' => false,
            'message' => 'Insufficient name data for checking'
        ]);
        exit;
    }
    
    try {
        // Build the full name variations to check against - Enhanced matching
        $full_name_variations = [
            // Standard full name formats
            trim($first_name . ' ' . $last_name),
            trim($first_name . ' ' . $middle_name . ' ' . $last_name),
            
            // Last name first formats
            trim($last_name . ', ' . $first_name),
            trim($last_name . ', ' . $first_name . ' ' . $middle_name),
            
            // With middle initial
            trim($first_name . ' ' . substr($middle_name, 0, 1) . '. ' . $last_name),
            trim($last_name . ', ' . $first_name . ' ' . substr($middle_name, 0, 1) . '.'),
            
            // Nickname/shortened first name variations (common in Philippines)
            trim(getNickname($first_name) . ' ' . $last_name),
            trim(getNickname($first_name) . ' ' . $middle_name . ' ' . $last_name),
            trim($last_name . ', ' . getNickname($first_name)),
            
            // Without middle name
            trim($last_name . ' ' . $first_name),
            
            // Handle "Jr.", "Sr.", "III" suffixes in last names
            trim($first_name . ' ' . $last_name . ' Jr.'),
            trim($first_name . ' ' . $last_name . ' Sr.'),
            trim($first_name . ' ' . $last_name . ' III'),
        ];
        
        // Add variations with different spacing and punctuation
        $additional_variations = [];
        foreach ($full_name_variations as $name) {
            // Remove extra spaces
            $additional_variations[] = preg_replace('/\s+/', ' ', $name);
            // Remove punctuation
            $additional_variations[] = str_replace(['.', ','], '', $name);
        }
        
        $full_name_variations = array_merge($full_name_variations, $additional_variations);
        
        // Remove empty variations and duplicates, ensure minimum length
        $full_name_variations = array_unique(array_filter($full_name_variations, function($name) {
            return !empty(trim($name)) && strlen(trim($name)) > 2; // Reduced from 3 to 2 for shorter names
        }));
        
        // Log the variations being checked for debugging
        error_log("Checking blotter for name variations: " . json_encode($full_name_variations));
        
        // Check for unresolved blotter records using multiple name patterns - Enhanced
        $placeholders = str_repeat('?,', count($full_name_variations) * 2);
        $placeholders = rtrim($placeholders, ',');
        
        // Enhanced SQL query with case-insensitive matching and LIKE patterns
        $sql = "
            SELECT 
                id, blotter_number, incident_type, complainant_name, respondent_name, 
                incident_date, location, description, classification, status, created_at
            FROM barangay_blotter 
            WHERE (
                (LOWER(complainant_name) IN (" . implode(',', array_fill(0, count($full_name_variations), 'LOWER(?)')). "))
                OR (LOWER(respondent_name) IN (" . implode(',', array_fill(0, count($full_name_variations), 'LOWER(?)')). "))
                OR (" . implode(' OR ', array_map(function($i) { 
                    return 'LOWER(complainant_name) LIKE LOWER(?) OR LOWER(respondent_name) LIKE LOWER(?)';
                }, range(1, count($full_name_variations)))) . ")
            )
            AND status IN ('filed', 'under_investigation', 'mediation')
            AND status NOT IN ('resolved', 'dismissed', 'referred_to_court')
            ORDER BY incident_date DESC, created_at DESC
            LIMIT 5
        ";
        
        // Prepare parameters for exact matches and LIKE matches
        $params = [];
        // Add lowercase versions for exact IN matches
        foreach ($full_name_variations as $name) {
            $params[] = strtolower(trim($name));
        }
        foreach ($full_name_variations as $name) {
            $params[] = strtolower(trim($name));
        }
        // Add LIKE patterns (with % wildcards)
        foreach ($full_name_variations as $name) {
            $params[] = '%' . strtolower(trim($name)) . '%';
            $params[] = '%' . strtolower(trim($name)) . '%';
        }
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);
        
        if (!$result) {
            error_log("Blotter check SQL execution failed: " . json_encode($stmt->errorInfo()));
            throw new PDOException("Query execution failed");
        }
        
        $blotter_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Log the results for debugging
        error_log("Blotter check found " . count($blotter_records) . " records for: $first_name $middle_name $last_name");
        
        if (!empty($blotter_records)) {
            // Found unresolved issues
            $response = [
                'has_unresolved_issues' => true,
                'message' => 'Unresolved barangay issues found',
                'details' => [
                    'total_cases' => count($blotter_records),
                    'cases' => []
                ]
            ];
            
            // Add case details (limited for security)
            foreach ($blotter_records as $record) {
                $response['details']['cases'][] = [
                    'blotter_number' => $record['blotter_number'],
                    'incident_type' => ucfirst($record['incident_type']),
                    'status' => ucfirst(str_replace('_', ' ', $record['status'])),
                    'incident_date' => date('M j, Y', strtotime($record['incident_date'])),
                    'classification' => ucfirst($record['classification']),
                    'role' => (stripos($record['complainant_name'], $last_name) !== false) ? 'complainant' : 'respondent'
                ];
            }
            
            echo json_encode($response);
        } else {
            // No unresolved issues found
            echo json_encode([
                'has_unresolved_issues' => false,
                'message' => 'No unresolved barangay issues found'
            ]);
        }
        
    } catch (PDOException $e) {
        // Database error - don't block registration, just log
        error_log("Blotter check error: " . $e->getMessage());
        echo json_encode([
            'has_unresolved_issues' => false,
            'message' => 'Blotter check service temporarily unavailable',
            'error' => true
        ]);
    }
    
} else {
    // Invalid request
    http_response_code(400);
    echo json_encode([
        'has_unresolved_issues' => false,
        'message' => 'Invalid request method or action',
        'error' => true
    ]);
}
?>