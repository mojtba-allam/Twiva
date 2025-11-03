import { createApi, fetchBaseQuery } from '@reduxjs/toolkit/query/react'
import { Product, ProductFormData, Category } from '../../types/product'
import type { RootState } from '../index'

export const productApi = createApi({
  reducerPath: 'productApi',
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
  tagTypes: ['Product', 'Category'],
  endpoints: (builder) => ({
    // Public product endpoints
    getProducts: builder.query<Product[], { category?: number; search?: string }>({
      query: ({ category, search } = {}) => {
        const params = new URLSearchParams()
        if (category) params.append('category', category.toString())
        if (search) params.append('search', search)
        return `/products/index?${params.toString()}`
      },
      providesTags: ['Product'],
    }),
    getProduct: builder.query<Product, number>({
      query: (id) => `/products/${id}`,
      providesTags: ['Product'],
    }),
    
    // Categories
    getCategories: builder.query<Category[], void>({
      query: () => '/categories',
      providesTags: ['Category'],
    }),
    getCategory: builder.query<Category, number>({
      query: (id) => `/categories/${id}`,
      providesTags: ['Category'],
    }),
    
    // Business product management
    getMyProducts: builder.query<Product[], void>({
      query: () => '/business/my-products',
      providesTags: ['Product'],
    }),
    createProduct: builder.mutation<Product, ProductFormData>({
      query: (data) => ({
        url: '/business/products/new',
        method: 'POST',
        body: data,
      }),
      invalidatesTags: ['Product'],
    }),
    updateProduct: builder.mutation<Product, { id: number; data: Partial<ProductFormData> }>({
      query: ({ id, data }) => ({
        url: `/products/${id}/edit`,
        method: 'PATCH',
        body: data,
      }),
      invalidatesTags: ['Product'],
    }),
    
    // Admin product management
    getPendingProducts: builder.query<Product[], void>({
      query: () => '/products/pending',
      providesTags: ['Product'],
    }),
    getRejectedProducts: builder.query<Product[], void>({
      query: () => '/products/rejected',
      providesTags: ['Product'],
    }),
    approveProduct: builder.mutation<void, number>({
      query: (id) => ({
        url: `/products/${id}/approve`,
        method: 'POST',
      }),
      invalidatesTags: ['Product'],
    }),
    rejectProduct: builder.mutation<void, { id: number; reason: string }>({
      query: ({ id, reason }) => ({
        url: `/products/${id}/reject`,
        method: 'POST',
        body: { reason },
      }),
      invalidatesTags: ['Product'],
    }),
    deleteProduct: builder.mutation<void, number>({
      query: (id) => ({
        url: `/products/${id}/delete`,
        method: 'DELETE',
      }),
      invalidatesTags: ['Product'],
    }),
    
    // Admin category management
    createCategory: builder.mutation<Category, { name: string; description?: string; parent_id?: number }>({
      query: (data) => ({
        url: '/categories/new',
        method: 'POST',
        body: data,
      }),
      invalidatesTags: ['Category'],
    }),
    updateCategory: builder.mutation<Category, { id: number; data: Partial<Category> }>({
      query: ({ id, data }) => ({
        url: `/categories/${id}/edit`,
        method: 'PATCH',
        body: data,
      }),
      invalidatesTags: ['Category'],
    }),
    deleteCategory: builder.mutation<void, number>({
      query: (id) => ({
        url: `/categories/${id}/delete`,
        method: 'DELETE',
      }),
      invalidatesTags: ['Category'],
    }),
  }),
})

export const {
  useGetProductsQuery,
  useGetProductQuery,
  useGetCategoriesQuery,
  useGetCategoryQuery,
  useGetMyProductsQuery,
  useCreateProductMutation,
  useUpdateProductMutation,
  useGetPendingProductsQuery,
  useGetRejectedProductsQuery,
  useApproveProductMutation,
  useRejectProductMutation,
  useDeleteProductMutation,
  useCreateCategoryMutation,
  useUpdateCategoryMutation,
  useDeleteCategoryMutation,
} = productApi