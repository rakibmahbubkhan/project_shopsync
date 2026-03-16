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
    public function createEntry(string $date, string $description, array $lines, ?string $referenceType = null, ?int $referenceId = null): JournalEntry
    {
        return DB::transaction(function () use ($date, $description, $lines, $referenceType, $referenceId) {
            
            // 1. Validate Balance: Sum of Debits must equal Sum of Credits
            $totalDebit = collect($lines)->sum('debit');
            $totalCredit = collect($lines)->sum('credit');

            // Use a small epsilon for floating point comparison
            if (abs($totalDebit - $totalCredit) > 0.0001) {
                throw new Exception(
                    "Journal Entry is not balanced. " .
                    "Debits: {$totalDebit}, Credits: {$totalCredit}, " .
                    "Difference: " . abs($totalDebit - $totalCredit)
                );
            }

            // 2. Check if entry already exists for this reference (prevent duplicates)
            if ($referenceType && $referenceId) {
                $existingEntry = JournalEntry::where('reference_type', $referenceType)
                    ->where('reference_id', $referenceId)
                    ->first();
                
                if ($existingEntry) {
                    throw new Exception(
                        "Journal entry already exists for {$referenceType} #{$referenceId}"
                    );
                }
            }

            // 3. Create the Header Entry
            $entry = JournalEntry::create([
                'entry_date'     => $date,
                'description'    => $description,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'user_id'        => Auth::id(),
            ]);

            // 4. Create individual lines
            foreach ($lines as $index => $line) {
                // Validate each line has required fields
                if (!isset($line['account_id'])) {
                    throw new Exception("Line #{$index}: account_id is required");
                }

                // Ensure at least one of debit or credit is set
                $debit = $line['debit'] ?? 0;
                $credit = $line['credit'] ?? 0;
                
                if ($debit == 0 && $credit == 0) {
                    throw new Exception("Line #{$index}: Either debit or credit must be greater than zero");
                }

                if ($debit > 0 && $credit > 0) {
                    throw new Exception("Line #{$index}: Cannot have both debit and credit on same line");
                }

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $line['account_id'],
                    'debit'            => $debit,
                    'credit'           => $credit,
                ]);
            }

            // Log the entry creation for audit trail
            Log::info('Journal entry created', [
                'entry_id' => $entry->id,
                'reference' => $referenceType ? "{$referenceType}:{$referenceId}" : 'none',
                'total' => $totalDebit,
                'user_id' => Auth::id()
            ]);

            return $entry->load('lines');
        });
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