<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PartRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PartRequestController extends Controller
{
    /**
     * Get all part requests for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $partRequests = PartRequest::where('user_id', $user->id)
            ->with('customer')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $partRequests,
        ]);
    }

    /**
     * Create a new part request
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'part_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'motorcycle_model' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:4',
            'quantity' => 'required|integer|min:1',
            'budget' => 'nullable|numeric|min:0',
            'urgency' => 'required|in:low,medium,high',
            'image' => 'nullable|image|max:5120',
        ]);

        $user = $request->user();

        // Get or create customer for this user
        $customer = Customer::where('user_id', $user->id)
            ->orWhere('phone', $user->phone)
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete your profile before requesting parts.',
            ], 400);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'part_request_' . time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('part-requests', $filename, 'public');
            $validated['image_path'] = $path;
        }

        $validated['customer_id'] = $customer->id;
        $validated['user_id'] = $user->id;
        $validated['status'] = 'pending';

        $partRequest = PartRequest::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Part request submitted successfully',
            'data' => $partRequest->load('customer'),
        ], 201);
    }

    /**
     * Get a specific part request
     */
    public function show(Request $request, PartRequest $partRequest): JsonResponse
    {
        // Ensure user can only view their own requests
        if ($partRequest->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $partRequest->load('customer'),
        ]);
    }

    /**
     * Cancel a part request
     */
    public function cancel(Request $request, PartRequest $partRequest): JsonResponse
    {
        // Ensure user can only cancel their own requests
        if ($partRequest->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Can only cancel pending or in_progress requests
        if (!in_array($partRequest->status, ['pending', 'in_progress'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel this request. Current status: ' . $partRequest->status,
            ], 400);
        }

        $partRequest->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Part request cancelled successfully',
            'data' => $partRequest,
        ]);
    }
}
