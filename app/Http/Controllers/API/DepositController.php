<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class DepositController extends Controller
{
    /**
     * Get deposit status for a loan
     */
    public function getDepositStatus(Request $request, $loanId): JsonResponse
    {
        $loan = Loan::with('deposits')->findOrFail($loanId);

        // Check if user owns this loan
        if ($request->user()->customer_id !== $loan->customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this loan',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'deposit_required' => $loan->deposit_required,
                'deposit_amount' => $loan->deposit_amount,
                'deposit_paid' => $loan->deposit_paid,
                'remaining_deposit' => $loan->getRemainingDepositAmount(),
                'is_deposit_paid' => $loan->isDepositPaid(),
                'deposit_paid_at' => $loan->deposit_paid_at,
                'deposits' => $loan->deposits,
            ]
        ]);
    }

    /**
     * Initiate M-PESA deposit payment
     */
    public function initiateMpesaPayment(Request $request, $loanId): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|string|regex:/^0[0-9]{9}$/',
            'amount' => 'nullable|numeric|min:1',
        ]);

        $loan = Loan::findOrFail($loanId);

        // Check if user owns this loan
        if ($request->user()->customer_id !== $loan->customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this loan',
            ], 403);
        }

        // Check if deposit is already fully paid
        if ($loan->isDepositPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Deposit already fully paid',
            ], 400);
        }

        // Determine payment amount (full remaining or partial)
        $remainingDeposit = $loan->getRemainingDepositAmount();
        $paymentAmount = $request->amount ?? $remainingDeposit;

        // Validate payment amount doesn't exceed remaining
        if ($paymentAmount > $remainingDeposit) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount exceeds remaining deposit',
            ], 400);
        }

        // Create deposit record
        $deposit = Deposit::create([
            'loan_id' => $loan->id,
            'customer_id' => $loan->customer_id,
            'amount' => $paymentAmount,
            'phone_number' => $request->phone_number,
            'payment_method' => 'mpesa',
            'status' => 'pending',
            'transaction_id' => 'DEP-' . strtoupper(Str::random(10)),
        ]);

        // TODO: Integrate with actual M-PESA STK Push
        // For now, return the transaction details
        return response()->json([
            'success' => true,
            'message' => 'M-PESA payment initiated. Enter your PIN on your phone.',
            'data' => [
                'deposit_id' => $deposit->id,
                'transaction_id' => $deposit->transaction_id,
                'amount' => $deposit->amount,
                'phone_number' => $deposit->phone_number,
                'remaining_after_payment' => $remainingDeposit - $paymentAmount,
            ]
        ], 200);
    }

    /**
     * Verify M-PESA deposit payment
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $request->validate([
            'transaction_id' => 'required|string',
        ]);

        $deposit = Deposit::where('transaction_id', $request->transaction_id)->first();

        if (!$deposit) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        // Check if user owns this deposit
        if ($request->user()->customer_id !== $deposit->customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        // TODO: Check actual M-PESA transaction status
        // For now, simulate successful payment
        if ($deposit->status === 'pending') {
            $deposit->update([
                'status' => 'completed',
                'paid_at' => now(),
                'mpesa_receipt_number' => 'MPE' . strtoupper(Str::random(8)),
            ]);

            // Update loan deposit_paid amount
            $loan = $deposit->loan;
            $loan->update([
                'deposit_paid' => $loan->deposit_paid + $deposit->amount,
                'deposit_paid_at' => $loan->isDepositPaid() ? now() : $loan->deposit_paid_at,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment verified successfully',
            'data' => [
                'deposit' => $deposit->fresh(),
                'loan' => $deposit->loan->fresh(),
                'is_deposit_fully_paid' => $deposit->loan->isDepositPaid(),
            ]
        ]);
    }

    /**
     * Record cash deposit payment (admin only)
     */
    public function recordCashPayment(Request $request): JsonResponse
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'amount' => 'required|numeric|min:1',
            'phone_number' => 'required|string|regex:/^0[0-9]{9}$/',
            'notes' => 'nullable|string',
        ]);

        $loan = Loan::findOrFail($request->loan_id);

        // Check if deposit is already fully paid
        if ($loan->isDepositPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Deposit already fully paid',
            ], 400);
        }

        $remainingDeposit = $loan->getRemainingDepositAmount();
        if ($request->amount > $remainingDeposit) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount exceeds remaining deposit',
            ], 400);
        }

        // Create deposit record
        $deposit = Deposit::create([
            'loan_id' => $loan->id,
            'customer_id' => $loan->customer_id,
            'amount' => $request->amount,
            'phone_number' => $request->phone_number,
            'payment_method' => 'cash',
            'status' => 'completed',
            'paid_at' => now(),
            'notes' => $request->notes,
            'recorded_by' => $request->user()->id,
            'transaction_id' => 'DEP-CASH-' . strtoupper(Str::random(10)),
        ]);

        // Update loan deposit_paid amount
        $loan->update([
            'deposit_paid' => $loan->deposit_paid + $deposit->amount,
            'deposit_paid_at' => $loan->isDepositPaid() ? now() : $loan->deposit_paid_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Deposit recorded successfully',
            'data' => [
                'deposit' => $deposit->fresh(),
                'loan' => $loan->fresh(),
            ]
        ], 201);
    }

    /**
     * Get all deposits for a loan
     */
    public function getLoanDeposits(Request $request, $loanId): JsonResponse
    {
        $loan = Loan::findOrFail($loanId);

        // Check if user owns this loan
        if ($request->user()->customer_id !== $loan->customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this loan',
            ], 403);
        }

        $deposits = $loan->deposits()->with('recorder')->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $deposits,
        ]);
    }

    /**
     * Get all deposits (admin only)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');
        $loanId = $request->get('loan_id');

        $query = Deposit::with(['loan', 'customer', 'recorder']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($loanId) {
            $query->where('loan_id', $loanId);
        }

        $deposits = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $deposits,
        ]);
    }
}
