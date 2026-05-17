<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

// Admin auth
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['admin_role'] ?? 'secretary';
// RBAC disabled on reports page: show all sections to all admins
$showCensus = true;
$showCertificates = true;
$showBusinessApps = true;

// Initialize data buckets (defaults)
$stats = [
    'census' => ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0],
    'certificates' => ['total' => 0, 'pending' => 0, 'processing' => 0, 'ready' => 0, 'released' => 0, 'received' => 0],
    'business' => ['total' => 0, 'pending' => 0, 'reviewing' => 0, 'approved' => 0, 'ready' => 0, 'received' => 0, 'rejected' => 0],
    'business_details' => [
        'with_reference_no' => 0,
        'with_or_number' => 0,
        'with_ctc_number' => 0,
        'with_proof_image' => 0,
        'attachments_ctc_image' => 0,
        'attachments_certificate_image' => 0
    ],
];

// Monthly trends containers
$trends = [
    'census_submissions' => [], // [ ['month' => 'YYYY-MM', 'count' => N], ... ]
    'cert_requests' => [],      // same format
];

try {
    // Census registrations
    if ($showCensus) {
        try {
            $stats['census']['total'] = (int)$pdo->query("SELECT COUNT(*) FROM resident_registrations")->fetchColumn();
            $stats['census']['pending'] = (int)$pdo->query("SELECT COUNT(*) FROM resident_registrations WHERE status='pending'")->fetchColumn();
            $stats['census']['approved'] = (int)$pdo->query("SELECT COUNT(*) FROM resident_registrations WHERE status='approved'")->fetchColumn();
            $stats['census']['rejected'] = (int)$pdo->query("SELECT COUNT(*) FROM resident_registrations WHERE status='rejected'")->fetchColumn();

            // Monthly trends - last 6 months
            $stmt = $pdo->query("SELECT DATE_FORMAT(submitted_at, '%Y-%m') AS month, COUNT(*) AS cnt
                                  FROM resident_registrations
                                  WHERE submitted_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                                  GROUP BY DATE_FORMAT(submitted_at, '%Y-%m')
                                  ORDER BY month ASC");
            $trends['census_submissions'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {}
    }

    // Certificate requests
    if ($showCertificates) {
        try {
            $stats['certificates']['total'] = (int)$pdo->query("SELECT COUNT(*) FROM certificate_requests")->fetchColumn();
            foreach (['pending','processing','ready','released','received'] as $st) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM certificate_requests WHERE status = '".$st."'");
                $stats['certificates'][$st] = (int)$stmt->fetchColumn();
            }
            // Monthly trends - last 6 months
            $stmt = $pdo->query("SELECT DATE_FORMAT(submitted_at, '%Y-%m') AS month, COUNT(*) AS cnt
                                  FROM certificate_requests
                                  WHERE submitted_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                                  GROUP BY DATE_FORMAT(submitted_at, '%Y-%m')
                                  ORDER BY month ASC");
            $trends['cert_requests'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {}
    }

    // Business applications
    if ($showBusinessApps) {
        try {
            $stats['business']['total'] = (int)$pdo->query("SELECT COUNT(*) FROM business_applications")->fetchColumn();
            foreach (['pending','reviewing','approved','ready','received','rejected'] as $st) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM business_applications WHERE status = '".$st."'");
                $stats['business'][$st] = (int)$stmt->fetchColumn();
            }

            // Business details aligned with get-business-application-summary.php
            $stats['business_details']['with_reference_no'] = (int)$pdo->query("SELECT COUNT(*) FROM business_applications WHERE reference_no IS NOT NULL AND reference_no <> ''")->fetchColumn();
            $stats['business_details']['with_or_number'] = (int)$pdo->query("SELECT COUNT(*) FROM business_applications WHERE or_number IS NOT NULL AND or_number <> ''")->fetchColumn();
            $stats['business_details']['with_ctc_number'] = (int)$pdo->query("SELECT COUNT(*) FROM business_applications WHERE ctc_number IS NOT NULL AND ctc_number <> ''")->fetchColumn();
            $stats['business_details']['with_proof_image'] = (int)$pdo->query("SELECT COUNT(*) FROM business_applications WHERE proof_image IS NOT NULL AND proof_image <> ''")->fetchColumn();

            // Optional attachments table counts (ctc_image, certificate_image)
            try {
                $stats['business_details']['attachments_ctc_image'] = (int)$pdo->query("SELECT COUNT(*) FROM business_attachments WHERE ctc_image IS NOT NULL AND ctc_image <> ''")->fetchColumn();
            } catch (Exception $e) { /* table may not exist */ }
            try {
                $stats['business_details']['attachments_certificate_image'] = (int)$pdo->query("SELECT COUNT(*) FROM business_attachments WHERE certificate_image IS NOT NULL AND certificate_image <> ''")->fetchColumn();
            } catch (Exception $e) { /* table may not exist */ }
        } catch (Exception $e) {}
    }

} catch (Exception $e) {
    $error_message = 'Error building reports: ' . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reports & Site Activity - Admin</title>
    <link rel="stylesheet" href="../css/styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background:#f8faf8; margin:0; padding: 90px 16px 24px; }
        .container { max-width: 1400px; margin:0 auto; }

        .header { background: linear-gradient(135deg, #0a5d0a 0%, #1d7a1d 100%); color:#fff; padding:24px; border-radius:14px; box-shadow:0 16px 32px rgba(10,93,10,0.18); margin-bottom:16px; }
        .header h1 { margin:0 0 6px; font-size: 1.6rem; }
        .header p { margin:0; opacity:0.95; }

        .grid { display:grid; gap:14px; }
        .cards { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
        .card { background:#fff; border:1px solid #e8f0e8; border-radius:12px; padding:16px; box-shadow:0 8px 18px rgba(0,0,0,0.06); }
        .card h3 { margin:0 0 8px; font-size:0.95rem; color:#2e7d32; text-transform:uppercase; letter-spacing:0.5px; }
        .metric { font-size:2rem; font-weight:800; color:#0a5d0a; }
        .row { grid-template-columns: 1fr 1fr; }
        .row-3 { grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); }
        .muted { color:#666; font-size: 0.9rem; }
        .pill { display:inline-block; padding:4px 8px; border-radius:999px; border:1px solid #e8f0e8; margin-right:6px; font-size:0.8rem; }

        .section { background:#fff; border:1px solid #e8f0e8; border-radius:12px; padding:16px; box-shadow:0 8px 18px rgba(0,0,0,0.06); }
        .section h2 { margin:0 0 12px; font-size:1rem; color:#0a5d0a; text-transform:uppercase; letter-spacing:0.6px; }
        .list { display:grid; gap:6px; }
        .rowpair { display:flex; justify-content:space-between; gap:10px; padding:8px 10px; border:1px solid #f0f5f0; border-radius:8px; background:#fcfdfc; }

        @media (max-width: 768px) {
            .row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php $base_path = '../'; include __DIR__ . '/../includes/admin_mini_nav.php'; ?>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-chart-pie"></i> Reports & Site Activity</h1>
            <p>Overview of submissions, processing, and operational status across modules</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="card" style="border-left:4px solid #d14836;">
                <strong>Database warning:</strong> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="grid cards" style="margin-bottom:16px;">
            <?php if ($showCensus): ?>
            <div class="card">
                <h3>Census Registrations</h3>
                <div class="metric"><?php echo (int)$stats['census']['total']; ?></div>
                <div class="muted">
                    <span class="pill">Pending: <?php echo (int)$stats['census']['pending']; ?></span>
                    <span class="pill">Approved: <?php echo (int)$stats['census']['approved']; ?></span>
                    <span class="pill">Rejected: <?php echo (int)$stats['census']['rejected']; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($showCertificates): ?>
            <div class="card">
                <h3>Certificate Requests</h3>
                <div class="metric"><?php echo (int)$stats['certificates']['total']; ?></div>
                <div class="muted">
                    <span class="pill">Pending: <?php echo (int)$stats['certificates']['pending']; ?></span>
                    <span class="pill">Processing: <?php echo (int)$stats['certificates']['processing']; ?></span>
                    <span class="pill">Ready: <?php echo (int)$stats['certificates']['ready']; ?></span>
                    <span class="pill">Released: <?php echo (int)$stats['certificates']['released']; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($showBusinessApps): ?>
            <div class="card">
                <h3>Business Applications</h3>
                <div class="metric"><?php echo (int)$stats['business']['total']; ?></div>
                <div class="muted">
                    <span class="pill">Pending: <?php echo (int)$stats['business']['pending']; ?></span>
                    <span class="pill">Reviewing: <?php echo (int)$stats['business']['reviewing']; ?></span>
                    <span class="pill">Approved: <?php echo (int)$stats['business']['approved']; ?></span>
                    <span class="pill">Rejected: <?php echo (int)$stats['business']['rejected']; ?></span>
                </div>
            </div>
            <?php endif; ?>

            
            </div>

        <div class="grid row-3" style="margin-bottom:14px;">
            <?php if ($showCensus): ?>
            <div class="section">
                <h2><i class="fas fa-user-group"></i> Census Submissions (Last 6 Months)</h2>
                <canvas id="censusTrend" height="120"></canvas>
            </div>
            <?php endif; ?>

            <?php if ($showCertificates): ?>
            <div class="section">
                <h2><i class="fas fa-file-signature"></i> Certificate Requests (Last 6 Months)</h2>
                <canvas id="certTrend" height="120"></canvas>
            </div>
            <?php endif; ?>

            <?php if ($showBusinessApps): ?>
            <div class="section">
                <h2><i class="fas fa-briefcase"></i> Business Application Details</h2>
                <div class="list">
                    <div class="rowpair"><span>With Reference No.</span><strong><?php echo (int)$stats['business_details']['with_reference_no']; ?></strong></div>
                    <div class="rowpair"><span>With OR Number</span><strong><?php echo (int)$stats['business_details']['with_or_number']; ?></strong></div>
                    <div class="rowpair"><span>With CTC Number</span><strong><?php echo (int)$stats['business_details']['with_ctc_number']; ?></strong></div>
                    <div class="rowpair"><span>With Proof Image</span><strong><?php echo (int)$stats['business_details']['with_proof_image']; ?></strong></div>
                    <div class="rowpair"><span>Attachments: CTC Image</span><strong><?php echo (int)$stats['business_details']['attachments_ctc_image']; ?></strong></div>
                    <div class="rowpair"><span>Attachments: Certificate Image</span><strong><?php echo (int)$stats['business_details']['attachments_certificate_image']; ?></strong></div>
                </div>
            </div>
            <?php endif; ?>

            <div class="section">
                <h2><i class="fas fa-clipboard-list"></i> Quick Breakdown</h2>
                <div class="list">
                    <?php if ($showCensus): ?>
                    <div class="rowpair"><span>Total Census</span><strong><?php echo (int)$stats['census']['total']; ?></strong></div>
                    <?php endif; ?>
                    <?php if ($showCertificates): ?>
                    <div class="rowpair"><span>Total Certificates</span><strong><?php echo (int)$stats['certificates']['total']; ?></strong></div>
                    <?php endif; ?>
                    <?php if ($showBusinessApps): ?>
                    <div class="rowpair"><span>Total Business Apps</span><strong><?php echo (int)$stats['business']['total']; ?></strong></div>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    </div>

    <script>
        const censusData = <?php echo json_encode($trends['census_submissions'], JSON_NUMERIC_CHECK); ?>;
        const certData = <?php echo json_encode($trends['cert_requests'], JSON_NUMERIC_CHECK); ?>;

        function monthLabels(data) { return (data||[]).map(x => x.month); }
        function monthCounts(data) { return (data||[]).map(x => x.cnt || x.count || 0); }

        if (document.getElementById('censusTrend')) {
            const ctx1 = document.getElementById('censusTrend').getContext('2d');
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: monthLabels(censusData),
                    datasets: [{
                        label: 'Submissions',
                        data: monthCounts(censusData),
                        backgroundColor: 'rgba(34, 139, 34, 0.4)',
                        borderColor: 'rgba(34, 139, 34, 1)',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision:0 } }
                    }
                }
            });
        }

        if (document.getElementById('certTrend')) {
            const ctx2 = document.getElementById('certTrend').getContext('2d');
            new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: monthLabels(certData),
                    datasets: [{
                        label: 'Requests',
                        data: monthCounts(certData),
                        fill: false,
                        borderColor: 'rgba(10, 93, 10, 1)',
                        backgroundColor: 'rgba(10, 93, 10, 0.15)',
                        tension: 0.2,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgba(10, 93, 10, 1)'
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision:0 } } }
                }
            });
        }
    </script>
</body>
</html>
