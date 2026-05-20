<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        // Eager load the category relationship
        return response()->json(Expense::with('category')->orderBy('expense_date', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'note' => 'nullable|string'
        ]);

        $expense = Expense::create($validated);
        return response()->json($expense->load('category'), 201);
    }

    public function show($id)
    {
        return response()->json(Expense::with('category')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'note' => 'nullable|string'
        ]);

        $expense->update($validated);
        return response()->json($expense->load('category'));
    }

    public function destroy($id)
    {
        Expense::findOrFail($id)->delete();
        return response()->json(['message' => 'Expense deleted successfully']);
    }
}