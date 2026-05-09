<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=u138614204_gumaoc_db', 'u138614204_gumaoc', 'YOUR_DATABASE_PASSWORD_HERE'); // Replace with your actual database password
    $stmt = $pdo->query('DESCRIBE business_applications');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Business Applications Table Structure:\n";
    foreach ($columns as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
    
    echo "\n\nSample data:\n";
    $stmt = $pdo->query('SELECT * FROM business_applications LIMIT 1');
    $sample = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($sample) {
        foreach ($sample as $key => $value) {
            echo "$key: $value\n";
        }
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
