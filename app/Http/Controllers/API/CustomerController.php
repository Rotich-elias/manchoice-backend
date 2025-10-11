<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers
     */
    public function index(Request $request): JsonResponse
    {
        $query = Customer::with(['loans', 'payments']);

        // Search by name, phone, email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $customers = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'required|string|unique:customers,phone',
            'id_number' => 'nullable|string|unique:customers,id_number',
            'address' => 'nullable|string',
            'business_name' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'data' => $customer
        ], 201);
    }

    /**
     * Display the specified customer
     */
    public function show(Customer $customer): JsonResponse
    {
        $customer->load(['loans.payments', 'payments']);

        return response()->json([
            'success' => true,
            'data' => $customer
        ]);
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone' => 'sometimes|required|string|unique:customers,phone,' . $customer->id,
            'id_number' => 'nullable|string|unique:customers,id_number,' . $customer->id,
            'address' => 'nullable|string',
            'business_name' => 'nullable|string|max:255',
            'status' => 'sometimes|in:active,inactive,blacklisted',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully',
            'data' => $customer
        ]);
    }

    /**
     * Remove the specified customer
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully'
        ]);
    }

    /**
     * Get customer statistics
     */
    public function stats(Customer $customer): JsonResponse
    {
        $stats = [
            'total_loans' => $customer->loans()->count(),
            'active_loans' => $customer->activeLoans()->count(),
            'total_borrowed' => $customer->total_borrowed,
            'total_paid' => $customer->total_paid,
            'outstanding_balance' => $customer->outstandingBalance(),
            'credit_limit' => $customer->credit_limit,
            'available_credit' => max(0, $customer->credit_limit - $customer->outstandingBalance()),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
