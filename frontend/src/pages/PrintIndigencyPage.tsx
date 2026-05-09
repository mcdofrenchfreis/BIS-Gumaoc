import React, { useEffect, useState } from 'react';

const PrintIndigencyPage: React.FC = () => {
  const [certificateData, setCertificateData] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const certificateId = urlParams.get('id');
    
    if (certificateId) {
      fetchCertificateData(certificateId);
    } else {
      setLoading(false);
    }
  }, []);

  const fetchCertificateData = async (id: string) => {
    try {
      const response = await fetch(`/api/certificates/requests/${id}/print-indigency/`);
      if (response.ok) {
        setCertificateData({
          full_name: 'JUAN DELA CRUZ',
          address: '123 Main Street, Barangay Gumaoc East, CSJDM, Bulacan',
          age: 30,
          civilStatus: 'Single',
          purpose: 'Financial Assistance',
          issuedDate: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }),
          certificateNumber: 'IND-2024-001'
        });
      }
    } catch (error) {
      console.error('Error fetching certificate:', error);
    } finally {
      setLoading(false);
    }
  };

  const handlePrint = () => {
    window.print();
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p className="mt-2 text-gray-600">Loading certificate...</p>
        </div>
      </div>
    );
  }

  if (!certificateData) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <p className="text-gray-600">Certificate not found</p>
          <a href="/certificates" className="mt-4 inline-block text-blue-600 hover:underline">
            Back to Certificates
          </a>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-100 py-8">
      <div className="max-w-3xl mx-auto">
        <div className="bg-white p-8 shadow-lg print:shadow-none">
          {/* Header */}
          <div className="text-center mb-8 border-b-2 border-gray-300 pb-4">
            <h1 className="text-2xl font-bold text-gray-900">Republic of the Philippines</h1>
            <h2 className="text-xl font-semibold text-gray-800">Province of Bulacan</h2>
            <h3 className="text-lg font-medium text-gray-700">City of San Jose del Monte</h3>
            <h4 className="text-xl font-bold text-gray-900 mt-2">BARANGAY GUMAOC EAST</h4>
            <p className="text-sm text-gray-600 mt-1">Office of the Barangay Captain</p>
          </div>

          {/* Certificate Title */}
          <div className="text-center my-8">
            <h2 className="text-3xl font-bold text-gray-900 uppercase tracking-wide">
              Certificate of Indigency
            </h2>
          </div>

          {/* Certificate Content */}
          <div className="text-justify leading-relaxed">
            <p className="text-gray-800 mb-4">
              TO WHOM IT MAY CONCERN:
            </p>
            <p className="text-gray-800 mb-4">
              This is to certify that <strong>{certificateData.full_name}</strong>, 
              {certificateData.age && <span> {certificateData.age} years old,</span>}
              {certificateData.civilStatus && <span> {certificateData.civilStatus},</span>}
              is a bona fide resident of Barangay Gumaoc East, City of San Jose del Monte, Bulacan, with residence at:
            </p>
            <p className="text-gray-800 mb-4 font-medium">
              {certificateData.address}
            </p>
            <p className="text-gray-800 mb-4">
              Based on our records and personal knowledge, the above-named person is of indigent status and has no sufficient means of livelihood to support himself/herself and his/her family.
            </p>
            <p className="text-gray-800 mb-4">
              This certification is being issued upon the request of the interested party for the purpose of:
            </p>
            <p className="text-gray-800 mb-4 font-medium">
              {certificateData.purpose}
            </p>
            <p className="text-gray-800 mb-4">
              Issued this <strong>{certificateData.issuedDate}</strong> at Barangay Gumaoc East, City of San Jose del Monte, Bulacan.
            </p>
          </div>

          {/* Signature Section */}
          <div className="mt-12 flex justify-end">
            <div className="text-center">
              <p className="text-gray-800 mb-8">HON. BARANGAY CAPTAIN</p>
              <p className="text-gray-600 text-sm border-b border-gray-400 pb-1">_________________________</p>
              <p className="text-gray-600 text-sm mt-1">Signature over Printed Name</p>
            </div>
          </div>

          {/* Footer */}
          <div className="mt-12 pt-4 border-t border-gray-300 text-center">
            <p className="text-sm text-gray-600">
              Control No: {certificateData.certificateNumber}
            </p>
          </div>
        </div>

        {/* Print Button (hidden when printing) */}
        <div className="mt-6 flex justify-center space-x-4 print:hidden">
          <button
            onClick={handlePrint}
            className="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
          >
            Print Certificate
          </button>
          <button
            onClick={() => window.history.back()}
            className="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
          >
            Back
          </button>
        </div>
      </div>
    </div>
  );
};

export default PrintIndigencyPage;
