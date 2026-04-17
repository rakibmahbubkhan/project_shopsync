<?php
// app/Models/PurchaseItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'purchase_price',
        'subtotal',
        'discount_percent',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'total',
        'batch_no',
        'expiry_date',
        'notes',
        'received_quantity',
        'returned_quantity',
        'damaged_quantity'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'returned_quantity' => 'decimal:2',
        'damaged_quantity' => 'decimal:2',
        'expiry_date' => 'date'
    ];

    protected $attributes = [
        'discount_percent' => 0,
        'discount_amount' => 0,
        'tax_percent' => 0,
        'tax_amount' => 0,
        'received_quantity' => 0,
        'returned_quantity' => 0,
        'damaged_quantity' => 0
    ];

    // Relationships
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getFormattedPurchasePriceAttribute(): string
    {
        return number_format((float) $this->purchase_price, 2, '.', '');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return number_format((float) $this->subtotal, 2, '.', '');
    }

    public function getFormattedDiscountAmountAttribute(): string
    {
        return number_format((float) $this->discount_amount, 2, '.', '');
    }

    public function getFormattedTaxAmountAttribute(): string
    {
        return number_format((float) $this->tax_amount, 2, '.', '');
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format((float) $this->total, 2, '.', '');
    }

    public function getRemainingQuantityAttribute(): float
    {
        return $this->quantity - ($this->received_quantity + $this->returned_quantity + $this->damaged_quantity);
    }

    public function getReceivedPercentageAttribute(): float
    {
        if ($this->quantity > 0) {
            return round(($this->received_quantity / $this->quantity) * 100, 2);
        }
        return 0;
    }

    // Mutators with auto-calculation
    protected function recalculateTotals()
    {
        // Calculate subtotal
        $this->subtotal = $this->quantity * $this->purchase_price;
        
        // Calculate discount amount
        $this->discount_amount = ($this->subtotal * $this->discount_percent) / 100;
        
        // Calculate tax amount (on amount after discount)
        $taxableAmount = $this->subtotal - $this->discount_amount;
        $this->tax_amount = ($taxableAmount * $this->tax_percent) / 100;
        
        // Calculate total
        $this->total = $this->subtotal - $this->discount_amount + $this->tax_amount;
    }

    // Helper methods
    public function markAsReceived($quantity)
    {
        $this->received_quantity += $quantity;
        $this->save();
        return $this;
    }

    public function markAsReturned($quantity)
    {
        $this->returned_quantity += $quantity;
        $this->save();
        return $this;
    }

    public function markAsDamaged($quantity)
    {
        $this->damaged_quantity += $quantity;
        $this->save();
        return $this;
    }

    // Scopes
    public function scopeFullyReceived($query)
    {
        return $query->whereRaw('received_quantity >= quantity');
    }

    public function scopePartiallyReceived($query)
    {
        return $query->whereRaw('received_quantity < quantity AND received_quantity > 0');
    }

    public function scopeNotReceived($query)
    {
        return $query->where('received_quantity', 0);
    }

    public function scopeWithReturns($query)
    {
        return $query->where('returned_quantity', '>', 0);
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $item->recalculateTotals();
        });

        static::updating(function ($item) {
            if ($item->isDirty('quantity') || $item->isDirty('purchase_price') || 
                $item->isDirty('discount_percent') || $item->isDirty('tax_percent')) {
                $item->recalculateTotals();
            }
        });
    }
}