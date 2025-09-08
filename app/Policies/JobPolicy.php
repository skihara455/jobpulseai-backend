<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;

class JobPolicy
{
    public function viewAny(?User $user): bool
    {
        return true; // public listings
    }

    public function view(?User $user, Job $job): bool
    {
        return true; // public job detail
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployer();
    }

    public function update(User $user, Job $job): bool
    {
        return $user->isAdmin() || $job->employer_id === $user->id;
    }

    public function delete(User $user, Job $job): bool
    {
        return $user->isAdmin() || $job->employer_id === $user->id;
    }

    public function viewApplications(User $user, Job $job): bool
    {
        // Employer who owns the job or an admin can view applications
        return $user->isAdmin() || (int) $job->employer_id === (int) $user->id;
    }
}
