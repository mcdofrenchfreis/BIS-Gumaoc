import React from 'react';

const ServicesPage: React.FC = () => {
  const services = [
    {
      title: 'Barangay Clearance',
      description: 'Official clearance for employment, school enrollment, and other purposes',
      icon: '📋',
      link: '/certificates'
    },
    {
      title: 'Certificate of Indigency',
      description: 'For financial assistance programs and government benefits',
      icon: '📄',
      link: '/certificates'
    },
    {
      title: 'Proof of Residency',
      description: 'Document proving residence in the barangay',
      icon: '🏠',
      link: '/certificates'
    },
    {
      title: 'Business Permit',
      description: 'Business registration and renewal for local entrepreneurs',
      icon: '💼',
      link: '/business'
    },
    {
      title: 'Community Tax Certificate (CEDULA)',
      description: 'Tax certificate for various legal and business transactions',
      icon: '💳',
      link: '/certificates'
    },
    {
      title: 'Tricycle Permit',
      description: 'Permit for tricycle operators in the barangay',
      icon: '🛺',
      link: '/certificates'
    },
    {
      title: 'Blotter Report',
      description: 'Report incidents and complaints to the barangay',
      icon: '📝',
      link: '/blotter'
    },
    {
      title: 'Queue Management',
      description: 'Get a queue number for efficient service delivery',
      icon: '🎫',
      link: '/queue'
    }
  ];

  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4">
      <div className="max-w-7xl mx-auto">
        <div className="text-center mb-12">
          <h1 className="text-4xl font-bold text-gray-900 mb-4">Our Services</h1>
          <p className="text-xl text-gray-600 max-w-3xl mx-auto">
            Access various barangay services online. Choose from our comprehensive list of services below.
          </p>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {services.map((service, index) => (
            <a
              key={index}
              href={service.link}
              className="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300"
            >
              <div className="text-4xl mb-4">{service.icon}</div>
              <h3 className="text-xl font-semibold text-gray-900 mb-2">{service.title}</h3>
              <p className="text-gray-600 mb-4">{service.description}</p>
              <span className="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                Access Service
                <svg className="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                </svg>
              </span>
            </a>
          ))}
        </div>
        
        <div className="mt-12 bg-blue-50 rounded-lg p-8">
          <div className="max-w-3xl mx-auto text-center">
            <h2 className="text-2xl font-bold text-gray-900 mb-4">Need Help?</h2>
            <p className="text-gray-700 mb-6">
              If you have questions about any of our services or need assistance with your applications, 
              please don't hesitate to contact us or visit the barangay hall.
            </p>
            <div className="flex justify-center space-x-4">
              <a
                href="/contact"
                className="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
              >
                Contact Us
              </a>
              <a
                href="/queue"
                className="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Get Queue Number
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ServicesPage;
