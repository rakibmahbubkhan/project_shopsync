<?php
// app/Models/Purchase.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id',
        'warehouse_id',
        'purchase_date',
        'reference_no',
        'total_amount',
        'paid_amount',
        'payment_status',
        'status', // ordered, received, pending
        'created_by'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'paid_amount' => 0,
        'total_amount' => 0
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

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
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

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total_amount, 2);
    }

    public function getFormattedPaidAttribute()
    {
        return number_format($this->paid_amount, 2);
    }

    public function getFormattedDueAttribute()
    {
        return number_format($this->due_amount, 2);
    }

    // Mutators
    public function setPurchaseDateAttribute($value)
    {
        $this->attributes['purchase_date'] = $value;
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

        static::updating(function ($purchase) {
            // Update payment status based on paid amount
            if ($purchase->paid_amount >= $purchase->total_amount) {
                $purchase->payment_status = 'paid';
            } elseif ($purchase->paid_amount > 0) {
                $purchase->payment_status = 'partial';
            } else {
                $purchase->payment_status = 'unpaid';
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

    public function markAsReceived()
    {
        $this->status = 'received';
        $this->saveQuietly();
        
        // Update stock for each item
        foreach ($this->items as $item) {
            // Create stock log or update inventory
            StockLog::create([
                'product_id' => $item->product_id,
                'warehouse_id' => $this->warehouse_id,
                'reference_type' => 'purchase',
                'reference_id' => $this->id,
                'type' => 'in',
                'quantity' => $item->quantity,
                'created_by' => auth()->id(),
            ]);
        }
        
        return $this;
    }
}