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
import KioskLoginPage from './pages/KioskLoginPage'
import KioskDashboardPage from './pages/KioskDashboardPage'
import CheckBlotterPage from './pages/CheckBlotterPage'
import ProfilePage from './pages/ProfilePage'
import AboutPage from './pages/AboutPage'
import ContactPage from './pages/ContactPage'
import ServicesPage from './pages/ServicesPage'
import SettingsPage from './pages/SettingsPage'
import MyRequestsPage from './pages/MyRequestsPage'
import EServicesPage from './pages/EServicesPage'
import AdminBackupPage from './pages/AdminBackupPage'
import AdminReportsPage from './pages/AdminReportsPage'
import AdminQueueManagePage from './pages/AdminQueueManagePage'
import PrintBarangayClearancePage from './pages/PrintBarangayClearancePage'
import PrintCedulaPage from './pages/PrintCedulaPage'
import PrintIndigencyPage from './pages/PrintIndigencyPage'
import IndexPage from './pages/IndexPage'
import ForgotPasswordPage from './pages/ForgotPasswordPage'

const queryClient = new QueryClient()

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <Routes>
          <Route path="/" element={<LandingPage />} />
          <Route path="/index" element={<IndexPage />} />
          <Route path="/login" element={<LoginPage />} />
          <Route path="/forgot-password" element={<ForgotPasswordPage />} />
          <Route path="/register" element={<ResidentRegistrationPage />} />
          <Route path="/admin/login" element={<AdminLoginPage />} />
          <Route path="/admin" element={<AdminDashboardPage />} />
          <Route path="/admin/blotter" element={<AdminBlotterPage />} />
          <Route path="/admin/backup" element={<AdminBackupPage />} />
          <Route path="/admin/reports" element={<AdminReportsPage />} />
          <Route path="/admin/queue" element={<AdminQueueManagePage />} />
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
          <Route path="/kiosk/login" element={<KioskLoginPage />} />
          <Route path="/kiosk/dashboard" element={<KioskDashboardPage />} />
          <Route path="/kiosk" element={<KioskLoginPage />} />
          <Route path="/check-blotter" element={<CheckBlotterPage />} />
          <Route path="/profile" element={<ProfilePage />} />
          <Route path="/about" element={<AboutPage />} />
          <Route path="/contact" element={<ContactPage />} />
          <Route path="/services" element={<ServicesPage />} />
          <Route path="/settings" element={<SettingsPage />} />
          <Route path="/my-requests" element={<MyRequestsPage />} />
          <Route path="/e-services" element={<EServicesPage />} />
          <Route path="/print/barangay-clearance/:id" element={<PrintBarangayClearancePage />} />
          <Route path="/print/cedula/:id" element={<PrintCedulaPage />} />
          <Route path="/print/indigency/:id" element={<PrintIndigencyPage />} />
        </Routes>
      </BrowserRouter>
    </QueryClientProvider>
  )
}

export default App
