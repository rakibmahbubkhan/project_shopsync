<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            static::logActivity($model, 'created');
        });

        static::updated(function ($model) {
            static::logActivity($model, 'updated');
        });

        static::deleted(function ($model) {
            static::logActivity($model, 'deleted');
        });
    }

    protected static function logActivity($model, $action)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'old_values' => json_encode($model->getOriginal()),
            'new_values' => json_encode($model->getChanges()),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'auditable_type' => get_class($model), 
            'auditable_id' => $model->id,
            'updated_at' => now(),
            'created_at' => now(),
        ]);
    }
}