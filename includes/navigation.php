<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="modern-nav">
  <div class="nav-container">
    <div class="nav-brand">
      <div class="brand-logo">
        <span class="logo-icon">🏘️</span>
        <span class="brand-text">Gumaoc East</span>
      </div>
    </div>
    
    <ul class="nav-menu">
      <li><a href="<?php echo $base_path; ?>index.php" <?php echo $current_page === 'index.php' ? 'class="active"' : ''; ?>>
        <span class="nav-icon">🏠</span>Home</a></li>
      <li><a href="<?php echo $base_path; ?>pages/services.php" <?php echo $current_page === 'services.php' ? 'class="active"' : ''; ?>>
        <span class="nav-icon">💻</span>E-Services</a></li>
      <li><a href="<?php echo $base_path; ?>pages/report.php" <?php echo $current_page === 'report.php' ? 'class="active"' : ''; ?>>
        <span class="nav-icon">🚨</span>Report Incident</a></li>
      <li class="nav-dropdown">
        <a href="<?php echo $base_path; ?>pages/forms.php" <?php echo in_array($current_page, ['forms.php', 'resident-registration.php', 'certificate-request.php', 'business-application.php']) ? 'class="active"' : ''; ?>>
          <span class="nav-icon">📋</span>Forms <span class="dropdown-arrow">▼</span>
        </a>
        <ul class="dropdown-menu">
          <li><a href="<?php echo $base_path; ?>pages/resident-registration.php" <?php echo $current_page === 'resident-registration.php' ? 'class="current-page"' : ''; ?>>
            <span class="dropdown-icon">👥</span>Census Registration
          </a></li>
          <li><a href="<?php echo $base_path; ?>pages/certificate-request.php" <?php echo $current_page === 'certificate-request.php' ? 'class="current-page"' : ''; ?>>
            <span class="dropdown-icon">📄</span>Certificate Requests
          </a></li>
          <li><a href="<?php echo $base_path; ?>pages/business-application.php" <?php echo $current_page === 'business-application.php' ? 'class="current-page"' : ''; ?>>
            <span class="dropdown-icon">🏢</span>Business Application
          </a></li>
          <li><a href="<?php echo $base_path; ?>pages/forms.php" <?php echo $current_page === 'forms.php' ? 'class="current-page"' : ''; ?>>
            <span class="dropdown-icon">📋</span>All Forms
          </a></li>
        </ul>
      </li>
      <li><a href="<?php echo $base_path; ?>pages/rfid-registration.php" <?php echo $current_page === 'rfid-registration.php' ? 'class="active"' : ''; ?>>
        <span class="nav-icon">🏷️</span>RFID Registration</a></li>
      <li><a href="<?php echo $base_path; ?>pages/about.php" <?php echo $current_page === 'about.php' ? 'class="active"' : ''; ?>>
        <span class="nav-icon">ℹ️</span>About</a></li>
      <li><a href="<?php echo $base_path; ?>pages/contact.php" <?php echo $current_page === 'contact.php' ? 'class="active"' : ''; ?>>
        <span class="nav-icon">📞</span>Contact</a></li>
    </ul>
    
    <div class="nav-toggle" onclick="toggleMobileMenu()">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </div>
</nav>

<style>
/* Modern navigation styles - matching header green gradient */
.modern-nav {
  background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 50%, #388e3c 100%);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 4px 20px rgba(27, 94, 32, 0.3);
  position: sticky;
  top: 0;
  z-index: 1000;
  transition: all 0.3s ease;
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
}

.nav-brand {
  display: flex;
  align-items: center;
}

.brand-logo {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  color: white;
  text-decoration: none;
}

.logo-icon {
  font-size: 2rem;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.brand-text {
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: -0.5px;
}

.nav-menu {
  display: flex;
  list-style: none;
  margin: 0;
  padding: 0;
  gap: 0.5rem;
}

.nav-menu li {
  position: relative;
}

.nav-menu > li > a {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.8rem 1.2rem;
  color: rgba(255, 255, 255, 0.9);
  text-decoration: none;
  border-radius: 25px;
  transition: all 0.3s ease;
  font-weight: 500;
  font-size: 0.95rem;
}

.nav-menu > li > a:hover,
.nav-menu > li > a.active {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.nav-icon {
  font-size: 1.2rem;
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.2));
}

/* Dropdown Styles - COMPLETELY REWRITTEN */
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

/* Dropdown Menu - Fixed Structure */
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

/* Dropdown links - ALWAYS VISIBLE */
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

/* Normal hover state for dropdown items */
.dropdown-menu li a:hover {
  background: linear-gradient(135deg, #e8f5e8 0%, #f1f8e9 100%);
  color: #1b5e20;
  padding-left: 1.5rem;
}

/* Current page styling in dropdown */
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

/* Current page indicator */
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

/* Mobile toggle button */
.nav-toggle {
  display: none;
  flex-direction: column;
  cursor: pointer;
  padding: 0.5rem;
  gap: 3px;
}

.nav-toggle span {
  width: 25px;
  height: 3px;
  background: white;
  border-radius: 2px;
  transition: all 0.3s ease;
}

.nav-toggle.active span:nth-child(1) {
  transform: rotate(45deg) translate(6px, 6px);
}

.nav-toggle.active span:nth-child(2) {
  opacity: 0;
}

.nav-toggle.active span:nth-child(3) {
  transform: rotate(-45deg) translate(6px, -6px);
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .nav-container {
    padding: 1rem;
  }
  
  .nav-toggle {
    display: flex;
  }
  
  .nav-menu {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
    flex-direction: column;
    padding: 1rem 0;
    box-shadow: 0 4px 20px rgba(27, 94, 32, 0.4);
    transform: translateY(-100%);
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
  }
  
  .nav-menu.active {
    transform: translateY(0);
    opacity: 1;
    visibility: visible;
  }
  
  .nav-menu > li {
    margin: 0 1rem;
  }
  
  .nav-menu > li > a {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 0.5rem;
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
  
  .dropdown-menu li a.current-page::before {
    color: #81c784;
  }
}
</style>

<script>
function toggleMobileMenu() {
  const navMenu = document.querySelector('.nav-menu');
  const navToggle = document.querySelector('.nav-toggle');
  
  navMenu.classList.toggle('active');
  navToggle.classList.toggle('active');
}

// Enhanced dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
  const dropdown = document.querySelector('.nav-dropdown');
  if (!dropdown) return;
  
  const dropdownMenu = dropdown.querySelector('.dropdown-menu');
  const mainLink = dropdown.querySelector('> a');
  
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
        const navMenu = document.querySelector('.nav-menu');
        const navToggle = document.querySelector('.nav-toggle');
        navMenu.classList.remove('active');
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

// Close mobile menu when clicking on regular nav links
document.querySelectorAll('.nav-menu > li > a').forEach(link => {
  link.addEventListener('click', function() {
    if (!link.parentElement.classList.contains('nav-dropdown')) {
      const navMenu = document.querySelector('.nav-menu');
      const navToggle = document.querySelector('.nav-toggle');
      navMenu.classList.remove('active');
      navToggle.classList.remove('active');
    }
  });
});
</script>