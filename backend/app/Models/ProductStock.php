<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory; 



class ProductStock extends Model
{

    use HasFactory, Auditable;
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
    ];


    public function product() 
    {
    return $this->belongsTo(Product::class);
    }
}

