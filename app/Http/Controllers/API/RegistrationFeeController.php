<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RegistrationFee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class RegistrationFeeController extends Controller
{
    /**
     * Get registration fee status for authenticated user
     */
    public function getStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $registrationFee = $user->registrationFee;

        return response()->json([
            'success' => true,
            'data' => [
                'fee_paid' => $user->registration_fee_paid,
                'amount' => 300.00,
                'registration_fee' => $registrationFee,
            ]
        ]);
    }

    /**
     * Initiate M-PESA payment for registration fee
     */
    public function initiateMpesaPayment(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|string|regex:/^0[0-9]{9}$/',
        ]);

        $user = $request->user();

        // Check if already paid
        if ($user->registration_fee_paid) {
            return response()->json([
                'success' => false,
                'message' => 'Registration fee already paid',
            ], 400);
        }

        // Create or update registration fee record
        $registrationFee = RegistrationFee::updateOrCreate(
            ['user_id' => $user->id],
            [
                'amount' => 300.00,
                'phone_number' => $request->phone_number,
                'payment_method' => 'mpesa',
                'status' => 'pending',
                'transaction_id' => 'REG-' . strtoupper(Str::random(10)),
            ]
        );

        // TODO: Integrate with actual M-PESA STK Push
        // For now, return the transaction details
        return response()->json([
            'success' => true,
            'message' => 'M-PESA payment initiated. Enter your PIN on your phone.',
            'data' => [
                'transaction_id' => $registrationFee->transaction_id,
                'amount' => $registrationFee->amount,
                'phone_number' => $registrationFee->phone_number,
            ]
        ], 200);
    }

    /**
     * Record cash payment for registration fee (admin only)
     */
    public function recordCashPayment(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'phone_number' => 'required|string|regex:/^0[0-9]{9}$/',
            'notes' => 'nullable|string',
        ]);

        $user = User::findOrFail($request->user_id);

        // Check if already paid
        if ($user->registration_fee_paid) {
            return response()->json([
                'success' => false,
                'message' => 'Registration fee already paid',
            ], 400);
        }

        // Create registration fee record
        $registrationFee = RegistrationFee::create([
            'user_id' => $user->id,
            'amount' => 300.00,
            'phone_number' => $request->phone_number,
            'payment_method' => 'cash',
            'status' => 'completed',
            'paid_at' => now(),
            'notes' => $request->notes,
            'recorded_by' => $request->user()->id,
            'transaction_id' => 'REG-CASH-' . strtoupper(Str::random(10)),
        ]);

        // Update user record
        $user->update([
            'registration_fee_paid' => true,
            'registration_fee_amount' => 300.00,
            'registration_fee_paid_at' => now(),
        ]);

        // Update any loans that were awaiting registration fee
        $this->updateAwaitingLoans($user);

        return response()->json([
            'success' => true,
            'message' => 'Registration fee recorded successfully. Loan application moved to pending review.',
            'data' => $registrationFee->fresh(),
        ], 201);
    }

    /**
     * Verify M-PESA payment (callback or manual check)
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $request->validate([
            'transaction_id' => 'required|string',
        ]);

        $registrationFee = RegistrationFee::where('transaction_id', $request->transaction_id)->first();

        if (!$registrationFee) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        // TODO: Check actual M-PESA transaction status
        // For now, simulate successful payment
        if ($registrationFee->status === 'pending') {
            $registrationFee->update([
                'status' => 'completed',
                'paid_at' => now(),
                'mpesa_receipt_number' => 'MPE' . strtoupper(Str::random(8)),
            ]);

            // Update user record
            $registrationFee->user->update([
                'registration_fee_paid' => true,
                'registration_fee_amount' => 300.00,
                'registration_fee_paid_at' => now(),
            ]);

            // Update any loans that were awaiting registration fee
            $this->updateAwaitingLoans($registrationFee->user);
        }

        return response()->json([
            'success' => true,
            'message' => 'Registration fee payment verified successfully. Your loan application will now be reviewed by admin.',
            'data' => [
                'status' => $registrationFee->status,
                'fee_paid' => $registrationFee->user->registration_fee_paid,
                'registration_fee' => $registrationFee->fresh(),
                'next_step' => 'Wait for admin to review your application and set your loan limit',
            ]
        ]);
    }

    /**
     * Get all registration fees (admin only)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');

        $query = RegistrationFee::with(['user', 'recorder']);

        if ($status) {
            $query->where('status', $status);
        }

        $fees = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $fees,
        ]);
    }

    /**
     * Update loans that were awaiting registration fee payment
     */
    private function updateAwaitingLoans($user)
    {
        // Get customer for this user
        $customer = $user->customer;

        if ($customer) {
            // Update all loans with status 'awaiting_registration_fee' to 'pending'
            \App\Models\Loan::where('customer_id', $customer->id)
                ->where('status', 'awaiting_registration_fee')
                ->update(['status' => 'pending']);
        }
    }
}
