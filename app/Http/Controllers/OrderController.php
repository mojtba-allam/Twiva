<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Products;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check admin authentication first
        if (Auth::guard('admin')->check()) {
            return OrderResource::collection(Order::with(['user'])->paginate(10));
        }

        // Then check user authentication
        $user = Auth::guard('user')->user();
        if (!$user) {
            throw new NotFoundHttpException();
        }

        $orders = Order::where('user_id', $user->id)->with(['user'])->get();
        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No orders found for this user'], 404);
        }

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check user authentication
        if (!Auth::guard('user')->check()) {
            return response()->json([
                'message' => 'Unauthorized. Please login to create orders.',
                'status' => 401
            ], 401);
        }

        $user = Auth::guard('user')->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'status' => 404
            ], 404);
        }

        $request->validate([
            'products_list' => 'required|array',
            'products_list.*.product_id' => 'required|exists:products,id',
            'products_list.*.quantity' => 'required|integer|min:1',
            'status' => 'required|string'
        ]);

        // Begin transaction
        DB::beginTransaction();

        try {
            // Get the products list from request
            $products_list = $request->products_list;

            // Fetch all products and check quantities
            $products = Products::whereIn('id', collect($products_list)->pluck('product_id'))
                ->where('status', 'approved')  // Only allow approved products
                ->get();

            // Verify all products exist and are approved
            $requestedIds = collect($products_list)->pluck('product_id');
            $foundIds = $products->pluck('id');
            $missingIds = $requestedIds->diff($foundIds);

            if ($missingIds->isNotEmpty()) {
                throw new \Exception("Some products are not available or not approved: " . $missingIds->join(', '));
            }

            // Verify product availability and quantities
            foreach ($products_list as $item) {
                $product = $products->firstWhere('id', $item['product_id']);
                if ($product->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient quantity available for product: {$product->title}");
                }
            }

            // Calculate total price
            $total_price = $products->sum(function ($product) use ($products_list) {
                $quantity = collect($products_list)
                    ->firstWhere('product_id', $product->id)['quantity'];
                return $product->price * $quantity;
            });

            // Create the order
            $order = Order::create([
                'user_id' => $user->id,
                'products_list' => json_encode($products_list),
                'total_quantity' => collect($products_list)->sum('quantity'),
                'total_price' => $total_price,
                'status' => $request->status
            ]);

            // Update product quantities
            foreach ($products_list as $item) {
                $product = $products->firstWhere('id', $item['product_id']);
                $product->quantity -= $item['quantity'];
                $product->save();
            }

            DB::commit();

            return new OrderResource($order);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 422
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        // Check admin authentication first
        if (Auth::guard('admin')->check()) {
            $order = Order::with(['user'])->find($id);
            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }
            return new OrderResource($order);
        }

        // Check user authentication
        if (!Auth::guard('user')->check()) {
            return response()->json([
                'message' => 'Unauthorized. Please login to view orders.',
                'status' => 403
            ], 403);
        }

        $user = Auth::guard('user')->user();

        // First check if order exists
        $orderExists = Order::where('id', $id)->exists();
        if (!$orderExists) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Then check if it belongs to the user
        $order = Order::where('id', $id)->where('user_id', $user->id)->with(['user'])->first();

        if (!$order) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to view this order.',
                'status' => 403
            ], 403);
        }

        return new OrderResource($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Check user authentication
        if (!Auth::guard('user')->check()) {
            return response()->json([
                'message' => 'Unauthorized. Please login to update orders.',
                'status' => 403
            ], 403);
        }

        $user = Auth::guard('user')->user();

        // Begin transaction
        DB::beginTransaction();

        try {
            // Find the order and check if it belongs to the user
            $order = Order::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                throw new \Exception('Unauthorized. You do not have permission to update this order.');
            }

            // Validate the request
            $request->validate([
                'products_list' => 'required|array',
                'products_list.*.product_id' => 'required|exists:products,id',
                'products_list.*.quantity' => 'required|integer|min:1',
            ]);

            // Get the old and new products lists
            $old_products_list = json_decode($order->products_list, true);
            $new_products_list = $request->products_list;

            // Fetch all products involved (both old and new)
            $product_ids = collect($old_products_list)->pluck('product_id')
                ->merge(collect($new_products_list)->pluck('product_id'))
                ->unique();
            $products = Products::whereIn('id', $product_ids)->get();

            // First, return quantities from old order back to products
            foreach ($old_products_list as $item) {
                $product = $products->firstWhere('id', $item['product_id']);
                if ($product) {
                    $product->quantity += $item['quantity'];
                }
            }

            // Then, check and deduct new quantities
            foreach ($new_products_list as $item) {
                $product = $products->firstWhere('id', $item['product_id']);
                if (!$product) {
                    throw new \Exception("Product {$item['product_id']} not found");
                }

                if ($product->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient quantity available for product: {$product->title}");
                }

                $product->quantity -= $item['quantity'];
            }

            // Calculate new total price
            $total_price = $products->sum(function ($product) use ($new_products_list) {
                $item = collect($new_products_list)->firstWhere('product_id', $product->id);
                return $item ? $product->price * $item['quantity'] : 0;
            });

            // Update the order
            $order->update([
                'products_list' => json_encode($new_products_list),
                'total_quantity' => collect($new_products_list)->sum('quantity'),
                'total_price' => $total_price
            ]);

            // Save all product quantity changes
            foreach ($products as $product) {
                $product->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Order updated successfully! Your order details have been changed and product quantities have been adjusted.',
                'status' => 200,
                'data' => new OrderResource($order)
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 422
            ], 422);
        }
    }

    /**
     * Update order status (admin only).
     */
    public function updateStatus(Request $request, string $id)
    {
        // Check if admin is authenticated
        if (!Auth::guard('admin')->check()) {
            return response()->json([
                'message' => 'Unauthorized. Only admins can update order status.',
                'status' => 403
            ], 403);
        }

        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return new OrderResource($order);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Check user authentication
        if (!Auth::guard('user')->check()) {
            return response()->json([
                'message' => 'Unauthorized. Please login to delete orders.',
                'status' => 403
            ], 403);
        }

        // Begin transaction
        DB::beginTransaction();

        try {
            $user = Auth::guard('user')->user();

            // Find the order and check if it belongs to the user
            $order = Order::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                throw new \Exception('Unauthorized. You do not have permission to delete this order.');
            }

            // Return quantities back to products
            $products_list = json_decode($order->products_list, true);
            $products = Products::whereIn('id', collect($products_list)->pluck('product_id'))->get();

            foreach ($products_list as $item) {
                $product = $products->firstWhere('id', $item['product_id']);
                if ($product) {
                    $product->quantity += $item['quantity'];
                    $product->save();
                }
            }

            // Delete the order
            $order->delete();

            DB::commit();

            return response()->json([
                'message' => 'Order deleted successfully. Product quantities have been restored.',
                'status' => 200
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 422
            ], 422);
        }
    }
}
