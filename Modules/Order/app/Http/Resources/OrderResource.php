<?php

namespace Modules\Order\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Products;
use App\Models\Categories;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Decode the products list from the order
        $products_list = json_decode($this->products_list, true) ?? [];

        // Build the base response array
        $response = [
            'id' => $this->id,
            'user' => [
                'id' => $this->user_id,
                'name' => $this->User->name ?? "Unknown User"
            ],
            'total_quantity' => $this->total_quantity,
            'total_price' => number_format($this->total_price, 2) . ' $',
            'status' => $this->status,
        ];

        // Check if this is a single order request (detail view)
        $isDetailView = $request->route() && $request->route()->getName() === 'orders.show';

        if ($isDetailView) {
            // Prepare empty arrays for products and messages
            $activeProducts = [];
            $deletedProducts = [];
            $deleted_products_message = null;

            // Get all product IDs from the order
            $productIds = collect($products_list)->pluck('product_id')->toArray();

            // Use DB query directly to bypass model scopes or soft deletes
            $dbProducts = DB::table('products')
                ->whereIn('id', $productIds)
                ->get();

            // Process each product in the order
            foreach ($products_list as $item) {
                // Try to find the product in our results
                $dbProduct = $dbProducts->firstWhere('id', $item['product_id']);

                // Check if product is marked as deleted in the order or has deleted status
                $isDeleted = (isset($item['deleted']) && $item['deleted']) ||
                             (!$dbProduct || $dbProduct->status === 'deleted');

                // Create the product data array with appropriate values
                $productData = [
                    'product_id' => $item['product_id'],
                    'product_title' => $item['product_title'] ?? ($dbProduct ? $dbProduct->title : "Product #{$item['product_id']}"),
                    'quantity' => $item['quantity'],
                    'unit_price' => number_format($item['price'] ?? ($dbProduct ? $dbProduct->price : 0), 2) . ' $',
                    'subtotal' => number_format(($item['price'] ?? ($dbProduct ? $dbProduct->price : 0)) * $item['quantity'], 2) . ' $',
                    'product_image' => $dbProduct ? $dbProduct->image_url : null,
                ];

                // Add specific fields based on product status
                if ($isDeleted) {
                    // For deleted products, only include minimal information
                    $deletedProducts[] = [
                        'product_title' => $item['product_title'] ?? ($dbProduct ? $dbProduct->title : "Product #{$item['product_id']}"),
                        'status' => 'unavailable',
                        'message' => "This product is no longer available"
                    ];
                } else {
                    $productData['product_url'] = url("/api/products/{$item['product_id']}");
                    $activeProducts[] = $productData;
                }
            }

            // Combine active and deleted products
            $allProducts = array_merge($activeProducts, $deletedProducts);

            // Create the deleted products message if needed
            if (count($deletedProducts) > 0) {
                $deletedTitles = collect($deletedProducts)->pluck('product_title')->implode(', ');
                $deleted_products_message = "Some products in this order are no longer available: $deletedTitles";
            }

            // Add detailed products and deleted products message to the response
            $response['products'] = $allProducts;

            if ($deleted_products_message) {
                $response['deleted_products_message'] = $deleted_products_message;
            }
        } else {
            // For list view, just provide summary information

            // Count total products and deleted products
            $totalProducts = count($products_list);
            $deletedProductsCount = collect($products_list)->where('deleted', true)->count();

            // Add summary counts to response
            $response['products_count'] = $totalProducts;

            if ($deletedProductsCount > 0) {
                $response['unavailable_products_count'] = $deletedProductsCount;
            }

            // Add URL for the detail view
            $response['url'] = url("/api/orders/{$this->id}");
        }

        return $response;
    }
}
