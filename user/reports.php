<?php
require_once 'auth_check.php';
$page_title = 'Reports';
$base_path = '../';
$current_page = 'reports';

// Don't include the main header, we'll create our own
// include '../includes/header.php';

// Handle report submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
    $incident_type = $_POST['incident_type'] ?? '';
    $location = $_POST['location'] ?? '';
    $description = $_POST['description'] ?? '';
    $priority = $_POST['priority'] ?? 'medium';
    $contact_number = $_POST['contact_number'] ?? '';
    
    // Handle file upload
    $proof_image = null;
    $upload_error = null;
    
    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['proof_image'];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $upload_error = "File upload failed. Please try again.";
        } else {
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = mime_content_type($file['tmp_name']);
            
            if (!in_array($file_type, $allowed_types)) {
                $upload_error = "Only JPEG, PNG, GIF, and WebP images are allowed.";
            } else {
                // Validate file size (max 5MB)
                $max_size = 5 * 1024 * 1024; // 5MB in bytes
                if ($file['size'] > $max_size) {
                    $upload_error = "File size must be less than 5MB.";
                } else {
                    // Create upload directory if it doesn't exist
                    $upload_dir = '../assets/images/reports/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    // Generate unique filename
                    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $proof_image = 'report_' . uniqid() . '_' . time() . '.' . $file_extension;
                    $upload_path = $upload_dir . $proof_image;
                    
                    // Move uploaded file
                    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                        $upload_error = "Failed to save uploaded file. Please try again.";
                        $proof_image = null;
                    }
                }
            }
        }
    }
    
    // Validation
    $errors = [];
    
    if ($upload_error) {
        $errors[] = $upload_error;
    }
    
    if (empty($incident_type)) {
        $errors[] = "Incident type is required";
    }
    
    if (empty($location)) {
        $errors[] = "Location is required";
    }
    
    if (empty($description)) {
        $errors[] = "Description is required";
    }
    
    if (empty($contact_number)) {
        $errors[] = "Contact number is required";
    }
    
    // If no errors, save report
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO user_reports (user_id, incident_type, location, description, priority, contact_number, proof_image, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
        
        if ($stmt->execute([$user['id'], $incident_type, $location, $description, $priority, $contact_number, $proof_image])) {
            $success = "Report submitted successfully! We'll review it and get back to you soon.";
            // Clear form data after successful submission
            $_POST = [];
        } else {
            $errors[] = "Failed to submit report. Please try again.";
            // If database insert failed and we uploaded a file, clean it up
            if ($proof_image && file_exists($upload_dir . $proof_image)) {
                unlink($upload_dir . $proof_image);
            }
        }
    }
}

// Get user's reports
$stmt = $pdo->prepare("SELECT * FROM user_reports WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$reports = $stmt->fetchAll();

// Use residents table data
$display_name = $user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Barangay Gumaoc East</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            min-height: 100vh;
            line-height: 1.6;
            opacity: 0;
            animation: fadeInPage 0.8s ease-out 0.3s forwards;
        }
        
        @keyframes fadeInPage {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        

        
        /* User Navbar */
        .user-navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(25px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.12);
        }
        
        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            height: 70px;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #333;
            font-weight: 700;
            font-size: 18px;
        }
        
        .brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: white;
            font-size: 18px;
        }
        
        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-item {
            position: relative;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            color: #666;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background: #f8f9fa;
            color: #333;
        }
        
        .nav-link.active {
            background: #e7f3ff;
            color: #0066cc;
        }
        
        .user-menu {
            position: relative;
        }
        
        .user-button {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border: none;
            background: #f8f9fa;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .user-button:hover {
            background: #e9ecef;
        }
        
        .user-avatar-small {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }
        
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 12px 0;
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .user-menu:hover .user-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .dropdown-item:hover {
            background: #f8f9fa;
        }
        
        .dropdown-divider {
            height: 1px;
            background: #e9ecef;
            margin: 8px 0;
        }
        
        /* Page Content */
        .page-content {
            padding-top: 90px;
            min-height: 100vh;
        }
.reports-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.page-header {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(25px);
    border-radius: 24px;
    padding: 40px;
    margin-bottom: 30px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
    text-align: center;
    border: 1px solid rgba(255, 255, 255, 0.4);
}

.page-header h1 {
    color: #333;
    margin-bottom: 10px;
    font-size: 36px;
    font-weight: 700;
    background: linear-gradient(135deg, #2e7d32, #4caf50);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.page-header p {
    color: #666;
    font-size: 18px;
}

.content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.report-form-section, .reports-list-section {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(25px);
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.4);
}

.section-title {
    color: #333;
    margin-bottom: 25px;
    font-size: 24px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-title i {
    color: #2e7d32;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 500;
}

.form-group input, .form-group select, .form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e1e5e9;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    outline: none;
    border-color: #2e7d32;
    box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.file-upload-wrapper {
    position: relative;
    display: inline-block;
    width: 100%;
}

.file-upload-input {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.file-upload-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 40px 20px;
    border: 2px dashed #e1e5e9;
    border-radius: 10px;
    background: #f8f9fa;
    color: #666;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.file-upload-label:hover {
    border-color: #2e7d32;
    background: rgba(46, 125, 50, 0.05);
    color: #2e7d32;
}

.file-upload-label.dragover {
    border-color: #2e7d32;
    background: rgba(46, 125, 50, 0.1);
    color: #2e7d32;
}

.file-upload-icon {
    font-size: 24px;
}

.file-upload-text {
    font-weight: 500;
}

.file-upload-hint {
    font-size: 12px;
    color: #999;
    margin-top: 5px;
}

.file-preview {
    margin-top: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    display: none;
}

.file-preview.show {
    display: block;
}

.file-preview-item {
    display: flex;
    align-items: center;
    gap: 15px;
}

.file-preview-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid #e1e5e9;
}

.file-preview-info {
    flex: 1;
}

.file-preview-name {
    font-weight: 500;
    color: #333;
    margin-bottom: 5px;
}

.file-preview-size {
    font-size: 12px;
    color: #666;
}

.file-preview-remove {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 8px 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 12px;
}

.file-preview-remove:hover {
    background: #c82333;
}

.btn-submit {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 8px 25px rgba(46, 125, 50, 0.3);
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(46, 125, 50, 0.4);
}

.error-message {
    background: #fee;
    color: #c33;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #fcc;
}

.success-message {
    background: #efe;
    color: #363;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #cfc;
}

.reports-list {
    max-height: 500px;
    overflow-y: auto;
}

.report-item {
    border: 1px solid #e1e5e9;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.report-item:hover {
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.report-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.report-type {
    font-weight: 600;
    color: #333;
}

.report-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-processing {
    background: #cce5ff;
    color: #004085;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.status-rejected {
    background: #f8d7da;
    color: #721c24;
}

.report-details {
    color: #666;
    font-size: 14px;
    margin-bottom: 10px;
}

.report-description {
    color: #333;
    line-height: 1.5;
    margin-bottom: 10px;
}

.report-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: #999;
}

.priority-badge {
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
}

.priority-high {
    background: #f8d7da;
    color: #721c24;
}

.priority-medium {
    background: #fff3cd;
    color: #856404;
}

.priority-low {
    background: #d4edda;
    color: #155724;
}

.report-proof {
    margin: 15px 0;
}

.proof-image-container {
    margin-top: 10px;
}

.proof-image {
    max-width: 200px;
    max-height: 150px;
    border-radius: 8px;
    border: 2px solid #e1e5e9;
    cursor: pointer;
    transition: all 0.3s ease;
    object-fit: cover;
}

.proof-image:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

/* Image Modal */
.image-modal {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.9);
    animation: fadeIn 0.3s ease;
}

.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    max-width: 90%;
    max-height: 90%;
}

.modal-image {
    width: 100%;
    height: auto;
    border-radius: 8px;
}

.close-modal {
    position: absolute;
    top: 20px;
    right: 30px;
    color: white;
    font-size: 30px;
    font-weight: bold;
    cursor: pointer;
    z-index: 2001;
}

.close-modal:hover {
    color: #ccc;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.back-btn {
    display: inline-block;
    padding: 12px 30px;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-bottom: 20px;
}

.back-btn:hover {
    background: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(108, 117, 125, 0.3);
    color: white;
}

.no-reports {
    text-align: center;
    color: #666;
    padding: 40px;
    font-style: italic;
}

/* Google Maps Integration Styles - REMOVED */

/* Responsive Design */
@media (max-width: 768px) {
    .navbar-nav {
        display: none;
    }
    
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .page-header {
        padding: 30px 20px;
    }
    
    .page-header h1 {
        font-size: 28px;
    }
    
    .report-form-section, .reports-list-section {
        padding: 30px 20px;
    }
    
    .file-upload-label {
        padding: 30px 15px;
    }
    
    .file-preview-image {
        width: 60px;
        height: 60px;
    }
    
    .proof-image {
        max-width: 150px;
        max-height: 100px;
    }
    
    .modal-content {
        max-width: 95%;
        max-height: 85%;
    }
    
    .close-modal {
        top: 10px;
        right: 15px;
        font-size: 25px;
    }
}
</style>
</head>
<body>


    <?php include 'navbar_component.php'; ?>
    
    <!-- Page Content -->
    <div class="page-content">
        <div class="reports-page">
            <!-- Page Header -->
            <div class="page-header">
                <h1>📊 Reports Management</h1>
                <p>Submit incident reports and track their status</p>
            </div>

        <!-- Back Button -->
        <a href="dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Report Form Section -->
            <div class="report-form-section">
                <h2 class="section-title">
                    <i class="fas fa-plus-circle"></i>
                    Submit New Report
                </h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="success-message">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="incident_type">Incident Type</label>
                        <select id="incident_type" name="incident_type" required>
                            <option value="">Select incident type</option>
                            <option value="Theft" <?php echo (isset($_POST['incident_type']) && $_POST['incident_type'] === 'Theft') ? 'selected' : ''; ?>>Theft</option>
                            <option value="Vandalism" <?php echo (isset($_POST['incident_type']) && $_POST['incident_type'] === 'Vandalism') ? 'selected' : ''; ?>>Vandalism</option>
                            <option value="Noise Complaint" <?php echo (isset($_POST['incident_type']) && $_POST['incident_type'] === 'Noise Complaint') ? 'selected' : ''; ?>>Noise Complaint</option>
                            <option value="Traffic Violation" <?php echo (isset($_POST['incident_type']) && $_POST['incident_type'] === 'Traffic Violation') ? 'selected' : ''; ?>>Traffic Violation</option>
                            <option value="Suspicious Activity" <?php echo (isset($_POST['incident_type']) && $_POST['incident_type'] === 'Suspicious Activity') ? 'selected' : ''; ?>>Suspicious Activity</option>
                            <option value="Infrastructure Issue" <?php echo (isset($_POST['incident_type']) && $_POST['incident_type'] === 'Infrastructure Issue') ? 'selected' : ''; ?>>Infrastructure Issue</option>
                            <option value="Health Concern" <?php echo (isset($_POST['incident_type']) && $_POST['incident_type'] === 'Health Concern') ? 'selected' : ''; ?>>Health Concern</option>
                            <option value="Other" <?php echo (isset($_POST['incident_type']) && $_POST['incident_type'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" placeholder="Enter exact location" value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Provide detailed description of the incident" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="priority">Priority Level</label>
                        <select id="priority" name="priority" required>
                            <option value="low" <?php echo (isset($_POST['priority']) && $_POST['priority'] === 'low') ? 'selected' : ''; ?>>Low</option>
                            <option value="medium" <?php echo (!isset($_POST['priority']) || $_POST['priority'] === 'medium') ? 'selected' : ''; ?>>Medium</option>
                            <option value="high" <?php echo (isset($_POST['priority']) && $_POST['priority'] === 'high') ? 'selected' : ''; ?>>High</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="tel" id="contact_number" name="contact_number" placeholder="Your contact number" value="<?php echo htmlspecialchars($_POST['contact_number'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="proof_image">Proof Image (Optional)</label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="proof_image" name="proof_image" class="file-upload-input" accept="image/*">
                            <label for="proof_image" class="file-upload-label">
                                <div class="file-upload-content">
                                    <i class="fas fa-camera file-upload-icon"></i>
                                    <div class="file-upload-text">Click to upload or drag and drop</div>
                                    <div class="file-upload-hint">PNG, JPG, GIF, WebP up to 5MB</div>
                                </div>
                            </label>
                        </div>
                        <div class="file-preview" id="filePreview"></div>
                    </div>
                    
                    <button type="submit" name="submit_report" class="btn-submit">
                        <i class="fas fa-paper-plane"></i>
                        Submit Report
                    </button>
                </form>
            </div>
            
            <!-- Reports List Section -->
            <div class="reports-list-section">
                <h2 class="section-title">
                    <i class="fas fa-list"></i>
                    Your Reports
                </h2>
                
                <div class="reports-list">
                    <?php if (empty($reports)): ?>
                        <div class="no-reports">
                            <p>No reports submitted yet.</p>
                            <p>Submit your first report using the form on the left.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($reports as $report): ?>
                            <div class="report-item">
                                <div class="report-header">
                                    <span class="report-type"><?php echo htmlspecialchars($report['incident_type']); ?></span>
                                    <span class="report-status status-<?php echo $report['status']; ?>">
                                        <?php echo ucfirst($report['status']); ?>
                                    </span>
                                </div>
                                
                                <div class="report-details">
                                    <strong>Location:</strong> <?php echo htmlspecialchars($report['location']); ?>
                                </div>
                                
                                <div class="report-description">
                                    <?php echo htmlspecialchars($report['description']); ?>
                                </div>
                                
                                <?php if (!empty($report['proof_image'])): ?>
                                    <div class="report-proof">
                                        <strong>Proof:</strong>
                                        <div class="proof-image-container">
                                            <img src="../assets/images/reports/<?php echo htmlspecialchars($report['proof_image']); ?>" 
                                                 alt="Proof image" class="proof-image" 
                                                 onclick="openImageModal(this.src)">
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="report-meta">
                                    <span class="priority-badge priority-<?php echo $report['priority']; ?>">
                                        <?php echo ucfirst($report['priority']); ?> Priority
                                    </span>
                                    <span><?php echo date('M d, Y H:i', strtotime($report['created_at'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        </div>
    </div>
    
    <!-- Image Modal -->
    <div id="imageModal" class="image-modal">
        <span class="close-modal" onclick="closeImageModal()">&times;</span>
        <div class="modal-content">
            <img id="modalImage" class="modal-image" src="" alt="Full size image">
        </div>
    </div>
    
    <script>
        // Page loader functionality
        document.addEventListener('DOMContentLoaded', function() {
            // File upload functionality
            const fileInput = document.getElementById('proof_image');
            const fileLabel = document.querySelector('.file-upload-label');
            const filePreview = document.getElementById('filePreview');
            
            // Handle file selection
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    displayFilePreview(file);
                }
            });
            
            // Handle drag and drop
            fileLabel.addEventListener('dragover', function(e) {
                e.preventDefault();
                fileLabel.classList.add('dragover');
            });
            
            fileLabel.addEventListener('dragleave', function(e) {
                e.preventDefault();
                fileLabel.classList.remove('dragover');
            });
            
            fileLabel.addEventListener('drop', function(e) {
                e.preventDefault();
                fileLabel.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const file = files[0];
                    if (file.type.startsWith('image/')) {
                        fileInput.files = files;
                        displayFilePreview(file);
                    }
                }
            });
            
            function displayFilePreview(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = `
                        <div class="file-preview-item">
                            <img src="${e.target.result}" alt="Preview" class="file-preview-image">
                            <div class="file-preview-info">
                                <div class="file-preview-name">${file.name}</div>
                                <div class="file-preview-size">${formatFileSize(file.size)}</div>
                            </div>
                            <button type="button" class="file-preview-remove" onclick="removeFile()">
                                <i class="fas fa-times"></i> Remove
                            </button>
                        </div>
                    `;
                    filePreview.innerHTML = preview;
                    filePreview.classList.add('show');
                };
                reader.readAsDataURL(file);
            }
            
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
            
            // Make formatFileSize available globally
            window.formatFileSize = formatFileSize;
        });
        
        // Remove file function
        function removeFile() {
            const fileInput = document.getElementById('proof_image');
            const filePreview = document.getElementById('filePreview');
            
            fileInput.value = '';
            filePreview.classList.remove('show');
            filePreview.innerHTML = '';
        }
        
        // Image modal functions
        function openImageModal(src) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            
            modalImage.src = src;
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Close modal when clicking outside the image
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>
    
</body>
</html> 