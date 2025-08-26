<style>
.admin-sub-nav {
    background: linear-gradient(90deg, #2e7d32 0%, #4caf50 100%);
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 2px 10px rgba(27, 94, 32, 0.2);
    position: relative;
    z-index: 999;
    margin-bottom: 2rem;
    top: 0;
    width: 100%;
}

.admin-sub-nav-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 15px;
}

.admin-sub-nav-menu {
    display: flex;
    list-style: none;
    gap: 5px;
    flex-wrap: wrap;
    padding: 0;
    margin: 0;
}

.admin-sub-nav-link {
    text-decoration: none;
    color: rgba(255, 255, 255, 0.9);
    font-weight: 500;
    padding: 12px 15px;
    border-radius: 6px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    position: relative;
    overflow: hidden;
    white-space: nowrap;
}

.admin-sub-nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
    transition: left 0.3s ease;
    z-index: -1;
}

.admin-sub-nav-link:hover::before {
    left: 0;
}

.admin-sub-nav-link:hover {
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.admin-sub-nav-link.active {
    background: rgba(255, 255, 255, 0.25);
    color: white;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

/* Responsive design */
@media (max-width: 768px) {
    .admin-sub-nav-menu {
        gap: 3px;
        justify-content: center;
    }
    
    .admin-sub-nav-link {
        padding: 10px 12px;
        font-size: 0.85rem;
    }
}

@media (max-width: 480px) {
    .admin-sub-nav-container {
        padding: 0 10px;
    }
    
    .admin-sub-nav-menu {
        gap: 2px;
    }
    
    .admin-sub-nav-link {
        padding: 8px 10px;
        font-size: 0.8rem;
        gap: 5px;
    }
    
    .admin-sub-nav-link i {
        font-size: 0.9rem;
    }
}
</style>

<nav class="admin-sub-nav">
    <div class="admin-sub-nav-container">
        <ul class="admin-sub-nav-menu">
            <li><a href="manage-blotter.php" class="admin-sub-nav-link"><i class="fas fa-gavel"></i> Blotter Management</a></li>
            <li><a href="captain-clearances.php" class="admin-sub-nav-link"><i class="fas fa-file-signature"></i> Captain Clearances</a></li>
            <li><a href="blotter-reports.php" class="admin-sub-nav-link"><i class="fas fa-chart-bar"></i> Blotter Reports</a></li>
            <li><a href="queue-monitor.php" class="admin-sub-nav-link"><i class="fas fa-tv"></i> Queue Monitor</a></li>
            <li><a href="view-resident-registrations.php" class="admin-sub-nav-link"><i class="fas fa-users"></i> Resident Registrations</a></li>
            <li><a href="view-certificate-requests.php" class="admin-sub-nav-link"><i class="fas fa-file-alt"></i> Certificate Requests</a></li>
            <li><a href="view-business-applications.php" class="admin-sub-nav-link"><i class="fas fa-building"></i> Business Applications</a></li>
            <li><a href="view-logs.php" class="admin-sub-nav-link"><i class="fas fa-history"></i> System Logs</a></li>
        </ul>
    </div>
</nav>