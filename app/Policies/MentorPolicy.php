<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Mentor;

class MentorPolicy
{
    // Anyone can view list/details (public)
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Mentor $mentor): bool
    {
        return true;
    }

    // Admin-only actions
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Mentor $mentor): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Mentor $mentor): bool
    {
        return $user->isAdmin();
    }
}

