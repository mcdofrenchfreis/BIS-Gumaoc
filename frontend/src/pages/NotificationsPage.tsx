import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import '../styles/notifications.css';

interface Notification {
  id: number;
  title: string;
  message: string;
  type: 'info' | 'success' | 'warning' | 'error' | 'queue' | 'certificate';
  action_url: string | null;
  is_read: boolean;
  read_at: string | null;
  created_at: string;
}

interface QueueTicket {
  ticket_number: string;
  status: string;
  service_name: string;
  queue_position: number;
  estimated_time: string;
}

const NotificationsPage: React.FC = () => {
  const navigate = useNavigate();
  const [user, setUser] = useState<any>(null);
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [queueTickets, setQueueTickets] = useState<QueueTicket[]>([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState('all');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  useEffect(() => {
    const userData = localStorage.getItem('user');
    if (!userData) {
      navigate('/login');
      return;
    }
    setUser(JSON.parse(userData));
    fetchNotifications();
    fetchQueueTickets();
  }, [navigate]);

  const fetchNotifications = async () => {
    try {
      const token = localStorage.getItem('access_token');
      const response = await fetch(`http://localhost:8000/api/notifications/?filter=${filter}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });
      if (response.ok) {
        const data = await response.json();
        setNotifications(data.results || data);
      }
    } catch (err) {
      console.error('Error fetching notifications');
    } finally {
      setLoading(false);
    }
  };

  const fetchQueueTickets = async () => {
    try {
      const token = localStorage.getItem('access_token');
      const response = await fetch('http://localhost:8000/api/queue/active-tickets/', {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });
      if (response.ok) {
        const data = await response.json();
        setQueueTickets(data.tickets || []);
      }
    } catch (err) {
      console.error('Error fetching queue tickets');
    }
  };

  const markAsRead = async (notificationId: number) => {
    try {
      const token = localStorage.getItem('access_token');
      const response = await fetch(`http://localhost:8000/api/notifications/${notificationId}/mark-read/`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });
      if (response.ok) {
        setSuccess('Notification marked as read');
        fetchNotifications();
      }
    } catch (err) {
      setError('Error marking notification as read');
    }
  };

  const markAllAsRead = async () => {
    try {
      const token = localStorage.getItem('access_token');
      const response = await fetch('http://localhost:8000/api/notifications/mark-all-read/', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });
      if (response.ok) {
        setSuccess('All notifications marked as read');
        fetchNotifications();
      }
    } catch (err) {
      setError('Error marking all notifications as read');
    }
  };

  const handleFilterChange = (newFilter: string) => {
    setFilter(newFilter);
  };

  useEffect(() => {
    if (user) {
      fetchNotifications();
    }
  }, [filter]);

  if (!user) {
    return <div className="loading">Loading...</div>;
  }

  const unreadCount = notifications.filter(n => !n.is_read).length;

  return (
    <div className="notifications-wrapper">
      <a href="/dashboard" className="page-nav-link">
        <i className="fas fa-arrow-left"></i> Back to Dashboard
      </a>

      <div className="container">
        <div className="header">
          <h1>🔔 Notifications</h1>
          <p>Your alerts and updates</p>
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

        {/* Queue Tickets Section */}
        {queueTickets.length > 0 && (
          <div className="queue-tickets-section">
            <h3>🎫 Your Active Queue Tickets</h3>
            <div className="queue-tickets-grid">
              {queueTickets.map((ticket, index) => (
                <div key={index} className="queue-ticket-card">
                  <div className="ticket-number">{ticket.ticket_number}</div>
                  <div className="ticket-service">{ticket.service_name}</div>
                  <div className="ticket-status">
                    <span className={`status-badge ${ticket.status}`}>{ticket.status}</span>
                  </div>
                  <div className="ticket-position">Position: #{ticket.queue_position}</div>
                  <div className="ticket-time">Est: {ticket.estimated_time}</div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Notifications Section */}
        <div className="notifications-section">
          <div className="notifications-header">
            <div className="filter-tabs">
              <button
                className={`filter-tab ${filter === 'all' ? 'active' : ''}`}
                onClick={() => handleFilterChange('all')}
              >
                All ({notifications.length})
              </button>
              <button
                className={`filter-tab ${filter === 'unread' ? 'active' : ''}`}
                onClick={() => handleFilterChange('unread')}
              >
                Unread ({unreadCount})
              </button>
              <button
                className={`filter-tab ${filter === 'read' ? 'active' : ''}`}
                onClick={() => handleFilterChange('read')}
              >
                Read
              </button>
            </div>
            {unreadCount > 0 && (
              <button className="btn btn-secondary btn-sm" onClick={markAllAsRead}>
                <i className="fas fa-check-double"></i> Mark All Read
              </button>
            )}
          </div>

          {loading ? (
            <div className="loading">Loading notifications...</div>
          ) : notifications.length === 0 ? (
            <div className="empty-state">
              <div className="empty-icon">📭</div>
              <h3>No notifications</h3>
              <p>You don't have any notifications at the moment.</p>
            </div>
          ) : (
            <div className="notifications-list">
              {notifications.map(notification => (
                <div
                  key={notification.id}
                  className={`notification-item ${!notification.is_read ? 'unread' : ''}`}
                >
                  <div className={`notification-icon ${notification.type}`}>
                    {getNotificationIcon(notification.type)}
                  </div>
                  <div className="notification-content">
                    <div className="notification-header">
                      <h4 className="notification-title">{notification.title}</h4>
                      {!notification.is_read && <span className="unread-badge">New</span>}
                    </div>
                    <p className="notification-message">{notification.message}</p>
                    <div className="notification-footer">
                      <span className="notification-time">
                        {formatDate(notification.created_at)}
                      </span>
                      {notification.action_url && (
                        <a href={notification.action_url} className="notification-action">
                          View Details <i className="fas fa-arrow-right"></i>
                        </a>
                      )}
                    </div>
                  </div>
                  {!notification.is_read && (
                    <button
                      className="btn btn-sm btn-outline"
                      onClick={() => markAsRead(notification.id)}
                    >
                      <i className="fas fa-check"></i>
                    </button>
                  )}
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

function getNotificationIcon(type: string): string {
  switch (type) {
    case 'success':
      return '✅';
    case 'warning':
      return '⚠️';
    case 'error':
      return '❌';
    case 'queue':
      return '🎫';
    case 'certificate':
      return '📄';
    default:
      return 'ℹ️';
  }
}

function formatDate(dateString: string): string {
  const date = new Date(dateString);
  const now = new Date();
  const diff = now.getTime() - date.getTime();
  const minutes = Math.floor(diff / 60000);
  const hours = Math.floor(diff / 3600000);
  const days = Math.floor(diff / 86400000);

  if (minutes < 1) return 'Just now';
  if (minutes < 60) return `${minutes}m ago`;
  if (hours < 24) return `${hours}h ago`;
  if (days < 7) return `${days}d ago`;
  return date.toLocaleDateString();
}

export default NotificationsPage;
