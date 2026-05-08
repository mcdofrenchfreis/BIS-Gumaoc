import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import '../styles/user-dashboard.css';

const DashboardPage: React.FC = () => {
  const navigate = useNavigate();
  const [user, setUser] = useState<any>(null);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [notifications, setNotifications] = useState<any[]>([]);

  useEffect(() => {
    const userData = localStorage.getItem('user');
    if (!userData) {
      navigate('/login');
      return;
    }
    setUser(JSON.parse(userData));
  }, [navigate]);

  const handleLogout = () => {
    localStorage.clear();
    navigate('/');
  };

  const getInitials = (name: string) => {
    return name ? name.charAt(0).toUpperCase() : 'U';
  };

  const getDisplayName = () => {
    if (!user) return 'User';
    const firstName = user.first_name || '';
    const middleName = user.middle_name ? ` ${user.middle_name} ` : ' ';
    const lastName = user.last_name || '';
    return `${firstName}${middleName}${lastName}`;
  };

  const getFirstName = () => {
    if (!user) return 'User';
    return user.first_name || 'User';
  };

  if (!user) {
    return <div className="loading">Loading...</div>;
  }

  return (
    <div className="dashboard-wrapper">
      {/* User Navbar */}
      <nav className="user-navbar">
        <div className="navbar-container">
          <a href="/dashboard" className="navbar-brand">
            <div className="brand-icon">
              <i className="fas fa-landmark"></i>
            </div>
            <span>Barangay Gumaoc East</span>
          </a>
          
          <div className="navbar-nav">
            <a href="/dashboard" className="nav-link active">
              <i className="fas fa-home"></i> Dashboard
            </a>
            <a href="/e-services" className="nav-link">
              <i className="fas fa-desktop"></i> E-Services
            </a>
            <a href="/my-requests" className="nav-link">
              <i className="fas fa-file-alt"></i> My Requests
            </a>
            <a href="/profile" className="nav-link">
              <i className="fas fa-user"></i> Profile
            </a>
            
            <div className="user-menu">
              <button className="user-button">
                <div className="user-avatar-small">{getInitials(getDisplayName())}</div>
                <span>{getFirstName()}</span>
                <i className="fas fa-chevron-down"></i>
              </button>
              <div className="user-dropdown">
                <a href="/profile" className="dropdown-item">
                  <i className="fas fa-user-circle"></i> Profile
                </a>
                <a href="/settings" className="dropdown-item">
                  <i className="fas fa-cog"></i> Settings
                </a>
                <div className="dropdown-divider"></div>
                <a href="#" onClick={handleLogout} className="dropdown-item">
                  <i className="fas fa-sign-out-alt"></i> Logout
                </a>
              </div>
            </div>
          </div>
          
          {/* Mobile Menu Toggle */}
          <button 
            className={`mobile-menu-toggle ${mobileMenuOpen ? 'active' : ''}`}
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
          >
            <span className="hamburger-line"></span>
            <span className="hamburger-line"></span>
            <span className="hamburger-line"></span>
          </button>
        </div>
      </nav>
      
      {/* Mobile Navigation */}
      <div className={`mobile-nav ${mobileMenuOpen ? 'active' : ''}`}>
        <a href="/dashboard" className="mobile-nav-link active">
          <i className="fas fa-home"></i> Dashboard
        </a>
        <a href="/e-services" className="mobile-nav-link">
          <i className="fas fa-desktop"></i> E-Services
        </a>
        <a href="/my-requests" className="mobile-nav-link">
          <i className="fas fa-file-alt"></i> My Requests
        </a>
        <a href="/profile" className="mobile-nav-link">
          <i className="fas fa-user"></i> Profile
        </a>
        <div className="mobile-user-info">
          <div className="mobile-user-avatar">{getInitials(getDisplayName())}</div>
          <div className="mobile-user-details">
            <h4>{getDisplayName()}</h4>
            <p>{user.email || 'No email'}</p>
          </div>
        </div>
      </div>
      
      {/* Dashboard Content */}
      <div className="dashboard-content">
        <div className="dashboard-container">
          {/* Welcome Section */}
          <div className="welcome-section">
            <h1>Welcome back, {getFirstName()}!</h1>
            <p>Your personalized portal for barangay services and community engagement</p>
            
            <div className="user-info-card">
              <div className="user-avatar-large">
                {getInitials(getDisplayName())}
              </div>
              <div className="user-details">
                <h3>{getDisplayName()}</h3>
                <p>📧 {user.email || 'No email'}</p>
                {user.phone && <p>📱 {user.phone}</p>}
              </div>
            </div>
          </div>
          
          {/* Quick Stats */}
          <div className="quick-stats">
            <div className="stat-card">
              <div className="stat-number">2</div>
              <div className="stat-label">Available Services</div>
            </div>
            <div className="stat-card">
              <div className="stat-number">0</div>
              <div className="stat-label">Active Requests</div>
            </div>
            <div className="stat-card">
              <div className="stat-number">24/7</div>
              <div className="stat-label">Service Access</div>
            </div>
            <div className="stat-card">
              <div className="stat-number">100%</div>
              <div className="stat-label">Digital Services</div>
            </div>
          </div>
          
          {/* Services Section */}
          <div className="services-section">
            <h3 className="section-title">
              <i className="fas fa-th-large"></i>
              Available Services
            </h3>
            
            <div className="services-grid">
              <div className="service-card">
                <div className="service-icon">
                  <i className="fas fa-desktop"></i>
                </div>
                <h3>E-Services Portal</h3>
                <p>Access all available digital services, business permits, certificates, and community programs in one convenient location.</p>
                <a href="/e-services" className="service-btn">
                  <i className="fas fa-arrow-right"></i>
                  Explore Services
                </a>
              </div>
              
              <div className="service-card">
                <div className="service-icon">
                  <i className="fas fa-tachometer-alt"></i>
                </div>
                <h3>Dashboard Overview</h3>
                <p>Your personal dashboard with quick stats, recent activity, and easy access to all your account information.</p>
                <a href="/dashboard" className="service-btn">
                  <i className="fas fa-refresh"></i>
                  Refresh Dashboard
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      {/* Notifications */}
      {notifications.map(notification => (
        <div key={notification.id} className={`notification notification-${notification.type} show`}>
          <i className={`fas fa-${notification.type === 'success' ? 'check-circle' : notification.type === 'error' ? 'exclamation-circle' : notification.type === 'warning' ? 'exclamation-triangle' : 'info-circle'}`}></i>
          <span>{notification.message}</span>
          <button onClick={() => setNotifications(prev => prev.filter(n => n.id !== notification.id))} className="notification-close">
            <i className="fas fa-times"></i>
          </button>
        </div>
      ))}
    </div>
  );
};

export default DashboardPage;
