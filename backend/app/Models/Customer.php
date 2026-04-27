<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'contact_person',
        'website',
        'mobile_number',
        'phone_number',
        'tax_number',
        'billing_address',
        'billing_country',
        'billing_city',
        'shipping_address',
        'shipping_country',
        'shipping_city',
        'description',
        'logo',
        'status'
    ];
    
    protected $attributes = [
        'status' => 'active',  
    ];
    
    // Add this relationship
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}