import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '../lib/axios'

export default function QueuePage() {
  const queryClient = useQueryClient()

  const { data: tickets, isLoading } = useQuery({
    queryKey: ['queue-tickets'],
    queryFn: () => api.get('/api/queue/tickets/').then(res => res.data)
  })

  const { data: services } = useQuery({
    queryKey: ['queue-services'],
    queryFn: () => api.get('/api/queue/services/').then(res => res.data)
  })

  const createMutation = useMutation({
    mutationFn: (data: any) => api.post('/api/queue/tickets/', data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['queue-tickets'] })
    }
  })

  const getTicket = (serviceId: number) => {
    const service = services?.find((s: any) => s.id === serviceId)
    createMutation.mutate({ 
      service: serviceId, 
      name: 'Walk-in Customer',
      ticket_number: `${service?.prefix}-${Math.floor(Math.random() * 1000)}` 
    })
  }

  return (
    <div className="min-h-screen bg-gray-100">
      <header className="bg-white shadow">
        <div className="max-w-7xl mx-auto px-4 py-4">
          <h1 className="text-xl font-bold text-gray-800">Queue Management</h1>
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-4 py-8">
        <div className="grid md:grid-cols-4 gap-6 mb-8">
          {services?.map((service: any) => (
            <button
              key={service.id}
              onClick={() => getTicket(service.id)}
              className="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-center"
            >
              <div className="text-4xl mb-2">🎫</div>
              <h3 className="font-semibold text-gray-800">{service.name}</h3>
              <p className="text-sm text-gray-600 mt-1">{service.description}</p>
            </button>
          ))}
        </div>

        <div className="bg-white rounded-xl shadow p-6">
          <h2 className="text-lg font-semibold mb-4">Current Queue</h2>
          <div className="grid md:grid-cols-3 gap-4">
            {isLoading ? (
              <p>Loading...</p>
            ) : tickets?.filter((t: any) => t.status === 'waiting').slice(0, 9).map((ticket: any) => (
              <div key={ticket.id} className="border rounded-lg p-4 bg-yellow-50">
                <div className="text-2xl font-bold text-yellow-600">{ticket.ticket_number}</div>
                <div className="text-sm text-gray-600 mt-1">{ticket.name}</div>
                <div className="text-xs text-gray-500">{ticket.service?.name}</div>
              </div>
            ))}
          </div>
        </div>
      </main>
    </div>
  )
}
