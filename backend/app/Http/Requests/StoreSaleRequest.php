<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'nullable|exists:customers,id',
            'sale_date' => 'required|date',
            'payment_method' => 'required|in:cash,card,bank,mobile',
            'payment_status' => 'required|in:pending,partial,paid',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.selling_price' => 'required|numeric|min:0'
        ];
    }
}

