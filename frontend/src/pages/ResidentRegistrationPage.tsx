import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import '../styles/resident-registration.css';

interface FamilyMember {
  id: number;
  first_name: string;
  middle_name: string;
  last_name: string;
  relationship: string;
  birth_date: string;
  occupation: string;
}

const ResidentRegistrationPage: React.FC = () => {
  const navigate = useNavigate();
  const [user, setUser] = useState<any>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [activeTab, setActiveTab] = useState(1);

  const [formData, setFormData] = useState({
    first_name: '',
    middle_name: '',
    last_name: '',
    birth_date: '',
    gender: '',
    civil_status: '',
    citizenship: 'Filipino',
    birth_place: '',
    address: '',
    mobile_number: '',
    email: '',
    occupation: '',
    monthly_income: '',
    education: '',
  });

  const [familyMembers, setFamilyMembers] = useState<FamilyMember[]>([]);
  const [currentFamilyMember, setCurrentFamilyMember] = useState<Partial<FamilyMember>>({
    first_name: '',
    middle_name: '',
    last_name: '',
    relationship: '',
    birth_date: '',
    occupation: '',
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
      address: parsedUser.address || '',
      mobile_number: parsedUser.phone ? parsedUser.phone.replace('+63', '') : '',
      email: parsedUser.email || '',
    }));
  }, [navigate]);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleFamilyMemberChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setCurrentFamilyMember(prev => ({ ...prev, [name]: value }));
  };

  const addFamilyMember = () => {
    if (currentFamilyMember.first_name && currentFamilyMember.last_name && currentFamilyMember.relationship) {
      setFamilyMembers([...familyMembers, {
        id: Date.now(),
        first_name: currentFamilyMember.first_name,
        middle_name: currentFamilyMember.middle_name || '',
        last_name: currentFamilyMember.last_name,
        relationship: currentFamilyMember.relationship,
        birth_date: currentFamilyMember.birth_date || '',
        occupation: currentFamilyMember.occupation || '',
      }]);
      setCurrentFamilyMember({
        first_name: '',
        middle_name: '',
        last_name: '',
        relationship: '',
        birth_date: '',
        occupation: '',
      });
    }
  };

  const removeFamilyMember = (id: number) => {
    setFamilyMembers(familyMembers.filter(member => member.id !== id));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);

    try {
      const token = localStorage.getItem('access_token');
      const response = await fetch('http://localhost:8000/api/residents/registration/', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          ...formData,
          family_members: familyMembers,
        }),
      });

      if (response.ok) {
        setSuccess('Resident registration submitted successfully! Your information will be reviewed.');
        setTimeout(() => navigate('/dashboard'), 2000);
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

  if (!user) {
    return <div className="loading">Loading...</div>;
  }

  return (
    <div className="resident-registration-wrapper">
      <a href="/dashboard" className="page-nav-link">
        <i className="fas fa-arrow-left"></i> Back to Dashboard
      </a>

      <div className="container">
        <div className="header">
          <h1>📋 Resident Registration</h1>
          <p>Census Registration Form - Barangay Population Census Data Collection</p>
        </div>

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

        <div className="tabs">
          <button
            className={`tab ${activeTab === 1 ? 'active' : ''}`}
            onClick={() => setActiveTab(1)}
          >
            1. Personal Information
          </button>
          <button
            className={`tab ${activeTab === 2 ? 'active' : ''}`}
            onClick={() => setActiveTab(2)}
          >
            2. Family Members (Optional)
          </button>
          <button
            className={`tab ${activeTab === 3 ? 'active' : ''}`}
            onClick={() => setActiveTab(3)}
          >
            3. Additional Information
          </button>
        </div>

        <form onSubmit={handleSubmit}>
          {activeTab === 1 && (
            <div className="tab-content active">
              <div className="form-section">
                <h3>Personal Information</h3>
                <div className="form-grid">
                  <div className="form-group">
                    <label>First Name *</label>
                    <input
                      type="text"
                      name="first_name"
                      value={formData.first_name}
                      onChange={handleInputChange}
                      required
                    />
                  </div>
                  <div className="form-group">
                    <label>Middle Name</label>
                    <input
                      type="text"
                      name="middle_name"
                      value={formData.middle_name}
                      onChange={handleInputChange}
                    />
                  </div>
                  <div className="form-group">
                    <label>Last Name *</label>
                    <input
                      type="text"
                      name="last_name"
                      value={formData.last_name}
                      onChange={handleInputChange}
                      required
                    />
                  </div>
                  <div className="form-group">
                    <label>Birth Date *</label>
                    <input
                      type="date"
                      name="birth_date"
                      value={formData.birth_date}
                      onChange={handleInputChange}
                      required
                    />
                  </div>
                  <div className="form-group">
                    <label>Gender *</label>
                    <select name="gender" value={formData.gender} onChange={handleInputChange} required>
                      <option value="">Select Gender</option>
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                    </select>
                  </div>
                  <div className="form-group">
                    <label>Civil Status *</label>
                    <select name="civil_status" value={formData.civil_status} onChange={handleInputChange} required>
                      <option value="">Select Status</option>
                      <option value="Single">Single</option>
                      <option value="Married">Married</option>
                      <option value="Widowed">Widowed</option>
                      <option value="Separated">Separated</option>
                    </select>
                  </div>
                  <div className="form-group">
                    <label>Citizenship</label>
                    <input
                      type="text"
                      name="citizenship"
                      value={formData.citizenship}
                      onChange={handleInputChange}
                    />
                  </div>
                  <div className="form-group">
                    <label>Birth Place *</label>
                    <input
                      type="text"
                      name="birth_place"
                      value={formData.birth_place}
                      onChange={handleInputChange}
                      required
                    />
                  </div>
                </div>
                <div className="form-group full-width">
                  <label>Complete Address *</label>
                  <textarea
                    name="address"
                    rows={3}
                    value={formData.address}
                    onChange={handleInputChange}
                    required
                  ></textarea>
                </div>
              </div>
              <div className="form-actions">
                <button type="button" className="btn btn-secondary" onClick={() => setActiveTab(2)}>
                  Next: Family Members <i className="fas fa-arrow-right"></i>
                </button>
              </div>
            </div>
          )}

          {activeTab === 2 && (
            <div className="tab-content active">
              <div className="form-section">
                <h3>Family Members (Optional)</h3>
                <div className="family-member-form">
                  <div className="form-grid">
                    <div className="form-group">
                      <label>First Name</label>
                      <input
                        type="text"
                        name="first_name"
                        value={currentFamilyMember.first_name}
                        onChange={handleFamilyMemberChange}
                      />
                    </div>
                    <div className="form-group">
                      <label>Middle Name</label>
                      <input
                        type="text"
                        name="middle_name"
                        value={currentFamilyMember.middle_name}
                        onChange={handleFamilyMemberChange}
                      />
                    </div>
                    <div className="form-group">
                      <label>Last Name</label>
                      <input
                        type="text"
                        name="last_name"
                        value={currentFamilyMember.last_name}
                        onChange={handleFamilyMemberChange}
                      />
                    </div>
                    <div className="form-group">
                      <label>Relationship</label>
                      <select
                        name="relationship"
                        value={currentFamilyMember.relationship}
                        onChange={handleFamilyMemberChange}
                      >
                        <option value="">Select Relationship</option>
                        <option value="Spouse">Spouse</option>
                        <option value="Child">Child</option>
                        <option value="Parent">Parent</option>
                        <option value="Sibling">Sibling</option>
                        <option value="Other">Other</option>
                      </select>
                    </div>
                    <div className="form-group">
                      <label>Birth Date</label>
                      <input
                        type="date"
                        name="birth_date"
                        value={currentFamilyMember.birth_date}
                        onChange={handleFamilyMemberChange}
                      />
                    </div>
                    <div className="form-group">
                      <label>Occupation</label>
                      <input
                        type="text"
                        name="occupation"
                        value={currentFamilyMember.occupation}
                        onChange={handleFamilyMemberChange}
                      />
                    </div>
                  </div>
                  <button type="button" className="btn btn-primary" onClick={addFamilyMember}>
                    <i className="fas fa-plus"></i> Add Family Member
                  </button>
                </div>

                {familyMembers.length > 0 && (
                  <div className="family-members-list">
                    <h4>Added Family Members</h4>
                    {familyMembers.map(member => (
                      <div key={member.id} className="family-member-item">
                        <div className="member-info">
                          <strong>{member.first_name} {member.middle_name} {member.last_name}</strong>
                          <span className="relationship">({member.relationship})</span>
                          {member.occupation && <span className="occupation"> - {member.occupation}</span>}
                        </div>
                        <button type="button" className="btn btn-sm btn-danger" onClick={() => removeFamilyMember(member.id)}>
                          <i className="fas fa-trash"></i>
                        </button>
                      </div>
                    ))}
                  </div>
                )}
              </div>
              <div className="form-actions">
                <button type="button" className="btn btn-secondary" onClick={() => setActiveTab(1)}>
                  <i className="fas fa-arrow-left"></i> Back
                </button>
                <button type="button" className="btn btn-secondary" onClick={() => setActiveTab(3)}>
                  Next: Additional Information <i className="fas fa-arrow-right"></i>
                </button>
              </div>
            </div>
          )}

          {activeTab === 3 && (
            <div className="tab-content active">
              <div className="form-section">
                <h3>Additional Information</h3>
                <div className="form-grid">
                  <div className="form-group">
                    <label>Mobile Number</label>
                    <input
                      type="tel"
                      name="mobile_number"
                      placeholder="09XXXXXXXXX"
                      value={formData.mobile_number}
                      onChange={handleInputChange}
                    />
                  </div>
                  <div className="form-group">
                    <label>Email</label>
                    <input
                      type="email"
                      name="email"
                      value={formData.email}
                      onChange={handleInputChange}
                    />
                  </div>
                  <div className="form-group">
                    <label>Occupation</label>
                    <input
                      type="text"
                      name="occupation"
                      value={formData.occupation}
                      onChange={handleInputChange}
                    />
                  </div>
                  <div className="form-group">
                    <label>Monthly Income</label>
                    <input
                      type="text"
                      name="monthly_income"
                      placeholder="₱"
                      value={formData.monthly_income}
                      onChange={handleInputChange}
                    />
                  </div>
                  <div className="form-group full-width">
                    <label>Highest Education</label>
                    <select name="education" value={formData.education} onChange={handleInputChange}>
                      <option value="">Select Education Level</option>
                      <option value="Elementary">Elementary</option>
                      <option value="High School">High School</option>
                      <option value="College">College</option>
                      <option value="Vocational">Vocational</option>
                      <option value="Postgraduate">Postgraduate</option>
                      <option value="None">None</option>
                    </select>
                  </div>
                </div>
              </div>
              <div className="form-actions">
                <button type="button" className="btn btn-secondary" onClick={() => setActiveTab(2)}>
                  <i className="fas fa-arrow-left"></i> Back
                </button>
                <button type="submit" className="btn btn-primary" disabled={loading}>
                  <i className="fas fa-paper-plane"></i> {loading ? 'Submitting...' : 'Submit Registration'}
                </button>
              </div>
            </div>
          )}
        </form>
      </div>
    </div>
  );
};

export default ResidentRegistrationPage;
