import { configureStore } from '@reduxjs/toolkit'
import { authApi } from './api/authApi'
import { productApi } from './api/productApi'
import { orderApi } from './api/orderApi'
import { notificationApi } from './api/notificationApi'
import authReducer from './slices/authSlice'
import cartReducer from './slices/cartSlice'
import notificationReducer from './slices/notificationSlice'

export const store = configureStore({
  reducer: {
    auth: authReducer,
    cart: cartReducer,
    notifications: notificationReducer,
    [authApi.reducerPath]: authApi.reducer,
    [productApi.reducerPath]: productApi.reducer,
    [orderApi.reducerPath]: orderApi.reducer,
    [notificationApi.reducerPath]: notificationApi.reducer,
  },
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: {
        ignoredActions: ['persist/PERSIST', 'persist/REHYDRATE'],
      },
    }).concat(
      authApi.middleware,
      productApi.middleware,
      orderApi.middleware,
      notificationApi.middleware
    ),
})

export type RootState = ReturnType<typeof store.getState>
export type AppDispatch = typeof store.dispatch