<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (prefix: /api/v1)
|--------------------------------------------------------------------------
*/

// Auth routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
    });
});

// Categories
Route::apiResource('categories', CategoryController::class)
    ->only(['index', 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('categories', CategoryController::class)
        ->only(['store', 'update', 'destroy']);
});

// Products
Route::apiResource('products', ProductController::class)
    ->only(['index', 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class)
        ->only(['store', 'update', 'destroy']);
    Route::patch('/products/{product}/toggle-availability', [ProductController::class, 'toggleAvailability']);
});

// Protected routes (Customer & Admin)
Route::middleware('auth:sanctum')->group(function () {
    // Profile & Favorites
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{product}/toggle', [FavoriteController::class, 'toggle']);

    // Addresses
    Route::apiResource('addresses', AddressController::class);
    Route::patch('/addresses/{address}/set-default', [AddressController::class, 'setDefault']);

    // Orders
    Route::get('/orders/stats', [OrderController::class, 'stats']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    // Admin Customers Management
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::get('/customers/{id}', [CustomerController::class, 'show']);
});


