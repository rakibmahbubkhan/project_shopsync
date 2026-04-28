<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;


class Sale extends Model
{
    protected $table = 'sales';
    protected $with = [];
    
    protected $fillable = [
        'customer_id',
        'warehouse_id',
        'created_by',
        'sale_date',
        'payment_method',
        'payment_status',
        'discount',
        'tax',
        'total_amount',
        'paid_amount',
        'total_cogs',
        'gross_profit',
    ];
    
    protected $casts = [
        'sale_date' => 'datetime',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'total_cogs' => 'decimal:2',
        'gross_profit' => 'decimal:2',
    ];
    
    public $timestamps = true;
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
            $model->updated_at = null;
        });
        
        static::updating(function ($model) {
            $model->updated_at = $model->freshTimestamp();
        });
    }

    public function setUpdatedAt($value)
    {
        if ($this->exists) {
            $this->{static::UPDATED_AT} = $value;
        }
        return $this;
    }
    
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
    
    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }
    
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
    
    /**
     * Get the due amount for this sale
     */
    public function getDueAmountAttribute()
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }
    
    /**
 * Update payment status based on paid amount
 */
public function updatePaymentStatus()
{
    if ($this->paid_amount >= $this->total_amount) {
        $this->payment_status = 'paid';
    } elseif ($this->paid_amount > 0) {
        $this->payment_status = 'partial';
    } else {
        $this->payment_status = 'unpaid';
    }
    $this->saveQuietly(); // Save without triggering events
}
    
    /**
     * Record a payment
     */
    public function recordPayment($amount, $paymentMethod, $processedBy, $referenceNumber = null, $notes = null)
    {
        if ($amount <= 0) {
            throw new \Exception('Payment amount must be greater than zero');
        }
        
        if ($amount > $this->due_amount) {
            throw new \Exception('Payment amount exceeds due amount');
        }
        
        DB::transaction(function () use ($amount, $paymentMethod, $processedBy, $referenceNumber, $notes) {
            // Create payment record
            $payment = Payment::create([
                'sale_id' => $this->id,
                'customer_id' => $this->customer_id,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payment_status' => 'completed',
                'reference_number' => $referenceNumber,
                'notes' => $notes,
                'processed_by' => $processedBy,
            ]);
            
            // Update sale paid amount
            $this->paid_amount += $amount;
            $this->save();
            
            // Update payment status
            $this->updatePaymentStatus();
        });
        
        return true;
    }
}