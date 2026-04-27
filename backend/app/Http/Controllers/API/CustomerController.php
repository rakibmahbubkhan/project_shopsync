<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    /**
     * List all customers for the POS dropdown.
     */
    public function index()
    {
        return response()->json(Customer::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'mobile_number' => 'required|string',
            'billing_address' => 'required|string',
            'billing_country' => 'required|string',
            'billing_city' => 'required|string',
            'shipping_address' => 'required|string',
            'shipping_country' => 'required|string',
            'shipping_city' => 'required|string',
            'contact_person' => 'nullable|string',
            'website' => 'nullable|url',
            'phone_number' => 'nullable|string',
            'tax_number' => 'nullable|string',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        $customer = Customer::create($validated);
        return response()->json($customer, 201);
    }

    /**
     * Display the specified customer.
     */
    public function show($id)
    {
        $customer = Customer::findOrFail($id);
        return response()->json($customer);
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email',
            'mobile_number' => 'sometimes|required|string',
            'billing_address' => 'sometimes|required|string',
            'billing_country' => 'sometimes|required|string',
            'billing_city' => 'sometimes|required|string',
            'shipping_address' => 'sometimes|required|string',
            'shipping_country' => 'sometimes|required|string',
            'shipping_city' => 'sometimes|required|string',
            'contact_person' => 'nullable|string',
            'website' => 'nullable|url',
            'phone_number' => 'nullable|string',
            'tax_number' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $customer->update($validated);
        return response()->json($customer);
    }

    /**
     * Update customer status (activate/deactivate).
     */
    public function updateStatus(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|string|in:active,inactive'
        ]);
        
        $customer->status = $validated['status'];
        $customer->save();
        
        return response()->json($customer);
    }

    /**
     * Remove the specified customer.
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        
        return response()->json(null, 204);
    }

    /**
     * Get customers with pending payments.
     * Checks sales that are not fully paid (payment_status is not 'paid')
     */
    public function pendingPayments()
    {
        try {
            // Fetch customers who have at least one sale that is not fully paid
            $customers = Customer::whereHas('sales', function ($query) {
                $query->where('payment_status', '!=', 'paid')
                      ->whereNotNull('payment_status');
            })->with(['sales' => function ($query) {
                $query->where('payment_status', '!=', 'paid')
                      ->whereNotNull('payment_status');
            }])->get();

            // For each customer, calculate the due amount for each sale
            foreach ($customers as $customer) {
                foreach ($customer->sales as $sale) {
                    // Since there's no paid_amount field, due amount is the total_amount
                    $sale->due_amount = floatval($sale->total_amount);
                }
            }

            return response()->json($customers);
        } catch (\Exception $e) {
            Log::error('Error in pendingPayments: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get count of customers with pending payments
     */
    public function pendingPaymentsCount()
    {
        try {
            // Count customers with sales that are not fully paid
            $count = Customer::whereHas('sales', function($query) {
                $query->where('payment_status', '!=', 'paid')
                      ->whereNotNull('payment_status');
            })->count();
            
            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            Log::error('Error in pendingPaymentsCount: ' . $e->getMessage());
            return response()->json(['count' => 0]);
        }
    }
}