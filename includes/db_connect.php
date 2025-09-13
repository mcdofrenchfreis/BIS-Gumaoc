<?php
$host = 'localhost';
$dbname = 'u138614204_gumaoc_db';
$username = 'u138614204_gumaoc';
$password = 'YOUR_DATABASE_PASSWORD_HERE'; // Replace with your actual database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?> 