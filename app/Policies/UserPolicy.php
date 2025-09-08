<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    // List users — admin only (we’re not exposing this now, but it's here for completeness)
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    // View a user — self or admin
    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->isAdmin();
    }

    // Anyone can register — we don’t gate registration by policy
    public function create(?User $user): bool
    {
        return true;
    }

    // Update a user — self or admin
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->isAdmin();
    }

    // Delete a user — admin only
    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin();
    }
}
