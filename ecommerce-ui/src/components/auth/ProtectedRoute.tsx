import { ReactNode } from 'react'
import { Navigate } from 'react-router-dom'
import { UserType } from '../../types/auth'

interface ProtectedRouteProps {
  children: ReactNode
  isAuthenticated: boolean
  allowedUserTypes: UserType[]
  userType: UserType | null
}

const ProtectedRoute = ({ 
  children, 
  isAuthenticated, 
  allowedUserTypes, 
  userType 
}: ProtectedRouteProps) => {
  if (!isAuthenticated) {
    return <Navigate to="/login" replace />
  }

  if (userType && !allowedUserTypes.includes(userType)) {
    // Redirect to appropriate dashboard based on user type
    switch (userType) {
      case UserType.CUSTOMER:
        return <Navigate to="/dashboard" replace />
      case UserType.BUSINESS:
        return <Navigate to="/business" replace />
      case UserType.ADMIN:
        return <Navigate to="/admin" replace />
      default:
        return <Navigate to="/" replace />
    }
  }

  return <>{children}</>
}

export default ProtectedRoute