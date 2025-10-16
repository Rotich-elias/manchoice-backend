<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'customers' => Customer::count(),
            'loans' => Loan::count(),
            'pending_loans' => Loan::where('status', 'pending')->count(),
            'active_loans' => Loan::whereIn('status', ['approved', 'active'])->count(),
            'total_borrowed' => Loan::whereIn('status', ['approved', 'active', 'completed'])->sum('total_amount'),
            'total_paid' => Payment::where('status', 'completed')->sum('amount'),
            'products' => Product::count(),
            'low_stock_products' => Product::where('stock_quantity', '<', 10)->count(),
        ];

        $recentLoans = Loan::with('customer')->latest()->take(5)->get();
        $pendingLoans = Loan::with('customer')->where('status', 'pending')->latest()->get();

        return view('admin.dashboard', compact('stats', 'recentLoans', 'pendingLoans'));
    }

    public function customers()
    {
        $customers = Customer::with('loans')->latest()->paginate(20);
        return view('admin.customers', compact('customers'));
    }

    public function loans(Request $request)
    {
        $query = Loan::with(['customer', 'approver']);

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $loans = $query->latest()->paginate(20);
        $currentStatus = $request->status ?? 'all';

        return view('admin.loans', compact('loans', 'currentStatus'));
    }

    public function loanDetail($id)
    {
        $loan = Loan::with(['customer', 'approver', 'payments'])->findOrFail($id);
        return view('admin.loan-detail', compact('loan'));
    }

    public function products()
    {
        $products = Product::latest()->paginate(20);
        return view('admin.products', compact('products'));
    }

    public function approveLoan($id)
    {
        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'pending') {
            return back()->with('error', 'Only pending loans can be approved');
        }

        if (!auth()->check()) {
            return back()->with('error', 'You must be logged in to approve loans');
        }

        $loan->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'disbursement_date' => now()->toDateString(),
        ]);

        $customer = $loan->customer;
        $customer->total_borrowed += $loan->total_amount;
        $customer->save();

        return back()->with('success', 'Loan approved successfully');
    }

    public function rejectLoan(Request $request, $id)
    {
        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'pending') {
            return back()->with('error', 'Only pending loans can be rejected');
        }

        $rejectionReason = $request->input('rejection_reason', 'No reason provided');

        $loan->update([
            'status' => 'rejected',
            'approved_by' => auth()->id() ?? 1,
            'approved_at' => now(),
            'notes' => ($loan->notes ? $loan->notes . "\n\n" : '') .
                      "REJECTED: " . $rejectionReason,
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
            'image_url' => 'nullable|url',
            'stock_quantity' => 'required|integer|min:0',
            'is_available' => 'nullable|boolean',
        ]);

        // Set defaults
        $validated['is_available'] = $validated['is_available'] ?? true;
        $validated['discount_percentage'] = $validated['discount_percentage'] ?? 0;

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
            'image_url' => 'nullable|url',
            'stock_quantity' => 'required|integer|min:0',
            'is_available' => 'nullable|boolean',
        ]);

        // Set defaults
        $validated['discount_percentage'] = $validated['discount_percentage'] ?? 0;

        $product->update($validated);

        return back()->with('success', 'Product updated successfully');
    }

    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return back()->with('success', 'Product deleted successfully');
    }

    public function payments(Request $request)
    {
        $query = Payment::with(['loan.customer', 'customer']);

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
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
            'recorded_by' => auth()->id() ?? 1,
            'notes' => ($payment->notes ?? '') . "\nApproved by admin on " . now()->toDateTimeString()
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
            'notes' => ($payment->notes ?? '') . "\nRejected by admin: " . $rejectionReason
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

            return back()->with('success', 'Payment of KES ' . number_format($payment->amount, 2) . ' recorded successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create payment: ' . $e->getMessage());
        }
    }
}
