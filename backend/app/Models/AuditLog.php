<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

    class AuditLog extends Model
    {
        protected $fillable = [
            'user_id',
            'action',
            'auditable_type', // Add this
            'auditable_id',   // Add this
            'old_values',
            'new_values',
            'ip_address',
            'user_agent',
            'created_at',
            'updated_at'
        ];

        public function auditable()
        {
            return $this->morphTo();
        }

        protected $casts = [
            'old_values' => 'array',
            'new_values' => 'array',
        ];

        public function user()
        {
            return $this->belongsTo(User::class);
        }
    }


