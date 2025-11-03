import { createApi, fetchBaseQuery } from '@reduxjs/toolkit/query/react'
import { Order } from '../../types/order'
import type { RootState } from '../index'

export const orderApi = createApi({
  reducerPath: 'orderApi',
  baseQuery: fetchBaseQuery({
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
  }),
  tagTypes: ['Order'],
  endpoints: (builder) => ({
    // Customer orders
    getMyOrders: builder.query<Order[], void>({
      query: () => '/orders',
      providesTags: ['Order'],
    }),
    getOrder: builder.query<Order, number>({
      query: (id) => `/orders/${id}`,
      providesTags: ['Order'],
    }),
    createOrder: builder.mutation<Order, { products_list: string; total_quantity: number; total_price: number }>({
      query: (data) => ({
        url: '/orders/new',
        method: 'POST',
        body: data,
      }),
      invalidatesTags: ['Order'],
    }),
    updateOrder: builder.mutation<Order, { id: number; data: Partial<Order> }>({
      query: ({ id, data }) => ({
        url: `/orders/${id}/edit`,
        method: 'PUT',
        body: data,
      }),
      invalidatesTags: ['Order'],
    }),
    deleteOrder: builder.mutation<void, number>({
      query: (id) => ({
        url: `/orders/${id}/delete`,
        method: 'DELETE',
      }),
      invalidatesTags: ['Order'],
    }),
    
    // Admin order management
    getAllOrders: builder.query<Order[], void>({
      query: () => '/orders',
      providesTags: ['Order'],
    }),
    updateOrderStatus: builder.mutation<Order, { id: number; status: string }>({
      query: ({ id, status }) => ({
        url: `/orders/${id}/status`,
        method: 'PUT',
        body: { status },
      }),
      invalidatesTags: ['Order'],
    }),
  }),
})

export const {
  useGetMyOrdersQuery,
  useGetOrderQuery,
  useCreateOrderMutation,
  useUpdateOrderMutation,
  useDeleteOrderMutation,
  useGetAllOrdersQuery,
  useUpdateOrderStatusMutation,
} = orderApi