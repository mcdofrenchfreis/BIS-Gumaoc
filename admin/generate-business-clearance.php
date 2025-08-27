<?php
session_start();
include '../includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get business application ID from URL
$application_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($application_id <= 0) {
    header('Location: view-business-applications.php');
    exit;
}

// Fetch business application data
$stmt = $pdo->prepare("SELECT * FROM business_applications WHERE id = ?");
$stmt->execute([$application_id]);
$application = $stmt->fetch();

if (!$application) {
    header('Location: view-business-applications.php');
    exit;
}

// Generate clearance number
$clearance_number = 'BBC-' . date('Y') . '-' . str_pad($application_id, 4, '0', STR_PAD_LEFT);

// Current date
$current_date = date('F j, Y');
$valid_until = date('F j, Y', strtotime('+1 year'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Business Clearance - <?php echo htmlspecialchars($application['business_name']); ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Times+New+Roman:ital,wght@0,400;0,700;1,400&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .clearance-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        /* Background image */
        .clearance-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('../assets/images/forms/barangay-letterhead-bg.png');
            background-size: 100% 100%;
            background-position: top center;
            background-repeat: no-repeat;
            opacity: 0.8;
            pointer-events: none;
            z-index: 1;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }
        
        /* Top decorative border - now part of background image */
        .top-border {
            height: 40px;
            position: relative;
            z-index: 2;
        }
        
        /* Bottom decorative border - now part of background image */
        .bottom-border {
            height: 40px;
            position: relative;
            z-index: 2;
        }
        
        .clearance-content {
            padding: 80px 40px 40px 40px;
            position: relative;
            z-index: 3;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        /* Header section - logos now part of background image */
        .header-section {
            height: 120px;
            margin-bottom: 20px;
            position: relative;
            z-index: 3;
        }
        
        /* Header text removed - now part of background image */
        
        /* Watermark now part of background image */
        
        /* Main content */
        .main-content {
            margin-top: 40px;
            width: 100%;
            max-width: 800px;
        }
        
        .salutation {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: justify;
            width: 100%;
            max-width: 750px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .intro-text {
            font-size: 20px;
            margin-bottom: 25px;
            text-align: justify;
            line-height: 1.4;
        }
        
        .business-details {
            margin: 25px auto;
            width: 100%;
            max-width: 700px;
        }
        
        .detail-row {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        
        .detail-label {
            font-weight: normal;
            font-size: 16px;
            text-align: center;
            margin-top: 8px;
            color: #666;
        }
        
        .detail-value {
            font-weight: bold;
            font-size: 22px;
            text-align: center;
            min-width: 300px;
        }
        
        .detail-caption {
            font-size: 16px;
            color: #666;
            margin-top: 2px;
            text-align: center;
            font-style: italic;
        }
        
        .legal-text {
            font-size: 18px;
            text-align: justify;
            margin: 30px auto;
            line-height: 1.6;
            width: 100%;
            max-width: 750px;
        }
        
        .legal-text p {
            margin-bottom: 15px;
        }
        
        .issuance-info {
            margin: 30px auto;
            text-align: justify;
            width: 100%;
            max-width: 700px;
        }
        
        .issuance-text {
            font-size: 20px;
            margin-bottom: 12px;
        }
        
        .not-valid {
            font-size: 16px;
            font-style: italic;
            color: #666;
        }
        
        .certification {
            margin-top: 20px;
            text-align: center;
            margin-bottom: 20px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .certified-by {
            font-size: 20px;
            margin-bottom: 15px;
        }
        
        .signature-line {
            width: 250px;
            border-bottom: 2px solid #000;
            margin: 15px auto 5px auto;
        }
        
        .official-name {
            font-size: 20px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }
        
        .official-title {
            font-size: 18px;
        }
        

        
        /* Print styles */
/* Print styles – tightened from SALUTATION to CERTIFICATION */
@media print {
  body {
    background: white;
    padding: 0;
    margin: 0;
  }

  .clearance-container {
    box-shadow: none;
    max-width: none;
    width: 100%;
    height: 100vh;
    margin: 0;
    page-break-after: avoid;
    page-break-inside: avoid;
    overflow: hidden;
    background: white !important;
    min-height: auto;
    max-height: none;
  }

  .clearance-container::before {
    opacity: 0.9;
    -webkit-print-color-adjust: exact;
    color-adjust: exact;
  }

  .clearance-content {
    padding: 72px 32px 28px 32px;          /* slightly tighter padding */
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: center;
    color: #000 !important;
    text-align: center;
  }

  .header-section {                         /* keep header height but slightly tighter */
    height: 96px;
    margin-bottom: 10px;
  }

  /* --- DO NOT TOUCH MAIN TITLE --- */

  /* SALUTATION and body copy */
  .salutation {
    font-size: 18px;                        /* was 20 */
    font-weight: bold;
    text-align: justify;
    line-height: 1.4;                       /* was 1.8 */
    width: 100%;
    max-width: 650px;
    margin: 6px auto 6px;                   /* tighter top/bottom */
  }

  .intro-text {
    font-size: 15px;                        /* was 16 */
    margin: 6px auto 10px;                  /* was 12 bottom */
    text-align: center;
    line-height: 1.3;
    width: 100%;
    max-width: 650px;
  }

  /* BUSINESS DETAILS */
  .business-details {
    margin: 8px auto;                       /* was 12 */
    width: 100%;
    max-width: 630px;                       /* a bit narrower */
  }

  .detail-row {
    display: flex;
    flex-direction: column;
    margin-bottom: 8px;                     /* was 12 */
    align-items: center;
    justify-content: center;
    text-align: center;
  }

  .detail-value {
    font-weight: bold;
    font-size: 17px;                        /* was 18 */
    text-align: center;
    min-width: 260px;                       /* was 280 */
    line-height: 1.2;
  }

  .detail-label,
  .detail-caption {
    font-size: 13px;                        /* was 14 */
    margin-top: 2px;
    color: #666;
    font-style: italic;
  }

  /* LEGAL TEXT */
  .legal-text {
    font-size: 14px;                        /* was 15 */
    text-align: justify;
    margin: 8px auto;                       /* was 12 */
    line-height: 1.35;                      /* was 1.5 */
    width: 100%;
    max-width: 650px;
  }
  .legal-text p { margin-bottom: 8px; }     /* was 10 */

  /* ISSUANCE INFO */
  .issuance-info {
    margin: 8px auto 6px;                   /* was 12 */
    text-align: center;
    width: 100%;
    max-width: 650px;
  }
  .issuance-text {
    font-size: 15px;                        /* was 16 */
    margin-bottom: 6px;                     /* was 10 */
    line-height: 1.25;
  }
  .not-valid {
    font-size: 13px;                        /* was 14 */
    font-style: italic;
    color: #666;
  }

  /* CERTIFICATION (signature area / black line) */
  .certification {
    margin-top: 6px;                        /* pulls the signature up */
    margin-bottom: 6px;
    page-break-inside: avoid;
    text-align: center;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }
  .certified-by {
    font-size: 15px;                        /* was 16 */
    margin-bottom: 8px;                     /* was 12 */
  }
  .signature-line {
    width: 240px;                           /* can tweak: 230–260 */
    border-bottom: 2px solid #000;
    margin: 8px auto 4px;                   /* tighter space above/below the line */
  }
  .official-name {
    font-size: 15px;                        /* was 16 */
    font-weight: bold;
    text-decoration: underline;
    margin-bottom: 3px;
  }
  .official-title {
    font-size: 13px;                        /* was 14 */
  }

  .no-print { display: none !important; }

  /* Ensure background prints */
  * {
    -webkit-print-color-adjust: exact !important;
    color-adjust: exact !important;
  }

  /* Page box */
  @page {
    size: A4;
    margin: 14mm;                           /* keep margins sane; adjust if needed */
  }
            
            /* Ensure certification section fits on page */
            .certification {
                position: relative;
                bottom: 0;
            }
            
            /* Optimize container height for print */
            .clearance-container {
                min-height: auto;
                max-height: none;
            }
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4caf50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #2196f3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Print Clearance</button>
    <a href="view-business-applications.php" class="back-button no-print">← Back to Applications</a>
    
    <!-- Print Instructions -->
    <div class="print-instructions no-print" style="position: fixed; top: 70px; right: 20px; background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; font-size: 13px; max-width: 280px; z-index: 999;">
        <strong>📋 Print Instructions:</strong><br>
        • Enable "Background graphics" in print settings<br>
        • Use A4 paper size<br>
        • Set margins to "Default" or "Minimum"<br>
        • Disable headers/footers<br>
        • Scale: 100% (no scaling)
    </div>
    
    <div class="clearance-container">
        <div class="top-border"></div>
        
        <div class="clearance-content">
            <!-- Header Section - Background image contains the header text -->
            <div class="header-section">
                <!-- Header text removed - now part of background image -->
            </div>
            
            <!-- Main Title -->
            <div style="text-align: center; margin: 25px 0 30px 0; width: 100%;">
                <h1 style="font-size: 38px; color: #8b4513; font-weight: bold; margin-bottom: 12px; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);">
                    BARANGAY BUSINESS CLEARANCE
                </h1>
            </div>
            
            <!-- Salutation -->
            <div class="salutation">TO WHOM IT MAY CONCERN:</div>
            
            <!-- Introduction -->
            <div class="intro-text">
                This is to certify that the business or trade activity described below            
            </div>
            
            <!-- Business Details -->
            <div class="business-details">
                <div class="detail-row">
                    <div class="detail-value"><?php echo htmlspecialchars($application['business_name']); ?></div>
                    <div class="detail-label">(Business Name)</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-value"><?php echo htmlspecialchars($application['business_address']); ?></div>
                    <div class="detail-label">(Business Location)</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-value"><?php echo htmlspecialchars($application['owner_name']); ?></div>
                    <div class="detail-label">(President/Owner)</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-value"><?php echo htmlspecialchars($application['owner_address']); ?></div>
                    <div class="detail-label">(Address of Owner/Manager)</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-value"><?php echo htmlspecialchars($application['business_type']); ?></div>
                    <div class="detail-label">(Nature of Business)</div>
                </div>
            </div>
            
            <!-- Legal Text -->
            <div class="legal-text">
                <p>Proposed to be established in this Barangay and is being applied for a Barangay Business Clearance to be used in securing a corresponding Mayor’s Permit has been found to be in conformity with the provisions of existing Barangay Ordinances, rule and regulations being enforced in this Barangay.</p>
                
                <p>In view of the foregoing, the undersigned interposes no objection for the issuance of the corresponding Mayor’s Permit being applied for.</p>
                
                <p>This permit shall be valid until <?php echo $valid_until; ?> and can be cancelled/revoked anytime the establishment is found to have violated any law or ordinance within this Barangay.</p>
            </div>
            
            <!-- Issuance Info -->
            <div class="issuance-info">
                <div class="issuance-text">
                    Issued on <?php echo $current_date; ?> at Gumaoc East, City of San Jose Del Monte, Bulacan.
                </div>
                <div class="not-valid">NOT VALID WITHOUT DRY SEAL</div>
            </div>
            
            <!-- Certification -->
            <div class="certification">
                <div class="certified-by">Certified By:</div>
                <div class="signature-line"></div>
                <div class="official-name">HON. ROMMEL B. PITALBO</div>
                <div class="official-title">Punong Barangay</div>
            </div>
        </div>
        
        <div class="bottom-border"></div>
    </div>
</body>
</html> 