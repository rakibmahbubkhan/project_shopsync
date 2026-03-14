<?php
// app/Http/Resources/PurchaseResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'reference_no' => $this->reference_no,
            'purchase_date' => $this->purchase_date,
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount,
            'due_amount' => $this->due_amount,
            'payment_status' => $this->payment_status,
            'status' => $this->status,
            'payment_progress' => $this->payment_progress,
            
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            'created_by' => new UserResource($this->whenLoaded('user')),
            'items' => PurchaseItemResource::collection($this->whenLoaded('items')),
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            'formatted' => [
                'total' => $this->formatted_total,
                'paid' => $this->formatted_paid,
                'due' => $this->formatted_due,
                'date' => $this->purchase_date->format('Y-m-d'),
            ]
        ];
    }
}