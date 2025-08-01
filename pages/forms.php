<?php
session_start();
$base_path = '../';
$page_title = 'Forms - Barangay Gumaoc East';
$header_title = 'Barangay Forms';
$header_subtitle = 'Access various forms and documents';

include '../includes/header.php';
include '../includes/navigation.php';
?>

<div class="container">
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
      <strong>Success!</strong> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>
  
  <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
    <div class="alert alert-success">
      <strong>Success!</strong> Your application has been submitted successfully! You will receive a confirmation soon.
    </div>
  <?php endif; ?>
  
  <div class="section">
    <div class="forms-grid">
      <div class="form-card">
        <div class="form-icon">
          <i class="icon-people"></i>
        </div>
        <h3>Census Registration</h3>
        <p>Register as a resident of Barangay Gumaoc East. Complete your demographic information and household details.</p>
        <a href="resident-registration.php" class="btn btn-primary">Fill Out Form</a>
      </div>
      
      <div class="form-card">
        <div class="form-icon">
          <i class="icon-document"></i>
        </div>
        <h3>Certificate Requests</h3>
        <p>Request various barangay certificates such as Certificate of Residency, Indigency, and others.</p>
        <a href="certificate-request.php" class="btn btn-primary">Request Certificate</a>
      </div>
      
      <div class="form-card">
        <div class="form-icon">
          <i class="icon-business"></i>
        </div>
        <h3>Business Application</h3>
        <p>Apply for business permits and clearances required for operating within the barangay.</p>
        <a href="business-application.php" class="btn btn-primary">Apply Now</a>
      </div>
      
      <div class="form-card">
        <div class="form-icon">
          <i class="icon-help"></i>
        </div>
        <h3>Assistance Applications</h3>
        <p>Apply for various assistance programs offered by the barangay government.</p>
        <a href="#" class="btn btn-secondary">Coming Soon</a>
      </div>
    </div>
  </div>
</div>

<style>
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.section {
  margin-bottom: 30px;
}

.forms-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.form-card {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  padding: 30px;
  text-align: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.form-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.form-icon {
  width: 60px;
  height: 60px;
  background: #28a745;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  font-size: 24px;
  color: white;
}

.form-icon::before {
  content: "📋";
  font-size: 24px;
}

.form-card .icon-people::before {
  content: "👥";
}

.form-card .icon-document::before {
  content: "📄";
}

.form-card .icon-business::before {
  content: "🏢";
}

.form-card .icon-help::before {
  content: "🤝";
}

.form-card h3 {
  color: #333;
  margin-bottom: 15px;
  font-size: 1.4rem;
}

.form-card p {
  color: #666;
  margin-bottom: 25px;
  line-height: 1.5;
}

.btn {
  display: inline-block;
  padding: 12px 24px;
  border-radius: 4px;
  text-decoration: none;
  font-weight: 500;
  transition: background-color 0.3s ease;
  border: none;
  cursor: pointer;
  font-size: 16px;
}

.btn-primary {
  background-color: #28a745;
  color: white;
}

.btn-primary:hover {
  background-color: #218838;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background-color: #5a6268;
}

@media (max-width: 768px) {
  .forms-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }
  
  .form-card {
    padding: 20px;
  }
  
  .container {
    padding: 10px;
  }
}
</style>

<?php include '../includes/footer.php'; ?>
