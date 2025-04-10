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
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'image_url' => $this->image_url,
            'category_id' => $this->category_id,
            'business_account_id' => $this->business_account_id,
            'status' => $this->status,
            'rejection_reason' => $this->when($this->status === 'rejected', $this->rejection_reason),
            'url' => route('products.show', $this->id),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'category' => [
                'name' => $this->whenLoaded('category', fn() => $this->category->name),
                'url' => $this->whenLoaded('category', fn() => route('categories.show', $this->category_id))
            ],
            'business_account' => [
                'name' => $this->whenLoaded('businessAccount', fn() => $this->businessAccount->name),
                'url' => $this->whenLoaded('businessAccount', fn() => route('business.profile', $this->business_account_id))
            ],
        ];
    }
}
