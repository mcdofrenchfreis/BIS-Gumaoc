<?php
// Set content type to JSON
header('Content-Type: application/json');

// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get certificate request ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode(['error' => 'Invalid certificate request ID']);
    exit;
}

try {
    // Include the database connection file directly
    include_once('../includes/db_connect.php');
    
    // Check if $pdo is available from the included file
    if (!isset($pdo)) {
        // If not, create a new connection
        $host = 'localhost';
        $dbname = 'gumaoc_db';
        $username = 'root';
        $password = '';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    // Get the certificate request data
    $stmt = $pdo->prepare("
        SELECT * FROM certificate_requests WHERE id = :id
    ");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        echo json_encode(['error' => 'Certificate request not found']);
        exit;
    }
    
    // Return the certificate request details as JSON
    echo json_encode($request);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>