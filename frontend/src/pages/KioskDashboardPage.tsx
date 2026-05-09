import React, { useState, useEffect } from 'react';

const KioskDashboardPage: React.FC = () => {
  const [queueStatus, setQueueStatus] = useState<any>(null);
  const [selectedService, setSelectedService] = useState<number | null>(null);

  useEffect(() => {
    fetchQueueStatus();
    const interval = setInterval(fetchQueueStatus, 15000); // Refresh every 15 seconds
    return () => clearInterval(interval);
  }, []);

  const fetchQueueStatus = async () => {
    try {
      const response = await fetch('/api/queue/tickets/queue-status/');
      const data = await response.json();
      setQueueStatus(data);
    } catch (error) {
      console.error('Error fetching queue status:', error);
    }
  };

  const handleServiceSelect = (serviceId: number) => {
    setSelectedService(serviceId);
  };

  const handleGetTicket = async () => {
    if (!selectedService) return;

    try {
      const response = await fetch('/api/queue/tickets/generate/', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          service_id: selectedService,
          full_name: 'Kiosk User',
          contact_number: '',
          purpose: 'Kiosk Service',
          priority_level: 'normal',
        }),
      });

      if (response.ok) {
        const data = await response.json();
        alert(`Ticket ${data.ticket_number} generated successfully!`);
        setSelectedService(null);
        fetchQueueStatus();
      }
    } catch (error) {
      console.error('Error generating ticket:', error);
      alert('Failed to generate ticket');
    }
  };

  const handleLogout = () => {
    localStorage.removeItem('token');
    window.location.href = '/kiosk/login';
  };

  return (
    <div className="min-h-screen bg-gray-100">
      {/* Header */}
      <header className="bg-blue-600 text-white py-4 px-6">
        <div className="max-w-7xl mx-auto flex justify-between items-center">
          <div>
            <h1 className="text-2xl font-bold">Kiosk Dashboard</h1>
            <p className="text-blue-100 text-sm">Barangay Information System</p>
          </div>
          <button
            onClick={handleLogout}
            className="bg-blue-700 hover:bg-blue-800 px-4 py-2 rounded-lg text-sm font-medium"
          >
            Logout
          </button>
        </div>
      </header>

      <main className="max-w-7xl mx-auto py-8 px-6">
        {/* Queue Status */}
        {queueStatus && (
          <div className="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 className="text-xl font-bold text-gray-900 mb-4">Current Queue Status</h2>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              {queueStatus.services?.map((service: any) => (
                <div key={service.service_id} className="border rounded-lg p-4 bg-gray-50">
                  <h3 className="font-semibold text-gray-900">{service.service_name}</h3>
                  <div className="mt-2 space-y-1">
                    <p className="text-sm text-yellow-600">
                      Waiting: <span className="font-bold">{service.waiting_count}</span>
                    </p>
                    <p className="text-sm text-blue-600">
                      Serving: <span className="font-bold">{service.serving_count}</span>
                    </p>
                    <p className="text-sm text-green-600">
                      Completed: <span className="font-bold">{service.completed_count}</span>
                    </p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Service Selection */}
        <div className="bg-white rounded-lg shadow-lg p-6">
          <h2 className="text-xl font-bold text-gray-900 mb-4">Select Service</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            {queueStatus?.services?.map((service: any) => (
              <button
                key={service.service_id}
                onClick={() => handleServiceSelect(service.service_id)}
                className={`p-6 border-2 rounded-lg text-left transition-all ${
                  selectedService === service.service_id
                    ? 'border-blue-500 bg-blue-50'
                    : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50'
                }`}
              >
                <h3 className="font-semibold text-gray-900">{service.service_name}</h3>
                <p className="text-sm text-gray-600 mt-1">
                  Est. Time: {service.estimated_time} mins
                </p>
                <p className="text-sm text-gray-600">
                  Waiting: {service.waiting_count}
                </p>
              </button>
            ))}
          </div>

          {selectedService && (
            <div className="flex justify-end">
              <button
                onClick={handleGetTicket}
                className="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700"
              >
                Get Queue Number
              </button>
            </div>
          )}
        </div>

        {/* Instructions */}
        <div className="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
          <h3 className="font-semibold text-yellow-900 mb-2">Instructions</h3>
          <ol className="list-decimal list-inside text-yellow-800 space-y-1">
            <li>Select the service you need from the options above</li>
            <li>Click "Get Queue Number" to generate your ticket</li>
            <li>Wait for your number to be called on the display</li>
            <li>Proceed to the designated counter when called</li>
          </ol>
        </div>
      </main>

      {/* Auto-logout timer */}
      <div className="fixed bottom-4 right-4 bg-white rounded-lg shadow-lg p-4">
        <p className="text-sm text-gray-600">
          Session will auto-logout after 5 minutes of inactivity
        </p>
      </div>
    </div>
  );
};

export default KioskDashboardPage;
