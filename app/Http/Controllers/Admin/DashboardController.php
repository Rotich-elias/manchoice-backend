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

        $loan->update([
            'status' => 'approved',
            'approved_by' => auth()->id() ?? 1,
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

        return view('admin.payments', compact('payments', 'currentStatus', 'pendingCount'));
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
}
