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
    
    // Fetch related family members and other data...
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
?>

<?php if ($admin_view && $readonly): ?>
<!-- ULTRA-ROBUST ADMIN VISIBILITY ENHANCEMENTS -->
<style>
/* =============================================================================
   ADMIN-ONLY ENHANCED VISIBILITY - ULTIMATE SOLUTION
   ============================================================================= */

/* TAB 3 LIVELIHOOD - SUPER ENHANCED RADIO/CHECKBOX VISIBILITY */
#tab-content-3 .subsection {
  background: rgba(244, 254, 247, 0.95) !important;
  border: 3px solid #28a745 !important;
  border-radius: 15px !important;
  padding: 2rem !important;
  margin: 2rem 0 !important;
  box-shadow: 0 8px 25px rgba(40, 167, 69, 0.15) !important;
}

#tab-content-3 .subsection h4 {
  background: linear-gradient(135deg, #28a745, #20c997) !important;
  color: #ffffff !important;
  padding: 12px 20px !important;
  border-radius: 8px !important;
  margin: 0 0 1.5rem 0 !important;
  font-weight: 700 !important;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3) !important;
}

#tab-content-3 .checkbox-group {
  display: grid !important;
  gap: 1.2rem !important;
  padding: 1.5rem !important;
  background: rgba(255, 255, 255, 0.95) !important;
  border-radius: 12px !important;
  border: 2px solid rgba(40, 167, 69, 0.4) !important;
  box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.05) !important;
}

/* FORCE HIDE ALL RADIO AND CHECKBOX INPUTS */
body #tab-content-3 input[type="radio"],
body #tab-content-3 input[type="checkbox"] {
  position: absolute !important;
  opacity: 0 !important;
  width: 1px !important;
  height: 1px !important;
  overflow: hidden !important;
  clip: rect(0, 0, 0, 0) !important;
  pointer-events: none !important;
}

/* FORCE LABEL STYLING - UNSELECTED STATE */
body #tab-content-3 .checkbox-group label {
  display: block !important;
  position: relative !important;
  padding: 18px 25px 18px 65px !important;
  margin: 0 !important;
  border-radius: 10px !important;
  font-size: 1.15rem !important;
  font-weight: 600 !important;
  cursor: pointer !important;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
  background: #f8f9fa !important;
  border: 3px solid #e9ecef !important;
  color: #6c757d !important;
  opacity: 0.7 !important;
  min-height: 65px !important;
  display: flex !important;
  align-items: center !important;
  text-align: left !important;
  line-height: 1.4 !important;
  user-select: none !important;
  box-sizing: border-box !important;
}

/* FORCE HOVER EFFECTS */
body #tab-content-3 .checkbox-group label:hover {
  background: #e8f5e8 !important;
  border-color: #28a745 !important;
  color: #155724 !important;
  opacity: 0.9 !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2) !important;
}

/* FORCE CHECKED RADIO BUTTONS - MAXIMUM VISIBILITY */
body #tab-content-3 input[type="radio"]:checked + label,
body #tab-content-3 input[type="radio"]:disabled:checked + label {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
  color: #ffffff !important;
  font-weight: 800 !important;
  border: 4px solid #155724 !important;
  box-shadow: 0 12px 35px rgba(40, 167, 69, 0.8) !important;
  opacity: 1 !important;
  transform: scale(1.08) !important;
  text-shadow: 0 2px 6px rgba(0, 0, 0, 0.4) !important;
  z-index: 5 !important;
  position: relative !important;
}

/* FORCE CHECKED CHECKBOXES - MAXIMUM VISIBILITY WITH GREEN */
body #tab-content-3 input[type="checkbox"]:checked + label,
body #tab-content-3 input[type="checkbox"]:disabled:checked + label {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
  color: #ffffff !important;
  font-weight: 800 !important;
  border: 4px solid #155724 !important;
  box-shadow: 0 12px 35px rgba(40, 167, 69, 0.8) !important;
  opacity: 1 !important;
  transform: scale(1.08) !important;
  text-shadow: 0 2px 6px rgba(0, 0, 0, 0.4) !important;
  z-index: 5 !important;
  position: relative !important;
}

/* FORCE MASSIVE VISUAL CHECKMARKS FOR SELECTED OPTIONS */
body #tab-content-3 input[type="radio"]:checked + label::before,
body #tab-content-3 input[type="radio"]:disabled:checked + label::before {
  content: '✓' !important;
  position: absolute !important;
  left: 18px !important;
  top: 50% !important;
  transform: translateY(-50%) !important;
  width: 38px !important;
  height: 38px !important;
  background: #ffffff !important;
  color: #28a745 !important;
  border-radius: 50% !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  font-size: 22px !important;
  font-weight: 900 !important;
  border: 4px solid #155724 !important;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3) !important;
  z-index: 10 !important;
}

body #tab-content-3 input[type="checkbox"]:checked + label::before,
body #tab-content-3 input[type="checkbox"]:disabled:checked + label::before {
  content: '✓' !important;
  position: absolute !important;
  left: 18px !important;
  top: 50% !important;
  transform: translateY(-50%) !important;
  width: 34px !important;
  height: 34px !important;
  background: #ffffff !important;
  color: #28a745 !important;
  border-radius: 8px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  font-size: 20px !important;
  font-weight: 900 !important;
  border: 4px solid #155724 !important;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3) !important;
  z-index: 10 !important;
}

/* FORCE ADDITIONAL VISUAL INDICATOR FOR SELECTED STATE */
body #tab-content-3 input[type="radio"]:checked + label::after,
body #tab-content-3 input[type="checkbox"]:checked + label::after,
body #tab-content-3 input[type="radio"]:disabled:checked + label::after,
body #tab-content-3 input[type="checkbox"]:disabled:checked + label::after {
  content: 'SELECTED' !important;
  position: absolute !important;
  top: 8px !important;
  right: 15px !important;
  background: rgba(255, 255, 255, 0.95) !important;
  color: #155724 !important;
  padding: 4px 8px !important;
  border-radius: 4px !important;
  font-size: 0.7rem !important;
  font-weight: 700 !important;
  text-transform: uppercase !important;
  letter-spacing: 0.5px !important;
  border: 1px solid rgba(255, 255, 255, 0.6) !important;
  text-shadow: none !important;
}

/* DISABLE ANY CONFLICTING STYLES */
body #tab-content-3 .checkbox-group label * {
  pointer-events: none !important;
}

body #tab-content-3 .checkbox-group label {
  pointer-events: auto !important;
}

/* TAB 2 FAMILY TABLE - ULTRA ENHANCED SCROLLING */
#tab-content-2 .family-section {
  background: rgba(248, 249, 250, 0.9) !important;
  border: 2px solid #17a2b8 !important;
  border-radius: 12px !important;
  padding: 1rem !important;
}

#tab-content-2 .table-wrapper {
  overflow: visible !important;
  border-radius: 8px !important;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
}

#tab-content-2 .table-responsive {
  overflow-x: auto !important;
  overflow-y: auto !important;
  max-height: 400px !important;
  border-radius: 8px !important;
  border: 2px solid #17a2b8 !important;
}

#tab-content-2 .family-table {
  width: 100% !important;
  min-width: 1400px !important;
  border-collapse: separate !important;
  border-spacing: 0 !important;
  background: #ffffff !important;
  table-layout: fixed !important;
}

/* FIXED COLUMN WIDTHS TO PREVENT OVERLAP */
#tab-content-2 .family-table th:nth-child(1),
#tab-content-2 .family-table td:nth-child(1) {
  width: 250px !important;
  min-width: 250px !important;
}

#tab-content-2 .family-table th:nth-child(2),
#tab-content-2 .family-table td:nth-child(2) {
  width: 180px !important;
  min-width: 180px !important;
}

#tab-content-2 .family-table th:nth-child(3),
#tab-content-2 .family-table td:nth-child(3) {
  width: 100px !important;
  min-width: 100px !important;
}

#tab-content-2 .family-table th:nth-child(4),
#tab-content-2 .family-table td:nth-child(4) {
  width: 150px !important;
  min-width: 150px !important;
}

#tab-content-2 .family-table th:nth-child(5),
#tab-content-2 .family-table td:nth-child(5) {
  width: 150px !important;
  min-width: 150px !important;
}

#tab-content-2 .family-table th:nth-child(6),
#tab-content-2 .family-table td:nth-child(6) {
  width: 220px !important;
  min-width: 220px !important;
}

#tab-content-2 .family-table th:nth-child(7),
#tab-content-2 .family-table td:nth-child(7) {
  width: 180px !important;
  min-width: 180px !important;
}

#tab-content-2 .family-table th {
  background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
  color: #ffffff !important;
  font-weight: 700 !important;
  padding: 16px 8px !important;
  text-align: center !important;
  font-size: 0.9rem !important;
  border: none !important;
  position: sticky !important;
  top: 0 !important;
  z-index: 10 !important;
}

#tab-content-2 .family-table td {
  padding: 8px 4px !important;
  vertical-align: middle !important;
  border-bottom: 1px solid #e9ecef !important;
  background: #ffffff !important;
}

/* ULTRA-ENHANCED TABLE INPUTS WITH PROPER COLUMN FITTING */
#tab-content-2 .table-input {
  width: calc(100% - 8px) !important;
  padding: 8px 6px !important;
  border: 2px solid #28a745 !important;
  border-radius: 4px !important;
  background: #ffffff !important;
  color: #155724 !important;
  font-weight: 600 !important;
  font-size: 0.9rem !important;
  box-sizing: border-box !important;
  
  /* FORCED HORIZONTAL SCROLLING */
  overflow-x: auto !important;
  overflow-y: hidden !important;
  white-space: nowrap !important;
  text-overflow: clip !important;
  
  /* CUSTOM SCROLLBAR STYLING */
  scrollbar-width: thin !important;
  scrollbar-color: #28a745 rgba(40, 167, 69, 0.2) !important;
}

/* SPECIAL HANDLING FOR NAME FIELDS */
#tab-content-2 .scrollable-name-input {
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
  border: 3px solid #007bff !important;
  color: #004085 !important;
  font-weight: 700 !important;
}

#tab-content-2 .table-input::-webkit-scrollbar {
  height: 6px !important;
  width: 6px !important;
}

#tab-content-2 .table-input::-webkit-scrollbar-track {
  background: rgba(40, 167, 69, 0.1) !important;
  border-radius: 3px !important;
}

#tab-content-2 .table-input::-webkit-scrollbar-thumb {
  background: #28a745 !important;
  border-radius: 3px !important;
}

#tab-content-2 .table-input::-webkit-scrollbar-thumb:hover {
  background: #20c997 !important;
}

/* SPECIAL HANDLING FOR NAME FIELDS */
#tab-content-2 .scrollable-name-input {
  min-width: 200px !important;
  max-width: 300px !important;
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
  border: 3px solid #007bff !important;
  color: #004085 !important;
}

/* SELECT DROPDOWNS ENHANCED */
#tab-content-2 select.table-input {
  cursor: pointer !important;
  appearance: menulist !important;
  background-image: none !important;
}

/* READONLY/DISABLED STATES */
#tab-content-2 input[readonly],
#tab-content-2 select[disabled] {
  background: rgba(248, 249, 250, 0.8) !important;
  border-color: #6c757d !important;
  color: #495057 !important;
  cursor: default !important;
}

/* RESPONSIVE TABLE SCROLLING */
#tab-content-2 .table-responsive::-webkit-scrollbar {
  width: 8px !important;
  height: 8px !important;
}

#tab-content-2 .table-responsive::-webkit-scrollbar-track {
  background: rgba(23, 162, 184, 0.1) !important;
  border-radius: 4px !important;
}

#tab-content-2 .table-responsive::-webkit-scrollbar-thumb {
  background: #17a2b8 !important;
  border-radius: 4px !important;
}

#tab-content-2 .table-responsive::-webkit-scrollbar-corner {
  background: rgba(23, 162, 184, 0.1) !important;
}
</style>

<script>
// Pass PHP data to JavaScript for direct database-driven styling
window.LIVELIHOOD_DATA = <?php echo json_encode([
  'land_ownership' => $registration_data['land_ownership'] ?? null,
  'land_ownership_other' => $registration_data['land_ownership_other'] ?? null,
  'house_ownership' => $registration_data['house_ownership'] ?? null,
  'house_ownership_other' => $registration_data['house_ownership_other'] ?? null,
  'farmland' => $registration_data['farmland'] ?? null,
  'cooking_energy' => $registration_data['cooking_energy'] ?? null,
  'cooking_energy_other' => $registration_data['cooking_energy_other'] ?? null,
  'toilet_type' => $registration_data['toilet_type'] ?? null,
  'toilet_type_other' => $registration_data['toilet_type_other'] ?? null,
  'electricity_source' => $registration_data['electricity_source'] ?? null,
  'electricity_source_other' => $registration_data['electricity_source_other'] ?? null,
  'water_source' => $registration_data['water_source'] ?? null,
  'water_source_other' => $registration_data['water_source_other'] ?? null,
  'waste_disposal' => $registration_data['waste_disposal'] ?? null,
  'waste_disposal_other' => $registration_data['waste_disposal_other'] ?? null,
  'appliances' => $registration_data['appliances'] ?? null,
  'transportation' => $registration_data['transportation'] ?? null,
  'transportation_other' => $registration_data['transportation_other'] ?? null,
  'business' => $registration_data['business'] ?? null,
  'business_other' => $registration_data['business_other'] ?? null,
  'contraceptive' => $registration_data['contraceptive'] ?? null
]); ?>;

document.addEventListener('DOMContentLoaded', function() {
  // Force readonly mode styling
  var form = document.querySelector('.registration-form');
  if (form) {
    form.classList.add('admin-readonly-enhanced');
  }
  
  // DATABASE-DRIVEN TAB 3 LIVELIHOOD VISIBILITY FUNCTION
  function applyDatabaseDrivenStyling() {
    // Define section mappings: database_field -> {form_name, input_type}
    var sectionMappings = {
      'land_ownership': { formName: 'landOwnership', type: 'radio', otherField: 'land_ownership_other' },
      'house_ownership': { formName: 'houseOwnership', type: 'radio', otherField: 'house_ownership_other' },
      'farmland': { formName: 'farmland', type: 'radio', otherField: null },
      'cooking_energy': { formName: 'cookingEnergy', type: 'radio', otherField: 'cooking_energy_other' },
      'toilet_type': { formName: 'toiletType', type: 'radio', otherField: 'toilet_type_other' },
      'electricity_source': { formName: 'electricitySource', type: 'radio', otherField: 'electricity_source_other' },
      'water_source': { formName: 'waterSource', type: 'radio', otherField: 'water_source_other' },
      'waste_disposal': { formName: 'wasteDisposal', type: 'radio', otherField: 'waste_disposal_other' },
      'appliances': { formName: 'appliances', type: 'checkbox', otherField: null },
      'transportation': { formName: 'transportation', type: 'checkbox', otherField: 'transportation_other' },
      'business': { formName: 'business', type: 'checkbox', otherField: 'business_other' },
      'contraceptive': { formName: 'contraceptive', type: 'checkbox', otherField: null }
    };
    
    // Process each section
    Object.keys(sectionMappings).forEach(function(dbField) {
      var mapping = sectionMappings[dbField];
      var dbValue = window.LIVELIHOOD_DATA[dbField];
      var otherValue = mapping.otherField ? window.LIVELIHOOD_DATA[mapping.otherField] : null;
      
      if (!dbValue) {
        return;
      }
      
      // Get form inputs
      var inputSelector = mapping.type === 'checkbox' 
        ? `input[name="${mapping.formName}[]"]`
        : `input[name="${mapping.formName}"]`;
      var inputs = document.querySelectorAll(inputSelector);
      var otherInput = document.getElementById(mapping.formName + 'Other');
      
      if (inputs.length === 0) {
        return;
      }
      
      // Handle checkbox arrays (comma-separated values)
      var selectedValues = [];
      if (mapping.type === 'checkbox' && dbValue && dbValue.includes(',')) {
        selectedValues = dbValue.split(',').map(v => v.trim());
      } else if (dbValue) {
        selectedValues = [dbValue];
      }
      
      // Apply styling to each selected value
      inputs.forEach(function(input, index) {
        var label = input.nextElementSibling || input.parentNode.querySelector('label');
        if (!label && input.parentNode.tagName === 'LABEL') {
          label = input.parentNode;
        }
        
        if (!label) {
          return;
        }
        
        // Reset label styling
        resetLabelStyling(label);
        
        // Check if this input should be highlighted
        var shouldHighlight = selectedValues.includes(input.value);
        
        if (shouldHighlight) {
          // Apply selected styling - ALL GREEN for Tab 3
          var isRadio = mapping.type === 'radio';
          var selectedColor = '#28a745';  // Green for both radio and checkbox
          var selectedBorder = '#155724'; // Dark green for both radio and checkbox
          var selectedGradient = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)'; // Green gradient for both
          
          // Apply enhanced styling
          applySelectedStyling(label, selectedColor, selectedBorder, selectedGradient, isRadio);
          
          // Handle "Iba pa" special case - ENHANCED
          if (input.value === 'Iba pa') {
            // Always highlight the text input when "Iba pa" is selected, even if empty
            if (otherInput) {
              highlightOtherInput(otherInput, selectedColor, selectedBorder, isRadio);
              
              // Also make the text input more visible by ensuring it's enabled/visible
              otherInput.style.display = 'block';
              otherInput.disabled = false;
              
              // Add a special "Other" label indicator if the text input has content
              if (otherValue && otherValue.trim()) {
                var otherLabel = document.createElement('div');
                otherLabel.className = 'admin-other-label';
                otherLabel.style.cssText = `
                  position: absolute;
                  top: -8px;
                  left: 8px;
                  background: ${selectedColor};
                  color: white;
                  padding: 2px 8px;
                  border-radius: 4px;
                  font-size: 0.7rem;
                  font-weight: 700;
                  z-index: 15;
                `;
                otherLabel.textContent = 'OTHER';
                
                // Remove existing label if any
                var existingOtherLabel = otherInput.parentNode.querySelector('.admin-other-label');
                if (existingOtherLabel) existingOtherLabel.remove();
                
                if (otherInput.parentNode.style.position !== 'relative') {
                  otherInput.parentNode.style.position = 'relative';
                }
                otherInput.parentNode.appendChild(otherLabel);
              }
            }
          }
        } else {
          // Reset "Iba pa" text input if this was an "Iba pa" option that's not selected
          if (input.value === 'Iba pa' && otherInput) {
            resetOtherInput(otherInput);
          }
        }
      });
    });
  }
  
  // Helper function to reset label styling - ENHANCED
  function resetLabelStyling(label) {
    // Clear existing admin styling
    var existingCheckmark = label.querySelector('.admin-checkmark');
    var existingBadge = label.querySelector('.admin-selected-badge');
    if (existingCheckmark) existingCheckmark.remove();
    if (existingBadge) existingBadge.remove();
    
    // Apply default styling
    label.style.cssText = `
      display: block !important;
      position: relative !important;
      padding: 18px 25px 18px 65px !important;
      margin: 0 !important;
      border-radius: 10px !important;
      font-size: 1.15rem !important;
      font-weight: 600 !important;
      cursor: pointer !important;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
      background: #f8f9fa !important;
      border: 3px solid #e9ecef !important;
      color: #6c757d !important;
      opacity: 0.7 !important;
      min-height: 65px !important;
      display: flex !important;
      align-items: center !important;
      text-align: left !important;
      line-height: 1.4 !important;
      user-select: none !important;
      box-sizing: border-box !important;
    `;
  }
  
  // Helper function to reset "other" text input styling
  function resetOtherInput(otherInput) {
    if (!otherInput) return;
    
    // Remove special indicators
    var textIndicator = otherInput.parentNode.querySelector('.text-input-indicator');
    var otherLabel = otherInput.parentNode.querySelector('.admin-other-label');
    if (textIndicator) textIndicator.remove();
    if (otherLabel) otherLabel.remove();
    
    // Reset to default styling
    otherInput.style.cssText = `
      border: 2px solid #e9ecef !important;
      background: #f8f9fa !important;
      color: #6c757d !important;
      font-weight: 500 !important;
      padding: 8px 12px !important;
      border-radius: 4px !important;
      box-shadow: none !important;
      margin-top: 8px !important;
      font-size: 0.95rem !important;
      transform: none !important;
      opacity: 0.8 !important;
    `;
    
    // Remove event listeners by cloning the element
    var newOtherInput = otherInput.cloneNode(true);
    otherInput.parentNode.replaceChild(newOtherInput, otherInput);
  }
  
  // Helper function to apply selected styling
  function applySelectedStyling(label, selectedColor, selectedBorder, selectedGradient, isRadio) {
    label.style.cssText = `
      display: block !important;
      position: relative !important;
      padding: 16px 20px 16px 60px !important;
      margin: 6px 0 !important;
      border-radius: 8px !important;
      font-size: 1.1rem !important;
      font-weight: 800 !important;
      cursor: pointer !important;
      transition: all 0.3s ease !important;
      background: ${selectedGradient} !important;
      border: 3px solid ${selectedBorder} !important;
      color: #ffffff !important;
      opacity: 1 !important;
      min-height: 60px !important;
      align-items: center !important;
      text-align: left !important;
      line-height: 1.4 !important;
      user-select: none !important;
      box-sizing: border-box !important;
      box-shadow: 0 8px 25px rgba(40, 167, 69, 0.6) !important;
      transform: scale(1.05) !important;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3) !important;
      z-index: 5 !important;
    `;
    
    // Add checkmark
    var checkmark = document.createElement('div');
    checkmark.className = 'admin-checkmark';
    checkmark.style.cssText = `
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      width: ${isRadio ? '35px' : '32px'};
      height: ${isRadio ? '35px' : '32px'};
      background: #ffffff;
      color: ${selectedColor};
      border-radius: ${isRadio ? '50%' : '6px'};
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: ${isRadio ? '20px' : '18px'};
      font-weight: 900;
      border: 3px solid ${selectedBorder};
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      z-index: 10;
    `;
    checkmark.textContent = '✓';
    label.appendChild(checkmark);
    
    // Add selected badge
    var badge = document.createElement('div');
    badge.className = 'admin-selected-badge';
    badge.style.cssText = `
      position: absolute;
      top: 6px;
      right: 12px;
      background: rgba(255, 255, 255, 0.95);
      color: ${selectedBorder};
      padding: 3px 6px;
      border-radius: 3px;
      font-size: 0.65rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border: 1px solid rgba(255, 255, 255, 0.6);
      text-shadow: none;
    `;
    badge.textContent = 'SELECTED';
    label.appendChild(badge);
  }
  
  // Helper function to highlight "other" text inputs - ENHANCED
  function highlightOtherInput(otherInput, selectedColor, selectedBorder, isRadio) {
    console.log('🎨 Applying enhanced "Iba pa" text input styling');
    
    // Enhanced styling for "Iba pa" text inputs
    otherInput.style.cssText = `
      border: 4px solid ${selectedColor} !important;
      background: linear-gradient(135deg, rgba(40, 167, 69, 0.15), rgba(32, 201, 151, 0.1)) !important;
      color: ${selectedBorder} !important;
      font-weight: 800 !important;
      padding: 15px 45px 15px 15px !important;
      border-radius: 8px !important;
      box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4) !important;
      margin-top: 12px !important;
      font-size: 1.05rem !important;
      transition: all 0.3s ease !important;
      transform: scale(1.02) !important;
      min-height: 50px !important;
    `;
    
    // Add indicator to text input
    var textIndicator = otherInput.parentNode.querySelector('.text-input-indicator');
    if (!textIndicator) {
      textIndicator = document.createElement('div');
      textIndicator.className = 'text-input-indicator';
      textIndicator.style.cssText = `
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: ${selectedColor};
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 900;
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        z-index: 10;
      `;
      textIndicator.textContent = '✓';
      
      if (otherInput.parentNode.style.position !== 'relative') {
        otherInput.parentNode.style.position = 'relative';
      }
      otherInput.parentNode.appendChild(textIndicator);
    }
    
    // Add glow effect on focus
    otherInput.addEventListener('focus', function() {
      this.style.boxShadow = `0 0 0 4px rgba(40, 167, 69, 0.25), 0 6px 20px rgba(40, 167, 69, 0.4) !important`;
    });
    
    otherInput.addEventListener('blur', function() {
      this.style.boxShadow = `0 6px 20px rgba(40, 167, 69, 0.4) !important`;
    });
  }
  
  // Apply styling immediately and with delays for robustness
  applyDatabaseDrivenStyling();
  setTimeout(applyDatabaseDrivenStyling, 200);
  setTimeout(applyDatabaseDrivenStyling, 600);
  setTimeout(applyDatabaseDrivenStyling, 1200);
  
  // Force table input scrolling behavior for Tab 2
  var tableInputs = document.querySelectorAll('#tab-content-2 .table-input');
  tableInputs.forEach(function(input) {
    input.addEventListener('focus', function() {
      this.style.borderColor = '#007bff';
      this.style.boxShadow = '0 0 0 3px rgba(0, 123, 255, 0.25)';
    });
    
    input.addEventListener('blur', function() {
      this.style.borderColor = '#28a745';
      this.style.boxShadow = 'none';
    });
  });
});
</script>
<?php endif; ?>

<!-- Enhanced Data Privacy Act Notice -->
<div id="dataPrivacyOverlay" class="privacy-overlay">
  <div id="dataPrivacyModal" class="privacy-modal">
    <div class="privacy-header">
      <div class="privacy-icon">🛡️</div>
      <h3>Data Privacy Notice</h3>
      <button class="privacy-close" onclick="closePrivacyNotice()" aria-label="Close notice" title="Close Notice">&times;</button>
    </div>
    
    <div class="privacy-content">
      <div class="privacy-section">
        <h4>🇵🇭 Republic Act No. 10173 - Data Privacy Act of 2012</h4>
        <p><strong>Your Personal Information is Protected Under Philippine Law</strong></p>
      </div>
      
      <div class="privacy-section">
        <h5>📋 Census Data Collection Notice</h5>
        <p>This census form collects personal information for legitimate government purposes under the authority of the Local Government Unit of <strong>Barangay Gumaoc East, San Jose Del Monte City, Bulacan</strong>.</p>
      </div>
      
      <div class="privacy-section">
        <h5>🎯 Purpose of Data Collection</h5>
        <ul>
          <li><strong>Population and demographic planning</strong> for better resource allocation</li>
          <li><strong>Public service delivery improvement</strong> and program development</li>
          <li><strong>Emergency response and disaster preparedness</strong> planning</li>
          <li><strong>Statistical analysis for policy making</strong> and governance</li>
          <li><strong>Community development programs</strong> and social services</li>
        </ul>
      </div>
      
      <div class="privacy-section">
        <h5>🔒 Your Rights Under the Data Privacy Act</h5>
        <ul>
          <li><strong>Right to Information</strong> - You have the right to know how your data is collected, used, and processed</li>
          <li><strong>Right to Access</strong> - You can request access to your personal data we have collected</li>
          <li><strong>Right to Correction</strong> - You can request correction of inaccurate or incomplete data</li>
          <li><strong>Right to Erasure</strong> - You can request deletion of your data under certain legal conditions</li>
          <li><strong>Right to Data Portability</strong> - You can request a copy of your data in a commonly used format</li>
          <li><strong>Right to Object</strong> - You can object to certain types of data processing</li>
        </ul>
      </div>
      
      <div class="privacy-section">
        <h5>🛡️ Data Protection Measures</h5>
        <ul>
          <li>Information is stored securely with restricted access to authorized personnel only</li>
          <li>Data will not be shared with third parties without your explicit consent or legal requirement</li>
          <li>Personal information will be retained only as long as necessary for the stated purposes</li>
          <li>Appropriate technical and organizational security measures are implemented</li>
          <li>Regular security audits and data protection assessments are conducted</li>
        </ul>
      </div>
      
      <div class="privacy-section">
        <h5>⚖️ Legal Basis for Processing</h5>
        <p>The collection and processing of your personal data is based on:</p>
        <ul>
          <li><strong>Legal Obligation</strong> - LGU mandate for population census and planning</li>
          <li><strong>Public Interest</strong> - Delivery of public services and governance</li>
          <li><strong>Legitimate Interest</strong> - Community development and public welfare</li>
        </ul>
      </div>
      
      <div class="privacy-section">
        <h5>📞 Contact Information & Complaints</h5>
        <p><strong>Data Protection Officer:</strong><br>
        Barangay Gumaoc East<br>
        San Jose Del Monte City, Bulacan<br>
        📧 Email: gumaoceast@sjdm.gov.ph<br>
        📱 Phone: (044) 123-4567<br>
        🏢 Office Hours: Monday-Friday, 8:00 AM - 5:00 PM</p>
        
        <p><strong>For DPA Complaints:</strong><br>
        National Privacy Commission<br>
        📧 Email: info@privacy.gov.ph<br>
        📱 Hotline: (02) 8234-2228</p>
      </div>
      
      <div class="privacy-consent">
        <p><strong>⚠️ IMPORTANT CONSENT NOTICE</strong></p>
        <p>By proceeding with this census form, you acknowledge that you have <strong>read and understood</strong> this Data Privacy Notice and <strong>voluntarily consent</strong> to the collection and processing of your personal information for the stated legitimate purposes.</p>
      </div>
    </div>
    
    <div class="privacy-footer">
      <div class="privacy-timer">
        <span id="privacyTimer">⏱️ This notice will auto-close in <strong>120</strong> seconds</span>
      </div>
      <div class="privacy-actions">
        <button class="btn-privacy-accept" onclick="closePrivacyNotice()">
          ✅ I Understand and Agree to Proceed
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Success and Error Modals - Positioned at Top -->
<?php if (isset($_SESSION['error'])): ?>
  <!-- Error Modal -->
  <div class="modal-overlay" id="errorModal">
    <div class="modal-content modal-error">
      <div class="modal-header">
        <h4>❌ Error</h4>
        <button class="modal-close" onclick="closeModal('errorModal')">&times;</button>
      </div>
      <div class="modal-body">
        <p><?php echo htmlspecialchars($_SESSION['error']); ?></p>
      </div>
    </div>
  </div>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
  <!-- Success Modal -->
  <div class="modal-overlay" id="successModal">
    <div class="modal-content modal-success">
      <div class="modal-header">
        <h4>✅ Registration Successful!</h4>
        <button class="modal-close" onclick="closeModal('successModal')">&times;</button>
      </div>
      <div class="modal-body">
        <p><?php echo htmlspecialchars($_SESSION['success']); ?></p>
      </div>
      <div class="modal-footer">
        <div class="auto-close-timer">
          <span>This window will automatically close in <span id="countdown">120</span> seconds</span>
        </div>
      </div>
    </div>
  </div>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- Main Content Container -->
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
    
    <form id="censusForm" class="registration-form" method="POST" action="process-census.php">
      
      <!-- Tab Navigation -->
      <div class="tab-navigation">
        <button type="button" class="tab-btn active" onclick="showTab(1)" id="tab-1" data-step="1">1. Basic Information<br><small>Pangunahing Impormasyon</small></button>
        <button type="button" class="tab-btn" onclick="showTab(2)" id="tab-2" data-step="2">2. Family Members<br><small>Mga Kasapi ng Pamilya</small></button>
        <button type="button" class="tab-btn" onclick="showTab(3)" id="tab-3" data-step="3">3. Livelihood<br><small>Pangkabuhayan</small></button>
        <button type="button" class="tab-btn" onclick="showTab(4)" id="tab-4" data-step="4">4. Disabilities<br><small>Mga Kasambahay na may Kapansanan</small></button>
        <button type="button" class="tab-btn" onclick="showTab(5)" id="tab-5" data-step="5">5. Organizations<br><small>Mga Kasambahay na may Samahang Kinaaniban</small></button>
      </div>
      
      <!-- Progress Indicator -->
      <div class="progress-indicator">
        <div class="progress-bar">
          <div class="progress-fill" id="progressFill"></div>
        </div>
        <div class="progress-text" id="progressText">Step 1 of 5</div>
      </div>
      
      <!-- Tab Content Container -->
      <div class="tab-content-container">
        <!-- Tab 1: Basic Information -->
        <div class="tab-content active" id="tab-content-1">
          <fieldset>
            <legend>Basic Information<br><small>Pangunahing Impormasyon</small></legend>
        
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
          <label>Head of Family Name *<br><small>Pangalan ng Puno ng Pamilya *</small></label>
          <div class="name-fields-group">
            <div class="name-field">
              <input type="text" id="firstName" name="firstName" required placeholder="First Name / Pangalan" 
                     value="<?php echo $registration_data ? htmlspecialchars($registration_data['first_name']) : ''; ?>"
                     <?php echo $readonly ? 'readonly' : ''; ?>>
              <small>First Name</small>
            </div>
            <div class="name-field">
              <input type="text" id="middleName" name="middleName" placeholder="Middle Name / Gitnang Pangalan (Optional)"
                     value="<?php echo $registration_data ? htmlspecialchars($registration_data['middle_name']) : ''; ?>"
                     <?php echo $readonly ? 'readonly' : ''; ?>>
              <small>Middle Name (Optional)</small>
            </div>
            <div class="name-field">
              <input type="text" id="lastName" name="lastName" required placeholder="Last Name / Apelyido"
                     value="<?php echo $registration_data ? htmlspecialchars($registration_data['last_name']) : ''; ?>"
                     <?php echo $readonly ? 'readonly' : ''; ?>>
              <small>Last Name</small>
            </div>
          </div>
          <div id="nameValidation" class="validation-message"></div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="cellphone">Cellphone Number<br><small>Numero ng Cellphone</small></label>
            <input type="tel" id="cellphone" name="cellphone" placeholder="09XXXXXXXXX" pattern="[0-9]{11}" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['contact_number']) : ''; ?>"
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="email">Email Address<br><small>Email Address</small></label>
            <input type="email" id="email" name="email" placeholder="example@email.com"
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['email']) : ''; ?>"
                   <?php echo $readonly ? 'readonly' : ''; ?>>
            <div id="emailValidation" class="validation-message"></div>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="birthday">Date of Birth *<br><small>Petsa ng Kapanganakan *</small></label>
            <input type="date" id="birthday" name="birthday" required
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['birth_date']) : ''; ?>"
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="birthPlace">Place of Birth *<br><small>Lugar ng Kapanganakan *</small></label>
            <input type="text" id="birthPlace" name="birthPlace" required placeholder="City, Province, Country"
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['birth_place'] ?? '') : ''; ?>"
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="houseNumber">House Number *<br><small>Numero ng Bahay *</small></label>
            <input type="text" id="houseNumber" name="houseNumber" required placeholder="Numero ng bahay" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['house_number']) : ''; ?>"
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="interviewer">Interviewer Name *<br><small>Pangalan ng Nakapanayam *</small></label>
            <input type="text" id="interviewer" name="interviewer" required placeholder="Buong pangalan ng nakapanayam"
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['interviewer']) : ''; ?>"
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>

          <div class="form-group">
            <label for="interviewerTitle">Interviewer Position *<br><small>Taga-panayam *</small></label>
            <input type="text" id="interviewerTitle" name="interviewerTitle" required placeholder="Posisyon/Tungkulin"
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['interviewer_title']) : ''; ?>"
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>
          </fieldset>
        </div>
        
        <!-- Tab 2: Family Members -->
        <div class="tab-content" id="tab-content-2">
          <fieldset>
            <legend>Family Members<br><small>Mga Kasapi ng Pamilya</small></legend>
            
        <div class="family-section">
          <div class="table-wrapper">
            <div class="table-responsive">
              <table class="family-table">
                <thead>
                  <tr>
                    <th>Name<br><small>Pangalan</small></th>
                    <th>Relationship to Head<br><small>Relasyon sa Puno</small></th>
                    <th>Age<br><small>Edad</small></th>
                    <th>Gender<br><small>Kasarian</small></th>
                    <th>Civil Status<br><small>Katayuang Sibil</small></th>
                    <th>Email Address<br><small>Email Address</small></th>
                    <th>Occupation<br><small>Hanapbuhay</small></th>
                    <?php if (!$readonly): ?>
                    <th>Action<br><small>Aksyon</small></th>
                    <?php endif; ?>
                  </tr>
                </thead>
                <tbody id="familyMembersBody">
                <?php if (!empty($family_members)): ?>
                  <?php foreach ($family_members as $index => $member): ?>
                    <tr class="family-member-row">
                      <td data-label="Name" class="scrollable-name-cell"><input type="text" name="familyName[]" class="table-input scrollable-name-input" placeholder="Pangalan" value="<?php echo htmlspecialchars($member['full_name']); ?>" <?php echo $readonly ? 'readonly' : ''; ?>></td>
                      <td data-label="Relationship">
                        <select name="familyRelation[]" class="table-input" <?php echo $readonly ? 'disabled' : ''; ?>>
                          <option value="">Piliin ang Relasyon</option>
                          <option value="Asawa" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Asawa') ? 'selected' : ''; ?>>Asawa (Spouse)</option>
                          <option value="Anak" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Anak') ? 'selected' : ''; ?>>Anak (Child)</option>
                          <option value="Ama" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Ama') ? 'selected' : ''; ?>>Ama (Father)</option>
                          <option value="Ina" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Ina') ? 'selected' : ''; ?>>Ina (Mother)</option>
                          <option value="Kapatid" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Kapatid') ? 'selected' : ''; ?>>Kapatid (Sibling)</option>
                          <option value="Lolo" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Lolo') ? 'selected' : ''; ?>>Lolo (Grandfather)</option>
                          <option value="Lola" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Lola') ? 'selected' : ''; ?>>Lola (Grandmother)</option>
                          <option value="Apo" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Apo') ? 'selected' : ''; ?>>Apo (Grandchild)</option>
                          <option value="Tiyahin" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Tiyahin') ? 'selected' : ''; ?>>Tiyahin (Aunt)</option>
                          <option value="Tiyuhin" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Tiyuhin') ? 'selected' : ''; ?>>Tiyuhin (Uncle)</option>
                          <option value="Pamangkin" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Pamangkin') ? 'selected' : ''; ?>>Pamangkin (Nephew/Niece)</option>
                          <option value="Pinsan" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Pinsan') ? 'selected' : ''; ?>>Pinsan (Cousin)</option>
                          <option value="Manugang" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Manugang') ? 'selected' : ''; ?>>Manugang (Son/Daughter-in-law)</option>
                          <option value="Biyenan" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Biyenan') ? 'selected' : ''; ?>>Biyenan (Parent-in-law)</option>
                          <option value="Ninong" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Ninong') ? 'selected' : ''; ?>>Ninong (Godfather)</option>
                          <option value="Ninang" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Ninang') ? 'selected' : ''; ?>>Ninang (Godmother)</option>
                          <option value="Inaanak" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Inaanak') ? 'selected' : ''; ?>>Inaanak (Godchild)</option>
                          <option value="Kasambahay" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Kasambahay') ? 'selected' : ''; ?>>Kasambahay (Helper)</option>
                          <option value="Boarder" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Boarder') ? 'selected' : ''; ?>>Boarder</option>
                          <option value="Iba pa" <?php echo (isset($member['relationship']) && $member['relationship'] === 'Iba pa') ? 'selected' : ''; ?>>Iba pa (Others)</option>
                        </select>
                      </td>
                      <td data-label="Age"><input type="number" name="familyAge[]" class="table-input" placeholder="Edad" min="0" max="120" value="<?php echo $member['age']; ?>" <?php echo $readonly ? 'readonly' : ''; ?>></td>
                      <td data-label="Gender">
                        <select name="familyGender[]" class="table-input" <?php echo $readonly ? 'disabled' : ''; ?>>
                          <option value="">Piliin</option>
                          <option value="Male" <?php echo (isset($member['gender']) && ($member['gender'] === 'Male' || $member['gender'] === 'Lalaki')) ? 'selected' : ''; ?>>Lalaki (Male)</option>
                          <option value="Female" <?php echo (isset($member['gender']) && ($member['gender'] === 'Female' || $member['gender'] === 'Babae')) ? 'selected' : ''; ?>>Babae (Female)</option>
                        </select>
                      </td>
                      <td data-label="Civil Status">
                        <select name="familyCivilStatus[]" class="table-input" <?php echo $readonly ? 'disabled' : ''; ?>>
                          <option value="">Piliin</option>
                          <option value="Single" <?php echo (isset($member['civil_status']) && $member['civil_status'] === 'Single') ? 'selected' : ''; ?>>Single</option>
                          <option value="Married" <?php echo (isset($member['civil_status']) && $member['civil_status'] === 'Married') ? 'selected' : ''; ?>>Married</option>
                          <option value="Widowed" <?php echo (isset($member['civil_status']) && ($member['civil_status'] === 'Widowed' || $member['civil_status'] === 'Widow')) ? 'selected' : ''; ?>>Widowed</option>
                          <option value="Separated" <?php echo (isset($member['civil_status']) && $member['civil_status'] === 'Separated') ? 'selected' : ''; ?>>Separated</option>
                        </select>
                      </td>
                      <td data-label="Email"><input type="email" name="familyEmail[]" class="table-input" placeholder="email@example.com" value="<?php echo isset($member['email']) ? htmlspecialchars($member['email']) : ''; ?>" <?php echo $readonly ? 'readonly' : ''; ?>></td>
                      <td data-label="Occupation"><input type="text" name="familyOccupation[]" class="table-input" placeholder="Hanapbuhay" value="<?php echo isset($member['occupation']) ? htmlspecialchars($member['occupation']) : ''; ?>" <?php echo $readonly ? 'readonly' : ''; ?>></td>
                      <?php if (!$readonly): ?>
                      <td data-label="Action"><button type="button" class="btn-remove-row" onclick="removeFamilyMember(this)" title="Remove this family member">✕</button></td>
                      <?php endif; ?>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr class="family-member-row">
                    <td data-label="Name" class="scrollable-name-cell"><input type="text" name="familyName[]" class="table-input scrollable-name-input" placeholder="Pangalan" <?php echo $readonly ? 'readonly' : ''; ?>></td>
                    <td data-label="Relationship">
                      <select name="familyRelation[]" class="table-input" <?php echo $readonly ? 'disabled' : ''; ?>>
                        <option value="">Piliin ang Relasyon</option>
                        <option value="Asawa">Asawa (Spouse)</option>
                        <option value="Anak">Anak (Child)</option>
                        <option value="Ama">Ama (Father)</option>
                        <option value="Ina">Ina (Mother)</option>
                        <option value="Kapatid">Kapatid (Sibling)</option>
                        <option value="Lolo">Lolo (Grandfather)</option>
                        <option value="Lola">Lola (Grandmother)</option>
                        <option value="Apo">Apo (Grandchild)</option>
                        <option value="Tiyahin">Tiyahin (Aunt)</option>
                        <option value="Tiyuhin">Tiyuhin (Uncle)</option>
                        <option value="Pamangkin">Pamangkin (Nephew/Niece)</option>
                        <option value="Pinsan">Pinsan (Cousin)</option>
                        <option value="Manugang">Manugang (Son/Daughter-in-law)</option>
                        <option value="Biyenan">Biyenan (Parent-in-law)</option>
                        <option value="Ninong">Ninong (Godfather)</option>
                        <option value="Ninang">Ninang (Godmother)</option>
                        <option value="Inaanak">Inaanak (Godchild)</option>
                        <option value="Kasambahay">Kasambahay (Helper)</option>
                        <option value="Boarder">Boarder</option>
                        <option value="Iba pa">Iba pa (Others)</option>
                      </select>
                    </td>
                    <td data-label="Age"><input type="number" name="familyAge[]" class="table-input" placeholder="Edad" min="0" max="120" <?php echo $readonly ? 'readonly' : ''; ?>></td>
                    <td data-label="Gender">
                      <select name="familyGender[]" class="table-input" <?php echo $readonly ? 'disabled' : ''; ?>>
                        <option value="">Piliin</option>
                        <option value="Male">Lalaki (Male)</option>
                        <option value="Female">Babae (Female)</option>
                      </select>
                    </td>
                    <td data-label="Civil Status">
                      <select name="familyCivilStatus[]" class="table-input" <?php echo $readonly ? 'disabled' : ''; ?>>
                        <option value="">Piliin</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Widowed">Widowed</option>
                        <option value="Separated">Separated</option>
                      </select>
                    </td>
                    <td data-label="Email"><input type="email" name="familyEmail[]" class="table-input" placeholder="email@example.com" <?php echo $readonly ? 'readonly' : ''; ?>></td>
                    <td data-label="Occupation"><input type="text" name="familyOccupation[]" class="table-input" placeholder="Hanapbuhay" <?php echo $readonly ? 'readonly' : ''; ?>></td>
                    <?php if (!$readonly): ?>
                    <td data-label="Action"><button type="button" class="btn-remove-row" onclick="removeFamilyMember(this)" title="Remove this family member">✕</button></td>
                    <?php endif; ?>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
        
        <?php if (!$readonly): ?>
        <div class="family-controls">
          <button type="button" class="btn-add-family-member" onclick="addFamilyMember()">
            <span class="btn-icon">➕</span>
            <span class="btn-text">Add Family Member<br><small>Magdagdag ng Kasapi</small></span>
          </button>
        </div>
        <?php endif; ?>
        </div>
          </fieldset>
        </div>
        
        <!-- Tab 3: Livelihood -->
        <div class="tab-content" id="tab-content-3">
          <fieldset>
            <legend>I. Livelihood<br><small>I. Pangkabuhayan</small></legend>
        
        <div class="subsection">
          <h4>A. Land Occupied<br><small>A. Lupang Kinatatayuan</small></h4>
          <div class="checkbox-group">
            <label><input type="radio" name="landOwnership" value="Pag-aari" <?php echo ($registration_data && $registration_data['land_ownership'] === 'Pag-aari') ? 'checked' : ''; ?> onchange="toggleOtherInput('landOwnership', 'landOwnershipOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Pag-aari</label>
            <label><input type="radio" name="landOwnership" value="Inuupahan" <?php echo ($registration_data && $registration_data['land_ownership'] === 'Inuupahan') ? 'checked' : ''; ?> onchange="toggleOtherInput('landOwnership', 'landOwnershipOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Inuupahan</label>
            <label><input type="radio" name="landOwnership" value="Iba pa" <?php echo ($registration_data && $registration_data['land_ownership'] === 'Iba pa') ? 'checked' : ''; ?> onchange="toggleOtherInput('landOwnership', 'landOwnershipOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Iba pa</label>
            <input type="text" name="landOwnershipOther" id="landOwnershipOther" placeholder="Pakisulat kung iba pa" class="other-input" 
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['land_ownership_other'] ?? '') : ''; ?>"
                   <?php echo ($registration_data && $registration_data['land_ownership'] !== 'Iba pa') ? 'disabled' : ''; ?>
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="subsection">
          <h4>B. House Residence<br><small>B. Bahay na Tinitirhan</small></h4>
          <div class="checkbox-group">
            <label><input type="radio" name="houseOwnership" value="Pag-aari" <?php echo ($registration_data && $registration_data['house_ownership'] === 'Pag-aari') ? 'checked' : ''; ?> onchange="toggleOtherInput('houseOwnership', 'houseOwnershipOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Pag-aari</label>
            <label><input type="radio" name="houseOwnership" value="Umuupa" <?php echo ($registration_data && $registration_data['house_ownership'] === 'Umuupa') ? 'checked' : ''; ?> onchange="toggleOtherInput('houseOwnership', 'houseOwnershipOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Umuupa</label>
            <label><input type="radio" name="houseOwnership" value="Iba pa" <?php echo ($registration_data && $registration_data['house_ownership'] === 'Iba pa') ? 'checked' : ''; ?> onchange="toggleOtherInput('houseOwnership', 'houseOwnershipOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Iba pa</label>
            <input type="text" name="houseOwnershipOther" id="houseOwnershipOther" placeholder="Pakisulat kung iba pa" class="other-input"
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['house_ownership_other'] ?? '') : ''; ?>"
                   <?php echo ($registration_data && $registration_data['house_ownership'] !== 'Iba pa') ? 'disabled' : ''; ?>
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="subsection">
          <h4>C. Agricultural/Farm Land<br><small>C. Lupahang Sakahan/Pinagyayaman</small></h4>
          <div class="checkbox-group">
            <label><input type="radio" name="farmland" value="Pag-aari" <?php echo ($registration_data && $registration_data['farmland'] === 'Pag-aari') ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Pag-aari</label>
            <label><input type="radio" name="farmland" value="Binubuwisan" <?php echo ($registration_data && $registration_data['farmland'] === 'Binubuwisan') ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Binubuwisan</label>
            <label><input type="radio" name="farmland" value="Wala" <?php echo ($registration_data && $registration_data['farmland'] === 'Wala') ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Wala</label>
          </div>
        </div>

        <div class="subsection">
          <h4>D. Source of Cooking Energy<br><small>D. Pinagmumulan ng Enerhiya sa Pagluluto</small></h4>
          <div class="checkbox-group">
            <label><input type="radio" name="cookingEnergy" value="Gaas" <?php echo ($registration_data && $registration_data['cooking_energy'] === 'Gaas') ? 'checked' : ''; ?> onchange="toggleOtherInput('cookingEnergy', 'cookingEnergyOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Gaas</label>
            <label><input type="radio" name="cookingEnergy" value="Kuryente" <?php echo ($registration_data && $registration_data['cooking_energy'] === 'Kuryente') ? 'checked' : ''; ?> onchange="toggleOtherInput('cookingEnergy', 'cookingEnergyOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Kuryente</label>
            <label><input type="radio" name="cookingEnergy" value="LPG" <?php echo ($registration_data && $registration_data['cooking_energy'] === 'LPG') ? 'checked' : ''; ?> onchange="toggleOtherInput('cookingEnergy', 'cookingEnergyOther')" <?php echo $readonly ? 'disabled' : ''; ?>> LPG</label>
            <label><input type="radio" name="cookingEnergy" value="Kahoy" <?php echo ($registration_data && $registration_data['cooking_energy'] === 'Kahoy') ? 'checked' : ''; ?> onchange="toggleOtherInput('cookingEnergy', 'cookingEnergyOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Kahoy</label>
            <label><input type="radio" name="cookingEnergy" value="Iba pa" <?php echo ($registration_data && $registration_data['cooking_energy'] === 'Iba pa') ? 'checked' : ''; ?> onchange="toggleOtherInput('cookingEnergy', 'cookingEnergyOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Iba pa</label>
            <input type="text" name="cookingEnergyOther" id="cookingEnergyOther" placeholder="Pakisulat kung iba pa" class="other-input"
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['cooking_energy_other'] ?? '') : ''; ?>"
                   <?php echo ($registration_data && $registration_data['cooking_energy'] !== 'Iba pa') ? 'disabled' : ''; ?>
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="subsection">
          <h4>E. Type of Toilet<br><small>E. Uri ng Palikuran</small></h4>
          <div class="checkbox-group">
            <label><input type="radio" name="toiletType" value="Flush" <?php echo ($registration_data && $registration_data['toilet_type'] === 'Flush') ? 'checked' : ''; ?> onchange="toggleOtherInput('toiletType', 'toiletTypeOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Flush</label>
            <label><input type="radio" name="toiletType" value="De-buhos" <?php echo ($registration_data && $registration_data['toilet_type'] === 'De-buhos') ? 'checked' : ''; ?> onchange="toggleOtherInput('toiletType', 'toiletTypeOther')" <?php echo $readonly ? 'disabled' : ''; ?>> De-buhos</label>
            <label><input type="radio" name="toiletType" value="Hinuhukay/Balon" <?php echo ($registration_data && $registration_data['toilet_type'] === 'Hinuhukay/Balon') ? 'checked' : ''; ?> onchange="toggleOtherInput('toiletType', 'toiletTypeOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Hinuhukay/Balon</label>
            <label><input type="radio" name="toiletType" value="Iba pa" <?php echo ($registration_data && $registration_data['toilet_type'] === 'Iba pa') ? 'checked' : ''; ?> onchange="toggleOtherInput('toiletType', 'toiletTypeOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Iba pa</label>
            <input type="text" name="toiletTypeOther" id="toiletTypeOther" placeholder="Pakisulat kung iba pa" class="other-input"
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['toilet_type_other'] ?? '') : ''; ?>"
                   <?php echo ($registration_data && $registration_data['toilet_type'] !== 'Iba pa') ? 'disabled' : ''; ?>
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="subsection">
          <h4>F. Source of Electricity<br><small>F. Pinagmululan ng Elektrisidad</small></h4>
          <div class="checkbox-group">
            <label><input type="radio" name="electricitySource" value="Kuryente" <?php echo ($registration_data && $registration_data['electricity_source'] === 'Kuryente') ? 'checked' : ''; ?> onchange="toggleOtherInput('electricitySource', 'electricitySourceOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Kuryente</label>
            <label><input type="radio" name="electricitySource" value="Gaas" <?php echo ($registration_data && $registration_data['electricity_source'] === 'Gaas') ? 'checked' : ''; ?> onchange="toggleOtherInput('electricitySource', 'electricitySourceOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Gaas</label>
            <label><input type="radio" name="electricitySource" value="Iba pa" <?php echo ($registration_data && $registration_data['electricity_source'] === 'Iba pa') ? 'checked' : ''; ?> onchange="toggleOtherInput('electricitySource', 'electricitySourceOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Iba pa</label>
            <input type="text" name="electricitySourceOther" id="electricitySourceOther" placeholder="Pakisulat kung iba pa" class="other-input"
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['electricity_source_other'] ?? '') : ''; ?>"
                   <?php echo ($registration_data && $registration_data['electricity_source'] !== 'Iba pa') ? 'disabled' : ''; ?>
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="subsection">
          <h4>G. Source of Water<br><small>G. Pinagkukunan ng Tubig</small></h4>
          <div class="checkbox-group">
            <label><input type="radio" name="waterSource" value="Poso Artesiyano" <?php echo ($registration_data && $registration_data['water_source'] === 'Poso Artesiyano') ? 'checked' : ''; ?> onchange="toggleOtherInput('waterSource', 'waterSourceOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Poso Artesiyano</label>
            <label><input type="radio" name="waterSource" value="Water District" <?php echo ($registration_data && $registration_data['water_source'] === 'Water District') ? 'checked' : ''; ?> onchange="toggleOtherInput('waterSource', 'waterSourceOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Water District</label>
            <label><input type="radio" name="waterSource" value="Nawasa" <?php echo ($registration_data && $registration_data['water_source'] === 'Nawasa') ? 'checked' : ''; ?> onchange="toggleOtherInput('waterSource', 'waterSourceOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Nawasa</label>
            <label><input type="radio" name="waterSource" value="Iba pa" <?php echo ($registration_data && $registration_data['water_source'] === 'Iba pa') ? 'checked' : ''; ?> onchange="toggleOtherInput('waterSource', 'waterSourceOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Iba pa</label>
            <input type="text" name="waterSourceOther" id="waterSourceOther" placeholder="Pakisulat kung iba pa" class="other-input"
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['water_source_other'] ?? '') : ''; ?>"
                   <?php echo ($registration_data && $registration_data['water_source'] !== 'Iba pa') ? 'disabled' : ''; ?>
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="subsection">
          <h4>H. Method of Waste Disposal<br><small>H. Pamamaraan ng Pagtatapon ng Basura</small></h4>
          <div class="checkbox-group">
            <label><input type="radio" name="wasteDisposal" value="Sinusunog" <?php echo ($registration_data && $registration_data['waste_disposal'] === 'Sinusunog') ? 'checked' : ''; ?> onchange="toggleOtherInput('wasteDisposal', 'wasteDisposalOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Sinusunog</label>
            <label><input type="radio" name="wasteDisposal" value="Hukay na may takip" <?php echo ($registration_data && $registration_data['waste_disposal'] === 'Hukay na may takip') ? 'checked' : ''; ?> onchange="toggleOtherInput('wasteDisposal', 'wasteDisposalOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Hukay na may takip</label>
            <label><input type="radio" name="wasteDisposal" value="Kinokolekta" <?php echo ($registration_data && $registration_data['waste_disposal'] === 'Kinokolekta') ? 'checked' : ''; ?> onchange="toggleOtherInput('wasteDisposal', 'wasteDisposalOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Kinokolekta</label>
            <label><input type="radio" name="wasteDisposal" value="Itinatapon kung saan" <?php echo ($registration_data && $registration_data['waste_disposal'] === 'Itinatapon kung saan') ? 'checked' : ''; ?> onchange="toggleOtherInput('wasteDisposal', 'wasteDisposalOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Itinatapon kung saan</label>
            <label><input type="radio" name="wasteDisposal" value="Iba pa" <?php echo ($registration_data && $registration_data['waste_disposal'] === 'Iba pa') ? 'checked' : ''; ?> onchange="toggleOtherInput('wasteDisposal', 'wasteDisposalOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Iba pa</label>
            <input type="text" name="wasteDisposalOther" id="wasteDisposalOther" placeholder="Pakisulat kung iba pa" class="other-input"
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['waste_disposal_other'] ?? '') : ''; ?>"
                   <?php echo ($registration_data && $registration_data['waste_disposal'] !== 'Iba pa') ? 'disabled' : ''; ?>
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="subsection">
          <h4>I. Household Appliances<br><small>I. Kasangkapan sa Bahay</small></h4>
          <div class="checkbox-group">
            <?php 
            $appliances_array = $registration_data ? explode(',', $registration_data['appliances']) : [];
            ?>
            <label><input type="checkbox" name="appliances[]" value="Radyo/Stereo" <?php echo in_array('Radyo/Stereo', $appliances_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Radyo/Stereo</label>
            <label><input type="checkbox" name="appliances[]" value="Telebisyon" <?php echo in_array('Telebisyon', $appliances_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Telebisyon (TV)</label>
            <label><input type="checkbox" name="appliances[]" value="Refrigerator" <?php echo in_array('Refrigerator', $appliances_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Refrigerator</label>
            <label><input type="checkbox" name="appliances[]" value="Muwebles" <?php echo in_array('Muwebles', $appliances_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Muwebles (Furniture)</label>
          </div>
        </div>

        <div class="subsection">
          <h4>J. Transportation<br><small>J. Transportasyon</small></h4>
          <div class="checkbox-group">
            <?php 
            $transportation_array = $registration_data ? explode(',', $registration_data['transportation']) : [];
            ?>
            <label><input type="checkbox" name="transportation[]" value="Sasakyan" <?php echo in_array('Sasakyan', $transportation_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Sasakyan</label>
            <label><input type="checkbox" name="transportation[]" value="Jeep" <?php echo in_array('Jeep', $transportation_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Jeep</label>
            <label><input type="checkbox" name="transportation[]" value="Kotse" <?php echo in_array('Kotse', $transportation_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Kotse</label>
            <label><input type="checkbox" name="transportation[]" value="Tricycle" <?php echo in_array('Tricycle', $transportation_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Tricycle</label>
            <label><input type="checkbox" name="transportation[]" value="Truck" <?php echo in_array('Truck', $transportation_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Truck</label>
            <label><input type="checkbox" name="transportation[]" value="Motorsiklo" <?php echo in_array('Motorsiklo', $transportation_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Motorsiklo</label>
            <label><input type="checkbox" name="transportation[]" value="Iba pa" <?php echo in_array('Iba pa', $transportation_array) ? 'checked' : ''; ?> onchange="toggleCheckboxOther('transportation', 'transportationOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Iba pa</label>
            <input type="text" name="transportationOther" id="transportationOther" placeholder="Pakisulat kung iba pa" class="other-input"
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['transportation_other'] ?? '') : ''; ?>"
                   <?php echo (!in_array('Iba pa', $transportation_array)) ? 'disabled' : ''; ?>
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="subsection">
          <h4>K. Commercial/Other Sources of Income<br><small>K. Pang-Komersyo/Iba pang Pinagkakakitaan</small></h4>
          <div class="checkbox-group">
            <?php 
            $business_array = $registration_data ? explode(',', $registration_data['business']) : [];
            ?>
            <label><input type="checkbox" name="business[]" value="Sari-Sari Store" <?php echo in_array('Sari-Sari Store', $business_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Sari-Sari Store</label>
            <label><input type="checkbox" name="business[]" value="Patahian" <?php echo in_array('Patahian', $business_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Patahian</label>
            <label><input type="checkbox" name="business[]" value="Rice Mill" <?php echo in_array('Rice Mill', $business_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Rice Mill</label>
            <label><input type="checkbox" name="business[]" value="Iba pa" <?php echo in_array('Iba pa', $business_array) ? 'checked' : ''; ?> onchange="toggleCheckboxOther('business', 'businessOther')" <?php echo $readonly ? 'disabled' : ''; ?>> Iba pa</label>
            <input type="text" name="businessOther" id="businessOther" placeholder="Pakisulat kung iba pa" class="other-input"
                   value="<?php echo $registration_data ? htmlspecialchars($registration_data['business_other'] ?? '') : ''; ?>"
                   <?php echo (!in_array('Iba pa', $business_array)) ? 'disabled' : ''; ?>
                   <?php echo $readonly ? 'readonly' : ''; ?>>
          </div>
        </div>

        <div class="subsection">
          <h4>L. Contraceptive Methods Used<br><small>L. Gamit na Kontraseptibo</small></h4>
          <div class="checkbox-group">
            <?php 
            $contraceptive_array = $registration_data ? explode(',', $registration_data['contraceptive']) : [];
            ?>
            <label><input type="checkbox" name="contraceptive[]" value="Pills" <?php echo in_array('Pills', $contraceptive_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Pills</label>
            <label><input type="checkbox" name="contraceptive[]" value="IUD" <?php echo in_array('IUD', $contraceptive_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> IUD</label>
            <label><input type="checkbox" name="contraceptive[]" value="Condom" <?php echo in_array('Condom', $contraceptive_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Condom</label>
            <label><input type="checkbox" name="contraceptive[]" value="Sterilization" <?php echo in_array('Sterilization', $contraceptive_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Sterilization (Ligation/VAS)</label>
            <label><input type="checkbox" name="contraceptive[]" value="INJ" <?php echo in_array('INJ', $contraceptive_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> INJ</label>
            <label><input type="checkbox" name="contraceptive[]" value="NFP" <?php echo in_array('NFP', $contraceptive_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> NFP (Natural F.P.)</label>
            <label><input type="checkbox" name="contraceptive[]" value="Wala" <?php echo in_array('Wala', $contraceptive_array) ? 'checked' : ''; ?> <?php echo $readonly ? 'disabled' : ''; ?>> Wala</label>
          </div>
        </div>
          </fieldset>
        </div>
        
        <!-- Tab 4: Disabilities -->
        <div class="tab-content" id="tab-content-4">
          <fieldset>
            <legend>II. Family Members with Disabilities<br><small>II. Mga Kasambahay na may Kapansanan</small></legend>
            
        <div class="disability-section">
          <div class="disability-wrapper">
            <div class="disability-container" id="disabilitySection">
              <?php if (!empty($family_disabilities)): ?>
                <?php foreach ($family_disabilities as $disability): ?>
                  <div class="disability-row">
                    <div class="form-group">
                      <label>Name:<br><small>Pangalan:</small></label>
                      <input type="text" name="disabilityName[]" class="form-control" placeholder="Buong pangalan" 
                             value="<?php echo isset($disability['name']) ? htmlspecialchars($disability['name']) : ''; ?>" <?php echo $readonly ? 'readonly' : ''; ?>>
                    </div>
                    <div class="form-group">
                      <label>Disability:<br><small>Kapansanan:</small></label>
                      <input type="text" name="disabilityType[]" class="form-control" placeholder="Uri ng kapansanan"
                             value="<?php echo isset($disability['disability_type']) ? htmlspecialchars($disability['disability_type']) : ''; ?>" <?php echo $readonly ? 'readonly' : ''; ?>>
                    </div>
                    <?php if (!$readonly): ?>
                    <div class="form-group remove-group">
                      <button type="button" class="btn-remove-disability" onclick="removeDisability(this)" title="Remove this disability entry">✕</button>
                    </div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="disability-row">
                  <div class="form-group">
                    <label>Name:<br><small>Pangalan:</small></label>
                    <input type="text" name="disabilityName[]" class="form-control" placeholder="Buong pangalan" <?php echo $readonly ? 'readonly' : ''; ?>>
                  </div>
                  <div class="form-group">
                    <label>Disability:<br><small>Kapansanan:</small></label>
                    <input type="text" name="disabilityType[]" class="form-control" placeholder="Uri ng kapansanan" <?php echo $readonly ? 'readonly' : ''; ?>>
                  </div>
                  <?php if (!$readonly): ?>
                  <div class="form-group remove-group">
                    <button type="button" class="btn-remove-disability" onclick="removeDisability(this)" title="Remove this disability entry">✕</button>
                  </div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
          
          <?php if (!$readonly): ?>
          <div class="disability-controls">
            <button type="button" class="btn-add-disability" onclick="addDisability()">
              <span class="btn-icon">➕</span>
              <span class="btn-text">Add Disability Entry<br><small>Magdagdag ng Kapansanan</small></span>
            </button>
          </div>
          <?php endif; ?>
        </div>
          </fieldset>
        </div>
        
        <!-- Tab 5: Organizations -->
        <div class="tab-content" id="tab-content-5">
          <fieldset>
            <legend>III. Family Members with Organizational Membership<br><small>III. Mga Kasambahay na may Samahang Kinaaniban</small></legend>
            
        <div class="organization-section">
          <div class="organization-wrapper">
            <div class="organization-container" id="organizationSection">
              <?php if (!empty($family_organizations)): ?>
                <?php foreach ($family_organizations as $organization): ?>
                  <div class="organization-row">
                    <div class="form-group">
                      <label>Name:<br><small>Pangalan:</small></label>
                      <input type="text" name="organizationName[]" class="form-control" placeholder="Buong pangalan"
                             value="<?php echo isset($organization['name']) ? htmlspecialchars($organization['name']) : ''; ?>" <?php echo $readonly ? 'readonly' : ''; ?>>
                    </div>
                    <div class="form-group">
                      <label>Organization Membership:<br><small>Samahang Kinaaniban:</small></label>
                      <input type="text" name="organizationType[]" class="form-control" placeholder="Pangalan ng samahan/organisasyon"
                             value="<?php echo isset($organization['organization_type']) ? htmlspecialchars($organization['organization_type']) : ''; ?>" <?php echo $readonly ? 'readonly' : ''; ?>>
                    </div>
                    <?php if (!$readonly): ?>
                    <div class="form-group remove-group">
                      <button type="button" class="btn-remove-organization" onclick="removeOrganization(this)" title="Remove this organization entry">✕</button>
                    </div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="organization-row">
                  <div class="form-group">
                    <label>Name:<br><small>Pangalan:</small></label>
                    <input type="text" name="organizationName[]" class="form-control" placeholder="Buong pangalan" <?php echo $readonly ? 'readonly' : ''; ?>>
                  </div>
                  <div class="form-group">
                    <label>Organization Membership:<br><small>Samahang Kinaaniban:</small></label>
                    <input type="text" name="organizationType[]" class="form-control" placeholder="Pangalan ng samahan/organisasyon" <?php echo $readonly ? 'readonly' : ''; ?>>
                  </div>
                  <?php if (!$readonly): ?>
                  <div class="form-group remove-group">
                    <button type="button" class="btn-remove-organization" onclick="removeOrganization(this)" title="Remove this organization entry">✕</button>
                  </div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
          
          <?php if (!$readonly): ?>
          <div class="organization-controls">
            <button type="button" class="btn-add-organization" onclick="addOrganization()">
              <span class="btn-icon">➕</span>
              <span class="btn-text">Add Organization Entry<br><small>Magdagdag ng Samahan</small></span>
            </button>
          </div>
          <?php endif; ?>
        </div>
      </fieldset>
      
      <!-- Tab Navigation Controls -->
      <?php if (!$readonly): ?>
      <div class="tab-navigation-controls">
        <button type="button" class="btn btn-secondary" id="prevBtn" onclick="changeTab(-1)" style="display: none;">← Previous</button>
        <button type="button" class="btn btn-primary" id="nextBtn" onclick="changeTab(1)">Next →</button>
      </div>
      <?php else: ?>
      <div class="readonly-notice">
        <div class="readonly-badge">
          <span class="readonly-icon">👁️</span>
          <span class="readonly-text">Read-Only View Mode<br><small>Viewing Submitted Registration Data</small></span>
        </div>
      </div>
      <?php endif; ?>
      </div>

      <!-- Submit buttons - Always visible for normal forms, tab navigation controls visibility -->
      <div class="form-actions" id="mainFormActions">
        <?php if (!$readonly): ?>
          <div class="submit-instructions">
            <p><small>📝 Complete all required fields and navigate through all tabs, then click Submit<br>
            <em>Kumpletuhin ang lahat ng kinakailangang field at mag-navigate sa lahat ng tab, pagkatapos ay i-click ang Submit</em></small></p>
          </div>
          <button type="submit" class="btn btn-primary" id="hiddenSubmitBtn">Submit Census Form<br><small>I-submit ang Census Form</small></button>
          <button type="reset" class="btn">Clear Form<br><small>I-clear ang Form</small></button>
        <?php else: ?>
          <a href="../admin/view-resident-registrations.php" class="btn btn-secondary">← Back to Admin Dashboard</a>
          <button type="button" class="btn btn-primary" onclick="window.print()">🖨️ Print Form</button>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<style>
/* Enhanced Data Privacy Notice Styles */
.privacy-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(15px);
  z-index: 10000;
  display: none;
  align-items: flex-start;
  justify-content: center;
  opacity: 0;
  visibility: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0.0, 0.2, 1);
  padding: 20px;
  overflow-y: auto;
  padding-top: 50px;
}

.privacy-overlay.show {
  display: flex;
  opacity: 1;
  visibility: visible;
}

.privacy-modal {
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  border-radius: 24px;
  box-shadow: 0 30px 90px rgba(0, 0, 0, 0.4);
  max-width: 900px;
  width: 100%;
  max-height: calc(100vh - 100px);
  overflow: hidden;
  position: relative;
  border: 3px solid rgba(27, 94, 32, 0.2);
  transform: scale(0.8) translateY(50px);
  transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
  animation: modalSlideIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.privacy-overlay.show .privacy-modal {
  transform: scale(1) translateY(0);
}

@keyframes modalSlideIn {
  0% {
    transform: scale(0.7) translateY(100px) rotateX(10deg);
    opacity: 0;
  }
  100% {
    transform: scale(1) translateY(0) rotateX(0deg);
    opacity: 1;
  }
}

.privacy-header {
  background: linear-gradient(135deg, #1b5e20 0%, #4caf50 50%, #2e7d32 100%);
  color: white;
  padding: 2rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  position: relative;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(27, 94, 32, 0.3);
}

.privacy-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(45deg, 
    rgba(255, 255, 255, 0.1) 0%,
    transparent 30%,
    rgba(255, 255, 255, 0.05) 50%,
    transparent 70%,
    rgba(255, 255, 255, 0.1) 100%);
  animation: shimmer 3s infinite;
}

@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

.privacy-header > * {
  position: relative;
  z-index: 2;
}

.privacy-icon {
  font-size: 2.5rem;
  animation: iconPulse 2s infinite;
  filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
}

@keyframes iconPulse {
  0%, 100% { 
    transform: scale(1); 
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
  }
  50% { 
    transform: scale(1.1); 
    filter: drop-shadow(0 6px 12px rgba(0, 0, 0, 0.4));
  }
}

.privacy-header h3 {
  flex: 1;
  margin: 0;
  font-size: 1.8rem;
  font-weight: 800;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  letter-spacing: 0.5px;
}

.privacy-close {
  background: rgba(255, 255, 255, 0.15);
  border: 2px solid rgba(255, 255, 255, 0.3);
  color: white;
  width: 45px;
  height: 45px;
  border-radius: 50%;
  font-size: 1.8rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(15px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.privacy-close:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: scale(1.15) rotate(90deg);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
  border-color: rgba(255, 255, 255, 0.5);
}

.privacy-close:active {
  transform: scale(1.05) rotate(90deg);
}

.privacy-content {
  padding: 2.5rem;
  line-height: 1.7;
  color: #2c3e50;
  overflow-y: auto;
  max-height: calc(100vh - 300px);
  scrollbar-width: thin;
  scrollbar-color: #4caf50 rgba(76, 175, 80, 0.1);
}

.privacy-content::-webkit-scrollbar {
  width: 8px;
}

.privacy-content::-webkit-scrollbar-track {
  background: rgba(76, 175, 80, 0.1);
  border-radius: 4px;
}

.privacy-content::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #4caf50, #2e7d32);
  border-radius: 4px;
}

.privacy-section {
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, rgba(232, 245, 233, 0.4) 0%, rgba(255, 255, 255, 0.6) 100%);
  border-radius: 16px;
  border-left: 5px solid #4caf50;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(27, 94, 32, 0.1);
  position: relative;
  overflow: hidden;
}

.privacy-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, 
    rgba(232, 245, 233, 0.1) 0%,
    transparent 50%,
    rgba(232, 245, 233, 0.1) 100%);
  z-index: 0;
}

.privacy-section > * {
  position: relative;
  z-index: 1;
}

.privacy-section:hover {
  background: linear-gradient(135deg, rgba(232, 245, 233, 0.6) 0%, rgba(255, 255, 255, 0.8) 100%);
  transform: translateX(8px);
  box-shadow: 0 6px 25px rgba(27, 94, 32, 0.15);
}

.privacy-section h4 {
  color: #1b5e20;
  font-size: 1.3rem;
  font-weight: 800;
  margin: 0 0 1rem 0;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  text-shadow: 0 1px 3px rgba(27, 94, 32, 0.2);
}

.privacy-section h5 {
  color: #2e7d32;
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0 0 1rem 0;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  text-shadow: 0 1px 2px rgba(46, 125, 50, 0.2);
}

.privacy-section p {
  margin: 0 0 1rem 0;
  color: #495057;
  font-weight: 500;
}

.privacy-section ul {
  margin: 1rem 0;
  padding-left: 2rem;
}

.privacy-section li {
  margin-bottom: 0.8rem;
  color: #495057;
  font-weight: 500;
  line-height: 1.6;
}

.privacy-section li strong {
  color: #1b5e20;
  font-weight: 700;
}

.privacy-consent {
  background: linear-gradient(135deg, #fff8e1 0%, #fffde7 50%, #fff3c4 100%);
  border: 3px solid #ff8f00;
  border-radius: 16px;
  padding: 2rem;
  margin-top: 1.5rem;
  text-align: center;
  position: relative;
  overflow: hidden;
  box-shadow: 0 6px 25px rgba(255, 143, 0, 0.2);
  animation: consentGlow 3s infinite;
}

@keyframes consentGlow {
  0%, 100% {
    box-shadow: 0 6px 25px rgba(255, 143, 0, 0.2);
  }
  50% {
    box-shadow: 0 8px 35px rgba(255, 143, 0, 0.3);
  }
}

.privacy-consent::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(45deg, 
    rgba(255, 193, 7, 0.1) 0%,
    transparent 30%,
    rgba(255, 143, 0, 0.1) 50%,
    transparent 70%,
    rgba(255, 193, 7, 0.1) 100%);
  animation: consentShimmer 4s infinite;
}

@keyframes consentShimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

.privacy-consent p {
  position: relative;
  z-index: 1;
  margin: 0;
  color: #e65100;
  font-weight: 700;
  font-size: 1.1rem;
  text-shadow: 0 1px 3px rgba(230, 81, 0, 0.2);
}

.privacy-footer {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 2rem;
  border-top: 2px solid rgba(27, 94, 32, 0.1);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 1.5rem;
  box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.05);
  text-align: center;
}

.privacy-timer {
  color: #6c757d;
  font-size: 1rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.privacy-timer strong {
  color: #dc3545;
  font-weight: 800;
  font-size: 1.1rem;
  animation: timerPulse 1s infinite;
}

@keyframes timerPulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.privacy-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.btn-privacy-accept {
  background: linear-gradient(135deg, #28a745 0%, #20c997 50%, #17a2b8 100%);
  color: white;
  border: none;
  padding: 1.2rem 2.5rem;
  border-radius: 35px;
  font-size: 1.1rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.4, 0.0, 0.2, 1);
  text-transform: uppercase;
  letter-spacing: 1px;
  box-shadow: 0 8px 30px rgba(40, 167, 69, 0.4);
  position: relative;
  overflow: hidden;
  border: 2px solid transparent;
}

.btn-privacy-accept::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
  transition: left 0.6s ease;
}

.btn-privacy-accept:hover::before {
  left: 100%;
}

.btn-privacy-accept:hover {
  transform: translateY(-4px) scale(1.05);
  box-shadow: 0 12px 40px rgba(40, 167, 69, 0.5);
  background: linear-gradient(135deg, #34ce57 0%, #2dd4aa 50%, #20b2aa 100%);
  border-color: rgba(255, 255, 255, 0.3);
}

.btn-privacy-accept:active {
  transform: translateY(-2px) scale(1.02);
  box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
}

/* Responsive Design */
@media (max-width: 768px) {
  .privacy-overlay {
    padding: 10px;
    padding-top: 20px;
  }
  
  .privacy-modal {
    max-height: calc(100vh - 40px);
    border-radius: 20px;
  }
  
  .privacy-header {
    padding: 1.5rem;
  }
  
  .privacy-header h3 {
    font-size: 1.4rem;
  }
  
  .privacy-content {
    padding: 1.5rem;
    max-height: calc(100vh - 220px);
  }
  
  .privacy-section {
    padding: 1rem;
    margin-bottom: 1.5rem;
  }
  
  .privacy-section h4 {
    font-size: 1.1rem;
  }
  
  .privacy-section h5 {
    font-size: 1rem;
  }
  
  .privacy-footer {
    padding: 1.5rem;
    flex-direction: column;
    text-align: center;
  }
  
  .btn-privacy-accept {
    width: 100%;
    padding: 1rem 2rem;
    font-size: 1rem;
  }
}

@media (max-width: 480px) {
  .privacy-overlay {
    padding: 5px;
    padding-top: 10px;
  }
  
  .privacy-modal {
    max-height: calc(100vh - 20px);
    border-radius: 16px;
  }
  
  .privacy-header {
    padding: 1rem;
  }
  
  .privacy-header h3 {
    font-size: 1.2rem;
  }
  
  .privacy-icon {
    font-size: 2rem;
  }
  
  .privacy-close {
    width: 40px;
    height: 40px;
    font-size: 1.5rem;
  }
  
  .privacy-content {
    padding: 1rem;
    max-height: calc(100vh - 180px);
  }
  
  .privacy-section {
    padding: 0.8rem;
    margin-bottom: 1rem;
  }
  
  .privacy-section h4 {
    font-size: 1rem;
  }
  
  .privacy-section h5 {
    font-size: 0.95rem;
  }
  
  .privacy-consent {
    padding: 1.5rem;
  }
  
  .privacy-footer {
    padding: 1rem;
  }
  
  .privacy-timer {
    font-size: 0.9rem;
  }
  
  .btn-privacy-accept {
    padding: 1rem;
    font-size: 0.95rem;
  }
}

/* Blur effect for background content when modal is open */
.privacy-overlay.show ~ .container {
  filter: blur(5px);
  pointer-events: none;
  transition: filter 0.4s ease;
}

/* Tab Navigation Styles - Enhanced for Kiosk Visibility */
.tab-navigation {
  display: flex;
  background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 40%, #388e3c 70%, #4caf50 100%);
  border-radius: 25px 25px 0 0;
  padding: 2rem 1.5rem;
  margin-bottom: 0;
  box-shadow: 0 6px 30px rgba(27, 94, 32, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.1);
  overflow-x: auto;
  gap: 1.2rem;
  border: 4px solid rgba(255, 255, 255, 0.15);
  position: relative;
}

.tab-navigation::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 100%;
  background: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.1) 50%, rgba(255, 255, 255, 0.1) 75%, transparent 75%);
  background-size: 40px 40px;
  opacity: 0.1;
  pointer-events: none;
}

.tab-btn {
  flex: 1;
  min-width: 220px;
  padding: 1.8rem 2.2rem;
  border: 4px solid rgba(255, 255, 255, 0.4);
  background: rgba(255, 255, 255, 0.18);
  color: white;
  font-weight: 800;
  font-size: 1.15rem;
  border-radius: 18px;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  text-align: center;
  line-height: 1.3;
  position: relative;
  overflow: hidden;
  backdrop-filter: blur(15px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  letter-spacing: 0.5px;
}

.tab-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 0%, transparent 50%, rgba(255, 255, 255, 0.1) 100%);
  z-index: 0;
}

.tab-btn > * {
  position: relative;
  z-index: 1;
}

.tab-btn small {
  display: block;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.85);
  font-size: 0.95rem;
  font-style: italic;
  margin-top: 0.6rem;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

/* Add step number badges */
.tab-btn::after {
  content: attr(data-step);
  position: absolute;
  top: -8px;
  right: -8px;
  background: linear-gradient(135deg, #ff9800, #f57c00);
  color: white;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 900;
  box-shadow: 0 3px 10px rgba(255, 152, 0, 0.4);
  border: 2px solid white;
  z-index: 5;
}

.tab-btn.active::after {
  background: linear-gradient(135deg, #4caf50, #2e7d32);
  box-shadow: 0 3px 10px rgba(76, 175, 80, 0.5);
  transform: scale(1.1);
}

.tab-btn.completed::after {
  content: '✓';
  background: linear-gradient(135deg, #4caf50, #2e7d32);
  box-shadow: 0 3px 10px rgba(76, 175, 80, 0.5);
}

.tab-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.8);
  transform: translateY(-4px) scale(1.05);
  box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25);
  color: #fff;
  text-shadow: 0 3px 6px rgba(0, 0, 0, 0.4);
}

.tab-btn.active {
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 50%, #ffffff 100%);
  color: #1b5e20;
  border-color: #ffffff;
  box-shadow: 0 10px 40px rgba(27, 94, 32, 0.5), 0 0 0 2px rgba(255, 255, 255, 0.8);
  transform: translateY(-6px) scale(1.08);
  font-weight: 900;
  text-shadow: 0 1px 3px rgba(27, 94, 32, 0.2);
  z-index: 10;
}

.tab-btn.active::before {
  background: linear-gradient(45deg, rgba(27, 94, 32, 0.05) 0%, transparent 50%, rgba(27, 94, 32, 0.05) 100%);
}

.tab-btn.active small {
  color: #2e7d32;
  font-weight: 600;
}

.tab-btn.completed {
  background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
  color: white;
  border-color: #4caf50;
  box-shadow: 0 6px 20px rgba(76, 175, 80, 0.3);
}

.tab-btn.completed::after {
  content: '✓';
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(255, 255, 255, 0.9);
  color: #2e7d32;
  border-radius: 50%;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: bold;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  z-index: 2;
}

.tab-btn:focus {
  outline: 3px solid rgba(255, 255, 255, 0.8);
  outline-offset: 2px;
}

/* Tab Content Styles */
.tab-content-container {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 0 0 20px 20px;
  min-height: 400px;
  max-height: none;
  overflow-y: auto;
  position: relative;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.tab-content {
  display: none;
  padding: 1.5rem;
  animation: fadeInTab 0.4s ease-in-out;
  flex: 1;
  overflow-y: visible;
  min-height: 400px;
}

.tab-content.active {
  display: flex;
  flex-direction: column;
}

@keyframes fadeInTab {
  from {
    opacity: 0;
    transform: translateX(20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

/* Tab Navigation Controls */
.tab-navigation-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 0 0 20px 20px;
  border-top: 1px solid rgba(76, 175, 80, 0.1);
  box-shadow: 0 -2px 8px rgba(27, 94, 32, 0.05);
}

#prevBtn, #nextBtn {
  min-width: 160px;
  padding: 1.3rem 2.5rem;
  font-size: 1.1rem;
  font-weight: 800;
  border-radius: 35px;
  transition: all 0.4s ease;
  text-transform: uppercase;
  letter-spacing: 1px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

#nextBtn {
  background: linear-gradient(135deg, #4caf50, #2e7d32);
  border: none;
  color: white;
  box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
}

#nextBtn:hover {
  background: linear-gradient(135deg, #66bb6a, #388e3c);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
}

#nextBtn.btn-success {
  background: linear-gradient(135deg, #28a745, #20c997);
  border: none;
  color: white;
  box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

#nextBtn.btn-success:hover {
  background: linear-gradient(135deg, #20c997, #17a2b8);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

/* Progress Indicator Styles - Enhanced for Kiosk Visibility */
.progress-indicator {
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  padding: 1.5rem 2.5rem;
  border-radius: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 4px 15px rgba(27, 94, 32, 0.15);
  border-bottom: 3px solid rgba(76, 175, 80, 0.2);
  border-top: 1px solid rgba(255, 255, 255, 0.8);
}

.progress-bar {
  flex: 1;
  height: 12px;
  background: rgba(76, 175, 80, 0.15);
  border-radius: 8px;
  overflow: hidden;
  margin-right: 2rem;
  position: relative;
  border: 2px solid rgba(76, 175, 80, 0.2);
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
}

.progress-fill {
  height: 100%;
  background: linear-gradient(135deg, #4caf50 0%, #2e7d32 50%, #1b5e20 100%);
  border-radius: 6px;
  transition: width 0.6s cubic-bezier(0.4, 0.0, 0.2, 1);
  width: 20%; /* Default for step 1 */
  position: relative;
  box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
}

.progress-fill::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
  animation: progressShimmer 2.5s infinite;
}

@keyframes progressShimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

.progress-text {
  font-weight: 900;
  color: #1b5e20;
  font-size: 1.4rem;
  white-space: nowrap;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
  padding: 0.8rem 1.5rem;
  background: linear-gradient(135deg, rgba(76, 175, 80, 0.15), rgba(27, 94, 32, 0.1));
  border-radius: 25px;
  border: 3px solid rgba(76, 175, 80, 0.3);
  letter-spacing: 1px;
  position: relative;
  overflow: hidden;
}

.progress-text::before {
  content: '📋';
  margin-right: 0.8rem;
  font-size: 1.2rem;
}

#prevBtn {
  background: linear-gradient(135deg, #78909c, #546e7a);
  border: none;
  color: white;
  box-shadow: 0 4px 15px rgba(120, 144, 156, 0.3);
}

#prevBtn:hover {
  background: linear-gradient(135deg, #90a4ae, #607d8b);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(120, 144, 156, 0.4);
}

/* Kiosk-Friendly Adjustments */
.registration-form {
  min-height: 400px;
  overflow: visible;
  display: flex;
  flex-direction: column;
  flex: 1;
  margin: 0;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 0;
  box-shadow: none;
  backdrop-filter: blur(20px);
  border: none;
}

.registration-form::before {
  display: none;
}

.registration-form fieldset {
  border: none;
  margin: 0;
  padding: 0;
  background: transparent;
  box-shadow: none;
}

.registration-form legend {
  background: transparent;
  color: #1b5e20;
  padding: 0 0 1.5rem 0;
  border-radius: 0;
  font-size: 1.3rem;
  box-shadow: none;
  text-transform: none;
  margin-bottom: 1.5rem;
  border-bottom: 2px solid rgba(76, 175, 80, 0.2);
}

/* Responsive Design for Kiosk */
@media (max-width: 1024px) {
  .container {
    max-width: 100%;
    padding: 15px 10px;
  }
  
  .tab-navigation {
    padding: 0.8rem;
    gap: 0.3rem;
  }
  
  .tab-btn {
    min-width: 140px;
    padding: 0.8rem 1rem;
    font-size: 0.85rem;
  }
  
  .tab-content {
    padding: 1rem;
  }
  
  .tab-content-container {
    min-height: 400px;
  }
}

@media (max-width: 768px) {
  .container {
    max-width: 100%;
    padding: 10px 5px;
  }
  
  .tab-navigation {
    flex-wrap: wrap;
    padding: 0.5rem;
  }
  
  .tab-btn {
    min-width: 120px;
    padding: 0.7rem 0.8rem;
    font-size: 0.8rem;
  }
  
  .tab-content {
    padding: 0.8rem;
  }
  
  .tab-navigation-controls {
    padding: 1rem;
  }
  
  #prevBtn, #nextBtn {
    min-width: 100px;
    padding: 0.8rem 1.5rem;
    font-size: 0.9rem;
  }
  
  .tab-content-container {
    min-height: 400px;
  }
}

/* Fullscreen kiosk mode */
@media (min-width: 1200px) {
  .container {
    max-width: 100%;
    padding: 20px;
  }
  
  .tab-content-container {
    min-height: 500px;
  }
}

/* Enhanced Form Actions for Tab Interface */
.form-actions {
  position: sticky;
  bottom: 0;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 1.5rem;
  margin: 0;
  border-top: 2px solid rgba(76, 175, 80, 0.1);
  border-radius: 0 0 20px 20px;
}

/* ENHANCED Form Readonly Mode - Project Specification Compliant */
.registration-form.readonly-mode input,
.registration-form.readonly-mode select,
.registration-form.readonly-mode textarea {
  pointer-events: none;
  background-color: #ffffff !important;
  border: 3px solid #2196f3 !important;
  color: #0d47a1 !important;
  font-weight: 700 !important;
  font-size: 1.1rem !important;
  opacity: 1 !important;
  box-shadow: 0 4px 12px rgba(33, 150, 243, 0.2) !important;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;
  padding: 12px 16px !important;
  border-radius: 8px !important;
  min-height: 48px !important;
  line-height: 1.4 !important;
}

/* Enhanced family table input styling - NO dots with proper scrolling */
.registration-form.readonly-mode .family-table .table-input {
  border: 3px solid #4caf50 !important;
  color: #1b5e20 !important;
  font-weight: 700 !important;
  font-size: 1rem !important;
  background: rgba(255, 255, 255, 0.98) !important;
  box-shadow: 0 3px 10px rgba(76, 175, 80, 0.3) !important;
  padding: 12px 14px !important;
  min-width: 120px !important;
  width: 100% !important;
  border-radius: 6px !important;
  /* Proper scrolling without dots */
  overflow-x: auto;
  white-space: nowrap;
  text-overflow: clip;
}

/* Enhanced scrollable name inputs with visible scrollbars */
.registration-form.readonly-mode .scrollable-name-input {
  overflow-x: auto !important;
  white-space: nowrap !important;
  min-width: 180px !important;
  max-width: 250px !important;
  scrollbar-width: thin;
  scrollbar-color: #4caf50 rgba(76, 175, 80, 0.2);
  border: 3px solid #4caf50 !important;
  padding: 12px 14px !important;
  border-radius: 6px !important;
}

.registration-form.readonly-mode .scrollable-name-input::-webkit-scrollbar {
  height: 6px;
}

.registration-form.readonly-mode .scrollable-name-input::-webkit-scrollbar-track {
  background: rgba(76, 175, 80, 0.1);
  border-radius: 3px;
}

.registration-form.readonly-mode .scrollable-name-input::-webkit-scrollbar-thumb {
  background: #4caf50;
  border-radius: 3px;
}

.registration-form.readonly-mode input:focus,
.registration-form.readonly-mode select:focus,
.registration-form.readonly-mode textarea:focus {
  border-color: #1565c0 !important;
  box-shadow: 0 0 0 4px rgba(21, 101, 192, 0.3) !important;
}

.registration-form.readonly-mode select {
  background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 4 5"><path fill="%230d47a1" d="M2 0L0 2h4zm0 5L0 3h4z"/></svg>') !important;
  background-repeat: no-repeat !important;
  background-position: right 15px center !important;
  background-size: 16px !important;
  padding-right: 45px !important;
  font-weight: 700 !important;
}

/* Enhanced styling for selected options and text */
.registration-form.readonly-mode select option:checked,
.registration-form.readonly-mode select option[selected] {
  background-color: #e3f2fd !important;
  color: #0d47a1 !important;
  font-weight: 700 !important;
}

.registration-form.readonly-mode input[value]:not([value=""]),
.registration-form.readonly-mode select[value]:not([value=""]),
.registration-form.readonly-mode textarea:not(:empty) {
  background-color: #f3e5f5 !important;
  border-color: #7b1fa2 !important;
  color: #4a148c !important;
  font-weight: 800 !important;
}

/* Enhanced readonly mode styling for radio buttons and checkboxes */
.registration-form.readonly-mode input[type="radio"]:checked + label,
.registration-form.readonly-mode input[type="checkbox"]:checked + label {
  background: linear-gradient(135deg, #4caf50, #66bb6a) !important;
  color: #ffffff !important;
  font-weight: 800 !important;
  border: 3px solid #2e7d32 !important;
  border-radius: 12px !important;
  padding: 12px 16px !important;
  box-shadow: 0 6px 18px rgba(76, 175, 80, 0.5) !important;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3) !important;
  transform: scale(1.05) !important;
}

.registration-form.readonly-mode input[type="radio"]:checked,
.registration-form.readonly-mode input[type="checkbox"]:checked {
  transform: scale(1.3) !important;
  accent-color: #4caf50 !important;
  filter: drop-shadow(0 2px 4px rgba(76, 175, 80, 0.4)) !important;
}

.registration-form.readonly-mode label {
  color: #263238 !important;
  font-weight: 700 !important;
  font-size: 1.05rem !important;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
}

.registration-form.readonly-mode button[type="submit"],
.registration-form.readonly-mode button[type="reset"],
.registration-form.readonly-mode .btn-add-family-member,
.registration-form.readonly-mode .btn-remove-family,
.registration-form.readonly-mode .btn-add-disability,
.registration-form.readonly-mode .btn-remove-disability,
.registration-form.readonly-mode .btn-add-organization,
.registration-form.readonly-mode .btn-remove-organization {
  pointer-events: none;
  background-color: #f5f5f5 !important;
  border-color: #e0e0e0 !important;
  color: #9e9e9e !important;
  opacity: 0.6;
}

.registration-form.readonly-mode .tab-btn,
.registration-form.readonly-mode #prevBtn,
.registration-form.readonly-mode #nextBtn {
  pointer-events: auto !important;
}

.registration-form.readonly-mode .tab-navigation-controls #nextBtn {
  display: none !important;
}

.registration-form.readonly-mode .tab-navigation-controls #prevBtn {
  display: none !important;
}

/* Readonly Notice Styles */
.readonly-notice {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 1.5rem 2rem;
  background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
  border-radius: 0 0 20px 20px;
  border-top: 1px solid rgba(76, 175, 80, 0.1);
  box-shadow: 0 -2px 8px rgba(27, 94, 32, 0.05);
}

.readonly-badge {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 2rem;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 15px;
  border: 2px solid rgba(63, 81, 181, 0.2);
  box-shadow: 0 4px 15px rgba(63, 81, 181, 0.1);
}

.readonly-icon {
  font-size: 1.5rem;
}

.readonly-text {
  font-weight: 600;
  color: #3f51b5;
  text-align: center;
  line-height: 1.3;
}

.readonly-text small {
  display: block;
  font-weight: 400;
  color: #5c6bc0;
  font-style: italic;
  margin-top: 0.3rem;
}

/* Global Styles */
* {
  box-sizing: border-box;
}

body {
  font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  line-height: 1.6;
  color: #2c3e50;
  background: url('../background.jpg') no-repeat center center fixed;
  background-size: cover;
  min-height: 100vh;
  margin: 0;
  padding: 0;
  position: relative;
  display: flex;
  flex-direction: column;
}

body::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, 
    rgba(232, 245, 233, 0.9) 0%,
    rgba(255, 255, 255, 0.95) 50%,
    rgba(232, 245, 233, 0.9) 100%);
  backdrop-filter: blur(20px);
  z-index: -1;
}

/* Container Styles */
.container {
  max-width: 1400px;
  width: 100%;
  flex: 1;
  margin: 0 auto;
  padding: 20px 15px;
  background: rgba(255, 255, 255, 0.98);
  border-radius: 0;
  box-shadow: 0 25px 80px rgba(27, 94, 32, 0.15);
  backdrop-filter: blur(25px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, 
    rgba(232, 245, 233, 0.1) 0%,
    rgba(255, 255, 255, 0.05) 50%,
    rgba(232, 245, 233, 0.1) 100%);
  z-index: -1;
}

.section {
  margin-bottom: 0;
  position: relative;
  z-index: 1;
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Admin View Banner */
.admin-view-banner {
  background: linear-gradient(135deg, #1b5e20 0%, #4caf50 100%);
  color: white;
  padding: 2rem;
  border-radius: 20px;
  margin-bottom: 2.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1.5rem;
  box-shadow: 0 15px 35px rgba(27, 94, 32, 0.3);
  position: relative;
  overflow: hidden;
}

.admin-view-banner::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(45deg, 
    rgba(255, 255, 255, 0.1) 0%,
    transparent 50%,
    rgba(255, 255, 255, 0.1) 100%);
  z-index: 0;
}

.admin-view-banner > * {
  position: relative;
  z-index: 1;
}

.status-info {
  display: flex;
  gap: 1.5rem;
  align-items: center;
  flex-wrap: wrap;
}

.status-badge {
  padding: 0.8rem 1.5rem;
  border-radius: 30px;
  font-weight: 700;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
}

.status-badge:hover {
  transform: translateY(-2px);
}

.status-pending { 
  background: linear-gradient(135deg, #ff8f00, #ffb300); 
  color: white; 
}

.status-approved { 
  background: linear-gradient(135deg, #2e7d32, #4caf50); 
  color: white; 
}

.status-rejected { 
  background: linear-gradient(135deg, #c62828, #d32f2f); 
  color: white; 
}

.submission-info {
  background: rgba(255, 255, 255, 0.25);
  padding: 0.8rem 1.5rem;
  border-radius: 25px;
  font-size: 0.95rem;
  font-weight: 500;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.back-btn {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  padding: 1rem 2rem;
  border-radius: 30px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
  border: 2px solid rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(10px);
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

/* Form Styles */
.registration-form {
  background: rgba(255, 255, 255, 0.95);
  padding: 2.5rem;
  border-radius: 20px;
  box-shadow: 0 15px 40px rgba(27, 94, 32, 0.08);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  position: relative;
  overflow: hidden;
}

.registration-form::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, 
    rgba(232, 245, 233, 0.05) 0%,
    rgba(255, 255, 255, 0.02) 50%,
    rgba(232, 245, 233, 0.05) 100%);
  z-index: -1;
}

fieldset {
  border: 2px solid rgba(76, 175, 80, 0.2);
  border-radius: 16px;
  padding: 2rem;
  margin-bottom: 2rem;
  background: linear-gradient(135deg, 
    rgba(248, 249, 250, 0.9) 0%, 
    rgba(255, 255, 255, 0.95) 100%);
  backdrop-filter: blur(15px);
  box-shadow: 0 8px 25px rgba(27, 94, 32, 0.05);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

fieldset:hover {
  box-shadow: 0 12px 35px rgba(27, 94, 32, 0.1);
  transform: translateY(-2px);
}

fieldset::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, 
    rgba(232, 245, 233, 0.1) 0%,
    transparent 50%,
    rgba(232, 245, 233, 0.1) 100%);
  z-index: -1;
}

legend {
  background: linear-gradient(135deg, #1b5e20, #4caf50);
  color: white;
  padding: 1rem 2rem;
  border-radius: 30px;
  font-weight: 700;
  font-size: 1.1rem;
  border: none;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 1.5rem;
  box-shadow: 0 8px 25px rgba(27, 94, 32, 0.3);
  position: relative;
  overflow: hidden;
}

legend::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(45deg, 
    rgba(255, 255, 255, 0.2) 0%,
    transparent 50%,
    rgba(255, 255, 255, 0.2) 100%);
  z-index: 0;
}

legend * {
  position: relative;
  z-index: 1;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
  position: relative;
}

.form-group label {
  display: block;
  margin-bottom: 0.8rem;
  font-weight: 600;
  color: #1b5e20;
  font-size: 1rem;
  position: relative;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  line-height: 1.3;
}

.form-group label small {
  display: block;
  font-weight: 400;
  color: #666;
  font-size: 0.85rem;
  font-style: italic;
  margin-top: 0.2rem;
}

legend small {
  display: block;
  font-weight: 400;
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.85rem;
  font-style: italic;
  margin-top: 0.3rem;
  text-transform: none;
  letter-spacing: normal;
}

th small {
  display: block;
  font-weight: 400;
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.75rem;
  font-style: italic;
  margin-top: 0.2rem;
  text-transform: none;
  letter-spacing: normal;
}

h4 small {
  display: block;
  font-weight: 400;
  color: #666;
  font-size: 0.85rem;
  font-style: italic;
  margin-top: 0.2rem;
  text-shadow: none;
}

.btn small {
  display: block;
  font-weight: 400;
  font-size: 0.8rem;
  opacity: 0.8;
  margin-top: 0.2rem;
  text-transform: none;
  letter-spacing: normal;
}

.form-group label::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 30px;
  height: 2px;
  background: linear-gradient(135deg, #4caf50, #2e7d32);
  border-radius: 1px;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 1rem 1.2rem;
  border: 2px solid rgba(76, 175, 80, 0.2);
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  font-family: inherit;
  box-shadow: 0 2px 8px rgba(27, 94, 32, 0.05);
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #4caf50;
  outline: none;
  box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.15);
  transform: translateY(-2px);
  background: rgba(255, 255, 255, 1);
}

.form-group input:hover,
.form-group select:hover,
.form-group textarea:hover {
  border-color: rgba(76, 175, 80, 0.4);
  box-shadow: 0 4px 12px rgba(27, 94, 32, 0.08);
}

/* Name Fields Group Styles */
.name-fields-group {
  display: flex;
  gap: 1rem;
  width: 100%;
}

.name-field {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.name-field:nth-child(2) {
  flex: 0.8; /* Make middle name field slightly smaller */
}

.name-field input {
  margin-bottom: 0.5rem;
}

.name-field small {
  font-size: 0.8rem;
  color: #6c757d;
  font-weight: 500;
  text-align: center;
  display: block;
  width: 100%;
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

.validation-message.loading {
  background: rgba(255, 193, 7, 0.1);
  color: #856404;
  border: 1px solid rgba(255, 193, 7, 0.3);
}

/* Input validation states */
.form-group input.validating {
  border-color: #ffc107;
  box-shadow: 0 0 0 4px rgba(255, 193, 7, 0.15);
}

.form-group input.valid {
  border-color: #28a745;
  box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.15);
}

.form-group input.invalid {
  border-color: #dc3545;
  box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.15);
}

/* Responsive adjustments for name fields */
@media (max-width: 768px) {
  .name-fields-group {
    flex-direction: column;
    gap: 1rem;
  }
  
  .name-field:nth-child(2) {
    flex: 1; /* Reset flex on mobile */
  }
}

@media (max-width: 480px) {
  .name-fields-group {
    gap: 0.8rem;
  }
}

/* Table Styles */
.table-wrapper {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 16px;
  box-shadow: 0 8px 25px rgba(27, 94, 32, 0.05);
  overflow: hidden;
  margin: 1.5rem 0 0 0;
  border: 1px solid rgba(76, 175, 80, 0.1);
  flex: 1;
  min-height: 0;
}

.table-responsive {
  overflow: auto;
  max-height: 400px;
  scrollbar-width: thin;
  scrollbar-color: #4caf50 rgba(76, 175, 80, 0.1);
}

.table-responsive::-webkit-scrollbar {
  height: 8px;
  width: 8px;
}

.table-responsive::-webkit-scrollbar-track {
  background: rgba(76, 175, 80, 0.1);
  border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
  background: #4caf50;
  border-radius: 4px;
}

.family-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.95rem;
}

.family-table thead {
  background: linear-gradient(135deg, #1b5e20, #4caf50);
  color: white;
}

.family-table thead th {
  padding: 1.2rem 1rem;
  font-weight: 700;
  text-align: left;
  border: none;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.family-table tbody td {
  padding: 1rem;
  border-bottom: 1px solid rgba(76, 175, 80, 0.1);
  vertical-align: middle;
}

.family-table tbody tr {
  transition: all 0.3s ease;
}

.family-table tbody tr:hover {
  background: rgba(232, 245, 233, 0.3);
  transform: scale(1.01);
}

.table-input {
  width: 100%;
  padding: 0.8rem;
  border: 1px solid rgba(76, 175, 80, 0.2);
  border-radius: 8px;
  font-size: 0.9rem;
  background: rgba(255, 255, 255, 0.9);
  transition: all 0.3s ease;
}

.table-input:focus {
  border-color: #4caf50;
  outline: none;
  box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.15);
  background: white;
}

/* Checkbox and Radio Styles */
.checkbox-group {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin: 1rem 0;
}

.checkbox-group label {
  display: flex;
  align-items: center;
  padding: 0.8rem 1rem;
  background: rgba(255, 255, 255, 0.7);
  border: 2px solid rgba(76, 175, 80, 0.1);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 500;
  position: relative;
  overflow: hidden;
}

.checkbox-group label:hover {
  background: rgba(232, 245, 233, 0.5);
  border-color: rgba(76, 175, 80, 0.3);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(27, 94, 32, 0.1);
}

.checkbox-group label::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, 
    rgba(232, 245, 233, 0.1) 0%,
    transparent 50%,
    rgba(232, 245, 233, 0.1) 100%);
  z-index: -1;
}

.checkbox-group input[type="radio"],
.checkbox-group input[type="checkbox"] {
  margin-right: 0.8rem;
  transform: scale(1.2);
  accent-color: #4caf50;
}

.other-input {
  margin-top: 1rem !important;
  background: rgba(255, 255, 255, 0.9) !important;
  border: 2px solid rgba(76, 175, 80, 0.2) !important;
  border-radius: 12px !important;
  padding: 1rem !important;
  transition: all 0.3s ease !important;
}

.other-input:focus {
  border-color: #4caf50 !important;
  box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.15) !important;
}

.other-input:disabled {
  background: rgba(248, 249, 250, 0.8) !important;
  border-color: rgba(224, 224, 224, 0.5) !important;
  color: #999 !important;
}

/* Subsection Styles */
.subsection {
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.5);
  border-radius: 12px;
  border: 1px solid rgba(76, 175, 80, 0.1);
  backdrop-filter: blur(10px);
}

.subsection h4 {
  color: #1b5e20;
  font-size: 1.1rem;
  font-weight: 700;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid rgba(76, 175, 80, 0.2);
  position: relative;
}

.subsection h4::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 50px;
  height: 2px;
  background: linear-gradient(135deg, #4caf50, #2e7d32);
  border-radius: 1px;
}

/* Disability and Organization Sections */
.disability-row,
.organization-row {
  display: grid;
  grid-template-columns: 1fr 1fr auto;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.7);
  border-radius: 12px;
  border: 1px solid rgba(76, 175, 80, 0.1);
  transition: all 0.3s ease;
  position: relative;
}

.disability-row:hover,
.organization-row:hover {
  background: rgba(232, 245, 233, 0.3);
  border-color: rgba(76, 175, 80, 0.3);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(27, 94, 32, 0.1);
}

.remove-group {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Disability Controls */
.disability-controls {
  display: flex;
  justify-content: center;
  padding: 1.5rem 0;
  margin-top: 1rem;
  border-top: 2px solid rgba(76, 175, 80, 0.1);
  position: relative;
  z-index: 10;
  background: white;
  width: 100%;
  clear: both;
  box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
  flex-shrink: 0;
}

.btn-add-disability {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding: 1.2rem 2rem;
  background: linear-gradient(135deg, #ff9800, #f57c00);
  color: white;
  border: none;
  border-radius: 20px;
  font-size: 1.1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 6px 20px rgba(255, 152, 0, 0.3);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  min-width: 220px;
  justify-content: center;
}

.btn-add-disability:hover {
  background: linear-gradient(135deg, #ffb74d, #ff9800);
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(255, 152, 0, 0.4);
}

.btn-remove-disability {
  background: linear-gradient(135deg, #e74c3c, #c0392b);
  color: white;
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  font-size: 1.2rem;
  font-weight: 900;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
  min-width: 40px;
}

.btn-remove-disability:hover {
  background: linear-gradient(135deg, #c0392b, #a93226);
  transform: scale(1.1);
  box-shadow: 0 6px 18px rgba(231, 76, 60, 0.4);
}

/* Organization Controls */
.organization-controls {
  display: flex;
  justify-content: center;
  padding: 1.5rem 0;
  margin-top: 1rem;
  border-top: 2px solid rgba(76, 175, 80, 0.1);
  position: relative;
  z-index: 10;
  background: white;
  width: 100%;
  clear: both;
  box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
  flex-shrink: 0;
}

.btn-add-organization {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding: 1.2rem 2rem;
  background: linear-gradient(135deg, #9c27b0, #7b1fa2);
  color: white;
  border: none;
  border-radius: 20px;
  font-size: 1.1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 6px 20px rgba(156, 39, 176, 0.3);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  min-width: 220px;
  justify-content: center;
}

.btn-add-organization:hover {
  background: linear-gradient(135deg, #ba68c8, #9c27b0);
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(156, 39, 176, 0.4);
}

.btn-remove-organization {
  background: linear-gradient(135deg, #e74c3c, #c0392b);
  color: white;
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  font-size: 1.2rem;
  font-weight: 900;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
  min-width: 40px;
}

.btn-remove-organization:hover {
  background: linear-gradient(135deg, #c0392b, #a93226);
  transform: scale(1.1);
  box-shadow: 0 6px 18px rgba(231, 76, 60, 0.4);
}

.btn-add-disability .btn-icon,
.btn-add-organization .btn-icon {
  font-size: 1.3rem;
  background: rgba(255, 255, 255, 0.2);
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 900;
}

.btn-add-disability .btn-text,
.btn-add-organization .btn-text {
  text-align: left;
  line-height: 1.2;
}

.btn-add-disability .btn-text small,
.btn-add-organization .btn-text small {
  display: block;
  font-weight: 500;
  font-size: 0.85rem;
  opacity: 0.9;
  font-style: italic;
  text-transform: none;
  letter-spacing: normal;
  margin-top: 0.2rem;
}

.disability-row:hover,
.organization-row:hover {
  background: rgba(232, 245, 233, 0.3);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(27, 94, 32, 0.1);
}

.form-control {
  width: 100%;
  padding: 1rem;
  border: 2px solid rgba(76, 175, 80, 0.2);
  border-radius: 12px;
  font-size: 1rem;
  background: rgba(255, 255, 255, 0.9);
  transition: all 0.3s ease;
}

.form-control:focus {
  border-color: #4caf50;
  outline: none;
  box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.15);
  background: white;
}
.form-actions {
  display: flex;
  gap: 1.5rem;
  justify-content: center;
  margin-top: 3rem;
  padding-top: 2.5rem;
  border-top: 2px solid rgba(76, 175, 80, 0.1);
  position: relative;
}

/* Hide main submit buttons when using tab navigation, except on last tab */
.tab-navigation-active #mainFormActions {
  display: none;
}

/* Show submit buttons on last tab or when tab navigation is not active */
.tab-navigation-active.last-tab #mainFormActions,
.tab-navigation-inactive #mainFormActions {
  display: flex;
}

.submit-instructions {
  text-align: center;
  margin-bottom: 1rem;
  padding: 1rem;
  background: rgba(76, 175, 80, 0.1);
  border-radius: 8px;
  border: 1px solid rgba(76, 175, 80, 0.3);
}

.submit-instructions p {
  margin: 0;
  color: #2e7d32;
}

.submit-instructions small {
  font-size: 0.9rem;
}

.form-actions::before {
  content: '';
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100px;
  height: 2px;
  background: linear-gradient(135deg, #4caf50, #2e7d32);
  border-radius: 1px;
}

.btn {
  padding: 1rem 2.5rem;
  border: none;
  border-radius: 30px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  text-transform: uppercase;
  letter-spacing: 1px;
  position: relative;
  overflow: hidden;
  min-width: 180px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transition: left 0.5s ease;
}

.btn:hover::before {
  left: 100%;
}

.btn-primary {
  background: linear-gradient(135deg, #4caf50, #2e7d32);
  color: white;
  box-shadow: 0 8px 25px rgba(76, 175, 80, 0.3);
}

.btn-primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 35px rgba(76, 175, 80, 0.4);
  background: linear-gradient(135deg, #66bb6a, #388e3c);
}

.btn-secondary {
  background: linear-gradient(135deg, #78909c, #546e7a);
  color: white;
  box-shadow: 0 8px 25px rgba(120, 144, 156, 0.3);
}

.btn-secondary:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 35px rgba(120, 144, 156, 0.4);
  background: linear-gradient(135deg, #90a4ae, #607d8b);
}

.btn:active {
  transform: translateY(0);
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Alert Styles */
.alert {
  padding: 1.5rem 2rem;
  border-radius: 16px;
  margin-bottom: 2rem;
  border: none;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(15px);
  position: relative;
  overflow: hidden;
  font-weight: 500;
}

.alert::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  z-index: 1;
}

.alert h4 {
  margin: 0 0 0.5rem 0;
  font-weight: 700;
  font-size: 1.1rem;
}

.alert-success {
  background: linear-gradient(135deg, rgba(212, 237, 218, 0.9), rgba(195, 230, 203, 0.9));
  color: #155724;
  border-left: 4px solid #28a745;
}

.alert-success::before {
  background: linear-gradient(135deg, #28a745, #20c997);
}

.alert-error {
  background: linear-gradient(135deg, rgba(248, 215, 218, 0.9), rgba(245, 198, 203, 0.9));
  color: #721c24;
  border-left: 4px solid #dc3545;
}

.alert-error::before {
  background: linear-gradient(135deg, #dc3545, #e74c3c);
}

/* Responsive Design */
@media (max-width: 1024px) {
  .container {
    padding: 25px 15px;
    margin: 15px;
  }
  
  .checkbox-group {
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  }
  
  .disability-row,
  .organization-row {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}

@media (max-width: 768px) {
  .container {
    padding: 20px 10px;
    margin: 10px;
    border-radius: 16px;
  }
  
  .registration-form {
    padding: 1.5rem;
    border-radius: 16px;
  }
  
  fieldset {
    padding: 1.5rem;
    border-radius: 12px;
  }
  
  legend {
    padding: 0.8rem 1.5rem;
    font-size: 1rem;
    border-radius: 25px;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .checkbox-group {
    grid-template-columns: 1fr;
  }
  
  .table-responsive {
    font-size: 0.85rem;
  }
  
  .family-table thead th {
    padding: 0.8rem 0.5rem;
    font-size: 0.8rem;
  }
  
  .family-table tbody td {
    padding: 0.8rem 0.5rem;
  }
  
  .table-input {
    padding: 0.6rem;
    font-size: 0.85rem;
  }
  
  .form-actions {
    flex-direction: column;
    align-items: center;
    gap: 1rem;
  }
  
  .btn {
    width: 100%;
    max-width: 300px;
    padding: 1rem 2rem;
  }
  
  .admin-view-banner {
    padding: 1.5rem;
    flex-direction: column;
    text-align: center;
  }
  
  .status-info {
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .container {
    margin: 5px;
    padding: 15px 8px;
  }
  
  .registration-form {
    padding: 1rem;
  }
  
  fieldset {
    padding: 1rem;
  }
  
  legend {
    padding: 0.6rem 1rem;
    font-size: 0.9rem;
  }
  
  .subsection {
    padding: 1rem;
  }
  
  .subsection h4 {
    font-size: 1rem;
  }
  
  .checkbox-group label {
    padding: 0.6rem 0.8rem;
    font-size: 0.9rem;
  }
  
  .form-group label {
    font-size: 0.9rem;
  }
  
  .form-group input,
  .form-group select,
  .form-control {
    padding: 0.8rem;
    font-size: 0.9rem;
  }
  
  .btn {
    padding: 0.8rem 1.5rem;
    font-size: 0.9rem;
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

/* Data Privacy Notice Styles - Fixed positioning */
.privacy-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(15px);
  z-index: 10000;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  opacity: 0;
  visibility: hidden;
  transition: all 0.4s ease;
  padding: 20px;
  padding-top: 100px;
  overflow-y: auto;
}

.privacy-overlay.show {
  opacity: 1;
  visibility: visible;
}

.privacy-modal {
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  border-radius: 20px;
  box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
  max-width: 800px;
  width: 100%;
  max-height: calc(100vh - 140px);
  overflow-y: auto;
  position: relative;
  border: 2px solid rgba(27, 94, 32, 0.1);
  transform: scale(0.9) translateY(30px);
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  margin-top: 0;
}

.privacy-overlay.show .privacy-modal {
  transform: scale(1) translateY(0);
}

.privacy-header {
  background: linear-gradient(135deg, #1b5e20 0%, #4caf50 100%);
  color: white;
  padding: 1.5rem 2rem;
  border-radius: 18px 18px 0 0;
  display: flex;
  align-items: center;
  gap: 1rem;
  position: relative;
  overflow: hidden;
}

.privacy-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(45deg, 
    rgba(255, 255, 255, 0.1) 0%,
    transparent 50%,
    rgba(255, 255, 255, 0.1) 100%);
  z-index: 0;
}

.privacy-header > * {
  position: relative;
  z-index: 1;
}

.privacy-icon {
  font-size: 2rem;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.privacy-header h3 {
  flex: 1;
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.privacy-close {
  background: rgba(255, 255, 255, 0.2);
  border: 2px solid rgba(255, 255, 255, 0.3);
  color: white;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  font-size: 1.5rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(10px);
}

.privacy-close:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.1) rotate(90deg);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.privacy-content {
  padding: 2rem;
  line-height: 1.6;
  color: #2c3e50;
}

.privacy-section {
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: rgba(232, 245, 233, 0.3);
  border-radius: 12px;
  border-left: 4px solid #4caf50;
  transition: all 0.3s ease;
}

.privacy-section:hover {
  background: rgba(232, 245, 233, 0.5);
  transform: translateX(5px);
}

.privacy-section h4 {
  color: #1b5e20;
  font-size: 1.2rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.privacy-section h5 {
  color: #2e7d32;
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0 0 0.75rem 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.privacy-section p {
  margin: 0 0 0.5rem 0;
  color: #495057;
}

.privacy-section ul {
  margin: 0.5rem 0;
  padding-left: 1.5rem;
}

.privacy-section li {
  margin-bottom: 0.5rem;
  color: #495057;
}

.privacy-section li strong {
  color: #1b5e20;
  font-weight: 600;
}

.privacy-consent {
  background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
  border: 2px solid #ff8f00;
  border-radius: 12px;
  padding: 1.5rem;
  margin-top: 1rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.privacy-consent::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(45deg, 
    rgba(255, 193, 7, 0.1) 0%,
    transparent 50%,
    rgba(255, 193, 7, 0.1) 100%);
  z-index: 0;
}

.privacy-consent p {
  position: relative;
  z-index: 1;
  margin: 0;
  color: #856404;
  font-weight: 600;
  font-size: 1.05rem;
}

.privacy-footer {
  background: #f8f9fa;
  padding: 1.5rem 2rem;
  border-radius: 0 0 18px 18px;
  border-top: 1px solid rgba(27, 94, 32, 0.1);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  text-align: center;
}

.privacy-timer {
  color: #6c757d;
  font-size: 0.9rem;
  font-weight: 500;
}

.privacy-timer strong {
  color: #dc3545;
  font-weight: 700;
}

.privacy-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.btn-privacy-accept {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
  border: none;
  padding: 1rem 2rem;
  border-radius: 30px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
  position: relative;
  overflow: hidden;
}

.btn-privacy-accept::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transition: left 0.5s ease;
}

.btn-privacy-accept:hover::before {
  left: 100%;
}

.btn-privacy-accept:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 35px rgba(40, 167, 69, 0.4);
  background: linear-gradient(135deg, #34ce57 0%, #2dd4aa 100%);
}

.btn-privacy-accept:active {
  transform: translateY(-1px);
}

/* Responsive Design for Privacy Modal */
@media (max-width: 768px) {
  .privacy-overlay {
    padding: 10px;
    padding-top: 90px;
  }
  
  .privacy-modal {
    max-height: calc(100vh - 110px);
  }
  
  .privacy-header {
    padding: 1rem 1.5rem;
    flex-wrap: wrap;
  }
  
  .privacy-header h3 {
    font-size: 1.2rem;
  }
  
  .privacy-content {
    padding: 1.5rem;
  }
  
  .privacy-section {
    padding: 0.8rem;
  }
  
  .privacy-section h4 {
    font-size: 1.1rem;
  }
  
  .privacy-section h5 {
    font-size: 1rem;
  }
  
  .privacy-footer {
    padding: 1rem 1.5rem;
    flex-direction: column;
    text-align: center;
  }
  
  .btn-privacy-accept {
    width: 100%;
    padding: 1rem;
  }
}

@media (max-width: 480px) {
  .privacy-overlay {
    padding: 5px;
    padding-top: 80px;
  }
  
  .privacy-modal {
    max-height: calc(100vh - 90px);
  }
  
  .privacy-header {
    padding: 1rem;
  }
  
  .privacy-header h3 {
    font-size: 1.1rem;
  }
  
  .privacy-content {
    padding: 1rem;
  }
  
  .privacy-section {
    padding: 0.6rem;
    margin-bottom: 1rem;
  }
  
  .privacy-section h4 {
    font-size: 1rem;
  }
  
  .privacy-section h5 {
    font-size: 0.95rem;
  }
  
  .privacy-footer {
    padding: 1rem;
  }
  
  .privacy-timer {
    font-size: 0.8rem;
  }
}

/* Ensure content is blurred when privacy notice is shown */
.privacy-overlay.show ~ .container,
.privacy-overlay.show ~ * {
  filter: blur(3px);
  pointer-events: none;
}

body:has(.privacy-overlay.show) .container {
  filter: blur(3px);
  pointer-events: none;
}

/* Family Section Structure */
.family-section {
  display: flex;
  flex-direction: column;
  min-height: 500px;
  max-height: 600px;
}

.family-section .table-wrapper {
  flex: 1;
  min-height: 0;
  overflow: hidden;
}

/* Disability Section Structure */
.disability-section {
  display: flex;
  flex-direction: column;
  min-height: 400px;
  max-height: 500px;
}

.disability-wrapper {
  flex: 1;
  min-height: 0;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 16px;
  box-shadow: 0 8px 25px rgba(27, 94, 32, 0.05);
  margin: 1.5rem 0 0 0;
  border: 1px solid rgba(76, 175, 80, 0.1);
}

.disability-container {
  max-height: 350px;
  overflow-y: auto;
  padding: 1rem;
  scrollbar-width: thin;
  scrollbar-color: #4caf50 rgba(76, 175, 80, 0.1);
}

.disability-container::-webkit-scrollbar {
  width: 8px;
}

.disability-container::-webkit-scrollbar-track {
  background: rgba(76, 175, 80, 0.1);
  border-radius: 4px;
}

.disability-container::-webkit-scrollbar-thumb {
  background: #4caf50;
  border-radius: 4px;
}

/* Organization Section Structure */
.organization-section {
  display: flex;
  flex-direction: column;
  min-height: 400px;
  max-height: 500px;
}

.organization-wrapper {
  flex: 1;
  min-height: 0;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 16px;
  box-shadow: 0 8px 25px rgba(27, 94, 32, 0.05);
  margin: 1.5rem 0 0 0;
  border: 1px solid rgba(76, 175, 80, 0.1);
}

.organization-container {
  max-height: 350px;
  overflow-y: auto;
  padding: 1rem;
  scrollbar-width: thin;
  scrollbar-color: #4caf50 rgba(76, 175, 80, 0.1);
}

.organization-container::-webkit-scrollbar {
  width: 8px;
}

.organization-container::-webkit-scrollbar-track {
  background: rgba(76, 175, 80, 0.1);
  border-radius: 4px;
}

.organization-container::-webkit-scrollbar-thumb {
  background: #4caf50;
  border-radius: 4px;
}

/* Family Member Controls Styling */
.family-controls {
  display: flex;
  justify-content: center;
  padding: 1.5rem 0;
  margin-top: 1rem;
  border-top: 2px solid rgba(76, 175, 80, 0.1);
  position: relative;
  z-index: 10;
  background: white;
  width: 100%;
  clear: both;
  box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
  flex-shrink: 0;
}

.btn-add-family-member {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding: 1.2rem 2rem;
  background: linear-gradient(135deg, #4caf50, #2e7d32);
  color: white;
  border: none;
  border-radius: 20px;
  font-size: 1.1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 6px 20px rgba(76, 175, 80, 0.3);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  min-width: 220px;
  justify-content: center;
}

.btn-add-family-member:hover {
  background: linear-gradient(135deg, #66bb6a, #388e3c);
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
}

.btn-add-family-member:active {
  transform: translateY(-1px);
}

.btn-add-family-member .btn-icon {
  font-size: 1.3rem;
  background: rgba(255, 255, 255, 0.2);
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 900;
}

.btn-add-family-member .btn-text {
  text-align: left;
  line-height: 1.2;
}

.btn-add-family-member .btn-text small {
  display: block;
  font-weight: 500;
  font-size: 0.85rem;
  opacity: 0.9;
  font-style: italic;
  text-transform: none;
  letter-spacing: normal;
  margin-top: 0.2rem;
}

/* Remove Button Styling */
.btn-remove-row {
  background: linear-gradient(135deg, #e74c3c, #c0392b);
  color: white;
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  font-size: 1.2rem;
  font-weight: 900;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
  min-width: 40px;
}

.btn-remove-row:hover {
  background: linear-gradient(135deg, #c0392b, #a93226);
  transform: scale(1.1);
  box-shadow: 0 6px 18px rgba(231, 76, 60, 0.4);
}

.btn-remove-row:active {
  transform: scale(0.95);
}

/* Family Table Styling Enhancements */
.family-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.family-table th {
  background: linear-gradient(135deg, #1b5e20, #2e7d32);
  color: white;
  padding: 1.2rem 0.8rem;
  text-align: center;
  font-weight: 700;
  font-size: 0.95rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 2px solid rgba(255, 255, 255, 0.1);
}

.family-table th small {
  display: block;
  font-weight: 500;
  font-size: 0.8rem;
  opacity: 0.9;
  font-style: italic;
  text-transform: none;
  letter-spacing: normal;
  margin-top: 0.3rem;
}

.family-table td {
  padding: 0.8rem 0.5rem;
  text-align: center;
  border-bottom: 1px solid rgba(76, 175, 80, 0.1);
  vertical-align: middle;
}

.family-member-row {
  transition: all 0.3s ease;
}

.family-member-row:hover {
  background: rgba(76, 175, 80, 0.05);
  transform: translateY(-1px);
}

/* Enhanced scrollable name cell for family members */
.scrollable-name-cell {
  position: relative;
  max-width: 180px;
  min-width: 150px;
}

.scrollable-name-input {
  overflow-x: auto;
  white-space: nowrap;
  text-overflow: ellipsis;
  scrollbar-width: thin;
  scrollbar-color: #4caf50 rgba(76, 175, 80, 0.1);
}

.scrollable-name-input::-webkit-scrollbar {
  height: 4px;
}

.scrollable-name-input::-webkit-scrollbar-track {
  background: rgba(76, 175, 80, 0.1);
  border-radius: 2px;
}

.scrollable-name-input::-webkit-scrollbar-thumb {
  background: #4caf50;
  border-radius: 2px;
}

.scrollable-name-input:hover {
  overflow-x: auto;
}

/* Enhanced livelihood section visibility per project specifications */
.registration-form.readonly-mode .checkbox-group {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
  margin: 2rem 0;
  padding: 1.5rem;
  background: rgba(248, 250, 252, 0.9);
  border-radius: 16px;
  border: 3px solid rgba(46, 125, 50, 0.3);
  box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}

.registration-form.readonly-mode .checkbox-group label {
  transition: all 0.3s ease;
  position: relative;
  padding: 14px 20px 14px 45px;
  margin: 8px;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: default;
  display: inline-block;
  min-width: 180px;
  background: linear-gradient(135deg, #f5f5f5, #e8e8e8);
  border: 3px solid #bdbdbd;
  color: #424242;
  opacity: 0.6;
}

/* Enhanced visibility for selected options - Project Specification Compliant */
.registration-form.readonly-mode input[type="radio"]:checked + label,
.registration-form.readonly-mode input[type="checkbox"]:checked + label {
  background: linear-gradient(135deg, #4caf50, #66bb6a) !important;
  color: #ffffff !important;
  font-weight: 800 !important;
  border: 4px solid #2e7d32 !important;
  border-radius: 12px !important;
  padding: 16px 24px 16px 50px !important;
  box-shadow: 0 8px 24px rgba(76, 175, 80, 0.6) !important;
  text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5) !important;
  transform: scale(1.08) !important;
  opacity: 1 !important;
  z-index: 10;
}

/* Visible checkmark icons for selected options */
.registration-form.readonly-mode input[type="radio"]:checked + label::before {
  content: '✓';
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  background: #ffffff;
  color: #2e7d32;
  border-radius: 50%;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: 900;
  box-shadow: 0 3px 8px rgba(0,0,0,0.3);
  border: 2px solid #2e7d32;
}

.registration-form.readonly-mode input[type="checkbox"]:checked + label::before {
  content: '✓';
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  background: #ffffff;
  color: #2e7d32;
  border-radius: 6px;
  width: 26px;
  height: 26px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 900;
  box-shadow: 0 3px 8px rgba(0,0,0,0.3);
  border: 2px solid #2e7d32;
}

/* Keep radio buttons and checkboxes functional but visually hidden */
.registration-form.readonly-mode input[type="radio"],
.registration-form.readonly-mode input[type="checkbox"] {
  position: absolute;
  opacity: 0;
  width: 1px;
  height: 1px;
  margin: -1px;
  border: 0;
  padding: 0;
  clip: rect(0 0 0 0);
  overflow: hidden;
}
/* Enhanced styling for form elements with values */
.registration-form.readonly-mode input[value]:not([value=""]),
.registration-form.readonly-mode select[value]:not([value=""]),
.registration-form.readonly-mode textarea:not(:empty) {
  background: linear-gradient(135deg, #e8f5e9, #f1f8e9) !important;
  border: 3px solid #4caf50 !important;
  color: #1b5e20 !important;
  font-weight: 800 !important;
  box-shadow: 0 4px 16px rgba(76, 175, 80, 0.3) !important;
}

/* Enhanced table styling for family members */
.registration-form.readonly-mode .family-table {
  border: 3px solid #4caf50 !important;
  border-radius: 12px !important;
  overflow: hidden !important;
  box-shadow: 0 6px 20px rgba(76, 175, 80, 0.2) !important;
}

.registration-form.readonly-mode .family-table th {
  background: linear-gradient(135deg, #2e7d32, #4caf50) !important;
  color: #ffffff !important;
  font-weight: 800 !important;
  font-size: 1.05rem !important;
  padding: 16px 12px !important;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2) !important;
}

.registration-form.readonly-mode .family-table td {
  padding: 12px 10px !important;
  border-bottom: 1px solid rgba(76, 175, 80, 0.2) !important;
  background: rgba(255, 255, 255, 0.98) !important;
}

/* Kiosk-friendly table height management for family members */
.family-section {
  display: flex;
  flex-direction: column;
  min-height: 400px;
  max-height: 500px;
}

.table-wrapper {
  flex: 1;
  overflow-y: auto;
  overflow-x: auto;
  margin: 1rem 0;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.family-controls {
  flex-shrink: 0;
  padding: 1rem;
  background: rgba(248, 250, 252, 0.9);
  border-top: 2px solid rgba(76, 175, 80, 0.2);
  border-radius: 0 0 12px 12px;
}



.table-input {
  width: 100%;
  padding: 0.8rem;
  border: 2px solid rgba(76, 175, 80, 0.2);
  border-radius: 8px;
  font-size: 0.95rem;
  background: rgba(255, 255, 255, 0.9);
  transition: all 0.3s ease;
  min-width: 120px;
}

.table-input:focus {
  border-color: #4caf50;
  background: white;
  box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.15);
  outline: none;
  transform: translateY(-1px);
}

.table-input:hover {
  border-color: rgba(76, 175, 80, 0.4);
}

/* Responsive Design for Family Table */
@media (max-width: 1024px) {
  .family-section {
    min-height: 400px;
    max-height: 500px;
  }
  
  .disability-section,
  .organization-section {
    min-height: 350px;
    max-height: 450px;
  }
  
  .disability-container,
  .organization-container {
    max-height: 300px;
  }
  
  .table-wrapper {
    margin: 1rem 0 0 0;
  }
  
  .table-responsive {
    max-height: 300px;
  }
  
  .family-table th,
  .family-table td {
    padding: 0.6rem 0.4rem;
    font-size: 0.9rem;
  }
  
  .table-input {
    padding: 0.6rem;
    font-size: 0.9rem;
    min-width: 100px;
  }
  
  .table-input[type="email"] {
    min-width: 140px;
  }
  
  .btn-add-family-member {
    padding: 1rem 1.5rem;
    font-size: 1rem;
    min-width: 200px;
  }
  
  .btn-remove-row {
    width: 35px;
    height: 35px;
    font-size: 1rem;
  }
}

@media (max-width: 768px) {
  .family-section {
    min-height: 350px;
    max-height: 450px;
  }
  
  .disability-section,
  .organization-section {
    min-height: 300px;
    max-height: 400px;
  }
  
  .disability-container,
  .organization-container {
    max-height: 250px;
  }
  
  .disability-row,
  .organization-row {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .remove-group {
    justify-content: flex-end;
  }
  
  .btn-add-disability,
  .btn-add-organization {
    flex-direction: column;
    padding: 1rem;
    min-width: 180px;
    gap: 0.5rem;
  }
  
  .table-wrapper {
    margin: 1rem 0 0 0;
  }
  
  .table-responsive {
    max-height: 250px;
  }
  
  .family-controls {
    padding: 1rem 0;
  }
  
  .family-table {
    font-size: 0.85rem;
    overflow-x: auto;
    display: block;
    white-space: nowrap;
  }
  
  .family-table thead,
  .family-table tbody,
  .family-table th,
  .family-table td,
  .family-table tr {
    display: block;
  }
  
  .family-table thead tr {
    position: absolute;
    top: -9999px;
    left: -9999px;
  }
  
  .family-table tr {
    border: 1px solid #ccc;
    margin-bottom: 10px;
    border-radius: 8px;
    padding: 10px;
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }
  
  .family-table td {
    border: none;
    position: relative;
    padding: 8px 8px 8px 30%;
    text-align: left;
    white-space: normal;
  }
  
  .family-table td:before {
    content: attr(data-label);
    position: absolute;
    left: 6px;
    width: 25%;
    padding-right: 10px;
    white-space: nowrap;
    font-weight: bold;
    color: #1b5e20;
  }
  
  .table-input {
    padding: 0.5rem;
    font-size: 0.85rem;
    min-width: auto;
    width: 100%;
  }
  
  .btn-add-family-member {
    flex-direction: column;
    padding: 1rem;
    min-width: 180px;
    gap: 0.5rem;
  }
  
  .btn-add-family-member .btn-text small {
    margin-top: 0;
  }
  
  .btn-remove-row {
    width: 30px;
    height: 30px;
    font-size: 0.9rem;
    position: absolute;
    top: 10px;
    right: 10px;
  }
}

/* Modal Styles for Success and Error Messages */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(15px);
  z-index: 20000; /* Higher than privacy notice (10000) */
  display: flex;
  align-items: flex-start; /* Align to top instead of center */
  justify-content: center;
  opacity: 1;
  visibility: visible;
  padding: 20px;
  padding-top: 100px; /* Space below header */
}

.modal-content {
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  max-width: 800px; /* Increased from 600px */
  width: 90%; /* More responsive width */
  max-height: 80vh;
  overflow: hidden;
  position: relative;
  transform: scale(1);
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  animation: modalSlideIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes modalSlideIn {
  0% {
    transform: scale(0.8) translateY(50px);
    opacity: 0;
  }
  100% {
    transform: scale(1) translateY(0);
    opacity: 1;
  }
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 2rem 2.5rem; /* Increased padding */
  position: relative;
  overflow: hidden;
}

.modal-success .modal-header {
  background: linear-gradient(135deg, #28a745 0%, #4caf50 50%, #2e7d32 100%);
  color: white;
}

.modal-error .modal-header {
  background: linear-gradient(135deg, #dc3545 0%, #e74c3c 50%, #c82333 100%);
  color: white;
}

.modal-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(45deg, 
    rgba(255, 255, 255, 0.1) 0%,
    transparent 30%,
    rgba(255, 255, 255, 0.05) 50%,
    transparent 70%,
    rgba(255, 255, 255, 0.1) 100%);
  animation: shimmer 3s infinite;
}

@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

.modal-header > * {
  position: relative;
  z-index: 2;
}

.modal-header h4 {
  margin: 0;
  font-size: 1.8rem; /* Increased from 1.4rem */
  font-weight: 900; /* Increased from 700 for bolder text */
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  letter-spacing: 0.5px;
}

.modal-close {
  background: rgba(255, 255, 255, 0.15);
  border: 2px solid rgba(255, 255, 255, 0.3);
  color: white;
  width: 50px; /* Increased from 40px */
  height: 50px; /* Increased from 40px */
  border-radius: 50%;
  font-size: 1.8rem; /* Increased from 1.4rem */
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(10px);
}

.modal-close:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: scale(1.1) rotate(90deg);
  border-color: rgba(255, 255, 255, 0.5);
}

.modal-body {
  padding: 2.5rem; /* Increased from 2rem */
  color: #2c3e50;
  line-height: 1.8; /* Increased from 1.6 for better readability */
  font-size: 1.3rem; /* Increased from 1.1rem */
  font-weight: 600; /* Added bold font weight */
}

.modal-footer {
  padding: 2rem 2.5rem; /* Increased padding */
  background: rgba(248, 249, 250, 0.8);
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  text-align: center;
}

.auto-close-timer {
  color: #6c757d;
  font-size: 1.1rem; /* Increased from 0.95rem */
  font-weight: 700; /* Increased from 500 for bolder text */
}

.auto-close-timer span {
  font-weight: 900; /* Increased from 700 for extra bold countdown */
  color: #28a745;
  font-size: 1.2rem; /* Larger countdown number */
}

/* Responsive Design */
@media (max-width: 768px) {
  .modal-overlay {
    padding-top: 80px; /* Less space on mobile */
  }
  
  .modal-content {
    margin: 15px; /* Increased margin for larger modal */
    max-width: calc(100% - 30px); /* Adjusted for larger margin */
    width: 95%; /* Slightly wider on mobile */
  }
  
  .modal-header {
    padding: 1.5rem 2rem; /* Adjusted padding for mobile */
  }
  
  .modal-header h4 {
    font-size: 1.5rem; /* Slightly smaller on mobile but still large */
  }
  
  .modal-body {
    padding: 2rem; /* Adjusted padding for mobile */
    font-size: 1.2rem; /* Slightly smaller but still bold */
  }
  
  .modal-footer {
    padding: 1.5rem 2rem; /* Adjusted padding for mobile */
  }
  
  .modal-close {
    width: 45px; /* Slightly smaller on mobile */
    height: 45px;
    font-size: 1.6rem;
  }
}

@media (max-width: 480px) {
  .modal-overlay {
    padding-top: 60px; /* Even less space on very small screens */
  }
  
  .modal-content {
    width: 98%; /* Almost full width on very small screens */
  }
  
  .modal-header h4 {
    font-size: 1.3rem; /* Smaller but still prominent */
  }
  
  .modal-body {
    font-size: 1.1rem; /* Adjusted for very small screens */
  }
}

/* Hide privacy notice when modals are present */
body:has(.modal-overlay) #dataPrivacyOverlay,
.modal-overlay ~ #dataPrivacyOverlay {
  display: none !important;
}</style>

<?php if ($readonly): ?>
<script>
// Add readonly mode class and disable form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('censusForm');
    if (form) {
        form.classList.add('readonly-mode');
        
        // Prevent form submission in readonly mode
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            return false;
        });
        
        // Make sure tab navigation still works
        const tabBtns = form.querySelectorAll('.tab-btn');
        tabBtns.forEach(btn => {
            btn.style.pointerEvents = 'auto';
        });
        
        // Disable next/prev navigation in readonly mode
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        if (nextBtn) nextBtn.style.display = 'none';
        if (prevBtn) prevBtn.style.display = 'none';
    }
});
</script>
<?php endif; ?>

<script>
// Enhanced Data Privacy Notice Functionality
let privacyTimer;
let timeRemaining = 120; // 2 minutes in seconds

function startPrivacyTimer() {
  const timerElement = document.getElementById('privacyTimer');
  
  if (!timerElement) {
    console.log('Timer element not found');
    return;
  }
  
  console.log('Starting privacy timer...');
  
  privacyTimer = setInterval(() => {
    timeRemaining--;
    
    if (timeRemaining > 0) {
      timerElement.innerHTML = `⏱️ This notice will auto-close in <strong>${timeRemaining}</strong> seconds`;
      
      // Change color when time is running out
      if (timeRemaining <= 30) {
        timerElement.style.color = '#dc3545';
        timerElement.style.fontWeight = 'bold';
      } else if (timeRemaining <= 60) {
        timerElement.style.color = '#fd7e14';
      }
    } else {
      console.log('Timer reached zero, closing privacy notice');
      clearInterval(privacyTimer);
      closePrivacyNotice();
    }
  }, 1000);
}

function closePrivacyNotice() {
  console.log('Closing privacy notice...');
  
  const overlay = document.getElementById('dataPrivacyOverlay');
  
  if (!overlay) {
    console.log('Privacy overlay not found');
    return;
  }
  
  // Clear timer
  if (privacyTimer) {
    clearInterval(privacyTimer);
    privacyTimer = null;
  }
  
  // Animate out
  overlay.classList.remove('show');
  
  setTimeout(() => {
    overlay.style.display = 'none';
    
    // Re-enable scrolling on body
    document.body.style.overflow = 'auto';
    
    // Store in session to not show again for this session
    sessionStorage.setItem('privacyNoticeShown', 'true');
    
    // Remove blur effect from container
    const containers = document.querySelectorAll('.container');
    containers.forEach(container => {
      container.style.filter = 'none';
      container.style.pointerEvents = 'auto';
    });
    
    console.log('Privacy notice closed successfully');
  }, 400);
}

// Initialize privacy notice when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  console.log('DOM loaded, initializing privacy notice...');
  
  const overlay = document.getElementById('dataPrivacyOverlay');
  
  if (!overlay) {
    console.log('Privacy overlay element not found!');
    return;
  }
  
  // Check if this is an admin view - don't show privacy notice for admin views
  const isAdminView = <?php echo $admin_view ? 'true' : 'false'; ?>;
  console.log('Is admin view:', isAdminView);
  
  // Check if there are any success or error modals present
  const successModal = document.getElementById('successModal');
  const errorModal = document.getElementById('errorModal');
  const hasActiveModals = successModal || errorModal;
  
  console.log('Has active modals:', hasActiveModals);
  
  // Don't show privacy notice if there are active modals
  if (hasActiveModals) {
    console.log('Not showing privacy notice - active success/error modal present');
    overlay.style.display = 'none';
    // Mark privacy notice as shown since user is getting feedback
    sessionStorage.setItem('privacyNoticeShown', 'true');
    return;
  }
  
  // Reset privacy notice for testing - comment out for production
  // sessionStorage.removeItem('privacyNoticeShown');
  
  // Check if privacy notice was already shown in this session
  const privacyShown = sessionStorage.getItem('privacyNoticeShown');
  console.log('Privacy notice already shown:', privacyShown);
  
  if (privacyShown || isAdminView) {
    console.log('Not showing privacy notice - already shown or admin view');
    overlay.style.display = 'none';
    return;
  }
  
  console.log('Showing privacy notice...');
  
  // Prevent scrolling on body
  document.body.style.overflow = 'hidden';
  
  // Show the modal with animation
  overlay.style.display = 'flex';
  
  // Trigger the animation after a small delay
  setTimeout(() => {
    overlay.classList.add('show');
    
    // Start the countdown timer after modal is fully shown
    setTimeout(() => {
      startPrivacyTimer();
    }, 1000);
  }, 100);
});

// Close privacy notice when clicking on overlay background (not the modal)
document.addEventListener('click', function(e) {
  const overlay = document.getElementById('dataPrivacyOverlay');
  const modal = document.getElementById('dataPrivacyModal');
  
  if (overlay && overlay.classList.contains('show') && e.target === overlay) {
    console.log('Clicked on overlay background, closing privacy notice');
    closePrivacyNotice();
  }
});

// Close privacy notice with Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const overlay = document.getElementById('dataPrivacyOverlay');
    if (overlay && overlay.classList.contains('show')) {
      console.log('Escape key pressed, closing privacy notice');
      closePrivacyNotice();
    }
  }
});

// Prevent accidental closure by clicks inside the modal - wait for DOM
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('dataPrivacyModal');
  if (modal) {
    modal.addEventListener('click', function(e) {
      e.stopPropagation();
    });
  }
});

// Tab Navigation Functions
let currentTab = 1;
const totalTabs = 5;

function showTab(tabNumber) {
    console.log('Switching to tab:', tabNumber);
    
    // Hide all tab contents
    for (let i = 1; i <= totalTabs; i++) {
        const tabContent = document.getElementById(`tab-content-${i}`);
        const tabBtn = document.getElementById(`tab-${i}`);
        
        if (tabContent) {
            tabContent.classList.remove('active');
        }
        if (tabBtn) {
            tabBtn.classList.remove('active');
        }
    }
    
    // Show selected tab
    const activeTabContent = document.getElementById(`tab-content-${tabNumber}`);
    const activeTabBtn = document.getElementById(`tab-${tabNumber}`);
    
    if (activeTabContent) {
        activeTabContent.classList.add('active');
        console.log('Activated tab content:', tabNumber);
    } else {
        console.error('Tab content not found:', `tab-content-${tabNumber}`);
    }
    
    if (activeTabBtn) {
        activeTabBtn.classList.add('active');
        console.log('Activated tab button:', tabNumber);
    } else {
        console.error('Tab button not found:', `tab-${tabNumber}`);
    }
    
    currentTab = tabNumber;
    
    // Only update navigation if not in readonly mode
    const form = document.getElementById('censusForm');
    if (!form || !form.classList.contains('readonly-mode')) {
        updateNavigationButtons();
        updateProgressIndicator();
        updateSubmitButtonVisibility();
    } else {
        // In readonly mode, still update progress indicator
        updateProgressIndicator();
        console.log('Tab navigation in readonly mode - navigation buttons disabled');
    }
}

function updateProgressIndicator() {
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');
    
    if (progressFill) {
        const progressPercentage = (currentTab / totalTabs) * 100;
        progressFill.style.width = progressPercentage + '%';
    }
    
    if (progressText) {
        if (currentTab === totalTabs) {
            progressText.textContent = 'Ready to Submit!';
        } else {
            progressText.textContent = `Step ${currentTab} of ${totalTabs}`;
        }
    }
}

function changeTab(direction) {
    const newTab = currentTab + direction;
    
    if (newTab >= 1 && newTab <= totalTabs) {
        // Optional: Add basic validation before moving to next tab
        if (direction === 1 && !validateCurrentTab()) {
            // Show validation message if needed
            return;
        }
        
        showTab(newTab);
        
        // Mark completed tabs
        if (direction === 1) {
            const completedTabBtn = document.getElementById(`tab-${currentTab - 1}`);
            if (completedTabBtn) {
                completedTabBtn.classList.add('completed');
            }
        }
    }
}

function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    if (prevBtn) {
        prevBtn.style.display = currentTab === 1 ? 'none' : 'inline-flex';
    }
    
    if (nextBtn) {
        if (currentTab === totalTabs) {
            nextBtn.innerHTML = '✓ Submit Form<br><small>I-submit ang Census Form</small>';
            nextBtn.className = 'btn btn-success';
            nextBtn.onclick = function() {
                // Validate all tabs before submission
                if (validateAllTabs()) {
                    // Use the hidden submit button to maintain form functionality
                    const hiddenSubmit = document.getElementById('hiddenSubmitBtn');
                    if (hiddenSubmit) {
                        hiddenSubmit.click();
                    } else {
                        document.getElementById('censusForm').submit();
                    }
                } else {
                    alert('Please complete all required fields before submitting.');
                }
            };
        } else {
            nextBtn.innerHTML = 'Next →';
            nextBtn.className = 'btn btn-primary';
            nextBtn.onclick = function() { changeTab(1); };
        }
    }
}

function validateCurrentTab() {
    // Basic validation - can be expanded based on requirements
    const currentTabContent = document.getElementById(`tab-content-${currentTab}`);
    if (!currentTabContent) return true;
    
    const requiredFields = currentTabContent.querySelectorAll('input[required], select[required]');
    for (let field of requiredFields) {
        if (!field.value.trim()) {
            field.focus();
            field.style.borderColor = '#dc3545';
            setTimeout(() => {
                field.style.borderColor = '';
            }, 3000);
            return false;
        }
    }
    return true;
}

function validateAllTabs() {
    // Validate all tabs before final submission
    for (let i = 1; i <= totalTabs; i++) {
        const tabContent = document.getElementById(`tab-content-${i}`);
        if (tabContent) {
            const requiredFields = tabContent.querySelectorAll('input[required], select[required]');
            for (let field of requiredFields) {
                if (!field.value.trim()) {
                    showTab(i); // Jump to the tab with missing fields
                    field.focus();
                    field.style.borderColor = '#dc3545';
                    return false;
                }
            }
        }
    }
    return true;
}

// Initialize tab navigation when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if tab navigation is available
    const tabButtons = document.querySelectorAll('.tab-btn');
    const formElement = document.querySelector('.registration-form');
    const mainFormActions = document.getElementById('mainFormActions');
    
    if (tabButtons.length > 0) {
        // Tab navigation is available
        formElement.classList.add('tab-navigation-active');
        
        // Initialize first tab
        showTab(1);
        
        // Add keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey) {
                if (e.key === 'ArrowRight' && currentTab < totalTabs) {
                    e.preventDefault();
                    changeTab(1);
                } else if (e.key === 'ArrowLeft' && currentTab > 1) {
                    e.preventDefault();
                    changeTab(-1);
                }
            }
        });
        
        // Monitor tab changes to show/hide submit button
        updateSubmitButtonVisibility();
    } else {
        // No tab navigation, show submit button immediately
        formElement.classList.add('tab-navigation-inactive');
        if (mainFormActions) {
            mainFormActions.style.display = 'flex';
        }
    }
});

// Function to update submit button visibility based on current tab
function updateSubmitButtonVisibility() {
    const formElement = document.querySelector('.registration-form');
    const mainFormActions = document.getElementById('mainFormActions');
    
    if (currentTab === totalTabs) {
        formElement.classList.add('last-tab');
    } else {
        formElement.classList.remove('last-tab');
    }
}

// Fallback form submission function
function submitForm() {
    const form = document.getElementById('censusForm');
    if (form) {
        // Basic validation before submission
        if (validateAllTabs()) {
            form.submit();
        } else {
            alert('Please complete all required fields before submitting.');
        }
    }
}

// Add event listener for direct submit button clicks (non-JS fallback)
document.addEventListener('DOMContentLoaded', function() {
    const submitBtn = document.getElementById('hiddenSubmitBtn');
    const form = document.getElementById('censusForm');
    
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            // Only prevent default if using tab navigation
            const formElement = document.querySelector('.registration-form');
            if (formElement && formElement.classList.contains('tab-navigation-active')) {
                e.preventDefault();
                submitForm();
            }
            // Otherwise, let the normal form submission happen
        });
    }
});

// Your existing functions remain the same...
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

// Family Member Management Functions
function addFamilyMember() {
    const familyMembersBody = document.getElementById('familyMembersBody');
    if (!familyMembersBody) {
        console.error('Family members body not found');
        return;
    }
    
    // Create new row HTML
    const newRow = document.createElement('tr');
    newRow.className = 'family-member-row';
    newRow.innerHTML = `
        <td data-label="Name" class="scrollable-name-cell"><input type="text" name="familyName[]" class="table-input scrollable-name-input" placeholder="Pangalan"></td>
        <td data-label="Relationship">
            <select name="familyRelation[]" class="table-input">
                <option value="">Piliin ang Relasyon</option>
                <option value="Asawa">Asawa (Spouse)</option>
                <option value="Anak">Anak (Child)</option>
                <option value="Ama">Ama (Father)</option>
                <option value="Ina">Ina (Mother)</option>
                <option value="Kapatid">Kapatid (Sibling)</option>
                <option value="Lolo">Lolo (Grandfather)</option>
                <option value="Lola">Lola (Grandmother)</option>
                <option value="Apo">Apo (Grandchild)</option>
                <option value="Tiyahin">Tiyahin (Aunt)</option>
                <option value="Tiyuhin">Tiyuhin (Uncle)</option>
                <option value="Pamangkin">Pamangkin (Nephew/Niece)</option>
                <option value="Pinsan">Pinsan (Cousin)</option>
                <option value="Manugang">Manugang (Son/Daughter-in-law)</option>
                <option value="Biyenan">Biyenan (Parent-in-law)</option>
                <option value="Ninong">Ninong (Godfather)</option>
                <option value="Ninang">Ninang (Godmother)</option>
                <option value="Inaanak">Inaanak (Godchild)</option>
                <option value="Kasambahay">Kasambahay (Helper)</option>
                <option value="Boarder">Boarder</option>
                <option value="Iba pa">Iba pa (Others)</option>
            </select>
        </td>
        <td data-label="Age"><input type="number" name="familyAge[]" class="table-input" placeholder="Edad" min="0" max="120"></td>
        <td data-label="Gender">
            <select name="familyGender[]" class="table-input">
                <option value="">Piliin</option>
                <option value="Male">Lalaki (Male)</option>
                <option value="Female">Babae (Female)</option>
            </select>
        </td>
        <td data-label="Civil Status">
            <select name="familyCivilStatus[]" class="table-input">
                <option value="">Piliin</option>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Widowed">Widowed</option>
                <option value="Separated">Separated</option>
            </select>
        </td>
        <td data-label="Email"><input type="email" name="familyEmail[]" class="table-input" placeholder="email@example.com"></td>
        <td data-label="Occupation"><input type="text" name="familyOccupation[]" class="table-input" placeholder="Hanapbuhay"></td>
        <td data-label="Action"><button type="button" class="btn-remove-row" onclick="removeFamilyMember(this)" title="Remove this family member">✕</button></td>
    `;
    
    // Add animation class for smooth entry
    newRow.style.opacity = '0';
    newRow.style.transform = 'translateY(-10px)';
    
    // Add the row to the table
    familyMembersBody.appendChild(newRow);
    
    // Animate the new row in
    setTimeout(() => {
        newRow.style.transition = 'all 0.3s ease';
        newRow.style.opacity = '1';
        newRow.style.transform = 'translateY(0)';
        
        // Focus on the first input of the new row
        const firstInput = newRow.querySelector('input[name="familyName[]"]');
        if (firstInput) {
            firstInput.focus();
        }
    }, 10);
    
    // Update row count display and ensure controls remain visible
    updateFamilyMemberCount();
    
    // Force refresh of family controls visibility
    const familyControls = document.querySelector('.family-controls');
    if (familyControls) {
        familyControls.style.display = 'flex';
        familyControls.style.visibility = 'visible';
    }
}

function removeFamilyMember(button) {
    const row = button.closest('tr');
    const familyMembersBody = document.getElementById('familyMembersBody');
    
    if (!row || !familyMembersBody) {
        console.error('Cannot find row or family members body');
        return;
    }
    
    // Check if this is the last row - keep at least one row
    const allRows = familyMembersBody.querySelectorAll('.family-member-row');
    if (allRows.length <= 1) {
        // Instead of removing, just clear the values
        const inputs = row.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.value = '';
        });
        
        // Show a brief message
        showFamilyMessage('At least one family member row is required', 'info');
        return;
    }
    
    // Animate out
    row.style.transition = 'all 0.3s ease';
    row.style.opacity = '0';
    row.style.transform = 'translateX(-100%)';
    
    // Remove after animation
    setTimeout(() => {
        row.remove();
        updateFamilyMemberCount();
    }, 300);
}

function updateFamilyMemberCount() {
    const familyMembersBody = document.getElementById('familyMembersBody');
    const familyControls = document.querySelector('.family-controls');
    
    if (!familyMembersBody) return;
    
    const count = familyMembersBody.querySelectorAll('.family-member-row').length;
    
    // Ensure family controls are always visible
    if (familyControls) {
        familyControls.style.display = 'flex';
        familyControls.style.visibility = 'visible';
        familyControls.style.opacity = '1';
    }
    
    // Debug logging
    console.log(`Total family members: ${count}`);
    console.log('Family controls visible:', familyControls ? familyControls.style.display : 'not found');
}

function showFamilyMessage(message, type = 'info') {
    // Create a temporary message element
    const messageElement = document.createElement('div');
    messageElement.className = `family-message family-message-${type}`;
    messageElement.textContent = message;
    messageElement.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: ${type === 'info' ? '#17a2b8' : '#dc3545'};
        color: white;
        padding: 1rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        z-index: 10000;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        animation: fadeInOut 2s ease;
    `;
    
    // Add CSS animation
    if (!document.getElementById('familyMessageStyles')) {
        const styles = document.createElement('style');
        styles.id = 'familyMessageStyles';
        styles.textContent = `
            @keyframes fadeInOut {
                0%, 100% { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
                20%, 80% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
            }
        `;
        document.head.appendChild(styles);
    }
    
    document.body.appendChild(messageElement);
    
    // Remove after animation
    setTimeout(() => {
        if (messageElement && messageElement.parentNode) {
            messageElement.parentNode.removeChild(messageElement);
        }
    }, 2000);
}

// Disability Management Functions
function addDisability() {
    const disabilitySection = document.getElementById('disabilitySection');
    if (!disabilitySection) {
        console.error('Disability section not found');
        return;
    }
    
    // Create new disability row HTML
    const newRow = document.createElement('div');
    newRow.className = 'disability-row';
    newRow.innerHTML = `
        <div class="form-group">
            <label>Name:<br><small>Pangalan:</small></label>
            <input type="text" name="disabilityName[]" class="form-control" placeholder="Buong pangalan">
        </div>
        <div class="form-group">
            <label>Disability:<br><small>Kapansanan:</small></label>
            <input type="text" name="disabilityType[]" class="form-control" placeholder="Uri ng kapansanan">
        </div>
        <div class="form-group remove-group">
            <button type="button" class="btn-remove-disability" onclick="removeDisability(this)" title="Remove this disability entry">✕</button>
        </div>
    `;
    
    // Add animation class for smooth entry
    newRow.style.opacity = '0';
    newRow.style.transform = 'translateY(-10px)';
    
    // Add the row to the section
    disabilitySection.appendChild(newRow);
    
    // Animate the new row in
    setTimeout(() => {
        newRow.style.transition = 'all 0.3s ease';
        newRow.style.opacity = '1';
        newRow.style.transform = 'translateY(0)';
        
        // Focus on the first input of the new row
        const firstInput = newRow.querySelector('input[name="disabilityName[]"]');
        if (firstInput) {
            firstInput.focus();
        }
    }, 10);
    
    console.log('New disability entry added');
}

function removeDisability(button) {
    const row = button.closest('.disability-row');
    const disabilitySection = document.getElementById('disabilitySection');
    
    if (!row || !disabilitySection) {
        console.error('Cannot find row or disability section');
        return;
    }
    
    // Check if this is the last row - keep at least one row
    const allRows = disabilitySection.querySelectorAll('.disability-row');
    if (allRows.length <= 1) {
        // Instead of removing, just clear the values
        const inputs = row.querySelectorAll('input');
        inputs.forEach(input => {
            input.value = '';
        });
        
        showMessage('At least one disability row is required', 'info');
        return;
    }
    
    // Animate out
    row.style.transition = 'all 0.3s ease';
    row.style.opacity = '0';
    row.style.transform = 'translateX(-100%)';
    
    // Remove after animation
    setTimeout(() => {
        row.remove();
        console.log('Disability entry removed');
    }, 300);
}

// Organization Management Functions
function addOrganization() {
    const organizationSection = document.getElementById('organizationSection');
    if (!organizationSection) {
        console.error('Organization section not found');
        return;
    }
    
    // Create new organization row HTML
    const newRow = document.createElement('div');
    newRow.className = 'organization-row';
    newRow.innerHTML = `
        <div class="form-group">
            <label>Name:<br><small>Pangalan:</small></label>
            <input type="text" name="organizationName[]" class="form-control" placeholder="Buong pangalan">
        </div>
        <div class="form-group">
            <label>Organization Membership:<br><small>Samahang Kinaaniban:</small></label>
            <input type="text" name="organizationType[]" class="form-control" placeholder="Pangalan ng samahan/organisasyon">
        </div>
        <div class="form-group remove-group">
            <button type="button" class="btn-remove-organization" onclick="removeOrganization(this)" title="Remove this organization entry">✕</button>
        </div>
    `;
    
    // Add animation class for smooth entry
    newRow.style.opacity = '0';
    newRow.style.transform = 'translateY(-10px)';
    
    // Add the row to the section
    organizationSection.appendChild(newRow);
    
    // Animate the new row in
    setTimeout(() => {
        newRow.style.transition = 'all 0.3s ease';
        newRow.style.opacity = '1';
        newRow.style.transform = 'translateY(0)';
        
        // Focus on the first input of the new row
        const firstInput = newRow.querySelector('input[name="organizationName[]"]');
        if (firstInput) {
            firstInput.focus();
        }
    }, 10);
    
    console.log('New organization entry added');
}

function removeOrganization(button) {
    const row = button.closest('.organization-row');
    const organizationSection = document.getElementById('organizationSection');
    
    if (!row || !organizationSection) {
        console.error('Cannot find row or organization section');
        return;
    }
    
    // Check if this is the last row - keep at least one row
    const allRows = organizationSection.querySelectorAll('.organization-row');
    if (allRows.length <= 1) {
        // Instead of removing, just clear the values
        const inputs = row.querySelectorAll('input');
        inputs.forEach(input => {
            input.value = '';
        });
        
        showMessage('At least one organization row is required', 'info');
        return;
    }
    
    // Animate out
    row.style.transition = 'all 0.3s ease';
    row.style.opacity = '0';
    row.style.transform = 'translateX(-100%)';
    
    // Remove after animation
    setTimeout(() => {
        row.remove();
        console.log('Organization entry removed');
    }, 300);
}

// Generic message function for disabilities and organizations
function showMessage(message, type = 'info') {
    // Create a temporary message element
    const messageElement = document.createElement('div');
    messageElement.className = `message message-${type}`;
    messageElement.textContent = message;
    messageElement.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: ${type === 'info' ? '#17a2b8' : '#dc3545'};
        color: white;
        padding: 1rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        z-index: 10000;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        animation: fadeInOut 2s ease;
    `;
    
    // Add CSS animation if not exists
    if (!document.getElementById('messageStyles')) {
        const styles = document.createElement('style');
        styles.id = 'messageStyles';
        styles.textContent = `
            @keyframes fadeInOut {
                0%, 100% { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
                20%, 80% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
            }
        `;
        document.head.appendChild(styles);
    }
    
    document.body.appendChild(messageElement);
    
    // Remove after animation
    setTimeout(() => {
        if (messageElement && messageElement.parentNode) {
            messageElement.parentNode.removeChild(messageElement);
        }
    }, 2000);
}

// Real-time Validation Functions
let validationTimeout = null;

function validateEmail(email) {
    const emailInput = document.getElementById('email');
    const validationDiv = document.getElementById('emailValidation');
    
    // Clear previous timeout
    if (validationTimeout) {
        clearTimeout(validationTimeout);
    }
    
    // Reset states
    emailInput.classList.remove('validating', 'valid', 'invalid');
    validationDiv.classList.remove('show');
    
    // If email is empty, just clear validation
    if (!email || email.trim() === '') {
        return;
    }
    
    // Basic email format validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        emailInput.classList.add('invalid');
        showValidationMessage('emailValidation', 'Please enter a valid email format', 'error');
        return;
    }
    
    // Show loading state
    emailInput.classList.add('validating');
    showValidationMessage('emailValidation', 'Checking email availability...', 'loading');
    
    // Debounce the API call
    validationTimeout = setTimeout(() => {
        fetch('../api/validate_registration.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                type: 'email',
                value: email
            })
        })
        .then(response => response.json())
        .then(data => {
            emailInput.classList.remove('validating');
            
            if (data.valid) {
                emailInput.classList.add('valid');
                showValidationMessage('emailValidation', '✓ ' + data.message, 'success');
            } else {
                emailInput.classList.add('invalid');
                showValidationMessage('emailValidation', '✗ ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Email validation error:', error);
            emailInput.classList.remove('validating');
            emailInput.classList.add('invalid');
            showValidationMessage('emailValidation', 'Error checking email availability', 'error');
        });
    }, 500); // 500ms delay
}

function validateName() {
    const firstNameInput = document.getElementById('firstName');
    const middleNameInput = document.getElementById('middleName');
    const lastNameInput = document.getElementById('lastName');
    const validationDiv = document.getElementById('nameValidation');
    
    // Clear previous timeout
    if (validationTimeout) {
        clearTimeout(validationTimeout);
    }
    
    // Reset states
    [firstNameInput, middleNameInput, lastNameInput].forEach(input => {
        input.classList.remove('validating', 'valid', 'invalid');
    });
    validationDiv.classList.remove('show');
    
    const firstName = firstNameInput.value.trim();
    const middleName = middleNameInput.value.trim();
    const lastName = lastNameInput.value.trim();
    
    // Check if required fields are filled
    if (!firstName || !lastName) {
        if (!firstName) firstNameInput.classList.add('invalid');
        if (!lastName) lastNameInput.classList.add('invalid');
        showValidationMessage('nameValidation', 'First name and last name are required', 'error');
        return;
    }
    
    // Show loading state
    [firstNameInput, middleNameInput, lastNameInput].forEach(input => {
        input.classList.add('validating');
    });
    showValidationMessage('nameValidation', 'Checking name availability...', 'loading');
    
    // Debounce the API call
    validationTimeout = setTimeout(() => {
        fetch('../api/validate_registration.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                type: 'name',
                firstName: firstName,
                middleName: middleName,
                lastName: lastName
            })
        })
        .then(response => response.json())
        .then(data => {
            [firstNameInput, middleNameInput, lastNameInput].forEach(input => {
                input.classList.remove('validating');
            });
            
            if (data.valid) {
                [firstNameInput, middleNameInput, lastNameInput].forEach(input => {
                    input.classList.add('valid');
                });
                showValidationMessage('nameValidation', '✓ ' + data.message, 'success');
            } else {
                [firstNameInput, middleNameInput, lastNameInput].forEach(input => {
                    input.classList.add('invalid');
                });
                showValidationMessage('nameValidation', '✗ ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Name validation error:', error);
            [firstNameInput, middleNameInput, lastNameInput].forEach(input => {
                input.classList.remove('validating');
                input.classList.add('invalid');
            });
            showValidationMessage('nameValidation', 'Error checking name availability', 'error');
        });
    }, 800); // 800ms delay for name (longer since it checks multiple fields)
}

function showValidationMessage(elementId, message, type) {
    const validationDiv = document.getElementById(elementId);
    if (validationDiv) {
        validationDiv.textContent = message;
        validationDiv.className = `validation-message ${type} show`;
    }
}

// Initialize validation when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only add validation for non-readonly mode
    const isReadonly = <?php echo $readonly ? 'true' : 'false'; ?>;
    
    if (!isReadonly) {
        // Email validation
        const emailInput = document.getElementById('email');
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                validateEmail(this.value);
            });
            
            emailInput.addEventListener('blur', function() {
                if (this.value) {
                    validateEmail(this.value);
                }
            });
        }
        
        // Name validation
        const nameInputs = ['firstName', 'middleName', 'lastName'];
        nameInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('input', function() {
                    validateName();
                });
                
                input.addEventListener('blur', function() {
                    validateName();
                });
            }
        });
    }
});

// Modal Management Functions
let modalTimer;
let modalCountdown = 120; // 2 minutes in seconds

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        // Clear timer if it exists
        if (modalTimer) {
            clearInterval(modalTimer);
            modalTimer = null;
        }
        
        // Add fade out animation
        modal.style.opacity = '0';
        modal.style.transform = 'scale(0.8)';
        
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

function startModalTimer() {
    const countdownElement = document.getElementById('countdown');
    
    if (!countdownElement) {
        return;
    }
    
    modalTimer = setInterval(() => {
        modalCountdown--;
        countdownElement.textContent = modalCountdown;
        
        // Change color when time is running out
        if (modalCountdown <= 30) {
            countdownElement.style.color = '#dc3545';
            countdownElement.style.fontWeight = 'bold';
        } else if (modalCountdown <= 60) {
            countdownElement.style.color = '#fd7e14';
        }
        
        if (modalCountdown <= 0) {
            clearInterval(modalTimer);
            closeModal('successModal');
        }
    }, 1000);
}

// Initialize modal functionality when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if success modal exists and start timer
    const successModal = document.getElementById('successModal');
    if (successModal) {
        startModalTimer();
        
        // Hide privacy notice if it's showing
        const privacyOverlay = document.getElementById('dataPrivacyOverlay');
        if (privacyOverlay) {
            privacyOverlay.style.display = 'none';
            sessionStorage.setItem('privacyNoticeShown', 'true');
        }
    }
    
    // Check if error modal exists
    const errorModal = document.getElementById('errorModal');
    if (errorModal) {
        // Hide privacy notice if it's showing
        const privacyOverlay = document.getElementById('dataPrivacyOverlay');
        if (privacyOverlay) {
            privacyOverlay.style.display = 'none';
            sessionStorage.setItem('privacyNoticeShown', 'true');
        }
    }
    
    // Add click outside to close functionality
    const modals = document.querySelectorAll('.modal-overlay');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal(modal.id);
            }
        });
    });
    
    // Add escape key to close functionality
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.modal-overlay[style*="display: flex"], .modal-overlay:not([style*="display: none"])');
            openModals.forEach(modal => {
                if (modal.style.display !== 'none') {
                    closeModal(modal.id);
                }
            });
        }
    });
});

</script>

<?php include '../includes/footer.php'; ?>
