<?php
require_once 'auth_check.php';
$page_title = 'Reports';
$base_path = '../';

// Handle report submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
    $incident_type = $_POST['incident_type'] ?? '';
    $location = $_POST['location'] ?? '';
    $description = $_POST['description'] ?? '';
    $priority = $_POST['priority'] ?? 'medium';
    $contact_number = $_POST['contact_number'] ?? '';
    
    // Validation
    $errors = [];
    
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
        $stmt = $pdo->prepare("INSERT INTO user_reports (user_id, incident_type, location, description, priority, contact_number, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
        
        if ($stmt->execute([$user['id'], $incident_type, $location, $description, $priority, $contact_number])) {
            $success = "Report submitted successfully! We'll review it and get back to you soon.";
        } else {
            $errors[] = "Failed to submit report. Please try again.";
        }
    }
}

// Get user's reports
$stmt = $pdo->prepare("SELECT * FROM user_reports WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$reports = $stmt->fetchAll();

include '../includes/header.php';
?>

<style>
.reports-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 20px;
}

.reports-container {
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.page-header h1 {
    color: #333;
    margin-bottom: 10px;
    font-size: 32px;
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
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.section-title {
    color: #333;
    margin-bottom: 20px;
    font-size: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: #667eea;
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
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.btn-submit {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
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

@media (max-width: 768px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="reports-page">
    <div class="reports-container">
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
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="incident_type">Incident Type</label>
                        <select id="incident_type" name="incident_type" required>
                            <option value="">Select incident type</option>
                            <option value="Theft">Theft</option>
                            <option value="Vandalism">Vandalism</option>
                            <option value="Noise Complaint">Noise Complaint</option>
                            <option value="Traffic Violation">Traffic Violation</option>
                            <option value="Suspicious Activity">Suspicious Activity</option>
                            <option value="Infrastructure Issue">Infrastructure Issue</option>
                            <option value="Health Concern">Health Concern</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" placeholder="Enter exact location" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Provide detailed description of the incident" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="priority">Priority Level</label>
                        <select id="priority" name="priority" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="tel" id="contact_number" name="contact_number" placeholder="Your contact number" required>
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

<?php include '../includes/footer.php'; ?> 