<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory; 



class Product extends Model
{

    use HasFactory, Auditable;
    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'category_id',
        'brand_id',
        'unit_id',
        'cost_price',
        'selling_price',
        'stock_quantity',
        'alert_quantity',
        'image',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'product_stocks')
                    ->withPivot('quantity', 'avg_cost', 'last_purchase_price')
                    ->withTimestamps();
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }

    public function inventoryLedger() {
        return $this->hasMany(InventoryLedger::class);
    }

}

