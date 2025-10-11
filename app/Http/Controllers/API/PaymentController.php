<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Loan;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with(['loan', 'customer', 'recorder']);

        // Filter by loan
        if ($request->has('loan_id')) {
            $query->where('loan_id', $request->loan_id);
        }

        // Filter by customer
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->latest('payment_date')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Record a new payment
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:mpesa,cash,bank_transfer,other',
            'mpesa_receipt_number' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $loan = Loan::findOrFail($validated['loan_id']);

            // Check if loan can accept payments
            if (!in_array($loan->status, ['approved', 'active'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This loan cannot accept payments'
                ], 400);
            }

            // Check if payment exceeds balance
            if ($validated['amount'] > $loan->balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount exceeds loan balance'
                ], 400);
            }

            // Generate transaction ID
            $transactionId = 'TXN' . date('YmdHis') . Str::random(4);

            // Create payment
            $payment = Payment::create([
                ...$validated,
                'customer_id' => $loan->customer_id,
                'transaction_id' => $transactionId,
                'payment_date' => $validated['payment_date'] ?? now(),
                'status' => 'completed',
                'recorded_by' => $request->user()->id,
            ]);

            // Update loan
            $loan->amount_paid += $validated['amount'];
            $loan->balance -= $validated['amount'];

            if ($loan->balance <= 0) {
                $loan->status = 'completed';
            } else if ($loan->status === 'approved') {
                $loan->status = 'active';
            }

            $loan->save();

            // Update customer
            $customer = $loan->customer;
            $customer->total_paid += $validated['amount'];
            $customer->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'data' => $payment->load(['loan', 'customer'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified payment
     */
    public function show(Payment $payment): JsonResponse
    {
        $payment->load(['loan', 'customer', 'recorder']);

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /**
     * Update the specified payment
     */
    public function update(Request $request, Payment $payment): JsonResponse
    {
        // Only allow updating notes and status for now
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'status' => 'sometimes|in:pending,completed,failed,reversed',
        ]);

        $payment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully',
            'data' => $payment
        ]);
    }

    /**
     * Reverse a payment
     */
    public function reverse(Request $request, Payment $payment): JsonResponse
    {
        if ($payment->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Only completed payments can be reversed'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Update payment status
            $payment->update([
                'status' => 'reversed',
                'notes' => ($payment->notes ?? '') . "\nReversed on " . now()->toDateTimeString()
            ]);

            // Update loan
            $loan = $payment->loan;
            $loan->amount_paid -= $payment->amount;
            $loan->balance += $payment->amount;

            if ($loan->status === 'completed' && $loan->balance > 0) {
                $loan->status = 'active';
            }

            $loan->save();

            // Update customer
            $customer = $payment->customer;
            $customer->total_paid -= $payment->amount;
            $customer->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment reversed successfully',
                'data' => $payment->load(['loan', 'customer'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reverse payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified payment
     */
    public function destroy(Payment $payment): JsonResponse
    {
        if ($payment->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete completed payments. Please reverse instead.'
            ], 400);
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully'
        ]);
    }
}
