<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\PurchaseReturnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PurchaseReturnController extends Controller
{
    protected PurchaseReturnService $returnService;

    public function __construct(PurchaseReturnService $returnService)
    {
        $this->returnService = $returnService;
    }

    /**
     * List all purchase returns
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
            Log::error('Failed to load purchase returns', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to load returns',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single return details
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
     * Search purchases for return
     */
    public function searchPurchases(Request $request)
    {
        try {
            $search = $request->search;

            if (!$search || strlen(trim($search)) < 2) {
                return response()->json([]);
            }

            $results = $this->returnService->searchPurchasesForReturn($search);

            return response()->json($results);

        } catch (\Exception $e) {
            Log::error('Failed to search purchases', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to search purchases'], 500);
        }
    }

    /**
     * Process a new purchase return
     */
    public function store(Request $request)
    {
        try {
            Log::info('Purchase return store started', [
                'purchase_id' => $request->purchase_id,
                'items_count' => count($request->items ?? []),
                'user_id' => auth()->id()
            ]);

            $validated = $request->validate([
                'purchase_id' => 'required|exists:purchases,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'reason' => 'required|string|max:500',
                'notes' => 'nullable|string|max:1000',
            ]);

            $result = $this->returnService->processReturn($validated);

            Log::info('Purchase return completed', [
                'return_id' => $result['id'],
                'amount' => $result['total_amount']
            ]);

            return response()->json([
                'message' => 'Purchase return processed successfully',
                'data' => $result
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Purchase return failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
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
            return response()->json(['message' => 'Return approved successfully']);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Reject a pending return
     */
    public function reject($id)
    {
        try {
            $this->returnService->rejectReturn($id);
            return response()->json(['message' => 'Return rejected']);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Get statistics
     */
    public function stats()
    {
        try {
            $stats = $this->returnService->getStats();
            return response()->json(['data' => $stats]);

        } catch (\Exception $e) {
            Log::error('Failed to load stats', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to load stats',
                'data' => ['total' => 0, 'pending' => 0, 'approved' => 0, 'completed' => 0, 'rejected' => 0, 'totalCredited' => 0]
            ]);
        }
    }
}