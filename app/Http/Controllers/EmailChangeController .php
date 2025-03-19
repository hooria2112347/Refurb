<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class EmailChangeController extends Controller
{

    public function sendChangeCode(Request $request)
    {
        $request->validate([
            'current_email' => 'required|email',
            'new_email'     => 'required|email',
        ]);

        // Check if user exists with current_email
        $user = User::where('email', $request->current_email)->first();
        if (!$user) {
            return response()->json(['message' => 'Current email not found'], 404);
        }

        // Optional: Ensure the new email isn't already taken
        $existingUser = User::where('email', $request->new_email)->first();
        if ($existingUser) {
            return response()->json(['message' => 'New email is already in use'], 400);
        }

        // Generate a random 6-digit code
        $code = rand(100000, 999999);

        // Store the code in cache (no expiration)
        // You could also include the current email in the cache key if you like:
        // "email_change_code_{$user->id}_{$request->new_email}"
        Cache::forever('email_change_code_' . $request->new_email, $code);

        // Log for debugging (remove in production)
        \Log::info("Generated email change code: $code for new email: {$request->new_email}");

        // Send the code to the new email
        Mail::raw("Your email change code is: $code", function ($message) use ($request) {
            $message->to($request->new_email)->subject('Email Change Code');
        });

        return response()->json(['message' => 'Email change code sent to the new email address']);
    }

    /**
     * Verify the change-email code (optional separate step if you want).
     */
    public function verifyChangeCode(Request $request)
    {
        $request->validate([
            'new_email' => 'required|email',
            'code'      => 'required|numeric',
        ]);

        // Retrieve the code from cache
        $storedCode = Cache::get('email_change_code_' . $request->new_email);

        \Log::info("Stored code: {$storedCode}, Provided code: {$request->code}");

        // Check if code is valid
        if (!$storedCode || $storedCode != $request->code) {
            return response()->json(['message' => 'Invalid or expired code'], 400);
        }

        return response()->json(['message' => 'Code verified successfully']);
    }

    /**
     * Change the user's email after verifying the code.
     */
    public function changeEmail(Request $request)
    {
        $request->validate([
            'current_email' => 'required|email',
            'new_email'     => 'required|email',
            'code'          => 'required|numeric',
        ]);

        // Retrieve the code from cache
        $storedCode = Cache::get('email_change_code_' . $request->new_email);
        \Log::info("Attempting email change from {$request->current_email} to {$request->new_email}");
        \Log::info("Stored code: {$storedCode}, Provided code: {$request->code}");

        // Validate the code
        if (!$storedCode || $storedCode != $request->code) {
            return response()->json(['message' => 'Invalid or expired code'], 400);
        }

        // Find user by current_email
        $user = User::where('email', $request->current_email)->first();
        if (!$user) {
            return response()->json(['message' => 'Current email not found'], 404);
        }

        // Optional: double-check new_email isn't taken (if not checked earlier)
        $existingUser = User::where('email', $request->new_email)->first();
        if ($existingUser) {
            return response()->json(['message' => 'New email is already in use'], 400);
        }

        // Update the user's email
        $user->email = $request->new_email;
        $user->save();

        // Remove the code from cache
        Cache::forget('email_change_code_' . $request->new_email);

        return response()->json(['message' => 'Email changed successfully']);
    }
}
