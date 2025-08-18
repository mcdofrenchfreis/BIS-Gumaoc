<?php
$page_title = "Services - Barangay Gumaoc East";
$page_description = "Explore the comprehensive e-services offered by Barangay Gumaoc East for residents' convenience.";
$base_path = "../";
include '../includes/header.php';
?>

<div class="page-container">
    <div class="content-section">
        <div class="container">
            <div class="services-intro">
                <h2>Digital Services at Your Fingertips</h2>
                <p>Our IoT-enabled e-services system brings government services directly to you, making it easier and more convenient to access the documents and assistance you need.</p>
            </div>

            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">📄</div>
                    <h3>Certificate Requests</h3>
                    <p>Request various barangay certificates online including Certificate of Residency, Certificate of Indigency, and Business Permits.</p>
                    <ul class="service-features">
                        <li>✅ Online application</li>
                        <li>✅ Real-time status tracking</li>
                        <li>✅ Digital copy delivery</li>
                        <li>✅ Scheduled pickup</li>
                    </ul>
                    <a href="../certificate-request.php" class="service-btn">Request Certificate</a>
                </div>

                <div class="service-card">
                    <div class="service-icon">👥</div>
                    <h3>Resident Registration</h3>
                    <p>Register as a new resident or update your existing information in our digital census system.</p>
                    <ul class="service-features">
                        <li>✅ Digital registration form</li>
                        <li>✅ Document upload</li>
                        <li>✅ Verification process</li>
                        <li>✅ Instant confirmation</li>
                    </ul>
                    <a href="../resident-registration.php" class="service-btn">Register Now</a>
                </div>

                <div class="service-card">
                    <div class="service-icon">🏢</div>
                    <h3>Business Applications</h3>
                    <p>Apply for business permits and licenses required to operate within the barangay jurisdiction.</p>
                    <ul class="service-features">
                        <li>✅ Online application</li>
                        <li>✅ Requirements checklist</li>
                        <li>✅ Processing timeline</li>
                        <li>✅ Payment integration</li>
                    </ul>
                    <a href="../business-application.php" class="service-btn">Apply for Permit</a>
                </div>

                <div class="service-card">
                    <div class="service-icon">🚨</div>
                    <h3>Incident Reporting</h3>
                    <p>Report incidents, emergencies, or community concerns directly to barangay officials for immediate action.</p>
                    <ul class="service-features">
                        <li>✅ 24/7 reporting system</li>
                        <li>✅ Photo/video evidence</li>
                        <li>✅ GPS location tracking</li>
                        <li>✅ Priority classification</li>
                    </ul>
                    <a href="report.php" class="service-btn emergency">Report Incident</a>
                </div>

                <div class="service-card">
                    <div class="service-icon">📊</div>
                    <h3>Application Tracking</h3>
                    <p>Track the status of your submitted applications and receive real-time updates on processing.</p>
                    <ul class="service-features">
                        <li>✅ Real-time status updates</li>
                        <li>✅ Processing timeline</li>
                        <li>✅ Notification alerts</li>
                        <li>✅ Document downloads</li>
                    </ul>
                    <a href="../applications.php" class="service-btn">Track Applications</a>
                </div>

                <div class="service-card">
                    <div class="service-icon">💬</div>
                    <h3>Community Feedback</h3>
                    <p>Share your feedback, suggestions, or concerns about barangay services and community development.</p>
                    <ul class="service-features">
                        <li>✅ Anonymous feedback option</li>
                        <li>✅ Category-based submissions</li>
                        <li>✅ Response tracking</li>
                        <li>✅ Public transparency</li>
                    </ul>
                    <a href="contact.php" class="service-btn">Send Feedback</a>
                </div>
            </div>

            <div class="service-hours">
                <div class="hours-card">
                    <h3>Service Hours</h3>
                    <div class="hours-content">
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
                            <span class="time">Emergency Only</span>
                        </div>
                    </div>
                    <p class="hours-note">💡 Online services are available 24/7. Processing may be delayed outside office hours.</p>
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

.services-intro {
    text-align: center;
    margin-bottom: 50px;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(25px);
    padding: 40px 30px;
    border-radius: 25px;
    box-shadow: 0 20px 40px rgba(27, 94, 32, 0.1);
    border: 1px solid rgba(27, 94, 32, 0.1);
    position: relative;
    overflow: hidden;
}

.services-intro::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(232, 245, 233, 0.3) 0%, rgba(255, 255, 255, 0.1) 50%, rgba(232, 245, 233, 0.3) 100%);
    z-index: -1;
}

.services-intro h2 {
    color: #1b5e20;
    font-size: 2.5rem;
    margin-bottom: 20px;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 1;
}

.services-intro p {
    color: #333;
    font-size: 1.2rem;
    max-width: 800px;
    margin: 0 auto;
    line-height: 1.8;
    position: relative;
    z-index: 1;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 60px;
}

.service-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(27, 94, 32, 0.1);
    border: 1px solid rgba(27, 94, 32, 0.1);
    transition: all 0.3s ease;
    text-align: center;
}

.service-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 25px 50px rgba(27, 94, 32, 0.15);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(25px);
}

.service-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
}

.service-card h3 {
    color: #1b5e20;
    font-size: 1.8rem;
    margin-bottom: 15px;
    font-weight: 600;
}

.service-card p {
    color: #333;
    line-height: 1.6;
    margin-bottom: 20px;
    font-size: 1rem;
}

.service-features {
    list-style: none;
    padding: 0;
    margin: 20px 0;
    text-align: left;
}

.service-features li {
    color: #333;
    margin-bottom: 8px;
    padding-left: 5px;
    font-size: 0.95rem;
}

.service-btn {
    display: inline-block;
    padding: 12px 25px;
    background: linear-gradient(135deg, #1b5e20, #4caf50);
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-top: 15px;
    box-shadow: 0 5px 15px rgba(27, 94, 32, 0.2);
}

.service-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(27, 94, 32, 0.3);
}

.service-btn.emergency {
    background: linear-gradient(135deg, #dc3545, #ff5722);
    box-shadow: 0 5px 15px rgba(220, 53, 69, 0.2);
}

.service-btn.emergency:hover {
    box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
}

.service-hours {
    display: flex;
    justify-content: center;
}

.hours-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(27, 94, 32, 0.1);
    border: 1px solid rgba(27, 94, 32, 0.1);
    max-width: 500px;
    width: 100%;
    transition: all 0.3s ease;
}

.hours-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 45px rgba(27, 94, 32, 0.15);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(25px);
}

.hours-card h3 {
    color: #1b5e20;
    font-size: 2rem;
    text-align: center;
    margin-bottom: 30px;
    border-bottom: 3px solid #4caf50;
    padding-bottom: 15px;
    font-weight: 700;
}

.hours-content {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 20px;
}

.hours-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid rgba(224, 224, 224, 0.5);
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
    font-weight: bold;
}

.hours-note {
    background: rgba(232, 245, 233, 0.8);
    backdrop-filter: blur(10px);
    padding: 15px;
    border-radius: 10px;
    color: #333;
    font-size: 0.95rem;
    line-height: 1.6;
    margin: 0;
    text-align: center;
    border: 1px solid rgba(76, 175, 80, 0.2);
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

.services-intro,
.service-card,
.hours-card {
    animation: fadeInUp 0.6s ease forwards;
}

.services-intro { animation-delay: 0.1s; }
.service-card:nth-child(1) { animation-delay: 0.2s; }
.service-card:nth-child(2) { animation-delay: 0.3s; }
.service-card:nth-child(3) { animation-delay: 0.4s; }
.service-card:nth-child(4) { animation-delay: 0.5s; }
.service-card:nth-child(5) { animation-delay: 0.6s; }
.service-card:nth-child(6) { animation-delay: 0.7s; }

.hours-card { animation-delay: 0.8s; }

@media (max-width: 768px) {
    .page-container {
        background-attachment: scroll;
    }
    
    .services-intro {
        padding: 30px 20px;
        margin-bottom: 40px;
    }
    
    .services-intro h2 {
        font-size: 2rem;
    }
    
    .services-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .service-card {
        padding: 20px;
        margin: 0 10px;
    }
    
    .hours-card {
        padding: 25px;
        margin: 0 10px;
    }
    
    .content-section {
        padding: 30px 10px;
    }
}

@media (max-width: 480px) {
    .services-intro {
        padding: 25px 15px;
    }
    
    .services-intro h2 {
        font-size: 1.8rem;
    }
    
    .services-intro p {
        font-size: 1.1rem;
    }
    
    .service-card {
        padding: 20px 15px;
    }
    
    .service-icon {
        font-size: 3rem;
    }
    
    .service-card h3 {
        font-size: 1.5rem;
    }
    
    .hours-card {
        padding: 20px 15px;
    }
    
    .content-section {
        padding: 25px 10px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>