<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/BackupUtil.php';

// Require admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$BACKUP_DIR = realpath(__DIR__ . '/../assets') . DIRECTORY_SEPARATOR . 'backups';
if ($BACKUP_DIR === false) {
    $BACKUP_DIR = __DIR__ . '/../assets/backups';
}
BackupUtil::ensureDir($BACKUP_DIR);

$SETTINGS_FILE = $BACKUP_DIR . DIRECTORY_SEPARATOR . 'settings.json';
$settings = [
    'auto_enabled' => true,
    'frequency' => 'daily', // daily|weekly|monthly
    'retention' => 10,
    'last_run' => null,
];
if (is_file($SETTINGS_FILE)) {
    $loaded = json_decode((string)file_get_contents($SETTINGS_FILE), true);
    if (is_array($loaded)) { $settings = array_merge($settings, $loaded); }
}

function save_settings($file, $settings) {
    @file_put_contents($file, json_encode($settings, JSON_PRETTY_PRINT));
}

// Handle actions
$flash = ['type' => null, 'message' => null];
try {
    if (($_POST['action'] ?? '') === 'run_backup') {
        $res = BackupUtil::exportDatabase($pdo, $BACKUP_DIR, ['prefix' => 'brgy_']);
        // Create paired readable TXT using same db and timestamp
        try { BackupUtil::generateReadableTextDump($pdo, $BACKUP_DIR, $res['db'], $res['timestamp']); } catch (Throwable $e) {}
        $flash = ['type' => 'success', 'message' => 'Backup created: ' . basename($res['file']) . ' + readable summary'];
        // Enforce retention
        $keep = max(1, (int)$settings['retention']);
        $list = BackupUtil::listBackups($BACKUP_DIR);
        if (count($list) > $keep) {
            for ($i = $keep; $i < count($list); $i++) {
                @unlink($list[$i]['path']);
            }
        }
        // Update last_run
        $settings['last_run'] = date('c');
        save_settings($SETTINGS_FILE, $settings);
    } elseif (($_POST['action'] ?? '') === 'delete_backup' && isset($_POST['file'])) {
        // Allow deletion of .sql and .txt backups (safe basename enforcement)
        $safe = basename((string)$_POST['file']);
        $ext = strtolower(pathinfo($safe, PATHINFO_EXTENSION));
        $allowed = ['sql','txt'];
        $path = rtrim($BACKUP_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safe;
        if (in_array($ext, $allowed, true) && is_file($path)) {
            if (@unlink($path)) {
                $flash = ['type' => 'success', 'message' => 'Backup deleted.'];
            } else {
                $flash = ['type' => 'error', 'message' => 'Failed to delete backup.'];
            }
        } else {
            $flash = ['type' => 'error', 'message' => 'Invalid file.'];
        }
    } elseif (($_POST['action'] ?? '') === 'save_settings') {
        $settings['auto_enabled'] = isset($_POST['auto_enabled']);
        $settings['frequency'] = in_array(($_POST['frequency'] ?? 'daily'), ['daily','weekly','monthly']) ? $_POST['frequency'] : 'daily';
        $settings['retention'] = max(1, (int)($_POST['retention'] ?? 10));
        save_settings($SETTINGS_FILE, $settings);
        $flash = ['type' => 'success', 'message' => 'Settings saved.'];
    } elseif (($_POST['action'] ?? '') === 'download_backup' && isset($_POST['file'])) {
        // Stream download securely from protected directory
        $safe = basename((string)$_POST['file']);
        $path = rtrim($BACKUP_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safe;
        if (is_file($path)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = ($ext === 'sql') ? 'application/sql' : 'text/plain';
            while (ob_get_level()) { ob_end_clean(); }
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $mime);
            header('Content-Disposition: attachment; filename="' . $safe . '"');
            header('Content-Length: ' . filesize($path));
            header('Cache-Control: no-store');
            readfile($path);
            exit;
        } else {
            $flash = ['type' => 'error', 'message' => 'File not found.'];
        }
    } elseif (($_POST['action'] ?? '') === 'download_bundle' && isset($_POST['file'])) {
        // Create a ZIP containing the selected SQL and its matching readable TXT
        $safe = basename((string)$_POST['file']);
        $sqlPath = rtrim($BACKUP_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safe;
        if (!is_file($sqlPath)) { $flash = ['type'=>'error','message'=>'SQL file not found.']; }
        else {
            // Derive matching readable filename by timestamp and db name: brgy_<db>_YYYYMMDD_HHMMSS.sql -> readable_<db>_YYYYMMDD_HHMMSS.txt
            $readablePath = '';
            if (preg_match('/^brgy_(.+?)_(\d{8}_\d{6})\.sql$/', $safe, $m)) {
                $db = $m[1]; $ts = $m[2];
                $candidate = 'readable_' . $db . '_' . $ts . '.txt';
                $p = rtrim($BACKUP_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $candidate;
                if (is_file($p)) { $readablePath = $p; }
                else {
                    // Generate on the fly using same db and timestamp
                    try { $readablePath = BackupUtil::generateReadableTextDump($pdo, $BACKUP_DIR, $db, $ts); }
                    catch (Throwable $e) { $readablePath = ''; }
                }
            }
            $zipName = 'backup_set_' . date('Ymd_His') . '.zip';
            $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipName;
            $ok = false;
            if (class_exists('ZipArchive')) {
                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE|ZipArchive::OVERWRITE) === true) {
                    $ok = $zip->addFile($sqlPath, basename($sqlPath));
                    if ($readablePath && is_file($readablePath)) {
                        $zip->addFile($readablePath, basename($readablePath));
                    }
                    $zip->close();
                }
            }
            if ($ok && is_file($zipPath)) {
                while (ob_get_level()) { ob_end_clean(); }
                header('Content-Description: File Transfer');
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . basename($zipPath) . '"');
                header('Content-Length: ' . filesize($zipPath));
                header('Cache-Control: no-store');
                readfile($zipPath);
                @unlink($zipPath);
                exit;
            } else {
                // Fallback: just send SQL
                while (ob_get_level()) { ob_end_clean(); }
                header('Content-Description: File Transfer');
                header('Content-Type: application/sql');
                header('Content-Disposition: attachment; filename="' . basename($sqlPath) . '"');
                header('Content-Length: ' . filesize($sqlPath));
                header('Cache-Control: no-store');
                readfile($sqlPath);
                exit;
            }
        }
    }
} catch (Throwable $e) {
    $flash = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
}

// Pseudo auto-backup trigger (executed when page is visited)
try {
    if (!headers_sent() && $settings['auto_enabled']) {
        $now = time();
        $last = $settings['last_run'] ? strtotime($settings['last_run']) : 0;
        $due = false;
        switch ($settings['frequency']) {
            case 'weekly': $due = ($now - $last) >= 7*24*3600; break;
            case 'monthly': $due = ($now - $last) >= 30*24*3600; break;
            default: $due = ($now - $last) >= 24*3600; // daily
        }
        if ($due) {
            $resAuto = BackupUtil::exportDatabase($pdo, $BACKUP_DIR, ['prefix' => 'brgy_']);
            try { BackupUtil::generateReadableTextDump($pdo, $BACKUP_DIR, $resAuto['db'], $resAuto['timestamp']); } catch (Throwable $e) {}
            // retention for SQL files
            $keep = max(1, (int)$settings['retention']);
            $list = BackupUtil::listBackups($BACKUP_DIR);
            if (count($list) > $keep) {
                for ($i = $keep; $i < count($list); $i++) { @unlink($list[$i]['path']); }
            }
            $settings['last_run'] = date('c');
            save_settings($SETTINGS_FILE, $settings);
        }
    }
} catch (Throwable $e) {
    // ignore auto backup errors in UI flow
}

$backups = BackupUtil::listBackups($BACKUP_DIR);
// Readable backups (.txt)
$readable_backups = [];
foreach (glob(rtrim($BACKUP_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'readable_*.txt') ?: [] as $p) {
    $readable_backups[] = [
        'name' => basename($p),
        'path' => $p,
        'size' => filesize($p) ?: 0,
        'mtime' => filemtime($p) ?: 0,
    ];
}
usort($readable_backups, function($a,$b){ return $b['mtime'] <=> $a['mtime']; });
$last_run_human = $settings['last_run'] ? date('M j, Y g:i A', strtotime($settings['last_run'])) : 'Never';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Backup - Admin</title>
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    .admin-container { max-width: 1200px; margin: 0 auto; padding: 2rem; padding-top: 90px; background: #f8f9fa; min-height: 100vh; }
    .card { background:#fff; border-radius:12px; padding:1.25rem; box-shadow:0 4px 20px rgba(0,0,0,0.08); margin-bottom:1rem; }
    .card h2 { margin:0 0 0.75rem 0; color:#2e7d32; font-size:1.2rem; }
    .row { display:flex; gap:1rem; flex-wrap:wrap; }
    .col { flex:1 1 360px; }
    .admin-btn { display:inline-flex; align-items:center; justify-content:center; padding:0.6rem 1.2rem; background: linear-gradient(135deg, #4CAF50, #45a049); color:#fff; text-decoration:none; border-radius:8px; font-weight:600; border:none; cursor:pointer; transition:.2s; }
    .admin-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(76,175,80,.3); }
    .danger { background: linear-gradient(135deg, #dc3545, #c82333); }
    .secondary { background: linear-gradient(135deg, #0d6efd, #0b5ed7); }
    .muted { color:#666; font-size:.9rem; }
    table { width:100%; border-collapse: collapse; }
    th, td { padding:.75rem; border-bottom:1px solid #eee; text-align:left; }
    th { background:#f8f9fa; color:#2e7d32; }
    .badge { display:inline-block; padding:0.3rem 0.6rem; border-radius:12px; background:#e8f5e8; color:#2e7d32; font-weight:600; font-size:.8rem; }
    .hint { font-size:.85rem; color:#666; }
    .form-row { display:flex; gap:.75rem; align-items:center; flex-wrap:wrap; }
    .form-row input[type="number"], .form-row select { padding:.45rem .6rem; border:2px solid #e9ecef; border-radius:6px; font-size:.95rem; }
    .action-btns { display:flex; gap:.4rem; flex-wrap:wrap; }
  </style>
</head>
<body>
  <?php $base_path = '../'; include __DIR__ . '/../includes/admin_mini_nav.php'; ?>
  <div class="admin-container">
    <div class="card">
      <h2>📦 Barangay Database Backup</h2>
      <p class="muted">Last automatic backup: <strong><?php echo htmlspecialchars($last_run_human); ?></strong></p>
      <?php if ($flash['message']): ?>
        <div class="card" style="background:#e8f5e9; border:1px solid #c8e6c9; padding:.75rem;">✅ <?php echo htmlspecialchars($flash['message']); ?></div>
      <?php endif; ?>
      <form method="post" class="form-row">
        <input type="hidden" name="action" value="run_backup">
        <button class="admin-btn" type="submit">💾 Run Backup Now</button>
      </form>
      <p class="hint" style="margin-top:.5rem;">Each SQL backup automatically creates a readable TXT summary.</p>
    </div>

    <div class="row">
      <div class="col">
        <div class="card">
          <h2>⚙️ Settings</h2>
          <form method="post" class="form">
            <input type="hidden" name="action" value="save_settings">
            <div class="form-row">
              <label><input type="checkbox" name="auto_enabled" <?php echo $settings['auto_enabled'] ? 'checked' : ''; ?>> Enable Automatic Backup</label>
            </div>
            <div class="form-row">
              <label for="frequency">Frequency</label>
              <select id="frequency" name="frequency">
                <option value="daily" <?php echo $settings['frequency']==='daily'?'selected':''; ?>>Daily</option>
                <option value="weekly" <?php echo $settings['frequency']==='weekly'?'selected':''; ?>>Weekly</option>
                <option value="monthly" <?php echo $settings['frequency']==='monthly'?'selected':''; ?>>Monthly</option>
              </select>
            </div>
            <div class="form-row">
              <label for="retention">Retention (most recent backups to keep)</label>
              <input id="retention" type="number" name="retention" min="1" value="<?php echo (int)$settings['retention']; ?>">
            </div>
            <div class="form-row">
              <button class="admin-btn" type="submit">💾 Save Settings</button>
              <span class="hint">Backups are stored in <code>assets/backups/</code>. Protect this directory.</span>
            </div>
          </form>
        </div>
      </div>

      <div class="col">
        <div class="card">
          <h2>📂 Backup Files</h2>
          <?php if (empty($backups)): ?>
            <p class="muted">No backups yet.</p>
          <?php else: ?>
            <table>
              <thead>
                <tr>
                  <th>File</th>
                  <th>Size</th>
                  <th>Created</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($backups as $b): ?>
                  <tr>
                    <td><span class="badge"><?php echo htmlspecialchars($b['name']); ?></span></td>
                    <td><?php echo number_format($b['size']/1024, 2); ?> KB</td>
                    <td><?php echo date('M j, Y g:i A', $b['mtime']); ?></td>
                    <td>
                      <div class="action-btns">
                        <form method="post">
                          <input type="hidden" name="action" value="download_backup">
                          <input type="hidden" name="file" value="<?php echo htmlspecialchars($b['name']); ?>">
                          <button class="admin-btn secondary" type="submit" title="Download SQL only">⬇️ Download SQL</button>
                        </form>
                        <form method="post">
                          <input type="hidden" name="action" value="download_bundle">
                          <input type="hidden" name="file" value="<?php echo htmlspecialchars($b['name']); ?>">
                          <button class="admin-btn" type="submit" title="Download SQL + Readable TXT as ZIP">📦 Download Set (ZIP)</button>
                        </form>
                        <form method="post" onsubmit="return confirm('Delete this backup?');">
                          <input type="hidden" name="action" value="delete_backup">
                          <input type="hidden" name="file" value="<?php echo htmlspecialchars($b['name']); ?>">
                          <button class="admin-btn danger" type="submit">🗑️ Delete</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col">
        <div class="card">
          <h2>📘 Readable Backups</h2>
          <?php if (empty($readable_backups)): ?>
            <p class="muted">No readable backups yet.</p>
          <?php else: ?>
            <table>
              <thead>
                <tr>
                  <th>File</th>
                  <th>Size</th>
                  <th>Created</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($readable_backups as $b): ?>
                  <tr>
                    <td><span class="badge"><?php echo htmlspecialchars($b['name']); ?></span></td>
                    <td><?php echo number_format($b['size']/1024, 2); ?> KB</td>
                    <td><?php echo date('M j, Y g:i A', $b['mtime']); ?></td>
                    <td>
                      <div class="action-btns">
                        <form method="post">
                          <input type="hidden" name="action" value="download_backup">
                          <input type="hidden" name="file" value="<?php echo htmlspecialchars($b['name']); ?>">
                          <button class="admin-btn secondary" type="submit" title="Download readable TXT">⬇️ Download TXT</button>
                        </form>
                        <form method="post" onsubmit="return confirm('Delete this backup?');">
                          <input type="hidden" name="action" value="delete_backup">
                          <input type="hidden" name="file" value="<?php echo htmlspecialchars($b['name']); ?>">
                          <button class="admin-btn danger" type="submit">🗑️ Delete</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
