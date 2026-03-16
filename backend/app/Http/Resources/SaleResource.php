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
            'customer' => $this->customer->name ?? 'Walk-in Customer',
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => $this->warehouse->name ?? 'N/A',
            'created_by' => $this->created_by,
            'sale_date' => $this->sale_date->format('Y-m-d'),
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'total_amount' => $this->total_amount,
            'total_cogs' => $this->total_cogs,
            'gross_profit' => $this->gross_profit,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Items with detailed information
            'items' => $this->items->map(fn($item) => [
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'price' => $item->selling_price,
                'subtotal' => $item->subtotal
            ]),
            
            // Keep the original relationship resources as comments for reference
            // 'customer' => new CustomerResource($this->whenLoaded('customer')),
            // 'items' => SaleItemResource::collection($this->whenLoaded('items')),
            // 'user' => new UserResource($this->whenLoaded('user')),
            // 'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
        ];
    }
}