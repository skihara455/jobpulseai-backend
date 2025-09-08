<?php

namespace App\Http\Controllers;

use App\Models\Mentor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MentorController extends Controller
{
    /**
     * GET /api/v1/mentors?q=&location=&expertise=&per_page=
     * Public listing with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $q = Mentor::query();

        if ($search = $request->query('q')) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('headline', 'like', "%{$search}%")
                   ->orWhere('expertise', 'like', "%{$search}%");
            });
        }

        if ($location = $request->query('location')) {
            $q->where('location', 'like', "%{$location}%");
        }

        if ($expertise = $request->query('expertise')) {
            $q->where('expertise', 'like', "%{$expertise}%");
        }

        $mentors = $q->orderBy('name')->paginate(
            perPage: (int) $request->query('per_page', 10)
        );

        return response()->json($mentors);
    }

    /**
     * GET /api/v1/mentors/{mentor}
     * Public detail view.
     */
    public function show(Mentor $mentor): JsonResponse
    {
        return response()->json($mentor);
    }

    /**
     * POST /api/v1/mentors  (admin only)
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Mentor::class);

        $data = $request->validate([
            'user_id'      => 'sometimes|nullable|integer|exists:users,id',
            'name'         => 'required|string|max:255',
            'headline'     => 'nullable|string|max:255',
            'expertise'    => 'nullable|string|max:1000', // comma-separated tags
            'location'     => 'nullable|string|max:255',
            'website'      => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'github_url'   => 'nullable|url|max:255',
            'bio'          => 'nullable|string|max:5000',
            'avatar_url'   => 'nullable|url|max:255',
        ]);

        $mentor = Mentor::create($data);

        return response()->json([
            'message' => 'Mentor created.',
            'mentor'  => $mentor,
        ], 201);
    }

    /**
     * PUT /api/v1/mentors/{mentor}  (admin only)
     */
    public function update(Request $request, Mentor $mentor): JsonResponse
    {
        $this->authorize('update', $mentor);

        $data = $request->validate([
            'user_id'      => 'sometimes|nullable|integer|exists:users,id',
            'name'         => 'sometimes|string|max:255',
            'headline'     => 'sometimes|nullable|string|max:255',
            'expertise'    => 'sometimes|nullable|string|max:1000',
            'location'     => 'sometimes|nullable|string|max:255',
            'website'      => 'sometimes|nullable|url|max:255',
            'linkedin_url' => 'sometimes|nullable|url|max:255',
            'github_url'   => 'sometimes|nullable|url|max:255',
            'bio'          => 'sometimes|nullable|string|max:5000',
            'avatar_url'   => 'sometimes|nullable|url|max:255',
        ]);

        $mentor->update($data);

        return response()->json([
            'message' => 'Mentor updated.',
            'mentor'  => $mentor,
        ]);
    }

    /**
     * DELETE /api/v1/mentors/{mentor}  (admin only)
     */
    public function destroy(Mentor $mentor): JsonResponse
    {
        $this->authorize('delete', $mentor);

        $mentor->delete();

        return response()->json(['message' => 'Mentor deleted.']);
    }
}
