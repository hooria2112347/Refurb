<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomRequestController;

/*
|----------------------------------------------------------------------
| API Routes
|----------------------------------------------------------------------
*/

// Public routes (no auth needed)
Route::post('/signup', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Other public routes
Route::post('/password/forgot', [PasswordResetController::class, 'sendResetCode']);
Route::post('/password/verify-code', [PasswordResetController::class, 'verifyResetCode']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);

// Protected routes (require a valid Bearer token)
Route::middleware(['auth:sanctum'])->group(function () {

       // Change password
       Route::post('/change-password', [AuthController::class, 'changePassword']);

       // Custom request routes
       Route::post('/custom-requests', [CustomRequestController::class, 'store']);
       Route::get('/custom-requests', [CustomRequestController::class, 'index']);
       Route::get('/custom-requests/accepted', [CustomRequestController::class, 'getAcceptedRequests']);
   
   
       // Fetch the details of a specific custom request
       Route::get('/custom-requests/{id}', [CustomRequestController::class, 'show']); // New route added

    Route::delete('/custom-requests/comments/{commentId}', [CustomRequestController::class, 'deleteComment']);

    // Route to accept a custom request
    Route::post('custom-requests/{id}/accept', [CustomRequestController::class, 'accept']);

    // Route to decline a custom request
    Route::post('custom-requests/{id}/decline', [CustomRequestController::class, 'decline']);

    // Route for adding a comment
    Route::post('custom-requests/{id}/comments', [CustomRequestController::class, 'addComment']);

    // Logout (requires user to be authenticated, so the token can be revoked)
    Route::post('/logout', [AuthController::class, 'logout']);

    // Product routes
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Shows the currently authenticated user
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

 
});

