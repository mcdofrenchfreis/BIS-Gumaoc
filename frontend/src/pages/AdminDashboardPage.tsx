import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import '../styles/admin-dashboard.css';

interface DashboardStats {
  residentCount: number;
  pendingResident: number;
  certificateCount: number;
  pendingCertificate: number;
  businessCount: number;
  pendingBusiness: number;
  servicesCount: number;
  updatesCount: number;
  rfidAvailable: number;
  rfidAssigned: number;
}

const AdminDashboardPage: React.FC = () => {
  const navigate = useNavigate();
  const [stats, setStats] = useState<DashboardStats>({
    residentCount: 0,
    pendingResident: 0,
    certificateCount: 0,
    pendingCertificate: 0,
    businessCount: 0,
    pendingBusiness: 0,
    servicesCount: 0,
    updatesCount: 0,
    rfidAvailable: 0,
    rfidAssigned: 0,
  });
  const [adminName, setAdminName] = useState('');
  const [adminRole, setAdminRole] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const token = localStorage.getItem('adminToken');
    if (!token) {
      navigate('/admin/login');
      return;
    }

    setAdminName(localStorage.getItem('adminName') || 'Admin');
    setAdminRole(localStorage.getItem('adminRole') || 'admin');

    fetchDashboardStats();
  }, [navigate]);

  const fetchDashboardStats = async () => {
    try {
      const token = localStorage.getItem('adminToken');
      const response = await fetch('http://localhost:8000/api/admin/dashboard/stats/', {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });

      if (response.ok) {
        const data = await response.json();
        setStats(data);
      }
    } catch (error) {
      console.error('Error fetching dashboard stats:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleLogout = () => {
    localStorage.removeItem('adminToken');
    localStorage.removeItem('adminRole');
    localStorage.removeItem('adminName');
    navigate('/admin/login');
  };

  const role = adminRole.toLowerCase();
  const isSecretary = role === 'secretary';
  const isTreasurer = role === 'treasurer';

  const showCensus = !isTreasurer;
  const showCertificates = !isTreasurer;
  const showBusinessApps = !isSecretary;

  if (loading) {
    return <div className="loading">Loading...</div>;
  }

  return (
    <div className="admin-dashboard">
      {/* Admin Mini Nav */}
      <div className="admin-mini-nav">
        <div className="mini-nav-content">
          <span className="admin-welcome">Welcome, {adminName}</span>
          <span className="admin-role-badge">{adminRole}</span>
          <button onClick={handleLogout} className="logout-btn">
            <i className="fas fa-sign-out-alt"></i> Logout
          </button>
        </div>
      </div>

      <div className="dashboard-content">
        <div className="dashboard-header">
          <div className="gov-seal">
            <img src="/assets/images/logo.png" alt="Barangay Gumaoc East" className="gov-logo" />
          </div>
          <div className="gov-header-text">
            <div className="gov-header-top">Republic of the Philippines</div>
            <h1>Admin Dashboard</h1>
            <p>Barangay Gumaoc East - Digital Services Management</p>
          </div>
        </div>

        {/* Navigation */}
        <nav className="admin-nav">
          <a href="/admin/residents">
            <i className="fas fa-users"></i> Residents
          </a>
          {showCensus && (
            <a href="/admin/census">
              <i className="fas fa-chart-bar"></i> Census
            </a>
          )}
          {showCertificates && (
            <a href="/admin/certificates">
              <i className="fas fa-certificate"></i> Certificates
            </a>
          )}
          {showBusinessApps && (
            <a href="/admin/business">
              <i className="fas fa-briefcase"></i> Business
            </a>
          )}
          <a href="/admin/blotter">
            <i className="fas fa-exclamation-triangle"></i> Blotter
          </a>
          <a href="/admin/queue">
            <i className="fas fa-ticket-alt"></i> Queue
          </a>
          <a href="/admin/rfid">
            <i className="fas fa-id-card"></i> RFID
          </a>
          <a href="/admin/reports">
            <i className="fas fa-file-alt"></i> Reports
          </a>
          <a href="/admin/settings">
            <i className="fas fa-cog"></i> Settings
          </a>
        </nav>

        {/* Stats Grid */}
        <div className="dashboard-stats">
          {showCensus && (
            <a href="/admin/residents" className="stat-card">
              <h3><i className="fas fa-users"></i> Residents</h3>
              <div className="stat-number">{stats.residentCount}</div>
              <div className="stat-pending">{stats.pendingResident} Pending</div>
            </a>
          )}
          {showCertificates && (
            <a href="/admin/certificates" className="stat-card">
              <h3><i className="fas fa-certificate"></i> Certificates</h3>
              <div className="stat-number">{stats.certificateCount}</div>
              <div className="stat-pending">{stats.pendingCertificate} Pending</div>
            </a>
          )}
          {showBusinessApps && (
            <a href="/admin/business" className="stat-card">
              <h3><i className="fas fa-briefcase"></i> Business</h3>
              <div className="stat-number">{stats.businessCount}</div>
              <div className="stat-pending">{stats.pendingBusiness} Pending</div>
            </a>
          )}
          <a href="/admin/blotter" className="stat-card">
            <h3><i className="fas fa-exclamation-triangle"></i> Blotter</h3>
            <div className="stat-number">0</div>
            <div className="stat-pending">0 Pending</div>
          </a>
          <a href="/admin/rfid" className="stat-card">
            <h3><i className="fas fa-id-card"></i> RFID</h3>
            <div className="stat-number">{stats.rfidAvailable}</div>
            <div className="stat-pending">{stats.rfidAssigned} Assigned</div>
          </a>
        </div>

        {/* Main Content */}
        <div className="dashboard-main">
          {/* Quick Actions */}
          <div className="dashboard-section">
            <div className="section-title">
              <div className="section-icon"><i className="fas fa-bolt"></i></div>
              Quick Actions
            </div>
            <div className="dashboard-actions">
              {showCensus && (
                <div className="action-card">
                  <div className="action-icon"><i className="fas fa-user-plus"></i></div>
                  <h3>Register Resident</h3>
                  <p>Add new residents to the system with complete family information</p>
                  <a href="/admin/residents/new" className="admin-btn">
                    <i className="fas fa-plus"></i> Register
                  </a>
                </div>
              )}
              {showCertificates && (
                <div className="action-card">
                  <div className="action-icon"><i className="fas fa-file-certificate"></i></div>
                  <h3>Process Certificate</h3>
                  <p>Review and approve certificate requests from residents</p>
                  <a href="/admin/certificates" className="admin-btn">
                    <i className="fas fa-tasks"></i> Process
                  </a>
                </div>
              )}
              {showBusinessApps && (
                <div className="action-card">
                  <div className="action-icon"><i className="fas fa-store"></i></div>
                  <h3>Business Clearance</h3>
                  <p>Issue business clearances for local establishments</p>
                  <a href="/admin/business" className="admin-btn">
                    <i className="fas fa-check-circle"></i> Issue
                  </a>
                </div>
              )}
              <div className="action-card">
                <div className="action-icon"><i className="fas fa-gavel"></i></div>
                <h3>Manage Blotter</h3>
                <p>Handle incident reports and disputes in the barangay</p>
                <a href="/admin/blotter" className="admin-btn">
                  <i className="fas fa-folder-open"></i> Manage
                </a>
              </div>
              <div className="action-card">
                <div className="action-icon"><i className="fas fa-ticket-alt"></i></div>
                <h3>Queue Monitor</h3>
                <p>Monitor and manage queue tickets in real-time</p>
                <a href="/admin/queue" className="admin-btn">
                  <i className="fas fa-desktop"></i> Monitor
                </a>
              </div>
              <div className="action-card">
                <div className="action-icon"><i className="fas fa-id-card"></i></div>
                <h3>RFID Scanner</h3>
                <p>Scan and manage RFID cards for resident authentication</p>
                <a href="/admin/rfid" className="admin-btn">
                  <i className="fas fa-qrcode"></i> Scan
                </a>
              </div>
            </div>
          </div>

          {/* Queue Preview */}
          <div className="dashboard-section queue-section">
            <div className="section-title">
              <div className="section-icon"><i className="fas fa-ticket-alt"></i></div>
              Queue Status
              <div className="queue-actions">
                <a href="/admin/queue" className="queue-link">
                  <i className="fas fa-expand"></i> Full View
                </a>
              </div>
            </div>
            <div className="queue-stats">
              <div className="qstat">
                <div className="qnum">{stats.residentCount}</div>
                <div className="qlabel">Waiting</div>
              </div>
              <div className="qstat">
                <div className="qnum">0</div>
                <div className="qlabel">Serving</div>
              </div>
              <div className="qstat">
                <div className="qnum">0</div>
                <div className="qlabel">Completed</div>
              </div>
              <div className="qstat">
                <div className="qnum">0</div>
                <div className="qlabel">Priority</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AdminDashboardPage;
