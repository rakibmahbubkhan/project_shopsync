<?php
// app/Models/Purchase.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id',
        'warehouse_id',
        'purchase_date',
        'reference_no',
        'subtotal',
        'total_discount',
        'total_tax',
        'total_amount',
        'paid_amount',
        'payment_status',
        'status',
        'notes',
        'shipping_method',
        'shipping_cost',
        'payment_method',
        'expected_delivery_date',
        'delivered_date',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expected_delivery_date' => 'date',
        'delivered_date' => 'date',
        'subtotal' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'paid_amount' => 0,
        'total_amount' => 0,
        'subtotal' => 0,
        'total_discount' => 0,
        'total_tax' => 0,
        'shipping_cost' => 0
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOrdered($query)
    {
        return $query->where('status', 'ordered');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    public function scopePartial($query)
    {
        return $query->where('payment_status', 'partial');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('purchase_date', [$startDate, $endDate]);
    }

    // Accessors
    public function getDueAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function getPaymentProgressAttribute()
    {
        if ($this->total_amount > 0) {
            return round(($this->paid_amount / $this->total_amount) * 100, 2);
        }
        return 0;
    }

    public function getFormattedTotalAttribute(): string
    {
        return $this->total_amount ? number_format((float) $this->total_amount, 2, '.', '') : '0.00';
    }

    public function getFormattedPaidAttribute(): string
    {
        return $this->paid_amount ? number_format((float) $this->paid_amount, 2, '.', '') : '0.00';
    }

    public function getFormattedDueAttribute(): string
    {
        return $this->due_amount ? number_format((float) $this->due_amount, 2, '.', '') : '0.00';
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchase) {
            if (empty($purchase->reference_no)) {
                $purchase->reference_no = static::generateReferenceNumber();
            }
        });
    }

    // Helper methods
    protected static function generateReferenceNumber()
    {
        $prefix = 'PO-';
        $year = date('Y');
        $month = date('m');
        
        $lastPurchase = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->latest()
            ->first();

        if ($lastPurchase) {
            $lastNumber = intval(substr($lastPurchase->reference_no, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $year . $month . $newNumber;
    }

    public function updatePaymentStatus()
    {
        if ($this->paid_amount >= $this->total_amount) {
            $this->payment_status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'unpaid';
        }
        
        $this->saveQuietly();
        return $this;
    }
}