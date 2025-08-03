<?php
require_once 'auth_check.php';
$page_title = 'E-Services';
$base_path = '../';

include '../includes/header.php';
?>

<style>
.e-services-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 20px;
}

.e-services-container {
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.page-header h1 {
    color: #333;
    margin-bottom: 10px;
    font-size: 32px;
}

.page-header p {
    color: #666;
    font-size: 18px;
}

.back-btn {
    display: inline-block;
    padding: 12px 30px;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-bottom: 20px;
}

.back-btn:hover {
    background: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(108, 117, 125, 0.3);
    color: white;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

.service-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.service-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.service-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 32px;
    color: white;
}

.service-card h3 {
    color: #333;
    margin-bottom: 15px;
    font-size: 24px;
}

.service-card p {
    color: #666;
    margin-bottom: 25px;
    line-height: 1.6;
}

.service-features {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 25px;
    justify-content: center;
}

.feature-tag {
    background: #f8f9fa;
    color: #667eea;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
}

.service-btn {
    display: inline-block;
    padding: 12px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    margin: 5px;
}

.service-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    color: white;
}

.service-btn.secondary {
    background: #6c757d;
}

.service-btn.secondary:hover {
    background: #5a6268;
    box-shadow: 0 10px 20px rgba(108, 117, 125, 0.3);
}

.service-btn.danger {
    background: #dc3545;
}

.service-btn.danger:hover {
    background: #c82333;
    box-shadow: 0 10px 20px rgba(220, 53, 69, 0.3);
}

.service-status {
    position: absolute;
    top: 15px;
    right: 15px;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-available {
    background: #d4edda;
    color: #155724;
}

.status-coming-soon {
    background: #fff3cd;
    color: #856404;
}

.status-maintenance {
    background: #f8d7da;
    color: #721c24;
}

.quick-actions {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
}

.quick-actions h2 {
    color: #333;
    margin-bottom: 20px;
    font-size: 24px;
    text-align: center;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 15px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s ease;
}

.quick-action-btn:hover {
    background: #e9ecef;
    transform: translateY(-2px);
    color: #333;
}

.quick-action-icon {
    font-size: 24px;
    margin-bottom: 10px;
    color: #667eea;
}

.quick-action-text {
    font-weight: 600;
    text-align: center;
}

@media (max-width: 768px) {
    .services-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<div class="e-services-page">
    <div class="e-services-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>💻 E-Services Portal</h1>
            <p>Access all available electronic services from the comfort of your home</p>
        </div>

        <!-- Back Button -->
        <a href="dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="quick-actions-grid">
                <a href="#document-requests" class="quick-action-btn">
                    <div class="quick-action-icon">📄</div>
                    <div class="quick-action-text">Document Requests</div>
                </a>
                <a href="#business-services" class="quick-action-btn">
                    <div class="quick-action-icon">🏢</div>
                    <div class="quick-action-text">Business Services</div>
                </a>
                <a href="#community-services" class="quick-action-btn">
                    <div class="quick-action-icon">👥</div>
                    <div class="quick-action-text">Community Services</div>
                </a>
                <a href="#emergency-services" class="quick-action-btn">
                    <div class="quick-action-icon">🚨</div>
                    <div class="quick-action-text">Emergency Services</div>
                </a>
            </div>
        </div>

        <!-- Services Grid -->
        <div class="services-grid">
            <!-- Document Requests -->
            <div class="service-card" id="document-requests">
                <div class="service-status status-available">Available</div>
                <div class="service-icon">📄</div>
                <h3>Document Requests</h3>
                <p>Request official documents, certificates, and clearances online. Fast processing and digital delivery available.</p>
                <div class="service-features">
                    <span class="feature-tag">Barangay Clearance</span>
                    <span class="feature-tag">Indigency Certificate</span>
                    <span class="feature-tag">Residency Certificate</span>
                </div>
                <a href="../pages/certificate-request.php" class="service-btn">Request Document</a>
                <a href="#" class="service-btn secondary">Track Status</a>
            </div>

            <!-- Business Applications -->
            <div class="service-card" id="business-services">
                <div class="service-status status-available">Available</div>
                <div class="service-icon">🏢</div>
                <h3>Business Applications</h3>
                <p>Apply for business permits, licenses, and registrations. Streamlined process for entrepreneurs and business owners.</p>
                <div class="service-features">
                    <span class="feature-tag">Business Permit</span>
                    <span class="feature-tag">Market Stall</span>
                    <span class="feature-tag">Home Business</span>
                </div>
                <a href="../pages/business-application.php" class="service-btn">Apply Now</a>
                <a href="#" class="service-btn secondary">Requirements</a>
            </div>

            <!-- Community Census -->
            <div class="service-card" id="community-services">
                <div class="service-status status-available">Available</div>
                <div class="service-icon">👥</div>
                <h3>Community Census</h3>
                <p>Register as a resident and contribute to our comprehensive community database. Help us serve you better.</p>
                <div class="service-features">
                    <span class="feature-tag">Resident Registration</span>
                    <span class="feature-tag">Family Profile</span>
                    <span class="feature-tag">Update Information</span>
                </div>
                <a href="../pages/resident-registration.php" class="service-btn">Register Now</a>
                <a href="#" class="service-btn secondary">Update Profile</a>
            </div>

            <!-- Emergency Response -->
            <div class="service-card" id="emergency-services">
                <div class="service-status status-available">Available</div>
                <div class="service-icon">🚨</div>
                <h3>Emergency Response</h3>
                <p>Report emergencies and incidents in real-time. Get immediate response from our emergency services team.</p>
                <div class="service-features">
                    <span class="feature-tag">24/7 Available</span>
                    <span class="feature-tag">Real-time Tracking</span>
                    <span class="feature-tag">Emergency Contacts</span>
                </div>
                <a href="reports.php" class="service-btn">Report Emergency</a>
                <a href="#" class="service-btn danger">Emergency Hotline</a>
            </div>

            <!-- Health Services -->
            <div class="service-card">
                <div class="service-status status-coming-soon">Coming Soon</div>
                <div class="service-icon">🏥</div>
                <h3>Health Services</h3>
                <p>Access health-related services including medical certificates, health consultations, and vaccination schedules.</p>
                <div class="service-features">
                    <span class="feature-tag">Medical Certificate</span>
                    <span class="feature-tag">Health Consultation</span>
                    <span class="feature-tag">Vaccination</span>
                </div>
                <a href="#" class="service-btn secondary">Notify Me</a>
            </div>

            <!-- Infrastructure Requests -->
            <div class="service-card">
                <div class="service-status status-available">Available</div>
                <div class="service-icon">🏗️</div>
                <h3>Infrastructure Requests</h3>
                <p>Report infrastructure issues, request repairs, and track maintenance projects in your area.</p>
                <div class="service-features">
                    <span class="feature-tag">Road Repairs</span>
                    <span class="feature-tag">Street Lights</span>
                    <span class="feature-tag">Drainage</span>
                </div>
                <a href="reports.php" class="service-btn">Report Issue</a>
                <a href="#" class="service-btn secondary">Track Progress</a>
            </div>

            <!-- Social Services -->
            <div class="service-card">
                <div class="service-status status-coming-soon">Coming Soon</div>
                <div class="service-icon">🤝</div>
                <h3>Social Services</h3>
                <p>Access social welfare programs, financial assistance, and community support services.</p>
                <div class="service-features">
                    <span class="feature-tag">Financial Aid</span>
                    <span class="feature-tag">Senior Services</span>
                    <span class="feature-tag">Disability Support</span>
                </div>
                <a href="#" class="service-btn secondary">Notify Me</a>
            </div>

            <!-- Events & Activities -->
            <div class="service-card">
                <div class="service-status status-available">Available</div>
                <div class="service-icon">🎉</div>
                <h3>Events & Activities</h3>
                <p>Stay updated with community events, activities, and programs. Register for upcoming events.</p>
                <div class="service-features">
                    <span class="feature-tag">Community Events</span>
                    <span class="feature-tag">Sports Activities</span>
                    <span class="feature-tag">Cultural Programs</span>
                </div>
                <a href="#" class="service-btn">View Events</a>
                <a href="#" class="service-btn secondary">Register</a>
            </div>
        </div>

        <!-- Service Information -->
        <div class="quick-actions">
            <h2>Service Information</h2>
            <div class="quick-actions-grid">
                <div class="quick-action-btn">
                    <div class="quick-action-icon">📞</div>
                    <div class="quick-action-text">Contact Support</div>
                </div>
                <div class="quick-action-btn">
                    <div class="quick-action-icon">📋</div>
                    <div class="quick-action-text">Requirements Guide</div>
                </div>
                <div class="quick-action-btn">
                    <div class="quick-action-icon">⏰</div>
                    <div class="quick-action-text">Processing Times</div>
                </div>
                <div class="quick-action-btn">
                    <div class="quick-action-icon">💰</div>
                    <div class="quick-action-text">Service Fees</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 