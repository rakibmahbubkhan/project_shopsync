<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    protected $table = 'stock_logs';
    
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'reference_type',
        'reference_id',
        'type',
        'quantity',
        'old_quantity',
        'new_quantity',
        'cost_price',
        'created_by',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'old_quantity' => 'decimal:2',
        'new_quantity' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors
    public function getFormattedQuantityAttribute($value): string
    {
        return number_format($value, 2);
    }

    public function getFormattedOldQuantityAttribute($value): string
    {
        return number_format($value, 2);
    }

    public function getFormattedNewQuantityAttribute($value): string
    {
        return number_format($value, 2);
    }

    public function getFormattedCostPriceAttribute($value): string
    {
        return number_format($value, 2);
    }

    // Scopes
    public function scopeIncoming($query)
    {
        return $query->where('type', 'in');
    }

    public function scopeOutgoing($query)
    {
        return $query->where('type', 'out');
    }

    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeByReference($query, string $type, int $id)
    {
        return $query->where('reference_type', $type)->where('reference_id', $id);
    }

    public function scopeDateRange($query, $fromDate, $toDate)
    {
        return $query->whereBetween('created_at', [$fromDate, $toDate]);
    }
}