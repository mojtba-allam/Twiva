<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'statistics' => [
                'users' => $this->resource['statistics']['users'],
                'businesses' => $this->resource['statistics']['businesses'],
                'products' => $this->resource['statistics']['products'],
                'orders' => $this->resource['statistics']['orders'],
                'revenue' => $this->resource['statistics']['revenue'],
            ],
            'recent_activities' => [
                'orders' => OrderResource::collection($this->resource['recent_activities']['orders']),
                'products' => ProductResource::collection($this->resource['recent_activities']['products']),
                'users' => UserResource::collection($this->resource['recent_activities']['users']),
                'businesses' => BusinessAccountResource::collection($this->resource['recent_activities']['businesses']),
            ],
            'products_by_category' => CategoryResource::collection($this->resource['products_by_category']),
            'sales_analytics' => $this->resource['sales_analytics'],
        ];
    }
}