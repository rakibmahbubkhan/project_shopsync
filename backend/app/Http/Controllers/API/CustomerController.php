<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * List all customers for the POS dropdown.
     */
    public function index()
    {
        // We use latest() to show newly registered customers first
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
            // Optional fields
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
}