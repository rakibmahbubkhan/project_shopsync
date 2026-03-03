<?php

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    public function createEntry($date, $description, $lines, $referenceType = null, $referenceId = null)
    {
        return DB::transaction(function () use (
            $date,
            $description,
            $lines,
            $referenceType,
            $referenceId
        ) {

            $totalDebit = collect($lines)->sum('debit');
            $totalCredit = collect($lines)->sum('credit');

            if ($totalDebit != $totalCredit) {
                throw new \Exception('Journal entry not balanced.');
            }

            $entry = JournalEntry::create([
                'entry_date' => $date,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
                'user_id' => auth()->id(),
            ]);

            foreach ($lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                ]);
            }

            return $entry;
        });
    }

    public function deleteEntry(string $referenceType, int $referenceId): bool
    {
        return DB::transaction(function () use ($referenceType, $referenceId) {
            // Find the entry by its reference (e.g., 'sale', 10)
            $entry = JournalEntry::where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->first();

            if ($entry) {
                // Deleting the entry will cascade delete lines in the database
                return $entry->delete();
            }

            return false;
        });
    }
    
}
