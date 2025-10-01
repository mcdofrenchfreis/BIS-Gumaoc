<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'Assistance Requests - Barangay Gumaoc East';
$page_description = 'Live list of assistance (Need Help) requests.';
$base_path = '../';

// Attempt DB connection
$pdo = null;
try {
    $dbPath = __DIR__ . '/../includes/db_connect.php';
    if (file_exists($dbPath)) {
        require_once $dbPath; // should define $pdo
    }
} catch (Throwable $e) {
    // ignore
}

// Fetch recent assistance requests
$requests = [];
if ($pdo instanceof PDO) {
    try {
        $stmt = $pdo->query("SELECT id, user_id, user_name, page, is_admin, ip, user_agent, ts_ms, created_at
                              FROM assist_requests
                              ORDER BY created_at DESC, id DESC
                              LIMIT 100");
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $requests = [];
    }
}

include __DIR__ . '/../includes/header.php';
?>

<style>
  .assist-page-wrapper { padding: 20px; }
  .assist-card { background: rgba(255,255,255,0.92); backdrop-filter: blur(18px); border-radius: 16px; border: 1px solid rgba(27,94,32,0.15); box-shadow: 0 20px 40px rgba(27,94,32,0.12); padding: 20px; }
  .assist-header { display:flex; align-items:center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
  .assist-header h2 { margin: 0; color: #1b5e20; }
  .assist-actions { display:flex; gap: 10px; }
  .btn { padding: 10px 14px; border-radius: 10px; border: 1px solid transparent; font-weight: 700; cursor: pointer; }
  .btn-outline { background: #e8f5e9; color: #1b5e20; border-color: rgba(27,94,32,0.25); }
  .assist-table { width: 100%; border-collapse: collapse; }
  .assist-table th, .assist-table td { padding: 10px 12px; border-bottom: 1px solid rgba(0,0,0,0.08); text-align: left; }
  .assist-table th { color: #1b5e20; background: rgba(232,245,233,0.6); position: sticky; top: 0; z-index: 1; }
  .assist-badge { display:inline-block; padding: 4px 8px; border-radius: 999px; font-size: 0.8rem; font-weight: 800; border: 1px solid rgba(27,94,32,0.25); background: rgba(27,94,32,0.06); color: #1b5e20; }
  .muted { color: #666; font-size: 0.9rem; }
  .nowrap { white-space: nowrap; }
  .small { font-size: 0.9rem; }
</style>

<div class="page-container">
  <div class="content-section">
    <div class="container">
      <div class="assist-page-wrapper">
        <div class="assist-card">
          <div class="assist-header">
            <h2>Assistance Requests</h2>
            <div class="assist-actions">
              <button class="btn btn-outline" onclick="location.reload()">Refresh</button>
              <button class="btn btn-outline" id="toggleAutoBtn">Auto-Refresh: Off</button>
            </div>
          </div>

          <?php if (!($pdo instanceof PDO)): ?>
            <p class="muted">Database connection not available. The list cannot be loaded.</p>
          <?php elseif (empty($requests)): ?>
            <p class="muted">No assistance requests yet.</p>
          <?php else: ?>
            <div style="overflow: auto; max-height: 65vh;">
              <table class="assist-table">
                <thead>
                  <tr>
                    <th class="nowrap">#</th>
                    <th>When</th>
                    <th>User</th>
                    <th>From Page</th>
                    <th class="nowrap">Open</th>
                    <th class="nowrap">IP</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($requests as $row): 
                  $page = (string)($row['page'] ?? '/');
                  $pageTrim = ltrim($page, '/');
                  $openHref = $base_path . $pageTrim; // best-effort relative link
                  $ts = !empty($row['ts_ms']) ? (int)$row['ts_ms'] : null;
                  $when = $row['created_at'] ?? null;
                  $whenText = $when ? date('Y-m-d H:i:s', strtotime($when)) : ($ts ? date('Y-m-d H:i:s', (int)($ts/1000)) : '');
                ?>
                  <tr>
                    <td class="nowrap">#<?php echo (int)$row['id']; ?></td>
                    <td class="nowrap small"><?php echo htmlspecialchars($whenText); ?></td>
                    <td>
                      <div><?php echo htmlspecialchars($row['user_name'] ?? 'Guest'); ?></div>
                      <div class="muted small">ID: <?php echo htmlspecialchars((string)($row['user_id'] ?? '—')); ?> <?php echo (!empty($row['is_admin']) ? '(Admin)' : ''); ?></div>
                    </td>
                    <td>
                      <div class="small"><?php echo htmlspecialchars($page); ?></div>
                      <div class="muted small">UA: <?php echo htmlspecialchars((string)($row['user_agent'] ?? '')); ?></div>
                    </td>
                    <td class="nowrap"><a class="assist-badge" href="<?php echo htmlspecialchars($openHref); ?>" target="_blank" rel="noopener">Open</a></td>
                    <td class="nowrap small"><?php echo htmlspecialchars((string)($row['ip'] ?? '')); ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  var btn = document.getElementById('toggleAutoBtn');
  var timer = null;
  function updateBtn(){ btn.textContent = 'Auto-Refresh: ' + (timer ? 'On' : 'Off'); }
  if (btn) {
    btn.addEventListener('click', function(){
      if (timer) { clearInterval(timer); timer = null; updateBtn(); return; }
      timer = setInterval(function(){ location.reload(); }, 10000);
      updateBtn();
    });
    updateBtn();
  }
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
