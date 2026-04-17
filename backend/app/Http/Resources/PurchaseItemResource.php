<?php
// app/Http/Resources/PurchaseItemResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchase_id' => $this->purchase_id,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'quantity' => $this->quantity,
            'purchase_price' => $this->purchase_price,
            'subtotal' => $this->subtotal,
            'discount_percent' => $this->discount_percent ?? 0,
            'discount_amount' => $this->discount_amount ?? 0,
            'tax_percent' => $this->tax_percent ?? 0,
            'tax_amount' => $this->tax_amount ?? 0,
            'total' => $this->total,
            'batch_no' => $this->batch_no,
            'expiry_date' => $this->expiry_date,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}