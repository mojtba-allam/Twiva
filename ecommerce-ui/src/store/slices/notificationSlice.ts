import { createSlice, PayloadAction } from '@reduxjs/toolkit'

export interface Notification {
  id: number
  type: string
  data: any
  read_at?: string
  created_at: string
}

interface NotificationState {
  notifications: Notification[]
  unreadCount: number
  isOpen: boolean
}

const initialState: NotificationState = {
  notifications: [],
  unreadCount: 0,
  isOpen: false,
}

const notificationSlice = createSlice({
  name: 'notifications',
  initialState,
  reducers: {
    setNotifications: (state, action: PayloadAction<Notification[]>) => {
      state.notifications = action.payload
      state.unreadCount = action.payload.filter(n => !n.read_at).length
    },
    addNotification: (state, action: PayloadAction<Notification>) => {
      state.notifications.unshift(action.payload)
      if (!action.payload.read_at) {
        state.unreadCount += 1
      }
    },
    markAsRead: (state, action: PayloadAction<number>) => {
      const notification = state.notifications.find(n => n.id === action.payload)
      if (notification && !notification.read_at) {
        notification.read_at = new Date().toISOString()
        state.unreadCount -= 1
      }
    },
    markAllAsRead: (state) => {
      state.notifications.forEach(notification => {
        if (!notification.read_at) {
          notification.read_at = new Date().toISOString()
        }
      })
      state.unreadCount = 0
    },
    removeNotification: (state, action: PayloadAction<number>) => {
      const index = state.notifications.findIndex(n => n.id === action.payload)
      if (index !== -1) {
        const notification = state.notifications[index]
        if (!notification.read_at) {
          state.unreadCount -= 1
        }
        state.notifications.splice(index, 1)
      }
    },
    toggleNotificationPanel: (state) => {
      state.isOpen = !state.isOpen
    },
    closeNotificationPanel: (state) => {
      state.isOpen = false
    },
  },
})

export const {
  setNotifications,
  addNotification,
  markAsRead,
  markAllAsRead,
  removeNotification,
  toggleNotificationPanel,
  closeNotificationPanel,
} = notificationSlice.actions

export default notificationSlice.reducer