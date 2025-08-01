<?php
$base_path = './';
$page_title = 'Barangay Gumaoc East E-Services System';
$header_title = 'Barangay Gumaoc East';
$header_subtitle = 'Smart Digital Services for Modern Community Living';

include 'includes/header.php';
include 'includes/navigation.php';
?>

<!-- Hero Section -->
<div class="hero-section">
  <div class="hero-content">
    <div class="hero-text">
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
    <div class="hero-image">
      <div class="floating-card">
        <div class="card-icon">🏛️</div>
        <h4>Digital Government</h4>
        <p>24/7 accessible services</p>
      </div>
      <div class="floating-card">
        <div class="card-icon">📱</div>
        <h4>Mobile First</h4>
        <p>Optimized for all devices</p>
      </div>
      <div class="floating-card">
        <div class="card-icon">⚡</div>
        <h4>Lightning Fast</h4>
        <p>Instant processing</p>
      </div>
    </div>
  </div>
</div>

<!-- Services Overview -->
<div class="container">
  <div class="section">
    <div class="section-header">
      <h2>Our Digital Services</h2>
      <p>Comprehensive solutions designed for your convenience</p>
    </div>
    
    <div class="services-grid">
      <div class="service-card featured">
        <div class="service-icon">🚨</div>
        <h3>Emergency Response</h3>
        <p>Real-time incident reporting with IoT sensors and instant emergency response coordination.</p>
        <div class="service-features">
          <span class="feature-tag">IoT Enabled</span>
          <span class="feature-tag">24/7 Monitoring</span>
        </div>
        <a href="pages/report.php" class="service-btn">Report Incident</a>
      </div>
      
      <div class="service-card">
        <div class="service-icon">📋</div>
        <h3>Document Requests</h3>
        <p>Request certificates, clearances, and official documents online with automated processing.</p>
        <div class="service-features">
          <span class="feature-tag">Online Processing</span>
          <span class="feature-tag">Fast Approval</span>
        </div>
        <a href="pages/forms.php" class="service-btn">Apply Now</a>
      </div>
      
      <div class="service-card">
        <div class="service-icon">👥</div>
        <h3>Community Census</h3>
        <p>Register as a resident and contribute to our comprehensive community database.</p>
        <div class="service-features">
          <span class="feature-tag">Digital Registry</span>
          <span class="feature-tag">Secure Data</span>
        </div>
        <a href="pages/forms.php" class="service-btn">Register</a>
      </div>
      
      <div class="service-card">
        <div class="service-icon">🖥️</div>
        <h3>Self-Service Kiosk</h3>
        <p>Access services anytime through our interactive kiosk at the barangay hall.</p>
        <div class="service-features">
          <span class="feature-tag">24/7 Access</span>
          <span class="feature-tag">Touch Interface</span>
        </div>
        <a href="pages/services.php" class="service-btn">Explore</a>
      </div>
    </div>
  </div>

  <!-- Statistics Section -->
  <div class="section">
    <div class="stats-container">
      <div class="stat-card">
        <div class="stat-number">500+</div>
        <div class="stat-label">Residents Served</div>
      </div>
      <div class="stat-card">
        <div class="stat-number">98%</div>
        <div class="stat-label">Satisfaction Rate</div>
      </div>
      <div class="stat-card">
        <div class="stat-number">24/7</div>
        <div class="stat-label">Service Availability</div>
      </div>
      <div class="stat-card">
        <div class="stat-number">15min</div>
        <div class="stat-label">Average Response</div>
      </div>
    </div>
  </div>

  <!-- Latest Updates -->
  <div class="section">
    <div class="section-header">
      <h2>Latest Updates</h2>
      <p>Stay informed with the latest announcements and news</p>
    </div>
    
    <div class="updates-grid">
      <div class="update-card priority">
        <div class="update-badge">Important</div>
        <div class="update-icon">💉</div>
        <h3>COVID-19 Vaccination Drive</h3>
        <p>New vaccination schedule available. Free vaccination for all residents. Register online to secure your slot.</p>
        <div class="update-meta">
          <span class="update-date">📅 July 28, 2025</span>
          <span class="update-status">🟢 Active</span>
        </div>
      </div>
      
      <div class="update-card">
        <div class="update-badge">New</div>
        <div class="update-icon">🚀</div>
        <h3>Enhanced E-Services Launch</h3>
        <p>Our improved digital platform now offers faster processing, better security, and mobile optimization.</p>
        <div class="update-meta">
          <span class="update-date">📅 July 25, 2025</span>
          <span class="update-status">🟢 Live</span>
        </div>
      </div>
      
      <div class="update-card">
        <div class="update-badge">Community</div>
        <div class="update-icon">🎉</div>
        <h3>Town Fiesta 2025</h3>
        <p>Join us for our annual town celebration. Cultural shows, local food, and community activities for everyone.</p>
        <div class="update-meta">
          <span class="update-date">📅 August 15, 2025</span>
          <span class="update-status">🟡 Upcoming</span>
        </div>
      </div>
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

<?php include 'includes/footer.php'; ?> 