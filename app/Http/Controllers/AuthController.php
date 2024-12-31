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
    public function register(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',  // password_confirmation is automatically checked
            'role' => 'required|in:artist,scrapSeller',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid email or password.', 'errors' => $validator->errors()], 422);
        }

        // Hash the password before storing
        $data = $request->all();
        $data['password'] = Hash::make($data['password']);

        // Create the user in the database
        $user = User::create($data);

        // Return a success response
        return response()->json(['message' => 'User registered successfully'], 201);
    }
    public function login(Request $request)
{
    // Validate credentials (e.g., email, password)
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Attempt login
    if (!auth()->attempt($credentials)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    // If successful, create a personal access token
    $user = auth()->user();
    $token = $user->createToken('AppToken')->plainTextToken;  // <-- Important

    return response()->json([
        'access_token' => $token,
        'token_type'   => 'Bearer',
        'user'         => $user,
    ], 200);
}public function logout(Request $request)
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
        return response()->json(['message' => 'User not authenticated'], 401);
    }

    // Verify the current password
    if (!Hash::check($request->currentPassword, $user->password)) {
        return response()->json(['message' => 'Current password is incorrect'], 400);
    }

    // Update the password
    $user->password = Hash::make($request->newPassword);
    $user->save();

    return response()->json(['message' => 'Password changed successfully'], 200);
}
}