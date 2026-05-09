import React, { useState } from 'react';

const KioskLoginPage: React.FC = () => {
  const [rfidCode, setRfidCode] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleRFIDScan = async () => {
    if (!rfidCode.trim()) {
      setError('Please scan your RFID card');
      return;
    }

    setLoading(true);
    setError('');

    try {
      const response = await fetch('/api/rfid/login/', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ rfid_code: rfidCode }),
      });

      if (response.ok) {
        const data = await response.json();
        // Store token and redirect to kiosk dashboard
        localStorage.setItem('token', data.token);
        window.location.href = '/kiosk/dashboard';
      } else {
        const errorData = await response.json();
        setError(errorData.error || 'Invalid RFID card');
      }
    } catch (error) {
      setError('Connection error. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const handleManualLogin = () => {
    window.location.href = '/login';
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center p-4">
      <div className="max-w-md w-full bg-white rounded-lg shadow-2xl p-8">
        <div className="text-center mb-8">
          <div className="inline-flex items-center justify-center w-20 h-20 rounded-full bg-blue-100 mb-4">
            <svg className="w-10 h-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <h1 className="text-3xl font-bold text-gray-900">Kiosk Login</h1>
          <p className="text-gray-600 mt-2">Barangay Information System</p>
        </div>

        <div className="space-y-6">
          <div>
            <label htmlFor="rfid" className="block text-sm font-medium text-gray-700 mb-2">
              RFID Card Number
            </label>
            <input
              type="text"
              id="rfid"
              value={rfidCode}
              onChange={(e) => setRfidCode(e.target.value)}
              onKeyPress={(e) => e.key === 'Enter' && handleRFIDScan()}
              placeholder="Scan your RFID card"
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
              autoFocus
              disabled={loading}
            />
            <p className="text-sm text-gray-500 mt-2">
              Place your RFID card near the scanner
            </p>
          </div>

          {error && (
            <div className="bg-red-50 border border-red-200 rounded-lg p-4">
              <p className="text-sm text-red-800">{error}</p>
            </div>
          )}

          <button
            onClick={handleRFIDScan}
            disabled={loading}
            className="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
          >
            {loading ? 'Processing...' : 'Login with RFID'}
          </button>

          <div className="relative">
            <div className="absolute inset-0 flex items-center">
              <div className="w-full border-t border-gray-300"></div>
            </div>
            <div className="relative flex justify-center text-sm">
              <span className="px-2 bg-white text-gray-500">or</span>
            </div>
          </div>

          <button
            onClick={handleManualLogin}
            className="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
          >
            Manual Login
          </button>
        </div>

        <div className="mt-8 text-center">
          <p className="text-sm text-gray-500">
            Need help? Contact the barangay staff
          </p>
        </div>
      </div>
    </div>
  );
};

export default KioskLoginPage;
