import { Routes, Route } from 'react-router-dom'
import { useAppSelector } from './hooks/redux'
import Layout from './components/layout/Layout'
import HomePage from './pages/customer/HomePage'
import LoginPage from './pages/auth/LoginPage'
import RegisterPage from './pages/auth/RegisterPage'
import CustomerDashboard from './pages/customer/Dashboard'
import BusinessDashboard from './pages/business/Dashboard'
import AdminDashboard from './pages/admin/Dashboard'
import ProtectedRoute from './components/auth/ProtectedRoute'
import { UserType } from './types/auth'

function App() {
  const { isAuthenticated, userType } = useAppSelector((state) => state.auth)

  return (
    <Layout>
      <Routes>
        {/* Public Routes */}
        <Route path="/" element={<HomePage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        
        {/* Customer Routes */}
        <Route 
          path="/dashboard" 
          element={
            <ProtectedRoute 
              isAuthenticated={isAuthenticated} 
              allowedUserTypes={[UserType.CUSTOMER]}
              userType={userType}
            >
              <CustomerDashboard />
            </ProtectedRoute>
          } 
        />
        
        {/* Business Routes */}
        <Route 
          path="/business/*" 
          element={
            <ProtectedRoute 
              isAuthenticated={isAuthenticated} 
              allowedUserTypes={[UserType.BUSINESS]}
              userType={userType}
            >
              <BusinessDashboard />
            </ProtectedRoute>
          } 
        />
        
        {/* Admin Routes */}
        <Route 
          path="/admin/*" 
          element={
            <ProtectedRoute 
              isAuthenticated={isAuthenticated} 
              allowedUserTypes={[UserType.ADMIN]}
              userType={userType}
            >
              <AdminDashboard />
            </ProtectedRoute>
          } 
        />
      </Routes>
    </Layout>
  )
}

export default App