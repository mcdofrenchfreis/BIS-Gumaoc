<?php
// Lightweight top navigation with Back button (left) and Profile (right)
// Expects (if available): $base_path, $admin_view, $current_user
// Optional: $force_guest = true to disable any user/admin session resolution and always show Guest
if (!isset($base_path)) { $base_path = '../'; }
if (!isset($force_guest)) { $force_guest = false; }

// Ensure the correct area session is loaded (user only) without creating a default session
// Skip entirely when forcing guest mode
if (!$force_guest && session_status() === PHP_SESSION_NONE) {
    // Always load user session bootstrap
    $p = __DIR__ . '/../user/session_bootstrap.php';
    if (file_exists($p)) { require_once $p; }
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

if (!$force_guest && (!isset($current_user) || !is_array($current_user)) && (empty($_SESSION['user_id'])) ) {
    $haveUser  = !empty($_COOKIE['GUMAOC_USER_SESSID']);
}

$is_admin = !$force_guest && !empty($admin_view);
// Always use user area now
$area = 'user';
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
    // Final fallback: use auxiliary cookie set on login
    $display_name = (string)$_COOKIE['GUMAOC_USER_NAME'];
    $initials = strtoupper(substr($display_name, 0, 2));
}
?>
<?php if (!empty($_GET['debug_session'])): ?>
<!-- DEBUG SESSION
session_name: <?php echo htmlspecialchars((string)(session_status()===PHP_SESSION_ACTIVE?session_name():'NONE')); ?>
has_user_cookie: <?php echo isset($_COOKIE['GUMAOC_USER_SESSID']) ? '1' : '0'; ?>
_SESSION[user_id]: <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0; ?>
_SESSION[user_name]: <?php echo htmlspecialchars((string)($_SESSION['user_name'] ?? '')); ?>
area: <?php echo htmlspecialchars($area); ?>
-->
<?php endif; ?>
<style>
  .mini-nav {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1100;
    background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 50%, #388e3c 100%);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 4px 20px rgba(27, 94, 32, 0.3);
  }
  .mini-nav-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }
  .mini-brand {
    color: #ffffff;
    font-size: 1.05rem;
    font-weight: 800;
    letter-spacing: 0.3px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
  }
  .mini-login-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.32);
    border-radius: 12px;
    padding: 9px 14px;
    font-weight: 700;
    transition: all 0.2s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  }
  .mini-login-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.24);
    color: #fff;
  }
</style>
<nav class="mini-nav">
  <div class="mini-nav-inner">
    <div class="mini-brand">Barangay Gumaoc</div>
    <a href="<?php echo $base_path; ?>login.php" class="mini-login-btn">Login Page</a>
  </div>
</nav>
