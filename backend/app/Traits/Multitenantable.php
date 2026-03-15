<?php

namespace App\Traits;
use Illuminate\Support\Facades\Auth;


use Illuminate\Database\Eloquent\Builder;

trait Multitenantable
{
    protected static function bootMultitenantable()
    {
        if (Auth::check()) {
            // Automatically filter every 'GET' request by the user's business
            static::addGlobalScope('business_id', function (Builder $builder) {
                $builder->where('business_id', Auth::user()->business_id);
            });

            // Automatically set 'business_id' on every 'CREATE' request
            static::creating(function ($model) {
                $model->business_id = Auth::user()->business_id;
            });
        }
    }
}