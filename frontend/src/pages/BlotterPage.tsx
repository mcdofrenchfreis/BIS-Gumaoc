import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '../lib/axios'

export default function BlotterPage() {
  const [showForm, setShowForm] = useState(false)
  const queryClient = useQueryClient()

  const { data: blotters, isLoading } = useQuery({
    queryKey: ['blotters'],
    queryFn: () => api.get('/api/blotter/').then(res => res.data)
  })

  const createMutation = useMutation({
    mutationFn: (data: any) => api.post('/api/blotter/', data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['blotters'] })
      setShowForm(false)
    }
  })

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    const formData = new FormData(e.currentTarget)
    const data = Object.fromEntries(formData)
    createMutation.mutate(data)
  }

  return (
    <div className="min-h-screen bg-gray-100">
      <header className="bg-white shadow">
        <div className="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
          <h1 className="text-xl font-bold text-gray-800">Barangay Blotter</h1>
          <button
            onClick={() => setShowForm(!showForm)}
            className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
          >
            {showForm ? 'Cancel' : 'File Report'}
          </button>
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-4 py-8">
        {showForm && (
          <div className="bg-white rounded-xl shadow p-6 mb-6">
            <h2 className="text-lg font-semibold mb-4">File Incident Report</h2>
            <form onSubmit={handleSubmit} className="space-y-4">
              <select name="incident_type" className="w-full border p-2 rounded" required>
                <option value="">Select Incident Type</option>
                <option value="complaint">Complaint</option>
                <option value="incident">Incident</option>
                <option value="dispute">Dispute</option>
                <option value="violation">Violation</option>
                <option value="other">Other</option>
              </select>
              <input name="complainant_name" placeholder="Complainant Name" className="w-full border p-2 rounded" required />
              <input name="complainant_address" placeholder="Complainant Address" className="w-full border p-2 rounded" required />
              <input name="respondent_name" placeholder="Respondent Name" className="w-full border p-2 rounded" required />
              <input name="respondent_address" placeholder="Respondent Address" className="w-full border p-2 rounded" required />
              <input name="location" placeholder="Location" className="w-full border p-2 rounded" required />
              <input name="incident_date" type="datetime-local" className="w-full border p-2 rounded" required />
              <textarea name="description" placeholder="Description" className="w-full border p-2 rounded" rows={4} required />
              <button type="submit" className="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded">
                Submit Report
              </button>
            </form>
          </div>
        )}

        <div className="bg-white rounded-xl shadow overflow-hidden">
          <table className="w-full">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Blotter #</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Complainant</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Incident Date</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {isLoading ? (
                <tr><td colSpan={6} className="px-6 py-4 text-center">Loading...</td></tr>
              ) : blotters?.map((blotter: any) => (
                <tr key={blotter.id}>
                  <td className="px-6 py-4">{blotter.blotter_number}</td>
                  <td className="px-6 py-4">{blotter.incident_type}</td>
                  <td className="px-6 py-4">{blotter.complainant_name}</td>
                  <td className="px-6 py-4">{new Date(blotter.incident_date).toLocaleDateString()}</td>
                  <td className="px-6 py-4">
                    <span className={`px-2 py-1 rounded-full text-xs ${
                      blotter.status === 'resolved' ? 'bg-green-100 text-green-800' :
                      blotter.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                      'bg-red-100 text-red-800'
                    }`}>
                      {blotter.status}
                    </span>
                  </td>
                  <td className="px-6 py-4">
                    <button className="text-blue-600 hover:text-blue-800 mr-2">View</button>
                    <button className="text-gray-600 hover:text-gray-800">Edit</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </main>
    </div>
  )
}
