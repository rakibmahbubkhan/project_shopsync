<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'entry_date',
        'reference_type',
        'reference_id',
        'description',
        'user_id'
    ];

    /**
     * Get the financial lines (debits/credits) for this entry.
     */
    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }
}