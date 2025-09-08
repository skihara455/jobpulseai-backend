<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\Job;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        // Users can list their own applications; admins can list all (if needed)
        return $user->isAdmin() || $user->isSeeker() || $user->isEmployer();
    }

    public function view(User $user, Application $application): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Applicant can view their own application
        if ($application->user_id === $user->id) {
            return true;
        }

        // Employer can view apps to their own jobs
        return $user->isEmployer()
            && $application->job
            && $application->job->employer_id === $user->id;
    }

    public function create(User $user): bool
    {
        // Only seekers or admins can apply to jobs
        return $user->isSeeker() || $user->isAdmin();
    }

    public function delete(User $user, Application $application): bool
    {
        // Allow applicant to withdraw, or admin to delete
        return $user->isAdmin() || $application->user_id === $user->id;
    }

    public function viewAnyForJob(User $user, Job $job): bool
    {
        // Admins or the employer who owns the job can list applications
        return $user->isAdmin() || $job->employer_id === $user->id;
    }
}
