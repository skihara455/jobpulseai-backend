<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /** Toggle single-session (true = revoke old tokens on login) */
    private const SINGLE_SESSION = true;

    /**
     * POST /api/v1/auth/register
     * Body: name, email, password, password_confirmation, role_id
     * Returns: { success, message, user, token, token_type, abilities }
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            // In dev, avoid DNS checks which can fail: use email:rfc
            'email'    => 'required|string|email:rfc|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id'  => 'required|integer|exists:roles,id',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id'  => $data['role_id'],
        ]);

        $user->load('role');

        $abilities = $this->abilitiesForRole(optional($user->role)->name);
        $token     = $user->createToken('auth-token', $abilities)->plainTextToken;

        return response()->json([
            'success'    => true,
            'message'    => 'Registration successful.',
            'user'       => $this->publicUser($user),
            'abilities'  => $abilities,
            'token'      => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * POST /api/v1/auth/login
     * Body: email, password
     * Returns: { success, message, user, token, token_type, abilities }
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            // Use email:rfc (DNS can fail on local domains)
            'email'    => 'required|string|email:rfc',
            'password' => 'required|string',
        ]);

        // --- Throttle brute-force attempts ---
        $throttleKey = Str::lower($data['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'message' => "Too many attempts. Try again in {$seconds} seconds.",
            ], 429);
        }

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60); // decay 60s per miss
            // Keep message generic for security
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // Login OK → clear throttle
        RateLimiter::clear($throttleKey);

        // If you want only one active token per user/device:
        if (self::SINGLE_SESSION) {
            $user->tokens()->delete();
        }

        $user->load('role');

        $abilities = $this->abilitiesForRole(optional($user->role)->name);
        $token     = $user->createToken('auth-token', $abilities)->plainTextToken;

        return response()->json([
            'success'    => true,
            'message'    => 'Login successful.',
            'user'       => $this->publicUser($user),
            'abilities'  => $abilities,
            'token'      => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * POST /api/v1/auth/logout (auth:sanctum)
     * Header: Authorization: Bearer <token>
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            // Already logged out (no valid token) → still OK to tell frontend to clear local state
            return response()->json(['success' => true, 'message' => 'No active session.']);
        }

        $currentToken = $user->currentAccessToken();

        if ($currentToken instanceof PersonalAccessToken) {
            $currentToken->delete(); // revoke only this token/session
        } else {
            $user->tokens()->delete(); // fallback
        }

        return response()->json(['success' => true, 'message' => 'Logged out successfully.']);
    }

    /**
     * GET /api/v1/auth/me (auth:sanctum)
     * Header: Authorization: Bearer <token>
     * Returns: { success, user, abilities }
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $user->loadMissing('role');

        return response()->json([
            'success'   => true,
            'user'      => $this->publicUser($user),
            'abilities' => $this->abilitiesForRole(optional($user->role)->name),
        ]);
    }

    /**
     * Map a role name to Sanctum token abilities.
     * Adjust to match your authorization needs/policies.
     */
    private function abilitiesForRole(?string $roleName): array
    {
        $role = Str::of($roleName ?? 'user')->lower()->toString();

        return match ($role) {
            'admin'    => ['admin', 'manage-users', 'manage-jobs', 'manage-mentors', 'manage-companies', '*'],
            'employer' => ['employer', 'post-jobs', 'view-applications'],
            'mentor'   => ['mentor', 'view-mentees', 'post-content'],
            default    => ['user'],
        };
    }

    /**
     * Normalize the user payload for responses.
     */
    private function publicUser(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'role_id'    => $user->role_id,
            'role'       => optional($user->role)->name,
            'created_at' => optional($user->created_at)?->toISOString(),
            'updated_at' => optional($user->updated_at)?->toISOString(),
        ];
    }
}
