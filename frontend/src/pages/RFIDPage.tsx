import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '../lib/axios'

export default function RFIDPage() {
  const [rfidTag, setRfidTag] = useState('')
  const queryClient = useQueryClient()

  const { data: logs, isLoading } = useQuery({
    queryKey: ['rfid-logs'],
    queryFn: () => api.get('/api/rfid/logs/').then(res => res.data)
  })

  const loginMutation = useMutation({
    mutationFn: (tag: string) => api.post('/api/rfid/login/', { rfid_tag: tag }),
    onSuccess: (data) => {
      alert(`Welcome ${data.data.full_name}!`)
      setRfidTag('')
      queryClient.invalidateQueries({ queryKey: ['rfid-logs'] })
    },
    onError: () => {
      alert('RFID tag not found or inactive')
    }
  })

  const handleScan = () => {
    if (rfidTag) {
      loginMutation.mutate(rfidTag)
    }
  }

  return (
    <div className="min-h-screen bg-gray-100">
      <header className="bg-white shadow">
        <div className="max-w-7xl mx-auto px-4 py-4">
          <h1 className="text-xl font-bold text-gray-800">RFID Access Control</h1>
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-4 py-8">
        <div className="bg-white rounded-xl shadow p-6 mb-6">
          <h2 className="text-lg font-semibold mb-4">Scan RFID Tag</h2>
          <div className="flex gap-4">
            <input
              type="text"
              value={rfidTag}
              onChange={(e) => setRfidTag(e.target.value)}
              placeholder="Enter or scan RFID tag"
              className="flex-1 border p-3 rounded-lg text-2xl tracking-wider font-mono"
            />
            <button
              onClick={handleScan}
              className="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold"
            >
              Scan
            </button>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow p-6">
          <h2 className="text-lg font-semibold mb-4">Recent Access Logs</h2>
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">RFID Tag</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Access Time</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {isLoading ? (
                  <tr><td colSpan={3} className="px-4 py-4 text-center">Loading...</td></tr>
                ) : logs?.slice(0, 20).map((log: any) => (
                  <tr key={log.id}>
                    <td className="px-4 py-3 font-mono">{log.rfid_tag}</td>
                    <td className="px-4 py-3">{log.full_name}</td>
                    <td className="px-4 py-3">{new Date(log.access_time).toLocaleString()}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  )
}
