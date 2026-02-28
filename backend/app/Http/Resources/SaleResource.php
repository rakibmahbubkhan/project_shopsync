<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'warehouse_id' => $this->warehouse_id,
            'created_by' => $this->created_by,
            'sale_date' => $this->sale_date,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'total_amount' => $this->total_amount,
            'total_cogs' => $this->total_cogs,
            'gross_profit' => $this->gross_profit,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            // 'customer' => new CustomerResource($this->whenLoaded('customer')),
            // 'items' => SaleItemResource::collection($this->whenLoaded('items')),
            // 'user' => new UserResource($this->whenLoaded('user')),
            // 'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
        ];
    }
}