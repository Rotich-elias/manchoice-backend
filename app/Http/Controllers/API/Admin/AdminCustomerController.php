<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminCustomerController extends Controller
{
    /**
     * Update any customer from admin backend
     * Admin can update any customer regardless of user_id
     */
    public function update(Request $request, $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

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

        // Log admin action
        \Log::info('Admin updated customer', [
            'admin_id' => $request->user()->id,
            'admin_name' => $request->user()->name,
            'customer_id' => $customer->id,
            'updated_fields' => array_keys($validated),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully',
            'data' => $customer->load(['loans', 'payments'])
        ]);
    }

    /**
     * Get all customers (admin view)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Customer::with(['loans', 'payments', 'user']);

        // Search by name, phone, email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('id_number', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user_id
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $customers = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }

    /**
     * Get a single customer (admin view)
     */
    public function show($id): JsonResponse
    {
        $customer = Customer::with(['loans.payments', 'payments', 'user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $customer
        ]);
    }
}
