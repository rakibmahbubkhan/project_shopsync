<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialReportController extends Controller
{
    /**
     * Generate a Trial Balance report.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function trialBalance(Request $request)
    {
        $start = $request->start_date;
        $end = $request->end_date;

        $accounts = Account::select(
                'accounts.id',
                'accounts.name',
                'accounts.code',
                'accounts.type',
                DB::raw('SUM(journal_entry_lines.debit) as total_debit'),
                DB::raw('SUM(journal_entry_lines.credit) as total_credit')
            )
            ->leftJoin('journal_entry_lines', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->leftJoin('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('journal_entries.entry_date', [$start, $end]);
            })
            ->groupBy('accounts.id', 'accounts.name', 'accounts.code', 'accounts.type')
            ->get()
            ->map(function ($account) {
                // Calculate net balance based on account type
                $net = $account->total_debit - $account->total_credit;
                
                return [
                    'code' => $account->code,
                    'name' => $account->name,
                    'type' => $account->type,
                    'debit' => $net > 0 ? (float) $net : 0,
                    'credit' => $net < 0 ? (float) abs($net) : 0,
                    'balance' => (float) $net,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'accounts' => $accounts,
                'summary' => [
                    'total_debit' => $accounts->sum('debit'),
                    'total_credit' => $accounts->sum('credit'),
                    'difference' => $accounts->sum('debit') - $accounts->sum('credit'),
                ]
            ],
            'message' => 'Trial balance generated successfully'
        ]);
    }

    /**
     * Generate Profit & Loss statement.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profitLoss(Request $request)
    {
        $start = $request->start_date;
        $end = $request->end_date;

        // Get revenue/income accounts
        $incomeAccounts = Account::select(
                'accounts.id',
                'accounts.name',
                'accounts.code',
                DB::raw('SUM(journal_entry_lines.credit - journal_entry_lines.debit) as balance')
            )
            ->join('journal_entry_lines', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->whereIn('accounts.type', ['income', 'revenue'])
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('journal_entries.entry_date', [$start, $end]);
            })
            ->groupBy('accounts.id', 'accounts.name', 'accounts.code')
            ->get();

        // Get expense accounts
        $expenseAccounts = Account::select(
                'accounts.id',
                'accounts.name',
                'accounts.code',
                DB::raw('SUM(journal_entry_lines.debit - journal_entry_lines.credit) as balance')
            )
            ->join('journal_entry_lines', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('accounts.type', 'expense')
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('journal_entries.entry_date', [$start, $end]);
            })
            ->groupBy('accounts.id', 'accounts.name', 'accounts.code')
            ->get();

        $totalIncome = $incomeAccounts->sum('balance');
        $totalExpenses = $expenseAccounts->sum('balance');
        $netProfit = $totalIncome - $totalExpenses;

        return response()->json([
            'success' => true,
            'data' => [
                'income' => [
                    'accounts' => $incomeAccounts,
                    'total' => (float) $totalIncome
                ],
                'expenses' => [
                    'accounts' => $expenseAccounts,
                    'total' => (float) $totalExpenses
                ],
                'summary' => [
                    'gross_profit' => (float) $totalIncome,
                    'total_expenses' => (float) $totalExpenses,
                    'net_profit' => (float) $netProfit,
                    'profit_margin' => $totalIncome > 0 ? round(($netProfit / $totalIncome) * 100, 2) : 0
                ]
            ],
            'message' => 'Profit & Loss statement generated successfully'
        ]);
    }

    /**
     * Generate Balance Sheet.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->as_of_date ?? now()->toDateString();

        // Get Asset accounts
        $assets = Account::select(
                'accounts.id',
                'accounts.name',
                'accounts.code',
                DB::raw('SUM(journal_entry_lines.debit - journal_entry_lines.credit) as balance')
            )
            ->join('journal_entry_lines', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('accounts.type', 'asset')
            ->where('journal_entries.entry_date', '<=', $asOfDate)
            ->groupBy('accounts.id', 'accounts.name', 'accounts.code')
            ->get();

        // Get Liability accounts
        $liabilities = Account::select(
                'accounts.id',
                'accounts.name',
                'accounts.code',
                DB::raw('SUM(journal_entry_lines.credit - journal_entry_lines.debit) as balance')
            )
            ->join('journal_entry_lines', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->whereIn('accounts.type', ['liability', 'liabilities'])
            ->where('journal_entries.entry_date', '<=', $asOfDate)
            ->groupBy('accounts.id', 'accounts.name', 'accounts.code')
            ->get();

        // Get Equity accounts
        $equity = Account::select(
                'accounts.id',
                'accounts.name',
                'accounts.code',
                DB::raw('SUM(journal_entry_lines.credit - journal_entry_lines.debit) as balance')
            )
            ->join('journal_entry_lines', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('accounts.type', 'equity')
            ->where('journal_entries.entry_date', '<=', $asOfDate)
            ->groupBy('accounts.id', 'accounts.name', 'accounts.code')
            ->get();

        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        $totalEquity = $equity->sum('balance');
        $balanceCheck = $totalAssets - ($totalLiabilities + $totalEquity);

        return response()->json([
            'success' => true,
            'data' => [
                'as_of_date' => $asOfDate,
                'assets' => [
                    'accounts' => $assets,
                    'total' => (float) $totalAssets
                ],
                'liabilities' => [
                    'accounts' => $liabilities,
                    'total' => (float) $totalLiabilities
                ],
                'equity' => [
                    'accounts' => $equity,
                    'total' => (float) $totalEquity
                ],
                'summary' => [
                    'total_assets' => (float) $totalAssets,
                    'total_liabilities' => (float) $totalLiabilities,
                    'total_equity' => (float) $totalEquity,
                    'liabilities_plus_equity' => (float) ($totalLiabilities + $totalEquity),
                    'balance_check' => (float) $balanceCheck,
                    'is_balanced' => abs($balanceCheck) < 0.01
                ]
            ],
            'message' => 'Balance sheet generated successfully'
        ]);
    }

    /**
     * Generate General Ledger report.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generalLedger(Request $request)
    {
        $start = $request->start_date;
        $end = $request->end_date;
        $accountId = $request->account_id;

        $query = DB::table('journal_entries')
            ->join('journal_entry_lines', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->select(
                'journal_entries.id as entry_id',
                'journal_entries.entry_date',
                'journal_entries.description as entry_description',
                'journal_entries.reference_type',
                'journal_entries.reference_id',
                'accounts.id as account_id',
                'accounts.name as account_name',
                'accounts.code as account_code',
                'journal_entry_lines.debit',
                'journal_entry_lines.credit'
            )
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('journal_entries.entry_date', [$start, $end]);
            })
            ->when($accountId, function ($query) use ($accountId) {
                $query->where('accounts.id', $accountId);
            })
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_entries.id');

        $entries = $query->get();

        // Calculate running balance
        $runningBalance = 0;
        $ledgerEntries = $entries->map(function ($entry) use (&$runningBalance) {
            $runningBalance += ($entry->debit - $entry->credit);
            return [
                'date' => $entry->entry_date,
                'entry_id' => $entry->entry_id,
                'description' => $entry->entry_description,
                'reference' => $entry->reference_type ? "{$entry->reference_type}#{$entry->reference_id}" : null,
                'account' => [
                    'id' => $entry->account_id,
                    'name' => $entry->account_name,
                    'code' => $entry->account_code,
                ],
                'debit' => (float) $entry->debit,
                'credit' => (float) $entry->credit,
                'balance' => (float) $runningBalance,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'filters' => [
                    'start_date' => $start,
                    'end_date' => $end,
                    'account_id' => $accountId,
                ],
                'entries' => $ledgerEntries,
                'summary' => [
                    'total_debit' => $entries->sum('debit'),
                    'total_credit' => $entries->sum('credit'),
                    'opening_balance' => 0, // You might want to calculate this
                    'closing_balance' => $runningBalance,
                ]
            ],
            'message' => 'General ledger generated successfully'
        ]);
    }

    /**
     * Get account balances with details.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function accountBalances(Request $request)
    {
        $asOfDate = $request->as_of_date ?? now()->toDateString();

        $accounts = Account::with(['journalEntryLines' => function ($query) use ($asOfDate) {
            $query->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
                  ->where('journal_entries.entry_date', '<=', $asOfDate);
        }])->get();

        $balances = $accounts->map(function ($account) {
            $totalDebit = $account->journalEntryLines->sum('debit');
            $totalCredit = $account->journalEntryLines->sum('credit');
            $balance = $totalDebit - $totalCredit;

            return [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'debit' => (float) $totalDebit,
                'credit' => (float) $totalCredit,
                'balance' => (float) $balance,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'as_of_date' => $asOfDate,
                'accounts' => $balances,
                'summary' => [
                    'total_debit' => $balances->sum('debit'),
                    'total_credit' => $balances->sum('credit'),
                    'total_balance' => $balances->sum('balance'),
                ]
            ],
            'message' => 'Account balances retrieved successfully'
        ]);
    }

    /**
     * Helper method to get sum by account type.
     * 
     * @param string $type
     * @param string|null $start
     * @param string|null $end
     * @return float
     */
    private function accountSum(string $type, ?string $start, ?string $end): float
    {
        return (float) DB::table('accounts')
            ->join('journal_entry_lines', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('accounts.type', $type)
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('journal_entries.entry_date', [$start, $end]);
            })
            ->sum(DB::raw('journal_entry_lines.credit - journal_entry_lines.debit'));
    }

    /**
     * Helper method to get balance by account type.
     * 
     * @param string $type
     * @param string|null $start
     * @param string|null $end
     * @return float
     */
    private function balanceByType(string $type, ?string $start, ?string $end): float
    {
        return (float) DB::table('accounts')
            ->join('journal_entry_lines', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('accounts.type', $type)
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('journal_entries.entry_date', [$start, $end]);
            })
            ->sum(DB::raw('journal_entry_lines.debit - journal_entry_lines.credit'));
    }
}