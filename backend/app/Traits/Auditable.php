<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            self::logActivity($model, 'created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            self::logActivity(
                $model,
                'updated',
                $model->getOriginal(),
                $model->getChanges()
            );
        });

        static::deleted(function ($model) {
            self::logActivity($model, 'deleted', $model->getOriginal(), null);
        });
    }

    protected static function logActivity($model, $action, $oldValues, $newValues)
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
