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
      <li><a href="<?php echo $base_path; ?>pages/forms.php" <?php echo $current_page === 'forms.php' ? 'class="active"' : ''; ?>>
        <span class="nav-icon">📋</span>Forms</a></li>
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

<script>
function toggleMobileMenu() {
  const navMenu = document.querySelector('.nav-menu');
  const navToggle = document.querySelector('.nav-toggle');
  
  navMenu.classList.toggle('active');
  navToggle.classList.toggle('active');
}

// Close mobile menu when clicking on a link
document.querySelectorAll('.nav-menu a').forEach(link => {
  link.addEventListener('click', () => {
    const navMenu = document.querySelector('.nav-menu');
    const navToggle = document.querySelector('.nav-toggle');
    navMenu.classList.remove('active');
    navToggle.classList.remove('active');
  });
});
</script>