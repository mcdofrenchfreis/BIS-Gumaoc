<?php
session_start();
include '../includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get date filters
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-t'); // Last day of current month

try {
    // Overall statistics
    $overall_stats = $pdo->prepare("
        SELECT 
            COUNT(*) as total_records,
            SUM(CASE WHEN incident_type = 'complaint' THEN 1 ELSE 0 END) as complaints,
            SUM(CASE WHEN incident_type = 'incident' THEN 1 ELSE 0 END) as incidents,
            SUM(CASE WHEN incident_type = 'dispute' THEN 1 ELSE 0 END) as disputes,
            SUM(CASE WHEN incident_type = 'violation' THEN 1 ELSE 0 END) as violations,
            SUM(CASE WHEN status = 'filed' THEN 1 ELSE 0 END) as filed,
            SUM(CASE WHEN status = 'under_investigation' THEN 1 ELSE 0 END) as investigating,
            SUM(CASE WHEN status = 'mediation' THEN 1 ELSE 0 END) as mediation,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
            SUM(CASE WHEN status = 'dismissed' THEN 1 ELSE 0 END) as dismissed,
            SUM(CASE WHEN classification = 'minor' THEN 1 ELSE 0 END) as minor_cases,
            SUM(CASE WHEN classification = 'major' THEN 1 ELSE 0 END) as major_cases,
            SUM(CASE WHEN classification = 'critical' THEN 1 ELSE 0 END) as critical_cases
        FROM barangay_blotter 
        WHERE DATE(incident_date) BETWEEN ? AND ?
    ");
    $overall_stats->execute([$start_date, $end_date]);
    $stats = $overall_stats->fetch();

    // Monthly trends
    $monthly_trends = $pdo->prepare("
        SELECT 
            DATE_FORMAT(incident_date, '%Y-%m') as month,
            COUNT(*) as total,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved
        FROM barangay_blotter 
        WHERE incident_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(incident_date, '%Y-%m')
        ORDER BY month DESC
        LIMIT 12
    ");
    $monthly_trends->execute();
    $trends = $monthly_trends->fetchAll();

    // Resident status overview
    $resident_status = $pdo->query("
        SELECT 
            record_status,
            COUNT(*) as count,
            SUM(CASE WHEN requires_captain_clearance = 1 THEN 1 ELSE 0 END) as requires_clearance
        FROM resident_status 
        GROUP BY record_status
    ");
    $status_data = $resident_status->fetchAll();

    // Top complainants and respondents
    $top_complainants = $pdo->prepare("
        SELECT 
            complainant_name,
            COUNT(*) as complaint_count
        FROM barangay_blotter 
        WHERE DATE(incident_date) BETWEEN ? AND ?
        GROUP BY complainant_name 
        ORDER BY complaint_count DESC 
        LIMIT 10
    ");
    $top_complainants->execute([$start_date, $end_date]);
    $complainants = $top_complainants->fetchAll();

    $top_respondents = $pdo->prepare("
        SELECT 
            respondent_name,
            COUNT(*) as incident_count
        FROM barangay_blotter 
        WHERE DATE(incident_date) BETWEEN ? AND ?
        GROUP BY respondent_name 
        ORDER BY incident_count DESC 
        LIMIT 10
    ");
    $top_respondents->execute([$start_date, $end_date]);
    $respondents = $top_respondents->fetchAll();

    // Recent critical cases
    $critical_cases = $pdo->prepare("
        SELECT * FROM barangay_blotter 
        WHERE classification = 'critical' 
        AND DATE(incident_date) BETWEEN ? AND ?
        ORDER BY incident_date DESC 
        LIMIT 10
    ");
    $critical_cases->execute([$start_date, $end_date]);
    $critical = $critical_cases->fetchAll();

} catch (Exception $e) {
    $error_message = "Error fetching data: " . $e->getMessage();
    $stats = ['total_records' => 0];
    $trends = [];
    $status_data = [];
    $complainants = [];
    $respondents = [];
    $critical = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blotter Reports - Barangay Gumaoc East</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #2c5530 0%, #3e7b3e 100%); color: white; padding: 30px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .date-filter { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .form-inline { display: flex; align-items: end; gap: 15px; flex-wrap: wrap; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { margin-bottom: 5px; font-weight: 500; }
        .form-control { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-weight: 500; }
        .btn-primary { background: #2c5530; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-success { background: #28a745; color: white; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; border-left: 4px solid #2c5530; }
        .stat-number { font-size: 2.5em; font-weight: bold; color: #2c5530; }
        .stat-label { color: #666; margin-top: 5px; }
        .reports-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .report-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .report-card h3 { margin-top: 0; color: #2c5530; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 8px; text-align: left; border-bottom: 1px solid #eee; }
        .table th { background: #f8f9fa; font-weight: 600; }
        .status-badge { padding: 3px 6px; border-radius: 3px; font-size: 11px; font-weight: 500; text-transform: uppercase; }
        .status-resolved { background: #e8f5e8; color: #388e3c; }
        .status-critical { background: #ffebee; color: #d32f2f; }
        .status-major { background: #fff3e0; color: #f57c00; }
        .alert { padding: 12px; margin-bottom: 20px; border-radius: 4px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .full-width { grid-column: 1 / -1; }
        @media (max-width: 768px) {
            .reports-grid { grid-template-columns: 1fr; }
            .form-inline { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-chart-bar"></i> Blotter Reports & Statistics</h1>
            <p>Analyze complaints, incidents, and dispute trends</p>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Date Filter -->
        <div class="date-filter">
            <form method="GET" class="form-inline">
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-success" onclick="exportReport()"><i class="fas fa-download"></i> Export</button>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <a href="manage-blotter.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                </div>
            </form>
        </div>
        
        <!-- Overall Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_records']; ?></div>
                <div class="stat-label">Total Records</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['complaints']; ?></div>
                <div class="stat-label">Complaints</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['incidents']; ?></div>
                <div class="stat-label">Incidents</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['disputes']; ?></div>
                <div class="stat-label">Disputes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['resolved']; ?></div>
                <div class="stat-label">Resolved</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['critical_cases']; ?></div>
                <div class="stat-label">Critical Cases</div>
            </div>
        </div>
        
        <!-- Report Cards -->
        <div class="reports-grid">
            <!-- Case Status Breakdown -->
            <div class="report-card">
                <h3><i class="fas fa-tasks"></i> Case Status Breakdown</h3>
                <table class="table">
                    <tr><td>Filed</td><td><strong><?php echo $stats['filed']; ?></strong></td></tr>
                    <tr><td>Under Investigation</td><td><strong><?php echo $stats['investigating']; ?></strong></td></tr>
                    <tr><td>Mediation</td><td><strong><?php echo $stats['mediation']; ?></strong></td></tr>
                    <tr><td>Resolved</td><td><strong><?php echo $stats['resolved']; ?></strong></td></tr>
                    <tr><td>Dismissed</td><td><strong><?php echo $stats['dismissed']; ?></strong></td></tr>
                </table>
            </div>
            
            <!-- Case Classification -->
            <div class="report-card">
                <h3><i class="fas fa-exclamation-triangle"></i> Case Classification</h3>
                <table class="table">
                    <tr><td>Minor Cases</td><td><strong><?php echo $stats['minor_cases']; ?></strong></td></tr>
                    <tr><td>Major Cases</td><td><strong><?php echo $stats['major_cases']; ?></strong></td></tr>
                    <tr><td>Critical Cases</td><td><strong><?php echo $stats['critical_cases']; ?></strong></td></tr>
                </table>
            </div>
            
            <!-- Monthly Trends -->
            <div class="report-card">
                <h3><i class="fas fa-chart-line"></i> Monthly Trends (Last 12 Months)</h3>
                <?php if (!empty($trends)): ?>
                    <table class="table">
                        <thead>
                            <tr><th>Month</th><th>Total</th><th>Resolved</th><th>Rate</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_reverse($trends) as $trend): ?>
                                <?php $rate = $trend['total'] > 0 ? round(($trend['resolved'] / $trend['total']) * 100) : 0; ?>
                                <tr>
                                    <td><?php echo date('M Y', strtotime($trend['month'] . '-01')); ?></td>
                                    <td><?php echo $trend['total']; ?></td>
                                    <td><?php echo $trend['resolved']; ?></td>
                                    <td><?php echo $rate; ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No trend data available</p>
                <?php endif; ?>
            </div>
            
            <!-- Resident Status Overview -->
            <div class="report-card">
                <h3><i class="fas fa-users"></i> Resident Status Overview</h3>
                <?php if (!empty($status_data)): ?>
                    <table class="table">
                        <thead>
                            <tr><th>Status</th><th>Count</th><th>Need Clearance</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($status_data as $status): ?>
                                <tr>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $status['record_status'])); ?></td>
                                    <td><?php echo $status['count']; ?></td>
                                    <td><?php echo $status['requires_clearance']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No resident status data available</p>
                <?php endif; ?>
            </div>
            
            <!-- Top Complainants -->
            <div class="report-card">
                <h3><i class="fas fa-user-friends"></i> Frequent Complainants</h3>
                <?php if (!empty($complainants)): ?>
                    <table class="table">
                        <thead>
                            <tr><th>Name</th><th>Complaints</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($complainants as $complainant): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($complainant['complainant_name']); ?></td>
                                    <td><?php echo $complainant['complaint_count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No complainant data available</p>
                <?php endif; ?>
            </div>
            
            <!-- Top Respondents -->
            <div class="report-card">
                <h3><i class="fas fa-user-times"></i> Frequent Respondents</h3>
                <?php if (!empty($respondents)): ?>
                    <table class="table">
                        <thead>
                            <tr><th>Name</th><th>Incidents</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($respondents as $respondent): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($respondent['respondent_name']); ?></td>
                                    <td><?php echo $respondent['incident_count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No respondent data available</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Critical Cases -->
        <?php if (!empty($critical)): ?>
            <div class="report-card full-width">
                <h3><i class="fas fa-exclamation-circle"></i> Recent Critical Cases</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Blotter #</th>
                            <th>Type</th>
                            <th>Complainant</th>
                            <th>Respondent</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($critical as $case): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($case['blotter_number']); ?></strong></td>
                                <td><?php echo ucfirst($case['incident_type']); ?></td>
                                <td><?php echo htmlspecialchars($case['complainant_name']); ?></td>
                                <td><?php echo htmlspecialchars($case['respondent_name']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($case['incident_date'])); ?></td>
                                <td><span class="status-badge status-critical"><?php echo ucfirst(str_replace('_', ' ', $case['status'])); ?></span></td>
                                <td><?php echo htmlspecialchars(substr($case['description'], 0, 100)) . (strlen($case['description']) > 100 ? '...' : ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function exportReport() {
            const startDate = document.querySelector('input[name="start_date"]').value;
            const endDate = document.querySelector('input[name="end_date"]').value;
            const url = `export-blotter-report.php?start_date=${startDate}&end_date=${endDate}`;
            window.open(url, '_blank');
        }
    </script>
</body>
</html>