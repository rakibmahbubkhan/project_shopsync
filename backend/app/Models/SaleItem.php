<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'selling_price',
        'cost_price',
        'subtotal',
        'gross_profit',
        'created_at',
        'updated_at',
    ];
    
    protected $casts = [
        'quantity' => 'integer',
        'selling_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'gross_profit' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * FIXED: Removed custom boot methods that manually set timestamps
     * Let Eloquent handle timestamps automatically via $timestamps property
     */
    // REMOVED the custom static::creating and static::updating
    
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}