<?php
// app/Http/Resources/SupplierResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            
            // Optional: Include related data when loaded
            'purchases_count' => $this->whenCounted('purchases'),
            'recent_purchases' => PurchaseResource::collection($this->whenLoaded('purchases')),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Formatted data for convenience
            'formatted' => [
                'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            ]
        ];
    }
}