import { createApi, fetchBaseQuery } from '@reduxjs/toolkit/query/react'
import { LoginCredentials, RegisterData, AuthResponse, UserType } from '../../types/auth'
import type { RootState } from '../index'

const baseQuery = fetchBaseQuery({
  baseUrl: '/api',
  prepareHeaders: (headers, { getState }) => {
    const token = (getState() as RootState).auth.token
    if (token) {
      headers.set('authorization', `Bearer ${token}`)
    }
    headers.set('accept', 'application/json')
    headers.set('content-type', 'application/json')
    return headers
  },
})

const baseQueryWithReauth = async (args: any, api: any, extraOptions: any) => {
  let result = await baseQuery(args, api, extraOptions)
  
  if (result.error && result.error.status === 401) {
    // Try to refresh token
    const refreshResult = await baseQuery('/refresh', api, extraOptions)
    
    if (refreshResult.data) {
      // Store the new token
      api.dispatch({ type: 'auth/tokenRefreshed', payload: refreshResult.data })
      // Retry the original query
      result = await baseQuery(args, api, extraOptions)
    } else {
      // Refresh failed, logout user
      api.dispatch({ type: 'auth/logout' })
    }
  }
  
  return result
}

export const authApi = createApi({
  reducerPath: 'authApi',
  baseQuery: baseQueryWithReauth,
  endpoints: (builder) => ({
    // Customer Auth
    loginCustomer: builder.mutation<AuthResponse, LoginCredentials>({
      query: (credentials) => ({
        url: '/login',
        method: 'POST',
        body: credentials,
      }),
      transformResponse: (response: any) => ({
        ...response,
        userType: UserType.CUSTOMER,
      }),
    }),
    registerCustomer: builder.mutation<AuthResponse, RegisterData>({
      query: (data) => ({
        url: '/register',
        method: 'POST',
        body: data,
      }),
      transformResponse: (response: any) => ({
        ...response,
        userType: UserType.CUSTOMER,
      }),
    }),
    
    // Business Auth
    loginBusiness: builder.mutation<AuthResponse, LoginCredentials>({
      query: (credentials) => ({
        url: '/business/login',
        method: 'POST',
        body: credentials,
      }),
      transformResponse: (response: any) => ({
        ...response,
        userType: UserType.BUSINESS,
      }),
    }),
    registerBusiness: builder.mutation<AuthResponse, RegisterData>({
      query: (data) => ({
        url: '/business/register',
        method: 'POST',
        body: data,
      }),
      transformResponse: (response: any) => ({
        ...response,
        userType: UserType.BUSINESS,
      }),
    }),
    
    // Admin Auth
    loginAdmin: builder.mutation<AuthResponse, LoginCredentials>({
      query: (credentials) => ({
        url: '/admins/login',
        method: 'POST',
        body: credentials,
      }),
      transformResponse: (response: any) => ({
        ...response,
        userType: UserType.ADMIN,
      }),
    }),
    
    // Logout
    logout: builder.mutation<void, void>({
      query: () => ({
        url: '/logout',
        method: 'POST',
      }),
    }),
  }),
})

export const {
  useLoginCustomerMutation,
  useRegisterCustomerMutation,
  useLoginBusinessMutation,
  useRegisterBusinessMutation,
  useLoginAdminMutation,
  useLogoutMutation,
} = authApi