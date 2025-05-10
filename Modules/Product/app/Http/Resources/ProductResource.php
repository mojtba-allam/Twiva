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

        // Determine if this is the detail (show) endpoint
        $routeName = $request->route() ? $request->route()->getName() : null;

        // Build the response
        $response = [
            'id' => $this->id,
            'title' => $this->title,
            'price' => $this->price,
            'image_url' => $this->image_url,
            'status' => $this->status,
            'quantity' => $this->quantity,
        ];

        // Only include description on detail (e.g. show) contexts
        if ($routeName === 'products.show') {
            $response['description'] = $this->description;
        }

        // Only include product_url on non-detail (e.g. index) contexts
        if ($routeName !== 'products.show') {
            $response['product_url'] = route('products.show', $this->id);
        }

        // Add category name if category is loaded
        if ($this->relationLoaded('category') && $this->category) {
            $response['category_name'] = $this->category->name;
            $response['category_url'] = route('category.show', $this->category->id);
        }

        // Add business name if the business() relation is loaded
        if ($this->relationLoaded('business') && $this->business) {
            $response['business_name'] = $this->business->name;
            $response['business_url'] = route('api.business.profile', $this->business->id);
        }

        // Show rejection reason when product is rejected and user is admin or owner
        if ($this->status === 'rejected' && ($isAdmin || $isOwnerBusiness)) {
            $response['rejection_reason'] = $this->rejection_reason;
            $response['rejected_at'] = $this->updated_at;
        }

        return $response;
    }
}
