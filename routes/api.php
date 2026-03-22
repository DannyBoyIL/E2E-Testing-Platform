<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::apiResource('orders', OrderController::class);

    Route::get('payments',      [PaymentController::class, 'index']);
    Route::post('payments',     [PaymentController::class, 'store']);
    Route::get('payments/{id}', [PaymentController::class, 'show']);
});
