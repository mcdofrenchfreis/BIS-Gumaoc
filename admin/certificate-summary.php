<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

// Validate ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Invalid Request</title></head><body>';
    echo '<p style="color:#dc3545; font-family: Arial, sans-serif;">Invalid certificate request ID.</p>';
    echo '</body></html>';
    exit;
}

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Certificate Request Summary #<?php echo htmlspecialchars($id); ?></title>
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    body { background: #f8f9fa; }
    .page-container {
      max-width: 1200px;
      margin: 1.5rem auto;
      padding: 0 1.25rem 1.5rem;
    }
    .page-header {
      background: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%);
      color: #ffffff;
      border: none;
      padding: 0.9rem 1.1rem;
      border-radius: 10px;
      margin-bottom: 0.9rem;
      display: grid;
      grid-template-columns: 1fr auto;
      align-items: center;
    }
    .admin-btn {
      display: inline-block;
      padding: 0.6rem 1.2rem;
      background: linear-gradient(135deg, #4CAF50, #45a049);
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 600;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .admin-btn:hover { transform: translateY(-1px); box-shadow: 0 3px 10px rgba(76, 175, 80, 0.25); }

    /* Compact included summary container/header */
    .summary-container { margin-top: 10px; }
    .summary-header { background: #fff; border: 1px solid #e9ecef; border-radius: 10px; padding: 8px 12px; margin-bottom: 12px; }

    /* Summary sections styled as compact cards */
    .summary-section {
      margin: 0; /* grid gap controls spacing */
      border: 1px solid #e9ecef;
      padding: 14px 16px;
      border-radius: 10px;
      background: #fff;
      display: flex;
      flex-wrap: wrap;
    }
    .summary-section h3 { width: 100%; color: #2e7d32; font-size: 14px; margin: 0 0 6px 0; font-weight: 700; }
    .summary-row { display: flex; gap: 10px; align-items: flex-start; padding: 6px 0; line-height: 1.35; width: 100%; box-sizing: border-box; }
    .summary-label { flex: 0 0 40%; font-weight: 600; color: #5b5b5b; }
    .summary-value { flex: 1; color: #2f2f2f; }
    .status-badge { padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.8rem; font-weight: 500; display: inline-block; background:#f0f0f0; color:#333; }

    /* Single-column summary layout */
    .summary-details { display: grid; grid-template-columns: 1fr; gap: 12px; align-items: start; }
    .summary-section.full-span { grid-column: 1 / -1; }
    .summary-row { width: 100%; padding-left: 0; padding-right: 0; }
    @media (max-width: 768px) {
      .page-container { padding: 0 0.75rem 1rem; }
    }
  </style>
</head>
<body>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h1 style="margin:0;">📄 Certificate Request Summary</h1>
        <div style="opacity:0.9; font-size: 0.95rem;">Request ID: #<?php echo htmlspecialchars($id); ?></div>
      </div>
      <div style="display:flex; gap:0.5rem;">
        <a class="admin-btn" href="view-certificate-requests.php" title="Back to list">← Back to Requests</a>
        <button class="admin-btn" onclick="window.print()">🖨️ Print Page</button>
      </div>
    </div>

    <div id="summaryMount">
      <?php
        // Capture the rendered HTML from the existing summary builder
        $_GET['id'] = $id; // ensure get-certificate-summary.php reads the correct ID
        ob_start();
        include __DIR__ . '/get-certificate-summary.php';
        $summaryHtml = ob_get_clean();
        echo $summaryHtml;
      ?>
    </div>
  </div>

  <script>
    // Log the summary view action (non-blocking)
    try {
      fetch('../includes/log-action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'form_view',
          target_type: 'certificate_request',
          target_id: <?php echo json_encode($id); ?>,
          description: 'Viewed certificate request summary in new tab'
        })
      });
    } catch (e) { /* ignore logging errors */ }
  </script>
</body>
</html>
