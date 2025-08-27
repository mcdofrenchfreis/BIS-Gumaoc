<?php
session_start();

// Simulate a successful registration with queue information
$test_success_message = '<div class="success-content">';
$test_success_message .= '<div class="success-header">🎉 <strong>Registration Successfully Completed!</strong></div>';

// Add registrant information section
$test_success_message .= '<div class="success-section">';
$test_success_message .= '<div class="section-title">📝 Registration Details</div>';
$test_success_message .= '<div class="section-content">';
$test_success_message .= '<div class="info-row"><strong>Registrant:</strong> Juan Miguel Santos</div>';
$test_success_message .= '<div class="info-row"><strong>House Number:</strong> 123</div>';
$test_success_message .= '</div></div>';

// Queue information section
$test_success_message .= '<div class="success-section highlight-section">';
$test_success_message .= '<div class="section-title">🎫 Queue Information</div>';
$test_success_message .= '<div class="section-content">';
$test_success_message .= '<div class="info-row"><strong>Ticket Number:</strong> <span class="highlight-text">Q2024-001234</span></div>';
$test_success_message .= '<div class="info-row"><strong>Queue Position:</strong> #3</div>';
$test_success_message .= '<div class="info-row"><strong>Estimated Processing Time:</strong> 15-20 minutes</div>';
$test_success_message .= '<div class="info-row"><strong>Service:</strong> Resident Registration</div>';
$test_success_message .= '</div></div>';

// Email notification section
$test_success_message .= '<div class="success-section">';
$test_success_message .= '<div class="section-title">📧 Email Notifications</div>';
$test_success_message .= '<div class="section-content">';
$test_success_message .= '<div class="info-row success-item">✅ Confirmation email sent to: <strong>juan.santos@email.com</strong></div>';
$test_success_message .= '<div class="info-row success-item">✅ Family notifications sent to 2 member(s)</div>';
$test_success_message .= '<div class="info-row success-item">✅ 2 family member(s) registered as new users</div>';
$test_success_message .= '</div></div>';

// Next steps section
$test_success_message .= '<div class="success-section next-steps-section">';
$test_success_message .= '<div class="section-title">📋 What Happens Next?</div>';
$test_success_message .= '<div class="section-content">';
$test_success_message .= '<div class="step-item">1. Your registration is being processed by our team</div>';
$test_success_message .= '<div class="step-item">2. You will receive login credentials once approved</div>';
$test_success_message .= '<div class="step-item">3. Keep your queue ticket number for reference</div>';
$test_success_message .= '<div class="step-item">4. Check queue status using the "Queue" link in navigation</div>';
$test_success_message .= '</div></div>';

$test_success_message .= '<div class="success-footer">Thank you for registering with <strong>Barangay Gumaoc East</strong>!</div>';
$test_success_message .= '</div>';

// Set the success message in session
$_SESSION['success'] = $test_success_message;

$base_path = './';
$page_title = 'Success Message Test - Barangay Gumaoc East';
$header_title = 'Success Message Test';
$header_subtitle = 'Preview of Enhanced Registration Success Message';

include 'includes/header.php';
?>

<style>
/* Modal Styles for Testing */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(5px);
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal-content {
  background: white;
  border-radius: 15px;
  max-width: 600px;
  width: 100%;
  max-height: 80vh;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: modalSlideIn 0.4s ease;
  position: relative;
  overflow: hidden;
}

@keyframes modalSlideIn {
  from {
    transform: translateY(-50px) scale(0.9);
    opacity: 0;
  }
  to {
    transform: translateY(0) scale(1);
    opacity: 1;
  }
}

.modal-header {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
  padding: 2rem 2.5rem;
  position: relative;
  text-align: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.modal-success .modal-header {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.modal-header h4 {
  margin: 0;
  font-size: 1.6rem;
  font-weight: 700;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.modal-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  font-size: 1.8rem;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.modal-close:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: scale(1.1) rotate(90deg);
  border-color: rgba(255, 255, 255, 0.5);
}

/* Enhanced Success Message Styling */
.success-modal-body {
  padding: 1.5rem 2.5rem;
  max-height: 70vh;
  overflow-y: auto;
}

.success-message-container {
  color: #2c3e50;
  line-height: 1.6;
}

.success-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.success-header {
  text-align: center;
  font-size: 1.5rem;
  font-weight: 700;
  color: #28a745;
  padding: 1rem;
  background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
  border-radius: 12px;
  border: 2px solid #28a745;
  box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
}

.success-section {
  background: rgba(248, 249, 250, 0.8);
  border-radius: 10px;
  padding: 1.5rem;
  border-left: 4px solid #28a745;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.highlight-section {
  background: linear-gradient(135deg, #e8f5e8 0%, #f1f8e9 100%);
  border: 2px solid #28a745;
  border-left: 6px solid #28a745;
}

.next-steps-section {
  background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
  border-left: 4px solid #ffc107;
}

.section-title {
  font-size: 1.2rem;
  font-weight: 700;
  color: #1b5e20;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.section-content {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.info-row {
  padding: 0.6rem 0;
  font-size: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.info-row strong {
  color: #2d5a27;
  font-weight: 600;
}

.highlight-text {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
  padding: 0.3rem 0.8rem;
  border-radius: 20px;
  font-weight: 700;
  font-size: 1.1rem;
  box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.success-item {
  color: #155724;
  font-weight: 600;
}

.warning-item {
  color: #856404;
  font-weight: 600;
}

.step-item {
  padding: 0.8rem 1rem;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 8px;
  border-left: 3px solid #ffc107;
  font-weight: 500;
  position: relative;
}

.success-footer {
  text-align: center;
  font-size: 1.1rem;
  font-weight: 600;
  color: #2d5a27;
  padding: 1.5rem;
  background: linear-gradient(135deg, #e8f5e8 0%, #f1f8e9 100%);
  border-radius: 10px;
  border: 1px solid #c3e6cb;
}

.modal-footer {
  padding: 2rem 2.5rem;
  background: rgba(248, 249, 250, 0.8);
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  text-align: center;
}

.auto-close-timer {
  color: #6c757d;
  font-size: 1.1rem;
  font-weight: 700;
}

.auto-close-timer span {
  font-weight: 900;
  color: #28a745;
  font-size: 1.2rem;
}

/* Test Page Specific Styles */
.test-container {
  max-width: 800px;
  margin: 2rem auto;
  padding: 2rem;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 15px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.test-header {
  text-align: center;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
  border-radius: 12px;
}

.test-header h1 {
  margin: 0 0 0.5rem 0;
  font-size: 2rem;
}

.test-header p {
  margin: 0;
  font-size: 1.1rem;
  opacity: 0.9;
}

.test-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.test-btn {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
  padding: 1rem 2rem;
  border: none;
  border-radius: 25px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-block;
}

.test-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
}

.test-btn.secondary {
  background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .modal-overlay {
    padding: 10px;
  }
  
  .modal-content {
    max-width: 100%;
    width: 100%;
  }
  
  .success-modal-body {
    padding: 1rem 1.5rem;
  }
  
  .success-header {
    font-size: 1.3rem;
    padding: 0.8rem;
  }
  
  .section-title {
    font-size: 1.1rem;
  }
  
  .info-row {
    font-size: 0.95rem;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.3rem;
  }
  
  .highlight-text {
    font-size: 1rem;
  }
  
  .step-item {
    padding: 0.6rem 0.8rem;
  }
  
  .test-container {
    margin: 1rem;
    padding: 1.5rem;
  }
  
  .test-header h1 {
    font-size: 1.5rem;
  }
}
</style>

<div class="test-container">
  <div class="test-header">
    <h1>🧪 Success Message Test</h1>
    <p>Preview the enhanced registration success message design</p>
  </div>
  
  <div class="test-buttons">
    <button class="test-btn" onclick="showSuccessModal()">
      🎉 Show Success Message
    </button>
    <a href="pages/resident-registration.php" class="test-btn secondary">
      📝 Go to Registration Form
    </a>
    <a href="index.php" class="test-btn secondary">
      🏠 Back to Home
    </a>
  </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
  <!-- Success Modal -->
  <div class="modal-overlay" id="successModal">
    <div class="modal-content modal-success">
      <div class="modal-header">
        <h4>✅ Registration Successful!</h4>
        <button class="modal-close" onclick="closeModal('successModal')">&times;</button>
      </div>
      <div class="modal-body success-modal-body">
        <div class="success-message-container">
          <?php echo $_SESSION['success']; ?>
        </div>
      </div>
      <div class="modal-footer">
        <div class="auto-close-timer">
          <span>This window will automatically close in <span id="countdown">120</span> seconds</span>
        </div>
      </div>
    </div>
  </div>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<script>
function showSuccessModal() {
  // Redirect to this page to trigger the modal
  window.location.href = 'test-success-message.php';
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = 'none';
  }
}

// Auto-close timer
let timeRemaining = 120;
const countdownElement = document.getElementById('countdown');

if (countdownElement) {
  const timer = setInterval(() => {
    timeRemaining--;
    countdownElement.textContent = timeRemaining;
    
    if (timeRemaining <= 0) {
      clearInterval(timer);
      closeModal('successModal');
    }
  }, 1000);
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
  const modal = document.getElementById('successModal');
  if (modal && event.target === modal) {
    closeModal('successModal');
  }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    closeModal('successModal');
  }
});
</script>

<?php include 'includes/footer.php'; ?>