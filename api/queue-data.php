<?php
session_start();
header('Content-Type: application/json');

echo json_encode([
    'success' => false,
    'message' => 'Queue management feature has been disabled.',
]);

?>