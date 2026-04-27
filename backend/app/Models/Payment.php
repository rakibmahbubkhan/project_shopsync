<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table = 'payments';
    
    protected $fillable = [
        'sale_id',
        'customer_id',
        'amount',
        'payment_method',
        'payment_status',
        'reference_number',
        'notes',
        'processed_by',
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Get the sale that this payment belongs to.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
    
    /**
     * Get the customer that this payment belongs to.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    
    /**
     * Get the user who processed this payment.
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}