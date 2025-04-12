<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\BusinessAccount;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Models\Order;

class AdminProductController extends Controller
{
    public function pendingProducts(Request $request)
    {
        // Get the authenticated user
        $user = Auth::guard('sanctum')->user();

        // Check if user is admin
        $isAdmin = $user && $user instanceof \App\Models\Admin;

        // Check if user is a business account
        $isBusiness = $user && $user instanceof \App\Models\BusinessAccount;

        // If user is neither admin nor business, return 404
        if (!$isAdmin && !$isBusiness) {
            throw new NotFoundHttpException();
        }

        // If admin, show all pending products
        if ($isAdmin) {
            $products = Products::pending()
                ->with(['businessAccount', 'category'])
                ->paginate(10);

            return response()->json([
                'message' => 'Pending products retrieved successfully',
                'data' => ProductResource::collection($products)
            ]);
        }

        // If business account, show only their pending products
        if ($isBusiness) {
            $products = Products::pending()
                ->where('business_account_id', $user->id)
                ->with(['category'])
                ->paginate(10);

            // Check if this business has any pending products
            if ($products->isEmpty()) {
                return response()->json([
                    'message' => 'You have no pending products',
                ]);
            }

            return response()->json([
                'data' => ProductResource::collection($products)
            ]);
        }
    }

    public function approveProduct(Request $request, $id)
    {
        // Only admin can approve products
        if (!Auth::guard('admin')->check()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $product = Products::findOrFail($id);

        if ($product->status === Products::STATUS_APPROVED) {
            return response()->json([
                'message' => 'Product is already approved'
            ], 400);
        }

        $product->status = Products::STATUS_APPROVED;
        $product->rejection_reason = null;
        $product->save();

        return response()->json([
            'message' => 'Product approved successfully',
        ]);
    }

    public function rejectProduct(Request $request, $id)
    {
        // Only admin can reject products
        if (!Auth::guard('admin')->check()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $product = Products::findOrFail($id);

        if ($product->status === Products::STATUS_REJECTED) {
            return response()->json([
                'message' => 'Product is already rejected'
            ], 400);
        }

        $product->status = Products::STATUS_REJECTED;
        $product->rejection_reason = $request->rejection_reason;
        $product->save();

        return response()->json([
            'message' => 'Product rejected successfully',
            'product' => new ProductResource($product)
        ]);
    }

    public function deleteProduct(Request $request, $id)
    {
        // Check if admin is authenticated
        if (!Auth::guard('admin')->check()) {
            return response()->json([
                'message' => 'Unauthorized. Only admins can delete products.',
                'status' => 403
            ], 403);
        }

        // Find the product
        $product = Products::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found.',
                'status' => 404
            ], 404);
        }

        // Begin transaction
        DB::beginTransaction();

        try {
            // Find all orders containing this product
            $orders = Order::whereRaw("JSON_CONTAINS(products_list, JSON_OBJECT('product_id', ?))", [$id])->get();

            foreach ($orders as $order) {
                // Decode the products list
                $products_list = json_decode($order->products_list, true);

                // Remove the product from the list
                $products_list = array_filter($products_list, function($item) use ($id) {
                    return $item['product_id'] != $id;
                });

                // Recalculate total quantity and price
                $total_quantity = 0;
                $total_price = 0;

                foreach ($products_list as $item) {
                    $product = Products::find($item['product_id']);
                    if ($product) {
                        $total_quantity += $item['quantity'];
                        $total_price += $product->price * $item['quantity'];
                    }
                }

                // If no products left, delete the order
                if (empty($products_list)) {
                    $order->delete();
                } else {
                    // Update the order with new calculations
                    $order->update([
                        'products_list' => json_encode(array_values($products_list)),
                        'total_quantity' => $total_quantity,
                        'total_price' => $total_price
                    ]);
                }
            }

            // Delete the product
            $product->delete();

            // Commit transaction
            DB::commit();

            return response()->json([
                'message' => 'Product deleted successfully. Affected orders have been updated.',
                'affected_orders_count' => $orders->count(),
                'status' => 200
            ], 200);

        } catch (\Exception $e) {
            // Rollback transaction in case of error
            DB::rollback();

            return response()->json([
                'message' => 'Failed to delete product. Please try again.',
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }
}
