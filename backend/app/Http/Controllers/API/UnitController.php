<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    /**
     * Display a listing of all units.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $units = Unit::orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => $units
        ]);
    }

    /**
     * Store a newly created unit in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'symbol' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $unit = Unit::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Unit created successfully',
            'data' => $unit
        ], 201);
    }

    /**
     * Display the specified unit.
     *
     * @param  \App\Models\Unit  $unit
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Unit $unit)
    {
        return response()->json([
            'success' => true,
            'data' => $unit
        ]);
    }

    /**
     * Update the specified unit in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Unit  $unit
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('units')->ignore($unit->id)
            ],
            'symbol' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $unit->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Unit updated successfully',
            'data' => $unit
        ]);
    }

    /**
     * Remove the specified unit from storage.
     *
     * @param  \App\Models\Unit  $unit
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Unit $unit)
    {
        // Check if unit has any products
        if ($unit->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete unit with associated products.'
            ], 422);
        }

        $unit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Unit deleted successfully'
        ]);
    }
}