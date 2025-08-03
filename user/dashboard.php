<?php
require_once 'auth_check.php';
$page_title = 'User Dashboard';
$base_path = '../';

include '../includes/header.php';

// Determine user type and get appropriate display information
$user_type = $_SESSION['user_type'] ?? 'legacy';
$display_name = '';
$display_email = '';

if ($user_type === 'resident') {
    // Resident user - use first_name and last_name
    $display_name = $user['first_name'] . ' ' . $user['last_name'];
    $display_email = $user['contact_number'] ? 'Contact: ' . $user['contact_number'] : 'No contact info';
} else {
    // Legacy user - use full_name and email
    $display_name = $user['full_name'] ?? 'Unknown User';
    $display_email = $user['email'] ?? 'No email';
}
?>

<style>
.user-dashboard {
    min-height: 100vh;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 20px;
}

.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
}

.welcome-section {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.welcome-section h1 {
    color: #333;
    margin-bottom: 10px;
    font-size: 32px;
}

.welcome-section p {
    color: #666;
    font-size: 18px;
    margin-bottom: 20px;
}

.user-info {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.user-avatar {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: white;
    font-weight: bold;
}

.user-details h3 {
    color: #333;
    margin-bottom: 5px;
}

.user-details p {
    color: #666;
    margin: 0;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

.service-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    text-align: center;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.service-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 32px;
}

.service-card h3 {
    color: #333;
    margin-bottom: 15px;
    font-size: 24px;
}

.service-card p {
    color: #666;
    margin-bottom: 25px;
    line-height: 1.6;
}

.service-btn {
    display: inline-block;
    padding: 12px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.service-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    color: white;
}

.quick-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.stat-number {
    font-size: 32px;
    font-weight: bold;
    color: #667eea;
    margin-bottom: 5px;
}

.stat-label {
    color: #666;
    font-size: 14px;
}

.logout-section {
    text-align: center;
    margin-top: 30px;
}

.logout-btn {
    display: inline-block;
    padding: 12px 30px;
    background: #dc3545;
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.logout-btn:hover {
    background: #c82333;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(220, 53, 69, 0.3);
    color: white;
}

@media (max-width: 768px) {
    .services-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<div class="user-dashboard">
    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1>Welcome back, <?php echo htmlspecialchars($display_name); ?>!</h1>
            <p>Access your personalized barangay services and manage your requests</p>
            
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($display_name, 0, 1)); ?>
                </div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($display_name); ?></h3>
                    <p><?php echo htmlspecialchars($display_email); ?></p>
                    <?php if ($user_type === 'resident'): ?>
                        <p><small>Login ID: <?php echo htmlspecialchars($user['login_id'] ?? 'N/A'); ?></small></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Active Reports</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Pending Requests</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Completed Services</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Total Requests</div>
            </div>
        </div>

        <!-- Services Grid -->
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">📊</div>
                <h3>Reports</h3>
                <p>Submit incident reports, track their status, and view your reporting history. Get real-time updates on your submitted reports.</p>
                <a href="reports.php" class="service-btn">View Reports</a>
            </div>
            
            <div class="service-card">
                <div class="service-icon">💻</div>
                <h3>E-Services</h3>
                <p>Access all available electronic services including document requests, business applications, and community services.</p>
                <a href="e-services.php" class="service-btn">Access Services</a>
            </div>
        </div>

        <!-- Logout Section -->
        <div class="logout-section">
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 