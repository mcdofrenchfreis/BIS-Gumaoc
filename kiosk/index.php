<?php
$base_path = '../';
$page_title = 'Barangay Gumaoc East E-Services System';
$header_title = 'Barangay Gumaoc East';
$header_subtitle = 'Smart Digital Services for Modern Community Living';

include '../includes/header.php';
include '../includes/db_connect.php';

// Check if admin is logged in
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Fetch services from database
try {
    $services_query = $pdo->query("SELECT * FROM services ORDER BY is_featured DESC, display_order, id");
    $services = $services_query->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Fallback to default services if database table doesn't exist yet
    $services = [
        [
            'id' => 1,
            'title' => 'Emergency Response',
            'description' => 'Real-time incident reporting with IoT sensors and instant emergency response coordination.',
            'button_text' => 'Report Incident',
            'button_link' => $base_path . 'pages/report.php',
            'is_featured' => 1,
            'features' => 'IoT Enabled,24/7 Monitoring'
        ],
        [
            'id' => 2,
            'title' => 'Document Requests',
            'description' => 'Request certificates, clearances, and official documents online with automated processing.',
            'button_text' => 'Apply Now',
            'button_link' => $base_path . 'pages/forms.php',
            'is_featured' => 0,
            'features' => 'Online Processing,Fast Approval'
        ],
        [
            'id' => 3,
            'title' => 'Community Census',
            'description' => 'Register as a resident and contribute to our comprehensive community database.',
            'button_text' => 'Register',
            'button_link' => $base_path . 'pages/forms.php',
            'is_featured' => 0,
            'features' => 'Digital Registry,Secure Data'
        ],
        [
            'id' => 4,
            'title' => 'Self-Service Kiosk',
            'description' => 'Access services anytime through our interactive kiosk at the barangay hall.',
            'button_text' => 'Explore',
            'button_link' => $base_path . 'pages/services.php',
            'is_featured' => 0,
            'features' => '24/7 Access,Touch Interface'
        ]
    ];
}

// Fetch updates from database
try {
    $updates_query = $pdo->query("SELECT * FROM updates ORDER BY is_priority DESC, display_order, id LIMIT 3");
    $updates = $updates_query->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Fallback to default updates if database table doesn't exist yet
    $updates = [
        [
            'title' => 'COVID-19 Vaccination Drive',
            'description' => 'New vaccination schedule available. Free vaccination for all residents. Register online to secure your slot.',
            'icon' => '💉',
            'badge_text' => 'Important',
            'badge_type' => 'important',
            'date' => 'July 28, 2025',
            'status' => '🟢 Active',
            'is_priority' => 1
        ],
        [
            'title' => 'Enhanced E-Services Launch',
            'description' => 'Our improved digital platform now offers faster processing, better security, and mobile optimization.',
            'icon' => '🚀',
            'badge_text' => 'New',
            'badge_type' => 'new',
            'date' => 'July 25, 2025',
            'status' => '🟢 Live',
            'is_priority' => 0
        ],
        [
            'title' => 'Town Fiesta 2025',
            'description' => 'Join us for our annual town celebration. Cultural shows, local food, and community activities for everyone.',
            'icon' => '🎉',
            'badge_text' => 'Community',
            'badge_type' => 'community',
            'date' => 'August 15, 2025',
            'status' => '🟡 Upcoming',
            'is_priority' => 0
        ]
    ];
}
?>

<!-- Kiosk Hero Title -->
<div class="kiosk-hero">
  <h1 class="kiosk-hero-title">Welcome to the Future of<br>Barangay Services</h1>

</div>

<!-- Entry Gateway -->
<div class="container kiosk-gateway" style="margin-top: 1.25rem;">
  <div class="section" style="padding-top: 0;">
    <div class="section-header section-header-card" style="margin-bottom: 1.25rem;">
      <div class="section-header-content">
        <div class="section-icon">👋</div>
        <h2>How would you like to continue?</h2>
        <p>Select your option below to get started</p>
      </div>
    </div>

    <div class="gateway-grid">
      <!-- RFID User -->
      <a href="rfid-login.php" class="service-btn" style="
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: .6rem; text-decoration: none; padding: 2rem; border-radius: 24px;
        background: linear-gradient(135deg, #e8f5e9 0%, #ffffff 100%);
        border: 1px solid #c8e6c9; box-shadow: 0 10px 28px rgba(46,125,50,.15);
        font-weight: 800; color: #2e7d32;">
        <div style="font-size: 3rem;">🪪</div>
        <div style="font-size: 1.4rem;">RFID User</div>
        <div style="font-weight: 600; color:#2e7d32; opacity:.9; font-size:1.1rem;">Tap your RFID to continue</div>
      </a>

      <!-- Non-RFID / New User -->
      <a href="<?php echo $base_path; ?>pages/resident-registration.php" class="service-btn" style="
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: .6rem; text-decoration: none; padding: 2rem; border-radius: 24px;
        background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 100%);
        border: 1px solid #bbdefb; box-shadow: 0 10px 28px rgba(25,118,210,.15);
        font-weight: 800; color: #1565c0;">
        <div style="font-size: 3rem;">📝</div>
        <div style="font-size: 1.4rem;">Non-RFID / New User</div>
        <div style="font-weight: 600; color:#0d47a1; opacity:.9; font-size:1.1rem;">Register to get started</div>
      </a>
    </div>
  </div>
 </div>

<!-- Hero Section removed for kiosk: title moved above gateway -->

<!-- Quick Actions removed for kiosk -->
 </div>
</div>
<style>
/* Hide top navbar just for kiosk pages and remove top offset */
.navbar { display: none !important; }
.content-wrapper { margin-top: 0 !important; }

/* Kiosk hero title */
.kiosk-hero { display: grid; place-items: center; padding: 1.5rem 1rem 0.75rem; }
.kiosk-hero-title { text-align: center; color: #ffffff; font-weight: 900; font-size: clamp(2.2rem, 4.5vw, 3.2rem); text-shadow: 2px 2px 8px rgba(0,0,0,0.8), 0 0 20px rgba(0,0,0,0.6); margin: 0; }
/* Make the highlighted span plain white (no special gradient/treatment) */
.kiosk-hero-title .highlight { color: #ffffff !important; background: none !important; -webkit-text-fill-color: unset !important; text-shadow: inherit !important; }

/* Kiosk gateway high-contrast and sizing (white card) */
.kiosk-gateway { display: grid; place-items: center; padding: 0 1rem; }
.kiosk-gateway .section { width: 100%; max-width: 1000px; }
.kiosk-gateway .section-header-card {
  position: relative;
  background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(255,255,255,0.94)) !important;
  color: #1b2b22 !important;
  border: 1px solid rgba(0,0,0,0.06) !important;
  border-radius: 24px !important;
  box-shadow: 0 18px 44px rgba(0,0,0,0.16) !important;
  padding: 2rem !important;
  overflow: hidden;
}
/* Decorative top bar */
.kiosk-gateway .section-header-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0; height: 6px;
  background: linear-gradient(90deg, #2e7d32, #66bb6a, #2e7d32);
}
/* Subtle pattern overlay */
.kiosk-gateway .section-header-card::after {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at top right, rgba(46,125,50,0.08), transparent 45%),
              radial-gradient(ellipse at bottom left, rgba(25,118,210,0.06), transparent 40%);
  pointer-events: none;
}
.kiosk-gateway .section-icon { color: #2e7d32 !important; text-shadow: none; }
.kiosk-gateway .section-header-card h2 { color: #1b2b22 !important; font-size: clamp(1.6rem, 3.2vw, 2.4rem); letter-spacing: .2px; text-shadow: none; text-align: center; }
.kiosk-gateway .section-header-card p { color: #4b5d54 !important; font-size: 1.05rem; opacity: 1; text-align: center; margin-top: .35rem; }

/* Kiosk gateway enhanced contrast and sizing */
.gateway-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.25rem;
  justify-items: center;
}
.gateway-grid .service-btn {
  padding: 2rem !important;
  border-radius: 24px !important;
  box-shadow: 0 14px 32px rgba(0,0,0,.2) !important;
  min-width: 320px;
  max-width: 520px;
}

/* Add consistent green tint across entire page */
body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(46, 125, 50, 0.4);
    backdrop-filter: blur(1px);
    z-index: -1;
    pointer-events: none;
}

/* Preserve background image but remove unwanted tints */
body {
    background-color: transparent !important;
}

.container {
    background: transparent !important;
}

.section {
    background: transparent !important;
}

/* Hero Section - ensure consistent green tint with other sections */
.hero-section::before {
    background: rgba(46, 125, 50, 0.4) !important;
    backdrop-filter: blur(1px) !important;
}

.hero-section::after {
    background: transparent !important;
}

/* Hero Text Maximized Layout */
.hero-text-maximized {
    text-align: center;
    max-width: 900px;
    margin: 0 auto;
}

.hero-content {
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    z-index: 3;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 2rem;
}

/* Enhanced text visibility */
.hero-text-maximized .hero-title {
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.8), 0 0 20px rgba(0, 0, 0, 0.6);
    font-weight: 900;
    color: white;
}

.hero-text-maximized .hero-description {
    text-shadow: 1px 1px 6px rgba(0, 0, 0, 0.8), 0 0 15px rgba(0, 0, 0, 0.5);
    color: rgba(255, 255, 255, 0.95);
    font-weight: 500;
    font-size: 1.3rem;
    line-height: 1.7;
}

/* Override highlight color to white with strong shadow */
.hero-text-maximized .highlight {
    background: none;
    -webkit-background-clip: unset;
    -webkit-text-fill-color: unset;
    background-clip: unset;
    color: white;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.9), 0 0 25px rgba(0, 0, 0, 0.7);
    font-weight: 900;
}

/* Center buttons beneath description */
.hero-text-maximized .hero-buttons {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    margin-top: 2.5rem;
}

/* Enhanced button visibility */
.hero-text-maximized .btn {
    font-weight: 600;
    font-size: 1.1rem;
    padding: 1rem 2.5rem;
    border-radius: 50px;
    text-decoration: none;
    transition: all 0.3s ease;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.hero-text-maximized .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
}

/* Responsive grid adjustments */
.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  margin-top: 2rem;
}
</style>

<?php include '../includes/footer.php'; ?>
