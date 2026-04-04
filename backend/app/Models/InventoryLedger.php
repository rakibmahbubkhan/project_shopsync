<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLedger extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'reference_type',
        'reference_id',
        'movement_type',
        'quantity',
        'balance_before',
        'balance_after',
        'unit_cost',
        'total_cost',
        'user_id'
    ];

    public static function create(array $attributes = [])
    {
        // Remove balance_before if it doesn't exist in the table
        if (isset($attributes['balance_before'])) {
            unset($attributes['balance_before']);
        }
        
        return parent::create($attributes);
    }

    public function product() { return $this->belongsTo(Product::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function user() { return $this->belongsTo(User::class); }
}

