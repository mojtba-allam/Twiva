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
use App\Services\NotificationService;

class OrderController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

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

        // Validate request structure
        $request->validate([
            'products_list' => 'required|array',
            'products_list.*.product_id' => 'required|integer',
            'products_list.*.quantity' => 'required|integer|min:1',
            'status' => 'required|string'
        ]);

        // Begin transaction
        DB::beginTransaction();

        try {
            // Get products and validate quantities
            $products_list = [];
            $total_quantity = 0;
            $total_price = 0;
            $errors = [];

            foreach ($request->products_list as $index => $item) {
                try {
                    $product = Products::findOrFail($item['product_id']);

                    // Check if product is approved
                    if ($product->status !== 'approved') {
                        $errors[] = "Product '{$product->title}' is not available for ordering (Status: {$product->status})";
                        continue;
                    }

                    if ($product->quantity < $item['quantity']) {
                        $errors[] = "Insufficient quantity for product '{$product->title}'. Available: {$product->quantity}, Requested: {$item['quantity']}";
                        continue;
                    }

                    $products_list[] = [
                        'product_id' => $product->id,
                        'product_title' => $product->title,
                        'quantity' => $item['quantity'],
                        'price' => $product->price
                    ];

                    $total_quantity += $item['quantity'];
                    $total_price += ($product->price * $item['quantity']);
                } catch (ModelNotFoundException $e) {
                    // Try to get the product title from the database even if it's deleted
                    $deletedProduct = DB::table('products')
                        ->where('id', $item['product_id'])
                        ->first();

                    if ($deletedProduct) {
                        $errors[] = "Product '{$deletedProduct->title}' is no longer available";
                    } else {
                        $errors[] = "Product with ID {$item['product_id']} does not exist";
                    }
                }
            }

            // If there are any errors, return them
            if (!empty($errors)) {
                return response()->json([
                    'message' => 'Order validation failed',
                    'errors' => $errors
                ], 422);
            }

            // Create the order
            $order = Order::create([
                'user_id' => $user->id,
                'products_list' => json_encode($products_list),
                'total_quantity' => $total_quantity,
                'total_price' => $total_price,
                'status' => $request->status
            ]);

            // Update product quantities
            foreach ($products_list as $item) {
                $product = Products::findOrFail($item['product_id']);
                $product->quantity -= $item['quantity'];
                $product->save();
            }

            // Notify admins about the new order
            $this->notificationService->notifyAdmins(
                'new_order',
                'New Order Placed',
                "User {$user->name} has placed a new order with ID #{$order->id}",
                [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'total_price' => $total_price
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully! Your order has been placed and is being processed.',
                'status' => 201
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
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

            // Validate product status and quantities
            $errors = [];
            foreach ($new_products_list as $item) {
                $product = $products->firstWhere('id', $item['product_id']);
                if (!$product) {
                    $errors[] = "Product {$item['product_id']} not found";
                    continue;
                }

                // Add status validation
                if ($product->status !== 'approved') {
                    $errors[] = "Product '{$product->title}' is not available for ordering (Status: {$product->status})";
                    continue;
                }
            }

            // If there are any errors, return them
            if (!empty($errors)) {
                return response()->json([
                    'message' => 'Order validation failed',
                    'errors' => $errors
                ], 422);
            }

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

            // Notify admins about the order update
            $this->notificationService->notifyAdmins(
                'order_updated',
                'Order Updated',
                "User {$user->name} has updated order #{$order->id}",
                [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'total_price' => $total_price
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Order updated successfully! Your order details have been changed.',
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
            ], 403);
        }

        // Begin transaction
        DB::beginTransaction();

        try {
            $user = Auth::guard('user')->user();

            // First check if the order exists
            $orderExists = Order::where('id', $id)->exists();
            if (!$orderExists) {
                return response()->json([
                    'message' => 'Order not found',
                    'status' => 404
                ], 404);
            }

            // Then check if it belongs to the user
            $order = Order::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'message' => 'Unauthorized. You do not have permission to delete this order.',
                    'status' => 403
                ], 403);
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
