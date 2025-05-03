<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
            'read' => $this->read,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
        ];

        // Add URLs based on notification type
        switch ($this->type) {
            case 'new_order':
                if (isset($this->data['user_id'])) {
                    $data['user_url'] = url("/api/users/{$this->data['user_id']}");
                }
                if (isset($this->data['order_id'])) {
                    $data['order_url'] = url("/api/orders/{$this->data['order_id']}");
                }
                break;

            case 'order_updated':
                if (isset($this->data['user_id'])) {
                    $data['user_url'] = url("/api/users/{$this->data['user_id']}");
                }
                if (isset($this->data['order_id'])) {
                    $data['order_url'] = url("/api/orders/{$this->data['order_id']}");
                }
                break;

            case 'new_product':
                if (isset($this->data['product_id'])) {
                    $data['product_url'] = url("/api/products/{$this->data['product_id']}");
                }
                if (isset($this->data['business_account_id'])) {
                    $data['business_url'] = url("/api/business/{$this->data['business_account_id']}");
                }
                break;

            case 'product_edited':
                if (isset($this->data['product_id'])) {
                    $data['product_url'] = url("/api/products/{$this->data['product_id']}");
                }
                if (isset($this->data['business_account_id'])) {
                    $data['business_url'] = url("/api/business/{$this->data['business_account_id']}");
                }
                break;

            case 'product_approved':
            case 'product_rejected':
                if (isset($this->data['product_id'])) {
                    $data['product_url'] = url("/api/products/{$this->data['product_id']}");
                }
                if (isset($this->data['business_account_id'])) {
                    $data['business_url'] = url("/api/business/{$this->data['business_account_id']}");
                }
                break;

            case 'product_deleted':
                if (isset($this->data['product_id'])) {
                    $data['product_url'] = url("/api/products/{$this->data['product_id']}");
                }
                if (isset($this->data['business_account_id'])) {
                    $data['business_url'] = url("/api/business/{$this->data['business_account_id']}");
                }
                break;

            case 'product_in_order_deleted':
                if (isset($this->data['order_id'])) {
                    $data['order_url'] = url("/api/orders/{$this->data['order_id']}");
                }
                if (isset($this->data['product_id'])) {
                    $data['product_url'] = url("/api/products/{$this->data['product_id']}");
                }
                break;
        }

        return $data;
    }
}
