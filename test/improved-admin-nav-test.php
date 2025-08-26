<?php
// Test page for improved admin navigation
session_start();

// Simulate admin login for testing
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 1;
$_SESSION['admin_username'] = 'admin_user';

$base_path = '../';
$page_title = 'Improved Admin Navigation Test';

include '../includes/admin_header.php';
?>

<div style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
    <div style="background: white; padding: 2.5rem; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.1);">
        <h1 style="color: #1b5e20; margin-bottom: 1.5rem; font-size: 2.5rem;">🚀 Improved Admin Navigation</h1>
        <p style="color: #666; margin-bottom: 2rem; font-size: 1.1rem;">Testing the enhanced admin navigation with better styling and improved user experience.</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
            <div style="background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%); padding: 2rem; border-radius: 12px; border-left: 4px solid #4caf50;">
                <h3 style="color: #1b5e20; margin: 0 0 1rem 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle" style="color: #4caf50;"></i>
                    Navigation Improvements
                </h3>
                <ul style="margin: 0; padding-left: 1.5rem; color: #2e7d32;">
                    <li>✨ Enhanced dashboard button with gradient styling</li>
                    <li>🎯 Removed services link for cleaner navigation</li>
                    <li>🎨 Improved hover effects and animations</li>
                    <li>📱 Better responsive design for mobile</li>
                    <li>🌟 Interactive brand logo with rotation effect</li>
                    <li>💎 Glassmorphism effects on user info</li>
                </ul>
            </div>
            
            <div style="background: linear-gradient(135deg, #e3f2fd 0%, #e1f5fe 100%); padding: 2rem; border-radius: 12px; border-left: 4px solid #2196f3;">
                <h3 style="color: #1976d2; margin: 0 0 1rem 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-palette" style="color: #2196f3;"></i>
                    Visual Enhancements
                </h3>
                <ul style="margin: 0; padding-left: 1.5rem; color: #1565c0;">
                    <li>🎨 Smooth cubic-bezier transitions</li>
                    <li>✨ Backdrop blur effects</li>
                    <li>🌈 Enhanced gradient backgrounds</li>
                    <li>💫 Hover transformations and scaling</li>
                    <li>🎯 Better visual hierarchy</li>
                    <li>📐 Consistent spacing and alignment</li>
                </ul>
            </div>
        </div>
        
        <div style="background: linear-gradient(135deg, #fff3e0 0%, #fef7f0 100%); padding: 2rem; border-radius: 12px; border-left: 4px solid #ff9800; margin-bottom: 2rem;">
            <h3 style="color: #f57c00; margin: 0 0 1rem 0; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-mobile-alt" style="color: #ff9800;"></i>
                Responsive Features
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <div>
                    <h4 style="color: #e65100; margin-bottom: 0.5rem;">📱 Mobile (< 480px)</h4>
                    <ul style="margin: 0; padding-left: 1.5rem; color: #f57c00; font-size: 0.9rem;">
                        <li>Vertical navigation layout</li>
                        <li>Centered navigation items</li>
                        <li>Optimized button sizes</li>
                    </ul>
                </div>
                <div>
                    <h4 style="color: #e65100; margin-bottom: 0.5rem;">💻 Tablet (< 768px)</h4>
                    <ul style="margin: 0; padding-left: 1.5rem; color: #f57c00; font-size: 0.9rem;">
                        <li>Flexible wrapping navigation</li>
                        <li>Adjusted spacing and padding</li>
                        <li>Optimized touch targets</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div style="background: linear-gradient(135deg, #f3e5f5 0%, #fce4ec 100%); padding: 2rem; border-radius: 12px; border-left: 4px solid #9c27b0;">
            <h3 style="color: #7b1fa2; margin: 0 0 1rem 0; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-mouse-pointer" style="color: #9c27b0;"></i>
                Interactive Elements
            </h3>
            <p style="margin: 0 0 1rem 0; color: #8e24aa;">Hover over the navigation elements to experience:</p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div style="background: rgba(156, 39, 176, 0.1); padding: 1rem; border-radius: 8px;">
                    <strong style="color: #7b1fa2;">Dashboard Button:</strong>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: #8e24aa;">Scale effect, enhanced shadow, gradient transition</p>
                </div>
                <div style="background: rgba(156, 39, 176, 0.1); padding: 1rem; border-radius: 8px;">
                    <strong style="color: #7b1fa2;">Navigation Links:</strong>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: #8e24aa;">Slide effect, elevation, color transition</p>
                </div>
                <div style="background: rgba(156, 39, 176, 0.1); padding: 1rem; border-radius: 8px;">
                    <strong style="color: #7b1fa2;">Brand Logo:</strong>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: #8e24aa;">360° rotation, scale transform</p>
                </div>
                <div style="background: rgba(156, 39, 176, 0.1); padding: 1rem; border-radius: 8px;">
                    <strong style="color: #7b1fa2;">User Info:</strong>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: #8e24aa;">Glassmorphism effect, backdrop blur</p>
                </div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 3rem;">
            <a href="dashboard.php" class="admin-btn" style="margin-right: 1rem; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-tachometer-alt"></i> 
                Go to Dashboard
            </a>
            <a href="admin-header-test.php" class="admin-btn" style="background: linear-gradient(135deg, #2196f3 0%, #64b5f6 100%); display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-flask"></i>
                View Basic Test
            </a>
        </div>
    </div>
</div>

<script>
// Add some interactive JavaScript for demonstration
document.addEventListener('DOMContentLoaded', function() {
    // Add click tracking for demonstration
    const navLinks = document.querySelectorAll('.admin-nav-link, .dashboard-btn');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Don't prevent default, just log for demo
            console.log('Navigation clicked:', this.textContent.trim());
        });
    });
    
    // Add keyboard navigation support
    document.addEventListener('keydown', function(e) {
        if (e.altKey && e.key === 'd') {
            e.preventDefault();
            window.location.href = 'dashboard.php';
        }
    });
    
    console.log('🚀 Enhanced admin navigation loaded successfully!');
});
</script>

</body>
</html>