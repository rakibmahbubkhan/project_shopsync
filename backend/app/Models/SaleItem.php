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
    
    // Override the boot method to handle timestamps manually
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->created_at = now();
            // Don't set updated_at on creation
            $model->updated_at = null;
        });
        
        static::updating(function ($model) {
            $model->updated_at = now();
        });
    }
    
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}