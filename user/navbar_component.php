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
if (!isset($base_path)) {
    $base_path = '../';
}
require_once __DIR__ . '/includes/portal_urls.php';
$logo_src = $base_path . 'assets/images/logo.png';

// Resolve user (some pages pass $current_user instead of $user)
if (!isset($user) || !is_array($user)) {
    if (isset($current_user) && is_array($current_user)) {
        $user = $current_user;
    } elseif (!empty($_SESSION['user_id'])) {
        $dbPath = __DIR__ . '/../includes/db_connect.php';
        if (file_exists($dbPath)) {
            require_once $dbPath;
            try {
                if (isset($pdo)) {
                    $stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ? LIMIT 1");
                    $stmt->execute([$_SESSION['user_id']]);
                    $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (is_array($fetched)) {
                        $user = $fetched;
                    }
                }
            } catch (Throwable $e) {
                // fall through to guest fallback
            }
        }
    }
}

$first_name = is_array($user) ? trim((string)($user['first_name'] ?? '')) : '';
$middle_name = is_array($user) ? trim((string)($user['middle_name'] ?? '')) : '';
$last_name = is_array($user) ? trim((string)($user['last_name'] ?? '')) : '';
$display_name = trim($first_name . ' ' . ($middle_name !== '' ? ($middle_name . ' ') : '') . $last_name);
if ($display_name === '') {
    $display_name = $_SESSION['user_name'] ?? 'Guest';
}
$display_email = is_array($user) ? (($user['email'] ?? null) ?: 'No email') : 'No email';

// Debug: Check current page detection
// This will help identify if the issue is with page detection
$debug_current_page = $current_page;
$debug_filename = basename($_SERVER['PHP_SELF'], '.php');

// Navigation items (desktop + mobile)
$nav_items = [
    'dashboard' => ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'url' => user_portal_url('dashboard.php')],
    'announcements' => ['icon' => 'fas fa-bullhorn', 'label' => 'Announcements', 'url' => user_portal_url('announcements.php')],
    'profile' => ['icon' => 'fas fa-user', 'label' => 'Profile', 'url' => user_portal_url('profile.php')],
    'settings' => ['icon' => 'fas fa-cog', 'label' => 'Settings', 'url' => user_portal_url('settings.php')],
];

$mobile_extra_links = [
    ['icon' => 'fas fa-bullhorn', 'label' => 'Announcements', 'url' => user_portal_url('announcements.php')],
    ['icon' => 'fas fa-file-alt', 'label' => 'Certificate Request', 'url' => user_portal_url('certificate-request.php')],
    ['icon' => 'fas fa-building', 'label' => 'Business Application', 'url' => user_portal_url('business-application.php')],
    ['icon' => 'fas fa-clipboard-check', 'label' => 'Track Business App', 'url' => user_portal_url('my-business-applications.php')],
    ['icon' => 'fas fa-list-alt', 'label' => 'My Requests', 'url' => user_portal_url('my-requests.php')],
    ['icon' => 'fas fa-user', 'label' => 'My Profile', 'url' => user_portal_url('profile.php')],
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
        <a href="<?php echo htmlspecialchars(user_portal_home_url()); ?>" class="navbar-brand">
            <img src="<?php echo htmlspecialchars($logo_src); ?>" alt="" class="brand-logo-img" width="44" height="44">
            <span class="brand-title">Gumaoc Portal</span>
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
                <a href="<?php echo htmlspecialchars(user_portal_url('profile.php')); ?>" class="dropdown-item">
                    <i class="fas fa-user"></i>
                    Profile
                </a>
                <a href="<?php echo htmlspecialchars(user_portal_url('settings.php')); ?>" class="dropdown-item">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
                <div class="dropdown-divider"></div>
                <a href="<?php echo htmlspecialchars(user_portal_url('logout.php')); ?>" class="dropdown-item">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
        
        <button type="button" class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Open menu" aria-expanded="false" aria-controls="mobileNav">
            <div class="hamburger-line"></div>
            <div class="hamburger-line"></div>
            <div class="hamburger-line"></div>
        </button>
    </div>
    
    <div class="mobile-nav-backdrop" id="mobileNavBackdrop" aria-hidden="true"></div>
    
    <!-- Mobile Navigation Menu -->
    <div class="mobile-nav" id="mobileNav" aria-hidden="true">
        <p class="mobile-nav-heading">Menu</p>
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

        <p class="mobile-nav-heading mobile-nav-heading-sub">Services</p>
        <?php foreach ($mobile_extra_links as $link): ?>
            <div class="mobile-nav-item">
                <a href="<?php echo htmlspecialchars($link['url']); ?>" class="mobile-nav-link">
                    <i class="<?php echo $link['icon']; ?>"></i>
                    <?php echo htmlspecialchars($link['label']); ?>
                </a>
            </div>
        <?php endforeach; ?>


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
                <a href="<?php echo htmlspecialchars(user_portal_url('logout.php')); ?>" class="mobile-nav-link" style="color: #1b5e20; font-weight: 700; padding: 8px 0;">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<style>
.user-navbar {
    background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1100;
    box-shadow: 0 2px 12px rgba(27, 94, 32, 0.25);
    padding-top: env(safe-area-inset-top, 0);
}

.navbar-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 16px;
    height: 70px;
    gap: 12px;
}

.navbar-brand {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    min-width: 0;
}

.navbar-brand:hover {
    opacity: 0.95;
}

.brand-logo-img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    object-position: center;
    transform: scale(1.12);
    transform-origin: center center;
    margin-right: 10px;
    flex-shrink: 0;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.22);
}

.brand-title {
    color: rgba(255, 255, 255, 0.95);
    white-space: nowrap;
}

@media (max-width: 400px) {
    .brand-title {
        display: none;
    }
}

.navbar-nav {
    display: flex;
    align-items: center;
    gap: 4px;
}

.nav-item {
    position: relative;
}

.user-navbar .nav-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    min-height: 44px;
    border-radius: 10px;
    text-decoration: none;
    color: rgba(255, 255, 255, 0.92) !important;
    font-weight: 500;
    font-size: 14px;
    transition: background 0.2s ease, color 0.2s ease;
}

.user-navbar .nav-link:hover {
    background: rgba(255, 255, 255, 0.15);
    color: #fff !important;
}

.user-navbar .nav-link.active {
    background: rgba(255, 255, 255, 0.22) !important;
    color: #fff !important;
    font-weight: 600;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.2);
}

.user-menu {
    position: relative;
}

.user-button {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    min-height: 44px;
    border: none;
    background: rgba(255, 255, 255, 0.18);
    border-radius: 10px;
    cursor: pointer;
    transition: background 0.2s ease;
    color: #fff;
    font-weight: 500;
    font-size: 14px;
}

.user-button:hover {
    background: rgba(255, 255, 255, 0.28);
}

.user-button .fa-chevron-down {
    font-size: 12px;
    opacity: 0.85;
}

.user-avatar-small {
    width: 32px;
    height: 32px;
    background: rgba(255, 255, 255, 0.95);
    color: #1b5e20;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
}

.user-dropdown {
    position: absolute;
    top: calc(100% + 6px);
    right: 0;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    padding: 8px 0;
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(8px);
    transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;
    border: 1px solid #e8f5e9;
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
    padding: 12px 16px;
    text-decoration: none;
    color: #1a1a1a;
    font-size: 14px;
    transition: background 0.15s ease;
}

.dropdown-item:hover {
    background: #f7faf7;
}

.dropdown-divider {
    height: 1px;
    background: #e8f5e9;
    margin: 8px 0;
}

.mobile-menu-toggle {
    display: none;
    flex-direction: column;
    justify-content: center;
    cursor: pointer;
    padding: 10px;
    min-width: 44px;
    min-height: 44px;
    border: none;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 10px;
}

.hamburger-line {
    width: 22px;
    height: 2px;
    background: #fff;
    margin: 3px 0;
    transition: transform 0.25s ease, opacity 0.25s ease;
    border-radius: 2px;
}

.mobile-menu-toggle.active .hamburger-line:nth-child(2) {
    opacity: 0;
}

.user-navbar {
    overflow: visible;
}

.mobile-nav-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 1190;
    opacity: 0;
    transition: opacity 0.25s ease;
}

.mobile-nav-backdrop.active {
    display: block;
    opacity: 1;
}

.mobile-nav {
    display: none;
    position: fixed;
    top: var(--user-nav-total, 70px);
    left: 0;
    right: 0;
    bottom: 0;
    height: auto;
    max-height: calc(100dvh - var(--user-nav-total, 70px));
    overflow-x: hidden;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    background: #fff;
    z-index: 1200;
    padding: 12px 16px 24px;
    padding-bottom: calc(24px + env(safe-area-inset-bottom, 0px));
    opacity: 0;
    visibility: hidden;
    transform: translateY(-12px);
    transition: opacity 0.25s ease, transform 0.25s ease, visibility 0.25s;
    border-bottom: 1px solid #e8f5e9;
    box-shadow: 0 12px 32px rgba(27, 94, 32, 0.15);
}

.mobile-nav.active {
    display: flex;
    flex-direction: column;
    opacity: 1;
    visibility: visible;
    transform: none;
}

.mobile-nav-heading {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #666;
    margin: 4px 4px 8px;
}

.mobile-nav-heading-sub {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e8f0e8;
}

.mobile-nav-item {
    border-bottom: 1px solid #f0f4f1;
}

.mobile-nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: #1a1a1a;
    font-weight: 500;
    font-size: 16px;
    padding: 14px 4px;
    min-height: 48px;
    transition: color 0.15s ease, background 0.15s ease;
}

.mobile-nav-link:hover {
    color: #1b5e20;
}

.mobile-nav-link.active {
    color: #1b5e20 !important;
    font-weight: 600;
    background: #e8f5e9;
    padding-left: 12px;
    padding-right: 12px;
    margin-left: -12px;
    margin-right: -12px;
    border-radius: 10px;
}

.mobile-user-info {
    background: #f7faf7;
    border: 1px solid #e8f5e9;
    border-radius: 12px;
    padding: 16px;
    margin-top: 16px;
}

.mobile-user-avatar {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #1b5e20, #2e7d32);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 16px;
    margin-right: 12px;
}

.mobile-user-details h4 {
    color: #1a1a1a;
    font-size: 16px;
    margin-bottom: 4px;
}

.mobile-user-details p {
    color: #555;
    font-size: 14px;
    margin: 0;
}

.page-loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #f7faf7;
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
    width: 48px;
    height: 48px;
    border: 3px solid #e8f5e9;
    border-top-color: #2e7d32;
    border-radius: 50%;
    animation: spin 0.9s linear infinite;
    margin-bottom: 16px;
}

.loader-text {
    color: #1b5e20;
    font-size: 16px;
    font-weight: 600;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@media (max-width: 968px) {
    .navbar-nav {
        display: none;
    }
    .mobile-menu-toggle {
        display: flex;
    }
    .user-menu {
        display: none;
    }
}

@media (max-width: 768px) {
    .navbar-container {
        height: 60px;
        padding: 0 12px;
    }

    .brand-logo-img {
        width: 38px;
        height: 38px;
    }
}

@media (max-width: 480px) {
    .navbar-container {
        padding: 0 10px;
    }

    .user-navbar .nav-link,
    .mobile-nav-link {
        font-size: 15px;
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

    function mountMobileMenuPortal() {
        const mobileNav = document.getElementById('mobileNav');
        const mobileBackdrop = document.getElementById('mobileNavBackdrop');
        if (mobileBackdrop && mobileBackdrop.parentElement !== document.body) {
            document.body.appendChild(mobileBackdrop);
        }
        if (mobileNav && mobileNav.parentElement !== document.body) {
            document.body.appendChild(mobileNav);
        }
    }

    mountMobileMenuPortal();
    
    // Mobile menu functionality
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileNav = document.getElementById('mobileNav');
    
    const mobileBackdrop = document.getElementById('mobileNavBackdrop');

    function setMobileMenuOpen(open) {
        if (!mobileMenuToggle || !mobileNav) return;
        mobileMenuToggle.classList.toggle('active', open);
        mobileNav.classList.toggle('active', open);
        if (mobileBackdrop) {
            mobileBackdrop.classList.toggle('active', open);
            mobileBackdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
        }
        mobileNav.setAttribute('aria-hidden', open ? 'false' : 'true');
        mobileMenuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        mobileMenuToggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        document.body.classList.toggle('mobile-menu-open', open);
    }

    if (mobileMenuToggle && mobileNav) {
        mobileMenuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            setMobileMenuOpen(!mobileNav.classList.contains('active'));
        });

        if (mobileBackdrop) {
            mobileBackdrop.addEventListener('click', function() {
                setMobileMenuOpen(false);
            });
        }

        document.addEventListener('click', function(e) {
            if (!mobileNav.classList.contains('active')) return;
            if (mobileMenuToggle.contains(e.target) || mobileNav.contains(e.target)) return;
            setMobileMenuOpen(false);
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth > 968) {
                setMobileMenuOpen(false);
            }
        });
    }
});
</script>