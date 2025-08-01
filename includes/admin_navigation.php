<?php
// Get the current page filename without extension
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h3>Admin Panel</h3>
        <p>Welcome, <?php echo htmlspecialchars($admin_user['full_name']); ?></p>
    </div>
    
    <ul class="nav-menu">
        <li class="nav-title">Overview</li>
        <li class="nav-item">
            <a href="index.php" class="<?php echo $current_page === 'index' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-title">Content Management</li>
        <li class="nav-item">
            <a href="pages.php" class="<?php echo $current_page === 'pages' ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i>
                <span>Page Content</span>
            </a>
        </li>

        <li class="nav-title">Services</li>
        <li class="nav-item">
            <a href="rfid.php" class="<?php echo $current_page === 'rfid' ? 'active' : ''; ?>">
                <i class="fas fa-id-card"></i>
                <span>RFID Management</span>
            </a>
        </li>

        <li class="nav-title">Account</li>
        <li class="nav-item">
            <a href="logout.php" class="nav-link logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</aside> 