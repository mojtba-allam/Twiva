import { Fragment } from 'react'
import { Transition } from '@headlessui/react'
import { XMarkIcon, BellIcon } from '@heroicons/react/24/outline'
import { useNotifications } from '../../hooks/useNotifications'
import { formatDistanceToNow } from 'date-fns'

const NotificationPanel = () => {
  const { 
    notifications, 
    unreadCount, 
    isOpen, 
    isLoading,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    closePanel 
  } = useNotifications()

  const handleNotificationClick = (notification: any) => {
    if (!notification.read_at) {
      markAsRead(notification.id)
    }
  }

  return (
    <Transition
      show={isOpen}
      as={Fragment}
      enter="transition ease-out duration-200"
      enterFrom="opacity-0 translate-y-1"
      enterTo="opacity-100 translate-y-0"
      leave="transition ease-in duration-150"
      leaveFrom="opacity-100 translate-y-0"
      leaveTo="opacity-0 translate-y-1"
    >
      <div className="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
        {/* Header */}
        <div className="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
          <h3 className="text-lg font-semibold text-gray-900">Notifications</h3>
          <div className="flex items-center space-x-2">
            {unreadCount > 0 && (
              <button
                onClick={markAllAsRead}
                className="text-sm text-primary-600 hover:text-primary-800"
              >
                Mark all read
              </button>
            )}
            <button
              onClick={closePanel}
              className="text-gray-400 hover:text-gray-600"
            >
              <XMarkIcon className="h-5 w-5" />
            </button>
          </div>
        </div>

        {/* Content */}
        <div className="max-h-96 overflow-y-auto">
          {isLoading ? (
            <div className="px-4 py-8 text-center">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto"></div>
              <p className="text-sm text-gray-500 mt-2">Loading notifications...</p>
            </div>
          ) : notifications.length > 0 ? (
            <div className="divide-y divide-gray-200">
              {notifications.map((notification) => (
                <div
                  key={notification.id}
                  className={`px-4 py-3 hover:bg-gray-50 cursor-pointer ${
                    !notification.read_at ? 'bg-blue-50' : ''
                  }`}
                  onClick={() => handleNotificationClick(notification)}
                >
                  <div className="flex items-start space-x-3">
                    <div className="flex-shrink-0">
                      <div className={`w-2 h-2 rounded-full mt-2 ${
                        !notification.read_at ? 'bg-primary-600' : 'bg-gray-300'
                      }`} />
                    </div>
                    
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center justify-between">
                        <p className={`text-sm ${
                          !notification.read_at ? 'font-semibold text-gray-900' : 'text-gray-700'
                        }`}>
                          {notification.data?.title || 'Notification'}
                        </p>
                        <button
                          onClick={(e) => {
                            e.stopPropagation()
                            deleteNotification(notification.id)
                          }}
                          className="text-gray-400 hover:text-red-600"
                        >
                          <XMarkIcon className="h-4 w-4" />
                        </button>
                      </div>
                      
                      <p className="text-sm text-gray-600 mt-1">
                        {notification.data?.message || 'No message'}
                      </p>
                      
                      <p className="text-xs text-gray-400 mt-1">
                        {formatDistanceToNow(new Date(notification.created_at), { addSuffix: true })}
                      </p>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="px-4 py-8 text-center">
              <BellIcon className="h-12 w-12 text-gray-400 mx-auto mb-4" />
              <p className="text-sm text-gray-500">No notifications yet</p>
            </div>
          )}
        </div>
      </div>
    </Transition>
  )
}

export default NotificationPanelimport { Fragment } from 'react'
import { Transition } from '@headlessui/react'
import { XMarkIcon, BellIcon } from '@heroicons/react/24/outline'
import { useNotifications } from '../../hooks/useNotifications'
import { formatDistanceToNow } from 'date-fns'

const NotificationPanel = () => {
  const { 
    notifications, 
    unreadCount, 
    isOpen, 
    markAsRead, 
    markAllAsRead, 
    deleteNotification, 
    closePanel 
  } = useNotifications()

  const getNotificationIcon = (type: string) => {
    switch (type) {
      case 'order':
        return '📦'
      case 'product':
        return '🛍️'
      case 'approval':
        return '✅'
      case 'rejection':
        return '❌'
      default:
        return '🔔'
    }
  }

  const getNotificationColor = (type: string) => {
    switch (type) {
      case 'order':
        return 'bg-blue-100 text-blue-800'
      case 'product':
        return 'bg-green-100 text-green-800'
      case 'approval':
        return 'bg-green-100 text-green-800'
      case 'rejection':
        return 'bg-red-100 text-red-800'
      default:
        return 'bg-gray-100 text-gray-800'
    }
  }

  return (
    <Transition
      show={isOpen}
      as={Fragment}
      enter="transition ease-out duration-200"
      enterFrom="opacity-0 translate-y-1"
      enterTo="opacity-100 translate-y-0"
      leave="transition ease-in duration-150"
      leaveFrom="opacity-100 translate-y-0"
      leaveTo="opacity-0 translate-y-1"
    >
      <div className="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
        {/* Header */}
        <div className="flex items-center justify-between p-4 border-b border-gray-200">
          <h3 className="text-lg font-semibold text-gray-900">
            Notifications
            {unreadCount > 0 && (
              <span className="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                {unreadCount}
              </span>
            )}
          </h3>
          <div className="flex items-center space-x-2">
            {unreadCount > 0 && (
              <button
                onClick={markAllAsRead}
                className="text-sm text-primary-600 hover:text-primary-800"
              >
                Mark all read
              </button>
            )}
            <button
              onClick={closePanel}
              className="text-gray-400 hover:text-gray-600"
            >
              <XMarkIcon className="h-5 w-5" />
            </button>
          </div>
        </div>

        {/* Notifications List */}
        <div className="max-h-96 overflow-y-auto">
          {notifications.length > 0 ? (
            <div className="divide-y divide-gray-200">
              {notifications.map((notification) => (
                <div
                  key={notification.id}
                  className={`p-4 hover:bg-gray-50 cursor-pointer ${
                    !notification.read_at ? 'bg-blue-50' : ''
                  }`}
                  onClick={() => !notification.read_at && markAsRead(notification.id)}
                >
                  <div className="flex items-start space-x-3">
                    <div className="flex-shrink-0">
                      <span className="text-2xl">
                        {getNotificationIcon(notification.type)}
                      </span>
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center justify-between">
                        <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getNotificationColor(notification.type)}`}>
                          {notification.type}
                        </span>
                        <button
                          onClick={(e) => {
                            e.stopPropagation()
                            deleteNotification(notification.id)
                          }}
                          className="text-gray-400 hover:text-red-600"
                        >
                          <XMarkIcon className="h-4 w-4" />
                        </button>
                      </div>
                      <p className="mt-1 text-sm text-gray-900">
                        {notification.data?.message || 'New notification'}
                      </p>
                      <p className="mt-1 text-xs text-gray-500">
                        {formatDistanceToNow(new Date(notification.created_at), { addSuffix: true })}
                      </p>
                      {!notification.read_at && (
                        <div className="mt-2">
                          <div className="w-2 h-2 bg-primary-600 rounded-full"></div>
                        </div>
                      )}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="p-8 text-center">
              <BellIcon className="h-12 w-12 text-gray-400 mx-auto mb-4" />
              <p className="text-gray-500">No notifications yet</p>
            </div>
          )}
        </div>
      </div>
    </Transition>
  )
}

export default NotificationPanel