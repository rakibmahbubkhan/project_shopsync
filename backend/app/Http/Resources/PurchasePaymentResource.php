<?php
// app/Http/Resources/PurchasePaymentResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchasePaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchase_id' => $this->purchase_id,
            'amount' => $this->amount,
            'payment_date' => $this->payment_date,
            'payment_method' => $this->payment_method,
            'reference_no' => $this->reference_no,
            'notes' => $this->notes,
            'installment_number' => $this->installment_number,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
        ];
    }
}