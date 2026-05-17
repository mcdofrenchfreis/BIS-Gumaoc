<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/business_application_status.php';

business_application_ensure_status_schema($pdo);
$ba_status_labels = business_application_status_labels();

// Require admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo 'Invalid request ID.';
    exit;
}

$stmt = $pdo->prepare(
    "SELECT 
        ba.*, 
        r.address AS resident_address,
        r.phone AS resident_phone,
        r.civil_status AS resident_civil_status,
        r.gender AS resident_gender,
        r.birthdate AS resident_birthdate,
        r.birth_place AS resident_birth_place
     FROM business_applications ba
     LEFT JOIN residents r ON ba.user_id = r.id
     WHERE ba.id = ?"
);
$stmt->execute([$id]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$app) {
    http_response_code(404);
    echo 'Application not found.';
    exit;
}

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$full_name = trim(($app['first_name'] ?? '') . ' ' . (($app['middle_name'] ?? '') ? ($app['middle_name'] . ' ') : '') . ($app['last_name'] ?? ''));
if ($full_name === '') { $full_name = $app['owner_name'] ?? ''; }
$business_location = $app['business_location'] ?: ($app['business_address'] ?? '');
$contact = ($app['contact_number'] ?? '') === '09000000000' ? '' : ($app['contact_number'] ?? ($app['resident_phone'] ?? ''));

// Compute birthdate + age
$birthdate = $app['resident_birthdate'] ?? null;
$birth_age_text = '';
if (!empty($birthdate)) {
  try {
    $dob = new DateTime($birthdate);
    $now = new DateTime();
    $age = $dob->diff($now)->y;
    $birth_age_text = $dob->format('F j, Y') . ' (Age ' . $age . ')';
  } catch (Exception $e) { $birth_age_text = (string)$birthdate; }
}

// Try to load attachments (CTC and Business certificate)
$ctcImg = '';
$certImg = '';
try {
  $attachmentStmt = $pdo->prepare("SELECT ctc_image, certificate_image FROM business_attachments WHERE business_id = ? LIMIT 1");
  if ($attachmentStmt->execute([(int)$app['id']])) {
    $att = $attachmentStmt->fetch(PDO::FETCH_ASSOC);
    if ($att) { $ctcImg = $att['ctc_image'] ?? ''; $certImg = $att['certificate_image'] ?? ''; }
  }
} catch (Exception $e) {
  // Table may not exist; fallback via filesystem
}

$uploadDir = realpath(__DIR__ . '/../assets/uploads/business_applications');
if ($uploadDir !== false && is_dir($uploadDir)) {
  $ref = $app['reference_no'] ?? '';
  if ($ref) {
    if (empty($ctcImg)) {
      foreach (glob($uploadDir . DIRECTORY_SEPARATOR . 'ctc_' . $ref . '*') as $p) { $ctcImg = basename($p); break; }
    }
    if (empty($certImg)) {
      foreach (glob($uploadDir . DIRECTORY_SEPARATOR . 'cert_' . $ref . '*') as $p) { $certImg = basename($p); break; }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Business Application Summary - <?php echo h($full_name ?: ('#' . $app['id'])); ?></title>
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    .summary-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem;
      background: #f8f9fa;
      min-height: 100vh;
      box-sizing: border-box;
    }
    .summary-header {
      background: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%);
      color: #fff;
      padding: 1.5rem 2rem;
      border-radius: 12px;
      margin-bottom: 2rem;
      display: grid;
      grid-template-columns: auto 1fr auto;
      align-items: center;
      gap: 1rem 1.25rem;
      box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    .back-btn {
      background: #ffffff;
      color: #2e7d32;
      text-decoration: none;
      padding: 0.45rem 0.85rem;
      border-radius: 8px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.12);
      transition: transform 0.08s ease, box-shadow 0.2s ease;
    }
    .back-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(0,0,0,0.16); }
    .title-block h1 {
      margin: 0 0 0.25rem 0;
      font-size: 1.4rem;
      line-height: 1.2;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      text-shadow: 0 1px 1px rgba(0,0,0,0.2);
    }
    .title-block p { margin: 0; opacity: 0.95; font-size: 0.95rem; }
    .requester { display:flex; align-items:center; gap:0.5rem; white-space:nowrap; }
    .requester h2 { margin: 0; font-size: 1.15rem; font-weight: 700; text-shadow: 0 1px 1px rgba(0,0,0,0.2); }
    .status-form { display:flex; gap:0.5rem; align-items:center; margin-left: 0.75rem; }
    .action-select { padding: 0.4rem 0.6rem; border: 2px solid #e9ecef; border-radius: 6px; font-size: 0.9rem; }
    .summary-section { background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); }
    .section-title { color: #2e7d32; border-bottom: 2px solid #e9ecef; padding-bottom: 0.5rem; margin-bottom: 1.5rem; font-size: 1.3rem; }
    .info-grid { display: grid; grid-template-columns: 1fr; column-gap: 1rem; row-gap: 0.45rem; grid-auto-flow: row dense; margin-bottom: 0.5rem; }
    .info-item { margin: 0; }
    .info-grid > .info-item:not(:first-child) { margin-top: 0.15rem; }
    .info-item.full-row { grid-column: 1 / -1; display: block; }
    .info-label { font-weight: 600; color: #495057; margin: 0; font-size: 0.9rem; }
    .info-value { font-size: 1rem; color: #212529; padding: 0.35rem 0.5rem; background: #f8f9fa; border-radius: 6px; border-left: 3px solid #4CAF50; width: 100%; box-sizing: border-box; display: block; margin: 0; }
    .info-item.full-row .info-label { margin: 0 0 2px; }
    .info-item.full-row .info-value { width: 100%; margin-top: 0; }
    .badge { display:inline-block; padding:0.3rem 0.6rem; border-radius:20px; font-size:0.8rem; font-weight:600; }
    .status-pending { background:#fff3cd; color:#856404; }
    .status-ready { background:#e1bee7; color:#6a1b9a; }
    .status-received { background:#c8e6c9; color:#1b5e20; }
    .status-reviewing { background:#cce7ff; color:#004085; }
    .status-approved { background:#d4edda; color:#155724; }
    .status-rejected { background:#f8d7da; color:#721c24; }
    .actions { display:flex; gap:1rem; margin-top: 1rem; align-items:center; flex-wrap:wrap; }
    .print-btn { background: linear-gradient(135deg, #4CAF50, #45a049); color:#fff; border:none; padding:0.6rem 1rem; border-radius:8px; cursor:pointer; font-weight:600; }
    .scroll-top-btn { position: fixed; right: 20px; bottom: 20px; width: 44px; height: 44px; border-radius: 50%; border: none; background: #2e7d32; color: #fff; box-shadow: 0 6px 18px rgba(0,0,0,0.2); cursor: pointer; display: none; align-items: center; justify-content: center; font-size: 20px; z-index: 9999; }
    .scroll-top-btn.show { display: flex; }
    @media (max-width: 768px) {
      .summary-container { padding: 1rem; }
      .summary-header { padding: 1rem; }
      .info-grid { grid-template-columns: 1fr; }
      .info-label { margin: 0 0 2px; }
    }
    @media print { .actions, .back-btn { display:none !important; } .summary-container{ background:#fff; padding:0; } }
  </style>
</head>
<body>
  <div class="summary-container">
    <div class="summary-header">
      <a href="view-business-applications.php" class="back-btn" onclick="if (window.history.length > 1) { history.back(); return false; } return true;">← Back</a>
      <div class="title-block">
        <h1>🏢 Business Application Summary</h1>
        <p>
          <strong>ID:</strong> #<?php echo h($app['id']); ?>
          &nbsp;|&nbsp;
          <strong>Submitted:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($app['submitted_at'])); ?>
        </p>
      </div>
      <div class="requester">
        <h2><?php echo h($full_name ?: ($app['business_name'] ?? 'Business Application')); ?></h2>
        <span class="badge status-<?php echo h($app['status']); ?>"><?php echo h(business_application_status_label($app['status'])); ?></span>
        <form method="POST" action="view-business-applications.php" class="status-form">
          <input type="hidden" name="action" value="update_status">
          <input type="hidden" name="id" value="<?php echo (int)$app['id']; ?>">
          <label for="statusSelectHeader" class="info-label" style="color:#fff; opacity:0.95;">Status:</label>
          <select id="statusSelectHeader" name="status" class="action-select" onchange="this.form.submit()">
            <?php foreach ($ba_status_labels as $value => $label): ?>
            <option value="<?php echo h($value); ?>" <?php echo $app['status'] === $value ? 'selected' : ''; ?>>
              <?php echo h($label); ?>
            </option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>
    </div>

    <div class="summary-details">
      <div class="summary-section">
        <h3 class="section-title">Personal Information</h3>
        <div class="info-grid">
          <div class="info-item"><div class="info-label">First Name:</div><div class="info-value"><?php echo h($app['first_name'] ?? ''); ?></div></div>
          <div class="info-item"><div class="info-label">Middle Name:</div><div class="info-value"><?php echo h($app['middle_name'] ?? ''); ?></div></div>
          <div class="info-item"><div class="info-label">Last Name:</div><div class="info-value"><?php echo h($app['last_name'] ?? ''); ?></div></div>
          <div class="info-item"><div class="info-label">Address:</div><div class="info-value"><?php echo h(($app['owner_address'] ?? '') ?: ($app['resident_address'] ?? '')); ?></div></div>
          <div class="info-item"><div class="info-label">Mobile Number:</div><div class="info-value"><?php echo h($contact ?: '—'); ?></div></div>
          <div class="info-item"><div class="info-label">Civil Status:</div><div class="info-value"><?php echo h($app['resident_civil_status'] ?? ''); ?></div></div>
          <div class="info-item"><div class="info-label">Gender:</div><div class="info-value"><?php echo h($app['resident_gender'] ?? ''); ?></div></div>
          <div class="info-item"><div class="info-label">Birthdate and Age:</div><div class="info-value"><?php echo h($birth_age_text ?: ''); ?></div></div>
          <div class="info-item"><div class="info-label">Birthplace:</div><div class="info-value"><?php echo h($app['resident_birth_place'] ?? ''); ?></div></div>
        </div>
      </div>

      <div class="summary-section">
        <h3 class="section-title">Business Information</h3>
        <div class="info-grid">
          <div class="info-item"><div class="info-label">Business Name:</div><div class="info-value"><?php echo h($app['business_name'] ?? ''); ?></div></div>
          <div class="info-item"><div class="info-label">Business Type:</div><div class="info-value"><?php echo h($app['business_type'] ?? ''); ?></div></div>
          <div class="info-item full-row"><div class="info-label">Location/Address:</div><div class="info-value"><?php echo h($business_location); ?></div></div>
        </div>
      </div>

      <div class="summary-section">
        <h3 class="section-title">Document Details</h3>
        <div class="info-grid">
          <div class="info-item"><div class="info-label">Application Date:</div><div class="info-value"><?php echo h($app['application_date'] ?? ''); ?></div></div>
          <div class="info-item"><div class="info-label">Reference No.:</div><div class="info-value"><?php echo h($app['reference_no'] ?? ''); ?></div></div>
          <div class="info-item"><div class="info-label">OR Number:</div><div class="info-value"><?php echo h($app['or_number'] ?? ''); ?></div></div>
          <div class="info-item"><div class="info-label">CTC Number:</div><div class="info-value"><?php echo h($app['ctc_number'] ?? ''); ?></div></div>
          <div class="info-item"><div class="info-label">CTC Image:</div><div class="info-value"><?php echo $ctcImg ? ('<a href="../assets/uploads/business_applications/' . h($ctcImg) . '" target="_blank">View CTC Image</a>') : '—'; ?></div></div>
          <div class="info-item"><div class="info-label">Business Certificate Image:</div><div class="info-value"><?php echo $certImg ? ('<a href="../assets/uploads/business_applications/' . h($certImg) . '" target="_blank">View Certificate Image</a>') : '—'; ?></div></div>
          <div class="info-item"><div class="info-label">Proof Image:</div><div class="info-value"><?php echo !empty($app['proof_image']) ? ('<a href="../assets/uploads/business_applications/' . h($app['proof_image']) . '" target="_blank">View Proof Image</a>') : '—'; ?></div></div>
          <div class="info-item"><div class="info-label">Submitted At:</div><div class="info-value"><?php echo date('F j, Y g:i A', strtotime($app['submitted_at'])); ?></div></div>
        </div>
      </div>

      
    </div>
  </div>
  <button id="scrollTopBtn" class="scroll-top-btn" title="Back to top">↑</button>
  <script>
    (function(){
      var btn = document.getElementById('scrollTopBtn');
      function onScroll(){ if (window.scrollY > 200) { btn.classList.add('show'); } else { btn.classList.remove('show'); } }
      window.addEventListener('scroll', onScroll, { passive: true });
      btn.addEventListener('click', function(){ window.scrollTo({ top: 0, behavior: 'smooth' }); });
      onScroll();
    })();
  </script>
</body>
</html>
