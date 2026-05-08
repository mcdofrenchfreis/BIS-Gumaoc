import React from 'react';
import { Link } from 'react-router-dom';
import '../styles/landing.css';

const LandingPage: React.FC = () => {
  return (
    <div className="landing-page">
      <div className="wrap">
        <div className="panel">
          <div className="panel-header">
            <div className="logo">
              <img src="/assets/images/logo.png" alt="Barangay Gumaoc East Logo" />
            </div>
            <div className="title">
              <h1>Barangay Gumaoc East</h1>
              <p>Digital Services and Administration</p>
            </div>
            <div className="actions">
              <Link className="btn" to="/about">About</Link>
              <Link className="btn" to="/contact">Contact</Link>
            </div>
          </div>
          <div className="panel-body">
            <div className="intro">
              <h2>Select a portal to continue</h2>
              <p>Choose the appropriate portal based on your role or activity</p>
            </div>

            <div className="grid">
              <Link className="card" to="/kiosk">
                <span className="badge">Public</span>
                <div className="row">
                  <div className="icon">🖥️</div>
                  <div>
                    <h3>Kiosk</h3>
                    <p>Self-service access for residents and visitors</p>
                  </div>
                </div>
              </Link>

              <Link className="card" to="/user/login">
                <span className="badge resident">Resident</span>
                <div className="row">
                  <div className="icon resident">👤</div>
                  <div>
                    <h3>User Portal</h3>
                    <p>Login to manage requests, notifications, and settings</p>
                  </div>
                </div>
              </Link>
            </div>

            <div className="actions-bottom">
              <Link className="btn" to="/services">View Services</Link>
              <Link className="btn primary" to="/forms">Request Document</Link>
              <Link className="btn warn" to="/report">Report Emergency</Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default LandingPage;
