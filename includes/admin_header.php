<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get admin user data
$admin_user = ['full_name' => 'Admin User'];
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    if (!isset($pdo)) {
        try {
            require_once __DIR__ . '/db_connect.php';
        } catch (Exception $e) {
            // Keep default values if connection fails
        }
    }
    
    if (isset($pdo) && isset($_SESSION['admin_id'])) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user_data && isset($user_data['full_name'])) {
                $admin_user = $user_data;
            } elseif ($user_data && isset($user_data['username'])) {
                $admin_user['full_name'] = $user_data['username'];
            }
        } catch (Exception $e) {
            // Keep default values if query fails
        }
    } elseif (isset($_SESSION['admin_username'])) {
        $admin_user['full_name'] = $_SESSION['admin_username'];
    }
}

$page_title = $page_title ?? 'Admin Panel - Barangay Gumaoc East';
$page_description = $page_description ?? 'Administration Panel for Barangay Gumaoc East';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_path ?? '../'; ?>css/styles.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ffffff 0%, #f0f8f0 20%, #e8f5e8 40%, #c8e6c9 70%, #a5d6a7 100%);
            background-attachment: fixed;
            min-height: 100vh;
            position: relative;
        }
        
        /* Subtle pattern overlay for admin */
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
        
        .admin-navbar {
            background: linear-gradient(90deg, #1b5e20 0%, #2e7d32 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 20px rgba(27, 94, 32, 0.3);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: all 0.3s ease;
            height: 70px;
        }
        
        .admin-navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            padding: 0 15px;
            height: 100%;
        }
        
        .admin-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
            padding: 6px 10px;
            border-radius: 8px;
            flex: 0 0 auto;
            max-width: 250px;
        }
        
        .admin-brand:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
            color: white;
        }
        
        .admin-brand-logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.25), rgba(255, 255, 255, 0.15));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
            margin-right: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        
        .admin-brand:hover .admin-brand-logo {
            transform: rotate(360deg) scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }
        
        .admin-brand-text h1 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 2px;
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .admin-brand-text p {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.8);
            margin: 0;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .admin-nav-menu {
            display: flex;
            align-items: center;
            list-style: none;
            gap: 8px;
            flex: 1;
            justify-content: flex-end;
        }
        
        .admin-nav-link {
            text-decoration: none;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            position: relative;
            overflow: hidden;
            white-space: nowrap;
        }
        
        .admin-nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
            transition: left 0.3s ease;
            z-index: -1;
        }
        
        .admin-nav-link:hover::before {
            left: 0;
        }
        
        .admin-nav-link:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }
        
        .admin-nav-link.active {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .dashboard-btn {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            white-space: nowrap;
        }
        
        .dashboard-btn:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0.2) 100%);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-1px) scale(1.03);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.2);
            color: white;
        }
        
        .dashboard-btn i {
            font-size: 1rem;
        }
        
        .admin-user-info {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.85rem;
            padding: 6px 12px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .admin-user-info:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }
        
        .admin-user-info strong {
            color: white;
            font-weight: 600;
        }
        
        /* Main content spacing */
        .admin-main-content {
            margin-top: 70px;
            min-height: calc(100vh - 70px);
            padding-top: 0;
        }
        
        /* Success/Error Messages */
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
        
        /* Admin button styles */
        .admin-btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: linear-gradient(135deg, #1b5e20 0%, #4caf50 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .admin-btn:hover {
            background: linear-gradient(135deg, #2e7d32 0%, #66bb6a 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(27, 94, 32, 0.3);
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .admin-navbar-container {
                padding: 0 10px;
            }
            
            .admin-nav-menu {
                gap: 4px;
                flex-wrap: wrap;
            }
            
            .admin-nav-link {
                padding: 6px 8px;
                font-size: 0.8rem;
            }
            
            .dashboard-btn {
                padding: 6px 8px;
                font-size: 0.8rem;
            }
            
            .admin-brand {
                padding: 4px 8px;
                max-width: 200px;
            }
            
            .admin-brand-text h1 {
                font-size: 16px;
            }
            
            .admin-brand-text p {
                font-size: 10px;
            }
            
            .admin-brand-logo {
                width: 35px;
                height: 35px;
                margin-right: 8px;
                font-size: 14px;
            }
            
            .admin-user-info {
                font-size: 0.75rem;
                padding: 6px 8px;
            }
        }
        
        @media (max-width: 480px) {
            .admin-navbar {
                height: auto;
                min-height: 70px;
            }
            
            .admin-navbar-container {
                flex-direction: column;
                height: auto;
                padding: 8px 10px;
                gap: 8px;
            }
            
            .admin-brand {
                margin-bottom: 0;
                max-width: none;
                align-self: center;
            }
            
            .admin-brand-text h1 {
                font-size: 14px;
            }
            
            .admin-brand-text p {
                font-size: 9px;
            }
            
            .admin-nav-menu {
                justify-content: center;
                gap: 3px;
                width: 100%;
            }
            
            .admin-nav-link {
                padding: 5px 6px;
                font-size: 0.7rem;
            }
            
            .dashboard-btn {
                padding: 5px 6px;
                font-size: 0.7rem;
            }
            
            .admin-main-content {
                margin-top: 90px;
            }
        }
    </style>
</head>
<body>
    <nav class="admin-navbar">
        <div class="admin-navbar-container">
            <a href="dashboard.php" class="admin-brand">
                <div class="admin-brand-logo">
                    <i class="fas fa-cogs"></i>
                </div>
                <div class="admin-brand-text">
                    <h1>Admin Panel</h1>
                    <p>Barangay Gumaoc East</p>
                </div>
            </a>
            
            <ul class="admin-nav-menu">
                <li><a href="javascript:history.back()" class="admin-nav-link" title="Go Back"><i class="fas fa-arrow-left"></i> Back</a></li>
                <li><a href="dashboard.php" class="dashboard-btn"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="queue-admin.php" class="admin-nav-link"><i class="fas fa-list-ol"></i> Queue Management</a></li>
                <li><a href="rfid-scanner.php" class="admin-nav-link"><i class="fas fa-qrcode"></i> RFID Scanner</a></li>
                <li><a href="manage-rfid.php" class="admin-nav-link"><i class="fas fa-id-card"></i> RFID Management</a></li>
                <li><a href="view-resident-registrations.php" class="admin-nav-link"><i class="fas fa-users"></i> Residents</a></li>
                <li><a href="../index.php" class="admin-nav-link" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li>
                    <span class="admin-user-info">Welcome, <strong><?php echo htmlspecialchars($admin_user['full_name']); ?></strong></span>
                </li>
                <li><a href="logout.php" class="admin-nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <main class="admin-main-content">