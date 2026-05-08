import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '../lib/axios'

export default function BusinessPage() {
  const [showForm, setShowForm] = useState(false)
  const queryClient = useQueryClient()

  const { data: applications, isLoading } = useQuery({
    queryKey: ['business'],
    queryFn: () => api.get('/api/business/applications/').then(res => res.data)
  })

  const createMutation = useMutation({
    mutationFn: (data: any) => api.post('/api/business/applications/', data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['business'] })
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
          <h1 className="text-xl font-bold text-gray-800">Business Applications</h1>
          <button
            onClick={() => setShowForm(!showForm)}
            className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
          >
            {showForm ? 'Cancel' : 'Apply for Permit'}
          </button>
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-4 py-8">
        {showForm && (
          <div className="bg-white rounded-xl shadow p-6 mb-6">
            <h2 className="text-lg font-semibold mb-4">Business Permit Application</h2>
            <form onSubmit={handleSubmit} className="space-y-4">
              <input name="business_name" placeholder="Business Name" className="w-full border p-2 rounded" required />
              <input name="business_type" placeholder="Business Type" className="w-full border p-2 rounded" required />
              <input name="business_address" placeholder="Business Address" className="w-full border p-2 rounded" required />
              <input name="owner_name" placeholder="Owner Name" className="w-full border p-2 rounded" required />
              <input name="owner_address" placeholder="Owner Address" className="w-full border p-2 rounded" required />
              <input name="contact_number" placeholder="Contact Number" className="w-full border p-2 rounded" required />
              <input name="capital_amount" type="number" placeholder="Capital Amount" className="w-full border p-2 rounded" required />
              <input name="years_operation" type="number" placeholder="Years in Operation" className="w-full border p-2 rounded" required />
              <button type="submit" className="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded">
                Submit Application
              </button>
            </form>
          </div>
        )}

        <div className="bg-white rounded-xl shadow overflow-hidden">
          <table className="w-full">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Business Name</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Owner</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {isLoading ? (
                <tr><td colSpan={5} className="px-6 py-4 text-center">Loading...</td></tr>
              ) : applications?.map((app: any) => (
                <tr key={app.id}>
                  <td className="px-6 py-4">{app.business_name}</td>
                  <td className="px-6 py-4">{app.business_type}</td>
                  <td className="px-6 py-4">{app.owner_name}</td>
                  <td className="px-6 py-4">
                    <span className={`px-2 py-1 rounded-full text-xs ${
                      app.status === 'approved' ? 'bg-green-100 text-green-800' :
                      app.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                      'bg-red-100 text-red-800'
                    }`}>
                      {app.status}
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
