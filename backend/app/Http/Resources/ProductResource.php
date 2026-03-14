<?php
// app/Http/Resources/ProductResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'sku' => $this->sku ?? null,
            'code' => $this->code ?? null,
            'description' => $this->description ?? null,
            'unit' => $this->unit ?? null,
            'cost_price' => (float) ($this->cost_price ?? 0),
            'selling_price' => (float) ($this->selling_price ?? 0),
            
            // Stock information
            'stock_quantity' => (float) ($this->stock_quantity ?? 0),
            
            'formatted' => [
                'name' => $this->name,
                'cost_price' => $this->cost_price ? number_format($this->cost_price, 2) : '0.00',
                'selling_price' => $this->selling_price ? number_format($this->selling_price, 2) : '0.00',
                'stock_quantity' => number_format($this->stock_quantity ?? 0, 2),
            ]
        ];
    }
}