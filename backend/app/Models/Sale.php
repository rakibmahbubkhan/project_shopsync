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
    
    /**
     * Override the default timestamps behavior
     */
    public $timestamps = true;
    
    /**
     * Set only created_at on insert, updated_at will be null
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
            $model->updated_at = null; // Explicitly set to null
        });
        
        static::updating(function ($model) {
            $model->updated_at = $model->freshTimestamp();
        });
    }

    public function setUpdatedAt($value)
    {
        // Only set the value if the record already exists (is an update)
        if ($this->exists) {
            $this->{static::UPDATED_AT} = $value;
        }

        return $this;
    }
    
    // Rest of your relationships...
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