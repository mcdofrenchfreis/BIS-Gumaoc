<?php
session_start();
include '../includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get form submission counts
try {
    $resident_count = $pdo->query("SELECT COUNT(*) FROM resident_registrations")->fetchColumn();
    $certificate_count = $pdo->query("SELECT COUNT(*) FROM certificate_requests")->fetchColumn();
    $business_count = $pdo->query("SELECT COUNT(*) FROM business_applications")->fetchColumn();
    
    $pending_resident = $pdo->query("SELECT COUNT(*) FROM resident_registrations WHERE status = 'pending'")->fetchColumn();
    $pending_certificate = $pdo->query("SELECT COUNT(*) FROM certificate_requests WHERE status = 'pending'")->fetchColumn();
    $pending_business = $pdo->query("SELECT COUNT(*) FROM business_applications WHERE status = 'pending'")->fetchColumn();
} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Barangay Gumaoc East</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .admin-dashboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%);
            color: white;
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .dashboard-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2.5rem;
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #4caf50;
        }
        
        .stat-card h3 {
            color: #2e7d32;
            margin: 0 0 1rem 0;
            font-size: 1.1rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .stat-pending {
            font-size: 0.9rem;
            color: #ff9800;
            font-weight: 500;
        }
        
        .dashboard-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .action-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
        }
        
        .action-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .action-card h3 {
            color: #2e7d32;
            margin-bottom: 1rem;
        }
        
        .admin-btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: linear-gradient(45deg, #4CAF50, #2196F3);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .admin-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        
        .admin-nav {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .admin-nav a {
            color: #2e7d32;
            text-decoration: none;
            margin: 0 1rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <div class="dashboard-header">
            <h1>📊 Admin Dashboard</h1>
            <p>Manage form submissions and applications</p>
        </div>
        
        <div class="admin-nav">
            <a href="forms-manager.php">📋 Forms Manager</a> |
            <a href="../index.php" target="_blank">🌐 View Website</a> |
            <a href="logout.php">🚪 Logout</a>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Census Registrations</h3>
                <div class="stat-number"><?php echo $resident_count ?? 0; ?></div>
                <div class="stat-pending"><?php echo $pending_resident ?? 0; ?> pending</div>
            </div>
            
            <div class="stat-card">
                <h3>Certificate Requests</h3>
                <div class="stat-number"><?php echo $certificate_count ?? 0; ?></div>
                <div class="stat-pending"><?php echo $pending_certificate ?? 0; ?> pending</div>
            </div>
            
            <div class="stat-card">
                <h3>Business Applications</h3>
                <div class="stat-number"><?php echo $business_count ?? 0; ?></div>
                <div class="stat-pending"><?php echo $pending_business ?? 0; ?> pending</div>
            </div>
        </div>
        
        <div class="dashboard-actions">
            <div class="action-card">
                <div class="action-icon">👥</div>
                <h3>Census Registrations</h3>
                <p>View and manage resident census registrations</p>
                <a href="view-resident-registrations.php" class="admin-btn">View Submissions</a>
            </div>
            
            <div class="action-card">
                <div class="action-icon">📄</div>
                <h3>Certificate Requests</h3>
                <p>Process certificate requests and approvals</p>
                <a href="view-certificate-requests.php" class="admin-btn">View Requests</a>
            </div>
            
            <div class="action-card">
                <div class="action-icon">🏢</div>
                <h3>Business Applications</h3>
                <p>Review business permit applications</p>
                <a href="view-business-applications.php" class="admin-btn">View Applications</a>
            </div>
        </div>
    </div>
</body>
</html>
