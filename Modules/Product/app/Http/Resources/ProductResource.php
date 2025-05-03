<?php

namespace Modules\Product\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Categories\app\Models\Categories;
use Modules\Admin\app\Models\Admin;
use Illuminate\Support\Facades\Auth;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Check if user is admin or the business that created this product
        $user = Auth::guard('sanctum')->user();
        $isAdmin = $user && $user instanceof Admin;
        $isOwnerBusiness = $user && $user->id == $this->business_account_id;

        // Build the response
        $response = [
            'id' => $this->id,
            'title' => $this->title,
            'product_url' => route('products.show', $this->id),
            'price' => $this->price,
            'image_url' => $this->image_url,
            'status' => $this->status,
            'quantity' => $this->quantity,
            'description' => $this->description
        ];

        // Add category name if category is loaded
        if ($this->relationLoaded('category') && $this->category) {
            $response['category_name'] = $this->category->name;
        }

        // Add business name if business account is loaded
        if ($this->relationLoaded('businessAccount') && $this->businessAccount) {
            $response['business_name'] = $this->businessAccount->name;
        }

        // Show rejection reason when product is rejected and user is admin or owner
        if ($this->status === 'rejected' && ($isAdmin || $isOwnerBusiness)) {
            $response['rejection_reason'] = $this->rejection_reason;
            $response['rejected_at'] = $this->updated_at;
        }

        return $response;
    }
}
