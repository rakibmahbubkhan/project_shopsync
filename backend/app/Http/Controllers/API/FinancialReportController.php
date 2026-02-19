<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class FinancialReportController extends Controller
{

    public function trialBalance(Request $request)
    {
        $start = $request->start_date;
        $end = $request->end_date;

        $accounts = Account::select(
                'accounts.id',
                'accounts.name',
                'accounts.type',
                DB::raw('SUM(journal_entry_lines.debit) as total_debit'),
                DB::raw('SUM(journal_entry_lines.credit) as total_credit')
            )
            ->leftJoin('journal_entry_lines', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->leftJoin('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('journal_entries.entry_date', [$start, $end]);
            })
            ->groupBy('accounts.id', 'accounts.name', 'accounts.type')
            ->get();

        return response()->json([
            'accounts' => $accounts,
            'total_debit' => $accounts->sum('total_debit'),
            'total_credit' => $accounts->sum('total_credit'),
        ]);
    }

    public function profitLoss(Request $request)
    {
        $start = $request->start_date;
        $end = $request->end_date;

        $income = $this->accountSum('income', $start, $end);
        $expenses = $this->accountSum('expense', $start, $end);

        return response()->json([
            'total_income' => $income,
            'total_expenses' => $expenses,
            'net_profit' => $income - $expenses,
        ]);
    }

    private function accountSum($type, $start, $end)
    {
        return DB::table('accounts')
            ->join('journal_entry_lines', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('accounts.type', $type)
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('journal_entries.entry_date', [$start, $end]);
            })
            ->sum(DB::raw('journal_entry_lines.credit - journal_entry_lines.debit'));
    }

    public function balanceSheet(Request $request)
    {
        $start = $request->start_date;
        $end = $request->end_date;

        $assets = $this->balanceByType('asset', $start, $end);
        $liabilities = $this->balanceByType('liability', $start, $end);
        $equity = $this->balanceByType('equity', $start, $end);

        return response()->json([
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'balance_check' => $assets - ($liabilities + $equity)
        ]);
    }

    private function balanceByType($type, $start, $end)
    {
        return DB::table('accounts')
            ->join('journal_entry_lines', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('accounts.type', $type)
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('journal_entries.entry_date', [$start, $end]);
            })
            ->sum(DB::raw('journal_entry_lines.debit - journal_entry_lines.credit'));
    }



}
