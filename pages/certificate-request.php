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

// Determine if tricycle or cedula section should be shown by default
$show_tricycle_section = false;
$show_cedula_section = false;

if ($request_data) {
    if ($request_data['certificate_type'] === 'TRICYCLE PERMIT') {
        $show_tricycle_section = true;
    } elseif ($request_data['certificate_type'] === 'CEDULA/CTC') {
        $show_cedula_section = true;
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
            <input type="radio" name="certificateType" value="TRICYCLE PERMIT" <?php echo ($request_data && $request_data['certificate_type'] === 'TRICYCLE PERMIT') ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>>
            <span class="checkmark"></span>
            TRICYCLE PERMIT
          </label>
          
          <label class="certificate-option">
            <input type="radio" name="certificateType" value="PROOF OF RESIDENCY" <?php echo ($request_data && $request_data['certificate_type'] === 'PROOF OF RESIDENCY') ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>>
            <span class="checkmark"></span>
            PROOF OF RESIDENCY
          </label>
          
          <label class="certificate-option">
            <input type="radio" name="certificateType" value="CEDULA/CTC" <?php echo ($request_data && $request_data['certificate_type'] === 'CEDULA/CTC') ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>>
            <span class="checkmark"></span>
            CEDULA/CTC
          </label>
        </div>
      </fieldset>

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
            <input type="text" id="plateNo" name="plateNo" placeholder="License Plate Number" 
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
                   <?php echo $readonly ? 'readonly' : ''; ?>>
            <small class="input-help">Enter license number using numbers only (e.g., 123456789 or 123-456-789)</small>
          </div>
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
            <label for="address1">Street Address *</label>
            <div class="address-input-container">
              <input type="text" id="address1" name="address1" required 
                     placeholder="Search street address in Barangay Gumaoc East..." 
                     value="<?php echo $request_data ? htmlspecialchars($request_data['address']) : ''; ?>" 
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
                     value="<?php echo $request_data && $request_data['mobile_number'] ? substr($request_data['mobile_number'], 3) : ''; ?>" 
                     <?php echo $readonly ? 'readonly' : ''; ?>>
            </div>
            <small class="input-help">Enter your mobile number without +63 (e.g., 9171234567)</small>
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

<?php if (isset($_SESSION['queue_ticket_number'])): ?>
<div class="queue-info-banner">
    <div class="queue-info-content">
        <h3>🎫 Your Queue Ticket</h3>
        <div class="queue-details">
            <div class="queue-item">
                <strong>Ticket Number:</strong> <?php echo htmlspecialchars($_SESSION['queue_ticket_number']); ?>
            </div>
            <div class="queue-item">
                <strong>Queue Position:</strong> #<?php echo $_SESSION['queue_position']; ?>
            </div>
            <div class="queue-actions">
                <a href="queue-status.php?lookup=1&ticket_number=<?php echo urlencode($_SESSION['queue_ticket_number']); ?>" class="btn btn-primary">
                    📊 Check Queue Status
                </a>
                <a href="queue-ticket.php" class="btn btn-secondary">
                    🎫 Get New Ticket
                </a>
            </div>
        </div>
    </div>
</div>

<style>
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
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 15px;
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
  .certificate-types {
    grid-template-columns: 1fr;
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

// Main function to toggle certificate details based on selection
function toggleCertificateDetails() {
  console.log('toggleCertificateDetails called');
  
  const tricycleOption = document.querySelector('input[value="TRICYCLE PERMIT"]');
  const cedulaOption = document.querySelector('input[value="CEDULA/CTC"]');
  const tricycleSection = document.getElementById('tricycleDetailsSection');
  const cedulaSection = document.getElementById('cedulaDetailsSection');
  
  console.log('Elements found:', {
    tricycleOption: tricycleOption,
    cedulaOption: cedulaOption,
    tricycleSection: tricycleSection,
    cedulaSection: cedulaSection
  });
  
  // Hide all sections first
  if (tricycleSection) {
    tricycleSection.style.display = 'none';
    console.log('Tricycle section hidden');
  }
  if (cedulaSection) {
    cedulaSection.style.display = 'none';
    console.log('Cedula section hidden');
  }
  
  // Show relevant section based on selection
  if (tricycleOption && tricycleOption.checked) {
    console.log('Tricycle permit selected');
    if (tricycleSection) {
      tricycleSection.style.display = 'block';
      console.log('Tricycle section shown');
    }
    setTricycleFieldsRequired(true);
    setCedulaFieldsRequired(false);
  } else if (cedulaOption && cedulaOption.checked) {
    console.log('Cedula/CTC selected');
    if (cedulaSection) {
      cedulaSection.style.display = 'block';
      console.log('Cedula section shown');
    }
    setCedulaFieldsRequired(true);
    setTricycleFieldsRequired(false);
  } else {
    console.log('No relevant certificate type selected');
    setTricycleFieldsRequired(false);
    setCedulaFieldsRequired(false);
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

// Check for pre-selected certificate type
function checkPreSelectedCertificateType() {
  console.log('Checking pre-selected certificate type...');
  
  const checkedRadio = document.querySelector('input[name="certificateType"]:checked');
  console.log('Pre-checked radio found:', checkedRadio?.value);
  
  if (checkedRadio) {
    toggleCertificateDetails();
  } else {
    // Check for existing data to determine which section to show
    const makeTypeInput = document.getElementById('makeType');
    const cedulaYearInput = document.getElementById('cedulaYear');
    
    if (makeTypeInput && makeTypeInput.value) {
      console.log('Found tricycle data, selecting tricycle permit');
      const tricycleRadio = document.querySelector('input[value="TRICYCLE PERMIT"]');
      if (tricycleRadio) {
        tricycleRadio.checked = true;
        toggleCertificateDetails();
      }
    } else if (cedulaYearInput && cedulaYearInput.value) {
      console.log('Found cedula data, selecting cedula/ctc');
      const cedulaRadio = document.querySelector('input[value="CEDULA/CTC"]');
      if (cedulaRadio) {
        cedulaRadio.checked = true;
        toggleCertificateDetails();
      }
    }
  }
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
  
  // Add event listeners for certificate type changes
  const certificateRadios = document.querySelectorAll('input[name="certificateType"]');
  console.log('Found certificate radios:', certificateRadios.length);
  
  certificateRadios.forEach((radio) => {
    radio.addEventListener('change', function() {
      console.log('Certificate type changed to:', this.value);
      toggleCertificateDetails();
    });
  });
  
  // Initialize address search
  initializeAddressSearch();
  
  // Check for pre-selected certificate type
  checkPreSelectedCertificateType();
  
  // Initialize tax calculations if cedula is selected
  updateBasicTax();
  updateTaxCalculations();
  
  console.log('Initialization complete');
});

// Enhanced form validation
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('certificateForm');
  if (form) {
    form.addEventListener('submit', function(e) {
      console.log('Form submission started');
      
      // Mobile number validation
      const mobileInput = document.getElementById('mobileNumber');
      if (mobileInput && mobileInput.value) {
        const mobilePattern = /^9[0-9]{9}$/;
        if (!mobilePattern.test(mobileInput.value)) {
          e.preventDefault();
          alert('Please enter a valid Philippine mobile number starting with 9 (10 digits total)');
          mobileInput.focus();
          return;
        }
        
        const fullNumber = '+63' + mobileInput.value;
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'full_mobile_number';
        hiddenInput.value = fullNumber;
        this.appendChild(hiddenInput);
      }
      
      // Validate tricycle permit fields if selected
      const tricycleOption = document.querySelector('input[value="TRICYCLE PERMIT"]');
      if (tricycleOption && tricycleOption.checked) {
        const yearModel = document.getElementById('yearModel').value;
        
        if (yearModel) {
          if (!/^\d+$/.test(yearModel)) {
            e.preventDefault();
            alert('Year Model should contain only numbers');
            document.getElementById('yearModel').focus();
            return;
          }
          
          const year = parseInt(yearModel);
          const currentYear = new Date().getFullYear();
          
          if (isNaN(year) || year < 1980 || year > currentYear) {
            e.preventDefault();
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
          e.preventDefault();
          alert('Additional Community Tax cannot exceed ₱5,000.00. Please adjust your income amounts.');
          return;
        }
      }
      
      console.log('Form validation passed');
    });
  }
});
</script>

<!-- PHP-based solution as primary backup -->
<?php if ($request_data && $request_data['certificate_type'] === 'TRICYCLE PERMIT'): ?>
<script>
// IMMEDIATE execution for PHP-detected tricycle permits
console.log('PHP detected TRICYCLE PERMIT - forcing section display immediately');

// Force show immediately without waiting
function forceShowTricycleSection() {
  const tricycleSection = document.getElementById('tricycleDetailsSection');
  const tricycleRadio = document.querySelector('input[name="certificateType"][value="TRICYCLE PERMIT"]');
  
  if (tricycleSection) {
    tricycleSection.style.display = 'block';
    console.log('Tricycle section forced to show via PHP detection');
  }
  
  if (tricycleRadio) {
    tricycleRadio.checked = true;
    console.log('Tricycle radio checked via PHP detection');
  }
}

// Execute immediately
forceShowTricycleSection();

// Also execute after short delays
setTimeout(forceShowTricycleSection, 1);
setTimeout(forceShowTricycleSection, 10);
setTimeout(forceShowTricycleSection, 50);
setTimeout(forceShowTricycleSection, 100);
setTimeout(forceShowTricycleSection, 200);

// Execute when DOM is ready
document.addEventListener('DOMContentLoaded', forceShowTricycleSection);

// Execute when window is loaded
window.addEventListener('load', forceShowTricycleSection);
</script>
<?php endif; ?>

<!-- Enhanced PHP-based solution for cedula -->
<?php if ($request_data && $request_data['certificate_type'] === 'CEDULA/CTC'): ?>
<script>
console.log('PHP detected CEDULA/CTC - forcing section display immediately');

function forceShowCedulaSection() {
  const cedulaSection = document.getElementById('cedulaDetailsSection');
  const cedulaRadio = document.querySelector('input[name="certificateType"][value="CEDULA/CTC"]');
  
  if (cedulaSection) {
    cedulaSection.style.display = 'block';
    console.log('Cedula section forced to show via PHP detection');
  }
  
  if (cedulaRadio) {
    cedulaRadio.checked = true;
    console.log('Cedula radio checked via PHP detection');
  }
}

forceShowCedulaSection();
setTimeout(forceShowCedulaSection, 1);
setTimeout(forceShowCedulaSection, 10);
setTimeout(forceShowCedulaSection, 50);
setTimeout(forceShowCedulaSection, 100);

document.addEventListener('DOMContentLoaded', forceShowCedulaSection);
window.addEventListener('load', forceShowCedulaSection);
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
