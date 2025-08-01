<?php
session_start();
$base_path = '../';
$page_title = 'Census Registration - Barangay Gumaoc East';
$header_title = 'Census Registration Form';
$header_subtitle = 'Barangay Population Census Data Collection';

// Check if this is an admin view
$admin_view = isset($_GET['admin_view']) ? (int)$_GET['admin_view'] : null;
$readonly = isset($_GET['readonly']) && $_GET['readonly'] === '1';
$registration_data = null;

if ($admin_view) {
    include '../includes/db_connect.php';
    $stmt = $pdo->prepare("SELECT * FROM resident_registrations WHERE id = ?");
    $stmt->execute([$admin_view]);
    $registration_data = $stmt->fetch();
    
    // Fetch related family members
    $family_members = [];
    if ($registration_data) {
        $family_stmt = $pdo->prepare("SELECT * FROM family_members WHERE registration_id = ? ORDER BY id");
        $family_stmt->execute([$admin_view]);
        $family_members = $family_stmt->fetchAll();
        
        // Fetch family members with disabilities
        $disabilities_stmt = $pdo->prepare("SELECT * FROM family_disabilities WHERE registration_id = ? ORDER BY id");
        $disabilities_stmt->execute([$admin_view]);
        $family_disabilities = $disabilities_stmt->fetchAll();
        
        // Fetch family members in organizations
        $organizations_stmt = $pdo->prepare("SELECT * FROM family_organizations WHERE registration_id = ? ORDER BY id");
        $organizations_stmt->execute([$admin_view]);
        $family_organizations = $organizations_stmt->fetchAll();
        
        $header_title = 'Census Registration Details - ID #' . str_pad($admin_view, 5, '0', STR_PAD_LEFT);
        $header_subtitle = 'Submitted on ' . date('F j, Y \a\t g:i A', strtotime($registration_data['submitted_at']));
    }
}

include '../includes/header.php';
include '../includes/navigation.php';
?>

<div class="container">
  <div class="section">
    <?php if ($admin_view && $registration_data): ?>
      <div class="admin-view-banner">
        <div class="status-info">
          <span class="status-badge status-<?php echo $registration_data['status']; ?>">
            Status: <?php echo ucfirst($registration_data['status']); ?>
          </span>
          <span class="submission-info">
            Submitted: <?php echo date('M j, Y g:i A', strtotime($registration_data['submitted_at'])); ?>
          </span>
        </div>
        <?php if (!$readonly): ?>
          <a href="../admin/view-resident-registrations.php" class="back-btn">← Back to Admin</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-error">
        <h4>Error</h4>
        <p><?php echo htmlspecialchars($_SESSION['error']); ?></p>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success">
        <h4>Success</h4>
        <p><?php echo htmlspecialchars($_SESSION['success']); ?></p>
      </div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <form id="censusForm" class="registration-form" method="POST" action="process-census.php" <?php echo $readonly ? 'style="pointer-events: none;"' : ''; ?>>

      <fieldset>
        <legend>Pangunahing Impormasyon</legend>
        
        <div class="form-grid">
          <div class="form-group">
            <label for="barangay">BARANGAY</label>
            <input type="text" id="barangay" name="barangay" value="Gumaoc East" readonly>
          </div>

          <div class="form-group">
            <label for="sitio">SITIO/POOK</label>
            <input type="text" id="sitio" name="sitio" value="BLOCK" readonly>
          </div>
        </div>

        <div class="form-group">
          <label for="headOfFamily">Pangalan ng Puno ng Pamilya *</label>
          <input type="text" id="headOfFamily" name="headOfFamily" required placeholder="Buong pangalan ng puno ng pamilya"
                 value="<?php echo $registration_data ? htmlspecialchars(trim($registration_data['first_name'] . ' ' . $registration_data['middle_name'] . ' ' . $registration_data['last_name'])) : ''; ?>"
                 <?php echo $readonly ? 'readonly' : ''; ?>>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="cellphone">Cellphone no.</label>
            <input type="tel" id="cellphone" name="cellphone" placeholder="09XXXXXXXXX" pattern="[0-9]{11}" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['contact_number']) : ''; ?>"
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="houseNumber">Numero ng Bahay *</label>
            <input type="text" id="houseNumber" name="houseNumber" required placeholder="Numero ng bahay" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['house_number']) : ''; ?>"
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="interviewer">Pangalan ng Nakapanayam *</label>
            <input type="text" id="interviewer" name="interviewer" required placeholder="Buong pangalan ng nakapanayam"
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['interviewer']) : ''; ?>"
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="interviewerTitle">Taga-panayam *</label>
            <input type="text" id="interviewerTitle" name="interviewerTitle" required placeholder="Posisyon/Tungkulin"
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['interviewer_title']) : ''; ?>"
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>
      </fieldset>

      <div class="form-actions">
        <?php if (!$readonly): ?>
          <button type="submit" class="btn btn-primary">I-submit ang Census Form</button>
          <button type="reset" class="btn">I-clear ang Form</button>
        <?php else: ?>
          <a href="../admin/view-resident-registrations.php" class="btn btn-secondary">← Back to Admin Dashboard</a>
          <button type="button" class="btn btn-primary" onclick="window.print()">🖨️ Print Form</button>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<style>
/* Global Styles */
* {
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  line-height: 1.6;
  color: #2c3e50;
  background: url('../background.jpg') no-repeat center center fixed;
  background-size: cover;
  min-height: 100vh;
  margin: 0;
  padding: 0;
}

body::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.85);
  z-index: -1;
}

/* Container Styles */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px 15px;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 16px;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);
  margin-top: 20px;
  margin-bottom: 20px;
}

.section {
  margin-bottom: 25px;
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
}

.status-pending { background: #fff3cd; color: #856404; }
.status-approved { background: #d4edda; color: #155724; }
.status-rejected { background: #f8d7da; color: #721c24; }

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

/* Form Styles */
.registration-form {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

fieldset {
  border: 2px solid #e3f2fd;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

legend {
  background: linear-gradient(135deg, #2e7d32, #4caf50);
  color: white;
  padding: 0.8rem 1.5rem;
  border-radius: 25px;
  font-weight: 600;
  font-size: 1.1rem;
  border: none;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 1rem;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #2e7d32;
  font-size: 0.95rem;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.8rem;
  border: 2px solid #e1e5e9;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: white;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #4caf50;
  outline: none;
  box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
  transform: translateY(-1px);
}

/* Readonly Form Styling */
form[style*="pointer-events: none"] .form-group input,
form[style*="pointer-events: none"] .form-group select,
form[style*="pointer-events: none"] .table-input,
form[style*="pointer-events: none"] .form-control {
  background: #f8f9fa !important;
  border-color: #e9ecef !important;
  color: #6c757d !important;
  cursor: not-allowed !important;
}

/* Button Styles */
.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid #f0f0f0;
}

.btn {
  padding: 0.8rem 2rem;
  border: none;
  border-radius: 25px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-block;
  text-align: center;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn-primary {
  background: linear-gradient(135deg, #4caf50, #2e7d32);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3);
}

.btn-secondary {
  background: linear-gradient(135deg, #6c757d, #495057);
  color: white;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Alert Styles */
.alert {
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.alert-success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.alert-error {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

/* Responsive Design */
@media (max-width: 768px) {
  .container {
    padding: 15px 10px;
    margin: 10px;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    flex-direction: column;
    align-items: center;
  }
  
  .btn {
    width: 100%;
    max-width: 300px;
  }
}

/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

fieldset {
  animation: fadeInUp 0.6s ease-out;
  animation-fill-mode: both;
}

fieldset:nth-child(1) { animation-delay: 0.1s; }
fieldset:nth-child(2) { animation-delay: 0.2s; }
fieldset:nth-child(3) { animation-delay: 0.3s; }
fieldset:nth-child(4) { animation-delay: 0.4s; }
fieldset:nth-child(5) { animation-delay: 0.5s; }

/* Scroll to top behavior */
html {
  scroll-behavior: smooth;
}
</style>

<script>
// Form interaction functions
function toggleOtherInput(radioName, otherId) {
    const radios = document.querySelectorAll(`input[name="${radioName}"]`);
    const otherInput = document.getElementById(otherId);
    
    radios.forEach(radio => {
        if (radio.checked && radio.value === 'Iba pa') {
            otherInput.disabled = false;
            otherInput.focus();
        } else if (radio.checked) {
            otherInput.disabled = true;
            otherInput.value = '';
        }
    });
}

function toggleCheckboxOther(checkboxName, otherId) {
    const checkboxes = document.querySelectorAll(`input[name="${checkboxName}[]"]`);
    const otherInput = document.getElementById(otherId);
    let otherChecked = false;
    
    checkboxes.forEach(checkbox => {
        if (checkbox.checked && checkbox.value === 'Iba pa') {
            otherChecked = true;
        }
    });
    
    if (otherChecked) {
        otherInput.disabled = false;
        otherInput.focus();
    } else {
        otherInput.disabled = true;
        otherInput.value = '';
    }
}

// Family member functions (if needed for dynamic form)
function addFamilyMember() {
    // Implementation for adding family members dynamically
}

function removeFamilyMember(button) {
    // Implementation for removing family members
}

function toggleMemberDetails(button) {
    // Implementation for toggling member details
}

function addDisabilityRow() {
    // Implementation for adding disability rows
}

function removeDisabilityRow(button) {
    // Implementation for removing disability rows
}

function addOrganizationRow() {
    // Implementation for adding organization rows
}

function removeOrganizationRow(button) {
    // Implementation for removing organization rows
}
</script>

<?php include '../includes/footer.php'; ?>
