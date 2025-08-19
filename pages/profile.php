<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

$base_path = '../';
$page_title = 'My Profile - Barangay Gumaoc East';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception("User not found");
    }
} catch (Exception $e) {
    $error = "Error loading profile data.";
}

// Get user statistics
try {
    // Certificate requests
    $stmt = $pdo->prepare("SELECT COUNT(*) as total, 
                          SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                          SUM(CASE WHEN status = 'ready' THEN 1 ELSE 0 END) as ready
                          FROM certificate_requests WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cert_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Business applications
    $stmt = $pdo->prepare("SELECT COUNT(*) as total,
                          SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                          SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved
                          FROM business_applications WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $business_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Recent activities
    $recent_activities = [];
    
    // Get recent certificate requests
    $stmt = $pdo->prepare("SELECT 'certificate' as type, certificate_type as item, status, created_at as date 
                          FROM certificate_requests WHERE user_id = ? 
                          ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_activities = array_merge($recent_activities, $stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Get recent business applications
    $stmt = $pdo->prepare("SELECT 'business' as type, business_name as item, status, submitted_at as date 
                          FROM business_applications WHERE user_id = ? 
                          ORDER BY submitted_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_activities = array_merge($recent_activities, $stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Sort by date
    usort($recent_activities, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    $recent_activities = array_slice($recent_activities, 0, 5);
    
} catch (Exception $e) {
    $cert_stats = ['total' => 0, 'pending' => 0, 'ready' => 0];
    $business_stats = ['total' => 0, 'pending' => 0, 'approved' => 0];
    $recent_activities = [];
}

include '../includes/header.php';
?>

<div class="container">
    <div class="profile-header">
        <div class="profile-avatar">
            <div class="avatar-circle">
                <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
                <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                <p class="profile-id">Resident ID: #<?php echo str_pad($user['id'], 6, '0', STR_PAD_LEFT); ?></p>
            </div>
        </div>
        <div class="profile-actions">
            <a href="account-settings.php" class="btn btn-primary">
                <span class="btn-icon">⚙️</span>
                Account Settings
            </a>
        </div>
    </div>

    <div class="profile-content">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-info">
                    <h3><?php echo $cert_stats['total']; ?></h3>
                    <p>Certificate Requests</p>
                    <div class="stat-breakdown">
                        <span class="stat-pending"><?php echo $cert_stats['pending']; ?> Pending</span>
                        <span class="stat-ready"><?php echo $cert_stats['ready']; ?> Ready</span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🏢</div>
                <div class="stat-info">
                    <h3><?php echo $business_stats['total']; ?></h3>
                    <p>Business Applications</p>
                    <div class="stat-breakdown">
                        <span class="stat-pending"><?php echo $business_stats['pending']; ?> Pending</span>
                        <span class="stat-approved"><?php echo $business_stats['approved']; ?> Approved</span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-info">
                    <h3><?php echo date('Y'); ?></h3>
                    <p>Member Since</p>
                    <div class="stat-breakdown">
                        <span class="stat-date"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="info-section">
            <div class="section-header">
                <h2>👤 Personal Information</h2>
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <label>Full Name</label>
                    <span><?php echo htmlspecialchars($user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name']); ?></span>
                </div>
                <div class="info-item">
                    <label>Email Address</label>
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="info-item">
                    <label>Phone Number</label>
                    <span><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></span>
                </div>
                <div class="info-item">
                    <label>Address</label>
                    <span><?php echo htmlspecialchars($user['address'] ?? 'Not provided'); ?></span>
                </div>
                <div class="info-item">
                    <label>RFID Status</label>
                    <span class="<?php echo $user['rfid_code'] ? 'status-active' : 'status-inactive'; ?>">
                        <?php echo $user['rfid_code'] ? '✅ Registered' : '❌ Not Registered'; ?>
                    </span>
                </div>
                <div class="info-item">
                    <label>Account Status</label>
                    <span class="status-active">✅ Active</span>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="activities-section">
            <div class="section-header">
                <h2>📊 Recent Activities</h2>
            </div>
            <?php if (!empty($recent_activities)): ?>
            <div class="activities-list">
                <?php foreach ($recent_activities as $activity): ?>
                <div class="activity-item">
                    <div class="activity-icon">
                        <?php echo $activity['type'] === 'certificate' ? '📋' : '🏢'; ?>
                    </div>
                    <div class="activity-info">
                        <h4><?php echo htmlspecialchars($activity['item']); ?></h4>
                        <p><?php echo ucfirst($activity['type']); ?> Application</p>
                    </div>
                    <div class="activity-status">
                        <span class="status-badge status-<?php echo $activity['status']; ?>">
                            <?php echo ucfirst($activity['status']); ?>
                        </span>
                        <span class="activity-date">
                            <?php echo date('M j, Y', strtotime($activity['date'])); ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-activities">
                <div class="no-activities-icon">📭</div>
                <h3>No Recent Activities</h3>
                <p>You haven't submitted any applications yet.</p>
                <a href="../pages/forms.php" class="btn btn-primary">Browse Forms</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Global Styles */
body {
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: white;
    color: #2c3e50;
    line-height: 1.6;
}

.container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background: white;
}

/* Profile Header */
.profile-header {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    padding: 2rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 10px 40px rgba(76, 175, 80, 0.3);
}

.profile-avatar {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.avatar-circle {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 700;
    border: 3px solid rgba(255, 255, 255, 0.3);
}

.profile-info h1 {
    margin: 0 0 0.5rem 0;
    font-size: 1.8rem;
    font-weight: 700;
}

.profile-email {
    margin: 0 0 0.3rem 0;
    opacity: 0.9;
    font-size: 1.1rem;
}

.profile-id {
    margin: 0;
    opacity: 0.8;
    font-size: 0.9rem;
    font-family: 'Courier New', monospace;
}

.profile-actions .btn {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.8rem 1.5rem;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.profile-actions .btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 16px;
    border: 1px solid #e9ecef;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.stat-icon {
    font-size: 2.5rem;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #4CAF50, #45a049);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-info h3 {
    margin: 0 0 0.3rem 0;
    font-size: 2rem;
    font-weight: 700;
    color: #2e7d32;
}

.stat-info p {
    margin: 0 0 0.5rem 0;
    color: #666;
    font-weight: 500;
}

.stat-breakdown {
    display: flex;
    gap: 1rem;
    font-size: 0.85rem;
}

.stat-pending { color: #f39c12; }
.stat-ready { color: #27ae60; }
.stat-approved { color: #27ae60; }
.stat-date { color: #666; }

/* Info Section */
.info-section {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.section-header {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.section-header h2 {
    margin: 0;
    color: #2e7d32;
    font-size: 1.5rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-item label {
    font-weight: 600;
    color: #666;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-item span {
    font-size: 1rem;
    color: #2c3e50;
    font-weight: 500;
}

.status-active {
    color: #27ae60 !important;
    font-weight: 600 !important;
}

.status-inactive {
    color: #e74c3c !important;
    font-weight: 600 !important;
}

/* Activities Section */
.activities-section {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.activities-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.activity-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.activity-icon {
    font-size: 1.5rem;
    width: 50px;
    height: 50px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.activity-info {
    flex: 1;
}

.activity-info h4 {
    margin: 0 0 0.3rem 0;
    color: #2c3e50;
    font-size: 1.1rem;
    font-weight: 600;
}

.activity-info p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.activity-status {
    text-align: right;
}

.status-badge {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.3rem;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-processing { background: #cce5ff; color: #004085; }
.status-ready { background: #d4edda; color: #155724; }
.status-approved { background: #d4edda; color: #155724; }
.status-rejected { background: #f8d7da; color: #721c24; }

.activity-date {
    display: block;
    font-size: 0.8rem;
    color: #999;
}

.no-activities {
    text-align: center;
    padding: 3rem 1rem;
}

.no-activities-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.no-activities h3 {
    margin: 0 0 0.5rem 0;
    color: #666;
    font-size: 1.3rem;
}

.no-activities p {
    margin: 0 0 1.5rem 0;
    color: #999;
}

.no-activities .btn {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    padding: 0.8rem 1.5rem;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.no-activities .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(76, 175, 80, 0.3);
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        margin: 10px;
        padding: 15px;
    }
    
    .profile-header {
        flex-direction: column;
        gap: 1.5rem;
        text-align: center;
        padding: 1.5rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .activity-item {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .activity-status {
        text-align: center;
    }
}
</style>

<?php include '../includes/footer.php'; ?>