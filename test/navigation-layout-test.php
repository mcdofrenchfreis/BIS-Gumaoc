<?php
// Test page for the updated admin navigation layout
session_start();

// Simulate admin login for testing
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 1;
$_SESSION['admin_username'] = 'admin_user';

$base_path = '../';
$page_title = 'Navigation Layout Test';

include '../includes/admin_header.php';
?>

<div style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
        <h1 style="color: #1b5e20; margin-bottom: 1.5rem;">Navigation Layout Test</h1>
        <p style="color: #666; margin-bottom: 2rem;">This page tests the updated admin navigation layout to ensure it matches the expected design.</p>
        
        <div style="background: #e8f5e9; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #4caf50; margin-bottom: 2rem;">
            <h3 style="color: #2e7d32; margin-top: 0;">Layout Improvements</h3>
            <ul style="color: #388e3c;">
                <li>Reduced spacing between navigation elements</li>
                <li>More compact navigation items</li>
                <li>Better alignment and positioning</li>
                <li>Adjusted padding and font sizes</li>
                <li>Improved responsive behavior</li>
            </ul>
        </div>
        
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="dashboard.php" class="admin-btn">
                <i class="fas fa-tachometer-alt"></i> Go to Dashboard
            </a>
            <a href="../admin/rfid-scanner.php" class="admin-btn" style="background: linear-gradient(135deg, #2196f3 0%, #64b5f6 100%);">
                <i class="fas fa-qrcode"></i> Test RFID Scanner
            </a>
        </div>
    </div>
</div>

<script>
// Add some interactive JavaScript for demonstration
document.addEventListener('DOMContentLoaded', function() {
    console.log('Navigation layout test loaded successfully!');
    
    // Highlight the current page in navigation
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.admin-nav-link, .dashboard-btn');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });
});
</script>

</body>
</html>