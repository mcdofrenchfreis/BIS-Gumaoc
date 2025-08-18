<?php
$page_title = "Report Incident - Barangay Gumaoc East";
$page_description = "Report incidents and emergencies in your area using our IoT-enabled reporting system.";
$base_path = "../";
include '../includes/header.php';
?>

<div class="page-container">
    <div class="content-section">
        <div class="container">
            <div class="report-grid">
                <div class="report-form-section">
                    <div class="form-card">
                        <h2>Report an Incident</h2>
                        <form id="incidentForm" class="incident-form">
                            <div class="form-group">
                                <label for="incidentType">Incident Type</label>
                                <select id="incidentType" name="incident_type" required>
                                    <option value="">Select incident type</option>
                                    <option value="emergency">🚨 Emergency</option>
                                    <option value="fire">🔥 Fire</option>
                                    <option value="accident">🚗 Traffic Accident</option>
                                    <option value="crime">👮 Crime/Security</option>
                                    <option value="infrastructure">🏗️ Infrastructure Issue</option>
                                    <option value="flooding">🌊 Flooding</option>
                                    <option value="power_outage">⚡ Power Outage</option>
                                    <option value="other">❓ Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="urgencyLevel">Urgency Level</label>
                                <select id="urgencyLevel" name="urgency_level" required>
                                    <option value="">Select urgency level</option>
                                    <option value="critical">🔴 Critical - Life threatening</option>
                                    <option value="high">🟠 High - Urgent attention needed</option>
                                    <option value="medium">🟡 Medium - Should be addressed soon</option>
                                    <option value="low">🟢 Low - Non-urgent</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="location">Location</label>
                                <input type="text" id="location" name="location" placeholder="Street address or landmark" required>
                                <button type="button" id="getCurrentLocation" class="location-btn">
                                    📍 Use Current Location
                                </button>
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" rows="4" placeholder="Provide detailed description of the incident..." required></textarea>
                            </div>

                            <div class="form-group">
                                <label for="reporterName">Your Name</label>
                                <input type="text" id="reporterName" name="reporter_name" placeholder="Full name" required>
                            </div>

                            <div class="form-group">
                                <label for="reporterContact">Contact Number</label>
                                <input type="tel" id="reporterContact" name="reporter_contact" placeholder="Phone number" required>
                            </div>

                            <div class="form-group">
                                <label for="incidentPhoto">Photo Evidence (Optional)</label>
                                <input type="file" id="incidentPhoto" name="incident_photo" accept="image/*">
                                <small class="form-note">Upload photos to help authorities understand the situation better</small>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn-submit">
                                    🚨 Submit Report
                                </button>
                                <button type="reset" class="btn-reset">
                                    🔄 Clear Form
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="info-section">
                    <div class="emergency-contacts">
                        <h3>Emergency Contacts</h3>
                        <div class="contact-list">
                            <div class="contact-item">
                                <div class="contact-icon">🚨</div>
                                <div class="contact-info">
                                    <h4>Barangay Emergency</h4>
                                    <p class="contact-number">(XXX) XXX-XXXX</p>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <div class="contact-icon">🚒</div>
                                <div class="contact-info">
                                    <h4>Fire Department</h4>
                                    <p class="contact-number">116</p>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <div class="contact-icon">👮</div>
                                <div class="contact-info">
                                    <h4>Police</h4>
                                    <p class="contact-number">117</p>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <div class="contact-icon">🏥</div>
                                <div class="contact-info">
                                    <h4>Medical Emergency</h4>
                                    <p class="contact-number">911</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="safety-tips">
                        <h3>Safety Tips</h3>
                        <ul class="tips-list">
                            <li>🔥 For fires: Get to safety first, then report</li>
                            <li>🚗 For accidents: Check for injuries, call emergency services</li>
                            <li>👮 For crimes: Do not confront, report immediately</li>
                            <li>🌊 For flooding: Avoid flooded roads and areas</li>
                            <li>⚡ For power outages: Report to proper authorities</li>
                            <li>📱 Keep your phone charged for emergencies</li>
                        </ul>
                    </div>

                    <div class="reporting-process">
                        <h3>What Happens Next?</h3>
                        <div class="process-timeline">
                            <div class="timeline-item">
                                <div class="timeline-number">1</div>
                                <div class="timeline-content">
                                    <h4>Report Received</h4>
                                    <p>Your report is logged in our system immediately</p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-number">2</div>
                                <div class="timeline-content">
                                    <h4>Assessment</h4>
                                    <p>Our team evaluates the urgency and assigns resources</p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-number">3</div>
                                <div class="timeline-content">
                                    <h4>Response</h4>
                                    <p>Appropriate authorities are dispatched to address the incident</p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-number">4</div>
                                <div class="timeline-content">
                                    <h4>Follow-up</h4>
                                    <p>You'll be contacted if additional information is needed</p>
                                </div>
                            </div>
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

.report-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 40px;
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
    color: #dc3545;
    font-size: 2rem;
    margin-bottom: 30px;
    text-align: center;
    font-weight: 700;
}

.incident-form {
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
    border-color: #dc3545;
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    background: rgba(255, 255, 255, 0.95);
}

.location-btn {
    margin-top: 10px;
    padding: 8px 15px;
    background: rgba(40, 167, 69, 0.9);
    backdrop-filter: blur(10px);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    font-weight: 500;
}

.location-btn:hover {
    background: rgba(33, 136, 56, 0.95);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.form-note {
    color: #666;
    font-size: 0.85rem;
    margin-top: 5px;
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
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.9), rgba(255, 87, 34, 0.9));
    color: white;
    border: 1px solid rgba(220, 53, 69, 0.3);
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(220, 53, 69, 0.3);
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.95), rgba(255, 87, 34, 0.95));
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

.info-section {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.emergency-contacts,
.safety-tips,
.reporting-process {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(27, 94, 32, 0.1);
    border: 1px solid rgba(27, 94, 32, 0.1);
    transition: all 0.3s ease;
}

.emergency-contacts:hover,
.safety-tips:hover,
.reporting-process:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(27, 94, 32, 0.15);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(25px);
}

.emergency-contacts h3,
.safety-tips h3,
.reporting-process h3 {
    color: #dc3545;
    font-size: 1.5rem;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 700;
}

.contact-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: rgba(220, 53, 69, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 10px;
    border: 1px solid rgba(220, 53, 69, 0.2);
    transition: all 0.3s ease;
}

.contact-item:hover {
    background: rgba(220, 53, 69, 0.15);
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(220, 53, 69, 0.2);
}

.contact-icon {
    font-size: 2rem;
    width: 50px;
    text-align: center;
}

.contact-info h4 {
    color: #333;
    margin: 0 0 5px 0;
    font-size: 1rem;
    font-weight: 600;
}

.contact-number {
    color: #dc3545;
    font-weight: bold;
    font-size: 1.1rem;
    margin: 0;
}

.tips-list {
    list-style: none;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.tips-list li {
    padding: 10px 15px;
    background: rgba(40, 167, 69, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 8px;
    color: #333;
    line-height: 1.5;
    border: 1px solid rgba(40, 167, 69, 0.2);
    transition: all 0.3s ease;
}

.tips-list li:hover {
    background: rgba(40, 167, 69, 0.15);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.2);
}

.process-timeline {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.timeline-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.timeline-number {
    width: 30px;
    height: 30px;
    background: linear-gradient(135deg, #dc3545, #ff5722);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
    font-size: 0.9rem;
}

.timeline-content h4 {
    color: #333;
    margin: 0 0 5px 0;
    font-size: 1rem;
    font-weight: 600;
}

.timeline-content p {
    color: #666;
    margin: 0;
    line-height: 1.5;
    font-size: 0.9rem;
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
.emergency-contacts,
.safety-tips,
.reporting-process {
    animation: fadeInUp 0.6s ease forwards;
}

.form-card { animation-delay: 0.1s; }
.emergency-contacts { animation-delay: 0.2s; }
.safety-tips { animation-delay: 0.3s; }
.reporting-process { animation-delay: 0.4s; }

@media (max-width: 768px) {
    .page-container {
        background-attachment: scroll;
    }
    
    .report-grid {
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
    
    .info-section {
        order: -1;
        margin: 0 10px;
    }
    
    .emergency-contacts,
    .safety-tips,
    .reporting-process {
        padding: 20px;
    }
    
    .content-section {
        padding: 30px 10px;
    }
}

@media (max-width: 480px) {
    .form-card {
        padding: 20px 15px;
    }
    
    .emergency-contacts,
    .safety-tips,
    .reporting-process {
        padding: 15px;
    }
    
    .content-section {
        padding: 25px 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('incidentForm');
    const getCurrentLocationBtn = document.getElementById('getCurrentLocation');
    const locationInput = document.getElementById('location');
    
    // Get current location
    getCurrentLocationBtn.addEventListener('click', function() {
        if (navigator.geolocation) {
            getCurrentLocationBtn.textContent = '📍 Getting location...';
            getCurrentLocationBtn.disabled = true;
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    locationInput.value = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
                    getCurrentLocationBtn.textContent = '✅ Location obtained';
                    
                    setTimeout(() => {
                        getCurrentLocationBtn.textContent = '📍 Use Current Location';
                        getCurrentLocationBtn.disabled = false;
                    }, 2000);
                },
                function(error) {
                    alert('Unable to get your location. Please enter it manually.');
                    getCurrentLocationBtn.textContent = '📍 Use Current Location';
                    getCurrentLocationBtn.disabled = false;
                }
            );
        } else {
            alert('Geolocation is not supported by this browser.');
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = form.querySelector('.btn-submit');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = '📤 Submitting...';
        submitBtn.disabled = true;
        
        // Simulate form submission (replace with actual AJAX call)
        setTimeout(() => {
            alert('✅ Incident report submitted successfully! You will be contacted if additional information is needed.');
            form.reset();
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }, 2000);
    });
});
</script>

<?php include '../includes/footer.php'; ?>