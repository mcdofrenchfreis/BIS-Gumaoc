import React from 'react';

const EServicesPage: React.FC = () => {
  const services = [
    {
      title: 'Certificate Requests',
      description: 'Request various certificates online including barangay clearance, indigency, and residency certificates',
      icon: '📋',
      link: '/certificates',
      color: 'blue'
    },
    {
      title: 'Business Applications',
      description: 'Apply for business permits and renewals online',
      icon: '💼',
      link: '/business',
      color: 'green'
    },
    {
      title: 'Queue Management',
      description: 'Get a queue number and track your position in line',
      icon: '🎫',
      link: '/queue',
      color: 'purple'
    },
    {
      title: 'Blotter Reporting',
      description: 'Report incidents and complaints online',
      icon: '📝',
      link: '/blotter',
      color: 'red'
    },
    {
      title: 'My Requests',
      description: 'Track the status of all your submitted requests',
      icon: '📊',
      link: '/my-requests',
      color: 'orange'
    },
    {
      title: 'Profile Management',
      description: 'Update your personal information and preferences',
      icon: '👤',
      link: '/settings',
      color: 'teal'
    }
  ];

  const getColorClasses = (color: string) => {
    const colors = {
      blue: 'bg-blue-50 border-blue-200 hover:bg-blue-100',
      green: 'bg-green-50 border-green-200 hover:bg-green-100',
      purple: 'bg-purple-50 border-purple-200 hover:bg-purple-100',
      red: 'bg-red-50 border-red-200 hover:bg-red-100',
      orange: 'bg-orange-50 border-orange-200 hover:bg-orange-100',
      teal: 'bg-teal-50 border-teal-200 hover:bg-teal-100'
    };
    return colors[color as keyof typeof colors] || colors.blue;
  };

  return (
    <div className="min-h-screen bg-gray-50 py-8 px-4">
      <div className="max-w-7xl mx-auto">
        <div className="text-center mb-12">
          <h1 className="text-4xl font-bold text-gray-900 mb-4">E-Services</h1>
          <p className="text-xl text-gray-600 max-w-3xl mx-auto">
            Access all barangay services online from the comfort of your home. No need to visit the barangay hall for routine transactions.
          </p>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
          {services.map((service, index) => (
            <a
              key={index}
              href={service.link}
              className={`border-2 rounded-lg p-6 transition-all duration-300 ${getColorClasses(service.color)}`}
            >
              <div className="text-5xl mb-4">{service.icon}</div>
              <h3 className="text-xl font-semibold text-gray-900 mb-2">{service.title}</h3>
              <p className="text-gray-700 mb-4">{service.description}</p>
              <span className="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                Access Service
                <svg className="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                </svg>
              </span>
            </a>
          ))}
        </div>
        
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div className="bg-white rounded-lg shadow-lg p-6">
            <h2 className="text-2xl font-bold text-gray-900 mb-4">How It Works</h2>
            <div className="space-y-4">
              <div className="flex items-start">
                <div className="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                  <span className="text-blue-600 font-bold">1</span>
                </div>
                <div className="ml-4">
                  <h3 className="text-lg font-medium text-gray-900">Create Account</h3>
                  <p className="text-gray-600">Register or login to access all e-services</p>
                </div>
              </div>
              
              <div className="flex items-start">
                <div className="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                  <span className="text-blue-600 font-bold">2</span>
                </div>
                <div className="ml-4">
                  <h3 className="text-lg font-medium text-gray-900">Submit Request</h3>
                  <p className="text-gray-600">Fill out the required information for your desired service</p>
                </div>
              </div>
              
              <div className="flex items-start">
                <div className="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                  <span className="text-blue-600 font-bold">3</span>
                </div>
                <div className="ml-4">
                  <h3 className="text-lg font-medium text-gray-900">Get Queue Number</h3>
                  <p className="text-gray-600">Receive a queue number for efficient service delivery</p>
                </div>
              </div>
              
              <div className="flex items-start">
                <div className="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                  <span className="text-blue-600 font-bold">4</span>
                </div>
                <div className="ml-4">
                  <h3 className="text-lg font-medium text-gray-900">Receive Document</h3>
                  <p className="text-gray-600">Get notified when your document is ready for pickup</p>
                </div>
              </div>
            </div>
          </div>
          
          <div className="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <h2 className="text-2xl font-bold mb-4">Benefits of E-Services</h2>
            <ul className="space-y-3">
              <li className="flex items-start">
                <svg className="h-6 w-6 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                </svg>
                <span>Save time - no need to wait in long lines</span>
              </li>
              <li className="flex items-start">
                <svg className="h-6 w-6 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                </svg>
                <span>24/7 access - submit requests anytime</span>
              </li>
              <li className="flex items-start">
                <svg className="h-6 w-6 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                </svg>
                <span>Track status - monitor your requests in real-time</span>
              </li>
              <li className="flex items-start">
                <svg className="h-6 w-6 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                </svg>
                <span>Reduced paperwork - digital forms and records</span>
              </li>
              <li className="flex items-start">
                <svg className="h-6 w-6 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                </svg>
                <span>Secure - your data is protected</span>
              </li>
            </ul>
            
            <div className="mt-8">
              <a
                href="/certificates"
                className="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-blue-50"
              >
                Get Started Now
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default EServicesPage;
