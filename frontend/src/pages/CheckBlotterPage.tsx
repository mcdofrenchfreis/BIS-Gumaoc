import React, { useState } from 'react';

const CheckBlotterPage: React.FC = () => {
  const [blotterNumber, setBlotterNumber] = useState('');
  const [result, setResult] = useState<any>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSearch = async () => {
    if (!blotterNumber.trim()) {
      setError('Please enter a blotter number');
      return;
    }

    setLoading(true);
    setError('');
    setResult(null);

    try {
      const response = await fetch(`/api/blotter/?search=${blotterNumber}`);
      if (response.ok) {
        const data = await response.json();
        if (data.results && data.results.length > 0) {
          setResult(data.results[0]);
        } else {
          setError('No blotter record found with this number');
        }
      } else {
        setError('Failed to search blotter record');
      }
    } catch (error) {
      setError('Connection error. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 py-8 px-4">
      <div className="max-w-3xl mx-auto">
        <div className="bg-white rounded-lg shadow-lg p-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-6">Check Blotter Status</h1>
          
          <div className="mb-6">
            <label htmlFor="blotterNumber" className="block text-sm font-medium text-gray-700 mb-2">
              Blotter Number
            </label>
            <div className="flex space-x-4">
              <input
                type="text"
                id="blotterNumber"
                value={blotterNumber}
                onChange={(e) => setBlotterNumber(e.target.value)}
                onKeyPress={(e) => e.key === 'Enter' && handleSearch()}
                placeholder="Enter blotter number (e.g., BLT-2024-001)"
                className="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
              <button
                onClick={handleSearch}
                disabled={loading}
                className="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 disabled:bg-gray-400"
              >
                {loading ? 'Searching...' : 'Search'}
              </button>
            </div>
          </div>

          {error && (
            <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
              <p className="text-sm text-red-800">{error}</p>
            </div>
          )}

          {result && (
            <div className="border-t border-gray-200 pt-6">
              <h2 className="text-xl font-semibold text-gray-900 mb-4">Blotter Details</h2>
              <div className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <p className="text-sm text-gray-500">Blotter Number</p>
                    <p className="font-medium text-gray-900">{result.incident_number || result.id}</p>
                  </div>
                  <div>
                    <p className="text-sm text-gray-500">Incident Type</p>
                    <p className="font-medium text-gray-900">{result.incident_type}</p>
                  </div>
                  <div>
                    <p className="text-sm text-gray-500">Date Reported</p>
                    <p className="font-medium text-gray-900">
                      {result.reported_date ? new Date(result.reported_date).toLocaleDateString() : 'N/A'}
                    </p>
                  </div>
                  <div>
                    <p className="text-sm text-gray-500">Status</p>
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${
                      result.status === 'resolved' ? 'bg-green-100 text-green-800' :
                      result.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                      'bg-gray-100 text-gray-800'
                    }`}>
                      {result.status}
                    </span>
                  </div>
                  <div>
                    <p className="text-sm text-gray-500">Complainant</p>
                    <p className="font-medium text-gray-900">{result.complainant_name || 'N/A'}</p>
                  </div>
                  <div>
                    <p className="text-sm text-gray-500">Respondent</p>
                    <p className="font-medium text-gray-900">{result.respondent_name || 'N/A'}</p>
                  </div>
                </div>
                
                <div>
                  <p className="text-sm text-gray-500">Location</p>
                  <p className="font-medium text-gray-900">{result.location || 'N/A'}</p>
                </div>
                
                <div>
                  <p className="text-sm text-gray-500">Description</p>
                  <p className="font-medium text-gray-900">{result.description || 'N/A'}</p>
                </div>

                {result.action_taken && (
                  <div>
                    <p className="text-sm text-gray-500">Action Taken</p>
                    <p className="font-medium text-gray-900">{result.action_taken}</p>
                  </div>
                )}
              </div>
            </div>
          )}

          <div className="mt-8 pt-6 border-t border-gray-200">
            <p className="text-sm text-gray-600 mb-4">
              Don't have a blotter number? Contact the barangay office for assistance.
            </p>
            <a
              href="/contact"
              className="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium"
            >
              Contact Barangay Office
              <svg className="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            </a>
          </div>
        </div>
      </div>
    </div>
  );
};

export default CheckBlotterPage;
