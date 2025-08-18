<footer class="minimalist-footer">
  <div class="footer-container">
    <div class="footer-grid">
      <!-- Brand Section -->
      <div class="footer-brand">
        <h3>Barangay Gumaoc East</h3>
        <p>Digital Services Platform</p>
      </div>

      <!-- Quick Links -->
      <div class="footer-section">
        <h4>Services</h4>
        <ul>
          <li><a href="<?php echo $base_path; ?>pages/resident-registration.php">Census Registration</a></li>
          <li><a href="<?php echo $base_path; ?>pages/certificate-request.php">Certificate Requests</a></li>
          <li><a href="<?php echo $base_path; ?>pages/business-application.php">Business Applications</a></li>
          <li><a href="<?php echo $base_path; ?>pages/report.php">Report Incident</a></li>
        </ul>
      </div>

      <!-- Information -->
      <div class="footer-section">
        <h4>Information</h4>
        <ul>
          <li><a href="<?php echo $base_path; ?>pages/about.php">About Us</a></li>
          <li><a href="<?php echo $base_path; ?>pages/contact.php">Contact</a></li>
          <li><a href="<?php echo $base_path; ?>pages/services.php">All Services</a></li>
          <li><a href="#">Privacy Policy</a></li>
        </ul>
      </div>

      <!-- Contact Info -->
      <div class="footer-section">
        <h4>Contact</h4>
        <div class="contact-info">
          <p><strong>Address:</strong> Barangay Gumaoc East, San Jose del Monte, Bulacan</p>
          <p><strong>Phone:</strong> (044) 123-4567</p>
          <p><strong>Email:</strong> info@gumaoceast.gov.ph</p>
          <p><strong>Hours:</strong> Mon-Fri 8AM-5PM, Sat 8AM-12PM</p>
        </div>
      </div>
    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom">
      <p>&copy; <?php echo date('Y'); ?> Barangay Gumaoc East. All rights reserved.</p>
      <div class="footer-links">
        <a href="#">Terms</a>
        <a href="#">Privacy</a>
        <a href="#">Accessibility</a>
      </div>
    </div>
  </div>
</footer>

<style>
.minimalist-footer {
  background: #1b5e20;
  color: white;
  margin-top: 3rem;
  font-size: 0.9rem;
}

.footer-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2.5rem 2rem 1.5rem;
}

.footer-grid {
  display: grid;
  grid-template-columns: 1.5fr 1fr 1fr 1.2fr;
  gap: 2rem;
  margin-bottom: 2rem;
}

.footer-brand h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.3rem;
  font-weight: 600;
  color: white;
}

.footer-brand p {
  margin: 0;
  opacity: 0.8;
  font-size: 0.85rem;
}

.footer-section h4 {
  margin: 0 0 1rem 0;
  font-size: 1rem;
  font-weight: 600;
  color: #c8e6c9;
}

.footer-section ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-section li {
  margin-bottom: 0.5rem;
}

.footer-section a {
  color: rgba(255, 255, 255, 0.85);
  text-decoration: none;
  transition: color 0.2s ease;
}

.footer-section a:hover {
  color: #c8e6c9;
}

.contact-info p {
  margin: 0 0 0.6rem 0;
  line-height: 1.4;
  opacity: 0.9;
}

.contact-info strong {
  color: #c8e6c9;
  font-weight: 500;
}

.footer-bottom {
  border-top: 1px solid rgba(255, 255, 255, 0.2);
  padding-top: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.footer-bottom p {
  margin: 0;
  opacity: 0.8;
}

.footer-links {
  display: flex;
  gap: 1.5rem;
}

.footer-links a {
  color: rgba(255, 255, 255, 0.7);
  text-decoration: none;
  font-size: 0.85rem;
  transition: color 0.2s ease;
}

.footer-links a:hover {
  color: #c8e6c9;
}

/* Responsive */
@media (max-width: 768px) {
  .footer-container {
    padding: 2rem 1.5rem 1rem;
  }
  
  .footer-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  .footer-bottom {
    flex-direction: column;
    text-align: center;
    gap: 0.8rem;
  }
  
  .footer-links {
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .footer-container {
    padding: 1.5rem 1rem 0.8rem;
  }
  
  .footer-grid {
    gap: 1.2rem;
  }
  
  .footer-links {
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style>

</body>
</html>