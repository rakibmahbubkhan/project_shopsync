<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Record a payment for a sale
     */
    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,card,bank_transfer,mobile_banking',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);
        
        try {
            $sale = Sale::findOrFail($request->sale_id);
            
            // Check if amount is valid
            $dueAmount = $sale->total_amount - ($sale->paid_amount ?? 0);
            if ($request->amount > $dueAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount cannot exceed due amount of ' . $dueAmount
                ], 422);
            }
            
            DB::transaction(function () use ($request, $sale) {
                // Create payment record
                $payment = Payment::create([
                    'sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method,
                    'payment_status' => 'completed',
                    'reference_number' => $request->reference_number,
                    'notes' => $request->notes,
                    'processed_by' => Auth::id(),
                ]);
                
                // Update sale paid amount
                $sale->paid_amount = ($sale->paid_amount ?? 0) + $request->amount;
                $sale->save();
                
                // Update payment status
                $sale->updatePaymentStatus();
            });
            
            // Reload sale with relationships
            $sale->load(['customer', 'payments']);
            
            $dueAmount = $sale->total_amount - ($sale->paid_amount ?? 0);
            
            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'sale' => $sale,
                'due_amount' => max(0, $dueAmount),
                'payment_status' => $sale->payment_status
            ]);
            
        } catch (\Exception $e) {
            Log::error('Payment failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Record bulk payment for multiple sales (for a customer)
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sale_ids' => 'required|array',
            'sale_ids.*' => 'exists:sales,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,card,bank_transfer,mobile_banking',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);
        
        try {
            $sales = Sale::whereIn('id', $request->sale_ids)
                ->where('customer_id', $request->customer_id)
                ->get();
            
            $totalDue = $sales->sum(function($sale) {
                return $sale->total_amount - ($sale->paid_amount ?? 0);
            });
            
            if ($request->amount > $totalDue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount cannot exceed total due amount of ' . $totalDue
                ], 422);
            }
            
            DB::transaction(function () use ($request, $sales) {
                $remainingAmount = $request->amount;
                
                // Process payments from oldest to newest sale
                foreach ($sales->sortBy('sale_date') as $sale) {
                    if ($remainingAmount <= 0) break;
                    
                    $dueAmount = $sale->total_amount - ($sale->paid_amount ?? 0);
                    $paymentAmount = min($dueAmount, $remainingAmount);
                    
                    if ($paymentAmount > 0) {
                        // Create payment record
                        Payment::create([
                            'sale_id' => $sale->id,
                            'customer_id' => $request->customer_id,
                            'amount' => $paymentAmount,
                            'payment_method' => $request->payment_method,
                            'payment_status' => 'completed',
                            'reference_number' => $request->reference_number,
                            'notes' => $request->notes,
                            'processed_by' => Auth::id(),
                        ]);
                        
                        // Update sale paid amount
                        $sale->paid_amount = ($sale->paid_amount ?? 0) + $paymentAmount;
                        $sale->save();
                        
                        // Update payment status
                        $sale->updatePaymentStatus();
                        
                        $remainingAmount -= $paymentAmount;
                    }
                }
            });
            
            // Reload sales with relationships
            $updatedSales = Sale::whereIn('id', $request->sale_ids)
                ->with(['customer', 'payments'])
                ->get();
            
            $remainingDue = $updatedSales->sum(function($sale) {
                return $sale->total_amount - ($sale->paid_amount ?? 0);
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Bulk payment recorded successfully',
                'sales' => $updatedSales,
                'remaining_due' => max(0, $remainingDue)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Bulk payment failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Bulk payment failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get payment history for a sale
     */
    public function history($saleId)
    {
        $sale = Sale::findOrFail($saleId);
        $payments = $sale->payments()->with('processedBy')->latest()->get();
        
        return response()->json([
            'success' => true,
            'payments' => $payments,
            'total_paid' => $sale->paid_amount ?? 0,
            'due_amount' => max(0, $sale->total_amount - ($sale->paid_amount ?? 0))
        ]);
    }
}