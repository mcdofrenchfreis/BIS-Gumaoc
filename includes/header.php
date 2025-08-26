<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isset($_SESSION['rfid_authenticated']) && $_SESSION['rfid_authenticated'] === true;
$user_name = $_SESSION['user_name'] ?? 'User';
$user_id = $_SESSION['user_id'] ?? null;
$page_title = $page_title ?? 'Barangay Gumaoc East E-Services System';
$page_description = $page_description ?? 'IoT-Enabled Incident Reporting & E-Services Information System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'GUMAOC East'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/styles.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('assets/images/background.jpg') center/cover no-repeat;
            background-attachment: fixed;
            background-color: #2d5a27;
            min-height: 100vh;
            position: relative;
        }
        
        /* Green tint overlay */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, 
                rgba(45, 90, 39, 0.7) 0%, 
                rgba(74, 124, 89, 0.6) 25%, 
                rgba(53, 122, 60, 0.65) 50%, 
                rgba(45, 90, 39, 0.7) 75%, 
                rgba(30, 58, 26, 0.8) 100%);
            z-index: 1;
        }
        
        .navbar {
            background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 50%, #388e3c 100%);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 20px rgba(27, 94, 32, 0.3);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: all 0.3s ease;
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
            color: white;
        }
        
        .brand-logo {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
            margin-right: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .brand-text h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 2px;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .brand-text p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
            margin: 0;
        }
        
        .navbar-nav {
            display: flex;
            align-items: center;
            list-style: none;
            gap: 5px;
        }
        
        .nav-link {
            text-decoration: none;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 25px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            position: relative; /* Added this to ensure proper positioning context */
        }
        
        .nav-link:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .nav-link.active {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            font-weight: 600;
        }
        
        /* Dropdown Styles */
        .nav-dropdown {
            position: relative;
        }
        
        .dropdown-arrow {
            font-size: 0.8rem;
            margin-left: 0.3rem;
            transition: transform 0.3s ease;
        }
        
        .nav-dropdown:hover .dropdown-arrow {
            transform: rotate(180deg);
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            min-width: 250px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            padding: 0.8rem 0;
            z-index: 1001;
            list-style: none;
            margin: 0;
            
            /* Hidden by default */
            opacity: 0;
            visibility: hidden;
            transform: translateY(15px);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        /* Show dropdown on hover */
        .nav-dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(8px);
        }
        
        /* Dropdown list items */
        .dropdown-menu li {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        /* Dropdown links */
        .dropdown-menu li a {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.9rem 1.2rem;
            color: #444;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            border-radius: 0;
            position: relative;
        }
        
        .dropdown-menu li a:hover {
            background: linear-gradient(135deg, #e8f5e8 0%, #f1f8e9 100%);
            color: #1b5e20;
            padding-left: 1.5rem;
        }
        
        .dropdown-menu li a.current-page {
            background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
            color: white;
            font-weight: 600;
        }
        
        .dropdown-menu li a.current-page:hover {
            background: linear-gradient(135deg, #2e7d32 0%, #388e3c 100%);
            color: white;
            padding-left: 1.8rem;
        }
        
        .dropdown-menu li a.current-page::before {
            content: '●';
            position: absolute;
            left: 0.5rem;
            color: #81c784;
            font-size: 0.7rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .dropdown-icon {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }
        
        /* Dropdown arrow pointer */
        .dropdown-menu::before {
            content: '';
            position: absolute;
            top: -8px;
            left: 25px;
            width: 0;
            height: 0;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-bottom: 8px solid white;
            filter: drop-shadow(0 -2px 4px rgba(0, 0, 0, 0.1));
        }
        
        .user-menu {
            position: relative;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.2));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.4), rgba(255, 255, 255, 0.3));
        }
        
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 10px 0;
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            margin-top: 10px;
            border: 1px solid rgba(74, 124, 89, 0.2);
        }
        
        .user-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .user-info {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }
        
        .user-info .name {
            font-weight: 600;
            color: #2d5a27;
            margin-bottom: 5px;
        }
        
        .user-info .role {
            font-size: 12px;
            color: #666;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            text-decoration: none;
            color: #333;
            transition: background 0.3s ease;
        }
        
        .dropdown-item:hover {
            background: rgba(74, 124, 89, 0.1);
        }
        
        .dropdown-item.logout {
            color: #dc3545;
            border-top: 1px solid #eee;
        }
        
        .dropdown-item.logout:hover {
            background: rgba(220, 53, 69, 0.1);
        }
        
        .mobile-menu-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 5px;
            gap: 3px;
        }
        
        .mobile-menu-toggle span {
            width: 25px;
            height: 3px;
            background: white;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        
        .mobile-menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
        }
        
        .mobile-menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }
        
        .mobile-menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(6px, -6px);
        }
        
        .content-wrapper {
            margin-top: 70px;
            position: relative;
            z-index: 2;
            min-height: calc(100vh - 70px);
        }
        
        /* Mobile responsive */
        /* Toast Notification Styles */
        .toast-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            pointer-events: none;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 100px;
        }

        .toast {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            max-width: 500px;
            min-width: 300px;
            pointer-events: auto;
            transform: translateY(-50px);
            opacity: 0;
            animation: slideInDown 0.4s ease forwards;
            position: relative;
            overflow: hidden;
        }

        .toast::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(135deg, #f44336, #d32f2f);
            z-index: 1;
        }

        .toast-error::before {
            background: linear-gradient(135deg, #f44336, #d32f2f);
        }

        .toast-success::before {
            background: linear-gradient(135deg, #4caf50, #2e7d32);
        }

        .toast-warning::before {
            background: linear-gradient(135deg, #ff9800, #f57c00);
        }

        .toast-content {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            position: relative;
            z-index: 2;
        }

        .toast-icon {
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .toast-message {
            flex: 1;
            color: #333;
            font-weight: 500;
            line-height: 1.5;
        }

        .toast-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #666;
            cursor: pointer;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .toast-close:hover {
            background: rgba(0, 0, 0, 0.1);
            color: #333;
        }

        @keyframes slideInDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes slideOutUp {
            from {
                transform: translateY(0);
                opacity: 1;
            }
            to {
                transform: translateY(-50px);
                opacity: 0;
            }
        }

        .toast.closing {
            animation: slideOutUp 0.3s ease forwards;
        }

        @media (max-width: 768px) {
            .toast-overlay {
                padding: 20px;
                padding-top: 100px;
            }
            
            .toast {
                min-width: unset;
                width: 100%;
                max-width: 100%;
            }
            
            .navbar-container {
                padding: 0 15px;
            }
            
            .navbar-nav {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
                flex-direction: column;
                justify-content: flex-start;
                align-items: center;
                padding: 20px;
                gap: 10px;
                transition: left 0.3s ease;
            }
            
            .navbar-nav.show {
                left: 0;
            }
            
            .mobile-menu-toggle {
                display: flex;
            }
            
            .nav-link {
                width: 100%;
                text-align: center;
                padding: 15px;
                border-radius: 10px;
            }
            
            /* Mobile dropdown */
            .dropdown-menu {
                position: static;
                background: rgba(255, 255, 255, 0.1);
                box-shadow: none;
                border: none;
                margin-top: 0.5rem;
                width: 100%;
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s ease;
                transform: none;
                opacity: 1;
                visibility: visible;
            }
            
            .dropdown-menu.show {
                max-height: 300px;
            }
            
            .dropdown-menu::before {
                display: none;
            }
            
            .dropdown-menu li a {
                color: rgba(255, 255, 255, 0.9);
                padding: 0.8rem 1rem;
            }
            
            .dropdown-menu li a:hover {
                background: rgba(255, 255, 255, 0.2);
                color: white;
                padding-left: 1rem;
            }
            
            .dropdown-menu li a.current-page {
                background: rgba(255, 255, 255, 0.3);
                color: white;
                font-weight: 600;
            }
            
            .user-dropdown {
                position: relative;
                box-shadow: none;
                border: 1px solid #eee;
                margin-top: 10px;
                opacity: 1;
                visibility: visible;
                transform: none;
            }
            
            .brand-text h1 {
                font-size: 20px;
            }
            
            .brand-logo {
                width: 40px;
                height: 40px;
                font-size: 14px;
            }
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            z-index: 10;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        /* Ensure parent nav-link has relative positioning */
        .nav-link {
            text-decoration: none;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 25px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            position: relative; /* Added this to ensure proper positioning context */
        }
        
        /* Mobile responsive adjustments for notification badge */
        @media (max-width: 768px) {
            .notification-badge {
                top: -3px;
                right: -3px;
                width: 16px;
                height: 16px;
                font-size: 10px;
            }
            
            .nav-link {
                position: relative; /* Ensure relative positioning on mobile too */
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/GUMAOC/index.php" class="navbar-brand">
                <div class="brand-logo">BRGY</div>
                <div class="brand-text">
                    <h1>GUMAOC EAST</h1>
                    <p>Barangay Management System</p>
                </div>
            </a>
            
            <ul class="navbar-nav" id="navbarNav">
                <?php if (!$is_logged_in): ?>
                    <!-- Guest Navigation -->
                    <li><a href="/GUMAOC/index.php" class="nav-link">🏠 Home</a></li>
                    <li><a href="/GUMAOC/pages/about.php" class="nav-link">ℹ️ About</a></li>
                    <li><a href="/GUMAOC/pages/services.php" class="nav-link">🛠️ Services</a></li>
                    <li class="nav-dropdown">
                        <a href="/GUMAOC/pages/forms.php" class="nav-link">
                            📋 E-Services <span class="dropdown-arrow">▼</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="/GUMAOC/pages/resident-registration.php">
                                <span class="dropdown-icon">👥</span>Census Registration
                            </a></li>
                            <li><a href="/GUMAOC/pages/certificate-request.php">
                                <span class="dropdown-icon">📄</span>Certificate Requests
                            </a></li>
                            <li><a href="/GUMAOC/pages/forms.php">
                                <span class="dropdown-icon">📋</span>All Forms
                            </a></li>
                        </ul>
                    </li>
                    <li><a href="/GUMAOC/pages/report.php" class="nav-link">🚨 Report</a></li>
                    <li><a href="/GUMAOC/pages/contact.php" class="nav-link">📞 Contact</a></li>
                    <li><a href="/GUMAOC/login.php" class="nav-link">🔐 Login</a></li>
                    <li><a href="/GUMAOC/pages/resident-registration.php" class="nav-link">📝 Register</a></li>
                <?php else: ?>
                    <!-- Authenticated User Navigation -->
                    <li><a href="/GUMAOC/index.php" class="nav-link">🏠 Dashboard</a></li>
                    <li><a href="/GUMAOC/pages/services.php" class="nav-link">🛠️ Services</a></li>
                    <li class="nav-dropdown">
                        <a href="/GUMAOC/pages/forms.php" class="nav-link">
                            📋 E-Services <span class="dropdown-arrow">▼</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="/GUMAOC/pages/resident-registration.php">
                                <span class="dropdown-icon">👥</span>Census Registration
                            </a></li>
                            <li><a href="/GUMAOC/pages/certificate-request.php">
                                <span class="dropdown-icon">📄</span>Certificate Requests
                            </a></li>
                            <li><a href="/GUMAOC/pages/forms.php">
                                <span class="dropdown-icon">📋</span>All Forms
                            </a></li>
                        </ul>
                    </li>
                    <li><a href="/GUMAOC/pages/report.php" class="nav-link">🚨 Report</a></li>
                    <li><a href="notifications.php" class="nav-link" style="position: relative;">
                        🔔 Notifications
                        <span class="notification-badge">3</span>
                    </a></li>
                    
                    <!-- User Menu -->
                    <li class="user-menu">
                        <div class="user-avatar" onclick="toggleUserDropdown()">
                            <?php echo strtoupper(substr($user_name, 0, 2)); ?>
                        </div>
                        <div class="user-dropdown" id="userDropdown">
                            <div class="user-info">
                                <div class="name"><?php echo htmlspecialchars($user_name); ?></div>
                                <div class="role">Resident</div>
                            </div>
                            <a href="profile.php" class="dropdown-item">
                                👤 My Profile
                            </a>
                            <a href="settings.php" class="dropdown-item">
                                ⚙️ Account Settings
                            </a>
                            <a href="/GUMAOC/pages/queue-status.php" class="dropdown-item">
                                ⏳ Queue Status
                           </a>
                            <a href="help.php" class="dropdown-item">
                                ❓ Help & Support
                            </a>
                            <a href="/GUMAOC/logout.php" class="dropdown-item logout">
                                🚪 Logout
                            </a>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
            
            <div class="mobile-menu-toggle" onclick="toggleMobileMenu()" id="mobileToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>
    
    <div class="content-wrapper">
        <!-- Page content will go here -->

<script>
function toggleUserDropdown() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('show');
}

function toggleMobileMenu() {
    const navbarNav = document.getElementById('navbarNav');
    const mobileToggle = document.getElementById('mobileToggle');
    navbarNav.classList.toggle('show');
    mobileToggle.classList.toggle('active');
}

// Enhanced dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.querySelector('.nav-dropdown');
    if (!dropdown) return;
    
    const dropdownMenu = dropdown.querySelector('.dropdown-menu');
    const mainLink = dropdown.querySelector('a:first-child');
    
    // Mobile dropdown toggle
    if (window.innerWidth <= 768) {
        mainLink.addEventListener('click', function(e) {
            e.preventDefault();
            dropdownMenu.classList.toggle('show');
        });
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            dropdownMenu.classList.remove('show');
        } else {
            // Re-attach mobile click handler if needed
            mainLink.removeEventListener('click', handleMobileClick);
            mainLink.addEventListener('click', handleMobileClick);
        }
    });
    
    function handleMobileClick(e) {
        e.preventDefault();
        dropdownMenu.classList.toggle('show');
    }
    
    // Close mobile menu when clicking on dropdown links
    const dropdownLinks = dropdownMenu.querySelectorAll('a');
    dropdownLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                const navMenu = document.querySelector('.navbar-nav');
                const navToggle = document.querySelector('.mobile-menu-toggle');
                navMenu.classList.remove('show');
                navToggle.classList.remove('active');
                dropdownMenu.classList.remove('show');
            }
        });
    });
});

// Close dropdown when clicking elsewhere (mobile only)
document.addEventListener('click', function(e) {
    if (window.innerWidth <= 768) {
        if (!e.target.closest('.nav-dropdown')) {
            const dropdownMenu = document.querySelector('.dropdown-menu');
            if (dropdownMenu) {
                dropdownMenu.classList.remove('show');
            }
        }
    }
});

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const userMenu = document.querySelector('.user-menu');
    const dropdown = document.getElementById('userDropdown');
    
    if (userMenu && !userMenu.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});

// Global Toast System
<?php if (isset($_SESSION['auth_error'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    showToast('<?php echo addslashes($_SESSION['auth_error']); ?>', 'error');
});
<?php unset($_SESSION['auth_error']); ?>
<?php endif; ?>

function showToast(message, type = 'error', duration = 5000) {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast-overlay');
    existingToasts.forEach(toast => toast.remove());
    
    // Create toast HTML
    const toastId = 'toast_' + Date.now();
    const iconMap = {
        'error': '❌',
        'success': '✅',
        'warning': '⚠️',
        'info': 'ℹ️'
    };
    
    const toastHTML = `
        <div class="toast-overlay" id="${toastId}_overlay">
            <div class="toast toast-${type}" id="${toastId}">
                <div class="toast-content">
                    <span class="toast-icon">${iconMap[type] || '❌'}</span>
                    <span class="toast-message">${message}</span>
                    <button class="toast-close" onclick="closeToast('${toastId}')">&times;</button>
                </div>
            </div>
        </div>
    `;
    
    // Add to body
    document.body.insertAdjacentHTML('beforeend', toastHTML);
    
    // Auto-close after duration
    if (duration > 0) {
        setTimeout(() => {
            closeToast(toastId);
        }, duration);
    }
}

function closeToast(toastId) {
    const toast = document.getElementById(toastId);
    const overlay = document.getElementById(toastId + '_overlay');
    
    if (toast) {
        toast.classList.add('closing');
        setTimeout(() => {
            if (overlay) {
                overlay.remove();
            }
        }, 300);
    }
}

// Close mobile menu when clicking on regular nav links
document.querySelectorAll('.navbar-nav > li > a').forEach(link => {
    link.addEventListener('click', function() {
        if (!link.parentElement.classList.contains('nav-dropdown') && !link.parentElement.classList.contains('user-menu')) {
            const navMenu = document.querySelector('.navbar-nav');
            const navToggle = document.querySelector('.mobile-menu-toggle');
            navMenu.classList.remove('show');
            navToggle.classList.remove('active');
        }
    });
});
</script>