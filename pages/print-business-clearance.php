<?php
session_start();
include '../includes/db_connect.php';

// Get the business application ID from URL parameter
$application_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$application_id) {
    die("Business application ID is required.");
}

// Fetch the business application data
$stmt = $pdo->prepare("SELECT * FROM business_applications WHERE id = ?");
$stmt->execute([$application_id]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$app) {
    die("Business application not found.");
}

// Derive fields
$business_name = $app['business_name'] ?? '';
$business_location = $app['business_location'] ?: ($app['business_address'] ?? '');
$owner_name = $app['owner_name'] ?? '';
$owner_address = $app['owner_address'] ?? '';
$application_date_raw = $app['application_date'] ?? ($app['submitted_at'] ?? date('Y-m-d'));

// Dates
$application_date = date('F j, Y', strtotime($application_date_raw));
$issued_on = date('F j, Y'); // No explicit approval date column; use current date

// Validity: 5 months from the application date
$valid_until_ts = strtotime($application_date_raw . ' +5 months');
$valid_until = date('F j, Y', $valid_until_ts);

// Clearance number (optional display)
$clearance_number = 'BBC-' . date('Y') . '-' . str_pad($application_id, 4, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Business Clearance - <?php echo htmlspecialchars($business_name); ?></title>
    <style>
        @media print {
            @page { margin: 0; size: A4 portrait; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            body { margin: 0; padding: 0; background: white !important; font-family: 'Times New Roman', serif !important; }
            .no-print { display: none !important; }
            .certificate-container { width: 210mm !important; height: 297mm !important; margin: 0 !important; padding: 0 !important; background: url('../assets/images/forms/barangay-letterhead-bg.png') no-repeat center center !important; background-size: cover !important; box-shadow: none !important; position: relative !important; overflow: hidden !important; }
            .certificate-content { padding: 250px 60px 60px 60px !important; height: 100% !important; position: relative !important; z-index: 2 !important; }
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; line-height: 1.6; color: #000; background: #f5f5f5; }

        .certificate-container { width: 210mm; height: 297mm; margin: 20px auto; background: url('../assets/images/forms/barangay-letterhead-bg.png') no-repeat center center; background-size: cover; position: relative; box-shadow: 0 0 20px rgba(0,0,0,0.1); background-color: #f9f9f9; }
        .certificate-content { position: relative; z-index: 2; padding: 250px 60px 60px 60px; height: 100%; }

        .main-title { font-size: 32px; font-weight: bold; text-transform: uppercase; margin-bottom: 20px; color: #C2944D; letter-spacing: 3px; text-align: center; }

        .salutation { font-weight: bold; font-size: 18px; margin-bottom: 12px; text-align: justify; }
        .intro-text { font-size: 16px; margin-bottom: 18px; text-align: justify; }

        .business-details { margin: 15px auto 20px auto; width: 100%; max-width: 700px; }
        .detail-row { display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; margin-bottom: 14px; }
        .detail-value { font-weight: bold; font-size: 22px; text-align: center; }
        .detail-label { font-size: 14px; color: #666; font-style: italic; margin-top: 2px; text-align: center; }

        .legal-text { font-size: 14px; text-align: justify; margin: 20px auto; line-height: 1.45; max-width: 750px; }
        .legal-text p { margin-bottom: 12px; }
        .legal-text .valid-until { font-weight: bold; }

        .issuance-info { margin: 18px auto; text-align: center; }
        .not-valid { font-size: 12px; font-style: italic; color: #666; margin-top: 6px; }

        .certification { margin-top: 14px; text-align: center; }
        .certified-by { font-size: 16px; margin-bottom: 10px; }
        .signature-line { width: 240px; border-bottom: 2px solid #000; margin: 8px auto 4px auto; }
        .official-name { font-size: 16px; font-weight: bold; text-decoration: underline; margin-bottom: 4px; }
        .official-title { font-size: 14px; }

        .print-controls { text-align: center; margin: 20px 0; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .print-btn { background: #1a4d80; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-size: 16px; margin: 0 10px; }
        .back-btn { background: #6c757d; color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-size: 16px; margin: 0 10px; }
        .meta { color: #666; margin-top: 8px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="no-print print-controls">
        <button class="print-btn" onclick="window.print()">🖨️ Print Clearance</button>
        <a href="../admin/view-business-applications.php" class="back-btn">← Back to Admin</a>
        <p class="meta">Clearance #: <?php echo htmlspecialchars($clearance_number); ?> · Application ID: <?php echo str_pad($application_id, 5, '0', STR_PAD_LEFT); ?></p>
    </div>

    <div class="certificate-container">
        <div class="certificate-content">
            <div class="main-title">BARANGAY BUSINESS CLEARANCE</div>

            <div class="salutation">TO WHOM IT MAY CONCERN:</div>
            <div class="intro-text">This is to certify that the business or trade activity described below</div>

            <div class="business-details">
                <div class="detail-row">
                    <div class="detail-value"><?php echo htmlspecialchars($business_name); ?></div>
                    <div class="detail-label">(Business Name)</div>
                </div>
                <div class="detail-row">
                    <div class="detail-value"><?php echo htmlspecialchars($business_location); ?></div>
                    <div class="detail-label">(Business Location)</div>
                </div>
                <div class="detail-row">
                    <div class="detail-value"><?php echo htmlspecialchars($owner_name); ?></div>
                    <div class="detail-label">(President/Owner)</div>
                </div>
                <div class="detail-row">
                    <div class="detail-value"><?php echo htmlspecialchars($owner_address); ?></div>
                    <div class="detail-label">(Address of Owner/Manager)</div>
                </div>
            </div>

            <div class="legal-text">
                <p>Proposed to be established in this Barangay and is being applied for a Barangay Business Clearance to be used in securing a corresponding Mayor's Permit has been found to be on conformity with the provisions of existing Barangay Ordinances, rules and regulations being enforced in this barangay.</p>
                <p>In view of foregoing, the undersigned interposes no objection for the issuance of the corresponding Mayor's Permit being Applied for.</p>
                <p>This permit shall be valid until <span class="valid-until"><?php echo $valid_until; ?></span> and can be cancelled/revoked anytime the establishment is found to have violated any law or ordinance within this Barangay.</p>
            </div>

            <div class="issuance-info">
                <div>Issued on <?php echo $issued_on; ?> at Gumaoc East, City Of San Jose Del Monte, Bulacan.</div>
                <div class="not-valid">NOT VALID WITHOUT DRY SEAL</div>
            </div>

            <div class="certification">
                <div class="certified-by">Certified By:</div>
                <div class="signature-line"></div>
                <div class="official-name">HON. ROMMEL B. PITALBO</div>
                <div class="official-title">Punong Barangay</div>
            </div>
        </div>
    </div>
</body>
</html>
