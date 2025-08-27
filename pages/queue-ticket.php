<?php
session_start();
$base_path = '../';
$page_title = 'Get Queue Ticket - Barangay Gumaoc East';
$header_title = 'Queue Management System';
$header_subtitle = 'Get your queue number for faster service';

include '../includes/header.php';
include '../includes/db_connect.php';
include '../includes/QueueManager.php';

$queueManager = new QueueManager($pdo);
$success_message = '';
$error_message = '';
$ticket_data = null;

// Handle form submission
if ($_POST && isset($_POST['generate_ticket'])) {
    $service_id = (int)$_POST['service_id'];
    $full_name = trim($_POST['full_name']);
    $contact_number = trim($_POST['contact_number']);
    $purpose = trim($_POST['purpose']);
    $priority_level = $_POST['priority_level'] ?? 'normal';
    $user_id = $_SESSION['user_id'] ?? null;
    
    if (empty($full_name) || empty($service_id)) {
        $error_message = "Please fill in all required fields.";
    } else {
        $result = $queueManager->generateTicket($service_id, $full_name, $contact_number, $user_id, $purpose, $priority_level);
        
        if ($result['success']) {
            $ticket_data = $result;
            $success_message = "Queue ticket generated successfully!";
        } else {
            $error_message = $result['error'];
        }
    }
}

// Get available services
$services_stmt = $pdo->query("SELECT * FROM queue_services WHERE is_active = 1 ORDER BY service_name");
$services = $services_stmt->fetchAll();

// Get queue status
$queue_status = $queueManager->getQueueStatus();
?>

<div class="container">
    <section class="queue-section">
        <?php if ($ticket_data): ?>
        <!-- Ticket Generated Success -->
        <div class="ticket-generated">
            <div class="ticket-card">
                <div class="ticket-header">
                    <h2>🎫 Your Queue Ticket</h2>
                    <div class="ticket-number"><?php echo htmlspecialchars($ticket_data['ticket_number']); ?></div>
                </div>
                <div class="ticket-body">
                    <div class="ticket-info">
                        <p><strong>Queue Position:</strong> #<?php echo $ticket_data['queue_position']; ?></p>
                        <p><strong>Estimated Time:</strong> <?php echo $ticket_data['estimated_time']; ?></p>
                        <p><strong>Date:</strong> <?php echo date('F j, Y'); ?></p>
                    </div>
                    <div class="ticket-instructions">
                        <h4>📋 Instructions:</h4>
                        <ul>
                            <li>Keep this ticket number safe</li>
                            <li>Monitor the display board for your number</li>
                            <li>Present this ticket when called</li>
                            <li>Arrive 5 minutes before your estimated time</li>
                        </ul>
                    </div>
                </div>
                <div class="ticket-actions">
                    <button onclick="window.print()" class="btn btn-primary">🖨️ Print Ticket</button>
                    <a href="queue-status.php" class="btn btn-secondary">📊 View Queue Status</a>
                    <a href="queue-ticket.php" class="btn btn-secondary">➕ New Ticket</a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Queue Form -->
        <div class="queue-form-section">
            <div class="queue-header">
                <h2>🎫 Get Your Queue Number</h2>
                <p>Select a service and get your queue ticket for faster processing</p>
            </div>

            <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <!-- Current Queue Status -->
            <div class="queue-status-display">
                <h3>📊 Current Queue Status</h3>
                <div class="status-grid">
                    <?php foreach ($queue_status as $status): ?>
                    <div class="status-card">
                        <div class="service-name"><?php echo htmlspecialchars($status['service_name']); ?></div>
                        <div class="status-stats">
                            <span class="waiting">⏳ <?php echo $status['waiting_count']; ?> waiting</span>
                            <span class="serving">🔄 <?php echo $status['serving_count']; ?> serving</span>
                        </div>
                        <div class="avg-time">Avg: <?php echo $status['avg_wait_time'] ?? 15; ?> min</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Queue Form -->
            <form method="POST" class="queue-form">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="service_id">🏛️ Select Service <span class="required">*</span></label>
                        <select id="service_id" name="service_id" required>
                            <option value="">Choose a service...</option>
                            <?php foreach ($services as $service): ?>
                            <option value="<?php echo $service['id']; ?>" 
                                    data-time="<?php echo $service['estimated_time']; ?>"
                                    <?php echo (($_POST['service_id'] ?? '') == $service['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($service['service_name']); ?> 
                                (<?php echo $service['service_code']; ?>) - ~<?php echo $service['estimated_time']; ?> min
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="input-help">Select the service you need assistance with</div>
                    </div>

                    <div class="form-group">
                        <label for="full_name">👤 Full Name <span class="required">*</span></label>
                        <input type="text" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="contact_number">📱 Contact Number</label>
                        <input type="tel" id="contact_number" name="contact_number" 
                               value="<?php echo htmlspecialchars($_POST['contact_number'] ?? ''); ?>"
                               placeholder="09XXXXXXXXX">
                    </div>

                    <div class="form-group full-width">
                        <label for="purpose">📝 Purpose/Details</label>
                        <textarea id="purpose" name="purpose" rows="3" 
                                  placeholder="Brief description of your request..."><?php echo htmlspecialchars($_POST['purpose'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="priority_level">⚡ Priority Level</label>
                        <select id="priority_level" name="priority_level">
                            <option value="normal" <?php echo (($_POST['priority_level'] ?? '') === 'normal') ? 'selected' : ''; ?>>Normal</option>
                            <option value="senior" <?php echo (($_POST['priority_level'] ?? '') === 'senior') ? 'selected' : ''; ?>>Senior Citizen</option>
                            <option value="pwd" <?php echo (($_POST['priority_level'] ?? '') === 'pwd') ? 'selected' : ''; ?>>Person with Disability</option>
                            <option value="pregnant" <?php echo (($_POST['priority_level'] ?? '') === 'pregnant') ? 'selected' : ''; ?>>Pregnant</option>
                            <option value="emergency" <?php echo (($_POST['priority_level'] ?? '') === 'emergency') ? 'selected' : ''; ?>>Emergency</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="generate_ticket" class="btn btn-primary">
                        🎫 Generate Queue Ticket
                    </button>
                    <a href="queue-status.php" class="btn btn-secondary">
                        📊 View Queue Status
                    </a>
                    <a href="queue-kiosk.php" class="btn btn-success">
                        📺 Kiosk Display
                    </a>
                </div>
                
                <div class="service-links">
                    <h4>📄 Need to submit a form? Get your ticket here:</h4>
                    <div class="service-link-buttons">
                        <a href="certificate-request.php" class="service-link-btn">
                            📄 Certificate Request
                        </a>
                        <a href="resident-registration.php" class="service-link-btn">
                            👥 Resident Registration
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </section>
</div>

<style>
/* Queue System Styles */
.queue-section {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.queue-header {
    text-align: center;
    margin-bottom: 30px;
    padding: 20px;
    background: linear-gradient(135deg, #1a4d80, #2563eb);
    color: white;
    border-radius: 12px;
}

.queue-header h2 {
    margin: 0 0 10px 0;
    font-size: 28px;
}

/* Queue Status Display */
.queue-status-display {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
}

.queue-status-display h3 {
    margin: 0 0 20px 0;
    color: #1a4d80;
}

.status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.status-card {
    background: white;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.service-name {
    font-weight: bold;
    color: #1a4d80;
    margin-bottom: 10px;
}

.status-stats {
    display: flex;
    gap: 15px;
    margin-bottom: 5px;
}

.waiting, .serving {
    font-size: 14px;
    padding: 3px 8px;
    border-radius: 15px;
}

.waiting {
    background: #fff3cd;
    color: #856404;
}

.serving {
    background: #cce5ff;
    color: #0066cc;
}

.avg-time {
    font-size: 12px;
    color: #666;
}

/* Ticket Generated Styles */
.ticket-generated {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
}

.ticket-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    overflow: hidden;
    max-width: 500px;
    width: 100%;
}

.ticket-header {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 30px;
    text-align: center;
}

.ticket-header h2 {
    margin: 0 0 15px 0;
    font-size: 24px;
}

.ticket-number {
    font-size: 36px;
    font-weight: bold;
    font-family: 'Courier New', monospace;
    background: rgba(255,255,255,0.2);
    padding: 15px;
    border-radius: 8px;
    letter-spacing: 2px;
}

.ticket-body {
    padding: 30px;
}

.ticket-info {
    margin-bottom: 25px;
}

.ticket-info p {
    margin: 8px 0;
    font-size: 16px;
}

.ticket-instructions h4 {
    color: #1a4d80;
    margin-bottom: 15px;
}

.ticket-instructions ul {
    margin: 0;
    padding-left: 20px;
}

.ticket-instructions li {
    margin: 8px 0;
    color: #555;
}

.ticket-actions {
    padding: 20px 30px;
    background: #f8f9fa;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* Form Styles */
.queue-form {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #1a4d80;
    font-weight: 600;
    font-size: 14px;
}

.required {
    color: #dc3545;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #1a4d80;
    box-shadow: 0 0 0 3px rgba(26, 77, 128, 0.1);
}

.input-help {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

/* Buttons */
.form-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #1a4d80, #2563eb);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(26, 77, 128, 0.3);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
    transform: translateY(-2px);
}

.btn-success {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border: none;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.btn-success:hover {
    background: linear-gradient(135deg, #20c997, #17a2b8);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

/* Service Links Section */
.service-links {
    margin-top: 40px;
    padding: 30px;
    background: linear-gradient(135deg, #e8f5e9, #f1f8e9);
    border-radius: 15px;
    border: 2px solid #4caf50;
    text-align: center;
}

.service-links h4 {
    color: #2e7d32;
    margin: 0 0 20px 0;
    font-size: 1.3rem;
    font-weight: 600;
}

.service-link-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

.service-link-btn {
    background: linear-gradient(135deg, #4caf50, #66bb6a);
    color: white;
    padding: 15px 25px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
    min-width: 200px;
    text-align: center;
}

.service-link-btn:hover {
    background: linear-gradient(135deg, #66bb6a, #81c784);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
    text-decoration: none;
    color: white;
}

/* Alerts */
.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 500;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Print Styles */
@media print {
    body * {
        visibility: hidden;
    }
    
    .ticket-card,
    .ticket-card * {
        visibility: visible;
    }
    
    .ticket-card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        box-shadow: none;
    }
    
    .ticket-actions {
        display: none;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .status-grid {
        grid-template-columns: 1fr;
    }
    
    .ticket-actions {
        flex-direction: column;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh queue status every 30 seconds
    setInterval(function() {
        if (!document.querySelector('.ticket-generated')) {
            location.reload();
        }
    }, 30000);
    
    // Service selection handler
    const serviceSelect = document.getElementById('service_id');
    if (serviceSelect) {
        serviceSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const estimatedTime = selectedOption.dataset.time;
            
            if (estimatedTime) {
                console.log('Estimated service time:', estimatedTime, 'minutes');
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>