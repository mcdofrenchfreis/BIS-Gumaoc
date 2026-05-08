import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import '../styles/rfid-registration.css';

const RFIDRegistrationPage: React.FC = () => {
  const navigate = useNavigate();
  const [user, setUser] = useState<any>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const [formData, setFormData] = useState({
    rfid_number: '',
    card_type: 'resident',
    first_name: '',
    middle_name: '',
    last_name: '',
    birth_date: '',
    contact_number: '',
    address: '',
  });

  useEffect(() => {
    const userData = localStorage.getItem('user');
    if (!userData) {
      navigate('/login');
      return;
    }
    const parsedUser = JSON.parse(userData);
    setUser(parsedUser);
    setFormData(prev => ({
      ...prev,
      first_name: parsedUser.first_name || '',
      middle_name: parsedUser.middle_name || '',
      last_name: parsedUser.last_name || '',
      birth_date: parsedUser.birth_date || '',
      contact_number: parsedUser.phone ? parsedUser.phone.replace('+63', '') : '',
      address: parsedUser.address || '',
    }));
  }, [navigate]);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);

    try {
      const token = localStorage.getItem('access_token');
      const response = await fetch('http://localhost:8000/api/rfid/registrations/', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData),
      });

      if (response.ok) {
        setSuccess('RFID registration submitted successfully! Your application will be processed within 2-3 business days.');
        setFormData({
          rfid_number: '',
          card_type: 'resident',
          first_name: user?.first_name || '',
          middle_name: user?.middle_name || '',
          last_name: user?.last_name || '',
          birth_date: user?.birth_date || '',
          contact_number: user?.phone ? user.phone.replace('+63', '') : '',
          address: user?.address || '',
        });
      } else {
        const data = await response.json();
        setError(data.error || 'Error submitting registration');
      }
    } catch (err) {
      setError('Error submitting registration. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const handleReset = () => {
    setFormData({
      rfid_number: '',
      card_type: 'resident',
      first_name: user?.first_name || '',
      middle_name: user?.middle_name || '',
      last_name: user?.last_name || '',
      birth_date: user?.birth_date || '',
      contact_number: user?.phone ? user.phone.replace('+63', '') : '',
      address: user?.address || '',
    });
  };

  if (!user) {
    return <div className="loading">Loading...</div>;
  }

  return (
    <div className="rfid-registration-wrapper">
      <a href="/dashboard" className="page-nav-link">
        <i className="fas fa-arrow-left"></i> Back to Dashboard
      </a>

      <div className="container">
        <div className="section">
          {success && (
            <div className="alert alert-success">
              <h4>✅ Registration Successful!</h4>
              <p>{success}</p>
            </div>
          )}
          {error && (
            <div className="alert alert-error">
              <h4>❌ Registration Failed</h4>
              <p>{error}</p>
            </div>
          )}

          <div className="rfid-container">
            <div className="rfid-header">
              <div className="rfid-icon">🏷️</div>
              <h2>RFID Card Registration</h2>
              <p>Register your RFID card to access barangay services quickly and securely</p>
            </div>

            <div className="rfid-benefits">
              <h3>🎯 Benefits of RFID Registration</h3>
              <div className="benefits-grid">
                <div className="benefit-item">
                  <span className="benefit-icon">⚡</span>
                  <h4>Quick Access</h4>
                  <p>Fast service processing with just a tap</p>
                </div>
                <div className="benefit-item">
                  <span className="benefit-icon">🔒</span>
                  <h4>Secure</h4>
                  <p>Encrypted data protection</p>
                </div>
                <div className="benefit-item">
                  <span className="benefit-icon">📱</span>
                  <h4>Digital Records</h4>
                  <p>Automated record keeping</p>
                </div>
                <div className="benefit-item">
                  <span className="benefit-icon">✅</span>
                  <h4>Verified Identity</h4>
                  <p>Instant identity verification</p>
                </div>
              </div>
            </div>

            <form onSubmit={handleSubmit} className="rfid-form">
              <div className="form-section">
                <h3>🏷️ RFID Card Information</h3>
                <div className="rfid-input-group">
                  <label htmlFor="rfid_number">RFID Card Number *</label>
                  <input
                    type="text"
                    id="rfid_number"
                    name="rfid_number"
                    placeholder="Scan or enter RFID number"
                    value={formData.rfid_number}
                    onChange={handleInputChange}
                    required
                    autoFocus
                  />
                  <small className="input-help">📖 Place your RFID card near the reader or enter the number manually</small>
                </div>

                <div className="form-group">
                  <label htmlFor="card_type">Card Type *</label>
                  <select
                    id="card_type"
                    name="card_type"
                    value={formData.card_type}
                    onChange={handleInputChange}
                    required
                  >
                    <option value="resident">Resident</option>
                    <option value="employee">Barangay Employee</option>
                    <option value="visitor">Visitor</option>
                  </select>
                </div>
              </div>

              <div className="form-section">
                <h3>👤 Personal Information</h3>
                <div className="form-grid">
                  <div className="form-group">
                    <label htmlFor="first_name">First Name *</label>
                    <input
                      type="text"
                      id="first_name"
                      name="first_name"
                      value={formData.first_name}
                      onChange={handleInputChange}
                      required
                    />
                  </div>

                  <div className="form-group">
                    <label htmlFor="middle_name">Middle Name</label>
                    <input
                      type="text"
                      id="middle_name"
                      name="middle_name"
                      value={formData.middle_name}
                      onChange={handleInputChange}
                    />
                  </div>

                  <div className="form-group">
                    <label htmlFor="last_name">Last Name *</label>
                    <input
                      type="text"
                      id="last_name"
                      name="last_name"
                      value={formData.last_name}
                      onChange={handleInputChange}
                      required
                    />
                  </div>

                  <div className="form-group">
                    <label htmlFor="birth_date">Birth Date *</label>
                    <input
                      type="date"
                      id="birth_date"
                      name="birth_date"
                      value={formData.birth_date}
                      onChange={handleInputChange}
                      required
                    />
                  </div>
                </div>
              </div>

              <div className="form-section">
                <h3>📞 Contact Information</h3>
                <div className="form-grid">
                  <div className="form-group">
                    <label htmlFor="contact_number">Contact Number *</label>
                    <input
                      type="tel"
                      id="contact_number"
                      name="contact_number"
                      placeholder="09XXXXXXXXX"
                      pattern="[0-9]{11}"
                      maxLength={11}
                      value={formData.contact_number}
                      onChange={handleInputChange}
                      required
                    />
                  </div>

                  <div className="form-group full-width">
                    <label htmlFor="address">Complete Address *</label>
                    <textarea
                      id="address"
                      name="address"
                      rows={3}
                      placeholder="House No., Street, Sitio/Purok, Barangay"
                      value={formData.address}
                      onChange={handleInputChange}
                      required
                    ></textarea>
                  </div>
                </div>
              </div>

              <div className="form-actions">
                <button type="submit" className="btn-primary" disabled={loading}>
                  <span className="btn-icon">📝</span> {loading ? 'Submitting...' : 'Register RFID Card'}
                </button>
                <button type="button" className="btn-secondary" onClick={handleReset}>
                  <span className="btn-icon">🔄</span> Clear Form
                </button>
              </div>
            </form>

            <div className="rfid-info">
              <h3>ℹ️ Important Information</h3>
              <div className="info-grid">
                <div className="info-item">
                  <h4>📋 Required Documents</h4>
                  <ul>
                    <li>Valid ID (copy)</li>
                    <li>Proof of residency</li>
                    <li>Barangay clearance</li>
                  </ul>
                </div>

                <div className="info-item">
                  <h4>⏰ Processing Time</h4>
                  <ul>
                    <li>2-3 business days</li>
                    <li>Email notification</li>
                    <li>SMS confirmation</li>
                  </ul>
                </div>

                <div className="info-item">
                  <h4>💰 Fees</h4>
                  <ul>
                    <li>Residents: ₱50.00</li>
                    <li>Employees: Free</li>
                    <li>Visitors: ₱100.00</li>
                  </ul>
                </div>

                <div className="info-item">
                  <h4>📞 Contact Support</h4>
                  <ul>
                    <li>Phone: (02) 123-4567</li>
                    <li>Email: rfid@gumaoc.gov.ph</li>
                    <li>Office Hours: 8AM-5PM</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default RFIDRegistrationPage;
