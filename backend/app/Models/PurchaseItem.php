<?php
// app/Models/PurchaseItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    // Enable timestamps if you want created_at/updated_at
    public $timestamps = true;

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
        'notes'
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
        'expiry_date' => 'date'
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}