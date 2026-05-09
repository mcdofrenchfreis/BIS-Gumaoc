import React, { useState, useEffect } from 'react';

const AdminQueueManagePage: React.FC = () => {
  const [queueData, setQueueData] = useState<any>(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    fetchQueueData();
    const interval = setInterval(fetchQueueData, 30000); // Refresh every 30 seconds
    return () => clearInterval(interval);
  }, []);

  const fetchQueueData = async () => {
    setLoading(true);
    try {
      const response = await fetch('/api/queue/management/');
      const data = await response.json();
      setQueueData(data);
    } catch (error) {
      console.error('Error fetching queue data:', error);
    } finally {
      setLoading(false);
    }
  };

  const callNext = async (counterId: number) => {
    try {
      const response = await fetch(`/api/queue/counters/${counterId}/call-next/`, {
        method: 'POST',
      });
      if (response.ok) {
        fetchQueueData();
      }
    } catch (error) {
      console.error('Error calling next:', error);
    }
  };

  const completeTicket = async (counterId: number) => {
    try {
      const response = await fetch(`/api/queue/counters/${counterId}/complete-ticket/`, {
        method: 'POST',
      });
      if (response.ok) {
        fetchQueueData();
      }
    } catch (error) {
      console.error('Error completing ticket:', error);
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 py-8 px-4">
      <div className="max-w-7xl mx-auto">
        <div className="flex justify-between items-center mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Queue Management</h1>
          <button
            onClick={fetchQueueData}
            className="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
          >
            Refresh
          </button>
        </div>
        
        {loading ? (
          <div className="text-center py-12">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p className="mt-2 text-gray-600">Loading queue data...</p>
          </div>
        ) : queueData ? (
          <div className="space-y-6">
            {/* Statistics Cards */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div className="bg-white rounded-lg shadow p-6">
                <h3 className="text-sm font-medium text-gray-500 uppercase">Total Tickets</h3>
                <p className="text-3xl font-bold text-gray-900 mt-2">{queueData.total_tickets}</p>
              </div>
              <div className="bg-white rounded-lg shadow p-6">
                <h3 className="text-sm font-medium text-gray-500 uppercase">Waiting</h3>
                <p className="text-3xl font-bold text-yellow-600 mt-2">{queueData.waiting_tickets}</p>
              </div>
              <div className="bg-white rounded-lg shadow p-6">
                <h3 className="text-sm font-medium text-gray-500 uppercase">Serving</h3>
                <p className="text-3xl font-bold text-blue-600 mt-2">{queueData.serving_tickets}</p>
              </div>
              <div className="bg-white rounded-lg shadow p-6">
                <h3 className="text-sm font-medium text-gray-500 uppercase">Completed</h3>
                <p className="text-3xl font-bold text-green-600 mt-2">{queueData.completed_tickets}</p>
              </div>
            </div>
            
            {/* Service Statistics */}
            <div className="bg-white rounded-lg shadow-lg overflow-hidden">
              <div className="p-6 border-b border-gray-200">
                <h2 className="text-lg font-semibold text-gray-900">Service Statistics</h2>
              </div>
              <div className="p-6">
                <div className="overflow-x-auto">
                  <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                      <tr>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Service
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Total
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Waiting
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Serving
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Completed
                        </th>
                      </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                      {queueData.services?.map((service: any, index: number) => (
                        <tr key={index} className="hover:bg-gray-50">
                          <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {service.service_name}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {service.total}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-yellow-600 font-medium">
                            {service.waiting}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium">
                            {service.serving}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">
                            {service.completed}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            
            {/* Counter Management */}
            <div className="bg-white rounded-lg shadow-lg overflow-hidden">
              <div className="p-6 border-b border-gray-200">
                <h2 className="text-lg font-semibold text-gray-900">Counter Management</h2>
                <p className="text-sm text-gray-600 mt-1">Manage queue counters and call next tickets</p>
              </div>
              <div className="p-6">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                  {[1, 2, 3].map((counterId) => (
                    <div key={counterId} className="border rounded-lg p-4 bg-gray-50">
                      <h3 className="text-lg font-semibold text-gray-900 mb-4">Counter {counterId}</h3>
                      <div className="mb-4">
                        <p className="text-sm text-gray-600">Current Ticket:</p>
                        <p className="text-2xl font-bold text-blue-600">--</p>
                      </div>
                      <div className="space-y-2">
                        <button
                          onClick={() => callNext(counterId)}
                          className="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                        >
                          Call Next
                        </button>
                        <button
                          onClick={() => completeTicket(counterId)}
                          className="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                        >
                          Complete
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        ) : (
          <div className="text-center py-12">
            <p className="text-gray-600">No queue data available</p>
          </div>
        )}
      </div>
    </div>
  );
};

export default AdminQueueManagePage;
