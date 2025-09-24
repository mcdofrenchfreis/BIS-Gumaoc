<?php
session_start();
$base_path = '../';
$page_title = 'Forms - Barangay Gumaoc East';
$page_description = 'Access various forms and documents for barangay services and applications.';

include '../includes/header.php';
?>

<div class="page-container">
    <div class="content-section">
        <div class="container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <div class="alert-icon">✅</div>
                    <div class="alert-content">
                        <strong>Success!</strong> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
                <div class="alert alert-success">
                    <div class="alert-icon">✅</div>
                    <div class="alert-content">
                        <strong>Success!</strong> Your application has been submitted successfully! You will receive a confirmation soon.
                    </div>
                </div>
            <?php endif; ?>

            <div class="forms-intro">
                <h2>Barangay Forms & Applications</h2>
                <p>Access and complete various forms for barangay services, registrations, and applications. Our digital forms make it easy and convenient to process your requests.</p>
            </div>
            
            <div class="forms-grid">
                <div class="form-card">
                    <div class="form-content">
                        <div class="form-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <h3>Census Registration</h3>
                        <p>Register as a resident of Barangay Gumaoc East. Complete your demographic information and household details for proper documentation.</p>
                        <div class="form-features">
                            <span class="feature">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10 9 9 9 8 9"></polyline>
                                </svg> Digital Form
                            </span>
                            <span class="feature">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
                                </svg> Quick Process
                            </span>
                            <span class="feature">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg> Email Confirmation
                            </span>
                        </div>
                    </div>
                    <a href="resident-registration.php" class="form-btn primary">Fill Out Form</a>
                </div>
                
                <div class="form-card">
                    <div class="form-content">
                        <div class="form-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="8" r="7"></circle>
                                <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
                            </svg>
                        </div>
                        <h3>Certificate Requests</h3>
                        <p>Request various barangay certificates such as Certificate of Residency, Indigency, Business Clearance, and other official documents.</p>
                        <div class="form-features">
                            <span class="feature">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                </svg> Multiple Types
                            </span>
                            <span class="feature">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg> Status Tracking
                            </span>
                            <span class="feature">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                </svg> Online Payment
                            </span>
                        </div>
                    </div>
                    <a href="certificate-request.php" class="form-btn primary">Request Certificate</a>
                </div>
                

                <div class="form-card">
                    <div class="form-content">
                        <div class="form-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                                <line x1="4" y1="22" x2="4" y2="15"></line>
                            </svg>
                        </div>
                        <h3>Assistance Applications</h3>
                        <p>Apply for various assistance programs offered by the barangay government including financial aid, medical assistance, and social services.</p>
                        <div class="form-features">
                            <span class="feature">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                </svg> Financial Aid
                            </span>
                            <span class="feature">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                                </svg> Medical Help
                            </span>
                            <span class="feature">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10 9 9 9 8 9"></polyline>
                                </svg> Documentation
                            </span>
                        </div>
                    </div>
                    <a href="#" class="form-btn secondary">Coming Soon</a>
                </div>

                <div class="form-card">
                    <div class="form-content">
                        <div class="form-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                        </div>
                        <h3>Incident Reports</h3>
                        <p>Report incidents, emergencies, or community concerns directly to barangay officials for immediate attention and proper action.</p>
                        <div class="form-features">
                            <span class="feature">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                                    <line x1="12" y1="18" x2="12.01" y2="18"></line>
                                </svg> 24/7 Available
                            </span>
                            <span class="feature">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"></path>
                                    <circle cx="12" cy="13" r="3"></circle>
                                </svg> Photo Upload
                            </span>
                            <span class="feature">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
                                </svg> Immediate Alert
                            </span>
                        </div>
                    </div>
                    <a href="report.php" class="form-btn emergency">Report Now</a>
                </div>
            </div>

            <div class="forms-help">
                <div class="help-card">
                    <h3>Need Help?</h3>
                    <p>If you need assistance with any form or have questions about the application process, don't hesitate to contact us.</p>
                    <div class="help-options">
                        <div class="help-option">
                            <div class="help-icon">📞</div>
                            <div class="help-info">
                                <h4>Call Us</h4>
                                <p>(044) 123-4567</p>
                            </div>
                        </div>
                        <div class="help-option">
                            <div class="help-icon">📧</div>
                            <div class="help-info">
                                <h4>Email Us</h4>
                                <p>info@gumaoceast.gov.ph</p>
                            </div>
                        </div>
                        <div class="help-option">
                            <div class="help-icon">🕒</div>
                            <div class="help-info">
                                <h4>Office Hours</h4>
                                <p>Mon-Fri: 8AM-5PM</p>
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

.alert {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 30px;
    border: 1px solid rgba(40, 167, 69, 0.3);
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 10px 30px rgba(40, 167, 69, 0.1);
}

.alert.alert-success {
    border-color: rgba(40, 167, 69, 0.3);
    background: rgba(232, 245, 233, 0.95);
}

.alert-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.alert-content {
    color: #333;
    line-height: 1.5;
}

.alert-content strong {
    color: #1b5e20;
}

.forms-intro {
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

.forms-intro::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(232, 245, 233, 0.3) 0%, rgba(255, 255, 255, 0.1) 50%, rgba(232, 245, 233, 0.3) 100%);
    z-index: -1;
}

.forms-intro h2 {
    color: #1b5e20;
    font-size: 2.5rem;
    margin-bottom: 20px;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 1;
}

.forms-intro p {
    color: #333;
    font-size: 1.2rem;
    max-width: 800px;
    margin: 0 auto;
    line-height: 1.8;
    position: relative;
    z-index: 1;
}

.forms-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 60px;
}

.form-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(27, 94, 32, 0.1);
    border: 1px solid rgba(27, 94, 32, 0.1);
    transition: all 0.3s ease;
    text-align: center;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-height: 450px;
}

.form-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(232, 245, 233, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
    z-index: 0;
}

.form-content {
    flex: 1;
    padding: 35px 35px 20px 35px;
    position: relative;
    z-index: 1;
}

.form-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 25px 50px rgba(27, 94, 32, 0.15);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(25px);
}

.form-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
}

.form-card h3 {
    color: #1b5e20;
    font-size: 1.8rem;
    margin-bottom: 15px;
    font-weight: 600;
}

.form-card p {
    color: #333;
    line-height: 1.6;
    margin-bottom: 20px;
    font-size: 1rem;
}

.form-features {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    margin-bottom: 25px;
}

.feature {
    background: rgba(232, 245, 233, 0.8);
    backdrop-filter: blur(10px);
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    color: #1b5e20;
    border: 1px solid rgba(76, 175, 80, 0.2);
    font-weight: 500;
}

.form-btn {
    display: block;
    padding: 15px 30px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    font-size: 1rem;
    position: relative;
    z-index: 1;
    margin: 20px;
    margin-top: auto;
    border: 2px solid transparent;
    text-align: center;
}

.form-btn.primary {
    background: rgba(27, 94, 32, 0.1);
    backdrop-filter: blur(10px);
    color: #1b5e20;
    border-color: #1b5e20;
}

.form-btn.primary:hover {
    background: rgba(27, 94, 32, 0.2);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(27, 94, 32, 0.2);
}

.form-btn.secondary {
    background: rgba(108, 117, 125, 0.1);
    backdrop-filter: blur(10px);
    color: #6c757d;
    border-color: #6c757d;
}

.form-btn.secondary:hover {
    background: rgba(108, 117, 125, 0.2);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.2);
}

.form-btn.emergency {
    background: rgba(220, 53, 69, 0.1);
    backdrop-filter: blur(10px);
    color: #dc3545;
    border-color: #dc3545;
}

.form-btn.emergency:hover {
    background: rgba(220, 53, 69, 0.2);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(220, 53, 69, 0.2);
}

.forms-help {
    display: flex;
    justify-content: center;
}

.help-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(27, 94, 32, 0.1);
    border: 1px solid rgba(27, 94, 32, 0.1);
    max-width: 800px;
    width: 100%;
    transition: all 0.3s ease;
}

.help-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 45px rgba(27, 94, 32, 0.15);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(25px);
}

.help-card h3 {
    color: #1b5e20;
    font-size: 2rem;
    text-align: center;
    margin-bottom: 20px;
    font-weight: 700;
    border-bottom: 3px solid #4caf50;
    padding-bottom: 15px;
}

.help-card p {
    color: #333;
    text-align: center;
    margin-bottom: 30px;
    font-size: 1.1rem;
    line-height: 1.6;
}

.help-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.help-option {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: rgba(232, 245, 233, 0.6);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    border: 1px solid rgba(76, 175, 80, 0.2);
    transition: all 0.3s ease;
}

.help-option:hover {
    background: rgba(232, 245, 233, 0.8);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.2);
}

.help-icon {
    font-size: 2rem;
    flex-shrink: 0;
}

.help-info h4 {
    color: #1b5e20;
    margin: 0 0 5px 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.help-info p {
    color: #333;
    margin: 0;
    font-size: 0.95rem;
    text-align: left;
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

.forms-intro,
.form-card,
.help-card {
    animation: fadeInUp 0.6s ease forwards;
}

.forms-intro { animation-delay: 0.1s; }
.form-card:nth-child(1) { animation-delay: 0.2s; }
.form-card:nth-child(2) { animation-delay: 0.3s; }
.form-card:nth-child(3) { animation-delay: 0.4s; }
.form-card:nth-child(4) { animation-delay: 0.5s; }
.form-card:nth-child(5) { animation-delay: 0.6s; }
.help-card { animation-delay: 0.7s; }

@media (max-width: 768px) {
    .page-container {
        background-attachment: scroll;
    }
    
    .forms-intro {
        padding: 30px 20px;
        margin-bottom: 40px;
    }
    
    .forms-intro h2 {
        font-size: 2rem;
    }
    
    .forms-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .form-card {
        margin: 0 10px;
    }
    
    .form-content {
        padding: 25px 25px 20px 25px;
    }
    
    .help-card {
        padding: 25px;
        margin: 0 10px;
    }
    
    .help-options {
        grid-template-columns: 1fr;
    }
    
    .content-section {
        padding: 30px 10px;
    }
}

@media (max-width: 480px) {
    .forms-intro {
        padding: 25px 15px;
    }
    
    .forms-intro h2 {
        font-size: 1.8rem;
    }
    
    .forms-intro p {
        font-size: 1.1rem;
    }
    
    .form-content {
        padding: 20px 15px 15px 15px;
    }
    
    .form-btn {
        margin: 15px;
    }
    
    .form-icon {
        font-size: 3rem;
    }
    
    .form-card h3 {
        font-size: 1.5rem;
    }
    
    .help-card {
        padding: 20px 15px;
    }
    
    .content-section {
        padding: 25px 10px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
