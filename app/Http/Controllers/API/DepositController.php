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

        // Get rejection count
        $rejectionCount = Deposit::byLoan($loanId)->rejected()->count();
        $hasReachedLimit = $rejectionCount >= 3;

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
                'rejection_count' => $rejectionCount,
                'has_reached_rejection_limit' => $hasReachedLimit,
                'can_retry' => !$hasReachedLimit,
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

        $loan = Loan::with('customer')->findOrFail($loanId);

        // Check if user owns this loan
        if ($request->user()->customer_id !== $loan->customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this loan',
            ], 403);
        }

        // CRITICAL: Prevent deposit payment if credit limit is not set
        // This ensures customers can't pay deposits before admin reviews and sets their limit
        if ($loan->customer->credit_limit <= 0) {
            return response()->json([
                'success' => false,
                'show_popup' => true,
                'popup_type' => 'info',
                'popup_title' => 'Loan Under Review',
                'popup_icon' => '⏳',
                'message' => 'Your loan application is currently under review.',
                'popup_message' => "Please wait for admin review!\n\nYour loan application is currently being reviewed by our admin team. Once approved and your loan limit is set, you'll be able to proceed with the deposit payment.\n\nYou will be notified once the review is complete.\n\nThank you for your patience!",
                'credit_limit_not_set' => true,
                'status' => 'awaiting_admin_review',
                'action_required' => 'wait_for_admin_approval',
                'estimated_wait' => 'Usually within 24-48 hours',
            ], 202); // 202 Accepted - Cannot process deposit yet
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
            'type' => 'loan_deposit',
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
     * Admin manually verifies a deposit payment
     */
    public function verifyManualPayment(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:completed,failed,rejected',
            'rejection_reason' => 'required_if:status,failed,rejected|string|min:10',
            'notes' => 'nullable|string',
        ]);

        $deposit = Deposit::findOrFail($id);

        // Check if already verified
        if ($deposit->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'This payment has already been verified',
            ], 400);
        }

        if ($request->status === 'completed') {
            // Verify the payment
            $deposit->update([
                'status' => 'completed',
                'paid_at' => now(),
                'recorded_by' => $request->user()->id,
                'notes' => $request->notes,
            ]);

            // Update loan deposit_paid amount AND deduct from balance
            $loan = $deposit->loan;
            $loan->update([
                'deposit_paid' => $loan->deposit_paid + $deposit->amount,
                'deposit_paid_at' => $loan->isDepositPaid() ? now() : $loan->deposit_paid_at,
                'amount_paid' => $loan->amount_paid + $deposit->amount,
                'balance' => $loan->balance - $deposit->amount,
            ]);

            // If deposit is now fully paid, update loan status
            if ($loan->isDepositPaid() && $loan->status === 'awaiting_deposit') {
                $loan->update(['status' => 'pending']);
            }

            // Update customer total_paid
            $customer = $loan->customer;
            $customer->increment('total_paid', $deposit->amount);

            $message = 'Deposit payment verified successfully. Loan deposit has been updated.';
        } else {
            // Reject the payment (failed or rejected status)
            $totalRejections = Deposit::byLoan($deposit->loan_id)->rejected()->count();

            $deposit->update([
                'status' => $request->status, // 'failed' or 'rejected'
                'rejection_reason' => $request->rejection_reason,
                'rejected_at' => now(),
                'rejected_by' => $request->user()->id,
                'rejection_count' => $totalRejections + 1,
                'recorded_by' => $request->user()->id,
                'notes' => $request->notes,
            ]);

            // Keep loan in awaiting_deposit status
            if ($deposit->loan->status !== 'completed' && !$deposit->loan->isDepositPaid()) {
                $deposit->loan->update(['status' => 'awaiting_deposit']);
            }

            $message = 'Deposit payment rejected.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $deposit->fresh(['loan', 'customer', 'recorder', 'rejector']),
        ]);
    }

    /**
     * Submit manual deposit payment with M-PESA code (for verification)
     */
    public function submitManualPayment(Request $request): JsonResponse
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'phone_number' => 'required|string|regex:/^0[0-9]{9}$/',
            'mpesa_code' => 'required|string|min:8',
            'amount' => 'required|numeric|min:1',
        ]);

        $loan = Loan::with('customer')->findOrFail($request->loan_id);

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

        // Validate payment amount doesn't exceed remaining
        $remainingDeposit = $loan->getRemainingDepositAmount();
        if ($request->amount > $remainingDeposit) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount exceeds remaining deposit',
            ], 400);
        }

        // Check if this M-PESA code was already submitted
        $existingDeposit = Deposit::where('mpesa_receipt_number', $request->mpesa_code)->first();
        if ($existingDeposit) {
            return response()->json([
                'success' => false,
                'message' => 'This M-PESA code has already been submitted',
            ], 400);
        }

        // Create deposit record with pending status
        $deposit = Deposit::create([
            'loan_id' => $loan->id,
            'customer_id' => $loan->customer_id,
            'amount' => $request->amount,
            'type' => 'loan_deposit',
            'phone_number' => $request->phone_number,
            'mpesa_receipt_number' => strtoupper($request->mpesa_code),
            'payment_method' => 'mpesa',
            'status' => 'pending',
            'transaction_id' => 'DEP-MANUAL-' . strtoupper(Str::random(10)),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Deposit payment submitted for verification',
            'data' => [
                'deposit' => $deposit->fresh(),
                'status' => 'pending_verification',
            ]
        ], 201);
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
            'type' => 'loan_deposit',
            'phone_number' => $request->phone_number,
            'payment_method' => 'cash',
            'status' => 'completed',
            'paid_at' => now(),
            'notes' => $request->notes,
            'recorded_by' => $request->user()->id,
            'transaction_id' => 'DEP-CASH-' . strtoupper(Str::random(10)),
        ]);

        // Update loan deposit_paid amount AND deduct from balance
        $loan->update([
            'deposit_paid' => $loan->deposit_paid + $deposit->amount,
            'deposit_paid_at' => $loan->isDepositPaid() ? now() : $loan->deposit_paid_at,
            'amount_paid' => $loan->amount_paid + $deposit->amount,
            'balance' => $loan->balance - $deposit->amount,
        ]);

        // Update customer total_paid
        $customer = $loan->customer;
        $customer->increment('total_paid', $deposit->amount);

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
        $type = $request->get('type');

        $query = Deposit::with(['loan', 'customer', 'recorder']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($loanId) {
            $query->where('loan_id', $loanId);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $deposits = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $deposits,
        ]);
    }

    /**
     * Get rejection history for a loan
     */
    public function getRejectionHistory(Request $request, $loanId): JsonResponse
    {
        $loan = Loan::findOrFail($loanId);

        // Check if user owns this loan
        if ($request->user()->customer_id !== $loan->customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this loan',
            ], 403);
        }

        $rejectedDeposits = Deposit::byLoan($loanId)
            ->rejected()
            ->with('rejector')
            ->orderByDesc('rejected_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rejectedDeposits,
        ]);
    }

    /**
     * Admin rejects a deposit payment
     */
    public function rejectDeposit(Request $request, $id): JsonResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ]);

        $deposit = Deposit::findOrFail($id);
        $loan = $deposit->loan;

        // Check if already verified
        if ($deposit->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reject a completed payment',
            ], 400);
        }

        // Check if already rejected
        if ($deposit->status === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'This payment has already been rejected',
            ], 400);
        }

        // Calculate total rejection count for this loan
        $totalRejections = Deposit::byLoan($loan->id)->rejected()->count();

        // Update deposit status
        $deposit->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'rejected_at' => now(),
            'rejected_by' => $request->user()->id,
            'rejection_count' => $totalRejections + 1,
        ]);

        // Keep loan in awaiting_deposit status if applicable
        if ($loan->status !== 'completed' && !$loan->isDepositPaid()) {
            $loan->update(['status' => 'awaiting_deposit']);
        }

        // TODO: Send notification to customer
        // $this->sendRejectionNotification($deposit, $loan);

        // If reached limit, notify admins
        $hasReachedLimit = ($totalRejections + 1) >= 3;

        return response()->json([
            'success' => true,
            'message' => 'Deposit rejected successfully',
            'data' => [
                'deposit' => $deposit->fresh(['rejector', 'loan', 'customer']),
                'rejection_count' => $totalRejections + 1,
                'has_reached_limit' => $hasReachedLimit,
            ],
        ]);
    }

    /**
     * Update admin verification to handle rejection count
     */
    public function verifyManualPaymentUpdated(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:completed,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|min:10',
            'notes' => 'nullable|string',
        ]);

        $deposit = Deposit::findOrFail($id);

        // Check if already verified
        if ($deposit->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'This payment has already been verified',
            ], 400);
        }

        if ($request->status === 'completed') {
            // Verify the payment
            $deposit->update([
                'status' => 'completed',
                'paid_at' => now(),
                'recorded_by' => $request->user()->id,
                'notes' => $request->notes,
            ]);

            // Update loan deposit_paid amount AND deduct from balance
            $loan = $deposit->loan;
            $loan->update([
                'deposit_paid' => $loan->deposit_paid + $deposit->amount,
                'deposit_paid_at' => $loan->isDepositPaid() ? now() : $loan->deposit_paid_at,
                'amount_paid' => $loan->amount_paid + $deposit->amount,
                'balance' => $loan->balance - $deposit->amount,
            ]);

            // If deposit is now fully paid, update loan status
            if ($loan->isDepositPaid() && $loan->status === 'awaiting_deposit') {
                $loan->update(['status' => 'pending']);
            }

            // Update customer total_paid
            $customer = $loan->customer;
            $customer->increment('total_paid', $deposit->amount);

            $message = 'Deposit payment verified successfully. Loan deposit has been updated.';
        } else {
            // Reject the payment
            $totalRejections = Deposit::byLoan($deposit->loan_id)->rejected()->count();

            $deposit->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'rejected_at' => now(),
                'rejected_by' => $request->user()->id,
                'rejection_count' => $totalRejections + 1,
                'notes' => $request->notes,
            ]);

            $message = 'Deposit payment rejected.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $deposit->fresh(['loan', 'customer', 'recorder', 'rejector']),
        ]);
    }
}
