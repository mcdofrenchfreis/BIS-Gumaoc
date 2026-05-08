import React, { useState, useEffect } from 'react';
import '../styles/admin-blotter.css';

interface BlotterRecord {
  id: number;
  blotter_number: string;
  incident_type: string;
  complainant_name: string;
  complainant_address: string;
  complainant_contact: string;
  respondent_name: string;
  respondent_address: string;
  respondent_contact: string;
  incident_date: string;
  location: string;
  description: string;
  classification: string;
  status: string;
  investigating_officer: string;
  action_taken: string;
  settlement_details: string;
  created_at: string;
}

interface Resident {
  id: number;
  first_name: string;
  middle_name: string;
  last_name: string;
}

const AdminBlotterPage: React.FC = () => {
  const [blotterRecords, setBlotterRecords] = useState<BlotterRecord[]>([]);
  const [residents, setResidents] = useState<Resident[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [editingRecord, setEditingRecord] = useState<BlotterRecord | null>(null);
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState('');

  const [formData, setFormData] = useState({
    incident_type: 'complaint',
    complainant_resident_id: '',
    complainant_name: '',
    complainant_address: '',
    complainant_contact: '',
    respondent_resident_id: '',
    respondent_name: '',
    respondent_address: '',
    respondent_contact: '',
    incident_date: new Date().toISOString().split('T')[0],
    location: '',
    description: '',
    classification: 'minor',
    investigating_officer: '',
  });

  const [updateFormData, setUpdateFormData] = useState({
    status: '',
    action_taken: '',
    settlement_details: '',
  });

  const [stats, setStats] = useState({
    total: 0,
    filed: 0,
    resolved: 0,
    critical: 0,
  });

  useEffect(() => {
    fetchBlotterRecords();
    fetchResidents();
    fetchStats();
  }, []);

  const fetchBlotterRecords = async () => {
    try {
      const token = localStorage.getItem('admin_token');
      const response = await fetch('http://localhost:8000/api/blotter/records/', {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });
      if (response.ok) {
        const data = await response.json();
        setBlotterRecords(data);
      }
    } catch (err) {
      setError('Error fetching blotter records');
    } finally {
      setLoading(false);
    }
  };

  const fetchResidents = async () => {
    try {
      const token = localStorage.getItem('admin_token');
      const response = await fetch('http://localhost:8000/api/residents/', {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });
      if (response.ok) {
        const data = await response.json();
        setResidents(data.results || data);
      }
    } catch (err) {
      console.error('Error fetching residents');
    }
  };

  const fetchStats = async () => {
    try {
      const token = localStorage.getItem('admin_token');
      const response = await fetch('http://localhost:8000/api/blotter/stats/', {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });
      if (response.ok) {
        const data = await response.json();
        setStats(data);
      }
    } catch (err) {
      console.error('Error fetching stats');
    }
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleUpdateInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setUpdateFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleResidentSelect = (field: 'complainant' | 'respondent', residentId: string) => {
    const resident = residents.find(r => r.id === parseInt(residentId));
    if (resident) {
      const fullName = `${resident.first_name} ${resident.middle_name ? resident.middle_name + ' ' : ''}${resident.last_name}`;
      if (field === 'complainant') {
        setFormData(prev => ({
          ...prev,
          complainant_resident_id: residentId,
          complainant_name: fullName,
        }));
      } else {
        setFormData(prev => ({
          ...prev,
          respondent_resident_id: residentId,
          respondent_name: fullName,
        }));
      }
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setSuccess('');

    try {
      const token = localStorage.getItem('admin_token');
      const response = await fetch('http://localhost:8000/api/blotter/records/', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData),
      });

      if (response.ok) {
        setSuccess('Blotter record added successfully!');
        setShowForm(false);
        setFormData({
          incident_type: 'complaint',
          complainant_resident_id: '',
          complainant_name: '',
          complainant_address: '',
          complainant_contact: '',
          respondent_resident_id: '',
          respondent_name: '',
          respondent_address: '',
          respondent_contact: '',
          incident_date: new Date().toISOString().split('T')[0],
          location: '',
          description: '',
          classification: 'minor',
          investigating_officer: '',
        });
        fetchBlotterRecords();
        fetchStats();
      } else {
        const data = await response.json();
        setError(data.error || 'Error adding blotter record');
      }
    } catch (err) {
      setError('Error adding blotter record');
    }
  };

  const handleUpdateStatus = async (recordId: number) => {
    setError('');
    setSuccess('');

    try {
      const token = localStorage.getItem('admin_token');
      const response = await fetch(`http://localhost:8000/api/blotter/records/${recordId}/update_status/`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(updateFormData),
      });

      if (response.ok) {
        setSuccess('Blotter record updated successfully!');
        setEditingRecord(null);
        setUpdateFormData({ status: '', action_taken: '', settlement_details: '' });
        fetchBlotterRecords();
        fetchStats();
      } else {
        const data = await response.json();
        setError(data.error || 'Error updating blotter record');
      }
    } catch (err) {
      setError('Error updating blotter record');
    }
  };

  const filteredRecords = blotterRecords.filter(record => {
    const matchesSearch = 
      record.complainant_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      record.respondent_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      record.blotter_number.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesStatus = !statusFilter || record.status === statusFilter;
    return matchesSearch && matchesStatus;
  });

  const getClassificationColor = (classification: string) => {
    switch (classification) {
      case 'critical': return '#d32f2f';
      case 'major': return '#f57c00';
      case 'minor': return '#388e3c';
      default: return '#666';
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'resolved': return '#388e3c';
      case 'filed': return '#1976d2';
      case 'under_investigation': return '#f57c00';
      case 'mediation': return '#9c27b0';
      case 'dismissed': return '#757575';
      default: return '#666';
    }
  };

  if (loading) {
    return <div className="loading">Loading...</div>;
  }

  return (
    <div className="admin-blotter-wrapper">
      <a href="/admin" className="page-nav-link">
        <i className="fas fa-arrow-left"></i> Back to Dashboard
      </a>

      <div className="container">
        <div className="header">
          <h1>📋 Blotter Management</h1>
          <p>Manage barangay blotter records and incidents</p>
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

        <div className="stats-grid">
          <div className="stat-card">
            <div className="stat-number">{stats.total}</div>
            <div className="stat-label">Total Records</div>
          </div>
          <div className="stat-card">
            <div className="stat-number">{stats.filed}</div>
            <div className="stat-label">Filed Cases</div>
          </div>
          <div className="stat-card">
            <div className="stat-number">{stats.resolved}</div>
            <div className="stat-label">Resolved Cases</div>
          </div>
          <div className="stat-card">
            <div className="stat-number" style={{ color: '#d32f2f' }}>{stats.critical}</div>
            <div className="stat-label">Critical Cases</div>
          </div>
        </div>

        <div className="controls">
          <div className="form-grid">
            <div className="form-group">
              <label>Search:</label>
              <input
                type="text"
                className="form-control"
                placeholder="Search by name or blotter number..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
              />
            </div>
            <div className="form-group">
              <label>Status Filter:</label>
              <select
                className="form-control"
                value={statusFilter}
                onChange={(e) => setStatusFilter(e.target.value)}
              >
                <option value="">All Status</option>
                <option value="filed">Filed</option>
                <option value="under_investigation">Under Investigation</option>
                <option value="mediation">Mediation</option>
                <option value="resolved">Resolved</option>
                <option value="dismissed">Dismissed</option>
              </select>
            </div>
            <button className="btn btn-primary" onClick={() => setShowForm(!showForm)}>
              <i className="fas fa-plus"></i> {showForm ? 'Cancel' : 'Add New Blotter'}
            </button>
          </div>
        </div>

        {showForm && (
          <div className="form-section">
            <h3>Add New Blotter Record</h3>
            <form onSubmit={handleSubmit}>
              <div className="form-grid">
                <div className="form-group">
                  <label>Incident Type *</label>
                  <select name="incident_type" value={formData.incident_type} onChange={handleInputChange} required>
                    <option value="complaint">Complaint</option>
                    <option value="incident">Incident</option>
                    <option value="dispute">Dispute</option>
                    <option value="violation">Violation</option>
                  </select>
                </div>
                <div className="form-group">
                  <label>Classification *</label>
                  <select name="classification" value={formData.classification} onChange={handleInputChange} required>
                    <option value="minor">Minor</option>
                    <option value="major">Major</option>
                    <option value="critical">Critical</option>
                  </select>
                </div>
                <div className="form-group">
                  <label>Incident Date *</label>
                  <input type="date" name="incident_date" value={formData.incident_date} onChange={handleInputChange} required />
                </div>
              </div>

              <h4>Complainant Information</h4>
              <div className="form-grid">
                <div className="form-group">
                  <label>Resident (Optional)</label>
                  <select
                    value={formData.complainant_resident_id}
                    onChange={(e) => handleResidentSelect('complainant', e.target.value)}
                  >
                    <option value="">Select Resident</option>
                    {residents.map(resident => (
                      <option key={resident.id} value={resident.id}>
                        {resident.first_name} {resident.middle_name} {resident.last_name}
                      </option>
                    ))}
                  </select>
                </div>
                <div className="form-group">
                  <label>Name *</label>
                  <input
                    type="text"
                    name="complainant_name"
                    value={formData.complainant_name}
                    onChange={handleInputChange}
                    required
                  />
                </div>
                <div className="form-group">
                  <label>Contact</label>
                  <input type="text" name="complainant_contact" value={formData.complainant_contact} onChange={handleInputChange} />
                </div>
              </div>
              <div className="form-group">
                <label>Address</label>
                <textarea name="complainant_address" rows={2} value={formData.complainant_address} onChange={handleInputChange}></textarea>
              </div>

              <h4>Respondent Information</h4>
              <div className="form-grid">
                <div className="form-group">
                  <label>Resident (Optional)</label>
                  <select
                    value={formData.respondent_resident_id}
                    onChange={(e) => handleResidentSelect('respondent', e.target.value)}
                  >
                    <option value="">Select Resident</option>
                    {residents.map(resident => (
                      <option key={resident.id} value={resident.id}>
                        {resident.first_name} {resident.middle_name} {resident.last_name}
                      </option>
                    ))}
                  </select>
                </div>
                <div className="form-group">
                  <label>Name *</label>
                  <input
                    type="text"
                    name="respondent_name"
                    value={formData.respondent_name}
                    onChange={handleInputChange}
                    required
                  />
                </div>
                <div className="form-group">
                  <label>Contact</label>
                  <input type="text" name="respondent_contact" value={formData.respondent_contact} onChange={handleInputChange} />
                </div>
              </div>
              <div className="form-group">
                <label>Address</label>
                <textarea name="respondent_address" rows={2} value={formData.respondent_address} onChange={handleInputChange}></textarea>
              </div>

              <div className="form-grid">
                <div className="form-group">
                  <label>Location *</label>
                  <input type="text" name="location" value={formData.location} onChange={handleInputChange} required />
                </div>
                <div className="form-group">
                  <label>Investigating Officer</label>
                  <input type="text" name="investigating_officer" value={formData.investigating_officer} onChange={handleInputChange} />
                </div>
              </div>

              <div className="form-group">
                <label>Description *</label>
                <textarea name="description" rows={4} value={formData.description} onChange={handleInputChange} required></textarea>
              </div>

              <div className="form-actions">
                <button type="submit" className="btn btn-success">
                  <i className="fas fa-save"></i> Save Record
                </button>
                <button type="button" className="btn btn-secondary" onClick={() => setShowForm(false)}>
                  <i className="fas fa-times"></i> Cancel
                </button>
              </div>
            </form>
          </div>
        )}

        <div className="table-container">
          <table className="table">
            <thead>
              <tr>
                <th>Blotter No.</th>
                <th>Date</th>
                <th>Type</th>
                <th>Complainant</th>
                <th>Respondent</th>
                <th>Classification</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {filteredRecords.map(record => (
                <tr key={record.id}>
                  <td>{record.blotter_number}</td>
                  <td>{new Date(record.incident_date).toLocaleDateString()}</td>
                  <td>{record.incident_type}</td>
                  <td>{record.complainant_name}</td>
                  <td>{record.respondent_name}</td>
                  <td>
                    <span className="status-badge" style={{ backgroundColor: `${getClassificationColor(record.classification)}20`, color: getClassificationColor(record.classification) }}>
                      {record.classification}
                    </span>
                  </td>
                  <td>
                    <span className="status-badge" style={{ backgroundColor: `${getStatusColor(record.status)}20`, color: getStatusColor(record.status) }}>
                      {record.status.replace(/_/g, ' ')}
                    </span>
                  </td>
                  <td>
                    <button className="btn btn-sm btn-primary" onClick={() => setEditingRecord(record)}>
                      <i className="fas fa-edit"></i> Update
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {editingRecord && (
          <div className="modal">
            <div className="modal-content">
              <div className="modal-header">
                <h3>Update Blotter Status</h3>
                <button className="modal-close" onClick={() => setEditingRecord(null)}>
                  <i className="fas fa-times"></i>
                </button>
              </div>
              <div className="modal-body">
                <div className="form-group">
                  <label>Status *</label>
                  <select
                    name="status"
                    value={updateFormData.status || editingRecord.status}
                    onChange={handleUpdateInputChange}
                  >
                    <option value="filed">Filed</option>
                    <option value="under_investigation">Under Investigation</option>
                    <option value="mediation">Mediation</option>
                    <option value="resolved">Resolved</option>
                    <option value="dismissed">Dismissed</option>
                  </select>
                </div>
                <div className="form-group">
                  <label>Action Taken</label>
                  <textarea
                    name="action_taken"
                    rows={3}
                    value={updateFormData.action_taken || editingRecord.action_taken || ''}
                    onChange={handleUpdateInputChange}
                  ></textarea>
                </div>
                <div className="form-group">
                  <label>Settlement Details</label>
                  <textarea
                    name="settlement_details"
                    rows={3}
                    value={updateFormData.settlement_details || editingRecord.settlement_details || ''}
                    onChange={handleUpdateInputChange}
                  ></textarea>
                </div>
                <div className="form-actions">
                  <button className="btn btn-success" onClick={() => handleUpdateStatus(editingRecord.id)}>
                    <i className="fas fa-save"></i> Update
                  </button>
                  <button className="btn btn-secondary" onClick={() => setEditingRecord(null)}>
                    <i className="fas fa-times"></i> Cancel
                  </button>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default AdminBlotterPage;
