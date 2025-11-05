<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'customers' => Customer::count(),
            'loans' => Loan::count(),
            'pending_loans' => Loan::where('status', 'pending')->count(),
            'active_loans' => Loan::whereIn('status', ['approved', 'active'])->count(),
            'defaulted_loans' => Loan::where('status', 'defaulted')->count(),
            'total_borrowed' => Loan::whereIn('status', ['approved', 'active', 'completed'])->sum('total_amount'),
            'total_paid' => Payment::where('status', 'completed')->sum('amount'),
            'products' => Product::count(),
            'low_stock_products' => Product::where('stock_quantity', '<', 10)->count(),
        ];

        // Loan status breakdown for pie chart
        $loansByStatus = [
            'pending' => Loan::where('status', 'pending')->count(),
            'approved' => Loan::where('status', 'approved')->count(),
            'active' => Loan::where('status', 'active')->count(),
            'completed' => Loan::where('status', 'completed')->count(),
            'rejected' => Loan::where('status', 'rejected')->count(),
            'defaulted' => Loan::where('status', 'defaulted')->count(),
        ];

        // Revenue over last 30 days for line chart
        $revenueData = [];
        $revenueDates = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $revenueDates[] = now()->subDays($i)->format('M d');
            $revenueData[] = Payment::where('status', 'completed')
                ->whereDate('payment_date', $date)
                ->sum('amount');
        }

        // Loans disbursed over last 30 days for bar chart
        $loansData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $loansData[] = Loan::whereDate('created_at', $date)->count();
        }

        // Customer growth over last 6 months
        $customerGrowth = [];
        $customerMonths = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $customerMonths[] = $month->format('M Y');
            $customerGrowth[] = Customer::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        // Payment method breakdown
        $paymentMethods = Payment::where('status', 'completed')
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get()
            ->pluck('total', 'payment_method')
            ->toArray();

        $recentLoans = Loan::with('customer')->latest()->take(5)->get();
        $pendingLoans = Loan::with('customer')->where('status', 'pending')->latest()->get();
        $defaultedLoans = Loan::with('customer')->where('status', 'defaulted')->latest()->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentLoans',
            'pendingLoans',
            'defaultedLoans',
            'loansByStatus',
            'revenueData',
            'revenueDates',
            'loansData',
            'customerGrowth',
            'customerMonths',
            'paymentMethods'
        ));
    }

    public function customers(Request $request)
    {
        $query = Customer::with('loans', 'creator', 'updater');

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by credit status
        if ($request->has('credit_status') && $request->credit_status) {
            switch ($request->credit_status) {
                case 'available':
                    $query->whereRaw('(credit_limit - (total_borrowed - total_paid)) > 0');
                    break;
                case 'maxed':
                    $query->whereRaw('(credit_limit - (total_borrowed - total_paid)) = 0');
                    break;
                case 'overlimit':
                    $query->whereRaw('(credit_limit - (total_borrowed - total_paid)) < 0');
                    break;
            }
        }

        // Search by name, phone, or ID number
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('id_number', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(20);
        return view('admin.customers', compact('customers'));
    }

    public function customerDetail($id)
    {
        $customer = Customer::with(['loans' => function($query) {
            $query->latest();
        }, 'creator', 'updater'])->findOrFail($id);

        return view('admin.customer-detail', compact('customer'));
    }

    public function updateCreditLimit(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'credit_limit' => 'required|numeric|min:0',
        ]);

        $customer->update([
            'credit_limit' => $validated['credit_limit'],
        ]);

        return back()->with('success', 'Credit limit updated successfully');
    }

    /**
     * Store a new customer (admin created)
     */
    public function storeCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^0[0-9]{9}$/|unique:customers,phone',
            'email' => 'nullable|email|unique:customers,email',
            'id_number' => 'nullable|string|unique:customers,id_number',
            'address' => 'nullable|string',
            'business_name' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive,blacklisted',
            'notes' => 'nullable|string',
            // User association (optional)
            'user_id' => 'nullable|exists:users,id',
        ]);

        // Set defaults
        $validated['credit_limit'] = $validated['credit_limit'] ?? 1000.00;
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['accepted_terms'] = true;
        $validated['accepted_terms_at'] = now();
        $validated['accepted_terms_version'] = '1.0';
        $validated['accepted_terms_ip'] = $request->ip();
        $validated['created_by'] = auth()->id();

        // Add admin note
        $adminNote = "Customer created by admin: " . auth()->user()->name . " on " . now()->toDateTimeString();
        $validated['notes'] = isset($validated['notes'])
            ? $validated['notes'] . "\n\n" . $adminNote
            : $adminNote;

        $customer = Customer::create($validated);

        return redirect('/admin/customers')->with('success', 'Customer created successfully');
    }

    /**
     * Update a customer (admin)
     */
    public function updateCustomer(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^0[0-9]{9}$/|unique:customers,phone,' . $id,
            'email' => 'nullable|email|unique:customers,email,' . $id,
            'id_number' => 'nullable|string|unique:customers,id_number,' . $id,
            'address' => 'nullable|string',
            'business_name' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,blacklisted',
            'notes' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();
        $customer->update($validated);

        return back()->with('success', 'Customer updated successfully');
    }

    public function loans(Request $request)
    {
        $query = Loan::with(['customer', 'approver', 'rejector', 'creator']);

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Search by loan number, customer name, or phone
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('loan_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $loans = $query->latest()->paginate(20);
        $currentStatus = $request->status ?? 'all';

        return view('admin.loans', compact('loans', 'currentStatus'));
    }

    public function loanDetail($id)
    {
        $loan = Loan::with(['customer', 'approver', 'rejector', 'creator', 'payments', 'deposits'])->findOrFail($id);
        return view('admin.loan-detail', compact('loan'));
    }

    /**
     * Show the form for creating a new loan
     */
    public function createLoan()
    {
        $customers = Customer::where('status', '!=', 'deleted')->orderBy('name')->get();
        $products = Product::where('is_available', true)->orderBy('name')->get();

        return view('admin.create-loan', compact('customers', 'products'));
    }

    /**
     * Store a new loan with payment tracking (server-side)
     */
    public function storeLoan(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'principal_amount' => 'required|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'duration_days' => 'nullable|integer|min:1',
            'disbursement_date' => 'nullable|date',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',
            'amount_already_paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:M-Pesa,Cash,Bank Transfer,Cheque',
            'payment_reference' => 'nullable|string',
            'skip_credit_limit_check' => 'nullable|boolean',
            'deposit_required' => 'nullable|boolean',
            'force_create' => 'nullable|boolean',
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
        ]);

        try {
            \DB::beginTransaction();

            $customer = Customer::findOrFail($validated['customer_id']);

            // Calculate amounts
            $interestRate = (float)($validated['interest_rate'] ?? 0);
            $principalAmount = (float)$validated['principal_amount'];
            $totalAmount = $principalAmount * (1 + ($interestRate / 100));
            $amountAlreadyPaid = (float)($validated['amount_already_paid'] ?? 0);
            $balance = $totalAmount - $amountAlreadyPaid;

            // Validate amount paid
            if ($amountAlreadyPaid > $totalAmount) {
                return back()->withErrors(['amount_already_paid' => 'Amount paid cannot exceed total loan amount'])->withInput();
            }

            // Check customer status (allow override)
            if (!($validated['force_create'] ?? false)) {
                if ($customer->status === 'blacklisted') {
                    return back()->withErrors(['customer_id' => 'Customer is blacklisted'])->withInput();
                }
                if ($customer->status === 'inactive') {
                    return back()->withErrors(['customer_id' => 'Customer is inactive'])->withInput();
                }
            }

            // Check credit limit
            if (!($validated['skip_credit_limit_check'] ?? false)) {
                if ($customer->credit_limit > 0) {
                    $outstanding = $customer->total_borrowed - $customer->total_paid;
                    $available = $customer->credit_limit - $outstanding;

                    if ($totalAmount > $available) {
                        return back()->withErrors([
                            'principal_amount' => "Loan exceeds available credit (KES " . number_format($available, 2) . ")"
                        ])->withInput();
                    }
                }
            }

            // Generate loan number
            $loanNumber = 'LN' . date('Ymd') . str_pad(Loan::count() + 1, 4, '0', STR_PAD_LEFT);

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
                }
            }

            // Calculate dates
            $disbursementDate = $validated['disbursement_date'] ?? now()->toDateString();
            $durationDays = (int)($validated['duration_days'] ?? 30);
            $dueDate = \Carbon\Carbon::parse($disbursementDate)->addDays($durationDays)->toDateString();

            // Deposit settings
            $depositRequired = !isset($validated['deposit_required']);
            $depositAmount = $depositRequired ? round($totalAmount * 0.10, 2) : 0;

            // Determine status
            if ($amountAlreadyPaid >= $totalAmount) {
                $status = 'completed';
            } elseif ($amountAlreadyPaid > 0) {
                $status = 'active';
            } elseif ($depositRequired) {
                $status = 'awaiting_deposit';
            } else {
                $status = 'pending';
            }

            // Create loan
            $loan = Loan::create(array_merge([
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
                'status' => $status,
                'disbursement_date' => $disbursementDate,
                'duration_days' => $durationDays,
                'due_date' => $dueDate,
                'purpose' => $validated['purpose'] ?? null,
                'notes' => ($validated['notes'] ?? '') . "\n\n[Created by Admin: " . auth()->user()->name . " on " . now()->toDateTimeString() . "]",
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ], $photoPaths));

            // Calculate and update daily payment
            $dailyPaymentData = $loan->calculateDailyPayment();
            $loan->update([
                'daily_payment_amount' => $dailyPaymentData['daily_payment_amount'],
                'adjusted_duration_days' => $dailyPaymentData['adjusted_duration_days'],
                'due_date' => $dailyPaymentData['due_date']->toDateString(),
            ]);

            // Generate payment schedule
            $loan->generatePaymentSchedule();

            // Record payment if amount already paid
            if ($amountAlreadyPaid > 0) {
                Payment::create([
                    'loan_id' => $loan->id,
                    'customer_id' => $customer->id,
                    'amount' => $amountAlreadyPaid,
                    'payment_method' => $validated['payment_method'] ?? 'Cash',
                    'transaction_id' => $validated['payment_reference'] ?? null,
                    'status' => 'completed',
                    'payment_date' => now(),
                    'recorded_by' => auth()->id(),
                    'notes' => 'Initial payment recorded by admin during loan creation',
                ]);

                // Update payment schedule
                $this->updatePaymentScheduleForLoan($loan, $amountAlreadyPaid);
            }

            // Add loan items
            if (isset($validated['items']) && !empty($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    $product = Product::find($item['product_id']);

                    \App\Models\LoanItem::create([
                        'loan_id' => $loan->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $product->price,
                    ]);

                    // Deduct stock if loan is active/approved/completed
                    if (in_array($status, ['active', 'approved', 'completed'])) {
                        $product->reduceStock($item['quantity']);
                    }
                }
            }

            // Update customer profile with document photos
            if (!empty($photoPaths)) {
                $customer->update($photoPaths);
            }

            // Update customer stats
            $customer->increment('loan_count');
            $customer->total_borrowed += $totalAmount;
            $customer->total_paid += $amountAlreadyPaid;
            $customer->save();

            \DB::commit();

            return redirect('/admin/loans/' . $loan->id)->with('success', 'Loan created successfully!');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Admin loan creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create loan: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Helper to update payment schedule based on amount paid
     */
    private function updatePaymentScheduleForLoan(Loan $loan, float $amountPaid): void
    {
        $remainingAmount = $amountPaid;
        $schedules = $loan->paymentSchedule()->orderBy('day_number')->get();

        foreach ($schedules as $schedule) {
            if ($remainingAmount <= 0) break;

            $expectedAmount = $schedule->expected_amount;

            if ($remainingAmount >= $expectedAmount) {
                $schedule->update([
                    'paid_amount' => $expectedAmount,
                    'status' => 'paid',
                ]);
                $remainingAmount -= $expectedAmount;
            } else {
                $schedule->update([
                    'paid_amount' => $remainingAmount,
                    'status' => 'partial',
                ]);
                $remainingAmount = 0;
            }
        }
    }

    public function products(Request $request)
    {
        $query = Product::query();

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Filter by stock status
        if ($request->has('stock_status') && $request->stock_status) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->where('stock_quantity', '>', 10);
                    break;
                case 'low_stock':
                    $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10);
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity', '=', 0);
                    break;
            }
        }

        // Filter by availability status
        if ($request->has('status') && $request->status) {
            $isAvailable = $request->status === 'available';
            $query->where('is_available', $isAvailable);
        }

        // Search by product name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->latest()->paginate(20);
        return view('admin.products', compact('products'));
    }

    public function approveLoan($id)
    {
        $loan = Loan::with('items.product')->findOrFail($id);

        if ($loan->status !== 'pending') {
            return back()->with('error', 'Only pending loans can be approved');
        }

        if (!auth()->check()) {
            return back()->with('error', 'You must be logged in to approve loans');
        }

        // Get customer and check status
        $customer = $loan->customer;

        // Check if this is the customer's first loan (no approved/active/completed loans) and if credit limit is not set
        $approvedLoansCount = $customer->loans()->whereIn('status', ['approved', 'active', 'completed'])->count();
        if ($approvedLoansCount == 0 && (!$customer->credit_limit || $customer->credit_limit <= 0)) {
            return back()->with('error', 'Please set a loan limit for this customer before approving their first loan. Go to customer details to set the loan limit.');
        }

        // Check customer status
        if ($customer->status === 'blacklisted') {
            return back()->with('error', 'Cannot approve loan. Customer account is blacklisted.');
        }

        if ($customer->status === 'inactive') {
            return back()->with('error', 'Cannot approve loan. Customer account is inactive.');
        }

        // Check credit limit (only if credit_limit > 0)
        if ($customer->credit_limit > 0) {
            $outstandingBalance = $customer->total_borrowed - $customer->total_paid;
            $availableCredit = $customer->credit_limit - $outstandingBalance;

            if ($loan->total_amount > $availableCredit) {
                return back()->with('error',
                    "Cannot approve loan. Loan amount (KSh " . number_format($loan->total_amount, 2) .
                    ") exceeds customer's available credit (KSh " . number_format($availableCredit, 2) .
                    "). Outstanding balance: KSh " . number_format($outstandingBalance, 2));
            }
        }

        // Check if there are items and verify stock availability
        if ($loan->items && $loan->items->count() > 0) {
            foreach ($loan->items as $item) {
                $product = $item->product;
                if ($product && (!$product->isInStock() || $product->stock_quantity < $item->quantity)) {
                    return back()->with('error', "Insufficient stock for product: {$product->name}. Available: {$product->stock_quantity}, Required: {$item->quantity}");
                }
            }

            // Deduct stock quantities
            foreach ($loan->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->reduceStock($item->quantity);
                }
            }
        }

        // Calculate daily payment and adjusted duration
        $paymentCalculation = $loan->calculateDailyPayment();

        $loan->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'disbursement_date' => now()->toDateString(),
            'daily_payment_amount' => $paymentCalculation['daily_payment_amount'],
            'adjusted_duration_days' => $paymentCalculation['adjusted_duration_days'],
            'due_date' => $paymentCalculation['due_date'],
        ]);

        // Generate payment schedule
        $loan->generatePaymentSchedule();

        $customer = $loan->customer;
        $customer->total_borrowed += $loan->total_amount;
        $customer->save();

        return back()->with('success', 'Loan approved successfully with daily payment of KES ' . number_format($paymentCalculation['daily_payment_amount'], 2) . ' for ' . $paymentCalculation['adjusted_duration_days'] . ' days');
    }

    public function rejectLoan(Request $request, $id)
    {
        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'pending') {
            return back()->with('error', 'Only pending loans can be rejected');
        }

        // Get customer and check if this is their first loan
        $customer = $loan->customer;

        // Check if this is the customer's first loan (no approved/active/completed loans) and if credit limit is not set
        $approvedLoansCount = $customer->loans()->whereIn('status', ['approved', 'active', 'completed'])->count();
        if ($approvedLoansCount == 0 && (!$customer->credit_limit || $customer->credit_limit <= 0)) {
            return back()->with('error', 'Please set a loan limit for this customer before rejecting their first loan. Go to customer details to set the loan limit.');
        }

        $rejectionReason = $request->input('rejection_reason', 'No reason provided');

        $loan->update([
            'status' => 'rejected',
            'rejected_by' => auth()->id() ?? 1,
            'rejected_at' => now(),
            'notes' => ($loan->notes ? $loan->notes . "\n\n" : '') .
                      "REJECTED by " . (auth()->user()->name ?? 'System') . " on " . now()->toDateTimeString() . ": " . $rejectionReason,
        ]);

        return back()->with('success', 'Loan rejected successfully');
    }

    public function updateProductStock(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'stock_quantity' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return back()->with('success', 'Stock updated successfully');
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'stock_quantity' => 'required|integer|min:0',
            'is_available' => 'nullable|boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('products', 'public');
            $validated['image_path'] = $imagePath;
        }

        // Auto-calculate discount percentage from original_price and price
        if (isset($validated['original_price']) && $validated['original_price'] > 0 && $validated['price'] < $validated['original_price']) {
            $validated['discount_percentage'] = round((($validated['original_price'] - $validated['price']) / $validated['original_price']) * 100);
        } else {
            $validated['discount_percentage'] = 0;
            $validated['original_price'] = null; // Clear original price if no discount
        }

        // Set defaults
        $validated['is_available'] = $validated['is_available'] ?? true;

        // Remove 'image' from validated data as it's not a database field
        unset($validated['image']);

        Product::create($validated);

        return back()->with('success', 'Product created successfully');
    }

    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'stock_quantity' => 'required|integer|min:0',
            'is_available' => 'nullable|boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }

            $image = $request->file('image');
            $imagePath = $image->store('products', 'public');
            $validated['image_path'] = $imagePath;
        }

        // Auto-calculate discount percentage from original_price and price
        if (isset($validated['original_price']) && $validated['original_price'] > 0 && $validated['price'] < $validated['original_price']) {
            $validated['discount_percentage'] = round((($validated['original_price'] - $validated['price']) / $validated['original_price']) * 100);
        } else {
            $validated['discount_percentage'] = 0;
            $validated['original_price'] = null; // Clear original price if no discount
        }

        // Remove 'image' from validated data as it's not a database field
        unset($validated['image']);

        $product->update($validated);

        return back()->with('success', 'Product updated successfully');
    }

    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);

        // Delete product image if exists
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return back()->with('success', 'Product deleted successfully');
    }

    public function payments(Request $request)
    {
        $query = Payment::with(['loan.customer', 'customer', 'approver', 'rejector', 'recorder']);

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Search by transaction ID, customer name, loan number, or receipt
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('mpesa_receipt_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('loan', function($loanQuery) use ($search) {
                      $loanQuery->where('loan_number', 'like', "%{$search}%")
                                ->orWhereHas('customer', function($customerQuery) use ($search) {
                                    $customerQuery->where('name', 'like', "%{$search}%");
                                });
                  })
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->latest()->paginate(20);
        $currentStatus = $request->status ?? 'all';

        $pendingCount = Payment::where('status', 'pending')->count();

        // Get active/approved loans for the payment form
        $activeLoans = Loan::with('customer')
            ->whereIn('status', ['approved', 'active'])
            ->where('balance', '>', 0)
            ->orderBy('loan_number')
            ->get();

        return view('admin.payments', compact('payments', 'currentStatus', 'pendingCount', 'activeLoans'));
    }

    public function approvePayment($id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Only pending payments can be approved');
        }

        // Update payment status
        $payment->update([
            'status' => 'completed',
            'approved_by' => auth()->id() ?? 1,
            'approved_at' => now(),
            'notes' => ($payment->notes ?? '') . "\nApproved by " . (auth()->user()->name ?? 'Admin') . " on " . now()->toDateTimeString()
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
        $customer = $payment->customer;
        $customer->total_paid += $payment->amount;
        $customer->save();

        return back()->with('success', 'Payment approved successfully');
    }

    public function rejectPayment(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Only pending payments can be rejected');
        }

        $rejectionReason = $request->input('rejection_reason', 'No reason provided');

        $payment->update([
            'status' => 'failed',
            'rejected_by' => auth()->id() ?? 1,
            'rejected_at' => now(),
            'notes' => ($payment->notes ?? '') . "\nRejected by " . (auth()->user()->name ?? 'Admin') . " on " . now()->toDateTimeString() . ": " . $rejectionReason
        ]);

        return back()->with('success', 'Payment rejected successfully');
    }

    /**
     * Create a new payment directly (Admin initiated)
     */
    public function createPayment(Request $request)
    {
        $validated = $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:mpesa,cash,bank_transfer,other',
            'mpesa_receipt_number' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $loan = Loan::findOrFail($validated['loan_id']);

            // Check if loan can accept payments
            if (!in_array($loan->status, ['approved', 'active'])) {
                return back()->with('error', 'This loan cannot accept payments. Current status: ' . $loan->status);
            }

            // Check if payment exceeds balance
            if ($validated['amount'] > $loan->balance) {
                return back()->with('error', 'Payment amount (KES ' . number_format($validated['amount'], 2) . ') exceeds loan balance (KES ' . number_format($loan->balance, 2) . ')');
            }

            // Warning for payments below recommended daily amount (but still allow)
            $warningMessage = '';
            if ($loan->daily_payment_amount && $validated['amount'] < $loan->daily_payment_amount) {
                $warningMessage = ' Note: Payment is below recommended daily amount of KES ' . number_format($loan->daily_payment_amount, 2) . '.';
            }

            // Generate transaction ID
            $transactionId = 'TXN' . date('YmdHis') . strtoupper(substr(md5(uniqid()), 0, 4));

            // Create payment with completed status (admin created, no approval needed)
            $payment = Payment::create([
                'loan_id' => $validated['loan_id'],
                'customer_id' => $loan->customer_id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'mpesa_receipt_number' => $validated['mpesa_receipt_number'] ?? null,
                'phone_number' => $validated['phone_number'] ?? null,
                'transaction_id' => $transactionId,
                'payment_date' => $validated['payment_date'],
                'status' => 'completed',
                'recorded_by' => auth()->id() ?? 1,
                'notes' => ($validated['notes'] ?? '') . "\nManually recorded by admin on " . now()->toDateTimeString(),
            ]);

            // Update loan
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

            return back()->with('success', 'Payment of KES ' . number_format($payment->amount, 2) . ' recorded successfully!' . $warningMessage);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create payment: ' . $e->getMessage());
        }
    }

    /**
     * Display all support tickets
     */
    public function supportTickets(Request $request)
    {
        $query = SupportTicket::with(['user', 'customer', 'assignedAdmin']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $tickets = $query->latest()->paginate(20);

        $stats = [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'resolved' => SupportTicket::whereIn('status', ['resolved', 'closed'])->count(),
        ];

        return view('admin.support-tickets', compact('tickets', 'stats'));
    }

    /**
     * View single support ticket
     */
    public function viewTicket($id)
    {
        $ticket = SupportTicket::with(['user', 'customer', 'assignedAdmin'])->findOrFail($id);
        return view('admin.support-ticket-detail', compact('ticket'));
    }

    /**
     * Update support ticket status and response
     */
    public function updateTicket(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'admin_response' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $ticket = SupportTicket::findOrFail($id);

        if (isset($validated['admin_response'])) {
            $validated['admin_response'] = ($ticket->admin_response ?? '') . "\n\n[" . now()->toDateTimeString() . " - " . auth()->user()->name . "]\n" . $validated['admin_response'];
        }

        if (in_array($validated['status'], ['resolved', 'closed']) && !$ticket->resolved_at) {
            $validated['resolved_at'] = now();
        }

        $ticket->update($validated);

        return back()->with('success', 'Ticket updated successfully');
    }

    public function registrationFees(Request $request)
    {
        $status = $request->get('status', 'all');
        $currentStatus = $status;

        $query = \App\Models\RegistrationFee::with(['user', 'recorder']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $fees = $query->latest()->paginate(20);
        $pendingCount = \App\Models\RegistrationFee::where('status', 'pending')->count();

        return view('admin.registration-fees', compact('fees', 'currentStatus', 'pendingCount'));
    }

    public function verifyRegistrationFee(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:completed,failed',
            'notes' => 'nullable|string',
        ]);

        $fee = \App\Models\RegistrationFee::with('user')->findOrFail($id);

        // Check if already verified
        if ($fee->status === 'completed') {
            return back()->with('error', 'This payment has already been verified');
        }

        if ($validated['status'] === 'completed') {
            // Verify the payment
            $fee->update([
                'status' => 'completed',
                'paid_at' => now(),
                'recorded_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Update user record
            $fee->user->update([
                'registration_fee_paid' => true,
                'registration_fee_amount' => 300.00,
                'registration_fee_paid_at' => now(),
            ]);

            // Update any loans that were awaiting registration fee
            \App\Models\Loan::where('customer_id', $fee->user->customer_id)
                ->where('status', 'awaiting_registration_fee')
                ->update(['status' => 'pending']);

            return back()->with('success', 'Registration fee verified successfully. User can now proceed with loan applications.');
        } else {
            // Mark as failed
            $fee->update([
                'status' => 'failed',
                'recorded_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            return back()->with('success', 'Registration fee marked as failed.');
        }
    }

    public function deposits(Request $request)
    {
        $status = $request->get('status', 'all');
        $currentStatus = $status;

        $query = \App\Models\Deposit::with(['loan', 'customer', 'recorder', 'verifier', 'rejector'])
            ->where('type', 'loan_deposit')
            ->whereNotNull('loan_id'); // Only show deposits with valid loan IDs

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Search by loan number, customer name, phone, or M-PESA code
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('mpesa_code', 'like', "%{$search}%")
                  ->orWhereHas('loan', function($loanQuery) use ($search) {
                      $loanQuery->where('loan_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $deposits = $query->latest()->paginate(20);
        $pendingCount = \App\Models\Deposit::where('status', 'pending')
            ->where('type', 'loan_deposit')
            ->whereNotNull('loan_id')
            ->count();

        return view('admin.deposits', compact('deposits', 'currentStatus', 'pendingCount'));
    }

    public function verifyDeposit(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:completed,failed',
            'notes' => 'nullable|string',
        ]);

        $deposit = \App\Models\Deposit::with(['loan', 'customer'])->findOrFail($id);

        // Check if already verified
        if ($deposit->status === 'completed') {
            return back()->with('error', 'This payment has already been verified');
        }

        if ($validated['status'] === 'completed') {
            // Verify the payment
            $deposit->update([
                'status' => 'completed',
                'paid_at' => now(),
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Update loan deposit_paid amount AND deduct from balance
            $loan = $deposit->loan;
            $loan->update([
                'deposit_paid' => $loan->deposit_paid + $deposit->amount,
                'deposit_paid_at' => $loan->isDepositPaid() ? now() : $loan->deposit_paid_at,
                'amount_paid' => $loan->amount_paid + $deposit->amount,
                'balance' => $loan->balance - $deposit->amount,
            ]);

            // If deposit is now fully paid, update loan status from awaiting_deposit to pending
            if ($loan->isDepositPaid() && $loan->status === 'awaiting_deposit') {
                $loan->update(['status' => 'pending']);
            }

            // Update customer total_paid
            $customer = $loan->customer;
            $customer->increment('total_paid', $deposit->amount);

            return back()->with('success', 'Deposit payment verified successfully. Loan deposit balance has been updated.');
        } else {
            // Mark as failed
            $deposit->update([
                'status' => 'failed',
                'recorded_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            return back()->with('success', 'Deposit payment marked as failed.');
        }
    }

    /**
     * Display staff users management page
     */
    public function users(Request $request)
    {
        $query = User::with(['creator'])
            ->where('role', '!=', User::ROLE_CUSTOMER)
            ->latest();

        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Search by name, email, or phone
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15);

        // Get statistics
        $stats = [
            'total_staff' => User::where('role', '!=', User::ROLE_CUSTOMER)->count(),
            'by_role' => [
                'super_admin' => User::where('role', User::ROLE_SUPER_ADMIN)->count(),
                'admin' => User::where('role', User::ROLE_ADMIN)->count(),
                'manager' => User::where('role', User::ROLE_MANAGER)->count(),
                'clerk' => User::where('role', User::ROLE_CLERK)->count(),
                'collector' => User::where('role', User::ROLE_COLLECTOR)->count(),
            ],
            'by_status' => [
                'active' => User::where('role', '!=', User::ROLE_CUSTOMER)
                    ->where('status', User::STATUS_ACTIVE)->count(),
                'inactive' => User::where('role', '!=', User::ROLE_CUSTOMER)
                    ->where('status', User::STATUS_INACTIVE)->count(),
                'suspended' => User::where('role', '!=', User::ROLE_CUSTOMER)
                    ->where('status', User::STATUS_SUSPENDED)->count(),
            ],
        ];

        return view('admin.users', compact('users', 'stats'));
    }

    /**
     * Store a new staff user
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|regex:/^0[0-9]{9}$/|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,manager,clerk,collector',
            'status' => 'required|in:active,inactive',
            'approval_limit' => 'nullable|numeric|min:0',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'pin' => Hash::make('1234'), // Default PIN
            'role' => $validated['role'],
            'status' => $validated['status'],
            'approval_limit' => $validated['approval_limit'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect('/admin/users')->with('success', 'User created successfully');
    }

    /**
     * Update an existing staff user
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::where('role', '!=', User::ROLE_CUSTOMER)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'required|string|regex:/^0[0-9]{9}$/|unique:users,phone,' . $id,
            'role' => 'required|in:admin,manager,clerk,collector',
            'status' => 'required|in:active,inactive,suspended',
            'approval_limit' => 'nullable|numeric|min:0',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'status' => $validated['status'],
            'approval_limit' => $validated['approval_limit'] ?? null,
        ]);

        return redirect('/admin/users')->with('success', 'User updated successfully');
    }

    /**
     * Delete a staff user
     */
    public function deleteUser($id)
    {
        $user = User::where('role', '!=', User::ROLE_CUSTOMER)->findOrFail($id);

        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account');
        }

        // Check if user has created other users
        if ($user->createdUsers()->exists()) {
            return back()->with('error', 'Cannot delete user who has created other users');
        }

        $user->delete();

        return redirect('/admin/users')->with('success', 'User deleted successfully');
    }

    /**
     * Update user status (activate/suspend)
     */
    public function updateUserStatus(Request $request, $id)
    {
        $user = User::where('role', '!=', User::ROLE_CUSTOMER)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:active,inactive,suspended',
        ]);

        // Prevent changing own status
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own status');
        }

        $user->update(['status' => $validated['status']]);

        $statusText = [
            'active' => 'activated',
            'inactive' => 'deactivated',
            'suspended' => 'suspended',
        ];

        return back()->with('success', "User {$statusText[$validated['status']]} successfully");
    }

    /**
     * Reset user password
     */
    public function resetUserPassword(Request $request, $id)
    {
        $user = User::where('role', '!=', User::ROLE_CUSTOMER)->findOrFail($id);

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password reset successfully');
    }
}
