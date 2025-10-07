<?php
// Admin Mini Navigation (fixed): Back button (left) + Admin identity and quick links (right)
// Expects $base_path (optional). Falls back to '../' if not set.
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($base_path)) { $base_path = '../'; }

// Determine admin display name
$admin_name = 'Admin';
if (!empty($_SESSION['admin_name'])) {
    $admin_name = (string)$_SESSION['admin_name'];
} elseif (!empty($_SESSION['user_name']) && !empty($_SESSION['is_admin'])) {
    // fallback if your system uses user_name + is_admin flag
    $admin_name = (string)$_SESSION['user_name'];
}
$initials = strtoupper(substr($admin_name, 0, 2));

// Determine if current page is the admin dashboard to optionally hide Back button
$current_script = basename($_SERVER['PHP_SELF'] ?? '');
$is_admin_dashboard = ($current_script === 'dashboard.php');
?>
<style>
  .admin-mini-nav { position: fixed; top: 0; left: 0; right: 0; z-index: 1200; background: #ffffff; border-bottom: 1px solid #e5e7eb; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
  .admin-mini-inner { max-width: 1400px; margin: 0 auto; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
  .admin-back-btn { display: inline-flex; align-items: center; gap: 10px; background: #f3f4f6; color: #1f2937; border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px 14px; font-weight: 700; cursor: pointer; transition: all 0.2s ease; }
  .admin-back-btn:hover { background: #e5e7eb; transform: translateY(-1px); }
  .admin-back-btn .icon { font-size: 1.1rem; }
  .admin-back-btn .label { font-size: 0.95rem; }

  .admin-right { display: inline-flex; align-items: center; gap: 10px; }
  .admin-quick-link { text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; border-radius: 999px; font-weight: 700; }
  .admin-quick-link:hover { background: #d1fae5; }
  .admin-pill { display: inline-flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 999px; border: 1px solid #e5e7eb; background: #ffffff; color: #111827; }
  .admin-avatar { width: 32px; height: 32px; border-radius: 50%; background: #10b981; color: #fff; display:flex; align-items:center; justify-content:center; font-weight: 800; }
  .admin-name { font-size: 0.95rem; color: #111827; font-weight: 700; }
  .admin-role { font-size: 0.75rem; padding: 4px 8px; border-radius: 999px; background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; font-weight: 700; }
</style>
<nav class="admin-mini-nav">
  <div class="admin-mini-inner">
    <div class="admin-left">
      <?php if (!$is_admin_dashboard): ?>
      <button type="button" id="adminMiniBack" class="admin-back-btn" aria-label="Go back">
        <span class="icon">←</span>
        <span class="label">Back</span>
      </button>
      <?php endif; ?>
    </div>
    <div class="admin-right">
      <div class="admin-pill">
        <div class="admin-avatar"><?php echo htmlspecialchars($initials); ?></div>
        <div class="admin-name"><?php echo htmlspecialchars($admin_name); ?></div>
        <?php $role_label = isset($_SESSION['admin_role']) && $_SESSION['admin_role'] ? ucfirst($_SESSION['admin_role']) : 'Admin'; ?>
        <span class="admin-role"><?php echo htmlspecialchars($role_label); ?></span>
      </div>
      <a href="<?php echo $base_path; ?>logout.php" class="admin-quick-link" title="Logout">🚪 Logout</a>
    </div>
  </div>
</nav>
<script>
(function(){
  var back = document.getElementById('adminMiniBack');

  function parentDirectoryHref() {
    try {
      var loc = window.location;
      var path = loc.pathname || '/';
      // Normalize slashes
      path = path.replace(/\\/g, '/');

      // CASE 1: URL points to a file -> go to its containing directory
      if (!path.endsWith('/')) {
        var lastSlash = path.lastIndexOf('/');
        var dir = path.substring(0, lastSlash + 1);
        // If that directory is /admin/, default to dashboard
        if (/\/admin\/$/i.test(dir)) return dir + 'dashboard.php';
        return dir || '<?php echo $base_path; ?>admin/dashboard.php';
      }

      // CASE 2: URL already ends with a directory -> go one level up
      if (path === '/' || path === '') {
        return '<?php echo $base_path; ?>admin/dashboard.php';
      }
      var trimmed = path.slice(0, -1); // remove trailing slash
      var parentSlash = trimmed.lastIndexOf('/');
      var parent = parentSlash >= 0 ? trimmed.substring(0, parentSlash + 1) : '/';
      if (/\/admin\/$/i.test(parent)) return parent + 'dashboard.php';
      return parent || '<?php echo $base_path; ?>admin/dashboard.php';
    } catch (e) {
      return '<?php echo $base_path; ?>admin/dashboard.php';
    }
  }

  if (back) back.addEventListener('click', function(){
    try {
      var target = parentDirectoryHref();
      window.location.href = target;
    } catch(e){
      window.location.href = '<?php echo $base_path; ?>admin/dashboard.php';
    }
  });
})();
</script>
