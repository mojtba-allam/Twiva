<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Products;
use App\Models\Categories;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
        public function toArray($request)
        {
            // Decode the products_list JSON
            $products_list = json_decode($this->products_list, true);

            // Fetch all related products
            $products = Products::whereIn('id', collect($products_list)->pluck('product_id'))->get();

            // Map over the products_list to add URLs and other details
            $products_list_with_urls = collect($products_list)->map(function ($item) use ($products) {
                $product = $products->firstWhere('id', $item['product_id']);
                return [
                    'product_id' => $item['product_id'],
                    'product_title' => $product->title,
                    'quantity' => $item['quantity'],
                    'product_price' => $product->price . ' $',
                    'product_category' => optional(Categories::where('id', $product->category_id)->first())->name ?? 'Unknown Category',
                    'product_image' => $product->image_url,
                    'product_url' => $product->product_url
                ];
            })->toArray();

            // Add the enhanced products_list to the order
            $this->products_list = $products_list_with_urls;

            // Return the transformed order data
            return [
                'id' => $this->id,
                'user_id' => $this->user_id,
                'total_price' => $this->total_price . ' $',
                'products_list' => $this->products_list,
            ];
        }
    }
