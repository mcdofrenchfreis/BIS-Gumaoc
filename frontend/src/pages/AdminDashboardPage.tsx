import { useQuery } from '@tanstack/react-query'
import api from '../lib/axios'

export default function AdminDashboardPage() {
  const { data: stats } = useQuery({
    queryKey: ['admin-stats'],
    queryFn: async () => {
      const [residents, certificates, business, blotter, queue] = await Promise.all([
        api.get('/api/residents/').then(res => res.data),
        api.get('/api/certificates/requests/').then(res => res.data),
        api.get('/api/business/applications/').then(res => res.data),
        api.get('/api/blotter/').then(res => res.data),
        api.get('/api/queue/tickets/').then(res => res.data),
      ])
      return {
        totalResidents: residents?.length || 0,
        pendingCertificates: certificates?.filter((c: any) => c.status === 'pending').length || 0,
        pendingBusiness: business?.filter((b: any) => b.status === 'pending').length || 0,
        openBlotters: blotter?.filter((b: any) => b.status !== 'resolved').length || 0,
        activeQueue: queue?.filter((q: any) => q.status === 'waiting').length || 0,
      }
    }
  })

  return (
    <div className="min-h-screen bg-gray-100">
      <header className="bg-white shadow">
        <div className="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
          <h1 className="text-xl font-bold text-gray-800">Admin Dashboard</h1>
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
      </header>

      <main className="max-w-7xl mx-auto px-4 py-8">
        <div className="mb-8">
          <h2 className="text-2xl font-bold text-gray-800">Overview</h2>
          <p className="text-gray-600">Barangay statistics and pending actions</p>
        </div>

        <div className="grid md:grid-cols-5 gap-6 mb-8">
          <div className="bg-white p-6 rounded-xl shadow">
            <div className="text-3xl mb-2">👥</div>
            <p className="text-sm text-gray-600">Total Residents</p>
            <p className="text-3xl font-bold text-blue-600">{stats?.totalResidents || 0}</p>
          </div>

          <div className="bg-white p-6 rounded-xl shadow">
            <div className="text-3xl mb-2">📄</div>
            <p className="text-sm text-gray-600">Pending Certificates</p>
            <p className="text-3xl font-bold text-yellow-600">{stats?.pendingCertificates || 0}</p>
          </div>

          <div className="bg-white p-6 rounded-xl shadow">
            <div className="text-3xl mb-2">🏢</div>
            <p className="text-sm text-gray-600">Pending Business</p>
            <p className="text-3xl font-bold text-orange-600">{stats?.pendingBusiness || 0}</p>
          </div>

          <div className="bg-white p-6 rounded-xl shadow">
            <div className="text-3xl mb-2">📋</div>
            <p className="text-sm text-gray-600">Open Blotters</p>
            <p className="text-3xl font-bold text-red-600">{stats?.openBlotters || 0}</p>
          </div>

          <div className="bg-white p-6 rounded-xl shadow">
            <div className="text-3xl mb-2">🎫</div>
            <p className="text-sm text-gray-600">Active Queue</p>
            <p className="text-3xl font-bold text-purple-600">{stats?.activeQueue || 0}</p>
          </div>
        </div>

        <div className="grid md:grid-cols-2 gap-6 mb-8">
          <div className="bg-white p-6 rounded-xl shadow">
            <h3 className="font-semibold text-gray-800 mb-4">Quick Actions</h3>
            <div className="grid grid-cols-2 gap-3">
              <a href="/residents" className="bg-blue-50 hover:bg-blue-100 p-4 rounded-lg text-center">
                <div className="text-2xl mb-1">👥</div>
                <p className="text-sm font-medium">Manage Residents</p>
              </a>
              <a href="/certificates" className="bg-green-50 hover:bg-green-100 p-4 rounded-lg text-center">
                <div className="text-2xl mb-1">📄</div>
                <p className="text-sm font-medium">Certificates</p>
              </a>
              <a href="/business" className="bg-orange-50 hover:bg-orange-100 p-4 rounded-lg text-center">
                <div className="text-2xl mb-1">🏢</div>
                <p className="text-sm font-medium">Business Permits</p>
              </a>
              <a href="/blotter" className="bg-red-50 hover:bg-red-100 p-4 rounded-lg text-center">
                <div className="text-2xl mb-1">📋</div>
                <p className="text-sm font-medium">Blotter</p>
              </a>
            </div>
          </div>

          <div className="bg-white p-6 rounded-xl shadow">
            <h3 className="font-semibold text-gray-800 mb-4">Recent Activity</h3>
            <div className="space-y-3">
              <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <span className="text-xl">📝</span>
                <div className="flex-1">
                  <p className="text-sm font-medium">New resident registration</p>
                  <p className="text-xs text-gray-500">2 minutes ago</p>
                </div>
              </div>
              <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <span className="text-xl">📄</span>
                <div className="flex-1">
                  <p className="text-sm font-medium">Certificate request submitted</p>
                  <p className="text-xs text-gray-500">15 minutes ago</p>
                </div>
              </div>
              <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <span className="text-xl">🏢</span>
                <div className="flex-1">
                  <p className="text-sm font-medium">Business permit approved</p>
                  <p className="text-xs text-gray-500">1 hour ago</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div className="bg-white p-6 rounded-xl shadow">
          <h3 className="font-semibold text-gray-800 mb-4">Reports</h3>
          <div className="grid md:grid-cols-4 gap-4">
            <button className="bg-gray-100 hover:bg-gray-200 p-4 rounded-lg text-left">
              <div className="text-2xl mb-2">📊</div>
              <p className="font-medium">Census Report</p>
              <p className="text-xs text-gray-500">Generate census statistics</p>
            </button>
            <button className="bg-gray-100 hover:bg-gray-200 p-4 rounded-lg text-left">
              <div className="text-2xl mb-2">💰</div>
              <p className="font-medium">Revenue Report</p>
              <p className="text-xs text-gray-500">Business permit revenue</p>
            </button>
            <button className="bg-gray-100 hover:bg-gray-200 p-4 rounded-lg text-left">
              <div className="text-2xl mb-2">📈</div>
              <p className="font-medium">Activity Report</p>
              <p className="text-xs text-gray-500">System activity summary</p>
            </button>
            <button className="bg-gray-100 hover:bg-gray-200 p-4 rounded-lg text-left">
              <div className="text-2xl mb-2">⚠️</div>
              <p className="font-medium">Incident Report</p>
              <p className="text-xs text-gray-500">Blotter statistics</p>
            </button>
          </div>
        </div>
      </main>
    </div>
  )
}
