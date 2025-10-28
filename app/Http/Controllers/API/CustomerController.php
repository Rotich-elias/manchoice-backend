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
        // Filter customers by authenticated user
        $query = Customer::with(['loans', 'payments'])
            ->where('user_id', $request->user()->id);

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
            // Motorcycle Details
            'motorcycle_number_plate' => 'nullable|string|max:255',
            'motorcycle_chassis_number' => 'nullable|string|max:255',
            'motorcycle_model' => 'nullable|string|max:255',
            'motorcycle_type' => 'nullable|string|max:255',
            'motorcycle_engine_cc' => 'nullable|string|max:255',
            'motorcycle_colour' => 'nullable|string|max:255',
            // Next of Kin Details
            'next_of_kin_name' => 'nullable|string|max:255',
            'next_of_kin_phone' => 'nullable|string|max:255',
            'next_of_kin_relationship' => 'nullable|string|max:255',
            'next_of_kin_email' => 'nullable|email|max:255',
            'next_of_kin_passport_photo_path' => 'nullable|string',
            // Guarantor Details
            'guarantor_name' => 'nullable|string|max:255',
            'guarantor_phone' => 'nullable|string|max:255',
            'guarantor_relationship' => 'nullable|string|max:255',
            'guarantor_email' => 'nullable|email|max:255',
            'guarantor_passport_photo_path' => 'nullable|string',
            // Guarantor Motorcycle Details
            'guarantor_motorcycle_number_plate' => 'nullable|string|max:255',
            'guarantor_motorcycle_chassis_number' => 'nullable|string|max:255',
            'guarantor_motorcycle_model' => 'nullable|string|max:255',
            'guarantor_motorcycle_type' => 'nullable|string|max:255',
            'guarantor_motorcycle_engine_cc' => 'nullable|string|max:255',
            'guarantor_motorcycle_colour' => 'nullable|string|max:255',
            // Terms & Conditions
            'accepted_terms' => 'required|boolean|accepted',
        ]);

        // Associate customer with authenticated user
        $validated['user_id'] = $request->user()->id;

        // Set default credit limit to 1000 for new customers if not specified
        if (!isset($validated['credit_limit'])) {
            $validated['credit_limit'] = 1000.00;
        }

        // Store T&C acceptance details
        $validated['accepted_terms'] = true;
        $validated['accepted_terms_at'] = now();
        $validated['accepted_terms_version'] = '1.0';
        $validated['accepted_terms_ip'] = $request->ip();

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
    public function show(Request $request, Customer $customer): JsonResponse
    {
        // Ensure user can only access their own customers
        if ($customer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

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
        // Ensure user can only update their own customers
        if ($customer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

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
            // Motorcycle Details
            'motorcycle_number_plate' => 'nullable|string|max:255',
            'motorcycle_chassis_number' => 'nullable|string|max:255',
            'motorcycle_model' => 'nullable|string|max:255',
            'motorcycle_type' => 'nullable|string|max:255',
            'motorcycle_engine_cc' => 'nullable|string|max:255',
            'motorcycle_colour' => 'nullable|string|max:255',
            // Next of Kin Details
            'next_of_kin_name' => 'nullable|string|max:255',
            'next_of_kin_phone' => 'nullable|string|max:255',
            'next_of_kin_relationship' => 'nullable|string|max:255',
            'next_of_kin_email' => 'nullable|email|max:255',
            'next_of_kin_passport_photo_path' => 'nullable|string',
            // Guarantor Details
            'guarantor_name' => 'nullable|string|max:255',
            'guarantor_phone' => 'nullable|string|max:255',
            'guarantor_relationship' => 'nullable|string|max:255',
            'guarantor_email' => 'nullable|email|max:255',
            'guarantor_passport_photo_path' => 'nullable|string',
            // Guarantor Motorcycle Details
            'guarantor_motorcycle_number_plate' => 'nullable|string|max:255',
            'guarantor_motorcycle_chassis_number' => 'nullable|string|max:255',
            'guarantor_motorcycle_model' => 'nullable|string|max:255',
            'guarantor_motorcycle_type' => 'nullable|string|max:255',
            'guarantor_motorcycle_engine_cc' => 'nullable|string|max:255',
            'guarantor_motorcycle_colour' => 'nullable|string|max:255',
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
    public function destroy(Request $request, Customer $customer): JsonResponse
    {
        // Ensure user can only delete their own customers
        if ($customer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

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

    /**
     * Get customer profile for the authenticated user
     */
    public function myProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        // Try to find customer by user's customer_id or phone
        $customer = null;

        if ($user->customer_id) {
            $customer = Customer::find($user->customer_id);
        } else {
            // Try to find by phone number
            $customer = Customer::where('phone', $user->phone)->first();
        }

        if ($customer) {
            return response()->json([
                'success' => true,
                'data' => $customer
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'No profile found'
        ]);
    }
}
