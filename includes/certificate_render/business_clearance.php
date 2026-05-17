<?php

declare(strict_types=1);

require_once __DIR__ . '/../business_clearance_print.php';

if (!isset($application) || !is_array($application)) {
    throw new RuntimeException('Business application data is required.');
}

$clearance_number = $clearance_number ?? ('BBC-' . date('Y') . '-' . str_pad((string) $request_id, 4, '0', STR_PAD_LEFT));
$current_date = $current_date ?? date('F j, Y');
$valid_until = $valid_until ?? date('F j, Y', strtotime('+1 year'));
$embed = $embed ?? false;

$browserTitle = 'Barangay Business Clearance - ' . ($application['business_name'] ?? '');

bcp_html_head($browserTitle);
bcp_print_controls('view-business-applications.php', $embed);
bcp_open_page();
?>
            <h1 class="bcp-doc-title">Barangay Business Clearance</h1>

            <div class="bcp-salutation">TO WHOM IT MAY CONCERN:</div>

            <p class="bcp-intro">This is to certify that the business or trade activity described below</p>

            <div class="bcp-details">
                <div class="bcp-detail-row">
                    <div class="bcp-detail-value"><?php echo htmlspecialchars($application['business_name'] ?? ''); ?></div>
                    <div class="bcp-detail-label">(Business Name)</div>
                </div>
                <div class="bcp-detail-row">
                    <div class="bcp-detail-value"><?php echo htmlspecialchars($application['business_address'] ?? ''); ?></div>
                    <div class="bcp-detail-label">(Business Location)</div>
                </div>
                <div class="bcp-detail-row">
                    <div class="bcp-detail-value"><?php echo htmlspecialchars($application['owner_name'] ?? ''); ?></div>
                    <div class="bcp-detail-label">(President/Owner)</div>
                </div>
                <div class="bcp-detail-row">
                    <div class="bcp-detail-value"><?php echo htmlspecialchars($application['owner_address'] ?? ''); ?></div>
                    <div class="bcp-detail-label">(Address of Owner/Manager)</div>
                </div>
                <div class="bcp-detail-row">
                    <div class="bcp-detail-value"><?php echo htmlspecialchars($application['business_type'] ?? ''); ?></div>
                    <div class="bcp-detail-label">(Nature of Business)</div>
                </div>
            </div>

            <div class="bcp-legal">
                <p>Proposed to be established in this Barangay and is being applied for a Barangay Business Clearance to be used in securing a corresponding Mayor's Permit has been found to be in conformity with the provisions of existing Barangay Ordinances, rule and regulations being enforced in this Barangay.</p>
                <p>In view of the foregoing, the undersigned interposes no objection for the issuance of the corresponding Mayor's Permit being applied for.</p>
                <p>This permit shall be valid until <?php echo htmlspecialchars($valid_until); ?> and can be cancelled/revoked anytime the establishment is found to have violated any law or ordinance within this Barangay.</p>
            </div>

            <div class="bcp-issuance">
                <p>Issued on <?php echo htmlspecialchars($current_date); ?> at Gumaoc East, City of San Jose Del Monte, Bulacan.</p>
                <p class="bcp-not-valid">NOT VALID WITHOUT DRY SEAL</p>
            </div>

            <div class="bcp-certification">
                <div class="bcp-certified-by">Certified By:</div>
                <div class="bcp-sig-line"></div>
                <div class="bcp-official-name">HON. ROMMEL B. PITALBO</div>
                <div class="bcp-official-title">Punong Barangay</div>
            </div>
<?php
bcp_close_page();
bcp_html_foot();
