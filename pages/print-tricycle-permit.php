<?php
session_start();
include '../includes/db_connect.php';

// Get the certificate request ID from URL parameter
$request_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$request_id) {
    die("Certificate request ID is required.");
}

// Fetch the certificate request data - FIXED: Look for TRICYCLE PERMIT, not CEDULA
$stmt = $pdo->prepare("SELECT * FROM certificate_requests WHERE id = ? AND certificate_type = 'TRICYCLE PERMIT'");
$stmt->execute([$request_id]);
$certificate_data = $stmt->fetch();

if (!$certificate_data) {
    die("Certificate request not found or is not a TRICYCLE PERMIT certificate.");
}

// Extract tricycle details from either columns or additional_data JSON
$additional = [];
try {
    if (!empty($certificate_data['additional_data'])) {
        $decoded = json_decode($certificate_data['additional_data'], true);
        if (is_array($decoded)) { $additional = $decoded; }
    }
} catch (Exception $e) {
    $additional = [];
}

$vehicle_make_type = $certificate_data['vehicle_make_type'] ?? '';
if ($vehicle_make_type === '' && isset($additional['vehicle_make_type'])) { $vehicle_make_type = $additional['vehicle_make_type']; }
$motor_no = $certificate_data['motor_no'] ?? '';
if ($motor_no === '' && isset($additional['motor_no'])) { $motor_no = $additional['motor_no']; }
$chassis_no = $certificate_data['chassis_no'] ?? '';
if ($chassis_no === '' && isset($additional['chassis_no'])) { $chassis_no = $additional['chassis_no']; }
$plate_no = $certificate_data['plate_no'] ?? '';
if ($plate_no === '' && isset($additional['plate_no'])) { $plate_no = $additional['plate_no']; }
$vehicle_color = $certificate_data['vehicle_color'] ?? '';
if ($vehicle_color === '' && isset($additional['vehicle_color'])) { $vehicle_color = $additional['vehicle_color']; }
$year_model = $certificate_data['year_model'] ?? '';
if (($year_model === '' || $year_model === null) && isset($additional['year_model'])) { $year_model = $additional['year_model']; }
$body_no = $certificate_data['body_no'] ?? '';
if ($body_no === '' && isset($additional['body_no'])) { $body_no = $additional['body_no']; }
$operator_license = $certificate_data['operator_license'] ?? '';
if ($operator_license === '' && isset($additional['operator_license'])) { $operator_license = $additional['operator_license']; }

// Resolve applicant 1x1 photo for this certificate
$photoSrc = '../assets/images/forms/1x1.jpeg';
try {
    if (!empty($certificate_data['photo_path'])) {
        $candidate = '../' . ltrim($certificate_data['photo_path'], '/\\');
        $photoSrc = $candidate;
    } elseif (!empty($certificate_data['photo_id'])) {
        $stmtPhoto = $pdo->prepare("SELECT photo_path FROM user_photos WHERE id = ? AND is_active = 1");
        $stmtPhoto->execute([$certificate_data['photo_id']]);
        $photo = $stmtPhoto->fetch(PDO::FETCH_ASSOC);
        if ($photo && !empty($photo['photo_path'])) {
            $photoSrc = '../' . ltrim($photo['photo_path'], '/\\');
        }
    } elseif (!empty($certificate_data['user_id'])) {
        // Strictly prefer the photo uploaded for this certificate request
        $stmtPhoto = $pdo->prepare("SELECT photo_path FROM user_photos WHERE user_id = ? AND certificate_request_id = ? AND is_active = 1 ORDER BY uploaded_at DESC LIMIT 1");
        $stmtPhoto->execute([$certificate_data['user_id'], $request_id]);
        $photo = $stmtPhoto->fetch(PDO::FETCH_ASSOC);
        if ($photo && !empty($photo['photo_path'])) {
            $photoSrc = '../' . ltrim($photo['photo_path'], '/\\');
        }
    }
} catch (Exception $e) {
    // keep fallback
}

// Resolve tricycle photo from additional_data (if present)
$tricyclePhotoSrc = '../assets/images/forms/1x1.jpeg';
try {
    $additional = [];
    if (!empty($certificate_data['additional_data'])) {
        $decoded = json_decode($certificate_data['additional_data'], true);
        if (is_array($decoded)) { $additional = $decoded; }
    }
    if (!empty($additional['tricycle_image'])) {
        // Stored as filename relative to uploads/tricycle_photos
        $tricyclePhotoSrc = '../uploads/tricycle_photos/' . basename($additional['tricycle_image']);
    }
} catch (Exception $e) {
    // keep fallback
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

// Add logging for print actions
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    include '../includes/AdminLogger.php';
    $logger = new AdminLogger($pdo);
    
    if ($certificate_data) {
        $logger->logPrintAction(
            'certificate_request',
            $request_id,
            'tricycle_permit',
            [
                'certificate_type' => 'TRICYCLE PERMIT',
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
            .right-photos { display: flex !important; flex-direction: column !important; gap: 8px !important; }
            
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
                width: 1in !important;
                height: 1in !important;
                border: 2px solid #000 !important;
                font-size: 11px !important;
                margin-left: 20px !important;
                overflow: hidden !important;
            }
            .photo-placeholder img { width: 100% !important; height: 100% !important; object-fit: cover !important; display: block !important; }
            .tricycle-photo { width: 1in !important; height: 1in !important; border: 2px solid #000 !important; overflow: hidden !important; background: #fff !important; float: right !important; margin: 0 0 10px 20px !important; display: flex !important; align-items: center !important; justify-content: center !important; }
            .tricycle-photo img { width: 100% !important; height: 100% !important; object-fit: cover !important; display: block !important; }
            
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
        .right-photos { display: flex; flex-direction: column; gap: 8px; }
        
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
            width: 1in;
            height: 1in;
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
            overflow: hidden;
        }
        .photo-placeholder img { width: 100%; height: 100%; object-fit: cover; display: block; }
        
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

        .vehicle-photo {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 6px 0 16px 0;
        }
        .tricycle-photo {
            width: 1in;
            height: 1in;
            border: 2px solid #000;
            overflow: hidden;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            float: right;
            margin: 0 0 10px 20px;
        }
        .tricycle-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
        
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
                    <div class="vehicle-details">
                        <p><span class="detail-label-left">Make and Type</span><span class="detail-colon">:</span> <span class="underline-value"><?php echo htmlspecialchars($vehicle_make_type); ?></span></p>
                        <p><span class="detail-label-left">Motor No.</span><span class="detail-colon">:</span> <span class="underline-value"><?php echo htmlspecialchars($motor_no); ?></span></p>
                        <p><span class="detail-label-left">Chassis No.</span><span class="detail-colon">:</span> <span class="underline-value"><?php echo htmlspecialchars($chassis_no); ?></span></p>
                        <p><span class="detail-label-left">Plate No.</span><span class="detail-colon">:</span> <span class="underline-value"><?php echo htmlspecialchars($plate_no); ?></span></p>
                        <?php if (!empty($vehicle_color)): ?>
                        <p><span class="detail-label-left">Vehicle Color</span><span class="detail-colon">:</span> <span class="underline-value"><?php echo htmlspecialchars($vehicle_color); ?></span></p>
                        <?php endif; ?>
                        <?php if (!empty($year_model)): ?>
                        <p><span class="detail-label-left">Year Model</span><span class="detail-colon">:</span> <span class="underline-value"><?php echo htmlspecialchars($year_model); ?></span></p>
                        <?php endif; ?>
                        <?php if (!empty($body_no)): ?>
                        <p><span class="detail-label-left">Body No.</span><span class="detail-colon">:</span> <span class="underline-value"><?php echo htmlspecialchars($body_no); ?></span></p>
                        <?php endif; ?>
                        <?php if (!empty($operator_license)): ?>
                        <p><span class="detail-label-left">Operator's License No.</span><span class="detail-colon">:</span> <span class="underline-value"><?php echo htmlspecialchars($operator_license); ?></span></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="right-photos">
                    <div class="photo-placeholder">
                        <img src="<?php echo htmlspecialchars($photoSrc); ?>" alt="Photo" />
                    </div>
                    <div class="tricycle-photo">
                        <img src="<?php echo htmlspecialchars($tricyclePhotoSrc); ?>" alt="Tricycle Photo" />
                    </div>
                </div>
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
