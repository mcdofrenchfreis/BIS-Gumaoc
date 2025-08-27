<?php
$base_path = './';
$page_title = 'Barangay Gumaoc East E-Services System';
$header_title = 'Barangay Gumaoc East';
$header_subtitle = 'Smart Digital Services for Modern Community Living';

include 'includes/header.php';
include 'includes/db_connect.php';

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
            'button_link' => 'pages/report.php',
            'is_featured' => 1,
            'features' => 'IoT Enabled,24/7 Monitoring'
        ],
        [
            'id' => 2,
            'title' => 'Document Requests',
            'description' => 'Request certificates, clearances, and official documents online with automated processing.',
            'button_text' => 'Apply Now',
            'button_link' => 'pages/forms.php',
            'is_featured' => 0,
            'features' => 'Online Processing,Fast Approval'
        ],
        [
            'id' => 3,
            'title' => 'Community Census',
            'description' => 'Register as a resident and contribute to our comprehensive community database.',
            'button_text' => 'Register',
            'button_link' => 'pages/forms.php',
            'is_featured' => 0,
            'features' => 'Digital Registry,Secure Data'
        ],
        [
            'id' => 4,
            'title' => 'Self-Service Kiosk',
            'description' => 'Access services anytime through our interactive kiosk at the barangay hall.',
            'button_text' => 'Explore',
            'button_link' => 'pages/services.php',
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

<!-- Hero Section -->
<div class="hero-section">
  <div class="hero-content">
    <div class="hero-text-maximized">
      <h1 class="hero-title">Welcome to the Future of<br><span class="highlight">Barangay Services</span></h1>
      <p class="hero-description">
        Experience seamless, efficient, and transparent public service through our cutting-edge digital platform. 
        Your community, empowered by technology.
      </p>
      <div class="hero-buttons">
        <a href="pages/forms.php" class="btn btn-primary">Get Started</a>
        <a href="pages/about.php" class="btn btn-outline">Learn More</a>
      </div>
    </div>
  </div>
</div>

<!-- Services Overview -->
<div class="container">
  <div class="section">
    <div class="section-header section-header-card">
      <div class="section-header-content">
        <div class="section-icon">🌐</div>
        <h2>Our Digital Services</h2>
        <p>Comprehensive solutions designed for your convenience</p>
      </div>
      <?php if ($is_admin): ?>
        <div class="admin-controls-section">
          <a href="admin/manage-services.php" class="admin-edit-btn">
            <span class="admin-icon">⚙️</span>
            Manage Services Content
          </a>
        </div>
      <?php endif; ?>
    </div>
    
    <div class="services-grid">
      <?php if (empty($services)): ?>
        <div class="no-services-message">
          <div class="empty-state">
            <div class="empty-icon">📋</div>
            <h3>No Services Available</h3>
            <p>Services will appear here once they are configured.</p>
            <?php if ($is_admin): ?>
              <a href="admin/manage-services.php" class="admin-btn btn-primary">Add Services</a>
            <?php endif; ?>
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($services as $service): ?>
          <div class="service-card <?php echo $service['is_featured'] ? 'featured' : ''; ?>">
            <h3><?php echo htmlspecialchars($service['title']); ?></h3>
            <p><?php echo htmlspecialchars($service['description']); ?></p>
            
            <?php if (!empty($service['features'])): ?>
              <div class="service-features">
                <?php 
                $features = explode(',', $service['features']);
                foreach ($features as $feature): 
                  if (trim($feature)): ?>
                    <span class="feature-tag"><?php echo htmlspecialchars(trim($feature)); ?></span>
                  <?php endif; 
                endforeach; 
                ?>
              </div>
            <?php endif; ?>
            
            <a href="<?php echo htmlspecialchars($service['button_link']); ?>" class="service-btn">
              <?php echo htmlspecialchars($service['button_text']); ?>
            </a>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Latest Updates -->
  <div class="section">
    <div class="section-header section-header-card">
      <div class="section-header-content">
        <div class="section-icon">📢</div>
        <h2>Latest Updates</h2>
        <p>Stay informed with the latest announcements and news</p>
      </div>
      <?php if ($is_admin): ?>
        <div class="admin-controls-section">
          <a href="admin/manage-updates.php" class="admin-edit-btn">
            <span class="admin-icon">📢</span>
            Manage Updates Content
          </a>
        </div>
      <?php endif; ?>
    </div>
    
    <div class="updates-grid">
      <?php if (empty($updates)): ?>
        <div class="no-updates-message">
          <div class="empty-state">
            <div class="empty-icon">📢</div>
            <h3>No Updates Available</h3>
            <p>Latest updates will appear here once they are added.</p>
            <?php if ($is_admin): ?>
              <a href="admin/manage-updates.php" class="admin-btn btn-primary">Add Updates</a>
            <?php endif; ?>
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($updates as $update): ?>
          <div class="update-card <?php echo (!empty($update['is_priority']) && $update['is_priority']) ? 'priority' : ''; ?>">
            <div class="update-badge <?php echo 'badge-' . (isset($update['badge_type']) ? $update['badge_type'] : 'info'); ?>">
              <?php echo htmlspecialchars(isset($update['badge_text']) ? $update['badge_text'] : 'Update'); ?>
            </div>
            <div class="update-icon">
              <?php echo isset($update['icon']) ? $update['icon'] : '📢'; ?>
            </div>
            <h3><?php echo htmlspecialchars($update['title']); ?></h3>
            <p><?php echo htmlspecialchars($update['description']); ?></p>
            <div class="update-meta">
              <span class="update-date">📅 <?php echo htmlspecialchars(isset($update['date']) ? $update['date'] : date('M d, Y')); ?></span>
              <span class="update-status"><?php echo htmlspecialchars(isset($update['status']) ? $update['status'] : '🟢 Active'); ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="section">
    <div class="quick-actions">
      <h2>Quick Actions</h2>
      <div class="actions-grid">
        <a href="pages/report.php" class="action-btn emergency">
          <div class="action-icon">🚨</div>
          <span>Emergency Report</span>
        </a>
        <a href="pages/forms.php" class="action-btn document">
          <div class="action-icon">📄</div>
          <span>Request Document</span>
        </a>
        <a href="pages/contact.php" class="action-btn contact">
          <div class="action-icon">📞</div>
          <span>Contact Us</span>
        </a>
        <a href="pages/services.php" class="action-btn services">
          <div class="action-icon">⚙️</div>
          <span>All Services</span>
        </a>
      </div>
    </div>
  </div>
</div>

<style>
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

/* Responsive adjustments for maximized layout */
@media (max-width: 768px) {
    .hero-text-maximized {
        max-width: 100%;
        padding: 0 1rem;
    }
    
    .hero-text-maximized .hero-buttons {
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }
    
    .hero-text-maximized .btn {
        width: 200px;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .hero-text-maximized .hero-title {
        font-size: 2rem;
    }
    
    .hero-text-maximized .hero-description {
        font-size: 1.1rem;
    }
}

/* Update badge styles for different types */
.update-badge.badge-important {
    background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%) !important;
}

/* Remove gray tint from important update cards */
.update-card.priority {
    background: rgba(255, 255, 255, 0.95) !important;
    border: 1px solid rgba(76, 175, 80, 0.3) !important;
}

.update-badge.badge-new {
    background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
}

.update-badge.badge-community {
    background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
}

.update-badge.badge-info {
    background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
}

/* Empty state styling for updates */
.no-updates-message {
  grid-column: 1 / -1;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 300px;
}

/* Existing styles remain the same */
.no-services-message {
  grid-column: 1 / -1;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 300px;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  border: 2px dashed #e0e0e0;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
}

/* Card-style backgrounds for section headers */
.section-header-card {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border: 1px solid #dee2e6;
  border-radius: 16px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);
  position: relative;
  overflow: hidden;
}

.section-header-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #4A90E2, #7B68EE, #9B59B6, #E74C3C, #F39C12);
  background-size: 400% 100%;
  animation: gradientShift 4s ease infinite;
}

@keyframes gradientShift {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

.section-header-content {
  text-align: center;
  position: relative;
  z-index: 2;
}

.section-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  display: block;
  animation: iconFloat 3s ease-in-out infinite;
}

@keyframes iconFloat {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-10px);
  }
}

.section-header-card h2 {
  color: #2c3e50;
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.section-header-card p {
  color: #6c757d;
  font-size: 1.2rem;
  margin-bottom: 0;
  font-weight: 400;
  line-height: 1.6;
}

.section-header-card .admin-controls-section {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(108, 117, 125, 0.2);
}

/* Hover effect for card headers */
.section-header-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
}

/* Responsive design for card headers */
@media (max-width: 768px) {
  .section-header-card {
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border-radius: 12px;
  }
  
  .section-icon {
    font-size: 2.5rem;
    margin-bottom: 0.8rem;
  }
  
  .section-header-card h2 {
    font-size: 2rem;
  }
  
  .section-header-card p {
    font-size: 1.1rem;
  }
}

@media (max-width: 480px) {
  .section-header-card {
    padding: 1rem;
    margin-bottom: 1rem;
  }
  
  .section-icon {
    font-size: 2rem;
    margin-bottom: 0.6rem;
  }
  
  .section-header-card h2 {
    font-size: 1.8rem;
  }
  
  .section-header-card p {
    font-size: 1rem;
  }
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  opacity: 0.6;
}

.empty-state h3 {
  color: #666;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #999;
  margin-bottom: 1.5rem;
}

/* Responsive grid adjustments */
.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  margin-top: 2rem;
}

@media (max-width: 768px) {
  .services-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .no-services-message,
  .no-updates-message {
    min-height: 200px;
  }
  
  .empty-state {
    padding: 2rem 1rem;
  }
  
  .empty-icon {
    font-size: 3rem;
  }
}
</style>

<?php include 'includes/footer.php'; ?>