<?php
session_start();
include '../includes/db_connect.php';
include '../includes/AdminLogger.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['export']) || $_GET['export'] !== 'csv') {
    header('Location: view-logs.php');
    exit;
}

$logger = new AdminLogger($pdo);

// Get filters from URL
$filters = array_filter([
    'admin_id' => $_GET['admin_id'] ?? '',
    'action_type' => $_GET['action_type'] ?? '',
    'target_type' => $_GET['target_type'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
]);

// Get all logs with filters (no limit for export)
$logs = $logger->getLogs(10000, 0, $filters);

// Set headers for CSV download
$filename = 'admin_logs_' . date('Y-m-d_H-i-s') . '.csv';
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Create file pointer
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'ID',
    'Timestamp',
    'Admin ID',
    'Action Type',
    'Target Type',
    'Target ID',
    'Description',
    'IP Address',
    'User Agent',
    'Details'
]);

// Add data rows
foreach ($logs as $log) {
    fputcsv($output, [
        $log['id'],
        $log['created_at'],
        $log['admin_id'],
        $log['action_type'],
        $log['target_type'],
        $log['target_id'],
        $log['description'],
        $log['ip_address'],
        $log['user_agent'],
        $log['details']
    ]);
}

// Log the export action
$logger->log('export_action', 'admin_logs', 'Exported admin logs to CSV', null, [
    'filename' => $filename,
    'record_count' => count($logs),
    'filters_applied' => $filters
]);

fclose($output);
exit;
?>