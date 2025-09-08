<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken; // for safe logout checks

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/register
     * Body: name, email, password, password_confirmation, role_id
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email:rfc,dns|unique:users,email',
            'password'              => 'required|string|min:8|confirmed', // expects password_confirmation
            'role_id'               => 'required|integer|exists:roles,id',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id'  => $data['role_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'user'    => $this->publicUser($user),
        ], 201);
    }

    /**
     * POST /api/v1/auth/login
     * Body: email, password
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|string|email:rfc,dns',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success'    => true,
            'message'    => 'Login successful.',
            'user'       => $this->publicUser($user),
            'abilities'  => $user->abilities(),
            'token'      => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     * Header: Authorization: Bearer <token>
     * Handles PersonalAccessToken (Bearer) and TransientToken (cookie) safely.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => true,
                'message' => 'No active session.',
            ]);
        }

        $currentToken = $user->currentAccessToken();

        if ($currentToken instanceof PersonalAccessToken) {
            // Revoke only this token (Bearer token case)
            $user->tokens()->where('id', $currentToken->id)->delete();
        } else {
            // Cookie (TransientToken) case — revoke all personal tokens
            $user->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * GET /api/v1/auth/me
     * Header: Authorization: Bearer <token>
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success'   => true,
            'user'      => $this->publicUser($user),
            'abilities' => $user->abilities(),
        ]);
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
            'created_at' => $user->created_at?->toISOString(),
            'updated_at' => $user->updated_at?->toISOString(),
        ];
    }
}
