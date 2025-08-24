<?php
session_start();
include '../includes/db_connect.php';
include '../includes/AdminLogger.php';
include '../includes/QueueManager.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$logger = new AdminLogger($pdo);
$queueManager = new QueueManager($pdo);

// Handle actions
$message = '';
$message_type = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'call_next':
            $window_id = (int)$_POST['window_id'];
            // Placeholder for call next ticket functionality
            $message = "Call next ticket functionality not implemented yet";
            $message_type = 'info';
            break;
            
        case 'complete_ticket':
            $ticket_id = (int)$_POST['ticket_id'];
            $notes = trim($_POST['notes'] ?? '');
            
            if ($queueManager->updateTicketStatus($ticket_id, 'completed', $notes)) {
                $message = "Ticket completed successfully";
                $message_type = 'success';
                $logger->log('queue_action', 'queue_ticket', 'Completed ticket ID: ' . $ticket_id);
            } else {
                $message = "Failed to complete ticket";
                $message_type = 'error';
            }
            break;
            
        case 'cancel_ticket':
            $ticket_id = (int)$_POST['ticket_id'];
            
            if ($queueManager->updateTicketStatus($ticket_id, 'cancelled')) {
                $message = "Ticket cancelled successfully";
                $message_type = 'success';
                $logger->log('queue_action', 'queue_ticket', 'Cancelled ticket ID: ' . $ticket_id);
            } else {
                $message = "Failed to cancel ticket";
                $message_type = 'error';
            }
            break;
    }
}

// Get windows
$windows_stmt = $pdo->query("
    SELECT w.*, t.ticket_number, t.customer_name as current_customer
    FROM queue_windows w
    LEFT JOIN queue_tickets t ON w.current_ticket_id = t.id
    WHERE w.is_active = 1
    ORDER BY w.window_name
");
$windows = $windows_stmt->fetchAll();

// Get current serving tickets
$serving_stmt = $pdo->query("
    SELECT t.*, s.service_name, w.window_name
    FROM queue_tickets t
    JOIN queue_services s ON t.service_id = s.id
    LEFT JOIN queue_windows w ON t.serving_window = w.window_code
    WHERE t.status = 'serving' AND DATE(t.created_at) = CURDATE()
    ORDER BY t.called_at ASC
");
$serving_tickets = $serving_stmt->fetchAll();

// Get waiting tickets
$waiting_stmt = $pdo->query("
    SELECT t.*, s.service_name, s.service_code,
           ROW_NUMBER() OVER (PARTITION BY t.service_id ORDER BY 
               FIELD(t.priority_level, 'emergency', 'senior', 'pwd', 'pregnant', 'normal'),
               t.created_at ASC
           ) as queue_position
    FROM queue_tickets t
    JOIN queue_services s ON t.service_id = s.id
    WHERE t.status = 'waiting' AND DATE(t.created_at) = CURDATE()
    ORDER BY s.service_name, queue_position
    LIMIT 50
");
$waiting_tickets = $waiting_stmt->fetchAll();

// Get today's statistics
$stats_stmt = $pdo->query("
    SELECT 
        COUNT(CASE WHEN status = 'waiting' THEN 1 END) as waiting,
        COUNT(CASE WHEN status = 'serving' THEN 1 END) as serving,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
        COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled,
        COUNT(CASE WHEN status = 'no_show' THEN 1 END) as no_show,
        COUNT(*) as total
    FROM queue_tickets 
    WHERE DATE(created_at) = CURDATE()
");
$stats = $stats_stmt->fetch();

$logger->log('page_view', 'admin_panel', 'Viewed queue management page');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Management - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .queue-admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #1a4d80, #2563eb);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .stats-dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid;
        }
        
        .stat-card.waiting { border-left-color: #ffc107; }
        .stat-card.serving { border-left-color: #17a2b8; }
        .stat-card.completed { border-left-color: #28a745; }
        .stat-card.cancelled { border-left-color: #dc3545; }
        .stat-card.total { border-left-color: #6c757d; }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #1a4d80;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        
        .queue-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .queue-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .section-title {
            margin: 0 0 20px 0;
            color: #1a4d80;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .window-controls {
            display: grid;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .window-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #1a4d80;
        }
        
        .window-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .window-name {
            font-weight: bold;
            color: #1a4d80;
        }
        
        .current-ticket {
            margin-bottom: 15px;
        }
        
        .ticket-info {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        
        .window-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary { background: #1a4d80; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        
        .queue-list {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .queue-item {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .queue-item.priority-emergency { border-left-color: #dc3545; }
        .queue-item.priority-senior { border-left-color: #ffc107; }
        .queue-item.priority-pwd { border-left-color: #17a2b8; }
        .queue-item.priority-pregnant { border-left-color: #e83e8c; }
        .queue-item.priority-normal { border-left-color: #6c757d; }
        
        .ticket-details {
            flex: 1;
        }
        
        .ticket-number {
            font-weight: bold;
            color: #1a4d80;
            font-size: 16px;
        }
        
        .ticket-service {
            color: #666;
            font-size: 14px;
        }
        
        .ticket-customer {
            color: #333;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .ticket-actions {
            display: flex;
            gap: 10px;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .message.info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        
        .refresh-indicator {
            font-size: 12px;
            color: #666;
            margin-left: 10px;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="queue-admin-container">
        <?php include '../includes/admin_navigation.php'; ?>
        
        <div class="admin-header">
            <h1>Queue Management</h1>
            <p>Manage and monitor the current queue status</p>
            <div class="header-actions">
                <span class="refresh-indicator" id="refreshTime">—</span>
                <button id="manualRefresh" class="btn btn-secondary">Refresh</button>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistics Dashboard -->
        <div class="stats-dashboard" id="statsDashboard">
            <div class="stat-card waiting">
                <div class="stat-number"><?php echo $stats['waiting'] ?? 0; ?></div>
                <div class="stat-label">Waiting</div>
            </div>
            <div class="stat-card serving">
                <div class="stat-number"><?php echo $stats['serving'] ?? 0; ?></div>
                <div class="stat-label">Serving</div>
            </div>
            <div class="stat-card completed">
                <div class="stat-number"><?php echo $stats['completed'] ?? 0; ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-card cancelled">
                <div class="stat-number"><?php echo $stats['cancelled'] ?? 0; ?></div>
                <div class="stat-label">Cancelled</div>
            </div>
            <div class="stat-card total">
                <div class="stat-number"><?php echo $stats['total'] ?? 0; ?></div>
                <div class="stat-label">Total Today</div>
            </div>
        </div>
        
        <div class="queue-sections">
            <!-- Currently Serving -->
            <div class="queue-section">
                <h3 class="section-title">
                    <i class="fas fa-play-circle"></i>
                    Currently Serving
                </h3>
                <div class="queue-list" id="servingList">
                    <?php if (empty($serving_tickets)): ?>
                        <p>No tickets currently being served.</p>
                    <?php else: ?>
                        <?php foreach ($serving_tickets as $ticket): ?>
                            <div class="queue-item">
                                <div class="ticket-details">
                                    <div class="ticket-number"><?php echo htmlspecialchars($ticket['ticket_number']); ?></div>
                                    <div class="ticket-service"><?php echo htmlspecialchars($ticket['service_name']); ?></div>
                                    <div class="ticket-customer"><?php echo htmlspecialchars($ticket['customer_name']); ?></div>
                                </div>
                                <div class="ticket-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="complete_ticket">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                        <button type="submit" class="btn btn-success">Complete</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="cancel_ticket">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Cancel</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Waiting Queue -->
            <div class="queue-section">
                <h3 class="section-title">
                    <i class="fas fa-clock"></i>
                    Waiting Queue
                </h3>
                <div class="queue-list" id="waitingList">
                    <?php if (empty($waiting_tickets)): ?>
                        <p>No tickets waiting in queue.</p>
                    <?php else: ?>
                        <?php foreach ($waiting_tickets as $ticket): ?>
                            <div class="queue-item priority-<?php echo $ticket['priority_level'] ?? 'normal'; ?>">
                                <div class="ticket-details">
                                    <div class="ticket-number"><?php echo htmlspecialchars($ticket['ticket_number']); ?></div>
                                    <div class="ticket-service"><?php echo htmlspecialchars($ticket['service_name']); ?></div>
                                    <div class="ticket-customer"><?php echo htmlspecialchars($ticket['customer_name']); ?></div>
                                    <div class="ticket-position">Position: <?php echo $ticket['queue_position'] ?? 'N/A'; ?></div>
                                </div>
                                <div class="ticket-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="cancel_ticket">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Cancel</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Window Controls -->
        <div class="window-controls">
            <h3>Window Controls</h3>
            <?php foreach ($windows as $window): ?>
                <div class="window-card">
                    <div class="window-header">
                        <div class="window-name"><?php echo htmlspecialchars($window['window_name']); ?></div>
                        <div class="window-status"><?php echo $window['is_active'] ? 'Active' : 'Inactive'; ?></div>
                    </div>
                    
                    <?php if ($window['current_customer']): ?>
                        <div class="current-ticket">
                            <div class="ticket-info">
                                <strong>Current Ticket:</strong> <?php echo htmlspecialchars($window['current_customer']); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="current-ticket">
                            <div class="ticket-info">
                                <em>No ticket currently assigned</em>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="window-actions">
                        <button class="btn btn-primary" disabled>Call Next</button>
                        <button class="btn btn-secondary" disabled>Pause</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
    const apiUrl = '../api/queue-state.php';
    const statsDashboard = document.getElementById('statsDashboard');
    const servingList = document.getElementById('servingList');
    const waitingList = document.getElementById('waitingList');
    const refreshTime = document.getElementById('refreshTime');

    function updateQueueData() {
        fetch(apiUrl, { credentials: 'same-origin' })
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Failed');
                
                refreshTime.textContent = 'Updated ' + new Date().toLocaleTimeString();

                // Update stats
                const s = data.stats || {};
                statsDashboard.innerHTML = `
                    <div class="stat-card waiting">
                        <div class="stat-number">${s.waiting || 0}</div>
                        <div class="stat-label">Waiting</div>
                    </div>
                    <div class="stat-card serving">
                        <div class="stat-number">${s.serving || 0}</div>
                        <div class="stat-label">Serving</div>
                    </div>
                    <div class="stat-card completed">
                        <div class="stat-number">${s.completed || 0}</div>
                        <div class="stat-label">Completed</div>
                    </div>
                    <div class="stat-card cancelled">
                        <div class="stat-number">${s.cancelled || 0}</div>
                        <div class="stat-label">Cancelled</div>
                    </div>
                    <div class="stat-card total">
                        <div class="stat-number">${s.total || 0}</div>
                        <div class="stat-label">Total Today</div>
                    </div>
                `;

                // Update serving list
                if (data.serving && data.serving.length > 0) {
                    servingList.innerHTML = data.serving.map(t => `
                        <div class="queue-item">
                            <div class="ticket-details">
                                <div class="ticket-number">${escapeHtml(t.ticket_number)}</div>
                                <div class="ticket-service">${escapeHtml(t.service_name || '')}</div>
                                <div class="ticket-customer">${escapeHtml(t.customer_name || '')}</div>
                            </div>
                            <div class="ticket-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="complete_ticket">
                                    <input type="hidden" name="ticket_id" value="${t.id}">
                                    <button type="submit" class="btn btn-success">Complete</button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="cancel_ticket">
                                    <input type="hidden" name="ticket_id" value="${t.id}">
                                    <button type="submit" class="btn btn-danger">Cancel</button>
                                </form>
                            </div>
                        </div>
                    `).join('');
                } else {
                    servingList.innerHTML = '<p>No tickets currently being served.</p>';
                }

                // Update waiting list
                if (data.waiting && data.waiting.length > 0) {
                    waitingList.innerHTML = data.waiting.map(t => `
                        <div class="queue-item priority-${t.priority_level || 'normal'}">
                            <div class="ticket-details">
                                <div class="ticket-number">${escapeHtml(t.ticket_number)}</div>
                                <div class="ticket-service">${escapeHtml(t.service_name || '')}</div>
                                <div class="ticket-customer">${escapeHtml(t.customer_name || '')}</div>
                                <div class="ticket-position">Position: ${t.queue_position || 'N/A'}</div>
                            </div>
                            <div class="ticket-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="cancel_ticket">
                                    <input type="hidden" name="ticket_id" value="${t.id}">
                                    <button type="submit" class="btn btn-danger">Cancel</button>
                                </form>
                            </div>
                        </div>
                    `).join('');
                } else {
                    waitingList.innerHTML = '<p>No tickets waiting in queue.</p>';
                }
            })
            .catch(err => {
                refreshTime.textContent = 'Error: ' + err.message;
            });
    }

    function escapeHtml(s) {
        if (s == null) return '';
        return String(s).replace(/[&<>"]+/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
    }

    // Auto-refresh every 10 seconds
    document.getElementById('manualRefresh').addEventListener('click', updateQueueData);
    updateQueueData();
    setInterval(updateQueueData, 10000);
    </script>
</body>
</html>