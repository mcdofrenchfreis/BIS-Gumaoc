export default function DashboardPage() {
  const user = JSON.parse(localStorage.getItem('user') || '{}')

  return (
    <div className="min-h-screen bg-gray-100">
      {/* Header */}
      <header className="bg-white shadow">
        <div className="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
          <h1 className="text-xl font-bold text-gray-800">Barangay Gumaoc East</h1>
          <div className="flex items-center gap-4">
            <span className="text-gray-600">{user.first_name} {user.last_name}</span>
            <button
              onClick={() => {
                localStorage.clear()
                window.location.href = '/'
              }}
              className="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg"
            >
              Logout
            </button>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-4 py-8">
        <div className="mb-8">
          <h2 className="text-2xl font-bold text-gray-800">Welcome back!</h2>
          <p className="text-gray-600">Here's an overview of your barangay services</p>
        </div>

        <div className="grid md:grid-cols-4 gap-6">
          <div className="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
            <div className="text-3xl mb-2">👥</div>
            <h3 className="font-semibold text-gray-800">Residents</h3>
            <p className="text-2xl font-bold text-blue-600 mt-2">1,234</p>
          </div>

          <div className="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
            <div className="text-3xl mb-2">📄</div>
            <h3 className="font-semibold text-gray-800">Certificates</h3>
            <p className="text-2xl font-bold text-green-600 mt-2">567</p>
          </div>

          <div className="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
            <div className="text-3xl mb-2">🏢</div>
            <h3 className="font-semibold text-gray-800">Business</h3>
            <p className="text-2xl font-bold text-orange-600 mt-2">89</p>
          </div>

          <div className="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
            <div className="text-3xl mb-2">🎫</div>
            <h3 className="font-semibold text-gray-800">Queue</h3>
            <p className="text-2xl font-bold text-purple-600 mt-2">12</p>
          </div>
        </div>

        <div className="grid md:grid-cols-2 gap-6">
          <div className="bg-white p-6 rounded-xl shadow">
            <h3 className="font-semibold text-gray-800 mb-4">Quick Actions</h3>
            <div className="space-y-2">
              <a href="/residents" className="block text-left px-4 py-2 hover:bg-gray-100 rounded-lg">
                Manage Residents
              </a>
              <a href="/certificates" className="block text-left px-4 py-2 hover:bg-gray-100 rounded-lg">
                Request Certificate
              </a>
              <a href="/blotter" className="block text-left px-4 py-2 hover:bg-gray-100 rounded-lg">
                File Blotter Report
              </a>
              <a href="/business" className="block text-left px-4 py-2 hover:bg-gray-100 rounded-lg">
                Apply for Business Permit
              </a>
              <a href="/queue" className="block text-left px-4 py-2 hover:bg-gray-100 rounded-lg">
                Get Queue Number
              </a>
              <a href="/rfid" className="block text-left px-4 py-2 hover:bg-gray-100 rounded-lg">
                RFID Access
              </a>
            </div>
          </div>

          <div className="bg-white p-6 rounded-xl shadow">
            <h3 className="font-semibold text-gray-800 mb-4">Recent Notifications</h3>
            <div className="space-y-3">
              <div className="flex items-start gap-3 p-3 bg-blue-50 rounded-lg">
                <span className="text-xl">ℹ️</span>
                <div>
                  <p className="font-medium text-gray-800">New Service Available</p>
                  <p className="text-sm text-gray-600">Online certificate requests now available</p>
                </div>
              </div>
              <div className="flex items-start gap-3 p-3 bg-green-50 rounded-lg">
                <span className="text-xl">✅</span>
                <div>
                  <p className="font-medium text-gray-800">Application Approved</p>
                  <p className="text-sm text-gray-600">Your business permit has been approved</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  )
}
