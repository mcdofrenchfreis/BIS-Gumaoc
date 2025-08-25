<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in and has incomplete profile
if (!isset($_SESSION['rfid_authenticated']) || !isset($_SESSION['profile_incomplete'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

// Get user's current data
try {
    $stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: ../login.php');
        exit();
    }
} catch (PDOException $e) {
    $error = "Database error. Please try again.";
}

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $birth_date = trim($_POST['birth_date'] ?? '');
        $birth_place = trim($_POST['birth_place'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $civil_status = trim($_POST['civil_status'] ?? '');
        
        // Validation
        $errors = [];
        if (empty($phone)) $errors[] = "Phone number is required";
        if (empty($address)) $errors[] = "Address is required";
        if (empty($birth_date)) $errors[] = "Date of birth is required";
        if (empty($birth_place)) $errors[] = "Place of birth is required";
        if (empty($gender)) $errors[] = "Gender is required";
        if (empty($civil_status)) $errors[] = "Civil status is required";
        
        if (empty($errors)) {
            // Update user profile
            $update_sql = "UPDATE residents SET 
                phone = ?, address = ?, birthdate = ?, birth_place = ?, 
                gender = ?, civil_status = ?, profile_complete = 1,
                updated_at = NOW()
                WHERE id = ?";
            
            $update_stmt = $pdo->prepare($update_sql);
            $result = $update_stmt->execute([
                $phone, $address, $birth_date, $birth_place,
                $gender, $civil_status, $user_id
            ]);
            
            if ($result) {
                // Clear profile incomplete flag
                unset($_SESSION['profile_incomplete']);
                unset($_SESSION['user_email']);
                
                $success = "Profile completed successfully! You can now access all system features.";
                
                // Redirect to home page after 2 seconds
                echo "<script>
                    setTimeout(function() {
                        window.location.href = '../index.php';
                    }, 2000);
                </script>";
            } else {
                $error = "Failed to update profile. Please try again.";
            }
        } else {
            $error = implode(', ', $errors);
        }
    } catch (PDOException $e) {
        $error = "Database error. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile - GUMAOC</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .profile-completion-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .profile-header h1 {
            color: #2d5a27;
            margin-bottom: 0.5rem;
        }
        
        .profile-header p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .welcome-message {
            background: linear-gradient(135deg, #2d5a27, #4a7c59);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2d5a27;
            box-shadow: 0 0 0 3px rgba(45, 90, 39, 0.1);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #2d5a27, #4a7c59);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s ease;
        }
        
        .submit-btn:hover {
            background: linear-gradient(135deg, #1e3f1a, #2d5a27);
        }
        
        /* Toast Notification Styles */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2d5a27;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateX(400px);
            transition: transform 0.3s ease;
            z-index: 1000;
            max-width: 400px;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast.error {
            background: #dc3545;
        }
        
        .toast.success {
            background: #28a745;
        }
        
        .toast-header {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .toast-close {
            position: absolute;
            top: 0.5rem;
            right: 0.75rem;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }
        
        .required-fields-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="profile-completion-container">
        <div class="profile-header">
            <h1>Complete Your Profile</h1>
            <p>Welcome to GUMAOC East Information System</p>
        </div>
        
        <div class="welcome-message">
            <h2>Hello, <?php echo htmlspecialchars($user_name); ?>!</h2>
            <p>You were added as a family member by another resident. To access all system features, please complete your personal information below.</p>
        </div>
        
        <div class="required-fields-notice">
            <strong>Required Information:</strong> Please fill in all the required fields to complete your profile and gain full access to the system.
        </div>
        
        <form method="POST" action="">
            <div class="form-grid">
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" required placeholder="09XXXXXXXXX" 
                           pattern="[0-9]{11}" maxlength="11" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="birth_date">Date of Birth *</label>
                    <input type="date" id="birth_date" name="birth_date" required 
                           value="<?php echo htmlspecialchars($user['birthdate'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Complete Address *</label>
                <input type="text" id="address" name="address" required placeholder="Complete address including barangay, city, province"
                       value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="birth_place">Place of Birth *</label>
                    <input type="text" id="birth_place" name="birth_place" required placeholder="City, Province, Country"
                           value="<?php echo htmlspecialchars($user['birth_place'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="gender">Gender *</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo ($user['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($user['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($user['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="civil_status">Civil Status *</label>
                <select id="civil_status" name="civil_status" required>
                    <option value="">Select Civil Status</option>
                    <option value="Single" <?php echo ($user['civil_status'] ?? '') === 'Single' ? 'selected' : ''; ?>>Single</option>
                    <option value="Married" <?php echo ($user['civil_status'] ?? '') === 'Married' ? 'selected' : ''; ?>>Married</option>
                    <option value="Widowed" <?php echo ($user['civil_status'] ?? '') === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                    <option value="Separated" <?php echo ($user['civil_status'] ?? '') === 'Separated' ? 'selected' : ''; ?>>Separated</option>
                    <option value="Divorced" <?php echo ($user['civil_status'] ?? '') === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                </select>
            </div>
            
            <button type="submit" class="submit-btn">Complete Profile</button>
        </form>
    </div>
    
    <!-- Toast Notification -->
    <?php if (!empty($success)): ?>
    <div class="toast success show">
        <div class="toast-header">Profile Completed!</div>
        <div><?php echo htmlspecialchars($success); ?></div>
        <button class="toast-close" onclick="hideToast()">&times;</button>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
    <div class="toast error show">
        <div class="toast-header">Error</div>
        <div><?php echo htmlspecialchars($error); ?></div>
        <button class="toast-close" onclick="hideToast()">&times;</button>
    </div>
    <?php endif; ?>
    
    <!-- Profile Incomplete Toast on Page Load -->
    <div id="profileIncompleteToast" class="toast show">
        <div class="toast-header">Profile Incomplete</div>
        <div>Please complete your personal information to access all system features.</div>
        <button class="toast-close" onclick="hideToast('profileIncompleteToast')">&times;</button>
    </div>
    
    <script>
        // Show toast notification on page load
        window.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                hideToast('profileIncompleteToast');
            }, 5000); // Auto hide after 5 seconds
        });
        
        function hideToast(toastId = null) {
            if (toastId) {
                const toast = document.getElementById(toastId);
                if (toast) {
                    toast.classList.remove('show');
                }
            } else {
                const toasts = document.querySelectorAll('.toast');
                toasts.forEach(toast => {
                    toast.classList.remove('show');
                });
            }
        }
        
        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>