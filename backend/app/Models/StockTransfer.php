<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class StockTransfer extends Model
{
    use Auditable;

    protected $fillable = [
        'from_warehouse_id', 
        'to_warehouse_id', 
        'reference_no', 
        'transfer_date', 
        'total_items',
        'total_cost',
        'status', 
        'notes',
        'user_id'
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'total_items' => 'integer',
        'total_cost' => 'decimal:2'
    ];

    // Relationships
    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Helper Methods
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'draft']);
    }

    public function updateTotals(): void
    {
        $this->total_items = $this->items()->count();
        $this->total_cost = $this->items()->sum('total_cost');
        $this->saveQuietly();
    }

    // Boot method for auto-generating reference number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transfer) {
            if (empty($transfer->reference_no)) {
                $transfer->reference_no = 'TRF-' . date('Ymd') . '-' . str_pad(static::max('id') + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }
}