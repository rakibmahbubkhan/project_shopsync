<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        return response()->json(ExpenseCategory::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:expense_categories,name',
            'description' => 'nullable|string'
        ]);

        $category = ExpenseCategory::create($validated);
        return response()->json($category, 201);
    }

    public function show(ExpenseCategory $expenseCategory)
    {
        return response()->json($expenseCategory);
    }

    public function update(Request $request, $id)
    {
        $category = ExpenseCategory::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|unique:expense_categories,name,' . $id,
            'description' => 'nullable|string'
        ]);

        $category->update($validated);
        return response()->json($category);
    }

    public function destroy($id)
    {
        ExpenseCategory::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}