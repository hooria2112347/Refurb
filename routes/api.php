<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomRequestController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ArtistProfileController;
use App\Http\Controllers\AdminController;

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

// Fetch all products (for browsing)
Route::get('/products/all', [ProductController::class, 'getAllProducts']);
Route::get('/products/{id}', [ProductController::class, 'show']);
// Protected routes (require a valid Bearer token)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/users/{id}', [AuthController::class, 'showProfile']);
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
    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::post('/products', [ProductController::class, 'store']); 
    Route::get('/products', [ProductController::class, 'index']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
}); 
    // Shows the currently authenticated user
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });        
    Route::middleware('auth:sanctum')->group(function () {
        // PROJECTS
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::get('/projects/{id}', [ProjectController::class, 'show']);
        Route::get('/projects/{id}/available-artists', [ProjectController::class, 'getAvailableArtists']);
        Route::post('/projects/{id}/complete', [ProjectController::class, 'completeProject']);
        Route::post('/projects/{id}/feedback', [ProjectController::class, 'submitFeedback']);
        Route::get('/projects/{id}/feedback', [ProjectController::class, 'getFeedback']);

    
        // INVITATIONS
        Route::post('/projects/{projectId}/invite', [InvitationController::class, 'invite']);
        Route::post('/invitations/{id}/respond', [InvitationController::class, 'respond']);
    
        // JOIN REQUESTS
        Route::post('/projects/{projectId}/request-join', [InvitationController::class, 'requestJoin']);
        Route::post('/invitations/{id}/owner-respond', [InvitationController::class, 'ownerRespondToRequest']);
    
        // FEEDBACK AND RATING SYSTEM
        Route::post('/projects/{id}/feedback', [ProjectController::class, 'submitFeedback']);
        Route::get('/projects/{id}/feedback', [ProjectController::class, 'getFeedback']);
    });
    

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/my-owned-projects', [ArtistProfileController::class, 'getOwnedProjects']);
        Route::get('/my-collaborations', [ArtistProfileController::class, 'getCollaborationProjects']);
      });


      Route::middleware(['auth:sanctum'])->group(function () {
        // Retrieve pending invitations for the logged-in user
        Route::get('/my-invitations', [InvitationController::class, 'indexPendingInvitations']);
    
        // Respond to an invitation
        Route::post('/invitations/{id}/respond', [InvitationController::class, 'respond']);
    });


    Route::get('/projects/{id}/available-artists', [ProjectController::class, 'getAvailableArtists']);

    Route::get('/my-sent-invitations', [InvitationController::class, 'indexSentInvitations']);

    Route::delete('/invitations/{id}', [InvitationController::class, 'destroy']);
});
    // ADMIN product management
    Route::get('/admin/feedback', [AdminController::class, 'getAllFeedback']);
    Route::get('/admin/products', [AdminProductController::class, 'index']);
    Route::put('/admin/products/{id}', [AdminProductController::class, 'update']);
    Route::delete('/admin/products/{id}', [AdminProductController::class, 'destroy']);