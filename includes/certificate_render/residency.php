<?php

declare(strict_types=1);

require_once __DIR__ . '/../certificate_long_print.php';
require_once __DIR__ . '/../certificate_print_helpers.php';

$formatted_birth_date = cr_format_birth_date($certificate_data);
$certificate_number = 'RES-' . str_pad((string) $request_id, 5, '0', STR_PAD_LEFT) . '-' . date('Y');
$photoSrc = cr_resolve_photo_path($pdo, $certificate_data, $request_id);
$yearsRes = !empty($certificate_data['years_of_residence'])
    ? $certificate_data['years_of_residence'] . ' years'
    : '';
$meta = 'Certificate #' . htmlspecialchars($certificate_number) . ' | Request ID: ' . str_pad((string) $request_id, 5, '0', STR_PAD_LEFT);

clp_html_head('CERTIFICATION OF RESIDENCY - ' . $certificate_data['full_name']);
clp_print_controls('view-certificate-requests.php', $meta, $embed);
clp_open_page();
clp_header();
clp_doc_title('CERTIFICATION OF RESIDENCY');
clp_open_body();
?>
            <div class="cert-row-photo">
                <div class="cert-col-text">
                    <div class="cert-salutation">TO WHOM IT MAY CONCERN,</div>
                    <p class="cert-p">This is to certify that the person whose name, signature, thumb marks, and other
                        personal data appearing hereon, has requested for a Certification of Residency from this
                        Office and the results are listed below.</p>

                    <div class="cert-detail-line"><span class="label">NAME</span><span class="colon">:</span><span class="value"><?php echo htmlspecialchars($certificate_data['full_name']); ?></span></div>
                    <div class="cert-detail-line"><span class="label">ADDRESS</span><span class="colon">:</span><span class="value"><?php echo htmlspecialchars($certificate_data['address']); ?></span></div>
                    <div class="cert-detail-line"><span class="label">DATE OF BIRTH</span><span class="colon">:</span><span class="value"><?php echo htmlspecialchars($formatted_birth_date); ?></span></div>
                    <div class="cert-detail-line"><span class="label">PLACE OF BIRTH</span><span class="colon">:</span><span class="value"><?php echo htmlspecialchars($certificate_data['birth_place']); ?></span></div>
                    <div class="cert-detail-line"><span class="label">YEAR OF RESIDENCY</span><span class="colon">:</span><span class="value"><?php echo htmlspecialchars($yearsRes); ?></span></div>
                    <div class="cert-detail-line"><span class="label">PURPOSE</span><span class="colon">:</span><span class="value"><?php echo htmlspecialchars($certificate_data['purpose']); ?></span></div>

                    <p class="cert-p cert-p--after-details">This is to further certify that he/she is a bonafide resident of this Barangay.</p>
                    <p class="cert-p">This certification is issued upon the request of the above-named person for whatever legal
                        purpose and intents it is deemed necessary.</p>
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
