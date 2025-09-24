<?php
require_once 'auth_check.php';
$page_title = 'E-Services';
$base_path = '../';
$current_page = 'e-services';

// Use residents table data
$display_name = $user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Barangay Gumaoc East</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/background.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            min-height: 100vh;
            line-height: 1.6;
            opacity: 0;
            animation: fadeInPage 0.8s ease-out 0.3s forwards;
        }
        
        @keyframes fadeInPage {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Page Content */
        .page-content {
            padding-top: 90px;
            min-height: 100vh;
        }
        
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
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
        }
        
        .dropdown-divider {
            height: 1px;
            background: #e9ecef;
            margin: 8px 0;
        }
        
        /* Page Content */
        .page-content {
            padding-top: 90px;
            min-height: 100vh;
        }
.e-services-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.search-filter-section {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(25px);
    border-radius: 24px;
    padding: 25px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
    border: 1px solid rgba(255, 255, 255, 0.4);
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: center;
}

.search-container {
    position: relative;
    flex: 1;
    min-width: 300px;
}

.search-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
}

.search-input {
    width: 100%;
    padding: 15px 15px 15px 45px;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #4caf50;
    box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
}

.filter-container {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.filter-select {
    padding: 12px 15px;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    background: white;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: #333;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: #4caf50;
    box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
}

.page-header {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(25px);
    border-radius: 24px;
    padding: 40px;
    margin-bottom: 30px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
    text-align: center;
    border: 1px solid rgba(255, 255, 255, 0.4);
    position: relative;
    overflow: hidden;
}

.page-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #2e7d32, #4caf50, #2e7d32);
    background-size: 200% 100%;
    animation: shimmer 3s infinite;
}

.header-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #2e7d32, #4caf50);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 24px;
    color: white;
    box-shadow: 0 10px 20px rgba(46, 125, 50, 0.3);
}

.page-header h1 {
    color: #333;
    margin-bottom: 10px;
    font-size: 36px;
    font-weight: 700;
    background: linear-gradient(135deg, #2e7d32, #4caf50);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    position: relative;
}

.page-header p {
    color: #666;
    font-size: 18px;
}

.back-btn {
    display: inline-block;
    padding: 12px 30px;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-bottom: 20px;
}

.back-btn:hover {
    background: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(108, 117, 125, 0.3);
    color: white;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

.service-card {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(25px);
    border-radius: 24px;
    padding: 35px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    text-align: center;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.4);
    transform: translateY(0);
    opacity: 0;
    animation: slideUp 0.6s ease forwards;
    animation-delay: var(--delay, 0s);
}

.service-card:nth-child(1) { --delay: 0s; }
.service-card:nth-child(2) { --delay: 0.1s; }
.service-card:nth-child(3) { --delay: 0.2s; }
.service-card:nth-child(4) { --delay: 0.3s; }
.service-card:nth-child(5) { --delay: 0.4s; }
.service-card:nth-child(6) { --delay: 0.5s; }
.service-card:nth-child(7) { --delay: 0.6s; }
.service-card:nth-child(8) { --delay: 0.7s; }

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

.service-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.service-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.12);
}

.service-card:hover::before {
    transform: scaleX(1);
}

.service-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    font-size: 35px;
    color: white;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 30px rgba(46, 125, 50, 0.3);
}

.service-card:hover .service-icon {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 15px 40px rgba(46, 125, 50, 0.4);
}

.service-card h3 {
    color: #333;
    margin-bottom: 15px;
    font-size: 24px;
}

.service-card p {
    color: #666;
    margin-bottom: 25px;
    line-height: 1.6;
}

.service-features {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 25px;
    justify-content: center;
}

.feature-tag {
    background: rgba(46, 125, 50, 0.1);
    color: #2e7d32;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
}

.service-btn {
    display: inline-block;
    padding: 14px 32px;
    background: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%);
    color: white;
    text-decoration: none;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    margin: 5px;
    box-shadow: 0 8px 25px rgba(46, 125, 50, 0.3);
    position: relative;
    overflow: hidden;
    cursor: pointer;
    border: none;
    font-size: 14px;
    line-height: 1.5;
}

.service-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.6s;
}

.service-btn:hover::before {
    left: 100%;
}

.service-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(46, 125, 50, 0.4);
    color: white;
    text-decoration: none;
}

.service-btn:active {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(46, 125, 50, 0.3);
}

.service-btn:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.3), 0 15px 40px rgba(46, 125, 50, 0.4);
}

.service-btn.secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
}

.service-btn.secondary:hover {
    background: linear-gradient(135deg, #5a6268, #495057);
    box-shadow: 0 15px 40px rgba(108, 117, 125, 0.4);
    transform: translateY(-3px);
}

.service-btn.secondary:active {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(108, 117, 125, 0.3);
}

.service-btn.danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
    box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
    animation: pulse 2s infinite;
}

.service-btn.danger:hover {
    background: linear-gradient(135deg, #c82333, #bd2130);
    box-shadow: 0 15px 40px rgba(220, 53, 69, 0.4);
    transform: translateY(-3px);
    animation: none;
}

.service-btn.danger:active {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(220, 53, 69, 0.3);
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
    }
    50% {
        box-shadow: 0 12px 35px rgba(220, 53, 69, 0.5);
    }
}

/* Loading state for buttons */
.service-btn.loading {
    pointer-events: none;
    opacity: 0.8;
}

.service-btn.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid transparent;
    border-top: 2px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Service Notification System */
.service-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    max-width: 400px;
    opacity: 0;
    transform: translateY(-100%);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.service-notification-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    padding: 20px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    border-left: 4px solid #2e7d32;
}

.service-notification-info .service-notification-content {
    border-left-color: #007bff;
}

.service-notification-success .service-notification-content {
    border-left-color: #28a745;
}

.service-notification-warning .service-notification-content {
    border-left-color: #ffc107;
}

.service-notification-error .service-notification-content {
    border-left-color: #dc3545;
}

.service-notification-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: white;
    background: #2e7d32;
    flex-shrink: 0;
}

.service-notification-info .service-notification-icon {
    background: #007bff;
}

.service-notification-success .service-notification-icon {
    background: #28a745;
}

.service-notification-warning .service-notification-icon {
    background: #ffc107;
    color: #333;
}

.service-notification-error .service-notification-icon {
    background: #dc3545;
}

.service-notification-message {
    flex: 1;
    color: #333;
    font-size: 14px;
    line-height: 1.5;
}

.service-notification-close {
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    font-size: 16px;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.service-notification-close:hover {
    background: #f8f9fa;
    color: #333;
}

/* Mobile responsive for notifications */
@media (max-width: 480px) {
    .service-notification {
        left: 10px;
        right: 10px;
        top: 10px;
        max-width: none;
    }
    
    .service-notification-content {
        padding: 15px;
    }
}

.no-results {
    grid-column: 1 / -1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    padding: 60px 40px;
    background: rgba(255, 255, 255, 0.98);
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
    text-align: center;
    border: 1px solid rgba(255, 255, 255, 0.4);
}

.no-results-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #6c757d, #5a6268);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 35px;
    color: white;
    opacity: 0.7;
}

.no-results h3 {
    color: #333;
    font-size: 24px;
    margin: 0;
}

.no-results p {
    color: #666;
    font-size: 16px;
    margin: 0;
    max-width: 400px;
    line-height: 1.5;
}

.service-status {
    position: absolute;
    top: 15px;
    right: 15px;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-available {
    background: #d4edda;
    color: #155724;
}

.status-coming-soon {
    background: #fff3cd;
    color: #856404;
}

.status-maintenance {
    background: #f8d7da;
    color: #721c24;
}

.quick-actions {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(25px);
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
    border: 1px solid rgba(255, 255, 255, 0.4);
}

.quick-actions h2 {
    color: #333;
    margin-bottom: 25px;
    font-size: 24px;
    text-align: center;
    font-weight: 600;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
    padding: 25px 20px;
    background: rgba(46, 125, 50, 0.05);
    border-radius: 16px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s ease;
    border: 1px solid rgba(46, 125, 50, 0.1);
    cursor: pointer;
}

.quick-action-btn:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(46, 125, 50, 0.15);
    background: rgba(46, 125, 50, 0.1);
    color: #333;
    text-decoration: none;
}

.quick-action-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #2e7d32, #4caf50);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    transition: all 0.3s ease;
}

.quick-action-btn:hover .quick-action-icon {
    transform: scale(1.1);
    box-shadow: 0 10px 20px rgba(46, 125, 50, 0.3);
}

.quick-action-text {
    font-weight: 600;
    font-size: 14px;
    text-align: center;
    line-height: 1.4;
}

/* Responsive Design */
@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

@media (max-width: 768px) {
    .navbar-nav {
        display: none;
    }
    
    .mobile-menu-toggle {
        display: block;
    }
    
    .services-grid {
        grid-template-columns: 1fr;
    }
    
    .page-header {
        padding: 30px 20px;
    }
    
    .page-header h1 {
        font-size: 28px;
    }
    
    .service-card {
        padding: 25px 20px;
    }
    
    .search-filter-section {
        flex-direction: column;
        align-items: stretch;
        padding: 20px;
    }
    
    .search-container {
        min-width: auto;
    }
    
    .filter-container {
        width: 100%;
    }
    
    .filter-select {
        flex: 1;
    }
    
    .quick-actions-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .quick-action-btn {
        padding: 20px 15px;
    }
    
    .quick-action-icon {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    .quick-action-text {
        font-size: 12px;
    }
    
    .e-services-page {
        padding: 0 15px;
    }
    
    .navbar-container {
        padding: 0 15px;
    }
    
    .user-menu {
        position: relative;
    }
    
    .user-button {
        font-size: 14px;
    }
    
    .user-button span {
        display: none;
    }
}

@media (max-width: 480px) {
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }
    
    .page-header h1 {
        font-size: 24px;
    }
    
    .quick-actions {
        padding: 25px 15px;
    }
}
</style>
</head>
<body>


    <?php include 'navbar_component.php'; ?>
    
    <!-- Page Content -->
    <div class="page-content">
        <div class="e-services-page">

            <!-- Page Header -->
            <div class="page-header">
                <div class="header-icon">
                    <i class="fas fa-laptop"></i>
                </div>
                <h1>E-Services Portal</h1>
                <p>Access all available electronic services from the comfort of your home</p>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="quick-actions-grid">
                    <a href="#document-requests" class="quick-action-btn">
                        <div class="quick-action-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="quick-action-text">Document Requests</div>
                    </a>
                    <a href="#business-services" class="quick-action-btn">
                        <div class="quick-action-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="quick-action-text">Business Services</div>
                    </a>
                    <a href="#community-services" class="quick-action-btn">
                        <div class="quick-action-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="quick-action-text">Community Services</div>
                    </a>
                    <a href="#emergency-services" class="quick-action-btn">
                        <div class="quick-action-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="quick-action-text">Emergency Services</div>
                    </a>
                </div>
            </div>

            <!-- Services Grid -->
            <div class="services-grid">
                <!-- Document Requests -->
                <div class="service-card" id="document-requests">
                    <div class="service-status status-available">Available</div>
                    <div class="service-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3>Document Requests</h3>
                    <p>Request official documents, certificates, and clearances online. Fast processing and digital delivery available.</p>
                    <div class="service-features">
                        <span class="feature-tag">Barangay Clearance</span>
                        <span class="feature-tag">Indigency Certificate</span>
                        <span class="feature-tag">Residency Certificate</span>
                    </div>
                    <a href="certificate-request.php" class="service-btn">Request Document</a>
                    <a href="my-requests.php" class="service-btn secondary">Track Status</a>
                </div>

                <!-- Business Applications -->
                <div class="service-card" id="business-services">
                    <div class="service-status status-available">Available</div>
                    <div class="service-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3>Business Applications</h3>
                    <p>Apply for business permits, licenses, and registrations. Streamlined process for entrepreneurs and business owners.</p>
                    <div class="service-features">
                        <span class="feature-tag">Business Permit</span>
                        <span class="feature-tag">Market Stall</span>
                        <span class="feature-tag">Home Business</span>
                    </div>
                    <a href="business-application.php" class="service-btn">Apply Now</a>
                    <a href="../pages/services.php" class="service-btn secondary">Requirements</a>
                </div>

                <!-- Emergency Response -->
                <div class="service-card" id="emergency-services">
                    <div class="service-status status-available">Available</div>
                    <div class="service-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3>Emergency Response</h3>
                    <p>Report emergencies and incidents in real-time. Get immediate response from our emergency services team.</p>
                    <div class="service-features">
                        <span class="feature-tag">24/7 Available</span>
                        <span class="feature-tag">Real-time Tracking</span>
                        <span class="feature-tag">Emergency Contacts</span>
                    </div>
                    <a href="reports.php" class="service-btn">Report Emergency</a>
                    <a href="tel:911" class="service-btn danger">Emergency Hotline: 911</a>
                </div>

                <!-- Infrastructure Requests -->
                <div class="service-card">
                    <div class="service-status status-available">Available</div>
                    <div class="service-icon">
                        <i class="fas fa-hard-hat"></i>
                    </div>
                    <h3>Infrastructure Requests</h3>
                    <p>Report infrastructure issues, request repairs, and track maintenance projects in your area.</p>
                    <div class="service-features">
                        <span class="feature-tag">Road Repairs</span>
                        <span class="feature-tag">Street Lights</span>
                        <span class="feature-tag">Drainage</span>
                    </div>
                    <a href="reports.php" class="service-btn">Report Issue</a>
                    <a href="my-requests.php" class="service-btn secondary">Track Progress</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Mobile menu toggle functionality
        function toggleMobileMenu() {
            const mobileNav = document.getElementById('mobileNav');
            const toggleBtn = document.querySelector('.mobile-menu-toggle i');
            
            mobileNav.classList.toggle('active');
            
            // Change icon
            if (mobileNav.classList.contains('active')) {
                toggleBtn.className = 'fas fa-times';
            } else {
                toggleBtn.className = 'fas fa-bars';
            }
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const mobileNav = document.getElementById('mobileNav');
            const toggleBtn = document.querySelector('.mobile-menu-toggle');
            
            if (!mobileNav.contains(event.target) && !toggleBtn.contains(event.target)) {
                mobileNav.classList.remove('active');
                document.querySelector('.mobile-menu-toggle i').className = 'fas fa-bars';
            }
        });
        
        // Service filtering functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('serviceSearch');
            const statusFilter = document.getElementById('statusFilter');
            const categoryFilter = document.getElementById('categoryFilter');
            const serviceCards = document.querySelectorAll('.service-card');
            
            // Service categories mapping
            const serviceCategories = {
                'document-requests': 'documents',
                'business-services': 'business',
                'community-services': 'community',
                'emergency-services': 'emergency'
            };
            
            // Function to filter services
            function filterServices() {
                const searchTerm = searchInput.value.toLowerCase();
                const statusValue = statusFilter.value;
                const categoryValue = categoryFilter.value;
                
                let visibleCount = 0;
                
                serviceCards.forEach(card => {
                    const title = card.querySelector('h3').textContent.toLowerCase();
                    const description = card.querySelector('p').textContent.toLowerCase();
                    const status = card.querySelector('.service-status').textContent.toLowerCase();
                    const categoryId = card.id;
                    
                    // Check search term
                    const matchesSearch = searchTerm === '' || 
                        title.includes(searchTerm) || 
                        description.includes(searchTerm);
                    
                    // Check status
                    let matchesStatus = true;
                    if (statusValue !== 'all') {
                        matchesStatus = status.includes(statusValue);
                    }
                    
                    // Check category
                    let matchesCategory = true;
                    if (categoryValue !== 'all') {
                        if (categoryId && serviceCategories[categoryId]) {
                            matchesCategory = serviceCategories[categoryId] === categoryValue;
                        } else {
                            // For services without specific IDs, check the title
                            const cardTitle = card.querySelector('h3').textContent.toLowerCase();
                            if (categoryValue === 'infrastructure' && cardTitle.includes('infrastructure')) {
                                matchesCategory = true;
                            } else {
                                matchesCategory = false;
                            }
                        }
                    }
                    
                    // Show/hide card based on filters
                    if (matchesSearch && matchesStatus && matchesCategory) {
                        card.style.display = 'block';
                        card.style.animation = 'slideUp 0.3s ease forwards';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Show "no results" message if no cards are visible
                showNoResultsMessage(visibleCount === 0);
            }
            
            // Function to show/hide no results message
            function showNoResultsMessage(show) {
                let noResultsEl = document.getElementById('noResults');
                
                if (show && !noResultsEl) {
                    noResultsEl = document.createElement('div');
                    noResultsEl.id = 'noResults';
                    noResultsEl.className = 'no-results';
                    noResultsEl.innerHTML = `
                        <div class="no-results-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>No services found</h3>
                        <p>Try adjusting your search criteria or filters to find what you're looking for.</p>
                    `;
                    document.querySelector('.services-grid').appendChild(noResultsEl);
                } else if (!show && noResultsEl) {
                    noResultsEl.remove();
                }
            }
            
            // Add event listeners
            searchInput.addEventListener('input', filterServices);
            statusFilter.addEventListener('change', filterServices);
            categoryFilter.addEventListener('change', filterServices);
            
            // Add animation delay to service cards
            serviceCards.forEach((card, index) => {
                card.style.animationDelay = (index * 0.1) + 's';
            });
            
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                        
                        // Close mobile menu if open
                        const mobileNav = document.getElementById('mobileNav');
                        if (mobileNav.classList.contains('active')) {
                            mobileNav.classList.remove('active');
                            document.querySelector('.mobile-menu-toggle i').className = 'fas fa-bars';
                        }
                    }
                });
            });
            
            // Enhanced service button functionality
            document.querySelectorAll('.service-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    // Add visual feedback
                    this.style.transform = 'scale(0.95)';
                    
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                    
                    // Handle different button types
                    const href = this.getAttribute('href');
                    
                    // Check if it's a telephone link
                    if (href && href.startsWith('tel:')) {
                        // For phone links, show confirmation
                        const phoneNumber = href.replace('tel:', '');
                        if (confirm(`Call ${phoneNumber}?\n\nThis will open your phone app to make the call.`)) {
                            // Let the browser handle the tel: link
                            return;
                        } else {
                            e.preventDefault();
                            return;
                        }
                    }
                    
                    // Check for missing or placeholder links
                    if (!href || href === '#' || href === 'javascript:void(0)') {
                        e.preventDefault();
                        
                        // Show specific message based on button content
                        const buttonText = this.textContent.trim();
                        let message = 'This feature is coming soon! Please check back later.';
                        
                        if (buttonText.includes('Requirements')) {
                            message = 'Requirements information will be available soon. For now, you can proceed with the application and requirements will be shown during the process.';
                        } else if (buttonText.includes('Track Progress')) {
                            message = 'Progress tracking is available through "My Requests" page. You can access it from the main navigation.';
                        } else if (buttonText.includes('Update Profile')) {
                            message = 'Profile updates are being redirected to the resident registration page where you can update your information.';
                        }
                        
                        // Create a custom notification
                        showServiceNotification(message, 'info');
                        return;
                    }
                    
                    // Add loading state for valid links
                    if (href && !href.startsWith('#') && !href.startsWith('tel:') && !href.startsWith('javascript:')) {
                        this.classList.add('loading');
                        const originalText = this.innerHTML;
                        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                        
                        // Reset button if navigation fails (shouldn't happen in normal cases)
                        setTimeout(() => {
                            if (this.classList.contains('loading')) {
                                this.classList.remove('loading');
                                this.innerHTML = originalText;
                            }
                        }, 5000);
                    }
                });
                
                // Store original button text for restoration
                button.dataset.originalText = button.innerHTML;
                
                // Add keyboard accessibility
                button.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });
            
            // Service notification system
            function showServiceNotification(message, type = 'info') {
                // Remove existing notifications
                const existingNotification = document.querySelector('.service-notification');
                if (existingNotification) {
                    existingNotification.remove();
                }
                
                // Create notification element
                const notification = document.createElement('div');
                notification.className = `service-notification service-notification-${type}`;
                notification.innerHTML = `
                    <div class="service-notification-content">
                        <div class="service-notification-icon">
                            <i class="fas ${
                                type === 'info' ? 'fa-info-circle' :
                                type === 'success' ? 'fa-check-circle' :
                                type === 'warning' ? 'fa-exclamation-triangle' :
                                'fa-times-circle'
                            }"></i>
                        </div>
                        <div class="service-notification-message">${message}</div>
                        <button class="service-notification-close" onclick="this.parentElement.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                
                // Add to page
                document.body.appendChild(notification);
                
                // Auto-remove after 8 seconds
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.style.opacity = '0';
                        notification.style.transform = 'translateY(-100%)';
                        setTimeout(() => notification.remove(), 300);
                    }
                }, 8000);
                
                // Animate in
                setTimeout(() => {
                    notification.style.opacity = '1';
                    notification.style.transform = 'translateY(0)';
                }, 100);
            }
            
            // Add hover effects to service cards
            document.querySelectorAll('.service-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                    this.style.boxShadow = '0 20px 40px rgba(0, 0, 0, 0.15)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '';
                });
            });
        });
    </script>
</body>
</html>