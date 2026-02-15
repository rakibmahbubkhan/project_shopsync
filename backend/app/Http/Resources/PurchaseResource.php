<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supplier' => [
                'id' => $this->supplier->id,
                'name' => $this->supplier->name,
            ],
            'created_by' => $this->user->name,
            'purchase_date' => $this->purchase_date,
            'payment_status' => $this->payment_status,
            'total_amount' => $this->total_amount,
            'items' => $this->items->map(function ($item) {
                return [
                    'product' => $item->product->name,
                    'quantity' => $item->quantity,
                    'cost_price' => $item->cost_price,
                    'subtotal' => $item->subtotal
                ];
            }),
            'created_at' => $this->created_at,
        ];
    }
}

