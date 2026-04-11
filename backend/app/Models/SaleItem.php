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
    ];
    
    protected $casts = [
        'quantity' => 'integer',
        'selling_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'gross_profit' => 'decimal:2',
    ];
    
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}