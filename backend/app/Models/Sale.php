<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'customer_id',
        'created_by',
        'total_amount',
        'discount',
        'tax',
        'payment_method',
        'payment_status',
        'sale_date'
    ];

    protected $casts = [
        'sale_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}

