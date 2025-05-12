<?php

namespace Modules\Category\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Product\app\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Auth;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Determine the authenticated guard
        $guard = null;
        $user = null;

        if (Auth::guard('admin')->check()) {
            $guard = 'admin';
            $user = Auth::guard('admin')->user();
        } elseif (Auth::guard('business')->check()) {
            $guard = 'business';
            $user = Auth::guard('business')->user();
        } elseif (Auth::guard('user')->check()) {
            $guard = 'user';
            $user = Auth::guard('user')->user();
        }

        $response = [
            'id' => $this->id,
            'name' => $this->name,
            // Only show the category URL in the index route
            'url' => $this-> when(route('category.index'), route('category.show', $this->id)),

        ];

        if ($this->relationLoaded('Product')) {
            $products = $this->Product->map(function ($product) use ($guard, $user) {
                $data = [
                    'id' => $product->id,
                    'title' => $product->title,
                    'price' => number_format($product->price, 2) . ' $',
                    'quantity' => $product->quantity,
                    'image_url' => $product->image_url,
                    'status' => $product->status,
                    'product_url' => route('products.show', $product->id),
                ];

                if ($product->business) {
                    $data['business_name'] = $product->business->name;
                    $data['business_url'] = route('business.show', $product->business->id);
                }


                return $data;
            });

            if ($products->isNotEmpty()) {
                $response['products'] = $products;

                // Add stats for admin
                if ($guard === 'admin') {
                    $response['stats'] = [
                        'total_products' => $products->count(),
                        'approved_products' => $products->where('status', 'approved')->count(),
                        'pending_products' => $products->where('status', 'pending')->count(),
                    ];
                }
            } else {
                $response['message'] = 'No products found in this category';
            }
        }

        return $response;
    }
}
