<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $table = 'stock_movements';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'type',
        'reference_id',
        'reference_type',
        'unit_cost',
        'user_id'
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}