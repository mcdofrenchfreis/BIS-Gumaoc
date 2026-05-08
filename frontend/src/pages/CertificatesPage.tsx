import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '../lib/axios'

export default function CertificatesPage() {
  const [showForm, setShowForm] = useState(false)
  const queryClient = useQueryClient()

  const { data: certificates, isLoading } = useQuery({
    queryKey: ['certificates'],
    queryFn: () => api.get('/api/certificates/requests/').then(res => res.data)
  })

  const createMutation = useMutation({
    mutationFn: (data: any) => api.post('/api/certificates/requests/', data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['certificates'] })
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
          <h1 className="text-xl font-bold text-gray-800">Certificate Requests</h1>
          <button
            onClick={() => setShowForm(!showForm)}
            className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
          >
            {showForm ? 'Cancel' : 'Request Certificate'}
          </button>
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-4 py-8">
        {showForm && (
          <div className="bg-white rounded-xl shadow p-6 mb-6">
            <h2 className="text-lg font-semibold mb-4">Request Certificate</h2>
            <form onSubmit={handleSubmit} className="space-y-4">
              <select name="certificate_type" className="w-full border p-2 rounded" required>
                <option value="">Select Certificate Type</option>
                <option value="BRGY. CLEARANCE">Barangay Clearance</option>
                <option value="BRGY. INDIGENCY">Barangay Indigency</option>
                <option value="RESIDENCY">Certificate of Residency</option>
                <option value="CEDULA">Community Tax Certificate</option>
                <option value="TRICYCLE PERMIT">Tricycle Permit</option>
              </select>
              <input name="applicant_name" placeholder="Full Name" className="w-full border p-2 rounded" required />
              <input name="applicant_address" placeholder="Address" className="w-full border p-2 rounded" required />
              <textarea name="purpose" placeholder="Purpose" className="w-full border p-2 rounded" rows={3} required />
              <button type="submit" className="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded">
                Submit Request
              </button>
            </form>
          </div>
        )}

        <div className="grid md:grid-cols-3 gap-6">
          {isLoading ? (
            <p>Loading...</p>
          ) : certificates?.map((cert: any) => (
            <div key={cert.id} className="bg-white rounded-xl shadow p-6">
              <h3 className="font-semibold text-gray-800">{cert.certificate_type}</h3>
              <p className="text-sm text-gray-600 mt-2">{cert.applicant_name}</p>
              <div className="mt-4 flex justify-between items-center">
                <span className={`px-2 py-1 rounded-full text-xs ${
                  cert.status === 'approved' ? 'bg-green-100 text-green-800' :
                  cert.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                  'bg-red-100 text-red-800'
                }`}>
                  {cert.status}
                </span>
                {cert.status === 'approved' && (
                  <button className="text-blue-600 hover:text-blue-800 text-sm">Print</button>
                )}
              </div>
            </div>
          ))}
        </div>
      </main>
    </div>
  )
}
