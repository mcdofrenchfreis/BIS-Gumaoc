import React from 'react';

const AboutPage: React.FC = () => {
  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4">
      <div className="max-w-4xl mx-auto">
        <div className="bg-white rounded-lg shadow-lg p-8">
          <h1 className="text-4xl font-bold text-gray-900 mb-6">About Barangay Information System</h1>
          
          <div className="prose max-w-none">
            <p className="text-lg text-gray-700 mb-4">
              The Barangay Information System (BIS) for Gumaoc, CSJDM, Bulacan is a comprehensive web-based platform 
              designed to streamline barangay services and improve the delivery of government services to residents.
            </p>
            
            <h2 className="text-2xl font-semibold text-gray-900 mt-8 mb-4">Our Mission</h2>
            <p className="text-gray-700 mb-4">
              To provide efficient, transparent, and accessible barangay services through digital innovation, 
              ensuring that every resident receives timely assistance and support.
            </p>
            
            <h2 className="text-2xl font-semibold text-gray-900 mt-8 mb-4">Key Features</h2>
            <ul className="list-disc pl-6 text-gray-700 mb-4 space-y-2">
              <li>Online certificate requests and processing</li>
              <li>Business permit applications</li>
              <li>Blotter and incident reporting</li>
              <li>Queue management system for efficient service delivery</li>
              <li>RFID-based resident identification</li>
              <li>Real-time notifications and updates</li>
              <li>Comprehensive resident database management</li>
            </ul>
            
            <h2 className="text-2xl font-semibold text-gray-900 mt-8 mb-4">Services Offered</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
              <div className="bg-blue-50 p-4 rounded-lg">
                <h3 className="font-semibold text-blue-900 mb-2">Barangay Clearance</h3>
                <p className="text-sm text-blue-800">Official clearance for various purposes</p>
              </div>
              <div className="bg-green-50 p-4 rounded-lg">
                <h3 className="font-semibold text-green-900 mb-2">Certificate of Indigency</h3>
                <p className="text-sm text-green-800">For financial assistance programs</p>
              </div>
              <div className="bg-purple-50 p-4 rounded-lg">
                <h3 className="font-semibold text-purple-900 mb-2">Business Permits</h3>
                <p className="text-sm text-purple-800">Business registration and renewal</p>
              </div>
              <div className="bg-orange-50 p-4 rounded-lg">
                <h3 className="font-semibold text-orange-900 mb-2">Community Tax Certificate</h3>
                <p className="text-sm text-orange-800">CEDULA for tax purposes</p>
              </div>
            </div>
            
            <h2 className="text-2xl font-semibold text-gray-900 mt-8 mb-4">Contact Information</h2>
            <div className="bg-gray-50 p-6 rounded-lg">
              <p className="text-gray-700 mb-2">
                <strong>Barangay:</strong> Gumaoc East
              </p>
              <p className="text-gray-700 mb-2">
                <strong>Municipality:</strong> City of San Jose del Monte, Bulacan
              </p>
              <p className="text-gray-700 mb-2">
                <strong>Province:</strong> Bulacan
              </p>
              <p className="text-gray-700">
                <strong>Office Hours:</strong> Monday to Friday, 8:00 AM - 5:00 PM
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AboutPage;
