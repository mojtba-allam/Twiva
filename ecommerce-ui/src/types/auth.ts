export enum UserType {
  CUSTOMER = 'customer',
  BUSINESS = 'business',
  ADMIN = 'admin'
}

export interface User {
  id: number
  name: string
  email: string
  image?: string
  bio?: string
  created_at: string
  updated_at: string
}

export interface Business {
  id: number
  name: string
  email: string
  profile_picture?: string
  bio?: string
  created_at: string
  updated_at: string
}

export interface Admin {
  id: number
  name: string
  email: string
  created_at: string
  updated_at: string
}

export interface AuthState {
  isAuthenticated: boolean
  userType: UserType | null
  user: User | Business | Admin | null
  token: string | null
  loading: boolean
  error: string | null
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
  bio?: string
}

export interface AuthResponse {
  user: User | Business | Admin
  token: string
  userType: UserType
}