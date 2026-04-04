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
            'phone' => 'required|string|unique:customers',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);
        return response()->json($customer, 201);
    }
}