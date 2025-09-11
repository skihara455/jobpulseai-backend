<?php

use Illuminate\Support\Facades\Route;

// Controllers (import all you use here)
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\SavedJobController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MentorController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UploadController;

Route::prefix('v1')->group(function () {
    /**
     * -------------------------
     * Public (no auth required)
     * -------------------------
     * Keep /auth/login & /auth/register OUTSIDE auth:sanctum,
     * or you'll get 401 when trying to log in.
     */
    Route::prefix('auth')->group(function () {
        Route::post('login',    [AuthController::class, 'login'])->name('auth.login');
        Route::post('register', [AuthController::class, 'register'])->name('auth.register');
    });

    // (Optional) Public browse endpoints go here (jobs index/show, etc.)
    // Route::get('jobs', [JobController::class, 'index']);
    // Route::get('jobs/{job}', [JobController::class, 'show']);

    /**
     * -------------------------
     * Protected (auth required)
     * -------------------------
     */
    Route::middleware('auth:sanctum')->group(function () {

        // Authenticated user info & logout
        Route::prefix('auth')->group(function () {
            Route::get('me',     [AuthController::class, 'me'])->name('auth.me');
            Route::post('logout',[AuthController::class, 'logout'])->name('auth.logout');
        });

        // ----- YOUR ROUTES (from your snippet) -----

        // Applications
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

/**
 * JSON 404 fallback for any unmatched API route.
 * Note: api.php routes are automatically under /api, so the final path is /api/...
 */
Route::fallback(function () {
    return response()->json([
        'error' => [
            'message' => 'Endpoint not found',
            'code'    => 404,
        ]
    ], 404);
});
