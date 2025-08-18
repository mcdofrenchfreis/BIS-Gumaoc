<?php
session_start();
include 'includes/db_connect.php';

$error = '';

if ($_POST && isset($_POST['rfid_code'])) {
    $rfid_code = trim($_POST['rfid_code']);
    
    // Check if RFID exists in database
    $stmt = $pdo->prepare("SELECT * FROM residents WHERE rfid_code = ? AND status = 'active'");
    $stmt->execute([$rfid_code]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['rfid_authenticated'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        header('Location: pages/business-application.php');
        exit();
    } else {
        $error = 'Invalid RFID or user not found.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>RFID Login - GUMAOC</title>
</head>
<body>
    <h2>RFID Login</h2>
    <?php if ($error): ?>
        <div style="color: red;"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <input type="text" name="rfid_code" placeholder="Scan RFID Card" required autofocus>
        <button type="submit">Login</button>
    </form>
    
    <p><a href="resident-registration.php">Don't have an RFID? Register here</a></p>
</body>
</html>