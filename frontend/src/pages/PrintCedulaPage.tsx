import React, { useEffect, useState } from 'react';

const PrintCedulaPage: React.FC = () => {
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
      const response = await fetch(`/api/certificates/requests/${id}/print-cedula/`);
      if (response.ok) {
        setCertificateData({
          full_name: 'JUAN DELA CRUZ',
          address: '123 Main Street, Barangay Gumaoc East, CSJDM, Bulacan',
          age: 30,
          height: '5\'8"',
          weight: '65 kg',
          civil_status: 'Single',
          citizenship: 'Filipino',
          occupation: 'Employee',
          place_of_issue: 'Barangay Gumaoc East, CSJDM, Bulacan',
          issued_date: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }),
          basic_tax: 500.00,
          additional_tax: 0.00,
          total_amount: 500.00,
          ctc_number: '2024-001'
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
      <div className="max-w-4xl mx-auto">
        <div className="bg-white p-8 shadow-lg print:shadow-none">
          {/* Header */}
          <div className="text-center mb-6 border-b-2 border-gray-300 pb-4">
            <h1 className="text-xl font-bold text-gray-900">Republic of the Philippines</h1>
            <h2 className="text-lg font-semibold text-gray-800">Province of Bulacan</h2>
            <h3 className="text-base font-medium text-gray-700">City of San Jose del Monte</h3>
            <h4 className="text-lg font-bold text-gray-900 mt-2">BARANGAY GUMAOC EAST</h4>
          </div>

          {/* CTC Form */}
          <div className="border-2 border-gray-400 p-6">
            <div className="text-center mb-4">
              <h2 className="text-2xl font-bold text-gray-900 uppercase">Community Tax Certificate</h2>
              <p className="text-sm text-gray-600">(Cedula)</p>
            </div>

            {/* Personal Information */}
            <div className="grid grid-cols-2 gap-4 mb-4">
              <div>
                <label className="block text-sm font-medium text-gray-700">Full Name:</label>
                <p className="text-lg font-semibold text-gray-900">{certificateData.full_name}</p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700">Address:</label>
                <p className="text-lg font-semibold text-gray-900">{certificateData.address}</p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700">Age:</label>
                <p className="text-lg font-semibold text-gray-900">{certificateData.age}</p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700">Civil Status:</label>
                <p className="text-lg font-semibold text-gray-900">{certificateData.civil_status}</p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700">Height:</label>
                <p className="text-lg font-semibold text-gray-900">{certificateData.height}</p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700">Weight:</label>
                <p className="text-lg font-semibold text-gray-900">{certificateData.weight}</p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700">Citizenship:</label>
                <p className="text-lg font-semibold text-gray-900">{certificateData.citizenship}</p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700">Occupation:</label>
                <p className="text-lg font-semibold text-gray-900">{certificateData.occupation}</p>
              </div>
            </div>

            {/* Tax Information */}
            <div className="mt-6 border-t border-gray-300 pt-4">
              <h3 className="text-lg font-bold text-gray-900 mb-4">Tax Information</h3>
              <div className="space-y-2">
                <div className="flex justify-between">
                  <span className="text-gray-700">Basic Community Tax:</span>
                  <span className="font-semibold">₱{certificateData.basic_tax.toFixed(2)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-700">Additional Tax:</span>
                  <span className="font-semibold">₱{certificateData.additional_tax.toFixed(2)}</span>
                </div>
                <div className="flex justify-between border-t border-gray-300 pt-2 mt-2">
                  <span className="text-gray-900 font-bold">Total Amount Paid:</span>
                  <span className="font-bold text-xl">₱{certificateData.total_amount.toFixed(2)}</span>
                </div>
              </div>
            </div>

            {/* Issue Details */}
            <div className="mt-6 grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700">Place of Issue:</label>
                <p className="text-lg font-semibold text-gray-900">{certificateData.place_of_issue}</p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700">Date Issued:</label>
                <p className="text-lg font-semibold text-gray-900">{certificateData.issued_date}</p>
              </div>
            </div>
          </div>

          {/* Signature Section */}
          <div className="mt-8 flex justify-end">
            <div className="text-center">
              <p className="text-gray-800 mb-8">BARANGAY CAPTAIN</p>
              <p className="text-gray-600 text-sm border-b border-gray-400 pb-1">_________________________</p>
              <p className="text-gray-600 text-sm mt-1">Signature over Printed Name</p>
            </div>
          </div>

          {/* Footer */}
          <div className="mt-6 pt-4 border-t border-gray-300 text-center">
            <p className="text-sm text-gray-600">
              CTC No: {certificateData.ctc_number}
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

export default PrintCedulaPage;
