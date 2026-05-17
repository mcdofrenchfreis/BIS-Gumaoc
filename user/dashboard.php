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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo htmlspecialchars($page_title); ?> - Barangay Gumaoc East</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/background.css">
    <link rel="stylesheet" href="css/mobile.css">
    <link rel="stylesheet" href="css/portal-services.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* Body styling is now in background.css */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            line-height: 1.6;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Navbar & loader: see navbar_component.php + user/css/background.css */
        
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
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            max-width: 100%;
        }
        
        .service-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(46, 125, 50, 0.3);
            color: white;
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
            .dashboard-content {
                padding-top: 68px;
            }

            .welcome-section {
                padding: 1.5rem 1.25rem;
                border-radius: 16px;
                margin-bottom: 1rem;
            }
            
            .welcome-section h1 {
                font-size: 1.5rem;
                line-height: 1.25;
            }

            .welcome-section > p {
                font-size: 0.9375rem;
                margin-bottom: 1.25rem;
            }
            
            .user-info-card {
                flex-direction: column;
                text-align: center;
                width: 100%;
                max-width: 100%;
                padding: 1rem;
                gap: 0.75rem;
            }

            .user-details {
                text-align: center;
            }

            .user-details h3 {
                font-size: 1.0625rem;
                word-break: break-word;
            }

            .user-details p {
                font-size: 0.8125rem;
                word-break: break-all;
            }
            
            .quick-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.75rem;
                margin-bottom: 1rem;
            }

            .stat-card {
                padding: 1rem 0.75rem;
                border-radius: 14px;
            }

            .stat-number {
                font-size: 1.375rem;
            }

            .stat-label {
                font-size: 0.75rem;
                line-height: 1.3;
            }
            
            .services-section {
                padding: 1.25rem 1rem;
                border-radius: 16px;
            }

            .section-title {
                font-size: 1.125rem;
                margin-bottom: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .dashboard-content {
                padding-top: 64px;
                padding-bottom: 1rem;
            }

            .dashboard-container {
                padding: 0 0.75rem;
            }
            
            .welcome-section {
                padding: 1.25rem 1rem;
                margin-bottom: 0.875rem;
            }
            
            .welcome-section h1 {
                font-size: 1.25rem;
            }

            .welcome-section > p {
                font-size: 0.875rem;
                margin-bottom: 1rem;
            }
            
            .user-avatar-large {
                width: 48px;
                height: 48px;
                font-size: 1.125rem;
                border-radius: 12px;
            }
            
            .quick-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.5rem;
            }

            .stat-card {
                padding: 0.75rem 0.5rem;
            }

            .stat-number {
                font-size: 1.125rem;
                margin-bottom: 0.25rem;
            }

            .stat-label {
                font-size: 0.6875rem;
            }
            
            .services-section {
                padding: 1rem 0.75rem;
            }

            .section-title {
                font-size: 1rem;
                gap: 0.5rem;
            }

            .section-title i {
                font-size: 0.9375rem;
            }
        }

        @media (hover: none) {
            .stat-card:hover,
            .service-card:hover,
            .portal-service-card:hover {
                transform: none;
            }

            .user-avatar-large:hover {
                transform: none;
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
                <?php include __DIR__ . '/includes/portal_services.php'; ?>
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
            
            document.querySelectorAll('.quick-action-btn[href^="#"]').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    const id = this.getAttribute('href').slice(1);
                    const target = document.getElementById(id);
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });

            // Add smooth scrolling to service cards
            const serviceCards = document.querySelectorAll('.portal-service-card, .service-card');
            
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
                    
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);

                    if (this.href) {
                        showNotification('Loading...', 'info');
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
            border-left-color: #2e7d32;
        }
        
        .notification-warning {
            border-left-color: #2e7d32;
        }
        
        .notification-info {
            border-left-color: #2e7d32;
        }
        
        .notification i {
            color: #4caf50;
            font-size: 18px;
        }
        
        .notification-error i {
            color: #2e7d32;
        }
        
        .notification-warning i {
            color: #2e7d32;
        }
        
        .notification-info i {
            color: #2e7d32;
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