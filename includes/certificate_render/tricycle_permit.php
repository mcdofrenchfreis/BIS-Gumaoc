<?php

declare(strict_types=1);

require_once __DIR__ . '/../certificate_long_print.php';
require_once __DIR__ . '/../certificate_print_helpers.php';

$additional = cr_parse_additional_data($certificate_data);

$vehicle_make_type = $certificate_data['vehicle_make_type'] ?? ($additional['vehicle_make_type'] ?? '');
$motor_no = $certificate_data['motor_no'] ?? ($additional['motor_no'] ?? '');
$chassis_no = $certificate_data['chassis_no'] ?? ($additional['chassis_no'] ?? '');
$plate_no = $certificate_data['plate_no'] ?? ($additional['plate_no'] ?? '');
$vehicle_color = $certificate_data['vehicle_color'] ?? ($additional['vehicle_color'] ?? '');
$year_model = $certificate_data['year_model'] ?? ($additional['year_model'] ?? '');
$body_no = $certificate_data['body_no'] ?? ($additional['body_no'] ?? '');
$operator_license = $certificate_data['operator_license'] ?? ($additional['operator_license'] ?? '');

$photoSrc = cr_resolve_photo_path($pdo, $certificate_data, $request_id);
$tricyclePhotoSrc = '';
if (!empty($additional['tricycle_image'])) {
    $tricyclePhotoSrc = '../uploads/tricycle_photos/' . basename($additional['tricycle_image']);
}

$certificate_number = 'TP-' . str_pad((string) $request_id, 5, '0', STR_PAD_LEFT) . '-' . date('Y');
$meta = 'Certificate #' . htmlspecialchars($certificate_number) . ' | Request ID: ' . str_pad((string) $request_id, 5, '0', STR_PAD_LEFT);

clp_html_head('TRICYCLE PERMIT - ' . $certificate_data['full_name']);
clp_print_controls('view-certificate-requests.php', $meta, $embed);
clp_open_page();
clp_header();
clp_doc_title(['MOTORIZE TRICYCLE OPERATORS', 'PERMIT CERTIFICATION']);
clp_open_body();
?>
            <div class="cert-row-photo">
                <div class="cert-col-text">
                    <div class="cert-salutation">TO WHOM IT MAY CONCERN,</div>
                    <p class="cert-p">This is to certify that <strong><?php echo htmlspecialchars($certificate_data['full_name']); ?></strong>
                        a resident of <strong><?php echo htmlspecialchars($certificate_data['address']); ?></strong>
                        Gumaoc East, City of San Jose Del Monte, Bulacan and a legitimate owner of one unit of tricycle described as follows:</p>

                    <div class="cert-detail-line"><span class="label">Make and Type</span><span class="colon">:</span><span class="value"><span class="underline"><?php echo htmlspecialchars($vehicle_make_type); ?></span></span></div>
                    <div class="cert-detail-line"><span class="label">Motor No.</span><span class="colon">:</span><span class="value"><span class="underline"><?php echo htmlspecialchars($motor_no); ?></span></span></div>
                    <div class="cert-detail-line"><span class="label">Chassis No.</span><span class="colon">:</span><span class="value"><span class="underline"><?php echo htmlspecialchars($chassis_no); ?></span></span></div>
                    <div class="cert-detail-line"><span class="label">Plate No.</span><span class="colon">:</span><span class="value"><span class="underline"><?php echo htmlspecialchars($plate_no); ?></span></span></div>
                    <?php if ($vehicle_color !== '') { ?>
                    <div class="cert-detail-line"><span class="label">Vehicle Color</span><span class="colon">:</span><span class="value"><span class="underline"><?php echo htmlspecialchars($vehicle_color); ?></span></span></div>
                    <?php } ?>
                    <?php if ($year_model !== '' && $year_model !== null) { ?>
                    <div class="cert-detail-line"><span class="label">Year Model</span><span class="colon">:</span><span class="value"><span class="underline"><?php echo htmlspecialchars((string) $year_model); ?></span></span></div>
                    <?php } ?>
                    <?php if ($body_no !== '') { ?>
                    <div class="cert-detail-line"><span class="label">Body No.</span><span class="colon">:</span><span class="value"><span class="underline"><?php echo htmlspecialchars($body_no); ?></span></span></div>
                    <?php } ?>
                    <?php if ($operator_license !== '') { ?>
                    <div class="cert-detail-line"><span class="label">Operator's License No.</span><span class="colon">:</span><span class="value"><span class="underline"><?php echo htmlspecialchars($operator_license); ?></span></span></div>
                    <?php } ?>

                    <?php if ($tricyclePhotoSrc !== '') { ?>
                    <div style="margin-top:0.1in;display:flex;align-items:center;gap:10px;">
                        <span style="font-weight:bold;">Vehicle photo:</span>
                        <span class="cert-photo-box" style="width:0.85in;height:0.85in;"><img src="<?php echo htmlspecialchars($tricyclePhotoSrc); ?>" alt=""></span>
                    </div>
                    <?php } ?>
                </div>
                <div class="cert-photo-box">
                    <img src="<?php echo htmlspecialchars($photoSrc); ?>" alt="">
                </div>
            </div>

            <p class="cert-p">This certification is issued upon the request of the subject person, for all legal intents and purposes it may serve him/her best.</p>
            <p class="cert-p">Issued this <strong><?php echo strtoupper(date('d')); ?></strong> day of <strong><?php echo strtoupper(date('F Y')); ?></strong>
                at the Office of the Punong Barangay of Gumaoc East, City of San Jose Del Monte, Bulacan.</p>

            <div class="cert-tricycle-signoff">
                <div class="cert-tricycle-signoff-label">CERTIFIED BY:</div>
                <?php clp_punong_barangay_block(); ?>
            </div>

            <div class="cert-note-red-box">NOTE: Valid only for six (6) months from the date of issuance.</div>
<?php
clp_close_body();
clp_close_page();
clp_html_foot(false);
