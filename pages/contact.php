<?php
$base_path = '../';
$page_title = 'Contact Us - Barangay Gumaoc East';
$header_title = 'Contact Us';
$header_subtitle = 'Get in Touch with Barangay Gumaoc East';

include '../includes/header.php';
include '../includes/navigation.php';
?>

<div class="container">
  <div class="section">
    <h2>Send Us a Message</h2>
    <div class="card">
      <form id="contactForm" action="process_contact.php" method="POST">
        <div class="form-group">
          <label for="name">Full Name*</label>
          <input type="text" id="name" name="name" placeholder="Enter your full name" required>
        </div>

        <div class="form-group">
          <label for="email">Email Address*</label>
          <input type="email" id="email" name="email" placeholder="Enter your email address" required>
        </div>

        <div class="form-group">
          <label for="phone">Contact Number</label>
          <input type="tel" id="phone" name="phone" placeholder="Enter your contact number">
        </div>

        <div class="form-group">
          <label for="subject">Subject*</label>
          <input type="text" id="subject" name="subject" placeholder="Enter message subject" required>
        </div>

        <div class="form-group">
          <label for="message">Message*</label>
          <textarea id="message" name="message" rows="5" placeholder="Enter your message" required></textarea>
        </div>

        <button type="submit" class="btn">Send Message</button>
      </form>
    </div>
  </div>

  <div class="section">
    <h2>Contact Information</h2>
    <div class="features">
      <div class="feature">
        <h3>📍 Office Address</h3>
        <p>Barangay Hall, Gumaoc East</p>
        <p>City of San Jose del Monte</p>
        <p>Bulacan, Philippines</p>
      </div>

      <div class="feature">
        <h3>📞 Phone Numbers</h3>
        <p>Main Office: (044) 123-4567</p>
        <p>Mobile: +63 912 345 6789</p>
        <p>Fax: (044) 123-4568</p>
      </div>

      <div class="feature">
        <h3>📧 Email Addresses</h3>
        <p>General Inquiries: gumaoc.e-services@barangay.gov.ph</p>
        <p>Technical Support: support@barangay.gov.ph</p>
        <p>Document Requests: documents@barangay.gov.ph</p>
      </div>
    </div>
  </div>

  <div class="section">
    <h2>Office Hours</h2>
    <div class="card">
      <h3>Regular Office Hours</h3>
      <p>Monday to Friday: 8:00 AM - 5:00 PM</p>
      <p>Saturday: 8:00 AM - 12:00 PM</p>
      <p>Sunday: Closed</p>
      <p><strong>Note:</strong> For emergencies, our hotline is available 24/7.</p>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?> 