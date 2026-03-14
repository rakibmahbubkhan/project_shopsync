<?php
// app/Http/Resources/UserResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            
            // Include email verification status
            'email_verified' => !is_null($this->email_verified_at),
            'email_verified_at' => $this->email_verified_at,
            
            // Role and permissions
            'role' => $this->whenLoaded('role', function() {
                return [
                    'id' => $this->role->id,
                    'name' => $this->role->name,
                    'permissions' => $this->role->permissions->pluck('name'),
                ];
            }),
            
            'role_id' => $this->role_id,
            
            // Include permissions for frontend authorization
            'permissions' => $this->whenLoaded('role', function() {
                return $this->role->permissions->pluck('name');
            }),
            
            // Counts of related records
            'counts' => [
                'purchases_created' => $this->whenLoaded('purchases', fn() => $this->purchases->count()),
                'sales_created' => $this->whenLoaded('sales', fn() => $this->sales->count()),
            ],
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Formatted data
            'formatted' => [
                'name' => $this->name,
                'email' => $this->email,
                'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
                'email_verified_at' => $this->email_verified_at ? $this->email_verified_at->format('Y-m-d H:i:s') : null,
            ]
        ];
    }
}