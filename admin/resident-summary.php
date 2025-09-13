<?php
session_start();
include '../includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get registration ID from URL
$registration_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$registration_id) {
    header('Location: view-resident-registrations.php');
    exit;
}

// Fetch main registration data
$stmt = $pdo->prepare("SELECT * FROM resident_registrations WHERE id = ?");
$stmt->execute([$registration_id]);
$registration_data = $stmt->fetch();

if (!$registration_data) {
    header('Location: view-resident-registrations.php');
    exit;
}

// Fetch related family members
$family_members = [];
$family_stmt = $pdo->prepare("SELECT * FROM family_members WHERE registration_id = ? ORDER BY id");
$family_stmt->execute([$registration_id]);
$family_members = $family_stmt->fetchAll();

// Fetch family members with disabilities
$family_disabilities = [];
$disabilities_stmt = $pdo->prepare("SELECT * FROM family_disabilities WHERE registration_id = ? ORDER BY id");
$disabilities_stmt->execute([$registration_id]);
$family_disabilities = $disabilities_stmt->fetchAll();

// Fetch family members in organizations
$family_organizations = [];
$organizations_stmt = $pdo->prepare("SELECT * FROM family_organizations WHERE registration_id = ? ORDER BY id");
$organizations_stmt->execute([$registration_id]);
$family_organizations = $organizations_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Summary - <?php echo htmlspecialchars($registration_data['first_name'] . ' ' . $registration_data['last_name']); ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .summary-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .summary-header {
            background: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .summary-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .section-title {
            color: #2e7d32;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .info-item {
            margin-bottom: 1rem;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.3rem;
            font-size: 0.9rem;
        }
        
        .info-value {
            font-size: 1.1rem;
            color: #212529;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 3px solid #4CAF50;
        }
        
        .family-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .family-table th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid #e9ecef;
            color: #2e7d32;
        }
        
        .family-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .family-table tr:hover {
            background: rgba(76, 175, 80, 0.05);
        }
        
        .badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-badge {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-pending {
            background: #cce7ff;
            color: #004085;
        }
        
        .print-btn {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }
        
        .back-btn {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
        }
        
        .actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            justify-content: center;
        }
        
        @media print {
            .actions {
                display: none;
            }
            
            .summary-container {
                padding: 1rem;
                background: white;
            }
            
            .summary-header {
                box-shadow: none;
            }
        }
        
        @media (max-width: 768px) {
            .summary-container {
                padding: 1rem;
            }
            
            .summary-header {
                padding: 1rem;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
                align-items: center;
            }
            
            .print-btn, .back-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="summary-container">
        <div class="summary-header">
            <div>
                <h1>📋 Registration Summary</h1>
                <p><strong>ID:</strong> #<?php echo $registration_data['id']; ?> | 
                   <strong>Submitted:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($registration_data['submitted_at'])); ?> | 
                   <span class="badge status-<?php echo $registration_data['status']; ?>">
                       Status: <?php echo ucfirst($registration_data['status']); ?>
                   </span>
                </p>
            </div>
            <h2><?php echo htmlspecialchars($registration_data['first_name'] . ' ' . $registration_data['last_name']); ?></h2>
        </div>
        
        <!-- Personal Information Section -->
        <div class="summary-section">
            <h3 class="section-title">👤 Personal Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Full Name</div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($registration_data['first_name'] . ' ' . ($registration_data['middle_name'] ?? '') . ' ' . $registration_data['last_name']); ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Age</div>
                    <div class="info-value"><?php echo $registration_data['age'] ?? 'N/A'; ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Gender</div>
                    <div class="info-value"><?php echo $registration_data['gender'] ?? 'N/A'; ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Civil Status</div>
                    <div class="info-value"><?php echo $registration_data['civil_status'] ?? 'N/A'; ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Date of Birth</div>
                    <div class="info-value">
                        <?php echo $registration_data['birth_date'] ? date('F j, Y', strtotime($registration_data['birth_date'])) : 'N/A'; ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Place of Birth</div>
                    <div class="info-value"><?php echo $registration_data['birth_place'] ?? 'N/A'; ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo $registration_data['email'] ?? 'N/A'; ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Contact Number</div>
                    <div class="info-value"><?php echo $registration_data['contact_number'] ?? 'N/A'; ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">House Number</div>
                    <div class="info-value"><?php echo $registration_data['house_number'] ?? 'N/A'; ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Address</div>
                    <div class="info-value"><?php echo $registration_data['address'] ?? 'N/A'; ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Interviewer Name</div>
                    <div class="info-value"><?php echo $registration_data['interviewer'] ?? 'N/A'; ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Interviewer Position</div>
                    <div class="info-value"><?php echo $registration_data['interviewer_title'] ?? 'N/A'; ?></div>
                </div>
            </div>
        </div>
        
        <!-- Livelihood Information Section -->
        <div class="summary-section">
            <h3 class="section-title">🏠 Livelihood Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Land Ownership</div>
                    <div class="info-value">
                        <?php 
                        echo $registration_data['land_ownership'] ?? 'N/A';
                        if ($registration_data['land_ownership'] === 'Iba pa' && $registration_data['land_ownership_other']) {
                            echo ' - ' . htmlspecialchars($registration_data['land_ownership_other']);
                        }
                        ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">House Ownership</div>
                    <div class="info-value">
                        <?php 
                        echo $registration_data['house_ownership'] ?? 'N/A';
                        if ($registration_data['house_ownership'] === 'Iba pa' && $registration_data['house_ownership_other']) {
                            echo ' - ' . htmlspecialchars($registration_data['house_ownership_other']);
                        }
                        ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Agricultural/Farm Land</div>
                    <div class="info-value"><?php echo $registration_data['farmland'] ?? 'N/A'; ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Cooking Energy Source</div>
                    <div class="info-value">
                        <?php 
                        echo $registration_data['cooking_energy'] ?? 'N/A';
                        if ($registration_data['cooking_energy'] === 'Iba pa' && $registration_data['cooking_energy_other']) {
                            echo ' - ' . htmlspecialchars($registration_data['cooking_energy_other']);
                        }
                        ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Toilet Type</div>
                    <div class="info-value">
                        <?php 
                        echo $registration_data['toilet_type'] ?? 'N/A';
                        if ($registration_data['toilet_type'] === 'Iba pa' && $registration_data['toilet_type_other']) {
                            echo ' - ' . htmlspecialchars($registration_data['toilet_type_other']);
                        }
                        ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Electricity Source</div>
                    <div class="info-value">
                        <?php 
                        echo $registration_data['electricity_source'] ?? 'N/A';
                        if ($registration_data['electricity_source'] === 'Iba pa' && $registration_data['electricity_source_other']) {
                            echo ' - ' . htmlspecialchars($registration_data['electricity_source_other']);
                        }
                        ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Water Source</div>
                    <div class="info-value">
                        <?php 
                        echo $registration_data['water_source'] ?? 'N/A';
                        if ($registration_data['water_source'] === 'Iba pa' && $registration_data['water_source_other']) {
                            echo ' - ' . htmlspecialchars($registration_data['water_source_other']);
                        }
                        ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Waste Disposal Method</div>
                    <div class="info-value">
                        <?php 
                        echo $registration_data['waste_disposal'] ?? 'N/A';
                        if ($registration_data['waste_disposal'] === 'Iba pa' && $registration_data['waste_disposal_other']) {
                            echo ' - ' . htmlspecialchars($registration_data['waste_disposal_other']);
                        }
                        ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Household Appliances</div>
                    <div class="info-value">
                        <?php 
                        if ($registration_data['appliances']) {
                            $appliances = explode(',', $registration_data['appliances']);
                            echo implode(', ', array_map('htmlspecialchars', $appliances));
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Transportation</div>
                    <div class="info-value">
                        <?php 
                        if ($registration_data['transportation']) {
                            $transportation = explode(',', $registration_data['transportation']);
                            echo implode(', ', array_map('htmlspecialchars', $transportation));
                            if ($registration_data['transportation_other']) {
                                echo ', ' . htmlspecialchars($registration_data['transportation_other']);
                            }
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Business/Income Sources</div>
                    <div class="info-value">
                        <?php 
                        if ($registration_data['business']) {
                            $business = explode(',', $registration_data['business']);
                            echo implode(', ', array_map('htmlspecialchars', $business));
                            if ($registration_data['business_other']) {
                                echo ', ' . htmlspecialchars($registration_data['business_other']);
                            }
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Contraceptive Methods</div>
                    <div class="info-value">
                        <?php 
                        if ($registration_data['contraceptive']) {
                            $contraceptive = explode(',', $registration_data['contraceptive']);
                            echo implode(', ', array_map('htmlspecialchars', $contraceptive));
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Family Members Section -->
        <div class="summary-section">
            <h3 class="section-title">👨‍👩‍👧‍👦 Family Members (<?php echo count($family_members); ?>)</h3>
            <?php if (!empty($family_members)): ?>
            <div class="table-responsive">
                <table class="family-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Relationship</th>
                            <th>Birth Date</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Civil Status</th>
                            <th>Email</th>
                            <th>Occupation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($family_members as $member): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['full_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($member['relationship'] ?? 'N/A'); ?></td>
                            <td><?php echo $member['birth_date'] ? date('M j, Y', strtotime($member['birth_date'])) : 'N/A'; ?></td>
                            <td><?php echo $member['age'] ?? 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($member['gender'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($member['civil_status'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($member['email'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($member['occupation'] ?? 'N/A'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p>No family members recorded.</p>
            <?php endif; ?>
        </div>
        
        <!-- Disabilities Section -->
        <div class="summary-section">
            <h3 class="section-title">♿ Family Members with Disabilities (<?php echo count($family_disabilities); ?>)</h3>
            <?php if (!empty($family_disabilities)): ?>
            <div class="table-responsive">
                <table class="family-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Disability</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($family_disabilities as $disability): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($disability['name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($disability['disability_type'] ?? 'N/A'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p>No family members with disabilities recorded.</p>
            <?php endif; ?>
        </div>
        
        <!-- Organizations Section -->
        <div class="summary-section">
            <h3 class="section-title">🏢 Family Members in Organizations (<?php echo count($family_organizations); ?>)</h3>
            <?php if (!empty($family_organizations)): ?>
            <div class="table-responsive">
                <table class="family-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Organization</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($family_organizations as $organization): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($organization['name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($organization['organization_type'] ?? 'N/A'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p>No family members in organizations recorded.</p>
            <?php endif; ?>
        </div>
        
        <div class="actions">
            <button class="print-btn" onclick="window.print()">🖨️ Print Summary</button>
            <a href="view-resident-registrations.php" class="back-btn">← Back to Registrations</a>
        </div>
    </div>
</body>
</html>