<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Categories;
use App\Models\Admin;
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
        // Fetch the category name based on the category_id
        $category = Categories::find($this->category_id);

        // Check if user is admin or the business that created this product
        $user = Auth::guard('sanctum')->user();
        $isAdmin = $user && $user instanceof Admin;
        $isOwnerBusiness = $user && $user->id == $this->business_account_id;

        // Build the response
        $response = [
            'id' => $this->id,
            'title' => $this->title,
            'price' => $this->price,
            'image_url' => $this->image_url,
            'url' => route('products.show', $this->id),
        ];

        // Only show quantity to the business owner or admins
        if ($isAdmin || $isOwnerBusiness) {
            $response['quantity'] = $this->quantity;
        }

        // Show rejection reason when product is rejected
        if ($this->status === 'rejected') {
            $response['rejection_reason'] = $this->rejection_reason;
        }

        return $response;
    }
}
