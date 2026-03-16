<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\StockAdjustment;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id'   => 'required|exists:products,id',
            'type'         => 'required|in:addition,subtraction',
            'quantity'     => 'required|numeric|min:0.01',
            'reason'       => 'required|string|max:255',
        ]);

        return DB::transaction(function () use ($validated) {
            $adjustment = StockAdjustment::create([
                ...$validated,
                'user_id' => Auth::id(),
                'date'    => now(),
            ]);

            $cost = $this->stockService->getAverageCost($validated['product_id'], $validated['warehouse_id']);

            if ($validated['type'] === 'addition') {
                $this->stockService->increaseStock(
                    $validated['product_id'], $validated['warehouse_id'], $validated['quantity'], 
                    $cost, 'adjustment', $adjustment->id, Auth::id()
                );
            } else {
                $this->stockService->decreaseStock(
                    $validated['product_id'], $validated['warehouse_id'], $validated['quantity'], 
                    $cost, 'adjustment', $adjustment->id, Auth::id()
                );
            }

            return response()->json($adjustment, 201);
        });
    }
}