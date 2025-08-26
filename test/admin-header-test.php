<?php
// Test page for admin header functionality
session_start();

// Simulate admin login for testing
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 1;
$_SESSION['admin_username'] = 'test_admin';

$base_path = '../';
$page_title = 'Admin Header Test - Admin Panel';

include '../includes/admin_header.php';
?>

<div style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h1 style="color: #1b5e20; margin-bottom: 1rem;">🧪 Admin Header Test Page</h1>
        <p style="color: #666; margin-bottom: 2rem;">This page tests the admin header functionality and navigation.</p>
        
        <div style="background: #f0f8f0; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #4caf50;">
            <h3 style="color: #1b5e20; margin: 0 0 1rem 0;">✅ Admin Header Features Test</h3>
            <ul style="margin: 0; padding-left: 1.5rem;">
                <li>✅ Admin navigation bar should be visible at the top</li>
                <li>✅ Navigation should include: Dashboard, RFID Scanner, RFID Management, Services, etc.</li>
                <li>✅ Admin branding with green theme should be applied</li>
                <li>✅ User welcome message should display admin username</li>
                <li>✅ All navigation links should be functional</li>
                <li>✅ Responsive design should work on mobile devices</li>
            </ul>
        </div>
        
        <div style="margin-top: 2rem; padding: 1.5rem; background: #e3f2fd; border-radius: 8px; border-left: 4px solid #2196f3;">
            <h3 style="color: #1976d2; margin: 0 0 1rem 0;">📋 Navigation Links Test</h3>
            <p style="margin: 0;">Click each navigation link to verify they work correctly:</p>
            <ul style="margin: 1rem 0 0 1.5rem;">
                <li><a href="dashboard.php" style="color: #1976d2;">Dashboard</a></li>
                <li><a href="rfid-scanner.php" style="color: #1976d2;">RFID Scanner</a></li>
                <li><a href="manage-rfid.php" style="color: #1976d2;">RFID Management</a></li>
                <li><a href="manage-services.php" style="color: #1976d2;">Services</a></li>
                <li><a href="view-resident-registrations.php" style="color: #1976d2;">Residents</a></li>
            </ul>
        </div>
        
        <div style="margin-top: 2rem; padding: 1.5rem; background: #fff3e0; border-radius: 8px; border-left: 4px solid #ff9800;">
            <h3 style="color: #f57c00; margin: 0 0 1rem 0;">🔧 Session Information</h3>
            <pre style="background: #f5f5f5; padding: 1rem; border-radius: 4px; font-size: 0.9rem; overflow-x: auto;"><?php
echo "Admin Logged In: " . ($_SESSION['admin_logged_in'] ? 'Yes' : 'No') . "\n";
echo "Admin ID: " . ($_SESSION['admin_id'] ?? 'Not Set') . "\n";
echo "Admin Username: " . ($_SESSION['admin_username'] ?? 'Not Set') . "\n";
echo "Admin User Display Name: " . (isset($admin_user) ? $admin_user['full_name'] : 'Not Set') . "\n";
?></pre>
        </div>
        
        <div style="margin-top: 2rem; text-align: center;">
            <a href="dashboard.php" class="admin-btn" style="margin-right: 1rem;">← Back to Dashboard</a>
            <a href="logout.php" class="admin-btn" style="background: linear-gradient(135deg, #d32f2f 0%, #f44336 100%);">🚪 Logout</a>
        </div>
    </div>
</div>

</body>
</html>