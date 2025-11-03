import { createSlice, PayloadAction } from '@reduxjs/toolkit'
import { AuthState, AuthResponse, UserType } from '../../types/auth'

const initialState: AuthState = {
  isAuthenticated: false,
  userType: null,
  user: null,
  token: localStorage.getItem('token'),
  loading: false,
  error: null,
}

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    loginStart: (state) => {
      state.loading = true
      state.error = null
    },
    loginSuccess: (state, action: PayloadAction<AuthResponse>) => {
      state.loading = false
      state.isAuthenticated = true
      state.user = action.payload.user
      state.userType = action.payload.userType
      state.token = action.payload.token
      localStorage.setItem('token', action.payload.token)
    },
    loginFailure: (state, action: PayloadAction<string>) => {
      state.loading = false
      state.error = action.payload
    },
    logout: (state) => {
      state.isAuthenticated = false
      state.user = null
      state.userType = null
      state.token = null
      localStorage.removeItem('token')
    },
    tokenRefreshed: (state, action: PayloadAction<{ token: string }>) => {
      state.token = action.payload.token
      localStorage.setItem('token', action.payload.token)
    },
    clearError: (state) => {
      state.error = null
    },
    initializeAuth: (state) => {
      const token = localStorage.getItem('token')
      if (token) {
        state.token = token
        // You might want to validate the token here
        state.isAuthenticated = true
      }
    },
  },
})

export const { loginStart, loginSuccess, loginFailure, logout, tokenRefreshed, clearError, initializeAuth } = authSlice.actions
export default authSlice.reducer