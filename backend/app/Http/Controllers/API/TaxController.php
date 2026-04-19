<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaxController extends Controller
{
    /**
     * Display a listing of all taxes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $taxes = Tax::orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => $taxes
        ]);
    }

    /**
     * Store a newly created tax in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:taxes,name',
            'percentage' => 'required|numeric|min:0|max:100',
        ]);

        $tax = Tax::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tax created successfully',
            'data' => $tax
        ], 201);
    }

    /**
     * Display the specified tax.
     *
     * @param  \App\Models\Tax  $tax
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Tax $tax)
    {
        return response()->json([
            'success' => true,
            'data' => $tax
        ]);
    }

    /**
     * Update the specified tax in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tax  $tax
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Tax $tax)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('taxes')->ignore($tax->id)
            ],
            'percentage' => 'required|numeric|min:0|max:100',
        ]);

        $tax->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tax updated successfully',
            'data' => $tax
        ]);
    }

    /**
     * Remove the specified tax from storage.
     *
     * @param  \App\Models\Tax  $tax
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Tax $tax)
    {
        // Check if tax has any products
        if ($tax->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete tax with associated products.'
            ], 422);
        }

        $tax->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tax deleted successfully'
        ]);
    }
}