import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import '../styles/user-login.css';

const LoginPage: React.FC = () => {
  const navigate = useNavigate();
  const [activeTab, setActiveTab] = useState<'email' | 'rfid'>('email');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [rfidCode, setRfidCode] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    // Check if already logged in
    const token = localStorage.getItem('access_token');
    if (token) {
      navigate('/dashboard');
    }
  }, [navigate]);

  const handleEmailLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const response = await fetch('http://localhost:8000/api/auth/login/', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ username: email, password }),
      });

      const data = await response.json();

      if (response.ok) {
        localStorage.setItem('access_token', data.access);
        localStorage.setItem('refresh_token', data.refresh);
        localStorage.setItem('user', JSON.stringify(data.user));
        navigate('/dashboard');
      } else {
        setError('Invalid email or password');
      }
    } catch (err) {
      setError('Login failed. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const handleRfidLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const response = await fetch('http://localhost:8000/api/auth/rfid-login/', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ rfid_code: rfidCode }),
      });

      const data = await response.json();

      if (response.ok) {
        localStorage.setItem('access_token', data.access);
        localStorage.setItem('refresh_token', data.refresh);
        localStorage.setItem('user', JSON.stringify(data.user));
        localStorage.setItem('rfid_authenticated', 'true');
        navigate('/dashboard');
      } else {
        setError('Invalid RFID or user not found');
      }
    } catch (err) {
      setError('Login failed. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const handleRfidChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = e.target.value.toUpperCase();
    setRfidCode(value);
    // Auto-submit when RFID code reaches 10+ characters
    if (value.length >= 10) {
      setTimeout(() => {
        const form = e.target.closest('form');
        if (form) form.requestSubmit();
      }, 500);
    }
  };

  return (
    <div className="login-wrapper">
      <a href="/" className="back-btn">
        <i className="fas fa-arrow-left"></i>
        Back to Home
      </a>
      
      <div className="login-container">
        <div className="login-card">
          <div className="login-header">
            <h2>Resident Login</h2>
          </div>
          
          <div className="info-box">
            <h4>🔐 Login Options</h4>
            <p>Login using your email and password, or scan your RFID card.</p>
          </div>
          
          {error && (
            <div className="alert alert-error">
              <i className="fas fa-exclamation-circle"></i>
              {error}
            </div>
          )}
          
          {/* Login Method Tabs */}
          <div className="login-tabs">
            <div className="tab-buttons">
              <button
                type="button"
                className={`tab-button ${activeTab === 'email' ? 'active' : ''}`}
                onClick={() => setActiveTab('email')}
              >
                <i className="fas fa-envelope"></i> Email Login
              </button>
              <button
                type="button"
                className={`tab-button ${activeTab === 'rfid' ? 'active' : ''}`}
                onClick={() => setActiveTab('rfid')}
              >
                <i className="fas fa-credit-card"></i> RFID Login
              </button>
            </div>
            
            {/* Email Login Tab */}
            {activeTab === 'email' && (
              <div className="tab-content active">
                <form onSubmit={handleEmailLogin}>
                  <div className="form-group">
                    <label htmlFor="email">Email Address</label>
                    <input
                      type="email"
                      id="email"
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      required
                      autoFocus
                      placeholder="your.email@example.com"
                      disabled={loading}
                    />
                  </div>
                  
                  <div className="form-group">
                    <label htmlFor="password">Password</label>
                    <input
                      type="password"
                      id="password"
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      required
                      disabled={loading}
                    />
                  </div>
                  
                  <button type="submit" className="btn-login" disabled={loading}>
                    <i className="fas fa-sign-in-alt"></i>
                    {loading ? 'Logging in...' : 'Login with Email'}
                  </button>
                </form>
              </div>
            )}
            
            {/* RFID Login Tab */}
            {activeTab === 'rfid' && (
              <div className="tab-content active">
                <form onSubmit={handleRfidLogin}>
                  <div className="form-group">
                    <label htmlFor="rfid_code">RFID Card</label>
                    <input
                      type="text"
                      id="rfid_code"
                      value={rfidCode}
                      onChange={handleRfidChange}
                      placeholder="Scan or enter RFID code"
                      className="rfid-input"
                      disabled={loading}
                    />
                  </div>
                  
                  <button type="submit" className="btn-login btn-rfid" disabled={loading}>
                    <i className="fas fa-credit-card"></i>
                    {loading ? 'Verifying...' : 'Login with RFID'}
                  </button>
                  
                  <div className="rfid-instructions">
                    <small>
                      <i className="fas fa-info-circle"></i>
                      Place your RFID card near the reader or manually enter your RFID code
                    </small>
                  </div>
                </form>
              </div>
            )}
          </div>
          
          <div className="register-link">
            <a href="/register">Complete Census Registration</a>
            <a href="/forgot-password">Forgot Password?</a>
            <a href="/rfid-login">Quick RFID Access</a>
          </div>
        </div>
      </div>
    </div>
  );
};

export default LoginPage;
