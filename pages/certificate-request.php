<?php
session_start();
$base_path = '../';
$page_title = 'Certificate Request - Barangay Gumaoc East';
$header_title = 'Certificate Request Form';
$header_subtitle = 'Request for Barangay Certificates';

// Check if this is an admin view
$admin_view = isset($_GET['admin_view']) ? (int)$_GET['admin_view'] : null;
$readonly = isset($_GET['readonly']) && $_GET['readonly'] === '1';
$request_data = null;

if ($admin_view) {
    include '../includes/db_connect.php';
    $stmt = $pdo->prepare("SELECT * FROM certificate_requests WHERE id = ?");
    $stmt->execute([$admin_view]);
    $request_data = $stmt->fetch();
    
    if ($request_data) {
        $header_title = 'Certificate Request Details - ID #' . str_pad($admin_view, 5, '0', STR_PAD_LEFT);
        $header_subtitle = 'Submitted on ' . date('F j, Y \a\t g:i A', strtotime($request_data['submitted_at']));
    }
}

include '../includes/header.php';
?>

<div class="container">
  <div class="section">
    <?php if (isset($_SESSION['success'])): ?>
      <div id="toastOverlay" class="toast-overlay">
        <div id="successToast" class="toast toast-success">
          <div class="toast-content">
            <span class="toast-icon">✅</span>
            <span class="toast-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
            <button class="toast-close" onclick="closeToast('successToast')">&times;</button>
          </div>
        </div>
      </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
      <div id="toastOverlay" class="toast-overlay">
        <div id="errorToast" class="toast toast-error">
          <div class="toast-content">
            <span class="toast-icon">❌</span>
            <span class="toast-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
            <button class="toast-close" onclick="closeToast('errorToast')">&times;</button>
          </div>
        </div>
      </div>
    <?php endif; ?>
    
    <?php if ($admin_view && $request_data): ?>
      <div class="admin-view-banner">
        <div class="status-info">
          <span class="status-badge status-<?php echo $request_data['status']; ?>">
            Status: <?php echo ucfirst($request_data['status']); ?>
          </span>
          <span class="submission-info">
            Submitted: <?php echo date('M j, Y g:i A', strtotime($request_data['submitted_at'])); ?>
          </span>
        </div>
        <?php if (!$readonly): ?>
          <a href="../admin/view-certificate-requests.php" class="back-btn">← Back to Admin</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    
    <form id="certificateForm" class="certificate-form" method="POST" action="process_certificate_request.php" <?php echo $readonly ? 'style="pointer-events: none;"' : ''; ?>>

      <fieldset>
        <legend>PAKILAGYAN NG CHECK ANG NAAAKMANG KAILANGAN</legend>
        
        <div class="certificate-types">
          <label class="certificate-option">
            <input type="radio" name="certificateType" value="BRGY. CLEARANCE" <?php echo ($request_data && $request_data['certificate_type'] === 'BRGY. CLEARANCE') ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>>
            <span class="checkmark"></span>
            BRGY. CLEARANCE
          </label>
          
          <label class="certificate-option">
            <input type="radio" name="certificateType" value="BRGY. INDIGENCY" <?php echo ($request_data && $request_data['certificate_type'] === 'BRGY. INDIGENCY') ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>>
            <span class="checkmark"></span>
            BRGY. INDIGENCY
          </label>
          
          <label class="certificate-option">
            <input type="radio" name="certificateType" value="CEDULA" <?php echo ($request_data && $request_data['certificate_type'] === 'CEDULA') ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>>
            <span class="checkmark"></span>
            CEDULA
          </label>
          
          <label class="certificate-option">
            <input type="radio" name="certificateType" value="PROOF OF RESIDENCY" <?php echo ($request_data && $request_data['certificate_type'] === 'PROOF OF RESIDENCY') ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>>
            <span class="checkmark"></span>
            PROOF OF RESIDENCY
          </label>
        </div>
      </fieldset>

      <fieldset>
        <legend>Personal Information</legend>
        
        <div class="form-group">
          <label for="requestDate">Date *</label>
          <input type="date" id="requestDate" name="requestDate" required>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="firstName">First Name *</label>
            <input type="text" id="firstName" name="firstName" required placeholder="Enter first name" 
                   value="<?php echo $request_data ? htmlspecialchars(explode(' ', $request_data['full_name'])[0] ?? '') : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="middleName">Middle Name</label>
            <input type="text" id="middleName" name="middleName" placeholder="Enter middle name" 
                   value="<?php echo $request_data ? htmlspecialchars(implode(' ', array_slice(explode(' ', $request_data['full_name']), 1, -1))) : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="lastName">Last Name *</label>
            <?php 
            $lastName = '';
            if ($request_data && !empty($request_data['full_name'])) {
                $nameParts = explode(' ', $request_data['full_name']);
                $lastName = end($nameParts);
            }
            ?>
            <input type="text" id="lastName" name="lastName" required placeholder="Enter last name" 
                   value="<?php echo htmlspecialchars($lastName); ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="form-grid-two">
          <div class="form-group">
            <label for="address1">Address Line 1 *</label>
            <input type="text" id="address1" name="address1" required placeholder="Street/House Number" 
                   value="<?php echo $request_data ? htmlspecialchars($request_data['address']) : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="address2">Address Line 2</label>
            <input type="text" id="address2" name="address2" placeholder="Barangay/City/Province" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="mobileNumber">Mobile Number</label>
            <input type="tel" id="mobileNumber" name="mobileNumber" placeholder="09XX-XXX-XXXX" 
                   value="<?php echo $request_data ? htmlspecialchars($request_data['mobile_number']) : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="civilStatus">Civil Status *</label>
            <select id="civilStatus" name="civilStatus" required <?php echo $readonly ? 'disabled' : ''; ?>>
              <option value="">Select Civil Status</option>
              <option value="Single" <?php echo ($request_data && $request_data['civil_status'] === 'Single') ? 'selected' : ''; ?>>Single</option>
              <option value="Married" <?php echo ($request_data && $request_data['civil_status'] === 'Married') ? 'selected' : ''; ?>>Married</option>
              <option value="Divorced" <?php echo ($request_data && $request_data['civil_status'] === 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
              <option value="Widowed" <?php echo ($request_data && $request_data['civil_status'] === 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
              <option value="Separated" <?php echo ($request_data && $request_data['civil_status'] === 'Separated') ? 'selected' : ''; ?>>Separated</option>
            </select>
          </div>

          <div class="form-group">
            <label for="gender">Gender *</label>
            <select id="gender" name="gender" required <?php echo $readonly ? 'disabled' : ''; ?>>
              <option value="">Select Gender</option>
              <option value="Male" <?php echo ($request_data && $request_data['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
              <option value="Female" <?php echo ($request_data && $request_data['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
            </select>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="birthdate">Birthdate *</label>
            <input type="date" id="birthdate" name="birthdate" required 
                   value="<?php echo $request_data ? $request_data['birth_date'] : ''; ?>" 
                   onchange="calculateAge()"
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="age">Age</label>
            <?php 
            $calculated_age = '';
            if ($request_data && !empty($request_data['birth_date'])) {
                $birth_date = new DateTime($request_data['birth_date']);
                $today = new DateTime();
                $calculated_age = $today->diff($birth_date)->y;
            }
            ?>
            <input type="number" id="age" name="age" readonly placeholder="Auto-calculated" 
                   value="<?php echo $calculated_age; ?>">
          </div>

          <div class="form-group">
            <label for="birthplace">Birthplace *</label>
            <input type="text" id="birthplace" name="birthplace" required placeholder="Place of birth" 
                   value="<?php echo $request_data ? htmlspecialchars($request_data['birth_place']) : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="citizenship">Citizenship</label>
            <input type="text" id="citizenship" name="citizenship" placeholder="Filipino" 
                   value="<?php echo $request_data ? htmlspecialchars($request_data['citizenship']) : 'Filipino'; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="yearsOfResidence">Years of Residence</label>
            <input type="number" id="yearsOfResidence" name="yearsOfResidence" placeholder="Number of years" min="0"
                   value="<?php echo $request_data ? $request_data['years_of_residence'] : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="purpose">Purpose *</label>
            <input type="text" id="purpose" name="purpose" required placeholder="Purpose of certificate request" 
                   value="<?php echo $request_data ? htmlspecialchars($request_data['purpose']) : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>
      </fieldset>

      <div class="form-actions">
        <?php if (!$readonly): ?>
          <button type="reset" class="btn btn-secondary">Clear Form</button>
          <button type="submit" class="btn btn-primary">Submit Request</button>
        <?php else: ?>
          <a href="../admin/view-certificate-requests.php" class="btn btn-secondary">← Back to Admin Dashboard</a>
          <button type="button" class="btn btn-primary" onclick="window.print()">🖨️ Print Form</button>
        <?php endif; ?>
      </div>

    </form>
  </div>
</div>

<style>
/* Toast Notifications */
.toast-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(5px);
  z-index: 999;
  opacity: 0;
  transition: all 0.3s ease;
  pointer-events: none;
}

.toast-overlay.show {
  opacity: 1;
  pointer-events: auto;
}

.toast {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) scale(0.8);
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(15px);
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  z-index: 1000;
  opacity: 0;
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  min-width: 400px;
  max-width: 600px;
  width: 90%;
}

.toast.show {
  transform: translate(-50%, -50%) scale(1);
  opacity: 1;
}

.toast-content {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 24px 28px;
}

.toast-success {
  border-left: 5px solid #28a745;
  box-shadow: 0 20px 60px rgba(40, 167, 69, 0.2);
}

.toast-error {
  border-left: 5px solid #dc3545;
  box-shadow: 0 20px 60px rgba(220, 53, 69, 0.2);
}

.toast-icon {
  font-size: 24px;
  flex-shrink: 0;
}

.toast-message {
  flex: 1;
  font-weight: 500;
  color: #333;
  line-height: 1.5;
  font-size: 16px;
}

.toast-close {
  background: none;
  border: none;
  font-size: 24px;
  color: #999;
  cursor: pointer;
  padding: 4px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.toast-close:hover {
  background: rgba(0, 0, 0, 0.1);
  color: #666;
  transform: scale(1.1);
}

@media (max-width: 768px) {
  .toast {
    min-width: 320px;
    max-width: 90%;
    margin: 0 20px;
  }
  
  .toast-content {
    padding: 20px 24px;
  }
  
  .toast-message {
    font-size: 14px;
  }
  
  .toast-icon {
    font-size: 20px;
  }
}

@media (max-width: 480px) {
  .toast {
    min-width: 280px;
  }
  
  .toast-content {
    padding: 18px 20px;
    gap: 12px;
  }
}

/* Global Styles */
* {
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

/* Container and Layout */
.container {
  max-width: 1000px;
  margin: 20px auto;
  padding: 20px 15px;
  background: rgba(255, 255, 255, 0.98);
  border-radius: 24px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.section {
  margin-bottom: 25px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Admin View Banner */
.admin-view-banner {
  background: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%);
  color: white;
  padding: 1.5rem;
  border-radius: 12px;
  margin-bottom: 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  box-shadow: 0 8px 25px rgba(46, 125, 50, 0.3);
}

.status-info {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
}

.status-badge {
  padding: 0.5rem 1rem;
  border-radius: 25px;
  font-weight: 600;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.status-pending { background: #fff3cd; color: #856404; }
.status-processing { background: #cce5ff; color: #0066cc; }
.status-ready { background: #d4edda; color: #155724; }
.status-released { background: #e2e3e5; color: #383d41; }

.submission-info {
  background: rgba(255, 255, 255, 0.2);
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.9rem;
}

.back-btn {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  padding: 0.8rem 1.5rem;
  border-radius: 25px;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

/* Readonly Form Styling */
form[style*="pointer-events: none"] .form-group input,
form[style*="pointer-events: none"] .form-group select,
form[style*="pointer-events: none"] .certificate-option {
  background: #f8f9fa !important;
  border-color: #e9ecef !important;
  color: #6c757d !important;
}

form[style*="pointer-events: none"] .certificate-option.selected input[type="radio"]:checked + span {
  background: #28a745 !important;
}

form[style*="pointer-events: none"] .certificate-option {
  background: rgba(40, 167, 69, 0.1) !important;
  border-color: #28a745 !important;
  color: #155724 !important;
}

.form-group select:disabled {
  background: #f8f9fa !important;
  color: #6c757d !important;
  cursor: not-allowed;
}

/* Form Styling */
.certificate-form {
  width: 100%;
}

fieldset {
  border: 2px solid #e9ecef;
  border-radius: 15px;
  padding: 30px 25px;
  margin-bottom: 25px;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(5px);
}

legend {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
  padding: 10px 20px;
  border-radius: 25px;
  font-weight: 600;
  font-size: 1rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border: none;
}

/* Certificate Types */
.certificate-types {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.certificate-option {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 20px;
  background: #f8f9fa;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 500;
  font-size: 16px;
}

.certificate-option:hover {
  background: rgba(40, 167, 69, 0.1);
  border-color: #28a745;
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
}

.certificate-option input[type="radio"] {
  width: 20px;
  height: 20px;
  accent-color: #28a745;
  cursor: pointer;
}

.certificate-option input[type="radio"]:checked + .checkmark {
  background: #28a745;
}

/* Form Groups */
.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #495057;
  font-size: 14px;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 12px 15px;
  border: 2px solid #e9ecef;
  border-radius: 10px;
  font-size: 14px;
  transition: all 0.3s ease;
  background: #fff;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #28a745;
  box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
}

.form-group input[readonly] {
  background: #f8f9fa;
  color: #6c757d;
  cursor: not-allowed;
}

/* Form Grid */
.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.form-grid-two {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

/* Buttons */
.form-actions {
  display: flex;
  gap: 15px;
  justify-content: center;
  margin-top: 30px;
  flex-wrap: wrap;
}

.btn {
  padding: 15px 30px;
  border: none;
  border-radius: 50px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  text-decoration: none;
  display: inline-block;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 1px;
  min-width: 150px;
}

.btn-primary {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
  box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

.btn-secondary {
  background: linear-gradient(135deg, #6c757d, #495057);
  color: white;
  box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
}

.btn-secondary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
}

/* Print Styles */
@media print {
  .admin-view-banner,
  .form-actions {
    display: none !important;
  }
  
  .container {
    box-shadow: none;
    background: white;
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .container {
    margin: 10px;
    padding: 20px 15px;
    border-radius: 16px;
  }
  
  fieldset {
    padding: 20px 15px;
  }
  
  legend {
    font-size: 0.9rem;
    padding: 8px 16px;
  }
  
  .certificate-types {
    grid-template-columns: 1fr;
    gap: 15px;
  }
  
  .form-grid,
  .form-grid-two {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
  }
  
  .admin-view-banner {
    flex-direction: column;
    text-align: center;
  }
  
  .status-info {
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .certificate-option {
    padding: 15px;
    font-size: 14px;
  }
  
  .form-group input,
  .form-group select {
    padding: 10px 12px;
    font-size: 13px;
  }
}
</style>

<script>
// Toast notification functions
function showToast(toastId) {
  const toast = document.getElementById(toastId);
  const overlay = document.getElementById('toastOverlay');
  
  if (toast && overlay) {
    overlay.classList.add('show');
    setTimeout(() => {
      toast.classList.add('show');
    }, 100);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
      closeToast(toastId);
    }, 5000);
  }
}

function closeToast(toastId) {
  const toast = document.getElementById(toastId);
  const overlay = document.getElementById('toastOverlay');
  
  if (toast) {
    toast.classList.remove('show');
    
    setTimeout(() => {
      if (overlay) {
        overlay.classList.remove('show');
      }
      
      // Remove from DOM after animation
      setTimeout(() => {
        if (overlay && overlay.parentNode) {
          overlay.parentNode.removeChild(overlay);
        }
      }, 300);
    }, 200);
  }
}

// Close toast when clicking on overlay
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('toast-overlay')) {
    const toast = e.target.querySelector('.toast');
    if (toast) {
      const toastId = toast.id;
      closeToast(toastId);
    }
  }
});

// Calculate age based on birthdate
function calculateAge() {
  const birthdate = document.getElementById('birthdate').value;
  const ageField = document.getElementById('age');
  
  if (birthdate) {
    const today = new Date();
    const birth = new Date(birthdate);
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
      age--;
    }
    
    ageField.value = age;
  }
}

// Form validation
document.getElementById('certificateForm').addEventListener('submit', function(e) {
  const radioButtons = document.querySelectorAll('input[name="certificateType"]');
  const checked = Array.from(radioButtons).some(radio => radio.checked);
  
  if (!checked) {
    e.preventDefault();
    alert('Please select a certificate type.');
    return false;
  }
});

// Set today's date as default and show toasts
document.addEventListener('DOMContentLoaded', function() {
  const today = new Date().toISOString().split('T')[0];
  document.getElementById('requestDate').value = today;
  
  // Calculate age if birthdate is already filled (for admin view)
  const birthdateField = document.getElementById('birthdate');
  if (birthdateField.value) {
    calculateAge();
  }
  
  // Show toast notifications
  const successToast = document.getElementById('successToast');
  const errorToast = document.getElementById('errorToast');
  
  if (successToast) {
    showToast('successToast');
  }
  
  if (errorToast) {
    showToast('errorToast');
  }
});
</script>

<?php include '../includes/footer.php'; ?>
