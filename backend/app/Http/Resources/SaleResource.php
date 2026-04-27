<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray($request)
    {
        // Base data - only direct attributes, no relationships
        $data = [
            'id' => $this->id,
            'customer_name'  => $this->customer?->name ?? 'Walk-in Customer',
            'customer_phone' => $this->customer?->phone,
            'customer_id' => $this->customer_id,
            'warehouse_id' => $this->warehouse_id,
            'warehouse_name' => $this->warehouse?->name,
            'created_by' => $this->created_by,
            'sale_date' => $this->sale_date?->format('Y-m-d') ?? $this->sale_date,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'discount' => (float) $this->discount,
            'tax' => (float) $this->tax,
            'paid_amount' => (float) $this->paid_amount,
            'total_amount' => (float) $this->total_amount,
            'total_cogs' => (float) $this->total_cogs,
            'gross_profit' => (float) $this->gross_profit,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'items' => $this->whenLoaded('items', function() {
                return $this->items->map(fn($item) => [
                    'product_name'  => $item->product?->name,
                    'quantity'      => $item->quantity,
                    'selling_price' => (float) $item->selling_price,
                    'subtotal'      => (float) $item->subtotal,
                ]);
            }),
        ];

        // Only add customer if loaded and exists
        if ($this->relationLoaded('customer') && $this->customer) {
            $data['customer'] = [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
            ];
        } else {
            $data['customer'] = null;
            $data['customer_name'] = 'Walk-in Customer';
        }

        // Only add warehouse if loaded
        if ($this->relationLoaded('warehouse') && $this->warehouse) {
            $data['warehouse'] = [
                'id' => $this->warehouse->id,
                'name' => $this->warehouse->name,
            ];
        }

        // Only add user if loaded
        if ($this->relationLoaded('user') && $this->user) {
            $data['user'] = [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ];
        }

        // Only add items if loaded
        if ($this->relationLoaded('items')) {
            $data['items'] = [];
            foreach ($this->items as $item) {
                $itemData = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'selling_price' => (float) $item->selling_price,
                    'cost_price' => (float) $item->cost_price,
                    'subtotal' => (float) $item->subtotal,
                    'gross_profit' => (float) $item->gross_profit,
                ];
                
                // Only add product if loaded
                if ($item->relationLoaded('product') && $item->product) {
                    $itemData['product_name'] = $item->product->name;
                    $itemData['product_sku'] = $item->product->sku ?? null;
                } else {
                    $itemData['product_name'] = 'Product #' . $item->product_id;
                }
                
                $data['items'][] = $itemData;
            }
        }

        // Only add returns if loaded
        if ($this->relationLoaded('returns')) {
            $data['returns'] = [];
            foreach ($this->returns as $return) {
                $returnData = [
                    'id' => $return->id,
                    'product_id' => $return->product_id,
                    'quantity' => $return->quantity,
                    'refund_amount' => (float) $return->refund_amount,
                    'status' => $return->status,
                    'reason' => $return->reason,
                ];
                
                if ($return->relationLoaded('product') && $return->product) {
                    $returnData['product_name'] = $return->product->name;
                }
                
                if ($return->relationLoaded('refund') && $return->refund) {
                    $returnData['refund'] = [
                        'id' => $return->refund->id,
                        'amount' => (float) $return->refund->amount,
                        'payment_method' => $return->refund->payment_method,
                    ];
                }
                
                $data['returns'][] = $returnData;
            }
        }

        return $data;
    }
}