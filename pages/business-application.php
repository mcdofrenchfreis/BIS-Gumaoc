<?php
session_start();
$base_path = '../';
$page_title = 'Business Permit Application - Barangay Gumaoc East';
$header_title = 'Business Permit Application';
$header_subtitle = 'Apply for Business Permit in Barangay Gumaoc East';

// Check if this is an admin view
$admin_view = isset($_GET['admin_view']) ? (int)$_GET['admin_view'] : null;
$readonly = isset($_GET['readonly']) && $_GET['readonly'] === '1';
$application_data = null;

if ($admin_view) {
    // Admin is viewing - fetch the application data
    require_once '../includes/db_connect.php';
    $stmt = $pdo->prepare("SELECT * FROM business_applications WHERE id = ?");
    $stmt->execute([$admin_view]);
    $application_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application_data) {
        die("Application not found.");
    }
} else {
    // Regular user access - require authentication
    include '../includes/auth_check.php';
}

// Generate reference number if not exists
$reference_no = $application_data['reference_no'] ?? 'BA-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

// Only process form submission if not readonly
if ($_POST && !$readonly) {
    require_once '../includes/db_connect.php';
    
    try {
        // Get user_id from session
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$user_id) {
            throw new Exception("User session not found. Please log in again.");
        }
        
        // Map the form fields to the database structure
        $stmt = $pdo->prepare("INSERT INTO business_applications 
            (user_id, reference_no, application_date, first_name, middle_name, last_name, 
             business_name, business_type, business_address, business_location, owner_name, 
             owner_address, contact_number, or_number, ctc_number, years_operation, 
             investment_capital, status, submitted_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
        
        // Combine names for owner_name field
        $owner_name = trim($_POST['first_name'] . ' ' . ($_POST['middle_name'] ? $_POST['middle_name'] . ' ' : '') . $_POST['last_name']);
        
        $result = $stmt->execute([
            $user_id,
            $_POST['reference_no'],
            $_POST['application_date'],
            $_POST['first_name'],
            $_POST['middle_name'] ?: null,
            $_POST['last_name'],
            $_POST['business_name'],
            'General Business', // Default business type
            $_POST['business_location'], // business_address
            $_POST['business_location'], // business_location
            $owner_name, // owner_name (combined)
            $_POST['owner_address'],
            '09000000000', // Default contact number
            $_POST['or_number'],
            $_POST['ctc_number'],
            1, // Default years_operation
            0.00 // Default investment_capital
        ]);
        
        if ($result) {
            $_SESSION['success'] = "Business permit application submitted successfully! Reference: " . $_POST['reference_no'];
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            throw new Exception("Failed to insert application data.");
        }
        
    } catch (PDOException $e) {
        // Log the actual error for debugging
        error_log("Business Application Error: " . $e->getMessage());
        $error = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="container">
    <?php if ($admin_view): ?>
    <div class="admin-view-banner">
        <div class="status-info">
            <h2>📋 Business Application Review</h2>
            <div class="reference-badge">
                REF: <?php echo htmlspecialchars($application_data['reference_no']); ?>
            </div>
            <div class="status-badge status-<?php echo $application_data['status']; ?>">
                <?php echo ucfirst($application_data['status']); ?>
            </div>
        </div>
        <div class="submission-info">
            <p><strong>Submitted:</strong> <?php echo date('F j, Y g:i A', strtotime($application_data['submitted_at'])); ?></p>
            <p><strong>Business:</strong> <?php echo htmlspecialchars($application_data['business_name']); ?></p>
            <p><strong>Owner:</strong> <?php echo htmlspecialchars($application_data['first_name'] . ' ' . $application_data['last_name']); ?></p>
        </div>
        <button onclick="window.close()" class="back-btn">✕ Close</button>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="success-alert">
        <div class="alert-content">
            <span class="alert-icon">✅</span>
            <span class="alert-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
    <div class="error-alert">
        <div class="alert-content">
            <span class="alert-icon">❌</span>
            <span class="alert-message"><?php echo $error; ?></span>
        </div>
    </div>
    <?php endif; ?>

    <section class="section">
        <div class="form-header">
            <div class="form-title">
                <h1>🏢 Business Permit Application</h1>
                <p>Complete all required fields to submit your business permit application</p>
            </div>
            <div class="form-ref">
                <div class="ref-number">
                    <span class="ref-label">Reference No.</span>
                    <span class="ref-value"><?php echo htmlspecialchars($reference_no); ?></span>
                </div>
            </div>
        </div>

        <form class="business-form" <?php echo $readonly ? '' : 'method="POST"'; ?>>
            <input type="hidden" name="reference_no" value="<?php echo htmlspecialchars($reference_no); ?>">
            
            <div class="form-grid">
                <!-- Date -->
                <div class="form-group">
                    <label for="application_date">📅 Date <span class="required">*</span></label>
                    <input type="date" id="application_date" name="application_date" 
                           value="<?php echo htmlspecialchars($application_data['application_date'] ?? date('Y-m-d')); ?>" 
                           <?php echo $readonly ? 'readonly' : 'required'; ?>>
                </div>

                <!-- Empty space for alignment -->
                <div class="form-group"></div>

                <!-- Name Row - Full Width -->
                <div class="form-group full-width">
                    <div class="name-row">
                        <div class="name-field">
                            <label for="first_name">👤 First Name <span class="required">*</span></label>
                            <input type="text" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($application_data['first_name'] ?? ''); ?>" 
                                   placeholder="Enter first name"
                                   <?php echo $readonly ? 'readonly' : 'required'; ?>>
                        </div>
                        <div class="name-field">
                            <label for="middle_name">👤 Middle Name <span class="optional">(Optional)</span></label>
                            <input type="text" id="middle_name" name="middle_name" 
                                   value="<?php echo htmlspecialchars($application_data['middle_name'] ?? ''); ?>" 
                                   placeholder="Enter middle name"
                                   <?php echo $readonly ? 'readonly' : ''; ?>>
                        </div>
                        <div class="name-field">
                            <label for="last_name">👤 Last Name <span class="required">*</span></label>
                            <input type="text" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($application_data['last_name'] ?? ''); ?>" 
                                   placeholder="Enter last name"
                                   <?php echo $readonly ? 'readonly' : 'required'; ?>>
                        </div>
                    </div>
                </div>

                <!-- Business Location -->
                <div class="form-group full-width">
                    <label for="business_location">🏢 Location of Business <span class="required">*</span></label>
                    <textarea id="business_location" name="business_location" rows="3" 
                              placeholder="Enter complete business address..."
                              <?php echo $readonly ? 'readonly' : 'required'; ?>><?php echo htmlspecialchars($application_data['business_location'] ?? ''); ?></textarea>
                </div>

                <!-- Business Name -->
                <div class="form-group full-width">
                    <label for="business_name">🏪 Name of Business <span class="required">*</span></label>
                    <input type="text" id="business_name" name="business_name" 
                           value="<?php echo htmlspecialchars($application_data['business_name'] ?? ''); ?>" 
                           placeholder="Enter business name"
                           <?php echo $readonly ? 'readonly' : 'required'; ?>>
                </div>

                <!-- Owner Address -->
                <div class="form-group full-width">
                    <label for="owner_address">🏠 House Address of Owner <span class="required">*</span></label>
                    <textarea id="owner_address" name="owner_address" rows="3" 
                              placeholder="Enter complete home address..."
                              <?php echo $readonly ? 'readonly' : 'required'; ?>><?php echo htmlspecialchars($application_data['owner_address'] ?? ''); ?></textarea>
                </div>

                <!-- OR Number -->
                <div class="form-group">
                    <label for="or_number">🧾 OR No. <span class="required">*</span></label>
                    <input type="text" id="or_number" name="or_number" 
                           value="<?php echo htmlspecialchars($application_data['or_number'] ?? ''); ?>" 
                           placeholder="Enter Official Receipt number"
                           <?php echo $readonly ? 'readonly' : 'required'; ?>>
                </div>

                <!-- CTC Number -->
                <div class="form-group">
                    <label for="ctc_number">📋 CTC No. <span class="required">*</span></label>
                    <input type="text" id="ctc_number" name="ctc_number" 
                           value="<?php echo htmlspecialchars($application_data['ctc_number'] ?? ''); ?>" 
                           placeholder="Enter Community Tax Certificate number"
                           <?php echo $readonly ? 'readonly' : 'required'; ?>>
                </div>
            </div>

            <?php if (!$readonly): ?>
            <div class="form-actions">
                <button type="submit" name="submit_application" class="submit-btn">
                    <span class="btn-icon">📝</span>
                    <span class="btn-text">Submit Application</span>
                </button>
                <button type="reset" class="reset-btn">
                    <span class="btn-icon">🔄</span>
                    <span class="btn-text">Reset Form</span>
                </button>
            </div>
            <?php endif; ?>
        </form>
    </section>
</div>

<style>
/* Global Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: #2c3e50;
    background: white;
    min-height: 100vh;
    margin: 0;
    padding: 0;
}

/* Container Styles */
.container {
    max-width: 900px;
    margin: 20px auto;
    padding: 20px 15px;
    background: rgba(255, 255, 255, 0.98);
    border-radius: 24px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: relative;
}

.section {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 2.5rem;
    box-shadow: 
        0 20px 60px rgba(0, 0, 0, 0.1),
        0 8px 32px rgba(0, 0, 0, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: relative;
    overflow: hidden;
}

.section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #4CAF50, #45a049, #2e7d32);
    z-index: 1;
}

/* Form Header */
.form-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid rgba(76, 175, 80, 0.1);
}

.form-title h1 {
    font-size: 2.2rem;
    font-weight: 700;
    color: #2d5a27;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-title p {
    color: #666;
    font-size: 1rem;
    font-weight: 500;
}

.form-ref .ref-number {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
}

.ref-label {
    display: block;
    font-size: 0.8rem;
    opacity: 0.9;
    margin-bottom: 0.3rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.ref-value {
    display: block;
    font-size: 1.1rem;
    font-weight: 700;
    font-family: 'Courier New', monospace;
}

/* Admin View Banner */
.admin-view-banner {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: white;
    padding: 2rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 10px 40px rgba(30, 60, 114, 0.3);
    position: relative;
    overflow: hidden;
}

.admin-view-banner::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
    animation: shimmer 3s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.status-info h2 {
    margin: 0 0 1rem 0;
    font-size: 1.8rem;
    font-weight: 700;
}

.reference-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-family: 'Courier New', monospace;
    font-weight: 600;
    margin-bottom: 0.8rem;
    display: inline-block;
}

.status-badge {
    display: inline-block;
    padding: 0.6rem 1.2rem;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.status-pending {
    background: linear-gradient(135deg, #ffc107, #ffb300);
    color: #333;
}

.status-processing {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

.status-approved {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    color: white;
}

.status-rejected {
    background: linear-gradient(135deg, #dc3545, #bd2130);
    color: white;
}

.submission-info p {
    margin: 0.4rem 0;
    font-size: 1rem;
    opacity: 0.95;
}

.back-btn {
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 0.8rem 1.8rem;
    border-radius: 30px;
    cursor: pointer;
    font-weight: 700;
    font-size: 1rem;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.back-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

/* Alert Styles */
.success-alert, .error-alert {
    margin-bottom: 2rem;
    padding: 1.2rem 1.5rem;
    border-radius: 15px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    animation: slideIn 0.5s ease;
}

.success-alert {
    background: linear-gradient(135deg, rgba(76, 175, 80, 0.9), rgba(56, 142, 60, 0.9));
    color: white;
}

.error-alert {
    background: linear-gradient(135deg, rgba(244, 67, 54, 0.9), rgba(211, 47, 47, 0.9));
    color: white;
}

.alert-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.alert-icon {
    font-size: 1.3rem;
}

.alert-message {
    font-weight: 500;
    flex: 1;
}

@keyframes slideIn {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Form Styles */
.business-form {
    position: relative;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group {
    position: relative;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;
    margin-bottom: 0.8rem;
    font-weight: 600;
    color: #2d5a27;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.required {
    color: #e74c3c;
    font-weight: 700;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 1rem 1.2rem;
    border: 2px solid #e0e6ed;
    border-radius: 12px;
    font-size: 1rem;
    font-family: inherit;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #4CAF50;
    box-shadow: 
        0 0 0 3px rgba(76, 175, 80, 0.1),
        0 4px 12px rgba(76, 175, 80, 0.15);
    transform: translateY(-1px);
}

.form-group input:hover,
.form-group textarea:hover {
    border-color: #4CAF50;
    background: rgba(255, 255, 255, 1);
}

/* Readonly styles */
input[readonly], textarea[readonly] {
    background-color: #f8f9fa !important;
    border-color: #e9ecef !important;
    color: #6c757d !important;
    cursor: not-allowed !important;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2.5rem;
    padding-top: 2rem;
    border-top: 2px solid rgba(76, 175, 80, 0.1);
}

.submit-btn, .reset-btn {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 1rem 2rem;
    border: none;
    border-radius: 15px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    position: relative;
    overflow: hidden;
}

.submit-btn {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    box-shadow: 0 6px 20px rgba(76, 175, 80, 0.3);
}

.submit-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
    background: linear-gradient(135deg, #45a049, #4CAF50);
}

.reset-btn {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.3);
}

.reset-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
    background: linear-gradient(135deg, #5a6268, #6c757d);
}

.btn-icon {
    font-size: 1.2rem;
}

.btn-text {
    font-weight: 600;
}

/* Name Row Styling */
.name-row {
    display: flex;
    gap: 1rem;
    width: 100%;
}

.name-field {
    flex: 1;
    min-width: 0; /* Allows flex items to shrink below their content size */
}

.name-field:nth-child(2) {
    flex: 0.8; /* Make middle name field slightly smaller */
}

.optional {
    color: #6c757d;
    font-weight: 400;
    font-size: 0.9rem;
}

/* Responsive adjustments for name row */
@media (max-width: 768px) {
    .name-row {
        flex-direction: column;
        gap: 1rem;
    }
    
    .name-field:nth-child(2) {
        flex: 1; /* Reset flex on mobile */
    }
}

@media (max-width: 480px) {
    .name-row {
        gap: 0.8rem;
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        margin: 1rem auto;
        padding: 0 0.5rem;
    }
    
    .section {
        padding: 1.5rem;
        border-radius: 16px;
    }
    
    .form-header {
        flex-direction: column;
        gap: 1.5rem;
        text-align: center;
    }
    
    .form-title h1 {
        font-size: 1.8rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .submit-btn, .reset-btn {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
    
    .admin-view-banner {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
        padding: 1.5rem;
    }
    
    .submission-info {
        text-align: center;
    }
}

@media (max-width: 480px) {
    .form-title h1 {
        font-size: 1.5rem;
    }
    
    .form-group input,
    .form-group textarea {
        padding: 0.8rem 1rem;
        font-size: 0.9rem;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
