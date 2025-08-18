<?php
$page_title = "About Us - Barangay Gumaoc East";
$page_description = "Learn about Barangay Gumaoc East's mission, vision, and commitment to serving our community through innovative e-services.";
$base_path = "../";
include '../includes/header.php';
?>

<div class="page-container">
    <div class="content-section">
        <div class="container">
            <div class="about-grid">
                <div class="main-content">
                    <div class="content-card">
                        <h2>Our Story</h2>
                        <p>Barangay Gumaoc East has been at the forefront of community service and development in San Jose del Monte, Bulacan. Our commitment to serving our residents with excellence has driven us to embrace technology and innovation in our service delivery.</p>
                        
                        <p>Through our IoT-enabled e-services system, we've revolutionized how residents access government services, making them more efficient, transparent, and accessible to everyone in our community.</p>
                    </div>

                    <div class="content-card">
                        <h2>Mission</h2>
                        <p>To provide efficient, transparent, and accessible government services to all residents of Barangay Gumaoc East through innovative technology solutions while fostering community development and civic engagement.</p>
                    </div>

                    <div class="content-card">
                        <h2>Vision</h2>
                        <p>To be a model barangay that exemplifies good governance, technological innovation, and community empowerment, creating a safe, prosperous, and sustainable environment for all residents.</p>
                    </div>

                    <div class="content-card">
                        <h2>Core Values</h2>
                        <div class="values-grid">
                            <div class="value-item">
                                <div class="value-icon">🤝</div>
                                <h4>Integrity</h4>
                                <p>We uphold honesty, transparency, and accountability in all our actions.</p>
                            </div>
                            <div class="value-item">
                                <div class="value-icon">🎯</div>
                                <h4>Excellence</h4>
                                <p>We strive for the highest quality in service delivery and community development.</p>
                            </div>
                            <div class="value-item">
                                <div class="value-icon">🚀</div>
                                <h4>Innovation</h4>
                                <p>We embrace technology and creative solutions to better serve our community.</p>
                            </div>
                            <div class="value-item">
                                <div class="value-icon">❤️</div>
                                <h4>Compassion</h4>
                                <p>We serve with empathy and understanding for every resident's needs.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sidebar">
                    <div class="info-card">
                        <h3>Quick Facts</h3>
                        <div class="fact-list">
                            <div class="fact-item">
                                <span class="fact-label">Established:</span>
                                <span class="fact-value">1978</span>
                            </div>
                            <div class="fact-item">
                                <span class="fact-label">Population:</span>
                                <span class="fact-value">15,000+</span>
                            </div>
                            <div class="fact-item">
                                <span class="fact-label">Area:</span>
                                <span class="fact-value">5.2 sq km</span>
                            </div>
                            <div class="fact-item">
                                <span class="fact-label">Households:</span>
                                <span class="fact-value">3,500+</span>
                            </div>
                        </div>
                    </div>

                    <div class="info-card">
                        <h3>Leadership</h3>
                        <div class="leadership-list">
                            <div class="leader-item">
                                <h4>Barangay Captain</h4>
                                <p>Hon. Maria Santos</p>
                            </div>
                            <div class="leader-item">
                                <h4>Barangay Secretary</h4>
                                <p>Ms. Ana Cruz</p>
                            </div>
                            <div class="leader-item">
                                <h4>Barangay Treasurer</h4>
                                <p>Mr. Juan Dela Cruz</p>
                            </div>
                        </div>
                    </div>

                    <div class="info-card">
                        <h3>Office Hours</h3>
                        <div class="hours-list">
                            <div class="hours-item">
                                <span>Monday - Friday</span>
                                <span>8:00 AM - 5:00 PM</span>
                            </div>
                            <div class="hours-item">
                                <span>Saturday</span>
                                <span>8:00 AM - 12:00 PM</span>
                            </div>
                            <div class="hours-item">
                                <span>Sunday</span>
                                <span>Closed</span>
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
    backdrop-filter: blur(12px);
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

.about-grid {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 40px;
}

.main-content {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.content-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(27, 94, 32, 0.1);
    border: 1px solid rgba(27, 94, 32, 0.1);
    transition: all 0.3s ease;
}

.content-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 45px rgba(27, 94, 32, 0.15);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(25px);
}

.content-card h2 {
    color: #1b5e20;
    font-size: 2rem;
    margin-bottom: 20px;
    border-bottom: 3px solid #4caf50;
    padding-bottom: 10px;
    font-weight: 700;
}

.content-card p {
    color: #333;
    line-height: 1.8;
    margin-bottom: 15px;
    font-size: 1.1rem;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.value-item {
    text-align: center;
    padding: 25px 20px;
    background: rgba(232, 245, 233, 0.6);
    backdrop-filter: blur(15px);
    border-radius: 15px;
    border: 1px solid rgba(76, 175, 80, 0.2);
    transition: all 0.3s ease;
}

.value-item:hover {
    background: rgba(232, 245, 233, 0.8);
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(76, 175, 80, 0.2);
    backdrop-filter: blur(20px);
}

.value-icon {
    font-size: 3rem;
    margin-bottom: 15px;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
}

.value-item h4 {
    color: #1b5e20;
    font-size: 1.3rem;
    margin-bottom: 10px;
    font-weight: 600;
}

.value-item p {
    color: #666;
    font-size: 0.95rem;
    line-height: 1.6;
    margin: 0;
}

.sidebar {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.info-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    padding: 25px;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(27, 94, 32, 0.1);
    border: 1px solid rgba(27, 94, 32, 0.1);
    transition: all 0.3s ease;
}

.info-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 18px 40px rgba(27, 94, 32, 0.15);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(25px);
}

.info-card h3 {
    color: #1b5e20;
    font-size: 1.5rem;
    margin-bottom: 20px;
    text-align: center;
    border-bottom: 2px solid #4caf50;
    padding-bottom: 10px;
    font-weight: 600;
}

.fact-list,
.leadership-list,
.hours-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.fact-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid rgba(224, 224, 224, 0.5);
}

.fact-item:last-child {
    border-bottom: none;
}

.fact-label {
    color: #666;
    font-weight: 500;
}

.fact-value {
    color: #1b5e20;
    font-weight: 700;
    font-size: 1.1rem;
}

.leader-item,
.hours-item {
    padding: 12px 0;
    border-bottom: 1px solid rgba(224, 224, 224, 0.5);
}

.leader-item:last-child,
.hours-item:last-child {
    border-bottom: none;
}

.leader-item h4 {
    color: #1b5e20;
    margin: 0 0 5px 0;
    font-size: 1rem;
    font-weight: 600;
}

.leader-item p {
    color: #333;
    margin: 0;
    font-weight: 500;
}

.hours-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.hours-item span:first-child {
    color: #666;
    font-weight: 500;
}

.hours-item span:last-child {
    color: #1b5e20;
    font-weight: 700;
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

.content-card,
.info-card {
    animation: fadeInUp 0.6s ease forwards;
}

.content-card:nth-child(1) { animation-delay: 0.1s; }
.content-card:nth-child(2) { animation-delay: 0.2s; }
.content-card:nth-child(3) { animation-delay: 0.3s; }
.content-card:nth-child(4) { animation-delay: 0.4s; }

.info-card:nth-child(1) { animation-delay: 0.5s; }
.info-card:nth-child(2) { animation-delay: 0.6s; }
.info-card:nth-child(3) { animation-delay: 0.7s; }

@media (max-width: 768px) {
    .page-container {
        background-attachment: scroll;
    }
    
    .about-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .content-card,
    .info-card {
        padding: 20px;
        margin: 0 10px;
    }
    
    .values-grid {
        grid-template-columns: 1fr;
    }
    
    .sidebar {
        order: -1;
    }
    
    .content-section {
        padding: 30px 10px;
    }
}

@media (max-width: 480px) {
    .content-card h2 {
        font-size: 1.7rem;
    }
    
    .value-item {
        padding: 20px 15px;
    }
    
    .value-icon {
        font-size: 2.5rem;
    }
    
    .content-section {
        padding: 25px 10px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>