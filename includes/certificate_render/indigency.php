<?php

declare(strict_types=1);

require_once __DIR__ . '/../certificate_long_print.php';
require_once __DIR__ . '/../certificate_print_helpers.php';

$age = cr_calculate_age($certificate_data);
$certificate_number = 'IND-' . str_pad((string) $request_id, 5, '0', STR_PAD_LEFT) . '-' . date('Y');
$photoSrc = cr_resolve_photo_path($pdo, $certificate_data, $request_id);
$meta = 'Certificate #' . htmlspecialchars($certificate_number) . ' | Request ID: ' . str_pad((string) $request_id, 5, '0', STR_PAD_LEFT);

clp_html_head('BRGY. INDIGENCY Certificate - ' . $certificate_data['full_name']);
clp_print_controls('view-certificate-requests.php', $meta, $embed);
clp_open_page();
clp_header();
clp_doc_title('CERTIFICATION OF INDIGENCY');
clp_open_body();
?>
            <div class="cert-row-photo">
                <div class="cert-col-text">
                    <div class="cert-salutation">TO WHOM IT MAY CONCERN:</div>
                    <p class="cert-p">This is to certify that <strong><?php echo htmlspecialchars($certificate_data['full_name']); ?></strong>,
                        <?php echo (int) $age; ?> years old, with Address at No. <strong><?php echo htmlspecialchars($certificate_data['address']); ?></strong>,
                        Barangay Gumaoc East, City of San Jose Del Monte, Bulacan, is belonging to the Indigent Family in our Barangay.</p>
                    <p class="cert-p">This certification is issued upon the request of the above person to be used for his/her
                        <strong><?php echo htmlspecialchars($certificate_data['purpose']); ?></strong>.</p>
                    <p class="cert-p">Given this <strong><?php echo date('jS'); ?></strong> day of <strong><?php echo date('F Y'); ?></strong>
                        at Gumaoc East, City of San Jose Del Monte, Bulacan, Philippines.</p>
                </div>
                <div class="cert-photo-box">
                    <img src="<?php echo htmlspecialchars($photoSrc); ?>" alt="">
                </div>
            </div>

            <div class="cert-footer-grid">
                <div class="cert-footer-left">
                    <?php clp_verified_officer_block(); ?>
                    <?php clp_dates_block(); ?>
                    <?php clp_admin_block(); ?>
                </div>
                <div class="cert-footer-right">
                    <?php clp_applicant_signature_block(); ?>
                    <?php clp_thumb_marks_block(); ?>
                    <?php clp_punong_barangay_block(); ?>
                </div>
            </div>
            <p class="cert-note-red">NOT VALID WITHOUT DRY SEAL</p>
<?php
clp_close_body();
clp_close_page();
clp_html_foot(false);
