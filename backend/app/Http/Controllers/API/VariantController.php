<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Variant;
use App\Models\VariantItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VariantController extends Controller
{
    /**
     * Display a listing of all variants with their items.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $variants = Variant::with('items')->orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => $variants
        ]);
    }

    /**
     * Store a newly created variant in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:variants,name',
        ]);

        $variant = Variant::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Variant created successfully',
            'data' => $variant
        ], 201);
    }

    /**
     * Display the specified variant with its items.
     *
     * @param  \App\Models\Variant  $variant
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Variant $variant)
    {
        $variant->load('items');
        return response()->json([
            'success' => true,
            'data' => $variant
        ]);
    }

    /**
     * Update the specified variant in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Variant  $variant
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Variant $variant)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('variants')->ignore($variant->id)
            ],
        ]);

        $variant->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Variant updated successfully',
            'data' => $variant
        ]);
    }

    /**
     * Remove the specified variant from storage.
     *
     * @param  \App\Models\Variant  $variant
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Variant $variant)
    {
        // Check if variant has any items
        if ($variant->items()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete variant with associated items. Delete items first.'
            ], 422);
        }

        $variant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Variant deleted successfully'
        ]);
    }

    /**
     * Store a variant item.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Variant  $variant
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeItem(Request $request, Variant $variant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $item = $variant->items()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Variant item created successfully',
            'data' => $item
        ], 201);
    }

    /**
     * Update a variant item.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Variant  $variant
     * @param  int  $itemId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateItem(Request $request, Variant $variant, $itemId)
    {
        $item = VariantItem::where('variant_id', $variant->id)->findOrFail($itemId);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $item->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Variant item updated successfully',
            'data' => $item
        ]);
    }

    /**
     * Delete a variant item.
     *
     * @param  \App\Models\Variant  $variant
     * @param  int  $itemId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyItem(Variant $variant, $itemId)
    {
        $item = VariantItem::where('variant_id', $variant->id)->findOrFail($itemId);
        
        // Check if item is used in any products
        if ($item->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete variant item with associated products.'
            ], 422);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Variant item deleted successfully'
        ]);
    }
}