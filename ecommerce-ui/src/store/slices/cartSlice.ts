import { createSlice, PayloadAction } from '@reduxjs/toolkit'
import { CartItem } from '../../types/order'
import { Product } from '../../types/product'

interface CartState {
  items: CartItem[]
  total: number
  itemCount: number
}

// Calculate initial totals from localStorage
const savedItems = JSON.parse(localStorage.getItem('cart') || '[]')
const initialTotal = savedItems.reduce((total: number, item: CartItem) => total + (item.product.price * item.quantity), 0)
const initialItemCount = savedItems.reduce((count: number, item: CartItem) => count + item.quantity, 0)

const initialState: CartState = {
  items: savedItems,
  total: initialTotal,
  itemCount: initialItemCount,
}

const cartSlice = createSlice({
  name: 'cart',
  initialState,
  reducers: {
    addToCart: (state, action: PayloadAction<{ product: Product; quantity: number }>) => {
      const { product, quantity } = action.payload
      const existingItem = state.items.find(item => item.product.id === product.id)
      
      if (existingItem) {
        existingItem.quantity += quantity
      } else {
        state.items.push({ product, quantity })
      }
      
      cartSlice.caseReducers.calculateTotals(state)
      localStorage.setItem('cart', JSON.stringify(state.items))
    },
    removeFromCart: (state, action: PayloadAction<number>) => {
      state.items = state.items.filter(item => item.product.id !== action.payload)
      cartSlice.caseReducers.calculateTotals(state)
      localStorage.setItem('cart', JSON.stringify(state.items))
    },
    updateQuantity: (state, action: PayloadAction<{ productId: number; quantity: number }>) => {
      const { productId, quantity } = action.payload
      const item = state.items.find(item => item.product.id === productId)
      
      if (item) {
        item.quantity = quantity
        if (quantity <= 0) {
          state.items = state.items.filter(item => item.product.id !== productId)
        }
      }
      
      cartSlice.caseReducers.calculateTotals(state)
      localStorage.setItem('cart', JSON.stringify(state.items))
    },
    clearCart: (state) => {
      state.items = []
      state.total = 0
      state.itemCount = 0
      localStorage.removeItem('cart')
    },
    calculateTotals: (state) => {
      state.total = state.items.reduce((total, item) => total + (item.product.price * item.quantity), 0)
      state.itemCount = state.items.reduce((count, item) => count + item.quantity, 0)
    },
  },
})

export const { addToCart, removeFromCart, updateQuantity, clearCart, calculateTotals } = cartSlice.actions
export default cartSlice.reducer