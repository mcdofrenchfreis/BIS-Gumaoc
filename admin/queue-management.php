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
            $result = $queueManager->callNextTicket($window_id);
            
            if ($result['success']) {
                $message = "Called ticket: " . $result['ticket']['ticket_number'];
                $message_type = 'success';
                $logger->log('queue_action', 'queue_ticket', 'Called next ticket for window ' . $result['window']['window_code']);
            } else {
                $message = $result['error'];
                $message_type = 'error';
            }
            break;
            
        case 'complete_ticket':
            $ticket_id = (int)$_POST['ticket_id'];
            $notes = trim($_POST['notes'] ?? '');
            
            if ($queueManager->completeTicket($ticket_id, $notes)) {
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
            $stmt = $pdo->prepare("UPDATE queue_tickets SET status = 'cancelled' WHERE id = ?");
            
            if ($stmt->execute([$ticket_id])) {
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
    SELECT w.*, t.ticket_number, t.full_name as current_customer
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
    WHERE t.status = 'serving' AND t.queue_date = CURDATE()
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
    WHERE t.status = 'waiting' AND t.queue_date = CURDATE()
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
        COUNT(*) as total,
        AVG(CASE WHEN status = 'completed' AND completed_at IS NOT NULL AND called_at IS NOT NULL 
                 THEN TIMESTAMPDIFF(MINUTE, called_at, completed_at) END) as avg_service_time
    FROM queue_tickets 
    WHERE queue_date = CURDATE()
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
            justify-content: between;
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
        
        .queue-item.priority-emergency { border-left-color:// filepath: c:\xampp\htdocs\GUMAOC\admin\queue-management.php
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
            $result = $queueManager->callNextTicket($window_id);
            
            if ($result['success']) {
                $message = "Called ticket: " . $result['ticket']['ticket_number'];
                $message_type = 'success';
                $logger->log('queue_action', 'queue_ticket', 'Called next ticket for window ' . $result['window']['window_code']);
            } else {
                $message = $result['error'];
                $message_type = 'error';
            }
            break;
            
        case 'complete_ticket':
            $ticket_id = (int)$_POST['ticket_id'];
            $notes = trim($_POST['notes'] ?? '');
            
            if ($queueManager->completeTicket($ticket_id, $notes)) {
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
            $stmt = $pdo->prepare("UPDATE queue_tickets SET status = 'cancelled' WHERE id = ?");
            
            if ($stmt->execute([$ticket_id])) {
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
    SELECT w.*, t.ticket_number, t.full_name as current_customer
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
    WHERE t.status = 'serving' AND t.queue_date = CURDATE()
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
    WHERE t.status = 'waiting' AND t.queue_date = CURDATE()
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
        COUNT(*) as total,
        AVG(CASE WHEN status = 'completed' AND completed_at IS NOT NULL AND called_at IS NOT NULL 
                 THEN TIMESTAMPDIFF(MINUTE, called_at, completed_at) END) as avg_service_time
    FROM queue_tickets 
    WHERE queue_date = CURDATE()
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
            justify-content: between;
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
        
        .queue-item.priority-emergency { border-