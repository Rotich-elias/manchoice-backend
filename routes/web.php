<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\PartRequestController;
use App\Http\Controllers\Admin\ReportController;

Route::get('/', function () {
    return redirect('/admin');
});

// Admin authentication routes (no middleware)
Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
});

// Protected admin routes (requires admin middleware)
Route::prefix('admin')->middleware(['admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/customers', [DashboardController::class, 'customers']);
    Route::get('/customers/{id}', [DashboardController::class, 'customerDetail']);
    Route::post('/customers/{id}/update-credit-limit', [DashboardController::class, 'updateCreditLimit']);
    Route::get('/loans', [DashboardController::class, 'loans']);
    Route::get('/loans/{id}', [DashboardController::class, 'loanDetail']);
    Route::get('/products', [DashboardController::class, 'products']);
    Route::get('/payments', [DashboardController::class, 'payments']);
    Route::post('/loans/{id}/approve', [DashboardController::class, 'approveLoan']);
    Route::post('/loans/{id}/reject', [DashboardController::class, 'rejectLoan']);
    Route::post('/payments/{id}/approve', [DashboardController::class, 'approvePayment']);
    Route::post('/payments/{id}/reject', [DashboardController::class, 'rejectPayment']);
    Route::post('/payments/create', [DashboardController::class, 'createPayment']);
    Route::post('/products/{id}/update-stock', [DashboardController::class, 'updateProductStock']);
    Route::post('/products/store', [DashboardController::class, 'storeProduct']);
    Route::post('/products/{id}/update', [DashboardController::class, 'updateProduct']);
    Route::post('/products/{id}/delete', [DashboardController::class, 'deleteProduct']);

    // Part Requests routes
    Route::get('/part-requests', [PartRequestController::class, 'index']);
    Route::get('/part-requests/{id}', [PartRequestController::class, 'show']);
    Route::post('/part-requests/{id}/update-status', [PartRequestController::class, 'updateStatus']);

    // Support Tickets routes
    Route::get('/support-tickets', [DashboardController::class, 'supportTickets']);
    Route::get('/support-tickets/{id}', [DashboardController::class, 'viewTicket']);
    Route::post('/support-tickets/{id}/update', [DashboardController::class, 'updateTicket']);

    // Report export routes
    Route::get('/reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
    Route::get('/reports/loans', [ReportController::class, 'loans'])->name('reports.loans');
    Route::get('/reports/loans/{id}/detail', [ReportController::class, 'loanDetail'])->name('reports.loan-detail');
    Route::get('/reports/payments', [ReportController::class, 'payments'])->name('reports.payments');
    Route::get('/reports/products', [ReportController::class, 'products'])->name('reports.products');
});
