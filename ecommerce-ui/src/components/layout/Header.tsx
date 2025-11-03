import { Link } from 'react-router-dom'
import { useAuth } from '../../hooks/useAuth'
import { UserType } from '../../types/auth'

const Header = () => {
  const { isAuthenticated, userType, user, logout } = useAuth()

  const handleLogout = async () => {
    try {
      await logout()
    } catch (error) {
      console.error('Logout failed:', error)
    }
  }

  return (
    <header className="bg-white shadow-sm border-b">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          {/* Logo */}
          <div className="flex-shrink-0">
            <Link to="/" className="text-xl font-bold text-gray-900">
              E-Commerce
            </Link>
          </div>

          {/* Navigation */}
          <nav className="hidden md:flex space-x-8">
            <Link to="/" className="text-gray-700 hover:text-gray-900">
              Home
            </Link>
            <Link to="/products" className="text-gray-700 hover:text-gray-900">
              Products
            </Link>
          </nav>

          {/* User Menu */}
          <div className="flex items-center space-x-4">
            {isAuthenticated ? (
              <div className="flex items-center space-x-4">
                <span className="text-sm text-gray-700">
                  Welcome, {user?.name}
                </span>
                {userType === UserType.CUSTOMER && (
                  <Link
                    to="/dashboard"
                    className="text-gray-700 hover:text-gray-900"
                  >
                    Dashboard
                  </Link>
                )}
                {userType === UserType.BUSINESS && (
                  <Link
                    to="/business"
                    className="text-gray-700 hover:text-gray-900"
                  >
                    Business
                  </Link>
                )}
                {userType === UserType.ADMIN && (
                  <Link
                    to="/admin"
                    className="text-gray-700 hover:text-gray-900"
                  >
                    Admin
                  </Link>
                )}
                <button
                  onClick={handleLogout}
                  className="text-gray-700 hover:text-gray-900"
                >
                  Logout
                </button>
              </div>
            ) : (
              <div className="flex items-center space-x-4">
                <Link
                  to="/login"
                  className="text-gray-700 hover:text-gray-900"
                >
                  Login
                </Link>
                <Link
                  to="/register"
                  className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
                >
                  Register
                </Link>
              </div>
            )}
          </div>
        </div>
      </div>
    </header>
  )
}

export default Header