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
        $isShowRoute = $request->route()->getName() === 'category.show';

        $response = [
            'id' => $this->id,
            'name' => $this->name,
            'url' => route('category.show', $this->id),
        ];

        if ($isShowRoute) {
            if ($this->Product && $this->Product->isNotEmpty()) {
                $response['products'] = $this->Product->map(function($product) {
                    return [
                        'name' => $product->title,
                        'price' => $product->price,
                        'image_url' => $product->image_url,
                        'product_url' => route('products.show', $product->id)
                    ];
                });
            } else {
                $response['message'] = 'This category has no products yet';
            }
        }
        return $response;
    }
}
