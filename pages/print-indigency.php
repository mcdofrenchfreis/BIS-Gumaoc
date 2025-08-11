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
            }
            body { 
                margin: 0; 
                padding: 0;
                background: white !important;
            }
            .no-print { display: none !important; }
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
            }
            .certificate-content {
    padding: 230px 60px 20px 60px !important; /* increased from 150px to 230px */
    height: 100% !important;
    position: relative !important;
    z-index: 2 !important;
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
  padding: 230px 60px 20px 60px; /* increased from 150px to 230px */
  height: 100%;
  display: flex;
  flex-direction: column;
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
            font-size: 28px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 6px;      /* small space under the letterhead */
            margin-bottom: 20px;
            color: #C2944D;
            letter-spacing: 2px;
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
    padding-top: 0; /* remove extra padding pushing it down */
}
        
        .to-whom {
            display: justify;
            margin-bottom: 12px;
            font-weight: bold;
        }
        
        .main-content {
            margin-bottom: 15px;
        }
        
        .main-content p {
            display: justify;
            margin-bottom: 10px;
            text-indent: 0;
        }

        .main-content-with-photo {
            display: flex;
            align-items: flex-start;
            gap: 20px; /* space between text and image */
            margin-bottom: 15px;
        }

        .main-content-with-photo .main-text {
            flex: 1; /* let text take remaining space */
        }

        .photo-placeholder img {
            width: 1in;   /* exactly 1 inch */
            height: 1in;  /* exactly 1 inch */
            border: 1px solid #000;
            object-fit: cover;
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
        .lower-section {
            margin-bottom: 175px;
        }
        .signature-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: -20px;
        }
        
        .officer-section {
            text-align: justify;
            width: 45%;
            margin-left: -10px; /* moves it 30px left, adjust as needed */
        }

        
        .signature-applicant {
            text-align: center;
            width: 45%;
            font-weight: bold;
            position: relative;
            margin-left: -20px; /* pulls it closer to the boxes */
            margin-top: -10px;  /* raises it closer vertically */
        }

        
        .officer-label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .officer-sub-label {
            font-size: 14px;
            margin-top: -4px; /* pulls it closer to 'Verified by' */
        }

        .signature-applicant::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 180px;   /* was 120px — longer line */
            height: 1px;
            background-color: #000;
        }
        
        .thumb-mark-box {
            text-align: center;
            width: 120px;
        }
        
        .thumb-marks-right {
            display: flex;
            gap: 20px;
        }
        
        .thumb-mark-circle {
            width: 110px;      /* was 80px */
            height: 110px;     /* was 80px */
            border: 2px solid #000;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;   /* slightly larger text inside */
            font-weight: bold;
        }

        
        .thumb-mark-label {
            display: none;
        }
        
        .certificate-details {
            margin: 15px 0;
            display: flex;
            flex-direction: column;
        }
        
        .detail-item {
            width: 100%;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .detail-label {
            font-weight: bold;
            margin-right: 10px;
            min-width: 80px;
        }
        
        .detail-value {
            border-bottom: 1px solid #000;
            flex: 1;
            padding-bottom: 2px;
            min-width: 100px;
        }
        
        .signature-section {
            margin-top: 25px;
            display: flex;
            justify-content: flex-end; /* pushes content to the right */
            align-items: flex-end;    /* aligns vertically at the bottom */
        }

        .signature-box {
            text-align: center;
            width: 180px;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            margin-bottom: 8px;
            height: 30px;
        }
        
        .signature-name {
            font-weight: bold;
            font-size: 12px;
        }
        
        .signature-title {
            font-size: 10px;
            color: #666;
        }
        
        .date-section {
            text-align: center;
            margin-top: 10px;
            font-size: 12px;
        }
        
        .date-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .validity-section {
            margin-top: 10px;
            text-align: center;
            font-size: 12px;
        }
        
        .validity-label {
            font-weight: bold;
            margin-bottom: 5px;
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

            
            <div class="content">
                <div class="main-title">
                    CERTIFICATION OF INDIGENCY
                </div>
                
                <div class="to-whom">
                    TO WHOM IT MAY CONCERN:
                </div>
                
                <div class="main-content-with-photo">
                        <div class="main-text">
                            <p>This is to certify that <strong><?php echo htmlspecialchars($certificate_data['full_name']); ?></strong>, 
                            <?php echo $age; ?> years old, with Address no. <?php echo htmlspecialchars($certificate_data['address']); ?>, 
                            Gumaoc East, City of San Jose Del Monte, Bulacan, is belonging to the Indigent Family in our Barangay.</p>
                        </div>
                        <div class="photo-placeholder">
                            <img src="../assets/images/forms/photo-placeholder.png" alt="Photo" />
                        </div>
                    </div>

                    <p>This certification is issued upon the request of the above person to be used for his/her 
                    <strong><?php echo htmlspecialchars($certificate_data['purpose']); ?></strong>.</p>

                    <p>Given this <?php echo date('jS'); ?> day of <?php echo date('F Y'); ?>  at Gumaoc East, City of San Jose Del Monte, Bulacan. </p>

                </div>
                    <div class="lower-section">
                <div class="signature-row">
                    <div class="officer-section">
                        <div class="officer-label">Verified by</div>
                        <div class="officer-sub-label">Officer of the Day</div>
                        <div class="date-section">
                            <div class="date-label">Given this: <?php echo strtoupper(date('d F Y')); ?></div>
                        </div>
                        <div class="validity-section">
                            <div class="validity-label">Valid until: <?php echo strtoupper(date('d F Y', strtotime('+1 year'))); ?></div>
                        </div>
                    </div>

                    
                    <div class="signature-applicant">
                        Signature of Applicant
                    </div>
                </div>
                
                <div class="thumb-marks-section">
                    <div class="certificate-details">
                        <div class="detail-item">
                            <span class="detail-label">CTC NO:</span>
                            <span class="detail-value"></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">ISSUED AT:</span>
                            <span class="detail-value"></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">ISSUED ON:</span>
                            <span class="detail-value"></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">O.R. NO:</span>
                            <span class="detail-value"></span>
                        </div>
                    </div>
                    
                    <div class="thumb-marks-right">
                        <div class="thumb-mark-box">
                            <div class="thumb-mark-circle">LEFT<br>THUMB<br>MARK</div>
                            <div class="thumb-mark-label">LEFT THUMB MARK</div>
                        </div>
                        
                        <div class="thumb-mark-box">
                            <div class="thumb-mark-circle">RIGHT<br>THUMB<br>MARK</div>
                            <div class="thumb-mark-label">RIGHT THUMB MARK</div>
                        </div>
                    </div>
                </div>
                

                
                <div class="signature-section">
                    
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div class="signature-name">HON. ROMMEL B. PITALBO</div>
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
