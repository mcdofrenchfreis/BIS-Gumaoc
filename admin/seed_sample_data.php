<?php
// admin/seed_sample_data.php
// Generates sample data: 3 records per certificate/request type used by pages/certificate-request.php
// Safety: requires confirm=1 to execute. Optionally pass count (default 3)

session_start();
require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: text/html; charset=utf-8');

$confirm = isset($_GET['confirm']) && $_GET['confirm'] === '1';
$count = isset($_GET['count']) ? max(1, (int)$_GET['count']) : 3;
$useRandomResidents = !empty($_GET['use_random_residents']);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$types = [
  'BRGY. CLEARANCE',
  'BRGY. INDIGENCY',
  'TRICYCLE PERMIT',
  'PROOF OF RESIDENCY',
  'CEDULA/CTC'
];

$results = [
  'certificate_requests' => [],
  'cedula_request' => [],
  'business_applications' => []
];

$now = date('Y-m-d H:i:s');

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Seed Sample Data</title>
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 24px; color: #222; }
    .card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin-bottom: 16px; }
    .btn { display: inline-block; padding: 10px 14px; background: #2563eb; color: #fff; border-radius: 6px; text-decoration: none; }
    .btn:hover { background: #1d4ed8; }
    code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; }
    .ok { color: #16a34a; }
    .fail { color: #dc2626; }
    ul { line-height: 1.6; }
  </style>
</head>
<body>
  <h1>Seed Sample Data</h1>
  <div class="card">
    <p>This tool will insert sample rows into the database:</p>
    <ul>
      <li><strong>certificate_requests</strong>: <?php echo h($count); ?> per type for: BRGY. CLEARANCE, BRGY. INDIGENCY, TRICYCLE PERMIT, PROOF OF RESIDENCY, CEDULA/CTC</li>
      <li><strong>cedula_request</strong>: for each CEDULA/CTC row created</li>
      <li><strong>business_applications</strong>: <?php echo h($count); ?> sample rows (not part of certificate_requests flow)</li>
    </ul>
    <?php if (!$confirm): ?>
      <p>Nothing has been inserted yet. To proceed, click the button below.</p>
      <a class="btn" href="?confirm=1&amp;count=<?php echo h($count); ?>">Insert Sample Data</a>
      <p style="margin-top:10px;">Change count with <code>?confirm=1&amp;count=5</code> to insert 5 per type.</p>
      <p style="margin-top:6px;">If you already have residents and want to link records to them, add <code>&amp;use_random_residents=1</code>.</p>
    <?php endif; ?>
  </div>

<?php if ($confirm): ?>
  <div class="card">
    <h2>Running Inserts…</h2>
    <ul>
<?php
try {
    $pdo->beginTransaction();

    // Optionally fetch resident IDs to avoid FK issues
    $residentIds = [];
    if ($useRandomResidents) {
        try {
            $rs = $pdo->query("SELECT id FROM residents ORDER BY id DESC LIMIT 500");
            $residentIds = $rs ? $rs->fetchAll(PDO::FETCH_COLUMN, 0) : [];
        } catch (Throwable $e) {
            $residentIds = [];
        }
    }

    // Helper to insert into certificate_requests
    $certSql = $pdo->prepare("INSERT INTO certificate_requests (
      user_id, full_name, address, mobile_number, civil_status, gender,
      birth_date, birth_place, citizenship, years_of_residence,
      certificate_type, purpose,
      vehicle_make_type, motor_no, chassis_no, plate_no, vehicle_color, year_model, body_no, operator_license, tricycle_photo,
      status, submitted_at
    ) VALUES (
      :user_id, :full_name, :address, :mobile_number, :civil_status, :gender,
      :birth_date, :birth_place, :citizenship, :years_of_residence,
      :certificate_type, :purpose,
      :vehicle_make_type, :motor_no, :chassis_no, :plate_no, :vehicle_color, :year_model, :body_no, :operator_license, :tricycle_photo,
      'pending', NOW()
    )");

    // Seed per certificate type
    foreach ($types as $type) {
        for ($i=1; $i <= $count; $i++) {
            // Choose a valid user_id if available, otherwise NULL (guest submission)
            $userId = null;
            if (!empty($residentIds)) {
                $userId = (int)$residentIds[array_rand($residentIds)];
            }
            $fullName = $type . " Tester " . $i;
            $address = "Blk " . (10 + $i) . ", Lot " . (20 + $i) . ", Gumaoc East, SJDM";
            $mobile = "+63917" . str_pad((string)rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
            $civil = ($i % 2 === 0) ? 'Married' : 'Single';
            $gender = ($i % 2 === 0) ? 'Female' : 'Male';
            $birthDate = date('Y-m-d', strtotime('-' . (20 + $i) . ' years'));
            $birthPlace = 'SJDM, Bulacan';
            $citizenship = 'Filipino';
            $yearsResidence = 3 + $i;
            $purpose = ($type === 'BRGY. CLEARANCE') ? 'Employment requirement' : (($type === 'BRGY. INDIGENCY') ? 'Financial aid' : 'General purpose');

            $vehicleMake = null; $motorNo = null; $chassisNo = null; $plateNo = null; $color = null; $yearModel = null; $bodyNo = null; $opLic = null; $trikePhoto = null;
            if ($type === 'TRICYCLE PERMIT') {
                $vehicleMake = 'Honda TMX-155';
                $motorNo = 'MTR-' . rand(100000,999999);
                $chassisNo = 'CHS-' . rand(100000,999999);
                $plateNo = 'GD' . rand(100,999) . 'A';
                $color = (['Red','Blue','Green','Black'][$i % 4]);
                $yearModel = (int)date('Y') - (1 + ($i % 3));
                $bodyNo = 'B-' . rand(10,99);
                $opLic = (string)rand(100000000, 999999999);
                $trikePhoto = null; // keep null for seed
            }

            $certSql->execute([
                ':user_id' => $userId,
                ':full_name' => $fullName,
                ':address' => $address,
                ':mobile_number' => $mobile,
                ':civil_status' => $civil,
                ':gender' => $gender,
                ':birth_date' => $birthDate,
                ':birth_place' => $birthPlace,
                ':citizenship' => $citizenship,
                ':years_of_residence' => $yearsResidence,
                ':certificate_type' => $type,
                ':purpose' => $purpose,
                ':vehicle_make_type' => $vehicleMake,
                ':motor_no' => $motorNo,
                ':chassis_no' => $chassisNo,
                ':plate_no' => $plateNo,
                ':vehicle_color' => $color,
                ':year_model' => $yearModel,
                ':body_no' => $bodyNo,
                ':operator_license' => $opLic,
                ':tricycle_photo' => $trikePhoto,
            ]);

            $rid = (int)$pdo->lastInsertId();
            $results['certificate_requests'][] = $rid;

            if ($type === 'CEDULA/CTC') {
                // Insert into cedula_request
                $ced = $pdo->prepare("INSERT INTO cedula_request (
                  certificate_request_id, cedula_year, place_of_issue, date_issued,
                  cedula_citizenship, cedula_place_of_birth, cedula_date_of_birth, cedula_civil_status,
                  profession_occupation, height, weight, basic_tax_type, basic_community_tax,
                  gross_receipts_business, salaries_profession, income_real_property, total_tax, interest, total_amount_paid
                ) VALUES (
                  :rid, :cedula_year, :place_of_issue, :date_issued,
                  :cedula_citizenship, :cedula_place_of_birth, :cedula_date_of_birth, :cedula_civil_status,
                  :profession_occupation, :height, :weight, :basic_tax_type, :basic_community_tax,
                  :gross_receipts_business, :salaries_profession, :income_real_property, :total_tax, :interest, :total_amount_paid
                )");

                $ced->execute([
                    ':rid' => $rid,
                    ':cedula_year' => (int)date('Y'),
                    ':place_of_issue' => 'San Jose Del Monte City, Bulacan',
                    ':date_issued' => date('Y-m-d'),
                    ':cedula_citizenship' => 'Filipino',
                    ':cedula_place_of_birth' => 'SJDM, Bulacan',
                    ':cedula_date_of_birth' => $birthDate,
                    ':cedula_civil_status' => $civil,
                    ':profession_occupation' => ($i % 2 === 0) ? 'Teacher' : 'Driver',
                    ':height' => 160 + ($i % 20),
                    ':weight' => 50 + ($i % 30),
                    ':basic_tax_type' => 'voluntary',
                    ':basic_community_tax' => 5.00,
                    ':gross_receipts_business' => 150000.00 + ($i * 1000),
                    ':salaries_profession' => 80000.00 + ($i * 500),
                    ':income_real_property' => 30000.00 + ($i * 250),
                    ':total_tax' => 250.00 + $i,
                    ':interest' => 0.00,
                    ':total_amount_paid' => 255.00 + $i,
                ]);
                $results['cedula_request'][] = $rid;
            }
        }
    }

    // Seed Business Applications (not in certificate_requests)
    $bizIns = $pdo->prepare("INSERT INTO business_applications (
      user_id, reference_no, application_date, first_name, middle_name, last_name,
      business_name, business_type, business_address, business_location,
      owner_name, owner_address, contact_number, or_number, ctc_number,
      years_operation, investment_capital, status, submitted_at
    ) VALUES (
      :user_id, :reference_no, :application_date, :first_name, :middle_name, :last_name,
      :business_name, 'General Business', :business_address, :business_location,
      :owner_name, :owner_address, :contact_number, :or_number, :ctc_number,
      :years_operation, :investment_capital, 'pending', NOW()
    )");

    // No attachments seeding: values are already stored in business_applications per current schema

    for ($i=1; $i <= $count; $i++) {
        // Choose a valid user_id if available, otherwise NULL
        $bizUserId = null;
        if (!empty($residentIds)) {
            $bizUserId = (int)$residentIds[array_rand($residentIds)];
        }
        $ref = 'BA-' . date('Y') . '-' . str_pad((string)$i, 4, '0', STR_PAD_LEFT);
        $bizIns->execute([
            ':user_id' => $bizUserId,
            ':reference_no' => $ref,
            ':application_date' => date('Y-m-d'),
            ':first_name' => 'Owner' . $i,
            ':middle_name' => 'M',
            ':last_name' => 'Lastname' . $i,
            ':business_name' => 'Sample Business ' . $i,
            ':business_address' => 'Blk ' . (5 + $i) . ', Lot ' . (7 + $i) . ', Gumaoc East',
            ':business_location' => 'Gumaoc East, SJDM',
            ':owner_name' => 'Owner' . $i . ' M Lastname' . $i,
            ':owner_address' => 'Blk ' . (5 + $i) . ', Lot ' . (7 + $i) . ', Gumaoc East',
            ':contact_number' => '+639' . rand(100000000, 999999999),
            ':or_number' => 'OR-' . rand(100000,999999),
            ':ctc_number' => 'CTC-' . rand(100000,999999),
            ':years_operation' => 1 + ($i % 5),
            ':investment_capital' => 50000 + ($i * 1000),
        ]);
        $bid = (int)$pdo->lastInsertId();
        $results['business_applications'][] = $bid;

        // Attachments skipped intentionally
    }

    $pdo->commit();
    echo '<li class="ok">Insert completed successfully.</li>';
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo '<li class="fail">Insert failed: ' . h($e->getMessage()) . '</li>';
}
?>
    </ul>
  </div>
  <div class="card">
    <h2>Summary</h2>
    <p><strong>certificate_requests</strong>: inserted IDs = <?php echo h(implode(', ', $results['certificate_requests'])); ?></p>
    <p><strong>cedula_request</strong>: linked certificate_request_ids = <?php echo h(implode(', ', $results['cedula_request'])); ?></p>
    <p><strong>business_applications</strong>: inserted IDs = <?php echo h(implode(', ', $results['business_applications'])); ?></p>
  </div>
<?php endif; ?>

</body>
</html>
