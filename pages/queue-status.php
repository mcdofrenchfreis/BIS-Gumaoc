<?php
session_start();
$base_path = '../';
$page_title = 'Queue Status - Barangay Gumaoc East';

include '../includes/db_connect.php';
include '../includes/QueueManager.php';

// Initialize queue manager
$queueManager = new QueueManager($pdo);

// Handle lookup request
$lookup_result = null;
$lookup_error = null;

if (isset($_GET['lookup']) && isset($_GET['ticket_number'])) {
    $ticket_number = trim($_GET['ticket_number']);
    if (!empty($ticket_number)) {
        $lookup_result = $queueManager->getTicketStatus($ticket_number);
        if (!$lookup_result) {
            $lookup_error = "Ticket number not found or invalid.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_number'])) {
    $ticket_number = trim($_POST['ticket_number']);
    if (!empty($ticket_number)) {
        $lookup_result = $queueManager->getTicketStatus($ticket_number);
        if (!$lookup_result) {
            $lookup_error = "Ticket number not found or invalid.";
        }
    } else {
        $lookup_error = "Please enter a ticket number.";
    }
}

// Get current queue status
try {
    // Get today's queue statistics
    $today_stats = $pdo->query("
        SELECT 
            qs.service_name,
            COUNT(qt.id) as total_tickets,
            SUM(CASE WHEN qt.status = 'waiting' THEN 1 ELSE 0 END) as waiting,
            SUM(CASE WHEN qt.status = 'serving' THEN 1 ELSE 0 END) as serving,
            SUM(CASE WHEN qt.status = 'completed' THEN 1 ELSE 0 END) as completed
        FROM queue_services qs
        LEFT JOIN queue_tickets qt ON qs.id = qt.service_id AND DATE(qt.created_at) = CURDATE()
        WHERE qs.is_active = 1
        GROUP BY qs.id, qs.service_name
        ORDER BY qs.service_name
    ")->fetchAll();

    // Get current serving tickets (using queue_counters instead of queue_windows)
    $serving_tickets = $pdo->query("
        SELECT 
            qt.ticket_number,
            qt.customer_name,
            qs.service_name,
            qc.counter_name,
            qt.served_at
        FROM queue_tickets qt
        JOIN queue_services qs ON qt.service_id = qs.id
        LEFT JOIN queue_counters qc ON qc.current_ticket_id = qt.id
        WHERE qt.status = 'serving' 
        AND DATE(qt.created_at) = CURDATE()
        ORDER BY qt.served_at DESC
    ")->fetchAll();

    // Get next in queue for each service
    $next_tickets = $pdo->query("
        SELECT 
            qt.ticket_number,
            qt.customer_name,
            qs.service_name,
            qt.queue_position,
            qt.estimated_time
        FROM queue_tickets qt
        JOIN queue_services qs ON qt.service_id = qs.id
        WHERE qt.status = 'waiting' 
        AND DATE(qt.created_at) = CURDATE()
        AND qt.queue_position = (
            SELECT MIN(qt2.queue_position)
            FROM queue_tickets qt2
            WHERE qt2.service_id = qt.service_id 
            AND qt2.status = 'waiting'
            AND DATE(qt2.created_at) = CURDATE()
        )
        ORDER BY qs.service_name
    ")->fetchAll();

} catch (Exception $e) {
    error_log("Queue status error: " . $e->getMessage());
    $today_stats = [];
    $serving_tickets = [];
    $next_tickets = [];
}

include '../includes/header.php';
?>

<style>
/* Background styling with bg2.jpg */
body {
    background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('<?php echo $base_path; ?>assets/images/bg2.jpg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    background-repeat: no-repeat;
    min-height: 100vh;
}

.content-wrapper {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    min-height: calc(100vh - 80px);
}

.container {
    background: transparent;
}

/* Enhanced card styling for better visibility on background */
.lookup-card,
.stat-card,
.serving-card,
.next-card {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
}

.section-header {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.section-header h2 {
    color: #1565c0;
    font-size: 2.5em;
    margin: 0 0 10px 0;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
}

.section-subtitle {
    color: #424242;
    font-size: 1.2em;
    margin: 0;
    opacity: 0.8;
}

/* Lookup Section */
.lookup-section {
    margin-bottom: 40px;
}

.lookup-card {
    border-radius: 15px;
    padding: 35px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.lookup-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
}

.lookup-card h3 {
    margin: 0 0 25px 0;
    color: #1565c0;
    font-size: 24px;
    text-align: center;
}

.lookup-form .form-group {
    margin-bottom: 0;
}

.input-group {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: stretch;
}

.input-group input {
    flex: 1;
    min-width: 300px;
    padding: 15px 20px;
    border: 2px solid rgba(33, 150, 243, 0.3);
    border-radius: 12px;
    font-size: 16px;
    background: rgba(255, 255, 255, 0.9);
    transition: all 0.3s ease;
}

.input-group input:focus {
    outline: none;
    border-color: #2196f3;
    background: rgba(255, 255, 255, 1);
    box-shadow: 0 0 20px rgba(33, 150, 243, 0.3);
}

.btn {
    padding: 15px 25px;
    border-radius: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-primary {
    background: linear-gradient(135deg, #2196f3, #1976d2);
    color: white;
    box-shadow: 0 4px 15px rgba(33, 150, 243, 0.4);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #1976d2, #1565c0);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(33, 150, 243, 0.6);
}

.btn-secondary {
    background: linear-gradient(135deg, #757575, #616161);
    color: white;
    box-shadow: 0 4px 15px rgba(117, 117, 117, 0.4);
}

.btn-outline {
    background: transparent;
    color: #2196f3;
    border: 2px solid #2196f3;
}

.btn-outline:hover {
    background: #2196f3;
    color: white;
}

.btn-success {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.btn-success:hover {
    background: linear-gradient(135deg, #20c997, #17a2b8);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

.ticket-result {
    margin-top: 30px;
    padding: 25px;
    border-radius: 12px;
    border: 1px solid;
    backdrop-filter: blur(10px);
}

.ticket-result.success {
    background: rgba(76, 175, 80, 0.1);
    border-color: rgba(76, 175, 80, 0.3);
    color: #2e7d32;
}

.ticket-result.error {
    background: rgba(244, 67, 54, 0.1);
    border-color: rgba(244, 67, 54, 0.3);
    color: #c62828;
}

.ticket-info h4 {
    margin: 0 0 20px 0;
    color: #1565c0;
    font-size: 20px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.info-item .label {
    font-weight: 600;
    color: #424242;
}

.info-item .value {
    font-weight: bold;
    color: #1565c0;
}

.position-number {
    color: #ff5722 !important;
    font-size: 20px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

/* Status Overview */
.status-overview {
    margin-bottom: 40px;
}

.status-overview h3 {
    margin: 0 0 25px 0;
    color: #1565c0;
    font-size: 28px;
    text-align: center;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 25px;
}

.stat-card {
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.stat-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 15px 45px rgba(0, 0, 0, 0.2);
}

.stat-header {
    background: linear-gradient(135deg, #1565c0, #1976d2);
    color: white;
    padding: 20px 25px;
    border-bottom: none;
}

.stat-header h4 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
}

.stat-body {
    padding: 25px;
    display: flex;
    justify-content: space-around;
    align-items: center;
    background: rgba(255, 255, 255, 0.95);
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 32px;
    font-weight: bold;
    margin-bottom: 8px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

.stat-number.waiting {
    color: #ff9800;
}

.stat-number.serving {
    color: #4caf50;
}

.stat-number.completed {
    color: #2196f3;
}

.stat-label {
    font-size: 13px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
}

.stat-footer {
    background: rgba(248, 249, 250, 0.9);
    padding: 15px 25px;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    text-align: center;
}

.total-tickets {
    font-size: 16px;
    color: #1565c0;
    font-weight: 600;
}

/* Serving Section */
.serving-section, .next-section {
    margin-bottom: 40px;
}

.serving-section h3, .next-section h3 {
    margin: 0 0 25px 0;
    color: #1565c0;
    font-size: 28px;
    text-align: center;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

.serving-grid, .next-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
}

.serving-card, .next-card {
    border-radius: 12px;
    padding: 25px;
    text-align: center;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.serving-card:hover, .next-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.serving-card {
    border-left: 5px solid #4caf50;
    background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(255, 255, 255, 0.95));
}

.next-card {
    border-left: 5px solid #ff9800;
    background: linear-gradient(135deg, rgba(255, 152, 0, 0.1), rgba(255, 255, 255, 0.95));
}

.serving-ticket, .next-ticket {
    font-size: 22px;
    font-weight: bold;
    color: #1565c0;
    margin-bottom: 12px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

.serving-service, .next-service {
    font-size: 16px;
    color: #424242;
    margin-bottom: 12px;
    font-weight: 500;
}

.serving-counter {
    font-size: 14px;
    color: #4caf50;
    font-weight: 600;
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.serving-customer, .next-customer {
    font-size: 18px;
    color: #1565c0;
    font-weight: 600;
}

.next-eta {
    font-size: 14px;
    color: #ff9800;
    font-weight: 600;
    margin-top: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Quick Actions */
.quick-actions {
    text-align: center;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.quick-actions h3 {
    margin: 0 0 30px 0;
    color: #1565c0;
    font-size: 28px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

.action-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Status Badges */
.status-badge {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.status-waiting {
    background: linear-gradient(135deg, #fff3cd, #ffeaa7);
    color: #856404;
}

.status-serving {
    background: linear-gradient(135deg, #d4edda, #00b894);
    color: #155724;
}

.status-completed {
    background: linear-gradient(135deg, #e2e3e5, #74b9ff);
    color: #383d41;
}

.status-cancelled {
    background: linear-gradient(135deg, #f8d7da, #e17055);
    color: #721c24;
}

/* Auto-refresh indicator */
.refresh-indicator {
    position: fixed;
    top: 100px;
    right: 30px;
    background: linear-gradient(135deg, #2196f3, #1976d2);
    color: white;
    padding: 12px 20px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 600;
    opacity: 0;
    transition: all 0.3s ease;
    z-index: 1000;
    box-shadow: 0 4px 15px rgba(33, 150, 243, 0.4);
    backdrop-filter: blur(10px);
}

.refresh-indicator.show {
    opacity: 1;
    transform: translateX(0);
}

/* Responsive Design */
@media (max-width: 768px) {
    .section-header h2 {
        font-size: 2em;
    }
    
    .input-group {
        flex-direction: column;
    }
    
    .input-group input {
        min-width: 100%;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .stat-body {
        flex-direction: column;
        gap: 20px;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .stats-grid,
    .serving-grid,
    .next-grid {
        grid-template-columns: 1fr;
    }
    
    .lookup-card,
    .quick-actions {
        padding: 25px;
    }
}

/* Animation for cards */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stat-card,
.serving-card,
.next-card,
.lookup-card {
    animation: fadeInUp 0.6s ease-out;
}

/* Pulse animation for serving status */
@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.05); opacity: 0.8; }
    100% { transform: scale(1); opacity: 1; }
}

.status-serving {
    animation: pulse 2s infinite;
}
</style>

<div class="container">
    <div class="section">
        <div class="section-header">
            <h2>📊 Queue Status Monitor</h2>
            <p class="section-subtitle">Real-time queue monitoring and ticket lookup</p>
        </div>

        <!-- Ticket Lookup -->
        <div class="lookup-section">
            <div class="lookup-card">
                <h3>🔍 Check Your Ticket Status</h3>
                <form method="POST" class="lookup-form">
                    <div class="form-group">
                        <label for="ticket_number">Enter Ticket Number:</label>
                        <div class="input-group">
                            <input type="text" 
                                   id="ticket_number" 
                                   name="ticket_number" 
                                   placeholder="e.g., BC-20241221-001"
                                   value="<?php echo isset($_POST['ticket_number']) ? htmlspecialchars($_POST['ticket_number']) : ''; ?>"
                                   required>
                            <button type="submit" class="btn btn-primary">🔍 Check Status</button>
                        </div>
                    </div>
                </form>

                <?php if ($lookup_result): ?>
                <div class="ticket-result success">
                    <div class="ticket-info">
                        <h4>Ticket Information</h4>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label">Ticket Number:</span>
                                <span class="value"><?php echo htmlspecialchars($lookup_result['ticket_number']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Service:</span>
                                <span class="value"><?php echo htmlspecialchars($lookup_result['service_name']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Customer:</span>
                                <span class="value"><?php echo htmlspecialchars($lookup_result['customer_name']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Status:</span>
                                <span class="value status-badge status-<?php echo $lookup_result['status']; ?>">
                                    <?php echo ucfirst($lookup_result['status']); ?>
                                </span>
                            </div>
                            <?php if ($lookup_result['status'] === 'waiting' && $lookup_result['queue_position']): ?>
                            <div class="info-item">
                                <span class="label">Position in Queue:</span>
                                <span class="value position-number">#<?php echo $lookup_result['queue_position']; ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($lookup_result['estimated_time']): ?>
                            <div class="info-item">
                                <span class="label">Estimated Time:</span>
                                <span class="value"><?php echo date('g:i A', strtotime($lookup_result['estimated_time'])); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="info-item">
                                <span class="label">Created:</span>
                                <span class="value"><?php echo date('M j, Y g:i A', strtotime($lookup_result['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php elseif ($lookup_error): ?>
                <div class="ticket-result error">
                    <p><?php echo htmlspecialchars($lookup_error); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Current Status Overview -->
        <div class="status-overview">
            <h3>📈 Today's Queue Overview</h3>
            <div class="stats-grid">
                <?php foreach ($today_stats as $stat): ?>
                <div class="stat-card">
                    <div class="stat-header">
                        <h4><?php echo htmlspecialchars($stat['service_name']); ?></h4>
                    </div>
                    <div class="stat-body">
                        <div class="stat-item">
                            <span class="stat-number waiting"><?php echo $stat['waiting']; ?></span>
                            <span class="stat-label">Waiting</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number serving"><?php echo $stat['serving']; ?></span>
                            <span class="stat-label">Serving</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number completed"><?php echo $stat['completed']; ?></span>
                            <span class="stat-label">Completed</span>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="total-tickets">Total: <?php echo $stat['total_tickets']; ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Currently Serving -->
        <?php if (count($serving_tickets) > 0): ?>
        <div class="serving-section">
            <h3>🎯 Currently Serving</h3>
            <div class="serving-grid">
                <?php foreach ($serving_tickets as $ticket): ?>
                <div class="serving-card">
                    <div class="serving-ticket"><?php echo htmlspecialchars($ticket['ticket_number']); ?></div>
                    <div class="serving-service"><?php echo htmlspecialchars($ticket['service_name']); ?></div>
                    <div class="serving-counter"><?php echo htmlspecialchars($ticket['counter_name'] ?? 'Counter'); ?></div>
                    <div class="serving-customer"><?php echo htmlspecialchars($ticket['customer_name']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Next in Queue -->
        <?php if (count($next_tickets) > 0): ?>
        <div class="next-section">
            <h3>⏭️ Next in Queue</h3>
            <div class="next-grid">
                <?php foreach ($next_tickets as $ticket): ?>
                <div class="next-card">
                    <div class="next-ticket"><?php echo htmlspecialchars($ticket['ticket_number']); ?></div>
                    <div class="next-service"><?php echo htmlspecialchars($ticket['service_name']); ?></div>
                    <div class="next-customer"><?php echo htmlspecialchars($ticket['customer_name']); ?></div>
                    <?php if ($ticket['estimated_time']): ?>
                    <div class="next-eta">ETA: <?php echo date('g:i A', strtotime($ticket['estimated_time'])); ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h3>🚀 Quick Actions</h3>
            <div class="action-buttons">
                <a href="queue-ticket.php" class="btn btn-primary">🎫 Get New Ticket</a>
                <a href="queue-kiosk.php" class="btn btn-success">📺 Kiosk Display</a>
                <a href="certificate-request.php" class="btn btn-outline">📄 Request Certificate</a>
                <a href="resident-registration.php" class="btn btn-outline">👥 Register as Resident</a>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh every 30 seconds
setInterval(function() {
    // Show refresh indicator
    showRefreshIndicator();
    
    // Reload the page to get latest data
    setTimeout(function() {
        window.location.reload();
    }, 1000);
}, 30000);

function showRefreshIndicator() {
    // Remove existing indicator
    const existing = document.querySelector('.refresh-indicator');
    if (existing) {
        existing.remove();
    }
    
    // Create new indicator
    const indicator = document.createElement('div');
    indicator.className = 'refresh-indicator show';
    indicator.textContent = '🔄 Refreshing...';
    document.body.appendChild(indicator);
    
    // Hide after 2 seconds
    setTimeout(function() {
        indicator.classList.remove('show');
        setTimeout(function() {
            indicator.remove();
        }, 300);
    }, 2000);
}

// Focus on ticket input when page loads
document.addEventListener('DOMContentLoaded', function() {
    const ticketInput = document.getElementById('ticket_number');
    if (ticketInput && !ticketInput.value) {
        ticketInput.focus();
    }
});

// Add staggered animation to cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.stat-card, .serving-card, .next-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
});
</script>

<?php include '../includes/footer.php'; ?>