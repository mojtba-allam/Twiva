<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProductResource;
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
        $isShowRoute = $request->route()->getName() === 'categories.show';

        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => route('categories.show', $this->id),
            'products' => $this->when($isShowRoute && $this->Products, function() {
                return $this->Products->map(function($product) {
                    return [
                        'name' => $product->title,
                        'price' => $product->price,
                        'image_url' => $product->image_url,
                        'product_url' => route('products.show', $product->id)
                    ];
                });
            }),
        ];
    }
}
