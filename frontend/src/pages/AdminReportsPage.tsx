import React, { useState, useEffect } from 'react';

const AdminReportsPage: React.FC = () => {
  const [activeTab, setActiveTab] = useState('certificates');
  const [reportData, setReportData] = useState<any>(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    fetchReportData(activeTab);
  }, [activeTab]);

  const fetchReportData = async (tab: string) => {
    setLoading(true);
    try {
      // Placeholder API calls - implement actual endpoints
      const mockData = {
        certificates: {
          total: 150,
          pending: 25,
          approved: 100,
          rejected: 5,
          released: 20
        },
        business: {
          total: 50,
          pending: 10,
          approved: 35,
          rejected: 5
        },
        residents: {
          total: 1200,
          registered: 1000,
          pending: 150,
          inactive: 50
        },
        blotter: {
          total: 30,
          open: 10,
          resolved: 18,
          dismissed: 2
        }
      };
      setReportData(mockData[tab as keyof typeof mockData]);
    } catch (error) {
      console.error('Error fetching report data:', error);
    } finally {
      setLoading(false);
    }
  };

  const tabs = [
    { id: 'certificates', label: 'Certificates' },
    { id: 'business', label: 'Business Applications' },
    { id: 'residents', label: 'Residents' },
    { id: 'blotter', label: 'Blotter Reports' }
  ];

  return (
    <div className="min-h-screen bg-gray-50 py-8 px-4">
      <div className="max-w-7xl mx-auto">
        <h1 className="text-3xl font-bold text-gray-900 mb-8">Reports & Analytics</h1>
        
        <div className="bg-white rounded-lg shadow-lg">
          <div className="border-b border-gray-200">
            <nav className="flex space-x-8 px-6" aria-label="Tabs">
              {tabs.map((tab) => (
                <button
                  key={tab.id}
                  onClick={() => setActiveTab(tab.id)}
                  className={`py-4 px-1 border-b-2 font-medium text-sm ${
                    activeTab === tab.id
                      ? 'border-blue-500 text-blue-600'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  }`}
                >
                  {tab.label}
                </button>
              ))}
            </nav>
          </div>
          
          <div className="p-6">
            {loading ? (
              <div className="text-center py-12">
                <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p className="mt-2 text-gray-600">Loading report data...</p>
              </div>
            ) : reportData ? (
              <div>
                <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                  <div className="bg-blue-50 rounded-lg p-6">
                    <h3 className="text-sm font-medium text-blue-600 uppercase">Total</h3>
                    <p className="text-3xl font-bold text-blue-900 mt-2">{reportData.total}</p>
                  </div>
                  <div className="bg-yellow-50 rounded-lg p-6">
                    <h3 className="text-sm font-medium text-yellow-600 uppercase">Pending</h3>
                    <p className="text-3xl font-bold text-yellow-900 mt-2">{reportData.pending}</p>
                  </div>
                  <div className="bg-green-50 rounded-lg p-6">
                    <h3 className="text-sm font-medium text-green-600 uppercase">
                      {activeTab === 'blotter' ? 'Resolved' : 'Approved'}
                    </h3>
                    <p className="text-3xl font-bold text-green-900 mt-2">
                      {reportData.approved || reportData.resolved}
                    </p>
                  </div>
                  <div className="bg-red-50 rounded-lg p-6">
                    <h3 className="text-sm font-medium text-red-600 uppercase">
                      {activeTab === 'blotter' ? 'Dismissed' : 'Rejected'}
                    </h3>
                    <p className="text-3xl font-bold text-red-900 mt-2">
                      {reportData.rejected || reportData.dismissed}
                    </p>
                  </div>
                </div>
                
                <div className="border-t border-gray-200 pt-6">
                  <h2 className="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h2>
                  <div className="bg-gray-50 rounded-lg p-4">
                    <p className="text-gray-600 text-center py-8">
                      Detailed activity logs and charts would be displayed here.
                      Implement with chart libraries like Chart.js or Recharts.
                    </p>
                  </div>
                </div>
                
                <div className="mt-6 flex justify-end space-x-4">
                  <button className="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Export CSV
                  </button>
                  <button className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Generate Report
                  </button>
                </div>
              </div>
            ) : (
              <div className="text-center py-12">
                <p className="text-gray-600">No data available</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default AdminReportsPage;
