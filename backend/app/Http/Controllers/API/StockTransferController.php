<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockTransferController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index()
    {
        return response()->json(StockTransfer::with(['fromWarehouse', 'toWarehouse', 'user'])->latest()->paginate(15));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id'   => 'required|exists:warehouses,id|different:from_warehouse_id',
            'transfer_date'     => 'required|date',
            'items'             => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'notes'             => 'nullable|string'
        ]);

        return DB::transaction(function () use ($validated) {
            $transfer = StockTransfer::create([
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_warehouse_id'   => $validated['to_warehouse_id'],
                'transfer_date'     => $validated['transfer_date'],
                'reference_no'      => 'TRF-' . time(),
                'status'            => 'completed',
                'notes'             => $validated['notes'],
                'user_id'           => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                // 1. Record the transfer item
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id'        => $item['product_id'],
                    'quantity'          => $item['quantity'],
                ]);

                // 2. Get current average cost for the transfer record
                $cost = $this->stockService->getAverageCost($item['product_id'], $validated['from_warehouse_id']);

                // 3. Decrease stock from Source
                $this->stockService->decreaseStock(
                    $item['product_id'],
                    $validated['from_warehouse_id'],
                    $item['quantity'],
                    $cost,
                    'transfer_out',
                    $transfer->id,
                    Auth::id()
                );

                // 4. Increase stock at Destination
                $this->stockService->increaseStock(
                    $item['product_id'],
                    $validated['to_warehouse_id'],
                    $item['quantity'],
                    $cost,
                    'transfer_in',
                    $transfer->id,
                    Auth::id()
                );
            }

            return response()->json($transfer->load('items'), 201);
        });
    }
}