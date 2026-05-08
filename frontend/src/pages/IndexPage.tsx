export default function IndexPage() {
  return (
    <div className="min-h-screen bg-gradient-to-br from-green-900 to-green-700 flex items-center justify-center p-4">
      <div className="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl max-w-4xl w-full overflow-hidden">
        {/* Header */}
        <div className="bg-gradient-to-r from-green-800 to-green-600 text-white p-6">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-2xl font-bold">Barangay Gumaoc East</h1>
              <p className="text-green-100">Barangay Information System</p>
            </div>
            <div className="w-16 h-16 bg-green-100 text-green-700 rounded-xl flex items-center justify-center text-3xl">
              🏛️
            </div>
          </div>
        </div>

        {/* Portal Cards */}
        <div className="p-8">
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <a href="/login" className="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition transform hover:-translate-y-1 block">
              <div className="text-5xl mb-4">👥</div>
              <h3 className="text-xl font-semibold text-gray-800 mb-2">Resident Portal</h3>
              <p className="text-gray-600">Access resident services and manage your profile</p>
            </a>

            <a href="/admin" className="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition transform hover:-translate-y-1 block">
              <div className="text-5xl mb-4">🏢</div>
              <h3 className="text-xl font-semibold text-gray-800 mb-2">Admin Portal</h3>
              <p className="text-gray-600">Administrative dashboard for barangay management</p>
            </a>

            <a href="/queue" className="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition transform hover:-translate-y-1 block">
              <div className="text-5xl mb-4">🎫</div>
              <h3 className="text-xl font-semibold text-gray-800 mb-2">Kiosk Portal</h3>
              <p className="text-gray-600">Self-service kiosk for quick transactions</p>
            </a>
          </div>

          {/* Quick Actions */}
          <div className="flex justify-center gap-3">
            <button className="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold rounded-xl transition">
              View Services
            </button>
            <button className="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition">
              Request Document
            </button>
            <button className="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl transition">
              Report Emergency
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}
