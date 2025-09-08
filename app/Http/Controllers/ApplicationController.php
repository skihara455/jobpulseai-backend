<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Notifications\NewJobApplication;

class ApplicationController extends Controller
{
    /**
     * POST /api/v1/jobs/{job}/apply  (seeker or admin)
     */
    public function store(Request $request, Job $job): JsonResponse
    {
        // Policy: seekers/admin can create applications for an OPEN job
        $this->authorize('create', [Application::class, $job]);

        $data = $request->validate([
            'cover_letter' => 'nullable|string',
            'resume_url'   => 'sometimes|nullable|url|max:255',
            'resume_path'  => 'sometimes|nullable|string|max:255',
        ]);

        // Prevent duplicate application (also enforce via unique index if present)
        $existing = Application::where('job_id', $job->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message'     => 'You have already applied to this job.',
                'application' => $existing,
            ], 200);
        }

        $app = Application::create([
            'job_id'       => $job->id,
            'user_id'      => $request->user()->id,
            'cover_letter' => $data['cover_letter'] ?? null,
            'resume_url'   => $data['resume_url'] ?? null,
            'resume_path'  => $data['resume_path'] ?? null,
            'status'       => 'pending',
        ]);

        // Notify employer
        $job->loadMissing('employer');
        if ($job->employer) {
            $job->employer->notify(
                new NewJobApplication($job, $request->user(), $data['cover_letter'] ?? null)
            );
        }

        return response()->json([
            'message'     => 'Application submitted.',
            'application' => $app,
        ], 201);
    }

    /**
     * GET /api/v1/applications  (current user's applications)
     */
    public function myApplications(Request $request): JsonResponse
    {
        $apps = Application::with('job:id,title,company_id,location,type,status')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate((int) $request->query('per_page', 12));

        return response()->json($apps);
    }

    /**
     * GET /api/v1/jobs/{job}/applications  (employer/admin)
     */
    public function jobApplications(Request $request, Job $job): JsonResponse
    {
        // Policy: job owner or admin may list applications for this job
        $this->authorize('viewAnyForJob', [Application::class, $job]);

        $apps = Application::with('user:id,name,email')
            ->where('job_id', $job->id)
            ->orderByDesc('created_at')
            ->paginate((int) $request->query('per_page', 12));

        return response()->json($apps);
    }

    /**
     * DELETE /api/v1/applications/{application}  (applicant or admin)
     */
    public function destroy(Request $request, Application $application): JsonResponse
    {
        // Policy: applicant/admin can delete
        $this->authorize('delete', $application);

        $application->delete();

        return response()->json(['message' => 'Application deleted.']);
    }
}
