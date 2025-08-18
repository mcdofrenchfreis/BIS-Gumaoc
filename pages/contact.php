<?php
$page_title = 'Contact Us - Barangay Gumaoc East';
$page_description = 'Get in touch with Barangay Gumaoc East for inquiries, concerns, and feedback.';
$base_path = '../';
include '../includes/header.php';
?>

<div class="page-container">
    <div class="content-section">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-form-section">
                    <div class="form-card">
                        <h2>Send Us a Message</h2>
                        <form id="contactForm" class="contact-form">
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
                                <select id="subject" name="subject" required>
                                    <option value="">Select a subject</option>
                                    <option value="general">General Inquiry</option>
                                    <option value="services">Service Information</option>
                                    <option value="complaint">Complaint</option>
                                    <option value="suggestion">Suggestion</option>
                                    <option value="technical">Technical Support</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="message">Message*</label>
                                <textarea id="message" name="message" rows="5" placeholder="Enter your message" required></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn-submit">
                                    📧 Send Message
                                </button>
                                <button type="reset" class="btn-reset">
                                    🔄 Clear Form
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="contact-info-section">
                    <div class="info-card">
                        <h3>📍 Office Address</h3>
                        <div class="info-content">
                            <p><strong>Barangay Hall, Gumaoc East</strong></p>
                            <p>City of San Jose del Monte</p>
                            <p>Bulacan, Philippines</p>
                            <p>Postal Code: 3023</p>
                        </div>
                    </div>

                    <div class="info-card">
                        <h3>📞 Phone Numbers</h3>
                        <div class="info-content">
                            <div class="contact-item">
                                <span class="contact-label">Main Office:</span>
                                <span class="contact-value">(044) 123-4567</span>
                            </div>
                            <div class="contact-item">
                                <span class="contact-label">Mobile:</span>
                                <span class="contact-value">+63 912 345 6789</span>
                            </div>
                            <div class="contact-item">
                                <span class="contact-label">Emergency:</span>
                                <span class="contact-value">(044) 123-4568</span>
                            </div>
                            <div class="contact-item">
                                <span class="contact-label">Fax:</span>
                                <span class="contact-value">(044) 123-4569</span>
                            </div>
                        </div>
                    </div>

                    <div class="info-card">
                        <h3>📧 Email Addresses</h3>
                        <div class="info-content">
                            <div class="contact-item">
                                <span class="contact-label">General:</span>
                                <span class="contact-value">info@gumaoceast.gov.ph</span>
                            </div>
                            <div class="contact-item">
                                <span class="contact-label">Services:</span>
                                <span class="contact-value">services@gumaoceast.gov.ph</span>
                            </div>
                            <div class="contact-item">
                                <span class="contact-label">Support:</span>
                                <span class="contact-value">support@gumaoceast.gov.ph</span>
                            </div>
                            <div class="contact-item">
                                <span class="contact-label">Documents:</span>
                                <span class="contact-value">documents@gumaoceast.gov.ph</span>
                            </div>
                        </div>
                    </div>

                    <div class="info-card">
                        <h3>🕒 Office Hours</h3>
                        <div class="info-content">
                            <div class="hours-item">
                                <span class="day">Monday - Friday</span>
                                <span class="time">8:00 AM - 5:00 PM</span>
                            </div>
                            <div class="hours-item">
                                <span class="day">Saturday</span>
                                <span class="time">8:00 AM - 12:00 PM</span>
                            </div>
                            <div class="hours-item">
                                <span class="day">Sunday</span>
                                <span class="time">Closed (Emergency Only)</span>
                            </div>
                        </div>
                        <div class="hours-note">
                            <p><strong>Note:</strong> For emergencies, our hotline is available 24/7.</p>
                        </div>
                    </div>

                    <div class="info-card">
                        <h3>🌐 Follow Us</h3>
                        <div class="social-links">
                            <a href="#" class="social-link facebook">📘 Facebook</a>
                            <a href="#" class="social-link twitter">🐦 Twitter</a>
                            <a href="#" class="social-link instagram">📷 Instagram</a>
                            <a href="#" class="social-link youtube">📺 YouTube</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="map-section">
                <div class="map-card">
                    <h3>📍 Find Us Here</h3>
                    <div class="map-placeholder">
                        <div class="map-content">
                            <div class="map-icon">🗺️</div>
                            <h4>Interactive Map</h4>
                            <p>Barangay Hall, Gumaoc East<br>San Jose del Monte, Bulacan</p>
                            <button class="map-btn">📍 Get Directions</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.page-container {
    min-height: 100vh;
    background-image: url('../assets/images/bg2.jpg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    background-repeat: no-repeat;
    position: relative;
}

.page-container::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(232, 245, 233, 0.75);
    backdrop-filter: blur(15px);
    z-index: -1;
}

.content-section {
    padding: 40px 20px;
    position: relative;
    z-index: 1;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
}

.contact-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 40px;
    margin-bottom: 40px;
}

.form-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(27, 94, 32, 0.1);
    border: 1px solid rgba(27, 94, 32, 0.1);
    transition: all 0.3s ease;
}

.form-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 45px rgba(27, 94, 32, 0.15);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(25px);
}

.form-card h2 {
    color: #1b5e20;
    font-size: 2rem;
    margin-bottom: 30px;
    text-align: center;
    font-weight: 700;
}

.contact-form {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    font-size: 1rem;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 12px 15px;
    border: 2px solid rgba(224, 224, 224, 0.8);
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #1b5e20;
    box-shadow: 0 0 0 3px rgba(27, 94, 32, 0.1);
    background: rgba(255, 255, 255, 0.95);
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.btn-submit,
.btn-reset {
    padding: 15px 30px;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    flex: 1;
    backdrop-filter: blur(15px);
}

.btn-submit {
    background: linear-gradient(135deg, rgba(27, 94, 32, 0.9), rgba(76, 175, 80, 0.9));
    color: white;
    border: 1px solid rgba(27, 94, 32, 0.3);
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(27, 94, 32, 0.3);
    background: linear-gradient(135deg, rgba(27, 94, 32, 0.95), rgba(76, 175, 80, 0.95));
}

.btn-reset {
    background: rgba(108, 117, 125, 0.9);
    color: white;
    border: 1px solid rgba(108, 117, 125, 0.3);
}

.btn-reset:hover {
    background: rgba(90, 98, 104, 0.95);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

.contact-info-section {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.info-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(27, 94, 32, 0.1);
    border: 1px solid rgba(27, 94, 32, 0.1);
    transition: all 0.3s ease;
}

.info-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(27, 94, 32, 0.15);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(25px);
}

.info-card h3 {
    color: #1b5e20;
    font-size: 1.3rem;
    margin-bottom: 20px;
    border-bottom: 2px solid #4caf50;
    padding-bottom: 10px;
    font-weight: 600;
}

.info-content {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.contact-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid rgba(240, 240, 240, 0.7);
}

.contact-item:last-child {
    border-bottom: none;
}

.contact-label {
    color: #666;
    font-weight: 500;
}

.contact-value {
    color: #1b5e20;
    font-weight: 600;
}

.hours-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid rgba(240, 240, 240, 0.7);
}

.hours-item:last-child {
    border-bottom: none;
}

.day {
    color: #666;
    font-weight: 500;
}

.time {
    color: #1b5e20;
    font-weight: 600;
}

.hours-note {
    margin-top: 15px;
    padding: 15px;
    background: rgba(232, 245, 233, 0.8);
    backdrop-filter: blur(10px);
    border-radius: 8px;
    border: 1px solid rgba(76, 175, 80, 0.2);
}

.hours-note p {
    margin: 0;
    color: #333;
    font-size: 0.9rem;
    line-height: 1.5;
}

.social-links {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.social-link {
    display: block;
    padding: 12px 15px;
    background: rgba(232, 245, 233, 0.6);
    backdrop-filter: blur(10px);
    color: #1b5e20;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-weight: 500;
    border: 1px solid rgba(76, 175, 80, 0.2);
}

.social-link:hover {
    background: rgba(232, 245, 233, 0.8);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.2);
}

.map-section {
    margin-top: 40px;
}

.map-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(27, 94, 32, 0.1);
    border: 1px solid rgba(27, 94, 32, 0.1);
    transition: all 0.3s ease;
}

.map-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 45px rgba(27, 94, 32, 0.15);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(25px);
}

.map-card h3 {
    color: #1b5e20;
    font-size: 1.8rem;
    margin-bottom: 25px;
    text-align: center;
    border-bottom: 3px solid #4caf50;
    padding-bottom: 15px;
    font-weight: 700;
}

.map-placeholder {
    height: 300px;
    background: linear-gradient(135deg, rgba(232, 245, 233, 0.6), rgba(255, 255, 255, 0.3));
    backdrop-filter: blur(15px);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px dashed rgba(76, 175, 80, 0.5);
    transition: all 0.3s ease;
}

.map-placeholder:hover {
    background: linear-gradient(135deg, rgba(232, 245, 233, 0.8), rgba(255, 255, 255, 0.4));
    border-color: rgba(76, 175, 80, 0.7);
}

.map-content {
    text-align: center;
}

.map-icon {
    font-size: 4rem;
    margin-bottom: 15px;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
}

.map-content h4 {
    color: #1b5e20;
    font-size: 1.5rem;
    margin-bottom: 10px;
    font-weight: 600;
}

.map-content p {
    color: #666;
    margin-bottom: 20px;
    line-height: 1.6;
}

.map-btn {
    padding: 12px 25px;
    background: linear-gradient(135deg, rgba(27, 94, 32, 0.9), rgba(76, 175, 80, 0.9));
    backdrop-filter: blur(10px);
    color: white;
    border: 1px solid rgba(27, 94, 32, 0.3);
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.map-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(27, 94, 32, 0.3);
    background: linear-gradient(135deg, rgba(27, 94, 32, 0.95), rgba(76, 175, 80, 0.95));
}

/* Animation for cards */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-card,
.info-card,
.map-card {
    animation: fadeInUp 0.6s ease forwards;
}

.form-card { animation-delay: 0.1s; }
.info-card:nth-child(1) { animation-delay: 0.2s; }
.info-card:nth-child(2) { animation-delay: 0.3s; }
.info-card:nth-child(3) { animation-delay: 0.4s; }
.info-card:nth-child(4) { animation-delay: 0.5s; }
.info-card:nth-child(5) { animation-delay: 0.6s; }
.map-card { animation-delay: 0.7s; }

@media (max-width: 768px) {
    .page-container {
        background-attachment: scroll;
    }
    
    .contact-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .form-card {
        padding: 25px;
        margin: 0 10px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .contact-info-section {
        order: -1;
        margin: 0 10px;
    }
    
    .info-card {
        padding: 20px;
    }
    
    .map-card {
        padding: 25px;
        margin: 0 10px;
    }
    
    .map-placeholder {
        height: 250px;
    }
    
    .content-section {
        padding: 30px 10px;
    }
}

@media (max-width: 480px) {
    .form-card {
        padding: 20px 15px;
    }
    
    .info-card {
        padding: 15px;
    }
    
    .map-card {
        padding: 20px 15px;
    }
    
    .content-section {
        padding: 25px 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = form.querySelector('.btn-submit');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = '📤 Sending...';
        submitBtn.disabled = true;
        
        // Simulate form submission (replace with actual AJAX call)
        setTimeout(() => {
            alert('✅ Message sent successfully! We will get back to you within 24 hours.');
            form.reset();
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }, 2000);
    });
    
    // Map button functionality
    const mapBtn = document.querySelector('.map-btn');
    if (mapBtn) {
        mapBtn.addEventListener('click', function() {
            // Open Google Maps with the location
            const address = "Barangay Hall, Gumaoc East, San Jose del Monte, Bulacan, Philippines";
            const googleMapsUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(address)}`;
            window.open(googleMapsUrl, '_blank');
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>