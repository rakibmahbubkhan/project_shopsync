<?php
// app/Http/Resources/PurchaseResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supplier_id' => $this->supplier_id,
            'supplier' => $this->whenLoaded('supplier'),
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => $this->whenLoaded('warehouse'),
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user'),
            'purchase_date' => $this->purchase_date,
            'reference_no' => $this->reference_no,
            'subtotal' => $this->subtotal,
            'total_discount' => $this->total_discount,
            'total_tax' => $this->total_tax,
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount,
            'payment_status' => $this->payment_status,
            'status' => $this->status,
            'notes' => $this->notes,
            'shipping_method' => $this->shipping_method,
            'shipping_cost' => $this->shipping_cost,
            'payment_method' => $this->payment_method,
            'expected_delivery_date' => $this->expected_delivery_date,
            'delivered_date' => $this->delivered_date,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'items' => PurchaseItemResource::collection($this->whenLoaded('items')),
            'payments' => PurchasePaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}