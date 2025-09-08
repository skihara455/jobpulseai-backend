<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function viewAny(?User $user): bool
    {
        return true; // public index
    }

    public function view(?User $user, Company $company): bool
    {
        return true; // public show
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployer();
    }

    public function update(User $user, Company $company): bool
    {
        return $user->isAdmin() || $company->owner_id === $user->id;
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->isAdmin() || $company->owner_id === $user->id;
    }

    // Handy for logo upload endpoints
    public function uploadLogo(User $user, Company $company): bool
    {
        return $this->update($user, $company);
    }
}
