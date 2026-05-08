<?php
// Lightweight top navigation with Back button (left) and Profile (right)
// Expects (if available): $base_path, $admin_view, $current_user
// Optional: $force_guest = true to disable any user/admin session resolution and always show Guest
if (!isset($base_path)) { $base_path = '../'; }
if (!isset($force_guest)) { $force_guest = false; }

// Ensure the correct area session is loaded (user/kiosk) without creating a default session
// Skip entirely when forcing guest mode
if (!$force_guest) {
    // Per-page override if provided
    if (isset($force_area) && ($force_area === 'user' || $force_area === 'kiosk')) {
        $want = $force_area;
    }
    // Prefer explicit cookies to decide which area to load on shared paths like /pages/
    // Since primary login is kiosk, prefer kiosk cookie over user when both exist
    elseif (!empty($_COOKIE['GUMAOC_KIOSK_SESSID'])) {
        $want = 'kiosk';
    } elseif (!empty($_COOKIE['GUMAOC_USER_SESSID'])) {
        $want = 'user';
    } else {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $want = (strpos($script, '/kiosk/') !== false) ? 'kiosk' : ((strpos($script, '/user/') !== false) ? 'user' : 'user');
    }
    $expectedName = ($want === 'kiosk') ? 'GUMAOC_KIOSK_SESSID' : 'GUMAOC_USER_SESSID';

    if (session_status() === PHP_SESSION_ACTIVE) {
        // If a session is active but not the expected area, close it so we can init the right one
        if (session_name() !== $expectedName) {
            @session_write_close();
        }
    }

    if (session_status() !== PHP_SESSION_ACTIVE || session_name() !== $expectedName) {
        $p = __DIR__ . '/../' . $want . '/session_bootstrap.php';
        if (file_exists($p)) { require_once $p; }
    }
    // Final safety: ensure session is active to read $_SESSION
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
}

// If the parent page did not provide $current_user, attempt to resolve from session
if (!$force_guest && (!isset($current_user) || !is_array($current_user))) {
    if (!empty($_SESSION['user_id'])) {
        // Safely include DB connector relative to this file
        $dbPath = __DIR__ . '/db_connect.php';
        if (file_exists($dbPath)) {
            require_once $dbPath;
            try {
                if (isset($pdo)) {
                    $stmt = $pdo->prepare('SELECT * FROM residents WHERE id = ?');
                    $stmt->execute([$_SESSION['user_id']]);
                    $fetched = $stmt->fetch();
                    if ($fetched && is_array($fetched)) {
                        $current_user = $fetched;
                    }
                }
            } catch (Throwable $e) {
                // Silently ignore; will fall back to session-provided name or guest
            }
        }
    }
}

// If still not resolved and we initially loaded 'user' but kiosk cookie exists, switch to kiosk and retry
if (!$force_guest && (!isset($current_user) || !is_array($current_user)) && (empty($_SESSION['user_id'])) ) {
    $haveKiosk = !empty($_COOKIE['GUMAOC_KIOSK_SESSID']);
    $haveUser  = !empty($_COOKIE['GUMAOC_USER_SESSID']);
    $currentName = (session_status()===PHP_SESSION_ACTIVE) ? session_name() : '';
    if ($haveKiosk && $currentName !== 'GUMAOC_KIOSK_SESSID') {
        @session_write_close();
        $p = __DIR__ . '/../kiosk/session_bootstrap.php';
        if (file_exists($p)) { require_once $p; }
        if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
        if (!empty($_SESSION['user_id'])) {
            $dbPath = __DIR__ . '/db_connect.php';
            if (file_exists($dbPath)) {
                require_once $dbPath;
                try {
                    if (isset($pdo)) {
                        $stmt = $pdo->prepare('SELECT * FROM residents WHERE id = ?');
                        $stmt->execute([$_SESSION['user_id']]);
                        $fetched = $stmt->fetch();
                        if ($fetched && is_array($fetched)) {
                            $current_user = $fetched;
                        }
                    }
                } catch (Throwable $e) {}
            }
        }
    }
}

$is_admin = !$force_guest && !empty($admin_view);
// Determine area for display (User vs Kiosk)
$sessionName = (!$force_guest && session_status() === PHP_SESSION_ACTIVE) ? session_name() : '';
$area = ($sessionName === 'GUMAOC_KIOSK_SESSID') ? 'kiosk' : 'user';
$display_name = 'Guest';
$initials = 'G';

if ($is_admin) {
    $display_name = 'Administrator';
    $initials = 'AD';
} elseif (isset($current_user) && is_array($current_user)) {
    $fn = trim($current_user['first_name'] ?? '');
    $ln = trim($current_user['last_name'] ?? '');
    $display_name = trim($fn . ' ' . $ln);
    if ($display_name === '') {
        // Fallbacks if names are not available
        $display_name = $current_user['email'] ?? ($_SESSION['user_name'] ?? 'User');
    }
    $initials = strtoupper(substr($fn, 0, 1) . substr($ln, 0, 1));
    if ($initials === '') {
        $initials = strtoupper(substr((string)$display_name, 0, 2));
    }
} elseif (!$force_guest && !empty($_SESSION['user_name'])) {
    // Fallback to session-provided name
    $display_name = (string)$_SESSION['user_name'];
    $initials = strtoupper(substr($display_name, 0, 2));
} elseif (!$force_guest && !empty($_COOKIE['GUMAOC_USER_NAME'])) {
    // Final fallback: use auxiliary cookie set on kiosk login
    $display_name = (string)$_COOKIE['GUMAOC_USER_NAME'];
    $initials = strtoupper(substr($display_name, 0, 2));
}
?>
<?php if (!empty($_GET['debug_session'])): ?>
<!-- DEBUG SESSION
session_name: <?php echo htmlspecialchars((string)(session_status()===PHP_SESSION_ACTIVE?session_name():'NONE')); ?>
has_user_cookie: <?php echo isset($_COOKIE['GUMAOC_USER_SESSID']) ? '1' : '0'; ?>
has_kiosk_cookie: <?php echo isset($_COOKIE['GUMAOC_KIOSK_SESSID']) ? '1' : '0'; ?>
_SESSION[user_id]: <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0; ?>
_SESSION[user_name]: <?php echo htmlspecialchars((string)($_SESSION['user_name'] ?? '')); ?>
area: <?php echo htmlspecialchars($area); ?>
-->
<?php endif; ?>
<style>
  /* Inherit header.php theme */
  .mini-nav {
    position: fixed; top: 0; left: 0; right: 0; z-index: 1100;
    background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 50%, #388e3c 100%);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 4px 20px rgba(27, 94, 32, 0.3);
  }
  .mini-nav-inner { max-width: 1200px; margin: 0 auto; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
  /* Back button styled like a white translucent chip on green nav */
  .mini-nav .back-btn { display: inline-flex; align-items: center; gap: 10px; background: rgba(255, 255, 255, 0.2); color: #fff; border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 12px; padding: 10px 14px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); transition: all 0.2s ease; }
  .mini-nav .back-btn:hover { background: rgba(255, 255, 255, 0.3); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25); }
  .mini-nav .back-btn .icon { font-size: 1.2rem; }
  .mini-nav .back-btn .label { font-size: 1rem; letter-spacing: .2px; }
  /* Profile pill matches header translucency */
  .profile-pill { display: inline-flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 999px; border: 1px solid rgba(255, 255, 255, 0.3); background: rgba(255, 255, 255, 0.2); color: #fff; font-weight: 700; }
  .profile-avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, rgba(255,255,255,0.3), rgba(255,255,255,0.2)); color: #fff; display:flex; align-items:center; justify-content:center; font-weight: 800; border: 2px solid rgba(255,255,255,0.3); box-shadow: 0 2px 6px rgba(0,0,0,0.2); }
  .profile-name { font-size: 0.95rem; color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,0.2); }
  .role-badge { padding: 4px 8px; border-radius: 999px; font-size: 0.75rem; font-weight: 800; }
  .role-user { background: rgba(255, 255, 255, 0.15); color: #e3f2fd; border: 1px solid rgba(255, 255, 255, 0.25); }
  .role-admin { background: rgba(255, 255, 255, 0.15); color: #ffcdd2; border: 1px solid rgba(255, 255, 255, 0.25); }
  /* Help button styles */
  .help-btn { display: inline-flex; align-items: center; gap: 8px; margin-left: 12px; padding: 8px 12px; border-radius: 999px; border: 1px solid rgba(255, 255, 255, 0.35); background: rgba(255, 255, 255, 0.22); color: #fff; font-weight: 800; cursor: pointer; box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: all 0.2s ease; }
  .help-btn:hover { background: rgba(255,255,255,0.3); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0,0,0,0.25); }
  .help-btn .icon { font-size: 1rem; }
  .help-btn .label { font-size: 0.95rem; letter-spacing: .2px; }
  .help-btn.assist-sent { opacity: 0.7; pointer-events: none; }
  /* Assistance Modal */
  .assist-modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 1300; display: none; align-items: center; justify-content: center; }
  .assist-modal { width: min(480px, 92vw); background: rgba(255,255,255,0.98); backdrop-filter: blur(18px); border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.35); border: 1px solid rgba(27,94,32,0.15); overflow: hidden; transform: translateY(10px); opacity: 0; transition: all .22s ease; }
  .assist-modal.show { transform: translateY(0); opacity: 1; }
  .assist-modal-header { display:flex; align-items:center; justify-content: space-between; padding: 14px 18px; background: linear-gradient(135deg, #1b5e20, #2e7d32); color: #fff; }
  .assist-modal-title { font-weight: 800; letter-spacing: .3px; }
  .assist-modal-close { background: transparent; border: none; color: #fff; font-size: 20px; cursor: pointer; }
  .assist-modal-body { padding: 18px; color: #1b5e20; }
  .assist-modal-actions { display:flex; gap: 10px; justify-content: flex-end; padding: 0 18px 18px; }
  .btn { padding: 10px 14px; border-radius: 10px; border: 1px solid transparent; font-weight: 700; cursor: pointer; }
  .btn-secondary { background: #e8f5e9; color: #1b5e20; border-color: rgba(27,94,32,0.2); }
  .btn-primary { background: linear-gradient(135deg, #1b5e20, #4caf50); color: #fff; border-color: rgba(27,94,32,0.35); }
  .assist-status { display:none; padding: 12px 14px; margin-top: 10px; border-radius: 10px; border: 1px solid rgba(27,94,32,0.2); background: rgba(232,245,233,0.7); color: #1b5e20; font-weight: 700; }
</style>
<nav class="mini-nav">
  <div class="mini-nav-inner">
    <div class="mini-nav-left">
      <button type="button" id="miniBackBtn" class="back-btn" aria-label="Go back">
        <span class="icon">←</span>
        <span class="label">Back</span>
      </button>
    </div>
    <div class="mini-nav-right">
      <div class="profile-pill">
        <div class="profile-avatar"><?php echo htmlspecialchars($initials); ?></div>
        <div class="profile-name"><?php echo htmlspecialchars($display_name); ?></div>
        <span class="role-badge <?php echo $is_admin ? 'role-admin' : 'role-user'; ?>"><?php echo $is_admin ? 'Admin' : 'User'; ?></span>
      </div>
      <a href="<?php echo $base_path; ?>pages/report.php" class="help-btn" style="text-decoration: none;" aria-label="Report an issue" title="Report an issue">
        <span class="icon">📝</span>
        <span class="label">Report Incident</span>
      </a>
      <button type="button" id="miniHelpBtn" class="help-btn" aria-label="Call for assistance" title="Call for staff assistance">
        <span class="icon">🆘</span>
        <span class="label">Need Help</span>
      </button>
    </div>
  </div>
</nav>
<!-- Assistance Modal Markup -->
<div class="assist-modal-backdrop" id="assistBackdrop" role="dialog" aria-modal="true" aria-hidden="true">
  <div class="assist-modal" id="assistModal">
    <div class="assist-modal-header">
      <div class="assist-modal-title">Request Assistance</div>
      <button class="assist-modal-close" id="assistCloseBtn" aria-label="Close">×</button>
    </div>
    <div class="assist-modal-body">
      <div id="assistMessage">Do you want to notify staff for assistance on this station?</div>
      <div class="assist-status" id="assistStatus"></div>
    </div>
    <div class="assist-modal-actions">
      <button class="btn btn-secondary" id="assistCancelBtn">Cancel</button>
      <button class="btn btn-primary" id="assistConfirmBtn">Request Now</button>
    </div>
  </div>
</div>
<script>
(function(){
  var backBtn = document.getElementById('miniBackBtn');
  function hasHistory(){ return (window.history && window.history.length > 1); }
  if (backBtn) {
    backBtn.addEventListener('click', function(){
      try {
        if (hasHistory()) {
          window.history.back();
        } else if (document.referrer) {
          window.location.href = document.referrer;
        } else {
          // No history and no referrer: do nothing to avoid unintended redirects/logouts
        }
      } catch(e) {
        // Swallow any errors; avoid redirecting to any page that might log the user out
      }
    });
  }

  // Assistance button behavior
  var helpBtn = document.getElementById('miniHelpBtn');
  var cooldownMs = 30000; // 30s cooldown to prevent spam
  var lastKey = 'assist_last_time';
  var assistEndpoint = <?php echo json_encode($base_path . 'assist-request.php'); ?>;
  var assistPayloadBase = {
    page: window.location.pathname + window.location.search,
    user_id: <?php echo json_encode($force_guest ? null : ($_SESSION['user_id'] ?? null)); ?>,
    user_name: <?php echo json_encode($display_name); ?>,
    is_admin: <?php echo json_encode($is_admin); ?>
  };

  // Modal elements
  var backdrop = document.getElementById('assistBackdrop');
  var modal = document.getElementById('assistModal');
  var btnClose = document.getElementById('assistCloseBtn');
  var btnCancel = document.getElementById('assistCancelBtn');
  var btnConfirm = document.getElementById('assistConfirmBtn');
  var statusBox = document.getElementById('assistStatus');
  var messageBox = document.getElementById('assistMessage');

  function withinCooldown(){
    try {
      var last = parseInt(localStorage.getItem(lastKey) || '0', 10);
      return (Date.now() - last) < cooldownMs;
    } catch(e){ return false; }
  }

  function markCooldown(){
    try { localStorage.setItem(lastKey, String(Date.now())); } catch(e){}
  }

  function openAssistModal(){
    messageBox.textContent = 'Do you want to notify staff for assistance on this station?';
    statusBox.style.display = 'none';
    btnConfirm.disabled = false;
    backdrop.style.display = 'flex';
    requestAnimationFrame(function(){ modal.classList.add('show'); });
  }

  function closeAssistModal(){
    modal.classList.remove('show');
    setTimeout(function(){ backdrop.style.display = 'none'; }, 180);
  }

  function notifyAssistance(){
    // Try sendBeacon first (non-blocking background)
    try {
      var assistPayload = Object.assign({ ts: Date.now() }, assistPayloadBase);
      var data = new Blob([JSON.stringify(assistPayload)], { type: 'application/json' });
      if (navigator.sendBeacon && navigator.sendBeacon(assistEndpoint, data)) {
        return Promise.resolve('beacon');
      }
    } catch(e){}
    // Fallback to fetch
    var assistPayload = Object.assign({ ts: Date.now() }, assistPayloadBase);
    return fetch(assistEndpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(assistPayload),
      keepalive: true
    }).then(function(r){ return r.ok ? 'ok' : Promise.reject('http'); });
  }

  if (helpBtn) {
    helpBtn.addEventListener('click', function(){
      openAssistModal();
      if (withinCooldown()) {
        statusBox.style.display = 'block';
        statusBox.textContent = 'Assistance was recently requested. Please wait a moment before trying again.';
        btnConfirm.disabled = true;
      }
    });
  }

  // Wire modal buttons
  if (btnClose) btnClose.addEventListener('click', closeAssistModal);
  if (btnCancel) btnCancel.addEventListener('click', closeAssistModal);
  if (btnConfirm) btnConfirm.addEventListener('click', function(){
    btnConfirm.disabled = true;
    helpBtn.classList.add('assist-sent');
    markCooldown();
    statusBox.style.display = 'block';
    statusBox.textContent = 'Requesting assistance…';
    notifyAssistance()
      .then(function(){ statusBox.textContent = 'Assistance requested. A staff member has been notified.'; })
      .catch(function(){ statusBox.textContent = 'Request noted locally. Network notification may have failed.'; })
      .finally(function(){
        setTimeout(function(){
          closeAssistModal();
          setTimeout(function(){ helpBtn.classList.remove('assist-sent'); }, cooldownMs);
        }, 1200);
        try { if (navigator.vibrate) navigator.vibrate([60, 40, 60]); } catch(e){}
      });
  });
})();
</script>
