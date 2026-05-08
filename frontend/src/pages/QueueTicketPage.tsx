import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import '../styles/queue-ticket.css';

interface QueueService {
  id: number;
  service_name: string;
  service_code: string;
  estimated_time: number;
}

interface QueueStatus {
  service_name: string;
  waiting_count: number;
  serving_count: number;
  avg_wait_time: number;
}

const QueueTicketPage: React.FC = () => {
  const navigate = useNavigate();
  const [user, setUser] = useState<any>(null);
  const [services, setServices] = useState<QueueService[]>([]);
  const [queueStatus, setQueueStatus] = useState<QueueStatus[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [ticketData, setTicketData] = useState<any>(null);

  const [formData, setFormData] = useState({
    service_id: '',
    full_name: '',
    contact_number: '',
    purpose: '',
    priority_level: 'normal',
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
      full_name: `${parsedUser.first_name} ${parsedUser.middle_name ? parsedUser.middle_name + ' ' : ''}${parsedUser.last_name}`,
      contact_number: parsedUser.phone ? parsedUser.phone.replace('+63', '') : '',
    }));

    fetchServices();
    fetchQueueStatus();
  }, [navigate]);

  const fetchServices = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/queue/services/');
      if (response.ok) {
        const data = await response.json();
        setServices(data.results || data);
      }
    } catch (err) {
      console.error('Error fetching services');
    } finally {
      setLoading(false);
    }
  };

  const fetchQueueStatus = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/queue/status/');
      if (response.ok) {
        const data = await response.json();
        setQueueStatus(data);
      }
    } catch (err) {
      console.error('Error fetching queue status');
    }
  };

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
      const response = await fetch('http://localhost:8000/api/queue/tickets/', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData),
      });

      if (response.ok) {
        const data = await response.json();
        setTicketData(data);
        setSuccess('Queue ticket generated successfully!');
        fetchQueueStatus();
      } else {
        const data = await response.json();
        setError(data.error || 'Error generating ticket');
      }
    } catch (err) {
      setError('Error generating ticket. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const handleNewTicket = () => {
    setTicketData(null);
    setSuccess('');
    setFormData({
      service_id: '',
      full_name: user ? `${user.first_name} ${user.middle_name ? user.middle_name + ' ' : ''}${user.last_name}` : '',
      contact_number: user?.phone ? user.phone.replace('+63', '') : '',
      purpose: '',
      priority_level: 'normal',
    });
  };

  if (!user) {
    return <div className="loading">Loading...</div>;
  }

  return (
    <div className="queue-ticket-wrapper">
      <a href="/dashboard" className="page-nav-link">
        <i className="fas fa-arrow-left"></i> Back to Dashboard
      </a>

      <div className="container">
        <section className="queue-section">
          {ticketData ? (
            <div className="ticket-generated">
              <div className="ticket-card">
                <div className="ticket-header">
                  <h2>🎫 Your Queue Ticket</h2>
                  <div className="ticket-number">{ticketData.ticket_number}</div>
                </div>
                <div className="ticket-body">
                  <div className="ticket-info">
                    <p><strong>Queue Position:</strong> #{ticketData.queue_position}</p>
                    <p><strong>Estimated Time:</strong> {ticketData.estimated_time}</p>
                    <p><strong>Date:</strong> {new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}</p>
                  </div>
                  <div className="ticket-instructions">
                    <h4>📋 Instructions:</h4>
                    <ul>
                      <li>Keep this ticket number safe</li>
                      <li>Monitor the display board for your number</li>
                      <li>Present this ticket when called</li>
                      <li>Arrive 5 minutes before your estimated time</li>
                    </ul>
                  </div>
                </div>
                <div className="ticket-actions">
                  <button className="btn btn-primary" onClick={() => window.print()}>
                    <i className="fas fa-print"></i> Print Ticket
                  </button>
                  <button className="btn btn-secondary" onClick={() => navigate('/queue-status')}>
                    <i className="fas fa-chart-bar"></i> View Queue Status
                  </button>
                  <button className="btn btn-secondary" onClick={handleNewTicket}>
                    <i className="fas fa-plus"></i> New Ticket
                  </button>
                </div>
              </div>
            </div>
          ) : (
            <div className="queue-form-section">
              <div className="queue-header">
                <h2>🎫 Get Your Queue Number</h2>
                <p>Select a service and get your queue ticket for faster processing</p>
              </div>

              {success && (
                <div className="alert alert-success">
                  <i className="fas fa-check-circle"></i> {success}
                </div>
              )}
              {error && (
                <div className="alert alert-error">
                  <i className="fas fa-exclamation-circle"></i> {error}
                </div>
              )}

              <div className="queue-status-display">
                <h3>📊 Current Queue Status</h3>
                <div className="status-grid">
                  {queueStatus.map((status, index) => (
                    <div key={index} className="status-card">
                      <div className="service-name">{status.service_name}</div>
                      <div className="status-stats">
                        <span className="waiting">⏳ {status.waiting_count} waiting</span>
                        <span className="serving">🔄 {status.serving_count} serving</span>
                      </div>
                      <div className="avg-time">Avg: {status.avg_wait_time || 15} min</div>
                    </div>
                  ))}
                </div>
              </div>

              <form onSubmit={handleSubmit} className="queue-form">
                <div className="form-grid">
                  <div className="form-group full-width">
                    <label htmlFor="service_id">🏛️ Select Service <span className="required">*</span></label>
                    <select
                      id="service_id"
                      name="service_id"
                      value={formData.service_id}
                      onChange={handleInputChange}
                      required
                    >
                      <option value="">Choose a service...</option>
                      {services.map(service => (
                        <option key={service.id} value={service.id}>
                          {service.service_name} ({service.service_code}) - ~{service.estimated_time} min
                        </option>
                      ))}
                    </select>
                    <div className="input-help">Select the service you need assistance with</div>
                  </div>

                  <div className="form-group">
                    <label htmlFor="full_name">👤 Full Name <span className="required">*</span></label>
                    <input
                      type="text"
                      id="full_name"
                      name="full_name"
                      value={formData.full_name}
                      onChange={handleInputChange}
                      required
                    />
                  </div>

                  <div className="form-group">
                    <label htmlFor="contact_number">📱 Contact Number</label>
                    <input
                      type="tel"
                      id="contact_number"
                      name="contact_number"
                      value={formData.contact_number}
                      onChange={handleInputChange}
                      placeholder="09XXXXXXXXX"
                    />
                  </div>

                  <div className="form-group full-width">
                    <label htmlFor="purpose">📝 Purpose/Details</label>
                    <textarea
                      id="purpose"
                      name="purpose"
                      rows={3}
                      value={formData.purpose}
                      onChange={handleInputChange}
                      placeholder="Brief description of your request..."
                    ></textarea>
                  </div>

                  <div className="form-group">
                    <label htmlFor="priority_level">⚡ Priority Level</label>
                    <select
                      id="priority_level"
                      name="priority_level"
                      value={formData.priority_level}
                      onChange={handleInputChange}
                    >
                      <option value="normal">Normal</option>
                      <option value="senior">Senior Citizen</option>
                      <option value="pwd">Person with Disability</option>
                      <option value="pregnant">Pregnant</option>
                      <option value="emergency">Emergency</option>
                    </select>
                  </div>
                </div>

                <div className="form-actions">
                  <button type="submit" className="btn btn-primary" disabled={loading}>
                    <i className="fas fa-ticket-alt"></i> {loading ? 'Generating...' : 'Generate Queue Ticket'}
                  </button>
                  <button type="button" className="btn btn-secondary" onClick={() => navigate('/queue-status')}>
                    <i className="fas fa-chart-bar"></i> View Queue Status
                  </button>
                </div>

                <div className="service-links">
                  <h4>📄 Need to submit a form? Get your ticket here:</h4>
                  <div className="service-link-buttons">
                    <button type="button" className="service-link-btn" onClick={() => navigate('/certificate-request')}>
                      📄 Certificate Request
                    </button>
                  </div>
                </div>
              </form>
            </div>
          )}
        </section>
      </div>
    </div>
  );
};

export default QueueTicketPage;
