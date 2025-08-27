<?php
require_once 'auth_check.php';
$page_title = 'User Dashboard';
$base_path = '../';
$current_page = 'dashboard';

// Use residents table data
$display_name = $user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name'];
$display_email = $user['email'] ?? 'No email';
$display_phone = $user['phone'] ?? 'No phone';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Barangay Gumaoc East</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            min-height: 100vh;
            line-height: 1.6;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx=".5" cy=".5" r=".5"><stop offset="0%" stop-color="%23ffffff" stop-opacity=".1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)"/><circle cx="800" cy="300" r="150" fill="url(%23a)"/><circle cx="400" cy="700" r="120" fill="url(%23a)"/></svg>');
            opacity: 0.3;
            z-index: 0;
        }
        
        /* User Navbar */
        .user-navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(25px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.12);
            transition: all 0.3s ease;
        }
        
        .user-navbar:hover {
            box-shadow: 0 6px 40px rgba(0, 0, 0, 0.15);
        }
        
        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            height: 70px;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #333;
            font-weight: 700;
            font-size: 18px;
            transition: all 0.3s ease;
        }
        
        .navbar-brand:hover {
            transform: scale(1.02);
        }
        
        .brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: white;
            font-size: 18px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
        }
        
        .brand-icon:hover {
            transform: scale(1.05) rotate(5deg);
            box-shadow: 0 6px 20px rgba(46, 125, 50, 0.4);
        }
        
        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-item {
            position: relative;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            color: #666;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(46, 125, 50, 0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .nav-link:hover::before {
            left: 100%;
        }
        
        .nav-link:hover {
            background: #f8f9fa;
            color: #333;
            transform: translateY(-1px);
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, #e7f3ff, #f0f8ff);
            color: #0066cc;
            box-shadow: 0 2px 8px rgba(0, 102, 204, 0.2);
        }
        
        .user-menu {
            position: relative;
        }
        
        .user-button {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border: none;
            background: #f8f9fa;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .user-button:hover {
            background: #e9ecef;
            transform: scale(1.02);
        }
        
        .user-avatar-small {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 12px 0;
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .user-menu:hover .user-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .dropdown-item:hover {
            background: #f8f9fa;
            transform: translateX(5px);
        }
        
        .dropdown-divider {
            height: 1px;
            background: #e9ecef;
            margin: 8px 0;
        }
        
        /* Mobile hamburger menu */
        .mobile-menu-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 8px;
            border: none;
            background: none;
            transition: all 0.3s ease;
        }
        
        .hamburger-line {
            width: 25px;
            height: 3px;
            background: #333;
            margin: 3px 0;
            transition: all 0.3s ease;
            border-radius: 2px;
        }
        
        .mobile-menu-toggle.active .hamburger-line:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }
        
        .mobile-menu-toggle.active .hamburger-line:nth-child(2) {
            opacity: 0;
        }
        
        .mobile-menu-toggle.active .hamburger-line:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -6px);
        }
        
        .mobile-nav {
            display: none;
            position: fixed;
            top: 70px;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(25px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            z-index: 999;
            padding: 20px;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease;
        }
        
        .mobile-nav.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        
        .mobile-nav-item {
            display: block;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .mobile-nav-item:last-child {
            border-bottom: none;
        }
        
        .mobile-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #666;
            font-weight: 500;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .mobile-nav-link:hover {
            color: #2e7d32;
            transform: translateX(10px);
        }
        
        .mobile-nav-link.active {
            color: #2e7d32;
            font-weight: 600;
        }
        
        .mobile-user-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 16px;
            margin-top: 16px;
        }
        
        .mobile-user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
            margin-right: 12px;
        }
        
        .mobile-user-details h4 {
            color: #333;
            font-size: 16px;
            margin-bottom: 4px;
        }
        
        .mobile-user-details p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        
        /* Loading Animation */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
        
        .page-loader.hidden {
            opacity: 0;
            visibility: hidden;
        }
        
        .loader-icon {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        .loader-text {
            color: white;
            font-size: 18px;
            font-weight: 500;
            opacity: 0.9;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Dashboard Content */
        .dashboard-content {
            padding-top: 90px;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .welcome-section {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(25px);
            border-radius: 24px;
            padding: 50px 40px;
            margin-bottom: 30px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            animation: slideUp 0.8s ease-out;
        }
        
        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #2e7d32, transparent);
            animation: shimmer 3s infinite;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        .welcome-section h1 {
            color: #333;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .welcome-section p {
            color: #666;
            font-size: 18px;
            margin-bottom: 30px;
        }
        
        .user-info-card {
            display: inline-flex;
            align-items: center;
            gap: 20px;
            background: #f8f9fa;
            padding: 20px 30px;
            border-radius: 15px;
            border: 1px solid #e9ecef;
        }
        
        .user-avatar-large {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            font-weight: 700;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.3);
            transition: all 0.3s ease;
        }
        
        .user-avatar-large:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 35px rgba(46, 125, 50, 0.4);
        }
        
        .user-details {
            text-align: left;
        }
        
        .user-details h3 {
            color: #333;
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .user-details p {
            color: #666;
            margin: 0;
            font-size: 14px;
        }
        
        /* Services Section */
        .services-section {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(25px);
            border-radius: 24px;
            padding: 50px 40px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            animation: slideUp 0.8s ease-out 0.2s both;
        }
        
        .section-title {
            color: #333;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .service-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 20px;
            padding: 35px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            transform: translateY(0);
            animation: slideUp 0.6s ease-out calc(0.4s + var(--delay, 0s)) both;
        }
        
        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .service-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, rgba(46, 125, 50, 0.05) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .service-card:nth-child(1) { --delay: 0s; }
        .service-card:nth-child(2) { --delay: 0.1s; }
        .service-card:nth-child(3) { --delay: 0.2s; }
        
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
            border-color: #2e7d32;
        }
        
        .service-card:hover::before {
            transform: scaleX(1);
        }
        
        .service-card:hover::after {
            opacity: 1;
        }
        
        .service-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 35px;
            color: white;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 30px rgba(46, 125, 50, 0.3);
            position: relative;
        }
        
        .service-icon::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, #2e7d32, #4caf50, #2e7d32);
            border-radius: 22px;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .service-card:hover .service-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 15px 40px rgba(46, 125, 50, 0.4);
        }
        
        .service-card:hover .service-icon::before {
            opacity: 1;
        }
        
        .service-card h3 {
            color: #333;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .service-card p {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        
        .service-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .service-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(46, 125, 50, 0.3);
            color: white;
        }
        
        /* Emergency Response Card Styling */
        .emergency-card {
            border: 2px solid #ff9800;
            background: linear-gradient(135deg, #fff3e0, #ffeaa7);
        }
        
        .emergency-card:hover {
            border-color: #f57c00;
            box-shadow: 0 25px 60px rgba(255, 152, 0, 0.2);
        }
        
        .emergency-icon {
            background: linear-gradient(135deg, #ff9800, #f57c00) !important;
            box-shadow: 0 10px 30px rgba(255, 152, 0, 0.4) !important;
        }
        
        .emergency-card:hover .emergency-icon {
            box-shadow: 0 15px 40px rgba(255, 152, 0, 0.5) !important;
        }
        
        .emergency-btn {
            background: linear-gradient(135deg, #ff9800, #f57c00) !important;
            margin-bottom: 10px;
            display: block;
            width: 100%;
        }
        
        .emergency-btn:hover {
            box-shadow: 0 8px 20px rgba(255, 152, 0, 0.4) !important;
        }
        
        .emergency-hotline {
            background: linear-gradient(135deg, #f44336, #d32f2f) !important;
            display: block;
            width: 100%;
        }
        
        .emergency-hotline:hover {
            box-shadow: 0 8px 20px rgba(244, 67, 54, 0.4) !important;
        }
        
        /* Quick Stats */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(25px);
            border-radius: 20px;
            padding: 30px 25px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            animation: slideUp 0.6s ease-out calc(0.6s + var(--delay, 0s)) both;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(46, 125, 50, 0.05), rgba(76, 175, 80, 0.05));
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .stat-card:nth-child(1) { --delay: 0s; }
        .stat-card:nth-child(2) { --delay: 0.1s; }
        .stat-card:nth-child(3) { --delay: 0.2s; }
        .stat-card:nth-child(4) { --delay: 0.3s; }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card:hover::before {
            opacity: 1;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }
        
        @keyframes fadeInContent {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive Design */
        @media (max-width: 968px) {
            .navbar-nav {
                display: none;
            }
            
            .mobile-menu-toggle {
                display: flex;
            }
            
            .welcome-section {
                padding: 30px 20px;
            }
            
            .welcome-section h1 {
                font-size: 28px;
            }
            
            .user-info-card {
                flex-direction: column;
                text-align: center;
            }
            
            .services-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .services-section {
                padding: 30px 20px;
            }
        }
        
        @media (max-width: 480px) {
            .navbar-container {
                padding: 0 15px;
            }
            
            .dashboard-container {
                padding: 0 15px;
            }
            
            .welcome-section {
                padding: 20px 15px;
                margin-bottom: 20px;
            }
            
            .welcome-section h1 {
                font-size: 24px;
            }
            
            .user-avatar-large {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
            
            .service-card {
                padding: 25px 20px;
            }
            
            .service-icon {
                width: 60px;
                height: 60px;
                font-size: 28px;
            }
            
            .quick-stats {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .stat-card {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar_component.php'; ?>
    
    <!-- Dashboard Content -->
    <div class="dashboard-content">
        <div class="dashboard-container">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h1>Welcome back, <?php echo htmlspecialchars(explode(' ', $display_name)[0]); ?>!</h1>
                <p>Your personalized portal for barangay services and community engagement</p>
                
                <div class="user-info-card">
                    <div class="user-avatar-large">
                        <?php echo strtoupper(substr($display_name, 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <h3><?php echo htmlspecialchars($display_name); ?></h3>
                        <p>📧 <?php echo htmlspecialchars($display_email); ?></p>
                        <?php if ($display_phone && $display_phone !== 'No phone'): ?>
                            <p>📱 <?php echo htmlspecialchars($display_phone); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="stat-card">
                    <div class="stat-number">2</div>
                    <div class="stat-label">Available Services</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Active Requests</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Service Access</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Digital Services</div>
                </div>
            </div>
            
            <!-- Services Section -->
            <div class="services-section">
                <h3 class="section-title">
                    <i class="fas fa-th-large"></i>
                    Available Services
                </h3>
                
                <div class="services-grid">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-desktop"></i>
                        </div>
                        <h3>E-Services Portal</h3>
                        <p>Access all available digital services, business permits, certificates, and community programs in one convenient location.</p>
                        <a href="e-services.php" class="service-btn">
                            <i class="fas fa-arrow-right"></i>
                            Explore Services
                        </a>
                    </div>
                    
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <h3>Dashboard Overview</h3>
                        <p>Your personal dashboard with quick stats, recent activity, and easy access to all your account information.</p>
                        <a href="dashboard.php" class="service-btn">
                            <i class="fas fa-refresh"></i>
                            Refresh Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Hide page loader
            setTimeout(function() {
                const loader = document.getElementById('pageLoader');
                loader.classList.add('hidden');
                setTimeout(() => loader.remove(), 500);
            }, 1000);
            
            // Mobile menu functionality
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const mobileNav = document.getElementById('mobileNav');
            
            mobileMenuToggle.addEventListener('click', function() {
                this.classList.toggle('active');
                mobileNav.classList.toggle('active');
                
                // Prevent body scroll when menu is open
                if (mobileNav.classList.contains('active')) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = 'auto';
                }
            });
            
            // Close mobile menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!mobileMenuToggle.contains(e.target) && !mobileNav.contains(e.target)) {
                    mobileMenuToggle.classList.remove('active');
                    mobileNav.classList.remove('active');
                    document.body.style.overflow = 'auto';
                }
            });
            
            // Close mobile menu when window resizes to desktop size
            window.addEventListener('resize', function() {
                if (window.innerWidth > 968) {
                    mobileMenuToggle.classList.remove('active');
                    mobileNav.classList.remove('active');
                    document.body.style.overflow = 'auto';
                }
            });
            
            // Add smooth scrolling to service cards
            const serviceCards = document.querySelectorAll('.service-card');
            
            serviceCards.forEach((card, index) => {
                card.style.animationDelay = `${0.1 * index}s`;
                
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-10px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
            
            // Enhanced stat cards animation
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px) scale(1.05)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
            
            // Unified Button Click Handler
            const allButtons = document.querySelectorAll('.service-btn');
            allButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    console.log('Button clicked:', this.href || this.textContent.trim(), 'Classes:', this.className);
                    
                    // Add ripple effect for all buttons
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.classList.add('ripple');
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        if (ripple.parentNode) {
                            ripple.remove();
                        }
                    }, 600);
                    
                    // Handle different button types
                    if (this.classList.contains('emergency-hotline')) {
                        // Emergency hotline - prevent default and show confirmation
                        e.preventDefault();
                        const confirmCall = confirm(
                            'Are you sure you want to call emergency services (911)?\n\n' +
                            'This will dial emergency services immediately. ' +
                            'Only call if this is a real emergency requiring immediate assistance.'
                        );
                        
                        if (confirmCall) {
                            window.location.href = 'tel:911';
                            showNotification('Calling emergency services...', 'warning');
                        }
                    } else if (this.classList.contains('emergency-btn')) {
                        // Emergency report - show confirmation but allow navigation if confirmed
                        const confirmReport = confirm(
                            'You are about to report an emergency or incident.\n\n' +
                            'Please make sure this is for a legitimate emergency or incident that needs to be reported to barangay officials.'
                        );
                        
                        if (confirmReport) {
                            showNotification('Redirecting to emergency report form...', 'info');
                            // Allow normal navigation to proceed
                        } else {
                            e.preventDefault();
                        }
                    } else {
                        // Regular buttons - just add visual feedback and allow navigation
                        this.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            this.style.transform = '';
                        }, 150);
                        
                        if (this.href) {
                            showNotification('Loading...', 'info');
                        }
                    }
                });
            });
            
            // Add hover effect to navbar links
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-1px)';
                });
                
                link.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Add notification system
            function showNotification(message, type = 'success') {
                const notification = document.createElement('div');
                notification.className = `notification notification-${type}`;
                
                let icon = 'check-circle';
                if (type === 'error') icon = 'exclamation-circle';
                else if (type === 'warning') icon = 'exclamation-triangle';
                else if (type === 'info') icon = 'info-circle';
                
                notification.innerHTML = `
                    <i class="fas fa-${icon}"></i>
                    <span>${message}</span>
                    <button onclick="this.parentElement.remove()" class="notification-close">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.classList.add('show');
                }, 100);
                
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 5000);
            }
            
            // Welcome message (optional)
            setTimeout(() => {
                showNotification(`Welcome back, ${document.querySelector('.welcome-section h1').textContent.split(',')[1].trim()}!`, 'success');
            }, 2000);
        });
    </script>
    
    <style>
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            transform: scale(0);
            animation: ripple-animation 0.6s ease-out;
            pointer-events: none;
        }
        
        @keyframes ripple-animation {
            to {
                transform: scale(2);
                opacity: 0;
            }
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-radius: 12px;
            padding: 16px 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 10000;
            transform: translateX(400px);
            transition: all 0.3s ease;
            border-left: 4px solid #4caf50;
            max-width: 350px;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification-error {
            border-left-color: #f44336;
        }
        
        .notification-warning {
            border-left-color: #ff9800;
        }
        
        .notification-info {
            border-left-color: #2196f3;
        }
        
        .notification i {
            color: #4caf50;
            font-size: 18px;
        }
        
        .notification-error i {
            color: #f44336;
        }
        
        .notification-warning i {
            color: #ff9800;
        }
        
        .notification-info i {
            color: #2196f3;
        }
        
        .notification-close {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 4px;
            margin-left: auto;
        }
        
        .notification-close:hover {
            color: #333;
        }
    </style>
</body>
</html> 