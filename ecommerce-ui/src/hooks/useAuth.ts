import { useEffect } from 'react'
import { useAppDispatch, useAppSelector } from './redux'
import { 
  useLoginCustomerMutation, 
  useLoginBusinessMutation, 
  useLoginAdminMutation,
  useLogoutMutation 
} from '../store/api/authApi'
import { loginStart, loginSuccess, loginFailure, logout, initializeAuth } from '../store/slices/authSlice'
import { LoginCredentials, UserType } from '../types/auth'

export const useAuth = () => {
  const dispatch = useAppDispatch()
  const { isAuthenticated, user, userType, loading, error } = useAppSelector((state) => state.auth)
  
  const [loginCustomer] = useLoginCustomerMutation()
  const [loginBusiness] = useLoginBusinessMutation()
  const [loginAdmin] = useLoginAdminMutation()
  const [logoutMutation] = useLogoutMutation()

  // Initialize auth state on app load
  useEffect(() => {
    dispatch(initializeAuth())
  }, [dispatch])

  const login = async (credentials: LoginCredentials, type: UserType) => {
    try {
      dispatch(loginStart())
      
      let result
      switch (type) {
        case UserType.CUSTOMER:
          result = await loginCustomer(credentials).unwrap()
          break
        case UserType.BUSINESS:
          result = await loginBusiness(credentials).unwrap()
          break
        case UserType.ADMIN:
          result = await loginAdmin(credentials).unwrap()
          break
        default:
          throw new Error('Invalid user type')
      }
      
      dispatch(loginSuccess(result))
      return result
    } catch (error: any) {
      const errorMessage = error.data?.message || 'Login failed. Please try again.'
      dispatch(loginFailure(errorMessage))
      throw error
    }
  }

  const handleLogout = async () => {
    try {
      await logoutMutation().unwrap()
    } catch (error) {
      // Even if logout fails on server, clear local state
      console.error('Logout error:', error)
    } finally {
      dispatch(logout())
    }
  }

  const isCustomer = userType === UserType.CUSTOMER
  const isBusiness = userType === UserType.BUSINESS
  const isAdmin = userType === UserType.ADMIN

  return {
    // State
    isAuthenticated,
    user,
    userType,
    loading,
    error,
    
    // User type checks
    isCustomer,
    isBusiness,
    isAdmin,
    
    // Actions
    login,
    logout: handleLogout,
  }
}