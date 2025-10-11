<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Iankumu\Mpesa\Facades\Mpesa;

class MpesaController extends Controller
{
    /**
     * Initiate STK Push payment
     */
    public function stkPush(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'phone_number' => 'required|string|regex:/^254[0-9]{9}$/',
            'amount' => 'required|numeric|min:1',
        ]);

        try {
            $loan = Loan::with('customer')->findOrFail($validated['loan_id']);

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

            // Generate account reference (loan number)
            $accountReference = $loan->loan_number;
            $transactionDesc = "Loan Payment for {$loan->customer->name}";

            // Initiate STK Push
            $response = Mpesa::stkpush(
                $validated['phone_number'],
                $validated['amount'],
                $accountReference,
                $transactionDesc
            );

            // Log the response
            Log::info('M-PESA STK Push Response:', ['response' => $response]);

            if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
                // Create pending payment record
                $payment = Payment::create([
                    'loan_id' => $validated['loan_id'],
                    'customer_id' => $loan->customer_id,
                    'transaction_id' => $response['CheckoutRequestID'],
                    'amount' => $validated['amount'],
                    'payment_method' => 'mpesa',
                    'status' => 'pending',
                    'payment_date' => now(),
                    'phone_number' => $validated['phone_number'],
                    'notes' => 'M-PESA STK Push initiated',
                    'recorded_by' => $request->user()->id ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'STK Push sent successfully',
                    'data' => [
                        'checkout_request_id' => $response['CheckoutRequestID'],
                        'merchant_request_id' => $response['MerchantRequestID'],
                        'payment' => $payment
                    ]
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate STK Push',
                'error' => $response['errorMessage'] ?? 'Unknown error'
            ], 400);

        } catch (\Exception $e) {
            Log::error('M-PESA STK Push Error:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle M-PESA callback
     */
    public function callback(Request $request): JsonResponse
    {
        $data = $request->all();
        Log::info('M-PESA Callback Received:', $data);

        try {
            $resultCode = $data['Body']['stkCallback']['ResultCode'] ?? null;
            $checkoutRequestID = $data['Body']['stkCallback']['CheckoutRequestID'] ?? null;

            if (!$checkoutRequestID) {
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid request']);
            }

            // Find the payment record
            $payment = Payment::where('transaction_id', $checkoutRequestID)->first();

            if (!$payment) {
                Log::warning('Payment not found for CheckoutRequestID:', ['id' => $checkoutRequestID]);
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Payment not found']);
            }

            // Check if payment was successful
            if ($resultCode == 0) {
                // Extract M-PESA receipt number
                $callbackMetadata = $data['Body']['stkCallback']['CallbackMetadata']['Item'] ?? [];
                $mpesaReceiptNumber = null;

                foreach ($callbackMetadata as $item) {
                    if ($item['Name'] == 'MpesaReceiptNumber') {
                        $mpesaReceiptNumber = $item['Value'];
                        break;
                    }
                }

                DB::beginTransaction();

                // Update payment
                $payment->update([
                    'status' => 'completed',
                    'mpesa_receipt_number' => $mpesaReceiptNumber,
                    'notes' => ($payment->notes ?? '') . "\nPayment confirmed via M-PESA callback"
                ]);

                // Update loan
                $loan = $payment->loan;
                $loan->amount_paid += $payment->amount;
                $loan->balance -= $payment->amount;

                if ($loan->balance <= 0) {
                    $loan->status = 'completed';
                } else if ($loan->status === 'approved') {
                    $loan->status = 'active';
                }

                $loan->save();

                // Update customer
                $customer = $loan->customer;
                $customer->total_paid += $payment->amount;
                $customer->save();

                DB::commit();

                Log::info('Payment processed successfully:', ['payment_id' => $payment->id]);
            } else {
                // Payment failed
                $payment->update([
                    'status' => 'failed',
                    'notes' => ($payment->notes ?? '') . "\nPayment failed: " . ($data['Body']['stkCallback']['ResultDesc'] ?? 'Unknown error')
                ]);

                Log::warning('Payment failed:', ['payment_id' => $payment->id, 'result_code' => $resultCode]);
            }

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);

        } catch (\Exception $e) {
            Log::error('M-PESA Callback Error:', ['error' => $e->getMessage(), 'data' => $data]);
            DB::rollBack();

            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Error processing callback']);
        }
    }

    /**
     * Check STK Push status
     */
    public function checkStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'checkout_request_id' => 'required|string',
        ]);

        try {
            $payment = Payment::where('transaction_id', $validated['checkout_request_id'])->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $payment->load(['loan', 'customer'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle M-PESA timeout
     */
    public function timeout(Request $request): JsonResponse
    {
        $data = $request->all();
        Log::info('M-PESA Timeout:', $data);

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Timeout received']);
    }

    /**
     * Handle M-PESA result URL (for B2C, C2B, etc.)
     */
    public function result(Request $request): JsonResponse
    {
        $data = $request->all();
        Log::info('M-PESA Result:', $data);

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Result received']);
    }
}
