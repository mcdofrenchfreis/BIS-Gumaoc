<?php
/**
 * Enhanced User Navigation Component
 * Provides consistent navigation across all user pages with mobile support
 * 
 * Usage: include 'navbar_component.php';
 * Make sure to set $current_page variable before including
 */

// Default current page if not set
if (!isset($current_page)) {
    $current_page = basename($_SERVER['PHP_SELF'], '.php');
}

// Get user display name
$display_name = $user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name'];
$display_email = $user['email'] ?? 'No email';

// Debug: Check current page detection
// This will help identify if the issue is with page detection
$debug_current_page = $current_page;
$debug_filename = basename($_SERVER['PHP_SELF'], '.php');

// Navigation items
$nav_items = [
    'dashboard' => ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'url' => 'dashboard.php'],
    'e-services' => ['icon' => 'fas fa-desktop', 'label' => 'E-Services', 'url' => 'e-services.php']
];
?>

<!-- Page Loader -->
<div class="page-loader" id="pageLoader">
    <div class="loader-icon"></div>
    <div class="loader-text">Loading...</div>
</div>

<!-- User Navbar -->
<nav class="user-navbar">
    <div class="navbar-container">
        <a href="dashboard.php" class="navbar-brand">
            <div class="brand-icon">
                <i class="fas fa-home"></i>
            </div>
            Gumaoc East Portal
        </a>
        
        <div class="navbar-nav">
            <?php foreach ($nav_items as $key => $item): ?>
                <?php $is_active = ($current_page === $key); ?>
                <div class="nav-item">
                    <a href="<?php echo $item['url']; ?>" class="nav-link <?php echo $is_active ? 'active' : ''; ?>" 
                       data-page="<?php echo $key; ?>" data-current="<?php echo $current_page; ?>">
                        <i class="<?php echo $item['icon']; ?>"></i>
                        <?php echo $item['label']; ?>
                    </a>
                </div>
            <?php endforeach; ?>
            <div class="nav-item">
                <a href="../index.php" class="nav-link">
                    <i class="fas fa-globe"></i>
                    Main Site
                </a>
            </div>
        </div>
        
        <div class="user-menu">
            <button class="user-button">
                <div class="user-avatar-small">
                    <?php echo strtoupper(substr($display_name, 0, 1)); ?>
                </div>
                <span><?php echo htmlspecialchars(explode(' ', $display_name)[0]); ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="user-dropdown">
                <a href="#" class="dropdown-item">
                    <i class="fas fa-user"></i>
                    Profile
                </a>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
                <div class="dropdown-divider"></div>
                <a href="logout.php" class="dropdown-item">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
        
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <div class="hamburger-line"></div>
            <div class="hamburger-line"></div>
            <div class="hamburger-line"></div>
        </button>
    </div>
    
    <!-- Mobile Navigation Menu -->
    <div class="mobile-nav" id="mobileNav">
        <?php foreach ($nav_items as $key => $item): ?>
            <?php $is_active = ($current_page === $key); ?>
            <div class="mobile-nav-item">
                <a href="<?php echo $item['url']; ?>" class="mobile-nav-link <?php echo $is_active ? 'active' : ''; ?>"
                   data-page="<?php echo $key; ?>" data-current="<?php echo $current_page; ?>">
                    <i class="<?php echo $item['icon']; ?>"></i>
                    <?php echo $item['label']; ?>
                </a>
            </div>
        <?php endforeach; ?>
        <div class="mobile-nav-item">
            <a href="../index.php" class="mobile-nav-link">
                <i class="fas fa-globe"></i>
                Main Site
            </a>
        </div>
        
        <div class="mobile-user-info">
            <div style="display: flex; align-items: center;">
                <div class="mobile-user-avatar">
                    <?php echo strtoupper(substr($display_name, 0, 1)); ?>
                </div>
                <div class="mobile-user-details">
                    <h4><?php echo htmlspecialchars(explode(' ', $display_name)[0]); ?></h4>
                    <p><?php echo htmlspecialchars($display_email); ?></p>
                </div>
            </div>
            <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e9ecef;">
                <a href="logout.php" class="mobile-nav-link" style="color: #dc3545; padding: 8px 0;">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<style>
/* Enhanced Navbar Styles */
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

.user-navbar .nav-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    color: #666 !important;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.user-navbar .nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(46, 125, 50, 0.1), transparent);
    transition: left 0.5s ease;
}

.user-navbar .nav-link:hover::before {
    left: 100%;
}

.user-navbar .nav-link:hover {
    background: #f8f9fa;
    color: #333 !important;
    transform: translateY(-1px);
}

.user-navbar .nav-link.active {
    background: linear-gradient(135deg, #e7f3ff, #f0f8ff) !important;
    color: #0066cc !important;
    box-shadow: 0 2px 8px rgba(0, 102, 204, 0.2);
    font-weight: 600;
    transform: scale(1.02);
}

.user-navbar .nav-link.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 30px;
    height: 3px;
    background: #0066cc;
    border-radius: 2px;
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
    color: #0066cc !important;
    font-weight: 600;
    background: linear-gradient(135deg, #e7f3ff, #f0f8ff);
    padding: 8px 12px;
    border-radius: 8px;
    transform: translateX(5px);
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

/* Responsive Design */
@media (max-width: 968px) {
    .navbar-nav {
        display: none;
    }
    
    .mobile-menu-toggle {
        display: flex;
    }
}

@media (max-width: 480px) {
    .navbar-container {
        padding: 0 15px;
    }
}
</style>

<script>
// Enhanced mobile menu functionality
document.addEventListener('DOMContentLoaded', function() {
    // Hide page loader
    setTimeout(function() {
        const loader = document.getElementById('pageLoader');
        if (loader) {
            loader.classList.add('hidden');
            setTimeout(() => loader.remove(), 500);
        }
    }, 800);
    
    // Mobile menu functionality
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileNav = document.getElementById('mobileNav');
    
    if (mobileMenuToggle && mobileNav) {
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
    }
    
    // Add hover effects to navbar links
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-1px)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>