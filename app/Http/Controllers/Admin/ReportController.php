<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\Product;
use App\Exports\CustomersExport;
use App\Exports\LoansExport;
use App\Exports\PaymentsExport;
use App\Exports\ProductsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Export customers report
     */
    public function customers(Request $request)
    {
        $format = $request->get('format', 'pdf');
        $customers = Customer::with(['loans'])->latest()->get();

        if ($format === 'excel') {
            return Excel::download(new CustomersExport(), 'customers_report_' . date('Y-m-d') . '.xlsx');
        }

        // PDF Export
        $pdf = Pdf::loadView('reports.customers', compact('customers'));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('customers_report_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export loans report
     */
    public function loans(Request $request)
    {
        $format = $request->get('format', 'pdf');
        $status = $request->get('status');

        $query = Loan::with(['customer', 'approver']);

        if ($status) {
            $query->where('status', $status);
        }

        $loans = $query->latest()->get();

        if ($format === 'excel') {
            return Excel::download(new LoansExport($status), 'loans_report_' . date('Y-m-d') . '.xlsx');
        }

        // PDF Export
        $pdf = Pdf::loadView('reports.loans', compact('loans', 'status'));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('loans_report_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export loan detail report
     */
    public function loanDetail(Request $request, $id)
    {
        $loan = Loan::with(['customer', 'approver', 'payments', 'items.product'])->findOrFail($id);

        $pdf = Pdf::loadView('reports.loan-detail', compact('loan'));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('loan_' . $loan->loan_number . '_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export payments report
     */
    public function payments(Request $request)
    {
        $format = $request->get('format', 'pdf');
        $status = $request->get('status');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Payment::with(['customer', 'loan', 'recorder']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($dateFrom) {
            $query->whereDate('payment_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('payment_date', '<=', $dateTo);
        }

        $payments = $query->latest('payment_date')->get();

        if ($format === 'excel') {
            return Excel::download(new PaymentsExport($status, $dateFrom, $dateTo), 'payments_report_' . date('Y-m-d') . '.xlsx');
        }

        // PDF Export
        $pdf = Pdf::loadView('reports.payments', compact('payments', 'status', 'dateFrom', 'dateTo'));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('payments_report_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export products report
     */
    public function products(Request $request)
    {
        $format = $request->get('format', 'pdf');
        $products = Product::latest()->get();

        if ($format === 'excel') {
            return Excel::download(new ProductsExport(), 'products_report_' . date('Y-m-d') . '.xlsx');
        }

        // PDF Export
        $pdf = Pdf::loadView('reports.products', compact('products'));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('products_report_' . date('Y-m-d') . '.pdf');
    }
}
