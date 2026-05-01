<?php
// app/Models/InventoryLedger.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLedger extends Model
{
    protected $table = 'inventory_ledgers';
    
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

    protected $casts = [
        'quantity' => 'decimal:3',
        'balance_before' => 'decimal:3',
        'balance_after' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2'
    ];

    // Remove the custom create method that was removing balance_before

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