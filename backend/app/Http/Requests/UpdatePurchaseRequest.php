<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add your authorization logic
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'sometimes|required|exists:suppliers,id',
            'warehouse_id' => 'sometimes|required|exists:warehouses,id',
            'purchase_date' => 'nullable|date',
            'status' => 'nullable|in:ordered,received,pending',
            'paid_amount' => 'nullable|numeric|min:0',
            'items' => 'sometimes|required|array|min:1',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|numeric|min:0.01',
            'items.*.purchase_price' => 'required_with:items|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'items.*.tax' => 'nullable|numeric|min:0|max:100',
        ];
    }
}