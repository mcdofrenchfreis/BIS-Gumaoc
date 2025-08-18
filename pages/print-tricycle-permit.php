<?php
session_start();
include '../includes/db_connect.php';

// Get the certificate request ID from URL parameter
$request_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$request_id) {
    die("Certificate request ID is required.");
}

// Fetch the certificate request data
$stmt = $pdo->prepare("SELECT * FROM certificate_requests WHERE id = ? AND certificate_type = 'TRICYCLE PERMIT'");
$stmt->execute([$request_id]);
$certificate_data = $stmt->fetch();

if (!$certificate_data) {
    die("Certificate request not found or is not a TRICYCLE PERMIT certificate.");
}

// Calculate age from birth date
$birth_date = new DateTime($certificate_data['birth_date']);
$current_date = new DateTime();
$age = $current_date->diff($birth_date)->y;

// Format dates
$formatted_birth_date = date('F j, Y', strtotime($certificate_data['birth_date']));
$current_date_formatted = date('F j, Y');
$valid_until = date('F j, Y', strtotime('+6 months'));

// Generate certificate number
$certificate_number = 'TP-' . str_pad($request_id, 5, '0', STR_PAD_LEFT) . '-' . date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRICYCLE PERMIT - <?php echo htmlspecialchars($certificate_data['full_name']); ?></title>
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
                padding: 250px 60px 60px 60px !important;
                height: 100% !important;
                position: relative !important;
                z-index: 2 !important;
            }
            
            .main-title {
                font-size: 32px !important;
                margin-bottom: 20px !important;
                letter-spacing: 2px !important;
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
            
            .to-whom {
                font-weight: bold !important;
                font-size: 16px !important;
                margin-bottom: 15px !important;
            }
            
            .main-text {
                font-size: 16px !important;
                line-height: 1.4 !important;
                text-align: justify !important;
            }
            
            .photo-placeholder {
                width: 85px !important;
                height: 85px !important;
                border: 2px solid #000 !important;
                font-size: 11px !important;
                margin-left: 20px !important;
            }
            
            .vehicle-details {
                font-size: 15px !important;
                margin: 10px 0 20px 0 !important;
            }
            
            .vehicle-details p {
                display: flex !important;
                align-items: baseline !important;
                margin-bottom: 7px !important;
            }
            
            .detail-label-left {
                min-width: 210px !important;
                font-weight: bold !important;
                display: inline-block !important;
            }
            
            .detail-colon {
                margin: 0 7px !important;
            }

            .underline-value {
                display: inline-block !important;
                min-width: 240px !important;
                border-bottom: 1px solid #000 !important;
                padding-bottom: 1px !important;
                height: 17px !important;
            }
            
            .issuance-text {
                font-size: 15px !important;
                line-height: 1.4 !important;
                text-align: justify !important;
                margin-bottom: 20px !important;
            }
            
            .signature-section {
                margin-top: 20px !important;
                display: block !important;
                width: 100% !important;
                text-align: center !important;
            }
            
            .signature-line {
                border-bottom: 2px solid #000 !important;
                width: 250px !important;
                height: 1px !important;
                margin: 0 auto 7px auto !important;
            }
            
            .signature-name {
                font-weight: bold !important;
                font-size: 14px !important;
                text-decoration: underline !important;
                margin-bottom: 2px !important;
            }
            .certified-label {
                font-weight: bold !important;
                font-size: 13px !important;
                margin-top: 2px !important;
                margin-bottom: 2px !important;
            }
            
            .signature-title {
                font-size: 13px !important;
                margin-top: 2px !important;
            }
            
            .validity-notice {
                text-align: center !important;
                font-size: 11px !important;
                font-weight: bold !important;
                margin-top: 15px !important;
                color: #d32f2f !important;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            line-height: 1.7;
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
            background-color: #f9f9f9;
            page-break-inside: avoid;
            page-break-after: avoid;
            page-break-before: avoid;
        }
        
        .certificate-content {
            position: relative;
            z-index: 2;
            padding: 250px 60px 60px 60px;
            height: 100%;
        }
        
        .main-title {
            font-size: 32px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
            color: #C2944D;
            letter-spacing: 2px;
            text-align: center;
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
        
        .to-whom {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 15px;
        }
        
        .main-text {
            font-size: 16px;
            line-height: 1.5;
            text-align: justify;
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
        
        .vehicle-details {
            margin: 10px 0 20px 0;
            font-size: 15px;
        }
        
        .vehicle-details p {
            margin-bottom: 7px;
            line-height: 1.3;
            display: flex;
            align-items: baseline;
        }
        
        .detail-label-left {
            font-weight: bold;
            min-width: 210px;
            display: inline-block;
        }
        
        .detail-colon {
            margin: 0 7px;
        }
        
        .underline-value {
            display: inline-block;
            min-width: 240px;
            border-bottom: 1px solid #000;
            padding-bottom: 1px;
            height: 17px;
        }
        
        .issuance-text {
            font-size: 15px;
            line-height: 1.5;
            text-align: justify;
            margin-bottom: 20px;
        }
        
        .signature-section {
            margin-top: 20px;
            display: block;
            width: 100%;
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 2px solid #000;
            width: 250px;
            height: 1px;
            margin: 0 auto 7px auto;
        }
        
        .signature-name {
            font-weight: bold;
            font-size: 14px;
            text-decoration: underline;
            margin-bottom: 2px;
        }
        
        .certified-label {
            font-weight: bold;
            font-size: 13px;
            margin-top: 2px;
            margin-bottom: 2px;
        }
        
        .signature-title {
            font-size: 13px;
            margin-top: 2px;
        }
        
        .validity-notice {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            margin-top: 15px;
            color: #d32f2f;
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
                MOTORIZED TRICYCLE OPERATORS PERMIT CERTIFICATION
            </div>
            
            <div class="content-with-photo">
                <div class="left-content">
                    <div class="to-whom">
                        TO WHOM IT MAY CONCERN,
                    </div>
                    
                    <div class="main-text">
                        <p>This is to certify that <strong><?php echo htmlspecialchars($certificate_data['full_name']); ?></strong> 
                            a resident of <strong><?php echo htmlspecialchars($certificate_data['address']); ?></strong> 
                            Gumaoc East, City of San Jose Del Monte, Bulacan and a legitimate owner of one unit of tricycle described as follows:</p>
                    </div>
                </div>
                <div class="photo-placeholder">
                    IMAGE
                </div>
            </div>

            <div class="vehicle-details">
                <p><span class="detail-label-left">Make and Type</span><span class="detail-colon">:</span> <span class="underline-value"><?php echo htmlspecialchars($certificate_data['vehicle_make'] ?? ''); ?></span></p>
                <p><span class="detail-label-left">Motor No.</span><span class="detail-colon">:</span> <span class="underline-value"><?php echo htmlspecialchars($certificate_data['motor_number'] ?? ''); ?></span></p>
                <p><span class="detail-label-left">Chassis No.</span><span class="detail-colon">:</span> <span class="underline-value"><?php echo htmlspecialchars($certificate_data['chassis_number'] ?? ''); ?></span></p>
                <p><span class="detail-label-left">Plate No.</span><span class="detail-colon">:</span> <span class="underline-value"><?php echo htmlspecialchars($certificate_data['plate_number'] ?? ''); ?></span></p>
            </div>

            <div class="issuance-text">
                <p>This certification is issued upon the request of the subject person, for all legal intents and purposes it may serve him/her best.</p>
                <p>Issued this <strong><?php echo strtoupper(date('d')); ?></strong> day of <strong><?php echo strtoupper(date('F Y')); ?></strong> 
                    at the Office of the Punong Barangay of Gumaoc East, City of San Jose Del Monte, Bulacan.</p>
            </div>

            <div class="signature-section">
                <div class="certified-label">CERTIFIED BY:</div>
                <div class="signature-name">HON. ROMMEL B. PITALBO</div>
                <div class="signature-line"></div>
                <div class="signature-title">Punong Barangay</div>
            </div>
            
            <div class="validity-notice">
                NOTE: Valid only for six (6) months from the date of issuance.
            </div>
        </div>
    </div>
<script>
  (function(){
    const params = new URLSearchParams(window.location.search);
    if (params.get('auto') === '1') {
      setTimeout(function(){
        window.print();
        window.addEventListener('afterprint', function(){ window.close(); });
      }, 100);
    }
  })();
</script>
</body>
</html>
