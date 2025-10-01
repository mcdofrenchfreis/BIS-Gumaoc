<?php
header('Location: kiosk/rfid-login.php');
exit;
?>
<!DOCTYPE html>
<html lang="en"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID Login - GUMAOC</title>
    <style>
        body { margin:0; background:#f8f9fa; font-family: Arial, Helvetica, sans-serif; }
        .wrap { max-width: 520px; margin: 2rem auto; padding: 0 1rem; }
        .card { background:#fff; border:1px solid #e9ecef; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,.06); overflow:hidden; }
        .card-header { background: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%); color:#fff; padding: 12px 16px; display:flex; align-items:center; justify-content:space-between; }
        .card-header h2 { margin:0; font-size:1.1rem; }
        .back-btn { background: rgba(255,255,255,.15); color:#fff; border:1px solid rgba(255,255,255,.35); padding:6px 10px; border-radius:8px; text-decoration:none; font-weight:600; }
        .back-btn:hover { background: rgba(255,255,255,.25); }
        .card-body { padding: 18px; }
        .hint { color:#555; font-size:.95rem; margin-bottom:.75rem; }
        .field { display:flex; gap:.5rem; }
        input[type="text"] { flex:1; padding:12px 14px; border:1px solid #ced4da; border-radius:10px; font-size:1rem; }
        button[type="submit"] { background: linear-gradient(135deg, #4CAF50, #45a049); color:#fff; border:none; padding:12px 16px; border-radius:10px; font-weight:700; cursor:pointer; }
        button[type="submit"]:hover { transform: translateY(-1px); box-shadow:0 6px 16px rgba(76,175,80,.25); }
        .error { color:#dc3545; margin: .5rem 0 0; font-weight:600; }
        .footer { padding: 12px 16px; background:#fafbfc; border-top:1px solid #e9ecef; font-size:.95rem; }
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
                Don't have an RFID? <a href="pages/resident-registration.php">Register here</a>
            </div>
        </div>
    </div>
</body>
</html>