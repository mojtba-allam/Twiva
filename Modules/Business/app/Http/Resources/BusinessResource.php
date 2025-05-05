<?php

namespace Modules\Business\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class BusinessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Check if this is a detailed view or a list view
        $isDetailedView = false;

        // Check if this is a show route (v1/business/{id})
        $routeName = $request->route() ? $request->route()->getName() : null;
        $routePath = $request->route() ? $request->route()->uri() : null;

        // Consider detailed view for profile routes OR direct business ID access
        if ($routeName === 'business.profile' ||
            $routeName === 'api.business.profile' ||
            $routePath === 'api/v1/business/{id}') {
            $isDetailedView = true;
        }

        // Check if user is admin
        $user = auth()->guard('sanctum')->user();
        $isAdmin = $user && $user instanceof \Modules\Admin\app\Models\Admin;

        // Base data for list view (minimal info)
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'profile_picture' => $this->profile_picture,
            'url' => route('api.business.profile', $this->id)
        ];

        // Add additional data for detailed profile view
        if ($isDetailedView) {
            $data = array_merge($data, [
                'email' => $this->email,
                'bio' => $this->bio,
                'products' => $this->whenLoaded('products', function() use ($isAdmin) {
                    return $this->products->map(function($product) use ($isAdmin) {
                        $productData = [
                            'title' => $product->title,
                            'price' => $product->price,
                            'image_url' => $product->image_url,
                            'url' => route('products.show', $product->id)
                        ];
                        if ($isAdmin) {
                            $productData['status'] = $product->status;
                            $productData['id'] = $product->id;
                            if ($product->status === 'rejected') {
                                $productData['rejection_reason'] = $product->rejection_reason;
                            }
                        }
                        return $productData;
                    });
                }, []),
            ]);
        }

        return $data;
    }
}
