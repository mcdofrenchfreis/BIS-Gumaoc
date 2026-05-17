<?php
/**
 * Portal service cards (formerly on e-services page).
 */
?>
<div class="portal-services">
    <div class="quick-actions">
        <h2>Quick Actions</h2>
        <div class="quick-actions-grid">
            <a href="#document-requests" class="quick-action-btn">
                <div class="quick-action-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="quick-action-text">Document Requests</div>
            </a>
            <a href="#business-services" class="quick-action-btn">
                <div class="quick-action-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="quick-action-text">Business Services</div>
            </a>
        </div>
    </div>

    <div class="portal-services-grid">
        <div class="portal-service-card" id="document-requests">
            <span class="service-status">Available</span>
            <div class="service-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <h3>Document Requests</h3>
            <p>Request official documents, certificates, and clearances online. Fast processing and digital delivery available.</p>
            <div class="service-features">
                <span class="feature-tag">Barangay Clearance</span>
                <span class="feature-tag">Indigency Certificate</span>
                <span class="feature-tag">Residency Certificate</span>
            </div>
            <a href="certificate-request.php" class="service-btn">Request Document</a>
            <a href="my-requests.php" class="service-btn secondary">Track Status</a>
        </div>

        <div class="portal-service-card" id="business-services">
            <span class="service-status">Available</span>
            <div class="service-icon">
                <i class="fas fa-building"></i>
            </div>
            <h3>Business Applications</h3>
            <p>Apply for business permits, licenses, and registrations. Streamlined process for entrepreneurs and business owners.</p>
            <div class="service-features">
                <span class="feature-tag">Business Permit</span>
                <span class="feature-tag">Market Stall</span>
                <span class="feature-tag">Home Business</span>
            </div>
            <a href="business-application.php" class="service-btn">Apply Now</a>
            <a href="my-business-applications.php" class="service-btn secondary">Track Application</a>
        </div>
    </div>
</div>
