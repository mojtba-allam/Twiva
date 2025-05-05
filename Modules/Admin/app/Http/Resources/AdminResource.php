<?php

namespace Modules\Admin\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get the current route path to determine context
        $routePath = $request->path();

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->profile_picture,
            // bio is intentionally excluded as requested
            'email' => $this->when(strpos($routePath, 'admins/index') == false, function () {
                return $this->email;
            }),
            'bio' => $this->when(strpos($routePath, 'admins/index') == false, function () {
                return $this->bio;
            }),
        ];

        // Only include profile_url when showing a collection (index)
        // and not when showing a single admin detail
        if (strpos($routePath, 'admins/index') !== false) {
            $data['profile_url'] = url("/api/admins/{$this->id}");
        }

        return $data;
    }
}
