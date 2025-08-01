<?php
$base_path = '../';
$page_title = 'Incident Reporting - Barangay Gumaoc East';
$header_title = 'Incident Reporting';
$header_subtitle = 'Report Incidents and Emergencies';

include '../includes/header.php';
include '../includes/navigation.php';
?>

<div class="container">
  <div class="section">
    <h2>Report an Incident</h2>
    <div class="card">
      <form id="incidentForm" action="process_report.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label for="incidentType">Type of Incident*</label>
          <select id="incidentType" name="incidentType" required>
            <option value="">Select incident type</option>
            <option value="emergency">Emergency (Medical, Fire, etc.)</option>
            <option value="crime">Crime-related</option>
            <option value="accident">Traffic Accident</option>
            <option value="disturbance">Public Disturbance</option>
            <option value="infrastructure">Infrastructure Issue</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div class="form-group">
          <label for="location">Location*</label>
          <input type="text" id="location" name="location" placeholder="Enter incident location" required>
        </div>

        <div class="form-group">
          <label for="datetime">Date and Time*</label>
          <input type="datetime-local" id="datetime" name="datetime" required>
        </div>

        <div class="form-group">
          <label for="description">Description*</label>
          <textarea id="description" name="description" rows="5" placeholder="Describe the incident in detail" required></textarea>
        </div>

        <div class="form-group">
          <label for="photos">Upload Photos (if any)</label>
          <input type="file" id="photos" name="photos[]" accept="image/*" multiple>
        </div>

        <div class="form-group">
          <label for="name">Your Name*</label>
          <input type="text" id="name" name="name" placeholder="Enter your full name" required>
        </div>

        <div class="form-group">
          <label for="contact">Contact Number*</label>
          <input type="tel" id="contact" name="contact" placeholder="Enter your contact number" required>
        </div>

        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" placeholder="Enter your email address">
        </div>

        <button type="submit" class="btn">Submit Report</button>
      </form>
    </div>
  </div>

  <div class="section">
    <h2>Emergency Contacts</h2>
    <div class="features">
      <div class="feature">
        <h3>🚓 Police Station</h3>
        <p>Contact for crime-related emergencies</p>
        <p>📞 (044) 123-4567</p>
      </div>

      <div class="feature">
        <h3>🚒 Fire Station</h3>
        <p>Contact for fire-related emergencies</p>
        <p>📞 (044) 123-4568</p>
      </div>

      <div class="feature">
        <h3>🚑 Medical Emergency</h3>
        <p>Contact for medical emergencies</p>
        <p>📞 (044) 123-4569</p>
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

.card {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  padding: 20px;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
}

.form-group input[type="text"],
.form-group input[type="tel"],
.form-group input[type="email"],
.form-group input[type="datetime-local"],
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
  box-sizing: border-box;
}

.form-group textarea {
  resize: vertical;
  min-height: 100px;
}

.form-group input[type="file"] {
  width: 100%;
  padding: 10px;
  border: 2px dashed #ddd;
  border-radius: 4px;
  background-color: #f8f9fa;
  cursor: pointer;
  box-sizing: border-box;
}

.form-group input[type="file"]::-webkit-file-upload-button {
  background-color: #e9ecef;
  border: none;
  border-radius: 4px;
  padding: 8px 16px;
  margin-right: 10px;
  cursor: pointer;
}

.form-group input[type="file"]::file-selector-button {
  background-color: #e9ecef;
  border: none;
  border-radius: 4px;
  padding: 8px 16px;
  margin-right: 10px;
  cursor: pointer;
}

.features {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.feature {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.feature h3 {
  margin-top: 0;
  color: #333;
}

.btn {
  background-color: #28a745;
  color: white;
  padding: 12px 24px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
  width: 100%;
}

.btn:hover {
  background-color: #218838;
}

@media (max-width: 768px) {
  .container {
    padding: 10px;
  }
  
  .features {
    grid-template-columns: 1fr;
  }
}
</style>

<?php include '../includes/footer.php'; ?> 