<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Customer;
use App\Models\Product;
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
        // Only show loans for customers belonging to the authenticated user
        $query = Loan::with(['customer', 'payments', 'items.product'])
            ->whereHas('customer', function($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });

        // Filter by customer ID
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by customer name
        if ($request->has('customer_name')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->customer_name . '%');
            });
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

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'customer':
            case 'customer_name':
                $query->join('customers', 'loans.customer_id', '=', 'customers.id')
                      ->select('loans.*')
                      ->orderBy('customers.name', $sortOrder);
                break;
            case 'amount':
            case 'total_amount':
                $query->orderBy('total_amount', $sortOrder);
                break;
            case 'due_date':
                $query->orderBy('due_date', $sortOrder);
                break;
            case 'balance':
                $query->orderBy('balance', $sortOrder);
                break;
            case 'principal_amount':
                $query->orderBy('principal_amount', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
                break;
        }

        $loans = $query->paginate(15);

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
            // Products/items for this loan
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            // Document uploads
            'bike_photo' => 'nullable|image|max:5120',
            'logbook_photo' => 'nullable|image|max:5120',
            'passport_photo' => 'nullable|image|max:5120',
            'id_photo_front' => 'nullable|image|max:5120',
            'id_photo_back' => 'nullable|image|max:5120',
            'next_of_kin_id_front' => 'nullable|image|max:5120',
            'next_of_kin_id_back' => 'nullable|image|max:5120',
            'next_of_kin_passport_photo' => 'nullable|image|max:5120',
            'guarantor_id_front' => 'nullable|image|max:5120',
            'guarantor_id_back' => 'nullable|image|max:5120',
            'guarantor_passport_photo' => 'nullable|image|max:5120',
            'guarantor_bike_photo' => 'nullable|image|max:5120',
            'guarantor_logbook_photo' => 'nullable|image|max:5120',
            // Or photo paths if already stored locally
            'bike_photo_path' => 'nullable|string',
            'logbook_photo_path' => 'nullable|string',
            'passport_photo_path' => 'nullable|string',
            'id_photo_front_path' => 'nullable|string',
            'id_photo_back_path' => 'nullable|string',
            'next_of_kin_id_front_path' => 'nullable|string',
            'next_of_kin_id_back_path' => 'nullable|string',
            'next_of_kin_passport_photo_path' => 'nullable|string',
            'guarantor_id_front_path' => 'nullable|string',
            'guarantor_id_back_path' => 'nullable|string',
            'guarantor_passport_photo_path' => 'nullable|string',
            'guarantor_bike_photo_path' => 'nullable|string',
            'guarantor_logbook_photo_path' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Get customer and check status
            $customer = Customer::find($validated['customer_id']);

            // Check customer status
            if ($customer->status === 'blacklisted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot create loan. Customer account is blacklisted.',
                ], 403);
            }

            if ($customer->status === 'inactive') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot create loan. Customer account is inactive.',
                ], 403);
            }

            // Calculate total amount
            $interestRate = (float)($validated['interest_rate'] ?? 0);
            $totalAmount = (float)$validated['principal_amount'] * (1 + ($interestRate / 100));

            // Check credit limit (only if credit_limit > 0)
            if ($customer->credit_limit > 0) {
                $outstandingBalance = $customer->total_borrowed - $customer->total_paid;
                $availableCredit = $customer->credit_limit - $outstandingBalance;

                if ($totalAmount > $availableCredit) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Loan amount exceeds your available credit limit',
                        'data' => [
                            'credit_limit' => number_format($customer->credit_limit, 2),
                            'outstanding_balance' => number_format($outstandingBalance, 2),
                            'available_credit' => number_format($availableCredit, 2),
                            'requested_amount' => number_format($totalAmount, 2),
                        ]
                    ], 400);
                }
            }

            // Generate loan number
            $loanNumber = 'LN' . date('Ymd') . str_pad(Loan::count() + 1, 4, '0', STR_PAD_LEFT);

            // Calculate due date if duration is provided
            if (isset($validated['duration_days']) && !isset($validated['due_date'])) {
                $validated['due_date'] = now()->addDays((int)$validated['duration_days'])->toDateString();
            }

            // Handle file uploads
            $photoPaths = [];
            $photoFields = ['bike_photo', 'logbook_photo', 'passport_photo', 'id_photo_front', 'id_photo_back', 'next_of_kin_id_front', 'next_of_kin_id_back', 'next_of_kin_passport_photo', 'guarantor_id_front', 'guarantor_id_back', 'guarantor_passport_photo', 'guarantor_bike_photo', 'guarantor_logbook_photo'];

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

            // Update customer profile with latest document photos for future reuse
            $customer = Customer::find($validated['customer_id']);
            if (!empty($photoPaths)) {
                $customer->update($photoPaths);
            }

            // Add loan items if provided
            if (isset($validated['items']) && !empty($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    $product = Product::find($item['product_id']);

                    LoanItem::create([
                        'loan_id' => $loan->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $product->price,
                        // subtotal calculated automatically in model
                    ]);
                }
            }

            // Update customer loan count
            $customer = Customer::find($validated['customer_id']);
            $customer->increment('loan_count');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Loan created successfully',
                'data' => $loan->load(['customer', 'items.product'])
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
    public function show(Request $request, Loan $loan): JsonResponse
    {
        // Ensure user can only access loans for their own customers
        if ($loan->customer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $loan->load(['customer', 'payments', 'approver', 'items.product']);

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
        // Ensure user can only update loans for their own customers
        if ($loan->customer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

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

            // Validate stock availability for loan items
            $loan->load('items.product');
            $insufficientStock = [];

            foreach ($loan->items as $item) {
                if (!$item->product->isInStock() || $item->product->stock_quantity < $item->quantity) {
                    $insufficientStock[] = [
                        'product' => $item->product->name,
                        'required' => $item->quantity,
                        'available' => $item->product->stock_quantity
                    ];
                }
            }

            if (!empty($insufficientStock)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock for some products',
                    'insufficient_stock' => $insufficientStock
                ], 400);
            }

            // Deduct stock for each loan item
            foreach ($loan->items as $item) {
                $product = $item->product;
                $product->reduceStock($item->quantity);
            }

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
                'message' => 'Loan approved successfully and stock deducted',
                'data' => $loan->load(['customer', 'approver', 'items.product'])
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
    public function destroy(Request $request, Loan $loan): JsonResponse
    {
        // Ensure user can only delete loans for their own customers
        if ($loan->customer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

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

    /**
     * Get payment schedule for a loan
     */
    public function getPaymentSchedule(Loan $loan): JsonResponse
    {
        // Check if user is authorized to view this loan
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Allow if user owns the loan or is admin
        if ($loan->customer->user_id !== $user->id && !$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $schedule = $loan->paymentSchedule()
            ->orderBy('day_number')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'day_number' => $item->day_number,
                    'due_date' => $item->due_date->format('Y-m-d'),
                    'expected_amount' => (float) $item->expected_amount,
                    'paid_amount' => (float) $item->paid_amount,
                    'remaining_amount' => (float) $item->remaining_amount,
                    'status' => $item->status,
                    'is_overdue' => $item->isOverdue(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'loan_id' => $loan->id,
                'loan_number' => $loan->loan_number,
                'daily_payment_amount' => (float) $loan->daily_payment_amount,
                'adjusted_duration_days' => $loan->adjusted_duration_days,
                'total_amount' => (float) $loan->total_amount,
                'schedule' => $schedule,
            ]
        ]);
    }

    /**
     * Get all defaulted loans (admin or user's own)
     */
    public function getDefaultedLoans(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $query = Loan::with(['customer', 'paymentSchedule'])
            ->where('status', 'defaulted');

        // If not admin, only show user's own loans
        if (!$user->is_admin) {
            $query->whereHas('customer', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $loans = $query->latest()->get()->map(function ($loan) {
            return [
                'id' => $loan->id,
                'loan_number' => $loan->loan_number,
                'customer_name' => $loan->customer->name,
                'customer_id' => $loan->customer_id,
                'total_amount' => (float) $loan->total_amount,
                'balance' => (float) $loan->balance,
                'amount_paid' => (float) $loan->amount_paid,
                'daily_payment_amount' => (float) $loan->daily_payment_amount,
                'missed_payments' => $loan->getMissedPaymentsCount(),
                'overdue_amount' => (float) $loan->getOverdueAmount(),
                'due_date' => $loan->due_date ? $loan->due_date->format('Y-m-d') : null,
                'created_at' => $loan->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $loans,
            'count' => $loans->count(),
        ]);
    }
}
