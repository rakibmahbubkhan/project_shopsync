<?php
// app/Models/ProductStock.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    protected $table = 'product_stocks';
    
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'avg_cost',
        'last_updated_by'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'avg_cost' => 'decimal:2'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }
}