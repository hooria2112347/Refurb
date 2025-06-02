<?php

// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Hash;
use Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show user profile with rating information
     */
    public function showProfile($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Convert to array and add calculated fields
            $userData = $user->toArray();
            
            // Calculate average rating manually
            if ($user->rating_count > 0) {
                $userData['average_rating'] = round($user->total_rating / $user->rating_count, 1);
            } else {
                $userData['average_rating'] = 0;
            }
            return response()->json($userData, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    /**
     * Get user products
     */
    public function getUserProducts($id)
    {
        try {
            $user = User::findOrFail($id);
            $products = $user->products()->get();
            
            return response()->json($products, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    /**
     * Rate a user
     */
      public function rateUser(Request $request, $id)
    {
        try {
            // Log the incoming request
            Log::info('Rating request received', [
                'user_id' => $id,
                'request_data' => $request->all(),
                'auth_user' => Auth::id()
            ]);

            // Validate the rating
            $validator = Validator::make($request->all(), [
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                Log::warning('Rating validation failed', [
                    'errors' => $validator->errors(),
                    'request_data' => $request->all()
                ]);
                
                return response()->json([
                    'message' => 'Invalid rating data.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if user is authenticated
            $currentUser = Auth::user();
            if (!$currentUser) {
                Log::warning('Unauthenticated rating attempt');
                return response()->json(['message' => 'Authentication required.'], 401);
            }

            Log::info('Current user authenticated', [
                'current_user_id' => $currentUser->id,
                'target_user_id' => $id
            ]);

            // Check if user is trying to rate themselves
            if ($currentUser->id == $id) {
                Log::warning('User attempting to rate themselves', [
                    'user_id' => $currentUser->id
                ]);
                return response()->json(['message' => 'You cannot rate yourself.'], 403);
            }

            // Find the user to be rated
            $userToRate = User::findOrFail($id);
            
            Log::info('User to rate found', [
                'user_id' => $userToRate->id,
                'current_rating_count' => $userToRate->rating_count,
                'current_total_rating' => $userToRate->total_rating
            ]);

            // Store previous values for logging
            $previousRatingCount = $userToRate->rating_count;
            $previousTotalRating = $userToRate->total_rating;

            // Add the rating
            $userToRate->addRating($request->rating);

            Log::info('Rating added successfully', [
                'user_id' => $userToRate->id,
                'new_rating' => $request->rating,
                'previous_count' => $previousRatingCount,
                'new_count' => $userToRate->rating_count,
                'previous_total' => $previousTotalRating,
                'new_total' => $userToRate->total_rating,
                'new_average' => $userToRate->average_rating
            ]);

            return response()->json([
                'message' => 'Rating submitted successfully.',
                'new_average' => $userToRate->average_rating,
                'total_ratings' => $userToRate->rating_count,
                'user_data' => [
                    'id' => $userToRate->id,
                    'name' => $userToRate->name,
                    'rating_count' => $userToRate->rating_count,
                    'total_rating' => $userToRate->total_rating,
                    'average_rating' => $userToRate->average_rating
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('User not found for rating', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'User not found.'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Rating submission error', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to submit rating.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user ratings (for display purposes - returns empty array since we don't store individual ratings)
     */
    public function getUserRatings($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Since we're not storing individual ratings, return summary info
            return response()->json([
                'total_ratings' => $user->rating_count,
                'average_rating' => $user->average_rating,
                'ratings' => [] // Empty array since we don't store individual ratings
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    /**
     * Handle user registration and return a token.
     */
    public function register(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|digits_between:1,11',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:artist,scrapSeller,general',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid input data.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Hash the password before storing
        $data = $request->all();
        $data['password'] = Hash::make($data['password']);

        // Create the user in the database
        $user = User::create($data);

        // Generate Sanctum token
        $token = $user->createToken('auth_token', ['add-product'])->plainTextToken;

        // Return a success response with the token
        return response()->json([
            'message' => 'User registered successfully.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    /**
     * Handle user login and return a token.
     */
    public function login(Request $request)
    {
        // Validate credentials (e.g., email, password)
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt login
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        // If successful, create a personal access token with specific abilities
        $user = Auth::user();
        $token = $user->createToken('AppToken', ['add-product'])->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user,
        ], 200);
    }

    /**
     * Handle user logout by revoking the current token.
     */
    public function logout(Request $request)
    {
        try {
            // Log incoming request details
            \Log::info('Logout attempt:', [
                'user' => $request->user(),
                'headers' => $request->headers->all(),
            ]);

            // Retrieve the current access token
            $token = $request->user()->currentAccessToken();

            // Log token details
            \Log::info('Token type:', ['type' => get_class($token)]);

            // Check for invalid token type (e.g., TransientToken)
            if (is_null($token) || get_class($token) === 'Laravel\Sanctum\TransientToken') {
                throw new \Exception('Invalid token type: TransientToken cannot be deleted.');
            }

            // Revoke the token
            $token->delete();
            \Log::info('Token revoked successfully.', ['tokenId' => $token->id]);

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Logout error: ' . $e->getMessage(), [
                'userId' => $request->user()->id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Logout failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle password change for authenticated users.
     */
    public function changePassword(Request $request)
    {
        // Validate the request inputs
        $request->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required|min:8|same:confirmPassword',
            'confirmPassword' => 'required',
        ]);

        // Get the authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        // Verify the current password
        if (!Hash::check($request->currentPassword, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 400);
        }

        // Update the password
        $user->password = Hash::make($request->newPassword);
        $user->save();

        return response()->json(['message' => 'Password changed successfully.'], 200);
    }
}