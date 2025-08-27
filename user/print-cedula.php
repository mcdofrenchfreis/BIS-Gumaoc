<?php
session_start();
include '../includes/db_connect.php';

// Get the certificate request ID from URL parameter
$request_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$request_id) {
    die("Certificate request ID is required.");
}

// Fetch the certificate request data
$stmt = $pdo->prepare("SELECT * FROM certificate_requests WHERE id = ? AND certificate_type = 'CEDULA/CTC'");
$stmt->execute([$request_id]);
$certificate_data = $stmt->fetch();

if (!$certificate_data) {
    die("Certificate request not found or is not a CEDULA/CTC certificate.");
}

// Calculate age from cedula birth date or regular birth date
$birth_date = !empty($certificate_data['cedula_date_of_birth']) ? 
    $certificate_data['cedula_date_of_birth'] : $certificate_data['birth_date'];
$birth_date_obj = new DateTime($birth_date);
$current_date = new DateTime();
$age = $current_date->diff($birth_date_obj)->y;

// Format dates
$formatted_birth_date = date('F j, Y', strtotime($birth_date));
$current_date_formatted = date('F j, Y');
$cedula_year = $certificate_data['cedula_year'] ?? date('Y');
$date_issued = $certificate_data['date_issued'] ?? date('Y-m-d');
$formatted_date_issued = date('F j, Y', strtotime($date_issued));

// Generate certificate number
$certificate_number = 'CTC' . $cedula_year . str_pad($request_id, 8, '0', STR_PAD_LEFT);

// Parse name components
$name_parts = explode(' ', trim($certificate_data['full_name']));
$first_name = $name_parts[0] ?? '';
$middle_name = count($name_parts) > 2 ? $name_parts[1] : '';
$last_name = count($name_parts) > 1 ? end($name_parts) : '';

// Get cedula-specific data
$citizenship = $certificate_data['cedula_citizenship'] ?? $certificate_data['citizenship'] ?? 'Filipino';
$place_of_birth = $certificate_data['cedula_place_of_birth'] ?? $certificate_data['birth_place'] ?? '';
$civil_status = $certificate_data['cedula_civil_status'] ?? $certificate_data['civil_status'] ?? '';
$profession = $certificate_data['profession_occupation'] ?? '';
$place_of_issue = $certificate_data['place_of_issue'] ?? 'San Jose Del Monte City, Bulacan';
$height = $certificate_data['height'] ?? '';
$weight = $certificate_data['weight'] ?? '';

// Tax information
$basic_tax = $certificate_data['basic_community_tax'] ?? 5.00;
$basic_tax_type = $certificate_data['basic_tax_type'] ?? 'voluntary';
$gross_receipts = $certificate_data['gross_receipts_business'] ?? 0.00;
$salaries = $certificate_data['salaries_profession'] ?? 0.00;
$real_property = $certificate_data['income_real_property'] ?? 0.00;
$total_tax = $certificate_data['total_tax'] ?? $basic_tax;
$interest = $certificate_data['interest'] ?? 0.00;
$total_amount = $certificate_data['total_amount_paid'] ?? ($total_tax + $interest);

// Calculate additional taxes
$business_tax = round($gross_receipts / 1000, 2);
$profession_tax = round($salaries / 1000, 2);
$property_tax = round($real_property / 1000, 2);

// Add logging for print actions
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    include '../includes/AdminLogger.php';
    $logger = new AdminLogger($pdo);
    
    if ($certificate_data) {
        $logger->logPrintAction(
            'certificate_request',
            $request_id,
            'cedula_certificate',
            [
                'certificate_type' => 'CEDULA/CTC',
                'applicant_name' => $certificate_data['full_name'],
                'print_timestamp' => date('Y-m-d H:i:s'),
                'certificate_number' => $certificate_number
            ]
        );
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COMMUNITY TAX CERTIFICATE - <?php echo htmlspecialchars($certificate_data['full_name']); ?></title>
    <style>
        @media print {
            @page {
                margin: 8mm;
                size: A4 landscape;
            }
            body { 
                margin: 0; 
                padding: 0;
                background: white !important;
                font-size: 10px;
            }
            .no-print { display: none !important; }
            .cedula-container {
                max-width: 100% !important;
                width: 100% !important;
                padding: 5mm !important;
                margin: 0 !important;
                box-shadow: none !important;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 8px;
            background: #f5f5f5;
            font-size: 10px;
            line-height: 1.1;
        }
        
        .cedula-container {
            width: 100%;
            max-width: 270mm;
            height: auto;
            min-height: 185mm;
            margin: 0 auto;
            background: white;
            padding: 6mm;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            position: relative;
            border: 2px solid #000;
            box-sizing: border-box;
        }
        
        .cedula-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 8px;
            position: relative;
        }
        
        .bir-form {
            font-size: 8px;
            position: absolute;
            top: 0;
            left: 0;
        }
        
        .header-title {
            font-size: 12px;
            font-weight: bold;
            margin: 2px 0;
            letter-spacing: 1px;
        }
        
        .header-subtitle {
            background: #000;
            color: white;
            padding: 3px 12px;
            display: inline-block;
            font-size: 10px;
            font-weight: bold;
            margin: 2px 0;
        }
        
        .cert-number {
            position: absolute;
            top: 0;
            right: 0;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        
        .taxpayer-copy {
            position: absolute;
            top: 25px;
            right: 0;
            font-size: 8px;
            text-transform: uppercase;
        }
        
        .main-content {
            display: flex;
            gap: 10px;
            width: 100%;
        }
        
        .left-section {
            flex: 2.3;
            min-width: 0;
        }
        
        .right-section {
            flex: 0.7;
            border-left: 1px solid #000;
            padding-left: 8px;
            min-width: 0;
        }
        
        .form-row {
            display: flex;
            align-items: center;
            margin-bottom: 4px;
            min-height: 16px;
            flex-wrap: wrap;
        }
        
        .form-label {
            font-size: 8px;
            min-width: 60px;
            text-transform: uppercase;
            margin-right: 2px;
        }
        
        .form-value {
            border-bottom: 1px solid #000;
            min-width: 70px;
            padding: 1px 2px;
            margin: 0 2px;
            font-size: 9px;
            font-weight: bold;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .wide-value {
            flex: 1;
            max-width: none;
            min-width: 0;
        }
        
        .checkbox-group {
            display: flex;
            gap: 10px;
            margin: 8px 0;
            flex-wrap: wrap;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 2px;
            font-size: 8px;
            margin-right: 6px;
        }
        
        .checkbox {
            width: 10px;
            height: 10px;
            border: 1px solid #000;
            display: inline-block;
            text-align: center;
            line-height: 8px;
            font-size: 8px;
        }
        
        .checked {
            background: #000;
            color: white;
        }
        
        .tax-section {
            border: 1px solid #000;
            padding: 6px;
            margin: 8px 0;
        }
        
        .tax-title {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 6px;
            text-transform: uppercase;
            line-height: 1.1;
        }
        
        .tax-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3px;
            font-size: 8px;
            line-height: 1.1;
        }
        
        .amount-box {
            border: 1px solid #000;
            width: 50px;
            height: 16px;
            text-align: center;
            line-height: 14px;
            font-weight: bold;
            font-size: 8px;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            gap: 8px;
        }
        
        .signature-box {
            text-align: center;
            width: 120px;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            height: 30px;
            margin-bottom: 2px;
        }
        
        .thumbprint {
            width: 50px;
            height: 50px;
            border: 1px solid #000;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 6px;
            text-align: center;
            line-height: 1;
        }
        
        .date-issued {
            position: absolute;
            bottom: 10px;
            left: 10px;
            font-size: 10px;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2e7d32;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        
        .print-button:hover {
            background: #1b5e20;
        }
        
        @media screen {
            .cedula-container {
                transform: scale(0.85);
                transform-origin: top center;
                margin: 15px auto;
            }
        }
        
        @media screen and (max-width: 1200px) {
            .cedula-container {
                transform: scale(0.75);
            }
        }
        
        @media print {
            .cedula-container {
                transform: none !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .main-content {
                gap: 8px !important;
            }
            .left-section {
                flex: 2.4 !important;
            }
            .right-section {
                flex: 0.6 !important;
                padding-left: 6px !important;
            }
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Print Certificate</button>
    
    <div class="cedula-container">
        <div class="cedula-header">
            <div class="bir-form">BIR FORM 0018 (March 2009)</div>
            <div class="header-title">COMMUNITY TAX CERTIFICATE</div>
            <div class="header-subtitle">INDIVIDUAL</div>
            <div class="cert-number"><?php echo $certificate_number; ?></div>
            <div class="taxpayer-copy">TAXPAYER'S COPY</div>
        </div>
        
        <div class="main-content">
            <div class="left-section">
                <div class="form-row">
                    <span class="form-label">YEAR</span>
                    <span class="form-value"><?php echo $cedula_year; ?></span>
                    <span class="form-label">PLACE OF ISSUE (City/Mun./Prov.)</span>
                    <span class="form-value wide-value"><?php echo htmlspecialchars($place_of_issue); ?></span>
                    <span class="form-label">DATE ISSUED</span>
                    <span class="form-value"><?php echo $formatted_date_issued; ?></span>
                </div>
                
                <div class="form-row">
                    <span class="form-label">NAME (SURNAME)</span>
                    <span class="form-value wide-value"><?php echo htmlspecialchars($last_name); ?></span>
                    <span class="form-label">(FIRST)</span>
                    <span class="form-value wide-value"><?php echo htmlspecialchars($first_name); ?></span>
                    <span class="form-label">(MIDDLE)</span>
                    <span class="form-value"><?php echo htmlspecialchars($middle_name); ?></span>
                    <span class="form-label">TIN (if any):</span>
                    <div style="display: flex; gap: 3px;">
                        <span class="form-value" style="width: 30px;"></span>
                        <span class="form-value" style="width: 30px;"></span>
                        <span class="form-value" style="width: 30px;"></span>
                        <span class="form-value" style="width: 30px;"></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <span class="form-label">ADDRESS</span>
                    <span class="form-value wide-value"><?php echo htmlspecialchars($certificate_data['address']); ?></span>
                    <span class="form-label">SEX:</span>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <span class="checkbox <?php echo (strtolower($certificate_data['gender']) === 'male') ? 'checked' : ''; ?>">
                                <?php echo (strtolower($certificate_data['gender']) === 'male') ? '✓' : ''; ?>
                            </span>
                            <span>1. MALE</span>
                        </div>
                        <div class="checkbox-item">
                            <span class="checkbox <?php echo (strtolower($certificate_data['gender']) === 'female') ? 'checked' : ''; ?>">
                                <?php echo (strtolower($certificate_data['gender']) === 'female') ? '✓' : ''; ?>
                            </span>
                            <span>2. FEMALE</span>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <span class="form-label">CITIZENSHIP</span>
                    <span class="form-value"><?php echo htmlspecialchars($citizenship); ?></span>
                    <span class="form-label">ICR NO. (if an Alien)</span>
                    <span class="form-value"></span>
                    <span class="form-label">PLACE OF BIRTH</span>
                    <span class="form-value wide-value"><?php echo htmlspecialchars($place_of_birth); ?></span>
                    <div style="display: flex; align-items: center; gap: 3px; margin-left: auto;">
                        <span class="form-label">HEIGHT</span>
                        <span class="form-value"><?php echo $height ? $height . ' cm' : ''; ?></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <span class="form-label">CIVIL STATUS</span>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <span class="checkbox <?php echo (strtolower($civil_status) === 'single') ? 'checked' : ''; ?>">
                                <?php echo (strtolower($civil_status) === 'single') ? '✓' : ''; ?>
                            </span>
                            <span>1. Single</span>
                        </div>
                        <div class="checkbox-item">
                            <span class="checkbox <?php echo (strtolower($civil_status) === 'married') ? 'checked' : ''; ?>">
                                <?php echo (strtolower($civil_status) === 'married') ? '✓' : ''; ?>
                            </span>
                            <span>2. Married</span>
                        </div>
                        <div class="checkbox-item">
                            <span class="checkbox <?php echo (in_array(strtolower($civil_status), ['widow', 'widower'])) ? 'checked' : ''; ?>">
                                <?php echo (in_array(strtolower($civil_status), ['widow', 'widower'])) ? '✓' : ''; ?>
                            </span>
                            <span>3. Widow/Widower/Legally Separated</span>
                        </div>
                        <div class="checkbox-item">
                            <span class="checkbox <?php echo (strtolower($civil_status) === 'divorced') ? 'checked' : ''; ?>">
                                <?php echo (strtolower($civil_status) === 'divorced') ? '✓' : ''; ?>
                            </span>
                            <span>4. Divorced</span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 15px; margin-left: auto;">
                        <div style="display: flex; align-items: center; gap: 3px;">
                            <span class="form-label">DATE OF BIRTH</span>
                            <span class="form-value"><?php echo $formatted_birth_date; ?></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 3px;">
                            <span class="form-label">WEIGHT</span>
                            <span class="form-value"><?php echo $weight ? $weight . ' kg' : ''; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <span class="form-label">PROFESSION/OCCUPATION/BUSINESS</span>
                    <span class="form-value wide-value"><?php echo htmlspecialchars($profession); ?></span>
                    <span class="form-label">TAXABLE AMOUNT</span>
                    <span class="form-value"><?php echo number_format($total_amount, 2); ?></span>
                    <span class="form-label">COMMUNITY TAX DUE</span>
                    <span class="form-value">₱</span>
                </div>
                
                <div class="tax-section">
                    <div class="tax-title">A. BASIC COMMUNITY TAX (₱5.00) Voluntary or Exempted (₱1.00)</div>
                    <div class="tax-row">
                        <span></span>
                        <div class="amount-box">₱ <?php echo number_format($basic_tax, 2); ?></div>
                    </div>
                    
                    <div class="tax-title" style="margin-top: 15px;">B. ADDITIONAL COMMUNITY TAX (tax not to exceed ₱5,000.00)</div>
                    
                    <div class="tax-row">
                        <span>1. GROSS RECEIPTS OR EARNINGS DERIVED FROM BUSINESS DURING THE PRECEDING YEAR (₱1.00 for every ₱1,000)</span>
                        <div class="amount-box">₱ <?php echo number_format($business_tax, 2); ?></div>
                    </div>
                    
                    <div class="tax-row">
                        <span>2. SALARIES OR GROSS RECEIPT OR EARNINGS DERIVED FROM EXERCISE OF PROFESSION OR PURSUIT OF ANY OCCUPATION (₱1.00 for every ₱1,000)</span>
                        <div class="amount-box">₱ <?php echo number_format($profession_tax, 2); ?></div>
                    </div>
                    
                    <div class="tax-row">
                        <span>3. INCOME FROM REAL PROPERTY (₱1.00 for every ₱1,000)</span>
                        <div class="amount-box">₱ <?php echo number_format($property_tax, 2); ?></div>
                    </div>
                </div>
                
                <div class="signature-section">
                    <div class="signature-box">
                        <div class="thumbprint">
                            Right Thumb<br>Print
                        </div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div style="font-size: 10px;">TAXPAYER'S SIGNATURE</div>
                    </div>
                </div>
            </div>
            
            <div class="right-section">
                <div class="tax-row">
                    <span style="font-weight: bold;">TOTAL</span>
                    <div class="amount-box">₱ <?php echo number_format($total_tax, 2); ?></div>
                </div>
                
                <div class="tax-row" style="margin-top: 15px;">
                    <span style="font-weight: bold;">INTEREST</span>
                    <div class="amount-box">₱ <?php echo number_format($interest, 2); ?></div>
                </div>
                
                <div class="tax-row" style="margin-top: 15px;">
                    <span style="font-weight: bold;">TOTAL AMOUNT PAID</span>
                    <div class="amount-box">₱ <?php echo number_format($total_amount, 2); ?></div>
                </div>
                
                <div style="margin-top: 20px; font-size: 10px; text-align: center;">
                    <div>(in words)</div>
                    <div style="border-bottom: 1px solid #000; padding: 5px; margin: 10px 0; font-weight: bold;">
                        <?php 
                        // Convert amount to words (basic implementation)
                        $amount_words = '';
                        if ($total_amount > 0) {
                            $peso_amount = floor($total_amount);
                            $centavo_amount = round(($total_amount - $peso_amount) * 100);
                            
                            if ($peso_amount == 5 && $centavo_amount == 0) {
                                $amount_words = 'Five Pesos Only';
                            } else {
                                $amount_words = number_format($total_amount, 2) . ' Pesos';
                            }
                        }
                        echo $amount_words;
                        ?>
                    </div>
                </div>
                
                <div style="margin-top: 30px; text-align: center;">
                    <div style="border-bottom: 1px solid #000; padding: 20px 5px; margin-bottom: 5px;"></div>
                    <div style="font-size: 10px; font-weight: bold;">MUNICIPAL TREASURER</div>
                </div>
            </div>
        </div>
        
        <div class="date-issued">
            *01-01-2002
        </div>
    </div>
    
    <div class="no-print" style="text-align: center; margin-top: 20px; padding: 20px;">
        <button onclick="window.print()" style="background: #2e7d32; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-right: 10px;">
            🖨️ Print Certificate
        </button>
        <button onclick="window.close()" style="background: #666; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
            ✖️ Close
        </button>
    </div>
</body>
</html>