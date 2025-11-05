<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminLoanController extends Controller
{
    /**
     * Create a loan for any customer from admin backend
     * Admin can create loans without the restrictions of regular users
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
            'status' => 'nullable|in:pending,approved,active,awaiting_deposit,awaiting_registration_fee',
            // Products/items for this loan
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            // Document uploads (optional - admin may create without docs)
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
            // Or photo paths if already stored
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
            // Admin can override deposit requirement
            'deposit_required' => 'nullable|boolean',
            'skip_credit_limit_check' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            // Get customer
            $customer = Customer::findOrFail($validated['customer_id']);

            // Admin note: Check customer status but allow override with warning
            if ($customer->status === 'blacklisted') {
                if (!$request->has('force_create')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Customer account is blacklisted. Include "force_create": true to override.',
                        'requires_confirmation' => true,
                    ], 400);
                }
            }

            if ($customer->status === 'inactive') {
                if (!$request->has('force_create')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Customer account is inactive. Include "force_create": true to override.',
                        'requires_confirmation' => true,
                    ], 400);
                }
            }

            // Calculate total amount
            $interestRate = (float)($validated['interest_rate'] ?? 0);
            $totalAmount = (float)$validated['principal_amount'] * (1 + ($interestRate / 100));

            // Check credit limit (admin can skip this check)
            if (!($validated['skip_credit_limit_check'] ?? false)) {
                if ($customer->credit_limit > 0) {
                    $outstandingBalance = $customer->total_borrowed - $customer->total_paid;
                    $availableCredit = $customer->credit_limit - $outstandingBalance;

                    if ($totalAmount > $availableCredit) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Loan amount exceeds customer\'s available credit limit. Include "skip_credit_limit_check": true to override.',
                            'data' => [
                                'credit_limit' => number_format($customer->credit_limit, 2),
                                'outstanding_balance' => number_format($outstandingBalance, 2),
                                'available_credit' => number_format($availableCredit, 2),
                                'requested_amount' => number_format($totalAmount, 2),
                            ],
                            'requires_confirmation' => true,
                        ], 400);
                    }
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

            // Calculate 10% deposit
            $depositAmount = round($totalAmount * 0.10, 2);
            $depositRequired = $validated['deposit_required'] ?? true;

            // Determine loan status - admin can set custom status or default to pending
            $loanStatus = $validated['status'] ?? 'pending';

            $loan = Loan::create([
                'customer_id' => $validated['customer_id'],
                'loan_number' => $loanNumber,
                'principal_amount' => $validated['principal_amount'],
                'interest_rate' => $validated['interest_rate'] ?? 0,
                'total_amount' => $totalAmount,
                'balance' => $totalAmount,
                'amount_paid' => 0,
                'deposit_amount' => $depositRequired ? $depositAmount : 0,
                'deposit_paid' => 0,
                'deposit_required' => $depositRequired,
                'status' => $loanStatus,
                'duration_days' => $validated['duration_days'] ?? null,
                'due_date' => $validated['due_date'] ?? null,
                'purpose' => $validated['purpose'] ?? null,
                'notes' => ($validated['notes'] ?? '') . "\n\n[Created by Admin: " . $request->user()->name . " on " . now()->toDateTimeString() . "]",
                ...$photoPaths,
            ]);

            // Update customer profile with latest document photos for future reuse
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
                    ]);
                }
            }

            // Update customer loan count
            $customer->increment('loan_count');

            DB::commit();

            // Log admin action
            \Log::info('Admin created loan', [
                'admin_id' => $request->user()->id,
                'admin_name' => $request->user()->name,
                'loan_id' => $loan->id,
                'loan_number' => $loan->loan_number,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'amount' => $totalAmount,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Loan created successfully by admin',
                'data' => $loan->load(['customer', 'items.product'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Admin loan creation failed: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create loan',
                'error' => $e->getMessage(),
                'details' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Create a loan with payment tracking (enhanced admin feature)
     * Allows admin to create loans and record payments that have already been made
     */
    public function storeWithPayments(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // Standard loan fields
            'customer_id' => 'required|exists:customers,id',
            'principal_amount' => 'required|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'duration_days' => 'nullable|integer|min:1',
            'due_date' => 'nullable|date',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',

            // Payment tracking fields (NEW)
            'amount_already_paid' => 'nullable|numeric|min:0',
            'disbursement_date' => 'nullable|date',
            'payment_method' => 'nullable|in:M-Pesa,Cash,Bank Transfer,Cheque',
            'payment_reference' => 'nullable|string',

            // Admin overrides
            'status' => 'nullable|in:pending,approved,active,completed,awaiting_deposit,awaiting_registration_fee',
            'deposit_required' => 'nullable|boolean',
            'skip_credit_limit_check' => 'nullable|boolean',
            'force_create' => 'nullable|boolean',

            // Products/items
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',

            // Document uploads (all 13 photo fields)
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
            // Or photo paths if already stored
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

            // Get customer
            $customer = Customer::findOrFail($validated['customer_id']);

            // Check customer status (allow override with force_create)
            if ($customer->status === 'blacklisted' && !($validated['force_create'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer account is blacklisted. Include "force_create": true to override.',
                    'requires_confirmation' => true,
                ], 400);
            }

            if ($customer->status === 'inactive' && !($validated['force_create'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer account is inactive. Include "force_create": true to override.',
                    'requires_confirmation' => true,
                ], 400);
            }

            // Calculate total amount
            $interestRate = (float)($validated['interest_rate'] ?? 0);
            $principalAmount = (float)$validated['principal_amount'];
            $totalAmount = $principalAmount * (1 + ($interestRate / 100));

            // Validate amount_already_paid
            $amountAlreadyPaid = (float)($validated['amount_already_paid'] ?? 0);
            if ($amountAlreadyPaid > $totalAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Amount already paid cannot exceed total loan amount',
                    'data' => [
                        'total_amount' => number_format($totalAmount, 2),
                        'amount_already_paid' => number_format($amountAlreadyPaid, 2),
                    ]
                ], 400);
            }

            // Check credit limit (admin can skip)
            if (!($validated['skip_credit_limit_check'] ?? false)) {
                if ($customer->credit_limit > 0) {
                    $outstandingBalance = $customer->total_borrowed - $customer->total_paid;
                    $availableCredit = $customer->credit_limit - $outstandingBalance;

                    if ($totalAmount > $availableCredit) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Loan amount exceeds customer\'s available credit limit. Include "skip_credit_limit_check": true to override.',
                            'data' => [
                                'credit_limit' => number_format($customer->credit_limit, 2),
                                'outstanding_balance' => number_format($outstandingBalance, 2),
                                'available_credit' => number_format($availableCredit, 2),
                                'requested_amount' => number_format($totalAmount, 2),
                            ],
                            'requires_confirmation' => true,
                        ], 400);
                    }
                }
            }

            // Generate loan number
            $loanNumber = 'LN' . date('Ymd') . str_pad(Loan::count() + 1, 4, '0', STR_PAD_LEFT);

            // Handle disbursement date
            $disbursementDate = $validated['disbursement_date'] ?? now()->toDateString();

            // Calculate due date if duration is provided
            $durationDays = $validated['duration_days'] ?? 30;
            if (!isset($validated['due_date'])) {
                $validated['due_date'] = now()->parse($disbursementDate)->addDays($durationDays)->toDateString();
            }

            // Handle file uploads
            $photoPaths = [];
            $photoFields = [
                'bike_photo', 'logbook_photo', 'passport_photo',
                'id_photo_front', 'id_photo_back',
                'next_of_kin_id_front', 'next_of_kin_id_back', 'next_of_kin_passport_photo',
                'guarantor_id_front', 'guarantor_id_back', 'guarantor_passport_photo',
                'guarantor_bike_photo', 'guarantor_logbook_photo'
            ];

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

            // Calculate deposit
            $depositRequired = $validated['deposit_required'] ?? true;
            $depositAmount = $depositRequired ? round($totalAmount * 0.10, 2) : 0;

            // Calculate balance
            $balance = $totalAmount - $amountAlreadyPaid;

            // Determine loan status based on payment
            if (isset($validated['status'])) {
                $loanStatus = $validated['status'];
            } else {
                if ($amountAlreadyPaid >= $totalAmount) {
                    $loanStatus = 'completed';
                } elseif ($amountAlreadyPaid > 0) {
                    $loanStatus = 'active';
                } elseif ($depositRequired) {
                    $loanStatus = 'awaiting_deposit';
                } else {
                    $loanStatus = 'pending';
                }
            }

            // Create the loan
            $loan = Loan::create([
                'customer_id' => $validated['customer_id'],
                'loan_number' => $loanNumber,
                'principal_amount' => $principalAmount,
                'interest_rate' => $interestRate,
                'total_amount' => $totalAmount,
                'balance' => $balance,
                'amount_paid' => $amountAlreadyPaid,
                'deposit_amount' => $depositAmount,
                'deposit_paid' => $depositRequired ? 0 : $depositAmount,
                'deposit_required' => $depositRequired,
                'status' => $loanStatus,
                'disbursement_date' => $disbursementDate,
                'duration_days' => $durationDays,
                'due_date' => $validated['due_date'],
                'purpose' => $validated['purpose'] ?? null,
                'notes' => ($validated['notes'] ?? '') . "\n\n[Created by Admin: " . $request->user()->name . " on " . now()->toDateTimeString() . "]",
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                ...$photoPaths,
            ]);

            // Calculate and set daily payment
            $dailyPaymentData = $loan->calculateDailyPayment();
            $loan->update([
                'daily_payment_amount' => $dailyPaymentData['daily_payment_amount'],
                'adjusted_duration_days' => $dailyPaymentData['adjusted_duration_days'],
                'due_date' => $dailyPaymentData['due_date']->toDateString(),
            ]);

            // Generate payment schedule
            $loan->generatePaymentSchedule();

            // If amount already paid, create payment record and update schedule
            if ($amountAlreadyPaid > 0) {
                // Create payment record
                \App\Models\Payment::create([
                    'loan_id' => $loan->id,
                    'customer_id' => $customer->id,
                    'amount' => $amountAlreadyPaid,
                    'payment_method' => $validated['payment_method'] ?? 'Cash',
                    'transaction_id' => $validated['payment_reference'] ?? null,
                    'status' => 'completed',
                    'payment_date' => now(),
                    'recorded_by' => $request->user()->id,
                    'notes' => 'Initial payment recorded by admin during loan creation',
                ]);

                // Update payment schedule to mark days as paid
                $this->updatePaymentSchedule($loan, $amountAlreadyPaid);
            }

            // Update customer profile with document photos
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
                    ]);

                    // Deduct stock if loan is active/approved
                    if (in_array($loanStatus, ['active', 'approved', 'completed'])) {
                        $product->reduceStock($item['quantity']);
                    }
                }
            }

            // Update customer stats
            $customer->increment('loan_count');
            $customer->total_borrowed += $totalAmount;
            $customer->total_paid += $amountAlreadyPaid;
            $customer->save();

            DB::commit();

            // Log admin action
            \Log::info('Admin created loan with payment tracking', [
                'admin_id' => $request->user()->id,
                'admin_name' => $request->user()->name,
                'loan_id' => $loan->id,
                'loan_number' => $loan->loan_number,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'total_amount' => $totalAmount,
                'amount_already_paid' => $amountAlreadyPaid,
                'balance' => $balance,
                'status' => $loanStatus,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Loan created successfully with payment tracking',
                'data' => $loan->load(['customer', 'items.product', 'payments', 'paymentSchedule'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Admin loan creation with payments failed: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create loan',
                'error' => $e->getMessage(),
                'details' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Helper method to update payment schedule based on amount paid
     */
    private function updatePaymentSchedule(Loan $loan, float $amountPaid): void
    {
        $remainingAmount = $amountPaid;

        // Get payment schedules ordered by day number
        $schedules = $loan->paymentSchedule()->orderBy('day_number')->get();

        foreach ($schedules as $schedule) {
            if ($remainingAmount <= 0) {
                break;
            }

            $expectedAmount = $schedule->expected_amount;

            if ($remainingAmount >= $expectedAmount) {
                // Fully pay this schedule item
                $schedule->update([
                    'paid_amount' => $expectedAmount,
                    'status' => 'paid',
                ]);
                $remainingAmount -= $expectedAmount;
            } else {
                // Partially pay this schedule item
                $schedule->update([
                    'paid_amount' => $remainingAmount,
                    'status' => 'partial',
                ]);
                $remainingAmount = 0;
            }
        }
    }

    /**
     * Get all loans (admin view)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Loan::with(['customer', 'payments', 'items.product', 'approver']);

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
            default:
                $query->orderBy('created_at', $sortOrder);
                break;
        }

        $loans = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $loans
        ]);
    }

    /**
     * Get a single loan (admin view)
     */
    public function show($id): JsonResponse
    {
        $loan = Loan::with(['customer', 'payments', 'approver', 'items.product', 'deposits'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $loan
        ]);
    }

    /**
     * Update a loan (admin)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $loan = Loan::findOrFail($id);

        $validated = $request->validate([
            'principal_amount' => 'sometimes|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'duration_days' => 'nullable|integer|min:1',
            'due_date' => 'nullable|date',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'sometimes|in:pending,approved,active,completed,defaulted,cancelled,rejected,awaiting_deposit,awaiting_registration_fee',
        ]);

        // Recalculate total if principal or interest changes
        if (isset($validated['principal_amount']) || isset($validated['interest_rate'])) {
            $principal = $validated['principal_amount'] ?? $loan->principal_amount;
            $rate = $validated['interest_rate'] ?? $loan->interest_rate;
            $totalAmount = $principal * (1 + ($rate / 100));

            $validated['total_amount'] = $totalAmount;
            $validated['balance'] = $totalAmount - $loan->amount_paid;
        }

        // Add admin note
        if (isset($validated['notes'])) {
            $validated['notes'] = $loan->notes . "\n\n[Updated by Admin: " . $request->user()->name . " on " . now()->toDateTimeString() . "]\n" . $validated['notes'];
        }

        $loan->update($validated);

        // Log admin action
        \Log::info('Admin updated loan', [
            'admin_id' => $request->user()->id,
            'admin_name' => $request->user()->name,
            'loan_id' => $loan->id,
            'updated_fields' => array_keys($validated),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Loan updated successfully',
            'data' => $loan->load(['customer', 'payments', 'items.product'])
        ]);
    }
}
