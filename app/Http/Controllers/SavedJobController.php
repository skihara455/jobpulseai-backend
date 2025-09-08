<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SavedJobController extends Controller
{
    // POST /api/v1/jobs/{job}/save
    public function save(Request $request, Job $job): JsonResponse
    {
        $user = $request->user();

        // prevent employers saving their own job if you want (optional):
        // if ($user->id === $job->employer_id) abort(403, 'You cannot save your own job.');

        $user->savedJobs()->syncWithoutDetaching([$job->id]);

        return response()->json(['message' => 'Job saved']);
    }

    // DELETE /api/v1/jobs/{job}/save
    public function unsave(Request $request, Job $job): JsonResponse
    {
        $user = $request->user();
        $user->savedJobs()->detach($job->id);

        return response()->json(['message' => 'Job unsaved']);
    }

    // GET /api/v1/saved-jobs
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $saved = $user->savedJobs()
            ->orderBy('saved_jobs.created_at', 'desc')
            ->paginate((int) $request->query('per_page', 10));

        return response()->json($saved);
    }
}
