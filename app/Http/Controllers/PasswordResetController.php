<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class PasswordResetController extends Controller
{
    // Method to send reset code to email
    public function sendResetCode(Request $request)
{
    $request->validate(['email' => 'required|email']);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $code = rand(100000, 999999);
    Cache::forever('password_reset_code_' . $request->email, $code);

    \Log::info('Generated code: ' . $code . ' for email: ' . $request->email);
    \Log::info('Cached code (no expiration): ' . Cache::get('password_reset_code_' . $request->email));

    Mail::raw("Your password reset code is: $code", function ($message) use ($request) {
        $message->to($request->email)->subject('Password Reset Code');
    });

    return response()->json(['message' => 'Reset code sent to email']);
}

    // Method to verify the reset code
    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|numeric',
        ]);

        $storedCode = Cache::get('password_reset_code_' . $request->email);

        // Debug: Log stored and provided codes
        \Log::info('Stored code: ' . $storedCode . ', Provided code: ' . $request->code);

        if (!$storedCode || $storedCode != $request->code) {
            return response()->json(['message' => 'Invalid or expired code'], 400);
        }

        return response()->json(['message' => 'Code verified successfully']);
    }

    // Method to reset the password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|numeric',
            'password' => 'required|min:6|confirmed',
        ]);

        $storedCode = Cache::get('password_reset_code_' . $request->email);

        // Debug: Log stored and provided codes
        \Log::info('Reset request for email: ' . $request->email);
        \Log::info('Stored code: ' . $storedCode . ', Provided code: ' . $request->code);

        if (!$storedCode || $storedCode != $request->code) {
            return response()->json(['message' => 'Invalid or expired code'], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        // Clear the reset code from cache
        Cache::forget('password_reset_code_' . $request->email);

        return response()->json(['message' => 'Password reset successfully']);
    }
}
