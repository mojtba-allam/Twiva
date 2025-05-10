<?php

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Admin\app\Models\Admin;
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
            }),
            'business' => [
                'id' => $this->business->id,
                'name' => $this->business->name,
                'image_url' => $this->business->image_url,
                'url' => route('business.profile', $this->business->id)
            ]
        ];

        // Additional information for admin only
        if ($isAdmin) {
            $response['business'] = $this->whenLoaded('business', function() {
                return [
                    'id' => $this->business->id,
                    'name' => $this->business->name,
                    'image_url' => $this->business->image_url,
                    'url' => route('business.profile', $this->business->id)
                ];
            });
        }

        return $response;
    }
}
