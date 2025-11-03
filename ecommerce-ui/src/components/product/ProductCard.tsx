import { Link } from 'react-router-dom'
import { useAppDispatch } from '../../hooks/redux'
import { addToCart } from '../../store/slices/cartSlice'
import { Product } from '../../types/product'
import { ShoppingCartIcon } from '@heroicons/react/24/outline'

interface ProductCardProps {
  product: Product
}

const ProductCard = ({ product }: ProductCardProps) => {
  const dispatch = useAppDispatch()

  const handleAddToCart = (e: React.MouseEvent) => {
    e.preventDefault()
    dispatch(addToCart({ product, quantity: 1 }))
  }

  return (
    <Link to={`/products/${product.id}`} className="group">
      <div className="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
        {/* Product Image */}
        <div className="aspect-square bg-gray-200 overflow-hidden">
          {product.image_url ? (
            <img
              src={product.image_url}
              alt={product.title}
              className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center text-gray-400">
              <svg className="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          )}
        </div>

        {/* Product Info */}
        <div className="p-4">
          <h3 className="font-semibold text-gray-900 mb-1 line-clamp-2">
            {product.title}
          </h3>
          
          {product.business && (
            <p className="text-sm text-gray-500 mb-2">
              by {product.business.name}
            </p>
          )}

          <div className="flex items-center justify-between">
            <span className="text-lg font-bold text-primary-600">
              ${product.price.toFixed(2)}
            </span>
            
            <button
              onClick={handleAddToCart}
              className="p-2 text-gray-600 hover:text-primary-600 hover:bg-primary-50 rounded-full transition-colors"
              title="Add to cart"
            >
              <ShoppingCartIcon className="w-5 h-5" />
            </button>
          </div>

          {/* Stock indicator */}
          <div className="mt-2">
            {product.quantity > 0 ? (
              <span className="text-xs text-green-600">
                {product.quantity} in stock
              </span>
            ) : (
              <span className="text-xs text-red-600">
                Out of stock
              </span>
            )}
          </div>
        </div>
      </div>
    </Link>
  )
}

export default ProductCard