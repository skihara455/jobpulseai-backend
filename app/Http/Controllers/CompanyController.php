<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    /**
     * GET /api/v1/companies (public)
     * Query params: q, location, industry, size, per_page
     */
    public function index(Request $request): JsonResponse
    {
        $q = Company::query()
            ->when($request->query('q'), function ($qq, $term) {
                $like = "%{$term}%";
                $qq->where(function ($sub) use ($like) {
                    $sub->where('name', 'like', $like)
                        ->orWhere('industry', 'like', $like)
                        ->orWhere('location', 'like', $like)
                        ->orWhere('description', 'like', $like);
                });
            })
            ->when($request->query('location'), fn ($qq, $loc) => $qq->where('location', 'like', "%{$loc}%"))
            ->when($request->query('industry'), fn ($qq, $ind) => $qq->where('industry', $ind))
            ->when($request->query('size'), fn ($qq, $sz) => $qq->where('size', $sz))
            ->orderByDesc('created_at');

        $companies = $q->paginate((int) $request->query('per_page', 12));

        return response()->json($companies);
    }

    /**
     * GET /api/v1/companies/{company} (public)
     */
    public function show(Company $company): JsonResponse
    {
        $company->loadMissing('owner:id,name,email');
        return response()->json($company);
    }

    /**
     * POST /api/v1/companies  (employer or admin)
     */
    public function store(Request $request): JsonResponse
    {
        // ✅ Policy: employer/admin may create
        $this->authorize('create', Company::class);

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'website'      => 'nullable|url|max:255',
            'location'     => 'nullable|string|max:255',
            'industry'     => 'nullable|string|max:255',
            'size'         => 'nullable|string|max:100',   // e.g. "1-10", "11-50", etc.
            'description'  => 'nullable|string',
            'logo_path'    => 'nullable|string|max:255',
            'logo_url'     => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'twitter_url'  => 'nullable|url|max:255',
        ]);

        $company = Company::create($data + [
            'owner_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Company created.',
            'company' => $company,
        ], 201);
    }

    /**
     * PUT /api/v1/companies/{company}  (owner or admin)
     */
    public function update(Request $request, Company $company): JsonResponse
    {
        // ✅ Policy: owner/admin
        $this->authorize('update', $company);

        $data = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'website'      => 'sometimes|nullable|url|max:255',
            'location'     => 'sometimes|nullable|string|max:255',
            'industry'     => 'sometimes|nullable|string|max:255',
            'size'         => 'sometimes|nullable|string|max:100',
            'description'  => 'sometimes|nullable|string',
            'logo_path'    => 'sometimes|nullable|string|max:255',
            'logo_url'     => 'sometimes|nullable|url|max:255',
            'linkedin_url' => 'sometimes|nullable|url|max:255',
            'twitter_url'  => 'sometimes|nullable|url|max:255',
        ]);

        $company->update($data);

        return response()->json([
            'message' => 'Company updated.',
            'company' => $company->fresh(),
        ]);
    }

    /**
     * DELETE /api/v1/companies/{company}  (owner or admin)
     */
    public function destroy(Request $request, Company $company): JsonResponse
    {
        // ✅ Policy: owner/admin
        $this->authorize('delete', $company);

        $company->delete();

        return response()->json(['message' => 'Company deleted.']);
    }
}
