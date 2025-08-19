<?php
session_start();
include '../includes/db_connect.php';
include '../includes/AdminLogger.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$logger = new AdminLogger($pdo);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_update':
                $update_id = (int)$_POST['update_id'];
                $title = htmlspecialchars($_POST['title']);
                $description = htmlspecialchars($_POST['description']);
                $badge_text = htmlspecialchars($_POST['badge_text']);
                $badge_type = htmlspecialchars($_POST['badge_type']);
                $date = htmlspecialchars($_POST['date']);
                $status = htmlspecialchars($_POST['status']);
                $is_priority = isset($_POST['is_priority']) ? 1 : 0;
                
                // Get old data for logging
                $old_data_stmt = $pdo->prepare("SELECT * FROM updates WHERE id = ?");
                $old_data_stmt->execute([$update_id]);
                $old_data = $old_data_stmt->fetch();
                
                $stmt = $pdo->prepare("UPDATE updates SET title = ?, description = ?, badge_text = ?, badge_type = ?, date = ?, status = ?, is_priority = ? WHERE id = ?");
                if ($stmt->execute([$title, $description, $badge_text, $badge_type, $date, $status, $is_priority, $update_id])) {
                    // Log the update
                    $logger->log(
                        'content_update',
                        'community_update',
                        "Updated community update ID #{$update_id}: '{$title}'",
                        $update_id,
                        [
                            'old_title' => $old_data['title'],
                            'new_title' => $title,
                            'old_status' => $old_data['status'],
                            'new_status' => $status,
                            'old_priority' => (bool)$old_data['is_priority'],
                            'new_priority' => (bool)$is_priority,
                            'badge_type' => $badge_type
                        ]
                    );
                    
                    $_SESSION['success'] = "Update modified successfully!";
                } else {
                    $_SESSION['error'] = "Failed to update.";
                    $logger->log('error', 'community_update', "Failed to update community update ID #{$update_id}", $update_id);
                }
                break;
                
            case 'add_update':
                $title = htmlspecialchars($_POST['title']);
                $description = htmlspecialchars($_POST['description']);
                $badge_text = htmlspecialchars($_POST['badge_text']);
                $badge_type = htmlspecialchars($_POST['badge_type']);
                $date = htmlspecialchars($_POST['date']);
                $status = htmlspecialchars($_POST['status']);
                $is_priority = isset($_POST['is_priority']) ? 1 : 0;
                
                $stmt = $pdo->prepare("INSERT INTO updates (title, description, badge_text, badge_type, date, status, is_priority) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$title, $description, $badge_text, $badge_type, $date, $status, $is_priority])) {
                    $new_id = $pdo->lastInsertId();
                    
                    // Log the addition
                    $logger->log(
                        'content_create',
                        'community_update',
                        "Created new community update: '{$title}'",
                        $new_id,
                        [
                            'title' => $title,
                            'badge_type' => $badge_type,
                            'status' => $status,
                            'is_priority' => (bool)$is_priority,
                            'date' => $date
                        ]
                    );
                    
                    $_SESSION['success'] = "Update added successfully!";
                } else {
                    $_SESSION['error'] = "Failed to add update.";
                    $logger->log('error', 'community_update', "Failed to create new community update: '{$title}'");
                }
                break;
                
            case 'delete_update':
                $update_id = (int)$_POST['update_id'];
                
                // Get data before deletion for logging
                $delete_data_stmt = $pdo->prepare("SELECT * FROM updates WHERE id = ?");
                $delete_data_stmt->execute([$update_id]);
                $delete_data = $delete_data_stmt->fetch();
                
                $stmt = $pdo->prepare("DELETE FROM updates WHERE id = ?");
                if ($stmt->execute([$update_id])) {
                    // Log the deletion
                    $logger->log(
                        'content_delete',
                        'community_update',
                        "Deleted community update ID #{$update_id}: '{$delete_data['title']}'",
                        $update_id,
                        [
                            'deleted_title' => $delete_data['title'],
                            'deleted_status' => $delete_data['status'],
                            'was_priority' => (bool)$delete_data['is_priority'],
                            'badge_type' => $delete_data['badge_type']
                        ]
                    );
                    
                    $_SESSION['success'] = "Update deleted successfully!";
                } else {
                    $_SESSION['error'] = "Failed to delete update.";
                    $logger->log('error', 'community_update', "Failed to delete community update ID #{$update_id}", $update_id);
                }
                break;
        }
        header('Location: manage-updates.php');
        exit;
    }
}

// Log page view
$logger->log('page_view', 'admin_panel', 'Viewed manage updates admin page');

// Create updates table if it doesn't exist
$pdo->exec("
    CREATE TABLE IF NOT EXISTS updates (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        badge_text VARCHAR(50) NOT NULL,
        badge_type ENUM('important', 'new', 'community', 'info') DEFAULT 'info',
        date VARCHAR(50) NOT NULL,
        status VARCHAR(50) NOT NULL,
        is_priority BOOLEAN DEFAULT FALSE,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
");

// Insert default updates if table is empty
$count = $pdo->query("SELECT COUNT(*) FROM updates")->fetchColumn();
if ($count == 0) {
    $default_updates = [
        [
            'COVID-19 Vaccination Drive',
            'New vaccination schedule available. Free vaccination for all residents. Register online to secure your slot.',
            'Important',
            'important',
            'July 28, 2025',
            '🟢 Active',
            1
        ],
        [
            'Enhanced E-Services Launch',
            'Our improved digital platform now offers faster processing, better security, and mobile optimization.',
            'New',
            'new',
            'July 25, 2025',
            '🟢 Live',
            0
        ],
        [
            'Town Fiesta 2025',
            'Join us for our annual town celebration. Cultural shows, local food, and community activities for everyone.',
            'Community',
            'community',
            'August 15, 2025',
            '🟡 Upcoming',
            0
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO updates (title, description, badge_text, badge_type, date, status, is_priority) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($default_updates as $update) {
        $stmt->execute($update);
    }
}

// Fetch all updates
$updates = $pdo->query("SELECT * FROM updates ORDER BY is_priority DESC, display_order, id")->fetchAll(PDO::FETCH_ASSOC);

$base_path = '../';
$page_title = 'Manage Updates - Admin Panel';
$header_title = 'Manage Latest Updates';
$header_subtitle = 'Configure and manage community updates';

include '../includes/header.php';
?>

<body>
    <div class="admin-container">
        <div class="admin-header">
            <div>
                <h1>📢 Manage Latest Updates</h1>
                <p>Configure community announcements and news updates</p>
            </div>
            <div class="admin-nav-buttons">
                <a href="dashboard.php" class="admin-btn">📊 Dashboard</a>
                <a href="manage-services.php" class="admin-btn">⚙️ Services</a>
                <a href="../index.php" class="admin-btn">🏠 View Site</a>
            </div>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <strong>Success!</strong> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <strong>Error!</strong> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Add New Update -->
        <div class="admin-section">
            <h2>➕ Add New Update</h2>
            <form method="POST" class="update-form">
                <input type="hidden" name="action" value="add_update">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Update Title</label>
                        <input type="text" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Badge Text</label>
                        <input type="text" name="badge_text" placeholder="Important, New, etc." required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Description</label>
                        <textarea name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Badge Type</label>
                        <select name="badge_type" required>
                            <option value="info">Info (Orange)</option>
                            <option value="important">Important (Red)</option>
                            <option value="new">New (Blue)</option>
                            <option value="community">Community (Green)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Date</label>
                        <input type="text" name="date" placeholder="July 28, 2025" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <input type="text" name="status" placeholder="🟢 Active, 🟡 Upcoming, etc." required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_priority">
                            Priority Update (highlighted display)
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="admin-btn btn-primary">Add Update</button>
            </form>
        </div>

        <!-- Existing Updates -->
        <div class="admin-section">
            <h2>📋 Existing Updates</h2>
            <div class="updates-list">
                <?php foreach ($updates as $update): ?>
                    <div class="update-edit-card <?php echo $update['is_priority'] ? 'priority-card' : ''; ?>">
                        <div class="update-preview">
                            <div class="update-info">
                                <div class="update-header">
                                    <div class="update-icon">📢</div>
                                    <h3><?php echo htmlspecialchars($update['title']); ?></h3>
                                    <span class="badge badge-<?php echo $update['badge_type']; ?>"><?php echo htmlspecialchars($update['badge_text']); ?></span>
                                </div>
                                <p><?php echo htmlspecialchars($update['description']); ?></p>
                                <div class="update-meta">
                                    <span>📅 <?php echo htmlspecialchars($update['date']); ?></span>
                                    <span><?php echo htmlspecialchars($update['status']); ?></span>
                                </div>
                                <?php if ($update['is_priority']): ?>
                                    <span class="priority-badge">⚠️ Priority Update</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="update-actions">
                            <button class="admin-btn btn-small" onclick="editUpdate(<?php echo $update['id']; ?>)">✏️ Edit</button>
                            <button class="admin-btn btn-danger btn-small" onclick="deleteUpdate(<?php echo $update['id']; ?>)">🗑️ Delete</button>
                        </div>
                        
                        <!-- Edit Form (Hidden by default) -->
                        <div id="edit-form-<?php echo $update['id']; ?>" class="edit-form" style="display: none;">
                            <form method="POST" class="update-form">
                                <input type="hidden" name="action" value="update_update">
                                <input type="hidden" name="update_id" value="<?php echo $update['id']; ?>">
                                
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>Update Title</label>
                                        <input type="text" name="title" value="<?php echo htmlspecialchars($update['title']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Badge Text</label>
                                        <input type="text" name="badge_text" value="<?php echo htmlspecialchars($update['badge_text']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group full-width">
                                        <label>Description</label>
                                        <textarea name="description" rows="3" required><?php echo htmlspecialchars($update['description']); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Badge Type</label>
                                        <select name="badge_type" required>
                                            <option value="info" <?php echo $update['badge_type'] === 'info' ? 'selected' : ''; ?>>Info (Orange)</option>
                                            <option value="important" <?php echo $update['badge_type'] === 'important' ? 'selected' : ''; ?>>Important (Red)</option>
                                            <option value="new" <?php echo $update['badge_type'] === 'new' ? 'selected' : ''; ?>>New (Blue)</option>
                                            <option value="community" <?php echo $update['badge_type'] === 'community' ? 'selected' : ''; ?>>Community (Green)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Date</label>
                                        <input type="text" name="date" value="<?php echo htmlspecialchars($update['date']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Status</label>
                                        <input type="text" name="status" value="<?php echo htmlspecialchars($update['status']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group full-width">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="is_priority" <?php echo $update['is_priority'] ? 'checked' : ''; ?>>
                                            Priority Update (highlighted display)
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="admin-btn btn-primary">Update</button>
                                    <button type="button" class="admin-btn" onclick="cancelEdit(<?php echo $update['id']; ?>)">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-nav-buttons {
            display: flex;
            gap: 1rem;
        }
        
        .admin-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .admin-section h2 {
            color: #2e7d32;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }
        
        .update-form {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4caf50;
        }
        
        .admin-btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #2e7d32;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1b5e20;
        }
        
        .btn-danger {
            background: #d32f2f;
            color: white;
        }
        
        .btn-danger:hover {
            background: #b71c1c;
        }
        
        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        
        .updates-list {
            display: grid;
            gap: 1.5rem;
        }
        
        .update-edit-card {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 1.5rem;
            background: #f8f9fa;
        }
        
        .priority-card {
            border-left: 5px solid #f44336;
            background: linear-gradient(135deg, rgba(244, 67, 54, 0.05) 0%, rgba(255, 255, 255, 0.95) 100%);
        }
        
        .update-preview {
            margin-bottom: 1rem;
        }
        
        .update-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .update-icon {
            font-size: 2rem;
        }
        
        .update-info h3 {
            margin: 0;
            color: #2e7d32;
            flex-grow: 1;
        }
        
        .update-info p {
            margin: 0 0 1rem 0;
            color: #666;
            line-height: 1.6;
        }
        
        .update-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.9rem;
            color: #757575;
        }
        
        .badge {
            padding: 0.4rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-important {
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            color: white;
        }
        
        .badge-new {
            background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
            color: white;
        }
        
        .badge-community {
            background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
            color: white;
        }
        
        .badge-info {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            color: white;
        }
        
        .priority-badge {
            background: #ffebee;
            color: #d32f2f;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-top: 0.5rem;
        }
        
        .update-actions {
            display: flex;
            gap: 1rem;
        }
        
        .edit-form {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e0e0e0;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .checkbox-label {
            display: flex !important;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        
        .checkbox-label input {
            width: auto !important;
        }
        
        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .admin-nav-buttons {
                flex-direction: column;
                width: 100%;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .update-header {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }
            
            .update-actions {
                justify-content: center;
            }
        }
    </style>

    <script>
        function editUpdate(updateId) {
            // Hide all edit forms
            document.querySelectorAll('.edit-form').forEach(form => {
                form.style.display = 'none';
            });
            
            // Show the selected edit form
            document.getElementById('edit-form-' + updateId).style.display = 'block';
        }
        
        function cancelEdit(updateId) {
            document.getElementById('edit-form-' + updateId).style.display = 'none';
        }
        
        function deleteUpdate(updateId) {
            if (confirm('Are you sure you want to delete this update? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_update">
                    <input type="hidden" name="update_id" value="${updateId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>