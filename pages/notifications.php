<?php
session_start();
$base_path = '../';
$page_title = 'Notifications - Barangay Gumaoc East';
$header_title = 'Notifications';
$header_subtitle = 'Your alerts and updates';

// Check if user is logged in
if (!isset($_SESSION['rfid_authenticated']) || $_SESSION['rfid_authenticated'] !== true) {
    $_SESSION['auth_error'] = 'Please log in to view your notifications.';
    header('Location: ../auth/login.php');
    exit;
}

include '../includes/db_connect.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Check if notifications table exists, if not create it
try {
    $check_table = $pdo->query("SHOW TABLES LIKE 'notifications'");
    if ($check_table->rowCount() == 0) {
        // Create notifications table
        $create_table_sql = "
        CREATE TABLE `notifications` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `title` varchar(255) NOT NULL,
            `message` text NOT NULL,
            `type` enum('info','success','warning','error','queue','certificate') DEFAULT 'info',
            `action_url` varchar(500) DEFAULT NULL,
            `is_read` tinyint(1) DEFAULT 0,
            `read_at` timestamp NULL DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_is_read` (`is_read`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $pdo->exec($create_table_sql);
        
        // Insert welcome notification for current user
        $insert_welcome = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, type) 
            VALUES (?, 'Welcome to GUMAOC East E-Services', 'Your account has been successfully activated. You can now access all available services.', 'success')
        ");
        $insert_welcome->execute([$user_id]);
    }
} catch (Exception $e) {
    error_log("Notifications table creation error: " . $e->getMessage());
}

// Handle mark as read
if ($_POST['action'] ?? '' === 'mark_read' && isset($_POST['notification_id'])) {
    try {
        $notification_id = (int)$_POST['notification_id'];
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?");
        $stmt->execute([$notification_id, $user_id]);
        
        $_SESSION['success'] = 'Notification marked as read.';
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error updating notification.';
        error_log("Mark read error: " . $e->getMessage());
    }
    header('Location: notifications.php');
    exit;
}

// Handle mark all as read
if ($_POST['action'] ?? '' === 'mark_all_read') {
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        
        $_SESSION['success'] = 'All notifications marked as read.';
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error updating notifications.';
        error_log("Mark all read error: " . $e->getMessage());
    }
    header('Location: notifications.php');
    exit;
}

// Get filter parameters
$filter = $_GET['filter'] ?? 'all';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Initialize variables
$notifications = [];
$total_records = 0;
$total_pages = 0;

// Get notifications with error handling
try {
    // Build query based on filter
    $where_conditions = ["user_id = ?"];
    $params = [$user_id];

    if ($filter === 'unread') {
        $where_conditions[] = "is_read = 0";
    } elseif ($filter === 'read') {
        $where_conditions[] = "is_read = 1";
    }

    $where_clause = "WHERE " . implode(" AND ", $where_conditions);

    // Get total count
    $count_sql = "SELECT COUNT(*) FROM notifications {$where_clause}";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $per_page);

    // Get notifications
    $sql = "SELECT * FROM notifications {$where_clause} ORDER BY created_at DESC LIMIT {$per_page} OFFSET {$offset}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $notifications = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Notifications fetch error: " . $e->getMessage());
    $_SESSION['error'] = 'Error loading notifications.';
}

// Get queue tickets for this user
$queue_tickets = [];
try {
    // Check if queue_tickets table exists
    $check_queue_table = $pdo->query("SHOW TABLES LIKE 'queue_tickets'");
    if ($check_queue_table->rowCount() > 0) {
        $queue_stmt = $pdo->prepare("
            SELECT qt.*, qs.service_name 
            FROM queue_tickets qt 
            LEFT JOIN queue_services qs ON qt.service_id = qs.id 
            WHERE qt.customer_name = ? 
            AND DATE(qt.created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAYS)
            ORDER BY qt.created_at DESC
            LIMIT 10
        ");
        $queue_stmt->execute([$user_name]);
        $queue_tickets = $queue_stmt->fetchAll();
    }
} catch (Exception $e) {
    error_log("Queue tickets fetch error: " . $e->getMessage());
}

// Get certificate requests for this user
$certificate_requests = [];
try {
    // Check if certificate_requests table exists
    $check_cert_table = $pdo->query("SHOW TABLES LIKE 'certificate_requests'");
    if ($check_cert_table->rowCount() > 0) {
        $cert_stmt = $pdo->prepare("
            SELECT cr.*, qt.ticket_number, qt.status as queue_status
            FROM certificate_requests cr
            LEFT JOIN queue_tickets qt ON cr.queue_ticket_id = qt.id
            WHERE cr.full_name = ? 
            AND DATE(cr.submitted_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAYS)
            ORDER BY cr.submitted_at DESC
            LIMIT 5
        ");
        $cert_stmt->execute([$user_name]);
        $certificate_requests = $cert_stmt->fetchAll();
    }
} catch (Exception $e) {
    error_log("Certificate requests fetch error: " . $e->getMessage());
}

// Get unread count for badge
$unread_count = 0;
try {
    $unread_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $unread_stmt->execute([$user_id]);
    $unread_count = $unread_stmt->fetchColumn();
} catch (Exception $e) {
    error_log("Unread count error: " . $e->getMessage());
}

include '../includes/header.php';
?>

<div class="container">
    <div class="section">
        <div class="section-header">
            <h2>🔔 Notifications Center</h2>
            <p class="section-subtitle">Stay updated with your requests and queue status</p>
        </div>

        <!-- Quick Actions -->
        <div class="notification-actions">
            <?php if ($unread_count > 0): ?>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="mark_all_read">
                <button type="submit" class="btn btn-secondary">Mark All as Read (<?php echo $unread_count; ?>)</button>
            </form>
            <?php endif; ?>
            
            <div class="filter-tabs">
                <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                    All (<?php echo $total_records; ?>)
                </a>
                <a href="?filter=unread" class="filter-tab <?php echo $filter === 'unread' ? 'active' : ''; ?>">
                    Unread (<?php echo $unread_count; ?>)
                </a>
                <a href="?filter=read" class="filter-tab <?php echo $filter === 'read' ? 'active' : ''; ?>">
                    Read (<?php echo max(0, $total_records - $unread_count); ?>)
                </a>
            </div>
        </div>

        <!-- Queue Status Summary -->
        <?php if (count($queue_tickets) > 0): ?>
        <div class="notification-section">
            <h3>🎫 Recent Queue Tickets</h3>
            <div class="queue-summary-grid">
                <?php foreach ($queue_tickets as $ticket): ?>
                <div class="queue-summary-card">
                    <div class="queue-card-header">
                        <span class="ticket-number"><?php echo htmlspecialchars($ticket['ticket_number']); ?></span>
                        <span class="queue-status status-<?php echo $ticket['status']; ?>">
                            <?php echo ucfirst($ticket['status']); ?>
                        </span>
                    </div>
                    <div class="queue-card-body">
                        <p class="service-name"><?php echo htmlspecialchars($ticket['service_name'] ?? 'General Service'); ?></p>
                        <?php if ($ticket['queue_position'] && $ticket['status'] === 'waiting'): ?>
                        <p class="queue-position">Position: #<?php echo $ticket['queue_position']; ?></p>
                        <?php endif; ?>
                        <p class="ticket-date"><?php echo date('M j, Y g:i A', strtotime($ticket['created_at'])); ?></p>
                    </div>
                    <div class="queue-card-actions">
                        <a href="queue-status.php?lookup=1&ticket_number=<?php echo urlencode($ticket['ticket_number']); ?>" 
                           class="btn btn-sm btn-primary">View Details</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Certificate Requests Summary -->
        <?php if (count($certificate_requests) > 0): ?>
        <div class="notification-section">
            <h3>📄 Recent Certificate Requests</h3>
            <div class="certificate-summary-grid">
                <?php foreach ($certificate_requests as $request): ?>
                <div class="certificate-summary-card">
                    <div class="cert-card-header">
                        <span class="request-id">#<?php echo str_pad($request['id'], 5, '0', STR_PAD_LEFT); ?></span>
                        <span class="cert-status status-<?php echo $request['status']; ?>">
                            <?php echo ucfirst($request['status']); ?>
                        </span>
                    </div>
                    <div class="cert-card-body">
                        <p class="cert-type"><?php echo htmlspecialchars($request['certificate_type']); ?></p>
                        <?php if ($request['ticket_number']): ?>
                        <p class="queue-info">Queue: <?php echo htmlspecialchars($request['ticket_number']); ?></p>
                        <?php endif; ?>
                        <p class="request-date"><?php echo date('M j, Y g:i A', strtotime($request['submitted_at'])); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- System Notifications -->
        <div class="notification-section">
            <h3>📢 System Notifications</h3>
            
            <?php if (count($notifications) > 0): ?>
            <div class="notifications-list">
                <?php foreach ($notifications as $notification): ?>
                <div class="notification-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
                    <div class="notification-content">
                        <div class="notification-header">
                            <h4 class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></h4>
                            <span class="notification-time">
                                <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                            </span>
                        </div>
                        <div class="notification-body">
                            <p><?php echo htmlspecialchars($notification['message']); ?></p>
                            <?php if ($notification['action_url']): ?>
                            <a href="<?php echo htmlspecialchars($notification['action_url']); ?>" 
                               class="notification-action">View Details</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!$notification['is_read']): ?>
                    <div class="notification-actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="mark_read">
                            <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-outline">Mark as Read</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>" 
                       class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="no-notifications">
                <div class="no-notifications-icon">🔔</div>
                <h3>No notifications yet</h3>
                <p>We'll notify you when there are updates on your requests or queue status.</p>
                <div class="no-notifications-actions">
                    <a href="certificate-request.php" class="btn btn-primary">Request Certificate</a>
                    <a href="../index.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Override body background for notifications page */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: url('../assets/images/bg2.jpg') center/cover no-repeat;
    background-attachment: fixed;
    background-color: #2d5a27;
    min-height: 100vh;
    position: relative;
}

/* Green tint overlay */
body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, 
        rgba(30, 58, 26, 0.8) 100%);
    z-index: 1;
}

/* Ensure content is above overlay */
.content-wrapper {
    position: relative;
    z-index: 2;
}

/* Notification Actions */
.notification-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 15px;
}

.filter-tabs {
    display: flex;
    gap: 5px;
}

.filter-tab {
    padding: 8px 16px;
    border: 1px solid #ddd;
    background: white;
    color: #666;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.filter-tab:hover {
    background: #f8f9fa;
    color: #333;
}

.filter-tab.active {
    background: #2196f3;
    color: white;
    border-color: #2196f3;
}

/* Notification Sections */
.notification-section {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.notification-section h3 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 18px;
}

/* Queue Summary Grid */
.queue-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
}

.queue-summary-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.queue-summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.queue-card-header {
    background: #f8f9fa;
    padding: 12px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ticket-number {
    font-weight: bold;
    color: #2196f3;
}

.queue-card-body {
    padding: 15px;
}

.service-name {
    font-weight: 500;
    margin: 0 0 8px 0;
    color: #333;
}

.queue-position {
    margin: 4px 0;
    color: #666;
    font-size: 14px;
}

.ticket-date {
    margin: 8px 0 0 0;
    color: #888;
    font-size: 13px;
}

.queue-card-actions {
    padding: 12px 15px;
    border-top: 1px solid #f0f0f0;
    background: #fafafa;
}

/* Certificate Summary Grid */
.certificate-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 15px;
}

.certificate-summary-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.certificate-summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.cert-card-header {
    background: #f8f9fa;
    padding: 12px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.request-id {
    font-weight: bold;
    color: #2196f3;
}

.cert-card-body {
    padding: 15px;
}

.cert-type {
    font-weight: 500;
    margin: 0 0 8px 0;
    color: #333;
}

.queue-info {
    margin: 4px 0;
    color: #666;
    font-size: 14px;
}

.request-date {
    margin: 8px 0 0 0;
    color: #888;
    font-size: 13px;
}

/* Status Badges */
.queue-status, .cert-status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-waiting {
    background: #fff3cd;
    color: #856404;
}

.status-serving {
    background: #d4edda;
    color: #155724;
}

.status-completed {
    background: #e2e3e5;
    color: #383d41;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-processing {
    background: #cce5ff;
    color: #0066cc;
}

.status-ready {
    background: #d4edda;
    color: #155724;
}

.status-released {
    background: #e2e3e5;
    color: #383d41;
}

/* Notifications List */
.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.notification-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 20px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.notification-item.unread {
    background: #f8f9ff;
    border-left: 4px solid #2196f3;
}

.notification-item.read {
    background: #fafafa;
    opacity: 0.8;
}

.notification-content {
    flex: 1;
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.notification-title {
    margin: 0;
    color: #333;
    font-size: 16px;
}

.notification-time {
    color: #888;
    font-size: 13px;
    white-space: nowrap;
    margin-left: 15px;
}

.notification-body p {
    margin: 0 0 10px 0;
    color: #666;
    line-height: 1.5;
}

.notification-action {
    color: #2196f3;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
}

.notification-action:hover {
    text-decoration: underline;
}

.notification-actions {
    margin-left: 15px;
}

/* No Notifications */
.no-notifications {
    text-align: center;
    padding: 60px 20px;
}

.no-notifications-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.no-notifications h3 {
    margin: 0 0 10px 0;
    color: #666;
}

.no-notifications p {
    margin: 0 0 30px 0;
    color: #888;
}

.no-notifications-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    gap: 5px;
    margin-top: 30px;
}

.page-link {
    padding: 8px 12px;
    border: 1px solid #ddd;
    background: white;
    color: #666;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.page-link:hover {
    background: #f8f9fa;
    color: #333;
}

.page-link.active {
    background: #2196f3;
    color: white;
    border-color: #2196f3;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .notification-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-tabs {
        justify-content: center;
    }
    
    .queue-summary-grid,
    .certificate-summary-grid {
        grid-template-columns: 1fr;
    }
    
    .notification-item {
        flex-direction: column;
        gap: 15px;
    }
    
    .notification-header {
        flex-direction: column;
        gap: 5px;
    }
    
    .notification-time {
        margin-left: 0;
    }
    
    .no-notifications-actions {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<?php
// Check if we should show toast
$show_toast = isset($_SESSION['success']) || isset($_SESSION['error']);
$toast_message = $_SESSION['success'] ?? $_SESSION['error'] ?? '';
$toast_type = isset($_SESSION['success']) ? 'success' : 'error';

// Clear session variables after getting them
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    unset($_SESSION['error']);
}
?>

<?php if ($show_toast): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    showToast('<?php echo addslashes($toast_message); ?>', '<?php echo $toast_type; ?>');
});
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>