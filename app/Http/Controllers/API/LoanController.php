<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    /**
     * Display a listing of loans
     */
    public function index(Request $request): JsonResponse
    {
        $query = Loan::with(['customer', 'payments']);

        // Filter by customer
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter overdue loans
        if ($request->has('overdue') && $request->overdue === 'true') {
            $query->where('due_date', '<', now())
                  ->whereIn('status', ['active', 'approved'])
                  ->where('balance', '>', 0);
        }

        $loans = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $loans
        ]);
    }

    /**
     * Store a newly created loan
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'principal_amount' => 'required|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'duration_days' => 'nullable|integer|min:1',
            'due_date' => 'nullable|date|after:today',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',
            // Document uploads
            'bike_photo' => 'nullable|image|max:5120',
            'logbook_photo' => 'nullable|image|max:5120',
            'passport_photo' => 'nullable|image|max:5120',
            'id_photo' => 'nullable|image|max:5120',
            'next_of_kin_id_photo' => 'nullable|image|max:5120',
            'guarantor_id_photo' => 'nullable|image|max:5120',
            // Or photo paths if already stored locally
            'bike_photo_path' => 'nullable|string',
            'logbook_photo_path' => 'nullable|string',
            'passport_photo_path' => 'nullable|string',
            'id_photo_path' => 'nullable|string',
            'next_of_kin_id_photo_path' => 'nullable|string',
            'guarantor_id_photo_path' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Calculate total amount
            $interestRate = $validated['interest_rate'] ?? 0;
            $totalAmount = $validated['principal_amount'] * (1 + ($interestRate / 100));

            // Generate loan number
            $loanNumber = 'LN' . date('Ymd') . str_pad(Loan::count() + 1, 4, '0', STR_PAD_LEFT);

            // Calculate due date if duration is provided
            if (isset($validated['duration_days']) && !isset($validated['due_date'])) {
                $validated['due_date'] = now()->addDays($validated['duration_days'])->toDateString();
            }

            // Handle file uploads
            $photoPaths = [];
            $photoFields = ['bike_photo', 'logbook_photo', 'passport_photo', 'id_photo', 'next_of_kin_id_photo', 'guarantor_id_photo'];

            foreach ($photoFields as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $filename = $loanNumber . '_' . $field . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('loan-documents', $filename, 'public');
                    $photoPaths[$field . '_path'] = $path;
                } elseif (isset($validated[$field . '_path'])) {
                    $photoPaths[$field . '_path'] = $validated[$field . '_path'];
                }
            }

            $loan = Loan::create([
                ...$validated,
                'loan_number' => $loanNumber,
                'total_amount' => $totalAmount,
                'balance' => $totalAmount,
                'amount_paid' => 0,
                'status' => 'pending',
                ...$photoPaths,
            ]);

            // Update customer loan count
            $customer = Customer::find($validated['customer_id']);
            $customer->increment('loan_count');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Loan created successfully',
                'data' => $loan->load('customer')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create loan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified loan
     */
    public function show(Loan $loan): JsonResponse
    {
        $loan->load(['customer', 'payments', 'approver']);

        return response()->json([
            'success' => true,
            'data' => $loan
        ]);
    }

    /**
     * Update the specified loan
     */
    public function update(Request $request, Loan $loan): JsonResponse
    {
        $validated = $request->validate([
            'principal_amount' => 'sometimes|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'duration_days' => 'nullable|integer|min:1',
            'due_date' => 'nullable|date',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'sometimes|in:pending,approved,active,completed,defaulted,cancelled',
        ]);

        // Recalculate total if principal or interest changes
        if (isset($validated['principal_amount']) || isset($validated['interest_rate'])) {
            $principal = $validated['principal_amount'] ?? $loan->principal_amount;
            $rate = $validated['interest_rate'] ?? $loan->interest_rate;
            $totalAmount = $principal * (1 + ($rate / 100));

            $validated['total_amount'] = $totalAmount;
            $validated['balance'] = $totalAmount - $loan->amount_paid;
        }

        $loan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Loan updated successfully',
            'data' => $loan
        ]);
    }

    /**
     * Approve a loan
     */
    public function approve(Request $request, Loan $loan): JsonResponse
    {
        if ($loan->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending loans can be approved'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $loan->update([
                'status' => 'approved',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'disbursement_date' => now()->toDateString(),
            ]);

            // Update customer totals
            $customer = $loan->customer;
            $customer->total_borrowed += $loan->total_amount;
            $customer->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Loan approved successfully',
                'data' => $loan->load(['customer', 'approver'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve loan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a loan
     */
    public function reject(Request $request, Loan $loan): JsonResponse
    {
        if ($loan->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending loans can be rejected'
            ], 400);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $loan->update([
                'status' => 'rejected',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'notes' => ($loan->notes ? $loan->notes . "\n\n" : '') .
                          "REJECTED: " . $validated['rejection_reason'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Loan rejected successfully',
                'data' => $loan->load(['customer', 'approver'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject loan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified loan
     */
    public function destroy(Loan $loan): JsonResponse
    {
        if (in_array($loan->status, ['approved', 'active'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete approved or active loans'
            ], 400);
        }

        $loan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Loan deleted successfully'
        ]);
    }
}
