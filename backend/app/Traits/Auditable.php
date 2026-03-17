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
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'action' => $action,
            'old_values' => $action === 'updated' ? json_encode($model->getOriginal()) : null,
            'new_values' => $action !== 'deleted' ? json_encode($model->getAttributes()) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}