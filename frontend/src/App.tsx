import { BrowserRouter, Routes, Route } from 'react-router-dom'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import IndexPage from './pages/IndexPage'
import LoginPage from './pages/LoginPage'
import DashboardPage from './pages/DashboardPage'
import AdminDashboardPage from './pages/AdminDashboardPage'
import ResidentsPage from './pages/ResidentsPage'
import CertificatesPage from './pages/CertificatesPage'
import BusinessPage from './pages/BusinessPage'
import BlotterPage from './pages/BlotterPage'
import QueuePage from './pages/QueuePage'
import RFIDPage from './pages/RFIDPage'

const queryClient = new QueryClient()

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <Routes>
          <Route path="/" element={<IndexPage />} />
          <Route path="/login" element={<LoginPage />} />
          <Route path="/dashboard" element={<DashboardPage />} />
          <Route path="/admin" element={<AdminDashboardPage />} />
          <Route path="/residents" element={<ResidentsPage />} />
          <Route path="/certificates" element={<CertificatesPage />} />
          <Route path="/business" element={<BusinessPage />} />
          <Route path="/blotter" element={<BlotterPage />} />
          <Route path="/queue" element={<QueuePage />} />
          <Route path="/rfid" element={<RFIDPage />} />
        </Routes>
      </BrowserRouter>
    </QueryClientProvider>
  )
}

export default App
