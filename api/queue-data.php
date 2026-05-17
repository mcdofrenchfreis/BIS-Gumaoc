<?php
session_start();
header('Content-Type: application/json');

include '../includes/db_connect.php';
include '../includes/QueueManager.php';

// Initialize queue manager
$queueManager = new QueueManager($pdo);

// Get real-time queue data
$currently_serving = $queueManager->getCurrentlyServing();
$next_in_queue = $queueManager->getNextInQueue(10);

// Get today's overall statistics
$today_stats = [];
try {
    $stats_query = $pdo->query("
        SELECT 
            COUNT(*) as total_tickets,
            SUM(CASE WHEN status = 'waiting' THEN 1 ELSE 0 END) as waiting,
            SUM(CASE WHEN status = 'serving' THEN 1 ELSE 0 END) as serving,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
        FROM queue_tickets 
        WHERE DATE(created_at) = CURDATE()
    ");
    $today_stats = $stats_query->fetch();
} catch (Exception $e) {
    $today_stats = ['total_tickets' => 0, 'waiting' => 0, 'serving' => 0, 'completed' => 0];
}

// Prepare response data
$response = [
    'currently_serving' => $currently_serving,
    'next_in_queue' => $next_in_queue,
    'today_stats' => $today_stats,
    'current_time' => date('l, F j, Y, h:i:s A')
];

echo json_encode($response);
?>