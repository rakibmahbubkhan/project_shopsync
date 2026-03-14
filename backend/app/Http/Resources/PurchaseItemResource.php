<?php
// app/Http/Resources/PurchaseItemResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseItemResource extends JsonResource
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
            'purchase_id' => $this->purchase_id,
            'product_id' => $this->product_id,
            'quantity' => (float) $this->quantity,
            'cost_price' => (float) $this->cost_price,
            'subtotal' => (float) $this->subtotal,
            
            // Relationships
            'product' => new ProductResource($this->whenLoaded('product')),
            'purchase' => new PurchaseResource($this->whenLoaded('purchase')),
            
            // Computed fields
            'total' => (float) ($this->quantity * $this->cost_price),
            
            // Formatted values
            'formatted' => [
                'quantity' => number_format($this->quantity, 2),
                'cost_price' => number_format($this->cost_price, 2),
                'subtotal' => number_format($this->subtotal, 2),
                'total' => number_format($this->quantity * $this->cost_price, 2),
            ],
            
            // Product details when loaded (for quick access)
            'product_details' => $this->whenLoaded('product', function() {
                return [
                    'name' => $this->product->name,
                    'sku' => $this->product->sku ?? null,
                    'code' => $this->product->code ?? null,
                    'unit' => $this->product->unit ?? null,
                ];
            }),
        ];
    }
}