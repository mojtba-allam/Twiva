import { User } from './auth'
import { Product } from './product'

export interface Order {
  id: number
  user_id: number
  products_list: string // JSON string of products
  total_quantity: number
  total_price: number
  status: string
  deleted_products?: string
  user?: User
  created_at: string
  updated_at: string
}

export interface OrderItem {
  product_id: number
  quantity: number
  price: number
  product?: Product
}

export interface CartItem {
  product: Product
  quantity: number
}