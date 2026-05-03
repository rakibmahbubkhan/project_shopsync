<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SaleReturn extends Model
{
    protected $fillable = [
        'sale_id',
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

    /**
     * IMPORTANT: Prevent recursive relationship loading
     */
    protected $with = [];
    protected $hidden = [];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // public function items(): HasMany
    // {
    //     return $this->hasMany(SaleReturnItem::class);
    // }

    public function items()
    {
        return $this->hasMany(SaleReturnItem::class, 'sale_return_id');
    }

    // public function refund(): HasMany
    // {
    //     return $this->hasMany(Refund::class);
    // }

    public function refund(): HasOne
{
    return $this->hasOne(Refund::class);
}

    /**
     * FIXED: Renamed from approvedBy() to approver()
     * to avoid naming collision with the 'approved_by' database column
     */
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

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helper Methods
    public function approve(int $userId): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now()
        ]);
    }

    public function complete(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}