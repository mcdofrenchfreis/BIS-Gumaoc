<?php
session_start();
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

// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'get_queue_data') {
    header('Content-Type: application/json');
    
    try {
        $stats_query = $pdo->query("
            SELECT 
                COUNT(CASE WHEN status = 'waiting' THEN 1 END) AS waiting,
                COUNT(CASE WHEN status = 'serving' THEN 1 END) AS serving,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) AS completed,
                COUNT(CASE WHEN status IN ('cancelled', 'no_show') THEN 1 END) AS cancelled,
                COUNT(*) AS total_tickets
            FROM queue_tickets 
            WHERE DATE(created_at) = CURDATE()
        ");
        $stats = $stats_query->fetch(PDO::FETCH_ASSOC);
        
        $waiting_query = $pdo->query("
            SELECT t.id, t.ticket_number, t.customer_name, s.service_name, 
                   t.priority_level, t.status, t.created_at,
                   ROW_NUMBER() OVER (ORDER BY t.created_at ASC) as queue_position
            FROM queue_tickets t
            JOIN queue_services s ON t.service_id = s.id
            WHERE t.status IN ('waiting', 'serving')
                AND DATE(t.created_at) = CURDATE()
            ORDER BY t.created_at ASC
            LIMIT 50
        ");
        $tickets = $waiting_query->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'tickets' => $tickets,
            'timestamp' => date('c')
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Unable to load queue information. Please refresh the page and try again.'
        ]);
    }
    exit;
}

// Handle AJAX POST actions
if ($_POST && isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'call_next':
                $counter_id = (int)$_POST['counter_id'];
                $result = $queueManager->callNextTicket($counter_id);
                
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Called ticket: ' . $result['ticket']['ticket_number'],
                        'ticket' => $result['ticket']
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => $result['message']
                    ]);
                }
                break;
                
            case 'complete_ticket':
                $counter_id = (int)$_POST['counter_id'];
                $result = $queueManager->completeTicket($counter_id, '');
                
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Ticket completed successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => $result['message']
                    ]);
                }
                break;
                
            case 'reset_counter':
                $counter_id = (int)$_POST['counter_id'];
                
                $stmt = $pdo->prepare("UPDATE queue_counters SET current_ticket_id = NULL WHERE id = ?");
                if ($stmt->execute([$counter_id])) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Counter reset successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to reset counter'
                    ]);
                }
                break;
                
            case 'generate_test_ticket':
                $result = $queueManager->generateTicket(5, 'Test Customer ' . date('His'), '09123456789', null, 'Test General Service', 'normal');
                
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Test ticket generated: ' . $result['ticket_number'],
                        'ticket_number' => $result['ticket_number']
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to generate test ticket'
                    ]);
                }
                break;
                
            case 'remove_ticket':
                $ticket_id = (int)$_POST['ticket_id'];
                
                try {
                    $stmt = $pdo->prepare("UPDATE queue_tickets SET status = 'cancelled', notes = CONCAT(COALESCE(notes, ''), ' - Removed by admin') WHERE id = ?");
                    if ($stmt->execute([$ticket_id])) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Ticket removed successfully'
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Failed to remove ticket'
                        ]);
                    }
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Unable to remove ticket. Please try again or contact support if the problem persists.'
                    ]);
                }
                break;
                
            case 'archive_ticket':
                $ticket_id = (int)$_POST['ticket_id'];
                
                try {
                    $stmt = $pdo->prepare("UPDATE queue_tickets SET status = 'no_show', notes = CONCAT(COALESCE(notes, ''), ' - Archived by admin') WHERE id = ?");
                    if ($stmt->execute([$ticket_id])) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Ticket archived successfully'
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Failed to archive ticket'
                        ]);
                    }
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Unable to archive ticket. Please try again or contact support if the problem persists.'
                    ]);
                }
                break;
                
            case 'generate_dummy_tickets':
                $count = (int)($_POST['count'] ?? 20);
                $count = max(1, min(50, $count)); // Limit between 1 and 50
                
                try {
                    $success_count = 0;
                    $failed_count = 0;
                    
                    // Sample data for realistic dummy tickets
                    $firstNames = ['Maria', 'Juan', 'Jose', 'Ana', 'Pedro', 'Carmen', 'Miguel', 'Rosa', 'Carlos', 'Elena', 'Roberto', 'Isabel', 'Francisco', 'Patricia', 'Manuel', 'Luz', 'Antonio', 'Teresa', 'Daniel', 'Esperanza'];
                    $lastNames = ['Santos', 'Reyes', 'Cruz', 'Bautista', 'Ocampo', 'Garcia', 'Mendoza', 'Torres', 'Gonzales', 'Rodriguez', 'Perez', 'Flores', 'Rivera', 'Gomez', 'Fernandez', 'Lopez', 'Hernandez', 'Diaz', 'Morales', 'Jimenez'];
                    $purposes = ['Certificate Request', 'Document Verification', 'General Inquiry', 'Permit Application', 'Registration Update', 'Complaint Filing', 'Information Request', 'Form Submission'];
                    $priorityLevels = ['normal', 'normal', 'normal', 'normal', 'priority', 'senior', 'pwd'];
                    $serviceIds = [1, 2, 3, 4, 5, 5, 5, 6]; // More general services
                    
                    for ($i = 0; $i < $count; $i++) {
                        $firstName = $firstNames[array_rand($firstNames)];
                        $lastName = $lastNames[array_rand($lastNames)];
                        $fullName = $firstName . ' ' . $lastName;
                        $serviceId = $serviceIds[array_rand($serviceIds)];
                        $purpose = $purposes[array_rand($purposes)];
                        $priority = $priorityLevels[array_rand($priorityLevels)];
                        $mobile = '09' . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
                        
                        $result = $queueManager->generateTicket(
                            $serviceId,
                            $fullName,
                            $mobile,
                            null,
                            $purpose,
                            $priority
                        );
                        
                        if ($result['success']) {
                            $success_count++;
                        } else {
                            $failed_count++;
                        }
                        
                        // Small delay to avoid overwhelming the system
                        usleep(10000); // 10ms delay
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'message' => "Generated {$success_count} dummy tickets successfully" . ($failed_count > 0 ? ", {$failed_count} failed" : ''),
                        'generated' => $success_count,
                        'failed' => $failed_count
                    ]);
                    
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Unable to generate test tickets. Please try again or contact support if the problem persists.'
                    ]);
                }
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'message' => 'Unknown action requested. Please refresh the page and try again.'
                ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'A system error occurred. Please try again or contact support if the problem persists.'
        ]);
    }
    exit;
}

// Handle regular POST actions (fallback)
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'call_next':
            $counter_id = (int)$_POST['counter_id'];
            $result = $queueManager->callNextTicket($counter_id);
            
            if ($result['success']) {
                $success_message = "Called ticket: " . $result['ticket']['ticket_number'];
            } else {
                $error_message = $result['message'];
            }
            break;
            
        case 'complete_ticket':
            $counter_id = (int)$_POST['counter_id'];
            $result = $queueManager->completeTicket($counter_id, '');
            
            if ($result['success']) {
                $success_message = "Ticket completed successfully";
            } else {
                $error_message = $result['message'];
            }
            break;
            
        case 'reset_counter':
            $counter_id = (int)$_POST['counter_id'];
            
            try {
                $stmt = $pdo->prepare("UPDATE queue_counters SET current_ticket_id = NULL WHERE id = ?");
                if ($stmt->execute([$counter_id])) {
                    $success_message = "Counter reset successfully";
                } else {
                    $error_message = "Failed to reset counter";
                }
            } catch (Exception $e) {
                $error_message = "Unable to reset counter. Please try again or contact support if the problem persists.";
            }
            break;
            
        case 'generate_test_ticket':
            $result = $queueManager->generateTicket(5, 'Test Customer ' . date('His'), '09123456789', null, 'Test General Service', 'normal');
            
            if ($result['success']) {
                $success_message = "Test ticket generated: " . $result['ticket_number'];
            } else {
                $error_message = "Failed to generate test ticket";
            }
            break;
            
        case 'remove_ticket':
            $ticket_id = (int)$_POST['ticket_id'];
            
            try {
                $stmt = $pdo->prepare("UPDATE queue_tickets SET status = 'cancelled', notes = CONCAT(COALESCE(notes, ''), ' - Removed by admin') WHERE id = ?");
                if ($stmt->execute([$ticket_id])) {
                    $success_message = "Ticket removed successfully";
                } else {
                    $error_message = "Failed to remove ticket";
                }
            } catch (Exception $e) {
                $error_message = "Unable to remove ticket. Please try again or contact support if the problem persists.";
            }
            break;
            
        case 'archive_ticket':
            $ticket_id = (int)$_POST['ticket_id'];
            
            try {
                $stmt = $pdo->prepare("UPDATE queue_tickets SET status = 'no_show', notes = CONCAT(COALESCE(notes, ''), ' - Archived by admin') WHERE id = ?");
                if ($stmt->execute([$ticket_id])) {
                    $success_message = "Ticket archived successfully";
                } else {
                    $error_message = "Failed to archive ticket";
                }
            } catch (Exception $e) {
                $error_message = "Unable to archive ticket. Please try again or contact support if the problem persists.";
            }
            break;
            
        case 'generate_dummy_tickets':
            $count = (int)($_POST['count'] ?? 20);
            $count = max(1, min(50, $count));
            
            try {
                $success_count = 0;
                $failed_count = 0;
                
                $firstNames = ['Maria', 'Juan', 'Jose', 'Ana', 'Pedro', 'Carmen', 'Miguel', 'Rosa', 'Carlos', 'Elena'];
                $lastNames = ['Santos', 'Reyes', 'Cruz', 'Bautista', 'Ocampo', 'Garcia', 'Mendoza', 'Torres', 'Gonzales', 'Rodriguez'];
                $purposes = ['Certificate Request', 'Document Verification', 'General Inquiry', 'Permit Application'];
                $priorityLevels = ['normal', 'normal', 'normal', 'priority', 'senior'];
                $serviceIds = [1, 2, 3, 4, 5, 5, 6];
                
                for ($i = 0; $i < $count; $i++) {
                    $firstName = $firstNames[array_rand($firstNames)];
                    $lastName = $lastNames[array_rand($lastNames)];
                    $fullName = $firstName . ' ' . $lastName;
                    $serviceId = $serviceIds[array_rand($serviceIds)];
                    $purpose = $purposes[array_rand($purposes)];
                    $priority = $priorityLevels[array_rand($priorityLevels)];
                    $mobile = '09' . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
                    
                    $result = $queueManager->generateTicket($serviceId, $fullName, $mobile, null, $purpose, $priority);
                    
                    if ($result['success']) {
                        $success_count++;
                    } else {
                        $failed_count++;
                    }
                }
                
                $success_message = "Generated {$success_count} dummy tickets successfully" . ($failed_count > 0 ? ", {$failed_count} failed" : '');
                
            } catch (Exception $e) {
                $error_message = "Unable to generate test tickets. Please try again or contact support if the problem persists.";
            }
            break;
    }
    
    header("Location: queue-admin.php" . ($success_message ? "?success=" . urlencode($success_message) : "") . ($error_message ? "?error=" . urlencode($error_message) : ""));
    exit;
}

// Handle URL parameters
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}
if (isset($_GET['error'])) {
    $error_message = $_GET['error'];
}

// Get counters
$counters = $pdo->query("
    SELECT qc.*, qs.service_name, qt.ticket_number, qt.customer_name, qt.id as current_ticket_id
    FROM queue_counters qc 
    LEFT JOIN queue_services qs ON qc.service_id = qs.id 
    LEFT JOIN queue_tickets qt ON qc.current_ticket_id = qt.id 
    WHERE qc.is_active = 1 
    ORDER BY qc.counter_number
")->fetchAll();

$page_title = 'Queue Management Dashboard';
include '../includes/admin_header.php';
?>

<style>
:root {
    --primary: #2563eb;
    --success: #22c55e;
    --warning: #f59e0b;
    --error: #ef4444;
    --info: #06b6d4;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-500: #6b7280;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --white: #ffffff;
    --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --radius: 8px;
    --transition: all 0.15s ease;
}

.admin-main-content {
    padding: 8px 24px 24px 24px;
    background: var(--gray-50);
    min-height: calc(100vh - 80px);
    padding-bottom: 24px;
}

.dashboard-header {
    background: linear-gradient(135deg, #1b5e20, #2e7d32, #4caf50);
    border-radius: var(--radius);
    padding: 20px 28px;
    margin-bottom: 20px;
    color: white;
    text-align: center;
    box-shadow: var(--shadow-md);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.dashboard-header h1 {
    font-size: 2.5rem;
    font-weight: 800;
    margin: 0 0 12px 0;
}

.dashboard-header p {
    font-size: 1.125rem;
    margin: 0 0 24px 0;
    opacity: 0.9;
}

.header-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.header-action-btn {
    background: rgba(255,255,255,0.15);
    border: 2px solid rgba(255,255,255,0.2);
    color: white;
    padding: 12px 20px;
    border-radius: var(--radius);
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.header-action-btn:hover {
    background: rgba(255,255,255,0.25);
    color: white;
    text-decoration: none;
}

.alert {
    padding: 16px 20px;
    border-radius: var(--radius);
    margin-bottom: 24px;
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
}

.alert-success {
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    color: #065f46;
    border-left: 4px solid var(--success);
}

.alert-error {
    background: linear-gradient(135deg, #fef2f2, #fecaca);
    color: #991b1b;
    border-left: 4px solid var(--error);
}

.main-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 32px;
    margin-bottom: 32px;
}

.counter-section {
    background: var(--white);
    border-radius: var(--radius);
    padding: 24px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--gray-200);
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 16px;
    border-bottom: 2px solid var(--gray-100);
}

.counters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.counter-card {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    border-radius: var(--radius);
    padding: 20px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    min-height: 280px;
    display: flex;
    flex-direction: column;
}

.counter-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}

.counter-card.available {
    background: rgba(240, 253, 244, 0.8);
    backdrop-filter: blur(10px);
    border-color: rgba(34, 197, 94, 0.4);
}

.counter-card.available::before {
    background: var(--success);
}

.counter-card.busy {
    background: rgba(254, 251, 235, 0.8);
    backdrop-filter: blur(10px);
    border-color: rgba(245, 158, 11, 0.4);
}

.counter-card.busy::before {
    background: var(--warning);
}

.counter-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.counter-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.counter-info h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--gray-800);
    margin: 0 0 4px 0;
    line-height: 1.2;
    word-wrap: break-word;
    hyphens: auto;
}

.counter-info p {
    color: var(--gray-500);
    margin: 0;
    font-size: 0.875rem;
}

.counter-status {
    padding: 6px 12px;
    border-radius: var(--radius);
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.counter-status.available {
    background: var(--success);
    color: white;
}

.counter-status.busy {
    background: var(--warning);
    color: white;
}

.service-tag {
    background: var(--gray-100);
    padding: 8px 12px;
    border-radius: var(--radius);
    margin: 12px 0;
    font-size: 0.875rem;
    color: var(--gray-700);
}

.current-ticket {
    background: rgba(249, 250, 251, 0.8);
    backdrop-filter: blur(5px);
    border-radius: var(--radius);
    padding: 16px;
    margin: 12px 0;
    border-left: 4px solid var(--primary);
    flex: 1;
}

.ticket-number {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--primary);
    margin-bottom: 4px;
}

.customer-name {
    color: var(--gray-700);
    font-weight: 500;
}

.counter-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: auto;
    padding-top: 16px;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 16px;
    font-size: 0.875rem;
    font-weight: 600;
    border: none;
    border-radius: var(--radius);
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    flex: 1;
    min-width: 100px;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: #1d4ed8;
}

.btn-success {
    background: var(--success);
    color: white;
}

.btn-warning {
    background: var(--warning);
    color: white;
}

.btn-info {
    background: var(--info);
    color: white;
}

.btn-secondary {
    background: linear-gradient(135deg, var(--gray-500), var(--gray-600));
    color: white;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, var(--gray-600), var(--gray-700));
    transform: translateY(-1px);
}

.queue-sidebar {
    background: var(--white);
    border-radius: var(--radius);
    padding: 20px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--gray-200);
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

.search-box input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius);
    font-size: 0.875rem;
    margin-bottom: 20px;
}

.action-buttons {
    display: grid;
    gap: 8px;
    margin-bottom: 20px;
}

.action-buttons .btn {
    min-width: auto;
    flex: none;
    padding: 8px 12px;
    font-size: 0.8125rem;
}

.ticket-item {
    background: var(--gray-50);
    padding: 12px;
    margin-bottom: 8px;
    border-radius: var(--radius);
    border-left: 4px solid var(--gray-200);
    transition: var(--transition);
}

.ticket-item.urgent {
    border-left-color: var(--error);
}

.ticket-item.priority {
    border-left-color: var(--primary);
}

.ticket-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.ticket-number-small {
    font-weight: 700;
    color: var(--primary);
}

.priority-badge {
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
}

.priority-badge.urgent {
    background: var(--error);
    color: white;
}

.priority-badge.priority {
    background: var(--primary);
    color: white;
}

.priority-badge.normal {
    background: var(--gray-200);
    color: var(--gray-700);
}

.customer-name-small {
    font-size: 0.8125rem;
    color: var(--gray-500);
    margin-bottom: 4px;
}

.ticket-controls {
    display: flex;
    gap: 6px;
    margin-top: 8px;
}

.btn-ticket-action {
    flex: 1;
    padding: 4px 8px;
    font-size: 0.7rem;
    font-weight: 600;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.btn-remove {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
}

.btn-remove:hover {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    transform: translateY(-1px);
}

.btn-archive {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
}

.btn-archive:hover {
    background: linear-gradient(135deg, #d97706, #b45309);
    transform: translateY(-1px);
}

.btn-ticket-action:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.bottom-stats {
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    padding: 20px 24px;
    margin: 32px 0 0 0;
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
}

.stat-card {
    text-align: center;
    padding: 12px 8px;
}

.stat-number {
    font-size: 1.75rem;
    font-weight: 800;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 0.75rem;
    color: var(--gray-500);
    font-weight: 600;
    text-transform: uppercase;
}

.stat-card.waiting .stat-number { color: var(--warning); }
.stat-card.serving .stat-number { color: var(--info); }
.stat-card.completed .stat-number { color: var(--success); }
.stat-card.cancelled .stat-number { color: var(--error); }
.stat-card.total .stat-number { color: var(--primary); }

.toast-container {
    position: fixed;
    top: 80px;
    right: 20px;
    z-index: 10001;
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 400px;
}

.toast {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: var(--radius);
    padding: 0;
    box-shadow: var(--shadow-md);
    border-left: 4px solid var(--primary);
    animation: slideIn 0.3s ease;
    position: relative;
    overflow: hidden;
    min-width: 320px;
    max-width: 400px;
}

.toast.success { border-left-color: var(--success); }
.toast.error { border-left-color: var(--error); }
.toast.warning { border-left-color: var(--warning); }
.toast.info { border-left-color: var(--info); }

.toast-content {
    display: flex;
    align-items: flex-start;
    padding: 16px 20px;
    gap: 12px;
}

.toast-icon {
    font-size: 1.2rem;
    flex-shrink: 0;
    margin-top: 2px;
}

.toast-message {
    flex: 1;
    line-height: 1.4;
}

.toast-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--gray-500);
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: var(--transition);
    flex-shrink: 0;
}

.toast-close:hover {
    background: var(--gray-100);
    color: var(--gray-700);
}

.toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: var(--primary);
    width: 100%;
    transform-origin: left;
}

.toast.success .toast-progress { background: var(--success); }
.toast.error .toast-progress { background: var(--error); }
.toast.warning .toast-progress { background: var(--warning); }
.toast.info .toast-progress { background: var(--info); }

@keyframes progress {
    from { transform: scaleX(1); }
    to { transform: scaleX(0); }
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10002;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.modal-overlay.modal-show {
    opacity: 1;
}

.modal-content {
    background: white;
    border-radius: var(--radius);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow: auto;
    transform: scale(0.8) translateY(-20px);
    transition: transform 0.3s ease;
}

.modal-show .modal-content {
    transform: scale(1) translateY(0);
}

.modal-confirm {
    max-width: 420px;
}

.modal-header {
    display: flex;
    align-items: center;
    padding: 24px 24px 16px 24px;
    gap: 12px;
    border-bottom: 1px solid var(--gray-200);
}

.modal-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.modal-header h3 {
    flex: 1;
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-800);
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--gray-500);
    padding: 4px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: var(--transition);
}

.modal-close:hover {
    background: var(--gray-100);
    color: var(--gray-700);
}

.modal-body {
    padding: 16px 24px;
}

.modal-body p {
    margin: 0;
    line-height: 1.5;
    color: var(--gray-700);
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 16px 24px 24px 24px;
    border-top: 1px solid var(--gray-200);
}

.modal-btn {
    padding: 10px 20px;
    border: none;
    border-radius: var(--radius);
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.875rem;
}

.modal-btn-primary {
    background: var(--primary);
    color: white;
}

.modal-btn-primary:hover {
    background: #1d4ed8;
}

.modal-btn-secondary {
    background: var(--gray-200);
    color: var(--gray-700);
}

.modal-btn-secondary:hover {
    background: var(--gray-300);
}

.modal-btn-danger {
    background: var(--error);
    color: white;
}

.modal-btn-danger:hover {
    background: #dc2626;
}

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@media (max-width: 1024px) {
    .main-content {
        grid-template-columns: 1fr;
        gap: 24px;
    }
    
    .counters-grid {
        grid-template-columns: 1fr;
    }
    
    .bottom-stats {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .admin-main-content {
        padding: 16px;
    }
    
    .dashboard-header h1 {
        font-size: 2rem;
    }
    
    .header-actions {
        grid-template-columns: 1fr;
    }
    
    .counter-actions {
        flex-direction: column;
    }
    
    .counter-card {
        min-height: 250px;
    }
    
    .counter-info h3 {
        font-size: 1.1rem;
    }
    
    .bottom-stats {
        grid-template-columns: repeat(2, 1fr);
        padding: 16px;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
}
</style>

<div class="admin-main-content">
    <div class="dashboard-header">
        <h1>🎫 Queue Management Dashboard</h1>
        <p>Real-time control center for managing service counters and customer queue</p>
        <div class="header-actions">
            <a href="../pages/queue-ticket.php" class="header-action-btn" target="_blank">
                🎫 Customer Portal
            </a>
            <a href="../pages/queue-kiosk.php" class="header-action-btn" target="_blank">
                📺 Display Kiosk
            </a>
            <a href="queue-monitor.php" class="header-action-btn">
                📊 Live Monitor
            </a>
            <button onclick="generateDummyTickets()" class="header-action-btn" style="border: none; cursor: pointer;">
                👥 Quick Generate 20 Dummies
            </button>
        </div>
    </div>

    <?php if ($success_message): ?>
    <div class="alert alert-success">
        <span>✅</span>
        <div>
            <strong>Success!</strong>
            <div><?php echo htmlspecialchars($success_message); ?></div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
    <div class="alert alert-error">
        <span>❌</span>
        <div>
            <strong>Error!</strong>
            <div><?php echo htmlspecialchars($error_message); ?></div>
        </div>
    </div>
    <?php endif; ?>

    <div class="main-content">
        <div class="counter-section">
            <div class="section-title">
                <span>🏢</span>
                Service Counter Management
            </div>
            
            <div class="counters-grid">
                <?php foreach ($counters as $counter): ?>
                <div class="counter-card <?php echo $counter['current_ticket_id'] ? 'busy' : 'available'; ?>">
                    <div class="counter-header">
                        <div class="counter-info">
                            <h3><?php echo htmlspecialchars($counter['counter_name']); ?></h3>
                            <p>Counter <?php echo $counter['counter_number']; ?></p>
                        </div>
                        <div class="counter-status <?php echo $counter['current_ticket_id'] ? 'busy' : 'available'; ?>">
                            <?php echo $counter['current_ticket_id'] ? 'BUSY' : 'READY'; ?>
                        </div>
                    </div>
                    
                    <div class="service-tag">
                        💼 <?php echo htmlspecialchars($counter['service_name']); ?>
                    </div>
                    
                    <?php if ($counter['current_ticket_id']): ?>
                    <div class="current-ticket">
                        <div class="ticket-number">🎫 <?php echo htmlspecialchars($counter['ticket_number']); ?></div>
                        <div class="customer-name">👤 <?php echo htmlspecialchars($counter['customer_name']); ?></div>
                        
                        <div class="counter-actions">
                            <button onclick="performCounterAction('complete_ticket', <?php echo $counter['id']; ?>)" class="btn btn-success">
                                ✓ Complete
                            </button>
                            <button onclick="performCounterAction('reset_counter', <?php echo $counter['id']; ?>)" class="btn btn-warning">
                                🔄 Reset
                            </button>
                        </div>
                    </div>
                    <?php else: ?>
                    <div style="text-align: center; padding: 20px 0;">
                        <div style="color: var(--gray-500); margin-bottom: 16px;">Ready to serve next customer</div>
                        <div class="counter-actions">
                            <button onclick="performCounterAction('call_next', <?php echo $counter['id']; ?>)" class="btn btn-primary">
                                📢 Call Next
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="queue-sidebar">
            <div class="section-title">
                <span>⏳</span>
                Queue Control
            </div>
            
            <div class="search-box">
                <input type="text" id="ticketSearch" placeholder="Search tickets...">
            </div>
            
            <div class="action-buttons">
                <button onclick="refreshData()" class="btn btn-info">
                    🔄 Refresh
                </button>
                <button onclick="performCounterAction('generate_test_ticket', 0)" class="btn btn-warning">
                    🧪 Test Ticket
                </button>
                <button onclick="generateDummyTickets()" class="btn btn-secondary">
                    👥 Generate 20 Dummies
                </button>
            </div>
            
            <div>
                <h4>📋 Current Queue</h4>
                <div id="waitingTicketsContainer">
                    <div style="text-align: center; color: var(--gray-500); padding: 20px;">
                        <div style="font-size: 2rem; margin-bottom: 12px; opacity: 0.5;">🎫</div>
                        <div>Loading queue data...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="admin-main-content" style="padding-top: 0; background: transparent; min-height: auto;">
    <div class="counter-section">
        <div class="section-title">
            <span>📊</span>
            Daily Queue Statistics
        </div>
        
        <div class="bottom-stats" id="statsContainer">
            <div class="stat-card waiting">
                <div class="stat-number">-</div>
                <div class="stat-label">Waiting</div>
            </div>
            <div class="stat-card serving">
                <div class="stat-number">-</div>
                <div class="stat-label">Serving</div>
            </div>
            <div class="stat-card completed">
                <div class="stat-number">-</div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-card cancelled">
                <div class="stat-number">-</div>
                <div class="stat-label">Cancelled</div>
            </div>
            <div class="stat-card total">
                <div class="stat-number">-</div>
                <div class="stat-label">Total Tickets</div>
            </div>
        </div>
    </div>
</div>

<div id="toastContainer" class="toast-container"></div>

<script>
function showToast(message, type = 'info', duration = 4000) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    const toastId = 'toast-' + Date.now();
    toast.id = toastId;
    toast.className = `toast ${type}`;
    
    // Enhanced toast with icon and close button
    const icons = {
        'success': '✅',
        'error': '❌',
        'warning': '⚠️',
        'info': 'ℹ️'
    };
    
    toast.innerHTML = `
        <div class="toast-content">
            <div class="toast-icon">${icons[type] || icons['info']}</div>
            <div class="toast-message">
                <strong>${type.charAt(0).toUpperCase() + type.slice(1)}</strong><br>
                ${message}
            </div>
            <button class="toast-close" onclick="closeToast('${toastId}')">&times;</button>
        </div>
        <div class="toast-progress"></div>
    `;
    
    container.appendChild(toast);
    
    // Auto-dismiss with progress bar
    const progressBar = toast.querySelector('.toast-progress');
    progressBar.style.animation = `progress ${duration}ms linear`;
    
    setTimeout(() => {
        closeToast(toastId);
    }, duration);
}

function closeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.style.transform = 'translateX(100%)';
        toast.style.opacity = '0';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }
}

function showModal(title, message, type = 'info', buttons = null) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.id = 'queue-modal';
    
    const icons = {
        'success': '✅',
        'error': '❌',
        'warning': '⚠️',
        'info': 'ℹ️',
        'question': '❓'
    };
    
    const defaultButtons = `
        <button class="modal-btn modal-btn-primary" onclick="closeModal()">OK</button>
    `;
    
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">${icons[type] || icons['info']}</div>
                <h3>${title}</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>${message}</p>
            </div>
            <div class="modal-footer">
                ${buttons || defaultButtons}
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Animate in
    setTimeout(() => {
        modal.classList.add('modal-show');
    }, 10);
}

function closeModal() {
    const modal = document.getElementById('queue-modal');
    if (modal) {
        modal.classList.remove('modal-show');
        setTimeout(() => {
            if (modal.parentNode) {
                modal.parentNode.removeChild(modal);
            }
        }, 300);
    }
}

function confirmAction(title, message, onConfirm, onCancel = null) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.id = 'confirm-modal';
    
    modal.innerHTML = `
        <div class="modal-content modal-confirm">
            <div class="modal-header">
                <div class="modal-icon">❓</div>
                <h3>${title}</h3>
            </div>
            <div class="modal-body">
                <p>${message}</p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-secondary" onclick="handleConfirmCancel()">Cancel</button>
                <button class="modal-btn modal-btn-danger" onclick="handleConfirmAction()">Confirm</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Store callbacks
    window.currentConfirmAction = onConfirm;
    window.currentConfirmCancel = onCancel;
    
    // Animate in
    setTimeout(() => {
        modal.classList.add('modal-show');
    }, 10);
}

function handleConfirmAction() {
    const modal = document.getElementById('confirm-modal');
    if (modal) {
        modal.classList.remove('modal-show');
        setTimeout(() => {
            if (modal.parentNode) {
                modal.parentNode.removeChild(modal);
            }
        }, 300);
    }
    
    if (window.currentConfirmAction) {
        window.currentConfirmAction();
        window.currentConfirmAction = null;
        window.currentConfirmCancel = null;
    }
}

function handleConfirmCancel() {
    const modal = document.getElementById('confirm-modal');
    if (modal) {
        modal.classList.remove('modal-show');
        setTimeout(() => {
            if (modal.parentNode) {
                modal.parentNode.removeChild(modal);
            }
        }, 300);
    }
    
    if (window.currentConfirmCancel) {
        window.currentConfirmCancel();
    }
    window.currentConfirmAction = null;
    window.currentConfirmCancel = null;
}

function performCounterAction(action, counterId) {
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span style="animation: spin 1s linear infinite; display: inline-block;">⟳</span> Processing...';
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', action);
    formData.append('counter_id', counterId);
    formData.append('ajax', 'true');
    
    // Perform AJAX request
    fetch('queue-admin.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            
            // Refresh the page data after successful action
            setTimeout(() => {
                refreshData();
                // Reload the entire counter section to reflect changes
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Unable to complete this action. Please try again.', 'error');
        }
    })
    .catch(error => {
        showToast('Unable to connect to the server. Please check your internet connection and try again.', 'error');
    })
    .finally(() => {
        // Restore button state
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

function manageTicket(action, ticketId) {
    const actionMessages = {
        'remove_ticket': {
            title: 'Remove Ticket',
            message: 'Are you sure you want to remove this ticket? This action will cancel the ticket and cannot be undone.'
        },
        'archive_ticket': {
            title: 'Archive Ticket', 
            message: 'Are you sure you want to archive this ticket? This will mark it as no-show and remove it from the active queue.'
        }
    };
    
    const actionInfo = actionMessages[action];
    if (!actionInfo) {
        showToast('This action is not available. Please refresh the page and try again.', 'error');
        return;
    }
    
    // Use custom confirmation modal instead of browser confirm
    confirmAction(
        actionInfo.title,
        actionInfo.message,
        () => executeTicketAction(action, ticketId), // onConfirm
        () => showToast('Action cancelled.', 'info') // onCancel
    );
}

function executeTicketAction(action, ticketId) {
    // Show loading state on ticket item
    const ticketElement = document.getElementById(`ticket-${ticketId}`);
    if (ticketElement) {
        ticketElement.style.opacity = '0.5';
        ticketElement.style.pointerEvents = 'none';
    }
    
    showToast('Processing request...', 'info', 2000);
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', action);
    formData.append('ticket_id', ticketId);
    formData.append('ajax', 'true');
    
    // Perform AJAX request
    fetch('queue-admin.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            
            // Remove ticket from display with animation
            if (ticketElement) {
                ticketElement.style.transform = 'translateX(-100%)';
                setTimeout(() => {
                    ticketElement.remove();
                    refreshData(); // Refresh stats
                    showToast('Queue statistics updated.', 'info', 2000);
                }, 300);
            }
        } else {
            showToast(data.message || 'Unable to complete this action. Please try again.', 'error');
            // Restore ticket element state
            if (ticketElement) {
                ticketElement.style.opacity = '1';
                ticketElement.style.pointerEvents = 'auto';
            }
        }
    })
    .catch(error => {
        showToast('Unable to complete the requested action. Please try again.', 'error');
        // Restore ticket element state
        if (ticketElement) {
            ticketElement.style.opacity = '1';
            ticketElement.style.pointerEvents = 'auto';
        }
    });
}

function generateDummyTickets(count = 20) {
    // Confirm action with user
    confirmAction(
        'Generate Dummy Tickets',
        `This will generate ${count} random dummy tickets for testing. Are you sure you want to proceed?`,
        () => executeDummyGeneration(count),
        () => showToast('Dummy ticket generation cancelled.', 'info')
    );
}

function executeDummyGeneration(count) {
    showToast('Generating dummy tickets... Please wait.', 'info', 3000);
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'generate_dummy_tickets');
    formData.append('count', count);
    formData.append('ajax', 'true');
    
    // Perform AJAX request
    fetch('queue-admin.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success', 6000);
            
            // Show detailed results if available
            if (data.generated > 0) {
                showModal(
                    'Dummy Tickets Generated Successfully!',
                    `
                        <strong>Generation Complete:</strong><br>
                        ✅ Successfully generated: ${data.generated} tickets<br>
                        ${data.failed > 0 ? `❌ Failed to generate: ${data.failed} tickets<br>` : ''}
                        <br>
                        The queue has been populated with realistic dummy data for testing purposes.
                    `,
                    'success'
                );
            }
            
            // Refresh the queue data
            setTimeout(() => {
                refreshData();
                showToast('Queue data refreshed with new dummy tickets.', 'info', 3000);
            }, 1000);
            
        } else {
            showToast(data.message || 'Unable to generate test tickets. Please try again.', 'error');
        }
    })
    .catch(error => {
        showToast('Unable to generate test tickets. Please try again or contact support if the problem persists.', 'error');
    });
}

function refreshData() {
    fetch('queue-admin.php?action=get_queue_data')
        .then(response => {
            if (!response.ok) {
                throw new Error('Server connection failed');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                updateStatsDisplay(data.stats);
                updateTicketsDisplay(data.tickets);
                showToast('Queue data refreshed successfully.', 'success', 2000);
            } else {
                showToast('Unable to refresh queue information: ' + (data.message || 'Please try again later.'), 'error');
            }
        })
        .catch(error => {
            showToast('Unable to refresh queue information. Please check your connection and try again.', 'error');
        });
}

function updateStatsDisplay(stats) {
    if (!stats) return;
    
    const container = document.getElementById('statsContainer');
    
    // Display the basic statistics
    const totalTickets = stats.total_tickets || 0;
    
    container.innerHTML = `
        <div class="stat-card waiting">
            <div class="stat-number">${stats.waiting || 0}</div>
            <div class="stat-label">Waiting</div>
        </div>
        <div class="stat-card serving">
            <div class="stat-number">${stats.serving || 0}</div>
            <div class="stat-label">Serving</div>
        </div>
        <div class="stat-card completed">
            <div class="stat-number">${stats.completed || 0}</div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-card cancelled">
            <div class="stat-number">${stats.cancelled || 0}</div>
            <div class="stat-label">Cancelled</div>
        </div>
        <div class="stat-card total">
            <div class="stat-number">${totalTickets}</div>
            <div class="stat-label">Total Tickets</div>
        </div>
    `;
}

function updateTicketsDisplay(tickets) {
    const container = document.getElementById('waitingTicketsContainer');
    if (!container) return;
    
    if (!tickets || tickets.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; color: var(--gray-500); padding: 20px;">
                <div style="font-size: 2rem; margin-bottom: 12px; opacity: 0.5;">🎫</div>
                <div>No tickets in queue</div>
            </div>
        `;
        return;
    }
    
    container.innerHTML = tickets.map(ticket => `
        <div class="ticket-item ${ticket.priority_level}" id="ticket-${ticket.id}">
            <div class="ticket-header">
                <div class="ticket-number-small">${ticket.ticket_number}</div>
                <span class="priority-badge ${ticket.priority_level}">${ticket.priority_level}</span>
            </div>
            <div class="customer-name-small">${ticket.customer_name}</div>
            <div style="font-size: 0.75rem; color: var(--gray-500); margin-bottom: 8px;">${ticket.service_name}</div>
            <div class="ticket-controls">
                <button onclick="manageTicket('remove_ticket', ${ticket.id})" class="btn-ticket-action btn-remove" title="Remove Ticket">
                    🗑️ Remove
                </button>
                <button onclick="manageTicket('archive_ticket', ${ticket.id})" class="btn-ticket-action btn-archive" title="Archive Ticket">
                    📦 Archive
                </button>
            </div>
        </div>
    `).join('');
}

// Add CSS for loading spinner
const style = document.createElement('style');
style.textContent = `
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
`;
document.head.appendChild(style);

document.addEventListener('DOMContentLoaded', function() {
    refreshData();
    setInterval(refreshData, 30000);
    
    setTimeout(() => {
        showToast('Queue Management Dashboard loaded successfully', 'success');
    }, 500);
});
</script>

<?php include '../includes/admin_footer.php'; ?>