<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Get user dashboard data with active loans, balance, and statistics
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get user's customer profile
        $customer = null;
        if ($user->customer_id) {
            $customer = Customer::find($user->customer_id);
        } else {
            // Try to find by phone number
            $customer = Customer::where('phone', $user->phone)
                ->where('user_id', $user->id)
                ->first();
        }

        // If no customer profile exists, return minimal data
        if (!$customer) {
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'customer' => null,
                    'active_loans' => [],
                    'statistics' => [
                        'total_loans' => 0,
                        'active_loans_count' => 0,
                        'total_borrowed' => 0,
                        'total_paid' => 0,
                        'outstanding_balance' => 0,
                        'credit_limit' => 0,
                        'available_credit' => 0,
                    ],
                    'recent_payments' => [],
                ]
            ]);
        }

        // Get active loans with details
        $activeLoans = $customer->loans()
            ->with(['payments'])
            ->whereIn('status', ['approved', 'active', 'pending'])
            ->latest()
            ->get()
            ->map(function ($loan) {
                return [
                    'id' => $loan->id,
                    'loan_number' => $loan->loan_number,
                    'status' => $loan->status,
                    'principal_amount' => $loan->principal_amount,
                    'total_amount' => $loan->total_amount,
                    'amount_paid' => $loan->amount_paid,
                    'balance' => $loan->balance,
                    'interest_rate' => $loan->interest_rate,
                    'due_date' => $loan->due_date,
                    'disbursement_date' => $loan->disbursement_date,
                    'created_at' => $loan->created_at,
                    'is_overdue' => $loan->due_date && $loan->due_date < now() && $loan->balance > 0,
                    'days_overdue' => $loan->due_date && $loan->due_date < now()
                        ? now()->diffInDays($loan->due_date)
                        : 0,
                ];
            });

        // Get recent payments
        $recentPayments = $customer->payments()
            ->with(['loan'])
            ->latest('payment_date')
            ->take(5)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'payment_date' => $payment->payment_date,
                    'status' => $payment->status,
                    'loan_number' => $payment->loan->loan_number ?? null,
                ];
            });

        // Calculate statistics
        $statistics = [
            'total_loans' => $customer->loans()->count(),
            'active_loans_count' => $customer->activeLoans()->count(),
            'total_borrowed' => (float) $customer->total_borrowed,
            'total_paid' => (float) $customer->total_paid,
            'outstanding_balance' => (float) $customer->outstandingBalance(),
            'credit_limit' => (float) $customer->credit_limit,
            'available_credit' => (float) max(0, $customer->credit_limit - $customer->outstandingBalance()),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'profile_completed' => $user->profile_completed,
                ],
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'status' => $customer->status,
                ],
                'active_loans' => $activeLoans,
                'statistics' => $statistics,
                'recent_payments' => $recentPayments,
            ]
        ]);
    }

    /**
     * Get quick summary for the user
     */
    public function quickView(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get user's customer profile
        $customer = null;
        if ($user->customer_id) {
            $customer = Customer::find($user->customer_id);
        } else {
            $customer = Customer::where('phone', $user->phone)
                ->where('user_id', $user->id)
                ->first();
        }

        if (!$customer) {
            return response()->json([
                'success' => true,
                'data' => [
                    'has_profile' => false,
                    'active_loans' => 0,
                    'total_balance' => 0,
                    'next_payment_due' => null,
                ]
            ]);
        }

        // Get the most urgent loan (next due date)
        $nextLoan = $customer->loans()
            ->whereIn('status', ['approved', 'active'])
            ->where('balance', '>', 0)
            ->orderBy('due_date', 'asc')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'has_profile' => true,
                'customer_name' => $customer->name,
                'active_loans' => $customer->activeLoans()->count(),
                'total_balance' => (float) $customer->outstandingBalance(),
                'total_borrowed' => (float) $customer->total_borrowed,
                'total_paid' => (float) $customer->total_paid,
                'credit_limit' => (float) $customer->credit_limit,
                'available_credit' => (float) max(0, $customer->credit_limit - $customer->outstandingBalance()),
                'next_payment_due' => $nextLoan ? [
                    'loan_number' => $nextLoan->loan_number,
                    'amount_due' => (float) $nextLoan->balance,
                    'due_date' => $nextLoan->due_date,
                    'is_overdue' => $nextLoan->due_date < now(),
                ] : null,
            ]
        ]);
    }
}
