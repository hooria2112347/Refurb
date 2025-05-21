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
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// User registration & login
Route::post('/signup', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Password reset (forgot/verify code/reset)
Route::post('/password/forgot', [PasswordResetController::class, 'sendResetCode']);
Route::post('/password/verify-code', [PasswordResetController::class, 'verifyResetCode']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);

// Public product browsing
Route::get('/products/all', [ProductController::class, 'getAllProducts']);
Route::get('/products/{id}', [ProductController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Require Sanctum Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->group(function () {

 Route::get('orders/seller', [OrderController::class, 'getSellerOrders']);
 // User order routes
    Route::prefix('orders')->group(function () {
        // Customer routes
        Route::post('/checkout', [OrderController::class, 'checkout']);
        Route::get('/', [OrderController::class, 'getUserOrders']);
        Route::get('/{orderId}', [OrderController::class, 'getOrderDetails']);
        Route::put('/{orderId}/cancel', [OrderController::class, 'cancelOrder']);
        
        // Add seller routes - these match what your Vue component is calling

        // Route::get('/seller', [OrderController::class, 'getSellerOrders']);
        Route::put('/{orderId}/status', [OrderController::class, 'updateOrderItemStatus']);
    });

    Route::get('/users/{id}', [AuthController::class, 'showProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Show the currently authenticated user
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    // ---------------------
    // Custom Request Routes
    // ---------------------
    Route::post('/custom-requests', [CustomRequestController::class, 'store']);
    Route::get('/custom-requests', [CustomRequestController::class, 'index']);
    Route::get('/custom-requests/accepted', [CustomRequestController::class, 'getAcceptedRequests']);
    Route::get('/custom-requests/{id}', [CustomRequestController::class, 'show']);
    Route::delete('/custom-requests/comments/{commentId}', [CustomRequestController::class, 'deleteComment']);

    // Accept/Decline requests
    Route::post('/custom-requests/{id}/accept', [CustomRequestController::class, 'accept']);
    Route::post('/custom-requests/{id}/decline', [CustomRequestController::class, 'decline']);

    // Comments on custom requests
    Route::post('/custom-requests/{id}/comments', [CustomRequestController::class, 'addComment']);

    // --------------
    // Product Routes
    // --------------
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // -----------
    // Projects
    // -----------
    Route::get('/projects/completed', [PortfolioController::class, 'completedProjects']);
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    Route::get('/projects/{id}/available-artists', [ProjectController::class, 'getAvailableArtists']);

    // Complete project, feedback
    Route::post('/projects/{id}/complete', [ProjectController::class, 'completeProject']);
    Route::post('/projects/{id}/feedback', [ProjectController::class, 'submitFeedback']);
    Route::get('/projects/{id}/feedback', [ProjectController::class, 'getFeedback']);

    // -----------
    // Invitations
    // -----------
    // Invite artists to a project
    Route::post('/projects/{projectId}/invite', [InvitationController::class, 'invite']);

    // Respond to an invitation (invitee)
    Route::post('/invitations/{id}/respond', [InvitationController::class, 'respond']);

    // Join requests (artist requesting to join a project)
    Route::post('/projects/{projectId}/request-join', [InvitationController::class, 'requestJoin']);
    // Project owner responds to a join request
    Route::post('/invitations/{id}/owner-respond', [InvitationController::class, 'ownerRespondToRequest']);

    // Artist profile & collaboration
    Route::get('/my-owned-projects', [ArtistProfileController::class, 'getOwnedProjects']);
    Route::get('/my-collaborations', [ArtistProfileController::class, 'getCollaborationProjects']);

    // Invitation management
    Route::get('/my-invitations', [InvitationController::class, 'indexPendingInvitations']);
    Route::get('/my-sent-invitations', [InvitationController::class, 'indexSentInvitations']);
    Route::delete('/invitations/{id}', [InvitationController::class, 'destroy']);

    // ---------------
    // Review Routes
    // ---------------
    Route::post('/products/{id}/reviews', [ReviewController::class, 'store']);

    // -------------------------
    // Admin Product Management
    // -------------------------
    Route::get('/admin/feedback', [AdminController::class, 'getAllFeedback']);
    Route::get('/admin/products', [AdminController::class, 'index']);
    Route::put('/admin/products/{id}', [AdminController::class, 'update']);
    Route::delete('/admin/products/{id}', [AdminController::class, 'destroy']);

    // ---------------
    // Wishlist Routes
    // ---------------
    Route::post('/wishlist/add/{productId}', [WishlistController::class, 'addToWishlist']);
    Route::delete('/wishlist/remove/{productId}', [WishlistController::class, 'removeFromWishlist']);
    Route::get('/wishlist', [WishlistController::class, 'getWishlist']);

    // ---------------
    // Cart Routes
    // ---------------
    Route::post('/cart/add/{productId}', [CartController::class, 'addToCart']);
    Route::put('/cart/update/{productId}', [CartController::class, 'updateCartItem']);
    Route::delete('/cart/remove/{productId}', [CartController::class, 'removeFromCart']);
    Route::get('/cart', [CartController::class, 'getCart']);
    Route::delete('/cart/clear', [CartController::class, 'clearCart']);

    // ---------------
    // Portfolio Routes
    // ---------------
    Route::get('/portfolio', [PortfolioController::class, 'index']);
    Route::put('/portfolio/{id}', [PortfolioController::class, 'updatePortfolioProject']);
    Route::delete('/portfolio/{id}', [PortfolioController::class, 'removeFromPortfolio']);


    
});