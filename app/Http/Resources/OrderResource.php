<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Products;
use App\Models\Categories;
use App\Models\User;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Basic order information that's always included
        $response = [
            'id' => $this->id,
            'user' => [
                'id' => $this->user_id,
                'name' => optional($this->user)->name,
            ],
            'status' => $this->status,
            'total_quantity' => $this->total_quantity,
            'total_price' => number_format($this->total_price, 2) . ' $',
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];

        // If this is a show request (single order), include full products details
        if ($request->route()->getName() === 'orders.show') {
            // Decode the products_list JSON
            $products_list = json_decode($this->products_list, true);

            // Fetch all related products with their categories
            $products = Products::with('category')
                ->whereIn('id', collect($products_list)->pluck('product_id'))
                ->get();

            // Map over the products_list to add details
            $response['products'] = collect($products_list)->map(function ($item) use ($products) {
                $product = $products->firstWhere('id', $item['product_id']);
                return [
                    'product_id' => $item['product_id'],
                    'product_title' => $product->title,
                    'quantity' => $item['quantity'],
                    'unit_price' => number_format($product->price, 2) . ' $',
                    'subtotal' => number_format($product->price * $item['quantity'], 2) . ' $',
                    'product_category' => optional($product->category)->name ?? 'Unknown Category',
                    'product_image' => $product->image_url,
                    'product_url' => $product->product_url
                ];
            })->values()->toArray();
        } else {
            // For index/list view, just include the URL to view order details
            $response['order_details_url'] = url("/api/orders/{$this->id}");
        }

        return $response;
    }
}
