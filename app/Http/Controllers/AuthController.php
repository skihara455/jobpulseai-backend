<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

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
            'password' => Hash::make($data['password']), // ok even with casts
            'role_id'  => $data['role_id'],
        ])->load('role');

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
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // Optional: single-session style—clear old tokens
        $user->tokens()->delete();

        $token = $user->createToken('auth-token')->plainTextToken;
        $user->load('role');

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
     * POST /api/v1/auth/logout (auth:sanctum)
     * Header: Authorization: Bearer <token>
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => true, 'message' => 'No active session.']);
        }

        $currentToken = $user->currentAccessToken();
        if ($currentToken instanceof PersonalAccessToken) {
            $user->tokens()->where('id', $currentToken->id)->delete(); // revoke only this token
        } else {
            $user->tokens()->delete(); // cookie/transient case
        }

        return response()->json(['success' => true, 'message' => 'Logged out successfully.']);
    }

    /**
     * GET /api/v1/auth/me (auth:sanctum)
     * Header: Authorization: Bearer <token>
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('role');

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
