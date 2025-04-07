<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Categories;
use App\Models\Admin;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Fetch the category name based on the category_id
        $category = Categories::find($this->category_id);
        $admin = Admin::find($this->admin_id);

        return [
            'id' => $this->id,

             'product_url' => $this->when(
            $request->routeIs('products.index'), // Show product_url only for products.index
            $this->product_url
        ),

            'title' => $this->title,

           'description' => $this->when(
                $request->routeIs('products.show'),
                $this->description
            ),

            'price' => $this->price . ' $',

            'quantity' => $this->when(
                $request->routeIs('products.show'),
                $this->quantity
            ),

            'image_url' => $this->image_url,
            'category_name' => $category->name ?? 'Unknown Category', // Use category name
            'admin_id' => $admin->name ?? 'Unknown Admin', // Use admin name

        ];
    }
}
