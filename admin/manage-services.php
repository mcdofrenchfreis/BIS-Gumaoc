<?php
session_start();
include '../includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_service':
                $service_id = (int)$_POST['service_id'];
                $title = htmlspecialchars($_POST['title']);
                $description = htmlspecialchars($_POST['description']);
                $button_text = htmlspecialchars($_POST['button_text']);
                $button_link = htmlspecialchars($_POST['button_link']);
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $features = isset($_POST['features']) ? implode(',', $_POST['features']) : '';
                
                $stmt = $pdo->prepare("UPDATE services SET title = ?, description = ?, button_text = ?, button_link = ?, is_featured = ?, features = ? WHERE id = ?");
                if ($stmt->execute([$title, $description, $button_text, $button_link, $is_featured, $features, $service_id])) {
                    $_SESSION['success'] = "Service updated successfully!";
                } else {
                    $_SESSION['error'] = "Failed to update service.";
                }
                break;
                
            case 'add_service':
                $title = htmlspecialchars($_POST['title']);
                $description = htmlspecialchars($_POST['description']);
                $button_text = htmlspecialchars($_POST['button_text']);
                $button_link = htmlspecialchars($_POST['button_link']);
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $features = isset($_POST['features']) ? implode(',', $_POST['features']) : '';
                
                $stmt = $pdo->prepare("INSERT INTO services (title, description, button_text, button_link, is_featured, features) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$title, $description, $button_text, $button_link, $is_featured, $features])) {
                    $_SESSION['success'] = "Service added successfully!";
                } else {
                    $_SESSION['error'] = "Failed to add service.";
                }
                break;
                
            case 'delete_service':
                $service_id = (int)$_POST['service_id'];
                $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
                if ($stmt->execute([$service_id])) {
                    $_SESSION['success'] = "Service deleted successfully!";
                } else {
                    $_SESSION['error'] = "Failed to delete service.";
                }
                break;
        }
        header('Location: manage-services.php');
        exit;
    }
}

// Create services table if it doesn't exist (updated without icon column)
$pdo->exec("
    CREATE TABLE IF NOT EXISTS services (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        button_text VARCHAR(100) NOT NULL,
        button_link VARCHAR(255) NOT NULL,
        is_featured BOOLEAN DEFAULT FALSE,
        features TEXT,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
");

// Insert default services if table is empty (without icons)
$count = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
if ($count == 0) {
    $default_services = [
        [
            'Emergency Response',
            'Real-time incident reporting with IoT sensors and instant emergency response coordination.',
            'Report Incident',
            'pages/report.php',
            1,
            'IoT Enabled,24/7 Monitoring'
        ],
        [
            'Document Requests',
            'Request certificates, clearances, and official documents online with automated processing.',
            'Apply Now',
            'pages/forms.php',
            0,
            'Online Processing,Fast Approval'
        ],
        [
            'Community Census',
            'Register as a resident and contribute to our comprehensive community database.',
            'Register',
            'pages/forms.php',
            0,
            'Digital Registry,Secure Data'
        ],
        [
            'Self-Service Kiosk',
            'Access services anytime through our interactive kiosk at the barangay hall.',
            'Explore',
            'pages/services.php',
            0,
            '24/7 Access,Touch Interface'
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO services (title, description, button_text, button_link, is_featured, features) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($default_services as $service) {
        $stmt->execute($service);
    }
}

// Fetch all services
$services = $pdo->query("SELECT * FROM services ORDER BY display_order, id")->fetchAll(PDO::FETCH_ASSOC);

$base_path = '../';
$page_title = 'Manage Services - Admin Panel';
$header_title = 'Manage Digital Services';
$header_subtitle = 'Configure and manage service content';

include '../includes/header.php';
?>

<body>
    <div class="admin-container">
        <div class="admin-header">
            <div>
                <h1>🎛️ Manage Digital Services</h1>
                <p>Configure service cards, descriptions, and links</p>
            </div>
            <div class="admin-nav-buttons">
                <a href="dashboard.php" class="admin-btn">📊 Dashboard</a>
                <a href="manage-updates.php" class="admin-btn">📢 Updates</a>
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

        <!-- Add New Service -->
        <div class="admin-section">
            <h2>➕ Add New Service</h2>
            <form method="POST" class="service-form">
                <input type="hidden" name="action" value="add_service">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Service Title</label>
                        <input type="text" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Button Text</label>
                        <input type="text" name="button_text" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Description</label>
                        <textarea name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Button Link</label>
                        <input type="text" name="button_link" placeholder="pages/service.php" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Features (comma separated)</label>
                        <input type="text" name="features[]" placeholder="Feature 1,Feature 2">
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_featured">
                            Featured Service (larger display)
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="admin-btn btn-primary">Add Service</button>
            </form>
        </div>

        <!-- Existing Services -->
        <div class="admin-section">
            <h2>📋 Existing Services</h2>
            <div class="services-list">
                <?php foreach ($services as $service): ?>
                    <div class="service-edit-card">
                        <div class="service-preview">
                            <div class="service-info">
                                <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                                <p><?php echo htmlspecialchars($service['description']); ?></p>
                                <?php if ($service['is_featured']): ?>
                                    <span class="featured-badge">⭐ Featured</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="service-actions">
                            <button class="admin-btn btn-small" onclick="editService(<?php echo $service['id']; ?>)">✏️ Edit</button>
                            <button class="admin-btn btn-danger btn-small" onclick="deleteService(<?php echo $service['id']; ?>)">🗑️ Delete</button>
                        </div>
                        
                        <!-- Edit Form (Hidden by default) -->
                        <div id="edit-form-<?php echo $service['id']; ?>" class="edit-form" style="display: none;">
                            <form method="POST" class="service-form">
                                <input type="hidden" name="action" value="update_service">
                                <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>Service Title</label>
                                        <input type="text" name="title" value="<?php echo htmlspecialchars($service['title']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Button Text</label>
                                        <input type="text" name="button_text" value="<?php echo htmlspecialchars($service['button_text']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group full-width">
                                        <label>Description</label>
                                        <textarea name="description" rows="3" required><?php echo htmlspecialchars($service['description']); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group full-width">
                                        <label>Button Link</label>
                                        <input type="text" name="button_link" value="<?php echo htmlspecialchars($service['button_link']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group full-width">
                                        <label>Features (comma separated)</label>
                                        <input type="text" name="features[]" value="<?php echo htmlspecialchars($service['features']); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="is_featured" <?php echo $service['is_featured'] ? 'checked' : ''; ?>>
                                            Featured Service (larger display)
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="admin-btn btn-primary">Update Service</button>
                                    <button type="button" class="admin-btn" onclick="cancelEdit(<?php echo $service['id']; ?>)">Cancel</button>
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
        
        .service-form {
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
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
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
        
        .services-list {
            display: grid;
            gap: 1.5rem;
        }
        
        .service-edit-card {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 1.5rem;
            background: #f8f9fa;
        }
        
        .service-preview {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .service-info h3 {
            margin: 0 0 0.5rem 0;
            color: #2e7d32;
        }
        
        .service-info p {
            margin: 0 0 0.5rem 0;
            color: #666;
        }
        
        .featured-badge {
            background: #ff9800;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .service-actions {
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
            
            .service-preview {
                flex-direction: column;
                text-align: center;
            }
            
            .service-actions {
                justify-content: center;
            }
        }
    </style>

    <script>
        function editService(serviceId) {
            // Hide all edit forms
            document.querySelectorAll('.edit-form').forEach(form => {
                form.style.display = 'none';
            });
            
            // Show the selected edit form
            document.getElementById('edit-form-' + serviceId).style.display = 'block';
        }
        
        function cancelEdit(serviceId) {
            document.getElementById('edit-form-' + serviceId).style.display = 'none';
        }
        
        function deleteService(serviceId) {
            if (confirm('Are you sure you want to delete this service? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_service">
                    <input type="hidden" name="service_id" value="${serviceId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>