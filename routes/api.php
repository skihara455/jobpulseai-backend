<?php

use Illuminate\Support\Facades\Route;

// Core controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\SavedJobController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MentorController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ToolsController;

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
| - Public routes are readable by anyone.
| - Protected routes require a valid Sanctum token (Bearer ...).
| - All v1 routes are rate-limited with throttle:api.
| - A JSON 404 fallback ensures the API never returns HTML by mistake.
*/

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    // -------- Preflight (CORS) --------
    // Prevent 405s for OPTIONS requests from the frontend (CORS preflight)
    Route::options('{any}', fn () => response()->noContent())
        ->where('any', '.*')
        ->name('preflight');

    // -------- Health (for demos & monitoring) --------
    Route::get('health', fn () => response()->json([
        'app'    => config('app.name'),
        'status' => 'OK',
        'time'   => now()->toIso8601String(),
    ]))->name('health');

    // -------- Debug (helps verify your Authorization header arrives) --------
    Route::get('debug/echo-auth', function (\Illuminate\Http\Request $r) {
        return response()->json([
            'authorization' => $r->header('Authorization'),
            'accept'        => $r->header('Accept'),
        ]);
    })->name('debug.echo-auth');

    // -------- Public Auth --------
    Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('auth/login',    [AuthController::class, 'login'])->name('auth.login');

    // -------- Public Roles --------
    Route::get('roles', [RoleController::class, 'fetchRoles'])->name('roles.index');

    // -------- Public Jobs (read) --------
    Route::get('jobs',       [JobController::class, 'index'])->name('jobs.index');
    Route::get('jobs/{job}', [JobController::class, 'show'])->name('jobs.show');

    // -------- Mentors (public read) --------
    Route::get('mentors',          [MentorController::class, 'index'])->name('mentors.index');
    Route::get('mentors/{mentor}', [MentorController::class, 'show'])->name('mentors.show');

    // -------- Companies (public read) --------
    Route::get('companies',           [CompanyController::class, 'index'])->name('companies.index');
    Route::get('companies/{company}', [CompanyController::class, 'show'])->name('companies.show');

    // -------- Protected (requires Sanctum token) --------
    Route::middleware(['auth:sanctum'])->group(function () {
        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('auth/me',      [AuthController::class, 'me'])->name('auth.me');

        // Profile
        Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');   // alias to /auth/me w/ extras
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

        // Uploads (avatar & resume)
        Route::post('profile/avatar',   [UploadController::class, 'uploadAvatar'])->name('profile.avatar.upload');
        Route::delete('profile/avatar', [UploadController::class, 'deleteAvatar'])->name('profile.avatar.delete');
        Route::post('profile/resume',   [UploadController::class, 'uploadResume'])->name('profile.resume.upload');
        Route::delete('profile/resume', [UploadController::class, 'deleteResume'])->name('profile.resume.delete');

        // Dashboard
        Route::get('dashboard/summary', [DashboardController::class, 'summary'])->name('dashboard.summary');

        // Roles (admin-only via policy)
        Route::post('roles',          [RoleController::class, 'saveRole'])->name('roles.store');
        Route::get('roles/{role}',    [RoleController::class, 'fetchRole'])->name('roles.show');
        Route::put('roles/{role}',    [RoleController::class, 'updateRole'])->name('roles.update');
        Route::delete('roles/{role}', [RoleController::class, 'deleteRole'])->name('roles.destroy');

        // Jobs (write: employer or admin; update/delete: owner or admin)
        Route::post('jobs',         [JobController::class, 'store'])->name('jobs.store');
        Route::put('jobs/{job}',    [JobController::class, 'update'])->name('jobs.update');
        Route::delete('jobs/{job}', [JobController::class, 'destroy'])->name('jobs.destroy');

        // Applications
        Route::post('jobs/{job}/apply',       [ApplicationController::class, 'store'])->name('applications.store');
        Route::get('applications',            [ApplicationController::class, 'myApplications'])->name('applications.mine');
        Route::get('jobs/{job}/applications', [ApplicationController::class, 'jobApplications'])->name('applications.byJob');

        // Saved Jobs
        Route::post('jobs/{job}/save',   [SavedJobController::class, 'save'])->name('saved-jobs.save');
        Route::delete('jobs/{job}/save', [SavedJobController::class, 'unsave'])->name('saved-jobs.unsave');
        Route::get('saved-jobs',         [SavedJobController::class, 'index'])->name('saved-jobs.index');

        // Career Tools (stubs)
        Route::post('tools/cv-builder',    [ToolsController::class, 'cvBuilder'])->name('tools.cvBuilder');
        Route::post('tools/ai-job-match',  [ToolsController::class, 'aiJobMatch'])->name('tools.aiJobMatch');
        Route::post('tools/skill-builder', [ToolsController::class, 'skillBuilder'])->name('tools.skillBuilder');
        Route::post('tools/quiz/submit',   [ToolsController::class, 'submitQuiz'])->name('tools.quiz.submit');

        // Notifications
        Route::get('notifications',            [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all',  [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
        Route::delete('notifications/{id}',    [NotificationController::class, 'destroy'])->name('notifications.destroy');

        // Mentors (admin-only CRUD, policy enforced)
        Route::post('mentors',            [MentorController::class, 'store'])->name('mentors.store');
        Route::put('mentors/{mentor}',    [MentorController::class, 'update'])->name('mentors.update');
        Route::delete('mentors/{mentor}', [MentorController::class, 'destroy'])->name('mentors.destroy');

        // Companies (admin or employer can manage; policy enforced)
        Route::post('companies',             [CompanyController::class, 'store'])->name('companies.store');
        Route::put('companies/{company}',    [CompanyController::class, 'update'])->name('companies.update');
        Route::delete('companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');

        // Company logo uploads (owner or admin)
        Route::post('companies/{company}/logo',   [UploadController::class, 'uploadCompanyLogo'])->name('companies.logo.upload');
        Route::delete('companies/{company}/logo', [UploadController::class, 'deleteCompanyLogo'])->name('companies.logo.delete');
    });
});

// -------- JSON 404 fallback for any unmatched API route --------
Route::fallback(function () {
    return response()->json([
        'error' => [
            'message' => 'Endpoint not found',
            'code'    => 404,
        ]
    ], 404);
});
