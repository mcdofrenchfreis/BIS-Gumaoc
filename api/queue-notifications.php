<?php
header('Content-Type: application/json');
session_start();

// Check if user is logged in
if (!isset($_SESSION['rfid_authenticated']) || $_SESSION['rfid_authenticated'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

include '../includes/db_connect.php';

$user_name = $_SESSION['user_name'];

try {
    // Get active queue tickets for this user
    $stmt = $pdo->prepare("
        SELECT qt.ticket_number, qt.status, qs.service_name, qt.queue_position, qt.estimated_time
        FROM queue_tickets qt 
        JOIN queue_services qs ON qt.service_id = qs.id 
        WHERE qt.customer_name = ? 
        AND qt.status IN ('waiting', 'serving') 
        AND DATE(qt.created_at) = CURDATE()
        ORDER BY qt.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_name]);
    $tickets = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'tickets' => $tickets,
        'count' => count($tickets)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching queue notifications: ' . $e->getMessage()
    ]);
}
?>