<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    protected $table = 'stock_transfers';

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

    // Helper method to check if transfer can be cancelled
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'draft']);
    }
}