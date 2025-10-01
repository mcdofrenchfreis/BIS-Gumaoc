<?php
// Admin landing index - redirect to dashboard
// If you want to change the landing later, update the Location target below.

// Prevent caching of the redirect for quicker updates if changed later
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Temporary redirect (302). Change to 301 if you want it permanent.
header('Location: dashboard.php', true, 302);
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Redirecting...</title>
  <meta http-equiv="refresh" content="0; url=dashboard.php" />
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 2rem; color: #333; }
    a { color: #1b5e20; }
  </style>
</head>
<body>
  <p>Redirecting to the admin dashboard. If you are not redirected automatically, <a href="dashboard.php">click here</a>.</p>
</body>
</html>
