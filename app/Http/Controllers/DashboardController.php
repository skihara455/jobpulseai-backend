<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Job;
use App\Models\Application;

class DashboardController extends Controller
{
    // GET /api/v1/dashboard/summary
    public function summary(Request $request): JsonResponse
    {
        $u = $request->user();

        if ($u->isAdmin()) {
            return response()->json([
                'role'               => 'admin',
                'totals' => [
                    'users'         => User::count(),
                    'jobs'          => Job::count(),
                    'applications'  => Application::count(),
                    'unread_notifications' => $u->unreadNotifications()->count(),
                ],
            ]);
        }

        if ($u->isEmployer()) {
            $myJobsCount = Job::where('employer_id', $u->id)->count();
            $appsToMyJobs = Application::whereIn(
                'job_id',
                Job::where('employer_id', $u->id)->pluck('id')
            )->count();

            return response()->json([
                'role'               => 'employer',
                'totals' => [
                    'my_jobs'       => $myJobsCount,
                    'applications_to_my_jobs' => $appsToMyJobs,
                    'unread_notifications'    => $u->unreadNotifications()->count(),
                ],
            ]);
        }

        // default: seeker (or mentor)
        $myApplications = Application::where('user_id', $u->id)->count();
        $mySaved = $u->savedJobs()->count();

        return response()->json([
            'role'               => $u->isSeeker() ? 'seeker' : ($u->isMentor() ? 'mentor' : 'user'),
            'totals' => [
                'my_applications'        => $myApplications,
                'my_saved_jobs'          => $mySaved,
                'unread_notifications'   => $u->unreadNotifications()->count(),
            ],
        ]);
    }
}
