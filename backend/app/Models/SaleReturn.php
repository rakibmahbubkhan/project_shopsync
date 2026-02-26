<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    //

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'refund_amount',
        'cost_price',
        'profit_reversed',
        'processed_by'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function refund()
    {
        return $this->hasOne(Refund::class);
    }

}



