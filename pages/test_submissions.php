<!DOCTYPE html>
<html>
<head>
    <title>Form Submission Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .test-container { max-width: 800px; margin: 0 auto; }
        .test-form { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #218838; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>Form Submission Test</h1>
        
        <?php
        session_start();
        if (isset($_SESSION['success'])) {
            echo '<div class="success">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo '<div class="error">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        ?>
        
        <!-- Test Certificate Request -->
        <div class="test-form">
            <h2>Test Certificate Request</h2>
            <form method="POST" action="process_certificate_request.php">
                <div class="form-group">
                    <label>Certificate Type:</label>
                    <select name="certificateType" required>
                        <option value="BRGY. CLEARANCE">Barangay Clearance</option>
                        <option value="BRGY. INDIGENCY">Certificate of Indigency</option>
                        <option value="PROOF OF RESIDENCY">Proof of Residency</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>First Name:</label>
                    <input type="text" name="firstName" value="Juan" required>
                </div>
                
                <div class="form-group">
                    <label>Middle Name:</label>
                    <input type="text" name="middleName" value="Santos">
                </div>
                
                <div class="form-group">
                    <label>Last Name:</label>
                    <input type="text" name="lastName" value="Dela Cruz" required>
                </div>
                
                <div class="form-group">
                    <label>Address 1:</label>
                    <input type="text" name="address1" value="123 Test Street" required>
                </div>
                
                <div class="form-group">
                    <label>Address 2:</label>
                    <input type="text" name="address2" value="Gumaoc East, Rizal">
                </div>
                
                <div class="form-group">
                    <label>Birth Date:</label>
                    <input type="date" name="birthdate" value="1990-01-15" required>
                </div>
                
                <div class="form-group">
                    <label>Birth Place:</label>
                    <input type="text" name="birthplace" value="Rizal Province" required>
                </div>
                
                <div class="form-group">
                    <label>Purpose:</label>
                    <input type="text" name="purpose" value="Employment Application" required>
                </div>
                
                <button type="submit">Submit Certificate Request</button>
            </form>
        </div>
        
        <!-- Test Business Application -->
        <div class="test-form">
            <h2>Test Business Application</h2>
            <form method="POST" action="process_business_application.php">
                <div class="form-group">
                    <label>First Name:</label>
                    <input type="text" name="first_name" value="Maria" required>
                </div>
                
                <div class="form-group">
                    <label>Middle Name:</label>
                    <input type="text" name="middle_name" value="Garcia">
                </div>
                
                <div class="form-group">
                    <label>Last Name:</label>
                    <input type="text" name="last_name" value="Santos" required>
                </div>
                
                <div class="form-group">
                    <label>Business Name:</label>
                    <input type="text" name="business_name" value="Maria's Sari-Sari Store" required>
                </div>
                
                <div class="form-group">
                    <label>Business Address 1:</label>
                    <input type="text" name="business_address_1" value="456 Business Street" required>
                </div>
                
                <div class="form-group">
                    <label>Business Address 2:</label>
                    <input type="text" name="business_address_2" value="Gumaoc East">
                </div>
                
                <div class="form-group">
                    <label>House Address:</label>
                    <textarea name="house_address" required>789 Home Avenue, Gumaoc East, Rizal</textarea>
                </div>
                
                <button type="submit">Submit Business Application</button>
            </form>
        </div>
        
        <!-- Test Resident Registration -->
        <div class="test-form">
            <h2>Test Resident Registration (Census)</h2>
            <form method="POST" action="process-census.php">
                <div class="form-group">
                    <label>Head of Family (Puno ng Pamilya):</label>
                    <input type="text" name="headOfFamily" value="Pedro Gonzales Cruz" required>
                </div>
                
                <div class="form-group">
                    <label>Cellphone Number:</label>
                    <input type="tel" name="cellphone" value="09123456789">
                </div>
                
                <div class="form-group">
                    <label>House Number:</label>
                    <input type="text" name="houseNumber" value="123" required>
                </div>
                
                <div class="form-group">
                    <label>Interviewer Name:</label>
                    <input type="text" name="interviewer" value="Ana Santos" required>
                </div>
                
                <div class="form-group">
                    <label>Interviewer Title:</label>
                    <input type="text" name="interviewerTitle" value="Barangay Health Worker" required>
                </div>
                
                <button type="submit">Submit Census Registration</button>
            </form>
        </div>
        
        <div class="test-form">
            <h2>Admin Links</h2>
            <p><a href="../admin/dashboard.php">View Admin Dashboard</a></p>
            <p><a href="../admin/view-certificate-requests.php">View Certificate Requests</a></p>
            <p><a href="../admin/view-business-applications.php">View Business Applications</a></p>
            <p><a href="../admin/view-resident-registrations.php">View Resident Registrations</a></p>
        </div>
    </div>
</body>
</html>
