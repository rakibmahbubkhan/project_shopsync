<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'purchase_id',
        'supplier_id',
        'warehouse_id',
        'user_id',
        'return_date',
        'reason',
        'total_amount',
        'status',
        'notes',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'return_date' => 'datetime',
        'approved_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    protected $with = [];

    // Relationships
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function supplierCredit(): HasMany
    {
        return $this->hasMany(SupplierCredit::class);
    }

    // FIXED: Renamed to avoid naming collision with 'approved_by' column
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->whereIn('status', ['approved', 'completed']);
    }
}