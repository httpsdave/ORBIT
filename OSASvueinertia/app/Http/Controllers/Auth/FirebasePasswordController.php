<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\Rules;

class FirebasePasswordController extends Controller
{
    /**
     * Sync password change from Firebase to Laravel
     */
    public function syncPasswordReset(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        // Update the user's password in Laravel
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.'
        ]);
    }

    /**
     * Check if user exists in Laravel before Firebase reset
     */
    public function checkUserExists(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        return response()->json([
            'exists' => $user !== null,
            'user' => $user ? ['id' => $user->id, 'name' => $user->name, 'email' => $user->email] : null
        ]);
    }
}