<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Use admin authentication for access control
require_once __DIR__ . '/auth_check.php'; // sets admin session and redirects if not logged in
// If we reached here, the user passed admin auth_check
$isAllowed = true;

$page_title = 'Assistance Requests - Admin';
$page_description = 'Live list of assistance (Need Help) requests for staff.';
$base_path = '../';

// DB connection: `auth_check.php` already required the connector and likely set $pdo.
// As a safeguard, only attempt to include the connector if $pdo is not a valid PDO instance.
if (!isset($pdo) || !($pdo instanceof PDO)) {
    try {
        $dbPath = __DIR__ . '/../includes/db_connect.php';
        if (file_exists($dbPath)) {
            require_once $dbPath; // should define $pdo
        }
    } catch (Throwable $e) {
        // ignore
    }
}

// Fetch recent assistance requests
$requests = [];
if ($pdo instanceof PDO) {
    try {
        $stmt = $pdo->query("SELECT id, user_id, user_name, is_admin, ts_ms, created_at
                              FROM assist_requests
                              ORDER BY created_at DESC, id DESC
                              LIMIT 200");
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $requests = [];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Assistance Requests - Admin</title>
  <style>
    /* Base */
    html, body { background: #ffffff; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    /* Inherit admin page structure from view-certificate-requests.php */
    .admin-container { max-width: 1400px; margin: 0 auto; padding: 2rem; background: #f8f9fa; min-height: 100vh; }
    .admin-header { background: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%); color: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    .admin-header .title { margin: 0; font-weight: 700; font-size: 1.25rem; }
    .admin-actions { display: flex; gap: .5rem; }
    .admin-btn { display:inline-block; padding: 0.6rem 1.2rem; background: linear-gradient(135deg, #4CAF50, #45a049); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; transition: all 0.3s ease; }
    .admin-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3); }
    .admin-btn.secondary { background: #e8f5e9; color: #1b5e20; border: 1px solid #cde7cf; box-shadow: none; }
    .admin-table { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); }
    .admin-table table { width: 100%; border-collapse: collapse; }
    .admin-table th, .admin-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
    .admin-table th { background: #f8f9fa; font-weight: 600; color: #2e7d32; }
    .muted { color: #666; font-size: 0.9rem; }
    .nowrap { white-space: nowrap; }
    .small { font-size: 0.95rem; }
    .badge-role { display:inline-block; padding: 0.3rem 0.8rem; border-radius: 12px; background: #f0f6f0; color: #1b5e20; border: 1px solid rgba(27,94,32,0.15); font-size: 0.8rem; font-weight: 800; }
    .banner { margin: 0 0 10px; padding: 10px 12px; border-radius: 8px; background: #ecf7ec; border: 1px solid rgba(27,94,32,0.15); color: #1b5e20; display:none; }
  </style>
  <style>
    /* Offset for fixed admin mini nav */
    .admin-container { padding-top: 70px; }
  </style>
</head>
<body>
<?php $base_path = '../'; include __DIR__ . '/../includes/admin_mini_nav.php'; ?>

<div class="admin-container">
<?php if (!$isAllowed): ?>
      <div style="background:#fff; padding:20px; border-radius:12px; border:1px solid #eee; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
        <h3 style="margin:0 0 10px; color:#b71c1c;">Access denied</h3>
        <p>You do not have permission to view assistance requests.</p>
      </div>
<?php else: ?>
      <div class="admin-header">
        <h3 class="title">Assistance Requests</h3>
        <div class="admin-actions">
          <button class="admin-btn secondary" onclick="location.reload()">Refresh</button>
          <button class="admin-btn secondary" id="toggleAutoBtn">Auto-Refresh: Off</button>
        </div>
      </div>
      <div class="admin-table">
        <div class="banner" id="newBanner">New assistance received</div>

<?php if (!($pdo instanceof PDO)): ?>
        <p class="muted">Database connection not available. The list cannot be loaded.</p>
<?php elseif (empty($requests)): ?>
        <p class="muted">No assistance requests yet.</p>
<?php else: ?>
          <div style="overflow: auto; max-height: 65vh;">
            <table>
            <thead>
              <tr>
                <th class="nowrap">#</th>
                <th>When</th>
                <th>User</th>
                <th class="nowrap">Role</th>
              </tr>
            </thead>
            <tbody>
<?php foreach ($requests as $row): 
  $ts = !empty($row['ts_ms']) ? (int)$row['ts_ms'] : null;
  $when = $row['created_at'] ?? null;
  $whenText = $when ? date('Y-m-d H:i:s', strtotime($when)) : ($ts ? date('Y-m-d H:i:s', (int)($ts/1000)) : '');
?>
              <tr>
                <td class="nowrap">#<?php echo (int)$row['id']; ?></td>
                <td class="nowrap small"><?php echo htmlspecialchars($whenText); ?></td>
                <td>
                  <div><?php echo htmlspecialchars($row['user_name'] ?? 'Guest'); ?></div>
                  <div class="muted small">ID: <?php echo htmlspecialchars((string)($row['user_id'] ?? '—')); ?></div>
                </td>
                <td class="nowrap"><span class="badge-role"><?php echo !empty($row['is_admin']) ? 'Admin' : 'Resident'; ?></span></td>
              </tr>
<?php endforeach; ?>
            </tbody>
            </table>
          </div>
      </div>
<?php endif; ?>
    
</div>
<?php endif; ?>

<script>
(function(){
  var btn = document.getElementById('toggleAutoBtn');
  var timer = null;
  function updateBtn(){ if(btn) btn.textContent = 'Auto-Refresh: ' + (timer ? 'On' : 'Off'); }
  if (btn) {
    btn.addEventListener('click', function(){
      if (timer) { clearInterval(timer); timer = null; updateBtn(); return; }
      timer = setInterval(function(){ location.reload(); }, 10000);
      updateBtn();
    });
    updateBtn();
  }

  // New request notifier
  var lastSeenId = <?php echo isset($requests[0]['id']) ? (int)$requests[0]['id'] : 0; ?>;
  var banner = document.getElementById('newBanner');
  function beep(){
    try {
      var ctx = new (window.AudioContext || window.webkitAudioContext)();
      var o = ctx.createOscillator();
      var g = ctx.createGain();
      o.connect(g); g.connect(ctx.destination);
      o.type = 'sine'; o.frequency.value = 880; g.gain.value = 0.08;
      o.start(); setTimeout(function(){ o.stop(); ctx.close(); }, 350);
    } catch(e){}
  }
  function poll(){
    fetch('<?php echo $base_path; ?>admin/assist-requests-feed.php', { cache: 'no-store' })
      .then(function(r){ return r.ok ? r.json() : null; })
      .then(function(json){
        if (!json || typeof json.last_id === 'undefined') return;
        if (json.last_id > lastSeenId) {
          lastSeenId = json.last_id;
          if (banner) {
            banner.style.display = 'block';
            setTimeout(function(){ banner.style.display = 'none'; }, 2500);
          }
          beep();
        }
      })
      .catch(function(){});
  }
  setInterval(poll, 5000);
})();
</script>

</body>
</html>
