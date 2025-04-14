<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

class PendingProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Check if user is admin
        $user = Auth::guard('sanctum')->user();
        $isAdmin = $user && $user instanceof Admin;

        // Base response for both admin and business
        $response = [
            'id' => $this->id,
            'title' => $this->title,
            'price' => $this->price,
            'image_url' => $this->image_url,
            'quantity' => $this->quantity,
            'category' => $this->whenLoaded('category', function() {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name
                ];
            })
        ];

        // Additional information for admin only
        if ($isAdmin) {
            $response['business'] = $this->whenLoaded('businessAccount', function() {
                return [
                    'id' => $this->businessAccount->id,
                    'name' => $this->businessAccount->name,
                    'image_url' => $this->businessAccount->image_url,
                    'url' => route('business.profile', $this->businessAccount->id)
                ];
            });
        }

        return $response;
    }
}
