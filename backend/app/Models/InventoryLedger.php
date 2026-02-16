<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLedger extends Model
{
    protected $fillable = [
        'product_id',
        'reference_type',
        'reference_id',
        'movement_type',
        'quantity',
        'balance_after',
        'unit_cost',
        'total_cost',
        'user_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

