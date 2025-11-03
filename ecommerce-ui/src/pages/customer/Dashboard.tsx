import { useAuth } from '../../hooks/useAuth'

const CustomerDashboard = () => {
  const { user } = useAuth()

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900">
          Welcome back, {user?.name}!
        </h1>
        <p className="text-gray-600">Manage your orders and profile</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">
            Recent Orders
          </h2>
          <p className="text-gray-600">
            Order management features coming soon
          </p>
        </div>

        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">
            Profile
          </h2>
          <p className="text-gray-600">
            Profile management features coming soon
          </p>
        </div>

        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">
            Notifications
          </h2>
          <p className="text-gray-600">
            Notification center coming soon
          </p>
        </div>
      </div>
    </div>
  )
}

export default CustomerDashboard