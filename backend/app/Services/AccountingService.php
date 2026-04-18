<?php

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class AccountingService
{
    /**
     * Create a balanced Journal Entry with Debit and Credit lines.
     * 
     * @param string $date Entry date
     * @param string $description Description of the entry
     * @param array $lines Array of lines with account_id, debit, credit
     * @param string|null $referenceType Reference type (sale, purchase, etc.)
     * @param int|null $referenceId ID of the reference record
     * @return JournalEntry
     * @throws Exception
     */
    public function createEntry($date, $description, $entries, $referenceType, $referenceId)
    {
        return DB::transaction(function () use ($date, $description, $entries, $referenceType, $referenceId) {
            // Create journal entry
            $journalEntry = JournalEntry::create([
                'entry_date' => $date,
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_by' => Auth::id(),
                'notes' => $description
            ]);

            // Create journal entry lines
            foreach ($entries as $entry) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $entry['account_id'],
                    'debit' => $entry['debit'],
                    'credit' => $entry['credit'],
                    'description' => $description
                ]);
            }

            return $journalEntry;
        });
    }

    public function updateAccountingForPayment($purchase, $paymentAmount)
    {
        // This method is called from PurchaseController
        // You can implement additional logic here if needed
    }

    /**
     * Delete an entry (e.g., when a sale is voided or purchase is deleted).
     * Cascading delete in migrations will handle the lines.
     * 
     * @param string $referenceType Reference type (sale, purchase, etc.)
     * @param int $referenceId ID of the reference record
     * @return bool True if deleted, false if not found
     */
    public function deleteEntry(string $referenceType, int $referenceId): bool
    {
        return DB::transaction(function () use ($referenceType, $referenceId) {
            // Find the entry by its reference
            $entry = JournalEntry::where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->first();

            if ($entry) {
                // Log before deletion for audit trail
                Log::info('Journal entry deleted', [
                    'entry_id' => $entry->id,
                    'reference' => "{$referenceType}:{$referenceId}",
                    'user_id' => Auth::id()
                ]);

                // Deleting the entry will cascade delete lines if foreign key is set up correctly
                return $entry->delete();
            }

            Log::warning('Journal entry not found for deletion', [
                'reference' => "{$referenceType}:{$referenceId}"
            ]);

            return false;
        });
    }

    /**
     * Get journal entries for a specific reference.
     * 
     * @param string $referenceType
     * @param int $referenceId
     * @return JournalEntry|null
     */
    public function getEntryForReference(string $referenceType, int $referenceId): ?JournalEntry
    {
        return JournalEntry::with('lines')
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->first();
    }

    /**
     * Get all journal entries for a date range.
     * 
     * @param string $startDate Y-m-d format
     * @param string $endDate Y-m-d format
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEntriesByDateRange(string $startDate, string $endDate)
    {
        return JournalEntry::with('lines', 'user')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->orderBy('entry_date', 'desc')
            ->get();
    }

    /**
     * Get account balance for a specific account.
     * 
     * @param int $accountId
     * @param string|null $asOfDate Optional date to calculate balance up to
     * @return float
     */
    public function getAccountBalance(int $accountId, ?string $asOfDate = null): float
    {
        $query = JournalEntryLine::where('account_id', $accountId)
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id');

        if ($asOfDate) {
            $query->where('journal_entries.entry_date', '<=', $asOfDate);
        }

        $totalDebit = (float) $query->sum('debit');
        $totalCredit = (float) $query->sum('credit');

        return $totalDebit - $totalCredit;
    }

    /**
     * Reverse a journal entry (create opposite entry).
     * Useful for correcting errors or voiding transactions.
     * 
     * @param string $referenceType
     * @param int $referenceId
     * @param string $reason
     * @return JournalEntry|null
     */
    public function reverseEntry(string $referenceType, int $referenceId, string $reason = 'Void'): ?JournalEntry
    {
        return DB::transaction(function () use ($referenceType, $referenceId, $reason) {
            $originalEntry = JournalEntry::with('lines')
                ->where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->first();

            if (!$originalEntry) {
                return null;
            }

            // Create reversed lines
            $reversedLines = $originalEntry->lines->map(function ($line) {
                return [
                    'account_id' => $line->account_id,
                    'debit' => $line->credit, // Swap debit and credit
                    'credit' => $line->debit,
                ];
            })->toArray();

            // Create reversal entry
            return $this->createEntry(
                now()->toDateString(),
                "Reversal of {$originalEntry->description} - {$reason}",
                $reversedLines,
                "{$referenceType}_reversal",
                $referenceId
            );
        });
    }

    /**
     * Check if an entry exists for a reference.
     * 
     * @param string $referenceType
     * @param int $referenceId
     * @return bool
     */
    public function entryExists(string $referenceType, int $referenceId): bool
    {
        return JournalEntry::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->exists();
    }
}