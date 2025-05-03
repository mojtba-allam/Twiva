<?php

namespace Modules\Admin\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Product\app\Models\Product;
use Modules\Business\app\Models\Business;
use Illuminate\Http\Request;
use Modules\Product\app\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Modules\Order\app\Models\Order;
use Modules\Product\app\Http\Resources\PendingProductResource;
use Modules\Notification\app\Services\NotificationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminProductController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

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

        // If admin, show all pending products with business information
        if ($isAdmin) {
            $products = Product::where('status', Product::STATUS_PENDING)
                ->with(['businessAccount', 'category'])
                ->paginate(10);

            return response()->json([
                'data' => ProductResource::collection($products)
            ]);
        }

        // If business account, show only their pending products without business information
        if ($isBusiness) {
            $products = Product::where('status', Product::STATUS_PENDING)
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

    public function approveProduct($id)
    {
        try {
            $product = Product::findOrFail($id);

            if ($product->status !== Product::STATUS_PENDING) {
                return response()->json([
                    'message' => 'Only pending products can be approved'
                ], 400);
            }

            $product->status = Product::STATUS_APPROVED;
            $product->save();

            // Notify business about product approval
            $this->notificationService->notifyBusiness(
                $product->businessAccount,
                'product_approved',
                'Product Approved',
                "Your product '{$product->title}' has been approved",
                [
                    'product_id' => $product->id,
                    'business_account_id' => $product->business_account_id
                ]
            );

            return response()->json([
                'message' => 'Product approved successfully',
                'product' => new ProductResource($product)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }
    }

    public function rejectProduct(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            if ($product->status !== Product::STATUS_PENDING) {
                return response()->json([
                    'message' => 'Only pending products can be rejected'
                ], 400);
            }

            $request->validate([
                'rejection_reason' => 'required|string|max:500'
            ]);

            $product->status = Product::STATUS_REJECTED;
            $product->rejection_reason = $request->rejection_reason;
            $product->save();

            // Notify business about product rejection
            $this->notificationService->notifyBusiness(
                $product->businessAccount,
                'product_rejected',
                'Product Rejected',
                "Your product '{$product->title}' has been rejected. Reason: {$request->rejection_reason}",
                [
                    'product_id' => $product->id,
                    'business_account_id' => $product->business_account_id
                ]
            );

            return response()->json([
                'message' => 'Product rejected successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }
    }

    public function deleteProduct($id)
    {
        try {
            // Find the product
            $product = Product::findOrFail($id);
            $productTitle = $product->title;

            // Get all orders from the database
            $allOrders = Order::all();
            $orders = collect();

            // Manually filter orders that contain this product
            foreach ($allOrders as $order) {
                $products_list = json_decode($order->products_list, true);
                if (!is_array($products_list)) {
                    continue;
                }

                foreach ($products_list as $item) {
                    if ((string)($item['product_id'] ?? '') === (string)$id) {
                        $orders->push($order);
                        break;
                    }
                }
            }

            // Process each affected order
            foreach ($orders as $order) {
                $products_list = json_decode($order->products_list, true);
                $deleted_products = [];
                $total_quantity = 0;
                $total_price = 0;

                // Update each product in the list
                foreach ($products_list as $key => $item) {
                    if ((string)($item['product_id'] ?? '') === (string)$id) {
                        // Mark the product as deleted but keep it in the list
                        $products_list[$key]['deleted'] = true;
                        $products_list[$key]['product_title'] = $product->title;

                        // Add to deleted products for notification
                        $deleted_products[] = [
                            'product_id' => $id,
                            'product_title' => $product->title,
                            'quantity' => $item['quantity'],
                            'price' => $product->price // Use the product's current price
                        ];
                    } else {
                        // Only count non-deleted products in totals
                        $total_quantity += $item['quantity'];
                        // Make sure we have a valid price
                        $itemPrice = isset($item['price']) ? $item['price'] : 0;
                        if ($itemPrice == 0 && isset($item['product_id'])) {
                            $productItem = Product::find($item['product_id']);
                            if ($productItem) {
                                $itemPrice = $productItem->price;
                            }
                        }
                        $total_price += ($itemPrice * $item['quantity']);
                    }
                }

                // Update the order with the modified products_list (keeping deleted products)
                $order->update([
                    'products_list' => json_encode($products_list),
                    'total_quantity' => $total_quantity,
                    'total_price' => $total_price
                ]);

                // Notify users about deleted products in their orders
                if (!empty($deleted_products)) {
                    $user = \App\Models\User::find($order->user_id);
                    if ($user) {
                        $this->notificationService->notifyUser(
                            $user,
                            'product_deleted',
                            'Product Removed from Order',
                            "Product '{$product->title}' has been removed from your order #{$order->id}",
                            [
                                'order_id' => $order->id,
                                'product_id' => $id,
                                'deleted_products' => $deleted_products
                            ]
                        );
                    }
                }
            }

            // Update product status to 'deleted' instead of deleting it
            $product->update([
                'status' => Product::STATUS_DELETED,
                'quantity' => 0  // Set quantity to 0 since it's no longer available
            ]);

            // Notify the business account about the product deletion
            if ($product->business_account_id) {
                $businessAccount = \App\Models\BusinessAccount::find($product->business_account_id);
                if ($businessAccount) {
                    $this->notificationService->notifyBusiness(
                        $businessAccount,
                        'product_deleted',
                        'Product Deleted',
                        "Your product '{$productTitle}' has been deleted by an admin",
                        [
                            'product_id' => $product->id,
                            'business_account_id' => $product->business_account_id
                        ]
                    );
                }
            }

            return response()->json([
                'message' => 'Product marked as deleted successfully. Affected orders have been updated.',
                'affected_orders_count' => $orders->count(),
                'status' => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete product. Please try again.',
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    public function rejectedProducts(Request $request)
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

        // If admin, show all rejected products with business information
        if ($isAdmin) {
            $products = Product::where('status', Product::STATUS_REJECTED)
                ->with(['businessAccount', 'category'])
                ->paginate(10);

            return response()->json([
                'data' => ProductResource::collection($products)
            ]);
        }

        // If business account, show only their rejected products
        if ($isBusiness) {
            $products = Product::where('status', Product::STATUS_REJECTED)
                ->where('business_account_id', $user->id)
                ->with(['category'])
                ->paginate(10);

            if ($products->isEmpty()) {
                return response()->json([
                    'message' => 'You have no rejected products',
                ]);
            }

            return response()->json([
                'data' => ProductResource::collection($products)
            ]);
        }
    }

    public function index()
    {
        $products = Product::with('business')
            ->orderBy('created_at', 'desc')
            ->get();

        return ProductResource::collection($products);
    }
}
