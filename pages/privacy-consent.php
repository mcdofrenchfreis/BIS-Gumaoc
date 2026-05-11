<?php
session_start();
require_once '../includes/db_connect.php';

// Get the return URL from query parameter
$returnUrl = $_GET['return'] ?? '../pages/root-landing.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['consent_given']) && $_POST['consent_given'] === 'yes') {
        $_SESSION['privacy_consent'] = true;
        $_SESSION['privacy_consent_time'] = time();
        
        // Redirect to the intended page
        header('Location: ' . $returnUrl);
        exit;
    } else {
        $error = 'You must provide consent to continue.';
    }
}

$pageTitle = 'Privacy Consent';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Barangay Information System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .consent-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .consent-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
        }
        
        .consent-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .consent-body {
            padding: 40px;
        }
        
        .privacy-content {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            border-left: 4px solid #4f46e5;
        }
        
        .consent-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn-consent {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-accept {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .btn-accept:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
        }
        
        .btn-decline {
            background: #ef4444;
            color: white;
        }
        
        .btn-decline:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .section-title {
            color: #4f46e5;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .info-item {
            margin-bottom: 15px;
            padding-left: 20px;
            position: relative;
        }
        
        .info-item:before {
            content: '•';
            color: #4f46e5;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
    </style>
</head>
<body>
    <div class="consent-container">
        <div class="consent-card">
            <div class="consent-header">
                <i class="fas fa-shield-alt fa-3x mb-3"></i>
                <h1 class="mb-3">Privacy & Data Protection Consent</h1>
                <p class="mb-0">Barangay Information System - Gumaoc</p>
            </div>
            
            <div class="consent-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <div class="privacy-content">
                    <h3 class="section-title">Privacy Notice</h3>
                    <p class="mb-4">
                        The Barangay Information System is committed to protecting your personal data and privacy. 
                        This notice explains how we collect, use, and protect your information in accordance with 
                        the Data Privacy Act of 2012.
                    </p>
                    
                    <h4 class="section-title">Information We Collect</h4>
                    <div class="info-item">
                        <strong>Personal Information:</strong> Name, age, birth date, address, contact details
                    </div>
                    <div class="info-item">
                        <strong>Government IDs:</strong> Valid identification numbers for verification
                    </div>
                    <div class="info-item">
                        <strong>Service Records:</strong> History of barangay services availed
                    </div>
                    <div class="info-item">
                        <strong>Biometric Data:</strong> RFID/fingerprint data for system access (if applicable)
                    </div>
                    
                    <h4 class="section-title">How We Use Your Information</h4>
                    <div class="info-item">
                        Provide barangay services and assistance
                    </div>
                    <div class="info-item">
                        Maintain accurate resident records
                    </div>
                    <div class="info-item">
                        Process requests for certificates and clearances
                    </div>
                    <div class="info-item">
                        Improve service delivery and planning
                    </div>
                    
                    <h4 class="section-title">Data Protection</h4>
                    <p class="mb-3">
                        We implement appropriate technical and organizational measures to protect your personal data 
                        against unauthorized access, alteration, disclosure, or destruction.
                    </p>
                    
                    <h4 class="section-title">Your Rights</h4>
                    <div class="info-item">
                        Right to access your personal data
                    </div>
                    <div class="info-item">
                        Right to correct inaccurate information
                    </div>
                    <div class="info-item">
                        Right to request data deletion (subject to legal requirements)
                    </div>
                    <div class="info-item">
                        Right to file complaints with the National Privacy Commission
                    </div>
                    
                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Consent:</strong> By proceeding, you acknowledge that you have read, understood, 
                        and agree to the collection and processing of your personal data as described above.
                    </div>
                </div>
                
                <form method="POST" action="">
                    <div class="consent-buttons">
                        <button type="submit" name="consent_given" value="yes" class="btn-consent btn-accept">
                            <i class="fas fa-check me-2"></i>
                            I Agree & Continue
                        </button>
                        <button type="button" class="btn-consent btn-decline" onclick="window.history.back();">
                            <i class="fas fa-times me-2"></i>
                            Decline
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
