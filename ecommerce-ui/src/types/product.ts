import { Business } from './auth'

export enum ProductStatus {
  PENDING = 'pending',
  APPROVED = 'approved',
  REJECTED = 'rejected',
  DELETED = 'deleted'
}

export interface Category {
  id: number
  name: string
  description?: string
  parent_id?: number
  created_at: string
  updated_at: string
}

export interface Product {
  id: number
  title: string
  description: string
  price: number
  quantity: number
  image_url?: string
  business_account_id: number
  category_id: number
  status: ProductStatus
  rejection_reason?: string
  business?: Business
  category?: Category
  created_at: string
  updated_at: string
}

export interface ProductFormData {
  title: string
  description: string
  price: number
  quantity: number
  image_url?: string
  category_id: number
}