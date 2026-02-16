<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer' => $this->customer?->name,
            'created_by' => $this->user->name,
            'sale_date' => $this->sale_date,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'total_amount' => $this->total_amount,
            'items' => $this->items->map(function ($item) {
                return [
                    'product' => $item->product->name,
                    'quantity' => $item->quantity,
                    'selling_price' => $item->selling_price,
                    'subtotal' => $item->subtotal
                ];
            }),
            'created_at' => $this->created_at,
        ];
    }
}

