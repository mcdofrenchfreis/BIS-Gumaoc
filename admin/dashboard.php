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
    // Check and create tables if they don't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS services (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            button_text VARCHAR(100) NOT NULL,
            button_link VARCHAR(255) NOT NULL,
            is_featured BOOLEAN DEFAULT FALSE,
            features TEXT,
            display_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS updates (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            badge_text VARCHAR(50) NOT NULL,
            badge_type ENUM('important', 'new', 'community', 'info') DEFAULT 'info',
            date VARCHAR(50) NOT NULL,
            status VARCHAR(50) NOT NULL,
            is_priority BOOLEAN DEFAULT FALSE,
            display_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    // Get form submission counts (with error handling for non-existent tables)
    $resident_count = 0;
    $certificate_count = 0;
    $business_count = 0;
    $pending_resident = 0;
    $pending_certificate = 0;
    $pending_business = 0;
    
    try {
        $resident_count = $pdo->query("SELECT COUNT(*) FROM resident_registrations")->fetchColumn();
        $pending_resident = $pdo->query("SELECT COUNT(*) FROM resident_registrations WHERE status = 'pending'")->fetchColumn();
    } catch (Exception $e) {
        // Table doesn't exist yet
    }
    
    try {
        $certificate_count = $pdo->query("SELECT COUNT(*) FROM certificate_requests")->fetchColumn();
        $pending_certificate = $pdo->query("SELECT COUNT(*) FROM certificate_requests WHERE status = 'pending'")->fetchColumn();
    } catch (Exception $e) {
        // Table doesn't exist yet
    }
    
    try {
        $business_count = $pdo->query("SELECT COUNT(*) FROM business_applications")->fetchColumn();
        $pending_business = $pdo->query("SELECT COUNT(*) FROM business_applications WHERE status = 'pending'")->fetchColumn();
    } catch (Exception $e) {
        // Table doesn't exist yet
    }
    
    // Get services and updates counts
    $services_count = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
    $updates_count = $pdo->query("SELECT COUNT(*) FROM updates")->fetchColumn();
    
    // Get RFID statistics
    $rfid_available = 0;
    $rfid_assigned = 0;
    try {
        $rfid_available = $pdo->query("SELECT COUNT(*) FROM scanned_rfid_codes WHERE status = 'available'")->fetchColumn();
        $rfid_assigned = $pdo->query("SELECT COUNT(*) FROM scanned_rfid_codes WHERE status = 'assigned'")->fetchColumn();
    } catch (Exception $e) {
        // Table doesn't exist yet
    }
    
} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
    // Set default values if there's an error
    $resident_count = 0;
    $certificate_count = 0;
    $business_count = 0;
    $pending_resident = 0;
    $pending_certificate = 0;
    $pending_business = 0;
    $services_count = 0;
    $updates_count = 0;
    $rfid_available = 0;
    $rfid_assigned = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Barangay Gumaoc East</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #ffffff 0%, #f0f8f0 20%, #e8f5e8 40%, #c8e6c9 70%, #a5d6a7 100%);
            background-attachment: fixed;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            position: relative;
        }
        
        /* Add subtle pattern overlay */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(27, 94, 32, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(76, 175, 80, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }
        
        .admin-dashboard {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        /* Header */
        .dashboard-header {
            background: linear-gradient(135deg, rgba(27, 94, 32, 0.95) 0%, rgba(46, 125, 50, 0.95) 50%, rgba(56, 142, 60, 0.95) 100%);
            backdrop-filter: blur(20px);
            color: white;
            padding: 2.5rem;
            border-radius: 24px;
            margin-bottom: 2rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(27, 94, 32, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 40%, rgba(255, 255, 255, 0.1) 50%, transparent 60%);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .dashboard-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 3rem;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
        }
        
        .dashboard-header p {
            font-size: 1.2rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        /* Navigation */
        .admin-nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            padding: 1.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(27, 94, 32, 0.1);
            border: 1px solid rgba(27, 94, 32, 0.1);
        }
        
        .admin-nav a {
            color: #1b5e20;
            text-decoration: none;
            margin: 0 1.5rem;
            font-weight: 600;
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: inline-block;
            position: relative;
            overflow: hidden;
        }
        
        .admin-nav a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
            transition: left 0.3s ease;
            z-index: -1;
        }
        
        .admin-nav a:hover::before {
            left: 0;
        }
        
        .admin-nav a:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(27, 94, 32, 0.3);
        }
        
        /* Stats Grid - Modified for single row */
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1rem;
            margin-bottom: 3rem;
            overflow-x: auto;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            padding: 1.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(27, 94, 32, 0.1);
            border: 1px solid rgba(27, 94, 32, 0.1);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            min-width: 200px;
            text-align: center;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(135deg, #1b5e20 0%, #4caf50 100%);
        }
        
        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.1) 0%, rgba(27, 94, 32, 0.05) 100%);
            border-radius: 0 20px 0 40px;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(27, 94, 32, 0.15);
            background: rgba(255, 255, 255, 1);
        }
        
        .stat-card h3 {
            color: #1b5e20;
            margin: 0 0 1rem 0;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
            line-height: 1.2;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1b5e20;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
        }
        
        .stat-pending {
            font-size: 0.85rem;
            color: #f57c00;
            font-weight: 600;
            background: linear-gradient(135deg, rgba(255, 152, 0, 0.1) 0%, rgba(255, 193, 7, 0.1) 100%);
            padding: 0.3rem 0.6rem;
            border-radius: 15px;
            display: inline-block;
            border: 1px solid rgba(255, 152, 0, 0.2);
            position: relative;
            z-index: 1;
            line-height: 1.2;
        }

        /* Main Content Grid */
        .dashboard-main {
            display: block;
            margin-bottom: 2rem;
        }
        
        .dashboard-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(27, 94, 32, 0.1);
            border: 1px solid rgba(27, 94, 32, 0.1);
        }
        
        .section-title {
            color: #1b5e20;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
            padding-bottom: 0.8rem;
            border-bottom: 3px solid rgba(27, 94, 32, 0.1);
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        
        .section-icon {
            background: linear-gradient(135deg, #1b5e20 0%, #4caf50 100%);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 4px 15px rgba(27, 94, 32, 0.2);
        }
        
        /* Action Cards */
        .dashboard-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
        }
        
        .action-card {
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid rgba(27, 94, 32, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(27, 94, 32, 0.03) 0%, rgba(76, 175, 80, 0.03) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .action-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(27, 94, 32, 0.15);
            border-color: #4caf50;
            background: rgba(255, 255, 255, 1);
        }
        
        .action-card:hover::before {
            opacity: 1;
        }
        
        .action-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #1b5e20 0%, #4caf50 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            z-index: 1;
        }
        
        .action-card h3 {
            color: #1b5e20;
            margin-bottom: 1rem;
            font-size: 1.3rem;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }
        
        .action-card p {
            color: #666;
            margin-bottom: 1.5rem;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }
        
        .admin-btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #1b5e20 0%, #4caf50 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(27, 94, 32, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }
        
        .admin-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #2e7d32 0%, #66bb6a 100%);
            transition: left 0.3s ease;
            z-index: -1;
        }
        
        .admin-btn:hover::before {
            left: 0;
        }
        
        .admin-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(27, 94, 32, 0.4);
            text-decoration: none;
            color: white;
        }
        
        /* Alert Styles */
        .alert {
            padding: 1.2rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: none;
            font-weight: 500;
            backdrop-filter: blur(10px);
        }
        
        .alert-error {
            background: linear-gradient(135deg, rgba(255, 235, 238, 0.95) 0%, rgba(255, 205, 210, 0.9) 100%);
            color: #c62828;
            border-left: 4px solid #f44336;
            border: 1px solid rgba(244, 67, 54, 0.2);
        }
        
        .alert-success {
            background: linear-gradient(135deg, rgba(232, 245, 233, 0.95) 0%, rgba(200, 230, 201, 0.9) 100%);
            color: #2e7d32;
            border-left: 4px solid #4caf50;
            border: 1px solid rgba(76, 175, 80, 0.2);
        }
        
        /* Quick Stats Row */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .quick-stat {
            background: rgba(255, 255, 255, 0.9);
            padding: 1.5rem;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(27, 94, 32, 0.08);
            border: 1px solid rgba(27, 94, 32, 0.1);
        }
        
        .quick-stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1b5e20;
        }
        
        .quick-stat-label {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        /* Responsive Design - Updated for single row */
        @media (max-width: 1400px) {
            .dashboard-stats {
                gap: 0.8rem;
            }
            
            .stat-card {
                padding: 1.2rem;
                min-width: 180px;
            }
            
            .stat-card h3 {
                font-size: 0.9rem;
            }
            
            .stat-number {
                font-size: 2.2rem;
            }
            
            .stat-pending {
                font-size: 0.8rem;
                padding: 0.25rem 0.5rem;
            }
        }
        
        @media (max-width: 1200px) {
            .dashboard-stats {
                grid-template-columns: repeat(5, minmax(160px, 1fr));
                gap: 0.6rem;
            }
            
            .stat-card {
                padding: 1rem;
                min-width: 160px;
            }
            
            .stat-card h3 {
                font-size: 0.85rem;
                margin-bottom: 0.8rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .admin-dashboard {
                padding: 1rem;
            }
            
            .dashboard-header {
                padding: 2rem 1.5rem;
            }
            
            .dashboard-header h1 {
                font-size: 2.5rem;
            }
            
            .dashboard-stats {
                grid-template-columns: repeat(5, minmax(140px, 1fr));
                gap: 0.5rem;
                overflow-x: auto;
                padding-bottom: 0.5rem;
            }
            
            .stat-card {
                padding: 0.8rem;
                min-width: 140px;
            }
            
            .stat-card h3 {
                font-size: 0.75rem;
                margin-bottom: 0.6rem;
            }
            
            .stat-number {
                font-size: 1.8rem;
            }
            
            .stat-pending {
                font-size: 0.7rem;
                padding: 0.2rem 0.4rem;
            }
            
            .dashboard-actions {
                grid-template-columns: 1fr;
            }
            
            .admin-nav a {
                display: block;
                margin: 0.5rem 0;
            }
        }
        
        @media (max-width: 480px) {
            .dashboard-header h1 {
                font-size: 2rem;
            }
            
            .dashboard-stats {
                grid-template-columns: repeat(5, minmax(120px, 1fr));
                gap: 0.4rem;
            }
            
            .stat-card {
                padding: 0.6rem;
                min-width: 120px;
            }
            
            .stat-card h3 {
                font-size: 0.7rem;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .stat-pending {
                font-size: 0.65rem;
            }
            
            .action-icon {
                font-size: 2.5rem;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .stat-card, .action-card {
            animation: fadeInUp 0.6s ease;
        }
        
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        .stat-card:nth-child(5) { animation-delay: 0.5s; }

        /* Queue Monitor (Dashboard preview) */
        .queue-section .queue-stats { display: grid; grid-template-columns: repeat(5, minmax(100px, 1fr)); gap: 0.75rem; margin-bottom: 1rem; }
        .queue-section .qstat { background: rgba(27, 94, 32, 0.04); border: 1px solid rgba(27, 94, 32, 0.1); border-radius: 10px; padding: 0.75rem; text-align: center; }
        .queue-section .qnum { font-size: 1.4rem; font-weight: 800; color: #1b5e20; }
        .queue-section .qlabel { font-size: 0.8rem; color: #46634a; }
        .queue-lists { display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; }
        .queue-panel { background: rgba(255,255,255,0.95); border: 1px solid rgba(27, 94, 32, 0.1); border-radius: 12px; padding: 1rem; }
        .queue-panel h3 { margin: 0 0 0.75rem 0; color: #1b5e20; font-size: 1rem; }
        .queue-list { max-height: 300px; overflow-y: auto; display: grid; gap: 0.5rem; }
        .queue-ticket { display:flex; justify-content:space-between; align-items:center; background: rgba(27, 94, 32, 0.04); border: 1px solid rgba(27, 94, 32, 0.1); padding: 0.6rem 0.75rem; border-radius: 10px; }
        .qbadges { display:flex; gap: 6px; align-items:center; }
        .qbadge { padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; border: 1px solid transparent; }
        .qbadge.waiting { background:#fff3cd; color:#856404; border-color: rgba(133,100,4,0.15); }
        .qbadge.serving { background:#d4edda; color:#155724; border-color: rgba(21,87,36,0.15); }
        .qbadge.urgent { background:#f8d7da; color:#721c24; border-color: rgba(114,28,36,0.15); }
        .qbadge.priority { background:#cce5ff; color:#004085; border-color: rgba(0,64,133,0.15); }
        .queue-actions { display:flex; justify-content: flex-end; margin-top: 0.75rem; }
        .queue-link { display:inline-block; padding: 0.6rem 1rem; background: linear-gradient(135deg, #1b5e20 0%, #4caf50 100%); color:#fff; text-decoration:none; border-radius: 10px; font-weight: 600; }

        @media (max-width: 768px) {
            .queue-section .queue-stats { grid-template-columns: repeat(5, minmax(80px, 1fr)); }
            .queue-lists { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <div class="dashboard-header">
            <h1>🎛️ Admin Dashboard</h1>
            <p>Manage website content and form submissions</p>
        </div>
        
        <div class="admin-nav">
            <a href="forms-manager.php">📋 Forms Manager</a>
            <a href="rfid-scanner.php">📱 RFID Scanner</a>
            <a href="manage-blotter.php">📝 Blotter Management</a>
            <a href="captain-clearances.php">🛡️ Captain Clearances</a>
            <a href="blotter-reports.php">📊 Blotter Reports</a>
            <a href="queue-monitor.php">📺 Queue Monitor</a>
            <a href="../index.php" target="_blank">🌐 View Website</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <strong>⚠️ Error:</strong> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>👥 Census Registrations</h3>
                <div class="stat-number"><?php echo $resident_count; ?></div>
                <div class="stat-pending"><?php echo $pending_resident; ?> pending review</div>
            </div>
            
            <div class="stat-card">
                <h3>📄 Certificate Requests</h3>
                <div class="stat-number"><?php echo $certificate_count; ?></div>
                <div class="stat-pending"><?php echo $pending_certificate; ?> pending approval</div>
            </div>
            
            <div class="stat-card">
                <h3>🏢 Business Applications</h3>
                <div class="stat-number"><?php echo $business_count; ?></div>
                <div class="stat-pending"><?php echo $pending_business; ?> pending processing</div>
            </div>
            
            <div class="stat-card">
                <h3>📱 Available RFID Codes</h3>
                <div class="stat-number"><?php echo $rfid_available; ?></div>
                <div class="stat-pending">Ready for assignment</div>
            </div>
            
            <div class="stat-card">
                <h3>🆔 Assigned RFID Codes</h3>
                <div class="stat-number"><?php echo $rfid_assigned; ?></div>
                <div class="stat-pending">Currently in use</div>
            </div>
        </div>
        
        <div class="dashboard-main">
            <div class="dashboard-section">
                <h2 class="section-title">
                    <div class="section-icon">🎛️</div>
                    Management Tools
                </h2>
                
                <div class="dashboard-actions">
                    <div class="action-card">
                        <div class="action-icon">⚙️</div>
                        <h3>Manage Services</h3>
                        <p>Configure service cards, descriptions, and links displayed on the homepage</p>
                        <a href="manage-services.php" class="admin-btn">Manage Services</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">📢</div>
                        <h3>Manage Updates</h3>
                        <p>Add, edit, and manage community announcements and latest news updates</p>
                        <a href="manage-updates.php" class="admin-btn">Manage Updates</a>
                    </div>
                    
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
                    
                    <div class="action-card">
                        <div class="action-icon">📝</div>
                        <h3>Blotter Management</h3>
                        <p>Record and manage complaints, incidents, and disputes within the barangay</p>
                        <a href="manage-blotter.php" class="admin-btn">Manage Blotter</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">🛡️</div>
                        <h3>Captain Clearances</h3>
                        <p>Manage clearances for residents with records or requiring special permissions</p>
                        <a href="captain-clearances.php" class="admin-btn">Manage Clearances</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">📈</div>
                        <h3>Blotter Reports</h3>
                        <p>Generate reports and analyze trends in community incidents and resolutions</p>
                        <a href="blotter-reports.php" class="admin-btn">View Reports</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">📊</div>
                        <h3>Forms Manager</h3>
                        <p>Comprehensive form management and analytics</p>
                        <a href="forms-manager.php" class="admin-btn">Open Manager</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">📱</div>
                        <h3>RFID Scanner</h3>
                        <p>Scan and manage RFID codes for resident registration</p>
                        <a href="rfid-scanner.php" class="admin-btn">Open Scanner</a>
                    </div>
                    
                    
                    <div class="action-card">
                        <div class="action-icon">📋</div>
                        <h3>System Logs</h3>
                        <p>View activity logs and system events</p>
                        <a href="view-logs.php" class="admin-btn">View Logs</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="dashboard-section queue-section">
            <h2 class="section-title">
                <div class="section-icon">🖥️</div>
                Queue Monitor
            </h2>
            <div class="queue-stats" id="queue-stats">
                <div class="qstat"><div class="qnum">0</div><div class="qlabel">Waiting</div></div>
                <div class="qstat"><div class="qnum">0</div><div class="qlabel">Serving</div></div>
                <div class="qstat"><div class="qnum">0</div><div class="qlabel">Completed</div></div>
                <div class="qstat"><div class="qnum">0</div><div class="qlabel">Cancelled</div></div>
                <div class="qstat"><div class="qnum">0</div><div class="qlabel">Total</div></div>
            </div>
            <div class="queue-lists">
                <div class="queue-panel">
                    <h3>Currently Serving</h3>
                    <div class="queue-list" id="queue-serving"></div>
                </div>
                <div class="queue-panel">
                    <h3>Waiting Queue</h3>
                    <div class="queue-list" id="queue-waiting"></div>
                </div>
            </div>
            <div class="queue-actions">
                <a href="queue-monitor.php" class="queue-link">Open Full Monitor</a>
            </div>
        </div>
    </div>
    
    <script>
        // Add smooth scrolling to dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Animate numbers counting up
            const numbers = document.querySelectorAll('.stat-number');
            numbers.forEach(number => {
                const finalNumber = parseInt(number.textContent);
                let currentNumber = 0;
                const increment = finalNumber / 50;
                
                const timer = setInterval(() => {
                    currentNumber += increment;
                    if (currentNumber >= finalNumber) {
                        currentNumber = finalNumber;
                        clearInterval(timer);
                    }
                    number.textContent = Math.floor(currentNumber);
                }, 20);
            });
            
            // Add hover effects to stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
        
        // Queue Monitor (Dashboard preview)
        (function() {
            const apiUrl = '../api/queue-state.php';
            const statsEl = document.getElementById('queue-stats');
            const servingEl = document.getElementById('queue-serving');
            const waitingEl = document.getElementById('queue-waiting');
            if (!statsEl || !servingEl || !waitingEl) return;

            function escapeHtml(s) {
                if (s == null) return '';
                return String(s).replace(/[&<>\"]+/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c] || c));
            }

            function badgeForPriority(level) {
                if (level === 'urgent') return '<span class="qbadge urgent">Urgent</span>';
                if (level === 'priority') return '<span class="qbadge priority">Priority</span>';
                return '';
            }

            function renderQueue() {
                fetch(apiUrl, { credentials: 'same-origin' })
                    .then(r => r.json())
                    .then(data => {
                        const s = data && data.stats ? data.stats : {};
                        statsEl.innerHTML = `
                            <div class=\"qstat\"><div class=\"qnum\">${s.waiting || 0}</div><div class=\"qlabel\">Waiting</div></div>
                            <div class=\"qstat\"><div class=\"qnum\">${s.serving || 0}</div><div class=\"qlabel\">Serving</div></div>
                            <div class=\"qstat\"><div class=\"qnum\">${s.completed || 0}</div><div class=\"qlabel\">Completed</div></div>
                            <div class=\"qstat\"><div class=\"qnum\">${s.cancelled || 0}</div><div class=\"qlabel\">Cancelled</div></div>
                            <div class=\"qstat\"><div class=\"qnum\">${s.total || 0}</div><div class=\"qlabel\">Total</div></div>
                        `;

                        const serving = (data && data.serving) ? data.serving : [];
                        servingEl.innerHTML = serving.map(t => `
                            <div class=\"queue-ticket\">
                                <div>
                                    <div><strong>${escapeHtml(t.ticket_number)}</strong> — ${escapeHtml(t.service_name || '')}</div>
                                    <div style=\"font-size:12px;color:#555\">${escapeHtml(t.customer_name || '')}</div>
                                </div>
                                <div class=\"qbadges\">
                                    <span class=\"qbadge serving\">${escapeHtml(t.window_number || t.window_name || 'Serving')}</span>
                                </div>
                            </div>
                        `).join('');

                        const waiting = (data && data.waiting) ? data.waiting : [];
                        waitingEl.innerHTML = waiting.slice(0, 15).map(t => `
                            <div class=\"queue-ticket\">
                                <div>
                                    <div><strong>${escapeHtml(t.ticket_number)}</strong> — ${escapeHtml(t.service_name || '')}</div>
                                    <div style=\"font-size:12px;color:#555\">#${t.queue_position ?? '-'} · ${escapeHtml(t.priority_level || 'normal')}</div>
                                </div>
                                <div class=\"qbadges\">
                                    <span class=\"qbadge waiting\">Waiting</span>
                                    ${badgeForPriority(t.priority_level)}
                                </div>
                            </div>
                        `).join('');
                    })
                    .catch(() => {
                        // Silent fail on preview
                    });
            }

            renderQueue();
            setInterval(renderQueue, 10000);
        })();
    </script>
</body>
</html>
