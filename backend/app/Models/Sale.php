<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    protected $table = 'sales';
    
    protected $fillable = [
        'customer_id',
        'warehouse_id',
        'created_by',
        'sale_date',
        'payment_method',
        'payment_status',
        'discount',
        'tax',
        'total_amount',
        'total_cogs',
        'gross_profit',
    ];
    
    protected $casts = [
        'sale_date' => 'datetime',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'total_cogs' => 'decimal:2',
        'gross_profit' => 'decimal:2',
    ];
    
    // Make sure these relationships exist and don't have circular references
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
    
    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }
}