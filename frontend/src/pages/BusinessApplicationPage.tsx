import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import '../styles/business-application.css';

const BusinessApplicationPage: React.FC = () => {
  const navigate = useNavigate();
  const [user, setUser] = useState<any>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [notification, setNotification] = useState<{ message: string; type: string } | null>(null);
  const [referenceNo, setReferenceNo] = useState('');

  const [formData, setFormData] = useState({
    applicationDate: new Date().toISOString().split('T')[0],
    firstName: '',
    middleName: '',
    lastName: '',
    ownerAddress: '',
    mobileNumber: '',
    businessName: '',
    businessType: '',
    yearsOperation: '1',
    investmentCapital: '0.00',
    businessAddress: '',
    orNumber: '',
    ctcNumber: '',
  });

  const [proofFile, setProofFile] = useState<File | null>(null);

  const businessTypes = [
    'General Business',
    'Retail Store',
    'Restaurant/Food Service',
    'Service Business',
    'Home Business',
    'Other',
  ];

  useEffect(() => {
    const userData = localStorage.getItem('user');
    if (!userData) {
      navigate('/login');
      return;
    }
    const parsedUser = JSON.parse(userData);
    setUser(parsedUser);

    // Generate reference number
    const year = new Date().getFullYear();
    const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
    setReferenceNo(`BA-${year}-${random}`);

    // Auto-populate form with user data
    setFormData(prev => ({
      ...prev,
      firstName: parsedUser.first_name || '',
      middleName: parsedUser.middle_name || '',
      lastName: parsedUser.last_name || '',
      ownerAddress: parsedUser.address || '',
      mobileNumber: parsedUser.phone ? parsedUser.phone.replace('+63', '') : '',
    }));
  }, [navigate]);

  const showNotification = (message: string, type: string = 'info') => {
    setNotification({ message, type });
    setTimeout(() => setNotification(null), 4000);
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));

    // Mobile number validation
    if (name === 'mobileNumber') {
      const cleaned = value.replace(/[^0-9]/g, '');
      const truncated = cleaned.substring(0, 10);
      const formatted = truncated.length > 0 && truncated[0] !== '9' ? '9' + truncated.substring(1) : truncated;
      setFormData(prev => ({ ...prev, mobileNumber: formatted }));
    }
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      setProofFile(file);
    }
  };

  const handleRemoveFile = () => {
    setProofFile(null);
  };

  const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);

    // Mobile number validation
    if (formData.mobileNumber && !/^9[0-9]{9}$/.test(formData.mobileNumber)) {
      setError('Please enter a valid Philippine mobile number starting with 9 (10 digits total)');
      setLoading(false);
      return;
    }

    try {
      const formDataToSend = new FormData();
      formDataToSend.append('reference_no', referenceNo);
      formDataToSend.append('application_date', formData.applicationDate);
      formDataToSend.append('first_name', formData.firstName);
      formDataToSend.append('middle_name', formData.middleName);
      formDataToSend.append('last_name', formData.lastName);
      formDataToSend.append('owner_address', formData.ownerAddress);
      formDataToSend.append('mobile_number', '+63' + formData.mobileNumber);
      formDataToSend.append('business_name', formData.businessName);
      formDataToSend.append('business_type', formData.businessType);
      formDataToSend.append('years_operation', formData.yearsOperation);
      formDataToSend.append('investment_capital', formData.investmentCapital);
      formDataToSend.append('business_address', formData.businessAddress);
      formDataToSend.append('or_number', formData.orNumber);
      formDataToSend.append('ctc_number', formData.ctcNumber);
      if (proofFile) {
        formDataToSend.append('proof_image', proofFile);
      }

      const token = localStorage.getItem('access_token');
      const response = await fetch('http://localhost:8000/api/business/applications/', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
        body: formDataToSend,
      });

      if (response.ok) {
        setSuccess(`Business permit application submitted successfully! Reference: ${referenceNo}`);
        showNotification('Application submitted successfully!', 'success');
        setTimeout(() => navigate('/dashboard'), 2000);
      } else {
        const data = await response.json();
        setError(data.error || 'Error submitting application');
      }
    } catch (err) {
      setError('Error submitting application. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  if (!user) {
    return <div className="loading">Loading...</div>;
  }

  return (
    <div className="business-application-wrapper">
      <a href="/dashboard" className="page-nav-link">
        <i className="fas fa-arrow-left"></i> Back to Dashboard
      </a>

      <div className="container">
        <div className="header">
          <h1>🏢 Business Permit Application</h1>
          <p>Apply for your business permit online</p>
        </div>

        <div className="content">
          {error && (
            <div className="alert alert-error">
              <i className="fas fa-exclamation-circle"></i> {error}
            </div>
          )}
          {success && (
            <div className="alert alert-success">
              <i className="fas fa-check-circle"></i> {success}
            </div>
          )}
          {user && (
            <div className="alert alert-success auto-populated-notice">
              <i className="fas fa-user-check"></i> Your personal information has been automatically filled from your profile. Please review and update if necessary.
            </div>
          )}

          <form onSubmit={handleSubmit}>
            <div className="form-section">
              <h3><i className="fas fa-calendar"></i> Application Details</h3>
              <div className="form-grid">
                <div className="form-group">
                  <label htmlFor="applicationDate">📅 Application Date <span className="required">*</span></label>
                  <input
                    type="date"
                    id="applicationDate"
                    name="applicationDate"
                    value={formData.applicationDate}
                    onChange={handleInputChange}
                    required
                  />
                </div>
                <div className="form-group">
                  <label htmlFor="referenceDisplay">📄 Reference Number</label>
                  <input
                    type="text"
                    value={referenceNo}
                    readOnly
                    className="readonly-input"
                  />
                </div>
              </div>
            </div>

            <div className="form-section">
              <h3><i className="fas fa-user"></i> Personal Information</h3>
              <div className="form-group full-width">
                <label>👤 Full Name <span className="required">*</span></label>
                <div className="name-row">
                  <input
                    type="text"
                    name="firstName"
                    placeholder="First Name"
                    value={formData.firstName}
                    onChange={handleInputChange}
                    required
                  />
                  <input
                    type="text"
                    name="middleName"
                    placeholder="Middle Name (Optional)"
                    value={formData.middleName}
                    onChange={handleInputChange}
                  />
                  <input
                    type="text"
                    name="lastName"
                    placeholder="Last Name"
                    value={formData.lastName}
                    onChange={handleInputChange}
                    required
                  />
                </div>
              </div>

              <div className="form-grid">
                <div className="form-group">
                  <label htmlFor="ownerAddress">🏠 Home Address <span className="required">*</span></label>
                  <textarea
                    name="ownerAddress"
                    rows={3}
                    placeholder="Enter your complete home address..."
                    value={formData.ownerAddress}
                    onChange={handleInputChange}
                    required
                  ></textarea>
                </div>
                <div className="form-group">
                  <label htmlFor="mobileNumber">📱 Mobile Number</label>
                  <div className="mobile-input-container">
                    <div className="country-code">
                      <span>🇵🇭</span>
                      <span>+63</span>
                    </div>
                    <input
                      type="tel"
                      id="mobileNumber"
                      name="mobileNumber"
                      placeholder="9XX XXX XXXX"
                      maxLength={10}
                      value={formData.mobileNumber}
                      onChange={handleInputChange}
                    />
                  </div>
                </div>
              </div>
            </div>

            <div className="form-section">
              <h3><i className="fas fa-building"></i> Business Information</h3>
              <div className="form-grid">
                <div className="form-group full-width">
                  <label htmlFor="businessName">🏪 Business Name <span className="required">*</span></label>
                  <input
                    type="text"
                    name="businessName"
                    placeholder="Enter the name of your business"
                    value={formData.businessName}
                    onChange={handleInputChange}
                    required
                  />
                </div>
                <div className="form-group">
                  <label htmlFor="businessType">🏷️ Business Type <span className="required">*</span></label>
                  <select
                    name="businessType"
                    value={formData.businessType}
                    onChange={handleInputChange}
                    required
                  >
                    <option value="">Select Business Type</option>
                    {businessTypes.map(type => (
                      <option key={type} value={type}>{type}</option>
                    ))}
                  </select>
                </div>
                <div className="form-group">
                  <label htmlFor="yearsOperation">📅 Years of Operation</label>
                  <input
                    type="number"
                    name="yearsOperation"
                    value={formData.yearsOperation}
                    min="0"
                    max="100"
                    onChange={handleInputChange}
                  />
                </div>
                <div className="form-group">
                  <label htmlFor="investmentCapital">💰 Investment Capital</label>
                  <input
                    type="number"
                    name="investmentCapital"
                    value={formData.investmentCapital}
                    min="0"
                    step="0.01"
                    onChange={handleInputChange}
                  />
                </div>
              </div>
              <div className="form-group full-width">
                <label htmlFor="businessAddress">🏢 Business Address <span className="required">*</span></label>
                <textarea
                  name="businessAddress"
                  rows={3}
                  placeholder="Enter complete business address..."
                  value={formData.businessAddress}
                  onChange={handleInputChange}
                  required
                ></textarea>
              </div>
            </div>

            <div className="form-section">
              <h3><i className="fas fa-receipt"></i> Required Documents</h3>
              <div className="form-grid">
                <div className="form-group">
                  <label htmlFor="orNumber">🧾 OR Number <span className="required">*</span></label>
                  <input
                    type="text"
                    name="orNumber"
                    placeholder="Official Receipt Number"
                    value={formData.orNumber}
                    onChange={handleInputChange}
                    required
                  />
                </div>
                <div className="form-group">
                  <label htmlFor="ctcNumber">📋 CTC Number <span className="required">*</span></label>
                  <input
                    type="text"
                    name="ctcNumber"
                    placeholder="Community Tax Certificate Number"
                    value={formData.ctcNumber}
                    onChange={handleInputChange}
                    required
                  />
                </div>
              </div>
            </div>

            <div className="form-section">
              <h3><i className="fas fa-camera"></i> Supporting Documents (Optional)</h3>
              <div className="file-upload-container">
                <input
                  type="file"
                  id="proofImage"
                  accept="image/*,.pdf"
                  onChange={handleFileChange}
                  className="file-input"
                />
                <div className="file-upload-display">
                  <div className="file-upload-icon">
                    <i className="fas fa-cloud-upload-alt"></i>
                  </div>
                  <div className="file-upload-text">
                    <span className="file-upload-label">Click to upload or drag and drop</span>
                    <span className="file-upload-hint">PNG, JPG, PDF up to 5MB</span>
                  </div>
                </div>
                {proofFile && (
                  <div className="file-preview">
                    <div className="file-preview-info">
                      <div className="file-preview-icon">📄</div>
                      <div className="file-preview-details">
                        <div className="file-preview-name">{proofFile.name}</div>
                        <div className="file-preview-size">{formatFileSize(proofFile.size)}</div>
                      </div>
                      <button type="button" className="file-remove-btn" onClick={handleRemoveFile}>
                        ×
                      </button>
                    </div>
                  </div>
                )}
              </div>
            </div>

            <div className="form-actions">
              <button type="submit" className="submit-btn" disabled={loading}>
                <i className="fas fa-paper-plane"></i> {loading ? 'Submitting...' : 'Submit Application'}
              </button>
              <button
                type="button"
                className="reset-btn"
                onClick={() => {
                  setFormData({
                    applicationDate: new Date().toISOString().split('T')[0],
                    firstName: user.first_name || '',
                    middleName: user.middle_name || '',
                    lastName: user.last_name || '',
                    ownerAddress: user.address || '',
                    mobileNumber: user.phone ? user.phone.replace('+63', '') : '',
                    businessName: '',
                    businessType: '',
                    yearsOperation: '1',
                    investmentCapital: '0.00',
                    businessAddress: '',
                    orNumber: '',
                    ctcNumber: '',
                  });
                  setProofFile(null);
                }}
              >
                <i className="fas fa-undo"></i> Reset Form
              </button>
            </div>
          </form>
        </div>
      </div>

      {notification && (
        <div className={`notification notification-${notification.type} show`}>
          <i className={`fas fa-${notification.type === 'success' ? 'check-circle' : notification.type === 'error' ? 'exclamation-circle' : notification.type === 'warning' ? 'exclamation-triangle' : 'info-circle'}`}></i>
          <span>{notification.message}</span>
          <button onClick={() => setNotification(null)} className="notification-close">
            <i className="fas fa-times"></i>
          </button>
        </div>
      )}
    </div>
  );
};

export default BusinessApplicationPage;
