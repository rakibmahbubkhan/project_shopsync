<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SaleItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sale_id' => $this->sale_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'selling_price' => $this->selling_price,
            'cost_price' => $this->cost_price,
            'subtotal' => $this->subtotal,
            'gross_profit' => $this->gross_profit,
            //'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}