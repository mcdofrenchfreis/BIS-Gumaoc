<?php
// admin/get-certificate-summary.php
// Returns an HTML summary for a certificate request ID

header('Content-Type: text/html; charset=UTF-8');
$standalone = isset($_GET['standalone']) && $_GET['standalone'] == '1';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo '<p style="color:#dc3545;">Invalid certificate request ID.</p>';
    exit;
}

require_once __DIR__ . '/../includes/db_connect.php';

function esc($v) {
    return htmlspecialchars((string)$v ?? '', ENT_QUOTES, 'UTF-8');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM certificate_requests WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$r) {
        echo '<p style="color:#dc3545;">Certificate request not found.</p>';
        exit;
    }

    // Parse additional_data JSON if present
    $extra = [];
    if (!empty($r['additional_data'])) {
        $decoded = json_decode($r['additional_data'], true);
        if (is_array($decoded)) {
            $extra = $decoded;
        }
    }

    // If CEDULA, try to load from cedula_request table
    $cedula_row = null;
    $certType = strtoupper(trim((string)$r['certificate_type']));
    if ($certType === 'CEDULA' || $certType === 'CEDULA/CTC') {
        try {
            $cs = $pdo->prepare("SELECT * FROM cedula_request WHERE certificate_request_id = :rid LIMIT 1");
            $cs->execute([':rid' => $r['id']]);
            $cedula_row = $cs->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $ignored) {
            // Table may not exist yet; ignore and rely on additional_data
        }
    }

    $certType = strtoupper(trim((string)$r['certificate_type']));

    // Start building summary HTML using existing admin CSS classes
    ob_start();
?>
<div class="summary-container">
  <div class="summary-header">
    <a href="view-certificate-requests.php" class="back-btn" style="text-decoration:none;background:#e9ecef;color:#212529;padding:0.4rem 0.8rem;border-radius:8px;font-weight:600;display:inline-flex;align-items:center;gap:0.4rem" onclick="if (window.history.length > 1) { history.back(); return false; } return true;">‹ Back</a>
    <span class="status-badge status-<?= esc($r['status']) ?>"><?php echo ucfirst($r['status']); ?></span>
    <div style="color:#666;">
      Submitted: <?= esc(date('M j, Y g:i A', strtotime($r['submitted_at']))) ?>
    </div>
  </div>

  <div class="summary-details">
    <div class="summary-section">
      <h3>General Information</h3>
      <div class="summary-row">
        <div class="summary-label">Certificate Type:</div>
        <div class="summary-value"><?= esc($r['certificate_type']) ?></div>
      </div>
      <div class="summary-row">
        <div class="summary-label">Purpose:</div>
        <div class="summary-value"><?= esc($r['purpose'] ?? 'N/A') ?></div>
      </div>
    </div>

    <div class="summary-section">
      <h3>Personal Information</h3>
      <div class="summary-row">
        <div class="summary-label">Full Name:</div>
        <div class="summary-value"><?= esc($r['full_name']) ?></div>
      </div>
      <div class="summary-row">
        <div class="summary-label">Gender:</div>
        <div class="summary-value"><?= esc($r['gender'] ?? 'N/A') ?></div>
      </div>
      <div class="summary-row">
        <div class="summary-label">Civil Status:</div>
        <div class="summary-value"><?= esc($r['civil_status'] ?? 'N/A') ?></div>
      </div>
      <div class="summary-row">
        <div class="summary-label">Birth Date:</div>
        <div class="summary-value"><?= esc($r['birth_date'] ? date('M j, Y', strtotime($r['birth_date'])) : 'N/A') ?></div>
      </div>
      <div class="summary-row">
        <div class="summary-label">Birth Place:</div>
        <div class="summary-value"><?= esc($r['birth_place'] ?? 'N/A') ?></div>
      </div>
      <div class="summary-row">
        <div class="summary-label">Address:</div>
        <div class="summary-value"><?= esc($r['address'] ?? 'N/A') ?></div>
      </div>
      <div class="summary-row">
        <div class="summary-label">Mobile Number:</div>
        <div class="summary-value"><?= esc($r['mobile_number'] ?? 'N/A') ?></div>
      </div>
      <div class="summary-row">
        <div class="summary-label">Citizenship:</div>
        <div class="summary-value"><?= esc($r['citizenship'] ?? 'N/A') ?></div>
      </div>
      <div class="summary-row">
        <div class="summary-label">Years of Residence:</div>
        <div class="summary-value"><?= esc($r['years_of_residence'] ?? 'N/A') ?></div>
      </div>
    </div>

<?php if ($certType === 'TRICYCLE PERMIT'): ?>
    <div class="summary-section">
      <h3>Tricycle Details</h3>
      <div class="summary-row">
        <div class="summary-label">Make and Type:</div>
        <div class="summary-value"><?= esc($r['vehicle_make_type'] ?? 'N/A') ?></div>
      </div>
      <div class="summary-row">
        <div class="summary-label">Motor No:</div>
        <div class="summary-value"><?= esc($r['motor_no'] ?? 'N/A') ?></div>
      </div>
      <div class="summary-row">
        <div class="summary-label">Chassis No:</div>
        <div class="summary-value"><?= esc($r['chassis_no'] ?? 'N/A') ?></div>
      </div>
      <div class="summary-row">
        <div class="summary-label">Plate No:</div>
        <div class="summary-value"><?= esc($r['plate_no'] ?? 'N/A') ?></div>
      </div>
      <div class="summary-row">
        <div class="summary-label">Vehicle Color:</div>
        <div class="summary-value"><?= esc($r['vehicle_color'] ?? 'N/A') ?></div>
      </div>
      <div class="summary-row">
        <div class="summary-label">Year Model:</div>
        <div class="summary-value"><?= esc($r['year_model'] ?? 'N/A') ?></div>
      </div>
      <div class="summary-row">
        <div class="summary-label">Body No:</div>
        <div class="summary-value"><?= esc($r['body_no'] ?? 'N/A') ?></div>
      </div>
      <div class="summary-row">
        <div class="summary-label">Operator's License:</div>
        <div class="summary-value"><?= esc($r['operator_license'] ?? 'N/A') ?></div>
      </div>
      <div class="summary-row">
        <div class="summary-label">Tricycle Photo:</div>
        <div class="summary-value"><?php if (!empty($r['tricycle_photo'])): ?>
          <a href="../assets/uploads/tricycle_photos/<?= esc($r['tricycle_photo']) ?>" target="_blank">View Photo</a>
        <?php else: ?>No photo uploaded<?php endif; ?></div>
      </div>
    </div>
<?php endif; ?>

<?php
// Cedula/CTC details are likely saved in additional_data JSON
if ($certType === 'CEDULA' || $certType === 'CEDULA/CTC'):
    // Helper to fetch from cedula_request first, then additional_data
    $cedula = function($key, $fallback = 'N/A') use ($cedula_row, $extra) {
        if (is_array($cedula_row) && array_key_exists($key, $cedula_row) && $cedula_row[$key] !== null && $cedula_row[$key] !== '') {
            return $cedula_row[$key];
        }
        if (isset($extra[$key]) && $extra[$key] !== '') return $extra[$key];
        // Try camelCase equivalent from additional_data
        $camel = preg_replace_callback('/_([a-z])/', function($m){ return strtoupper($m[1]); }, $key);
        if (isset($extra[$camel]) && $extra[$camel] !== '') return $extra[$camel];
        return $fallback;
    };
?>
    <div class="summary-section">
      <h3>Cedula/CTC Issuance Information</h3>
      <div class="summary-row"><div class="summary-label">Year:</div><div class="summary-value"><?= esc($cedula('cedula_year')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Place of Issue:</div><div class="summary-value"><?= esc($cedula('place_of_issue')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Date Issued:</div><div class="summary-value"><?= esc($cedula('date_issued')) ?></div></div>
    </div>

    <div class="summary-section">
      <h3>Personal Details for Cedula</h3>
      <div class="summary-row"><div class="summary-label">Citizenship:</div><div class="summary-value"><?= esc($cedula('cedula_citizenship')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Place of Birth:</div><div class="summary-value"><?= esc($cedula('cedula_place_of_birth')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Date of Birth:</div><div class="summary-value"><?= esc($cedula('cedula_date_of_birth')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Civil Status:</div><div class="summary-value"><?= esc($cedula('cedula_civil_status')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Profession/Occupation/Business:</div><div class="summary-value"><?= esc($cedula('profession_occupation')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Height (cm):</div><div class="summary-value"><?= esc($cedula('height')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Weight (kg):</div><div class="summary-value"><?= esc($cedula('weight')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Amount:</div><div class="summary-value"><?= esc($cedula('amount')) ?></div></div>
    </div>

    <div class="summary-section">
      <h3>Community Tax Due</h3>
      <div class="summary-row"><div class="summary-label">Basic Community Tax Type:</div><div class="summary-value"><?= esc($cedula('basic_tax_type')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Basic Community Tax:</div><div class="summary-value">₱<?= esc($cedula('basic_community_tax')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Gross Receipts/Earnings from Business:</div><div class="summary-value">₱<?= esc($cedula('gross_receipts_business')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Salaries/Gross Receipts from Profession:</div><div class="summary-value">₱<?= esc($cedula('salaries_profession')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Income from Real Property:</div><div class="summary-value">₱<?= esc($cedula('income_real_property')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Total Tax:</div><div class="summary-value">₱<?= esc($cedula('total_tax')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Interest:</div><div class="summary-value">₱<?= esc($cedula('interest')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Total Amount Paid:</div><div class="summary-value">₱<?= esc($cedula('total_amount_paid')) ?></div></div>
    </div>
<?php endif; ?>

<?php
// Business Application details likely saved in additional_data as well
if ($certType === 'BUSINESS APPLICATION'):
    $biz = function($key, $fallback = 'N/A') use ($extra) {
        return isset($extra[$key]) && $extra[$key] !== '' ? $extra[$key] : $fallback;
    };
?>
    <div class="summary-section">
      <h3>Business Application Details</h3>
      <div class="summary-row"><div class="summary-label">Application Date:</div><div class="summary-value"><?= esc($biz('businessApplicationDate')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Reference No:</div><div class="summary-value"><?= esc($biz('businessReferenceNo')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Business Name:</div><div class="summary-value"><?= esc($biz('businessName')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Business Location:</div><div class="summary-value"><?= esc($biz('businessLocation')) ?></div></div>
      <div class="summary-row"><div class="summary-label">Owner Address:</div><div class="summary-value"><?= esc($biz('businessOwnerAddress')) ?></div></div>
      <div class="summary-row"><div class="summary-label">OR Number:</div><div class="summary-value"><?= esc($biz('businessOrNumber')) ?></div></div>
      <div class="summary-row"><div class="summary-label">CTC Number:</div><div class="summary-value"><?= esc($biz('businessCtcNumber')) ?></div></div>
    </div>
    <div class="summary-section">
      <h3>Attachments</h3>
      <div class="summary-row">
        <div class="summary-label">CTC Image:</div>
        <div class="summary-value"><?php $ctc = $biz('ctcImage', ''); if ($ctc !== ''): ?>
          <a href="../assets/uploads/business_attachments/<?= esc($ctc) ?>" target="_blank">View CTC Image</a>
        <?php else: ?>No CTC image uploaded<?php endif; ?></div>
      </div>
      <div class="summary-row">
        <div class="summary-label">Business Certificate Image:</div>
        <div class="summary-value"><?php $cert = $biz('certificateImage', ''); if ($cert !== ''): ?>
          <a href="../assets/uploads/business_attachments/<?= esc($cert) ?>" target="_blank">View Certificate Image</a>
        <?php else: ?>No certificate image uploaded<?php endif; ?></div>
      </div>
    </div>
<?php endif; ?>

  </div>
</div>
<?php
    $summary_html = ob_get_clean();

    if ($standalone) {
        // Render using the same UI style and structure as resident-summary.php
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Certificate Request Summary - <?php echo htmlspecialchars($r['full_name'] ?: ('#'.$r['id'])); ?></title>
            <link rel="stylesheet" href="../css/styles.css">
            <style>
                .summary-container {
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 2rem;
                    background: #f8f9fa;
                    min-height: 100vh;
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
                .title-block p {
                    margin: 0;
                    opacity: 0.95;
                    font-size: 0.95rem;
                }
                .requester {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    white-space: nowrap;
                }
                .requester h2 {
                    margin: 0;
                    font-size: 1.15rem;
                    font-weight: 700;
                    text-shadow: 0 1px 1px rgba(0,0,0,0.2);
                }
                .status-form { display:flex; gap:0.5rem; align-items:center; margin-left: 0.75rem; }
                .status-locked { background: #e9ecef; color: #6c757d; border: 2px solid #dee2e6; padding: 0.35rem 0.6rem; border-radius: 6px; font-weight: 600; display:inline-flex; align-items:center; gap:0.35rem; }
                .status-locked::before { content: '🔒'; }
                .summary-section {
                    background: white;
                    border-radius: 12px;
                    padding: 1.5rem;
                    margin-bottom: 1.5rem;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                }
                .section-title {
                    color: #2e7d32;
                    border-bottom: 2px solid #e9ecef;
                    padding-bottom: 0.5rem;
                    margin-bottom: 1.5rem;
                    font-size: 1.3rem;
                }
                .info-grid {
                    display: grid;
                    grid-template-columns: 1fr;
                    column-gap: 1rem;   /* tighter horizontal gap */
                    row-gap: 0.45rem;   /* clearer separation between rows */
                    grid-auto-flow: row dense; /* pack items left after full-width rows */
                    margin-bottom: 0.5rem;
                }
                .info-item { margin: 0; }
                .info-grid > .info-item:not(:first-child) { margin-top: 0.15rem; }
                .info-item.full-row { grid-column: 1 / -1; display: block; }
                /* Specific items are set to full width via the .full-row class */
                .info-label {
                    font-weight: 600;
                    color: #495057;
                    margin: 0; /* inline with value */
                    font-size: 0.9rem;
                }
                .info-value {
                    font-size: 1rem;
                    color: #212529;
                    padding: 0.35rem 0.5rem; /* reduce vertical padding to minimize perceived gap */
                    background: #f8f9fa;
                    border-radius: 6px;
                    border-left: 3px solid #4CAF50;
                    width: 100%;
                    box-sizing: border-box;
                    display: block;
                    margin: 0; /* remove any default spacing */
                }
                .info-item.full-row .info-label { margin: 0 0 2px; }
                .info-item.full-row .info-value { width: 100%; margin-top: 0; }
                .badge { display:inline-block; padding:0.3rem 0.6rem; border-radius:20px; font-size:0.8rem; font-weight:600; }
                .status-pending { background:#cce7ff; color:#004085; }
                .status-approved { background:#d4edda; color:#155724; }
                .status-rejected { background:#f8d7da; color:#721c24; }
                .actions { display:flex; gap:1rem; margin-top: 1rem; align-items:center; flex-wrap: wrap; }
                .print-btn { background: linear-gradient(135deg, #4CAF50, #45a049); color:#fff; border:none; padding:0.6rem 1rem; border-radius:8px; cursor:pointer; font-weight:600; }
                .action-select { padding: 0.4rem 0.6rem; border: 2px solid #e9ecef; border-radius: 6px; font-size: 0.9rem; }
                .scroll-top-btn { position: fixed; right: 20px; bottom: 20px; width: 44px; height: 44px; border-radius: 50%; border: none; background: #2e7d32; color: #fff; box-shadow: 0 6px 18px rgba(0,0,0,0.2); cursor: pointer; display: none; align-items: center; justify-content: center; font-size: 20px; z-index: 9999; }
                .scroll-top-btn.show { display: flex; }
                @media (max-width: 768px) {
                    .summary-container { padding: 1rem; }
                    .summary-header { padding: 1rem; }
                    .info-grid { grid-template-columns: 1fr; }
                    .info-label { margin: 0 0 2px; }
                }
            </style>
        </head>
        <body>
            <div class="summary-container">
                <div class="summary-header">
                    <a href="view-certificate-requests.php" class="back-btn" onclick="if (window.history.length > 1) { history.back(); return false; } return true;">← Back</a>
                    <div class="title-block">
                        <h1>📄 Certificate Request Summary</h1>
                        <p>
                            <strong>ID:</strong> #<?php echo htmlspecialchars($r['id']); ?>
                            &nbsp;|&nbsp;
                            <strong>Submitted:</strong> <?php echo date('F j, Y \\a\\t g:i A', strtotime($r['submitted_at'])); ?>
                        </p>
                    </div>
                    <div class="requester">
                        <h2><?php echo htmlspecialchars($r['full_name'] ?: $r['certificate_type']); ?></h2>
                        <span class="badge status-<?php echo htmlspecialchars($r['status']); ?>"><?php echo ucfirst($r['status']); ?></span>
                        <?php if ($r['status'] !== 'released'): ?>
                        <form method="POST" action="view-certificate-requests.php" class="status-form">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                            <label for="headerStatusSelect" class="info-label" style="color:#fff; opacity:0.95;">Status:</label>
                            <select id="headerStatusSelect" name="status" class="action-select" onchange="this.form.submit()">
                                <option value="pending" <?php echo $r['status']==='pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $r['status']==='processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="ready" <?php echo $r['status']==='ready' ? 'selected' : ''; ?>>Ready</option>
                                <option value="released" <?php echo $r['status']==='released' ? 'selected' : ''; ?>>Released</option>
                            </select>
                        </form>
                        <?php else: ?>
                            <div class="status-locked">Released (Locked)</div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
                // Extract only the summary details (remove original header and outer container)
                $summary_source = $summary_html;
                if (preg_match('/<div class="summary-details">(.*)<\/div>\s*<\/div>\s*<\/div>/s', $summary_html, $m)) {
                    $summary_source = $m[1];
                }

                // Map existing classes to resident-summary equivalents and enhance headings
                $summary = str_replace(
                    [
                        'class="summary-row"',
                        'class="summary-label"',
                        'class="summary-value"',
                        '<h3>'
                    ],
                    [
                        'class="info-item"',
                        'class="info-label"',
                        'class="info-value"',
                        '<h3 class="section-title">'
                    ],
                    $summary_source
                );

                // Wrap each section's rows inside an info-grid for proper two-column layout
                $summary = preg_replace_callback(
                    '/<div class="summary-section">.*?<\/div>/s',
                    function ($m) {
                        $sec = $m[0];
                        // Insert grid immediately after the section title
                        $sec = preg_replace('/(<h3 class="section-title">.*?<\/h3>)/s', '$1<div class="info-grid">', $sec, 1);
                        // Close the grid just before the final closing div of this section
                        $pos = strrpos($sec, '</div>');
                        if ($pos !== false) {
                            $sec = substr_replace($sec, '</div></div>', $pos, 6);
                        }
                        return $sec;
                    },
                    $summary
                );

                // Make specific fields span the full width
                $summary = preg_replace(
                    '/<div class="info-item">\s*<div class="info-label">\s*Certificate Type:\s*<\/div>/i',
                    '<div class="info-item full-row"><div class="info-label">Certificate Type:</div>',
                    $summary,
                    1
                );
                $summary = preg_replace(
                    '/<div class="info-item">\s*<div class="info-label">\s*Full Name:\s*<\/div>/i',
                    '<div class="info-item full-row"><div class="info-label">Full Name:</div>',
                    $summary,
                    1
                );

                echo $summary;
                ?>
            </div>
            <button id="scrollTopBtn" class="scroll-top-btn" title="Back to top">↑</button>
            <script>
                (function(){
                    var btn = document.getElementById('scrollTopBtn');
                    function onScroll(){
                        if (window.scrollY > 200) { btn.classList.add('show'); } else { btn.classList.remove('show'); }
                    }
                    window.addEventListener('scroll', onScroll, { passive: true });
                    btn.addEventListener('click', function(){ window.scrollTo({ top: 0, behavior: 'smooth' }); });
                    // Initialize state
                    onScroll();
                })();
            </script>
        </body>
        </html>
        <?php
    } else {
        // Original fragment mode
        echo $summary_html;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo '<p style="color:#dc3545;">Server error while building summary.</p>';
}
