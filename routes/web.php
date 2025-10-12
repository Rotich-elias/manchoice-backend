<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

Route::get('/', function () {
    return redirect('/admin');
});

// Admin routes
Route::prefix('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/customers', [DashboardController::class, 'customers']);
    Route::get('/loans', [DashboardController::class, 'loans']);
    Route::get('/loans/{id}', [DashboardController::class, 'loanDetail']);
    Route::get('/products', [DashboardController::class, 'products']);
    Route::post('/loans/{id}/approve', [DashboardController::class, 'approveLoan']);
    Route::post('/loans/{id}/reject', [DashboardController::class, 'rejectLoan']);
    Route::post('/products/{id}/update-stock', [DashboardController::class, 'updateProductStock']);
});
