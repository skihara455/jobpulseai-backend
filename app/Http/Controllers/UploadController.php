<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    // -----------------------
    // Profile: Avatar Upload
    // -----------------------
    // POST /api/v1/profile/avatar
    public function uploadAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'avatar' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048', // 2MB
        ]);

        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->avatar_path = $path;
        $user->avatar_url  = Storage::url($path);
        $user->save();

        return response()->json([
            'success'    => true,
            'message'    => 'Avatar uploaded.',
            'avatar_url' => $user->avatar_url,
        ]);
    }

    // DELETE /api/v1/profile/avatar
    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->avatar_path = null;
        $user->avatar_url  = null;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Avatar deleted.']);
    }

    // -----------------------
    // Profile: Resume Upload
    // -----------------------
    // POST /api/v1/profile/resume
    public function uploadResume(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'resume' => 'required|file|mimes:pdf,doc,docx|max:5120', // 5MB
        ]);

        if ($user->resume_path && Storage::disk('public')->exists($user->resume_path)) {
            Storage::disk('public')->delete($user->resume_path);
        }

        $path = $request->file('resume')->store('resumes', 'public');

        $user->resume_path = $path;
        $user->resume_url  = Storage::url($path);
        $user->save();

        return response()->json([
            'success'     => true,
            'message'     => 'Resume uploaded.',
            'resume_url'  => $user->resume_url,
        ]);
    }

    // DELETE /api/v1/profile/resume
    public function deleteResume(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->resume_path && Storage::disk('public')->exists($user->resume_path)) {
            Storage::disk('public')->delete($user->resume_path);
        }

        $user->resume_path = null;
        $user->resume_url  = null;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Resume deleted.']);
    }

    // -----------------------
    // Company: Logo Upload
    // -----------------------
    // POST /api/v1/companies/{company}/logo
    public function uploadCompanyLogo(Request $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $data = $request->validate([
            'logo' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048', // 2MB
        ]);

        if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
            Storage::disk('public')->delete($company->logo_path);
        }

        $path = $request->file('logo')->store('company-logos', 'public');

        $company->logo_path = $path;
        $company->logo_url  = Storage::url($path);
        $company->save();

        return response()->json([
            'success'  => true,
            'message'  => 'Company logo uploaded.',
            'logo_url' => $company->logo_url,
        ]);
    }

    // DELETE /api/v1/companies/{company}/logo
    public function deleteCompanyLogo(Request $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
            Storage::disk('public')->delete($company->logo_path);
        }

        $company->logo_path = null;
        $company->logo_url  = null;
        $company->save();

        return response()->json(['success' => true, 'message' => 'Company logo deleted.']);
    }
}
