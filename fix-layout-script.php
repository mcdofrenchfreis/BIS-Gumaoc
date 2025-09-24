<?php
// Script to fix layout issues in resident-registration.php

// Path to the file
$file_path = __DIR__ . '/pages/resident-registration.php';

// Read the file content
$content = file_get_contents($file_path);

// Create a backup
file_put_contents($file_path . '.bak2', $content);

// Fix 1: Update the new family member template section
$old_section = <<<'EOD'
                    <!-- Disability, Organization, and Deceased Information in a wider grid -->
                    <div class="form-grid disability-org-grid">
                      <!-- Disability Information - Full width -->
                      <div class="form-group" style="grid-column: span 2;">
                        <label>Disability (if applicable)<br><small>Kapansanan (kung mayroon)</small></label>
                        <input type="text" name="disabilityType[]" class="form-control" placeholder="Type of disability">
                      </div>
                      
                      <!-- Organization Membership - Full width -->
                      <div class="form-group" style="grid-column: span 2;">
                        <label>Organization Membership (if applicable)<br><small>Samahang Kinaaniban (kung mayroon)</small></label>
                        <input type="text" name="organizationType[]" class="form-control" placeholder="Organization name">
                      </div>
                      
                      <!-- Checkboxes in a single row -->
                      <div class="form-group checkbox-container" style="display: flex; justify-content: space-between; grid-column: span 2;">
                        <!-- Deceased Status Checkbox -->
                        <label class="checkbox-label" style="display: flex; align-items: center; cursor: pointer; margin-right: 20px;">
                          <input type="checkbox" name="isDeceased[]" class="deceased-checkbox" style="margin-right: 8px; width: 18px; height: 18px;">
                          <span style="font-weight: normal;">Deceased<br><small>Namatay</small></span>
                        </label>
                        
                        <!-- Has Account Checkbox -->
                        <label class="checkbox-label" style="display: flex; align-items: center; cursor: pointer;">
                          <input type="checkbox" name="hasAccount[]" class="has-account-checkbox" style="margin-right: 8px; width: 18px; height: 18px;">
                          <span style="font-weight: normal;">Already has an account<br><small>May account na</small></span>
                        </label>
                      </div>
                    </div>
EOD;

$new_section = <<<'EOD'
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
EOD;

// Replace the old section with the new section
$content = str_replace($old_section, $new_section, $content);

// Write the updated content back to the file
file_put_contents($file_path, $content);

echo "Layout fixes applied successfully to resident-registration.php\n";
echo "A backup has been created at resident-registration.php.bak2\n";
?>