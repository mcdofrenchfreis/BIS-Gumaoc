import React, { useState, useEffect } from 'react';

const ProfilePage: React.FC = () => {
  const [residentData, setResidentData] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchResidentData();
  }, []);

  const fetchResidentData = async () => {
    try {
      const token = localStorage.getItem('token');
      const response = await fetch('/api/residents/profile/', {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });
      if (response.ok) {
        const data = await response.json();
        setResidentData(data);
      }
    } catch (error) {
      console.error('Error fetching resident data:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p className="mt-2 text-gray-600">Loading profile...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 py-8 px-4">
      <div className="max-w-4xl mx-auto">
        <div className="bg-white rounded-lg shadow-lg overflow-hidden">
          {/* Profile Header */}
          <div className="bg-gradient-to-r from-blue-500 to-blue-600 p-8">
            <div className="flex items-center space-x-6">
              <div className="w-24 h-24 rounded-full bg-white flex items-center justify-center">
                <span className="text-4xl text-blue-600">
                  {residentData?.first_name?.[0] || 'U'}
                </span>
              </div>
              <div className="text-white">
                <h1 className="text-2xl font-bold">
                  {residentData?.first_name} {residentData?.last_name}
                </h1>
                <p className="text-blue-100">{residentData?.address || 'No address provided'}</p>
                <span className={`inline-flex mt-2 px-3 py-1 rounded-full text-xs font-medium ${
                  residentData?.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                }`}>
                  {residentData?.status || 'Active'}
                </span>
              </div>
            </div>
          </div>

          {/* Profile Details */}
          <div className="p-8">
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-xl font-bold text-gray-900">Profile Information</h2>
              <a
                href="/settings"
                className="text-blue-600 hover:text-blue-800 font-medium"
              >
                Edit Profile
              </a>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <p className="text-sm text-gray-500">Full Name</p>
                <p className="font-medium text-gray-900">
                  {residentData?.first_name} {residentData?.middle_name} {residentData?.last_name}
                </p>
              </div>

              <div>
                <p className="text-sm text-gray-500">Date of Birth</p>
                <p className="font-medium text-gray-900">
                  {residentData?.birth_date ? new Date(residentData.birth_date).toLocaleDateString() : 'N/A'}
                </p>
              </div>

              <div>
                <p className="text-sm text-gray-500">Gender</p>
                <p className="font-medium text-gray-900">{residentData?.gender || 'N/A'}</p>
              </div>

              <div>
                <p className="text-sm text-gray-500">Civil Status</p>
                <p className="font-medium text-gray-900">{residentData?.civil_status || 'N/A'}</p>
              </div>

              <div>
                <p className="text-sm text-gray-500">Contact Number</p>
                <p className="font-medium text-gray-900">{residentData?.contact_number || 'N/A'}</p>
              </div>

              <div>
                <p className="text-sm text-gray-500">Email</p>
                <p className="font-medium text-gray-900">{residentData?.email || 'N/A'}</p>
              </div>

              <div className="md:col-span-2">
                <p className="text-sm text-gray-500">Address</p>
                <p className="font-medium text-gray-900">{residentData?.address || 'N/A'}</p>
              </div>

              {residentData?.rfid_tag && (
                <div>
                  <p className="text-sm text-gray-500">RFID Tag</p>
                  <p className="font-medium text-gray-900">{residentData.rfid_tag}</p>
                </div>
              )}

              <div>
                <p className="text-sm text-gray-500">Citizenship</p>
                <p className="font-medium text-gray-900">{residentData?.citizenship || 'N/A'}</p>
              </div>
            </div>

            {/* Quick Actions */}
            <div className="mt-8 pt-6 border-t border-gray-200">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a
                  href="/certificates"
                  className="flex items-center p-4 border rounded-lg hover:bg-gray-50 transition-colors"
                >
                  <div className="text-2xl mr-3">📋</div>
                  <div>
                    <p className="font-medium text-gray-900">Request Certificate</p>
                    <p className="text-sm text-gray-600">Get barangay clearance, indigency, etc.</p>
                  </div>
                </a>

                <a
                  href="/my-requests"
                  className="flex items-center p-4 border rounded-lg hover:bg-gray-50 transition-colors"
                >
                  <div className="text-2xl mr-3">📊</div>
                  <div>
                    <p className="font-medium text-gray-900">My Requests</p>
                    <p className="text-sm text-gray-600">Track your submitted requests</p>
                  </div>
                </a>

                <a
                  href="/queue"
                  className="flex items-center p-4 border rounded-lg hover:bg-gray-50 transition-colors"
                >
                  <div className="text-2xl mr-3">🎫</div>
                  <div>
                    <p className="font-medium text-gray-900">Get Queue Number</p>
                    <p className="text-sm text-gray-600">Skip the line with online queuing</p>
                  </div>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ProfilePage;
