<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Auth API Controller - Bearer tokens with Laravel Sanctum
 *
 * Module 1 (PHP)                    Laravel
 * login.php + generateSecureToken() -> login() + createToken()
 * requireAuth()                     -> auth:sanctum middleware
 * (no logout)                       -> logout() revokes the token
 */
class AuthController extends Controller
{
    /**
     * POST /api/login
     * Check the password, then issue a Bearer token.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        // Hash::check compares the plain password with the stored hash
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid email or password'], 401);
        }

        // Token name, abilities ('*' = all), expiration time
        $token = $user->createToken('api-token', ['*'], now()->addHour())->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'user' => $user->name,
        ]);
    }

    /**
     * GET /api/me  (protected)
     * Sanctum found the user that owns the token.
     */
    public function me(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }

    /**
     * POST /api/logout  (protected)
     * Delete only the token used in this request.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
