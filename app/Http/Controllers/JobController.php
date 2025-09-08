<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class JobController extends Controller
{
    /**
     * GET /api/v1/jobs
     * Query params: q, location, type, status, company_id, per_page
     */
    public function index(Request $request): JsonResponse
    {
        $q = Job::with(['employer:id,name,email', 'company:id,name,logo_url'])
            ->when($request->query('status'), fn ($qq, $status) => $qq->where('status', $status))
            ->when($request->query('location'), fn ($qq, $loc) => $qq->where('location', 'like', "%{$loc}%"))
            ->when($request->query('type'), fn ($qq, $type) => $qq->where('type', $type))
            ->when($request->query('company_id'), fn ($qq, $cid) => $qq->where('company_id', $cid))
            ->search($request->query('q'))
            ->orderByDesc('created_at');

        $jobs = $q->paginate((int) $request->query('per_page', 12));

        return response()->json($jobs);
    }

    /**
     * GET /api/v1/jobs/{job}
     */
    public function show(Job $job): JsonResponse
    {
        $job->load(['employer:id,name,email', 'company:id,name,logo_url,website,location,industry']);
        return response()->json($job);
    }

    /**
     * POST /api/v1/jobs  (employer or admin)
     * Body: title, location, type, salary_min, salary_max, tags, description, status, company_id?
     */
    public function store(Request $request): JsonResponse
    {
        // Policy: employer/admin can create
        $this->authorize('create', Job::class);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'location'    => 'required|string|max:255',
            'type'        => 'required|string|max:50', // e.g. full-time, part-time, remote
            'salary_min'  => 'nullable|integer|min:0',
            'salary_max'  => 'nullable|integer|min:0',
            'tags'        => 'nullable|string|max:1000',
            'description' => 'required|string',
            'status'      => 'required|string|in:open,closed,draft',
            'company_id'  => 'nullable|integer|exists:companies,id',
        ]);

        $user = $request->user();

        // If company_id is provided, ensure current user owns it (unless admin)
        if (!empty($data['company_id']) && !($user && $user->isAdmin())) {
            $owns = Company::where('id', $data['company_id'])
                ->where('owner_id', $user?->id)
                ->exists();

            if (!$owns) {
                abort(403, 'You can only attach companies you own.');
            }
        }

        // salary_min <= salary_max (if both set)
        if (!empty($data['salary_min']) && !empty($data['salary_max']) && $data['salary_min'] > $data['salary_max']) {
            abort(422, 'salary_min cannot be greater than salary_max.');
        }

        $job = Job::create($data + [
            'employer_id' => $user?->id,
        ]);

        return response()->json([
            'message' => 'Job created.',
            'job'     => $job->fresh(['employer:id,name,email', 'company:id,name,logo_url']),
        ], 201);
    }

    /**
     * PUT /api/v1/jobs/{job}  (owner or admin)
     */
    public function update(Request $request, Job $job): JsonResponse
    {
        // Policy: owner/admin
        $this->authorize('update', $job);

        $data = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'location'    => 'sometimes|string|max:255',
            'type'        => 'sometimes|string|max:50',
            'salary_min'  => 'sometimes|nullable|integer|min:0',
            'salary_max'  => 'sometimes|nullable|integer|min:0',
            'tags'        => 'sometimes|nullable|string|max:1000',
            'description' => 'sometimes|string',
            'status'      => 'sometimes|string|in:open,closed,draft',
            'company_id'  => 'sometimes|nullable|integer|exists:companies,id',
        ]);

        $user = $request->user();

        // If company is being changed, enforce ownership (unless admin)
        if (array_key_exists('company_id', $data) && !empty($data['company_id']) && !($user && $user->isAdmin())) {
            $owns = Company::where('id', $data['company_id'])
                ->where('owner_id', $user?->id)
                ->exists();

            if (!$owns) {
                abort(403, 'You can only attach companies you own.');
            }
        }

        // salary bounds sanity
        $min = $data['salary_min'] ?? $job->salary_min;
        $max = $data['salary_max'] ?? $job->salary_max;
        if (!is_null($min) && !is_null($max) && $min > $max) {
            abort(422, 'salary_min cannot be greater than salary_max.');
        }

        $job->update($data);

        return response()->json([
            'message' => 'Job updated.',
            'job'     => $job->fresh(['employer:id,name,email', 'company:id,name,logo_url']),
        ]);
    }

    /**
     * DELETE /api/v1/jobs/{job}  (owner or admin)
     */
    public function destroy(Request $request, Job $job): JsonResponse
    {
        // Policy: owner/admin
        $this->authorize('delete', $job);

        $job->delete();

        return response()->json(['message' => 'Job deleted.']);
    }
}
