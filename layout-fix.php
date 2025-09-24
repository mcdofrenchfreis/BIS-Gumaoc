<?php
// This is a layout fix for the resident-registration.php file
?>

<!-- Disability and Organization in a single row -->
<div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
  <!-- Disability Information -->
  <div class="form-group">
    <label>Disability (if applicable)<br><small>Kapansanan (kung mayroon)</small></label>
    <input type="text" name="disabilityType[]" class="form-control" placeholder="Type of disability">
  </div>
  
  <!-- Organization Membership -->
  <div class="form-group">
    <label>Organization Membership (if applicable)<br><small>Samahang Kinaaniban (kung mayroon)</small></label>
    <input type="text" name="organizationType[]" class="form-control" placeholder="Organization name">
  </div>
</div>

<!-- Checkboxes in a single row with better spacing -->
<div class="form-group checkbox-container" style="display: flex; justify-content: flex-start; gap: 30px; margin-top: 10px;">
  <!-- Deceased Status Checkbox -->
  <label class="checkbox-label" style="display: flex; align-items: center; cursor: pointer;">
    <input type="checkbox" name="isDeceased[]" class="deceased-checkbox" style="margin-right: 8px; width: 18px; height: 18px;">
    <span style="font-weight: normal;">Deceased<br><small>Namatay</small></span>
  </label>
  
  <!-- Has Account Checkbox -->
  <label class="checkbox-label" style="display: flex; align-items: center; cursor: pointer;">
    <input type="checkbox" name="hasAccount[]" class="has-account-checkbox" style="margin-right: 8px; width: 18px; height: 18px;">
    <span style="font-weight: normal;">Already has an account<br><small>May account na</small></span>
  </label>
</div>