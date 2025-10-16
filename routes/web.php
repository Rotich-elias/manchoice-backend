<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AuthController;

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
});
