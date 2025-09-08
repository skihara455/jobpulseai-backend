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
use App\Http\Controllers\ToolsController; // <-- correct import

Route::prefix('v1')->group(function () {
    // -------- Health --------
    Route::get('health', fn () => [
        'app'    => config('app.name'),
        'status' => 'OK',
    ]);

    // -------- Public Auth --------
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login',    [AuthController::class, 'login']);

    // -------- Public Roles --------
    Route::get('roles', [RoleController::class, 'fetchRoles']);

    // -------- Public Jobs (read) --------
    Route::get('jobs',       [JobController::class, 'index']);
    Route::get('jobs/{job}', [JobController::class, 'show']);

    // -------- Mentors (public read) --------
    Route::get('mentors',          [MentorController::class, 'index']);
    Route::get('mentors/{mentor}', [MentorController::class, 'show']);

    // -------- Companies (public read) --------
    Route::get('companies',           [CompanyController::class, 'index']);
    Route::get('companies/{company}', [CompanyController::class, 'show']);

    // -------- Protected (requires Sanctum token) --------
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me',      [AuthController::class, 'me']);

        // Profile
        Route::get('profile', [ProfileController::class, 'show']);   // alias of /auth/me with extra fields
        Route::put('profile', [ProfileController::class, 'update']);

        // Uploads (avatar & resume)
        Route::post('profile/avatar',   [UploadController::class, 'uploadAvatar']);
        Route::delete('profile/avatar', [UploadController::class, 'deleteAvatar']);
        Route::post('profile/resume',   [UploadController::class, 'uploadResume']);
        Route::delete('profile/resume', [UploadController::class, 'deleteResume']);

        // Dashboard
        Route::get('dashboard/summary', [DashboardController::class, 'summary']);

        // Roles (admin-only via policy)
        Route::post('roles',          [RoleController::class, 'saveRole']);
        Route::get('roles/{role}',    [RoleController::class, 'fetchRole']);
        Route::put('roles/{role}',    [RoleController::class, 'updateRole']);
        Route::delete('roles/{role}', [RoleController::class, 'deleteRole']);

        // Jobs (write: employer or admin; update/delete: owner or admin)
        Route::post('jobs',         [JobController::class, 'store']);
        Route::put('jobs/{job}',    [JobController::class, 'update']);
        Route::delete('jobs/{job}', [JobController::class, 'destroy']);

        // Applications
        Route::post('jobs/{job}/apply',       [ApplicationController::class, 'store']);            // seeker/admin apply
        Route::get('applications',            [ApplicationController::class, 'myApplications']);   // current user's apps
        Route::get('jobs/{job}/applications', [ApplicationController::class, 'jobApplications']);  // employer/admin view apps for a job

        // Saved Jobs
        Route::post('jobs/{job}/save',    [SavedJobController::class, 'save']);
        Route::delete('jobs/{job}/save',  [SavedJobController::class, 'unsave']);
        Route::get('saved-jobs',          [SavedJobController::class, 'index']);

        // Career Tools (stubs)
        Route::post('tools/cv-builder',    [ToolsController::class, 'cvBuilder']);
        Route::post('tools/ai-job-match',  [ToolsController::class, 'aiJobMatch']);
        Route::post('tools/skill-builder', [ToolsController::class, 'skillBuilder']);
        Route::post('tools/quiz/submit',   [ToolsController::class, 'submitQuiz']);

        // Notifications
        Route::get('notifications',             [NotificationController::class, 'index']);
        Route::post('notifications/{id}/read',  [NotificationController::class, 'markRead']);
        Route::post('notifications/read-all',   [NotificationController::class, 'markAllRead']);
        Route::delete('notifications/{id}',     [NotificationController::class, 'destroy']);

        // Mentors (admin-only CRUD, policy enforced)
        Route::post('mentors',            [MentorController::class, 'store']);
        Route::put('mentors/{mentor}',    [MentorController::class, 'update']);
        Route::delete('mentors/{mentor}', [MentorController::class, 'destroy']);

        // Companies (admin or employer can manage; policy enforced)
        Route::post('companies',             [CompanyController::class, 'store']);
        Route::put('companies/{company}',    [CompanyController::class, 'update']);
        Route::delete('companies/{company}', [CompanyController::class, 'destroy']);

        // Company logo uploads (owner or admin)
        Route::post('companies/{company}/logo',   [UploadController::class, 'uploadCompanyLogo']);
        Route::delete('companies/{company}/logo', [UploadController::class, 'deleteCompanyLogo']);
    });
});

