<?php
session_start();
include '../includes/db_connect.php';

// Get the certificate request ID from URL parameter
$request_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$request_id) {
    die("Certificate request ID is required.");
}

// Fetch the certificate request data
$stmt = $pdo->prepare("SELECT * FROM certificate_requests WHERE id = ? AND certificate_type = 'BRGY. INDIGENCY'");
$stmt->execute([$request_id]);
$certificate_data = $stmt->fetch();

if (!$certificate_data) {
    die("Certificate request not found or is not a BRGY. INDIGENCY certificate.");
}

// Calculate age from birth date
$birth_date = new DateTime($certificate_data['birth_date']);
$current_date = new DateTime();
$age = $current_date->diff($birth_date)->y;

// Format dates
$formatted_birth_date = date('F j, Y', strtotime($certificate_data['birth_date']));
$current_date_formatted = date('F j, Y');
$valid_until = date('F j, Y', strtotime('+1 year'));

// Generate certificate number
$certificate_number = 'IND-' . str_pad($request_id, 5, '0', STR_PAD_LEFT) . '-' . date('Y');

// Add logging for print actions
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    include '../includes/AdminLogger.php';
    $logger = new AdminLogger($pdo);
    
    if ($certificate_data) {
        $logger->logPrintAction(
            'certificate_request',
            $request_id,
            'indigency_certificate',
            [
                'certificate_type' => 'BRGY. INDIGENCY',
                'applicant_name' => $certificate_data['full_name'],
                'print_timestamp' => date('Y-m-d H:i:s'),
                'certificate_number' => $certificate_number ?? 'auto-generated'
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
    <title>BRGY. INDIGENCY Certificate - <?php echo htmlspecialchars($certificate_data['full_name']); ?></title>
    <style>
        @media print {
            @page {
                margin: 0;
                size: A4 portrait;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            body { 
                margin: 0; 
                padding: 0;
                background: white !important;
                font-family: 'Times New Roman', serif !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .no-print { 
                display: none !important; 
            }
            
            .certificate-container { 
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
                page-break-before: avoid !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 210mm !important;
                height: 297mm !important;
                background: url('../assets/images/forms/barangay-letterhead-bg.png') no-repeat center center !important;
                background-size: cover !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                box-shadow: none !important;
                position: relative !important;
                overflow: hidden !important;
            }
            
            .certificate-content {
                padding: 250px 60px 60px 60px !important;
                height: 100% !important;
                position: relative !important;
                z-index: 2 !important;
            }
            
            .main-title {
                font-size: 32px !important;
                margin-bottom: 20px !important;
                letter-spacing: 3px !important;
                color: #C2944D !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .to-whom {
                font-weight: bold !important;
                font-size: 14px !important;
                margin-bottom: 15px !important;
            }
            
            .content-with-photo {
                display: flex !important;
                align-items: flex-start !important;
                gap: 25px !important;
                margin-bottom: 20px !important;
            }
            
            .left-content {
                flex: 1 !important;
            }
            
            .main-text {
                font-size: 14px !important;
                line-height: 1.4 !important;
                text-align: justify !important;
            }
            
            .certification-text {
                margin-bottom: 18px !important;
                text-align: justify !important;
                font-size: 13px !important;
                line-height: 1.4 !important;
                max-width: 100% !important;
            }
            
            .photo-placeholder {
                width: 85px !important;
                height: 85px !important;
                border: 2px solid #000 !important;
                font-size: 11px !important;
                margin-left: 20px !important;
            }
            
            .bottom-sections-container {
                display: flex !important;
                justify-content: space-between !important;
                align-items: flex-start !important;
                margin-top: 15px !important;
                page-break-inside: avoid !important;
            }
            
            .left-section {
                width: 45% !important;
                display: flex !important;
                flex-direction: column !important;
                gap: 8px !important;
            }
            
            .right-section {
                width: 50% !important;
                display: flex !important;
                flex-direction: column !important;
                gap: 12px !important;
                align-items: center !important;
                margin-top: 20px !important;
            }
            
            .officer-section {
                width: 100% !important;
                margin-bottom: 15px !important;
            }
            
            .officer-label {
                font-weight: bold !important;
                margin-bottom: 2px !important;
                font-size: 14px !important;
            }

            .officer-name {
                font-weight: bold !important;
                text-decoration: underline !important;
                margin-bottom: 2px !important;
                font-size: 14px !important;
            }

            .officer-sub-label {
                font-size: 13px !important;
                margin-bottom: 0 !important;
            }
            
            .signature-applicant-section {
                width: 100% !important;
                text-align: center !important;
                position: relative !important;
                margin-bottom: 15px !important;
                margin-top: 15px !important;
            }
            
            .signature-line-applicant {
                border-bottom: 2px solid #000 !important;
                width: 210px !important;
                height: 1px !important;
                margin: 0 auto 7px auto !important;
            }

            .signature-applicant {
                font-weight: bold !important;
                font-size: 13px !important;
            }
            
            .thumb-mark-circle {
                width: 78px !important;
                height: 78px !important;
                font-size: 10px !important;
            }
            
            .ctc-details-section {
                width: 140% !important;
                page-break-inside: avoid !important;
            }
            
            .ctc-detail-item {
                display: flex !important;
                align-items: center !important;
                margin-bottom: 5px !important;
                page-break-inside: avoid !important;
            }
            
            .ctc-detail-label {
                font-weight: bold !important;
                margin-right: 6px !important;
                min-width: 80px !important;
                font-size: 12px !important;
                white-space: nowrap !important;
            }
            
            .ctc-detail-value {
                flex: 1 !important;
                padding-bottom: 1px !important;
                height: 17px !important;
                min-width: 280px !important;
                white-space: nowrap !important;
                overflow: visible !important;
                font-size: 11px !important;
            }
            
            .main-title {
                font-size: 32px !important;
                margin-bottom: 20px !important;
                letter-spacing: 3px !important;
                color: #C2944D !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .signature-line,
            .signature-line-applicant,
            .thumb-mark-circle {
                border-color: #000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .photo-placeholder {
                border: 2px solid #000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .signature-section {
                page-break-inside: avoid !important;
            }
            
            .thumb-marks-section {
                page-break-inside: avoid !important;
            }
            
            .signature-box {
                page-break-inside: avoid !important;
            }
            
            .date-validity-thumb-section {
                display: block !important;
                margin: 0 !important;
                position: relative !important;
            }
            
            .date-validity-row {
                width: 100% !important;
                font-size: 12px !important;
                font-weight: bold !important;
                margin-top: 3px !important;
                position: relative !important;
                top: -2px !important;
            }
            
            .date-validity-row div {
                margin-bottom: 4px !important;
            }
            
            .thumb-marks-section {
                width: 100% !important;
                display: flex !important;
                flex-direction: row !important;
                gap: 28px !important;
                justify-content: center !important;
                align-items: center !important;
            }
            
            .thumb-mark-box {
                text-align: center;
                width: 105px;
                flex-shrink: 0;
            }
            
            .thumb-mark-circle {
                width: 95px;
                height: 95px;
                border: 2px solid #000;
                margin: 0 auto 7px auto;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 11px;
                font-weight: bold;
                line-height: 1.1;
            }
            
            .thumb-mark-label {
                font-size: 11px;
                font-weight: bold;
            }
            
            .signature-section {
                margin-top: 0 !important;
                display: block !important;
                width: 100% !important;
                position: relative !important;
                top: -3px !important;
            }
            
            .ctc-details-section {
                width: 100% !important;
            }
            
            .ctc-detail-item {
                display: flex !important;
                align-items: center !important;
                margin-bottom: 5px !important;
            }
            
            .ctc-detail-label {
                font-weight: bold;
                margin-right: 6px;
                min-width: 80px;
                font-size: 12px;
            }
            
            .ctc-detail-value {
                flex: 1 !important;
                padding-bottom: 1px !important;
                height: 17px !important;
                font-size: 11px !important;
            }
            
            .signature-line {
                border-bottom: 2px solid #000 !important;
                margin-bottom: 4px !important;
                height: 1px !important;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            line-height: 1.6;
            color: #000;
            background: #f5f5f5;
        }
        
        .certificate-container {
            width: 210mm;
            height: 297mm;
            margin: 20px auto;
            background: url('../assets/images/forms/barangay-letterhead-bg.png') no-repeat center center;
            background-size: cover;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            /* Fallback background color in case image doesn't load */
            background-color: #f9f9f9;
            page-break-inside: avoid;
            page-break-after: avoid;
            page-break-before: avoid;
        }
        
        .certificate-content {
  position: relative;
  z-index: 2;
  padding: 250px 60px 60px 60px; /* matching barangay clearance exactly */
  height: 100%;
}
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .barangay-name {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 3px;
            color: #1a4d80;
            letter-spacing: 1px;
        }
        
        .municipality {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
            color: #1a4d80;
        }
        
        .province {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #1a4d80;
        }
        
        .certificate-title {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 8px;
            color: #1a4d80;
            letter-spacing: 1px;
        }
        
        .certificate-subtitle {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #1a4d80;
        }
        
        .main-title {
            font-size: 32px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
            color: #C2944D;
            letter-spacing: 3px;
            text-align: center;
        }
        
        .certificate-number {
            text-align: right;
            font-size: 12px;
            margin-bottom: 20px;
            color: #666;
        }
        
        .content {
    flex: 1;
    font-size: 14px;
    line-height: 1.6;
    padding-top: 0;
    margin-bottom: 15px; /* add bottom margin for better spacing */
}
        
        .content-with-photo {
            display: flex;
            align-items: flex-start;
            gap: 25px;
            margin-bottom: 20px;
        }
        
        .left-content {
            flex: 1;
        }
        
        .main-text {
            font-size: 14px;
            line-height: 1.4;
            text-align: justify;
        }
        
        .certification-text {
            margin-bottom: 18px;
            text-align: justify;
            font-size: 13px;
            line-height: 1.4;
            max-width: 100%; /* changed from 500px to 100% to match main-text width */
        }
        
        .to-whom {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .photo-placeholder {
            width: 85px;
            height: 85px;
            border: 2px solid #000;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            color: #000;
            flex-shrink: 0;
            float: right;
            margin: 0 0 10px 20px;
        }


        
        .person-details {
            margin: 15px 0;
            padding-left: 30px;
        }
        
        .person-details p {
            margin-bottom: 6px;
        }
        
        .thumb-marks-section {
            margin: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .bottom-sections-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 15px; /* reduced from 20px to 15px */
        }
        
        .left-section {
            width: 45%; /* back to barangay clearance proportions */
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .right-section {
            width: 50%; /* back to barangay clearance proportions */
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: center;
            margin-top: 20px; /* reduced from 33px to 20px */
        }
        
        .officer-section {
            width: 100%;
            margin-bottom: 15px;
        }
        
        .officer-label {
            font-weight: bold;
            margin-bottom: 2px;
            font-size: 14px; /* increased from 13px to 14px */
        }
        
        .officer-name {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 2px;
            font-size: 14px; /* increased from 13px to 14px */
        }
        
        .officer-sub-label {
            font-size: 13px; /* increased from 12px to 13px */
            margin-bottom: 0;
        }
        
        .signature-applicant-section {
            width: 100%;
            text-align: center;
            position: relative;
            margin-bottom: 15px;
            margin-top: 15px;
        }
        
        .signature-line-applicant {
            border-bottom: 2px solid #000;
            width: 210px;
            height: 1px;
            margin: 0 auto 7px auto;
        }
        
        .signature-applicant {
            font-weight: bold;
            font-size: 13px; /* increased from 12px to 13px */
        }
        
        .date-validity-thumb-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin: 5px 0; /* reduced from 15px to 5px */
            position: relative;
        }
        
        .date-validity-row {
            width: 60%; /* increased from 41% to 60% */
            font-size: 13px; /* increased from 12px to 13px */
            font-weight: bold;
            margin-top: 3px; /* reduced from 8px to 3px */
            position: relative;
            top: 0px;
        }
        
        .date-validity-row div {
            margin-bottom: 3px; /* reduced from 5px to 3px */
        }
        
        .thumb-marks-section {
            width: 38%; /* reduced from 56% to 38% to accommodate larger date section */
            display: flex;
            flex-direction: row;
            gap: 28px;
            justify-content: center;
            align-items: center;
        }
        
        .thumb-mark-box {
            text-align: center;
            width: 105px;
            flex-shrink: 0;
        }
        
        .thumb-mark-circle {
            width: 95px;
            height: 95px;
            border: 2px solid #000;
            margin: 0 auto 7px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
            line-height: 1.1;
        }
        
        .thumb-mark-label {
            font-size: 11px;
            font-weight: bold;
        }
        
        .signature-section {
            margin-top: 5px; /* reduced from 15px to 5px */
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            position: relative;
            top: 0px;
        }
        
        .ctc-details-section {
            width: 46%;
        }
        
        .ctc-detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .ctc-detail-label {
            font-weight: bold;
            margin-right: 12px;
            min-width: 92px;
            font-size: 13px; /* increased from 12px to 13px */
        }
        
        .ctc-detail-value {
            flex: 1;
            padding-bottom: 1px;
            height: 17px;
        }
        
        .signature-box {
            text-align: center;
            width: 235px;
        }
        
        .signature-line {
            border-bottom: 2px solid #000;
            margin-bottom: 4px;
            height: 1px;
        }
        
        .signature-name {
            font-weight: bold;
            font-size: 13px;
            text-decoration: underline;
        }
        
        .signature-title {
            font-size: 12px;
            margin-top: 2px;
        }
        
        .signature-box {
            margin-top: 30px !important;
        }
        
        .signature-name {
            text-decoration: none !important;
        }
        
        .signature-box {
            width: 280px !important;
        }
        
        .signature-line {
            width: 250px !important;
        }
        
        .signature-name {
            font-size: 14px !important;
        }
        
        .signature-line {
            width: 250px !important;
        }
        
        .print-controls {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .print-btn {
            background: #1a4d80;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin: 0 10px;
        }
        
        .print-btn:hover {
            background: #0d2b4a;
        }
        
        .back-btn {
            background: #6c757d;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 16px;
            margin: 0 10px;
        }
        
        .back-btn:hover {
            background: #545b62;
        }
        
        .official-seal {
            position: absolute;
            bottom: 80px;
            right: 80px;
            width: 70px;
            height: 70px;
            border: 2px solid #1a4d80;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            font-weight: bold;
            color: #1a4d80;
            background: rgba(255, 255, 255, 0.9);
            text-align: center;
            line-height: 1.2;
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(255, 0, 0, 0.08);
            font-weight: bold;
            z-index: 1;
            pointer-events: none;
        }
        
        .not-valid-notice {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            margin-top: 10px;
            color: #d32f2f;
        }
        
    </style>
</head>
<body>
    <div class="no-print print-controls">
        <button class="print-btn" onclick="window.print()">🖨️ Print Certificate</button>
        <a href="../admin/view-certificate-requests.php" class="back-btn">← Back to Admin</a>
        <p style="margin-top: 10px; color: #666;">
            Certificate #<?php echo $certificate_number; ?> | 
            Request ID: <?php echo str_pad($request_id, 5, '0', STR_PAD_LEFT); ?>
        </p>
    </div>

    <div class="certificate-container">
        
        <div class="certificate-content">

            
            <div class="main-title">
                CERTIFICATION OF INDIGENCY
            </div>
            
            <div class="content-with-photo">
                <div class="left-content">
                    <div class="to-whom">
                        TO WHOM IT MAY CONCERN:
                    </div>
                    
                    <div class="main-text">
                        <p>This is to certify that <strong><?php echo htmlspecialchars($certificate_data['full_name']); ?></strong>, 
                        <?php echo $age; ?> years old, with Address no. <?php echo htmlspecialchars($certificate_data['address']); ?>, 
                        Gumaoc East, City of San Jose Del Monte, Bulacan, is belonging to the Indigent Family in our Barangay.</p>
                    </div>
                </div>
                <div class="photo-placeholder">
                    IMAGE
                </div>
            </div>

            <div class="certification-text">
                <p>This certification is issued upon the request of the above person to be used for his/her 
                <strong><?php echo htmlspecialchars($certificate_data['purpose']); ?></strong>. 
                
                <p>Given this <?php echo date('jS'); ?> day of <?php echo date('F Y'); ?> at Gumaoc East, City of San Jose Del Monte, Bulacan, 
                Philippines. This certification is valid for official and legal purposes as may be required by the requesting party.</p>
            </div>
                    <div class="bottom-sections-container">
                <div class="left-section">
                    <div class="officer-section">
                        <div class="officer-label">Verified by</div>
                        <div class="officer-name">HON. MARITESS O. SY</div>
                        <div class="officer-sub-label">Officer of the Day</div>
                    </div>
                    
                    <div class="date-validity-thumb-section">
                        <div class="date-validity-row">
                            <div><strong>Given this:</strong> <?php echo strtoupper(date('d F Y')); ?></div>
                            <div><strong>Valid until:</strong> <?php echo strtoupper(date('d F Y', strtotime('+1 year'))); ?></div>
                        </div>
                    </div>
                    
                    <div class="signature-section">
                        <div class="ctc-details-section">
                            <div class="ctc-detail-item">
                                <span class="ctc-detail-label">CTC NO</span>
                                <span class="ctc-detail-value"></span>
                            </div>
                            <div class="ctc-detail-item">
                                <span class="ctc-detail-label">ISSUED AT</span>
                                <span class="ctc-detail-value">BRGY. GUMAOC EAST, CSJDM, BULACAN</span>
                            </div>
                            <div class="ctc-detail-item">
                                <span class="ctc-detail-label">ISSUED ON</span>
                                <span class="ctc-detail-value"><?php echo strtoupper(date('d F Y')); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="right-section">
                    <div class="signature-applicant-section">
                        <div class="signature-line-applicant"></div>
                        <div class="signature-applicant">Signature of Applicant</div>
                    </div>
                    
                    <div class="thumb-marks-section">
                        <div class="thumb-mark-box">
                            <div class="thumb-mark-circle">LEFT<br>THUMB<br>MARK</div>
                        </div>
                        
                        <div class="thumb-mark-box">
                            <div class="thumb-mark-circle">RIGHT<br>THUMB<br>MARK</div>
                        </div>
                    </div>
                    
                    <div class="signature-box">
                        <div class="signature-name">HON. ROMMEL B. PITALBO</div>
                        <div class="signature-line"></div>
                        <div class="signature-title">Punong Barangay</div>
                    </div>
                </div>
            </div>

                
                <div class="not-valid-notice">
                    NOT VALID WITHOUT DRY SEAL
                </div>
            </div>
        </div>
        

    </div>
</body>
</html>
