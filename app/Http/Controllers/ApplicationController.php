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
     * - Creates a new application if none exists for (job_id, user_id)
     * - If one exists, updates cover_letter / resume fields instead of rejecting
     */
    public function store(Request $request, Job $job): JsonResponse
    {
        // Policy: seekers/admin can create applications for an OPEN job
        $this->authorize('create', [Application::class, $job]);

        $data = $request->validate([
            'cover_letter' => ['nullable', 'string'],
            'resume_url'   => ['sometimes', 'nullable', 'url', 'max:255'],
            'resume_path'  => ['sometimes', 'nullable', 'string', 'max:255'],
            // optionally allow status override for admins:
            'status'       => ['sometimes', 'in:pending,reviewed,accepted,rejected'],
        ]);

        $user = $request->user();

        // Upsert behavior: create if missing, else update fields provided
        $app = Application::firstOrNew([
            'job_id'  => $job->id,
            'user_id' => $user->id,
        ]);

        $wasNew = !$app->exists;

        // Fill only provided fields; keep previous values when updating
        if (array_key_exists('cover_letter', $data)) {
            $app->cover_letter = $data['cover_letter'];
        }
        if (array_key_exists('resume_url', $data)) {
            $app->resume_url = $data['resume_url'];
        }
        if (array_key_exists('resume_path', $data)) {
            $app->resume_path = $data['resume_path'];
        }

        if ($wasNew) {
            $app->status = $data['status'] ?? 'pending';
        } elseif (array_key_exists('status', $data)) {
            $app->status = $data['status']; // allow controlled updates
        }

        $app->save();

        // Notify employer only on first-time submission
        if ($wasNew) {
            $job->loadMissing('employer');
            if ($job->employer) {
                $job->employer->notify(
                    new NewJobApplication($job, $user, $app->cover_letter)
                );
            }
        }

        return response()->json([
            'message'     => $wasNew ? 'Application submitted.' : 'Application updated.',
            'application' => $app->fresh(),
        ], $wasNew ? 201 : 200);
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
