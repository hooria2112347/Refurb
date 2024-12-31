<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomRequestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (no auth needed)
Route::post('/signup', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Other public routes
Route::post('/password/forgot', [PasswordResetController::class, 'sendResetCode']);
Route::post('/password/verify-code', [PasswordResetController::class, 'verifyResetCode']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products', [ProductController::class, 'index']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);

// Protected routes (require a valid Bearer token)
Route::middleware(['auth:sanctum'])->group(function () {     // Logout (requires user to be authenticated, so the token can be revoked)
    Route::post('/logout', [AuthController::class, 'logout']);
    // Shows the currently authenticated user
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });
    
    // Change password
    Route::post('/change-password', [AuthController::class, 'changePassword']);



    // Custom requests
    Route::post('/custom-requests', [CustomRequestController::class, 'store']);
    Route::get('/custom-requests', [CustomRequestController::class, 'index']);
});
