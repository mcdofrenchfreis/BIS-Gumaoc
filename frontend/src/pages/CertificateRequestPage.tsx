import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import '../styles/certificate-request.css';

const CertificateRequestPage: React.FC = () => {
  const navigate = useNavigate();
  const [user, setUser] = useState<any>(null);
  const [selectedType, setSelectedType] = useState<string>('');
  const [showForm, setShowForm] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [notification, setNotification] = useState<{ message: string; type: string } | null>(null);

  // Form state
  const [formData, setFormData] = useState({
    firstName: '',
    middleName: '',
    lastName: '',
    address: '',
    mobileNumber: '',
    civilStatus: '',
    gender: '',
    birthdate: '',
    birthplace: '',
    citizenship: 'Filipino',
    yearsOfResidence: '',
    purpose: '',
    // Tricycle fields
    makeType: '',
    motorNo: '',
    chassisNo: '',
    plateNo: '',
    vehicleColor: '',
    yearModel: '',
    bodyNo: '',
    operatorLicense: '',
    // Cedula fields
    cedulaYear: new Date().getFullYear().toString(),
    placeOfIssue: 'San Jose Del Monte City, Bulacan',
    dateIssued: new Date().toISOString().split('T')[0],
    professionOccupation: '',
    height: '',
    weight: '',
    basicCommunityTaxType: 'voluntary',
    basicCommunityTax: '5.00',
    grossReceiptsBusiness: '',
    salariesProfession: '',
    incomeRealProperty: '',
    totalTax: '',
    interest: '0.00',
    totalAmountPaid: '',
  });

  // File state
  const [photoFile, setPhotoFile] = useState<File | null>(null);
  const [tricycleFile, setTricycleFile] = useState<File | null>(null);
  const [proofFile, setProofFile] = useState<File | null>(null);

  const certificateTypes = [
    { type: 'BRGY. CLEARANCE', icon: 'fa-home', title: 'Barangay Clearance', description: 'Certificate of good moral character' },
    { type: 'BRGY. INDIGENCY', icon: 'fa-hand-holding-heart', title: 'Indigency Certificate', description: 'Certificate of financial status' },
    { type: 'CERTIFICATION OF RESIDENCY', icon: 'fa-map-marker-alt', title: 'Residency Certificate', description: 'Proof of residence' },
    { type: 'TRICYCLE PERMIT', icon: 'fa-motorcycle', title: 'Tricycle Permit', description: 'Operating permit for tricycle' },
    { type: 'CEDULA/CTC', icon: 'fa-file-alt', title: 'Community Tax Certificate', description: 'Cedula/CTC Document' },
  ];

  useEffect(() => {
    const userData = localStorage.getItem('user');
    if (!userData) {
      navigate('/login');
      return;
    }
    const parsedUser = JSON.parse(userData);
    setUser(parsedUser);
    
    // Auto-populate form with user data
    setFormData(prev => ({
      ...prev,
      firstName: parsedUser.first_name || '',
      middleName: parsedUser.middle_name || '',
      lastName: parsedUser.last_name || '',
      address: parsedUser.address || '',
      mobileNumber: parsedUser.phone ? parsedUser.phone.replace('+63', '') : '',
      civilStatus: parsedUser.civil_status || '',
      gender: parsedUser.gender || '',
      birthdate: parsedUser.birthdate || '',
      birthplace: parsedUser.birth_place || '',
    }));
  }, [navigate]);

  const showNotification = (message: string, type: string = 'info') => {
    setNotification({ message, type });
    setTimeout(() => setNotification(null), 4000);
  };

  const handleCertificateSelect = (type: string) => {
    setSelectedType(type);
    setShowForm(true);
  };

  const handleBackToSelection = () => {
    setShowForm(false);
    setSelectedType('');
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));

    // Recalculate tax for Cedula
    if (name === 'basicCommunityTaxType') {
      const basicTax = value === 'voluntary' ? '5.00' : '1.00';
      setFormData(prev => ({ ...prev, basicCommunityTax: basicTax }));
      calculateTotalTax(basicTax);
    }
    if (['grossReceiptsBusiness', 'salariesProfession', 'incomeRealProperty', 'interest'].includes(name)) {
      calculateTotalTax(formData.basicCommunityTax);
    }
  };

  const calculateTotalTax = (basicTax: string) => {
    const grossReceipts = parseFloat(formData.grossReceiptsBusiness) || 0;
    const salaries = parseFloat(formData.salariesProfession) || 0;
    const realProperty = parseFloat(formData.incomeRealProperty) || 0;
    const interest = parseFloat(formData.interest) || 0;

    let additionalTax = 0;
    if (grossReceipts > 0) additionalTax += Math.floor(grossReceipts / 1000);
    if (salaries > 0) additionalTax += Math.floor(salaries / 1000);
    if (realProperty > 0) additionalTax += Math.floor(realProperty / 1000);
    additionalTax = Math.min(additionalTax, 5000);

    const totalTax = parseFloat(basicTax) + additionalTax;
    const totalAmount = totalTax + interest;

    setFormData(prev => ({
      ...prev,
      totalTax: totalTax.toFixed(2),
      totalAmountPaid: totalAmount.toFixed(2),
    }));
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>, type: 'photo' | 'tricycle' | 'proof') => {
    const file = e.target.files?.[0];
    if (file) {
      if (type === 'photo') setPhotoFile(file);
      if (type === 'tricycle') setTricycleFile(file);
      if (type === 'proof') setProofFile(file);
    }
  };

  const handleRemoveFile = (type: 'photo' | 'tricycle' | 'proof') => {
    if (type === 'photo') setPhotoFile(null);
    if (type === 'tricycle') setTricycleFile(null);
    if (type === 'proof') setProofFile(null);
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

    // Validation
    if (!photoFile) {
      setError('Please upload your 1x1 photo. This is required for the certificate.');
      setLoading(false);
      return;
    }

    if (selectedType === 'TRICYCLE PERMIT' && !tricycleFile) {
      setError('Please upload a photo of your tricycle. This is required for tricycle permit.');
      setLoading(false);
      return;
    }

    try {
      const formDataToSend = new FormData();
      formDataToSend.append('certificate_type', selectedType);
      formDataToSend.append('full_name', `${formData.firstName} ${formData.middleName} ${formData.lastName}`);
      formDataToSend.append('address', formData.address);
      formDataToSend.append('mobile_number', '+63' + formData.mobileNumber);
      formDataToSend.append('civil_status', formData.civilStatus);
      formDataToSend.append('gender', formData.gender);
      formDataToSend.append('birth_date', formData.birthdate);
      formDataToSend.append('birth_place', formData.birthplace);
      formDataToSend.append('citizenship', formData.citizenship);
      formDataToSend.append('years_of_residence', formData.yearsOfResidence);
      formDataToSend.append('purpose', formData.purpose);
      formDataToSend.append('photo_image', photoFile);
      if (tricycleFile) formDataToSend.append('tricycle_image', tricycleFile);
      if (proofFile) formDataToSend.append('proof_image', proofFile);

      // Add certificate-specific data
      if (selectedType === 'TRICYCLE PERMIT') {
        formDataToSend.append('vehicle_make_type', formData.makeType);
        formDataToSend.append('motor_no', formData.motorNo);
        formDataToSend.append('chassis_no', formData.chassisNo);
        formDataToSend.append('plate_no', formData.plateNo);
        formDataToSend.append('vehicle_color', formData.vehicleColor);
        formDataToSend.append('year_model', formData.yearModel);
        formDataToSend.append('body_no', formData.bodyNo);
        formDataToSend.append('operator_license', formData.operatorLicense);
      } else if (selectedType === 'CEDULA/CTC') {
        formDataToSend.append('cedula_year', formData.cedulaYear);
        formDataToSend.append('place_of_issue', formData.placeOfIssue);
        formDataToSend.append('date_issued', formData.dateIssued);
        formDataToSend.append('profession_occupation', formData.professionOccupation);
        formDataToSend.append('height', formData.height);
        formDataToSend.append('weight', formData.weight);
        formDataToSend.append('basic_tax_type', formData.basicCommunityTaxType);
        formDataToSend.append('basic_community_tax', formData.basicCommunityTax);
        formDataToSend.append('gross_receipts_business', formData.grossReceiptsBusiness);
        formDataToSend.append('salaries_profession', formData.salariesProfession);
        formDataToSend.append('income_real_property', formData.incomeRealProperty);
        formDataToSend.append('total_tax', formData.totalTax);
        formDataToSend.append('interest', formData.interest);
        formDataToSend.append('total_amount_paid', formData.totalAmountPaid);
      }

      const token = localStorage.getItem('access_token');
      const response = await fetch('http://localhost:8000/api/certificates/request/', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
        body: formDataToSend,
      });

      if (response.ok) {
        setSuccess('Certificate request submitted successfully! You will be notified when it\'s ready.');
        showNotification('Certificate request submitted successfully!', 'success');
        setTimeout(() => navigate('/dashboard'), 2000);
      } else {
        const data = await response.json();
        setError(data.error || 'Error submitting request');
      }
    } catch (err) {
      setError('Error submitting request. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  if (!user) {
    return <div className="loading">Loading...</div>;
  }

  return (
    <div className="certificate-request-wrapper">
      <a href="/dashboard" className="page-nav-link">
        <i className="fas fa-arrow-left"></i> Back to Dashboard
      </a>

      <div className="container">
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

          {!showForm ? (
            <div className="certificate-selection">
              <h2>Select Certificate Type</h2>
              <p>Choose the type of certificate you would like to request</p>
              
              <div className="certificate-grid">
                {certificateTypes.map(cert => (
                  <div
                    key={cert.type}
                    className={`certificate-card ${selectedType === cert.type ? 'selected' : ''}`}
                    onClick={() => handleCertificateSelect(cert.type)}
                  >
                    <i className={`fas ${cert.icon} certificate-icon`}></i>
                    <div className="certificate-title">{cert.title}</div>
                    <div className="certificate-description">{cert.description}</div>
                  </div>
                ))}
              </div>
            </div>
          ) : (
            <div className="form-container show">
              <button className="back-btn" onClick={handleBackToSelection}>
                <i className="fas fa-arrow-left"></i> Back to Selection
              </button>
              
              <form onSubmit={handleSubmit}>
                <input type="hidden" name="certificateType" value={selectedType} />
                
                <div className="form-section">
                  <h3><i className="fas fa-user"></i> Personal Information</h3>
                  
                  <div className="form-grid">
                    <div className="form-group">
                      <label htmlFor="firstName">First Name *</label>
                      <input
                        type="text"
                        id="firstName"
                        name="firstName"
                        required
                        value={formData.firstName}
                        onChange={handleInputChange}
                      />
                    </div>
                    
                    <div className="form-group">
                      <label htmlFor="middleName">Middle Name</label>
                      <input
                        type="text"
                        id="middleName"
                        name="middleName"
                        value={formData.middleName}
                        onChange={handleInputChange}
                      />
                    </div>
                    
                    <div className="form-group">
                      <label htmlFor="lastName">Last Name *</label>
                      <input
                        type="text"
                        id="lastName"
                        name="lastName"
                        required
                        value={formData.lastName}
                        onChange={handleInputChange}
                      />
                    </div>
                  </div>
                  
                  <div className="form-grid">
                    <div className="form-group">
                      <label htmlFor="address">Address *</label>
                      <input
                        type="text"
                        id="address"
                        name="address"
                        required
                        placeholder="Street Address, Barangay Gumaoc East"
                        value={formData.address}
                        onChange={handleInputChange}
                      />
                    </div>
                    
                    <div className="form-group">
                      <label htmlFor="mobileNumber">Mobile Number</label>
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
                  
                  <div className="form-grid">
                    <div className="form-group">
                      <label htmlFor="civilStatus">Civil Status *</label>
                      <select
                        id="civilStatus"
                        name="civilStatus"
                        required
                        value={formData.civilStatus}
                        onChange={handleInputChange}
                      >
                        <option value="">Select Civil Status</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Divorced">Divorced</option>
                        <option value="Widowed">Widowed</option>
                      </select>
                    </div>
                    
                    <div className="form-group">
                      <label htmlFor="gender">Gender *</label>
                      <select
                        id="gender"
                        name="gender"
                        required
                        value={formData.gender}
                        onChange={handleInputChange}
                      >
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                      </select>
                    </div>
                  </div>
                  
                  <div className="form-grid">
                    <div className="form-group">
                      <label htmlFor="birthdate">Birthdate *</label>
                      <input
                        type="date"
                        id="birthdate"
                        name="birthdate"
                        required
                        value={formData.birthdate}
                        onChange={handleInputChange}
                      />
                    </div>
                    
                    <div className="form-group">
                      <label htmlFor="birthplace">Birthplace *</label>
                      <input
                        type="text"
                        id="birthplace"
                        name="birthplace"
                        required
                        placeholder="City/Municipality, Province"
                        value={formData.birthplace}
                        onChange={handleInputChange}
                      />
                    </div>
                  </div>
                  
                  <div className="form-grid">
                    <div className="form-group">
                      <label htmlFor="citizenship">Citizenship</label>
                      <input
                        type="text"
                        id="citizenship"
                        name="citizenship"
                        value={formData.citizenship}
                        onChange={handleInputChange}
                      />
                    </div>
                    
                    <div className="form-group">
                      <label htmlFor="yearsOfResidence">Years of Residence</label>
                      <input
                        type="number"
                        id="yearsOfResidence"
                        name="yearsOfResidence"
                        min="0"
                        placeholder="Number of years"
                        value={formData.yearsOfResidence}
                        onChange={handleInputChange}
                      />
                    </div>
                  </div>
                  
                  <div className="form-group">
                    <label htmlFor="purpose">Purpose *</label>
                    <textarea
                      id="purpose"
                      name="purpose"
                      required
                      rows={3}
                      placeholder="State the purpose for requesting this certificate..."
                      value={formData.purpose}
                      onChange={handleInputChange}
                    ></textarea>
                  </div>
                </div>

                {selectedType === 'TRICYCLE PERMIT' && (
                  <div className="form-section">
                    <h3><i className="fas fa-motorcycle"></i> Tricycle Vehicle Information</h3>
                    
                    <div className="form-grid">
                      <div className="form-group">
                        <label htmlFor="makeType">Make and Type *</label>
                        <input
                          type="text"
                          id="makeType"
                          name="makeType"
                          placeholder="e.g., Honda TMX-155"
                          value={formData.makeType}
                          onChange={handleInputChange}
                        />
                      </div>
                      
                      <div className="form-group">
                        <label htmlFor="motorNo">Motor No. *</label>
                        <input
                          type="text"
                          id="motorNo"
                          name="motorNo"
                          placeholder="Engine/Motor Number"
                          value={formData.motorNo}
                          onChange={handleInputChange}
                        />
                      </div>
                      
                      <div className="form-group">
                        <label htmlFor="chassisNo">Chassis No. *</label>
                        <input
                          type="text"
                          id="chassisNo"
                          name="chassisNo"
                          placeholder="Chassis Number"
                          value={formData.chassisNo}
                          onChange={handleInputChange}
                        />
                      </div>
                    </div>
                    
                    <div className="form-grid">
                      <div className="form-group">
                        <label htmlFor="plateNo">Plate No. *</label>
                        <input
                          type="text"
                          id="plateNo"
                          name="plateNo"
                          placeholder="License Plate Number"
                          value={formData.plateNo}
                          onChange={handleInputChange}
                        />
                      </div>
                      
                      <div className="form-group">
                        <label htmlFor="vehicleColor">Vehicle Color</label>
                        <input
                          type="text"
                          id="vehicleColor"
                          name="vehicleColor"
                          placeholder="Primary color of tricycle"
                          value={formData.vehicleColor}
                          onChange={handleInputChange}
                        />
                      </div>
                      
                      <div className="form-group">
                        <label htmlFor="yearModel">Year Model</label>
                        <input
                          type="number"
                          id="yearModel"
                          name="yearModel"
                          placeholder="e.g., 2020"
                          min={1980}
                          max={new Date().getFullYear()}
                          value={formData.yearModel}
                          onChange={handleInputChange}
                        />
                      </div>
                    </div>
                    
                    <div className="form-grid">
                      <div className="form-group">
                        <label htmlFor="bodyNo">Body No.</label>
                        <input
                          type="text"
                          id="bodyNo"
                          name="bodyNo"
                          placeholder="Body/Frame Number (if applicable)"
                          value={formData.bodyNo}
                          onChange={handleInputChange}
                        />
                      </div>
                      
                      <div className="form-group">
                        <label htmlFor="operatorLicense">Operator's License No.</label>
                        <input
                          type="text"
                          id="operatorLicense"
                          name="operatorLicense"
                          placeholder="License Number"
                          value={formData.operatorLicense}
                          onChange={handleInputChange}
                        />
                      </div>
                    </div>
                    
                    <div className="form-group" style={{ marginTop: '20px' }}>
                      <h4><i className="fas fa-camera"></i> Tricycle Photo</h4>
                      <p className="section-description">Upload a clear photo of your tricycle (required for tricycle permit)</p>
                      
                      <div className="file-upload-container">
                        <input
                          type="file"
                          id="tricycleImage"
                          accept="image/*"
                          onChange={(e) => handleFileChange(e, 'tricycle')}
                          className="file-input"
                          required
                        />
                        <div className="file-upload-display">
                          <div className="file-upload-icon">
                            <i className="fas fa-motorcycle"></i>
                          </div>
                          <div className="file-upload-text">
                            <span className="file-upload-label">Click to upload tricycle photo</span>
                            <span className="file-upload-hint">JPG, PNG up to 5MB - Clear side view recommended</span>
                          </div>
                        </div>
                        {tricycleFile && (
                          <div className="file-preview">
                            <div className="file-preview-info">
                              <div className="file-preview-icon">🖼️</div>
                              <div className="file-preview-details">
                                <div className="file-preview-name">{tricycleFile.name}</div>
                                <div className="file-preview-size">{formatFileSize(tricycleFile.size)}</div>
                              </div>
                              <button type="button" className="file-remove-btn" onClick={() => handleRemoveFile('tricycle')}>
                                ×
                              </button>
                            </div>
                          </div>
                        )}
                      </div>
                    </div>
                  </div>
                )}

                {selectedType === 'CEDULA/CTC' && (
                  <div className="form-section">
                    <h3><i className="fas fa-file-invoice-dollar"></i> Community Tax Certificate Details</h3>
                    
                    <div className="form-grid">
                      <div className="form-group">
                        <label htmlFor="cedulaYear">Year *</label>
                        <input
                          type="number"
                          id="cedulaYear"
                          name="cedulaYear"
                          value={formData.cedulaYear}
                          min={new Date().getFullYear() - 5}
                          max={new Date().getFullYear() + 1}
                          onChange={handleInputChange}
                        />
                      </div>
                      
                      <div className="form-group">
                        <label htmlFor="placeOfIssue">Place of Issue *</label>
                        <input
                          type="text"
                          id="placeOfIssue"
                          name="placeOfIssue"
                          value={formData.placeOfIssue}
                          placeholder="City/Municipality, Province"
                          onChange={handleInputChange}
                        />
                      </div>
                      
                      <div className="form-group">
                        <label htmlFor="dateIssued">Date Issued *</label>
                        <input
                          type="date"
                          id="dateIssued"
                          name="dateIssued"
                          value={formData.dateIssued}
                          onChange={handleInputChange}
                        />
                      </div>
                    </div>
                    
                    <div className="form-grid">
                      <div className="form-group">
                        <label htmlFor="professionOccupation">Profession/Occupation *</label>
                        <input
                          type="text"
                          id="professionOccupation"
                          name="professionOccupation"
                          placeholder="e.g., Teacher, Driver, Farmer"
                          value={formData.professionOccupation}
                          onChange={handleInputChange}
                        />
                      </div>
                      
                      <div className="form-group">
                        <label htmlFor="height">Height (cm)</label>
                        <input
                          type="number"
                          id="height"
                          name="height"
                          placeholder="e.g., 170"
                          min={100}
                          max={250}
                          value={formData.height}
                          onChange={handleInputChange}
                        />
                      </div>
                      
                      <div className="form-group">
                        <label htmlFor="weight">Weight (kg)</label>
                        <input
                          type="number"
                          id="weight"
                          name="weight"
                          placeholder="e.g., 65"
                          min={20}
                          max={200}
                          value={formData.weight}
                          onChange={handleInputChange}
                        />
                      </div>
                    </div>
                    
                    <div className="tax-section">
                      <h4>Tax Information</h4>
                      
                      <div className="form-group">
                        <label>Basic Community Tax *</label>
                        <div className="radio-group">
                          <label className="radio-option">
                            <input
                              type="radio"
                              name="basicCommunityTaxType"
                              value="voluntary"
                              checked={formData.basicCommunityTaxType === 'voluntary'}
                              onChange={handleInputChange}
                            />
                            <span>Voluntary (₱5.00)</span>
                          </label>
                          <label className="radio-option">
                            <input
                              type="radio"
                              name="basicCommunityTaxType"
                              value="exempted"
                              checked={formData.basicCommunityTaxType === 'exempted'}
                              onChange={handleInputChange}
                            />
                            <span>Exempted (₱1.00)</span>
                          </label>
                        </div>
                        <input
                          type="number"
                          id="basicCommunityTax"
                          name="basicCommunityTax"
                          value={formData.basicCommunityTax}
                          step="0.01"
                          readOnly
                        />
                      </div>
                      
                      <div className="form-grid">
                        <div className="form-group">
                          <label htmlFor="grossReceiptsBusiness">Gross Receipts from Business</label>
                          <input
                            type="number"
                            id="grossReceiptsBusiness"
                            name="grossReceiptsBusiness"
                            placeholder="0.00"
                            step="0.01"
                            value={formData.grossReceiptsBusiness}
                            onChange={handleInputChange}
                          />
                        </div>
                        
                        <div className="form-group">
                          <label htmlFor="salariesProfession">Salaries from Profession</label>
                          <input
                            type="number"
                            id="salariesProfession"
                            name="salariesProfession"
                            placeholder="0.00"
                            step="0.01"
                            value={formData.salariesProfession}
                            onChange={handleInputChange}
                          />
                        </div>
                        
                        <div className="form-group">
                          <label htmlFor="incomeRealProperty">Income from Real Property</label>
                          <input
                            type="number"
                            id="incomeRealProperty"
                            name="incomeRealProperty"
                            placeholder="0.00"
                            step="0.01"
                            value={formData.incomeRealProperty}
                            onChange={handleInputChange}
                          />
                        </div>
                      </div>
                      
                      <div className="form-grid">
                        <div className="form-group">
                          <label htmlFor="totalTax">Total Tax</label>
                          <input
                            type="number"
                            id="totalTax"
                            name="totalTax"
                            placeholder="0.00"
                            step="0.01"
                            value={formData.totalTax}
                            readOnly
                          />
                        </div>
                        
                        <div className="form-group">
                          <label htmlFor="interest">Interest</label>
                          <input
                            type="number"
                            id="interest"
                            name="interest"
                            value={formData.interest}
                            step="0.01"
                            onChange={handleInputChange}
                          />
                        </div>
                        
                        <div className="form-group">
                          <label htmlFor="totalAmountPaid">Total Amount</label>
                          <input
                            type="number"
                            id="totalAmountPaid"
                            name="totalAmountPaid"
                            placeholder="0.00"
                            step="0.01"
                            value={formData.totalAmountPaid}
                            readOnly
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                )}
                
                <div className="form-section">
                  <h3><i className="fas fa-user-circle"></i> 1x1 Photo Upload</h3>
                  <p className="section-description">Upload your 1x1 photo for the certificate (required)</p>
                  
                  <div className="file-upload-container">
                    <input
                      type="file"
                      id="photoImage"
                      accept="image/*"
                      onChange={(e) => handleFileChange(e, 'photo')}
                      className="file-input"
                      required
                    />
                    <div className="file-upload-display">
                      <div className="file-upload-icon">
                        <i className="fas fa-user-circle"></i>
                      </div>
                      <div className="file-upload-text">
                        <span className="file-upload-label">Click to upload your 1x1 photo</span>
                        <span className="file-upload-hint">JPG, PNG up to 5MB - 1x1 inch recommended</span>
                      </div>
                    </div>
                    {photoFile && (
                      <div className="file-preview">
                        <div className="file-preview-info">
                          <div className="file-preview-icon">🖼️</div>
                          <div className="file-preview-details">
                            <div className="file-preview-name">{photoFile.name}</div>
                            <div className="file-preview-size">{formatFileSize(photoFile.size)}</div>
                          </div>
                          <button type="button" className="file-remove-btn" onClick={() => handleRemoveFile('photo')}>
                            ×
                          </button>
                        </div>
                      </div>
                    )}
                  </div>
                </div>
                
                <div className="form-section">
                  <h3><i className="fas fa-camera"></i> Optional Proof Image</h3>
                  <p className="section-description">Upload supporting documents or proof (optional)</p>
                  
                  <div className="file-upload-container">
                    <input
                      type="file"
                      id="proofImage"
                      accept="image/*,.pdf"
                      onChange={(e) => handleFileChange(e, 'proof')}
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
                          <button type="button" className="file-remove-btn" onClick={() => handleRemoveFile('proof')}>
                            ×
                          </button>
                        </div>
                      </div>
                    )}
                  </div>
                </div>
                
                <div className="submit-container">
                  <button type="submit" className="submit-btn" disabled={loading}>
                    <i className="fas fa-paper-plane"></i> {loading ? 'Submitting...' : 'Submit Request'}
                  </button>
                </div>
              </form>
            </div>
          )}
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

export default CertificateRequestPage;
