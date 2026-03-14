<?php
// app/Http/Resources/WarehouseResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'address' => $this->address,
            'is_active' => $this->is_active,
            
            // Status badge helper
            'status' => $this->is_active ? 'active' : 'inactive',
            'status_badge' => $this->is_active ? 'success' : 'danger',
            
            // Relationships
            'created_by' => new UserResource($this->whenLoaded('creator')),
            
            // Stats when loaded
            'stats' => $this->when($this->relationLoaded('purchases') || $this->relationLoaded('stocks'), [
                'total_purchases' => $this->whenLoaded('purchases', fn() => $this->purchases->count()),
                'total_stock_value' => $this->whenLoaded('stocks', fn() => $this->stocks->sum('value')),
                'total_products' => $this->whenLoaded('stocks', fn() => $this->stocks->count()),
            ]),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Formatted data
            'formatted' => [
                'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
                'created_by_name' => $this->whenLoaded('creator', fn() => $this->creator->name ?? null),
            ]
        ];
    }
}