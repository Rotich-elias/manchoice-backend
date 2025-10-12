<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\LoanController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\MpesaController;
use App\Http\Controllers\API\ProductController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Customer routes
    Route::apiResource('customers', CustomerController::class);
    Route::get('customers/{customer}/stats', [CustomerController::class, 'stats']);

    // Loan routes
    Route::apiResource('loans', LoanController::class);
    Route::post('loans/{loan}/approve', [LoanController::class, 'approve']);
    Route::post('loans/{loan}/reject', [LoanController::class, 'reject']);

    // Payment routes
    Route::apiResource('payments', PaymentController::class);
    Route::post('payments/{payment}/reverse', [PaymentController::class, 'reverse']);

    // M-PESA routes
    Route::post('mpesa/stk-push', [MpesaController::class, 'stkPush']);
    Route::post('mpesa/check-status', [MpesaController::class, 'checkStatus']);

    // Product routes
    Route::apiResource('products', ProductController::class);
    Route::post('products/{product}/update-stock', [ProductController::class, 'updateStock']);
    Route::post('products/{product}/toggle-availability', [ProductController::class, 'toggleAvailability']);
    Route::get('products/category/{category}', [ProductController::class, 'byCategory']);

});

// M-PESA Callback routes (public - no authentication required)
Route::post('mpesa/callback', [MpesaController::class, 'callback']);
Route::post('mpesa/timeout', [MpesaController::class, 'timeout']);
Route::post('mpesa/result', [MpesaController::class, 'result']);
