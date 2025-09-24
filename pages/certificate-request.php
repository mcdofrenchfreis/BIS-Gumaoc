<?php
session_start();
$base_path = '../';
$page_title = 'Certificate Request - Barangay Gumaoc East';
$header_title = 'Certificate Request Form';
$header_subtitle = 'Request for Barangay Certificates';

// Initialize database connection
include '../includes/db_connect.php';

// Include database connection for blotter checking
if (!$admin_view) {
    include '../includes/db_connect.php';
}

// Check if this is an admin view
$admin_view = isset($_GET['admin_view']) ? (int)$_GET['admin_view'] : null;
$readonly = isset($_GET['readonly']) && $_GET['readonly'] === '1';
$request_data = null;

// Get current user data for auto-population
$current_user = null;
$is_logged_in = isset($_SESSION['rfid_authenticated']) && $_SESSION['rfid_authenticated'] === true;

if ($is_logged_in && isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $current_user = $stmt->fetch();
    } catch (PDOException $e) {
        // Handle database error silently for form functionality
        $current_user = null;
    }
}

if ($admin_view) {
    $stmt = $pdo->prepare("SELECT * FROM certificate_requests WHERE id = ?");
    $stmt->execute([$admin_view]);
    $request_data = $stmt->fetch();
    
    if ($request_data) {
        $header_title = 'Certificate Request Details - ID #' . str_pad($admin_view, 5, '0', STR_PAD_LEFT);
        $header_subtitle = 'Submitted on ' . date('F j, Y \a\t g:i A', strtotime($request_data['submitted_at']));
    }
}

// Determine if tricycle or cedula section should be shown by default
$show_tricycle_section = false;
$show_cedula_section = false;
$show_business_section = false;

if ($request_data) {
    if ($request_data['certificate_type'] === 'TRICYCLE PERMIT') {
        $show_tricycle_section = true;
    } elseif ($request_data['certificate_type'] === 'CEDULA/CTC') {
        $show_cedula_section = true;
    } elseif ($request_data['certificate_type'] === 'BUSINESS APPLICATION') {
        $show_business_section = true;
    }
}

include '../includes/header.php';
?>

<script>
// Define dismiss functions immediately when page loads
function dismissNotification() {
  console.log('Debug: dismissNotification called');
  
  const notification = document.getElementById('autoPopulatedNotification');
  
  if (!notification) {
    console.log('Debug: Notification element not found');
    return false;
  }
  
  console.log('Debug: Starting dismiss animation');
  
  // Immediate visual feedback
  notification.style.pointerEvents = 'none';
  
  // Apply animation styles
  notification.style.transition = 'all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
  notification.style.transform = 'translateX(100px) scale(0.8)';
  notification.style.opacity = '0';
  
  // Remove element after animation
  setTimeout(function() {
    console.log('Debug: Removing notification from DOM');
    if (notification && notification.parentNode) {
      notification.parentNode.removeChild(notification);
      console.log('Debug: Notification removed successfully');
    }
  }, 400);
  
  return false;
}

// Alternative simple hide function
function forceHideNotification() {
  const notification = document.getElementById('autoPopulatedNotification');
  if (notification) {
    notification.style.display = 'none';
    console.log('Debug: Notification hidden with display:none');
  }
}

// Add pulse animation
const style = document.createElement('style');
style.textContent = `
@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.1); opacity: 0.8; }
}

/* Placeholder styles */
#operatorLicense::placeholder,
input::placeholder,
textarea::placeholder,
select::placeholder {
  opacity: 0.5;
  font-family: inherit;
}

/* Tricycle Photo Upload Styles */
.file-upload-container {
  position: relative;
  margin-bottom: 10px;
}

.file-upload-input {
  position: absolute;
  width: 0.1px;
  height: 0.1px;
  opacity: 0;
  overflow: hidden;
  z-index: -1;
}

.file-upload-label {
  display: flex;
  align-items: center;
  padding: 10px 15px;
  background-color: #f5f5f5;
  border: 2px dashed #ccc;
  border-radius: 5px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.file-upload-label:hover {
  background-color: #e8f5e9;
  border-color: #4caf50;
}

.file-upload-icon {
  font-size: 24px;
  margin-right: 10px;
}

.file-upload-text {
  font-size: 14px;
  color: #555;
}

.file-upload-preview {
  margin-top: 10px;
  position: relative;
}

.tricycle-photo-preview {
  max-width: 100%;
  max-height: 200px;
  border-radius: 5px;
  border: 1px solid #ddd;
}

.remove-preview {
  position: absolute;
  top: 5px;
  right: 5px;
  width: 25px;
  height: 25px;
  background: rgba(255, 255, 255, 0.8);
  border: 1px solid #ddd;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 16px;
  color: #f44336;
}

.existing-photo {
  margin-top: 10px;
  padding: 8px;
  background-color: #f9f9f9;
  border-radius: 4px;
  border-left: 3px solid #2196f3;
}
`;
document.head.appendChild(style);

// Enhanced keyboard support
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape' || event.keyCode === 27) {
    console.log('Debug: ESC key pressed');
    const notification = document.getElementById('autoPopulatedNotification');
    if (notification && notification.style.display !== 'none') {
      dismissNotification();
    }
  }
});

// Tricycle photo preview functionality
document.addEventListener('DOMContentLoaded', function() {
  const tricyclePhotoInput = document.getElementById('tricyclePhoto');
  const tricyclePhotoPreview = document.getElementById('tricyclePhotoPreview');
  
  if (tricyclePhotoInput && tricyclePhotoPreview) {
    tricyclePhotoInput.addEventListener('change', function() {
      // Clear previous preview
      tricyclePhotoPreview.innerHTML = '';
      
      if (this.files && this.files[0]) {
        const file = this.files[0];
        
        // Check file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
          alert('File is too large. Maximum size is 5MB.');
          this.value = '';
          return;
        }
        
        // Check file type
        if (!file.type.match('image.*')) {
          alert('Only image files are allowed (JPG, PNG, etc.)');
          this.value = '';
          return;
        }
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
          const img = document.createElement('img');
          img.src = e.target.result;
          img.className = 'tricycle-photo-preview';
          tricyclePhotoPreview.appendChild(img);
          
          const removeBtn = document.createElement('button');
          removeBtn.innerHTML = '&times;';
          removeBtn.className = 'remove-preview';
          removeBtn.onclick = function() {
            tricyclePhotoPreview.innerHTML = '';
            tricyclePhotoInput.value = '';
            return false;
          };
          tricyclePhotoPreview.appendChild(removeBtn);
        };
        
        reader.readAsDataURL(file);
      }
    });
  }
});
</script>

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
    
    <!-- Certificate Selection Screen -->
    <div id="certificateSelectionScreen" class="certificate-selection-screen">
      <h1 class="kiosk-title">Select Certificate Type</h1>
      <p class="kiosk-subtitle">Choose the type of certificate you would like to request</p>
      
      <div class="certificate-types">
        <div class="certificate-option" data-type="BRGY. CLEARANCE" <?php echo ($request_data && $request_data['certificate_type'] === 'BRGY. CLEARANCE') ? 'data-selected="true"' : ''; ?>>
          <div class="certificate-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36" fill="currentColor">
              <path d="M12,3L2,12h3v8h6v-6h2v6h6v-8h3L12,3z M17,18h-2v-6H9v6H7v-7.81l5-4.5l5,4.5V18z"/>
            </svg>
          </div>
          <div class="certificate-text">
            <strong>BARANGAY CLEARANCE</strong>
            <small style="display: block; margin-top: 8px; opacity: 0.8;">Certificate of good moral character</small>
          </div>
        </div>
        
        <div class="certificate-option" data-type="BRGY. INDIGENCY" <?php echo ($request_data && $request_data['certificate_type'] === 'BRGY. INDIGENCY') ? 'data-selected="true"' : ''; ?>>
          <div class="certificate-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36" fill="currentColor">
              <path d="M12,2C6.48,2,2,6.48,2,12s4.48,10,10,10s10-4.48,10-10S17.52,2,12,2z M12,20c-4.41,0-8-3.59-8-8s3.59-8,8-8s8,3.59,8,8 S16.41,20,12,20z M12.31,11.14c-1.77-0.45-2.34-0.94-2.34-1.67c0-0.84,0.79-1.43,2.1-1.43c1.38,0,1.9,0.66,1.94,1.64h1.71 c-0.05-1.34-0.87-2.57-2.49-2.97V5H10.9v1.69c-1.51,0.32-2.72,1.3-2.72,2.81c0,1.79,1.49,2.69,3.66,3.21 c1.95,0.46,2.34,1.15,2.34,1.87c0,0.53-0.39,1.39-2.1,1.39c-1.6,0-2.23-0.72-2.32-1.64H8.04c0.1,1.7,1.36,2.66,2.86,2.97V19h2.34 v-1.67c1.52-0.29,2.72-1.16,2.73-2.77C16.01,12.48,14.83,11.74,12.31,11.14z"/>
            </svg>
          </div>
          <div class="certificate-text">
            <strong>BARANGAY INDIGENCY</strong>
            <small style="display: block; margin-top: 8px; opacity: 0.8;">Certificate of financial status</small>
          </div>
        </div>
        
        <div class="certificate-option" data-type="TRICYCLE PERMIT" <?php echo ($request_data && $request_data['certificate_type'] === 'TRICYCLE PERMIT') ? 'data-selected="true"' : ''; ?>>
          <div class="certificate-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36" fill="currentColor">
              <path d="M15.5,5.5c1.1,0,2-0.9,2-2s-0.9-2-2-2s-2,0.9-2,2S14.4,5.5,15.5,5.5z M5,12c-2.8,0-5,2.2-5,5s2.2,5,5,5s5-2.2,5-5 S7.8,12,5,12z M5,20.5c-1.9,0-3.5-1.6-3.5-3.5s1.6-3.5,3.5-3.5s3.5,1.6,3.5,3.5S6.9,20.5,5,20.5z M19,12c-2.8,0-5,2.2-5,5 s2.2,5,5,5s5-2.2,5-5S21.8,12,19,12z M19,20.5c-1.9,0-3.5-1.6-3.5-3.5s1.6-3.5,3.5-3.5s3.5,1.6,3.5,3.5S20.9,20.5,19,20.5z M12,8.5h-1v-2h1V5H4v1.5h1v2H4V10h8V8.5z M10,5.5H6.5v2H10V5.5z M17.5,10.5c-0.8,0-1.5,0.7-1.5,1.5s0.7,1.5,1.5,1.5 s1.5-0.7,1.5-1.5S18.3,10.5,17.5,10.5z M18.5,6h-6v2h6V6z"/>
            </svg>
          </div>
          <div class="certificate-text">
            <strong>TRICYCLE PERMIT</strong>
            <small style="display: block; margin-top: 8px; opacity: 0.8;">Vehicle operation permit</small>
          </div>
        </div>
        
        <div class="certificate-option" data-type="PROOF OF RESIDENCY" <?php echo ($request_data && $request_data['certificate_type'] === 'PROOF OF RESIDENCY') ? 'data-selected="true"' : ''; ?>>
          <div class="certificate-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36" fill="currentColor">
              <path d="M12,2C8.13,2,5,5.13,5,9c0,5.25,7,13,7,13s7-7.75,7-13C19,5.13,15.87,2,12,2z M12,11.5c-1.38,0-2.5-1.12-2.5-2.5 s1.12-2.5,2.5-2.5s2.5,1.12,2.5,2.5S13.38,11.5,12,11.5z"/>
            </svg>
          </div>
          <div class="certificate-text">
            <strong>PROOF OF RESIDENCY</strong>
            <small style="display: block; margin-top: 8px; opacity: 0.8;">Certificate of residence</small>
          </div>
        </div>
        
        <div class="certificate-option" data-type="CEDULA/CTC" <?php echo ($request_data && $request_data['certificate_type'] === 'CEDULA/CTC') ? 'data-selected="true"' : ''; ?>>
          <div class="certificate-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36" fill="currentColor">
              <path d="M14,2H6C4.9,2,4,2.9,4,4v16c0,1.1,0.9,2,2,2h12c1.1,0,2-0.9,2-2V8L14,2z M16,18H8v-2h8V18z M16,14H8v-2h8V14z M13,9V3.5 L18.5,9H13z"/>
            </svg>
          </div>
          <div class="certificate-text">
            <strong>CEDULA/CTC</strong>
            <small style="display: block; margin-top: 8px; opacity: 0.8;">Community Tax Certificate</small>
          </div>
        </div>
        
        <div class="certificate-option" data-type="BUSINESS APPLICATION" <?php echo ($request_data && $request_data['certificate_type'] === 'BUSINESS APPLICATION') ? 'data-selected="true"' : ''; ?>>
          <div class="certificate-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36" fill="currentColor">
              <path d="M12,7V3H2v18h20V7H12z M6,19H4v-2h2V19z M6,15H4v-2h2V15z M6,11H4V9h2V11z M6,7H4V5h2V7z M10,19H8v-2h2V19z M10,15H8v-2 h2V15z M10,11H8V9h2V11z M10,7H8V5h2V7z M20,19h-8v-2h2v-2h-2v-2h2v-2h-2V9h8V19z M18,11h-2v2h2V11z M18,15h-2v2h2V15z"/>
            </svg>
          </div>
          <div class="certificate-text">
            <strong>BUSINESS APPLICATION</strong>
            <small style="display: block; margin-top: 8px; opacity: 0.8;">Business permit application</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Container -->
    <div id="formContainer" class="form-container">
      <!-- Simple Row with Inline Styles for Guaranteed Layout -->
      <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; width: 100%;">
        <div style="flex: 0 0 auto;">
          <button type="button" class="back-to-selection" onclick="showSelectionScreen()">
            ← Back to Certificate Selection
          </button>
        </div>
        
        <?php if ($current_user && !$admin_view): ?>
          <div style="flex: 0 0 auto; margin-left: 20px;">
            <div class="simple-notification" id="autoPopulatedNotification" style="display: inline-flex; align-items: center; background: #e8f5e9; border: 2px solid #4caf50; border-radius: 10px; padding: 12px 16px; gap: 12px; box-shadow: 0 3px 12px rgba(76, 175, 80, 0.25); max-width: 380px; min-width: 320px;">
              <span style="font-size: 20px; animation: pulse 2s infinite;">✅</span>
              <div style="flex: 1;">
                <div style="color: #1b5e20; font-weight: 700; font-size: 15px; margin-bottom: 4px; line-height: 1.2;">Auto-Populated</div>
                <div style="color: #2e7d32; font-size: 13px; opacity: 0.9; line-height: 1.3;">Information has been pre-filled. Please review and update if needed.</div>
              </div>
              <button id="dismissBtn" onclick="dismissNotification(); return false;" onmouseover="this.style.background='rgba(220, 53, 69, 0.1)'; this.style.color='#dc3545';" onmouseout="this.style.background='rgba(0, 0, 0, 0.05)'; this.style.color='#666';" style="background: rgba(0, 0, 0, 0.05); border: none; font-size: 18px; color: #666; cursor: pointer; padding: 6px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.2s ease; font-weight: bold;" title="Dismiss notification">
                ×
              </button>
            </div>
          </div>
        <?php endif; ?>
      </div>
      
      <form id="certificateForm" class="certificate-form" method="POST" action="process_certificate_request.php" enctype="multipart/form-data" <?php echo $readonly ? 'style="pointer-events: none;"' : ''; ?>>
        <input type="hidden" id="selectedCertificateType" name="certificateType" value="<?php echo $request_data ? htmlspecialchars($request_data['certificate_type']) : ''; ?>">

      <!-- Tricycle Details Section -->
      <fieldset id="tricycleDetailsSection" style="display: <?php echo $show_tricycle_section ? 'block' : 'none'; ?>;">
        <legend>Tricycle Details</legend>
        
        <div class="form-grid">
          <div class="form-group">
            <label for="makeType">Make and Type *</label>
            <input type="text" id="makeType" name="makeType" placeholder="e.g., Honda TMX-155" 
                   value="<?php echo $request_data ? htmlspecialchars($request_data['vehicle_make_type'] ?? '') : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="motorNo">Motor No. *</label>
            <input type="text" id="motorNo" name="motorNo" placeholder="Engine/Motor Number" 
                   value="<?php echo $request_data ? htmlspecialchars($request_data['motor_no'] ?? '') : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="chassisNo">Chassis No. *</label>
            <input type="text" id="chassisNo" name="chassisNo" placeholder="Chassis Number" 
                   value="<?php echo $request_data ? htmlspecialchars($request_data['chassis_no'] ?? '') : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="plateNo">Plate No. *</label>
            <input type="text" id="plateNo" name="plateNo" placeholder="e.g., 123ABC (official) or GD776A (temporary)" 
                   value="<?php echo $request_data ? htmlspecialchars($request_data['plate_no'] ?? '') : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="vehicleColor">Vehicle Color</label>
            <input type="text" id="vehicleColor" name="vehicleColor" placeholder="Primary color of tricycle" 
                   value="<?php echo $request_data ? htmlspecialchars($request_data['vehicle_color'] ?? '') : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="yearModel">Year Model</label>
            <input type="number" id="yearModel" name="yearModel" 
                   placeholder="e.g., 2020" 
                   min="1980" 
                   max="<?php echo date('Y'); ?>"
                   pattern="[0-9]{4}"
                   title="Please enter a valid 4-digit year (1980-<?php echo date('Y'); ?>)"
                   value="<?php echo $request_data ? ($request_data['year_model'] ?? '') : ''; ?>" 
                   style="text-align: left;"
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="form-grid-two">
          <div class="form-group">
            <label for="bodyNo">Body No.</label>
            <input type="text" id="bodyNo" name="bodyNo" placeholder="Body/Frame Number (if applicable)" 
                   value="<?php echo $request_data ? htmlspecialchars($request_data['body_no'] ?? '') : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="operatorLicense">Operator's License No.</label>
            <input type="text" id="operatorLicense" name="operatorLicense" 
                   placeholder="License Number (numbers only)" 
                   pattern="[0-9\-]+"
                   title="Please enter numbers only (dashes allowed for formatting)"
                   value="<?php echo $request_data ? htmlspecialchars($request_data['operator_license'] ?? '') : ''; ?>" 
                   style="font-family: inherit;"
                   <?php echo $readonly ? 'readonly' : ''; ?>>
            <small class="input-help">Enter license number using numbers only (e.g., 123456789 or 123-456-789)</small>
          </div>
        </div>

        <div class="form-group">
          <label for="tricyclePhoto">Tricycle Photo (Optional)</label>
          <div class="file-upload-container">
            <input type="file" id="tricyclePhoto" name="tricyclePhoto" accept="image/*" class="file-upload-input"
                  <?php echo $readonly ? 'disabled' : ''; ?>>
            <label for="tricyclePhoto" class="file-upload-label">
              <span class="file-upload-icon">📷</span>
              <span class="file-upload-text">Choose a photo of your tricycle</span>
            </label>
            <div class="file-upload-preview" id="tricyclePhotoPreview"></div>
          </div>
          <small class="input-help">Upload a clear photo of your tricycle (max 5MB, JPG/PNG format)</small>
          <?php if ($request_data && !empty($request_data['tricycle_photo'])): ?>
            <div class="existing-photo">
              <p>Current photo: <a href="../assets/uploads/tricycle_photos/<?php echo htmlspecialchars($request_data['tricycle_photo']); ?>" target="_blank">View Photo</a></p>
            </div>
          <?php endif; ?>
        </div>
      </fieldset>

      <!-- Cedula/CTC Details Section -->
      <fieldset id="cedulaDetailsSection" style="display: <?php echo $show_cedula_section ? 'block' : 'none'; ?>;">
        <legend>Community Tax Certificate (Cedula) Details</legend>
        
        <!-- Issuance Information -->
        <div class="form-grid">
          <div class="form-group">
            <label for="cedulaYear">Year *</label>
            <input type="number" id="cedulaYear" name="cedulaYear" 
                   placeholder="<?php echo date('Y'); ?>" 
                   min="<?php echo date('Y') - 5; ?>" 
                   max="<?php echo date('Y') + 1; ?>"
                   value="<?php echo $request_data ? ($request_data['cedula_year'] ?? date('Y')) : date('Y'); ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="placeOfIssue">Place of Issue (City/Municipality/Province) *</label>
            <input type="text" id="placeOfIssue" name="placeOfIssue" 
                   placeholder="San Jose Del Monte City, Bulacan" 
                   value="<?php echo $request_data ? htmlspecialchars($request_data['place_of_issue'] ?? 'San Jose Del Monte City, Bulacan') : 'San Jose Del Monte City, Bulacan'; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="dateIssued">Date Issued *</label>
            <input type="date" id="dateIssued" name="dateIssued"
                   value="<?php echo $request_data ? ($request_data['date_issued'] ?? '') : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <!-- Personal Details for Cedula -->
        <div class="form-grid">
          <div class="form-group">
            <label for="cedulaCitizenship">Citizenship *</label>
            <input type="text" id="cedulaCitizenship" name="cedulaCitizenship" 
                   placeholder="Filipino" 
                   value="<?php echo $request_data ? htmlspecialchars($request_data['cedula_citizenship'] ?? 'Filipino') : 'Filipino'; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="cedulaPlaceOfBirth">Place of Birth *</label>
            <input type="text" id="cedulaPlaceOfBirth" name="cedulaPlaceOfBirth" 
                   placeholder="City/Municipality, Province" 
                   value="<?php echo $request_data ? htmlspecialchars($request_data['cedula_place_of_birth'] ?? '') : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="cedulaDateOfBirth">Date of Birth *</label>
            <input type="date" id="cedulaDateOfBirth" name="cedulaDateOfBirth"
                   value="<?php echo $request_data ? ($request_data['cedula_date_of_birth'] ?? '') : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="cedulaCivilStatus">Civil Status *</label>
            <select id="cedulaCivilStatus" name="cedulaCivilStatus" <?php echo $readonly ? 'disabled' : ''; ?>>
              <option value="">Select Civil Status</option>
              <option value="Single" <?php echo ($request_data && $request_data['cedula_civil_status'] === 'Single') ? 'selected' : ''; ?>>Single</option>
              <option value="Married" <?php echo ($request_data && $request_data['cedula_civil_status'] === 'Married') ? 'selected' : ''; ?>>Married</option>
              <option value="Widow" <?php echo ($request_data && $request_data['cedula_civil_status'] === 'Widow') ? 'selected' : ''; ?>>Widow</option>
              <option value="Widower" <?php echo ($request_data && $request_data['cedula_civil_status'] === 'Widower') ? 'selected' : ''; ?>>Widower</option>
              <option value="Divorced" <?php echo ($request_data && $request_data['cedula_civil_status'] === 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
            </select>
          </div>

          <div class="form-group">
            <label for="professionOccupation">Profession/Occupation/Business *</label>
            <input type="text" id="professionOccupation" name="professionOccupation" 
                   placeholder="e.g., Teacher, Driver, Farmer, etc." 
                   value="<?php echo $request_data ? htmlspecialchars($request_data['profession_occupation'] ?? '') : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="height">Height (cm)</label>
            <input type="number" id="height" name="height" 
                   placeholder="e.g., 170" 
                   min="100" 
                   max="250"
                   value="<?php echo $request_data ? ($request_data['height'] ?? '') : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="form-grid-two">
          <div class="form-group">
            <label for="weight">Weight (kg)</label>
            <input type="number" id="weight" name="weight" 
                   placeholder="e.g., 65" 
                   min="20" 
                   max="200"
                   value="<?php echo $request_data ? ($request_data['weight'] ?? '') : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" id="amount" name="amount" 
                   placeholder="0.00" 
                   min="0" 
                   step="0.01"
                   value="<?php echo $request_data ? ($request_data['amount'] ?? '') : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <!-- Community Tax Details -->
        <div class="cedula-tax-section">
          <h4>Community Tax Due</h4>
          
          <div class="form-group">
            <label for="basicCommunityTax">A. Basic Community Tax *</label>
            <div class="radio-group">
              <label class="radio-option">
                <input type="radio" name="basicCommunityTaxType" value="voluntary" 
                       <?php echo ($request_data && $request_data['basic_tax_type'] === 'voluntary') ? 'checked' : 'checked'; ?> 
                       <?php echo $readonly ? 'disabled' : ''; ?>>
                <span>Voluntary (₱5.00)</span>
              </label>
              <label class="radio-option">
                <input type="radio" name="basicCommunityTaxType" value="exempted" 
                       <?php echo ($request_data && $request_data['basic_tax_type'] === 'exempted') ? 'checked' : ''; ?> 
                       <?php echo $readonly ? 'disabled' : ''; ?>>
                <span>Exempted (₱1.00)</span>
              </label>
            </div>
            <input type="number" id="basicCommunityTax" name="basicCommunityTax" 
                   placeholder="5.00" 
                   min="1" 
                   max="5" 
                   step="0.01"
                   value="<?php echo $request_data ? ($request_data['basic_community_tax'] ?? '5.00') : '5.00'; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="additional-tax-section">
            <h5>B. Additional Community Tax (not to exceed ₱5,000.00)</h5>
            
            <div class="form-group">
              <label for="grossReceiptsBusiness">1. Gross Receipts/Earnings from Business (₱1.00 for every ₱1,000)</label>
              <div class="tax-input-group">
                <input type="number" id="grossReceiptsBusiness" name="grossReceiptsBusiness" 
                       placeholder="0.00" 
                       min="0" 
                       step="0.01"
                       value="<?php echo $request_data ? ($request_data['gross_receipts_business'] ?? '') : ''; ?>" 
                       <?php echo $readonly ? 'readonly' : ''; ?>>
                <span class="tax-calculation" id="businessTaxCalc">Tax: ₱0.00</span>
              </div>
            </div>

            <div class="form-group">
              <label for="salariesProfession">2. Salaries/Gross Receipts from Profession/Occupation (₱1.00 for every ₱1,000)</label>
              <div class="tax-input-group">
                <input type="number" id="salariesProfession" name="salariesProfession" 
                       placeholder="0.00" 
                       min="0" 
                       step="0.01"
                       value="<?php echo $request_data ? ($request_data['salaries_profession'] ?? '') : ''; ?>" 
                       <?php echo $readonly ? 'readonly' : ''; ?>>
                <span class="tax-calculation" id="professionTaxCalc">Tax: ₱0.00</span>
              </div>
            </div>

            <div class="form-group">
              <label for="incomeRealProperty">3. Income from Real Property (₱1.00 for every ₱1,000)</label>
              <div class="tax-input-group">
                <input type="number" id="incomeRealProperty" name="incomeRealProperty" 
                       placeholder="0.00" 
                       min="0" 
                       step="0.01"
                       value="<?php echo $request_data ? ($request_data['income_real_property'] ?? '') : ''; ?>" 
                       <?php echo $readonly ? 'readonly' : ''; ?>>
                <span class="tax-calculation" id="propertyTaxCalc">Tax: ₱0.00</span>
              </div>
            </div>
          </div>

          <!-- Tax Summary -->
          <div class="tax-summary">
            <div class="form-grid">
              <div class="form-group">
                <label for="totalTax">Total</label>
                <input type="number" id="totalTax" name="totalTax" 
                       placeholder="0.00" 
                       step="0.01"
                       readonly
                       value="<?php echo $request_data ? ($request_data['total_tax'] ?? '') : ''; ?>">
              </div>

              <div class="form-group">
                <label for="interest">Interest</label>
                <input type="number" id="interest" name="interest" 
                       placeholder="0.00" 
                       min="0" 
                       step="0.01"
                       value="<?php echo $request_data ? ($request_data['interest'] ?? '0.00') : '0.00'; ?>" 
                       <?php echo $readonly ? 'readonly' : ''; ?>>
              </div>

              <div class="form-group">
                <label for="totalAmountPaid">Total Amount Paid</label>
                <input type="number" id="totalAmountPaid" name="totalAmountPaid" 
                       placeholder="0.00" 
                       step="0.01"
                       readonly
                       value="<?php echo $request_data ? ($request_data['total_amount_paid'] ?? '') : ''; ?>">
              </div>
            </div>
          </div>
        </div>
      </fieldset>

      <!-- Business Application Details Section -->
      <fieldset id="businessApplicationSection" style="display: <?php echo $show_business_section ? 'block' : 'none'; ?>;">
        <legend>Business Application Details</legend>
        
        <div class="form-grid">
          <div class="form-group">
            <label for="businessApplicationDate">Application Date *</label>
            <input type="date" id="businessApplicationDate" name="businessApplicationDate" 
                   value="<?php echo $request_data ? ($request_data['business_application_date'] ?? date('Y-m-d')) : date('Y-m-d'); ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="businessReferenceNo">Reference No.</label>
            <input type="text" id="businessReferenceNo" name="businessReferenceNo" 
                   placeholder="Auto-generated" 
                   value="<?php echo $request_data ? htmlspecialchars($request_data['business_reference_no'] ?? '') : ''; ?>" 
                   readonly style="background-color: #f8f9fa; cursor: not-allowed;">
          </div>
        </div>

        <div class="form-group">
          <label for="businessName">Business Name *</label>
          <input type="text" id="businessName" name="businessName" 
                 placeholder="Enter business name" 
                 value="<?php echo $request_data ? htmlspecialchars($request_data['business_name'] ?? '') : ''; ?>" 
                 <?php echo $readonly ? 'readonly' : ''; ?>>
        </div>

        <div class="form-grid-two">
          <div class="form-group">
            <label for="businessLocation">Business Location *</label>
            <textarea id="businessLocation" name="businessLocation" rows="3" 
                      placeholder="Enter complete business address..."
                      <?php echo $readonly ? 'readonly' : ''; ?>><?php echo $request_data ? htmlspecialchars($request_data['business_location'] ?? '') : ''; ?></textarea>
          </div>

          <div class="form-group">
            <label for="businessOwnerAddress">Owner Address *</label>
            <textarea id="businessOwnerAddress" name="businessOwnerAddress" rows="3" 
                      placeholder="Enter complete home address..."
                      <?php echo $readonly ? 'readonly' : ''; ?>><?php echo $request_data ? htmlspecialchars($request_data['business_owner_address'] ?? '') : ''; ?></textarea>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="businessOrNumber">OR Number *</label>
            <input type="text" id="businessOrNumber" name="businessOrNumber" 
                   placeholder="Enter Official Receipt number" 
                   value="<?php echo $request_data ? htmlspecialchars($request_data['business_or_number'] ?? '') : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="businessCtcNumber">CTC Number *</label>
            <input type="text" id="businessCtcNumber" name="businessCtcNumber" 
                   placeholder="Enter Community Tax Certificate number" 
                   value="<?php echo $request_data ? htmlspecialchars($request_data['business_ctc_number'] ?? '') : ''; ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <!-- Image Attachments Section -->
        <div class="form-section">
          <h4>📎 Optional Attachments</h4>
          <p class="section-description">Upload images of your CTC and business certificate (optional)</p>
          
          <div class="form-grid-two">
            <div class="form-group">
              <label for="ctcImage">CTC Image (Optional)</label>
              <div class="file-upload-container">
                <input type="file" id="ctcImage" name="ctcImage" 
                       accept="image/*,.pdf" 
                       class="file-input"
                       <?php echo $readonly ? 'disabled' : ''; ?>>
                <div class="file-upload-display">
                  <div class="file-upload-icon">📄</div>
                  <div class="file-upload-text">
                    <span class="file-upload-label">Click to upload CTC image</span>
                    <span class="file-upload-hint">PNG, JPG, PDF up to 5MB</span>
                  </div>
                </div>
                <div class="file-preview" id="ctcImagePreview" style="display: none;"></div>
              </div>
            </div>

            <div class="form-group">
              <label for="certificateImage">Business Certificate Image (Optional)</label>
              <div class="file-upload-container">
                <input type="file" id="certificateImage" name="certificateImage" 
                       accept="image/*,.pdf" 
                       class="file-input"
                       <?php echo $readonly ? 'disabled' : ''; ?>>
                <div class="file-upload-display">
                  <div class="file-upload-icon">📄</div>
                  <div class="file-upload-text">
                    <span class="file-upload-label">Click to upload certificate image</span>
                    <span class="file-upload-hint">PNG, JPG, PDF up to 5MB</span>
                  </div>
                </div>
                <div class="file-preview" id="certificateImagePreview" style="display: none;"></div>
              </div>
            </div>
          </div>
        </div>
      </fieldset>



      <fieldset>
        <legend>Personal Information</legend>
        
        <div class="form-group">
          <label for="requestDate">Date *</label>
          <input type="date" id="requestDate" name="requestDate" required 
                 value="<?php echo date('Y-m-d'); ?>">
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="firstName">First Name *</label>
            <input type="text" id="firstName" name="firstName" required placeholder="Enter first name" 
                   value="<?php 
                   if ($request_data) {
                       echo htmlspecialchars(explode(' ', $request_data['full_name'])[0] ?? '');
                   } elseif ($current_user) {
                       echo htmlspecialchars($current_user['first_name'] ?? '');
                   }
                   ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="middleName">Middle Name</label>
            <input type="text" id="middleName" name="middleName" placeholder="Enter middle name" 
                   value="<?php 
                   if ($request_data) {
                       echo htmlspecialchars(implode(' ', array_slice(explode(' ', $request_data['full_name']), 1, -1)));
                   } elseif ($current_user) {
                       echo htmlspecialchars($current_user['middle_name'] ?? '');
                   }
                   ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="lastName">Last Name *</label>
            <?php 
            $lastName = '';
            if ($request_data && !empty($request_data['full_name'])) {
                $nameParts = explode(' ', $request_data['full_name']);
                $lastName = end($nameParts);
            } elseif ($current_user) {
                $lastName = $current_user['last_name'] ?? '';
            }
            ?>
            <input type="text" id="lastName" name="lastName" required placeholder="Enter last name" 
                   value="<?php echo htmlspecialchars($lastName); ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>
        
        <!-- Name validation message -->
        <div id="nameValidation" class="validation-message"></div>

        <div class="form-grid-two">
          <div class="form-group">
            <label for="address1">Street Address *</label>
            <div class="address-input-container">
              <input type="text" id="address1" name="address1" required 
                     placeholder="Search street address in Barangay Gumaoc East..." 
                     value="<?php 
                     if ($request_data) {
                         echo htmlspecialchars($request_data['address']);
                     } elseif ($current_user) {
                         echo htmlspecialchars($current_user['address'] ?? '');
                     }
                     ?>" 
                     <?php echo $readonly ? 'readonly' : ''; ?>
                     autocomplete="off">
              <div id="addressSuggestions" class="address-suggestions"></div>
            </div>
          </div>

          <div class="form-group">
            <label for="address2">Complete Address</label>
            <input type="text" id="address2" name="address2" 
                   value="Barangay Gumaoc East, San Jose Del Monte, Bulacan, Philippines" 
                   readonly style="background-color: #f8f9fa; cursor: not-allowed;">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="mobileNumber">Mobile Number</label>
            <div class="mobile-input-container">
              <div class="country-code">
                <span class="ph-flag">🇵🇭</span>
                <span class="code">+63</span>
              </div>
              <input type="tel" id="mobileNumber" name="mobileNumber" 
                     placeholder="9XX XXX XXXX" 
                     pattern="9[0-9]{9}" 
                     maxlength="10"
                     title="Please enter a valid Philippine mobile number (starting with 9, 10 digits total)"
                     value="<?php 
                     if ($request_data && $request_data['mobile_number']) {
                         echo substr($request_data['mobile_number'], 3);
                     } elseif ($current_user && $current_user['phone']) {
                         // Remove +63 prefix if present
                         $phone = $current_user['phone'];
                         if (substr($phone, 0, 3) === '+63') {
                             echo substr($phone, 3);
                         } elseif (substr($phone, 0, 2) === '63') {
                             echo substr($phone, 2);
                         } else {
                             echo $phone;
                         }
                     }
                     ?>" 
                     <?php echo $readonly ? 'readonly' : ''; ?>>
            </div>
            <small class="input-help">Enter your mobile number without +63 (e.g., 9171234567)</small>
          </div>

          <div class="form-group">
            <label for="civilStatus">Civil Status *</label>
            <select id="civilStatus" name="civilStatus" required <?php echo $readonly ? 'disabled' : ''; ?>>
              <option value="">Select Civil Status</option>
              <?php 
              $civil_status_value = '';
              if ($request_data) {
                  $civil_status_value = $request_data['civil_status'];
              } elseif ($current_user) {
                  $civil_status_value = $current_user['civil_status'];
              }
              ?>
              <option value="Single" <?php echo ($civil_status_value === 'Single') ? 'selected' : ''; ?>>Single</option>
              <option value="Married" <?php echo ($civil_status_value === 'Married') ? 'selected' : ''; ?>>Married</option>
              <option value="Divorced" <?php echo ($civil_status_value === 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
              <option value="Widowed" <?php echo ($civil_status_value === 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
              <option value="Separated" <?php echo ($civil_status_value === 'Separated') ? 'selected' : ''; ?>>Separated</option>
            </select>
          </div>

          <div class="form-group">
            <label for="gender">Gender *</label>
            <select id="gender" name="gender" required <?php echo $readonly ? 'disabled' : ''; ?>>
              <option value="">Select Gender</option>
              <?php 
              $gender_value = '';
              if ($request_data) {
                  $gender_value = $request_data['gender'];
              } elseif ($current_user) {
                  $gender_value = $current_user['gender'];
              }
              ?>
              <option value="Male" <?php echo ($gender_value === 'Male') ? 'selected' : ''; ?>>Male</option>
              <option value="Female" <?php echo ($gender_value === 'Female') ? 'selected' : ''; ?>>Female</option>
            </select>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="birthdate">Birthdate *</label>
            <input type="date" id="birthdate" name="birthdate" required 
                   value="<?php 
                   if ($request_data) {
                       echo $request_data['birth_date'];
                   } elseif ($current_user) {
                       echo htmlspecialchars($current_user['birthdate'] ?? '');
                   }
                   ?>" 
                   onchange="calculateAge()"
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="age">Age</label>
            <?php 
            $calculated_age = '';
            $birth_date_for_age = '';
            if ($request_data && !empty($request_data['birth_date'])) {
                $birth_date_for_age = $request_data['birth_date'];
            } elseif ($current_user && !empty($current_user['birthdate'])) {
                $birth_date_for_age = $current_user['birthdate'];
            }
            
            if ($birth_date_for_age) {
                $birth_date = new DateTime($birth_date_for_age);
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
                   value="<?php 
                   if ($request_data) {
                       echo htmlspecialchars($request_data['birth_place']);
                   } elseif ($current_user) {
                       echo htmlspecialchars($current_user['birth_place'] ?? '');
                   }
                   ?>" 
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="citizenship">Citizenship</label>
            <input type="text" id="citizenship" name="citizenship" placeholder="Filipino" 
                   value="<?php 
                   if ($request_data) {
                       echo htmlspecialchars($request_data['citizenship']);
                   } else {
                       echo 'Filipino';
                   }
                   ?>" 
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
</div>

<?php if (isset($_SESSION['queue_ticket_number'])): ?>
<div class="queue-info-banner kiosk-queue-display">
    <div class="queue-info-content">
        <h2>🎫 YOUR QUEUE TICKET</h2>
        <div class="queue-main-info">
            <div class="ticket-number-display">
                <div class="ticket-label">TICKET NUMBER</div>
                <div class="ticket-number-big"><?php echo htmlspecialchars($_SESSION['queue_ticket_number']); ?></div>
            </div>
            <div class="queue-position-display">
                <div class="position-label">QUEUE POSITION</div>
                <div class="position-number-big">#<?php echo $_SESSION['queue_position']; ?></div>
            </div>
        </div>
        <div class="queue-service-info">
            <div class="service-name">Service: <?php echo htmlspecialchars($_SESSION['service_name'] ?? 'Certificate Request'); ?></div>
            <div class="estimated-time">Estimated processing time: <?php echo $_SESSION['estimated_time'] ?? 'Please check status'; ?></div>
        </div>
        <div class="queue-actions">
            <a href="queue-status.php?lookup=1&ticket_number=<?php echo urlencode($_SESSION['queue_ticket_number']); ?>" class="btn btn-primary btn-large">
                📊 CHECK QUEUE STATUS
            </a>
            <a href="queue-ticket.php" class="btn btn-secondary btn-large">
                🎫 GET NEW TICKET
            </a>
        </div>
        <div class="queue-instructions">
            <h4>📋 IMPORTANT INSTRUCTIONS:</h4>
            <ul>
                <li>🔹 Keep this ticket number safe - you'll need it to check your status</li>
                <li>🔹 Monitor the queue display screen for your number</li>
                <li>🔹 Present this ticket when called at the service counter</li>
                <li>🔹 Arrive at the office when your number is close to being called</li>
            </ul>
        </div>
    </div>
</div>

<style>
/* Enhanced Kiosk-Friendly Queue Display */
.kiosk-queue-display {
    background: linear-gradient(135deg, #1565c0, #1976d2);
    border: 4px solid #0d47a1;
    border-radius: 20px;
    padding: 40px;
    margin: 30px 0;
    text-align: center;
    box-shadow: 0 10px 30px rgba(13, 71, 161, 0.3);
    color: white;
}

.kiosk-queue-display h2 {
    color: white;
    margin: 0 0 30px 0;
    font-size: 2.5rem;
    font-weight: 900;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    letter-spacing: 2px;
}

.queue-main-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin: 30px 0;
}

.ticket-number-display, .queue-position-display {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 15px;
    padding: 30px 20px;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.ticket-label, .position-label {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 15px;
    opacity: 0.9;
    letter-spacing: 1px;
}

.ticket-number-big, .position-number-big {
    font-size: 4rem;
    font-weight: 900;
    font-family: 'Courier New', monospace;
    text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.4);
    margin: 10px 0;
    word-break: break-all;
    line-height: 1;
}

.queue-service-info {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 20px;
    margin: 25px 0;
    backdrop-filter: blur(5px);
}

.service-name {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 8px;
}

.estimated-time {
    font-size: 1.1rem;
    opacity: 0.9;
}

.queue-actions {
    display: flex;
    gap: 20px;
    margin: 30px 0;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-large {
    padding: 15px 30px;
    font-size: 1.2rem;
    font-weight: 700;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.btn-large:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.queue-instructions {
    text-align: left;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 25px;
    margin-top: 30px;
    backdrop-filter: blur(5px);
}

.queue-instructions h4 {
    color: white;
    font-size: 1.3rem;
    margin: 0 0 15px 0;
    text-align: center;
}

.queue-instructions ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.queue-instructions li {
    padding: 8px 0;
    font-size: 1.1rem;
    line-height: 1.4;
}

/* Responsive Design for Kiosk */
@media (max-width: 768px) {
    .queue-main-info {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .ticket-number-big, .position-number-big {
        font-size: 3rem;
    }
    
    .kiosk-queue-display h2 {
        font-size: 2rem;
    }
    
    .queue-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-large {
        width: 100%;
        max-width: 300px;
    }
}

.queue-info-banner {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border: 2px solid #2196f3;
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
    text-align: center;
}

.queue-info-content h3 {
    color: #1565c0;
    margin: 0 0 15px 0;
}

.queue-details {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: center;
}

.queue-item {
    background: white;
    padding: 10px 15px;
    border-radius: 8px;
    font-size: 16px;
}

.queue-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    flex-wrap: wrap;
    justify-content: center;
}



/* Auto-populated Inline Card Design */
.auto-populated-inline-card {
  background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
  border: 2px solid #4caf50;
  border-radius: 12px;
  padding: 10px 16px;
  box-shadow: 0 4px 12px rgba(76, 175, 80, 0.15);
  display: flex;
  align-items: center;
  gap: 10px;
  max-width: 280px;
  animation: slideInFromRight 0.5s ease;
  border-left: 4px solid #4caf50;
}

.auto-populated-inline-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(76, 175, 80, 0.2);
  transition: all 0.3s ease;
}

@keyframes slideInFromRight {
  0% {
    transform: translateX(30px);
    opacity: 0;
  }
  100% {
    transform: translateX(0);
    opacity: 1;
  }
}

.auto-populated-inline-card .card-icon {
  font-size: 18px;
  flex-shrink: 0;
  animation: iconPulse 3s infinite;
}

@keyframes iconPulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

.auto-populated-inline-card .card-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
}

.auto-populated-inline-card .card-text strong {
  color: #1b5e20;
  font-size: 14px;
  font-weight: 700;
  line-height: 1.2;
}

.auto-populated-inline-card .card-text span {
  color: #2e7d32;
  font-size: 12px;
  line-height: 1.3;
  opacity: 0.9;
}

/* Responsive design for form header */
@media (max-width: 768px) {
  .form-header-row {
    flex-direction: column;
    gap: 15px;
    align-items: stretch;
  }
  
  .auto-populated-inline-card {
    max-width: 100%;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .form-header-row {
    gap: 12px;
  }
  
  .auto-populated-inline-card {
    padding: 8px 12px;
    gap: 8px;
  }
  
  .auto-populated-inline-card .card-text strong {
    font-size: 13px;
  }
  
  .auto-populated-inline-card .card-text span {
    font-size: 11px;
  }
}

/* Responsive design for mobile */
@media (max-width: 768px) {
  .auto-populated-card {
    position: relative !important;
    top: auto !important;
    right: auto !important;
    margin-bottom: 20px;
    max-width: 100%;
    min-width: auto;
  }
  
  .auto-populated-card::after {
    display: none;
  }
}

@media (max-width: 480px) {
  .auto-populated-card {
    padding: 14px 18px;
    gap: 10px;
    border-radius: 12px;
  }
  
  .card-icon {
    font-size: 18px;
  }
  
  .card-text strong {
    font-size: 14px;
  }
  
  .card-text span {
    font-size: 11px;
  }
}

@media (max-width: 768px) {
    .queue-actions {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<?php 
// Clear queue session data after displaying
unset($_SESSION['queue_ticket_number'], $_SESSION['queue_position']); 
endif; ?>

<style>
/* Toast Notifications */
.toast-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.2);
  backdrop-filter: blur(2px);
  z-index: 999;
  opacity: 0;
  transition: all 0.3s ease;
  pointer-events: none;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toast-overlay.show {
  opacity: 1;
  pointer-events: auto;
}

.toast {
  position: relative;
  top: auto;
  left: auto;
  transform: scale(0.8);
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(8px);
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.4);
  z-index: 1000;
  opacity: 0;
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  min-width: 400px;
  max-width: 600px;
  width: 90%;
  margin: 20px;
}

.toast.show {
  transform: scale(1);
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
    margin: 15px;
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
    margin: 10px;
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
  max-width: 95%;
  width: 95%;
  margin: 20px auto;
  padding: 20px 15px;
  background: rgba(255, 255, 255, 0.98);
  border-radius: 24px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  min-height: 80vh;
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

/* Kiosk-style Certificate Selection */
.certificate-selection-screen {
  text-align: center;
  padding: 40px 20px;
  min-height: 60vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.kiosk-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 20px;
  text-transform: uppercase;
  letter-spacing: 2px;
}

.kiosk-subtitle {
  font-size: 1.2rem;
  color: #6c757d;
  margin-bottom: 50px;
  font-weight: 400;
}

.certificate-types {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 25px;
  margin-top: 40px;
  max-width: 1200px;
  margin-left: auto;
  margin-right: auto;
}

.certificate-option {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
  padding: 40px 30px;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border: 3px solid #e9ecef;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.4s ease;
  font-weight: 600;
  font-size: 1.1rem;
  text-align: center;
  min-height: 180px;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.certificate-option::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
  transition: left 0.5s ease;
}

.certificate-option:hover::before {
  left: 100%;
}

.certificate-icon {
  font-size: 3rem;
  margin-bottom: 10px;
  transition: transform 0.3s ease;
}

.certificate-option:hover .certificate-icon {
  transform: scale(1.1) rotate(5deg);
}

.certificate-option:hover {
  background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(32, 201, 151, 0.1) 100%);
  border-color: #28a745;
  transform: translateY(-5px) scale(1.02);
  box-shadow: 0 15px 35px rgba(40, 167, 69, 0.3);
}

.certificate-option input[type="radio"] {
  display: none;
}

.certificate-option.selected {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
  border-color: #28a745;
  transform: translateY(-5px) scale(1.05);
  box-shadow: 0 15px 35px rgba(40, 167, 69, 0.4);
}

.certificate-option.selected .certificate-icon {
  color: white;
}

/* Form Container - Initially Hidden */
.form-container {
  display: none;
  opacity: 0;
  transform: translateY(20px);
  transition: all 0.5s ease;
}

.form-container.show {
  display: block;
  opacity: 1;
  transform: translateY(0);
}

/* Back Button */
.back-to-selection {
  background: linear-gradient(135deg, #6c757d, #495057);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 25px;
  cursor: pointer;
  font-weight: 600;
  margin-bottom: 20px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
}

.back-to-selection:hover {
  background: linear-gradient(135deg, #495057, #343a40);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
}

/* Validation Message Styles */
.validation-message {
  margin-top: 0.5rem;
  padding: 0.8rem 1rem;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  display: none;
  opacity: 0;
  transform: translateY(-10px);
  transition: all 0.3s ease;
}

.validation-message.show {
  display: block;
  opacity: 1;
  transform: translateY(0);
}

.validation-message.success {
  background: rgba(40, 167, 69, 0.1);
  color: #155724;
  border: 1px solid rgba(40, 167, 69, 0.3);
}

.validation-message.error {
  background: rgba(220, 53, 69, 0.1);
  color: #721c24;
  border: 1px solid rgba(220, 53, 69, 0.3);
}

.validation-message.warning {
  background: rgba(255, 193, 7, 0.1);
  color: #856404;
  border: 1px solid rgba(255, 193, 7, 0.3);
}

.validation-message.loading {
  background: rgba(108, 117, 125, 0.1);
  color: #495057;
  border: 1px solid rgba(108, 117, 125, 0.3);
}

/* Input validation states */
.form-group input.validating {
  border-color: #ffc107 !important;
  box-shadow: 0 0 0 4px rgba(255, 193, 7, 0.15) !important;
}

.form-group input.valid {
  border-color: #28a745 !important;
  box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.15) !important;
}

.form-group input.invalid {
  border-color: #dc3545 !important;
  box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.15) !important;
}
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.back-to-selection:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
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

.form-group textarea {
  width: 100%;
  padding: 12px 15px;
  border: 2px solid #e9ecef;
  border-radius: 10px;
  font-size: 14px;
  transition: all 0.3s ease;
  background: #fff;
  resize: vertical;
  min-height: 80px;
  font-family: inherit;
}

.form-group textarea:focus {
  outline: none;
  border-color: #28a745;
  box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
}

.form-group textarea[readonly] {
  background: #f8f9fa;
  color: #6c757d;
  cursor: not-allowed;
}

/* Form Section Styling */
.form-section {
  margin: 25px 0;
  padding: 20px;
  background: rgba(40, 167, 69, 0.05);
  border-radius: 12px;
  border: 1px solid rgba(40, 167, 69, 0.1);
}

.form-section h4 {
  color: #28a745;
  margin: 0 0 8px 0;
  font-size: 1.1rem;
  font-weight: 600;
}

.section-description {
  color: #6c757d;
  margin: 0 0 20px 0;
  font-size: 0.9rem;
}

/* File Upload Styling */
.file-upload-container {
  position: relative;
  border: 2px dashed #d0d7de;
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  background: #f8f9fa;
  transition: all 0.3s ease;
  cursor: pointer;
}

.file-upload-container:hover {
  border-color: #28a745;
  background: rgba(40, 167, 69, 0.05);
}

.file-upload-container.drag-over {
  border-color: #28a745;
  background: rgba(40, 167, 69, 0.1);
  transform: scale(1.02);
}

.file-input {
  position: absolute;
  opacity: 0;
  width: 100%;
  height: 100%;
  cursor: pointer;
  top: 0;
  left: 0;
}

.file-upload-display {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  pointer-events: none;
}

.file-upload-icon {
  font-size: 2rem;
  color: #6c757d;
}

.file-upload-text {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.file-upload-label {
  font-weight: 600;
  color: #495057;
  font-size: 14px;
}

.file-upload-hint {
  font-size: 12px;
  color: #6c757d;
}

.file-preview {
  margin-top: 15px;
  padding: 10px;
  background: white;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.file-preview img {
  max-width: 100%;
  max-height: 200px;
  border-radius: 4px;
}

.file-preview-info {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  background: #f8f9fa;
  border-radius: 6px;
  margin-top: 10px;
}

.file-preview-icon {
  font-size: 1.5rem;
}

.file-preview-details {
  flex: 1;
}

.file-preview-name {
  font-weight: 600;
  color: #495057;
  font-size: 14px;
}

.file-preview-size {
  font-size: 12px;
  color: #6c757d;
}

.file-remove-btn {
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  transition: all 0.3s ease;
}

.file-remove-btn:hover {
  background: #c82333;
  transform: scale(1.1);
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
  
  .file-upload-container {
    padding: 15px;
  }
  
  .file-upload-icon {
    font-size: 1.5rem;
  }
  
  .file-upload-label {
    font-size: 13px;
  }
  
  .file-upload-hint {
    font-size: 11px;
  }
  
  .form-section {
    padding: 15px;
  }
  
  .form-section h4 {
    font-size: 1rem;
  }
}

/* Mobile Number Input Styling */
.mobile-input-container {
  display: flex;
  border: 2px solid #e0e7ff;
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s ease;
  background: white;
}

.mobile-input-container:focus-within {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.country-code {
  display: flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  padding: 12px 16px;
  border-right: 1px solid #e2e8f0;
  white-space: nowrap;
  min-width: 100px;
}

.ph-flag {
  font-size: 1.2em;
  line-height: 1;
}

.code {
  font-weight: 600;
  color: #334155;
  font-size: 0.95em;
}

.mobile-input-container input {
  flex: 1;
  border: none;
  padding: 12px 16px;
  font-size: 1rem;
  background: transparent;
  outline: none;
  min-width: 0;
}

.mobile-input-container input:focus {
  border: none;
  box-shadow: none;
}

.input-help {
  display: block;
  margin-top: 6px;
  color: #6b7280;
  font-size: 0.875rem;
  line-height: 1.4;
}

/* Address Input Styling */
.address-input-container {
  position: relative;
}

.address-suggestions {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #e2e8f0;
  border-top: none;
  border-radius: 0 0 8px 8px;
  max-height: 200px;
  overflow-y: auto;
  z-index: 1000;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  display: none;
}

.address-suggestions.show {
  display: block;
}

.address-suggestion {
  padding: 12px 16px;
  cursor: pointer;
  border-bottom: 1px solid #f1f5f9;
  transition: background-color 0.2s ease;
  font-size: 0.95rem;
}

.address-suggestion:hover {
  background: #f8fafc;
}

.address-suggestion:last-child {
  border-bottom: none;
}

.address-suggestion.selected {
  background: #eef2ff;
  color: #3730a3;
}

.suggestion-main {
  font-weight: 500;
  color: #1f2937;
}

.suggestion-sub {
  font-size: 0.85rem;
  color: #6b7280;
  margin-top: 2px;
}

/* Tricycle Details Section Styling */
#tricycleDetailsSection {
  background: rgba(255, 193, 7, 0.05);
  border-color: #ffc107;
}

#tricycleDetailsSection legend {
  background: linear-gradient(135deg, #ffc107, #ffb300);
  color: #212529;
}

#tricycleDetailsSection .form-group input {
  border-color: #ffc107;
}

#tricycleDetailsSection .form-group input:focus {
  border-color: #ffb300;
  box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.1);
}

/* Cedula Details Section Styling */
#cedulaDetailsSection {
  background: rgba(255, 235, 59, 0.05);
  border-color: #ffeb3b;
}

#cedulaDetailsSection legend {
  background: linear-gradient(135deg, #ffeb3b, #fbc02d);
  color: #212529;
}

#cedulaDetailsSection .form-group input,
#cedulaDetailsSection .form-group select {
  border-color: #ffeb3b;
}

#cedulaDetailsSection .form-group input:focus,
#cedulaDetailsSection .form-group select:focus {
  border-color: #fbc02d;
  box-shadow: 0 0 0 3px rgba(255, 235, 59, 0.1);
}

/* Cedula Tax Section */
.cedula-tax-section {
  background: rgba(255, 249, 196, 0.5);
  border: 1px solid #fff176;
  border-radius: 8px;
  padding: 20px;
  margin-top: 20px;
}

.cedula-tax-section h4 {
  color: #f57f17;
  margin: 0 0 20px 0;
  font-size: 1.1rem;
  font-weight: 600;
}

.cedula-tax-section h5 {
  color: #ff8f00;
  margin: 20px 0 15px 0;
  font-size: 1rem;
  font-weight: 600;
}

/* Radio Group for Basic Tax */
.radio-group {
  display: flex;
  gap: 20px;
  margin-bottom: 10px;
  flex-wrap: wrap;
}

.radio-option {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-weight: 500;
}

.radio-option input[type="radio"] {
  width: 16px;
  height: 16px;
  accent-color: #ff8f00;
}

/* Tax Input Group */
.tax-input-group {
  display: flex;
  align-items: center;
  gap: 15px;
  flex-wrap: wrap;
}

.tax-input-group input {
  flex: 1;
  min-width: 200px;
}

.tax-calculation {
  background: #e8f5e8;
  color: #2e7d32;
  padding: 8px 12px;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.9rem;
  white-space: nowrap;
}

/* Additional Tax Section */
.additional-tax-section {
  background: rgba(255, 255, 255, 0.7);
  border: 1px solid #ffcc02;
  border-radius: 8px;
  padding: 15px;
  margin-top: 15px;
}

/* Tax Summary */
.tax-summary {
  background: linear-gradient(135deg, #e8f5e8, #c8e6c8);
  border: 2px solid #4caf50;
  border-radius: 8px;
  padding: 20px;
  margin-top: 20px;
}

.tax-summary .form-group input {
  background: #fff;
  font-weight: 600;
  font-size: 1.1rem;
  text-align: center;
}

.tax-summary .form-group input[readonly] {
  background: #f1f8e9;
  color: #2e7d32;
  border-color: #4caf50;
}

/* Enhanced Certificate Types Grid */
.certificate-types {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 15px;
  margin-top: 20px;
}

@media (max-width: 768px) {
  .container {
    max-width: 98%;
    width: 98%;
    margin: 10px auto;
    padding: 15px 10px;
  }
  
  .kiosk-title {
    font-size: 2rem;
  }
  
  .kiosk-subtitle {
    font-size: 1rem;
    margin-bottom: 30px;
  }
  
  .certificate-types {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .certificate-option {
    min-height: 150px;
    padding: 30px 20px;
  }
  
  .certificate-icon {
    font-size: 2.5rem;
  }
  
  .radio-group {
    flex-direction: column;
    gap: 10px;
  }
  
  .tax-input-group {
    flex-direction: column;
    align-items: stretch;
  }
  
  .tax-input-group input {
    min-width: auto;
  }
}

/* Toast Notifications */
.toast-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.2);
  backdrop-filter: blur(2px);
  z-index: 999;
  opacity: 0;
  transition: all 0.3s ease;
  pointer-events: none;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toast-overlay.show {
  opacity: 1;
  pointer-events: auto;
}

.toast {
  position: relative;
  top: auto;
  left: auto;
  transform: scale(0.8);
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(8px);
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.4);
  z-index: 1000;
  opacity: 0;
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  min-width: 400px;
  max-width: 600px;
  width: 90%;
  margin: 20px;
}

.toast.show {
  transform: scale(1);
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
    margin: 15px;
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
    margin: 10px;
  }
  
  .toast-content {
    padding: 18px 20px;
    gap: 12px;
  }
}

/* Enhanced input validation styling */
.form-group input:invalid {
  border-color: #dc3545;
  box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.1);
}

.form-group input:valid {
  border-color: #28a745;
}

/* Disable spinner buttons for number inputs to prevent confusion */
.form-group input[type="number"]::-webkit-outer-spin-button,
.form-group input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
  display: none;
}

.form-group input[type="number"] {
  -moz-appearance: textfield;
}

/* Input help text for tricycle fields */
#tricycleDetailsSection .input-help {
  display: block;
  margin-top: 6px;
  color: #6b7280;
  font-size: 0.875rem;
  line-height: 1.4;
}

/* Year model specific styling */
#yearModel {
  text-align: center;
  font-weight: 500;
  font-family: 'Courier New', monospace;
}

/* Operator license specific styling */
#operatorLicense {
  font-family: 'Courier New', monospace;
  letter-spacing: 1px;
}

/* Prevent text selection of input spinners */
#yearModel::-webkit-outer-spin-button,
#yearModel::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>

<script>
// Define street addresses array for autocomplete
const streetAddresses = [
  'Purok 1, Gumaoc East',
  'Purok 2, Gumaoc East', 
  'Purok 3, Gumaoc East',
  'Purok 4, Gumaoc East',
  'Purok 5, Gumaoc East',
  'Purok 6, Gumaoc East',
  'Main Road, Gumaoc East',
  'Chapel Road, Gumaoc East',
  'School Road, Gumaoc East',
  'Market Road, Gumaoc East',
  'Barangay Hall Road, Gumaoc East',
  'Health Center Road, Gumaoc East',
  'Sports Complex Road, Gumaoc East',
  'Cemetery Road, Gumaoc East',
  'Upper Gumaoc East',
  'Lower Gumaoc East',
  'Central Gumaoc East',
  'San Jose Del Monte City Road, Gumaoc East',
  'Quirino Highway, Gumaoc East',
  'NIA Road, Gumaoc East',
  'Gumaoc East Proper',
  'Gumaoc East Extension',
  'Gumaoc East Poblacion',
  'Gumaoc East Riverside',
  'Gumaoc East San Vicente',
  'Gumaoc East Santo Niño',
  'Gumaoc East Bagong Buhay',
  'Gumaoc East Bayanihan',
  'Gumaoc East Maligaya',
  'Gumaoc East Pag-asa',
  'Gumaoc East Salin ng Lahi',
  'Villa Joson, Gumaoc East',
  'Villa Maria, Gumaoc East',
  'Villa Rey, Gumaoc East',
  'Villa Verde, Gumaoc East',
  'Villa Angela, Gumaoc East',
  'Villa Felicidad, Gumaoc East',
  'Villa Esperanza, Gumaoc East',
  'Villa Consuelo, Gumaoc East',
  'Villa Cristina, Gumaoc East'
];

// Add zone-based addresses dynamically
for (let i = 1; i <= 100; i++) {
  streetAddresses.push(`Gumaoc East Zone ${i}`);
}

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

// Show certificate selection screen
function showSelectionScreen() {
  const selectionScreen = document.getElementById('certificateSelectionScreen');
  const formContainer = document.getElementById('formContainer');
  
  if (selectionScreen && formContainer) {
    formContainer.classList.remove('show');
    setTimeout(() => {
      formContainer.style.display = 'none';
      selectionScreen.style.display = 'block';
    }, 300);
  }
  
  // Clear selection
  document.querySelectorAll('.certificate-option').forEach(option => {
    option.classList.remove('selected');
  });
  
  // Clear hidden input
  const hiddenInput = document.getElementById('selectedCertificateType');
  if (hiddenInput) {
    hiddenInput.value = '';
  }
}

// Show form for selected certificate type
function showCertificateForm(certificateType) {
  const selectionScreen = document.getElementById('certificateSelectionScreen');
  const formContainer = document.getElementById('formContainer');
  const hiddenInput = document.getElementById('selectedCertificateType');
  
  // Hide selection screen
  if (selectionScreen) {
    selectionScreen.style.display = 'none';
  }
  
  // Set certificate type
  if (hiddenInput) {
    hiddenInput.value = certificateType;
  }
  
  // Show form
  if (formContainer) {
    formContainer.style.display = 'block';
    setTimeout(() => {
      formContainer.classList.add('show');
    }, 100);
  }
  
  // Toggle specific sections
  toggleCertificateDetails(certificateType);
}

// Main function to toggle certificate details based on selection
function toggleCertificateDetails(certificateType) {
  console.log('toggleCertificateDetails called for:', certificateType);
  
  const tricycleSection = document.getElementById('tricycleDetailsSection');
  const cedulaSection = document.getElementById('cedulaDetailsSection');
  const businessSection = document.getElementById('businessApplicationSection');
  
  // Hide all sections first
  if (tricycleSection) {
    tricycleSection.style.display = 'none';
  }
  if (cedulaSection) {
    cedulaSection.style.display = 'none';
  }
  if (businessSection) {
    businessSection.style.display = 'none';
  }
  
  // Show relevant section based on selection
  if (certificateType === 'TRICYCLE PERMIT') {
    console.log('Tricycle permit selected');
    if (tricycleSection) {
      tricycleSection.style.display = 'block';
    }
    setTricycleFieldsRequired(true);
    setCedulaFieldsRequired(false);
    setBusinessFieldsRequired(false);
  } else if (certificateType === 'CEDULA/CTC') {
    console.log('Cedula/CTC selected');
    if (cedulaSection) {
      cedulaSection.style.display = 'block';
    }
    setCedulaFieldsRequired(true);
    setTricycleFieldsRequired(false);
    setBusinessFieldsRequired(false);
  } else if (certificateType === 'BUSINESS APPLICATION') {
    console.log('Business application selected');
    if (businessSection) {
      businessSection.style.display = 'block';
    }
    setBusinessFieldsRequired(true);
    setTricycleFieldsRequired(false);
    setCedulaFieldsRequired(false);
    
    // Generate reference number for business application
    generateBusinessReferenceNumber();
    
    // Set today's date for business application date
    const businessDateField = document.getElementById('businessApplicationDate');
    if (businessDateField && !businessDateField.value) {
      const today = new Date().toISOString().split('T')[0];
      businessDateField.value = today;
    }
  } else {
    console.log('Basic certificate type selected');
    setTricycleFieldsRequired(false);
    setCedulaFieldsRequired(false);
    setBusinessFieldsRequired(false);
  }
}

// Set required status for tricycle fields
function setTricycleFieldsRequired(required) {
  const isReadonly = document.querySelector('form[style*="pointer-events: none"]');
  if (isReadonly) return;
  
  const fields = ['makeType', 'motorNo', 'chassisNo', 'plateNo'];
  fields.forEach(fieldId => {
    const field = document.getElementById(fieldId);
    if (field) {
      field.required = required;
      console.log(`${fieldId} required set to ${required}`);
    }
  });
}

// Set required status for cedula fields
function setCedulaFieldsRequired(required) {
  const isReadonly = document.querySelector('form[style*="pointer-events: none"]');
  if (isReadonly) return;
  
  const fields = [
    'cedulaYear', 'placeOfIssue', 'dateIssued', 'cedulaCitizenship',
    'cedulaPlaceOfBirth', 'cedulaDateOfBirth', 'cedulaCivilStatus',
    'professionOccupation'
  ];
  
  fields.forEach(fieldId => {
    const field = document.getElementById(fieldId);
    if (field) {
      field.required = required;
      console.log(`${fieldId} required set to ${required}`);
    }
  });
}

// Set required status for business application fields
function setBusinessFieldsRequired(required) {
  const isReadonly = document.querySelector('form[style*="pointer-events: none"]');
  if (isReadonly) return;
  
  const fields = [
    'businessName', 'businessLocation', 'businessOwnerAddress',
    'businessOrNumber', 'businessCtcNumber'
  ];
  
  fields.forEach(fieldId => {
    const field = document.getElementById(fieldId);
    if (field) {
      field.required = required;
      console.log(`${fieldId} required set to ${required}`);
    }
  });
}

// Generate business reference number
function generateBusinessReferenceNumber() {
  const referenceField = document.getElementById('businessReferenceNo');
  if (referenceField && !referenceField.value) {
    const year = new Date().getFullYear();
    const randomNum = Math.floor(Math.random() * 9999) + 1;
    const paddedNum = randomNum.toString().padStart(4, '0');
    const referenceNo = `BA-${year}-${paddedNum}`;
    referenceField.value = referenceNo;
  }
}

// Update basic community tax amount based on type
function updateBasicTax() {
  const voluntaryRadio = document.querySelector('input[name="basicCommunityTaxType"][value="voluntary"]');
  const exemptedRadio = document.querySelector('input[name="basicCommunityTaxType"][value="exempted"]');
  const basicTaxInput = document.getElementById('basicCommunityTax');
  
  if (basicTaxInput) {
    if (voluntaryRadio && voluntaryRadio.checked) {
      basicTaxInput.value = '5.00';
    } else if (exemptedRadio && exemptedRadio.checked) {
      basicTaxInput.value = '1.00';
    }
    calculateTotalTax();
  }
}

// Calculate tax for income amounts
function calculateAdditionalTax(amount) {
  if (!amount || amount <= 0) return 0;
  return Math.floor(amount / 1000) * 1.00;
}

// Update tax calculations
function updateTaxCalculations() {
  // Business tax
  const businessAmount = parseFloat(document.getElementById('grossReceiptsBusiness')?.value) || 0;
  const businessTax = calculateAdditionalTax(businessAmount);
  const businessCalc = document.getElementById('businessTaxCalc');
  if (businessCalc) businessCalc.textContent = `Tax: ₱${businessTax.toFixed(2)}`;
  
  // Profession tax
  const professionAmount = parseFloat(document.getElementById('salariesProfession')?.value) || 0;
  const professionTax = calculateAdditionalTax(professionAmount);
  const professionCalc = document.getElementById('professionTaxCalc');
  if (professionCalc) professionCalc.textContent = `Tax: ₱${professionTax.toFixed(2)}`;
  
  // Property tax
  const propertyAmount = parseFloat(document.getElementById('incomeRealProperty')?.value) || 0;
  const propertyTax = calculateAdditionalTax(propertyAmount);
  const propertyCalc = document.getElementById('propertyTaxCalc');
  if (propertyCalc) propertyCalc.textContent = `Tax: ₱${propertyTax.toFixed(2)}`;
  
  calculateTotalTax();
}

// Calculate total tax amount
function calculateTotalTax() {
  const basicTax = parseFloat(document.getElementById('basicCommunityTax')?.value) || 0;
  const businessAmount = parseFloat(document.getElementById('grossReceiptsBusiness')?.value) || 0;
  const professionAmount = parseFloat(document.getElementById('salariesProfession')?.value) || 0;
  const propertyAmount = parseFloat(document.getElementById('incomeRealProperty')?.value) || 0;
  
  const additionalTax = calculateAdditionalTax(businessAmount) + 
                       calculateAdditionalTax(professionAmount) + 
                       calculateAdditionalTax(propertyAmount);
  
  // Cap additional tax at 5000.00
  const cappedAdditionalTax = Math.min(additionalTax, 5000.00);
  
  const totalTax = basicTax + cappedAdditionalTax;
  const interest = parseFloat(document.getElementById('interest')?.value) || 0;
  const totalAmountPaid = totalTax + interest;
  
  const totalTaxField = document.getElementById('totalTax');
  const totalAmountField = document.getElementById('totalAmountPaid');
  
  if (totalTaxField) totalTaxField.value = totalTax.toFixed(2);
  if (totalAmountField) totalAmountField.value = totalAmountPaid.toFixed(2);
}

// Setup mobile number validation
function setupMobileNumberValidation() {
  const mobileInput = document.getElementById('mobileNumber');
  if (mobileInput) {
    mobileInput.addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
      
      // Limit to 10 digits
      if (value.length > 10) {
        value = value.substring(0, 10);
      }
      
      // Must start with 9
      if (value.length > 0 && value[0] !== '9') {
        value = '9' + value.substring(1);
      }
      
      e.target.value = value;
    });
  }
}

// Setup tricycle validation
function setupTricycleValidation() {
  const yearModelInput = document.getElementById('yearModel');
  if (yearModelInput) {
    yearModelInput.addEventListener('input', function(e) {
      const year = parseInt(e.target.value);
      const currentYear = new Date().getFullYear();
      
      if (year && (year < 1980 || year > currentYear)) {
        e.target.setCustomValidity(`Please enter a year between 1980 and ${currentYear}`);
      } else {
        e.target.setCustomValidity('');
      }
    });
  }
  
  const operatorLicenseInput = document.getElementById('operatorLicense');
  if (operatorLicenseInput) {
    operatorLicenseInput.addEventListener('input', function(e) {
      // Allow only numbers and dashes
      let value = e.target.value.replace(/[^0-9\-]/g, '');
      e.target.value = value;
    });
  }
}

// Setup cedula validation and calculations
function setupCedulaValidation() {
  // Basic tax type radio buttons
  const basicTaxRadios = document.querySelectorAll('input[name="basicCommunityTaxType"]');
  basicTaxRadios.forEach(radio => {
    radio.addEventListener('change', updateBasicTax);
  });
  
  // Income amount inputs
  const incomeInputs = [
    'grossReceiptsBusiness',
    'salariesProfession', 
    'incomeRealProperty'
  ];
  
  incomeInputs.forEach(inputId => {
    const input = document.getElementById(inputId);
    if (input) {
      input.addEventListener('input', updateTaxCalculations);
    }
  });
  
  // Interest input
  const interestInput = document.getElementById('interest');
  if (interestInput) {
    interestInput.addEventListener('input', calculateTotalTax);
  }
  
  // Year validation
  const yearInput = document.getElementById('cedulaYear');
  if (yearInput) {
    yearInput.addEventListener('input', function(e) {
      const year = parseInt(e.target.value);
      const currentYear = new Date().getFullYear();
      
      if (year && (year < (currentYear - 5) || year > (currentYear + 1))) {
        e.target.setCustomValidity(`Please enter a year between ${currentYear - 5} and ${currentYear + 1}`);
      } else {
        e.target.setCustomValidity('');
      }
    });
  }
  
  // Height and weight validation
  const heightInput = document.getElementById('height');
  const weightInput = document.getElementById('weight');
  
  if (heightInput) {
    heightInput.addEventListener('input', function(e) {
      const height = parseInt(e.target.value);
      if (height && (height < 100 || height > 250)) {
        e.target.setCustomValidity('Please enter a height between 100-250 cm');
      } else {
        e.target.setCustomValidity('');
      }
    });
  }
  
  if (weightInput) {
    weightInput.addEventListener('input', function(e) {
      const weight = parseInt(e.target.value);
      if (weight && (weight < 20 || weight > 200)) {
        e.target.setCustomValidity('Please enter a weight between 20-200 kg');
      } else {
        e.target.setCustomValidity('');
      }
    });
  }
}

// Initialize address search
function initializeAddressSearch() {
  const addressInput = document.getElementById('address1');
  const suggestionsDiv = document.getElementById('addressSuggestions');
  
  if (!addressInput || !suggestionsDiv) return;
  
  let selectedIndex = -1;
  
  addressInput.addEventListener('input', function(e) {
    const value = e.target.value.toLowerCase();
    
    if (value.length < 2) {
      suggestionsDiv.classList.remove('show');
      return;
    }
    
    const matches = streetAddresses.filter(address => 
      address.toLowerCase().includes(value)
    ).slice(0, 8);
    
    if (matches.length > 0) {
      suggestionsDiv.innerHTML = matches.map((address, index) => 
        `<div class="address-suggestion" data-index="${index}">${address}</div>`
      ).join('');
      
      suggestionsDiv.classList.add('show');
      selectedIndex = -1;
      
      // Add click event listeners to suggestions
      suggestionsDiv.querySelectorAll('.address-suggestion').forEach((suggestion, index) => {
        suggestion.addEventListener('click', function() {
          addressInput.value = matches[index];
          suggestionsDiv.classList.remove('show');
        });
      });
    } else {
      suggestionsDiv.classList.remove('show');
    }
  });
  
  // Handle keyboard navigation
  addressInput.addEventListener('keydown', function(e) {
    const suggestions = suggestionsDiv.querySelectorAll('.address-suggestion');
    
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      selectedIndex = Math.min(selectedIndex + 1, suggestions.length - 1);
      updateSelectedSuggestion(suggestions);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      selectedIndex = Math.max(selectedIndex - 1, -1);
      updateSelectedSuggestion(suggestions);
    } else if (e.key === 'Enter' && selectedIndex >= 0) {
      e.preventDefault();
      addressInput.value = suggestions[selectedIndex].textContent;
      suggestionsDiv.classList.remove('show');
    } else if (e.key === 'Escape') {
      suggestionsDiv.classList.remove('show');
    }
  });
  
  function updateSelectedSuggestion(suggestions) {
    suggestions.forEach((suggestion, index) => {
      suggestion.classList.toggle('selected', index === selectedIndex);
    });
  }
  
  // Hide suggestions when clicking outside
  document.addEventListener('click', function(e) {
    if (!addressInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
      suggestionsDiv.classList.remove('show');
    }
  });
}

// Setup certificate selection handlers
function setupCertificateSelection() {
  const certificateOptions = document.querySelectorAll('.certificate-option');
  
  certificateOptions.forEach(option => {
    option.addEventListener('click', function() {
      const certificateType = this.getAttribute('data-type');
      
      // Update visual selection
      certificateOptions.forEach(opt => opt.classList.remove('selected'));
      this.classList.add('selected');
      
      // Show form after short delay for better UX
      setTimeout(() => {
        showCertificateForm(certificateType);
      }, 300);
    });
  });
}

// Check for pre-selected certificate type
function checkPreSelectedCertificateType() {
  console.log('Checking pre-selected certificate type...');
  
  // Only auto-select if we're in admin view mode with existing data
  if (window.location.search.includes('admin_view=') || window.location.search.includes('readonly=1')) {
    const preSelectedOption = document.querySelector('.certificate-option[data-selected="true"]');
    
    if (preSelectedOption) {
      const certificateType = preSelectedOption.getAttribute('data-type');
      console.log('Admin view - Pre-selected certificate type found:', certificateType);
      
      // Mark as selected and show form immediately for admin view
      preSelectedOption.classList.add('selected');
      showCertificateForm(certificateType);
      return;
    }
  }
  
  // For regular users, always show the selection screen first
  console.log('Regular user access - showing selection screen');
  const selectionScreen = document.getElementById('certificateSelectionScreen');
  const formContainer = document.getElementById('formContainer');
  
  if (selectionScreen) {
    selectionScreen.style.display = 'block';
  }
  if (formContainer) {
    formContainer.style.display = 'none';
    formContainer.classList.remove('show');
  }
}

// Blotter Detection Function
function checkBlotterRecord(firstName, middleName, lastName) {
    return fetch('check-blotter.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=check_blotter&first_name=${encodeURIComponent(firstName)}&middle_name=${encodeURIComponent(middleName)}&last_name=${encodeURIComponent(lastName)}`
    })
    .then(response => response.json())
    .catch(error => {
        console.error('Blotter check error:', error);
        return { has_unresolved_issues: false, message: 'Blotter check unavailable' };
    });
}

// Blotter Warning Modal Management
let blotterModalTimer;
let blotterCountdown = 60; // 1 minute in seconds

function showBlotterWarningModal() {
    // Create modal if it doesn't exist
    let modal = document.getElementById('blotterWarningModal');
    if (!modal) {
        modal = createBlotterWarningModal();
        document.body.appendChild(modal);
    }
    
    // Reset countdown
    blotterCountdown = 60;
    const countdownElement = modal.querySelector('#blotterCountdown');
    if (countdownElement) {
        countdownElement.textContent = blotterCountdown;
    }
    
    // Show modal with animation
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.style.opacity = '1';
        modal.style.transform = 'scale(1)';
    }, 10);
    
    // Start countdown timer
    startBlotterModalTimer();
    
    // Hide other modals if visible to prevent conflicts
    const toastOverlay = document.getElementById('toastOverlay');
    if (toastOverlay && toastOverlay.style.display !== 'none') {
        toastOverlay.style.display = 'none';
    }
}

function createBlotterWarningModal() {
    const modal = document.createElement('div');
    modal.id = 'blotterWarningModal';
    modal.className = 'modal-overlay blotter-warning-modal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 10000;
        opacity: 0;
        transform: scale(0.8);
        transition: all 0.3s ease;
        backdrop-filter: blur(5px);
    `;
    
    modal.innerHTML = `
        <div class="modal-content blotter-warning-content" style="
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border: 4px solid #f39c12;
            border-radius: 20px;
            padding: 0;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(243, 156, 18, 0.4);
            animation: warningPulse 2s infinite alternate;
            overflow: hidden;
        ">
            <div class="modal-header blotter-warning-header" style="
                background: linear-gradient(135deg, #f39c12, #e67e22);
                color: white;
                padding: 1.5rem 2rem;
                margin: 0;
                border-bottom: none;
            ">
                <div style="font-size: 3rem; margin-bottom: 0.5rem;">⚠️</div>
                <h4 style="
                    color: white;
                    font-size: 1.5rem;
                    font-weight: 800;
                    margin: 0;
                    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
                ">Unresolved Barangay Issue Detected</h4>
            </div>
            <div class="modal-body blotter-warning-body" style="
                padding: 2rem;
                margin: 0;
                line-height: 1.6;
                background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            ">
                <p style="
                    font-size: 1.2rem;
                    color: #d35400;
                    font-weight: 600;
                    margin: 0 0 1rem 0;
                ">You still have an unresolved issue with the barangay, please proceed to the help desk</p>
                <p style="
                    font-size: 1rem;
                    color: #8b4513;
                    margin: 0;
                ">This modal will automatically close in <span id="blotterCountdown" style="font-weight: 800; color: #d35400;">60</span> seconds</p>
            </div>
            <div class="modal-footer blotter-warning-footer" style="
                background: linear-gradient(135deg, #fff3cd, #ffeaa7);
                padding: 1.5rem 2rem;
                margin: 0;
                border-top: none;
            ">
                <button onclick="closeBlotterWarningModal()" style="
                    background: linear-gradient(135deg, #e67e22, #d35400);
                    color: white;
                    border: none;
                    padding: 12px 24px;
                    border-radius: 25px;
                    font-size: 1rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 15px rgba(230, 126, 34, 0.3);
                    width: auto;
                    min-width: 120px;
                " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(230, 126, 34, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(230, 126, 34, 0.3)';">I Understand</button>
            </div>
        </div>
    `;
    
    // Add CSS animation for warning pulse
    if (!document.getElementById('blotterWarningStyles')) {
        const styles = document.createElement('style');
        styles.id = 'blotterWarningStyles';
        styles.textContent = `
            @keyframes warningPulse {
                0% { box-shadow: 0 20px 60px rgba(243, 156, 18, 0.4); }
                100% { box-shadow: 0 25px 70px rgba(243, 156, 18, 0.6); }
            }
        `;
        document.head.appendChild(styles);
    }
    
    return modal;
}

function closeBlotterWarningModal() {
    const modal = document.getElementById('blotterWarningModal');
    if (modal) {
        // Clear timer
        if (blotterModalTimer) {
            clearInterval(blotterModalTimer);
            blotterModalTimer = null;
        }
        
        // Animate out
        modal.style.opacity = '0';
        modal.style.transform = 'scale(0.8)';
        
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

function startBlotterModalTimer() {
    const countdownElement = document.getElementById('blotterCountdown');
    
    if (!countdownElement) {
        return;
    }
    
    blotterModalTimer = setInterval(() => {
        blotterCountdown--;
        countdownElement.textContent = blotterCountdown;
        
        // Change color when time is running out
        if (blotterCountdown <= 10) {
            countdownElement.style.color = '#c0392b';
            countdownElement.style.fontWeight = '900';
        } else if (blotterCountdown <= 30) {
            countdownElement.style.color = '#d35400';
            countdownElement.style.fontWeight = '800';
        }
        
        if (blotterCountdown <= 0) {
            clearInterval(blotterModalTimer);
            closeBlotterWarningModal();
        }
    }, 1000);
}



// Enhanced modal escape key support for blotter warning modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const blotterModal = document.getElementById('blotterWarningModal');
        if (blotterModal && blotterModal.style.display !== 'none') {
            closeBlotterWarningModal();
        }
    }
});



// Validate user eligibility (blotter check) before form submission
function validateUserEligibility() {
    const firstName = document.getElementById('firstName')?.value?.trim();
    const middleName = document.getElementById('middleName')?.value?.trim() || '';
    const lastName = document.getElementById('lastName')?.value?.trim();
    
    if (!firstName || !lastName) {
        console.log('Skipping blotter check - insufficient name data');
        return Promise.resolve({ eligible: true, message: 'Insufficient name data for blotter check' });
    }
    
    return checkBlotterRecord(firstName, middleName, lastName)
        .then(blotterResult => {
            if (blotterResult.has_unresolved_issues) {
                showBlotterWarningModal();
                return {
                    eligible: false,
                    message: 'User has unresolved barangay issues - certificate request blocked',
                    blotterDetails: blotterResult
                };
            } else {
                return {
                    eligible: true,
                    message: 'No blotter issues found - user eligible for certificate request'
                };
            }
        })
        .catch(error => {
            console.error('Blotter validation error:', error);
            // On error, allow submission but log the issue
            return {
                eligible: true,
                message: 'Blotter check failed - allowing submission'
            };
        });
}

// Variables for blotter validation debounce
let validationTimeout;

// Show validation message helper function
function showValidationMessage(elementId, message, type) {
    const validationDiv = document.getElementById(elementId);
    if (validationDiv) {
        validationDiv.textContent = message;
        validationDiv.className = `validation-message ${type} show`;
    }
}

// Real-time name validation with blotter checking
function validateNameAndBlotter() {
    const firstNameInput = document.getElementById('firstName');
    const middleNameInput = document.getElementById('middleName');
    const lastNameInput = document.getElementById('lastName');
    const validationDiv = document.getElementById('nameValidation');
    
    // Skip validation in readonly mode or admin view
    const readonly = <?php echo $readonly ? 'true' : 'false'; ?>;
    const adminView = <?php echo $admin_view ? 'true' : 'false'; ?>;
    
    if (readonly || adminView) {
        return;
    }
    
    // Clear previous timeout
    if (validationTimeout) {
        clearTimeout(validationTimeout);
    }
    
    // Reset states
    [firstNameInput, middleNameInput, lastNameInput].forEach(input => {
        if (input) {
            input.classList.remove('validating', 'valid', 'invalid');
        }
    });
    if (validationDiv) {
        validationDiv.classList.remove('show');
    }
    
    const firstName = firstNameInput?.value?.trim() || '';
    const middleName = middleNameInput?.value?.trim() || '';
    const lastName = lastNameInput?.value?.trim() || '';
    
    // Check if required fields are filled
    if (!firstName || !lastName) {
        if (!firstName && firstNameInput) firstNameInput.classList.add('invalid');
        if (!lastName && lastNameInput) lastNameInput.classList.add('invalid');
        if (firstName || lastName) { // Only show message if at least one field has content
            showValidationMessage('nameValidation', 'First name and last name are required', 'error');
        }
        return;
    }
    
    // Show loading state
    [firstNameInput, middleNameInput, lastNameInput].forEach(input => {
        if (input) {
            input.classList.add('validating');
        }
    });
    showValidationMessage('nameValidation', 'Checking name and blotter records...', 'loading');
    
    // Debounce the API call (1.5 seconds as per specifications)
    validationTimeout = setTimeout(async () => {
        try {
            // Check for blotter records
            const blotterResult = await checkBlotterRecord(firstName, middleName, lastName);
            
            [firstNameInput, middleNameInput, lastNameInput].forEach(input => {
                if (input) {
                    input.classList.remove('validating');
                }
            });
            
            if (blotterResult.has_unresolved_issues) {
                // Show blotter warning modal
                showBlotterWarningModal();
                // Mark name as valid but show warning
                [firstNameInput, middleNameInput, lastNameInput].forEach(input => {
                    if (input) {
                        input.classList.add('valid');
                    }
                });
                showValidationMessage('nameValidation', '⚠️ Unresolved barangay issues detected - please visit help desk', 'warning');
            } else {
                // No blotter issues found
                [firstNameInput, middleNameInput, lastNameInput].forEach(input => {
                    if (input) {
                        input.classList.add('valid');
                    }
                });
                showValidationMessage('nameValidation', '✓ Name verified, no barangay issues found', 'success');
            }
            
        } catch (error) {
            console.error('Name/Blotter validation error:', error);
            [firstNameInput, middleNameInput, lastNameInput].forEach(input => {
                if (input) {
                    input.classList.remove('validating');
                    input.classList.add('invalid');
                }
            });
            showValidationMessage('nameValidation', 'Error checking name and records', 'error');
        }
    }, 1500); // 1.5 seconds as per specifications
}

// Main DOM ready function
document.addEventListener('DOMContentLoaded', function() {
  console.log('DOM Content Loaded - Starting initialization');
  
  // Set today's date
  const today = new Date().toISOString().split('T')[0];
  const requestDateField = document.getElementById('requestDate');
  if (requestDateField && !requestDateField.value) {
    requestDateField.value = today;
  }
  
  // Set default date issued for cedula
  const dateIssuedField = document.getElementById('dateIssued');
  if (dateIssuedField && !dateIssuedField.value) {
    dateIssuedField.value = today;
  }
  
  // Calculate age if birthdate is already filled
  const birthdateField = document.getElementById('birthdate');
  if (birthdateField && birthdateField.value) {
    calculateAge();
  }

  // Setup all validation functions
  setupMobileNumberValidation();
  setupTricycleValidation();
  setupCedulaValidation();
  
  // Show toast notifications
  const successToast = document.getElementById('successToast');
  const errorToast = document.getElementById('errorToast');
  
  if (successToast) showToast('successToast');
  if (errorToast) showToast('errorToast');
  
  // Setup certificate selection
  setupCertificateSelection();
  
  // Initialize address search
  initializeAddressSearch();
  
  // Check for pre-selected certificate type (only for admin views)
  checkPreSelectedCertificateType();
  
  // Initialize tax calculations if cedula is selected
  updateBasicTax();
  updateTaxCalculations();
  
  // Setup real-time blotter validation for name fields
  const readonly = <?php echo $readonly ? 'true' : 'false'; ?>;
  const adminView = <?php echo $admin_view ? 'true' : 'false'; ?>;
  
  if (!readonly && !adminView) {
    const nameInputs = ['firstName', 'middleName', 'lastName'];
    nameInputs.forEach(inputId => {
      const input = document.getElementById(inputId);
      if (input) {
        // Add both input and blur event listeners as per specifications
        input.addEventListener('input', function() {
          validateNameAndBlotter();
        });
        
        input.addEventListener('blur', function() {
          validateNameAndBlotter();
        });
      }
    });
  }
  
  console.log('Initialization complete');
  
  // Setup file upload functionality
  setupFileUploads();
});

// File Upload Functionality
function setupFileUploads() {
  const fileInputs = document.querySelectorAll('.file-input');
  
  fileInputs.forEach(input => {
    const container = input.closest('.file-upload-container');
    const previewId = input.id + 'Preview';
    const preview = document.getElementById(previewId);
    
    // File selection handler
    input.addEventListener('change', function(e) {
      handleFileSelection(e.target, preview);
    });
    
    // Drag and drop handlers
    container.addEventListener('dragover', function(e) {
      e.preventDefault();
      container.classList.add('drag-over');
    });
    
    container.addEventListener('dragleave', function(e) {
      e.preventDefault();
      container.classList.remove('drag-over');
    });
    
    container.addEventListener('drop', function(e) {
      e.preventDefault();
      container.classList.remove('drag-over');
      
      const files = e.dataTransfer.files;
      if (files.length > 0) {
        input.files = files;
        handleFileSelection(input, preview);
      }
    });
  });
}

function handleFileSelection(input, preview) {
  const file = input.files[0];
  
  if (!file) {
    hideFilePreview(preview);
    return;
  }
  
  // Validate file size (5MB limit)
  const maxSize = 5 * 1024 * 1024; // 5MB in bytes
  if (file.size > maxSize) {
    alert('File size must be less than 5MB');
    input.value = '';
    hideFilePreview(preview);
    return;
  }
  
  // Validate file type
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
  if (!allowedTypes.includes(file.type)) {
    alert('Please select an image (PNG, JPG) or PDF file');
    input.value = '';
    hideFilePreview(preview);
    return;
  }
  
  showFilePreview(file, preview, input);
}

function showFilePreview(file, preview, input) {
  if (!preview) return;
  
  preview.style.display = 'block';
  
  const isImage = file.type.startsWith('image/');
  const fileSize = formatFileSize(file.size);
  
  if (isImage) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.innerHTML = `
        <img src="${e.target.result}" alt="Preview">
        <div class="file-preview-info">
          <div class="file-preview-icon">🖼️</div>
          <div class="file-preview-details">
            <div class="file-preview-name">${file.name}</div>
            <div class="file-preview-size">${fileSize}</div>
          </div>
          <button type="button" class="file-remove-btn" onclick="removeFile('${input.id}', '${preview.id}')">
            ×
          </button>
        </div>
      `;
    };
    reader.readAsDataURL(file);
  } else {
    preview.innerHTML = `
      <div class="file-preview-info">
        <div class="file-preview-icon">📄</div>
        <div class="file-preview-details">
          <div class="file-preview-name">${file.name}</div>
          <div class="file-preview-size">${fileSize}</div>
        </div>
        <button type="button" class="file-remove-btn" onclick="removeFile('${input.id}', '${preview.id}')">
          ×
        </button>
      </div>
    `;
  }
}

function hideFilePreview(preview) {
  if (preview) {
    preview.style.display = 'none';
    preview.innerHTML = '';
  }
}

function removeFile(inputId, previewId) {
  const input = document.getElementById(inputId);
  const preview = document.getElementById(previewId);
  
  if (input) input.value = '';
  hideFilePreview(preview);
}

function formatFileSize(bytes) {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Enhanced form validation
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('certificateForm');
  if (form) {
    form.addEventListener('submit', function(e) {
      console.log('Form submission started');
      
      // Prevent default form submission initially
      e.preventDefault();
      
      // Store form reference for later submission
      const formElement = this;
      
      // First, validate user eligibility (blotter check)
      console.log('Starting blotter validation...');
      validateUserEligibility()
        .then(eligibilityResult => {
          console.log('Blotter validation result:', eligibilityResult);
          
          if (!eligibilityResult.eligible) {
            console.log('User not eligible due to blotter issues - blocking submission');
            // Show error message or handle as needed
            alert('Certificate request blocked: You have unresolved barangay issues. Please visit the help desk first.');
            return; // Stop form submission
          }
          
          console.log('User eligible - continuing with form validation');
          
          // Continue with existing form validation
          // Mobile number validation
          const mobileInput = document.getElementById('mobileNumber');
          if (mobileInput && mobileInput.value) {
            const mobilePattern = /^9[0-9]{9}$/;
            if (!mobilePattern.test(mobileInput.value)) {
              alert('Please enter a valid Philippine mobile number starting with 9 (10 digits total)');
              mobileInput.focus();
              return;
            }
            
            const fullNumber = '+63' + mobileInput.value;
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'full_mobile_number';
            hiddenInput.value = fullNumber;
            formElement.appendChild(hiddenInput);
          }
          
          // Validate tricycle permit fields if selected
          const tricycleOption = document.querySelector('input[value="TRICYCLE PERMIT"]');
          if (tricycleOption && tricycleOption.checked) {
            const yearModel = document.getElementById('yearModel').value;
            
            if (yearModel) {
              if (!/^\d+$/.test(yearModel)) {
                alert('Year Model should contain only numbers');
                document.getElementById('yearModel').focus();
                return;
              }
              
              const year = parseInt(yearModel);
              const currentYear = new Date().getFullYear();
              
              if (isNaN(year) || year < 1980 || year > currentYear) {
                alert(`Please enter a valid year between 1980 and ${currentYear}`);
                document.getElementById('yearModel').focus();
                return;
              }
            }
          }
          
          // Validate cedula fields if selected
          const cedulaOption = document.querySelector('input[value="CEDULA/CTC"]');
          if (cedulaOption && cedulaOption.checked) {
            const additionalTaxInputs = [
              document.getElementById('grossReceiptsBusiness'),
              document.getElementById('salariesProfession'),
              document.getElementById('incomeRealProperty')
            ];
            
            let totalAdditionalTax = 0;
            additionalTaxInputs.forEach(input => {
              if (input && input.value) {
                totalAdditionalTax += calculateAdditionalTax(parseFloat(input.value));
              }
            });
            
            if (totalAdditionalTax > 5000) {
              alert('Additional Community Tax cannot exceed ₱5,000.00. Please adjust your income amounts.');
              return;
            }
          }
          
          // Validate business application fields if selected
          const selectedCertificateType = document.getElementById('selectedCertificateType');
          if (selectedCertificateType && selectedCertificateType.value === 'BUSINESS APPLICATION') {
            const requiredBusinessFields = [
              { id: 'businessName', name: 'Business Name' },
              { id: 'businessLocation', name: 'Business Location' },
              { id: 'businessOwnerAddress', name: 'Owner Address' },
              { id: 'businessOrNumber', name: 'OR Number' },
              { id: 'businessCtcNumber', name: 'CTC Number' }
            ];
            
            for (const field of requiredBusinessFields) {
              const element = document.getElementById(field.id);
              if (element && !element.value.trim()) {
                alert(`${field.name} is required for business applications.`);
                element.focus();
                return;
              }
            }
          }
          
          console.log('All form validation passed - submitting form');
          
          // All validations passed - submit the form
          formElement.submit();
          
        })
        .catch(error => {
          console.error('Error during eligibility validation:', error);
          // On error, allow submission but log the issue
          alert('Unable to verify eligibility at this time. Your request will be reviewed manually.');
          formElement.submit();
        });
    });
  }
});
</script>

<!-- PHP-based solution as primary backup -->
<?php if ($admin_view && $request_data && $request_data['certificate_type'] === 'TRICYCLE PERMIT'): ?>
<script>
// IMMEDIATE execution for PHP-detected tricycle permits in admin view only
console.log('PHP detected TRICYCLE PERMIT in admin view - forcing section display immediately');

// Force show immediately without waiting
function forceShowTricycleSection() {
  const tricycleSection = document.getElementById('tricycleDetailsSection');
  
  if (tricycleSection) {
    tricycleSection.style.display = 'block';
    console.log('Tricycle section forced to show via PHP detection');
  }
}

// Execute immediately for admin view only
forceShowTricycleSection();
setTimeout(forceShowTricycleSection, 1);
setTimeout(forceShowTricycleSection, 10);
setTimeout(forceShowTricycleSection, 50);
setTimeout(forceShowTricycleSection, 100);
setTimeout(forceShowTricycleSection, 200);

document.addEventListener('DOMContentLoaded', forceShowTricycleSection);
window.addEventListener('load', forceShowTricycleSection);
</script>
<?php endif; ?>

<!-- Enhanced PHP-based solution for cedula -->
<?php if ($admin_view && $request_data && $request_data['certificate_type'] === 'CEDULA/CTC'): ?>
<script>
console.log('PHP detected CEDULA/CTC in admin view - forcing section display immediately');

function forceShowCedulaSection() {
  const cedulaSection = document.getElementById('cedulaDetailsSection');
  
  if (cedulaSection) {
    cedulaSection.style.display = 'block';
    console.log('Cedula section forced to show via PHP detection');
  }
}

// Execute immediately for admin view only
forceShowCedulaSection();
setTimeout(forceShowCedulaSection, 1);
setTimeout(forceShowCedulaSection, 10);
setTimeout(forceShowCedulaSection, 50);
setTimeout(forceShowCedulaSection, 100);

document.addEventListener('DOMContentLoaded', forceShowCedulaSection);
window.addEventListener('load', forceShowCedulaSection);
</script>
<?php endif; ?>

<!-- Enhanced PHP-based solution for business application -->
<?php if ($admin_view && $request_data && $request_data['certificate_type'] === 'BUSINESS APPLICATION'): ?>
<script>
console.log('PHP detected BUSINESS APPLICATION in admin view - forcing section display immediately');

function forceShowBusinessSection() {
  const businessSection = document.getElementById('businessApplicationSection');
  
  if (businessSection) {
    businessSection.style.display = 'block';
    console.log('Business section forced to show via PHP detection');
  }
}

// Execute immediately for admin view only
forceShowBusinessSection();
setTimeout(forceShowBusinessSection, 1);
setTimeout(forceShowBusinessSection, 10);
setTimeout(forceShowBusinessSection, 50);
setTimeout(forceShowBusinessSection, 100);

document.addEventListener('DOMContentLoaded', forceShowBusinessSection);
window.addEventListener('load', forceShowBusinessSection);
</script>
<?php endif; ?>

<!-- Auto-populate user data enhancement script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-populate birthdate to age calculation
    const birthdateField = document.getElementById('birthdate');
    if (birthdateField && birthdateField.value) {
        calculateAge();
    }
    
    // Auto-format mobile number input
    const mobileField = document.getElementById('mobileNumber');
    if (mobileField) {
        mobileField.addEventListener('input', function(e) {
            // Remove non-digits
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Ensure it starts with 9 for Philippine mobile numbers
            if (this.value.length > 0 && this.value[0] !== '9') {
                this.value = '9' + this.value;
            }
        });
    }
    
    // Show notice animation for auto-populated data
    <?php if ($current_user && !$admin_view): ?>
    const noticeElement = document.querySelector('.auto-populated-notice');
    if (noticeElement) {
        // Scroll to notice smoothly after page loads
        setTimeout(function() {
            noticeElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Add a subtle animation to draw attention
            noticeElement.style.transform = 'scale(1.02)';
            noticeElement.style.transition = 'transform 0.3s ease';
            
            setTimeout(function() {
                noticeElement.style.transform = 'scale(1)';
            }, 300);
        }, 500);
    }
    <?php endif; ?>
});
</script>

<!-- Auto-populate user data if logged in -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-populate birthdate to age calculation
    const birthdateField = document.getElementById('birthdate');
    if (birthdateField && birthdateField.value) {
        calculateAge();
    }
    
    // Auto-format mobile number input
    const mobileField = document.getElementById('mobileNumber');
    if (mobileField) {
        mobileField.addEventListener('input', function(e) {
            // Remove non-digits
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Ensure it starts with 9 for Philippine mobile numbers
            if (this.value.length > 0 && this.value[0] !== '9') {
                this.value = '9' + this.value;
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
