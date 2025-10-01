<?php
 // Use kiosk-specific session bootstrap to isolate sessions from other areas
 require_once __DIR__ . '/session_bootstrap.php';
 include '../includes/db_connect.php';

$error = '';

if ($_POST && isset($_POST['rfid_code'])) {
    $rfid_code = trim($_POST['rfid_code']);
    
    // Check if RFID exists in database
    $stmt = $pdo->prepare("SELECT * FROM residents WHERE rfid_code = ? AND status = 'active'");
    $stmt->execute([$rfid_code]);
    $user = $stmt->fetch();
    
    if ($user) {
     // Rotate session ID on login to prevent fixation
     if (session_status() === PHP_SESSION_ACTIVE) {
         session_regenerate_id(true);
     }
     $_SESSION['rfid_authenticated'] = true;
     $_SESSION['user_id'] = $user['id'];
     $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
     // Set auxiliary cookies for name/id so shared pages can render even if session read fails
     $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
     $host = $_SERVER['HTTP_HOST'] ?? '';
     setcookie('GUMAOC_USER_NAME', $_SESSION['user_name'], 0, '/', '', $secure, false);
     setcookie('GUMAOC_USER_ID', (string)$_SESSION['user_id'], 0, '/', '', $secure, false);
     // Ensure session is flushed before redirect so subsequent pages see the data
     session_write_close();
     header('Location: ../pages/services.php');
     exit();
 } else {
        $error = 'Invalid RFID or user not found.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID Login - GUMAOC</title>
    <style>
        html, body { height: 100%; }
        body {
            margin:0;
            font-family: Arial, Helvetica, sans-serif;
            color:#123;
            position: relative;
            display: grid;
            place-items: center;
            /* No direct background; handled via pseudo-elements to limit blur to BG only */
            background: none;
        }
        /* Blurred background image layer (behind everything) */
        body::before {
            content: '';
            position: fixed;
            inset: -8px; /* small bleed to hide blur edges */
            background: url('../assets/images/bg2.jpg') center/cover no-repeat fixed;
            filter: blur(4px);
            z-index: -2;
            pointer-events: none;
        }
        /* Dark overlay above the blurred image (still behind content) */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background: linear-gradient(rgba(0,0,0,.35), rgba(0,0,0,.35));
            z-index: -1;
            pointer-events: none;
        }
        .wrap { width: min(920px, 92vw); margin: 3vh auto; padding: 0 1rem; }
        .card { background:#fff; border:1px solid #e9ecef; border-radius:16px; box-shadow:0 14px 36px rgba(0,0,0,.18); overflow:hidden; }
        .card-header { background: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%); color:#fff; padding: 16px 18px; display:flex; align-items:center; justify-content:space-between; }
        .card-header h2 { margin:0; font-size:1.25rem; }
        .back-btn { background: rgba(255,255,255,.15); color:#fff; border:1px solid rgba(255,255,255,.35); padding:6px 10px; border-radius:8px; text-decoration:none; font-weight:600; }
        .back-btn:hover { background: rgba(255,255,255,.25); }
        .card-body { padding: 24px; }
        .hint { color:#444; font-size:1.05rem; margin-bottom:1rem; }
        .field { display:flex; gap:.75rem; }
        input[type="text"] { flex:1; padding:16px 18px; border:1px solid #ced4da; border-radius:12px; font-size:1.1rem; }
        button[type="submit"] { background: linear-gradient(135deg, #4CAF50, #45a049); color:#fff; border:none; padding:16px 18px; border-radius:12px; font-weight:800; cursor:pointer; }
        button[type="submit"]:hover { transform: translateY(-1px); box-shadow:0 8px 20px rgba(76,175,80,.25); }
        .error { color:#dc3545; margin: .5rem 0 0; font-weight:600; }
        .footer { padding: 16px 18px; background:#fafbfc; border-top:1px solid #e9ecef; font-size:1rem; }
        .footer a { color:#1565c0; font-weight:700; text-decoration:none; }
        .footer a:hover { text-decoration:underline; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="card-header">
                <h2>RFID Tap Login</h2>
                <a class="back-btn" href="index.php">← Back</a>
            </div>
            <div class="card-body">
                <p class="hint">Please tap or scan your RFID card. If your reader types into the input, make sure the field below is focused.</p>
                <form method="POST">
                    <div class="field">
                        <input type="text" name="rfid_code" placeholder="Scan / Tap RFID Card" required autofocus>
                        <button type="submit">Continue</button>
                    </div>
                    <?php if ($error): ?>
                        <div class="error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                </form>
            </div>
            <div class="footer">
                Don't have an RFID? <a href="../pages/resident-registration.php">Register here</a>
            </div>
        </div>
    </div>
</body>
</html>
