<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Refund;
use App\Services\StockService;
use App\Services\ReturnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class ReturnController extends Controller
{
   protected ReturnService $returnService;

    public function __construct(ReturnService $returnService)
    {
        $this->returnService = $returnService;
    }

    /**
     * List all returns with filters
     */
    public function index(Request $request)
    {
        try {
            $filters = [
                'search' => $request->search,
                'status' => $request->status,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'sort_by' => $request->sort_by,
            ];

            $returns = $this->returnService->getReturns($filters, $request->per_page ?? 15);

            return response()->json($returns);

        } catch (\Exception $e) {
            Log::error('Failed to load returns list', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to load returns'], 500);
        }
    }

    /**
     * Show a specific return
     */
    public function show($id)
    {
        try {
            $return = $this->returnService->getReturn($id);

            if (!$return) {
                return response()->json(['message' => 'Return not found'], 404);
            }

            return response()->json(['data' => $return]);

        } catch (\Exception $e) {
            Log::error('Failed to load return details', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to load return details'], 500);
        }
    }

    /**
     * Search sales for return processing
     */
    public function searchSales(Request $request)
    {
        try {
            $search = $request->search;

            if (!$search || strlen(trim($search)) < 2) {
                return response()->json([]);
            }

            $results = $this->returnService->searchSalesForReturn($search);

            return response()->json($results);

        } catch (\Exception $e) {
            Log::error('Failed to search sales', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to search sales'], 500);
        }
    }

    /**
     * Process a new return
     */
    public function store(Request $request)
    {
        try {
            Log::info('Return store started', [
                'sale_id' => $request->sale_id,
                'items_count' => count($request->items ?? []),
                'user_id' => auth()->id()
            ]);

            $validated = $request->validate([
                'sale_id' => 'required|exists:sales,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'reason' => 'required|string|max:500',
                'notes' => 'nullable|string|max:1000',
                'refund_method' => 'required|string|in:cash,card,bank_transfer,mobile_banking',
            ]);

            // Use ReturnService to process (uses DB facade only, no Eloquent recursion)
            $result = $this->returnService->processReturn($validated);

            Log::info('Return completed successfully', [
                'return_id' => $result['id'],
                'amount' => $result['total_amount']
            ]);

            return response()->json([
                'message' => 'Return processed successfully',
                'data' => $result
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Return validation failed', [
                'errors' => $e->errors(),
                'input' => $request->except(['items'])
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Return processing failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a pending return
     */
    public function approve($id)
    {
        try {
            $this->returnService->approveReturn($id);

            return response()->json([
                'message' => 'Return approved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Return approval failed', [
                'return_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }


    /**
     * Reject a return
     */
    public function reject($id)
    {
        try {
            $this->returnService->rejectReturn($id);

            return response()->json([
                'message' => 'Return rejected'
            ]);

        } catch (\Exception $e) {
            Log::error('Return rejection failed', [
                'return_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function stats()
    {
        try {
            $stats = $this->returnService->getStats();

            return response()->json(['data' => $stats]);

        } catch (\Exception $e) {
            Log::error('Failed to load return stats', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to load stats'], 500);
        }
    }

    private function getPaymentAccountId(string $paymentMethod): int
    {
        return match($paymentMethod) {
            'cash' => config('accounts.cash', 1),
            'card' => config('accounts.bank', 7),
            'bank_transfer' => config('accounts.bank', 7),
            'mobile_banking' => config('accounts.mobile_wallet', 8),
            default => config('accounts.accounts_receivable', 9),
        };
    }
}