<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    // GET /api/v1/profile  (you already have /auth/me; this is an alias if you want)
    public function show(Request $request): JsonResponse
    {
        $u = $request->user()->load('role');
        return response()->json([
            'success' => true,
            'user' => [
                'id'          => $u->id,
                'name'        => $u->name,
                'email'       => $u->email,
                'role_id'     => $u->role_id,
                'role'        => optional($u->role)->name,
                'phone'       => $u->phone,
                'headline'    => $u->headline,
                'location'    => $u->location,
                'website'     => $u->website,
                'linkedin_url'=> $u->linkedin_url,
                'github_url'  => $u->github_url,
                'bio'         => $u->bio,
                'avatar_url'  => $u->avatar_url,
                'created_at'  => $u->created_at?->toISOString(),
                'updated_at'  => $u->updated_at?->toISOString(),
            ],
        ]);
    }

    // PUT /api/v1/profile
    public function update(Request $request): JsonResponse
    {
        $u = $request->user();

        $data = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'phone'        => 'sometimes|nullable|string|max:50',
            'headline'     => 'sometimes|nullable|string|max:255',
            'location'     => 'sometimes|nullable|string|max:255',
            'website'      => 'sometimes|nullable|url|max:255',
            'linkedin_url' => 'sometimes|nullable|url|max:255',
            'github_url'   => 'sometimes|nullable|url|max:255',
            'bio'          => 'sometimes|nullable|string|max:5000',
            'avatar_url'   => 'sometimes|nullable|url|max:255',
        ]);

        $u->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated.',
            'user'    => $u->fresh(),
        ]);
    }
}
