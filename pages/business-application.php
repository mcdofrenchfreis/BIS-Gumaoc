<?php
include '../includes/auth_check.php';
$base_path = '../';
$page_title = 'Business Application Form - Barangay Gumaoc East';
$header_title = 'Business Application Form';
$header_subtitle = 'Apply for business permits and clearances';

include '../includes/header.php';
include '../includes/navigation.php';
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
    
    <form id="businessForm" class="business-form" method="POST" action="process_business_application.php">

      <fieldset>
        <legend>Application Information</legend>
        
        <div class="form-grid-two">
          <div class="form-group">
            <label for="date">Date of Application <span class="required">*</span></label>
            <input type="date" id="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
          </div>
          <div class="form-group">
            <label for="reference_number">Reference Number</label>
            <input type="text" id="reference_number" name="reference_number" placeholder="Auto-generated" readonly>
          </div>
        </div>
      </fieldset>

      <fieldset>
        <legend>Business Owner Information</legend>
        
        <div class="form-grid">
          <div class="form-group">
            <label for="first_name">First Name <span class="required">*</span></label>
            <input type="text" id="first_name" name="first_name" required placeholder="Enter first name">
          </div>
          <div class="form-group">
            <label for="middle_name">Middle Name</label>
            <input type="text" id="middle_name" name="middle_name" placeholder="Enter middle name">
          </div>
          <div class="form-group">
            <label for="last_name">Last Name <span class="required">*</span></label>
            <input type="text" id="last_name" name="last_name" required placeholder="Enter last name">
          </div>
        </div>

        <div class="form-grid-two">
          <div class="form-group">
            <label for="contact_number">Contact Number <span class="required">*</span></label>
            <input type="tel" id="contact_number" name="contact_number" required placeholder="09XX-XXX-XXXX">
          </div>
          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="your.email@example.com">
          </div>
        </div>

        <div class="form-group">
          <label for="house_address">Owner's Residence Address <span class="required">*</span></label>
          <textarea id="house_address" name="house_address" required placeholder="Complete residential address of business owner" rows="3"></textarea>
        </div>
      </fieldset>

      <fieldset>
        <legend>Business Information</legend>
        
        <div class="form-group">
          <label for="business_name">Name of Business <span class="required">*</span></label>
          <input type="text" id="business_name" name="business_name" required placeholder="Enter business name">
        </div>
        
        <div class="form-grid">
          <div class="form-group">
            <label for="business_type">Type of Business <span class="required">*</span></label>
            <select id="business_type" name="business_type" required>
              <option value="">Select Business Type</option>
              <option value="Retail Store">Retail Store</option>
              <option value="Restaurant/Food Service">Restaurant/Food Service</option>
              <option value="Sari-sari Store">Sari-sari Store</option>
              <option value="Auto Repair Shop">Auto Repair Shop</option>
              <option value="Beauty Salon/Barbershop">Beauty Salon/Barbershop</option>
              <option value="Internet Cafe">Internet Cafe</option>
              <option value="Pharmacy">Pharmacy</option>
              <option value="Hardware Store">Hardware Store</option>
              <option value="Laundry Shop">Laundry Shop</option>
              <option value="Agricultural Business">Agricultural Business</option>
              <option value="Professional Services">Professional Services</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div class="form-group">
            <label for="business_nature">Nature of Business <span class="required">*</span></label>
            <input type="text" id="business_nature" name="business_nature" required placeholder="Describe business activities">
          </div>
        </div>
        
        <div class="form-grid-two">
          <div class="form-group">
            <label for="business_address_1">Business Location - Address Line 1 <span class="required">*</span></label>
            <input type="text" id="business_address_1" name="business_address_1" required placeholder="Street number, street name">
          </div>
          <div class="form-group">
            <label for="business_address_2">Business Location - Address Line 2</label>
            <input type="text" id="business_address_2" name="business_address_2" placeholder="Subdivision, building name, etc.">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="capital_investment">Capital Investment <span class="required">*</span></label>
            <input type="number" id="capital_investment" name="capital_investment" required placeholder="Enter amount in PHP" min="1" step="0.01">
          </div>
          <div class="form-group">
            <label for="number_of_employees">Number of Employees</label>
            <input type="number" id="number_of_employees" name="number_of_employees" placeholder="Including owner" min="1">
          </div>
        </div>
      </fieldset>

      <fieldset>
        <legend>Tax Information</legend>
        
        <div class="form-grid-two">
          <div class="form-group">
            <label for="or_number">OR Number <span class="required">*</span></label>
            <input type="text" id="or_number" name="or_number" required placeholder="Official Receipt Number">
          </div>
          <div class="form-group">
            <label for="ctc_number">CTC Number <span class="required">*</span></label>
            <input type="text" id="ctc_number" name="ctc_number" required placeholder="Community Tax Certificate Number">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="tin_number">TIN Number</label>
            <input type="text" id="tin_number" name="tin_number" placeholder="Tax Identification Number">
          </div>
          <div class="form-group">
            <label for="sss_number">SSS Number</label>
            <input type="text" id="sss_number" name="sss_number" placeholder="Social Security System Number">
          </div>
          <div class="form-group">
            <label for="philhealth_number">PhilHealth Number</label>
            <input type="text" id="philhealth_number" name="philhealth_number" placeholder="PhilHealth Number">
          </div>
        </div>
      </fieldset>

      <fieldset>
        <legend>Additional Information</legend>
        
        <div class="form-group">
          <label for="purpose">Purpose of Application <span class="required">*</span></label>
          <textarea id="purpose" name="purpose" required placeholder="Specify the purpose for this business application" rows="3"></textarea>
        </div>

        <div class="form-group">
          <label for="special_requirements">Special Requirements/Notes</label>
          <textarea id="special_requirements" name="special_requirements" placeholder="Any special requirements or additional information" rows="2"></textarea>
        </div>
      </fieldset>

      <div class="form-actions">
        <button type="button" class="btn btn-secondary" onclick="resetForm()">Clear Form</button>
        <button type="submit" class="btn btn-primary">Submit Application</button>
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

/* Container and Layout */
.container {
  max-width: 1000px;
  margin: 20px auto;
  padding: 20px 15px;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.section {
  margin-bottom: 25px;
}

/* Form Styling */
.business-form {
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

.required {
  color: #dc3545;
  font-weight: bold;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 12px 15px;
  border: 2px solid #e9ecef;
  border-radius: 10px;
  font-size: 14px;
  transition: all 0.3s ease;
  background: #fff;
  box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #28a745;
  box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
}

.form-group input[readonly] {
  background: #f8f9fa;
  color: #6c757d;
  cursor: not-allowed;
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
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

/* Error styling */
.form-group input.error,
.form-group select.error,
.form-group textarea.error {
  border-color: #dc3545;
  box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
}

/* Print Styles */
@media print {
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
}

@media (max-width: 480px) {
  .toast {
    min-width: 280px;
  }
  
  .toast-content {
    padding: 18px 20px;
    gap: 12px;
  }
  
  .form-group input,
  .form-group select,
  .form-group textarea {
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

// Generate reference number on page load
document.addEventListener('DOMContentLoaded', function() {
  const refNumber = 'BA-' + new Date().getFullYear() + '-' + String(Date.now()).slice(-6);
  document.getElementById('reference_number').value = refNumber;
  
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

// Form validation
document.getElementById('businessForm').addEventListener('submit', function(e) {
  const requiredFields = this.querySelectorAll('[required]');
  let isValid = true;
  
  requiredFields.forEach(field => {
    if (!field.value.trim()) {
      isValid = false;
      field.classList.add('error');
      
      // Remove error class when user starts typing
      field.addEventListener('input', function() {
        this.classList.remove('error');
      });
    }
  });
  
  if (!isValid) {
    e.preventDefault();
    // Create and show error toast
    if (!document.getElementById('validationErrorToast')) {
      const overlay = document.createElement('div');
      overlay.id = 'toastOverlay';
      overlay.className = 'toast-overlay';
      overlay.innerHTML = `
        <div id="validationErrorToast" class="toast toast-error">
          <div class="toast-content">
            <span class="toast-icon">❌</span>
            <span class="toast-message">Please fill in all required fields.</span>
            <button class="toast-close" onclick="closeToast('validationErrorToast')">&times;</button>
          </div>
        </div>
      `;
      document.body.appendChild(overlay);
      showToast('validationErrorToast');
    }
  }
});

// Reset form function
function resetForm() {
  document.getElementById('businessForm').reset();
  // Regenerate reference number
  const refNumber = 'BA-' + new Date().getFullYear() + '-' + String(Date.now()).slice(-6);
  document.getElementById('reference_number').value = refNumber;
  // Reset date to today
  document.getElementById('date').value = new Date().toISOString().split('T')[0];
  // Remove error classes
  document.querySelectorAll('.error').forEach(field => {
    field.classList.remove('error');
  });
}

// Auto-capitalize names
['first_name', 'middle_name', 'last_name', 'business_name'].forEach(fieldId => {
  const field = document.getElementById(fieldId);
  if (field) {
    field.addEventListener('input', function() {
      this.value = this.value.replace(/\b\w/g, l => l.toUpperCase());
    });
  }
});

// Format contact number
document.getElementById('contact_number').addEventListener('input', function() {
  let value = this.value.replace(/\D/g, '');
  if (value.length > 0 && !value.startsWith('09')) {
    if (value.startsWith('9')) {
      value = '0' + value;
    }
  }
  if (value.length > 11) {
    value = value.slice(0, 11);
  }
  this.value = value;
});

// Format OR and CTC numbers
document.getElementById('or_number').addEventListener('input', function() {
  this.value = this.value.replace(/[^0-9-]/g, '');
});

document.getElementById('ctc_number').addEventListener('input', function() {
  this.value = this.value.replace(/[^0-9-]/g, '');
});

// Format TIN number
document.getElementById('tin_number').addEventListener('input', function() {
  this.value = this.value.replace(/[^0-9-]/g, '');
});

// Format SSS number
document.getElementById('sss_number').addEventListener('input', function() {
  this.value = this.value.replace(/[^0-9-]/g, '');
});

// Format PhilHealth number
document.getElementById('philhealth_number').addEventListener('input', function() {
  this.value = this.value.replace(/[^0-9-]/g, '');
});

// Format capital investment
document.getElementById('capital_investment').addEventListener('input', function() {
  let value = parseFloat(this.value);
  if (!isNaN(value)) {
    this.setAttribute('title', 'PHP ' + value.toLocaleString('en-PH', {minimumFractionDigits: 2}));
  }
});

// Show/hide other business type input
document.getElementById('business_type').addEventListener('change', function() {
  const otherInput = document.getElementById('other_business_type');
  if (this.value === 'Other') {
    if (!otherInput) {
      const div = document.createElement('div');
      div.className = 'form-group';
      div.innerHTML = `
        <label for="other_business_type">Specify Other Business Type <span class="required">*</span></label>
        <input type="text" id="other_business_type" name="other_business_type" required placeholder="Please specify">
      `;
      this.closest('.form-group').after(div);
    }
  } else {
    if (otherInput) {
      otherInput.closest('.form-group').remove();
    }
  }
});
</script>

<?php include '../includes/footer.php'; ?>
