<?php
session_start();
include '../includes/admin_header.php';
include '../includes/db_connect.php';
include '../includes/QueueManager.php';

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$queueManager = new QueueManager($pdo);
$success_message = '';
$error_message = '';

// Handle actions
if ($_POST) {
    if (isset($_POST['call_next'])) {
        $counter_id = (int)$_POST['counter_id'];
        $result = $queueManager->callNextTicket($counter_id);
        
        if ($result['success']) {
            $success_message = "Called ticket: " . $result['ticket']['ticket_number'];
        } else {
            $error_message = $result['message'];
        }
    } elseif (isset($_POST['complete_ticket'])) {
        $counter_id = (int)$_POST['counter_id'];
        $notes = trim($_POST['notes'] ?? '');
        $result = $queueManager->completeTicket($counter_id, $notes);
        
        if ($result['success']) {
            $success_message = "Ticket completed successfully";
        } else {
            $error_message = $result['message'];
        }
    }
}

// Get counters and their current status
$counters = $pdo->query("
    SELECT qc.*, qs.service_name, qt.ticket_number, qt.customer_name 
    FROM queue_counters qc 
    LEFT JOIN queue_services qs ON qc.service_id = qs.id 
    LEFT JOIN queue_tickets qt ON qc.current_ticket_id = qt.id 
    WHERE qc.is_active = 1 
    ORDER BY qc.counter_number
")->fetchAll();

// Get today's statistics
$today_stats = $queueManager->getQueueStats();
?>

<div class="container">
    <div class="page-header">
        <h1>🎫 Queue Management</h1>
        <p>Manage queue tickets and service counters</p>
    </div>

    <?php if ($success_message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <?php if ($error_message): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="admin-actions">
        <a href="queue-kiosk.php" class="btn btn-primary" target="_blank">📺 Open Kiosk Display</a>
        <a href="../pages/queue-status.php" class="btn btn-secondary">📊 Public Queue Status</a>
        <a href="../pages/queue-ticket.php" class="btn btn-outline">🎫 Generate Ticket</a>
    </div>

    <!-- Queue Statistics -->
    <div class="stats-section">
        <h2>📈 Today's Statistics</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $today_stats['waiting'] ?? 0; ?></div>
                <div class="stat-label">Waiting</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $today_stats['serving'] ?? 0; ?></div>
                <div class="stat-label">Serving</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $today_stats['completed'] ?? 0; ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
    </div>

    <!-- Counter Management -->
    <div class="counters-section">
        <h2>🏢 Service Counters</h2>
        <div class="counters-grid">
            <?php foreach ($counters as $counter): ?>
            <div class="counter-card">
                <div class="counter-header">
                    <h3><?php echo htmlspecialchars($counter['counter_name']); ?></h3>
                    <span class="counter-number"><?php echo htmlspecialchars($counter['counter_number']); ?></span>
                </div>
                
                <div class="counter-service">
                    <strong>Service:</strong> <?php echo htmlspecialchars($counter['service_name']); ?>
                </div>
                
                <?php if ($counter['current_ticket_id']): ?>
                <div class="current-ticket">
                    <div class="ticket-info">
                        <strong>Current Ticket:</strong> <?php echo htmlspecialchars($counter['ticket_number']); ?><br>
                        <strong>Customer:</strong> <?php echo htmlspecialchars($counter['customer_name']); ?>
                    </div>
                    
                    <form method="POST" style="margin-top: 15px;">
                        <input type="hidden" name="counter_id" value="<?php echo $counter['id']; ?>">
                        <div class="form-group">
                            <textarea name="notes" placeholder="Service notes (optional)" rows="2" style="width: 100%; margin-bottom: 10px;"></textarea>
                        </div>
                        <button type="submit" name="complete_ticket" class="btn btn-success">✅ Complete Ticket</button>
                    </form>
                </div>
                <?php else: ?>
                <div class="no-ticket">
                    <p>No active ticket</p>
                    <form method="POST">
                        <input type="hidden" name="counter_id" value="<?php echo $counter['id']; ?>">
                        <button type="submit" name="call_next" class="btn btn-primary">📢 Call Next</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.admin-actions {
    display: flex;
    gap: 15px;
    margin: 30px 0;
    flex-wrap: wrap;
}

.stats-section {
    background: white;
    border-radius: 12px;
    padding: 30px;
    margin: 30px 0;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.stat-card {
    background: linear-gradient(135deg, #1565c0, #1976d2);
    color: white;
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(21, 101, 192, 0.3);
}

.stat-number {
    font-size: 3rem;
    font-weight: 900;
    margin-bottom: 10px;
}

.stat-label {
    font-size: 1.1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.counters-section {
    background: white;
    border-radius: 12px;
    padding: 30px;
    margin: 30px 0;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.counters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
    margin-top: 20px;
}

.counter-card {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 25px;
    transition: all 0.3s ease;
}

.counter-card:hover {
    border-color: #1565c0;
    box-shadow: 0 4px 15px rgba(21, 101, 192, 0.1);
}

.counter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.counter-header h3 {
    color: #1565c0;
    margin: 0;
}

.counter-number {
    background: #1565c0;
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
}

.counter-service {
    margin-bottom: 20px;
    color: #666;
}

.current-ticket {
    background: rgba(76, 175, 80, 0.1);
    border: 2px solid #4caf50;
    border-radius: 8px;
    padding: 20px;
}

.ticket-info {
    margin-bottom: 15px;
    font-size: 1.1rem;
}

.no-ticket {
    background: rgba(158, 158, 158, 0.1);
    border: 2px solid #9e9e9e;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}

.no-ticket p {
    color: #666;
    margin-bottom: 15px;
    font-style: italic;
}

.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-family: inherit;
    resize: vertical;
}

@media (max-width: 768px) {
    .counters-grid {
        grid-template-columns: 1fr;
    }
    
    .admin-actions {
        flex-direction: column;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/admin_footer.php'; ?>