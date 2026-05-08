import { BrowserRouter, Routes, Route } from 'react-router-dom'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import LoginPage from './pages/LoginPage'
import DashboardPage from './pages/DashboardPage'
import AdminDashboardPage from './pages/AdminDashboardPage'
import ResidentsPage from './pages/ResidentsPage'
import CertificatesPage from './pages/CertificatesPage'
import BusinessPage from './pages/BusinessPage'
import BlotterPage from './pages/BlotterPage'
import QueuePage from './pages/QueuePage'
import RFIDPage from './pages/RFIDPage'
import LandingPage from './pages/LandingPage'
import AdminLoginPage from './pages/AdminLoginPage'
import CertificateRequestPage from './pages/CertificateRequestPage'
import BusinessApplicationPage from './pages/BusinessApplicationPage'
import AdminBlotterPage from './pages/AdminBlotterPage'
import QueueTicketPage from './pages/QueueTicketPage'
import RFIDRegistrationPage from './pages/RFIDRegistrationPage'
import ResidentRegistrationPage from './pages/ResidentRegistrationPage'
import NotificationsPage from './pages/NotificationsPage'

const queryClient = new QueryClient()

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <Routes>
          <Route path="/" element={<LandingPage />} />
          <Route path="/login" element={<LoginPage />} />
          <Route path="/admin/login" element={<AdminLoginPage />} />
          <Route path="/admin/blotter" element={<AdminBlotterPage />} />
          <Route path="/admin" element={<AdminDashboardPage />} />
          <Route path="/dashboard" element={<DashboardPage />} />
          <Route path="/resident-registration" element={<ResidentRegistrationPage />} />
          <Route path="/notifications" element={<NotificationsPage />} />
          <Route path="/residents" element={<ResidentsPage />} />
          <Route path="/certificate-request" element={<CertificateRequestPage />} />
          <Route path="/business-application" element={<BusinessApplicationPage />} />
          <Route path="/queue-ticket" element={<QueueTicketPage />} />
          <Route path="/rfid-registration" element={<RFIDRegistrationPage />} />
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
