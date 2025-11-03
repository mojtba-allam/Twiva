import { useEffect } from 'react'
import { useAppDispatch, useAppSelector } from './redux'
import { 
  useGetNotificationsQuery,
  useMarkAsReadMutation,
  useMarkAllAsReadMutation,
  useDeleteNotificationMutation 
} from '../store/api/notificationApi'
import { 
  setNotifications,
  addNotification,
  markAsRead,
  markAllAsRead,
  removeNotification,
  toggleNotificationPanel,
  closeNotificationPanel 
} from '../store/slices/notificationSlice'

export const useNotifications = () => {
  const dispatch = useAppDispatch()
  const { notifications, unreadCount, isOpen } = useAppSelector((state) => state.notifications)
  
  const { data: notificationsData, isLoading } = useGetNotificationsQuery()
  const [markAsReadMutation] = useMarkAsReadMutation()
  const [markAllAsReadMutation] = useMarkAllAsReadMutation()
  const [deleteNotificationMutation] = useDeleteNotificationMutation()

  // Update local state when API data changes
  useEffect(() => {
    if (notificationsData) {
      dispatch(setNotifications(notificationsData))
    }
  }, [notificationsData, dispatch])

  const handleMarkAsRead = async (id: number) => {
    try {
      await markAsReadMutation(id).unwrap()
      dispatch(markAsRead(id))
    } catch (error) {
      console.error('Failed to mark notification as read:', error)
    }
  }

  const handleMarkAllAsRead = async () => {
    try {
      await markAllAsReadMutation().unwrap()
      dispatch(markAllAsRead())
    } catch (error) {
      console.error('Failed to mark all notifications as read:', error)
    }
  }

  const handleDeleteNotification = async (id: number) => {
    try {
      await deleteNotificationMutation(id).unwrap()
      dispatch(removeNotification(id))
    } catch (error) {
      console.error('Failed to delete notification:', error)
    }
  }

  const handleTogglePanel = () => {
    dispatch(toggleNotificationPanel())
  }

  const handleClosePanel = () => {
    dispatch(closeNotificationPanel())
  }

  const addNewNotification = (notification: any) => {
    dispatch(addNotification(notification))
  }

  return {
    // State
    notifications,
    unreadCount,
    isOpen,
    isLoading,
    
    // Actions
    markAsRead: handleMarkAsRead,
    markAllAsRead: handleMarkAllAsRead,
    deleteNotification: handleDeleteNotification,
    togglePanel: handleTogglePanel,
    closePanel: handleClosePanel,
    addNotification: addNewNotification,
  }
}