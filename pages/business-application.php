<?php
session_start();
$base_path = '../';
$page_title = 'Business Application Form - Barangay Gumaoc East';
$header_title = 'Business Application Form';
$header_subtitle = 'Apply for business permits and clearances';

// Display any error or success messages
if (isset($_SESSION['error'])) {
    echo '<div style="background: #f8d7da; color: #721c24; padding: 1rem; margin: 1rem; border-radius: 8px; border: 1px solid #f5c6cb;">';
    echo '<strong>Error:</strong> ' . $_SESSION['error'];
    echo '</div>';
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    echo '<div style="background: #d4edda; color: #155724; padding: 1rem; margin: 1rem; border-radius: 8px; border: 1px solid #c3e6cb;">';
    echo '<strong>Success:</strong> ' . $_SESSION['success'];
    echo '</div>';
    unset($_SESSION['success']);
}

include '../includes/header.php';
include '../includes/navigation.php';
?>

<div class="container">
  <div class="section">
    <form class="modern-form" method="POST" action="process_business_application.php">
      <!-- Application Details -->
      <div class="form-section">
        <h3>Application Information</h3>
        <div class="form-row">
          <div class="form-group">
            <label for="date">Date of Application <span class="required">*</span></label>
            <input type="date" id="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
          </div>
          <div class="form-group">
            <label for="reference_number">Reference Number</label>
            <input type="text" id="reference_number" name="reference_number" placeholder="Auto-generated" readonly>
          </div>
        </div>
      </div>

      <!-- Business Owner Information -->
      <div class="form-section">
        <h3>Business Owner Information</h3>
        <div class="form-row">
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
      </div>

      <!-- Business Information -->
      <div class="form-section">
        <h3>Business Information</h3>
        <div class="form-row">
          <div class="form-group full-width">
            <label for="business_name">Name of Business <span class="required">*</span></label>
            <input type="text" id="business_name" name="business_name" required placeholder="Enter business name">
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="business_address_1">Business Location - Address Line 1 <span class="required">*</span></label>
            <input type="text" id="business_address_1" name="business_address_1" required placeholder="Street number, street name">
          </div>
          <div class="form-group">
            <label for="business_address_2">Business Location - Address Line 2</label>
            <input type="text" id="business_address_2" name="business_address_2" placeholder="Subdivision, building name, etc.">
          </div>
        </div>
      </div>

      <!-- Owner Address -->
      <div class="form-section">
        <h3>Owner's Residence Information</h3>
        <div class="form-row">
          <div class="form-group full-width">
            <label for="house_address">House Address of Owner <span class="required">*</span></label>
            <textarea id="house_address" name="house_address" required placeholder="Complete residential address of business owner" rows="3"></textarea>
          </div>
        </div>
      </div>

      <!-- Tax Information -->
      <div class="form-section">
        <h3>Tax Information</h3>
        <div class="form-row">
          <div class="form-group">
            <label for="or_number">OR Number <span class="required">*</span></label>
            <input type="text" id="or_number" name="or_number" required placeholder="Official Receipt Number">
          </div>
          <div class="form-group">
            <label for="ctc_number">CTC Number <span class="required">*</span></label>
            <input type="text" id="ctc_number" name="ctc_number" required placeholder="Community Tax Certificate Number">
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="form-actions">
        <button type="button" class="btn btn-outline" onclick="window.history.back()">Cancel</button>
        <button type="reset" class="btn btn-secondary">Reset Form</button>
        <button type="submit" class="btn btn-primary">Submit Application</button>
      </div>
    </form>
  </div>
</div>

<script>
// Generate reference number on page load
document.addEventListener('DOMContentLoaded', function() {
  const refNumber = 'BA-' + new Date().getFullYear() + '-' + String(Date.now()).slice(-6);
  document.getElementById('reference_number').value = refNumber;
});

// Form validation
document.querySelector('.modern-form').addEventListener('submit', function(e) {
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
    alert('Please fill in all required fields.');
  }
});

// Auto-capitalize names
['first_name', 'middle_name', 'last_name', 'business_name'].forEach(fieldId => {
  const field = document.getElementById(fieldId);
  if (field) {
    field.addEventListener('input', function() {
      this.value = this.value.replace(/\b\w/g, l => l.toUpperCase());
    });
  }
});

// Format OR and CTC numbers
document.getElementById('or_number').addEventListener('input', function() {
  this.value = this.value.replace(/[^0-9-]/g, '');
});

document.getElementById('ctc_number').addEventListener('input', function() {
  this.value = this.value.replace(/[^0-9-]/g, '');
});
</script>

<?php include '../includes/footer.php'; ?>
